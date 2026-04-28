<?php
/**
 * @package Custom Redirect for Logouts @ WooCommerce My Account
 * @version 1.0
 */

add_filter( 'woocommerce_logout_default_redirect_url', 'dlck_redirect_after_woocommerce_logout' );

/**
 * Redirect customers to the homepage after logout.
 *
 * @return string
 */
function dlck_redirect_after_woocommerce_logout() {
	return home_url( '/' );
}

?>
