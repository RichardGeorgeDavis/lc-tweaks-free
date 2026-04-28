<?php
/**
 * Woo cart fragments/script policy control.
 */

if ( ! function_exists( 'dlck_woo_cart_script_policy_get_value' ) ) {
	/**
	 * Read and normalize cart script policy setting.
	 */
	function dlck_woo_cart_script_policy_get_value(): string {
		$policy = sanitize_key( (string) dlck_get_option( 'dlck_woo_cart_script_policy' ) );
		$allowed = array(
			'default',
			'disable_non_woo',
			'disable_everywhere',
			'disable_non_woo_plus_add_to_cart',
		);

		if ( ! in_array( $policy, $allowed, true ) ) {
			return 'default';
		}

		return $policy;
	}
}

if ( ! function_exists( 'dlck_woo_cart_script_policy_is_runtime_context' ) ) {
	/**
	 * Determine whether script policy should run in this request.
	 */
	function dlck_woo_cart_script_policy_is_runtime_context(): bool {
		if ( is_admin() || wp_doing_ajax() ) {
			return false;
		}

		if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
			return false;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}

		return true;
	}
}

if ( ! function_exists( 'dlck_woo_cart_script_policy_is_woo_context' ) ) {
	/**
	 * Detect whether current request is a Woo storefront context.
	 */
	function dlck_woo_cart_script_policy_is_woo_context(): bool {
		if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
			return true;
		}

		if ( function_exists( 'is_cart' ) && is_cart() ) {
			return true;
		}

		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			return true;
		}

		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			return true;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['wc-ajax'] ) ) {
			return true;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( function_exists( 'dlck_page_has_woocommerce_content' ) && dlck_page_has_woocommerce_content() ) {
			return true;
		}

		global $post;
		if ( ! ( $post instanceof WP_Post ) ) {
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
			'add_to_cart',
			'add_to_cart_url',
		);

		foreach ( $shortcodes as $shortcode ) {
			if ( has_shortcode( (string) $post->post_content, $shortcode ) ) {
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

if ( ! function_exists( 'dlck_woo_cart_script_policy_should_disable_handle' ) ) {
	/**
	 * Decide whether a script handle should be disabled under current policy.
	 */
	function dlck_woo_cart_script_policy_should_disable_handle( string $handle ): bool {
		$policy = dlck_woo_cart_script_policy_get_value();
		if ( $policy === 'default' ) {
			return false;
		}

		if ( function_exists( 'dlck_scope_rules_allow_option' ) && ! dlck_scope_rules_allow_option( 'dlck_woo_cart_script_policy' ) ) {
			return false;
		}

		if ( $policy === 'disable_everywhere' ) {
			return $handle === 'wc-cart-fragments';
		}

		if ( dlck_woo_cart_script_policy_is_woo_context() ) {
			return false;
		}

		if ( $policy === 'disable_non_woo' ) {
			return $handle === 'wc-cart-fragments';
		}

		if ( $policy === 'disable_non_woo_plus_add_to_cart' ) {
			return in_array( $handle, array( 'wc-cart-fragments', 'wc-add-to-cart' ), true );
		}

		return false;
	}
}

/**
 * Dequeue selected Woo cart scripts by policy.
 */
add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( ! dlck_woo_cart_script_policy_is_runtime_context() ) {
			return;
		}

		$handles = array( 'wc-cart-fragments', 'wc-add-to-cart' );
		foreach ( $handles as $handle ) {
			if ( ! dlck_woo_cart_script_policy_should_disable_handle( $handle ) ) {
				continue;
			}

			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
		}
	},
	9999
);

/**
 * Remove script data for disabled Woo script handles.
 */
add_filter(
	'woocommerce_get_script_data',
	static function ( $script_data, $handle ) {
		if ( ! is_string( $handle ) ) {
			return $script_data;
		}

		if ( dlck_woo_cart_script_policy_should_disable_handle( $handle ) ) {
			return null;
		}

		return $script_data;
	},
	10,
	2
);

