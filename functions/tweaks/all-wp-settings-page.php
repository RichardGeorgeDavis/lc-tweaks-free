<?php
/**
 * @package WordPress | All Settings Page
 */
defined( 'ABSPATH' ) or die();

function dlck_all_settings_link() {
	add_options_page(
		__( 'All Settings', 'divi-lc-kit' ),
		__( 'All Settings', 'divi-lc-kit' ),
		'administrator',
		'options.php'
	);
}
add_action( 'admin_menu', 'dlck_all_settings_link' );
