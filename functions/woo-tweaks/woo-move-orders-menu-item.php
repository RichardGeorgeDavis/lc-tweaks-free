<?php
/**
 * @package Move Orders Menu Item @ WordPress Dashboard
 * @version 1.0
 */

add_action( 'admin_menu', 'dlck_move_orders_menu_item', 9999 );

/**
 * Move the Orders submenu to a top-level menu item.
 */
function dlck_move_orders_menu_item() {
	global $menu, $submenu;

	if ( ! isset( $submenu['woocommerce'] ) ) {
		return;
	}

	$orders = null;
	foreach ( $submenu['woocommerce'] as $key => $item ) {
		if ( isset( $item[2] ) && in_array( $item[2], array( 'edit.php?post_type=shop_order', 'wc-orders' ), true ) ) {
			$orders = $item;
			unset( $submenu['woocommerce'][ $key ] );
			break;
		}
	}

	if ( ! $orders ) {
		return;
	}

	add_menu_page(
		$orders[0],
		$orders[0],
		$orders[1],
		$orders[2],
		'',
		'dashicons-cart',
		55.5
	);
}

?>
