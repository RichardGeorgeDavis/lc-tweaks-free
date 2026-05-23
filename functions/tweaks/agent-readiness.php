<?php
/**
 * AI agent readiness helpers for public WordPress content.
 *
 * @package LC Tweaks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'template_redirect', 'dlck_agent_readiness_maybe_render_markdown', 0 );
add_action( 'template_redirect', 'dlck_agent_readiness_send_discovery_headers', 11 );
add_action( 'do_robotstxt', 'dlck_agent_readiness_send_robots_headers', 0 );
add_filter( 'robots_txt', 'dlck_agent_readiness_filter_robots_txt', 20, 2 );
add_action( 'wp_head', 'dlck_agent_readiness_render_markdown_alternate_link', 2 );
add_filter( 'rank_math/llms_txt/extra_content', 'dlck_agent_readiness_enrich_llms_extra_content', 30 );

/**
 * Read a boolean agent-readiness option.
 */
function dlck_agent_readiness_enabled( string $option_name, string $default = '1' ): bool {
	if ( ! function_exists( 'dlck_get_option' ) ) {
		return $default === '1';
	}

	return (string) dlck_get_option( $option_name, $default ) === '1';
}

/**
 * Whether WooCommerce store surfaces should be skipped by agent-readable output.
 */
function dlck_agent_readiness_exclude_woocommerce(): bool {
	return dlck_agent_readiness_enabled( 'dlck_agent_readiness_exclude_woo', '0' );
}

/**
 * Whether included WooCommerce content should receive product/catalog Markdown.
 */
function dlck_agent_readiness_woocommerce_markdown_enabled(): bool {
	return dlck_agent_readiness_enabled( 'dlck_agent_readiness_woo_markdown', '1' );
}

/**
 * Return true when a post is a WooCommerce product or configured WooCommerce page.
 *
 * @param int|WP_Post $post_or_id Post object or ID.
 */
function dlck_agent_readiness_is_woocommerce_post( $post_or_id ): bool {
	$post = get_post( $post_or_id );
	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	if ( in_array( get_post_type( $post ), array( 'product', 'product_variation' ), true ) ) {
		return true;
	}

	if ( function_exists( 'wc_get_page_id' ) ) {
		$page_ids = array();
		foreach ( array( 'shop', 'cart', 'checkout', 'myaccount', 'terms' ) as $page ) {
			$page_id = wc_get_page_id( $page );
			if ( $page_id > 0 ) {
				$page_ids[] = (int) $page_id;
			}
		}

		return in_array( (int) $post->ID, $page_ids, true );
	}

	return false;
}

/**
 * Return true when a post is the configured WooCommerce shop page.
 *
 * @param int|WP_Post $post_or_id Post object or ID.
 */
function dlck_agent_readiness_is_woocommerce_shop_page( $post_or_id ): bool {
	$post = get_post( $post_or_id );
	if ( ! $post instanceof WP_Post || ! function_exists( 'wc_get_page_id' ) ) {
		return false;
	}

	$shop_id = wc_get_page_id( 'shop' );

	return $shop_id > 0 && (int) $post->ID === (int) $shop_id;
}

/**
 * Return the configured WooCommerce shop page ID.
 */
function dlck_agent_readiness_get_woocommerce_shop_page_id(): int {
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return 0;
	}

	$shop_id = wc_get_page_id( 'shop' );

	return $shop_id > 0 ? (int) $shop_id : 0;
}

/**
 * Return true when the current query is the WooCommerce shop/catalog page.
 */
function dlck_agent_readiness_is_woocommerce_shop_query(): bool {
	return function_exists( 'is_shop' ) && is_shop();
}

/**
 * Return true when this post should be excluded from agent-readable output.
 *
 * @param int|WP_Post $post_or_id Post object or ID.
 */
function dlck_agent_readiness_should_exclude_post( $post_or_id ): bool {
	return dlck_agent_readiness_exclude_woocommerce() && dlck_agent_readiness_is_woocommerce_post( $post_or_id );
}

/**
 * Return true when the current queried page is excluded from agent discovery.
 */
function dlck_agent_readiness_current_request_is_excluded(): bool {
	if ( ! dlck_agent_readiness_exclude_woocommerce() ) {
		return false;
	}

	if ( is_front_page() && get_option( 'show_on_front' ) === 'page' ) {
		$page_id = absint( get_option( 'page_on_front' ) );
		return $page_id > 0 && dlck_agent_readiness_is_woocommerce_post( $page_id );
	}

	if ( dlck_agent_readiness_is_woocommerce_shop_query() ) {
		$shop_id = dlck_agent_readiness_get_woocommerce_shop_page_id();
		return $shop_id > 0;
	}

	if ( is_singular() ) {
		$post = get_queried_object();
		return $post instanceof WP_Post && dlck_agent_readiness_is_woocommerce_post( $post );
	}

	return false;
}

/**
 * Return a normalized Content Signals value.
 */
function dlck_agent_readiness_get_signal_value( string $option_name, string $default ): string {
	$value = function_exists( 'dlck_get_option' ) ? sanitize_key( (string) dlck_get_option( $option_name, $default ) ) : $default;

	return in_array( $value, array( 'yes', 'no', 'unset' ), true ) ? $value : $default;
}

/**
 * Build the configured Content-Signal field value.
 */
function dlck_agent_readiness_get_content_signal(): string {
	$signals = array(
		'search'   => dlck_agent_readiness_get_signal_value( 'dlck_agent_readiness_signal_search', 'yes' ),
		'ai-input' => dlck_agent_readiness_get_signal_value( 'dlck_agent_readiness_signal_ai_input', 'yes' ),
		'ai-train' => dlck_agent_readiness_get_signal_value( 'dlck_agent_readiness_signal_ai_train', 'no' ),
	);

	$parts = array();
	foreach ( $signals as $signal => $value ) {
		if ( $value === 'unset' ) {
			continue;
		}
		$parts[] = $signal . '=' . $value;
	}

	return implode( ', ', $parts );
}

/**
 * Whether this request can safely receive public agent-readable output.
 */
function dlck_agent_readiness_is_public_request(): bool {
	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
	if ( ! in_array( $method, array( 'GET', 'HEAD' ), true ) ) {
		return false;
	}

	if ( is_admin() || wp_doing_ajax() || is_feed() || is_trackback() || is_search() ) {
		return false;
	}

	if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
		return false;
	}

	if ( function_exists( 'is_robots' ) && is_robots() ) {
		return false;
	}

	return true;
}

/**
 * Determine whether the current request prefers Markdown.
 */
function dlck_agent_readiness_request_accepts_markdown(): bool {
	$accept = isset( $_SERVER['HTTP_ACCEPT'] ) ? strtolower( (string) wp_unslash( $_SERVER['HTTP_ACCEPT'] ) ) : '';

	return $accept !== '' && str_contains( $accept, 'text/markdown' );
}

/**
 * Render Markdown when the request uses Accept negotiation or /index.md.
 */
function dlck_agent_readiness_maybe_render_markdown(): void {
	if ( ! dlck_agent_readiness_is_public_request() ) {
		return;
	}

	$is_index_md = dlck_agent_readiness_is_index_md_request();
	if ( $is_index_md ) {
		if ( ! dlck_agent_readiness_enabled( 'dlck_agent_readiness_index_md', '1' ) ) {
			return;
		}
		$document = dlck_agent_readiness_get_index_md_document();
	} else {
		if ( ! dlck_agent_readiness_enabled( 'dlck_agent_readiness_markdown_accept', '1' ) || ! dlck_agent_readiness_request_accepts_markdown() ) {
			return;
		}
		$document = dlck_agent_readiness_get_current_markdown_document();
	}

	if ( empty( $document['markdown'] ) || ! is_string( $document['markdown'] ) ) {
		return;
	}

	dlck_agent_readiness_send_markdown_response( $document['markdown'] );
}

/**
 * Return true for requests ending in /index.md under this WordPress home path.
 */
function dlck_agent_readiness_is_index_md_request(): bool {
	return null !== dlck_agent_readiness_get_index_md_target_path();
}

/**
 * Resolve the requested /index.md URL to the equivalent public URL path.
 */
function dlck_agent_readiness_get_index_md_target_path(): ?string {
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return null;
	}

	$request_uri  = (string) wp_unslash( $_SERVER['REQUEST_URI'] );
	$request_path = wp_parse_url( $request_uri, PHP_URL_PATH );
	if ( ! is_string( $request_path ) || $request_path === '' ) {
		return null;
	}

	$home_path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$home_path = is_string( $home_path ) ? untrailingslashit( $home_path ) : '';
	if ( $home_path !== '' && $home_path !== '/' && str_starts_with( $request_path, $home_path . '/' ) ) {
		$request_path = substr( $request_path, strlen( $home_path ) );
	}

	if ( $request_path === '/index.md' ) {
		return '/';
	}

	if ( ! str_ends_with( $request_path, '/index.md' ) ) {
		return null;
	}

	$target_path = substr( $request_path, 0, -strlen( 'index.md' ) );
	$target_path = '/' . ltrim( $target_path, '/' );

	return $target_path === '' ? '/' : $target_path;
}

/**
 * Build the Markdown document for a /index.md request.
 */
function dlck_agent_readiness_get_index_md_document(): ?array {
	$target_path = dlck_agent_readiness_get_index_md_target_path();
	if ( null === $target_path ) {
		return null;
	}

	if ( $target_path === '/' ) {
		return dlck_agent_readiness_get_home_markdown_document();
	}

	$post_id = url_to_postid( home_url( $target_path ) );
	if ( ! $post_id ) {
		return null;
	}

	if ( dlck_agent_readiness_should_exclude_post( $post_id ) ) {
		return null;
	}

	return dlck_agent_readiness_get_post_markdown_document( $post_id );
}

/**
 * Build the Markdown document for the currently queried public URL.
 */
function dlck_agent_readiness_get_current_markdown_document(): ?array {
	if ( is_front_page() ) {
		return dlck_agent_readiness_get_home_markdown_document();
	}

	if ( dlck_agent_readiness_is_woocommerce_shop_query() ) {
		$shop_id = dlck_agent_readiness_get_woocommerce_shop_page_id();
		if ( $shop_id > 0 ) {
			return dlck_agent_readiness_get_post_markdown_document( $shop_id );
		}
	}

	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			return dlck_agent_readiness_get_post_markdown_document( $post );
		}
	}

	return null;
}

/**
 * Render post content through normal WordPress filters with that post in context.
 */
function dlck_agent_readiness_apply_post_content_filters( WP_Post $post, string $content ): string {
	$had_previous_post = array_key_exists( 'post', $GLOBALS );
	$previous_post     = $had_previous_post ? $GLOBALS['post'] : null;
	$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata( $post );
	$html = apply_filters( 'the_content', $content );
	wp_reset_postdata();
	if ( $had_previous_post ) {
		$GLOBALS['post'] = $previous_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	} else {
		unset( $GLOBALS['post'] );
	}

	return is_string( $html ) ? $html : '';
}

/**
 * Build Markdown for the public front page.
 */
function dlck_agent_readiness_get_home_markdown_document(): ?array {
	$page_id = ( get_option( 'show_on_front' ) === 'page' ) ? absint( get_option( 'page_on_front' ) ) : 0;
	if ( $page_id ) {
		if ( dlck_agent_readiness_should_exclude_post( $page_id ) ) {
			return null;
		}
		return dlck_agent_readiness_get_post_markdown_document( $page_id, home_url( '/' ) );
	}

	$title       = wp_strip_all_tags( get_bloginfo( 'name' ) );
	$description = wp_strip_all_tags( get_bloginfo( 'description' ) );
	$posts       = get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 10,
			'ignore_sticky_posts' => true,
			'suppress_filters'    => false,
		)
	);

	$body = '';
	if ( $description !== '' ) {
		$body .= $description . "\n\n";
	}

	if ( ! empty( $posts ) ) {
		$body .= "## Latest Posts\n\n";
		foreach ( $posts as $post ) {
			if ( ! $post instanceof WP_Post || $post->post_password !== '' ) {
				continue;
			}

			$permalink = get_permalink( $post );
			$post_title = wp_strip_all_tags( get_the_title( $post ) );
			if ( $post_title === '' || ! $permalink ) {
				continue;
			}

			$body .= '- [' . dlck_agent_readiness_escape_markdown_inline( $post_title ) . '](' . esc_url_raw( $permalink ) . ')' . "\n";
		}
	}

	$markdown = dlck_agent_readiness_render_markdown_document(
		array(
			'title'       => $title,
			'url'         => home_url( '/' ),
			'description' => $description,
		),
		$title,
		$body
	);

	return array( 'markdown' => $markdown );
}

/**
 * Build Markdown for a public singular post.
 *
 * @param int|WP_Post $post_or_id Post object or ID.
 * @param string      $canonical_url Optional canonical URL override.
 */
function dlck_agent_readiness_get_post_markdown_document( $post_or_id, string $canonical_url = '' ): ?array {
	$post = get_post( $post_or_id );
	if ( ! $post instanceof WP_Post ) {
		return null;
	}

	if ( dlck_agent_readiness_should_exclude_post( $post ) ) {
		return null;
	}

	if ( get_post_status( $post ) !== 'publish' || ! is_post_type_viewable( get_post_type( $post ) ) || $post->post_password !== '' ) {
		return null;
	}

	if ( dlck_agent_readiness_woocommerce_markdown_enabled() ) {
		if ( get_post_type( $post ) === 'product' ) {
			return dlck_agent_readiness_get_product_markdown_document( $post, $canonical_url );
		}

		if ( dlck_agent_readiness_is_woocommerce_shop_page( $post ) ) {
			return dlck_agent_readiness_get_shop_markdown_document( $post, $canonical_url );
		}
	}

	$title = wp_strip_all_tags( get_the_title( $post ) );
	if ( $title === '' ) {
		$title = get_bloginfo( 'name' );
	}

	$permalink = $canonical_url !== '' ? $canonical_url : get_permalink( $post );
	if ( ! $permalink ) {
		$permalink = home_url( '/' );
	}

	$excerpt = has_excerpt( $post ) ? wp_strip_all_tags( get_the_excerpt( $post ) ) : '';
	$body    = dlck_agent_readiness_html_to_markdown( dlck_agent_readiness_apply_post_content_filters( $post, $post->post_content ) );
	if ( $body === '' && $excerpt !== '' ) {
		$body = $excerpt;
	}

	$markdown = dlck_agent_readiness_render_markdown_document(
		array(
			'title'        => $title,
			'url'          => $permalink,
			'description'  => $excerpt,
			'dateModified' => get_post_modified_time( 'c', true, $post ),
		),
		$title,
		$body
	);

	return array( 'markdown' => $markdown );
}

/**
 * Build Markdown for a WooCommerce product page.
 */
function dlck_agent_readiness_get_product_markdown_document( WP_Post $post, string $canonical_url = '' ): ?array {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return null;
	}

	$product = wc_get_product( $post );
	if ( ! $product ) {
		return null;
	}

	$title     = wp_strip_all_tags( $product->get_name() );
	$permalink = $canonical_url !== '' ? $canonical_url : get_permalink( $post );
	if ( ! $permalink ) {
		$permalink = home_url( '/' );
	}

	$had_previous_post = array_key_exists( 'post', $GLOBALS );
	$previous_post     = $had_previous_post ? $GLOBALS['post'] : null;
	$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata( $post );
	$had_previous_product = array_key_exists( 'product', $GLOBALS );
	$previous_product     = $had_previous_product ? $GLOBALS['product'] : null;
	$GLOBALS['product'] = $product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$summary_html = apply_filters( 'woocommerce_short_description', $product->get_short_description() );
	$summary_html = is_string( $summary_html ) ? $summary_html : '';
	$summary      = dlck_agent_readiness_html_to_markdown( $summary_html );
	$summary_text = trim( wp_strip_all_tags( $summary_html ) );
	wp_reset_postdata();
	if ( $had_previous_post ) {
		$GLOBALS['post'] = $previous_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	} else {
		unset( $GLOBALS['post'] );
	}

	$details = array(
		'Product Type' => ucfirst( str_replace( '-', ' ', (string) $product->get_type() ) ),
		'SKU'          => $product->get_sku(),
		'Price'        => wp_strip_all_tags( html_entity_decode( (string) $product->get_price_html(), ENT_QUOTES, 'UTF-8' ) ),
		'Availability' => dlck_agent_readiness_get_product_availability_text( $product ),
		'Categories'   => function_exists( 'wc_get_product_category_list' ) ? wp_strip_all_tags( wc_get_product_category_list( $product->get_id(), ', ' ) ) : '',
	);

	$body = '';
	if ( $summary !== '' ) {
		$body .= $summary . "\n\n";
	}

	$body .= "## Product Details\n\n";
	foreach ( $details as $label => $value ) {
		$value = trim( wp_strip_all_tags( (string) $value ) );
		if ( $value === '' ) {
			continue;
		}
		$body .= '- **' . $label . ':** ' . dlck_agent_readiness_escape_markdown_inline( $value ) . "\n";
	}

	$body .= '- **Product URL:** ' . esc_url_raw( $permalink ) . "\n";

	$description = dlck_agent_readiness_html_to_markdown( dlck_agent_readiness_apply_post_content_filters( $post, $product->get_description() ) );
	if ( $description !== '' ) {
		$body .= "\n## Description\n\n" . $description . "\n";
	}
	if ( $had_previous_product ) {
		$GLOBALS['product'] = $previous_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	} else {
		unset( $GLOBALS['product'] );
	}

	$markdown = dlck_agent_readiness_render_markdown_document(
		array(
			'title'        => $title,
			'url'          => $permalink,
			'description'  => $summary_text,
			'type'         => 'product',
			'sku'          => $product->get_sku(),
			'availability' => dlck_agent_readiness_get_product_availability_text( $product ),
			'dateModified' => get_post_modified_time( 'c', true, $post ),
		),
		$title,
		$body
	);

	return array( 'markdown' => $markdown );
}

/**
 * Build Markdown for the WooCommerce shop/catalog page.
 */
function dlck_agent_readiness_get_shop_markdown_document( WP_Post $post, string $canonical_url = '' ): ?array {
	$title = wp_strip_all_tags( get_the_title( $post ) );
	if ( $title === '' ) {
		$title = __( 'Shop', 'lc-tweaks' );
	}

	$permalink = $canonical_url !== '' ? $canonical_url : get_permalink( $post );
	if ( ! $permalink ) {
		$permalink = home_url( '/' );
	}

	$body = dlck_agent_readiness_html_to_markdown( dlck_agent_readiness_apply_post_content_filters( $post, $post->post_content ) );
	$product_query_args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => 24,
		'ignore_sticky_posts' => true,
		'orderby'             => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
		'suppress_filters'    => false,
	);

	if ( function_exists( 'wc_get_product_visibility_term_ids' ) ) {
		$product_visibility_terms = wc_get_product_visibility_term_ids();
		$excluded_visibility_ids  = array();
		if ( ! empty( $product_visibility_terms['exclude-from-catalog'] ) ) {
			$excluded_visibility_ids[] = (int) $product_visibility_terms['exclude-from-catalog'];
		}
		if ( get_option( 'woocommerce_hide_out_of_stock_items' ) === 'yes' && ! empty( $product_visibility_terms['outofstock'] ) ) {
			$excluded_visibility_ids[] = (int) $product_visibility_terms['outofstock'];
		}

		if ( ! empty( $excluded_visibility_ids ) ) {
			$product_query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => array_values( array_unique( $excluded_visibility_ids ) ),
					'operator' => 'NOT IN',
				),
			);
		}
	}

	$products = get_posts( $product_query_args );

	if ( ! empty( $products ) && function_exists( 'wc_get_product' ) ) {
		$body .= "\n\n## Products\n\n";
		foreach ( $products as $product_post ) {
			if ( ! $product_post instanceof WP_Post || $product_post->post_password !== '' ) {
				continue;
			}

			$product = wc_get_product( $product_post );
			if ( ! $product ) {
				continue;
			}

			$product_url = get_permalink( $product_post );
			$product_title = wp_strip_all_tags( $product->get_name() );
			if ( $product_title === '' || ! $product_url ) {
				continue;
			}

			$meta = array_filter(
				array(
					wp_strip_all_tags( html_entity_decode( (string) $product->get_price_html(), ENT_QUOTES, 'UTF-8' ) ),
					dlck_agent_readiness_get_product_availability_text( $product ),
				)
			);
			$suffix = empty( $meta ) ? '' : ' - ' . dlck_agent_readiness_escape_markdown_inline( implode( ' - ', $meta ) );
			$body  .= '- [' . dlck_agent_readiness_escape_markdown_inline( $product_title ) . '](' . esc_url_raw( $product_url ) . ')' . $suffix . "\n";
		}
	}

	$markdown = dlck_agent_readiness_render_markdown_document(
		array(
			'title'       => $title,
			'url'         => $permalink,
			'description' => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'type'        => 'shop',
		),
		$title,
		$body
	);

	return array( 'markdown' => $markdown );
}

/**
 * Return compact public product availability text.
 *
 * @param WC_Product $product Product object.
 */
function dlck_agent_readiness_get_product_availability_text( $product ): string {
	if ( ! is_object( $product ) || ! method_exists( $product, 'get_availability' ) ) {
		return '';
	}

	$availability = $product->get_availability();
	if ( is_array( $availability ) && ! empty( $availability['availability'] ) ) {
		return wp_strip_all_tags( (string) $availability['availability'] );
	}

	return method_exists( $product, 'is_in_stock' ) && $product->is_in_stock() ? __( 'In stock', 'lc-tweaks' ) : __( 'Out of stock', 'lc-tweaks' );
}

/**
 * Render front matter, heading, and body into one Markdown document.
 */
function dlck_agent_readiness_render_markdown_document( array $metadata, string $title, string $body ): string {
	$output = array();
	$front  = dlck_agent_readiness_render_front_matter( $metadata );
	if ( $front !== '' ) {
		$output[] = $front;
	}

	$title = trim( wp_strip_all_tags( $title ) );
	if ( $title !== '' ) {
		$output[] = '# ' . dlck_agent_readiness_escape_markdown_inline( $title );
	}

	$body = dlck_agent_readiness_normalize_markdown( $body );
	if ( $body !== '' ) {
		$output[] = $body;
	}

	return dlck_agent_readiness_normalize_markdown( implode( "\n\n", $output ) ) . "\n";
}

/**
 * Render simple YAML front matter for agents.
 */
function dlck_agent_readiness_render_front_matter( array $metadata ): string {
	$lines = array();
	foreach ( $metadata as $key => $value ) {
		if ( ! is_scalar( $value ) ) {
			continue;
		}

		$key   = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $key );
		$value = trim( wp_strip_all_tags( (string) $value ) );
		if ( $key === '' || $value === '' ) {
			continue;
		}

		$value   = str_replace( array( "\\", '"', "\r", "\n" ), array( "\\\\", '\"', ' ', ' ' ), $value );
		$lines[] = $key . ': "' . $value . '"';
	}

	if ( empty( $lines ) ) {
		return '';
	}

	return "---\n" . implode( "\n", $lines ) . "\n---";
}

/**
 * Convert rendered public HTML to compact Markdown.
 */
function dlck_agent_readiness_html_to_markdown( string $html ): string {
	$html = trim( $html );
	if ( $html === '' ) {
		return '';
	}

	if ( ! class_exists( 'DOMDocument' ) ) {
		return dlck_agent_readiness_normalize_markdown( wp_strip_all_tags( $html ) );
	}

	$document = new DOMDocument();
	$previous = libxml_use_internal_errors( true );
	$loaded   = $document->loadHTML( '<?xml encoding="UTF-8"><div id="dlck-agent-readiness-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( ! $loaded ) {
		return dlck_agent_readiness_normalize_markdown( wp_strip_all_tags( $html ) );
	}

	foreach ( array( 'script', 'style', 'noscript', 'svg', 'iframe', 'form', 'nav', 'header', 'footer' ) as $tag ) {
		$nodes = $document->getElementsByTagName( $tag );
		for ( $i = $nodes->length - 1; $i >= 0; $i-- ) {
			$node = $nodes->item( $i );
			if ( $node && $node->parentNode ) {
				$node->parentNode->removeChild( $node );
			}
		}
	}

	$root = $document->getElementById( 'dlck-agent-readiness-root' );
	if ( ! $root ) {
		return dlck_agent_readiness_normalize_markdown( wp_strip_all_tags( $html ) );
	}

	return dlck_agent_readiness_normalize_markdown( dlck_agent_readiness_node_children_to_markdown( $root ) );
}

/**
 * Convert child nodes to Markdown.
 *
 * @param DOMNode $node Parent node.
 */
function dlck_agent_readiness_node_children_to_markdown( $node ): string {
	$output = '';
	foreach ( $node->childNodes as $child ) {
		$output .= dlck_agent_readiness_node_to_markdown( $child );
	}

	return $output;
}

/**
 * Convert one DOM node to Markdown.
 *
 * @param DOMNode $node Node to convert.
 */
function dlck_agent_readiness_node_to_markdown( $node ): string {
	if ( defined( 'XML_TEXT_NODE' ) && $node->nodeType === XML_TEXT_NODE ) {
		return preg_replace( '/\s+/u', ' ', html_entity_decode( (string) $node->textContent, ENT_QUOTES, 'UTF-8' ) );
	}

	if ( defined( 'XML_ELEMENT_NODE' ) && $node->nodeType !== XML_ELEMENT_NODE ) {
		return '';
	}

	$tag      = strtolower( (string) $node->nodeName );
	$children = dlck_agent_readiness_node_children_to_markdown( $node );

	if ( preg_match( '/^h([1-6])$/', $tag, $matches ) ) {
		return "\n\n" . str_repeat( '#', (int) $matches[1] ) . ' ' . trim( $children ) . "\n\n";
	}

	switch ( $tag ) {
		case 'p':
		case 'div':
		case 'section':
		case 'article':
		case 'main':
			return "\n\n" . trim( $children ) . "\n\n";

		case 'br':
			return "\n";

		case 'strong':
		case 'b':
			return '**' . trim( $children ) . '**';

		case 'em':
		case 'i':
			return '*' . trim( $children ) . '*';

		case 'code':
			return '`' . trim( preg_replace( '/\s+/u', ' ', (string) $node->textContent ) ) . '`';

		case 'pre':
			return "\n\n```\n" . trim( (string) $node->textContent ) . "\n```\n\n";

		case 'blockquote':
			$quote = trim( $children );
			return $quote === '' ? '' : "\n\n> " . str_replace( "\n", "\n> ", $quote ) . "\n\n";

		case 'ul':
		case 'ol':
			return dlck_agent_readiness_list_to_markdown( $node, $tag === 'ol' );

		case 'a':
			return dlck_agent_readiness_link_to_markdown( $node, $children );

		case 'img':
			return dlck_agent_readiness_image_to_markdown( $node );

		case 'table':
			return "\n\n" . trim( preg_replace( '/\s+/u', ' ', (string) $node->textContent ) ) . "\n\n";
	}

	return $children;
}

/**
 * Convert a list node to Markdown list items.
 *
 * @param DOMNode $node List node.
 */
function dlck_agent_readiness_list_to_markdown( $node, bool $ordered ): string {
	$lines = array();
	$index = 1;
	foreach ( $node->childNodes as $child ) {
		if ( strtolower( (string) $child->nodeName ) !== 'li' ) {
			continue;
		}

		$text = trim( dlck_agent_readiness_node_children_to_markdown( $child ) );
		if ( $text === '' ) {
			continue;
		}

		$prefix  = $ordered ? $index . '. ' : '- ';
		$lines[] = $prefix . str_replace( "\n", "\n  ", $text );
		$index++;
	}

	return empty( $lines ) ? '' : "\n" . implode( "\n", $lines ) . "\n";
}

/**
 * Convert an anchor node to Markdown.
 *
 * @param DOMElement $node Anchor node.
 */
function dlck_agent_readiness_link_to_markdown( $node, string $children ): string {
	$text = trim( $children );
	$href = $node->hasAttribute( 'href' ) ? dlck_agent_readiness_absolute_url( $node->getAttribute( 'href' ) ) : '';

	if ( $text === '' ) {
		$text = $href;
	}

	if ( $href === '' || $text === '' ) {
		return $text;
	}

	return '[' . dlck_agent_readiness_escape_markdown_inline( $text ) . '](' . esc_url_raw( $href ) . ')';
}

/**
 * Convert an image node to Markdown.
 *
 * @param DOMElement $node Image node.
 */
function dlck_agent_readiness_image_to_markdown( $node ): string {
	$src = $node->hasAttribute( 'src' ) ? dlck_agent_readiness_absolute_url( $node->getAttribute( 'src' ) ) : '';
	if ( $src === '' ) {
		return '';
	}

	$alt = $node->hasAttribute( 'alt' ) ? trim( wp_strip_all_tags( $node->getAttribute( 'alt' ) ) ) : '';

	return '![' . dlck_agent_readiness_escape_markdown_inline( $alt ) . '](' . esc_url_raw( $src ) . ')';
}

/**
 * Resolve site-relative URLs to absolute URLs for agent output.
 */
function dlck_agent_readiness_absolute_url( string $url ): string {
	$url = trim( html_entity_decode( $url, ENT_QUOTES, 'UTF-8' ) );
	if ( $url === '' || str_starts_with( $url, '#' ) ) {
		return '';
	}

	if ( preg_match( '/^(https?:|mailto:|tel:)/i', $url ) ) {
		return $url;
	}

	if ( str_starts_with( $url, '//' ) ) {
		return is_ssl() ? 'https:' . $url : 'http:' . $url;
	}

	if ( str_starts_with( $url, '/' ) ) {
		return home_url( $url );
	}

	return '';
}

/**
 * Escape Markdown control characters inside inline text.
 */
function dlck_agent_readiness_escape_markdown_inline( string $text ): string {
	return str_replace( array( '[', ']' ), array( '\[', '\]' ), $text );
}

/**
 * Normalize noisy Markdown whitespace.
 */
function dlck_agent_readiness_normalize_markdown( string $markdown ): string {
	$markdown = str_replace( "\xc2\xa0", ' ', $markdown );
	$markdown = preg_replace( "/[ \t]+\n/", "\n", $markdown );
	$markdown = preg_replace( "/\n{3,}/", "\n\n", $markdown );
	$markdown = preg_replace( "/[ \t]{2,}/", ' ', $markdown );

	return trim( (string) $markdown );
}

/**
 * Send a Markdown response and stop normal theme rendering.
 */
function dlck_agent_readiness_send_markdown_response( string $markdown ): void {
	if ( ! headers_sent() ) {
		status_header( 200 );
			header( 'Content-Type: text/markdown; charset=UTF-8' );
		header( 'Vary: Accept', false );
		header( 'X-LC-Tweaks-Markdown: 1' );
		nocache_headers();

		$content_signal = dlck_agent_readiness_get_content_signal();
		if ( $content_signal !== '' ) {
			header( 'Content-Signal: ' . $content_signal );
		}
	}

	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
	if ( $method !== 'HEAD' ) {
		echo $markdown; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	exit;
}

/**
 * Keep virtual robots.txt Content Signals from being cached as stale policy.
 */
function dlck_agent_readiness_send_robots_headers(): void {
	if ( headers_sent() || ! dlck_agent_readiness_enabled( 'dlck_agent_readiness_robots_signals', '1' ) ) {
		return;
	}

	nocache_headers();
}

/**
 * Add Content Signals to WordPress virtual robots.txt output.
 *
 * @param string $output Existing robots.txt output.
 * @param bool   $public Whether the site discourages search engines.
 */
function dlck_agent_readiness_filter_robots_txt( string $output, bool $public ): string {
	if ( ! dlck_agent_readiness_enabled( 'dlck_agent_readiness_robots_signals', '1' ) ) {
		return $output;
	}

	if ( stripos( $output, 'Content-Signal:' ) !== false || stripos( $output, 'Content-signal:' ) !== false ) {
		return $output;
	}

	$content_signal = dlck_agent_readiness_get_content_signal();
	if ( $content_signal === '' ) {
		return $output;
	}

	$block = "# LC Tweaks AI Agent Readiness Content Signals\nUser-agent: *\nContent-Signal: " . $content_signal . "\n";
	if ( $public ) {
		$block .= "Allow: /\n";
	}

	return $block . "\n" . ltrim( $output );
}

/**
 * Send honest discovery Link headers for resources this site really exposes.
 */
function dlck_agent_readiness_send_discovery_headers(): void {
	if ( headers_sent() || ! dlck_agent_readiness_enabled( 'dlck_agent_readiness_discovery_headers', '1' ) || ! dlck_agent_readiness_is_public_request() ) {
		return;
	}

	if ( ! ( is_front_page() || is_singular() || dlck_agent_readiness_is_woocommerce_shop_query() ) ) {
		return;
	}

	if ( dlck_agent_readiness_current_request_is_excluded() ) {
		return;
	}

	$markdown_url = dlck_agent_readiness_get_current_markdown_url();
	$link_header  = function_exists( 'dlck_agent_readiness_get_discovery_link_header_value' ) ? dlck_agent_readiness_get_discovery_link_header_value( $markdown_url ) : '';
	if ( $link_header === '' ) {
		return;
	}

	header( 'Link: ' . $link_header, false );
}

/**
 * Add an HTML alternate link to the page head for the Markdown fallback URL.
 */
function dlck_agent_readiness_render_markdown_alternate_link(): void {
	if ( ! dlck_agent_readiness_enabled( 'dlck_agent_readiness_discovery_headers', '1' ) || ! dlck_agent_readiness_is_public_request() ) {
		return;
	}

	if ( dlck_agent_readiness_current_request_is_excluded() ) {
		return;
	}

	$markdown_url = dlck_agent_readiness_get_current_markdown_url();
	if ( $markdown_url === '' ) {
		return;
	}

	echo '<link rel="alternate" type="text/markdown" href="' . esc_url( $markdown_url ) . "\" />\n";
}

/**
 * Return the Markdown fallback URL for the current public page.
 */
function dlck_agent_readiness_get_current_markdown_url(): string {
	if ( ! dlck_agent_readiness_enabled( 'dlck_agent_readiness_index_md', '1' ) ) {
		return '';
	}

	if ( dlck_agent_readiness_current_request_is_excluded() ) {
		return '';
	}

	if ( is_front_page() ) {
		return home_url( '/index.md' );
	}

	if ( dlck_agent_readiness_is_woocommerce_shop_query() ) {
		$shop_id = dlck_agent_readiness_get_woocommerce_shop_page_id();
		if ( $shop_id <= 0 ) {
			return '';
		}

		$shop_url = get_permalink( $shop_id );

		return $shop_url ? trailingslashit( $shop_url ) . 'index.md' : '';
	}

	if ( ! is_singular() || get_option( 'permalink_structure' ) === '' ) {
		return '';
	}

	$post = get_queried_object();
	if ( ! $post instanceof WP_Post || get_post_status( $post ) !== 'publish' || ! is_post_type_viewable( get_post_type( $post ) ) || $post->post_password !== '' ) {
		return '';
	}

	if ( dlck_agent_readiness_should_exclude_post( $post ) ) {
		return '';
	}

	$permalink = get_permalink( $post );
	if ( ! $permalink ) {
		return '';
	}

	return trailingslashit( $permalink ) . 'index.md';
}

/**
 * Return the most likely public sitemap URL.
 */
function dlck_agent_readiness_get_sitemap_url(): string {
	$rank_math_active  = class_exists( 'RankMath' ) || function_exists( 'rank_math' );
	$rank_math_modules = get_option( 'rank_math_modules', array() );
	if ( $rank_math_active && is_array( $rank_math_modules ) && in_array( 'sitemap', $rank_math_modules, true ) ) {
		return home_url( '/sitemap_index.xml' );
	}

	if ( function_exists( 'wp_sitemaps_get_server' ) && get_option( 'blog_public' ) ) {
		return home_url( '/wp-sitemap.xml' );
	}

	return '';
}

/**
 * Return Rank Math's llms.txt URL when the module is available.
 */
function dlck_agent_readiness_get_llms_url(): string {
	$rank_math_active  = class_exists( 'RankMath' ) || function_exists( 'rank_math' );
	$rank_math_modules = get_option( 'rank_math_modules', array() );
	if ( $rank_math_active && is_array( $rank_math_modules ) && in_array( 'llms-txt', $rank_math_modules, true ) ) {
		return home_url( '/llms.txt' );
	}

	return '';
}

/**
 * Add a concise content-use and discovery note to Rank Math llms.txt output.
 *
 * @param mixed $extra Existing extra llms.txt content.
 */
function dlck_agent_readiness_enrich_llms_extra_content( $extra ): string {
	$existing = is_scalar( $extra ) ? trim( (string) $extra ) : '';
	if ( ! dlck_agent_readiness_enabled( 'dlck_agent_readiness_llms_enrichment', '1' ) ) {
		return $existing;
	}

	if ( preg_match( '/^##\s+AI Agent Readiness\s*$/mi', $existing ) ) {
		return $existing;
	}

	$lines = array(
		'## AI Agent Readiness',
		'- Content Signals: ' . ( dlck_agent_readiness_get_content_signal() ?: 'none configured' ),
	);

	if ( dlck_agent_readiness_enabled( 'dlck_agent_readiness_markdown_accept', '1' ) ) {
		$lines[] = '- Markdown negotiation: send `Accept: text/markdown` for public pages.';
	}

	if ( dlck_agent_readiness_enabled( 'dlck_agent_readiness_index_md', '1' ) ) {
		$lines[] = '- Markdown fallback: use `/index.md` for the homepage and `/page-slug/index.md` for public singular pages.';
	}

	$section = implode( "\n", $lines );

	return $existing === '' ? $section : $existing . "\n\n" . $section;
}
