<?php
/**
 * @package Sort Products Alphabetically @ WooCommerce Order
 * @version 1.0
 */

add_filter( 'woocommerce_order_get_items', 'dlck_sort_order_items', 9999, 2 );

/**
 * Sort order items based on selected mode.
 *
 * @param WC_Order_Item[] $items Order items.
 * @param WC_Order        $order Order object.
 * @return WC_Order_Item[]
 */
function dlck_sort_order_items( $items, $order ) {
	$mode = dlck_get_option( 'dlck_woo_order_items_sort_option' );
	if ( ! $mode ) {
		$mode = 'name_az';
	}

	switch ( $mode ) {
		case 'quantity_desc':
			uasort(
				$items,
				static function ( $a, $b ) {
					return (int) $b->get_quantity() <=> (int) $a->get_quantity();
				}
			);
			break;
		case 'total_desc':
			uasort(
				$items,
				static function ( $a, $b ) {
					return (float) $b->get_total() <=> (float) $a->get_total();
				}
			);
			break;
		case 'sku_az':
			uasort(
				$items,
				static function ( $a, $b ) {
					$product_a = $a->get_product();
					$product_b = $b->get_product();
					$sku_a     = $product_a ? $product_a->get_sku() : '';
					$sku_b     = $product_b ? $product_b->get_sku() : '';
					return strcasecmp( $sku_a, $sku_b );
				}
			);
			break;
		case 'category_az':
			uasort(
				$items,
				static function ( $a, $b ) {
					$product_a = $a->get_product();
					$product_b = $b->get_product();
					$cat_a     = $product_a ? wp_get_post_terms( $product_a->get_id(), 'product_cat', array( 'fields' => 'names' ) ) : array();
					$cat_b     = $product_b ? wp_get_post_terms( $product_b->get_id(), 'product_cat', array( 'fields' => 'names' ) ) : array();
					$cat_a_name = $cat_a ? $cat_a[0] : '';
					$cat_b_name = $cat_b ? $cat_b[0] : '';
					return strcasecmp( $cat_a_name, $cat_b_name );
				}
			);
			break;
		case 'product_id_asc':
			uasort(
				$items,
				static function ( $a, $b ) {
					return (int) $a->get_product_id() <=> (int) $b->get_product_id();
				}
			);
			break;
		case 'name_az':
		default:
			uasort(
				$items,
				static function ( $a, $b ) {
					return strcasecmp( $a->get_name(), $b->get_name() );
				}
			);
			break;
	}

	return $items;
}

?>
