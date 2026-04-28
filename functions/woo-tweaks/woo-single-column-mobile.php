<?php
/**
 * @package Shop single column on mobile - Display WooCommerce Products in Single Column on Mobile Devices.
 * @version 1.2
 */

add_action( 'wp_head', 'dlck_shop_single_column_on_mobile' );
add_action( 'dlck_collect_inline_assets_front', 'dlck_shop_single_column_on_mobile' );
function dlck_shop_single_column_on_mobile() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	$collecting = dlck_is_inline_collecting();

	if ( ! $collecting ) {
		if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
			return;
		}
	}
	$css = '@media (max-width:767px){body.et-db #et-boc .et-l .et_pb_shop ul.products.columns-1 li.product,body.et-db #et-boc .et-l .et_pb_shop ul.products.columns-2 li.product,body.et-db #et-boc .et-l .et_pb_shop ul.products.columns-3 li.product,body.et-db #et-boc .et-l .et_pb_shop ul.products.columns-4 li.product,body.et-db #et-boc .et-l .et_pb_shop ul.products.columns-5 li.product,body.et-db #et-boc .et-l .et_pb_shop ul.products.columns-6 li.product,body.et-db #et-boc .et-l .et_pb_wc_related_products ul.products.columns-1 li.product,body.et-db #et-boc .et-l .et_pb_wc_related_products ul.products.columns-2 li.product,body.et-db #et-boc .et-l .et_pb_wc_related_products ul.products.columns-3 li.product,body.et-db #et-boc .et-l .et_pb_wc_related_products ul.products.columns-4 li.product,body.et-db #et-boc .et-l .et_pb_wc_related_products ul.products.columns-5 li.product,body.et-db #et-boc .et-l .et_pb_wc_related_products ul.products.columns-6 li.product,body.et-db #et-boc .et-l .et_pb_wc_upsells ul.products.columns-1 li.product,body.et-db #et-boc .et-l .et_pb_wc_upsells ul.products.columns-2 li.product,body.et-db #et-boc .et-l .et_pb_wc_upsells ul.products.columns-3 li.product,body.et-db #et-boc .et-l .et_pb_wc_upsells ul.products.columns-4 li.product,body.et-db #et-boc .et-l .et_pb_wc_upsells ul.products.columns-5 li.product,body.et-db #et-boc .et-l .et_pb_wc_upsells ul.products.columns-6 li.product{width:100%!important;margin-right:0!important}}@media all and (max-width:767px){.woocommerce-page ul.products li.product:nth-child(n){margin:0 0 11.5%!important;width:100%!important}}';
	dlck_add_inline_css( $css );
}

?>
