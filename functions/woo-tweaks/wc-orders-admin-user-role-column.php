<?php
// Add a custom column to the WooCommerce Orders list table.
if ( ! function_exists( 'dlck_add_user_role_orders_list_column' ) ) {
	add_filter( 'manage_edit-shop_order_columns', 'dlck_add_user_role_orders_list_column', 20 );
	function dlck_add_user_role_orders_list_column( $columns ) {
		$new_columns = array();

		foreach ( $columns as $key => $column ) {
			$new_columns[ $key ] = $column;
			if ( 'order_status' === $key ) {
				// Insert our new column after the 'status' column.
				$new_columns['user_role'] = __( 'User Role', 'woocommerce' );
			}
		}

		return $new_columns;
	}
}

// Populate the custom column with user role data.
if ( ! function_exists( 'dlck_populate_user_role_orders_list_column' ) ) {
	add_action( 'manage_shop_order_posts_custom_column', 'dlck_populate_user_role_orders_list_column' );
	function dlck_populate_user_role_orders_list_column( $column ) {
		if ( 'user_role' !== $column ) {
			return;
		}

		$order_id = get_the_ID();
		if ( ! $order_id ) {
			return;
		}

		$order   = wc_get_order( $order_id );
		$user_id = $order ? $order->get_user_id() : 0;

		if ( $user_id ) {
			$user  = new WP_User( $user_id );
			$roles = $user->roles;
			echo esc_html( implode( ', ', $roles ) );
		} else {
			echo esc_html__( 'Guest', 'woocommerce' );
		}
	}
}

// Keep column non-sortable to avoid misleading ordering against roles.
