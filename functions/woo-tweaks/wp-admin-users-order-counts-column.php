<?php
// Order Counts @ WordPress Admin Users Table.
if ( ! function_exists( 'dlck_add_order_counts_column' ) ) {
	add_filter( 'manage_users_columns', 'dlck_add_order_counts_column' );
	function dlck_add_order_counts_column( $columns ) {
		$columns['order_counts'] = __( 'Order Counts', 'woocommerce' );
		return $columns;
	}
}

if ( ! function_exists( 'dlck_add_order_counts_column_content' ) ) {
	add_filter( 'manage_users_custom_column', 'dlck_add_order_counts_column_content', 10, 3 );
	function dlck_add_order_counts_column_content( $content, $column_name, $user_id ) {
		if ( 'order_counts' !== $column_name ) {
			return $content;
		}

		$order_counts = dlck_wc_get_customer_order_counts( $user_id );
		$content      = sprintf(
			/* translators: 1: completed count, 2: processing count, 3: pending payment count, 4: cancelled count */
			'Completed: %d<br>Processing: %d<br>Pending Payment: %d<br>Cancelled: %d',
			$order_counts['completed'],
			$order_counts['processing'],
			$order_counts['pending'],
			$order_counts['cancelled']
		);

		return $content;
	}
}

if ( ! function_exists( 'dlck_add_order_counts_orders_list_column' ) ) {
	add_filter( 'manage_edit-shop_order_columns', 'dlck_add_order_counts_orders_list_column', 25 );
	add_filter( 'manage_woocommerce_page_wc-orders_columns', 'dlck_add_order_counts_orders_list_column', 25 );
	function dlck_add_order_counts_orders_list_column( $columns ) {
		$new_columns = array();

		foreach ( $columns as $key => $column ) {
			$new_columns[ $key ] = $column;
			if ( 'order_status' === $key ) {
				$new_columns['order_counts'] = __( 'Order Counts', 'woocommerce' );
			}
		}

		return $new_columns;
	}
}

if ( ! function_exists( 'dlck_add_order_counts_orders_list_column_content' ) ) {
	add_action( 'manage_shop_order_posts_custom_column', 'dlck_add_order_counts_orders_list_column_content', 10, 2 );
	add_action( 'manage_woocommerce_page_wc-orders_custom_column', 'dlck_add_order_counts_orders_list_column_content', 10, 2 );
	function dlck_add_order_counts_orders_list_column_content( $column, $post_id = 0 ) {
		if ( 'order_counts' !== $column ) {
			return;
		}

		$order = null;
		if ( $post_id instanceof WC_Order ) {
			$order = $post_id;
		} elseif ( $post_id ) {
			$order = wc_get_order( $post_id );
		}

		if ( ! $order ) {
			return;
		}

		$user_id = $order->get_customer_id();
		if ( ! $user_id ) {
			echo esc_html__( 'Guest', 'woocommerce' );
			return;
		}

		$order_counts = dlck_wc_get_customer_order_counts( $user_id );
		echo esc_html(
			sprintf(
				/* translators: 1: completed count, 2: processing count, 3: pending payment count, 4: cancelled count */
				'Completed: %d | Processing: %d | Pending: %d | Cancelled: %d',
				$order_counts['completed'],
				$order_counts['processing'],
				$order_counts['pending'],
				$order_counts['cancelled']
			)
		);
	}
}

// Get the count of all orders for a customer based on different statuses.
if ( ! function_exists( 'dlck_wc_get_customer_order_counts' ) ) {
	function dlck_wc_get_customer_order_counts( $customer_id ) {
		static $dlck_order_counts_cache = array();

		if ( isset( $dlck_order_counts_cache[ $customer_id ] ) ) {
			return $dlck_order_counts_cache[ $customer_id ];
		}

		$statuses     = array( 'completed', 'processing', 'pending', 'cancelled' );
		$order_counts = array_fill_keys( $statuses, 0 );

		foreach ( $statuses as $status ) {
			$args = array(
				'customer_id' => $customer_id,
				'status'      => $status,
				'return'      => 'ids',
			);

			$orders                = wc_get_orders( $args );
			$order_counts[ $status ] = is_array( $orders ) ? count( $orders ) : 0;
		}

		$dlck_order_counts_cache[ $customer_id ] = $order_counts;
		return $order_counts;
	}
}
