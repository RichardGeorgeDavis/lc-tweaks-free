<?php
/**
 * @package Remove WooCommerce CSS and JS from unnecessary pages - Remove WooCommerce assets on non-WooCommerce pages
 * @version 1.4
 */

// Dequeue WooCommerce styles and scripts on non-Woo pages
add_action('wp_enqueue_scripts', 'dlck_disable_woocommerce_loading_css_js', 99);

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

function dlck_disable_woocommerce_loading_css_js() {

    // Check if WooCommerce is active
    if ( ! class_exists('WooCommerce') ) return;

    // Do not interfere with admin, AJAX, or REST requests.
    if ( is_admin() || wp_doing_ajax() ) return;
    if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) return;
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) return;

    // Only proceed if NOT on shop, cart, checkout or account pages
    if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {

        // Skip if the page embeds Woo content via shortcode/block.
        if ( function_exists( 'dlck_page_has_woocommerce_content' ) && dlck_page_has_woocommerce_content() ) {
            return;
        }

        // WooCommerce Styles
        // wp_dequeue_style('woocommerce-layout');
        // wp_dequeue_style('woocommerce-general');
        // wp_dequeue_style('woocommerce-smallscreen');
        wp_dequeue_style('wc-blocks-style');
        wp_deregister_style('wc-blocks-style');
        wp_dequeue_style('woocommerce_frontend_styles');
        wp_dequeue_style('woocommerce_fancybox_styles');
        wp_dequeue_style('woocommerce_chosen_styles');
        wp_dequeue_style('woocommerce_prettyPhoto_css');

        // WooCommerce Scripts
        wp_dequeue_script('wc-cart-fragments');
        wp_dequeue_script('woocommerce');
        wp_dequeue_script('wc-add-to-cart');
        wp_dequeue_script('wc_price_slider');
        wp_dequeue_script('wc-checkout');
        wp_dequeue_script('wc-add-to-cart-variation');
        wp_dequeue_script('wc-single-product');
        wp_dequeue_script('wc-cart');
        wp_dequeue_script('wc-chosen');
        wp_dequeue_script('prettyPhoto');
        wp_dequeue_script('prettyPhoto-init');
        wp_dequeue_script('jquery-blockui');
        wp_dequeue_script('jquery-placeholder');
        wp_dequeue_script('fancybox');
        wp_dequeue_script('vc_woocommerce-add-to-cart-js');
        wp_dequeue_script('jqueryui');

        // Also deregister js-cookie if it's not used
        wp_deregister_script('js-cookie');
        wp_dequeue_script('js-cookie');
    }
}
?>
