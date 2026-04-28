<?php
/**
* @package Autoplay Video Module Clips on Hover
 * @version 1.2
*/

add_action('wp_footer', 'LC_Kit_autoplay_video_on_hover');
add_action('dlck_collect_inline_assets_front', 'LC_Kit_autoplay_video_on_hover');
function LC_Kit_autoplay_video_on_hover()
{
	$collecting = dlck_is_inline_collecting();
	if ( ! $collecting && ( is_admin() || wp_doing_ajax() ) ) {
		return;
	}
	$js = <<<'JS'
jQuery(function($) {
  var $iframes = $('.dlck-autoplay-video-hover.et_pb_video iframe');
  if (!$iframes.length) {
    return;
  }
  $iframes.hover(function(){
    var $el = $(this);
    var orig = $el.attr('src');
    if (!orig) {
      return;
    }
    if (!$el.data('src-orig')) {
      $el.data('src-orig', orig);
    }
    var sep = orig.indexOf('?') === -1 ? '?' : '&';
    $el.attr('src', $el.data('src-orig') + sep + 'autoplay=1&mute=1');
  }, function(){
    var $el = $(this);
    var orig = $el.data('src-orig');
    if (orig) {
      $el.attr('src', orig);
    }
  });
});
JS;
	dlck_add_inline_js( $js );
}
?>
