<?php
/**
 * @package Divi Disable Plugin Check
 * @version 1.1
 */

add_action('plugins_loaded', 'jb_setup_et_builder_get_warnings');

function jb_setup_et_builder_get_warnings(){

    if ( function_exists( 'et_builder_get_warnings' ) ) {
        return;
    }

    function et_builder_get_warnings() {
        // Override the Divi builder warnings with none.
        return false;
    }
}
