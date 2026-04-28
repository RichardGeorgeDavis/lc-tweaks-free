<?php
/**
 * @package Resave all products
 * @version 1.1
 */

add_action( 'admin_init', 'dlck_woo_resave_all_products' );
add_action( 'admin_post_dlck_woo_resave_all_products', 'dlck_woo_resave_all_products_start' );

/**
 * Update a single LC Kit option stored in dlck_lc_kit.
 *
 * @param string $key Option key.
 * @param string $value Option value.
 */
function dlck_lc_kit_set_option( string $key, string $value ): void {
	$existing = maybe_unserialize( get_option( 'dlck_lc_kit' ) );
	$settings = is_array( $existing ) ? $existing : array();
	$settings[ $key ] = $value;
	update_option( 'dlck_lc_kit', maybe_serialize( $settings ) );
}

/**
 * Start the resave run via manual action.
 */
function dlck_woo_resave_all_products_start(): void {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'divi-lc-kit' ) );
	}
	check_admin_referer( 'dlck_woo_resave_all_products' );

	dlck_lc_kit_set_option( 'dlck_woo_resave_all_products', '1' );

	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=lc_tweaks&tab=woo-tweaks' ) );
	exit;
}

function dlck_woo_resave_all_products() {
	if ( ! function_exists( 'dlck_get_option' ) || dlck_get_option( 'dlck_woo_resave_all_products' ) !== '1' ) {
		return;
	}

	// Limit to admins to avoid running on front-end traffic.
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	// Avoid running concurrently.
	if ( get_transient( 'dlck_woo_resave_lock' ) ) {
		return;
	}
	set_transient( 'dlck_woo_resave_lock', 1, 30 );

	$limit       = 50;
	$product_ids = get_posts(
		array(
			'post_type'      => array( 'product', 'product_variation' ),
			'numberposts'    => $limit,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_sync_updated',
					'compare' => 'NOT EXISTS',
				),
			),
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'no_found_rows'  => true,
			'suppress_filters' => true,
		)
	);

	if ( empty( $product_ids ) ) {
		// Nothing left to process; turn off the toggle to stop future queries.
		dlck_lc_kit_set_option( 'dlck_woo_resave_all_products', '' );
		delete_transient( 'dlck_woo_resave_lock' );
		return;
	}

	foreach ( $product_ids as $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			continue;
		}
		$product->update_meta_data( '_sync_updated', true );
		$product->save();
	}

	delete_transient( 'dlck_woo_resave_lock' );
}
