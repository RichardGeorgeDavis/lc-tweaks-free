<?php
/**
 * @package Fix Email Product Name Symbols @ WooCommerce Emails
 * @version 1.0
 */

add_filter( 'woocommerce_email_order_item_name', 'dlck_fix_email_product_name_symbols', 10, 2 );

/**
 * Replace problematic symbols in WooCommerce email item names with email-friendly superscript entities.
 *
 * @param string        $product_name Email order item name HTML.
 * @param WC_Order_Item $item         Order item object.
 * @return string
 */
function dlck_fix_email_product_name_symbols( $product_name, $item ) {
	if ( $product_name === '' ) {
		return $product_name;
	}

	$sup_tm = '<sup style="font-size:60%; line-height:0; vertical-align:super;">&trade;</sup>';
	$sup_r  = '<sup style="font-size:60%; line-height:0; vertical-align:super;">&reg;</sup>';
	$sup_c  = '<sup style="font-size:60%; line-height:0; vertical-align:super;">&copy;</sup>';

	return strtr(
		$product_name,
		array(
			'&trade;' => $sup_tm,
			'&reg;'   => $sup_r,
			'&copy;'  => $sup_c,
			'™'       => $sup_tm,
			'®'       => $sup_r,
			'©'       => $sup_c,
		)
	);
}

?>
