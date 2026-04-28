<?php
/**
* @package Autoplay Videos in Divi Video Module And Hide Controls.
 * @version 1.1
*/

add_action('wp_footer', 'LC_Kit_autoplay_videos_hide_controls');
add_action('dlck_collect_inline_assets_front', 'LC_Kit_autoplay_videos_hide_controls');
function LC_Kit_autoplay_videos_hide_controls()
{
    $collecting = dlck_is_inline_collecting();
    if ( ! $collecting && ( is_admin() || wp_doing_ajax() ) ) {
        return;
    }
	$js = <<<JS
jQuery(document).ready(function() {
    var \$boxes = jQuery('.dlck-video-autoplay .et_pb_video_box');
    if (\$boxes.length === 0) {
        return;
    }
    \$boxes.find('video').prop('muted', true);
    \$boxes.find('video').attr('loop', 'loop');
    \$boxes.find('video').attr('playsInline', '');

    \$boxes.each(function() {
        var video = jQuery(this).find('video').get(0);
        if (video && typeof video.play === 'function') {
            video.play();
        }
    });
    \$boxes.find('video').removeAttr('controls');
});
JS;
	dlck_add_inline_js( $js );
}
?>
