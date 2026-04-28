<?php
/**
 * @package Allow Only Simple Products @ WooCommerce
 * @version 1.0
 */

add_filter( 'product_type_selector', 'dlck_simple_products_only' );
add_action( 'dlck_collect_inline_assets_admin', 'dlck_collect_simple_products_only_styles' );

/**
 * Keep only the Simple product type in the selector.
 *
 * @param array $types Product types.
 * @return array
 */
function dlck_simple_products_only( $types ) {
	if ( ! isset( $types['simple'] ) ) {
		return $types;
	}

	return array( 'simple' => $types['simple'] );
}

/**
 * Hide the product type dropdown on product edit screens.
 */
function dlck_collect_simple_products_only_styles() {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'product' ) {
		return;
	}

	dlck_add_inline_css(
		'label[for="product-type"],#product-type{display:none!important;}',
		'admin'
	);
}

?>
