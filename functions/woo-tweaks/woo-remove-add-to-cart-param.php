<?php
/**
 * @package Remove add-to-cart URL Parameter @ WooCommerce
 * @version 1.0
 */

add_filter( 'woocommerce_add_to_cart_redirect', 'dlck_remove_add_to_cart_param_redirect', 10, 1 );

/**
 * Strip add-to-cart param after a successful add to cart.
 *
 * @param string $url Redirect URL.
 * @return string
 */
function dlck_remove_add_to_cart_param_redirect( $url ) {
	if ( wp_doing_ajax() ) {
		return $url;
	}

	return remove_query_arg( 'add-to-cart', $url );
}

?>
