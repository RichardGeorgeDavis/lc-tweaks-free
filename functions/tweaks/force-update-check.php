<?php
/**
 * Manual admin action: force plugin and theme update checks.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_post_dlck_force_update_check', 'dlck_force_update_check_action' );
add_action( 'admin_notices', 'dlck_force_update_check_notice' );

/**
 * Return update-check capabilities for the current user.
 *
 * @return array{plugins:bool,themes:bool}
 */
function dlck_force_update_check_permissions(): array {
	return array(
		'plugins' => current_user_can( 'update_plugins' ),
		'themes'  => current_user_can( 'update_themes' ),
	);
}

/**
 * Whether the current user can run at least one update check type.
 */
function dlck_can_force_update_check(): bool {
	$permissions = dlck_force_update_check_permissions();
	return ! empty( $permissions['plugins'] ) || ! empty( $permissions['themes'] );
}

/**
 * Build a compact scope token for notices/redirects.
 *
 * @param array{plugins:bool,themes:bool} $permissions Update-check permissions.
 */
function dlck_force_update_check_scope( array $permissions ): string {
	if ( ! empty( $permissions['plugins'] ) && ! empty( $permissions['themes'] ) ) {
		return 'plugins_themes';
	}

	if ( ! empty( $permissions['plugins'] ) ) {
		return 'plugins';
	}

	if ( ! empty( $permissions['themes'] ) ) {
		return 'themes';
	}

	return '';
}

/**
 * Run fresh plugin/theme update checks and redirect back to Maintenance.
 */
function dlck_force_update_check_action(): void {
	$permissions = dlck_force_update_check_permissions();
	if ( ! dlck_can_force_update_check() ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'lc-tweaks' ) );
	}

	check_admin_referer( 'dlck_force_update_check' );

	$redirect_url = admin_url( 'admin.php?page=lc_tweaks&tab=maintenance' );
	$scope        = dlck_force_update_check_scope( $permissions );

	if ( get_transient( 'dlck_force_update_check_lock' ) ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'dlck_force_update_check'       => 'locked',
					'dlck_force_update_check_scope' => $scope,
				),
				$redirect_url
			)
		);
		exit;
	}

	set_transient( 'dlck_force_update_check_lock', 1, 30 );

	if ( ! function_exists( 'wp_clean_update_cache' ) || ! function_exists( 'wp_update_plugins' ) || ! function_exists( 'wp_update_themes' ) ) {
		require_once ABSPATH . 'wp-includes/update.php';
	}

	if ( ! empty( $permissions['plugins'] ) && ! empty( $permissions['themes'] ) && function_exists( 'wp_clean_update_cache' ) ) {
		wp_clean_update_cache();
	} else {
		if ( ! empty( $permissions['plugins'] ) && function_exists( 'wp_clean_plugins_cache' ) ) {
			wp_clean_plugins_cache( true );
		}
		if ( ! empty( $permissions['themes'] ) && function_exists( 'wp_clean_themes_cache' ) ) {
			wp_clean_themes_cache( true );
		}
	}

	if ( ! empty( $permissions['themes'] ) ) {
		wp_update_themes();
	}
	if ( ! empty( $permissions['plugins'] ) ) {
		wp_update_plugins();
	}

	delete_transient( 'dlck_force_update_check_lock' );

	wp_safe_redirect(
		add_query_arg(
			array(
				'dlck_force_update_check'       => 'done',
				'dlck_force_update_check_scope' => $scope,
			),
			$redirect_url
		)
	);
	exit;
}

/**
 * Output notices after running the manual update check.
 */
function dlck_force_update_check_notice(): void {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || $screen->id !== 'toplevel_page_lc_tweaks' ) {
		return;
	}

	$status = isset( $_GET['dlck_force_update_check'] ) ? sanitize_key( wp_unslash( $_GET['dlck_force_update_check'] ) ) : '';
	$scope  = isset( $_GET['dlck_force_update_check_scope'] ) ? sanitize_key( wp_unslash( $_GET['dlck_force_update_check_scope'] ) ) : '';
	if ( $status === '' ) {
		return;
	}

	if ( $status === 'locked' ) {
		echo '<div class="notice notice-warning is-dismissible"><p>'
			. esc_html__( 'A force update check is already running. Please try again in a moment.', 'lc-tweaks' )
			. '</p></div>';
		return;
	}

	if ( $status === 'done' ) {
		$message = __( 'Plugin and theme update checks have been refreshed. Third-party updaters may still apply their own caches.', 'lc-tweaks' );
		if ( $scope === 'plugins' ) {
			$message = __( 'Plugin update checks have been refreshed. Third-party updaters may still apply their own caches.', 'lc-tweaks' );
		} elseif ( $scope === 'themes' ) {
			$message = __( 'Theme update checks have been refreshed. Third-party updaters may still apply their own caches.', 'lc-tweaks' );
		}

		echo '<div class="notice notice-success is-dismissible"><p>'
			. esc_html( $message )
			. '</p></div>';
	}
}
