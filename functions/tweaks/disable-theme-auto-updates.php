<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Stop automatic theme updates when enabled.
add_filter( 'auto_update_theme', '__return_false' );
