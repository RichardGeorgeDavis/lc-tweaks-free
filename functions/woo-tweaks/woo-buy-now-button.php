<?php
/**
 * @package Add Buy Now Button @ Woo Single Product Page
 * @version 1.0
 */

add_action( 'woocommerce_after_add_to_cart_button', 'dlck_buy_now_button', 1 );
add_action( 'dlck_collect_inline_assets_front', 'dlck_collect_buy_now_assets' );

/**
 * Output Buy Now button after the add-to-cart button.
 */
function dlck_buy_now_button() {
	if ( ! is_product() ) {
		return;
	}

	global $product;
	if ( ! $product ) {
		return;
	}

	$product_id  = $product->get_id();
	$base_url    = '/checkout-link/';
	$buy_now_url = esc_url(
		add_query_arg(
			array(
				'products' => $product_id . ':' . 1,
			),
			$base_url
		)
	);

	echo ' &mdash; OR &mdash; <a href="' . $buy_now_url . '" class="single_add_to_cart_button button buy_now_button" data-product-id="' . esc_attr( $product_id ) . '" data-base-url="' . esc_url( $base_url ) . '">Buy Now</a>';
}

/**
 * Add cached JS to keep the Buy Now URL synced with quantity/variation.
 */
function dlck_collect_buy_now_assets() {
	dlck_add_inline_js(
		"jQuery(function($){\n" .
		"var button=$('a.buy_now_button');\n" .
		"if(!button.length){return;}\n" .
		"function updateBuyNowURL(){\n" .
		"var qty=$('form.cart').find('input.qty').val()||1;\n" .
		"var productId=button.data('product-id');\n" .
		"var variationId=$('form.cart').find('input[name=\"variation_id\"]').val();\n" .
		"if(variationId&&variationId!=='0'){productId=variationId;}\n" .
		"var baseUrl=button.data('base-url')||'/checkout-link/';\n" .
		"var newUrl=baseUrl+'?products='+productId+':'+qty;\n" .
		"button.attr('href',newUrl);\n" .
		"}\n" .
		"$(document).on('change input','form.cart input.qty',updateBuyNowURL);\n" .
		"$('form.cart').on('show_variation hide_variation',updateBuyNowURL);\n" .
		"updateBuyNowURL();\n" .
		"});"
	);
}

?>
