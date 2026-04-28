<?php
/**
 * @package Hide Price & Add to Cart for Logged Out Users
 * @version 1.2
 */

add_action( 'wp', 'bbloomer_hide_price_add_cart_not_logged_in' );

function bbloomer_hide_price_add_cart_not_logged_in() {
	// Only alter front end for guests when WooCommerce is available.
	if ( is_admin() || is_user_logged_in() || ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
		return;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
}

?>
