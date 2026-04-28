<?php
/**
 * @package WooCommerce Disable Reviews Tab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'woocommerce_product_tabs', 'dlck_woo_remove_reviews_tab', 98 );
function dlck_woo_remove_reviews_tab( $tabs ) {
	unset( $tabs['reviews'] );
	return $tabs;
}
