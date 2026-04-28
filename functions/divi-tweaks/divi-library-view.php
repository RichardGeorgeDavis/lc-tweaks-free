<?php
/**
 * Plugin Name: Divi Library View (Updated)
 * Description: Adds featured image support and front-end views for Divi Library layouts in a safer, updated way.
 * Author: Richard / Lucidity
 * Version: 1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ensure our changes only run once Divi has registered the et_pb_layout post type.
 */
add_action( 'init', function () {
	// Run a bit later than default init so Divi has a chance to register its CPT.
	if ( ! post_type_exists( 'et_pb_layout' ) ) {
		return;
	}

	// Add featured image support to Divi Library layouts.
	add_post_type_support( 'et_pb_layout', 'thumbnail' );

	// Register a small thumbnail size for the Library list table.
	if ( function_exists( 'add_image_size' ) ) {
		add_image_size( 'ds_library_layout_featured_image', 120, 120, true );
	}
}, 20 );

/**
 * Tweak et_pb_layout post type args without re-registering it.
 * Makes layouts viewable on the front end while keeping them out of search.
 */
add_filter( 'register_post_type_args', function ( $args, $post_type ) {
	if ( 'et_pb_layout' !== $post_type ) {
		return $args;
	}

	// Keep existing args, just adjust what we care about.
	$args['publicly_queryable']  = true;
	$args['exclude_from_search'] = true;

	return $args;
}, 10, 2 );

/**
 * Add a Featured Image column to the Divi Library list table.
 */
add_filter( 'manage_et_pb_layout_posts_columns', function ( $columns ) {

	$new_columns = [];

	// Optionally shift the thumbnail column to the front.
	foreach ( $columns as $key => $label ) {
		if ( 'cb' === $key ) {
			$new_columns[ $key ] = $label;
			$new_columns['ds_featured_image'] = __( 'Thumbnail', 'divi-library-view' );
		} else {
			$new_columns[ $key ] = $label;
		}
	}

	return $new_columns;
} );

add_action( 'manage_et_pb_layout_posts_custom_column', function ( $column, $post_id ) {
	if ( 'ds_featured_image' !== $column ) {
		return;
	}

	$thumb_id = get_post_thumbnail_id( $post_id );

	if ( ! $thumb_id ) {
		echo '&mdash;';
		return;
	}

	echo wp_kses_post(
		wp_get_attachment_image(
			$thumb_id,
			'ds_library_layout_featured_image',
			false,
			[ 'style' => 'max-width:120px;height:auto;' ]
		)
	);
}, 10, 2 );

/**
 * On single Divi Library views, optionally strip Divi builder-specific body classes
 * to avoid styling collisions. This mirrors the intent of the original snippet
 * but in a more targeted way.
 */
add_action( 'template_redirect', function () {
	if ( is_admin() || ! is_singular( 'et_pb_layout' ) ) {
		return;
	}

	if ( function_exists( 'et_builder_body_classes' ) ) {
		remove_filter( 'body_class', 'et_builder_body_classes' );
	}
} );

/**
 * Flush rewrite rules on activation and deactivation so the et_pb_layout
 * URLs work consistently when this plugin is toggled.
 */
function dlv_activate() {
	// Ensure CPTs are registered before flushing.
	dlv_prime_cpt();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'dlv_activate' );

function dlv_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'dlv_deactivate' );

/**
 * Prime CPT registration before flushing (in case init hasn't fired yet).
 * Divi normally registers et_pb_layout on init, but we defensively trigger init if needed.
 */
function dlv_prime_cpt() {
	// If init hasn't fired yet, fire it once so Divi can register its post types.
	if ( ! did_action( 'init' ) ) {
		do_action( 'init' );
	}
}