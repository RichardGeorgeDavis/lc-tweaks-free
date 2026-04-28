<?php
/**
 * Keep the Divi footer pinned to the bottom when content is short.
 */

add_action( 'wp_footer', 'dlck_sticky_footer' );
add_action( 'dlck_collect_inline_assets_front', 'dlck_sticky_footer' );

function dlck_sticky_footer() {
	$collecting = dlck_is_inline_collecting();
	if ( ! $collecting && ! function_exists( 'et_setup_theme' ) ) {
		return;
	}

	$js = <<<'JS'
jQuery(function($) {
  function positionFooter() {
    var $mainFooter;
    if ($('body:not(.et-fb) #main-footer').length > 0) {
      $mainFooter = $('body:not(.et-fb) #main-footer');
    } else if ($('body:not(.et-fb) .et-l--footer').length > 0) {
      $mainFooter = $('body:not(.et-fb) .et-l--footer');
    }

    var $beforeFooter = $('body:not(.et-fb) #dlck-before-footer').length > 0 ? $('body:not(.et-fb) #dlck-before-footer') : null;
    var $afterFooter = $('body:not(.et-fb) #dlck-after-footer').length > 0 ? $('body:not(.et-fb) #dlck-after-footer') : null;

    var bodyHeight = $(document.body).height();
    if ($('.dlck-body-wrapper').length > 0) {
      bodyHeight = $('.dlck-body-wrapper').height();
    }

    var mainFooterHeight = $mainFooter && $mainFooter.length ? $mainFooter.outerHeight() : 0;
    var beforeFooterHeight = $beforeFooter && $beforeFooter.length ? $beforeFooter.outerHeight() : 0;
    var afterFooterHeight = $afterFooter && $afterFooter.length ? $afterFooter.outerHeight() : 0;

    var contentHeight = bodyHeight + beforeFooterHeight + afterFooterHeight + mainFooterHeight;

    if (!($mainFooter && $mainFooter.length)) {
      return;
    }

    if ((contentHeight < $(window).height() && $mainFooter.css('position') === 'fixed') ||
        (bodyHeight < $(window).height() && $mainFooter.css('position') !== 'fixed')) {
      if ($afterFooter && $afterFooter.outerHeight() > 0) {
        $mainFooter.css({
          position: 'fixed',
          bottom: $afterFooter.outerHeight() + 'px',
          right: '0',
          left: '0'
        });
      } else {
        $mainFooter.css({
          position: 'fixed',
          bottom: '0',
          right: '0',
          left: '0'
        });
      }

      if ($beforeFooter) {
        $beforeFooter.css({
          position: 'fixed',
          bottom: $mainFooter.outerHeight() + afterFooterHeight + 'px',
          right: '0',
          left: '0'
        });
      }

      if ($afterFooter) {
        $afterFooter.css({
          position: 'fixed',
          bottom: '0',
          right: '0',
          left: '0'
        });
      }
    } else {
      $mainFooter.css({
        position: '',
        bottom: '',
        right: '',
        left: ''
      });

      if ($beforeFooter) {
        $beforeFooter.css({
          position: '',
          bottom: '',
          right: '',
          left: ''
        });
      }

      if ($afterFooter) {
        $afterFooter.css({
          position: '',
          bottom: '',
          right: '',
          left: ''
        });
      }
    }
  }

  positionFooter();
  $(window).on('scroll resize', positionFooter);
});
JS;
	dlck_add_inline_js( $js );
}
