<?php
/**
 * @package WP Rocket | Force Page Caching
 * @version 1.1
 * https://github.com/wp-media/wp-rocket-helpers/tree/master/cache/wp-rocket-cache-donotcachepage/
 */
defined( 'ABSPATH' ) or die();

/**
 * Override DONOTCACHEPAGE behavior for WP Rocket.
 */
if ( defined( 'WP_ROCKET_VERSION' ) ) {
	add_filter( 'rocket_override_donotcachepage', '__return_true', PHP_INT_MAX );
}
