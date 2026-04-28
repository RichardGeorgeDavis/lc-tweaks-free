<?php
/**
 * @package Get Order IDs that contain product ID @ WooCommerce
 * @version 1.0
 */

/**
 * Get paid order IDs that contain a given product ID.
 *
 * @param int $product_id WooCommerce product ID.
 * @return int[]
 */
function dlck_get_order_ids_by_product_id( $product_id ) {
	$product_id = absint( $product_id );
	if ( ! $product_id ) {
		return array();
	}

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return array();
	}

	$args = array(
		'status'        => wc_get_is_paid_statuses(),
		'limit'         => -1,
		'type'          => 'shop_order',
		'return'        => 'ids',
		's'             => $product->get_name(),
		'search_filter' => 'products',
	);

	return wc_get_orders( $args );
}

?>
