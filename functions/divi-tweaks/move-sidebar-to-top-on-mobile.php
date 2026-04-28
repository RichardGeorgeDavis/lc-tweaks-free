<?php
/**
 * @package Move sidebar to top on mobile
 * @version 1.3
 */
add_action('wp_footer', 'dlck_move_sidebar_to_top_on_mobile');
add_action('dlck_collect_inline_assets_front', 'dlck_move_sidebar_to_top_on_mobile');
function dlck_move_sidebar_to_top_on_mobile()
{
	$collecting = dlck_is_inline_collecting();
	$js = <<<JS
(function($) {
  function a() {
    if( $(window).innerWidth() >= 768) return;
    $('#sidebar').insertBefore('#left-area');
  }

  $(window).on('load', function() {
    a();
  });
})(jQuery);
JS;
	dlck_add_inline_js( $js );

}
