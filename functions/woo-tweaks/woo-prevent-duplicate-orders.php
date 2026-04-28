<?php
/**
 * @package WooCommerce Prevent Duplicate Order
 * @version 1.0
 */

add_action( 'woocommerce_checkout_process', 'dlck_prevent_duplicate_orders' );

/**
 * Prevent duplicate paid orders placed within a short time window.
 */
function dlck_prevent_duplicate_orders() {
	if ( empty( $_POST['billing_email'] ) || ! WC()->cart ) {
		return;
	}

	$email = sanitize_email( wp_unslash( $_POST['billing_email'] ) );
	if ( $email === '' ) {
		return;
	}

	$args = array(
		'limit'        => 1,
		'customer'     => $email,
		'date_created' => '>' . ( time() - 2 * MINUTE_IN_SECONDS ),
		'status'       => wc_get_is_paid_statuses(),
		'total'        => WC()->cart->get_total( 'edit' ),
		'return'       => 'ids',
	);

	$orders = wc_get_orders( $args );
	if ( $orders ) {
		wc_add_notice( __( 'It looks like you already placed this order recently. Please wait a minute before trying again.' ), 'error' );
	}
}

?>
