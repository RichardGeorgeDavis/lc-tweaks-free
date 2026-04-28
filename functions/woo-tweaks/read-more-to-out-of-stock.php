<?php
/**
* @package Read more > Out of stock
 * @version 1.1
 */


add_filter( 'woocommerce_product_add_to_cart_text', 'bbloomer_archive_custom_cart_button_text', 10, 2 );
function bbloomer_archive_custom_cart_button_text( $text, $product = null ) {
	if ( ! $product instanceof WC_Product ) {
		return $text;
	}

	if ( ! $product->is_in_stock() ) {
		return __( 'Out of stock', 'woocommerce' );
	}

	return $text;
}

?>
