<?php
/**
 * @package Hide Downloads Tab @ My Account Page
 * @version 1.0
 */

add_filter( 'woocommerce_account_menu_items', 'dlck_hide_downloads_tab_my_account', 9999 );

/**
 * Hide the Downloads tab if the customer has no downloadable products.
 *
 * @param array $items My Account menu items.
 * @return array
 */
function dlck_hide_downloads_tab_my_account( $items ) {
	$customer = WC()->customer;
	if ( ! $customer ) {
		return $items;
	}

	$downloads = $customer->get_downloadable_products();
	if ( empty( $downloads ) ) {
		unset( $items['downloads'] );
	}

	return $items;
}

?>
