<?php
/**
 * @package Complete Order Button @ WooCommerce Order Admin
 * @version 1.0
 */

add_action( 'woocommerce_order_actions_end', 'dlck_add_complete_order_button' );

/**
 * Add a Complete Order button on the order edit screen.
 *
 * @param WC_Order $order WooCommerce order object.
 */
function dlck_add_complete_order_button( $order ) {
	if ( ! $order instanceof WC_Order ) {
		return;
	}

	if ( $order->has_status( 'completed' ) ) {
		return;
	}

	$args = array(
		'action'   => 'woocommerce_mark_order_status',
		'status'   => 'completed',
		'order_id' => $order->get_id(),
		'_wpnonce' => wp_create_nonce( 'woocommerce-mark-order-status' ),
	);

	$url = add_query_arg( $args, admin_url( 'admin-ajax.php' ) );

	echo '<li class="wide">';
	echo '<a href="' . esc_url( $url ) . '" class="button" style="display:inline-flex;align-items:center;gap:4px;"><span class="dashicons dashicons-saved"></span> Complete Order</a>';
	echo '</li>';
}

?>
