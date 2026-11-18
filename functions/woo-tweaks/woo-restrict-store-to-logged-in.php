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

		$guest_visible_product_ids = apply_filters( 'dlck_woo_restrict_store_guest_visible_product_ids', array() );
		$guest_visible_product_ids = is_array( $guest_visible_product_ids ) ? array_map( 'absint', $guest_visible_product_ids ) : array();

		if (
			( function_exists( 'is_shop' ) && is_shop() )
			|| (
				function_exists( 'is_product' )
				&& is_product()
				&& ! in_array( absint( get_queried_object_id() ), $guest_visible_product_ids, true )
			)
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
