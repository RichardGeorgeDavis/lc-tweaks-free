<?php
/**
 * @package Disable Gutenberg Editor
 * @version 2.1
 */

// Disable Gutenberg on the back end.
add_filter( 'use_block_editor_for_post', '__return_false' );

// Disable Gutenberg for widgets.
add_filter( 'use_widgets_block_editor', '__return_false' );

add_filter('use_block_editor_for_post_type', '__return_false', 10);


add_action( 'wp_enqueue_scripts', function() {
    // Skip dequeues in admin and REST/AJAX contexts to avoid breaking editors/previews.
    if ( is_admin() || wp_doing_ajax() ) {
        return;
    }
    if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
        return;
    }
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        return;
    }

    // Remove CSS on the front end.
    wp_dequeue_style( 'wp-block-library' );

    // Remove Gutenberg theme.
    wp_dequeue_style( 'wp-block-library-theme' );

    // Remove inline global CSS on the front end.
    wp_dequeue_style( 'global-styles' );

    // WooCommerce block CSS
    wp_dequeue_style( 'wc-block-style' ); 

    // Toolset block CSS
    wp_dequeue_style( 'toolset_blocks-style-css' ); 
}, 100 );
