<?php
/**
 * @package WooCommerce Disable Brands Feature
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'dlck_disable_woocommerce_brands_feature' );
function dlck_disable_woocommerce_brands_feature() {
	if ( get_option( 'wc_feature_woocommerce_brands_enabled' ) !== 'no' ) {
		update_option( 'wc_feature_woocommerce_brands_enabled', 'no' );
	}
}

add_action( 'wp_enqueue_scripts', 'dlck_dequeue_woocommerce_brands_styles', 100 );
function dlck_dequeue_woocommerce_brands_styles() {
	wp_dequeue_style( 'brands-styles' );
	wp_deregister_style( 'brands-styles' );
}
