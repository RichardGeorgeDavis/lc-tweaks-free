<?php
/**
 * @package Resave all products
 * @version 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DLCK_WOO_RESAVE_ACTION = 'dlck_woo_resave_all_products_batch';
const DLCK_WOO_RESAVE_GROUP  = 'lc-tweaks';

add_action( 'admin_post_dlck_woo_resave_all_products', 'dlck_woo_resave_all_products_start' );
add_action( DLCK_WOO_RESAVE_ACTION, 'dlck_woo_resave_all_products_batch' );
add_action( 'init', 'dlck_woo_resave_all_products_resume', 20 );

/**
 * Update a single LC Tweaks option stored in dlck_lc_kit.
 */
function dlck_lc_kit_set_option( string $key, string $value ): void {
	$existing         = maybe_unserialize( get_option( 'dlck_lc_kit' ) );
	$settings         = is_array( $existing ) ? $existing : array();
	$settings[ $key ] = $value;
	update_option( 'dlck_lc_kit', maybe_serialize( $settings ) );
}

/**
 * Whether a product resave batch is already queued.
 */
function dlck_woo_resave_all_products_is_scheduled(): bool {
	if ( function_exists( 'as_next_scheduled_action' ) ) {
		return as_next_scheduled_action( DLCK_WOO_RESAVE_ACTION, array(), DLCK_WOO_RESAVE_GROUP ) !== false;
	}

	return wp_next_scheduled( DLCK_WOO_RESAVE_ACTION ) !== false;
}

/**
 * Queue the next product resave batch outside the interactive admin request.
 *
 * @param int  $delay       Delay before the next batch.
 * @param bool $after_batch Whether this is being scheduled by the running batch.
 */
function dlck_woo_resave_all_products_schedule( int $delay = 0, bool $after_batch = false ): void {
	if ( ! $after_batch && dlck_woo_resave_all_products_is_scheduled() ) {
		return;
	}

	if ( function_exists( 'as_schedule_single_action' ) ) {
		// A unique action cannot be added while this same hook is in progress.
		as_schedule_single_action( time() + max( 0, $delay ), DLCK_WOO_RESAVE_ACTION, array(), DLCK_WOO_RESAVE_GROUP, ! $after_batch );
		return;
	}

	wp_schedule_single_event( time() + max( 1, $delay ), DLCK_WOO_RESAVE_ACTION );
}

/**
 * Start the background resave run via manual action.
 */
function dlck_woo_resave_all_products_start(): void {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'lc-tweaks' ) );
	}
	check_admin_referer( 'dlck_woo_resave_all_products' );

	dlck_lc_kit_set_option( 'dlck_woo_resave_all_products', '1' );
	update_option(
		'dlck_woo_resave_progress',
		array(
			'status'    => 'queued',
			'processed' => 0,
			'started'   => time(),
			'updated'   => time(),
		),
		false
	);
	dlck_woo_resave_all_products_schedule();

	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=lc_tweaks&tab=woo-tweaks' ) );
	exit;
}

/**
 * Recover a queued run after plugin updates or scheduler interruptions.
 */
function dlck_woo_resave_all_products_resume(): void {
	if ( ! function_exists( 'dlck_get_option' ) || (string) dlck_get_option( 'dlck_woo_resave_all_products' ) !== '1' ) {
		return;
	}

	dlck_woo_resave_all_products_schedule( 5 );
}

/**
 * Process one background batch.
 */
function dlck_woo_resave_all_products_batch(): void {
	if ( ! function_exists( 'dlck_get_option' ) || (string) dlck_get_option( 'dlck_woo_resave_all_products' ) !== '1' ) {
		return;
	}

	$progress = get_option( 'dlck_woo_resave_progress', array() );
	$progress = is_array( $progress ) ? $progress : array();
	$progress = wp_parse_args(
		$progress,
		array(
			'status'    => 'running',
			'processed' => 0,
			'started'   => time(),
			'updated'   => time(),
		)
	);

	$product_ids = get_posts(
		array(
			'post_type'        => array( 'product', 'product_variation' ),
			'numberposts'      => 50,
			'post_status'      => 'publish',
			'fields'           => 'ids',
			'meta_query'       => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_sync_updated',
					'compare' => 'NOT EXISTS',
				),
			),
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'no_found_rows'    => true,
			'suppress_filters' => true,
		)
	);

	if ( empty( $product_ids ) ) {
		dlck_lc_kit_set_option( 'dlck_woo_resave_all_products', '' );
		$progress['status']    = 'completed';
		$progress['completed'] = time();
		$progress['updated']   = time();
		update_option( 'dlck_woo_resave_progress', $progress, false );
		return;
	}

	$processed = 0;
	foreach ( $product_ids as $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			update_post_meta( $product_id, '_sync_updated', true );
			continue;
		}

		$product->update_meta_data( '_sync_updated', true );
		$product->save();
		$processed++;
	}

	$progress['status']    = 'running';
	$progress['processed'] = (int) $progress['processed'] + $processed;
	$progress['updated']   = time();
	update_option( 'dlck_woo_resave_progress', $progress, false );

	dlck_woo_resave_all_products_schedule( 5, true );
}
