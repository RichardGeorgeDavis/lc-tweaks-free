<?php
/**
 * @package Remove WooCommerce Blocks assets only
 * @version 2.1
 */

add_action( 'wp_enqueue_scripts', 'dlck_remove_woo_blocks_assets', PHP_INT_MAX );

if ( ! function_exists( 'dlck_page_has_woocommerce_content' ) ) {
	/**
	 * Heuristically detect WooCommerce content on the current page.
	 *
	 * @return bool
	 */
	function dlck_page_has_woocommerce_content() {
		if ( is_cart() || is_checkout() || is_account_page() || is_woocommerce() ) {
			return true;
		}

		if ( isset( $_GET['wc-ajax'] ) ) {
			return true;
		}

		global $post;
		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		$shortcodes = array(
			'woocommerce_cart',
			'woocommerce_checkout',
			'woocommerce_my_account',
			'products',
			'product',
			'product_page',
			'product_category',
			'product_categories',
			'recent_products',
			'featured_products',
			'sale_products',
			'top_rated_products',
			'best_selling_products',
			'add_to_cart',
			'add_to_cart_url',
			'related_products',
		);

		foreach ( $shortcodes as $shortcode ) {
			if ( has_shortcode( $post->post_content, $shortcode ) ) {
				return true;
			}
		}

		if ( function_exists( 'has_block' ) ) {
			if ( has_block( 'woocommerce', $post ) || has_block( 'woocommerce/cart', $post ) || has_block( 'woocommerce/checkout', $post ) ) {
				return true;
			}
		}

		return false;
	}
}

function dlck_remove_woo_blocks_assets() {
	// Require WooCommerce.
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Do not interfere with admin, AJAX, or REST requests.
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}
	if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
		return;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	// Skip if the page has WooCommerce content/blocks/shortcodes.
	if ( dlck_page_has_woocommerce_content() ) {
		return;
	}

	global $wp_styles, $wp_scripts;

	// Dequeue WooCommerce Blocks CSS.
	if ( isset( $wp_styles->queue ) && is_array( $wp_styles->queue ) ) {
		foreach ( $wp_styles->queue as $index => $handle ) {
			if ( strpos( $handle, 'wc-blocks-' ) === 0 ) {
				unset( $wp_styles->queue[ $index ] );
			}
		}
	}

	// Dequeue WooCommerce Blocks JS.
	if ( isset( $wp_scripts->queue ) && is_array( $wp_scripts->queue ) ) {
		foreach ( $wp_scripts->queue as $index => $handle ) {
			if ( strpos( $handle, 'wc-blocks-' ) === 0 ) {
				unset( $wp_scripts->queue[ $index ] );
			}
		}
	}
}


?>
