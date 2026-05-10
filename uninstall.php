<?php

if (!defined('WP_UNINSTALL_PLUGIN')) exit;
if (!current_user_can('manage_options')) exit;

// Check LC Kit settings
if ( ! function_exists( 'dlck_get_option' ) ) {
	function dlck_get_option( $dlck_option_name, $dlck_default_value = ''){
		$dlck_option_value = '';
		$dlck_lc_kit_setting = maybe_unserialize( get_option( 'dlck_lc_kit' ) );
		if ( $dlck_option_name != '' && is_array( $dlck_lc_kit_setting ) && array_key_exists( $dlck_option_name,$dlck_lc_kit_setting ) ){
			$dlck_option_value = $dlck_lc_kit_setting[ $dlck_option_name ];
			if ( $dlck_option_value == '' && $dlck_default_value!= ''  ){
				$dlck_option_value = $dlck_default_value;
			}
		}
		return $dlck_option_value;
	}
}
// Check if user wants to remove plugin data 
$dlck_uninstall_data_val = dlck_get_option('dlck_uninstall_data');
if ($dlck_uninstall_data_val == '1' ){
	foreach (wp_load_alloptions() as $option => $value) {
		if (strpos($option, 'dlck_customize_') !== false) {
			delete_option($option);
		}
	}
	delete_option('dlck_lc_kit');
	delete_option('dlckchange_v1_2');
	delete_option('dlck_smtp_password');
	delete_option('dlck_code_helper_php_code');
	delete_option('dlck_code_helper_last_error');
	delete_option('dlck_support_access_token_hash');
	delete_option('dlck_support_access_token_plain_once');
	delete_option('dlck_support_access_expires');
	$dlck_support_user = get_user_by('login', 'lc-tweaks-support');
	if ($dlck_support_user && get_user_meta($dlck_support_user->ID, '_dlck_support_access_user', true) === '1') {
		if (!function_exists('wp_delete_user')) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}
		wp_delete_user($dlck_support_user->ID);
	}
}
