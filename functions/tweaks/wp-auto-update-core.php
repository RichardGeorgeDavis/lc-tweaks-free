<?php
/**
 * Disable core auto updates via filters (no wp-config constant needed).
 */
add_filter( 'auto_update_core', '__return_false' );
add_filter( 'allow_major_auto_core_updates', '__return_false' );
add_filter( 'allow_minor_auto_core_updates', '__return_false' );
