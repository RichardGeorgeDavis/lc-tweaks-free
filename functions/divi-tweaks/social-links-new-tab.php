<?php
/**
 * Open Divi social icon links in a new tab.
 */

add_action( 'wp_footer', 'dlck_social_links_new_tab' );
add_action( 'dlck_collect_inline_assets_front', 'dlck_social_links_new_tab' );

function dlck_social_links_new_tab() {
	$collecting = dlck_is_inline_collecting();
	if ( ! $collecting && ! function_exists( 'et_setup_theme' ) ) {
		return;
	}

	$js = <<<'JS'
jQuery(function($) {
  var $links = $('.et-social-icon a');
  if (!$links.length) {
    return;
  }
  $links.attr('target', '_blank').attr('rel', 'noopener noreferrer');
});
JS;
	dlck_add_inline_js( $js );
}
