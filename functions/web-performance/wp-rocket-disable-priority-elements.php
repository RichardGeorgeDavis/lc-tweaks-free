<?php
/**
 * Disable WP Rocket Priority Elements optimizations.
 */

defined( 'ABSPATH' ) || exit;

if ( defined( 'WP_ROCKET_VERSION' ) ) {
	add_filter( 'rocket_above_the_fold_optimization', '__return_false', 999 );
	add_filter( 'rocket_lrc_optimization', '__return_false', 999 );
	add_filter( 'rocket_preconnect_external_domains_optimization', '__return_false', 999 );
}
