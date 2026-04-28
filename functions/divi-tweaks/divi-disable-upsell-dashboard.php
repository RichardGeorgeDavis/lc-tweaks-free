<?php
/**
 * @package Divi Disable Upsell Dashboard
 * @version 1.1
 */


add_action( 'dlck_collect_inline_assets_admin', 'dlck_disable_divi_dashboard_upsell_css' );

/**
 * Hide Divi upsell panels in the dashboard.
 */
function dlck_disable_divi_dashboard_upsell_css() {
if ( ! is_admin() && ! dlck_is_inline_collecting() ) {
	return;
}

	$hide_divi_dashboard = '#et-onboarding .dashboard .dashboard-devider, #et-onboarding .dashboard .toolkit {display: none!important;}';
	dlck_add_inline_css( $hide_divi_dashboard, 'admin' );
}
