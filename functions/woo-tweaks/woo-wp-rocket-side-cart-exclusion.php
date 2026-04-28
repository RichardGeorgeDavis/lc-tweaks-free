<?php
/**
 * Exclude Side Cart For WooCommerce markup from WP Rocket LazyRender.
 */

if ( defined( 'WP_ROCKET_VERSION' ) ) {
	add_filter(
		'rocket_lrc_exclusions',
		static function ( $exclusions ) {
			$exclusions[] = 'class="xoo-wsc-markup"';
			return $exclusions;
		}
	);
}
