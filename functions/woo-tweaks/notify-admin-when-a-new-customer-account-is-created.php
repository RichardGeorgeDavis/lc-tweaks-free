<?php
/**
* @package Notify admin when a new customer account is created
 * @version 1.1
 */

add_action( 'woocommerce_created_customer', 'woocommerce_created_customer_admin_notification' );
function woocommerce_created_customer_admin_notification( $customer_id ) {
	if ( ! $customer_id ) {
		return;
	}

	if ( ! function_exists( 'wp_send_new_user_notifications' ) ) {
		return;
	}

	wp_send_new_user_notifications( $customer_id, 'admin' );
}

?>
