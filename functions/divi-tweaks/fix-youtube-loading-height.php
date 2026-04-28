<?php
/**
* @package Fix Youtube Loading Height in Divi Module
 * @version 1.1
*/

add_action('wp_footer', 'LC_Kit_fix_youtube_loading_height');
add_action('dlck_collect_inline_assets_front', 'LC_Kit_fix_youtube_loading_height');
function LC_Kit_fix_youtube_loading_height()
{
    $collecting = dlck_is_inline_collecting();
    if ( ! $collecting && ( is_admin() || wp_doing_ajax() ) ) {
        return;
    }
	$js = <<<'JS'
jQuery(function($){
    var $iframes = $('.et_pb_video_box iframe');
    if (!$iframes.length) {
        return;
    }
    $iframes.each(function(){
        var $iframe = $(this);
        if ($iframe.parent('.fluid-width-video-wrapper').length) {
            return;
        }
        $iframe.wrap('<div class="fluid-width-video-wrapper" style="padding-top: 56.2963%;"></div>');
    });
});
JS;
	dlck_add_inline_js( $js );
}
?>
