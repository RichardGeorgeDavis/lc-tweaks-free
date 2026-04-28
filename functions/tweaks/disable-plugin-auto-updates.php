<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Stop automatic plugin updates when enabled.
add_filter( 'auto_update_plugin', '__return_false' );
