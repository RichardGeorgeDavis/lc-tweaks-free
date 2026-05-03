<?php
/**
 * Simple inline CSS/JS aggregator with optional cache files.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if inline assets are currently being collected.
 */
function dlck_is_inline_collecting(): bool {
	return ! empty( $GLOBALS['dlck_inline_collecting'] );
}

/**
 * Internal storage for inline assets by context.
 *
 * @param string $context 'front' or 'admin'.
 * @return array{css: string[], js: string[]}
 */
function &dlck_inline_asset_store( $context = 'front' ) {
	static $store = array(
		'front' => array( 'css' => array(), 'js' => array() ),
		'admin' => array( 'css' => array(), 'js' => array() ),
	);

	if ( ! isset( $store[ $context ] ) ) {
		$store[ $context ] = array( 'css' => array(), 'js' => array() );
	}

	return $store[ $context ];
}

/**
 * Resolve the cache directory for inline assets.
 */
function dlck_inline_assets_get_cache_dir(): string {
	$base = trailingslashit( dirname( __DIR__ ) ) . 'cache/';
	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		$base .= 'site-' . get_current_blog_id() . '/';
	}
	return $base;
}

/**
 * Resolve the cache URL for inline assets.
 */
function dlck_inline_assets_get_cache_url(): string {
	$base = plugins_url( 'cache/', dirname( __FILE__ ) );
	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		$base .= 'site-' . get_current_blog_id() . '/';
	}
	return trailingslashit( $base );
}

/**
 * Reset stored snippets for a context.
 *
 * @param string $context Context identifier.
 */
function dlck_inline_asset_reset( $context ) {
	$store          = &dlck_inline_asset_store( $context );
	$store['css'] = array();
	$store['js']  = array();
}

/**
 * Add inline CSS to the aggregator.
 *
 * @param string $css Raw CSS (without <style>).
 * @param string $context Context identifier: 'front' (default) or 'admin'.
 */
function dlck_add_inline_css( $css, $context = 'front' ) {
	$css = trim( (string) $css );
	if ( $css === '' ) {
		return;
	}
	$store = &dlck_inline_asset_store( $context );
	if ( ! in_array( $css, $store['css'], true ) ) {
		$store['css'][] = $css;
	}
}

/**
 * Add inline JS to the aggregator.
 *
 * @param string $js Raw JS (without <script>).
 * @param string $context Context identifier: 'front' (default) or 'admin'.
 */
function dlck_add_inline_js( $js, $context = 'front' ) {
	$js = trim( (string) $js );
	if ( $js === '' ) {
		return;
	}
	$store = &dlck_inline_asset_store( $context );
	if ( ! in_array( $js, $store['js'], true ) ) {
		$store['js'][] = $js;
	}
}

/**
 * Build or reuse cached inline CSS/JS files and enqueue/print them.
 */
function dlck_inline_assets_enqueue() {
	dlck_inline_assets_enqueue_context( 'front' );
}
add_action(
	'wp_enqueue_scripts',
	static function () {
		dlck_inline_assets_enqueue();
	},
	50
);
add_action(
	'wp_head',
	static function () {
		// For head-critical snippets (e.g., preventing FOIT/FOUT), use the "front_head" context.
		dlck_inline_assets_output( 'front_head' );
	},
	1
);

add_action(
	'admin_enqueue_scripts',
	static function () {
		// Also load front-context assets in admin (e.g., visual builders/previews).
		dlck_inline_assets_enqueue_context( 'front' );
		dlck_inline_assets_enqueue_context( 'admin' );
	},
	50
);

/**
 * Enqueue assets for a given context.
 *
 * @param string $context Context identifier: 'front' or 'admin'.
 */
function dlck_inline_assets_enqueue_context( $context ) {
	static $enqueued_context_hashes = array();

	$blobs    = dlck_inline_assets_collect( $context );
	$css_blob = $blobs['css'];
	$js_blob  = $blobs['js'];
	$blob_hash = sha1( $css_blob . '|' . $js_blob );

	// Skip repeated enqueue passes only when content is unchanged.
	if ( isset( $enqueued_context_hashes[ $context ] ) && $enqueued_context_hashes[ $context ] === $blob_hash ) {
		return;
	}
	$enqueued_context_hashes[ $context ] = $blob_hash;

	// Nothing to do.
	if ( $css_blob === '' && $js_blob === '' ) {
		// If nothing was collected but cache files exist, reuse them.
		$upload_dir = dlck_inline_assets_get_cache_dir();
		$css_path   = $upload_dir . "dlck-inline-{$context}.css";
		$js_path    = $upload_dir . "dlck-inline-{$context}.js";
		$has_css_file = file_exists( $css_path ) && filesize( $css_path ) > 0;
		$has_js_file  = file_exists( $js_path ) && filesize( $js_path ) > 0;
		if ( ! $has_css_file && ! $has_js_file ) {
			return;
		}
		$cache_url = dlck_inline_assets_get_cache_url();
		if ( $has_css_file ) {
			$css_ver = filemtime( $css_path );
			wp_enqueue_style( "dlck-inline-css-{$context}", $cache_url . "dlck-inline-{$context}.css", array(), $css_ver ? (string) $css_ver : null );
		}
		if ( $has_js_file ) {
			$js_ver = filemtime( $js_path );
			wp_enqueue_script( "dlck-inline-js-{$context}", $cache_url . "dlck-inline-{$context}.js", array( 'jquery' ), $js_ver ? (string) $js_ver : null, true );
		}
		return;
	}

	$upload_dir = dlck_inline_assets_get_cache_dir();
	$cache_url  = dlck_inline_assets_get_cache_url();

	$has_css_file = dlck_inline_assets_maybe_write( $upload_dir, "dlck-inline-{$context}.css", $css_blob );
	$has_js_file  = dlck_inline_assets_maybe_write( $upload_dir, "dlck-inline-{$context}.js", $js_blob );

	if ( $has_css_file ) {
		$css_path = $upload_dir . "dlck-inline-{$context}.css";
		$css_ver  = file_exists( $css_path ) ? filemtime( $css_path ) : false;
		wp_enqueue_style( "dlck-inline-css-{$context}", $cache_url . "dlck-inline-{$context}.css", array(), $css_ver ? (string) $css_ver : null );
	} elseif ( $css_blob !== '' ) {
		wp_register_style( "dlck-inline-css-inline-{$context}", false );
		wp_enqueue_style( "dlck-inline-css-inline-{$context}" );
		wp_add_inline_style( "dlck-inline-css-inline-{$context}", $css_blob );
	}

	if ( $has_js_file ) {
		$js_path = $upload_dir . "dlck-inline-{$context}.js";
		$js_ver  = file_exists( $js_path ) ? filemtime( $js_path ) : false;
		wp_enqueue_script( "dlck-inline-js-{$context}", $cache_url . "dlck-inline-{$context}.js", array( 'jquery' ), $js_ver ? (string) $js_ver : null, true );
	} elseif ( $js_blob !== '' ) {
		wp_register_script( "dlck-inline-js-inline-{$context}", false, array( 'jquery' ), null, true );
		wp_enqueue_script( "dlck-inline-js-inline-{$context}" );
		wp_add_inline_script( "dlck-inline-js-inline-{$context}", $js_blob );
	}
}

/**
 * Echo inline assets and write cache files for the given context.
 *
 * @param string $context Context identifier.
 */
function dlck_inline_assets_output( $context ) {
	static $output_context_hashes = array();

	$blobs = dlck_inline_assets_collect( $context );
	$css_blob = $blobs['css'];
	$js_blob  = $blobs['js'];
	$blob_hash = sha1( $css_blob . '|' . $js_blob );

	// Skip repeated output passes only when content is unchanged.
	if ( isset( $output_context_hashes[ $context ] ) && $output_context_hashes[ $context ] === $blob_hash ) {
		return;
	}
	$output_context_hashes[ $context ] = $blob_hash;

	if ( $css_blob === '' && $js_blob === '' ) {
		return;
	}

	$dir = dlck_inline_assets_get_cache_dir();
	$write_files = ( $context !== 'front_head' ); // Keep head-critical snippets inline only.

	if ( $css_blob !== '' ) {
		if ( $write_files ) {
			dlck_inline_assets_maybe_write( $dir, "dlck-inline-{$context}.css", $css_blob );
		}
		echo '<style id="dlck-inline-css-' . esc_attr( $context ) . '">' . $css_blob . '</style>';
	}
	if ( $js_blob !== '' ) {
		if ( $write_files ) {
			dlck_inline_assets_maybe_write( $dir, "dlck-inline-{$context}.js", $js_blob );
		}
		echo '<script id="dlck-inline-js-' . esc_attr( $context ) . '">(function(){' . $js_blob . '})();</script>';
	}
}

/**
 * Collect and optionally write cache without echoing.
 *
 * @param string $context Context identifier.
 * @return array{css:string,js:string}
 */
function dlck_inline_assets_build_cache( $context ) {
	$blobs   = dlck_inline_assets_collect( $context );
	$dir     = dlck_inline_assets_get_cache_dir();
	if ( $blobs['css'] !== '' ) {
		dlck_inline_assets_maybe_write( $dir, "dlck-inline-{$context}.css", $blobs['css'] );
	} else {
		$css_path = $dir . "dlck-inline-{$context}.css";
		if ( file_exists( $css_path ) ) {
			@unlink( $css_path );
		}
		delete_option( 'dlck_' . md5( "dlck-inline-{$context}.css" ) . '_hash' );
	}

	if ( $blobs['js'] !== '' ) {
		dlck_inline_assets_maybe_write( $dir, "dlck-inline-{$context}.js", $blobs['js'] );
	} else {
		$js_path = $dir . "dlck-inline-{$context}.js";
		if ( file_exists( $js_path ) ) {
			@unlink( $js_path );
		}
		delete_option( 'dlck_' . md5( "dlck-inline-{$context}.js" ) . '_hash' );
	}
	return $blobs;
}

/**
 * Rebuild all inline caches (front, front_head, admin) and return blobs.
 *
 * @return array<string,array{css:string,js:string}>
 */
function dlck_rebuild_all_inline_caches() {
	return array(
		'front'      => dlck_inline_assets_build_cache( 'front' ),
		'front_head' => dlck_inline_assets_build_cache( 'front_head' ),
		'admin'      => dlck_inline_assets_build_cache( 'admin' ),
	);
}

/**
 * Collect snippets by firing a custom action for the context.
 *
 * @param string $context Context identifier.
 * @return array{css:string,js:string}
 */
function dlck_inline_assets_collect( $context ) {
	// Flag collection mode so snippets can skip page/admin guards.
	$GLOBALS['dlck_inline_collecting'] = true;

	dlck_inline_asset_reset( $context );

	/**
	 * Allow snippets to register front/admin assets without rendering output.
	 */
	do_action( "dlck_collect_inline_assets_{$context}" );

	$store = dlck_inline_asset_store( $context );

	// End collection flag.
	$GLOBALS['dlck_inline_collecting'] = false;

	return array(
		'css' => implode( "\n\n", $store['css'] ),
		'js'  => implode( "\n\n", $store['js'] ),
	);
}

/**
 * Write cached asset files if content exists and has changed.
 *
 * @param string $dir  Cache dir path.
 * @param string $file File name.
 * @param string $data Content to write.
 * @return bool True if file exists/usable.
 */
function dlck_inline_assets_maybe_write( $dir, $file, $data ) {
	if ( $data === '' ) {
		return false;
	}

	$target = $dir . $file;

	// Ensure directory exists.
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}

	$hash         = sha1( $data );
	$hash_option  = 'dlck_' . md5( $file ) . '_hash';
	$stored_hash  = get_option( $hash_option );

	if ( file_exists( $target ) && $stored_hash === $hash ) {
		return true;
	}

	$written = file_put_contents( $target, $data );
	if ( $written === false ) {
		return false;
	}

	update_option( $hash_option, $hash, false );

	return true;
}

/**
 * AJAX handler to clear generated cache files.
 */
function dlck_clear_cache_files_ajax() {
	check_ajax_referer( 'dlck_clear_cache_files' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'You do not have permission to do that.', 'lc-tweaks' ), 403 );
	}

	$dir = dlck_inline_assets_get_cache_dir();

	// Ensure the cache directory exists.
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}

	$files = glob( $dir . 'dlck-inline-*.*' );

	$deleted_any = false;
	$deleted     = 0;
	if ( is_array( $files ) ) {
		foreach ( $files as $path ) {
			if ( file_exists( $path ) ) {
				@unlink( $path );
				$deleted_any = true;
				$deleted++;
			}
			$base = basename( $path );
			delete_option( 'dlck_' . md5( $base ) . '_hash' );
		}
	}

	// Rebuild caches.
	$rebuilt = dlck_rebuild_all_inline_caches();

	// If front caches failed to write but we have content, force output once to create files.
	$front_css  = $dir . 'dlck-inline-front.css';
	$front_js   = $dir . 'dlck-inline-front.js';
	$front_head_js = $dir . 'dlck-inline-front_head.js';

	if ( ( ! file_exists( $front_css ) && ! file_exists( $front_js ) ) && ( $rebuilt['front']['css'] !== '' || $rebuilt['front']['js'] !== '' ) ) {
		ob_start();
		dlck_inline_assets_output( 'front' );
		ob_end_clean();
	}
	if ( ! file_exists( $front_head_js ) && ( $rebuilt['front_head']['js'] !== '' || $rebuilt['front_head']['css'] !== '' ) ) {
		ob_start();
		dlck_inline_assets_output( 'front_head' );
		ob_end_clean();
	}

	if ( $deleted_any ) {
		wp_send_json_success( sprintf( __( 'Cache files cleared (%d).', 'lc-tweaks' ), $deleted ) );
	}

	wp_send_json_success( __( 'No cache files to clear.', 'lc-tweaks' ) );
}
add_action( 'wp_ajax_dlck_clear_cache_files', 'dlck_clear_cache_files_ajax' );
