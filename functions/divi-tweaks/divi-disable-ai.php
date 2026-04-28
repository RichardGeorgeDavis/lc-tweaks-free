<?php
/**
 * @package Disable Divi AI
 * @version 1.1
 */

// === Disable Divi AI for non-administrators by default === //

if (false === get_option('et_pb_role_settings')) {
    add_option( 'et_pb_role_settings', array());
}

add_filter('option_et_pb_role_settings', 'dbc_disable_divi_ai_by_default');

function dbc_disable_divi_ai_by_default($option) {

	$allow_on_admin = apply_filters('dbc_disable_divi_ai_allow_on_admin', true);
	$allow_reactivation = apply_filters('dbc_disable_divi_ai_allow_reenable_in_role_editor', true);
	
    // Get a list of user roles
    $roles = array('administrator', 'editor', 'author', 'contributor'); 
    if (function_exists('et_pb_get_all_roles_list')) {
        $et_pb_roles = et_pb_get_all_roles_list();
        if (is_array($et_pb_roles)) {
          $roles = array_keys($et_pb_roles);
        }
    }

	// Disable "Divi AI" role as needed
    if (!is_array($option)) { 
        $option = array(); 
    }
    foreach($roles as $role) {
		
        if ($allow_on_admin && $role === 'administrator') { 
            continue; 
        }
        if (!isset($option[$role]) || !is_array($option[$role])) { 
            $option[$role] = array(); 
        }
        if (!$allow_reactivation || !isset($option[$role]['divi_ai'])) { 
            $option[$role]['divi_ai'] = 'off'; 
        }
    }

    return $option;
}

// Optional - disallow Divi AI for administrators by default
add_filter('dbc_disable_divi_ai_allow_on_admin', '__return_false');

// Optional - disallow reactivation of Divi AI via the role editor
add_filter('dbc_disable_divi_ai_allow_reenable_in_role_editor', '__return_false');