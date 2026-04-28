<?php
/**
 * @package WP Rocket | Disable Above The Fold Optimization
 */
defined( 'ABSPATH' ) or die();

if ( defined( 'WP_ROCKET_VERSION' ) ) {
	add_filter( 'rocket_above_the_fold_optimization', '__return_false' );
}
