<?php
$dlck_import_messages;
$dlck_import_messages = array(
	array(
		'Filed to import settings.',
		'This file is not a correct LC Kit export file. Please upload a JSON file.',
		'Per-site settings are locked by multisite policy.',
	),
	array(
		'Settings imported successfully.',
	),
);

$dlck_snapshot_messages;
$dlck_snapshot_messages = array(
	array(
		'Could not restore the selected snapshot.',
		'Snapshot ID is missing.',
		'The selected snapshot is no longer available.',
		'The selected snapshot is already active.',
		'Snapshot restore is locked by multisite policy.',
	),
	array(
		'Settings snapshot restored successfully.',
	),
);

function dlck_set_import_fail_msg() {
	wp_safe_redirect( admin_url( 'admin.php?page=divi_lc_kit&tab=settings' ) . '&import_success=0&import_msg=0' );
	exit;
}

/**
 * Update an option only when the value changed.
 *
 * @param string $option_name Option key.
 * @param mixed  $new_value   New value.
 * @param mixed  $default     Optional sentinel for missing current value.
 * @return bool True when an update was performed.
 */
function dlck_update_option_if_changed( string $option_name, $new_value, $default = '__dlck_missing__' ): bool {
	$current = get_option( $option_name, $default );
	if ( $current === $new_value ) {
		return false;
	}
	update_option( $option_name, $new_value );
	return true;
}

/**
 * Get sanitized import notice state from query args.
 *
 * @return array{status:int,message:int}
 */
function dlck_get_import_notice_state(): array {
	$status = isset( $_GET['import_success'] ) ? absint( wp_unslash( $_GET['import_success'] ) ) : -1;
	$message = isset( $_GET['import_msg'] ) ? absint( wp_unslash( $_GET['import_msg'] ) ) : -1;

	return array(
		'status'  => $status,
		'message' => $message,
	);
}

/**
 * Resolve a notice message string by status + message indexes.
 */
function dlck_get_import_notice_message( int $status, int $message ): string {
	global $dlck_import_messages;

	if ( ! isset( $dlck_import_messages[ $status ] ) || ! is_array( $dlck_import_messages[ $status ] ) ) {
		return '';
	}

	if ( ! isset( $dlck_import_messages[ $status ][ $message ] ) ) {
		return '';
	}

	return (string) $dlck_import_messages[ $status ][ $message ];
}

function dlck_import_admin_notice__success() {
	$state = dlck_get_import_notice_state();
	$text  = dlck_get_import_notice_message( $state['status'], $state['message'] );

	if ( $state['status'] !== 1 || $text === '' ) {
		return;
	}
	?>
	<div class="notice notice-success is-dismissible">
		<p><?php echo esc_html( $text ); ?></p>
	</div>
	<?php
}

function dlck_import_admin_notice__fail() {
	$state = dlck_get_import_notice_state();
	$text  = dlck_get_import_notice_message( $state['status'], $state['message'] );

	if ( $state['status'] !== 0 || $text === '' ) {
		return;
	}
	?>
	<div class="notice notice-error is-dismissible">
		<p><?php echo esc_html( $text ); ?></p>
	</div>
	<?php
}

function dlck_import_notifications() {
	$state = dlck_get_import_notice_state();
	$text  = dlck_get_import_notice_message( $state['status'], $state['message'] );

	if ( $text === '' ) {
		return;
	}

	if ( $state['status'] === 1 ) {
		add_action( 'admin_notices', 'dlck_import_admin_notice__success' );
	} elseif ( $state['status'] === 0 ) {
		add_action( 'admin_notices', 'dlck_import_admin_notice__fail' );
	}
}

/**
 * Return a normalized list of stored LC Tweaks setting snapshots.
 *
 * @return array<int,array<string,mixed>>
 */
function dlck_get_settings_snapshots(): array {
	$raw = get_option( 'dlck_lc_kit_snapshots', array() );
	if ( ! is_array( $raw ) ) {
		return array();
	}

	$snapshots = array();
	foreach ( $raw as $snapshot ) {
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
			'created'    => isset( $snapshot['created'] ) ? (int) $snapshot['created'] : 0,
			'reason'     => isset( $snapshot['reason'] ) ? sanitize_key( (string) $snapshot['reason'] ) : 'manual_save',
			'user_id'    => isset( $snapshot['user_id'] ) ? (int) $snapshot['user_id'] : 0,
			'user_login' => isset( $snapshot['user_login'] ) ? sanitize_user( (string) $snapshot['user_login'], true ) : '',
			'hash'       => isset( $snapshot['hash'] ) ? sanitize_key( (string) $snapshot['hash'] ) : '',
			'settings'   => $settings,
		);
	}

	usort(
		$snapshots,
		static function ( array $a, array $b ): int {
			return (int) $b['created'] <=> (int) $a['created'];
		}
	);

	return array_slice( $snapshots, 0, 5 );
}

/**
 * Return a user-facing label for snapshot reason codes.
 */
function dlck_snapshot_reason_label( string $reason ): string {
	switch ( $reason ) {
		case 'pre_restore_backup':
			return __( 'Backup before restore', 'lc-tweaks' );
		case 'preset_apply':
			return __( 'Preset apply', 'lc-tweaks' );
		case 'manual_save':
		default:
			return __( 'Manual save', 'lc-tweaks' );
	}
}

/**
 * Store a settings snapshot while keeping only the latest five.
 *
 * @param array  $settings LC Tweaks settings.
 * @param string $reason   Snapshot reason code.
 * @return bool True when a new snapshot is stored.
 */
function dlck_store_settings_snapshot( array $settings, string $reason = 'manual_save' ): bool {
	$filtered = array();
	foreach ( $settings as $key => $value ) {
		if ( ! is_string( $key ) || strpos( $key, 'dlck_' ) !== 0 ) {
			continue;
		}
		$filtered[ $key ] = $value;
	}

	if ( empty( $filtered ) ) {
		return false;
	}

	ksort( $filtered );
	$hash      = md5( (string) wp_json_encode( $filtered ) );
	$snapshots = dlck_get_settings_snapshots();

	if ( ! empty( $snapshots ) && isset( $snapshots[0]['hash'] ) && (string) $snapshots[0]['hash'] === $hash ) {
		return false;
	}

	$user = wp_get_current_user();
	array_unshift(
		$snapshots,
		array(
			'id'         => sanitize_key( 'snap_' . gmdate( 'YmdHis' ) . '_' . wp_generate_password( 6, false, false ) ),
			'created'    => time(),
			'reason'     => sanitize_key( $reason ),
			'user_id'    => (int) $user->ID,
			'user_login' => $user->exists() ? sanitize_user( (string) $user->user_login, true ) : '',
			'hash'       => $hash,
			'settings'   => $filtered,
		)
	);

	update_option( 'dlck_lc_kit_snapshots', array_slice( $snapshots, 0, 5 ), false );
	return true;
}

/**
 * Resolve a snapshot by ID from current stored snapshots.
 *
 * @param string $snapshot_id Snapshot identifier.
 * @return array<string,mixed>|null
 */
function dlck_get_snapshot_by_id( string $snapshot_id ): ?array {
	foreach ( dlck_get_settings_snapshots() as $snapshot ) {
		if ( isset( $snapshot['id'] ) && (string) $snapshot['id'] === $snapshot_id ) {
			return $snapshot;
		}
	}
	return null;
}

/**
 * Redirect to settings with snapshot notice params.
 */
function dlck_snapshot_redirect( int $success, int $message ): void {
	wp_safe_redirect(
		add_query_arg(
			array(
				'tab'              => 'settings',
				'snapshot_restore' => $success,
				'snapshot_msg'     => $message,
			),
			admin_url( 'admin.php?page=divi_lc_kit' )
		)
	);
	exit;
}

/**
 * Read snapshot notice text from query args.
 */
function dlck_get_snapshot_notice_message( int $status, int $message ): string {
	global $dlck_snapshot_messages;

	if ( ! isset( $dlck_snapshot_messages[ $status ] ) || ! is_array( $dlck_snapshot_messages[ $status ] ) ) {
		return '';
	}

	if ( ! isset( $dlck_snapshot_messages[ $status ][ $message ] ) ) {
		return '';
	}

	return (string) $dlck_snapshot_messages[ $status ][ $message ];
}

/**
 * Output snapshot restore notices.
 */
function dlck_snapshot_notifications() {
	$status  = isset( $_GET['snapshot_restore'] ) ? absint( wp_unslash( $_GET['snapshot_restore'] ) ) : -1;
	$message = isset( $_GET['snapshot_msg'] ) ? absint( wp_unslash( $_GET['snapshot_msg'] ) ) : -1;
	$text    = dlck_get_snapshot_notice_message( $status, $message );

	if ( $text === '' ) {
		return;
	}

	if ( $status === 1 ) {
		add_action(
			'admin_notices',
			static function () use ( $text ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php echo esc_html( $text ); ?></p>
				</div>
				<?php
			}
		);
		return;
	}

	if ( $status === 0 ) {
		add_action(
			'admin_notices',
			static function () use ( $text ) {
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php echo esc_html( $text ); ?></p>
				</div>
				<?php
			}
		);
	}
}

/**
 * Restore LC Tweaks settings from a selected snapshot.
 */
function dlck_restore_settings_snapshot() {
	if ( ! isset( $_POST['dlck_subform_type'] ) || sanitize_key( wp_unslash( $_POST['dlck_subform_type'] ) ) !== 'settings_restore_snapshot' ) {
		return;
	}
	if ( empty( $_POST['dlck_restore_snapshot_action'] ) || sanitize_key( wp_unslash( $_POST['dlck_restore_snapshot_action'] ) ) !== 'dlck_restore_snapshot' ) {
		return;
	}
	if ( empty( $_POST['dlck_restore_snapshot_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dlck_restore_snapshot_nonce'] ) ), 'dlck_restore_snapshot_nonce' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( function_exists( 'dlck_multisite_policy_blocks_site_saves' ) && dlck_multisite_policy_blocks_site_saves() ) {
		dlck_snapshot_redirect( 0, 4 );
	}

	$snapshot_id = isset( $_POST['dlck_snapshot_id'] ) ? sanitize_key( wp_unslash( $_POST['dlck_snapshot_id'] ) ) : '';
	if ( $snapshot_id === '' ) {
		dlck_snapshot_redirect( 0, 1 );
	}

	$snapshot = dlck_get_snapshot_by_id( $snapshot_id );
	if ( empty( $snapshot ) || empty( $snapshot['settings'] ) || ! is_array( $snapshot['settings'] ) ) {
		dlck_snapshot_redirect( 0, 2 );
	}

	$current_settings = maybe_unserialize( get_option( 'dlck_lc_kit' ) );
	$current_settings = is_array( $current_settings ) ? $current_settings : array();

	$current_hash = md5( (string) wp_json_encode( $current_settings ) );
	$target_hash  = md5( (string) wp_json_encode( $snapshot['settings'] ) );
	if ( $current_hash === $target_hash ) {
		dlck_snapshot_redirect( 0, 3 );
	}

	dlck_store_settings_snapshot( $current_settings, 'pre_restore_backup' );

	$restored_settings = dlck_enforce_mutually_exclusive_options( $snapshot['settings'], $current_settings );
	if ( function_exists( 'dlck_normalize_scope_rules_settings' ) ) {
		$restored_settings = dlck_normalize_scope_rules_settings( $restored_settings );
	}
	update_option( 'dlck_lc_kit', $restored_settings );

	if ( function_exists( 'dlck_rebuild_all_inline_caches' ) ) {
		dlck_rebuild_all_inline_caches();
	}

	dlck_snapshot_redirect( 1, 0 );
}
function dlck_import_settings() {
	if ( ! isset( $_POST['dlck_subform_type'] ) || sanitize_key( wp_unslash( $_POST['dlck_subform_type'] ) ) !== 'settings_import' ) {
		return;
	}
	if ( empty( $_POST['dlck_import_settings_action'] ) || sanitize_key( wp_unslash( $_POST['dlck_import_settings_action'] ) ) !== 'dlck_import_settings' ) {
		return;
	}
	if ( empty( $_POST['dlck_import_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dlck_import_settings_nonce'] ) ), 'dlck_import_settings_nonce' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( function_exists( 'dlck_multisite_policy_blocks_site_saves' ) && dlck_multisite_policy_blocks_site_saves() ) {
		wp_safe_redirect( admin_url( 'admin.php?page=divi_lc_kit&tab=settings' ) . '&import_success=0&import_msg=2' );
		exit;
	}

	if ( empty( $_FILES['import_file']['name'] ) || ! is_string( $_FILES['import_file']['name'] ) ) {
		dlck_set_import_fail_msg();
		return;
	}

	$extension = strtolower( pathinfo( wp_unslash( $_FILES['import_file']['name'] ), PATHINFO_EXTENSION ) );
	if ( $extension !== 'json' ) {
		wp_safe_redirect( admin_url( 'admin.php?page=divi_lc_kit&tab=settings' ) . '&import_success=0&import_msg=1' );
		exit;
	}

	$import_file = isset( $_FILES['import_file']['tmp_name'] ) ? wp_unslash( $_FILES['import_file']['tmp_name'] ) : '';
	if ( ! is_string( $import_file ) || $import_file === '' || ! is_uploaded_file( $import_file ) ) {
		dlck_set_import_fail_msg();
		return;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	// phpcs:ignore PHPCS_SecurityAudit.BadFunctions.FilesystemFunctions.WarnFilesystem
	$settings = json_decode( file_get_contents( $import_file ), true );
	if ( ! is_array( $settings ) ) {
		dlck_set_import_fail_msg();
		return;
	}

	if ( isset( $settings['settings'] ) ) {
		$imported_settings = maybe_unserialize( $settings['settings'] );
		if ( ! is_array( $imported_settings ) ) {
			dlck_set_import_fail_msg();
			return;
		}

		$current_settings = maybe_unserialize( get_option( 'dlck_lc_kit' ) );
		$current_settings = is_array( $current_settings ) ? $current_settings : array();
		$imported_settings = dlck_enforce_mutually_exclusive_options( $imported_settings, $current_settings );
		if ( function_exists( 'dlck_normalize_scope_rules_settings' ) ) {
			$imported_settings = dlck_normalize_scope_rules_settings( $imported_settings );
		}
		$current_hash = md5( (string) wp_json_encode( $current_settings ) );
		$target_hash  = md5( (string) wp_json_encode( $imported_settings ) );
		if ( $current_hash !== $target_hash ) {
			update_option( 'dlck_lc_kit', $imported_settings );
		}
	}
	if ( isset( $settings['customization'] ) ) {
		$all_options = wp_load_alloptions();
		foreach ( $all_options as $key => $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
			$name = explode( '_', $key );
			if ( isset( $name[0], $name[1] ) && $name[0] === 'dlck' && $name[1] === 'customize' ) {
				delete_option( $key );
			}
		}

		$customization = maybe_unserialize( $settings['customization'] );
		if ( is_array( $customization ) ) {
			foreach ( $customization as $key => $value ) {
				dlck_update_option_if_changed( (string) $key, $value );
			}
		}
	}
	if ( isset( $settings['modcustomization'] ) ) {
		$all_mods = get_theme_mods();
		foreach ( $all_mods as $key => $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
			$name = explode( '_', $key );
			if ( isset( $name[0], $name[1] ) && $name[0] === 'dlck' && $name[1] === 'modcustomize' ) {
				remove_theme_mod( $key );
			}
		}

		$modcustomization = maybe_unserialize( $settings['modcustomization'] );
		if ( is_array( $modcustomization ) ) {
			foreach ( $modcustomization as $key => $value ) {
				set_theme_mod( $key, $value );
			}
		}
	}

	require_once DLCK_LC_KIT_PLUGIN_DIR . 'functions/settings/static-css.php';
	dlck_create_static_css();
	wp_safe_redirect( admin_url( 'admin.php?page=divi_lc_kit&tab=settings' ) . '&import_success=1&import_msg=0' );
	exit;
}
function dlck_export_settings() {
	if ( ! isset( $_POST['dlck_subform_type'] ) || sanitize_key( wp_unslash( $_POST['dlck_subform_type'] ) ) !== 'settings_export' ) {
		return;
	}
	if ( empty( $_POST['dlck_export_settings_action'] ) || sanitize_key( wp_unslash( $_POST['dlck_export_settings_action'] ) ) !== 'dlck_export_settings' ) {
		return;
	}
	if ( empty( $_POST['dlck_export_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dlck_export_settings_nonce'] ) ), 'dlck_export_settings_nonce' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = array();
	if ( isset( $_POST['dlck_import_type_settings'] ) ) {
		$settings['settings'] = get_option( 'dlck_lc_kit' );
	}
	if ( isset( $_POST['dlck_import_type_customizer'] ) ) {
		$customizer_settings_array     = array();
		$customizer_mod_settings_array = array();
		$customizer_settings           = wp_load_alloptions();
		$customizer_mod_settings       = get_theme_mods();

		foreach ( $customizer_settings as $key => $value ) {
			$name = explode( '_', $key );
			if ( isset( $name[0], $name[1] ) && $name[0] === 'dlck' && $name[1] === 'customize' ) {
				$customizer_settings_array[ $key ] = $value;
			}
		}
		foreach ( $customizer_mod_settings as $key => $value ) {
			$name = explode( '_', $key );
			if ( isset( $name[0], $name[1] ) && $name[0] === 'dlck' && $name[1] === 'modcustomize' ) {
				$customizer_mod_settings_array[ $key ] = $value;
			}
		}
		$settings['customization']    = maybe_serialize( $customizer_settings_array );
		$settings['modcustomization'] = maybe_serialize( $customizer_mod_settings_array );
	}
	ignore_user_abort( true );
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=divi-lc-kit-settings-' . gmdate( 'm-d-Y' ) . '.json' );
	header( 'Expires: 0' );
	echo wp_json_encode( $settings );
	exit;
}

/**
 * Build a small cache-folder report.
 *
 * @param string $dir     Absolute folder path.
 * @param string $pattern Optional glob pattern.
 * @return array<string,mixed>
 */
function dlck_diagnostics_get_cache_report( string $dir, string $pattern = '*' ): array {
	$report = array(
		'dir'          => $dir,
		'exists'       => false,
		'writable'     => false,
		'file_count'   => 0,
		'total_bytes'  => 0,
		'recent_files' => array(),
	);

	if ( $dir === '' || ! is_dir( $dir ) ) {
		return $report;
	}

	$report['exists']   = true;
	$report['writable'] = is_writable( $dir );

	$glob_pattern = trailingslashit( $dir ) . ltrim( $pattern, '/' );
	$paths        = glob( $glob_pattern );
	if ( ! is_array( $paths ) ) {
		return $report;
	}

	$file_entries = array();
	foreach ( $paths as $path ) {
		if ( ! is_file( $path ) ) {
			continue;
		}
		$size = (int) filesize( $path );
		$time = (int) filemtime( $path );
		$file_entries[] = array(
			'name'      => basename( $path ),
			'bytes'     => $size,
			'modified'  => $time,
			'modified_gmt' => gmdate( 'c', $time ),
		);
		$report['total_bytes'] += $size;
	}

	$report['file_count'] = count( $file_entries );

	usort(
		$file_entries,
		static function ( array $a, array $b ): int {
			return (int) $b['modified'] <=> (int) $a['modified'];
		}
	);

	$report['recent_files'] = array_slice( $file_entries, 0, 20 );

	return $report;
}

/**
 * Build diagnostics payload used by the settings export action.
 *
 * @return array<string,mixed>
 */
function dlck_build_diagnostics_report(): array {
	$settings_raw = function_exists( 'dlck_get_effective_lc_kit_settings' )
		? dlck_get_effective_lc_kit_settings()
		: get_option( 'dlck_lc_kit' );
	$settings = is_array( $settings_raw ) ? $settings_raw : maybe_unserialize( $settings_raw );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	$enabled_options = array();
	foreach ( $settings as $key => $value ) {
		if ( strpos( (string) $key, 'dlck_' ) !== 0 ) {
			continue;
		}
		if ( ! empty( $value ) && (string) $value !== '0' ) {
			$enabled_options[] = (string) $key;
		}
	}
	sort( $enabled_options );

	$theme = wp_get_theme();

	$plugin_version = '';
	if ( function_exists( 'get_file_data' ) ) {
		$plugin_data = get_file_data(
			DLCK_LC_KIT_PLUGIN_DIR . 'lc-tweaks.php',
			array( 'Version' => 'Version' ),
			'plugin'
		);
		if ( isset( $plugin_data['Version'] ) ) {
			$plugin_version = (string) $plugin_data['Version'];
		}
	}

	$cron_hooks = array(
		'dlck_csc_run_auto_clear_after_updates',
		'dlck_woo_session_cleanup_cron',
		'action_scheduler_run_queue',
		'wp_version_check',
		'wp_update_plugins',
		'wp_update_themes',
	);
	$cron_events = array();
	foreach ( $cron_hooks as $hook ) {
		$next = wp_next_scheduled( $hook );
		$cron_events[ $hook ] = $next
			? array(
				'timestamp' => (int) $next,
				'gmt'       => gmdate( 'c', (int) $next ),
				'local'     => wp_date( 'c', (int) $next ),
			)
			: null;
	}

	$inline_cache_dir = function_exists( 'dlck_inline_assets_get_cache_dir' )
		? (string) dlck_inline_assets_get_cache_dir()
		: '';
	$lazy_cache_dir = function_exists( 'dlck_divi_lazy_get_cache_base_dir' )
		? (string) dlck_divi_lazy_get_cache_base_dir()
		: trailingslashit( WP_CONTENT_DIR . '/cache/lc-tweaks-lazy' );

	$active_plugins = (array) get_option( 'active_plugins', array() );
	if ( is_multisite() ) {
		$network_plugins = get_site_option( 'active_sitewide_plugins', array() );
		if ( is_array( $network_plugins ) ) {
			$active_plugins = array_values( array_unique( array_merge( $active_plugins, array_keys( $network_plugins ) ) ) );
		}
	}
	sort( $active_plugins );

	return array(
		'generated_gmt' => gmdate( 'c' ),
		'plugin'        => array(
			'name'    => 'LC Tweaks',
			'version' => $plugin_version,
		),
		'site'          => array(
			'home_url'       => home_url( '/' ),
			'site_url'       => site_url( '/' ),
			'wp_version'     => get_bloginfo( 'version' ),
			'php_version'    => PHP_VERSION,
			'multisite'      => is_multisite(),
			'locale'         => get_locale(),
			'timezone'       => wp_timezone_string(),
		),
		'theme'         => array(
			'name'       => $theme->get( 'Name' ),
			'version'    => $theme->get( 'Version' ),
			'stylesheet' => $theme->get_stylesheet(),
			'template'   => $theme->get_template(),
		),
		'integrations'  => array(
			'divi_theme_active'      => function_exists( 'dlck_is_divi_theme_active' ) ? dlck_is_divi_theme_active() : false,
			'woocommerce_active'     => function_exists( 'dlck_is_woocommerce_active' ) ? dlck_is_woocommerce_active() : false,
			'wp_rocket_available'    => function_exists( 'rocket_clean_domain' ),
			'litespeed_available'    => function_exists( 'dlck_misc_csc_litespeed_available' ) ? dlck_misc_csc_litespeed_available() : false,
			'w3_total_cache_available' => function_exists( 'dlck_misc_csc_w3_total_cache_available' ) ? dlck_misc_csc_w3_total_cache_available() : false,
			'nginx_helper_available' => function_exists( 'dlck_misc_csc_nginx_helper_available' ) ? dlck_misc_csc_nginx_helper_available() : false,
			'siteground_available'   => function_exists( 'dlck_misc_csc_siteground_available' ) ? dlck_misc_csc_siteground_available() : false,
			'wp_engine_available'    => function_exists( 'dlck_misc_csc_wp_engine_available' ) ? dlck_misc_csc_wp_engine_available() : false,
		),
		'lc_tweaks'     => array(
			'option_count'        => count( $settings ),
			'enabled_count'       => count( $enabled_options ),
			'enabled_options'     => $enabled_options,
			'preflight_conflicts' => function_exists( 'dlck_get_preflight_conflicts' ) ? dlck_get_preflight_conflicts( $settings ) : array(),
			'multisite_policy'    => function_exists( 'dlck_get_multisite_policy' )
				? array(
					'is_multisite'          => is_multisite(),
					'policy'                => dlck_get_multisite_policy(),
					'defaults_count'        => function_exists( 'dlck_get_multisite_default_settings' ) ? count( dlck_get_multisite_default_settings() ) : 0,
					'blocks_site_overrides' => function_exists( 'dlck_multisite_policy_blocks_site_saves' ) ? dlck_multisite_policy_blocks_site_saves() : false,
				)
				: array(),
			'scope_rules'         => array(
				'enabled'       => ( isset( $settings['dlck_scope_rules_enabled'] ) && (string) $settings['dlck_scope_rules_enabled'] === '1' ),
				'options'       => isset( $settings['dlck_scope_rules_options'] ) ? (string) $settings['dlck_scope_rules_options'] : '',
				'logged_state'  => isset( $settings['dlck_scope_rules_logged_state'] ) ? (string) $settings['dlck_scope_rules_logged_state'] : 'all',
				'roles'         => isset( $settings['dlck_scope_rules_roles'] ) ? (string) $settings['dlck_scope_rules_roles'] : '',
				'include_paths' => isset( $settings['dlck_scope_rules_include_paths'] ) ? (string) $settings['dlck_scope_rules_include_paths'] : '',
				'exclude_paths' => isset( $settings['dlck_scope_rules_exclude_paths'] ) ? (string) $settings['dlck_scope_rules_exclude_paths'] : '',
				'validation_messages' => function_exists( 'dlck_get_scope_rules_preflight_conflicts' ) ? dlck_get_scope_rules_preflight_conflicts( $settings ) : array(),
			),
			'woo_session_cleanup' => function_exists( 'dlck_get_woo_session_cleanup_health_snapshot' ) ? dlck_get_woo_session_cleanup_health_snapshot() : array(),
		),
		'cache'         => array(
			'inline_assets' => dlck_diagnostics_get_cache_report( $inline_cache_dir, 'dlck-inline-*.*' ),
			'lazy_load'     => dlck_diagnostics_get_cache_report( $lazy_cache_dir, '*' ),
		),
		'cron'          => array(
			'wp_cron_disabled' => ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ),
			'events'           => $cron_events,
		),
		'plugins'       => array(
			'active_plugins' => $active_plugins,
		),
	);
}

/**
 * Export diagnostics JSON from the Settings tab.
 */
function dlck_export_diagnostics() {
	if ( ! isset( $_POST['dlck_subform_type'] ) || sanitize_key( wp_unslash( $_POST['dlck_subform_type'] ) ) !== 'settings_diagnostics' ) {
		return;
	}
	if ( empty( $_POST['dlck_export_diagnostics_action'] ) || sanitize_key( wp_unslash( $_POST['dlck_export_diagnostics_action'] ) ) !== 'dlck_export_diagnostics' ) {
		return;
	}
	if ( empty( $_POST['dlck_export_diagnostics_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dlck_export_diagnostics_nonce'] ) ), 'dlck_export_diagnostics_nonce' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$report = dlck_build_diagnostics_report();

	ignore_user_abort( true );
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=lc-tweaks-diagnostics-' . gmdate( 'Y-m-d-His' ) . '.json' );
	header( 'Expires: 0' );
	echo wp_json_encode( $report, JSON_PRETTY_PRINT );
	exit;
}
