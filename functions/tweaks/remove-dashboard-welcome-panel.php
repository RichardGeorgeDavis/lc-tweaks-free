<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Remove the default Welcome panel from the Dashboard.
add_action(
	'wp_dashboard_setup',
	static function () {
		remove_action( 'welcome_panel', 'wp_welcome_panel' );
	},
	100
);
