<?php
/**
 * Block abusive WooCommerce/cart-style query URLs with tracking parameters.
 */

add_action( 'wp_loaded', 'dlck_woo_block_bad_query_abuse', 0 );

if ( ! function_exists( 'dlck_woo_query_has_abusive_tracking_param' ) ) {
	/**
	 * Determine whether the current request has a known tracking/cache-busting query parameter.
	 */
	function dlck_woo_query_has_abusive_tracking_param(): bool {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$tracking_keys = array(
			'srsltid',
			'gclid',
			'gad_source',
			'gad_campaignid',
			'gbraid',
			'wbraid',
		);

		foreach ( $tracking_keys as $key ) {
			if ( isset( $_GET[ $key ] ) ) {
				return true;
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return false;
	}
}

if ( ! function_exists( 'dlck_woo_query_has_abusive_commerce_action' ) ) {
	/**
	 * Determine whether the current request has a WooCommerce/cart-style query action.
	 */
	function dlck_woo_query_has_abusive_commerce_action(): bool {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$action_keys = array(
			'add-to-cart',
			'add_to_wishlist',
		);

		foreach ( $action_keys as $key ) {
			if ( isset( $_GET[ $key ] ) ) {
				return true;
			}
		}

		if ( isset( $_GET['action'] ) && is_string( $_GET['action'] ) ) {
			$action = sanitize_key( wp_unslash( $_GET['action'] ) );
			if ( $action === 'yith-woocompare-add-product' ) {
				return true;
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return false;
	}
}

if ( ! function_exists( 'dlck_woo_block_bad_query_abuse' ) ) {
	/**
	 * Return a 403 before WooCommerce processes abusive query requests.
	 */
	function dlck_woo_block_bad_query_abuse(): void {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}

		if ( ! dlck_woo_query_has_abusive_tracking_param() || ! dlck_woo_query_has_abusive_commerce_action() ) {
			return;
		}

		status_header( 403 );
		nocache_headers();
		header( 'X-LC-Blocked: bad-woocommerce-query-abuse' );
		exit( 'Forbidden' );
	}
}
