<?php
/**
 * WP-CLI commands for LC Tweaks.
 *
 * @package divi-lc-kit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build export payload for LC Tweaks settings.
 *
 * @param bool $include_settings   Include core LC Tweaks option payload.
 * @param bool $include_customizer Include customizer option payload.
 * @param bool $include_snapshots  Include stored snapshots payload.
 * @return array<string,mixed>
 */
function dlck_cli_build_settings_export_payload( bool $include_settings = true, bool $include_customizer = false, bool $include_snapshots = false ): array {
	$payload = array();

	if ( $include_settings ) {
		$payload['settings'] = get_option( 'dlck_lc_kit', array() );
	}

	if ( $include_customizer ) {
		$customizer_settings_array     = array();
		$customizer_mod_settings_array = array();
		$customizer_settings           = wp_load_alloptions();
		$customizer_mod_settings       = get_theme_mods();

		foreach ( $customizer_settings as $key => $value ) {
			$name = explode( '_', (string) $key );
			if ( isset( $name[0], $name[1] ) && $name[0] === 'dlck' && $name[1] === 'customize' ) {
				$customizer_settings_array[ $key ] = $value;
			}
		}

		foreach ( $customizer_mod_settings as $key => $value ) {
			$name = explode( '_', (string) $key );
			if ( isset( $name[0], $name[1] ) && $name[0] === 'dlck' && $name[1] === 'modcustomize' ) {
				$customizer_mod_settings_array[ $key ] = $value;
			}
		}

		$payload['customization']    = maybe_serialize( $customizer_settings_array );
		$payload['modcustomization'] = maybe_serialize( $customizer_mod_settings_array );
	}

	if ( $include_snapshots && function_exists( 'dlck_get_settings_snapshots' ) ) {
		$payload['snapshots'] = dlck_get_settings_snapshots();
	}

	return $payload;
}

/**
 * Read + decode a JSON file into an array.
 *
 * @param string $path Absolute or relative file path.
 * @return array<string,mixed>|null
 */
function dlck_cli_read_json_file( string $path ): ?array {
	if ( $path === '' || ! is_file( $path ) || ! is_readable( $path ) ) {
		return null;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$raw = file_get_contents( $path );
	if ( ! is_string( $raw ) || $raw === '' ) {
		return null;
	}

	$decoded = json_decode( $raw, true );
	if ( ! is_array( $decoded ) ) {
		return null;
	}

	return $decoded;
}

/**
 * Normalize incoming settings through existing plugin pipelines.
 *
 * @param array<string,mixed> $incoming Incoming setting values.
 * @param array<string,mixed> $current  Existing setting values.
 * @return array<string,mixed>
 */
function dlck_cli_normalize_incoming_settings( array $incoming, array $current ): array {
	$normalized = $incoming;

	if ( function_exists( 'dlck_enforce_mutually_exclusive_options' ) ) {
		$normalized = dlck_enforce_mutually_exclusive_options( $normalized, $current );
		if ( ! is_array( $normalized ) ) {
			$normalized = $incoming;
		}
	}

	if ( function_exists( 'dlck_normalize_scope_rules_settings' ) ) {
		$normalized = dlck_normalize_scope_rules_settings( $normalized );
	}

	return $normalized;
}

/**
 * Write string output to a file path.
 *
 * @param string $file_path File path.
 * @param string $contents  Output content.
 * @return bool
 */
function dlck_cli_write_output_file( string $file_path, string $contents ): bool {
	if ( $file_path === '' ) {
		return false;
	}

	$dir = dirname( $file_path );
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}

	$written = file_put_contents( $file_path, $contents );
	return is_int( $written ) && $written >= 0;
}

/**
 * Update an option only when value changes.
 *
 * @param string $option_name Option key.
 * @param mixed  $new_value   Value to persist.
 * @param bool|null $autoload Whether option should autoload. Null preserves existing behavior.
 * @return bool True when an update happened.
 */
function dlck_cli_update_option_if_changed( string $option_name, $new_value, ?bool $autoload = null ): bool {
	$current = get_option( $option_name, '__dlck_missing__' );
	if ( $current === $new_value ) {
		return false;
	}

	if ( null === $autoload ) {
		update_option( $option_name, $new_value );
	} else {
		update_option( $option_name, $new_value, $autoload );
	}

	return true;
}

/**
 * Build a text summary of diagnostics report.
 *
 * @param array<string,mixed> $report Diagnostics payload.
 * @return string
 */
function dlck_cli_diagnostics_to_text( array $report ): string {
	$lines = array();

	$lines[] = 'LC Tweaks Diagnostics';
	$lines[] = 'Generated (GMT): ' . (string) ( $report['generated_gmt'] ?? '' );
	$lines[] = '';

	$plugin_version = isset( $report['plugin']['version'] ) ? (string) $report['plugin']['version'] : '';
	$wp_version     = isset( $report['site']['wp_version'] ) ? (string) $report['site']['wp_version'] : '';
	$php_version    = isset( $report['site']['php_version'] ) ? (string) $report['site']['php_version'] : '';

	$lines[] = 'Plugin Version: ' . $plugin_version;
	$lines[] = 'WordPress Version: ' . $wp_version;
	$lines[] = 'PHP Version: ' . $php_version;
	$lines[] = '';

	$enabled_count = isset( $report['lc_tweaks']['enabled_count'] ) ? (int) $report['lc_tweaks']['enabled_count'] : 0;
	$option_count  = isset( $report['lc_tweaks']['option_count'] ) ? (int) $report['lc_tweaks']['option_count'] : 0;
	$lines[]       = 'Enabled Options: ' . $enabled_count . ' / ' . $option_count;

	$preflight = isset( $report['lc_tweaks']['preflight_conflicts'] ) && is_array( $report['lc_tweaks']['preflight_conflicts'] )
		? $report['lc_tweaks']['preflight_conflicts']
		: array();
	$lines[] = 'Preflight Warnings: ' . count( $preflight );
	if ( ! empty( $preflight ) ) {
		foreach ( $preflight as $warning ) {
			$lines[] = '- ' . (string) $warning;
		}
	}

	$lines[] = '';
	$lines[] = 'Cache';

	$inline_count = isset( $report['cache']['inline_assets']['file_count'] ) ? (int) $report['cache']['inline_assets']['file_count'] : 0;
	$lazy_count   = isset( $report['cache']['lazy_load']['file_count'] ) ? (int) $report['cache']['lazy_load']['file_count'] : 0;
	$lines[]      = 'Inline Assets Files: ' . $inline_count;
	$lines[]      = 'Lazy Cache Files: ' . $lazy_count;

	return implode( PHP_EOL, $lines ) . PHP_EOL;
}

/**
 * Clear and rebuild inline asset cache files.
 *
 * @return array{deleted:int,rebuilt:bool}
 */
function dlck_cli_clear_inline_asset_cache(): array {
	$deleted = 0;
	$rebuilt = false;

	if ( function_exists( 'dlck_inline_assets_get_cache_dir' ) ) {
		$dir = (string) dlck_inline_assets_get_cache_dir();
		if ( $dir !== '' && ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		if ( $dir !== '' ) {
			$files = glob( $dir . 'dlck-inline-*.*' );
			if ( is_array( $files ) ) {
				foreach ( $files as $path ) {
					if ( is_file( $path ) && @unlink( $path ) ) {
						$deleted++;
					}
					$base = basename( $path );
					delete_option( 'dlck_' . md5( $base ) . '_hash' );
				}
			}
		}
	}

	if ( function_exists( 'dlck_rebuild_all_inline_caches' ) ) {
		dlck_rebuild_all_inline_caches();
		$rebuilt = true;
	}

	return array(
		'deleted' => $deleted,
		'rebuilt' => $rebuilt,
	);
}

/**
 * Clear Divi lazy-load cache files.
 *
 * @return array{deleted:int,path:string}
 */
function dlck_cli_clear_lazy_cache_files(): array {
	$deleted = 0;
	$base    = function_exists( 'dlck_divi_lazy_get_cache_base_dir' )
		? (string) dlck_divi_lazy_get_cache_base_dir()
		: trailingslashit( WP_CONTENT_DIR . '/cache/lc-tweaks-lazy' );

	if ( $base !== '' && is_dir( $base ) ) {
		$paths = glob( trailingslashit( $base ) . '*' );
		if ( is_array( $paths ) ) {
			foreach ( $paths as $path ) {
				if ( is_file( $path ) && @unlink( $path ) ) {
					$deleted++;
				}
			}
		}
	}

	return array(
		'deleted' => $deleted,
		'path'    => $base,
	);
}

if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI_Command' ) ) {
	/**
	 * Manage LC Tweaks settings and maintenance tasks via WP-CLI.
	 */
	class DLCK_LC_Tweaks_CLI_Command extends WP_CLI_Command {
		/**
		 * Export LC Tweaks settings payload.
		 *
		 * ## OPTIONS
		 *
		 * [--file=<path>]
		 * : Save output to a file instead of stdout.
		 *
		 * [--format=<format>]
		 * : Output format.
		 * ---
		 * default: json
		 * options:
		 *   - json
		 *   - txt
		 * ---
		 *
		 * [--with-customizer]
		 * : Include customizer and theme-mod payloads.
		 *
		 * [--with-snapshots]
		 * : Include stored settings snapshots.
		 */
		public function export( array $args, array $assoc_args ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
			$format            = isset( $assoc_args['format'] ) ? sanitize_key( (string) $assoc_args['format'] ) : 'json';
			$include_customizer = isset( $assoc_args['with-customizer'] );
			$include_snapshots  = isset( $assoc_args['with-snapshots'] );
			$file_path          = isset( $assoc_args['file'] ) ? (string) $assoc_args['file'] : '';

			if ( ! in_array( $format, array( 'json', 'txt' ), true ) ) {
				WP_CLI::error( 'Unsupported format. Use --format=json or --format=txt.' );
				return;
			}

			$payload = dlck_cli_build_settings_export_payload( true, $include_customizer, $include_snapshots );

			if ( $format === 'txt' ) {
				$settings     = isset( $payload['settings'] ) && is_array( $payload['settings'] ) ? $payload['settings'] : array();
				$enabled      = 0;
				$settings_len = count( $settings );
				foreach ( $settings as $key => $value ) {
					if ( strpos( (string) $key, 'dlck_' ) === 0 && ! empty( $value ) && (string) $value !== '0' ) {
						$enabled++;
					}
				}

				$content = implode(
					PHP_EOL,
					array(
						'LC Tweaks Settings Export',
						'Generated (GMT): ' . gmdate( 'c' ),
						'Settings keys: ' . $settings_len,
						'Enabled toggles: ' . $enabled,
						'Included customizer data: ' . ( $include_customizer ? 'yes' : 'no' ),
						'Included snapshots: ' . ( $include_snapshots ? 'yes' : 'no' ),
						'',
					)
				);
			} else {
				$content = wp_json_encode( $payload, JSON_PRETTY_PRINT );
				if ( ! is_string( $content ) ) {
					WP_CLI::error( 'Could not encode export payload to JSON.' );
					return;
				}
			}

			if ( $file_path !== '' ) {
				if ( ! dlck_cli_write_output_file( $file_path, $content ) ) {
					WP_CLI::error( 'Could not write export file: ' . $file_path );
					return;
				}
				WP_CLI::success( 'Export written to ' . $file_path );
				return;
			}

			WP_CLI::line( $content );
		}

		/**
		 * Import LC Tweaks settings from JSON export.
		 *
		 * ## OPTIONS
		 *
		 * <file>
		 * : Path to a JSON file previously exported from LC Tweaks.
		 *
		 * [--dry-run]
		 * : Validate and report changes without applying them.
		 *
		 * [--skip-snapshot]
		 * : Do not create a pre-import settings snapshot.
		 *
		 * [--with-snapshots]
		 * : Import snapshots payload when present in the JSON file.
		 */
		public function import( array $args, array $assoc_args ): void {
			$file_path      = (string) ( $args[0] ?? '' );
			$dry_run        = isset( $assoc_args['dry-run'] );
			$skip_snapshot  = isset( $assoc_args['skip-snapshot'] );
			$with_snapshots = isset( $assoc_args['with-snapshots'] );
			$import_payload = dlck_cli_read_json_file( $file_path );

			if ( empty( $import_payload ) ) {
				WP_CLI::error( 'Could not read a valid JSON payload from: ' . $file_path );
				return;
			}

			$has_known_keys = isset( $import_payload['settings'] ) || isset( $import_payload['customization'] ) || isset( $import_payload['modcustomization'] );
			if ( ! $has_known_keys ) {
				WP_CLI::error( 'Import file does not include LC Tweaks export keys.' );
				return;
			}
			if ( function_exists( 'dlck_multisite_policy_blocks_site_saves' ) && dlck_multisite_policy_blocks_site_saves() ) {
				WP_CLI::error( 'Per-site settings are locked by multisite policy. Enable site overrides first.' );
				return;
			}

			$current_settings = maybe_unserialize( get_option( 'dlck_lc_kit', array() ) );
			$current_settings = is_array( $current_settings ) ? $current_settings : array();
			$target_settings  = $current_settings;

			if ( isset( $import_payload['settings'] ) ) {
				$incoming = maybe_unserialize( $import_payload['settings'] );
				if ( ! is_array( $incoming ) ) {
					WP_CLI::error( 'The `settings` payload is not a valid array.' );
					return;
				}
				$target_settings = dlck_cli_normalize_incoming_settings( $incoming, $current_settings );
			}

			$current_hash = md5( (string) wp_json_encode( $current_settings ) );
			$target_hash  = md5( (string) wp_json_encode( $target_settings ) );
			$changed      = ( $current_hash !== $target_hash );

			if ( $dry_run ) {
				WP_CLI::line( 'Dry run completed.' );
				WP_CLI::line( 'Settings changed: ' . ( $changed ? 'yes' : 'no' ) );
				WP_CLI::line( 'Has customizer payload: ' . ( isset( $import_payload['customization'] ) ? 'yes' : 'no' ) );
				WP_CLI::line( 'Has modcustomization payload: ' . ( isset( $import_payload['modcustomization'] ) ? 'yes' : 'no' ) );
				WP_CLI::line( 'Will import snapshots: ' . ( $with_snapshots && isset( $import_payload['snapshots'] ) ? 'yes' : 'no' ) );
				return;
			}

			if ( $changed && ! $skip_snapshot && function_exists( 'dlck_store_settings_snapshot' ) ) {
				dlck_store_settings_snapshot( $current_settings, 'manual_save' );
			}

			if ( $changed ) {
				dlck_cli_update_option_if_changed( 'dlck_lc_kit', $target_settings );
			}

			if ( isset( $import_payload['customization'] ) ) {
				$all_options = wp_load_alloptions();
				foreach ( $all_options as $key => $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
					$name = explode( '_', (string) $key );
					if ( isset( $name[0], $name[1] ) && $name[0] === 'dlck' && $name[1] === 'customize' ) {
						delete_option( $key );
					}
				}

				$customization = maybe_unserialize( $import_payload['customization'] );
				if ( is_array( $customization ) ) {
					foreach ( $customization as $key => $value ) {
						dlck_cli_update_option_if_changed( (string) $key, $value );
					}
				}
			}

			if ( isset( $import_payload['modcustomization'] ) ) {
				$all_mods = get_theme_mods();
				foreach ( $all_mods as $key => $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
					$name = explode( '_', (string) $key );
					if ( isset( $name[0], $name[1] ) && $name[0] === 'dlck' && $name[1] === 'modcustomize' ) {
						remove_theme_mod( (string) $key );
					}
				}

				$modcustomization = maybe_unserialize( $import_payload['modcustomization'] );
				if ( is_array( $modcustomization ) ) {
					foreach ( $modcustomization as $key => $value ) {
						set_theme_mod( (string) $key, $value );
					}
				}
			}

			if ( $with_snapshots && isset( $import_payload['snapshots'] ) && is_array( $import_payload['snapshots'] ) ) {
				$snapshots = array();
				foreach ( $import_payload['snapshots'] as $snapshot ) {
					if ( ! is_array( $snapshot ) ) {
						continue;
					}

					$id       = isset( $snapshot['id'] ) ? sanitize_key( (string) $snapshot['id'] ) : '';
					$settings = isset( $snapshot['settings'] ) && is_array( $snapshot['settings'] ) ? $snapshot['settings'] : array();
					if ( $id === '' || empty( $settings ) ) {
						continue;
					}

					$snapshots[] = array(
						'id'         => $id,
						'created'    => isset( $snapshot['created'] ) ? (int) $snapshot['created'] : time(),
						'reason'     => isset( $snapshot['reason'] ) ? sanitize_key( (string) $snapshot['reason'] ) : 'manual_save',
						'user_id'    => isset( $snapshot['user_id'] ) ? (int) $snapshot['user_id'] : 0,
						'user_login' => isset( $snapshot['user_login'] ) ? sanitize_user( (string) $snapshot['user_login'], true ) : '',
						'hash'       => isset( $snapshot['hash'] ) ? sanitize_key( (string) $snapshot['hash'] ) : sanitize_key( md5( (string) wp_json_encode( $settings ) ) ),
						'settings'   => $settings,
					);
				}

				if ( ! empty( $snapshots ) ) {
					usort(
						$snapshots,
						static function ( array $a, array $b ): int {
							return (int) $b['created'] <=> (int) $a['created'];
						}
					);
					dlck_cli_update_option_if_changed( 'dlck_lc_kit_snapshots', array_slice( $snapshots, 0, 5 ), false );
				}
			}

			if ( function_exists( 'dlck_rebuild_all_inline_caches' ) ) {
				dlck_rebuild_all_inline_caches();
			}
			if ( function_exists( 'dlck_sync_woo_session_cleanup_schedule' ) ) {
				dlck_sync_woo_session_cleanup_schedule();
			}
			if ( function_exists( 'dlck_create_static_css' ) ) {
				dlck_create_static_css();
			}

			WP_CLI::success( $changed ? 'Settings imported and applied.' : 'Import completed. No settings changes detected.' );
		}

		/**
		 * Manage settings presets.
		 *
		 * ## OPTIONS
		 *
		 * <action>
		 * : Preset action: list, apply, restore.
		 *
		 * [<preset_id>]
		 * : Preset key for the apply action.
		 *
		 * [--dry-run]
		 * : Preview apply changes without updating settings.
		 */
		public function preset( array $args, array $assoc_args ): void {
			$action = isset( $args[0] ) ? sanitize_key( (string) $args[0] ) : 'list';

			if ( $action === 'list' ) {
				$presets = function_exists( 'dlck_get_settings_presets' ) ? dlck_get_settings_presets() : array();
				if ( empty( $presets ) ) {
					WP_CLI::warning( 'No presets are currently available.' );
					return;
				}

				$items = array();
				foreach ( $presets as $id => $preset ) {
					$items[] = array(
						'id'          => (string) $id,
						'label'       => isset( $preset['label'] ) ? (string) $preset['label'] : (string) $id,
						'description' => isset( $preset['description'] ) ? (string) $preset['description'] : '',
					);
				}

				\WP_CLI\Utils\format_items( 'table', $items, array( 'id', 'label', 'description' ) );
				return;
			}

			if ( $action === 'restore' ) {
				if ( function_exists( 'dlck_multisite_policy_blocks_site_saves' ) && dlck_multisite_policy_blocks_site_saves() ) {
					WP_CLI::error( 'Preset restore is locked by multisite policy. Enable site overrides first.' );
					return;
				}

				if ( ! function_exists( 'dlck_get_preset_restore_payload' ) ) {
					WP_CLI::error( 'Preset restore payload helper is unavailable.' );
					return;
				}

				$restore_data = dlck_get_preset_restore_payload();
				if ( empty( $restore_data['settings'] ) || ! is_array( $restore_data['settings'] ) ) {
					WP_CLI::error( 'No preset restore backup is available.' );
					return;
				}

				$current_settings = maybe_unserialize( get_option( 'dlck_lc_kit', array() ) );
				$current_settings = is_array( $current_settings ) ? $current_settings : array();
				$target_settings  = dlck_cli_normalize_incoming_settings( $restore_data['settings'], $current_settings );

				$current_hash = md5( (string) wp_json_encode( $current_settings ) );
				$target_hash  = md5( (string) wp_json_encode( $target_settings ) );
				$changed      = ( $current_hash !== $target_hash );

				if ( $changed && function_exists( 'dlck_store_settings_snapshot' ) ) {
					dlck_store_settings_snapshot( $current_settings, 'pre_restore_backup' );
				}
				if ( $changed ) {
					dlck_cli_update_option_if_changed( 'dlck_lc_kit', $target_settings );
				}

				dlck_cli_update_option_if_changed( 'dlck_last_applied_preset', '', false );
				if ( function_exists( 'dlck_clear_preset_restore_payload' ) ) {
					dlck_clear_preset_restore_payload();
				}

				if ( function_exists( 'dlck_rebuild_all_inline_caches' ) ) {
					dlck_rebuild_all_inline_caches();
				}

				WP_CLI::success( $changed ? 'Preset restore completed.' : 'Preset restore backup already matches current settings.' );
				return;
			}

			if ( $action === 'apply' ) {
				if ( function_exists( 'dlck_multisite_policy_blocks_site_saves' ) && dlck_multisite_policy_blocks_site_saves() ) {
					WP_CLI::error( 'Preset apply is locked by multisite policy. Enable site overrides first.' );
					return;
				}

				$preset_id = isset( $args[1] ) ? sanitize_key( (string) $args[1] ) : '';
				$dry_run   = isset( $assoc_args['dry-run'] );

				if ( $preset_id === '' ) {
					WP_CLI::error( 'Please provide a preset ID. Example: wp lc-tweaks preset apply brochure' );
					return;
				}

				$preset_data = function_exists( 'dlck_get_settings_preset' ) ? dlck_get_settings_preset( $preset_id ) : null;
				if ( empty( $preset_data ) || ! is_array( $preset_data ) ) {
					WP_CLI::error( 'Unknown preset: ' . $preset_id );
					return;
				}

				$current_settings = maybe_unserialize( get_option( 'dlck_lc_kit', array() ) );
				$current_settings = is_array( $current_settings ) ? $current_settings : array();
				$preset_settings  = isset( $preset_data['settings'] ) && is_array( $preset_data['settings'] ) ? $preset_data['settings'] : array();

				$target_settings = $current_settings;
				foreach ( $preset_settings as $key => $value ) {
					if ( is_string( $key ) && strpos( $key, 'dlck_' ) === 0 ) {
						$target_settings[ $key ] = $value;
					}
				}
				$target_settings = dlck_cli_normalize_incoming_settings( $target_settings, $current_settings );

				$current_hash = md5( (string) wp_json_encode( $current_settings ) );
				$target_hash  = md5( (string) wp_json_encode( $target_settings ) );
				$changed      = ( $current_hash !== $target_hash );

				if ( $dry_run ) {
					WP_CLI::line( 'Dry run completed for preset: ' . $preset_id );
					WP_CLI::line( 'Settings changed: ' . ( $changed ? 'yes' : 'no' ) );
					return;
				}

				if ( $changed && function_exists( 'dlck_store_settings_snapshot' ) ) {
					dlck_store_settings_snapshot( $current_settings, 'preset_apply' );
				}

				$preset_label = isset( $preset_data['label'] ) ? (string) $preset_data['label'] : $preset_id;
				if ( function_exists( 'dlck_store_preset_restore_payload' ) ) {
					dlck_store_preset_restore_payload( $current_settings, $preset_id, $preset_label );
				}

				if ( $changed ) {
					dlck_cli_update_option_if_changed( 'dlck_lc_kit', $target_settings );
				}
				dlck_cli_update_option_if_changed( 'dlck_last_applied_preset', $preset_id, false );

				if ( function_exists( 'dlck_rebuild_all_inline_caches' ) ) {
					dlck_rebuild_all_inline_caches();
				}

				WP_CLI::success( $changed ? 'Preset applied: ' . $preset_label : 'Preset already active: ' . $preset_label );
				return;
			}

			WP_CLI::error( 'Unknown preset action. Use: list, apply, restore.' );
		}

		/**
		 * Export diagnostics data.
		 *
		 * ## OPTIONS
		 *
		 * [--file=<path>]
		 * : Save output to a file instead of stdout.
		 *
		 * [--format=<format>]
		 * : Output format.
		 * ---
		 * default: json
		 * options:
		 *   - json
		 *   - txt
		 * ---
		 */
		public function diagnostics( array $args, array $assoc_args ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
			if ( ! function_exists( 'dlck_build_diagnostics_report' ) ) {
				WP_CLI::error( 'Diagnostics report helper is unavailable.' );
				return;
			}

			$format    = isset( $assoc_args['format'] ) ? sanitize_key( (string) $assoc_args['format'] ) : 'json';
			$file_path = isset( $assoc_args['file'] ) ? (string) $assoc_args['file'] : '';

			if ( ! in_array( $format, array( 'json', 'txt' ), true ) ) {
				WP_CLI::error( 'Unsupported format. Use --format=json or --format=txt.' );
				return;
			}

			$report = dlck_build_diagnostics_report();
			$output = '';

			if ( $format === 'txt' ) {
				$output = dlck_cli_diagnostics_to_text( $report );
			} else {
				$output = wp_json_encode( $report, JSON_PRETTY_PRINT );
				if ( ! is_string( $output ) ) {
					WP_CLI::error( 'Could not encode diagnostics JSON output.' );
					return;
				}
			}

			if ( $file_path !== '' ) {
				if ( ! dlck_cli_write_output_file( $file_path, $output ) ) {
					WP_CLI::error( 'Could not write diagnostics file: ' . $file_path );
					return;
				}
				WP_CLI::success( 'Diagnostics written to ' . $file_path );
				return;
			}

			WP_CLI::line( $output );
		}

		/**
		 * Run cache maintenance tasks.
		 *
		 * ## OPTIONS
		 *
		 * <action>
		 * : Cache action: clear or status.
		 *
		 * [--target=<target>]
		 * : Cache clear target.
		 * ---
		 * default: all
		 * options:
		 *   - all
		 *   - inline
		 *   - lazy
		 *   - woo_sessions
		 * ---
		 */
		public function cache( array $args, array $assoc_args ): void {
			$action = isset( $args[0] ) ? sanitize_key( (string) $args[0] ) : 'status';
			$target = isset( $assoc_args['target'] ) ? sanitize_key( (string) $assoc_args['target'] ) : 'all';

			if ( $action === 'status' ) {
				if ( function_exists( 'dlck_build_diagnostics_report' ) ) {
					$report       = dlck_build_diagnostics_report();
					$inline_count = isset( $report['cache']['inline_assets']['file_count'] ) ? (int) $report['cache']['inline_assets']['file_count'] : 0;
					$lazy_count   = isset( $report['cache']['lazy_load']['file_count'] ) ? (int) $report['cache']['lazy_load']['file_count'] : 0;

					WP_CLI::line( 'Inline assets cache files: ' . $inline_count );
					WP_CLI::line( 'Lazy cache files: ' . $lazy_count );
					return;
				}

				WP_CLI::warning( 'Diagnostics helper unavailable; cache status is limited.' );
				return;
			}

			if ( $action !== 'clear' ) {
				WP_CLI::error( 'Unknown cache action. Use: clear or status.' );
				return;
			}

			if ( ! in_array( $target, array( 'all', 'inline', 'lazy', 'woo_sessions' ), true ) ) {
				WP_CLI::error( 'Unsupported cache target. Use: all, inline, lazy, woo_sessions.' );
				return;
			}

			if ( $target === 'inline' || $target === 'all' ) {
				$inline = dlck_cli_clear_inline_asset_cache();
				WP_CLI::line(
					sprintf(
						'Inline cache cleared (%d file(s) removed)%s.',
						(int) $inline['deleted'],
						$inline['rebuilt'] ? ' and rebuilt' : ''
					)
				);
			}

			if ( $target === 'lazy' || $target === 'all' ) {
				if ( function_exists( 'dlck_divi_lazy_clear_cache_all' ) ) {
					dlck_divi_lazy_clear_cache_all();
					WP_CLI::line( 'Lazy cache clear helper executed.' );
				} else {
					$lazy = dlck_cli_clear_lazy_cache_files();
					WP_CLI::line( sprintf( 'Lazy cache cleared (%d file(s) removed) in %s.', (int) $lazy['deleted'], $lazy['path'] ) );
				}
			}

			if ( $target === 'woo_sessions' || $target === 'all' ) {
				if ( function_exists( 'dlck_run_woo_session_cleanup_cron' ) ) {
					dlck_run_woo_session_cleanup_cron();
					$health = function_exists( 'dlck_get_woo_session_cleanup_health_snapshot' ) ? dlck_get_woo_session_cleanup_health_snapshot() : array();
					$status = isset( $health['status'] ) ? (string) $health['status'] : 'unknown';
					$count  = isset( $health['deleted_count'] ) ? (int) $health['deleted_count'] : 0;
					WP_CLI::line( sprintf( 'Woo sessions cleanup finished (status: %s, deleted: %d).', $status, $count ) );
				} else {
					WP_CLI::warning( 'Woo sessions cleanup helper is unavailable.' );
				}
			}

			WP_CLI::success( 'Cache maintenance completed.' );
		}
	}

	WP_CLI::add_command( 'lc-tweaks', 'DLCK_LC_Tweaks_CLI_Command' );
}
