<?php
/**
* @package Stop this admin menu item from displaying for anyone but an Administrator
 * @version 1.1
 */

add_action( 'admin_menu', 'dlck_remove_woo_menu_non_admins', 99 );
function dlck_remove_woo_menu_non_admins() {

	// If the current user is not an admin
	if ( ! current_user_can( 'manage_options' ) ) {

		remove_menu_page( 'woocommerce' ); // WooCommerce admin menu slug

	}
}


?>
