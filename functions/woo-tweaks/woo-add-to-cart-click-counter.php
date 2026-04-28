<?php
/**
 * @package Add Cart Button Click Counter @ WooCommerce
 * @version 1.0
 */

add_action( 'dlck_collect_inline_assets_front_head', 'dlck_collect_add_to_cart_click_counter_assets' );
add_action( 'wp_ajax_dlck_add_cart_clicked', 'dlck_add_cart_clicked' );
add_action( 'wp_ajax_nopriv_dlck_add_cart_clicked', 'dlck_add_cart_clicked' );
add_action( 'add_meta_boxes', 'dlck_product_meta_box_add_cart_clicks' );

/**
 * Collect click tracking JS so it can be cached.
 */
function dlck_collect_add_to_cart_click_counter_assets() {
	if ( ! dlck_is_inline_collecting() && ! is_product() ) {
		return;
	}

	$data = array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'dlck_add_cart_clicked' ),
	);
	$js = 'var dlckAddCart=' . wp_json_encode( $data ) . ";\n" .
		"(function(){\n" .
		"function init(){\n" .
		"if(!window.jQuery){return;}\n" .
		"var $=window.jQuery;\n" .
		"$(document).on('click','.single_add_to_cart_button',function(){\n" .
		"var pid=$(this).attr('value');\n" .
		"if(!pid){return;}\n" .
		"$.post(dlckAddCart.ajaxUrl,{action:'dlck_add_cart_clicked',pid:pid,nonce:dlckAddCart.nonce});\n" .
		"});\n" .
		"}\n" .
		"if(document.readyState==='complete'){init();}else{window.addEventListener('load',init);}\n" .
		"})();";
	dlck_add_inline_js( $js, 'front_head' );
}

/**
 * Track add-to-cart clicks via AJAX.
 */
function dlck_add_cart_clicked() {
	check_ajax_referer( 'dlck_add_cart_clicked', 'nonce' );

	if ( empty( $_POST['pid'] ) ) {
		wp_die();
	}

	$pid = absint( $_POST['pid'] );
	if ( ! $pid ) {
		wp_die();
	}

	$times_added_to_cart = (int) get_post_meta( $pid, 'add_cart_clicks', true );
	update_post_meta( $pid, 'add_cart_clicks', $times_added_to_cart + 1 );

	wp_die();
}

/**
 * Register a product meta box for add-to-cart stats.
 */
function dlck_product_meta_box_add_cart_clicks() {
	add_meta_box(
		'dlck_add_cart_stats',
		__( 'Add to Cart Stats', 'divi-lc-kit' ),
		'dlck_display_add_cart_stats',
		'product',
		'advanced',
		'high'
	);
}

/**
 * Render the add-to-cart stats meta box.
 */
function dlck_display_add_cart_stats() {
	global $post;

	if ( ! $post ) {
		return;
	}

	$product = wc_get_product( $post->ID );
	if ( ! $product ) {
		return;
	}

	$units_sold = (int) $product->get_total_sales();
	$times_added_to_cart = (int) get_post_meta( $post->ID, 'add_cart_clicks', true );
	if ( ! $times_added_to_cart ) {
		echo '<p>' . esc_html__( 'No data available', 'divi-lc-kit' ) . '</p>';
		return;
	}

	$conversion = 100 * $units_sold / $times_added_to_cart;
	echo '<p>' . esc_html__( 'Times added to cart:', 'divi-lc-kit' ) . ' ' . esc_html( $times_added_to_cart ) . '</p>';
	echo '<p>' . esc_html__( 'Sales:', 'divi-lc-kit' ) . ' ' . esc_html( $units_sold ) . '</p>';
	echo '<p>' . esc_html__( 'Conversion rate:', 'divi-lc-kit' ) . ' ' . esc_html( number_format( $conversion, 2 ) ) . '%</p>';
}

?>
