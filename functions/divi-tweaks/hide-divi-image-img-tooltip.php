<?php
/**
 * @package Hide The Divi Image Title Tooltip That Appears On Hover
 * @version 1.5
 */
add_action('wp_footer', 'dlck_hide_divi_image_tooltip');
add_action('dlck_collect_inline_assets_front', 'dlck_hide_divi_image_tooltip');
function dlck_hide_divi_image_tooltip()
{
    $collecting = dlck_is_inline_collecting();
    if ( ! $collecting && ( is_admin() || wp_doing_ajax() ) ) {
        return;
    }
	$js = <<<JS
jQuery(document).ready(function($) {
    // Disable title tooltip without altering alt/caption content.
    $("img").mouseenter(function() {
        let $pac_da_title = $(this).attr("title");
        if (typeof $pac_da_title !== 'undefined') {
            $(this).attr("pac_da_title", $pac_da_title);
            $(this).attr("title", "");
        }
    }).mouseleave(function() {
        let $pac_da_title = $(this).attr("pac_da_title");
        if (typeof $pac_da_title !== 'undefined') {
            $(this).attr("title", $pac_da_title);
            $(this).removeAttr("pac_da_title");
        }
    });
});
JS;
	dlck_add_inline_js( $js );
}
