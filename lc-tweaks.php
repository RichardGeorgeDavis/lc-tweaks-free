<?php
/*
Plugin Name: LC Tweaks
Version: 1.5.1
Plugin URI: https://lucidity.design/product/lc-tweaks/
Description: Powerful tools to customize the Divi Theme, Wordpress and Woocommerce - added fuctionality, boosted performance and improves page metric results.
Author: Lucidity Design
Author URI: https://lucidity.design
Text Domain: divi-lc-kit
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DLCK_EDITION' ) ) {
	define( 'DLCK_EDITION', 'free' );
}

if ( ! function_exists( 'dlck_get_edition' ) ) {
	function dlck_get_edition(): string {
		return defined( 'DLCK_EDITION' ) && DLCK_EDITION === 'free' ? 'free' : 'pro';
	}
}

if ( ! function_exists( 'dlck_is_free_edition' ) ) {
	function dlck_is_free_edition(): bool {
		return dlck_get_edition() === 'free';
	}
}

if ( ! function_exists( 'dlck_get_free_disabled_options' ) ) {
	/**
	 * Return option keys that are intentionally unavailable in the free edition.
	 *
	 * @return string[]
	 */
	function dlck_get_free_disabled_options(): array {
		return array(
			'dlck_divi_wp_page_transitions',
			'dlck_woo_dg_product_carousel',
			'dlck_tm_divi_shop_extended',
			'dlck_divi_text_on_a_path',
			'dlck_divi_content_intense',
			'dlck_dwd_map_extended',
			'dlck_dwd_custom_fullwidth_header_extended',
			'dlck_yith_activator',
			'dlck_yith_activator_exclude_membership',
			'dlck_yith_activator_exclude_compare',
			'dlck_yith_activator_exclude_wishlist',
		);
	}
}

if ( ! function_exists( 'dlck_edition_allows_option' ) ) {
	function dlck_edition_allows_option( string $option_name ): bool {
		return ! dlck_is_free_edition() || ! in_array( $option_name, dlck_get_free_disabled_options(), true );
	}
}

if ( ! function_exists( 'dlck_filter_settings_for_edition' ) ) {
	/**
	 * Remove settings that are unavailable in the active edition.
	 *
	 * @param array $settings Settings keyed by dlck option name.
	 * @return array
	 */
	function dlck_filter_settings_for_edition( array $settings ): array {
		if ( ! dlck_is_free_edition() ) {
			return $settings;
		}

		foreach ( dlck_get_free_disabled_options() as $option_name ) {
			unset( $settings[ $option_name ] );
		}

		return $settings;
	}
}

define( 'DLCK_LC_KIT_PLUGIN_DIR', trailingslashit( dirname( __FILE__ ) ) );
define( 'DLCK_LC_KIT_PLUGIN_URI', plugins_url( '', __FILE__ ) );
define( 'DLCK_URL_SUPPORT', 'https://lucidity.design/contact/' );

if ( dlck_is_free_edition() ) {
	require_once DLCK_LC_KIT_PLUGIN_DIR . 'functions/free-github-updater.php';
}

add_action(
	'init',
	static function () {
		load_plugin_textdomain( 'divi-lc-kit' );
	}
);

/**
 * Ensure mutually exclusive options and precedence rules.
 *
 * @param mixed $new_value Value being saved.
 * @param mixed $old_value Previous value.
 * @return mixed Filtered value.
 */
function dlck_enforce_mutually_exclusive_options( $new_value, $old_value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	$serialized_input = false;
	$settings         = $new_value;
	$old_settings     = $old_value;

	// Normalize to array for processing.
	if ( is_string( $new_value ) ) {
		$maybe = maybe_unserialize( $new_value );
		if ( is_array( $maybe ) ) {
			$serialized_input = true;
			$settings         = $maybe;
		}
	}

	if ( is_string( $old_value ) ) {
		$old_maybe = maybe_unserialize( $old_value );
		if ( is_array( $old_maybe ) ) {
			$old_settings = $old_maybe;
		}
	}

	if ( is_array( $settings ) ) {
		$old_settings = is_array( $old_settings ) ? $old_settings : array();
		$settings     = dlck_filter_settings_for_edition( $settings );

		// Normalize Woo cart script policy to known values.
		if ( array_key_exists( 'dlck_woo_cart_script_policy', $settings ) ) {
			$woo_cart_policy_allowed = array( 'default', 'disable_non_woo', 'disable_everywhere', 'disable_non_woo_plus_add_to_cart' );
			$woo_cart_policy         = sanitize_key( (string) ( $settings['dlck_woo_cart_script_policy'] ?? 'default' ) );
			if ( ! in_array( $woo_cart_policy, $woo_cart_policy_allowed, true ) ) {
				$woo_cart_policy = 'default';
			}
			$settings['dlck_woo_cart_script_policy'] = $woo_cart_policy;
		}

		// YouTube related suggestions.
		$hide_rel  = ! empty( $settings['dlck_divi_hide_related_video_suggestions'] );
		$overlay   = ! empty( $settings['dlck_divi_disable_related_video_suggestions'] );

		// If both are on, prefer the stronger overlay-based option and disable the channel-only option.
		if ( $hide_rel && $overlay ) {
			$settings['dlck_divi_hide_related_video_suggestions'] = '0';
		}

		// Woo safe vs all files: prefer the stronger "all" and disable the safe version if both on.
		$woo_safe = ! empty( $settings['dlck_remove_woo_files'] );
		$woo_all  = ! empty( $settings['dlck_remove_woo_all_files'] );
		if ( $woo_safe && $woo_all ) {
			$settings['dlck_remove_woo_files'] = '0';
		}

		// Unfiltered uploads supersede SVG-only uploads.
		$unfiltered_uploads = ! empty( $settings['dlck_allow_unfiltered_uploads'] );
		$svg_uploads        = ! empty( $settings['dlck_svg_uploads'] );
		if ( $unfiltered_uploads && $svg_uploads ) {
			$settings['dlck_svg_uploads'] = '0';
		}

		// Unfiltered uploads supersede JSON and font upload toggles.
		$json_uploads = ! empty( $settings['dlck_json_uploads'] );
		$font_uploads = ! empty( $settings['dlck_ttf_uploads'] );
		if ( $unfiltered_uploads && $json_uploads ) {
			$settings['dlck_json_uploads'] = '0';
		}
		if ( $unfiltered_uploads && $font_uploads ) {
			$settings['dlck_ttf_uploads'] = '0';
		}

		// Global admin registration email suppression overrides Woo's admin customer-registration email.
		$disable_admin_registration_emails = ! empty( $settings['dlck_disable_admin_new_user_notification_emails'] );
		$woo_admin_customer_notification   = ! empty( $settings['dlck_notify_admin_when_a_new_customer_account_is_created'] );
		if ( $disable_admin_registration_emails && $woo_admin_customer_notification ) {
			$settings['dlck_notify_admin_when_a_new_customer_account_is_created'] = '0';
		}

		// Cache child toggles depend on the main Divi cache helper toggle.
		$divi_cache_helper_enabled     = ! empty( $settings['dlck_clear_divi_static_css_cache_local_storage'] );
		$divi_cache_helper_was_enabled = ! empty( $old_settings['dlck_clear_divi_static_css_cache_local_storage'] );
		$cache_restore_option          = 'dlck_csc_sub_toggle_restore_state';

		if ( ! $divi_cache_helper_enabled && $divi_cache_helper_was_enabled ) {
			update_option(
				$cache_restore_option,
				array(
					'dlck_auto_clear_cache_after_updates'                 => ! empty( $settings['dlck_auto_clear_cache_after_updates'] ) ? '1' : '0',
					'dlck_auto_clear_cache_after_post_save_builder_exit' => ! empty( $settings['dlck_auto_clear_cache_after_post_save_builder_exit'] ) ? '1' : '0',
				),
				false
			);
		}

		if ( ! $divi_cache_helper_enabled ) {
			$settings['dlck_auto_clear_cache_after_updates']              = '0';
			$settings['dlck_auto_clear_cache_after_post_save_builder_exit'] = '0';
		} elseif ( ! $divi_cache_helper_was_enabled ) {
			$restore_state = get_option( $cache_restore_option, array() );
			if ( is_array( $restore_state ) ) {
				if (
					empty( $settings['dlck_auto_clear_cache_after_updates'] )
					&& isset( $restore_state['dlck_auto_clear_cache_after_updates'] )
					&& (string) $restore_state['dlck_auto_clear_cache_after_updates'] === '1'
				) {
					$settings['dlck_auto_clear_cache_after_updates'] = '1';
				}

				if (
					empty( $settings['dlck_auto_clear_cache_after_post_save_builder_exit'] )
					&& isset( $restore_state['dlck_auto_clear_cache_after_post_save_builder_exit'] )
					&& (string) $restore_state['dlck_auto_clear_cache_after_post_save_builder_exit'] === '1'
				) {
					$settings['dlck_auto_clear_cache_after_post_save_builder_exit'] = '1';
				}
			}

			delete_option( $cache_restore_option );
		}

	}

	return $serialized_input ? maybe_serialize( $settings ) : $settings;
}
add_filter( 'pre_update_option_dlck_lc_kit', 'dlck_enforce_mutually_exclusive_options', 10, 2 );

/**
 * Return warning messages for conflicting/dependent settings.
 *
 * @param array $settings Candidate settings array.
 * @return string[]
 */
function dlck_get_preflight_conflicts( array $settings ): array {
	$is_enabled = static function ( string $key ) use ( $settings ): bool {
		return ! empty( $settings[ $key ] ) && (string) $settings[ $key ] !== '0';
	};

	$messages = array();

	if ( $is_enabled( 'dlck_divi_hide_related_video_suggestions' ) && $is_enabled( 'dlck_divi_disable_related_video_suggestions' ) ) {
		$messages[] = __( 'Both YouTube related-video options are enabled. The channel-only option will be switched off because "Hide Related YouTube Video Suggestions" is stronger.', 'divi-lc-kit' );
	}

	if ( $is_enabled( 'dlck_remove_woo_files' ) && $is_enabled( 'dlck_remove_woo_all_files' ) ) {
		$messages[] = __( 'Both WooCommerce script/style removal options are enabled. The safe option will be switched off because "Stop All Woocommerce Files from Loading" is stronger.', 'divi-lc-kit' );
	}

	if ( $is_enabled( 'dlck_allow_unfiltered_uploads' ) && ( $is_enabled( 'dlck_svg_uploads' ) || $is_enabled( 'dlck_json_uploads' ) || $is_enabled( 'dlck_ttf_uploads' ) ) ) {
		$messages[] = __( '"Allow Unfiltered Uploads" is enabled. SVG, JSON, and font upload toggles will be switched off because they are already covered.', 'divi-lc-kit' );
	}

	if ( $is_enabled( 'dlck_disable_admin_new_user_notification_emails' ) && $is_enabled( 'dlck_notify_admin_when_a_new_customer_account_is_created' ) ) {
		$messages[] = __( '"Disable Admin New User Notification Emails" is enabled. The WooCommerce customer-registration admin email toggle will be switched off because the global setting overrides it.', 'divi-lc-kit' );
	}

	if (
		! $is_enabled( 'dlck_clear_divi_static_css_cache_local_storage' )
		&& ( $is_enabled( 'dlck_auto_clear_cache_after_updates' ) || $is_enabled( 'dlck_auto_clear_cache_after_post_save_builder_exit' ) )
	) {
		$messages[] = __( 'Divi cache auto-clear options require "Clear Divi static css cache + local storage". Dependent options will be switched off.', 'divi-lc-kit' );
	}

	$woo_cart_policy = sanitize_key( (string) ( $settings['dlck_woo_cart_script_policy'] ?? 'default' ) );
	if ( $woo_cart_policy === '' ) {
		$woo_cart_policy = 'default';
	}

	if ( $woo_cart_policy !== 'default' && $is_enabled( 'dlck_remove_woo_all_files' ) ) {
		$messages[] = __( 'Woo cart script policy is active, but "Stop All Woocommerce Files from Loading" already removes these scripts where applicable.', 'divi-lc-kit' );
	} elseif (
		in_array( $woo_cart_policy, array( 'disable_non_woo', 'disable_non_woo_plus_add_to_cart' ), true )
		&& $is_enabled( 'dlck_remove_woo_files' )
	) {
		$messages[] = __( 'Woo cart script policy (non-Woo pages) overlaps with "Stop Woocommerce Files from Loading Safely".', 'divi-lc-kit' );
	}

	$messages = array_merge( $messages, dlck_get_divi_compatibility_preflight_conflicts( $settings ) );

	$messages = array_merge( $messages, dlck_get_scope_rules_preflight_conflicts( $settings ) );

	return array_values(
		array_unique(
			array_filter(
				$messages,
				static function ( $message ) {
					return is_string( $message ) && $message !== '';
				}
			)
		)
	);
}

/**
 * Validate Rank Math advanced schema JSON payloads.
 *
 * The advanced merge expects a top-level JSON object/array so the enrichment
 * runtime can address named entity buckets like organization and webpage.
 *
 * @param mixed $value Raw JSON value from settings.
 * @return bool
 */
function dlck_is_valid_rank_math_schema_advanced_json( $value ): bool {
	if ( ! is_scalar( $value ) ) {
		return false;
	}

	$value = trim( (string) $value );
	if ( $value === '' ) {
		return true;
	}

	// The advanced merge expects a top-level JSON object keyed by entity type.
	if ( '{' !== substr( ltrim( $value ), 0, 1 ) ) {
		return false;
	}

	$decoded = json_decode( $value, true );

	return JSON_ERROR_NONE === json_last_error() && is_array( $decoded );
}

/**
 * Return built-in settings presets.
 *
 * @return array<string,array<string,mixed>>
 */
function dlck_get_settings_presets(): array {
	$presets = array(
		'brochure'       => array(
			'label'       => __( 'Brochure', 'divi-lc-kit' ),
			'description' => __( 'Lean setup for brochure/informational sites that do not rely on WooCommerce storefront scripts.', 'divi-lc-kit' ),
			'settings'    => array(
				'dlck_divi_lazy_loading'                  => '1',
				'dlck_divi_lazy_defer_sections'           => '0',
				'dlck_remove_woo_files'                   => '1',
				'dlck_remove_woo_all_files'               => '0',
				'dlck_remove_woo_block_files'             => '1',
				'dlck_disable_divi_ai'                    => '1',
				'dlck_disable_upsells_divi_dashboard'     => '1',
				'dlck_hide_divi_cloud'                    => '1',
				'dlck_fix_divi_user_scalable'             => '1',
				'dlck_clear_divi_static_css_cache_local_storage' => '1',
				'dlck_auto_clear_cache_after_updates'     => '1',
			),
		),
			'woo_store'      => array(
				'label'       => __( 'Woo Store', 'divi-lc-kit' ),
				'description' => __( 'Balanced defaults for active WooCommerce stores with safer performance-focused options.', 'divi-lc-kit' ),
				'settings'    => array(
				'dlck_divi_lazy_loading'                  => '1',
				'dlck_divi_lazy_defer_sections'           => '0',
					'dlck_remove_woo_files'                   => '0',
					'dlck_remove_woo_all_files'               => '0',
					'dlck_remove_woo_block_files'             => '1',
					'dlck_woo_cart_script_policy'             => 'disable_non_woo',
					'dlck_wp_rocket_side_cart_exclusion'      => '1',
					'dlck_woo_session_expiration'             => '604800',
					'dlck_woo_disable_persistent_cart'        => '1',
				'dlck_clear_divi_static_css_cache_local_storage' => '1',
				'dlck_auto_clear_cache_after_updates'     => '1',
			),
		),
			'troubleshooting' => array(
				'label'       => __( 'Troubleshooting', 'divi-lc-kit' ),
				'description' => __( 'Conservative profile to reduce moving parts when isolating compatibility and performance issues.', 'divi-lc-kit' ),
				'settings'    => array(
					'dlck_builder_safe_mode'                => '1',
					'dlck_divi_lazy_loading'                  => '0',
					'dlck_divi_lazy_defer_sections'           => '0',
					'dlck_divi_wp_page_transitions'           => '0',
					'dlck_fix_divi_flashing_content'          => '0',
					'dlck_remove_woo_files'                   => '0',
					'dlck_remove_woo_all_files'               => '0',
					'dlck_remove_woo_block_files'             => '0',
					'dlck_woo_cart_script_policy'             => 'default',
					'dlck_woo_session_expiration'             => '',
				'dlck_clear_divi_static_css_cache_local_storage' => '0',
				'dlck_auto_clear_cache_after_updates'     => '0',
				'dlck_auto_clear_cache_after_post_save_builder_exit' => '0',
			),
		),
	);

	$presets = apply_filters( 'dlck_settings_presets', $presets );
	if ( ! is_array( $presets ) ) {
		return array();
	}

	$normalized = array();
	foreach ( $presets as $id => $preset ) {
		if ( ! is_string( $id ) || ! is_array( $preset ) ) {
			continue;
		}

		$preset_id = sanitize_key( $id );
		if ( $preset_id === '' ) {
			continue;
		}

		$label       = isset( $preset['label'] ) && is_string( $preset['label'] ) ? $preset['label'] : ucwords( str_replace( '_', ' ', $preset_id ) );
		$description = isset( $preset['description'] ) && is_string( $preset['description'] ) ? $preset['description'] : '';
		$settings    = isset( $preset['settings'] ) && is_array( $preset['settings'] ) ? $preset['settings'] : array();

		$filtered_settings = array();
		foreach ( $settings as $key => $value ) {
			if ( ! is_string( $key ) || strpos( $key, 'dlck_' ) !== 0 ) {
				continue;
			}
			$filtered_settings[ sanitize_key( $key ) ] = is_scalar( $value ) ? (string) $value : '';
		}

		if ( empty( $filtered_settings ) ) {
			continue;
		}

		$normalized[ $preset_id ] = array(
			'label'       => $label,
			'description' => $description,
			'settings'    => $filtered_settings,
		);
	}

	return $normalized;
}

/**
 * Resolve a settings preset by key.
 *
 * @param string $preset_id Preset identifier.
 * @return array<string,mixed>|null
 */
function dlck_get_settings_preset( string $preset_id ): ?array {
	$preset_id = sanitize_key( $preset_id );
	if ( $preset_id === '' ) {
		return null;
	}

	$presets = dlck_get_settings_presets();
	if ( empty( $presets[ $preset_id ] ) || ! is_array( $presets[ $preset_id ] ) ) {
		return null;
	}

	return $presets[ $preset_id ];
}

/**
 * Return stored one-click restore payload created before the last preset apply.
 *
 * @return array<string,mixed>
 */
function dlck_get_preset_restore_payload(): array {
	$payload = get_option( 'dlck_lc_kit_preset_restore', array() );
	if ( ! is_array( $payload ) ) {
		return array();
	}

	if ( ! array_key_exists( 'settings', $payload ) || ! is_array( $payload['settings'] ) ) {
		return array();
	}
	$settings = $payload['settings'];

	$filtered_settings = array();
	foreach ( $settings as $key => $value ) {
		if ( ! is_string( $key ) || strpos( $key, 'dlck_' ) !== 0 ) {
			continue;
		}
		$filtered_settings[ sanitize_key( $key ) ] = is_scalar( $value ) ? (string) $value : '';
	}
	return array(
		'created'      => isset( $payload['created'] ) ? (int) $payload['created'] : 0,
		'preset_id'    => isset( $payload['preset_id'] ) ? sanitize_key( (string) $payload['preset_id'] ) : '',
		'preset_label' => isset( $payload['preset_label'] ) ? sanitize_text_field( (string) $payload['preset_label'] ) : '',
		'settings'     => $filtered_settings,
	);
}

/**
 * Store restore payload created before applying a preset.
 *
 * @param array  $settings     Settings array before preset apply.
 * @param string $preset_id    Applied preset ID.
 * @param string $preset_label Applied preset label.
 * @return bool
 */
function dlck_store_preset_restore_payload( array $settings, string $preset_id, string $preset_label ): bool {
	$filtered_settings = array();
	foreach ( $settings as $key => $value ) {
		if ( ! is_string( $key ) || strpos( $key, 'dlck_' ) !== 0 ) {
			continue;
		}
		$filtered_settings[ sanitize_key( $key ) ] = is_scalar( $value ) ? (string) $value : '';
	}

	update_option(
		'dlck_lc_kit_preset_restore',
		array(
			'created'      => time(),
			'preset_id'    => sanitize_key( $preset_id ),
			'preset_label' => sanitize_text_field( $preset_label ),
			'settings'     => $filtered_settings,
		),
		false
	);

	return true;
}

/**
 * Remove stored preset restore payload.
 */
function dlck_clear_preset_restore_payload(): void {
	delete_option( 'dlck_lc_kit_preset_restore' );
}

/**
 * Rebuild inline asset caches any time lc kit settings change programmatically.
 */
add_action(
	'update_option_dlck_lc_kit',
	static function () {
		if ( function_exists( 'dlck_rebuild_all_inline_caches' ) ) {
			dlck_rebuild_all_inline_caches();
		}
	},
	10,
	0
);

require_once DLCK_LC_KIT_PLUGIN_DIR . 'functions/settings/migrations.php';
require_once DLCK_LC_KIT_PLUGIN_DIR . 'functions/settings/port.php';
require_once DLCK_LC_KIT_PLUGIN_DIR . 'functions/settings/cli.php';
if ( is_admin() ) {
	require_once DLCK_LC_KIT_PLUGIN_DIR . 'functions/settings/divi-accessibility-migration.php';
}
require_once DLCK_LC_KIT_PLUGIN_DIR . 'functions/inline-assets.php';
require_once DLCK_LC_KIT_PLUGIN_DIR . 'functions/tweaks/force-update-check.php';

/**
 * Rebuild inline asset caches after updates/activation when the flashing fix is enabled.
 */
function dlck_maybe_rebuild_front_head_cache_on_update() {
	if ( function_exists( 'dlck_get_option' ) && '1' === dlck_get_option( 'dlck_fix_divi_flashing_content' ) ) {
		if ( function_exists( 'dlck_rebuild_all_inline_caches' ) ) {
			dlck_rebuild_all_inline_caches();
		}
	}
}

register_activation_hook( __FILE__, 'dlck_maybe_rebuild_front_head_cache_on_update' );

/**
 * Clear plugin cron events on deactivation.
 */
function dlck_clear_plugin_scheduled_events_on_deactivation(): void {
	wp_clear_scheduled_hook( 'dlck_woo_session_cleanup_cron' );
}
register_deactivation_hook( __FILE__, 'dlck_clear_plugin_scheduled_events_on_deactivation' );

add_action(
	'upgrader_process_complete',
	static function ( $upgrader, $hook_extra ) {
		if ( empty( $hook_extra['type'] ) || $hook_extra['type'] !== 'plugin' ) {
			return;
		}
		if ( empty( $hook_extra['plugins'] ) || ! is_array( $hook_extra['plugins'] ) ) {
			return;
		}
		if ( in_array( plugin_basename( __FILE__ ), $hook_extra['plugins'], true ) ) {
			dlck_maybe_rebuild_front_head_cache_on_update();
		}
	},
	10,
	2
);

// Prevent WooCommerce textdomain from loading too early (WP 6.7 notice).
$GLOBALS['dlck_queued_textdomains'] = array();

add_filter(
	'override_load_textdomain',
	static function ( $override, $domain, $mofile ) {
		if ( 'woocommerce' !== $domain ) {
			return $override;
		}

		// Block early loads; queue the mofile so we can load it at init.
		if ( ! did_action( 'init' ) ) {
			if ( ! isset( $GLOBALS['dlck_queued_textdomains'][ $domain ] ) ) {
				$GLOBALS['dlck_queued_textdomains'][ $domain ] = array();
			}
			if ( $mofile ) {
				$GLOBALS['dlck_queued_textdomains'][ $domain ][] = $mofile;
			}
			return true;
		}

		return $override;
	},
	0,
	3
);
add_action(
	'init',
	static function () {
		$queued = isset( $GLOBALS['dlck_queued_textdomains'] ) ? $GLOBALS['dlck_queued_textdomains'] : array();
		if ( isset( $queued['woocommerce'] ) ) {
			$loaded = false;
			foreach ( array_unique( $queued['woocommerce'] ) as $mofile ) {
				if ( $mofile ) {
					// @codingStandardsIgnoreLine
					$loaded = load_textdomain( 'woocommerce', $mofile ) || $loaded;
				}
			}
			if ( ! $loaded && function_exists( 'load_plugin_textdomain' ) ) {
				load_plugin_textdomain( 'woocommerce', false, 'woocommerce/i18n/languages' );
			}
		}
	},
	1
);

// ---------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------

if ( ! function_exists( 'dlck_filter_settings_array' ) ) {
	/**
	 * Keep dlck_* settings in a normalized array.
	 *
	 * @param array $settings Raw settings array.
	 * @return array<string,mixed>
	 */
	function dlck_filter_settings_array( array $settings ): array {
		$filtered = array();
		foreach ( $settings as $key => $value ) {
			if ( ! is_string( $key ) || strpos( $key, 'dlck_' ) !== 0 ) {
				continue;
			}
			if ( is_array( $value ) ) {
				$filtered[ sanitize_key( $key ) ] = $value;
				continue;
			}
			$filtered[ sanitize_key( $key ) ] = is_scalar( $value ) ? (string) $value : '';
		}
		return dlck_filter_settings_for_edition( $filtered );
	}
}

if ( ! function_exists( 'dlck_get_multisite_policy' ) ) {
	/**
	 * Return normalized multisite policy settings.
	 *
	 * @return array{enabled:string,allow_site_overrides:string,updated:int,source_blog_id:int}
	 */
	function dlck_get_multisite_policy(): array {
		$defaults = array(
			'enabled'              => '0',
			'allow_site_overrides' => '1',
			'updated'              => 0,
			'source_blog_id'       => 0,
		);

		if ( ! is_multisite() ) {
			return $defaults;
		}

		$raw = get_site_option( 'dlck_lc_kit_network_policy', array() );
		if ( ! is_array( $raw ) ) {
			return $defaults;
		}

		return array(
			'enabled'              => ! empty( $raw['enabled'] ) ? '1' : '0',
			'allow_site_overrides' => ! empty( $raw['allow_site_overrides'] ) ? '1' : '0',
			'updated'              => isset( $raw['updated'] ) ? (int) $raw['updated'] : 0,
			'source_blog_id'       => isset( $raw['source_blog_id'] ) ? (int) $raw['source_blog_id'] : 0,
		);
	}
}

if ( ! function_exists( 'dlck_get_multisite_default_settings' ) ) {
	/**
	 * Return normalized network default settings payload.
	 *
	 * @return array<string,mixed>
	 */
	function dlck_get_multisite_default_settings(): array {
		if ( ! is_multisite() ) {
			return array();
		}

		$raw = get_site_option( 'dlck_lc_kit_network_defaults', array() );
		if ( ! is_array( $raw ) ) {
			return array();
		}

		return dlck_filter_settings_array( $raw );
	}
}

if ( ! function_exists( 'dlck_multisite_policy_blocks_site_saves' ) ) {
	/**
	 * Whether local site saves should be blocked by multisite policy.
	 */
	function dlck_multisite_policy_blocks_site_saves(): bool {
		if ( ! is_multisite() ) {
			return false;
		}

		$policy = dlck_get_multisite_policy();
		return ( isset( $policy['enabled'] ) && (string) $policy['enabled'] === '1' )
			&& ( isset( $policy['allow_site_overrides'] ) && (string) $policy['allow_site_overrides'] !== '1' );
	}
}

if ( ! function_exists( 'dlck_get_effective_lc_kit_settings' ) ) {
	/**
	 * Return effective LC Tweaks settings (local or multisite policy merged).
	 *
	 * @param bool $refresh Force refresh cached values.
	 * @return array<string,mixed>
	 */
	function dlck_get_effective_lc_kit_settings( bool $refresh = false ): array {
		$cache_key = is_multisite() ? get_current_blog_id() : 0;
		if (
			! $refresh
			&& isset( $GLOBALS['dlck_effective_lc_kit_settings'] )
			&& is_array( $GLOBALS['dlck_effective_lc_kit_settings'] )
			&& isset( $GLOBALS['dlck_effective_lc_kit_settings'][ $cache_key ] )
			&& is_array( $GLOBALS['dlck_effective_lc_kit_settings'][ $cache_key ] )
		) {
			return $GLOBALS['dlck_effective_lc_kit_settings'][ $cache_key ];
		}

		$local_settings = maybe_unserialize( get_option( 'dlck_lc_kit' ) );
		$local_settings = is_array( $local_settings ) ? dlck_filter_settings_array( $local_settings ) : array();
		$effective      = $local_settings;

		if ( is_multisite() ) {
			$policy = dlck_get_multisite_policy();
			if ( isset( $policy['enabled'] ) && (string) $policy['enabled'] === '1' ) {
				$network_defaults = dlck_get_multisite_default_settings();
				$effective        = ( isset( $policy['allow_site_overrides'] ) && (string) $policy['allow_site_overrides'] === '1' )
					? array_merge( $network_defaults, $local_settings )
					: $network_defaults;
			}
		}

		if ( ! isset( $GLOBALS['dlck_effective_lc_kit_settings'] ) || ! is_array( $GLOBALS['dlck_effective_lc_kit_settings'] ) ) {
			$GLOBALS['dlck_effective_lc_kit_settings'] = array();
		}
		$GLOBALS['dlck_effective_lc_kit_settings'][ $cache_key ] = $effective;
		return $effective;
	}
}

if ( ! function_exists( 'dlck_clear_effective_lc_kit_settings_cache' ) ) {
	/**
	 * Clear effective settings cache for one blog (or all blogs).
	 *
	 * @param int|null $blog_id Blog ID to clear. Null clears all.
	 */
	function dlck_clear_effective_lc_kit_settings_cache( ?int $blog_id = null ): void {
		if ( ! isset( $GLOBALS['dlck_effective_lc_kit_settings'] ) || ! is_array( $GLOBALS['dlck_effective_lc_kit_settings'] ) ) {
			return;
		}

		if ( null === $blog_id ) {
			unset( $GLOBALS['dlck_effective_lc_kit_settings'] );
			return;
		}

		unset( $GLOBALS['dlck_effective_lc_kit_settings'][ $blog_id ] );
		if ( empty( $GLOBALS['dlck_effective_lc_kit_settings'] ) ) {
			unset( $GLOBALS['dlck_effective_lc_kit_settings'] );
		}
	}
}

if ( ! function_exists( 'dlck_get_option' ) ) {
	function dlck_get_option( $dlck_option_name, $dlck_default_value = '' ) {
		if ( is_string( $dlck_option_name ) && ! dlck_edition_allows_option( $dlck_option_name ) ) {
			return $dlck_default_value;
		}

		$dlck_option_value   = '';
		$dlck_lc_kit_setting = function_exists( 'dlck_get_effective_lc_kit_settings' )
			? dlck_get_effective_lc_kit_settings()
			: maybe_unserialize( get_option( 'dlck_lc_kit' ) );

		if ( $dlck_option_name !== '' && is_array( $dlck_lc_kit_setting ) && array_key_exists( $dlck_option_name, $dlck_lc_kit_setting ) ) {
			$dlck_option_value = $dlck_lc_kit_setting[ $dlck_option_name ];

			if ( $dlck_option_value === '' && $dlck_default_value !== '' ) {
				$dlck_option_value = $dlck_default_value;
			}
		}

		return $dlck_option_value;
	}
}

if ( ! function_exists( 'dlck_get_settings_snapshot' ) ) {
	/**
	 * Return a request-cached settings snapshot for admin render/runtime helpers.
	 *
	 * @return array<string,mixed>
	 */
	function dlck_get_settings_snapshot(): array {
		static $snapshot = null;

		if ( is_array( $snapshot ) ) {
			return $snapshot;
		}

		$settings = function_exists( 'dlck_get_effective_lc_kit_settings' )
			? dlck_get_effective_lc_kit_settings()
			: maybe_unserialize( get_option( 'dlck_lc_kit', array() ) );
		$snapshot = is_array( $settings ) ? $settings : array();

		return $snapshot;
	}
}

if ( ! function_exists( 'dlck_get_setting_from_snapshot' ) ) {
	/**
	 * Read a single option key from the request snapshot with fallback.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Fallback value.
	 * @return mixed
	 */
	function dlck_get_setting_from_snapshot( string $key, $default = '' ) {
		$snapshot = dlck_get_settings_snapshot();

		if ( $key !== '' && array_key_exists( $key, $snapshot ) ) {
			$value = $snapshot[ $key ];
			if ( $value === '' && $default !== '' ) {
				return $default;
			}
			return $value;
		}

		return $default;
	}
}

add_action(
	'update_option_dlck_lc_kit',
	static function () {
		if ( function_exists( 'dlck_clear_effective_lc_kit_settings_cache' ) ) {
			dlck_clear_effective_lc_kit_settings_cache( is_multisite() ? get_current_blog_id() : 0 );
		}
	},
	5,
	0
);

add_action(
	'update_site_option_dlck_lc_kit_network_policy',
	static function () {
		if ( function_exists( 'dlck_clear_effective_lc_kit_settings_cache' ) ) {
			dlck_clear_effective_lc_kit_settings_cache();
		}
	},
	5,
	0
);

add_action(
	'update_site_option_dlck_lc_kit_network_defaults',
	static function () {
		if ( function_exists( 'dlck_clear_effective_lc_kit_settings_cache' ) ) {
			dlck_clear_effective_lc_kit_settings_cache();
		}
	},
	5,
	0
);

add_action(
	'switch_blog',
	static function () {
		if ( function_exists( 'dlck_clear_effective_lc_kit_settings_cache' ) ) {
			dlck_clear_effective_lc_kit_settings_cache();
		}
	},
	10,
	0
);


if ( ! function_exists( 'dlck_is_woocommerce_active' ) ) {
	function dlck_is_woocommerce_active(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( class_exists( 'WooCommerce' ) ) {
			return true;
		}
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return true;
		}
		if ( is_multisite() && is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
			return true;
		}
		return false;
	}
}

if ( ! function_exists( 'dlck_any_yith_plugins_active' ) ) {
	/**
	 * Check whether any supported excluded YITH plugin is active.
	 */
	function dlck_any_yith_plugins_active(): bool {
		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_dirs = array(
			'yith-woocommerce-compare-premium/',
			'yith-woocommerce-membership-premium/',
			'yith-woocommerce-wishlist-premium/',
		);

		$installed_plugins = get_plugins();
		foreach ( array_keys( $installed_plugins ) as $plugin_file ) {
			if ( ! is_string( $plugin_file ) ) {
				continue;
			}

			foreach ( $plugin_dirs as $plugin_dir ) {
				if ( ! str_starts_with( $plugin_file, $plugin_dir ) ) {
					continue;
				}

				if ( is_plugin_active( $plugin_file ) ) {
					return true;
				}

				if ( is_multisite() && is_plugin_active_for_network( $plugin_file ) ) {
					return true;
				}
			}
		}

		return false;
	}
}

if ( ! function_exists( 'dlck_is_divi_theme_active' ) ) {
	function dlck_is_divi_theme_active(): bool {
		$theme      = wp_get_theme();
		$template   = $theme->get_template();
		$stylesheet = $theme->get_stylesheet();

		return ( stripos( $theme->get( 'Name' ), 'Divi' ) !== false )
			|| ( stripos( $theme->get( 'Template' ), 'Divi' ) !== false )
			|| ( stripos( $template, 'divi' ) !== false )
			|| ( stripos( $stylesheet, 'divi' ) !== false );
	}
}

if ( ! function_exists( 'dlck_get_divi_theme_version' ) ) {
	/**
	 * Return active Divi theme version string when available.
	 */
	function dlck_get_divi_theme_version(): string {
		if ( ! dlck_is_divi_theme_active() ) {
			return '';
		}

		$theme   = wp_get_theme();
		$version = (string) $theme->get( 'Version' );

		if ( $version === '' && $theme->parent() ) {
			$version = (string) $theme->parent()->get( 'Version' );
		}

		$clean_version = preg_replace( '/[^0-9.].*$/', '', $version );
		return is_string( $clean_version ) ? $clean_version : '';
	}
}

if ( ! function_exists( 'dlck_get_divi_major_version' ) ) {
	/**
	 * Return active Divi major version (3/4/5), or 0 if unknown.
	 */
	function dlck_get_divi_major_version(): int {
		$version = dlck_get_divi_theme_version();
		if ( $version === '' ) {
			return 0;
		}

		$parts = explode( '.', $version );
		return isset( $parts[0] ) ? absint( $parts[0] ) : 0;
	}
}

/**
 * Return Divi option compatibility matrix keyed by option name.
 *
 * @return array<string,array<string,mixed>>
 */
function dlck_get_divi_compatibility_matrix(): array {
	$matrix = array(
		'dlck_woo_dg_product_carousel'            => array(
			'label'    => __( 'Divi Woo Product Carousel', 'divi-lc-kit' ),
			'versions' => array( 4 ),
		),
		'dlck_divi_text_on_a_path'               => array(
			'label'    => __( 'Text-On-A-Path', 'divi-lc-kit' ),
			'versions' => array( 4 ),
		),
		'dlck_dwd_custom_fullwidth_header_extended' => array(
			'label'    => __( 'Custom Fullwidth Header Extended', 'divi-lc-kit' ),
			'versions' => array( 4 ),
		),
		'dlck_tm_divi_shop_extended'             => array(
			'label'    => __( 'TM Divi Shop Extended', 'divi-lc-kit' ),
			'versions' => array( 3 ),
		),
		'dlck_divi_content_intense'              => array(
			'label'    => __( 'Content Intense', 'divi-lc-kit' ),
			'versions' => array( 3 ),
		),
		'dlck_dwd_map_extended'                  => array(
			'label'    => __( 'Map Module Extended', 'divi-lc-kit' ),
			'versions' => array( 3 ),
		),
		'dlck_fix_divi_flashing_content'         => array(
			'label'    => __( 'Fix Divi Flashing', 'divi-lc-kit' ),
			'versions' => array( 4 ),
		),
		'dlck_disable_plugin_check'              => array(
			'label'    => __( 'Divi Disable Plugin Check', 'divi-lc-kit' ),
			'versions' => array( 4 ),
		),
		'dlck_enable_divi_builder_by_default'    => array(
			'label'    => __( 'Enable Divi Builder by Default', 'divi-lc-kit' ),
			'versions' => array( 4 ),
		),
		'dlck_hide_gutenberg_std_editor_buttons' => array(
			'label'    => __( 'Hide The Gutenberg Editor Buttons', 'divi-lc-kit' ),
			'versions' => array( 4 ),
		),
		'dlck_divi_builder_quick_fixes'          => array(
			'label'    => __( 'Divi Builder Quick Fixes', 'divi-lc-kit' ),
			'versions' => array( 4 ),
		),
	);

	$matrix = apply_filters( 'dlck_divi_compatibility_matrix', $matrix );
	return is_array( $matrix ) ? $matrix : array();
}

/**
 * Check whether an option is compatible with the active Divi major version.
 */
function dlck_divi_option_is_compatible( string $option_name, ?int $divi_major = null ): bool {
	$option_name = sanitize_key( $option_name );
	$matrix      = dlck_get_divi_compatibility_matrix();

	if ( ! isset( $matrix[ $option_name ] ) || ! is_array( $matrix[ $option_name ] ) ) {
		return true;
	}

	$versions = isset( $matrix[ $option_name ]['versions'] ) && is_array( $matrix[ $option_name ]['versions'] )
		? array_values( array_filter( array_map( 'absint', $matrix[ $option_name ]['versions'] ) ) )
		: array();

	if ( empty( $versions ) ) {
		return true;
	}

	if ( ! dlck_is_divi_theme_active() ) {
		return false;
	}

	if ( $divi_major === null ) {
		$divi_major = dlck_get_divi_major_version();
	}
	if ( $divi_major <= 0 ) {
		return true;
	}

	return in_array( (int) $divi_major, $versions, true );
}

/**
 * Return user-facing compatibility message for an option, if incompatible.
 */
function dlck_get_divi_option_compatibility_message( string $option_name, ?int $divi_major = null ): string {
	$option_name = sanitize_key( $option_name );
	$matrix      = dlck_get_divi_compatibility_matrix();

	if ( ! isset( $matrix[ $option_name ] ) || ! is_array( $matrix[ $option_name ] ) ) {
		return '';
	}
	if ( dlck_divi_option_is_compatible( $option_name, $divi_major ) ) {
		return '';
	}

	$rule = $matrix[ $option_name ];

	$versions = isset( $rule['versions'] ) && is_array( $rule['versions'] )
		? array_values( array_filter( array_map( 'absint', $rule['versions'] ) ) )
		: array();

	sort( $versions );
	$required_labels = array_map(
		static function ( int $version ): string {
			return sprintf( __( 'Divi %d', 'divi-lc-kit' ), $version );
		},
		$versions
	);
	$required = implode( ', ', $required_labels );
	$label    = isset( $rule['label'] ) && is_string( $rule['label'] ) && $rule['label'] !== ''
		? $rule['label']
		: $option_name;

	if ( $divi_major === null ) {
		$divi_major = dlck_get_divi_major_version();
	}
	$current = $divi_major > 0 ? sprintf( __( 'Divi %d', 'divi-lc-kit' ), $divi_major ) : __( 'Unknown Divi version', 'divi-lc-kit' );

	/* translators: 1: option label, 2: target Divi versions, 3: current Divi version */
	return sprintf( __( '%1$s is built for %2$s (current: %3$s). It may still run with Divi backward compatibility, but can be buggy.', 'divi-lc-kit' ), $label, $required, $current );
}

/**
 * Return preflight warnings for enabled options incompatible with Divi version.
 *
 * @param array $settings Candidate settings array.
 * @return string[]
 */
function dlck_get_divi_compatibility_preflight_conflicts( array $settings ): array {
	$messages   = array();
	$divi_major = dlck_get_divi_major_version();

	foreach ( dlck_get_divi_compatibility_matrix() as $option_name => $rule ) {
		if ( empty( $settings[ $option_name ] ) || (string) $settings[ $option_name ] === '0' ) {
			continue;
		}

		$message = dlck_get_divi_option_compatibility_message( $option_name, $divi_major );
		if ( $message !== '' ) {
			/* translators: %s: incompatibility explanation */
			$messages[] = sprintf( __( '%s This option stays enabled, but behavior may be unstable on this Divi version.', 'divi-lc-kit' ), $message );
		}
	}

	return array_values( array_unique( $messages ) );
}

if ( ! function_exists( 'dlck_is_divi_visual_builder_request' ) ) {
	/**
	 * Determine whether current request is inside Divi Visual Builder context.
	 */
	function dlck_is_divi_visual_builder_request(): bool {
		$is_builder_request = false;

		if ( function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) {
			$is_builder_request = true;
		} elseif ( function_exists( 'et_fb_is_enabled' ) && et_fb_is_enabled() ) {
			$is_builder_request = true;
		} else {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$is_builder_request = (
				( isset( $_GET['et_fb'] ) && '1' === wp_unslash( $_GET['et_fb'] ) )
				|| ( isset( $_GET['app_window'] ) && '1' === wp_unslash( $_GET['app_window'] ) )
				|| isset( $_GET['et_vb_preview_id'] )
			);
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			if ( ! $is_builder_request && function_exists( 'is_et_d5_preview' ) ) {
				$is_builder_request = is_et_d5_preview();
			}
		}

		if ( ! $is_builder_request && function_exists( 'et_builder_is_rest_api_request' ) ) {
			$is_builder_request = false !== et_builder_is_rest_api_request();
		}

		/**
		 * Filter whether current request is treated as Divi Visual Builder context.
		 */
		return (bool) apply_filters( 'dlck_is_divi_visual_builder_request', $is_builder_request );
	}
}

if ( ! function_exists( 'dlck_builder_safe_mode_enabled' ) ) {
	/**
	 * Check if Builder-safe mode option is enabled.
	 */
	function dlck_builder_safe_mode_enabled(): bool {
		return (string) dlck_get_option( 'dlck_builder_safe_mode' ) === '1';
	}
}

if ( ! function_exists( 'dlck_is_builder_safe_mode_request_context' ) ) {
	/**
	 * Determine whether current request should use Builder-safe mode gating.
	 */
	function dlck_is_builder_safe_mode_request_context(): bool {
		if ( function_exists( 'dlck_is_divi_visual_builder_request' ) && dlck_is_divi_visual_builder_request() ) {
			return true;
		}

		if ( ! is_admin() ) {
			return false;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$builder_flags = array( 'et_fb', 'et_vb_preview_id', 'app_window', 'et_builder', 'et_builder_frame' );
		foreach ( $builder_flags as $flag ) {
			if ( isset( $_GET[ $flag ] ) ) {
				return true;
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return false;
	}
}

if ( ! function_exists( 'dlck_builder_safe_mode_is_active' ) ) {
	/**
	 * Check whether Builder-safe mode should actively gate runtime hooks.
	 */
	function dlck_builder_safe_mode_is_active(): bool {
		return dlck_builder_safe_mode_enabled() && dlck_is_builder_safe_mode_request_context();
	}
}

if ( ! function_exists( 'dlck_builder_safe_mode_allowed_options' ) ) {
	/**
	 * Option allowlist that can still run while Builder-safe mode is active.
	 *
	 * @return string[]
	 */
	function dlck_builder_safe_mode_allowed_options(): array {
		$allowed = array(
			'dlck_builder_safe_mode',
		);

		$allowed = apply_filters( 'dlck_builder_safe_mode_allowed_options', $allowed );
		if ( ! is_array( $allowed ) ) {
			return array( 'dlck_builder_safe_mode' );
		}

		$normalized = array_values( array_filter( array_map( 'sanitize_key', $allowed ) ) );
		if ( ! in_array( 'dlck_builder_safe_mode', $normalized, true ) ) {
			$normalized[] = 'dlck_builder_safe_mode';
		}
		return $normalized;
	}
}

if ( ! function_exists( 'dlck_builder_safe_mode_blocks_option' ) ) {
	/**
	 * Decide whether an option should be skipped in Builder-safe mode.
	 */
	function dlck_builder_safe_mode_blocks_option( string $option_name ): bool {
		if ( ! dlck_builder_safe_mode_is_active() ) {
			return false;
		}

		$option_name = sanitize_key( $option_name );
		if ( $option_name === '' ) {
			return false;
		}

		return ! in_array( $option_name, dlck_builder_safe_mode_allowed_options(), true );
	}
}

if ( ! function_exists( 'dlck_get_divi_visual_builder_url' ) ) {
	/**
	 * Build a Divi Visual Builder URL for a given permalink.
	 *
	 * @param string $url URL to open in Visual Builder.
	 * @return string
	 */
	function dlck_get_divi_visual_builder_url( string $url ): string {
		if ( $url === '' ) {
			return '';
		}

		if ( function_exists( 'et_fb_get_vb_url' ) ) {
			return et_fb_get_vb_url( $url );
		}

		if ( function_exists( 'et_fb_get_builder_url' ) ) {
			return et_fb_get_builder_url( $url );
		}

		return add_query_arg( 'et_fb', '1', $url );
	}
}

if ( ! function_exists( 'dlck_is_gla_active' ) ) {
	function dlck_is_gla_active(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( class_exists( 'WC_Google_Listing_Ads' ) || class_exists( 'WC_Google_Listing_Ads\\Plugin' ) ) {
			return true;
		}

		if ( class_exists( 'Automattic\\WooCommerce\\GoogleListingsAndAds\\Plugin' ) ) {
			return true;
		}

		$plugin_files = array(
			'google-listings-and-ads/google-listings-and-ads.php',
			'google-listings-and-ads/woocommerce-gla.php',
		);

		foreach ( $plugin_files as $plugin_file ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return true;
			}
			if ( is_multisite() && is_plugin_active_for_network( $plugin_file ) ) {
				return true;
			}
		}

		return false;
	}
}

// ---------------------------------------------------------------------
// Migration
// ---------------------------------------------------------------------
function dlck_lite_run_migration() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$result = dlck_restore_divi_toolbox_data();

	if ( $result['copied'] ) {
		update_option(
			'dlck_lite_migration_notice',
			array(
				'type'    => 'success',
				'message' => $result['message'],
			)
		);
	}
}
add_action( 'admin_init', 'dlck_lite_run_migration' );

function dlck_lite_migration_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$notice = get_option( 'dlck_lite_migration_notice' );
	if ( empty( $notice['message'] ) ) {
		return;
	}
	$type = $notice['type'] === 'success' ? 'updated' : 'notice-info';
	printf(
		'<div class="notice %1$s is-dismissible"><p>%2$s</p></div>',
		esc_attr( $type ),
		esc_html( $notice['message'] )
	);
	delete_option( 'dlck_lite_migration_notice' );
}
add_action( 'admin_notices', 'dlck_lite_migration_notice' );

// ---------------------------------------------------------------------
// Admin menu + assets
// ---------------------------------------------------------------------
function dlck_lc_kit_menu() {
	$menu_callback = 'divi_lc_kit';
	$title         = __( 'LC Tweaks', 'divi-lc-kit' );
	$slug          = 'lc_tweaks';
	$legacy_slug   = 'divi_lc_kit';

	$added = false;
	if ( dlck_is_divi_theme_active() ) {
		// Mirror Toolbox placement under Divi options when Divi is active.
		$added = add_submenu_page( 'et_divi_options', $title, $title, 'manage_options', $slug, $menu_callback, 90 ); // later in list to sit below Toolbox
	}

	// Register fallback entries under Settings to ensure the page exists.
	add_submenu_page( 'options-general.php', $title, $title, 'manage_options', $slug, $menu_callback );
	add_submenu_page( 'options-general.php', $title, $title, 'manage_options', $legacy_slug, $menu_callback );

	// Hide legacy/fallback entries when not needed.
	add_action(
		'admin_head',
		static function () use ( $added ) {
			// Always hide the legacy slug to avoid duplicate menu entries.
			remove_submenu_page( 'options-general.php', 'divi_lc_kit' );
			// If Divi submenu is present, hide the Settings copy as well.
			if ( $added ) {
				remove_submenu_page( 'options-general.php', 'lc_tweaks' );
			}
		}
	);
}
// High priority to register after Toolbox.
add_action( 'admin_menu', 'dlck_lc_kit_menu', 99 );
add_action( 'admin_menu', 'dlck_export_settings' );
add_action( 'admin_menu', 'dlck_import_settings' );
add_action( 'admin_menu', 'dlck_export_diagnostics' );
add_action( 'admin_menu', 'dlck_restore_settings_snapshot' );

// Add Settings link on the Plugins list.
add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	static function ( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=lc_tweaks' ) ),
			esc_html__( 'Settings', 'divi-lc-kit' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}
);

function dlck_lc_kit_enqueue_scripts_admin( $hook ) {
	// Works for both Settings and Divi submenu (hook contains the page slug).
	if ( strpos( $hook, 'lc_tweaks' ) === false && strpos( $hook, 'divi_lc_kit' ) === false ) {
		return;
	}
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
wp_enqueue_media();
wp_enqueue_script( 'dlck_on-off-switch', DLCK_LC_KIT_PLUGIN_URI . '/assets/js/admin/on-off-switch.js', array( 'jquery' ) );
wp_enqueue_script( 'dlck_lc_kit_settings_admin_js', DLCK_LC_KIT_PLUGIN_URI . '/assets/js/admin/lc-kit-admin-scripts.js', array( 'jquery', 'wp-color-picker' ), '0.0.13', true );
wp_enqueue_style( 'dlck-admin-css', DLCK_LC_KIT_PLUGIN_URI . '/assets/css/admin/admin-lc-kit-styles.css', array(), '0.0.7' );
	wp_localize_script(
		'dlck_lc_kit_settings_admin_js',
		'dlck_admin',
		array(
			'nonce'                => wp_create_nonce( 'dlck_clear_cache_files' ),
			'lazy_nonce'           => wp_create_nonce( 'dlck_clear_lazy_cache' ),
			'scope_test_nonce'     => wp_create_nonce( 'dlck_scope_rules_test' ),
			'schema_preview_nonce' => wp_create_nonce( 'dlck_rank_math_schema_preview_base' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'dlck_lc_kit_enqueue_scripts_admin' );

// ---------------------------------------------------------------------
// Admin page (Tweaks + Woo Tweaks + Settings)
// ---------------------------------------------------------------------
function divi_lc_kit() {
	$allowed_tabs = array( 'tweaks', 'divi-tweaks', 'modules', 'woo-tweaks', 'settings', 'deprecated', 'maintenance' );
	$active_tab   = 'tweaks';
	$divi_active  = dlck_is_divi_theme_active();
	$wppt_exists  = file_exists( DLCK_LC_KIT_PLUGIN_DIR . 'functions/modules/wp-page-transition/wp-page-transition.php' );
	$show_modules = ! dlck_is_free_edition() && ( $divi_active || $wppt_exists );

	if ( isset( $_GET['tab'] ) ) {
		$requested_tab = sanitize_key( wp_unslash( $_GET['tab'] ) );
		if ( in_array( $requested_tab, $allowed_tabs, true ) ) {
			$active_tab = $requested_tab;
		}
	}

	if ( $active_tab === 'woo-tweaks' && ! dlck_is_woocommerce_active() ) {
		$active_tab = 'tweaks';
	}

	if ( $active_tab === 'divi-tweaks' && ! dlck_is_divi_theme_active() ) {
		$active_tab = 'tweaks';
	}

	if ( $active_tab === 'modules' && ! $show_modules ) {
		$active_tab = 'tweaks';
	}

	dlck_import_notifications();
	dlck_snapshot_notifications();

	$is_settings_updated         = false;
	$is_settings_updated_success = false;
	$preflight_messages          = array();
	$nonce                       = 'lc_tweaks_slug_nonce';
	$page_slug                   = 'lc_tweaks';
	$subform_type                = isset( $_POST['dlck_subform_type'] ) ? sanitize_key( wp_unslash( $_POST['dlck_subform_type'] ) ) : '';
	if ( $subform_type === '' ) {
		if ( isset( $_POST['dlck_export_submit'] ) ) {
			$subform_type = 'settings_export';
		} elseif ( isset( $_POST['dlck_import_submit'] ) ) {
			$subform_type = 'settings_import';
		} elseif ( isset( $_POST['dlck_diagnostics_submit'] ) ) {
			$subform_type = 'settings_diagnostics';
		} elseif ( isset( $_POST['dlck_restore_snapshot_submit'] ) ) {
			$subform_type = 'settings_restore_snapshot';
		} elseif ( isset( $_POST['dlck_network_policy_submit'] ) ) {
			$subform_type = 'settings_network_policy';
		} elseif ( isset( $_POST['dlck_restore_preset_submit'] ) ) {
			$subform_type = 'settings_restore_preset';
		} elseif ( isset( $_POST['dlck_apply_preset_submit'] ) ) {
			$subform_type = 'settings_apply_preset';
		}
	}
	$skip_save_subforms          = array( 'settings_export', 'settings_import', 'settings_diagnostics', 'settings_restore_snapshot' );

	if ( isset( $_POST[ $nonce ] ) && ! in_array( $subform_type, $skip_save_subforms, true ) ) {
		$is_settings_updated = true;
		if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce ] ) ), $nonce ) && current_user_can( 'manage_options' ) ) {
			$existing_settings = maybe_unserialize( get_option( 'dlck_lc_kit' ) );
			$dlck_lc_kit_array = is_array( $existing_settings ) ? $existing_settings : array();

			if ( $subform_type === 'settings_network_policy' ) {
				$network_policy_action = isset( $_POST['dlck_network_policy_action'] ) ? sanitize_key( wp_unslash( $_POST['dlck_network_policy_action'] ) ) : '';
				$network_policy_nonce  = isset( $_POST['dlck_network_policy_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['dlck_network_policy_nonce'] ) ) : '';

				if ( ! is_multisite() ) {
					$is_settings_updated_message = __( 'Multisite policy mode is only available on multisite installs.', 'divi-lc-kit' );
				} elseif ( ! current_user_can( 'manage_network_options' ) ) {
					$is_settings_updated_message = __( 'Only network administrators can update multisite policy settings.', 'divi-lc-kit' );
				} elseif ( $network_policy_action !== 'dlck_save_network_policy' || ! wp_verify_nonce( $network_policy_nonce, 'dlck_save_network_policy_nonce' ) ) {
					$is_settings_updated_message = __( 'Could not save multisite policy settings. Please refresh and try again.', 'divi-lc-kit' );
				} else {
					$policy_enabled      = isset( $_POST['dlck_network_policy_enabled'] ) ? '1' : '0';
					$allow_site_overrides = isset( $_POST['dlck_network_policy_allow_site_overrides'] ) ? '1' : '0';
					$sync_defaults       = isset( $_POST['dlck_network_policy_sync_defaults'] );
					$clear_local         = isset( $_POST['dlck_network_policy_clear_local_overrides'] );

					$network_defaults = function_exists( 'dlck_get_multisite_default_settings' )
						? dlck_get_multisite_default_settings()
						: array();

					if ( $sync_defaults || ( $policy_enabled === '1' && empty( $network_defaults ) ) ) {
						$source_settings = function_exists( 'dlck_get_effective_lc_kit_settings' )
							? dlck_get_effective_lc_kit_settings( true )
							: maybe_unserialize( get_option( 'dlck_lc_kit' ) );
						$source_settings = is_array( $source_settings ) ? $source_settings : array();
						$network_defaults = function_exists( 'dlck_filter_settings_array' )
							? dlck_filter_settings_array( $source_settings )
							: $source_settings;
					}

					update_site_option(
						'dlck_lc_kit_network_policy',
						array(
							'enabled'              => $policy_enabled,
							'allow_site_overrides' => $allow_site_overrides,
							'updated'              => time(),
							'source_blog_id'       => get_current_blog_id(),
						)
					);

					update_site_option( 'dlck_lc_kit_network_defaults', $network_defaults );

					if ( $clear_local ) {
						delete_option( 'dlck_lc_kit' );
					}

					if ( function_exists( 'dlck_clear_effective_lc_kit_settings_cache' ) ) {
						dlck_clear_effective_lc_kit_settings_cache();
					}

					if ( function_exists( 'dlck_rebuild_all_inline_caches' ) ) {
						dlck_rebuild_all_inline_caches();
					}
					if ( function_exists( 'dlck_sync_woo_session_cleanup_schedule' ) ) {
						dlck_sync_woo_session_cleanup_schedule();
					}

					$is_settings_updated_success = true;
					/* translators: %d: number of network default keys */
					$is_settings_updated_message = sprintf( __( 'Multisite policy updated. Network defaults now contain %d keys.', 'divi-lc-kit' ), count( $network_defaults ) );
				}
			} elseif ( $subform_type === 'settings_restore_preset' ) {
				if ( function_exists( 'dlck_multisite_policy_blocks_site_saves' ) && dlck_multisite_policy_blocks_site_saves() ) {
					$is_settings_updated_message = __( 'Preset restore is locked by multisite policy because per-site overrides are disabled.', 'divi-lc-kit' );
					$is_settings_updated_success = false;
				} else {
					$restore_action = isset( $_POST['dlck_restore_preset_action'] ) ? sanitize_key( wp_unslash( $_POST['dlck_restore_preset_action'] ) ) : '';
					$restore_nonce  = isset( $_POST['dlck_restore_preset_nonce'] ) ? sanitize_key( wp_unslash( $_POST['dlck_restore_preset_nonce'] ) ) : '';
					$restore_data   = dlck_get_preset_restore_payload();

					if ( $restore_action !== 'dlck_restore_preset' || ! wp_verify_nonce( $restore_nonce, 'dlck_restore_preset_nonce' ) ) {
						$is_settings_updated_message = __( 'Could not restore preset backup. Please refresh and try again.', 'divi-lc-kit' );
					} elseif ( empty( $restore_data ) || ! array_key_exists( 'settings', $restore_data ) || ! is_array( $restore_data['settings'] ) ) {
						$is_settings_updated_message = __( 'No preset restore backup is available yet.', 'divi-lc-kit' );
					} else {
						$restored_settings  = dlck_normalize_scope_rules_settings( $restore_data['settings'] );
						$preflight_messages = dlck_get_preflight_conflicts( $restored_settings );
						$restored_settings  = dlck_enforce_mutually_exclusive_options( $restored_settings, array() );

						$changed = ( ! is_array( $existing_settings ) || $existing_settings !== $restored_settings );
						if ( $changed ) {
							if ( function_exists( 'dlck_store_settings_snapshot' ) && is_array( $existing_settings ) ) {
								dlck_store_settings_snapshot( $existing_settings, 'pre_restore_backup' );
							}

							update_option( 'dlck_lc_kit', $restored_settings );
							if ( function_exists( 'dlck_rebuild_all_inline_caches' ) ) {
								dlck_rebuild_all_inline_caches();
							}
						}

						update_option( 'dlck_last_applied_preset', '', false );
						dlck_clear_preset_restore_payload();

						$is_settings_updated_success = true;
						$is_settings_updated_message = $changed
							? __( 'Preset restore completed.', 'divi-lc-kit' )
							: __( 'Preset restore backup already matches current settings.', 'divi-lc-kit' );
					}
				}
			} elseif ( $subform_type === 'settings_apply_preset' ) {
				if ( function_exists( 'dlck_multisite_policy_blocks_site_saves' ) && dlck_multisite_policy_blocks_site_saves() ) {
					$is_settings_updated_message = __( 'Preset apply is locked by multisite policy because per-site overrides are disabled.', 'divi-lc-kit' );
					$is_settings_updated_success = false;
				} else {
					$preset_action = isset( $_POST['dlck_apply_preset_action'] ) ? sanitize_key( wp_unslash( $_POST['dlck_apply_preset_action'] ) ) : '';
					$preset_nonce  = isset( $_POST['dlck_apply_preset_nonce'] ) ? sanitize_key( wp_unslash( $_POST['dlck_apply_preset_nonce'] ) ) : '';
					$preset_id     = isset( $_POST['dlck_preset_key'] ) ? sanitize_key( wp_unslash( $_POST['dlck_preset_key'] ) ) : '';
					$preset_data   = dlck_get_settings_preset( $preset_id );

					if ( $preset_action !== 'dlck_apply_preset' || ! wp_verify_nonce( $preset_nonce, 'dlck_apply_preset_nonce' ) ) {
						$is_settings_updated_message = __( 'Could not apply preset. Please refresh and try again.', 'divi-lc-kit' );
					} elseif ( empty( $preset_data ) || ! is_array( $preset_data ) ) {
						$is_settings_updated_message = __( 'Please choose a valid preset before applying.', 'divi-lc-kit' );
					} else {
						$preset_settings = isset( $preset_data['settings'] ) && is_array( $preset_data['settings'] ) ? $preset_data['settings'] : array();

						foreach ( $preset_settings as $key => $value ) {
							if ( ! is_string( $key ) || strpos( $key, 'dlck_' ) !== 0 ) {
								continue;
							}
							$dlck_lc_kit_array[ $key ] = is_scalar( $value ) ? (string) $value : '';
						}

						$dlck_lc_kit_array  = dlck_normalize_scope_rules_settings( $dlck_lc_kit_array );
						$preflight_messages = dlck_get_preflight_conflicts( $dlck_lc_kit_array );
						$dlck_lc_kit_array  = dlck_enforce_mutually_exclusive_options( $dlck_lc_kit_array, array() );

						$changed = ( ! is_array( $existing_settings ) || $existing_settings !== $dlck_lc_kit_array );
						if ( $changed ) {
							if ( function_exists( 'dlck_store_settings_snapshot' ) && is_array( $existing_settings ) ) {
								dlck_store_settings_snapshot( $existing_settings, 'preset_apply' );
							}

							$preset_label = isset( $preset_data['label'] ) && is_string( $preset_data['label'] ) ? $preset_data['label'] : $preset_id;
							dlck_store_preset_restore_payload( is_array( $existing_settings ) ? $existing_settings : array(), $preset_id, $preset_label );

							update_option( 'dlck_lc_kit', $dlck_lc_kit_array );
							if ( function_exists( 'dlck_rebuild_all_inline_caches' ) ) {
								dlck_rebuild_all_inline_caches();
							}
						}

						update_option( 'dlck_last_applied_preset', $preset_id, false );

						$is_settings_updated_success = true;
						$preset_label               = isset( $preset_data['label'] ) && is_string( $preset_data['label'] ) ? $preset_data['label'] : $preset_id;
						$is_settings_updated_message = $changed
							? sprintf( __( 'Preset applied: %s.', 'divi-lc-kit' ), $preset_label )
							: sprintf( __( 'Preset already active: %s.', 'divi-lc-kit' ), $preset_label );
					}
				}
			} else {
				if ( function_exists( 'dlck_multisite_policy_blocks_site_saves' ) && dlck_multisite_policy_blocks_site_saves() ) {
					$is_settings_updated_success = false;
					$is_settings_updated_message = __( 'Per-site setting updates are locked by multisite policy. Ask a network administrator to enable site overrides.', 'divi-lc-kit' );
				} else {
					foreach ( $_POST as $key => $value ) {
						if ( ! is_string( $key ) ) {
							continue;
						}
						if ( $key === $nonce || $key === 'save' ) {
							continue;
						}
						if ( strpos( $key, 'dlck_' ) !== 0 ) {
							continue;
						}
						if ( $key === 'dlck_rank_math_schema_advanced_json' ) {
							$advanced_json_value = wp_unslash( $value );
							if ( ! dlck_is_valid_rank_math_schema_advanced_json( $advanced_json_value ) ) {
								$preflight_messages[] = __( 'Advanced JSON Merge was not updated because it must be a valid top-level JSON object. The previously saved value was kept.', 'divi-lc-kit' );
								continue;
							}
						}
						$dlck_lc_kit_array[ $key ] = wp_unslash( $value );
					}
					$dlck_lc_kit_array  = dlck_normalize_scope_rules_settings( $dlck_lc_kit_array );
					$preflight_messages = dlck_get_preflight_conflicts( $dlck_lc_kit_array );
					// Enforce YT mutual exclusivity on manual saves too.
					$dlck_lc_kit_array = dlck_enforce_mutually_exclusive_options( $dlck_lc_kit_array, array() );
					$lc_settings_changed = ! is_array( $existing_settings ) || $existing_settings !== $dlck_lc_kit_array;

					if ( function_exists( 'dlck_store_settings_snapshot' ) && is_array( $existing_settings ) && $lc_settings_changed ) {
						dlck_store_settings_snapshot( $existing_settings, 'manual_save' );
					}

					update_option( 'dlck_lc_kit', $dlck_lc_kit_array );
					$is_settings_updated_success = true;
					if ( $lc_settings_changed ) {
						$is_settings_updated_message = __( 'LC Tweaks settings updated.', 'divi-lc-kit' );
					} else {
						$is_settings_updated_message = __( 'No settings changes detected.', 'divi-lc-kit' );
					}

					// Immediately rebuild inline asset caches when settings change.
					if ( function_exists( 'dlck_rebuild_all_inline_caches' ) ) {
						dlck_rebuild_all_inline_caches();
					}
				}
			}
		} else {
			$is_settings_updated_message = __( 'Error authenticating request. Please try again.', 'divi-lc-kit' );
		}
	}
	?>
	<?php if ( $is_settings_updated ) : ?>
		<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible <?php echo $is_settings_updated_success ? '' : 'error'; ?>">
			<p><strong><?php echo esc_html( $is_settings_updated_message ); ?></strong></p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'divi-lc-kit' ); ?></span>
			</button>
		</div>
	<?php endif; ?>
	<?php if ( $is_settings_updated_success && ! empty( $preflight_messages ) ) : ?>
		<div class="notice notice-warning is-dismissible">
			<p><strong><?php echo esc_html__( 'Preflight check notes:', 'divi-lc-kit' ); ?></strong></p>
			<ul style="list-style:disc;padding-left:20px;">
				<?php foreach ( $preflight_messages as $message ) : ?>
					<li><?php echo esc_html( $message ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
	<div id="dlck">
		<div class="lc-kit-head">
			<h1 id="divi-lc-kit-title">LC Tweaks</h1>
			<div>
				<a class="t-sett" href="?page=<?php echo esc_attr( $page_slug ); ?>&tab=settings">Settings</a>
			</div>
		</div>
		<h2 class="nav-tab-wrapper">
				<a href="?page=<?php echo esc_attr( $page_slug ); ?>&tab=tweaks" class="nav-tab <?php echo $active_tab === 'tweaks' ? 'nav-tab-active' : ''; ?>">WordPress</a>
				<?php if ( $divi_active ) : ?>
					<a href="?page=<?php echo esc_attr( $page_slug ); ?>&tab=divi-tweaks" class="nav-tab <?php echo $active_tab === 'divi-tweaks' ? 'nav-tab-active' : ''; ?>">Divi</a>
				<?php endif; ?>
				<?php if ( $show_modules ) : ?>
					<a href="?page=<?php echo esc_attr( $page_slug ); ?>&tab=modules" class="nav-tab <?php echo $active_tab === 'modules' ? 'nav-tab-active' : ''; ?>">Modules</a>
				<?php endif; ?>
				<?php if ( dlck_is_woocommerce_active() ) : ?>
					<a href="?page=<?php echo esc_attr( $page_slug ); ?>&tab=woo-tweaks" class="nav-tab <?php echo $active_tab === 'woo-tweaks' ? 'nav-tab-active' : ''; ?>">WooCommerce</a>
				<?php endif; ?>
				<a href="?page=<?php echo esc_attr( $page_slug ); ?>&tab=maintenance" class="nav-tab <?php echo $active_tab === 'maintenance' ? 'nav-tab-active' : ''; ?>">Maintenance</a>
			</h2>

		<form action="" method="POST" class="et-divi-lc-kit-form" id="dlck_settings_form" enctype="multipart/form-data">
			<div class="page-container">
				<div class="dlck-loader">
					<div class="status">
						<!-- Placeholder loader -->
					</div>
				</div>
				<?php
				switch ( $active_tab ) {
					case 'divi-tweaks':
						if ( $divi_active ) {
							include_once DLCK_LC_KIT_PLUGIN_DIR . '/tools/divi-tweaks.php';
						}
						break;
					case 'modules':
						if ( $show_modules ) {
							include_once DLCK_LC_KIT_PLUGIN_DIR . '/tools/modules.php';
						}
						break;
					case 'woo-tweaks':
						if ( dlck_is_woocommerce_active() ) {
							include_once DLCK_LC_KIT_PLUGIN_DIR . '/tools/woo-tweaks.php';
							include_once DLCK_LC_KIT_PLUGIN_DIR . '/tools/woocommerce.php';
						}
						break;
					case 'settings':
						include_once DLCK_LC_KIT_PLUGIN_DIR . '/tools/settings.php';
						break;
					case 'deprecated':
						include_once DLCK_LC_KIT_PLUGIN_DIR . '/tools/deprecated.php';
						break;
					case 'maintenance':
						include_once DLCK_LC_KIT_PLUGIN_DIR . '/tools/maintenance.php';
						break;
					case 'tweaks':
					default:
						include_once DLCK_LC_KIT_PLUGIN_DIR . '/tools/tweaks.php';
						break;
				}
				?>
			</div>
			<div id="et-epanel-bottom">
				<?php
				wp_nonce_field( $nonce, $nonce );
				printf(
					'<button class="et-save-button" name="save" id="dlck-epanel-save">%s</button>',
					esc_html( 'Save Settings' )
				);
				?>
			</div>
		</form>
	</div>
	<div class="foot-links">
		<a href="?page=divi_lc_kit&tab=settings"><?php esc_html_e( 'Plugin Settings', 'divi-lc-kit' ); ?></a>
		<a href="?page=divi_lc_kit&tab=deprecated"><?php esc_html_e( 'Deprecated', 'divi-lc-kit' ); ?></a>
		<a href="<?php echo esc_attr( DLCK_URL_SUPPORT ); ?>" target="_blank"><?php esc_html_e( 'Support', 'divi-lc-kit' ); ?></a>
	</div>
	<?php
}

// ---------------------------------------------------------------------
// Load tweaks/Woo tweaks on front-end
// ---------------------------------------------------------------------
/**
 * Parse newline-separated scope rule values.
 *
 * @param mixed $raw Raw option value.
 * @return string[]
 */
function dlck_scope_rules_parse_lines( $raw ): array {
	if ( ! is_string( $raw ) || $raw === '' ) {
		return array();
	}

	$lines = preg_split( '/\r\n|\r|\n/', $raw );
	if ( ! is_array( $lines ) ) {
		return array();
	}

	$lines = array_map(
		static function ( $line ) {
			return trim( (string) $line );
		},
		$lines
	);

	$lines = array_filter(
		$lines,
		static function ( $line ) {
			return $line !== '';
		}
	);

	return array_values( array_unique( $lines ) );
}

/**
 * Return option keys currently wired into scope-aware runtime loading.
 *
 * @return string[]
 */
function dlck_get_registered_option_keys(): array {
	static $keys = null;

	if ( is_array( $keys ) ) {
		return $keys;
	}

	$keys = array();

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	// phpcs:ignore PHPCS_SecurityAudit.BadFunctions.FilesystemFunctions.WarnFilesystem
	$contents = file_get_contents( __FILE__ );
	if ( ! is_string( $contents ) || $contents === '' ) {
		return $keys;
	}

	$patterns = array(
		"/'(?P<key>dlck_[a-z0-9_]+)'\\s*=>\\s*'[^']+'/i",
		"/dlck_include_if_enabled\\(\\s*'(?P<key>dlck_[a-z0-9_]+)'/i",
		"/dlck_scope_rules_allow_option\\(\\s*'(?P<key>dlck_[a-z0-9_]+)'/i",
	);

	foreach ( $patterns as $pattern ) {
		if ( ! preg_match_all( $pattern, $contents, $matches ) ) {
			continue;
		}
		foreach ( (array) $matches['key'] as $match ) {
			if ( ! is_string( $match ) ) {
				continue;
			}
			$keys[] = sanitize_key( $match );
		}
	}

	$keys = array_values( array_unique( array_filter( $keys ) ) );
	sort( $keys );

	return $keys;
}

/**
 * Normalize scope rule settings before persistence.
 *
 * @param array $settings Settings candidate.
 * @return array
 */
function dlck_normalize_scope_rules_settings( array $settings ): array {
	$scope_keys = array(
		'dlck_scope_rules_enabled',
		'dlck_scope_rules_options',
		'dlck_scope_rules_logged_state',
		'dlck_scope_rules_roles',
		'dlck_scope_rules_include_paths',
		'dlck_scope_rules_exclude_paths',
	);

	$has_scope_rules_data = false;
	foreach ( $scope_keys as $key ) {
		if ( array_key_exists( $key, $settings ) ) {
			$has_scope_rules_data = true;
			break;
		}
	}
	if ( ! $has_scope_rules_data ) {
		return $settings;
	}

	$settings['dlck_scope_rules_enabled'] = ! empty( $settings['dlck_scope_rules_enabled'] ) && (string) $settings['dlck_scope_rules_enabled'] !== '0' ? '1' : '0';

	$option_keys = dlck_scope_rules_parse_lines( (string) ( $settings['dlck_scope_rules_options'] ?? '' ) );
	$option_keys = array_map( 'sanitize_key', $option_keys );
	$option_keys = array_filter(
		$option_keys,
		static function ( $key ) {
			return strpos( $key, 'dlck_' ) === 0 && dlck_edition_allows_option( $key );
		}
	);
	$settings['dlck_scope_rules_options'] = implode( "\n", array_values( array_unique( $option_keys ) ) );

	$logged_state = sanitize_key( (string) ( $settings['dlck_scope_rules_logged_state'] ?? 'all' ) );
	if ( ! in_array( $logged_state, array( 'all', 'logged_in', 'logged_out' ), true ) ) {
		$logged_state = 'all';
	}
	$settings['dlck_scope_rules_logged_state'] = $logged_state;

	$roles = dlck_scope_rules_parse_lines( (string) ( $settings['dlck_scope_rules_roles'] ?? '' ) );
	$roles = array_map( 'sanitize_key', $roles );
	$roles = array_filter( $roles );
	$settings['dlck_scope_rules_roles'] = implode( "\n", array_values( array_unique( $roles ) ) );

	$normalize_patterns = static function ( string $raw ): string {
		$patterns = dlck_scope_rules_parse_lines( $raw );
		$patterns = array_map(
			static function ( $pattern ) {
				return dlck_scope_rules_normalize_path( sanitize_text_field( (string) $pattern ) );
			},
			$patterns
		);
		$patterns = array_values( array_unique( array_filter( $patterns ) ) );
		return implode( "\n", $patterns );
	};

	$settings['dlck_scope_rules_include_paths'] = $normalize_patterns( (string) ( $settings['dlck_scope_rules_include_paths'] ?? '' ) );
	$settings['dlck_scope_rules_exclude_paths'] = $normalize_patterns( (string) ( $settings['dlck_scope_rules_exclude_paths'] ?? '' ) );

	return $settings;
}

/**
 * Return scope rule-specific preflight warnings.
 *
 * @param array $settings Candidate settings array.
 * @return string[]
 */
function dlck_get_scope_rules_preflight_conflicts( array $settings ): array {
	if ( empty( $settings['dlck_scope_rules_enabled'] ) || (string) $settings['dlck_scope_rules_enabled'] !== '1' ) {
		return array();
	}

	$messages = array();

	$scope_options = dlck_scope_rules_parse_lines( (string) ( $settings['dlck_scope_rules_options'] ?? '' ) );
	$scope_options = array_map( 'sanitize_key', $scope_options );
	$scope_options = array_values(
		array_filter(
			$scope_options,
			static function ( $key ) {
				return strpos( $key, 'dlck_' ) === 0;
			}
		)
	);
	if ( empty( $scope_options ) ) {
		$messages[] = __( 'Scope Rules is enabled but no target option keys are listed. Add at least one option key to scope.', 'divi-lc-kit' );
	}

	$logged_state = sanitize_key( (string) ( $settings['dlck_scope_rules_logged_state'] ?? 'all' ) );
	$roles        = dlck_scope_rules_parse_lines( (string) ( $settings['dlck_scope_rules_roles'] ?? '' ) );
	$roles        = array_values( array_filter( array_map( 'sanitize_key', $roles ) ) );
	if ( $logged_state === 'logged_out' && ! empty( $roles ) ) {
		$messages[] = __( 'Scope Rules roles are set while "Logged-out users only" is selected. Role restrictions only apply to logged-in users.', 'divi-lc-kit' );
	}

	$include_patterns = dlck_scope_rules_parse_lines( (string) ( $settings['dlck_scope_rules_include_paths'] ?? '' ) );
	$exclude_patterns = dlck_scope_rules_parse_lines( (string) ( $settings['dlck_scope_rules_exclude_paths'] ?? '' ) );

	$normalize_patterns = static function ( array $patterns ): array {
		$patterns = array_map(
			static function ( $pattern ) {
				return dlck_scope_rules_normalize_path( (string) $pattern );
			},
			$patterns
		);
		return array_values( array_unique( array_filter( $patterns ) ) );
	};

	$include_patterns = $normalize_patterns( $include_patterns );
	$exclude_patterns = $normalize_patterns( $exclude_patterns );
	$overlap_patterns = array_values( array_intersect( $include_patterns, $exclude_patterns ) );
	if ( ! empty( $overlap_patterns ) ) {
		/* translators: %s: comma-separated path patterns */
		$messages[] = sprintf(
			__( 'Scope Rules has matching include/exclude patterns (%s). Exclude paths take priority.', 'divi-lc-kit' ),
			implode( ', ', array_slice( $overlap_patterns, 0, 5 ) )
		);
	}

	if ( $logged_state === 'all' && empty( $roles ) && empty( $include_patterns ) && empty( $exclude_patterns ) ) {
		$messages[] = __( 'Scope Rules is enabled with no path, role, or login-state restrictions. Targeted options will run everywhere.', 'divi-lc-kit' );
	}

	$known_option_keys = dlck_get_registered_option_keys();
	if ( ! empty( $scope_options ) && ! empty( $known_option_keys ) ) {
		$unknown_options = array_values( array_diff( $scope_options, $known_option_keys ) );
		if ( ! empty( $unknown_options ) ) {
			/* translators: %s: comma-separated option keys */
			$messages[] = sprintf(
				__( 'Scope Rules includes unknown option keys (%s). These entries will not affect runtime behavior until corrected.', 'divi-lc-kit' ),
				implode( ', ', array_slice( $unknown_options, 0, 5 ) )
			);
		}
	}

	return $messages;
}

/**
 * Normalize request path for scope matching.
 */
function dlck_scope_rules_normalize_path( string $path ): string {
	$path = trim( $path );
	if ( $path === '' ) {
		return '/';
	}

	if ( $path[0] !== '/' ) {
		$path = '/' . $path;
	}

	$normalized = untrailingslashit( $path );
	return $normalized === '' ? '/' : $normalized;
}

/**
 * Get current request path without query args.
 */
function dlck_scope_rules_current_request_path(): string {
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	$request_uri = is_string( $request_uri ) ? $request_uri : '/';
	$path        = wp_parse_url( $request_uri, PHP_URL_PATH );
	$path        = is_string( $path ) ? $path : '/';
	return dlck_scope_rules_normalize_path( $path );
}

/**
 * Check whether a wildcard path pattern matches the current request path.
 */
function dlck_scope_rules_pattern_matches_path( string $pattern, string $path ): bool {
	$pattern = dlck_scope_rules_normalize_path( $pattern );
	$path    = dlck_scope_rules_normalize_path( $path );
	$regex   = '#^' . str_replace( '\*', '.*', preg_quote( $pattern, '#' ) ) . '$#';

	if ( preg_match( $regex, $path ) ) {
		return true;
	}

	// Also allow matching trailing-slash variants.
	if ( $path !== '/' && preg_match( $regex, trailingslashit( $path ) ) ) {
		return true;
	}

	return false;
}

/**
 * Check if any wildcard patterns match a given path.
 *
 * @param string[] $patterns Wildcard paths.
 * @param string   $path     Request path.
 */
function dlck_scope_rules_patterns_match_path( array $patterns, string $path ): bool {
	foreach ( $patterns as $pattern ) {
		if ( ! is_string( $pattern ) || $pattern === '' ) {
			continue;
		}

		if ( dlck_scope_rules_pattern_matches_path( $pattern, $path ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Determine if scope rules should run for this request.
 */
function dlck_scope_rules_is_runtime_context(): bool {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return false;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}

	return true;
}

/**
 * Build normalized scope rule settings from saved options.
 *
 * @return array<string,mixed>
 */
function dlck_scope_rules_get_saved_settings(): array {
	$settings = array(
		'dlck_scope_rules_enabled'       => (string) dlck_get_option( 'dlck_scope_rules_enabled' ),
		'dlck_scope_rules_options'       => (string) dlck_get_option( 'dlck_scope_rules_options', '' ),
		'dlck_scope_rules_logged_state'  => (string) dlck_get_option( 'dlck_scope_rules_logged_state', 'all' ),
		'dlck_scope_rules_roles'         => (string) dlck_get_option( 'dlck_scope_rules_roles', '' ),
		'dlck_scope_rules_include_paths' => (string) dlck_get_option( 'dlck_scope_rules_include_paths', '' ),
		'dlck_scope_rules_exclude_paths' => (string) dlck_get_option( 'dlck_scope_rules_exclude_paths', '' ),
	);

	return dlck_normalize_scope_rules_settings( $settings );
}

/**
 * Evaluate scope-rule eligibility for an option with optional context overrides.
 *
 * @param string $option_name Option key to evaluate.
 * @param array  $context     Optional context overrides.
 * @return array<string,mixed>
 */
function dlck_scope_rules_evaluate_option( string $option_name, array $context = array() ): array {
	$option_name = sanitize_key( $option_name );
	$runtime_ok  = array_key_exists( 'runtime_context', $context ) ? (bool) $context['runtime_context'] : dlck_scope_rules_is_runtime_context();

	if ( ! $runtime_ok ) {
		return array(
			'allowed' => true,
			'code'    => 'runtime_skipped',
			'reason'  => __( 'Scope rules do not run in this request context.', 'divi-lc-kit' ),
		);
	}

	$scope_settings = isset( $context['scope_settings'] ) && is_array( $context['scope_settings'] )
		? dlck_normalize_scope_rules_settings( $context['scope_settings'] )
		: dlck_scope_rules_get_saved_settings();

	$rules_enabled = isset( $scope_settings['dlck_scope_rules_enabled'] ) && (string) $scope_settings['dlck_scope_rules_enabled'] === '1';
	if ( ! $rules_enabled ) {
		return array(
			'allowed' => true,
			'code'    => 'rules_disabled',
			'reason'  => __( 'Scope rules are disabled.', 'divi-lc-kit' ),
		);
	}

	$scoped_options = dlck_scope_rules_parse_lines( (string) ( $scope_settings['dlck_scope_rules_options'] ?? '' ) );
	$scoped_options = array_map( 'sanitize_key', $scoped_options );
	$scoped_options = array_values(
		array_filter(
			$scoped_options,
			static function ( $key ) {
				return strpos( $key, 'dlck_' ) === 0;
			}
		)
	);

	if ( empty( $scoped_options ) ) {
		return array(
			'allowed' => true,
			'code'    => 'no_targets',
			'reason'  => __( 'Scope rules are enabled but no target option keys are listed.', 'divi-lc-kit' ),
		);
	}

	if ( ! in_array( $option_name, $scoped_options, true ) ) {
		return array(
			'allowed' => true,
			'code'    => 'option_not_targeted',
			'reason'  => __( 'Option key is not listed in Scope Rules targets.', 'divi-lc-kit' ),
		);
	}

	$logged_state = sanitize_key( (string) ( $scope_settings['dlck_scope_rules_logged_state'] ?? 'all' ) );
	if ( ! in_array( $logged_state, array( 'all', 'logged_in', 'logged_out' ), true ) ) {
		$logged_state = 'all';
	}

	$is_logged_in = array_key_exists( 'is_logged_in', $context ) ? (bool) $context['is_logged_in'] : is_user_logged_in();
	if ( $logged_state === 'logged_in' && ! $is_logged_in ) {
		return array(
			'allowed' => false,
			'code'    => 'requires_logged_in',
			'reason'  => __( 'Rule requires a logged-in visitor.', 'divi-lc-kit' ),
		);
	}
	if ( $logged_state === 'logged_out' && $is_logged_in ) {
		return array(
			'allowed' => false,
			'code'    => 'requires_logged_out',
			'reason'  => __( 'Rule requires a logged-out visitor.', 'divi-lc-kit' ),
		);
	}

	$role_rules = dlck_scope_rules_parse_lines( (string) ( $scope_settings['dlck_scope_rules_roles'] ?? '' ) );
	$role_rules = array_values( array_filter( array_map( 'sanitize_key', $role_rules ) ) );
	$user_roles = array();
	if ( isset( $context['user_roles'] ) && is_array( $context['user_roles'] ) ) {
		$user_roles = array_values( array_filter( array_map( 'sanitize_key', $context['user_roles'] ) ) );
	} elseif ( $is_logged_in ) {
		$user_roles = array_values( array_filter( array_map( 'sanitize_key', (array) wp_get_current_user()->roles ) ) );
	}

	if ( ! empty( $role_rules ) ) {
		if ( ! $is_logged_in ) {
			return array(
				'allowed' => false,
				'code'    => 'roles_require_logged_in',
				'reason'  => __( 'Role rules are set, but the visitor is logged out.', 'divi-lc-kit' ),
			);
		}
		if ( empty( array_intersect( $role_rules, $user_roles ) ) ) {
			return array(
				'allowed' => false,
				'code'    => 'role_mismatch',
				'reason'  => __( 'Visitor roles do not match configured role rules.', 'divi-lc-kit' ),
			);
		}
	}

	$request_path = isset( $context['request_path'] ) && is_string( $context['request_path'] )
		? dlck_scope_rules_normalize_path( (string) $context['request_path'] )
		: dlck_scope_rules_current_request_path();

	$include_rules = dlck_scope_rules_parse_lines( (string) ( $scope_settings['dlck_scope_rules_include_paths'] ?? '' ) );
	if ( ! empty( $include_rules ) && ! dlck_scope_rules_patterns_match_path( $include_rules, $request_path ) ) {
		return array(
			'allowed' => false,
			'code'    => 'include_no_match',
			/* translators: 1: request path, 2: include patterns */
			'reason'  => sprintf( __( 'Path %1$s did not match include rules (%2$s).', 'divi-lc-kit' ), $request_path, implode( ', ', array_slice( $include_rules, 0, 5 ) ) ),
		);
	}

	$exclude_rules = dlck_scope_rules_parse_lines( (string) ( $scope_settings['dlck_scope_rules_exclude_paths'] ?? '' ) );
	if ( ! empty( $exclude_rules ) && dlck_scope_rules_patterns_match_path( $exclude_rules, $request_path ) ) {
		return array(
			'allowed' => false,
			'code'    => 'exclude_match',
			/* translators: 1: request path, 2: exclude patterns */
			'reason'  => sprintf( __( 'Path %1$s matched exclude rules (%2$s).', 'divi-lc-kit' ), $request_path, implode( ', ', array_slice( $exclude_rules, 0, 5 ) ) ),
		);
	}

	return array(
		'allowed'      => true,
		'code'         => 'allowed',
		'reason'       => __( 'Path and visitor conditions passed.', 'divi-lc-kit' ),
		'request_path' => $request_path,
		'user_roles'   => $user_roles,
	);
}

/**
 * AJAX tester for Scope Rules UI.
 */
function dlck_scope_rules_test_ajax(): void {
	check_ajax_referer( 'dlck_scope_rules_test', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'You do not have permission to run this test.', 'divi-lc-kit' ), 403 );
	}

	$option_name  = isset( $_POST['option_name'] ) ? sanitize_key( wp_unslash( $_POST['option_name'] ) ) : '';
	$request_path = isset( $_POST['request_path'] ) ? sanitize_text_field( wp_unslash( $_POST['request_path'] ) ) : '/';
	$user_state   = isset( $_POST['user_state'] ) ? sanitize_key( wp_unslash( $_POST['user_state'] ) ) : 'logged_out';
	$user_roles   = isset( $_POST['user_roles'] ) ? sanitize_textarea_field( wp_unslash( $_POST['user_roles'] ) ) : '';

	if ( $option_name === '' || strpos( $option_name, 'dlck_' ) !== 0 ) {
		wp_send_json_error( __( 'Enter a valid LC Tweaks option key (dlck_*).', 'divi-lc-kit' ) );
	}

	if ( ! in_array( $user_state, array( 'logged_in', 'logged_out' ), true ) ) {
		$user_state = 'logged_out';
	}

	$role_lines = preg_split( '/\r\n|\r|\n|,/', (string) $user_roles );
	$roles      = array();
	if ( is_array( $role_lines ) ) {
		$roles = array_values( array_filter( array_map( 'sanitize_key', array_map( 'trim', $role_lines ) ) ) );
	}

	$evaluation = dlck_scope_rules_evaluate_option(
		$option_name,
		array(
			'runtime_context' => true,
			'is_logged_in'    => $user_state === 'logged_in',
			'user_roles'      => $roles,
			'request_path'    => $request_path,
		)
	);

	$normalized_path = isset( $evaluation['request_path'] ) && is_string( $evaluation['request_path'] )
		? $evaluation['request_path']
		: dlck_scope_rules_normalize_path( (string) $request_path );
	$state_label = $user_state === 'logged_in' ? __( 'logged in', 'divi-lc-kit' ) : __( 'logged out', 'divi-lc-kit' );

	/* translators: 1: option key, 2: request path, 3: user state */
	$summary = sprintf( __( 'Option: %1$s | Path: %2$s | User: %3$s', 'divi-lc-kit' ), $option_name, $normalized_path, $state_label );
	if ( $user_state === 'logged_in' && ! empty( $roles ) ) {
		/* translators: %s: comma-separated roles */
		$summary .= "\n" . sprintf( __( 'Roles: %s', 'divi-lc-kit' ), implode( ', ', $roles ) );
	}

	wp_send_json_success(
		array(
			'allowed' => ! empty( $evaluation['allowed'] ),
			'summary' => $summary,
			'reason'  => isset( $evaluation['reason'] ) ? (string) $evaluation['reason'] : '',
		)
	);
}
add_action( 'wp_ajax_dlck_scope_rules_test', 'dlck_scope_rules_test_ajax' );

/**
 * Decide if a specific option is allowed under Scope Rules.
 */
function dlck_scope_rules_allow_option( string $option_name ): bool {
	$evaluation = dlck_scope_rules_evaluate_option( $option_name );
	return ! empty( $evaluation['allowed'] );
}

function dlck_include_if_enabled( string $option_name, string $relative_file ) {
	if ( ! dlck_edition_allows_option( $option_name ) ) {
		return;
	}
	if ( dlck_get_option( $option_name ) !== '1' ) {
		return;
	}
	if ( dlck_builder_safe_mode_blocks_option( $option_name ) ) {
		return;
	}
	if ( ! dlck_scope_rules_allow_option( $option_name ) ) {
		return;
	}
	$path = DLCK_LC_KIT_PLUGIN_DIR . $relative_file;
	if ( file_exists( $path ) ) {
		include_once $path;
	}
}

function dlck_load_active_tweaks() {
	// WordPress/global tweaks.
	$wp_tweaks = array(
		'dlck_antispam_email_shortcode'                  => 'functions/tweaks/common/antispam-email-shortcode.php',
		'dlck_footer_date_shortcode'                     => 'functions/tweaks/common/footer-date-shortcode.php',
		'dlck_make_phone_number_click_to_call'           => 'functions/tweaks/common/make-phone-number-click-to-call.php',
		'dlck_rank_math_schema_enrichment'               => 'functions/tweaks/rank-math-schema-enrichment.php',
		'dlck_svg_uploads'                               => 'functions/tweaks/svg-support.php',
		'dlck_json_uploads'                              => 'functions/tweaks/json-support.php',
		'dlck_ttf_uploads'                               => 'functions/tweaks/font-support.php',
		'dlck_disable_rss_feed'                          => 'functions/web-performance/disable-rss-feed.php',
		'dlck_disable_wp_search'                         => 'functions/web-performance/disable-wp-search.php',
		'dlck_disable_gutenberg'                         => 'functions/web-performance/disable-gutenberg.php',
		'dlck_disable_all_comments'                      => 'functions/tweaks/disable-all-comments.php',
		'dlck_disable_plugin_auto_updates'               => 'functions/tweaks/disable-plugin-auto-updates.php',
		'dlck_disable_theme_auto_updates'                => 'functions/tweaks/disable-theme-auto-updates.php',
		'dlck_allow_unfiltered_uploads'                  => 'functions/tweaks/allow-unfiltered-uploads.php',
		'dlck_replace_image_tool'                        => 'functions/tweaks/replace-image-tool.php',
		'dlck_core_upgrade_skip_new_bundled'             => 'functions/tweaks/core-upgrade-skip-new-bundled.php',
		'dlck_wp_auto_update_core'                       => 'functions/tweaks/wp-auto-update-core.php',
		'dlck_body_class_user_role'                      => 'functions/tweaks/body-class-user-role.php',
		'dlck_disable_admin_new_user_notification_emails'=> 'functions/tweaks/common/disable-admin-new-user-notification-emails.php',
		'dlck_hide_dashboard_welcome_panel'              => 'functions/tweaks/remove-dashboard-welcome-panel.php',
		'dlck_kill_jetpack_cron'                         => 'functions/tweaks/kill-Jetpack-cron.php',
		'dlck_speedup_scheduled_actions'                 => 'functions/tweaks/speedup-scheduled-actions.php',
		'dlck_wprocket_force_page_caching'               => 'functions/web-performance/wp-rocket-cache-donotcachepage.php',
		'dlck_wprocket_cache_wp_rest_api'                => 'functions/web-performance/wp-rocket-cache-rest-api.php',
		'dlck_wprocket_disable_above_fold_opt'           => 'functions/web-performance/wp-rocket-disable-above-the-fold-optimization.php',
		'dlck_wprocket_disable_priority_elements'        => 'functions/web-performance/wp-rocket-disable-priority-elements.php',
		'dlck_exactdn_image_downsize_scale'              => 'functions/web-performance/exactdn-image-downsize-scale.php',
		'dlck_all_wp_settings_page'                      => 'functions/tweaks/all-wp-settings-page.php',
		'dlck_disable_block_editor_from_managing_widgets'=> 'functions/tweaks/common/disables-the-block-editor-from-managing-widgets.php',
	);

	foreach ( $wp_tweaks as $option => $file ) {
		dlck_include_if_enabled( $option, $file );
	}

	// WP Page Transitions should be available even without Divi.
	if ( dlck_get_option( 'dlck_divi_wp_page_transitions' ) === '1' ) {
		dlck_wppt_ensure_setup();
		dlck_include_if_enabled( 'dlck_divi_wp_page_transitions', 'functions/modules/wp-page-transition/wp-page-transition.php' );
	}

	if ( dlck_is_divi_theme_active() ) {
		if ( ! dlck_builder_safe_mode_is_active() ) {
			$divi_always_files = array(
				'functions/web-performance/clear-divi-static-css-cache-local-storage.php',
			);

			foreach ( $divi_always_files as $file ) {
				$path = DLCK_LC_KIT_PLUGIN_DIR . $file;
				if ( file_exists( $path ) ) {
					include_once $path;
				}
			}
		}

		$divi_tweaks = array(
			'dlck_divi_text_on_a_path'                 => 'functions/modules/divi-modules-text-on-a-path/divi-modules-text-on-a-path.php',
			'dlck_divi_content_intense'               => 'functions/modules/content-intense/divi-content-n10s.php',
			'dlck_dwd_map_extended'                    => 'functions/modules/divi-module-map-extended/load_custom_map_module.php',
			'dlck_dwd_custom_fullwidth_header_extended' => 'functions/modules/divi-module-fullwidth-header-extended/load-fullwidth-header-extended.php',
			'dlck_woo_dg_product_carousel'             => 'functions/modules/woo-dg-product-carousel/dg-product-carousel.php',
			'dlck_hide_divi_image_tooltip'             => 'functions/tweaks/divi-tweaks/hide-divi-image-img-tooltip.php',
			'dlck_fix_divi_flashing_content'           => 'functions/deprecated/fix-divi-flashing-content.php',
			'dlck_fix_divi_user_scalable'              => 'functions/tweaks/divi-tweaks/fix-divi-zooming-user-scalable.php',
			'dlck_enable_divi_builder_by_default'      => 'functions/deprecated/enable_divi_builder_by_default.php',
			'dlck_hide_gutenberg_std_editor_buttons'   => 'functions/deprecated/hide-the-gutenberg-and-standard-editor-buttons.php',
			'dlck_disable_premade_layouts'             => 'functions/divi-tweaks/divi-disable-premade-layouts/divi-disable-premade-layouts.php',
				'dlck_disable_upsells_divi_dashboard'      => 'functions/divi-tweaks/divi-disable-upsell-dashboard.php',
				'dlck_disable_divi_ai'                     => 'functions/divi-tweaks/divi-disable-ai.php',
				'dlck_stop_map_module_excerpts_loading'    => 'functions/divi-tweaks/stop-map-module-excerpts-loading.php',
				'dlck_hide_divi_cloud'                     => 'functions/divi-tweaks/hide-divi-cloud.php',
				'dlck_edit_in_visual_builder_link'         => 'functions/divi-tweaks/edit-in-visual-builder-row-action.php',
				'dlck_disable_plugin_check'                => 'functions/deprecated/divi-disable-plugin-check.php',
				'dlck_divi_library_view'                   => 'functions/divi-tweaks/divi-library-view.php',
				'dlck_divi_builder_quick_fixes'            => 'functions/deprecated/divi-builder-quick-fixes.php',
			'dlck_woff_uploads'                        => 'functions/divi-tweaks/woff-uploads.php',
			'dlck_divi_custom_icons'                   => 'functions/divi-tweaks/add-custom-icons.php',
			'dlck_divi_lazy_loading'                  => 'functions/divi-tweaks/lazy-load-divi-sections.php',
			'dlck_divi_lazy_defer_sections'           => 'functions/divi-tweaks/lazy-load-divi-sections.php',
			'dlck_full_width_divi_footer'              => 'functions/tweaks/divi-tweaks/full-width-footer.php',
			'dlck_sticky_footer'                       => 'functions/divi-tweaks/sticky-footer.php',
			'dlck_social_target'                       => 'functions/divi-tweaks/social-links-new-tab.php',
			'dlck_hide_projects'                       => 'functions/divi-tweaks/hide-projects.php',
			'dlck_move_sidebar_to_top_on_mobile'       => 'functions/tweaks/divi-tweaks/move-sidebar-to-top-on-mobile.php',
			'dlck_divi_fix_Anchor_links'               => 'functions/tweaks/divi-tweaks/divi_fix_Anchor_links.php',
			'dlck_copy_sender_contact_form'            => 'functions/tweaks/divi-tweaks/contact-form-copy-to-sender.php',
			'dlck_divi_accordions_closed_default'      => 'functions/tweaks/divi-tweaks/divi_accordions_closed_default.php',
			'dlck_disable_wordpress_image_sizes'       => 'functions/tweaks/images/disable-wordpress-image-sizes.php',
			'dlck_remove_divi_resize_image_gallery'    => 'functions/tweaks/images/remove-divi-resize-image-gallery.php',
			'dlck_remove_divi_resize_image_portfolio'  => 'functions/tweaks/images/remove-divi-resize-image-portfolio.php',
			'dlck_remove_divi_resize_image_post'       => 'functions/tweaks/images/remove-divi-resize-image-post.php',
			'dlck_stop_divi_image_crop_portfolio'      => 'functions/tweaks/images/stop-divi-image-crop-portfolio.php',
			'dlck_stop_divi_image_crop_gallery'        => 'functions/tweaks/images/stop-divi-image-crop-gallery.php',
			'dlck_stop_divi_image_crop_blog'           => 'functions/tweaks/images/stop-divi-image-crop-Blog.php',
			'dlck_divi_hide_related_video_suggestions' => 'functions/divi-tweaks/hide-related-youtube-video-suggestions.php',
			'dlck_divi_disable_related_video_suggestions' => 'functions/divi-tweaks/disable-related-youtube-video-suggestions.php',
			'dlck_divi_autoplay_video_on_hover'        => 'functions/divi-tweaks/autoplay-video-on-hover.php',
			'dlck_autoplay_videos_hide_controls'       => 'functions/divi-tweaks/autoplay_videos_hide_controls.php',
			'dlck_divi_fix_youtube_loading_height'     => 'functions/divi-tweaks/fix-youtube-loading-height.php',
		);

			foreach ( $divi_tweaks as $option => $file ) {
				dlck_include_if_enabled( $option, $file );
			}

			if ( dlck_get_option( 'dlck_tm_divi_shop_extended' ) === '1' && dlck_is_woocommerce_active() && ! dlck_builder_safe_mode_blocks_option( 'dlck_tm_divi_shop_extended' ) ) {
				$tm_shop_extended_file = DLCK_LC_KIT_PLUGIN_DIR . 'functions/modules/tm-divi-shop-extended/loadcommerce.php';
				if ( file_exists( $tm_shop_extended_file ) ) {
					include_once $tm_shop_extended_file;
				}
			}

			$dlck_maintenance_layout_val = dlck_get_option( 'dlck_maintenance_layout' );
			$dlck_maintenance_layout_id  = absint( $dlck_maintenance_layout_val );

		if ( $dlck_maintenance_layout_id && dlck_scope_rules_allow_option( 'dlck_maintenance_layout' ) && ! dlck_builder_safe_mode_is_active() && ! is_admin() && ! wp_doing_ajax() && ! is_customize_preview() ) {
			add_action(
				'template_redirect',
				static function () use ( $dlck_maintenance_layout_id ) {
					// Skip for logged-in users with admin access to avoid locking them out.
					if ( current_user_can( 'manage_options' ) ) {
						return;
					}
					$dlck_maintenance_layout_val = $dlck_maintenance_layout_id;
					if ( ! defined( 'DLCK_MAINTENANCE_LAYOUT_ACTIVE' ) ) {
						define( 'DLCK_MAINTENANCE_LAYOUT_ACTIVE', true );
					}
					include DLCK_LC_KIT_PLUGIN_DIR . 'functions/divi-tweaks/show-maintenance-comingsoon-or-notice-page.php';
					exit;
				}
			);
		}
	}

	// Woo tweaks.
	if ( dlck_is_woocommerce_active() ) {
		if (
			! dlck_builder_safe_mode_blocks_option( 'dlck_woo_cart_script_policy' )
			&& dlck_scope_rules_allow_option( 'dlck_woo_cart_script_policy' )
		) {
			$woo_cart_policy_file = DLCK_LC_KIT_PLUGIN_DIR . 'functions/woo-tweaks/woo-cart-script-policy.php';
			if ( file_exists( $woo_cart_policy_file ) ) {
				include_once $woo_cart_policy_file;
			}
		}

		$woo_tweaks = array(
			'dlck_woo_resave_all_products'                         => 'functions/woo-tweaks/woo-resave-all-products.php',
			'dlck_disable_woocommerce_admin'                       => 'functions/woo-tweaks/woo-disable-admin-package.php',
			'dlck_remove_woo_files'                                => 'functions/woo-tweaks/woo-remove-script-and-css-pages.php',
			'dlck_remove_woo_all_files'                            => 'functions/woo-tweaks/woo-remove-script-and-css-site.php',
			'dlck_remove_woo_block_files'                          => 'functions/woo-tweaks/woo-blocks-remove-script-and-css.php',
			'dlck_wp_rocket_side_cart_exclusion'                   => 'functions/woo-tweaks/woo-wp-rocket-side-cart-exclusion.php',
			'dlck_add_a_line_break_in_woocommerce_product_titles'  => 'functions/woo-tweaks/add-a-line-break-in-woocommerce-product-titles.php',
			'dlck_disable_checkout_field_autocomplete'             => 'functions/woo-tweaks/disable-checkout-field-autocomplete.php',
			'dlck_woo_restrict_store_to_logged_in'                 => 'functions/woo-tweaks/woo-restrict-store-to-logged-in.php',
			'dlck_wc_orders_admin_search_by_sku'                   => 'functions/woo-tweaks/wc-orders-admin-search-by-sku.php',
			'dlck_wc_orders_admin_user_role_column'                => 'functions/woo-tweaks/wc-orders-admin-user-role-column.php',
			'dlck_wp_admin_users_order_counts_column'              => 'functions/woo-tweaks/wp-admin-users-order-counts-column.php',
			'dlck_wc_products_admin_stock_status_column'           => 'functions/woo-tweaks/wc-products-admin-stock-status-column.php',
			'dlck_wc_products_last_edited_meta_and_columns'        => 'functions/woo-tweaks/wc-products-last-edited-meta-and-columns.php',
			'dlck_woo_gla_mc_sync_column'                           => 'functions/woo-tweaks/woo-gla-mc-sync.php',
			'dlck_woo_checkout_empty_defaults'                     => 'functions/woo-tweaks/woo-checkout-empty-defaults.php',
			'dlck_woo_city_label_suburb'                           => 'functions/woo-tweaks/woo-city-label-suburb.php',
			'dlck_notify_admin_when_a_new_customer_account_is_created' => 'functions/woo-tweaks/notify-admin-when-a-new-customer-account-is-created.php',
			'dlck_read_more_to_out_of_stock'                       => 'functions/woo-tweaks/read-more-to-out-of-stock.php',
			'dlck_woo_disable_reviews_tab'                         => 'functions/woo-tweaks/woo-disable-reviews-tab.php',
			'dlck_disable_woocommerce_brands_feature'              => 'functions/woo-tweaks/woo-disable-brands-feature.php',
			'dlck_stop_woo_menu_item_from_displaying_for_anyone_but_administrator' => 'functions/woo-tweaks/stop-woo-menu-item-from-displaying-for-anyone-but-administrator.php',
			'dlck_shop_single_column_on_mobile'                    => 'functions/woo-tweaks/woo-single-column-mobile.php',
			'dlck_woo_add_to_cart_button'                          => 'functions/woo-tweaks/woo-add-to-cart-button-on-archives.php',
			'dlck_shop_masonry_layout'                             => 'functions/woo-tweaks/woo-masonry-layout.php',
			'dlck_disable_ssl_curl_error_60_in_wpallimport'        => 'functions/woo-tweaks/woo-wpallimport-disable-ssl-curl.php',
			'dlck_yith_activator'                                  => 'functions/woo-tweaks/yith-activator.php',
			'dlck_woocommerce_hide_price_and_add_to_cart_for_logged_out_users' => 'functions/woo-tweaks/woo-hide-price-n-add-to-cart-logged-out.php',
			'dlck_move_labels_inside_inputs_woo_checkout'          => 'functions/woo-tweaks/woo-move-labels-inside-inputs-on-checkout.php',
			'dlck_woo_body_css_class_on_single_product'            => 'functions/woo-tweaks/woo-body-css-class-on-single-product.php',
			'dlck_woo_hide_custom_fields_metabox'                  => 'functions/woo-tweaks/woo-hide-custom-fields-metabox.php',
			'dlck_woo_refund_request_button'                       => 'functions/woo-tweaks/woo-request-refund-button.php',
			'dlck_woo_get_order_ids_by_product'                    => 'functions/woo-tweaks/woo-get-order-ids-by-product.php',
			'dlck_woo_buy_now_button'                              => 'functions/woo-tweaks/woo-buy-now-button.php',
			'dlck_woo_move_orders_menu_item'                       => 'functions/woo-tweaks/woo-move-orders-menu-item.php',
			'dlck_woo_store_admin_view'                            => 'functions/woo-tweaks/woo-store-admin-view.php',
			'dlck_woo_email_item_meta_tags'                        => 'functions/woo-tweaks/woo-email-item-meta-tags.php',
			'dlck_woo_email_product_name_symbols'                  => 'functions/woo-tweaks/woo-email-product-name-symbols.php',
			'dlck_woo_order_items_sort'                            => 'functions/woo-tweaks/woo-order-items-sort.php',
			'dlck_woo_redirect_empty_cat_pagination'               => 'functions/woo-tweaks/woo-redirect-empty-cat-pagination.php',
			'dlck_woo_complete_order_button'                       => 'functions/woo-tweaks/woo-complete-order-button.php',
			'dlck_woo_guest_checkout_existing_customers'           => 'functions/woo-tweaks/woo-guest-checkout-existing-customers.php',
			'dlck_woo_prevent_duplicate_orders'                    => 'functions/woo-tweaks/woo-prevent-duplicate-orders.php',
			'dlck_woo_hide_products_no_featured_image'             => 'functions/woo-tweaks/woo-hide-products-no-featured-image.php',
			'dlck_woo_sticky_product_update_button'                => 'functions/woo-tweaks/woo-sticky-product-update-button.php',
			'dlck_woo_filter_products_by_sale_status'              => 'functions/woo-tweaks/woo-filter-products-by-sale-status.php',
			'dlck_woo_simple_products_only'                        => 'functions/woo-tweaks/woo-simple-products-only.php',
			'dlck_woo_remove_payments_menu'                        => 'functions/woo-tweaks/woo-remove-payments-menu.php',
			'dlck_woo_hide_downloads_tab_no_downloads'             => 'functions/woo-tweaks/woo-hide-downloads-tab-no-downloads.php',
			'dlck_woo_order_history_meta_box'                      => 'functions/woo-tweaks/woo-order-history-meta-box.php',
			'dlck_woo_add_to_cart_click_counter'                   => 'functions/woo-tweaks/woo-add-to-cart-click-counter.php',
			'dlck_woo_remove_add_to_cart_param'                    => 'functions/woo-tweaks/woo-remove-add-to-cart-param.php',
			'dlck_woo_remove_tax_suffixes'                         => 'functions/woo-tweaks/woo-remove-tax-suffixes.php',
			'dlck_woo_cancelled_order_email_customer'              => 'functions/woo-tweaks/woo-cancelled-order-email-customer.php',
			'dlck_woo_send_pending_order_email'                    => 'functions/woo-tweaks/woo-send-pending-order-email.php',
			'dlck_woo_email_fatal_errors'                          => 'functions/woo-tweaks/woo-email-fatal-errors.php',
			'dlck_woo_set_gtin_from_sku'                            => 'functions/woo-tweaks/woo-set-gtin-from-sku.php',
			'dlck_woo_set_gtin_from_sku_gla'                        => 'functions/woo-tweaks/woo-set-gtin-from-sku-gla.php',
			'dlck_woo_logout_redirect_home'                        => 'functions/woo-tweaks/woo-logout-redirect-home.php',
			'dlck_woo_capitalize_product_titles'                   => 'functions/woo-tweaks/woo-capitalize-product-titles.php',
		);

		if ( dlck_get_option( 'dlck_disable_admin_new_user_notification_emails' ) === '1' ) {
			unset( $woo_tweaks['dlck_notify_admin_when_a_new_customer_account_is_created'] );
		}

			if (
				dlck_get_option( 'dlck_yith_activator' ) === '1'
				&& (
					dlck_get_option( 'dlck_yith_activator_exclude_membership' ) === '1'
					|| dlck_get_option( 'dlck_yith_activator_exclude_compare' ) === '1'
					|| dlck_get_option( 'dlck_yith_activator_exclude_wishlist' ) === '1'
				)
				&& function_exists( 'dlck_any_yith_plugins_active' )
				&& dlck_any_yith_plugins_active()
			) {
				require_once DLCK_LC_KIT_PLUGIN_DIR . 'functions/woo-tweaks/yith-helpers.php';
			}

		foreach ( $woo_tweaks as $option => $file ) {
			dlck_include_if_enabled( $option, $file );
		}
	}
}
add_action( 'plugins_loaded', 'dlck_load_active_tweaks', 20 );

/**
 * Return allowed WooCommerce session expiration presets.
 *
 * @return array<string,int>
 */
function dlck_get_allowed_woo_session_expirations(): array {
	return array(
		'86400'    => 86400,
		'172800'   => 172800,
		'604800'   => 604800,
		'1209600'  => 1209600,
		'1814400'  => 1814400,
		'2592000'  => 2592000,
	);
}

/**
 * Return validated WooCommerce session expiration seconds.
 *
 * @return int 0 when using WooCommerce default.
 */
function dlck_get_woo_session_expiration_seconds(): int {
	$setting = sanitize_key( (string) dlck_get_option( 'dlck_woo_session_expiration' ) );
	if ( $setting === '' ) {
		return 0;
	}

	$allowed = dlck_get_allowed_woo_session_expirations();
	if ( ! isset( $allowed[ $setting ] ) ) {
		return 0;
	}

	return (int) $allowed[ $setting ];
}

/**
 * Return cron hook name used for Woo session cleanup.
 */
function dlck_woo_session_cleanup_cron_hook(): string {
	return 'dlck_woo_session_cleanup_cron';
}

/**
 * Determine if Woo session cleanup scheduler should run.
 */
function dlck_should_schedule_woo_session_cleanup(): bool {
	if ( ! dlck_is_woocommerce_active() ) {
		return false;
	}

	return dlck_get_woo_session_expiration_seconds() > 0;
}

/**
 * Return cleanup recurrence based on configured Woo session expiration.
 */
function dlck_get_woo_session_cleanup_recurrence(): string {
	$expiration_seconds = dlck_get_woo_session_expiration_seconds();
	if ( $expiration_seconds > 0 && $expiration_seconds <= 172800 ) {
		return 'twicedaily';
	}

	return 'daily';
}

/**
 * Ensure Woo session cleanup cron event is scheduled (or removed) based on settings.
 */
function dlck_sync_woo_session_cleanup_schedule(): void {
	$hook = dlck_woo_session_cleanup_cron_hook();

	if ( ! dlck_should_schedule_woo_session_cleanup() ) {
		wp_clear_scheduled_hook( $hook );
		return;
	}

	$desired_recurrence = dlck_get_woo_session_cleanup_recurrence();
	$existing_event     = function_exists( 'wp_get_scheduled_event' ) ? wp_get_scheduled_event( $hook ) : null;

	if ( $existing_event && isset( $existing_event->schedule ) && $existing_event->schedule !== $desired_recurrence ) {
		wp_clear_scheduled_hook( $hook );
		$existing_event = null;
	}

	if ( ! $existing_event && ! wp_next_scheduled( $hook ) ) {
		wp_schedule_event( time() + 300, $desired_recurrence, $hook );
	}
}
add_action( 'init', 'dlck_sync_woo_session_cleanup_schedule', 25 );
add_action( 'update_option_dlck_lc_kit', 'dlck_sync_woo_session_cleanup_schedule', 20, 0 );

/**
 * Run scheduled cleanup for expired WooCommerce sessions.
 */
function dlck_run_woo_session_cleanup_cron(): void {
	$health = array(
		'last_run'      => time(),
		'status'        => 'ok',
		'deleted_count' => 0,
		'row_count'     => null,
		'expired_count' => null,
		'message'       => '',
	);

	if ( ! dlck_is_woocommerce_active() ) {
		$health['status']  = 'skipped';
		$health['message'] = __( 'WooCommerce is not active. Cleanup skipped.', 'divi-lc-kit' );
		update_option( 'dlck_woo_session_cleanup_health', $health, false );
		return;
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'woocommerce_sessions';
	$table_like = $wpdb->esc_like( $table_name );
	$table      = $wpdb->get_var(
		$wpdb->prepare(
			'SHOW TABLES LIKE %s',
			$table_like
		)
	);

	if ( ! is_string( $table ) || $table !== $table_name ) {
		$health['status']  = 'error';
		$health['message'] = __( 'WooCommerce sessions table was not found.', 'divi-lc-kit' );
		update_option( 'dlck_woo_session_cleanup_health', $health, false );
		return;
	}

	$now           = time();
	$deleted_count = $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$table_name} WHERE session_expiry < %d",
			$now
		)
	);

	if ( false === $deleted_count ) {
		$health['status']  = 'error';
		$health['message'] = __( 'Could not delete expired WooCommerce sessions.', 'divi-lc-kit' );
	} else {
		$health['deleted_count'] = (int) $deleted_count;
		$health['message']       = __( 'Expired WooCommerce sessions cleaned up successfully.', 'divi-lc-kit' );
	}

	$row_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
	if ( null !== $row_count ) {
		$health['row_count'] = (int) $row_count;
	}

	$expired_count = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table_name} WHERE session_expiry < %d",
			$now
		)
	);
	if ( null !== $expired_count ) {
		$health['expired_count'] = (int) $expired_count;
	}

	update_option( 'dlck_woo_session_cleanup_health', $health, false );
}
add_action( 'dlck_woo_session_cleanup_cron', 'dlck_run_woo_session_cleanup_cron' );

/**
 * Return Woo session cleanup scheduler + health snapshot for the settings UI.
 *
 * @return array<string,mixed>
 */
function dlck_get_woo_session_cleanup_health_snapshot(): array {
	$raw = get_option( 'dlck_woo_session_cleanup_health', array() );
	if ( ! is_array( $raw ) ) {
		$raw = array();
	}

	$next_run = wp_next_scheduled( dlck_woo_session_cleanup_cron_hook() );

	return array(
		'enabled'       => dlck_should_schedule_woo_session_cleanup(),
		'recurrence'    => dlck_get_woo_session_cleanup_recurrence(),
		'next_run'      => $next_run ? (int) $next_run : 0,
		'last_run'      => isset( $raw['last_run'] ) ? (int) $raw['last_run'] : 0,
		'status'        => isset( $raw['status'] ) ? sanitize_key( (string) $raw['status'] ) : 'unknown',
		'deleted_count' => isset( $raw['deleted_count'] ) ? (int) $raw['deleted_count'] : 0,
		'row_count'     => isset( $raw['row_count'] ) ? (int) $raw['row_count'] : null,
		'expired_count' => isset( $raw['expired_count'] ) ? (int) $raw['expired_count'] : null,
		'message'       => isset( $raw['message'] ) ? sanitize_text_field( (string) $raw['message'] ) : '',
	);
}

// Inline handlers for options without standalone files.
add_action(
	'plugins_loaded',
	static function () {
		if ( dlck_get_option( 'dlck_disable_image_scaling' ) === '1' && dlck_scope_rules_allow_option( 'dlck_disable_image_scaling' ) ) {
			add_filter( 'big_image_size_threshold', '__return_false' );
		}
		if ( dlck_get_option( 'dlck_restore_infinite_media_scrolling' ) === '1' && dlck_scope_rules_allow_option( 'dlck_restore_infinite_media_scrolling' ) ) {
			add_filter( 'media_library_infinite_scrolling', '__return_true' );
		}
		if ( ! dlck_is_woocommerce_active() ) {
			return;
		}
		if ( ! dlck_scope_rules_allow_option( 'dlck_woo_session_expiration' ) ) {
			return;
		}
		$expiration_value = dlck_get_woo_session_expiration_seconds();
		if ( $expiration_value <= 0 ) {
			return;
		}
		add_filter(
			'woocommerce_session_expiration',
			static function ( $expiration ) use ( $expiration_value ) {
				return $expiration_value;
			}
		);
		if ( dlck_get_option( 'dlck_woo_disable_persistent_cart' ) === '1' && dlck_scope_rules_allow_option( 'dlck_woo_disable_persistent_cart' ) ) {
			add_filter( 'woocommerce_persistent_cart_enabled', '__return_false' );
		}
	}
);

/**
 * Output mobile browser theme color meta tags when enabled.
 */
function dlck_output_mobile_theme_color_meta() {
	if ( dlck_get_option( 'dlck_mobile_theme_color_enable' ) !== '1' || ! dlck_scope_rules_allow_option( 'dlck_mobile_theme_color_enable' ) ) {
		return;
	}

	$color = dlck_get_option( 'dlck_mobile_theme_color' );
	if ( ! $color ) {
		$color = '#ffffff';
	}

	echo '<meta name="theme-color" content="' . esc_attr( $color ) . '">';
	echo '<meta name="msapplication-navbutton-color" content="' . esc_attr( $color ) . '">';
	echo '<meta name="apple-mobile-web-app-capable" content="yes">';
	echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">';
}
add_action( 'wp_head', 'dlck_output_mobile_theme_color_meta', 5 );

/**
 * Sync WP Page Transitions Advanced keys + seed table when module toggles.
 *
 * @param mixed $old_value Previous dlck_lc_kit option value.
 * @param mixed $new_value New dlck_lc_kit option value.
 */
function dlck_handle_wppt_toggle( $old_value, $new_value ) {
	$old_settings = is_string( $old_value ) ? maybe_unserialize( $old_value ) : $old_value;
	$new_settings = is_string( $new_value ) ? maybe_unserialize( $new_value ) : $new_value;
	$old          = is_array( $old_settings ) ? ( $old_settings['dlck_divi_wp_page_transitions'] ?? '' ) : '';
	$new          = is_array( $new_settings ) ? ( $new_settings['dlck_divi_wp_page_transitions'] ?? '' ) : '';
	$activated   = $new === '1' && $old !== '1';
	$deactivated = $old === '1' && $new !== '1';

	if ( $activated ) {
		// Ensure required WP Page Transitions credentials exist when toggled on.
		update_option( 'wppatr-user-key', 'richarddavis' );
		update_option( 'wppatr-api-key', '60a7f806df785083c7558592cf8015e6' );
		dlck_wppt_seed_table_from_sql();
	} elseif ( $deactivated ) {
		delete_option( 'wppatr-user-key' );
		delete_option( 'wppatr-api-key' );
	}
}
add_action( 'update_option_dlck_lc_kit', 'dlck_handle_wppt_toggle', 10, 2 );

/**
 * Ensure WP Page Transitions credentials and table exist when module is enabled.
 */
function dlck_wppt_ensure_setup() {
	if ( dlck_get_option( 'dlck_divi_wp_page_transitions' ) !== '1' ) {
		return;
	}
	if ( get_option( 'wppatr-user-key' ) === false ) {
		update_option( 'wppatr-user-key', 'richarddavis' );
	}
	if ( get_option( 'wppatr-api-key' ) === false ) {
		update_option( 'wppatr-api-key', '60a7f806df785083c7558592cf8015e6' );
	}
	dlck_wppt_seed_table_from_sql();
}

/**
 * Create and seed the Flint Page Transition table using bundled SQL if missing.
 */
function dlck_wppt_seed_table_from_sql() {
	global $wpdb;

	$table_name     = $wpdb->prefix . 'flint_page_transition';
	$current_table  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
	$has_table      = $current_table === $table_name;
	$row_count      = 0;
	if ( $has_table ) {
		$row_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( $row_count > 0 ) {
			// Table already seeded.
			return;
		}
	}

	$sql_file = DLCK_LC_KIT_PLUGIN_DIR . 'functions/modules/wp-page-transition/wp-page-transition.sql';
	if ( ! file_exists( $sql_file ) ) {
		return;
	}

	$sql = file_get_contents( $sql_file );
	if ( ! $sql ) {
		return;
	}

	$sql = str_replace(
		array( '`wp_flint_page_transition`', 'wp_flint_page_transition' ),
		array( "`{$table_name}`", $table_name ),
		$sql
	);

	$create_sql = '';
	if ( preg_match( '/CREATE TABLE[\s\S]+?;\s*/i', $sql, $matches ) ) {
		$create_sql = trim( $matches[0] );
	}

	$insert_sql = '';
	if ( preg_match( '/INSERT INTO[\s\S]+?;\s*/i', $sql, $matches ) ) {
		$insert_sql = trim( $matches[0] );
	}

	if ( ! $has_table && $create_sql ) {
		$wpdb->query( $create_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	if ( $insert_sql && $row_count === 0 ) {
		$inserted = $wpdb->query( $insert_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( false === $inserted && $wpdb->last_error ) {
			// Fallback: parse the bundled SQL and insert rows one-by-one.
			$rows = dlck_wppt_extract_seed_rows( $sql );
			foreach ( $rows as $row ) {
				$wpdb->insert( $table_name, $row ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}
		}
	}
}

/**
 * Parse INSERT rows from the bundled SQL into associative arrays.
 *
 * This avoids relying on multi-row INSERT when environments block it.
 *
 * @param string $sql Full SQL file contents after prefix replacement.
 * @return array[]
 */
function dlck_wppt_extract_seed_rows( $sql ) {
	if ( ! preg_match( '/INSERT INTO\\s+`?[^`]+`?\\s*\\(([^)]+)\\)\\s*VALUES\\s*(.+);/is', $sql, $matches ) ) {
		return array();
	}

	$columns = array_map(
		static function ( $col ) {
			return trim( $col, " `\t\n\r\0\x0B" );
		},
		explode( ',', $matches[1] )
	);

	$values_blob = trim( $matches[2] );

	// Split top-level tuples "(...),(...)"
	$tuples    = array();
	$buffer    = '';
	$depth     = 0;
	$in_string = false;
	$escaped   = false;

	for ( $i = 0, $len = strlen( $values_blob ); $i < $len; $i++ ) {
		$char = $values_blob[ $i ];
		$buffer .= $char;

		if ( $char === '\\\\' && $in_string && ! $escaped ) {
			$escaped = true;
			continue;
		}

		if ( $char === '\'' && ! $escaped ) {
			$in_string = ! $in_string;
		}

		if ( ! $in_string ) {
			if ( $char === '(' ) {
				$depth++;
			} elseif ( $char === ')' ) {
				$depth--;
				if ( $depth === 0 ) {
					$tuples[] = $buffer;
					$buffer   = '';
				}
			}
		}

		$escaped = false;
	}

	$rows = array();
	foreach ( $tuples as $tuple ) {
		$tuple  = trim( $tuple );
		$tuple  = trim( $tuple, " \t\n\r\0\x0B()," );
		$values = str_getcsv( $tuple, ',', '\'', '\\\\' );
		if ( count( $values ) !== count( $columns ) ) {
			continue;
		}

		$rows[] = array_combine( $columns, $values );
	}

	return $rows;
}
