<?php
/**
 * @package Add Footer Date Shortcode
 * @version 1.0
 * &#169;[footer_date_]
 */

    function divi_lc_kit_footer_date( $atts ){
        return gmdate('Y');
    }
    if ( !shortcode_exists( 'footer_date_' ) ) add_shortcode( 'footer_date_', 'divi_lc_kit_footer_date' );
