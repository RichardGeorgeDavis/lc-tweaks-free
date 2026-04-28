<?php
/**
 * @package Remove Payments Menu @ WordPress Dashboard
 * @version 1.0
 */

add_action( 'admin_menu', 'dlck_remove_payments_from_wp_sidebar_menu', 9999 );

/**
 * Remove WooCommerce Payments-related menu entries.
 */
function dlck_remove_payments_from_wp_sidebar_menu() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	remove_menu_page( 'admin.php?page=wc-settings&tab=checkout' );
	remove_menu_page( 'admin.php?page=wc-admin&path=/wc-pay-welcome-page' );
	remove_menu_page( 'admin.php?page=wc-admin&task=payments' );
	remove_menu_page( 'admin.php?page=wc-admin&task=woocommerce-payments' );
	remove_menu_page( 'admin.php?page=wc-settings&tab=checkout&from=PAYMENTS_MENU_ITEM' );
}

?>
