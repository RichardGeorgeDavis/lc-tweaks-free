<?php
/**
 * @package Fix Divi Anchor Links
 * @version 1.3
 */

add_action( 'dlck_collect_inline_assets_front', 'dlck_fix_divi_anchor_links' );

/**
 * Adjust Divi smooth scroll offset for anchor links.
 */
function dlck_fix_divi_anchor_links() {
	$collecting = dlck_is_inline_collecting();
	if ( ! $collecting && is_admin() ) {
		return;
	}

	$js = <<<'JS'
jQuery(function($) {
  window.et_pb_smooth_scroll = function( $target, $top_section, speed, easing ) {
    var $window_width = $( window ).width();
    var $menu_offset = -1;
    var headerHeight = 85;
    if ( $( '#wpadminbar' ).length && $window_width <= 980 ) {
      $menu_offset += $( '#wpadminbar' ).outerHeight() + headerHeight;
    } else {
      $menu_offset += headerHeight;
    }
    var $scroll_position;
    if ( $top_section ) {
      $scroll_position = 0;
    } else {
      $scroll_position = $target.offset().top - $menu_offset;
    }
    if( typeof easing === 'undefined' ){
      easing = 'swing';
    }
    $( 'html, body' ).animate( { scrollTop :  $scroll_position }, speed, easing );
  };
});
JS;

	dlck_add_inline_js( $js );
}

?>
