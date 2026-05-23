<?php

$dlck_settings_snapshot = function_exists( 'dlck_get_settings_snapshot' ) ? dlck_get_settings_snapshot() : array();
$dlck_setting = static function ( string $key, $default = '' ) use ( $dlck_settings_snapshot ) {
	if ( array_key_exists( $key, $dlck_settings_snapshot ) ) {
		$value = $dlck_settings_snapshot[ $key ];
		return ( $value === '' && $default !== '' ) ? $default : $value;
	}
	return $default;
};

$dlck_kill_jetpack_cron_val          = $dlck_setting( 'dlck_kill_jetpack_cron' );
$dlck_speedup_scheduled_actions_val  = $dlck_setting( 'dlck_speedup_scheduled_actions' );
$dlck_wprocket_force_page_caching_val = $dlck_setting( 'dlck_wprocket_force_page_caching' );
$dlck_wprocket_cache_wp_rest_api_val = $dlck_setting( 'dlck_wprocket_cache_wp_rest_api' );
$dlck_wprocket_disable_above_fold_opt_val = $dlck_setting( 'dlck_wprocket_disable_above_fold_opt' );
$dlck_wprocket_disable_priority_elements_val = $dlck_setting( 'dlck_wprocket_disable_priority_elements' );
$dlck_disable_ssl_curl_error_60_in_wpallimport_val = $dlck_setting( 'dlck_disable_ssl_curl_error_60_in_wpallimport' );
$dlck_exactdn_image_downsize_scale_val = $dlck_setting( 'dlck_exactdn_image_downsize_scale' );
$dlck_disable_plugin_auto_updates_val = $dlck_setting( 'dlck_disable_plugin_auto_updates' );
$dlck_disable_theme_auto_updates_val  = $dlck_setting( 'dlck_disable_theme_auto_updates' );
$dlck_allow_unfiltered_uploads_val    = $dlck_setting( 'dlck_allow_unfiltered_uploads' );
$dlck_replace_image_tool_val          = $dlck_setting( 'dlck_replace_image_tool' );
$dlck_svg_uploads_val                 = $dlck_setting( 'dlck_svg_uploads' );
$dlck_json_uploads_val                = $dlck_setting( 'dlck_json_uploads' );
$dlck_ttf_uploads_val                 = $dlck_setting( 'dlck_ttf_uploads' );
$dlck_core_upgrade_skip_new_bundled_val = $dlck_setting( 'dlck_core_upgrade_skip_new_bundled' );
$dlck_wp_auto_update_core_val         = $dlck_setting( 'dlck_wp_auto_update_core' );
$dlck_hide_dashboard_welcome_panel_val = $dlck_setting( 'dlck_hide_dashboard_welcome_panel' );
$dlck_all_wp_settings_page_val        = $dlck_setting( 'dlck_all_wp_settings_page' );
$dlck_builder_safe_mode_val           = $dlck_setting( 'dlck_builder_safe_mode' );
$dlck_divi_theme_active               = function_exists( 'dlck_is_divi_theme_active' ) && dlck_is_divi_theme_active();
$dlck_updates_page_url                = admin_url( 'update-core.php' );
$dlck_force_update_check_permissions  = function_exists( 'dlck_force_update_check_permissions' )
	? dlck_force_update_check_permissions()
	: array(
		'plugins' => current_user_can( 'update_plugins' ),
		'themes'  => current_user_can( 'update_themes' ),
	);
$dlck_can_force_update_check          = ! empty( $dlck_force_update_check_permissions['plugins'] ) || ! empty( $dlck_force_update_check_permissions['themes'] );
$dlck_force_update_scope_label        = __( 'plugins and themes', 'lc-tweaks' );
if ( ! empty( $dlck_force_update_check_permissions['plugins'] ) && empty( $dlck_force_update_check_permissions['themes'] ) ) {
	$dlck_force_update_scope_label = __( 'plugins', 'lc-tweaks' );
} elseif ( empty( $dlck_force_update_check_permissions['plugins'] ) && ! empty( $dlck_force_update_check_permissions['themes'] ) ) {
	$dlck_force_update_scope_label = __( 'themes', 'lc-tweaks' );
}
