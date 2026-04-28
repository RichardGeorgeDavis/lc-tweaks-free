<?php
/**
 * @package Automatically Send Woo Customer Pending Email
 * @version 1.0
 */

add_action( 'woocommerce_new_order', 'dlck_send_pending_order_email', 9999, 2 );

/**
 * Automatically send the customer invoice email for pending orders.
 *
 * @param int      $order_id Order ID.
 * @param WC_Order $order Order object.
 */
function dlck_send_pending_order_email( $order_id, $order ) {
	if ( ! $order instanceof WC_Order ) {
		$order = wc_get_order( $order_id );
	}

	if ( ! $order ) {
		return;
	}

	if ( $order->get_status() !== 'pending' || ! $order->needs_payment() ) {
		return;
	}

	WC()->payment_gateways();
	WC()->shipping();
	WC()->mailer()->customer_invoice( $order );

	$order->add_order_note( __( 'Payment request automatically sent to customer.', 'divi-lc-kit' ), false, true );
}

?>
