<?php
/**
 * @package Shop Masonry Layout - Display products of WooCommerce with a masonry look.
 * @version 1.2
 */

add_action( 'wp_head', 'dlck_shop_masonry_layout' );
	add_action( 'dlck_collect_inline_assets_front', 'dlck_shop_masonry_layout' );
	function dlck_shop_masonry_layout() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

	$collecting = dlck_is_inline_collecting();

	if ( ! $collecting && ( is_admin() || wp_doing_ajax() ) ) {
		return;
	}

	if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
		return;
	}

	if ( ! $collecting ) {
		if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
			return;
		}
	}
		$css = '.woocommerce ul.products{column-count:3;-webkit-column-count:3;-moz-column-count:3;margin:1.5em 0;padding:0;-webkit-column-gap:1.5em;-moz-column-gap:1.5em;column-gap:1.5em}.woocommerce ul.products li.product{float:none;margin:0 0 1em!important;width:100%!important;-webkit-column-break-inside:avoid;-moz-column-break-inside:avoid;break-inside:avoid;page-break-inside:avoid}.woocommerce ul.products li.product:nth-child(n){width:100%!important}@media all and (max-width:768px){.et-db #et-boc .et-l .et_pb_shop ul.products.columns-4 li.product{width:100%!important;margin:1% 0!important}.woocommerce ul.products li.product:nth-child(n){width:100%!important;margin:0!important}}@media only screen and (max-width:980px){.et_pb_tabs ul li.product a{padding:0!important}}@media only screen and (max-width:480px){.woocommerce ul.products{-webkit-column-count:1;-moz-column-count:1;column-count:1}}.woocommerce ul.products li .et_overlay{display:none}.woocommerce ul.products li .et_shop_image img{margin:0!important}.woocommerce ul.products li .description-wrap{display:flex;flex-direction:column;justify-content:center;text-align:center;position:absolute;width:100%;height:100%;left:0;top:0;background:rgba(255,255,255,.9);z-index:10}.woocommerce ul.products li .description-wrap{transform:scaleX(0);transform-origin:center;transition:all .3s;box-shadow:0 0 100px rgba(0,0,0,.2)}.woocommerce ul.products li:hover .description-wrap{transform:none}';
		dlck_add_inline_css( $css );

		$js = <<<'JS'
(function($){
	$(function(){
		var $products = $('.woocommerce ul.products');
		if (!$products.length) {
			return;
		}
		$products.find('.et_shop_image').each(function(){
			var $img = $(this);
			if ($img.siblings('.description-wrap').length) {
				return;
			}
			$img.siblings().wrapAll("<div class='description-wrap'></div>");
		});
	});
})(jQuery);
JS;
		dlck_add_inline_js( $js );
	}

?>
