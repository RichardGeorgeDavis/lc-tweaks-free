<?php
/**
 * One-time migration from bundled Divi Accessibility to the standalone plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DLCK_DIVI_ACCESSIBILITY_MIGRATION_PLUGIN        = 'divi-accessibility/divi-accessibility.php';
const DLCK_DIVI_ACCESSIBILITY_MIGRATION_PACKAGE_URL   = 'https://github.com/RichardGeorgeDavis/divi-accessibility/releases/download/2.1.1/divi-accessibility-2.1.1.zip';
const DLCK_DIVI_ACCESSIBILITY_MIGRATION_TARGET        = '2.1.1';
const DLCK_DIVI_ACCESSIBILITY_MIGRATION_STATUS_OPTION = 'dlck_divi_accessibility_external_migration';

add_action( 'admin_init', 'dlck_divi_accessibility_migration_maybe_run' );
add_action( 'network_admin_edit_dlck_retry_divi_accessibility_migration', 'dlck_divi_accessibility_migration_retry_action' );
add_action( 'admin_post_dlck_retry_divi_accessibility_migration', 'dlck_divi_accessibility_migration_retry_action' );
add_action( 'admin_notices', 'dlck_divi_accessibility_migration_notice' );
add_action( 'network_admin_notices', 'dlck_divi_accessibility_migration_notice' );
add_action( 'upgrader_process_complete', 'dlck_divi_accessibility_migration_after_plugin_update', 20, 2 );

/**
 * Load WordPress plugin APIs used by the migration.
 */
function dlck_divi_accessibility_migration_load_plugin_api(): void {
	if ( ! function_exists( 'get_plugins' ) || ! function_exists( 'is_plugin_active' ) || ! function_exists( 'activate_plugin' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
}

/**
 * Determine whether LC Tweaks is network active.
 */
function dlck_divi_accessibility_migration_is_network_mode(): bool {
	if ( ! is_multisite() ) {
		return false;
	}

	dlck_divi_accessibility_migration_load_plugin_api();

	return is_plugin_active_for_network( plugin_basename( DLCK_LC_KIT_PLUGIN_DIR . 'lc-tweaks.php' ) );
}

/**
 * Normalize stored migration status for backward compatibility.
 *
 * @param array<string,mixed> $status Raw stored status.
 * @return array<string,mixed>
 */
function dlck_divi_accessibility_migration_normalize_status( array $status ): array {
	$network_mode = dlck_divi_accessibility_migration_is_network_mode();
	$normalized   = array(
		'status'         => isset( $status['status'] ) ? sanitize_key( (string) $status['status'] ) : '',
		'code'           => isset( $status['code'] ) ? sanitize_key( (string) $status['code'] ) : '',
		'message'        => isset( $status['message'] ) ? (string) $status['message'] : '',
		'version'        => isset( $status['version'] ) ? (string) $status['version'] : '',
		'mode'           => isset( $status['mode'] ) ? sanitize_key( (string) $status['mode'] ) : ( $network_mode ? 'network' : 'site' ),
		'required'       => ! empty( $status['required'] ),
		'installed_once' => array_key_exists( 'installed_once', $status ) ? ! empty( $status['installed_once'] ) : false,
		'updated'        => isset( $status['updated'] ) ? (int) $status['updated'] : 0,
	);

	if ( ! array_key_exists( 'installed_once', $status ) && $normalized['status'] === 'success' ) {
		$normalized['installed_once'] = true;
	}

	return $normalized;
}

/**
 * Build a status payload with defaults.
 *
 * @param string $status  Status code.
 * @param string $message Human-readable message.
 * @param array  $extra   Additional status fields.
 * @return array<string,mixed>
 */
function dlck_divi_accessibility_migration_build_status( string $status, string $message, array $extra = array() ): array {
	$payload = array_merge(
		array(
			'status'         => sanitize_key( $status ),
			'code'           => '',
			'message'        => $message,
			'version'        => '',
			'mode'           => dlck_divi_accessibility_migration_is_network_mode() ? 'network' : 'site',
			'required'       => false,
			'installed_once' => false,
		),
		$extra
	);

	return dlck_divi_accessibility_migration_normalize_status( $payload );
}

/**
 * Get the stored migration status for the current activation mode.
 */
function dlck_divi_accessibility_migration_get_status(): array {
	$status = dlck_divi_accessibility_migration_is_network_mode()
		? get_site_option( DLCK_DIVI_ACCESSIBILITY_MIGRATION_STATUS_OPTION, array() )
		: get_option( DLCK_DIVI_ACCESSIBILITY_MIGRATION_STATUS_OPTION, array() );

	if ( ! is_array( $status ) ) {
		$status = array();
	}

	return dlck_divi_accessibility_migration_normalize_status( $status );
}

/**
 * Store the migration status for the current activation mode.
 */
function dlck_divi_accessibility_migration_update_status( array $status ): void {
	$status   = dlck_divi_accessibility_migration_normalize_status( $status );
	$existing = dlck_divi_accessibility_migration_get_status();

	unset( $status['updated'], $existing['updated'] );
	if ( $status === $existing ) {
		return;
	}

	$status['updated'] = time();

	if ( dlck_divi_accessibility_migration_is_network_mode() ) {
		update_site_option( DLCK_DIVI_ACCESSIBILITY_MIGRATION_STATUS_OPTION, $status );
		return;
	}

	update_option( DLCK_DIVI_ACCESSIBILITY_MIGRATION_STATUS_OPTION, $status, false );
}

/**
 * Read raw LC Tweaks settings for a single site.
 *
 * @param int|null $blog_id Optional site ID in multisite.
 * @return array<string,mixed>
 */
function dlck_divi_accessibility_migration_get_legacy_settings_for_blog( ?int $blog_id = null ): array {
	$restore = false;

	if ( null !== $blog_id && is_multisite() && get_current_blog_id() !== $blog_id ) {
		switch_to_blog( $blog_id );
		$restore = true;
	}

	try {
		$settings = maybe_unserialize( get_option( 'dlck_lc_kit', array() ) );
		return is_array( $settings ) ? $settings : array();
	} finally {
		if ( $restore ) {
			restore_current_blog();
		}
	}
}

/**
 * Whether a site previously enabled the bundled Divi Accessibility module.
 *
 * @param int|null $blog_id Optional site ID in multisite.
 */
function dlck_divi_accessibility_migration_site_previously_used_legacy_module( ?int $blog_id = null ): bool {
	$settings = dlck_divi_accessibility_migration_get_legacy_settings_for_blog( $blog_id );

	return isset( $settings['dlck_divi_accessibility'] ) && (string) $settings['dlck_divi_accessibility'] === '1';
}

/**
 * Whether any site on the network previously enabled the bundled module.
 */
function dlck_divi_accessibility_migration_network_previously_used_legacy_module(): bool {
	static $required = null;

	if ( null !== $required ) {
		return $required;
	}

	$required = false;
	if ( ! is_multisite() || ! function_exists( 'get_sites' ) ) {
		return $required;
	}

	$site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);

	foreach ( $site_ids as $site_id ) {
		if ( dlck_divi_accessibility_migration_site_previously_used_legacy_module( (int) $site_id ) ) {
			$required = true;
			break;
		}
	}

	return $required;
}

/**
 * Whether the standalone plugin is required for this site/network.
 */
function dlck_divi_accessibility_migration_is_required_for_context(): bool {
	$status = dlck_divi_accessibility_migration_get_status();
	if ( ! empty( $status['required'] ) ) {
		return true;
	}

	if ( dlck_divi_accessibility_migration_is_network_mode() ) {
		return dlck_divi_accessibility_migration_network_previously_used_legacy_module();
	}

	return dlck_divi_accessibility_migration_site_previously_used_legacy_module();
}

/**
 * Whether the current user can activate the standalone plugin in this context.
 */
function dlck_divi_accessibility_migration_current_user_can_activate(): bool {
	if ( dlck_divi_accessibility_migration_is_network_mode() ) {
		return current_user_can( 'manage_network_plugins' );
	}

	return current_user_can( 'activate_plugins' );
}

/**
 * Whether the current user can install/update the standalone plugin in this context.
 */
function dlck_divi_accessibility_migration_current_user_can_install(): bool {
	if ( dlck_divi_accessibility_migration_is_network_mode() ) {
		return current_user_can( 'manage_network_plugins' );
	}

	return current_user_can( 'install_plugins' );
}

/**
 * Whether the current user can complete the required action for the standalone plugin.
 *
 * @param bool $needs_install Whether the action includes an install/update step.
 */
function dlck_divi_accessibility_migration_current_user_can_run( bool $needs_install = true ): bool {
	if ( ! dlck_divi_accessibility_migration_current_user_can_activate() ) {
		return false;
	}

	if ( ! $needs_install ) {
		return true;
	}

	return dlck_divi_accessibility_migration_current_user_can_install();
}

/**
 * Whether WordPress can install the standalone plugin without an interactive credentials prompt.
 */
function dlck_divi_accessibility_migration_can_install_unattended(): bool {
	require_once ABSPATH . 'wp-admin/includes/file.php';

	$method = get_filesystem_method( array(), WP_PLUGIN_DIR, true );

	return $method === 'direct';
}

/**
 * Return installed standalone Divi Accessibility plugin data, if present.
 */
function dlck_divi_accessibility_migration_get_installed_plugin() {
	dlck_divi_accessibility_migration_load_plugin_api();

	$plugins = get_plugins();

	return $plugins[ DLCK_DIVI_ACCESSIBILITY_MIGRATION_PLUGIN ] ?? null;
}

/**
 * Whether the standalone plugin is installed.
 */
function dlck_divi_accessibility_migration_is_standalone_installed(): bool {
	return is_array( dlck_divi_accessibility_migration_get_installed_plugin() );
}

/**
 * Whether the standalone plugin needs an install or update step before activation.
 *
 * @param array<string,mixed>|null $plugin Optional installed plugin data.
 */
function dlck_divi_accessibility_migration_needs_install_step( $plugin = null ): bool {
	if ( ! is_array( $plugin ) ) {
		$plugin = dlck_divi_accessibility_migration_get_installed_plugin();
	}

	if ( ! is_array( $plugin ) ) {
		return true;
	}

	$version = isset( $plugin['Version'] ) ? (string) $plugin['Version'] : '';

	return version_compare( $version, DLCK_DIVI_ACCESSIBILITY_MIGRATION_TARGET, '<' );
}

/**
 * Whether the standalone plugin is active in the requested activation mode.
 */
function dlck_divi_accessibility_migration_is_standalone_active(): bool {
	dlck_divi_accessibility_migration_load_plugin_api();

	if ( is_multisite() && is_plugin_active_for_network( DLCK_DIVI_ACCESSIBILITY_MIGRATION_PLUGIN ) ) {
		return true;
	}

	return is_plugin_active( DLCK_DIVI_ACCESSIBILITY_MIGRATION_PLUGIN );
}

/**
 * Mark migration success when the standalone plugin is installed and active.
 */
function dlck_divi_accessibility_migration_reconcile_success(): bool {
	$plugin = dlck_divi_accessibility_migration_get_installed_plugin();
	if ( ! is_array( $plugin ) ) {
		return false;
	}

	$version = isset( $plugin['Version'] ) ? (string) $plugin['Version'] : '';
	if ( version_compare( $version, DLCK_DIVI_ACCESSIBILITY_MIGRATION_TARGET, '<' ) ) {
		return false;
	}

	if ( ! dlck_divi_accessibility_migration_is_standalone_active() ) {
		return false;
	}

	$previous       = dlck_divi_accessibility_migration_get_status();
	$required       = ! empty( $previous['required'] ) || dlck_divi_accessibility_migration_is_required_for_context();
	$installed_once = ! empty( $previous['installed_once'] ) || $required;

	dlck_divi_accessibility_migration_update_status(
		dlck_divi_accessibility_migration_build_status(
			'success',
			__( 'Divi Accessibility is installed and active as a standalone plugin.', 'lc-tweaks' ),
			array(
				'version'        => $version,
				'required'       => $required,
				'installed_once' => $installed_once,
			)
		)
	);

	return true;
}

/**
 * Store a passive informational state for sites that do not require migration.
 */
function dlck_divi_accessibility_migration_mark_not_required(): void {
	dlck_divi_accessibility_migration_update_status(
		dlck_divi_accessibility_migration_build_status(
			'not_required',
			__( 'Divi Accessibility is available as a separate plugin and is not required for this site.', 'lc-tweaks' )
		)
	);
}

/**
 * Mark a site as needing a recommendation instead of an automatic reinstall.
 *
 * @param string $code           Recommendation code.
 * @param bool   $required       Whether the site required the original migration.
 * @param bool   $installed_once Whether standalone install succeeded before.
 * @param string $version        Installed standalone version, if available.
 */
function dlck_divi_accessibility_migration_mark_recommended( string $code, bool $required, bool $installed_once, string $version = '' ): void {
	$message = $code === 'inactive'
		? __( 'Divi Accessibility was previously migrated for this site, but the standalone plugin is currently inactive. Activate it again if you still need that functionality.', 'lc-tweaks' )
		: __( 'Divi Accessibility was previously migrated for this site, but the standalone plugin is no longer installed. Install it again if you still need that functionality.', 'lc-tweaks' );

	dlck_divi_accessibility_migration_update_status(
		dlck_divi_accessibility_migration_build_status(
			'recommended',
			$message,
			array(
				'code'           => $code,
				'version'        => $version,
				'required'       => $required,
				'installed_once' => $installed_once,
			)
		)
	);
}

/**
 * Run the migration on eligible admin loads.
 *
 * @param string $reason Invocation reason: passive, update, or retry.
 */
function dlck_divi_accessibility_migration_maybe_run( string $reason = 'passive' ): void {
	if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
		return;
	}

	$status = dlck_divi_accessibility_migration_get_status();
	if ( dlck_divi_accessibility_migration_reconcile_success() ) {
		return;
	}

	$required       = ! empty( $status['required'] ) || dlck_divi_accessibility_migration_is_required_for_context();
	$installed_once = ! empty( $status['installed_once'] );
	$plugin         = dlck_divi_accessibility_migration_get_installed_plugin();
	$installed      = is_array( $plugin );
	$version        = $installed && isset( $plugin['Version'] ) ? (string) $plugin['Version'] : '';
	$active         = $installed && dlck_divi_accessibility_migration_is_standalone_active();
	$needs_install  = dlck_divi_accessibility_migration_needs_install_step( $plugin );

	if ( $installed_once && ! $active ) {
		dlck_divi_accessibility_migration_mark_recommended(
			$installed ? 'inactive' : 'missing',
			true,
			true,
			$version
		);

		if ( $reason !== 'retry' ) {
			return;
		}
	} elseif ( ! $required && ! $installed_once ) {
		dlck_divi_accessibility_migration_mark_not_required();
		return;
	}

	if ( $reason !== 'retry' && isset( $status['status'] ) && $status['status'] === 'failed' ) {
		return;
	}

	if ( ! dlck_divi_accessibility_migration_current_user_can_run( $needs_install ) ) {
		if ( $required && ! $installed_once ) {
			dlck_divi_accessibility_migration_fail(
				'insufficient_permissions',
				$needs_install
					? __( 'Divi Accessibility could not be installed automatically because the current user cannot install and activate plugins.', 'lc-tweaks' )
					: __( 'Divi Accessibility could not be activated automatically because the current user cannot activate plugins.', 'lc-tweaks' )
			);
		}
		return;
	}

	dlck_divi_accessibility_migration_install_and_activate( $required || $installed_once, $installed_once );
}

/**
 * Install/update and activate the standalone Divi Accessibility plugin.
 *
 * @param bool $required       Whether the site required the original migration.
 * @param bool $installed_once Whether standalone install succeeded before.
 */
function dlck_divi_accessibility_migration_install_and_activate( bool $required = false, bool $installed_once = false ): void {
	try {
		dlck_divi_accessibility_migration_load_plugin_api();

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$plugin = dlck_divi_accessibility_migration_get_installed_plugin();
		if ( ! is_array( $plugin ) || version_compare( (string) ( $plugin['Version'] ?? '' ), DLCK_DIVI_ACCESSIBILITY_MIGRATION_TARGET, '<' ) ) {
			if ( ! dlck_divi_accessibility_migration_can_install_unattended() ) {
				dlck_divi_accessibility_migration_fail(
					'filesystem_credentials_required',
					sprintf(
						/* translators: %s: target Divi Accessibility version. */
						__( 'This host requires manual plugin installation access, so LC Tweaks could not install Divi Accessibility automatically. Download and install Divi Accessibility %s, then activate it.', 'lc-tweaks' ),
						DLCK_DIVI_ACCESSIBILITY_MIGRATION_TARGET
					)
				);
				return;
			}

			$skin     = new Automatic_Upgrader_Skin();
			$upgrader = new Plugin_Upgrader( $skin );
			$result   = $upgrader->install(
				DLCK_DIVI_ACCESSIBILITY_MIGRATION_PACKAGE_URL,
				array(
					'overwrite_package' => true,
				)
			);

			if ( is_wp_error( $result ) ) {
				dlck_divi_accessibility_migration_fail( $result->get_error_code(), $result->get_error_message() );
				return;
			}

			if ( ! $result ) {
				$error = method_exists( $skin, 'get_errors' ) ? $skin->get_errors() : null;
				if ( $error instanceof WP_Error && $error->has_errors() ) {
					dlck_divi_accessibility_migration_fail( $error->get_error_code(), $error->get_error_message() );
					return;
				}

				dlck_divi_accessibility_migration_fail( 'install_failed', __( 'WordPress could not install the standalone Divi Accessibility package.', 'lc-tweaks' ) );
				return;
			}

			wp_clean_plugins_cache( true );
			$plugin = dlck_divi_accessibility_migration_get_installed_plugin();
			if ( ! is_array( $plugin ) ) {
				dlck_divi_accessibility_migration_fail( 'plugin_missing_after_install', __( 'The package installed, but WordPress did not find divi-accessibility/divi-accessibility.php afterward.', 'lc-tweaks' ) );
				return;
			}
		}

		$network_wide = dlck_divi_accessibility_migration_is_network_mode();
		if ( ! dlck_divi_accessibility_migration_is_standalone_active() || ( $network_wide && ! is_plugin_active_for_network( DLCK_DIVI_ACCESSIBILITY_MIGRATION_PLUGIN ) ) ) {
			$activation = activate_plugin( DLCK_DIVI_ACCESSIBILITY_MIGRATION_PLUGIN, '', $network_wide, false );
			if ( is_wp_error( $activation ) ) {
				dlck_divi_accessibility_migration_fail( $activation->get_error_code(), $activation->get_error_message() );
				return;
			}
		}

		dlck_divi_accessibility_migration_update_status(
			dlck_divi_accessibility_migration_build_status(
				'success',
				__( 'Divi Accessibility was installed and activated as a standalone plugin.', 'lc-tweaks' ),
				array(
					'version'        => isset( $plugin['Version'] ) ? (string) $plugin['Version'] : DLCK_DIVI_ACCESSIBILITY_MIGRATION_TARGET,
					'required'       => $required,
					'installed_once' => true,
				)
			)
		);
	} catch ( Throwable $throwable ) {
		dlck_divi_accessibility_migration_fail( 'exception', $throwable->getMessage() );
	}
}

/**
 * Store a failed migration state.
 */
function dlck_divi_accessibility_migration_fail( string $code, string $message ): void {
	$previous = dlck_divi_accessibility_migration_get_status();

	dlck_divi_accessibility_migration_update_status(
		dlck_divi_accessibility_migration_build_status(
			'failed',
			$message,
			array(
				'code'           => sanitize_key( $code ),
				'version'        => isset( $previous['version'] ) ? (string) $previous['version'] : '',
				'required'       => ! empty( $previous['required'] ) || dlck_divi_accessibility_migration_is_required_for_context(),
				'installed_once' => ! empty( $previous['installed_once'] ),
			)
		)
	);
}

/**
 * Retry the migration from an admin notice action.
 */
function dlck_divi_accessibility_migration_retry_action(): void {
	$plugin        = dlck_divi_accessibility_migration_get_installed_plugin();
	$needs_install = dlck_divi_accessibility_migration_needs_install_step( $plugin );

	if ( ! dlck_divi_accessibility_migration_current_user_can_run( $needs_install ) ) {
		wp_die(
			esc_html(
				$needs_install
					? __( 'Sorry, you are not allowed to install and activate plugins.', 'lc-tweaks' )
					: __( 'Sorry, you are not allowed to activate plugins.', 'lc-tweaks' )
			)
		);
	}

	check_admin_referer( 'dlck_retry_divi_accessibility_migration' );
	dlck_divi_accessibility_migration_maybe_run( 'retry' );

	$redirect = wp_get_referer();
	if ( ! $redirect ) {
		$redirect = dlck_divi_accessibility_migration_is_network_mode()
			? network_admin_url( 'plugins.php' )
			: admin_url( 'plugins.php' );
	}

	wp_safe_redirect( add_query_arg( 'dlck_divi_accessibility_migration_retry', '1', $redirect ) );
	exit;
}

/**
 * Retry after future LC Tweaks plugin updates where this migration code is already loaded.
 *
 * @param WP_Upgrader $upgrader   WordPress upgrader instance.
 * @param array       $hook_extra Upgrader hook context.
 */
function dlck_divi_accessibility_migration_after_plugin_update( $upgrader, $hook_extra ): void {
	unset( $upgrader );

	if ( empty( $hook_extra['type'] ) || $hook_extra['type'] !== 'plugin' ) {
		return;
	}

	$plugins = array();
	if ( ! empty( $hook_extra['plugin'] ) ) {
		$plugins[] = $hook_extra['plugin'];
	}
	if ( ! empty( $hook_extra['plugins'] ) && is_array( $hook_extra['plugins'] ) ) {
		$plugins = array_merge( $plugins, $hook_extra['plugins'] );
	}

	if ( in_array( plugin_basename( DLCK_LC_KIT_PLUGIN_DIR . 'lc-tweaks.php' ), $plugins, true ) ) {
		dlck_divi_accessibility_migration_maybe_run( 'update' );
	}
}

/**
 * Output retry and recommendation notices.
 */
function dlck_divi_accessibility_migration_notice(): void {
	$status = dlck_divi_accessibility_migration_get_status();
	if ( empty( $status['status'] ) ) {
		return;
	}

	$retry_requested = isset( $_GET['dlck_divi_accessibility_migration_retry'] );
	if ( $status['status'] === 'success' && $retry_requested ) {
		echo '<div class="notice notice-success is-dismissible"><p>'
			. esc_html( $status['message'] ?? __( 'Divi Accessibility is installed and active as a standalone plugin.', 'lc-tweaks' ) )
			. '</p></div>';
		return;
	}

	$action_url = dlck_divi_accessibility_migration_is_network_mode()
		? network_admin_url( 'edit.php?action=dlck_retry_divi_accessibility_migration' )
		: admin_url( 'admin-post.php?action=dlck_retry_divi_accessibility_migration' );
	$retry_url  = wp_nonce_url( $action_url, 'dlck_retry_divi_accessibility_migration' );
	$plugin     = dlck_divi_accessibility_migration_get_installed_plugin();

	if ( $status['status'] === 'failed' ) {
		$needs_install = dlck_divi_accessibility_migration_needs_install_step( $plugin );
		$can_retry     = dlck_divi_accessibility_migration_current_user_can_run( $needs_install );
		if ( ! $can_retry ) {
			return;
		}
	}

	if ( $status['status'] === 'failed' ) {
		$message = $status['message'] !== ''
			? (string) $status['message']
			: __( 'Divi Accessibility could not be installed automatically.', 'lc-tweaks' );
		$link_label = $needs_install
			? __( 'Retry Divi Accessibility installation.', 'lc-tweaks' )
			: __( 'Retry Divi Accessibility activation.', 'lc-tweaks' );
		$manual_link = '';
		if ( $needs_install ) {
			$manual_link = sprintf(
				' <a href="%1$s">%2$s</a>',
				esc_url( DLCK_DIVI_ACCESSIBILITY_MIGRATION_PACKAGE_URL ),
				esc_html(
					sprintf(
						/* translators: %s: target Divi Accessibility version. */
						__( 'Download Divi Accessibility %s.', 'lc-tweaks' ),
						DLCK_DIVI_ACCESSIBILITY_MIGRATION_TARGET
					)
				)
			);
		}

		printf(
			'<div class="notice notice-error"><p>%1$s <a href="%2$s">%3$s</a>%4$s</p></div>',
			esc_html( $message ),
			esc_url( $retry_url ),
			esc_html( $link_label ),
			$manual_link
		);
		return;
	}

	if ( $status['status'] !== 'recommended' ) {
		return;
	}

	$needs_install = dlck_divi_accessibility_migration_needs_install_step( $plugin );
	if ( ! dlck_divi_accessibility_migration_current_user_can_run( $needs_install ) ) {
		return;
	}

	$link_label = $status['code'] === 'inactive'
		? __( 'Activate Divi Accessibility.', 'lc-tweaks' )
		: __( 'Install Divi Accessibility.', 'lc-tweaks' );

	printf(
		'<div class="notice notice-warning is-dismissible"><p>%1$s <a href="%2$s">%3$s</a></p></div>',
		esc_html( (string) $status['message'] ),
		esc_url( $retry_url ),
		esc_html( $link_label )
	);
}
