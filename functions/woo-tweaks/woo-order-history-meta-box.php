<?php
/**
 * @package Orders History @ WooCommerce Single Order Admin Page
 * @version 1.0
 */

add_action( 'add_meta_boxes', 'dlck_add_order_history_meta_box', 1 );

/**
 * Register the customer order history meta box.
 */
function dlck_add_order_history_meta_box() {
	add_meta_box(
		'dlck_order_history',
		__( 'Customer Order History', 'divi-lc-kit' ),
		'dlck_display_order_history_meta_box',
		'shop_order',
		'normal',
		'default'
	);
}

/**
 * Render the order history meta box.
 */
function dlck_display_order_history_meta_box() {
	global $post;

	if ( ! $post ) {
		return;
	}

	$order = wc_get_order( $post->ID );
	if ( ! $order ) {
		return;
	}

	$orders = array();
	$customer_id = $order->get_customer_id();
	if ( $customer_id ) {
		$orders = wc_get_orders(
			array(
				'customer_id' => $customer_id,
				'return'      => 'ids',
				'limit'       => 10,
			)
		);
	}

	if ( empty( $orders ) ) {
		return;
	}

	echo '<table style="width:100%"><thead><tr><th>ID</th><th>DATE</th><th>ITEMS</th><th>STATUS</th></tr></thead><tbody>';
	foreach ( $orders as $order_id ) {
		$history_order = wc_get_order( $order_id );
		if ( ! $history_order ) {
			continue;
		}

		$items = array();
		foreach ( $history_order->get_items() as $item ) {
			$items[] = $item->get_name();
		}

		echo '<tr><td>' . esc_html( $order_id ) . '</td><td>' . esc_html( wc_format_datetime( $history_order->get_date_created() ) ) . '</td><td>' . esc_html( implode( ' | ', $items ) ) . '</td><td>' . esc_html( $history_order->get_status() ) . '</td></tr>';
	}
	echo '</tbody></table>';
}

?>
