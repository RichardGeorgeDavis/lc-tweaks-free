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
	$js = <<<'JS'
jQuery(document).ready(function($) {
    // Disable title tooltip without altering alt/caption content.
    $("img").on("mouseenter", function() {
        var $img       = $(this),
            titleValue = $img.attr("title");
        if (titleValue !== undefined) {
            $img.attr("data-pac-da-title", titleValue);
            $img.removeAttr("title");
        }
    });
    $("img").on("mouseleave", function() {
        var $img       = $(this),
            titleValue = $img.attr("data-pac-da-title");
        if (titleValue !== undefined) {
            $img.attr("title", titleValue);
            $img.removeAttr("data-pac-da-title");
        }
    });
});
JS;
	dlck_add_inline_js( $js );
}
