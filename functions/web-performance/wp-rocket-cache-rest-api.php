<?php
/**
 * @package WP Rocket | Cache WP Rest API
 * @version 1.1
 * https://github.com/wp-media/wp-rocket-helpers/tree/master/cache/wp-rocket-cache-rest-api
 */

namespace WP_Rocket\Helpers\cache_wp_rest_api;

defined( 'ABSPATH' ) or die();

if ( defined( 'WP_ROCKET_VERSION' ) ) {
	add_filter( 'rocket_cache_reject_wp_rest_api', '__return_false' );
}

/**
 * Updates .htaccess, regenerates WP Rocket config file.
 *
 * @author Arun Basil Lal
 */
function flush_wp_rocket() {

	if ( ! function_exists( 'flush_rocket_htaccess' )
	  || ! function_exists( 'rocket_generate_config_file' ) ) {
		return false;
	}

	// Update WP Rocket .htaccess rules.
	flush_rocket_htaccess();

	// Regenerate WP Rocket config file.
	rocket_generate_config_file();
}
if ( defined( 'WP_ROCKET_VERSION' ) ) {
	register_activation_hook( __FILE__, __NAMESPACE__ . '\flush_wp_rocket' );
}

/**
 * Removes customizations, updates .htaccess, regenerates config file.
 *
 * @author Arun Basil Lal
 */
function deactivate() {

	// Remove all functionality added above.
	if ( defined( 'WP_ROCKET_VERSION' ) ) {
		remove_filter( 'rocket_cache_reject_wp_rest_api', '__return_false' );
	}

	// Flush .htaccess rules, and regenerate WP Rocket config file.
	flush_wp_rocket();
}
if ( defined( 'WP_ROCKET_VERSION' ) ) {
	register_deactivation_hook( __FILE__, __NAMESPACE__ . '\deactivate' );
}
