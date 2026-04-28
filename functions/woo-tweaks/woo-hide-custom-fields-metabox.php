<?php
/**
 * @package WooCommerce Hide Custom Fields Meta Box
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'add_meta_boxes',
	static function () {
		if ( function_exists( 'wc_get_product' ) ) {
			remove_meta_box( 'postcustom', 'product', 'normal' );
		}
	},
	99
);
