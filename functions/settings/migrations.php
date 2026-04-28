<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility: detect if an option does not exist without clobbering valid falsey values.
 */
function dlck_option_missing( string $name ): bool {
	return get_option( $name, '__dlck_missing__' ) === '__dlck_missing__';
}

/**
 * Copy all options that start with a given prefix to a new prefix.
 *
 * Only creates the target option if it does not already exist, so the real
 * Divi Toolbox data is never overwritten.
 */
function dlck_copy_options_to_prefix( string $source_prefix, string $target_prefix ): bool {
	global $wpdb;

	$copied    = false;
	$like      = $wpdb->esc_like( $source_prefix ) . '%';
	$raw_items = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->prepare( "SELECT option_name, option_value, autoload FROM {$wpdb->options} WHERE option_name LIKE %s", $like ),
		ARRAY_A
	);

	foreach ( $raw_items as $item ) {
		$target = $target_prefix . substr( $item['option_name'], strlen( $source_prefix ) );

		if ( dlck_option_missing( $target ) ) {
			add_option( $target, maybe_unserialize( $item['option_value'] ), '', $item['autoload'] );
			$copied = true;
		}
	}

	return $copied;
}

/**
 * Copy theme_mod values between prefixes without touching existing Toolbox mods.
 */
function dlck_copy_theme_mods_to_prefix( string $source_prefix, string $target_prefix ): bool {
	$mods   = get_theme_mods();
	$copied = false;

	if ( empty( $mods ) || ! is_array( $mods ) ) {
		return false;
	}

	foreach ( $mods as $key => $value ) {
		if ( strpos( $key, $source_prefix ) !== 0 ) {
			continue;
		}

		$target = $target_prefix . substr( $key, strlen( $source_prefix ) );

		if ( get_theme_mod( $target, '__dlck_missing__' ) === '__dlck_missing__' ) {
			set_theme_mod( $target, $value );
			$copied = true;
		}
	}

	return $copied;
}

/**
 * Remove any license-like keys from a settings array so we don't migrate keys
 * that don't belong to Divi Toolbox itself.
 */
function dlck_strip_license_fields( $settings ) {
	if ( ! is_array( $settings ) ) {
		return $settings;
	}

	foreach ( array_keys( $settings ) as $key ) {
		if ( stripos( $key, 'license' ) !== false ) {
			unset( $settings[ $key ] );
		}
	}

	return $settings;
}

/**
 * One-time migration: if a site previously ran the renamed LC Kit and its data
 * lives under dlck_* keys, copy it back into the Divi Toolbox namespace so the
 * original plugin regains its settings.
 */
function dlck_restore_divi_toolbox_data() {
	if ( get_option( 'dlck_migrated_toolbox_data' ) ) {
		return array(
			'copied'  => false,
			'message' => '',
		);
	}

	if ( ! is_admin() ) {
		return array(
			'copied'  => false,
			'message' => '',
		);
	}

	$copied_anything = false;

	$dlck_settings = dlck_strip_license_fields( get_option( 'dlck_lc_kit' ) );
	if ( ! empty( $dlck_settings ) && dlck_option_missing( 'dtb_toolbox' ) ) {
		add_option( 'dtb_toolbox', $dlck_settings );
		$copied_anything = true;
	}

	if ( dlck_copy_options_to_prefix( 'dlck_customize_', 'dtb_customize_' ) ) {
		$copied_anything = true;
	}

	if ( dlck_copy_theme_mods_to_prefix( 'dlck_modcustomize_', 'dtb_modcustomize_' ) ) {
		$copied_anything = true;
	}

	if ( ! dlck_option_missing( 'dlckchange_v1_2' ) && dlck_option_missing( 'dtbchange_v1_2' ) ) {
		add_option( 'dtbchange_v1_2', get_option( 'dlckchange_v1_2' ) );
		$copied_anything = true;
	}

	if ( $copied_anything ) {
		update_option( 'dlck_migrated_toolbox_data', current_time( 'mysql' ) );

		return array(
			'copied'  => true,
			'message' => __( 'Divi Toolbox data restored from LC Kit.', 'divi-lc-kit' ),
		);
	}

	return array(
		'copied'  => false,
		'message' => '',
	);
}
add_action( 'plugins_loaded', 'dlck_restore_divi_toolbox_data', 5 );
