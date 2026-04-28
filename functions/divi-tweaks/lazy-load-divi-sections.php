<?php
/**
 * Lazy load Divi sections on the front-end.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'the_content', 'dlck_divi_lazy_defer_sections', 98 );
add_filter( 'the_content', 'dlck_divi_lazy_load_sections', 99 );
add_action( 'wp', 'dlck_divi_lazy_disable_dynamic_assets', 1 );
add_action( 'wp_ajax_dlck_lazy_load_section', 'dlck_divi_lazy_load_section' );
add_action( 'wp_ajax_nopriv_dlck_lazy_load_section', 'dlck_divi_lazy_load_section' );
add_action( 'save_post', 'dlck_divi_lazy_clear_cache_for_post', 10, 2 );
add_action( 'delete_post', 'dlck_divi_lazy_clear_cache_for_post', 10, 1 );
add_action( 'save_post', 'dlck_divi_lazy_warm_cache_after_save', 30, 2 );
add_action( 'add_meta_boxes', 'dlck_divi_lazy_add_metabox' );
add_action( 'save_post', 'dlck_divi_lazy_save_metabox', 10, 2 );
add_filter( 'rocket_delay_js_exclusions', 'dlck_divi_lazy_rocket_delay_exclusions' );
add_action( 'after_rocket_clean_post', 'dlck_divi_lazy_clear_cache_after_rocket', 10, 3 );
add_action( 'after_rocket_clean_domain', 'dlck_divi_lazy_clear_cache_all', 10, 3 );
add_action( 'after_rocket_clean_cache_dir', 'dlck_divi_lazy_clear_cache_all', 10, 0 );
add_action( 'rocket_purge_cache', 'dlck_divi_lazy_clear_cache_after_rocket_purge', 10, 4 );
add_action( 'wp_ajax_dlck_clear_lazy_cache', 'dlck_divi_lazy_clear_cache_ajax' );
add_action( 'rocket_preload_completed', 'dlck_divi_lazy_preload_on_rocket', 10, 2 );
add_action( 'after_rocket_clean_post', 'dlck_divi_lazy_warm_cache_after_rocket_post', 20, 3 );
add_action( 'after_rocket_clean_domain', 'dlck_divi_lazy_warm_cache_after_rocket_domain', 20, 3 );
add_action( 'admin_bar_menu', 'dlck_divi_lazy_add_dipi_clear_menu', 1000 );
add_action( 'dlck_collect_inline_assets_front', 'dlck_divi_lazy_collect_dipi_clear_scripts' );
add_action( 'dipi_clear_cache', 'dlck_divi_lazy_clear_cache_all', 10, 0 );

/**
 * Front-end content filter to output only the initial sections.
 *
 * @param string $content Rendered post content.
 * @return string
 */
function dlck_divi_lazy_load_sections( $content ) {
	if ( ! dlck_divi_lazy_should_run() ) {
		return $content;
	}

	$post_id = get_the_ID();
	$queried_id = get_queried_object_id();
	if ( ! $post_id ) {
		$post_id = $queried_id;
	}
	if ( ! $post_id ) {
		return $content;
	}
	if ( $queried_id && (int) $post_id !== (int) $queried_id ) {
		return $content;
	}

	if ( get_post_meta( $post_id, '_dlck_divi_lazy_off', true ) ) {
		return $content;
	}

	$cache = dlck_divi_lazy_get_cache_from_content( $post_id, $content );
	if ( empty( $cache['chunk0'] ) || empty( $cache['chunks'] ) || $cache['chunks'] < 2 ) {
		$cache = dlck_divi_lazy_rebuild_cache( $post_id );
	}
	if ( empty( $cache['chunk0'] ) || empty( $cache['chunks'] ) || $cache['chunks'] < 2 ) {
		return $content;
	}

	dlck_divi_lazy_enqueue_assets( $post_id, $cache );

	return $cache['chunk0'];
}

/**
 * Defer below-the-fold sections using content-visibility.
 *
 * @param string $content Rendered post content.
 * @return string
 */
function dlck_divi_lazy_defer_sections( $content ) {
	if ( ! dlck_divi_lazy_should_defer() ) {
		return $content;
	}

	$post_id    = get_the_ID();
	$queried_id = get_queried_object_id();
	if ( ! $post_id ) {
		$post_id = $queried_id;
	}
	if ( ! $post_id ) {
		return $content;
	}
	if ( $queried_id && (int) $post_id !== (int) $queried_id ) {
		return $content;
	}

	if ( get_post_meta( $post_id, '_dlck_divi_lazy_off', true ) ) {
		return $content;
	}

	$extracted = dlck_divi_lazy_extract_sections( $content );
	if ( empty( $extracted['sections'] ) ) {
		dlck_divi_lazy_enqueue_assets( $post_id, array() );
		return $content;
	}

	$sections = $extracted['sections'];
	$initial  = dlck_divi_lazy_get_defer_initial_sections();

	if ( count( $sections ) <= $initial ) {
		return $content;
	}

	for ( $i = $initial; $i < count( $sections ); $i++ ) {
		$sections[ $i ] = dlck_divi_lazy_add_defer_class( $sections[ $i ] );
	}

	$output = implode( '', $sections );
	if ( $extracted['wrapper_start'] !== '' && $extracted['wrapper_end'] !== '' ) {
		$output = $extracted['wrapper_start'] . $output . $extracted['wrapper_end'];
	}

	dlck_divi_lazy_enqueue_assets( $post_id, array() );

	return $output;
}

/**
 * Determine if lazy loading should run on this request.
 */
function dlck_divi_lazy_should_run(): bool {
	if ( ! function_exists( 'dlck_get_option' ) || dlck_get_option( 'dlck_divi_lazy_loading' ) !== '1' ) {
		return false;
	}

	return dlck_divi_lazy_request_is_eligible();
}

/**
 * Determine if deferring should run on this request.
 */
function dlck_divi_lazy_should_defer(): bool {
	if ( ! function_exists( 'dlck_get_option' ) || dlck_get_option( 'dlck_divi_lazy_defer_sections' ) !== '1' ) {
		return false;
	}

	if ( dlck_get_option( 'dlck_divi_lazy_loading' ) === '1' ) {
		return false;
	}

	return dlck_divi_lazy_request_is_eligible();
}

/**
 * Shared checks for lazy load + defer.
 */
function dlck_divi_lazy_request_is_eligible(): bool {
	if ( ! function_exists( 'dlck_is_divi_theme_active' ) || ! dlck_is_divi_theme_active() ) {
		return false;
	}

	if ( is_admin() || wp_doing_ajax() || is_feed() || is_customize_preview() ) {
		return false;
	}

	if ( function_exists( 'dlck_get_option' ) && dlck_get_option( 'dlck_divi_lazy_home_only' ) === '1' && ! is_front_page() ) {
		return false;
	}

	if ( is_home() || is_archive() || is_search() ) {
		return false;
	}

	if ( ! is_singular() ) {
		return false;
	}

	if ( is_user_logged_in() ) {
		return false;
	}

	if ( isset( $_GET['et_fb'] ) && $_GET['et_fb'] === '1' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return false;
	}

	$post = get_post();
	if ( ! $post || post_password_required( $post ) ) {
		return false;
	}

	$page_for_posts = (int) get_option( 'page_for_posts' );
	if ( $page_for_posts && (int) get_queried_object_id() === $page_for_posts ) {
		return false;
	}

	if ( dlck_divi_lazy_is_excluded_url() ) {
		return false;
	}

	return true;
}

/**
 * Disable Divi dynamic assets when lazy loading sections.
 *
 * Ensures module styles/scripts are available for sections loaded after initial render.
 */
function dlck_divi_lazy_disable_dynamic_assets(): void {
	if ( ! dlck_divi_lazy_should_run() && ! dlck_divi_lazy_should_defer() ) {
		return;
	}

	add_filter( 'et_builder_should_load_all_module_data', '__return_true' );
	add_filter( 'et_disable_js_on_demand', '__return_true' );
	add_filter( 'et_use_dynamic_css', '__return_false' );
	add_filter( 'et_should_generate_dynamic_assets', '__return_false' );
	add_filter( 'et_builder_critical_css_enabled', '__return_false' );
	add_filter( 'et_builder_post_feature_cache_enabled', '__return_false' );
}

/**
 * Enqueue lazy load assets and pass config to the script.
 *
 * @param int   $post_id Post ID.
 * @param array $cache   Cache metadata.
 */
function dlck_divi_lazy_enqueue_assets( int $post_id, array $cache ): void {
	static $enqueued = false;

	if ( $enqueued ) {
		return;
	}

	$enqueued = true;

	$cache_key   = isset( $cache['key'] ) ? (string) $cache['key'] : '';
	$chunk_count = isset( $cache['chunks'] ) ? (int) $cache['chunks'] : 0;

	$js_path  = DLCK_LC_KIT_PLUGIN_DIR . 'assets/js/divi-lazy-load.js';
	$css_path = DLCK_LC_KIT_PLUGIN_DIR . 'assets/css/divi-lazy-load.css';
	$js_url   = DLCK_LC_KIT_PLUGIN_URI . '/assets/js/divi-lazy-load.js';
	$css_url  = DLCK_LC_KIT_PLUGIN_URI . '/assets/css/divi-lazy-load.css';

	wp_enqueue_script(
		'dlck-divi-lazy-load',
		$js_url,
		array( 'jquery' ),
		file_exists( $js_path ) ? filemtime( $js_path ) : null,
		true
	);

	wp_enqueue_style(
		'dlck-divi-lazy-load',
		$css_url,
		array(),
		file_exists( $css_path ) ? filemtime( $css_path ) : null
	);

	$loader_color = dlck_get_option( 'dlck_divi_lazy_loader_color', '#666666' );
	$loader_bg    = dlck_get_option( 'dlck_divi_lazy_loader_bg_color', '#ffffff' );
	$loader_size  = (int) dlck_get_option( 'dlck_divi_lazy_loader_size', 64 );
	$prefetch_offset = (int) dlck_get_option( 'dlck_divi_lazy_prefetch_offset', 300 );
	$load_all_interaction = dlck_get_option( 'dlck_divi_lazy_load_all_on_interaction' ) === '1';
	$load_all_idle = dlck_get_option( 'dlck_divi_lazy_load_all_on_idle' ) === '1';
	if ( $load_all_interaction && $load_all_idle ) {
		$load_all_idle = false;
	}

	$loader_color = sanitize_hex_color( $loader_color ) ?: '#666666';
	$loader_bg    = sanitize_hex_color( $loader_bg ) ?: '#ffffff';
	if ( $loader_size < 24 || $loader_size > 200 ) {
		$loader_size = 64;
	}
	if ( $prefetch_offset < 0 ) {
		$prefetch_offset = 0;
	}
	if ( $prefetch_offset > 2000 ) {
		$prefetch_offset = 2000;
	}

	$defer_sections = dlck_get_option( 'dlck_divi_lazy_defer_sections' ) === '1';
	$defer_initial  = (int) dlck_get_option( 'dlck_divi_lazy_defer_initial', 2 );
	if ( $defer_initial <= 0 ) {
		$defer_initial = 2;
	}
	$defer_margin = 600;

	$loader_scale = $loader_size / 64;
	$bg_rgb       = dlck_divi_lazy_hex_to_rgb( $loader_bg );

	wp_add_inline_style(
		'dlck-divi-lazy-load',
		sprintf(
			'#dlck-lazy-loader{--dlck-lazy-color:%1$s;--dlck-lazy-bg:%2$s;--dlck-lazy-bg-rgb:%3$s;--dlck-lazy-scale:%4$s;}',
			$loader_color,
			$loader_bg,
			$bg_rgb,
			rtrim( rtrim( sprintf( '%.3f', $loader_scale ), '0' ), '.' )
		)
	);

	wp_localize_script(
		'dlck-divi-lazy-load',
		'dlckLazyLoad',
		array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'postId'     => $post_id,
			'cacheKey'   => $cache_key,
			'chunkCount' => $chunk_count,
			'prefetchOffset' => $prefetch_offset,
			'loadAllOnInteraction' => $load_all_interaction,
			'loadAllOnIdle' => $load_all_idle,
			'deferSections' => $defer_sections,
			'deferInitial' => $defer_initial,
			'deferMargin' => $defer_margin,
			'strings'    => array(
				'loading' => __( 'Loading...', 'divi-lc-kit' ),
			),
		)
	);
}

/**
 * Convert a hex color to an RGB string for CSS.
 */
function dlck_divi_lazy_hex_to_rgb( string $color ): string {
	$hex = ltrim( $color, '#' );
	if ( strlen( $hex ) === 3 ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if ( strlen( $hex ) !== 6 ) {
		return '255,255,255';
	}

	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );

	return $r . ',' . $g . ',' . $b;
}

/**
 * AJAX handler to return the next lazy-loaded section chunk.
 */
function dlck_divi_lazy_load_section(): void {
	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$chunk   = isset( $_POST['chunk'] ) ? absint( $_POST['chunk'] ) : 0;

	if ( ! $post_id || $chunk < 1 ) {
		wp_die();
	}

	$post = get_post( $post_id );
	if ( ! $post || post_password_required( $post ) ) {
		wp_die();
	}

	if ( ! dlck_divi_lazy_is_post_public( $post ) ) {
		wp_die();
	}

	$cache = dlck_divi_lazy_get_cache_from_storage( $post_id );
	if ( empty( $cache['chunks'] ) || $chunk >= (int) $cache['chunks'] ) {
		$cache = dlck_divi_lazy_rebuild_cache( $post_id );
	}

	if ( empty( $cache['chunks'] ) || $chunk >= (int) $cache['chunks'] ) {
		wp_die();
	}

	$html = dlck_divi_lazy_read_chunk( $post_id, $cache['key'], $chunk );
	if ( $html === '' ) {
		wp_die();
	}

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	wp_die();
}

/**
 * Determine if a post is readable for the current visitor.
 */
function dlck_divi_lazy_is_post_public( WP_Post $post ): bool {
	if ( is_user_logged_in() ) {
		return current_user_can( 'read_post', $post->ID );
	}

	return $post->post_status === 'publish';
}

/**
 * Build lazy cache when WP Rocket preloads a URL.
 *
 * @param string $url Preloaded URL.
 * @param string $device Device flag from WP Rocket.
 */
function dlck_divi_lazy_preload_on_rocket( string $url, string $device = '' ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( ! function_exists( 'dlck_get_option' ) || dlck_get_option( 'dlck_divi_lazy_loading' ) !== '1' ) {
		return;
	}

	if ( ! function_exists( 'dlck_is_divi_theme_active' ) || ! dlck_is_divi_theme_active() ) {
		return;
	}

	$post_id = dlck_divi_lazy_url_to_post_id( $url );
	if ( ! $post_id ) {
		return;
	}

	if ( get_post_meta( $post_id, '_dlck_divi_lazy_off', true ) ) {
		return;
	}

	$post = get_post( $post_id );
	if ( ! $post || ! dlck_divi_lazy_is_post_public( $post ) ) {
		return;
	}

	$key = dlck_divi_lazy_get_cache_key( $post_id );
	if ( dlck_divi_lazy_read_chunk( $post_id, $key, 0 ) !== '' ) {
		return;
	}

	dlck_divi_lazy_rebuild_cache( $post_id );
}

/**
 * Resolve a URL to a post ID, including front page.
 */
function dlck_divi_lazy_url_to_post_id( string $url ): int {
	$post_id = url_to_postid( $url );
	if ( $post_id ) {
		return (int) $post_id;
	}

	$home = home_url( '/' );
	if ( untrailingslashit( $url ) === untrailingslashit( $home ) ) {
		$front_id = (int) get_option( 'page_on_front' );
		if ( $front_id ) {
			return $front_id;
		}
	}

	return 0;
}

/**
 * Warm lazy cache after WP Rocket clears a post when manual preload is disabled.
 *
 * @param WP_Post $post Post object.
 */
function dlck_divi_lazy_warm_cache_after_rocket_post( $post, $purge_urls = null, $lang = null ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( ! dlck_divi_lazy_should_warm_after_purge() ) {
		return;
	}

	if ( $post instanceof WP_Post ) {
		dlck_divi_lazy_maybe_warm_post( $post->ID );
	}
}

/**
 * Warm lazy cache after WP Rocket clears the domain when manual preload is disabled.
 */
function dlck_divi_lazy_warm_cache_after_rocket_domain( $root = null, $lang = null, $url = null ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( ! dlck_divi_lazy_should_warm_after_purge() ) {
		return;
	}

	$front_id = dlck_divi_lazy_get_front_page_id();
	if ( $front_id ) {
		dlck_divi_lazy_maybe_warm_post( $front_id );
	}
}

/**
 * Warm lazy cache after post saves, even when WP Rocket is unavailable.
 */
function dlck_divi_lazy_warm_cache_after_save( int $post_id, WP_Post $post ): void {
	if ( ! function_exists( 'dlck_get_option' ) || dlck_get_option( 'dlck_divi_lazy_loading' ) !== '1' ) {
		return;
	}

	if ( ! function_exists( 'dlck_is_divi_theme_active' ) || ! dlck_is_divi_theme_active() ) {
		return;
	}

	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( $post->post_status !== 'publish' ) {
		return;
	}

	dlck_divi_lazy_maybe_warm_post( $post_id );
}

/**
 * Determine if lazy cache should warm after WP Rocket purge (when preload is disabled).
 */
function dlck_divi_lazy_should_warm_after_purge(): bool {
	if ( ! function_exists( 'dlck_get_option' ) || dlck_get_option( 'dlck_divi_lazy_loading' ) !== '1' ) {
		return false;
	}

	if ( dlck_get_option( 'dlck_divi_lazy_preload_on_purge' ) !== '1' ) {
		return false;
	}

	if ( function_exists( 'get_rocket_option' ) && get_rocket_option( 'manual_preload', false ) ) {
		return false;
	}

	return true;
}

/**
 * Warm lazy cache for a specific post ID.
 */
function dlck_divi_lazy_maybe_warm_post( int $post_id ): void {
	if ( ! $post_id ) {
		return;
	}

	if ( get_post_meta( $post_id, '_dlck_divi_lazy_off', true ) ) {
		return;
	}

	$post = get_post( $post_id );
	if ( ! $post || ! dlck_divi_lazy_is_post_public( $post ) ) {
		return;
	}

	$key = dlck_divi_lazy_get_cache_key( $post_id );
	if ( dlck_divi_lazy_read_chunk( $post_id, $key, 0 ) !== '' ) {
		return;
	}

	dlck_divi_lazy_rebuild_cache( $post_id );
}

/**
 * Get the static front page ID, if set.
 */
function dlck_divi_lazy_get_front_page_id(): int {
	if ( get_option( 'show_on_front' ) !== 'page' ) {
		return 0;
	}

	return (int) get_option( 'page_on_front' );
}

/**
 * Build cache from existing rendered content.
 */
function dlck_divi_lazy_get_cache_from_content( int $post_id, string $content ): array {
	$key  = dlck_divi_lazy_get_cache_key( $post_id );
	$meta = dlck_divi_lazy_read_meta( $post_id );

	if ( $meta && ! empty( $meta['key'] ) && $meta['key'] === $key ) {
		$chunk0 = dlck_divi_lazy_read_chunk( $post_id, $key, 0 );
		if ( $chunk0 !== '' ) {
			return array(
				'key'    => $key,
				'chunks' => (int) $meta['chunks'],
				'chunk0' => $chunk0,
			);
		}
	}

	return dlck_divi_lazy_build_cache( $post_id, $content, $key );
}

/**
 * Build cache from a fresh render when needed.
 */
function dlck_divi_lazy_rebuild_cache( int $post_id ): array {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return array();
	}

	$original_priority = has_filter( 'the_content', 'dlck_divi_lazy_load_sections' );
	if ( false !== $original_priority ) {
		remove_filter( 'the_content', 'dlck_divi_lazy_load_sections', $original_priority );
	}

	$old_post = $GLOBALS['post'] ?? null;
	$GLOBALS['post'] = $post; // Provide context for filters expecting global post.
	if ( function_exists( 'setup_postdata' ) ) {
		setup_postdata( $post );
	}

	$content = apply_filters( 'the_content', $post->post_content );

	if ( function_exists( 'wp_reset_postdata' ) ) {
		wp_reset_postdata();
	}
	if ( $old_post instanceof WP_Post ) {
		$GLOBALS['post'] = $old_post;
	} elseif ( $old_post === null ) {
		unset( $GLOBALS['post'] );
	}

	if ( false !== $original_priority ) {
		add_filter( 'the_content', 'dlck_divi_lazy_load_sections', $original_priority );
	}

	$key = dlck_divi_lazy_get_cache_key( $post_id );
	return dlck_divi_lazy_build_cache( $post_id, $content, $key );
}

/**
 * Create cache files for sections.
 */
function dlck_divi_lazy_build_cache( int $post_id, string $content, string $key ): array {
	$extracted = dlck_divi_lazy_extract_sections( $content );
	if ( empty( $extracted['sections'] ) ) {
		return array();
	}

	$sections      = $extracted['sections'];
	$wrapper_start = $extracted['wrapper_start'];
	$wrapper_end   = $extracted['wrapper_end'];

	$initial    = dlck_divi_lazy_get_initial_sections();
	$subsequent = dlck_divi_lazy_get_subsequent_sections();

	if ( count( $sections ) <= $initial ) {
		return array();
	}

	$chunks = dlck_divi_lazy_chunk_sections( $sections, $initial, $subsequent );
	if ( count( $chunks ) < 2 ) {
		return array();
	}

	if ( $wrapper_start !== '' && $wrapper_end !== '' ) {
		$chunks[0] = $wrapper_start . $chunks[0] . $wrapper_end;
	}

	$dir = dlck_divi_lazy_get_cache_dir( $post_id );
	if ( ! wp_mkdir_p( $dir ) ) {
		return array();
	}

	foreach ( glob( $dir . 'chunk-*.html' ) as $old_file ) {
		@unlink( $old_file );
	}

	foreach ( $chunks as $index => $html ) {
		$path = dlck_divi_lazy_get_chunk_path( $post_id, $key, $index );
		file_put_contents( $path, $html );
	}

	$meta = array(
		'key'    => $key,
		'chunks' => count( $chunks ),
		'built'  => time(),
	);
	file_put_contents( dlck_divi_lazy_get_meta_path( $post_id ), wp_json_encode( $meta ) );

	return array(
		'key'    => $key,
		'chunks' => count( $chunks ),
		'chunk0' => $chunks[0],
	);
}

/**
 * Extract top-level Divi sections from rendered content.
 *
 * @param string $content Rendered HTML.
 * @return array
 */
function dlck_divi_lazy_extract_sections( string $content ): array {
	$content = trim( $content );
	if ( $content === '' ) {
		return array();
	}
	if ( ! class_exists( 'DOMDocument' ) ) {
		return array();
	}

	$previous_state = libxml_use_internal_errors( true );
	$doc            = new DOMDocument( '1.0', 'UTF-8' );
	$wrapper        = '<div id="dlck-lazy-root">' . $content . '</div>';

	$loaded = $doc->loadHTML( '<?xml encoding="utf-8" ?>' . $wrapper, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous_state );

	if ( ! $loaded ) {
		return array();
	}

	$root = $doc->getElementById( 'dlck-lazy-root' );
	if ( ! $root ) {
		return array();
	}

	$container      = $root;
	$wrapper_starts = array();
	$wrapper_ends   = array();

	while ( true ) {
		$elements = array();
		foreach ( $container->childNodes as $child ) {
			if ( $child->nodeType === XML_ELEMENT_NODE ) {
				$elements[] = $child;
			} elseif ( $child->nodeType === XML_COMMENT_NODE ) {
				continue;
			} elseif ( trim( $child->textContent ) !== '' ) {
				return array();
			}
		}

		if ( count( $elements ) === 1 && ! dlck_divi_lazy_is_section_node( $elements[0] ) ) {
			$container = $elements[0];
			$tags = dlck_divi_lazy_get_wrapper_tags( $container );
			$wrapper_starts[] = $tags['start'];
			$wrapper_ends[]   = $tags['end'];
			continue;
		}

		if ( empty( $elements ) ) {
			return array();
		}

		break;
	}

	$sections = array();
	foreach ( $container->childNodes as $child ) {
		if ( $child->nodeType === XML_ELEMENT_NODE ) {
			if ( dlck_divi_lazy_is_section_node( $child ) ) {
				$sections[] = $doc->saveHTML( $child );
			} elseif ( dlck_divi_lazy_is_ignorable_node( $child ) ) {
				continue;
			} else {
				return array();
			}
		} elseif ( $child->nodeType === XML_COMMENT_NODE ) {
			continue;
		} elseif ( trim( $child->textContent ) !== '' ) {
			return array();
		}
	}

	if ( empty( $sections ) ) {
		return array();
	}

	return array(
		'sections'      => $sections,
		'wrapper_start' => implode( '', $wrapper_starts ),
		'wrapper_end'   => implode( '', array_reverse( $wrapper_ends ) ),
	);
}

/**
 * Ensure a deferred section has the defer class.
 */
function dlck_divi_lazy_add_defer_class( string $section_html ): string {
	$section_html = trim( $section_html );
	if ( $section_html === '' ) {
		return $section_html;
	}
	if ( ! class_exists( 'DOMDocument' ) ) {
		return $section_html;
	}

	$previous_state = libxml_use_internal_errors( true );
	$doc            = new DOMDocument( '1.0', 'UTF-8' );
	$wrapper        = '<div id="dlck-defer-root">' . $section_html . '</div>';
	$loaded         = $doc->loadHTML( '<?xml encoding="utf-8" ?>' . $wrapper, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous_state );

	if ( ! $loaded ) {
		return $section_html;
	}

	$root = $doc->getElementById( 'dlck-defer-root' );
	if ( ! $root ) {
		return $section_html;
	}

	foreach ( $root->childNodes as $child ) {
		if ( $child->nodeType !== XML_ELEMENT_NODE ) {
			continue;
		}

		$class_attr = $child->attributes->getNamedItem( 'class' );
		$classes    = $class_attr ? $class_attr->nodeValue : '';
		if ( strpos( ' ' . $classes . ' ', ' dlck-defer-section ' ) === false ) {
			$child->setAttribute( 'class', trim( $classes . ' dlck-defer-section' ) );
		}
		break;
	}

	$output = '';
	foreach ( $root->childNodes as $child ) {
		$output .= $doc->saveHTML( $child );
	}

	return $output !== '' ? $output : $section_html;
}

/**
 * Determine if a DOM element is a Divi section.
 */
function dlck_divi_lazy_is_section_node( DOMNode $node ): bool {
	if ( $node->nodeType !== XML_ELEMENT_NODE ) {
		return false;
	}

	$class = $node->attributes->getNamedItem( 'class' );
	if ( ! $class ) {
		return false;
	}

	return strpos( $class->nodeValue, 'et_pb_section' ) !== false;
}

/**
 * Determine if a node can be ignored between sections (empty helper markup).
 */
function dlck_divi_lazy_is_ignorable_node( DOMNode $node ): bool {
	if ( $node->nodeType !== XML_ELEMENT_NODE ) {
		return false;
	}

	$tag = strtolower( $node->nodeName );
	if ( in_array( $tag, array( 'script', 'style' ), true ) ) {
		return false;
	}

	if ( trim( $node->textContent ) !== '' ) {
		return false;
	}

	foreach ( $node->childNodes as $child ) {
		if ( $child->nodeType === XML_COMMENT_NODE ) {
			continue;
		}
		if ( $child->nodeType === XML_TEXT_NODE && trim( $child->textContent ) === '' ) {
			continue;
		}
		if ( $child->nodeType === XML_ELEMENT_NODE ) {
			if ( dlck_divi_lazy_is_ignorable_node( $child ) ) {
				continue;
			}
			return false;
		}
	}

	return true;
}

/**
 * Build wrapper opening/closing tags for a container.
 *
 * @param DOMElement $element Wrapper element.
 * @return array{start:string,end:string}
 */
function dlck_divi_lazy_get_wrapper_tags( DOMElement $element ): array {
	$doc = $element->ownerDocument;
	if ( ! $doc ) {
		return array(
			'start' => '',
			'end'   => '',
		);
	}

	$clone = $element->cloneNode( false );
	$outer = $doc->saveHTML( $clone );
	$end   = '</' . $clone->tagName . '>';

	if ( substr( $outer, -strlen( $end ) ) === $end ) {
		$start = substr( $outer, 0, -strlen( $end ) );
	} else {
		$start = $outer;
	}

	return array(
		'start' => $start,
		'end'   => $end,
	);
}

/**
 * Build HTML chunks for initial + subsequent sections.
 */
function dlck_divi_lazy_chunk_sections( array $sections, int $initial, int $subsequent ): array {
	$chunks = array();

	$chunks[] = array_slice( $sections, 0, $initial );
	$remaining = array_slice( $sections, $initial );

	if ( $remaining ) {
		for ( $i = 0, $len = count( $remaining ); $i < $len; $i += $subsequent ) {
			$chunks[] = array_slice( $remaining, $i, $subsequent );
		}
	}

	return array_map(
		static function ( array $chunk ): string {
			return implode( '', $chunk );
		},
		$chunks
	);
}

/**
 * Read cached chunk HTML.
 */
function dlck_divi_lazy_read_chunk( int $post_id, string $key, int $chunk ): string {
	$path = dlck_divi_lazy_get_chunk_path( $post_id, $key, $chunk );
	if ( ! file_exists( $path ) ) {
		return '';
	}

	$data = file_get_contents( $path );
	return $data ? $data : '';
}

/**
 * Read cache metadata.
 */
function dlck_divi_lazy_read_meta( int $post_id ): ?array {
	$path = dlck_divi_lazy_get_meta_path( $post_id );
	if ( ! file_exists( $path ) ) {
		return null;
	}

	$raw = file_get_contents( $path );
	if ( ! $raw ) {
		return null;
	}

	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) {
		return null;
	}

	return $data;
}

/**
 * Get cache meta + key from storage.
 */
function dlck_divi_lazy_get_cache_from_storage( int $post_id ): array {
	$key  = dlck_divi_lazy_get_cache_key( $post_id );
	$meta = dlck_divi_lazy_read_meta( $post_id );

	if ( ! $meta || empty( $meta['key'] ) || $meta['key'] !== $key ) {
		return array();
	}

	return array(
		'key'    => $key,
		'chunks' => (int) $meta['chunks'],
	);
}

/**
 * Cache directory for a post.
 */
function dlck_divi_lazy_get_cache_dir( int $post_id ): string {
	$base = dlck_divi_lazy_get_cache_base_dir();
	return trailingslashit( $base . $post_id );
}

function dlck_divi_lazy_get_cache_base_dir(): string {
	$base = trailingslashit( WP_CONTENT_DIR . '/cache/lc-tweaks-lazy' );
	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		$base .= get_current_blog_id() . '/';
	}
	return $base;
}

function dlck_divi_lazy_get_meta_path( int $post_id ): string {
	return dlck_divi_lazy_get_cache_dir( $post_id ) . 'meta.json';
}

function dlck_divi_lazy_get_chunk_path( int $post_id, string $key, int $chunk ): string {
	return dlck_divi_lazy_get_cache_dir( $post_id ) . 'chunk-' . $key . '-' . $chunk . '.html';
}

/**
 * Cache key based on post revision + settings.
 */
function dlck_divi_lazy_get_cache_key( int $post_id ): string {
	$post = get_post( $post_id );
	$modified = $post ? $post->post_modified_gmt : '';
	$initial = dlck_divi_lazy_get_initial_sections();
	$subsequent = dlck_divi_lazy_get_subsequent_sections();

	return md5( $post_id . '|' . $modified . '|' . $initial . '|' . $subsequent );
}

function dlck_divi_lazy_get_initial_sections(): int {
	$val = (int) dlck_get_option( 'dlck_divi_lazy_sections_initial', 2 );
	return $val > 0 ? $val : 2;
}

function dlck_divi_lazy_get_defer_initial_sections(): int {
	$val = (int) dlck_get_option( 'dlck_divi_lazy_defer_initial', 2 );
	return $val > 0 ? $val : 2;
}

function dlck_divi_lazy_get_subsequent_sections(): int {
	$val = (int) dlck_get_option( 'dlck_divi_lazy_sections_subsequent', 2 );
	return $val > 0 ? $val : 2;
}

/**
 * Check if the current URL is excluded.
 */
function dlck_divi_lazy_is_excluded_url(): bool {
	$raw_list = dlck_get_option( 'dlck_divi_lazy_exclude_urls', '' );
	if ( ! is_string( $raw_list ) || trim( $raw_list ) === '' ) {
		return false;
	}

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$path = (string) wp_parse_url( $request_uri, PHP_URL_PATH );

	$lines = preg_split( '/\r\n|\r|\n/', $raw_list );
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( $line === '' ) {
			continue;
		}
		$line = wp_parse_url( $line, PHP_URL_PATH ) ?: $line;
		if ( $line[0] !== '/' ) {
			$line = '/' . $line;
		}
		if ( function_exists( 'fnmatch' ) ) {
			if ( fnmatch( $line, $path ) ) {
				return true;
			}
		} else {
			$pattern = '#^' . str_replace( '\*', '.*', preg_quote( $line, '#' ) ) . '$#';
			if ( preg_match( $pattern, $path ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Clear cached sections for a post.
 *
 * @param int $post_id Post ID.
 */
function dlck_divi_lazy_clear_cache_for_post( $post_id, $post = null ): void {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return;
	}

	$dir = dlck_divi_lazy_get_cache_dir( $post_id );
	if ( ! is_dir( $dir ) ) {
		return;
	}

	$files = glob( $dir . '*.*' );
	if ( $files ) {
		foreach ( $files as $file ) {
			@unlink( $file );
		}
	}
}

/**
 * Clear all cached lazy-load chunks.
 */
function dlck_divi_lazy_clear_cache_all( ...$args ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	$base = dlck_divi_lazy_get_cache_base_dir();
	if ( ! is_dir( $base ) ) {
		return;
	}

	$entries = glob( $base . '*' );
	if ( ! $entries ) {
		return;
	}

	foreach ( $entries as $entry ) {
		if ( is_dir( $entry ) ) {
			$files = glob( trailingslashit( $entry ) . '*.*' );
			if ( $files ) {
				foreach ( $files as $file ) {
					@unlink( $file );
				}
			}
			@rmdir( $entry );
		} elseif ( is_file( $entry ) ) {
			@unlink( $entry );
		}
	}
}

/**
 * Clear cache after WP Rocket cleans a post.
 *
 * @param WP_Post $post Post object.
 */
function dlck_divi_lazy_clear_cache_after_rocket( $post, $purge_urls = null, $lang = null ): void {
	if ( $post instanceof WP_Post ) {
		dlck_divi_lazy_clear_cache_for_post( $post->ID );
	}
}

/**
 * Clear cache when WP Rocket purges a single URL.
 *
 * @param string $type Purge type.
 * @param int    $id Post/term/user ID when applicable.
 * @param string $taxonomy Taxonomy name when applicable.
 * @param string $url Purged URL.
 */
function dlck_divi_lazy_clear_cache_after_rocket_purge( $type, $id, $taxonomy, $url ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( $type !== 'url' ) {
		return;
	}

	if ( ! is_string( $url ) || $url === '' ) {
		return;
	}

	$post_id = dlck_divi_lazy_url_to_post_id( $url );
	if ( ! $post_id ) {
		return;
	}

	dlck_divi_lazy_clear_cache_for_post( $post_id );
}

/**
 * Add a per-post toggle to disable lazy loading.
 */
function dlck_divi_lazy_add_metabox(): void {
	if ( ! function_exists( 'dlck_get_option' ) || dlck_get_option( 'dlck_divi_lazy_loading' ) !== '1' ) {
		return;
	}

	$screens = array( 'post', 'page', 'product' );
	add_meta_box(
		'dlck-divi-lazy-loading',
		__( 'LC Tweaks Lazy Loading', 'divi-lc-kit' ),
		'dlck_divi_lazy_render_metabox',
		$screens,
		'side'
	);
}

/**
 * Render the lazy load metabox.
 */
function dlck_divi_lazy_render_metabox( WP_Post $post ): void {
	wp_nonce_field( 'dlck_divi_lazy_metabox', 'dlck_divi_lazy_metabox_nonce' );
	$off = get_post_meta( $post->ID, '_dlck_divi_lazy_off', true );
	?>
	<p>
		<label>
			<input type="checkbox" name="dlck_divi_lazy_off" value="1" <?php checked( '1', $off ); ?> />
			<?php esc_html_e( 'Disable lazy loading for this content', 'divi-lc-kit' ); ?>
		</label>
	</p>
	<?php
}

/**
 * Save the metabox toggle.
 */
function dlck_divi_lazy_save_metabox( int $post_id, WP_Post $post ): void {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['dlck_divi_lazy_metabox_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dlck_divi_lazy_metabox_nonce'] ) ), 'dlck_divi_lazy_metabox' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( ! empty( $_POST['dlck_divi_lazy_off'] ) ) {
		update_post_meta( $post_id, '_dlck_divi_lazy_off', '1' );
	} else {
		delete_post_meta( $post_id, '_dlck_divi_lazy_off' );
	}
}

/**
 * Keep WP Rocket from delaying the lazy-load script when enabled.
 *
 * @param array $exclusions Existing exclusions.
 * @return array
 */
function dlck_divi_lazy_rocket_delay_exclusions( array $exclusions ): array {
	$exclusions[] = 'divi-lazy-load.js';
	return $exclusions;
}

/**
 * AJAX handler to clear lazy-load cache files.
 */
function dlck_divi_lazy_clear_cache_ajax(): void {
	check_ajax_referer( 'dlck_clear_lazy_cache' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'You do not have permission to do that.', 'divi-lc-kit' ), 403 );
	}

	$base = dlck_divi_lazy_get_cache_base_dir();
	if ( ! is_dir( $base ) ) {
		wp_send_json_success( __( 'No lazy cache files to clear.', 'divi-lc-kit' ) );
	}

	$deleted = 0;
	$entries = glob( $base . '*' );
	if ( $entries ) {
		foreach ( $entries as $entry ) {
			if ( is_dir( $entry ) ) {
				$files = glob( trailingslashit( $entry ) . '*.*' );
				if ( $files ) {
					foreach ( $files as $file ) {
						if ( file_exists( $file ) ) {
							@unlink( $file );
							$deleted++;
						}
					}
				}
				@rmdir( $entry );
			} elseif ( is_file( $entry ) ) {
				@unlink( $entry );
				$deleted++;
			}
		}
	}

	if ( $deleted ) {
		wp_send_json_success( sprintf( __( 'Lazy cache cleared (%d).', 'divi-lc-kit' ), $deleted ) );
	}

	wp_send_json_success( __( 'No lazy cache files to clear.', 'divi-lc-kit' ) );
}

/**
 * Check if Divi Pixel's clear cache menu is enabled and available.
 */
function dlck_divi_lazy_dipi_menu_enabled(): bool {
	if ( ! function_exists( 'dlck_get_option' ) || dlck_get_option( 'dlck_divi_lazy_loading' ) !== '1' ) {
		return false;
	}

	if ( ! class_exists( 'DiviPixel\\DIPI_Settings' ) ) {
		return false;
	}

	if ( ! \DiviPixel\DIPI_Settings::get_option( 'show_clear_divi_cache_in_adminbar' ) ) {
		return false;
	}

	if (
		\DiviPixel\DIPI_Settings::get_option( 'show_clear_divi_cache_in_adminbar_only_admin' ) &&
		! current_user_can( 'administrator' )
	) {
		return false;
	}

	if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		return false;
	}

	return true;
}

/**
 * Add a Clear Lazy Load Cache entry under the Divi Pixel cache menu.
 *
 * @param WP_Admin_Bar $admin_bar Admin bar instance.
 */
function dlck_divi_lazy_add_dipi_clear_menu( $admin_bar ): void {
	if ( ! dlck_divi_lazy_dipi_menu_enabled() ) {
		return;
	}

	if ( ! is_object( $admin_bar ) ) {
		return;
	}

	$admin_bar->add_menu(
		array(
			'id'     => 'dlck_dipi_clear_lazy_cache',
			'parent' => 'dipi_csc',
			'title'  => sprintf(
				'<span data-wpnonce="%1$s">%2$s</span>',
				wp_create_nonce( 'dlck_clear_lazy_cache' ),
				esc_html__( 'Clear Lazy Load Cache', 'divi-lc-kit' )
			),
			'href'   => 'javascript:void(0)',
		)
	);
}

/**
 * Attach AJAX handler for the Divi Pixel lazy cache admin bar entry.
 */
function dlck_divi_lazy_collect_dipi_clear_scripts(): void {
	if ( ! dlck_divi_lazy_dipi_menu_enabled() ) {
		return;
	}

	$js = <<<'JS'
jQuery(function ($) {
    var $lazyClear = $("#wp-admin-bar-dlck_dipi_clear_lazy_cache");
    if (!$lazyClear.length) {
        return;
    }

    var adminAjaxURL = window.ajaxurl || '/wp-admin/admin-ajax.php';
    var isAdmin = $('body').hasClass('wp-admin');

    $lazyClear.on('click', function (e) {
        e.preventDefault();
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: adminAjaxURL,
            data: {
                'action': 'dlck_clear_lazy_cache',
                '_wpnonce': $(this).find('span').data('wpnonce')
            },
            success: function (response) {
                if (response && response.success) {
                    var successData = response.data || 'Lazy load cache cleared.';
                    if (isAdmin) {
                        var messageHTML = '<div class="notice notice-success pac-misc-message"><p>' + successData + '</p></div>';
                        if ($('body .wrap h1').length > 0) {
                            $('body .wrap h1').after(messageHTML);
                        } else {
                            $('body #wpbody-content').prepend(messageHTML);
                        }
                        setTimeout(function () {
                            $(".pac-misc-message").remove();
                        }, 3500);
                    } else {
                        alert(successData);
                    }
                } else if (response && response.data) {
                    alert(response.data);
                }
            }
        });
    });
});
JS;

	dlck_add_inline_js( $js, 'front' );
}
