<?php
/**
 * @package Full-width Divi footer
 * @version 1.2
 */

add_action('wp_footer', 'dlck_full_width_divi_footer');
add_action('dlck_collect_inline_assets_front', 'dlck_full_width_divi_footer');
function dlck_full_width_divi_footer()
{
    $collecting = dlck_is_inline_collecting();
    if ( ! $collecting && ! function_exists( 'et_setup_theme' ) ) {
        return;
    }
	$css = '#footer-bottom .container{margin-right:2.773%;margin-left:2.773%;width:94.454%!important;max-width:94.454%}';
	dlck_add_inline_css( $css );
}

?>
