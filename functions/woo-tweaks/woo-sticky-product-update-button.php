<?php
/**
 * @package Sticky Update Product Button @ WP Admin
 * @version 1.0
 */

add_action( 'dlck_collect_inline_assets_admin', 'dlck_collect_sticky_product_update_button_styles' );

/**
 * Add admin CSS to keep the product Update button visible.
 */
function dlck_collect_sticky_product_update_button_styles() {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'product' ) {
		return;
	}

	dlck_add_inline_css(
		'#publish{position:fixed;top:50%;right:10px;z-index:1000;box-shadow:0 2px 5px #000;width:150px;}',
		'admin'
	);
}

?>
