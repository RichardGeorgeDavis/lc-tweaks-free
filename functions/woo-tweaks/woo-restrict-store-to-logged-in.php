<?php
/**
 * Redirect guests away from WooCommerce storefront pages.
 */

add_action(
	'template_redirect',
	static function () {
		if ( is_user_logged_in() || is_admin() || ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
			return;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		if (
			( function_exists( 'is_shop' ) && is_shop() )
			|| ( function_exists( 'is_product' ) && is_product() )
			|| ( function_exists( 'is_product_category' ) && is_product_category() )
			|| ( function_exists( 'is_product_tag' ) && is_product_tag() )
			|| ( function_exists( 'is_cart' ) && is_cart() )
			|| ( function_exists( 'is_checkout' ) && is_checkout() )
			|| ( function_exists( 'is_account_page' ) && is_account_page() )
		) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}
	},
	9
);
