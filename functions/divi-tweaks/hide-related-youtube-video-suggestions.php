<?php
/**
* @package Hide Related YouTube Video Suggestions In Divi
 * @version 1.2
 */

if ( defined( 'DLCK_YT_RELATED_HANDLER_LOADED' ) ) {
	return;
}
define( 'DLCK_YT_RELATED_HANDLER_LOADED', true );

add_action('wp_footer', 'LC_Kit_hide_related_video_suggestions', 100);
add_action('dlck_collect_inline_assets_front', 'LC_Kit_hide_related_video_suggestions');
function LC_Kit_hide_related_video_suggestions()
{
	$collecting = dlck_is_inline_collecting();
	$js = <<<JS
jQuery(document).ready(function($) {
  $('.et_pb_video iframe').attr("src", function(i, val) {
    return val + '&rel=0';
  });
});
JS;
	dlck_add_inline_js( $js );
}
?>
