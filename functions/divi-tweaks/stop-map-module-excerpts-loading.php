<?php
/**
 * @package Stop Map Module Excerpts Loading
 * @version 1.0
 */

add_action( 'loop_start', 'dlck_stop_map_module_excerpts_loading_start' );
add_action( 'loop_end', 'dlck_stop_map_module_excerpts_loading_end', 100 );

/**
 * Suppress Google Maps script loading while rendering excerpt loops.
 *
 * @param WP_Query $query Query instance.
 */
function dlck_stop_map_module_excerpts_loading_start( $query ) {
	if ( dlck_map_modules_in_excerpts( $query ) ) {
		add_filter( 'et_pb_enqueue_google_maps_script', '__return_false' );
	}
}

/**
 * Re-enable Google Maps script loading after the excerpt loop.
 *
 * @param WP_Query $query Query instance.
 */
function dlck_stop_map_module_excerpts_loading_end( $query ) {
	if ( dlck_map_modules_in_excerpts( $query ) ) {
		remove_filter( 'et_pb_enqueue_google_maps_script', '__return_false' );
	}
}

/**
 * Determine whether we are inside a main excerpt loop for Divi blog modules.
 *
 * @param WP_Query $query Query instance.
 * @return bool
 */
function dlck_map_modules_in_excerpts( $query ): bool {
	if ( is_admin() ) {
		return false;
	}

	if ( function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) {
		return false;
	}

	if ( is_singular() ) {
		return false;
	}

	if ( ! ( $query instanceof WP_Query ) || ! $query->is_main_query() ) {
		return false;
	}

	if ( function_exists( 'et_get_option' ) && et_get_option( 'divi_blog_style', 'false' ) === 'on' ) {
		return false;
	}

	return true;
}

