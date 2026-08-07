<?php
/**
 * @package Store Admin View @ WordPress Dashboard
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DLCK_STORE_ADMIN_COOKIE = 'dlck_store_admin';

add_action( 'admin_bar_menu', 'dlck_add_store_admin_toggle_button', 100 );
add_action( 'admin_init', 'dlck_store_admin_apply_performance_policy', -999 );
add_action( 'admin_init', 'dlck_handle_store_admin_toggle', -1000 );
add_action( 'admin_menu', 'dlck_filter_admin_menu_for_store_admin', 9999 );
add_filter( 'admin_body_class', 'dlck_store_admin_body_class' );
add_action( 'dlck_collect_inline_assets_admin', 'dlck_collect_store_admin_toggle_styles' );

/**
 * Whether Store Admin view is active for the current WooCommerce manager.
 */
function dlck_store_admin_mode_is_active(): bool {
	if ( ! is_admin() || ! current_user_can( 'manage_woocommerce' ) ) {
		return false;
	}

	return isset( $_COOKIE[ DLCK_STORE_ADMIN_COOKIE ] )
		&& (string) $_COOKIE[ DLCK_STORE_ADMIN_COOKIE ] === '1';
}

/**
 * Whether the optional Store Admin performance protections are active.
 */
function dlck_store_admin_performance_is_active(): bool {
	$enabled = function_exists( 'dlck_get_setting_from_snapshot' )
		? (string) dlck_get_setting_from_snapshot( 'dlck_woo_store_admin_performance', '1' ) === '1'
		: function_exists( 'dlck_get_option' ) && (string) dlck_get_option( 'dlck_woo_store_admin_performance', '1' ) === '1';

	return dlck_store_admin_mode_is_active()
		&& $enabled
		&& ! dlck_store_admin_is_background_request()
		&& ! dlck_store_admin_is_maintenance_request();
}

/**
 * Return true for background and API requests that must retain normal behavior.
 */
function dlck_store_admin_is_background_request(): bool {
	if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
		return true;
	}
	if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
		return true;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return true;
	}
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return true;
	}

	return false;
}

/**
 * Keep explicit maintenance and LC Tweaks requests fully functional.
 */
function dlck_store_admin_is_maintenance_request(): bool {
	global $pagenow;

	$maintenance_pages = array(
		'plugins.php',
		'plugin-install.php',
		'themes.php',
		'theme-install.php',
		'update.php',
		'update-core.php',
		'site-health.php',
		'admin-post.php',
	);

	if ( in_array( (string) $pagenow, $maintenance_pages, true ) ) {
		return true;
	}

	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return in_array( $page, array( 'lc_tweaks', 'divi_lc_kit', 'ld-care-plan-api-activation', 'wc-status' ), true )
		|| strpos( $page, 'wc_am_client_' ) === 0;
}

/**
 * Add Store Admin toggle button to the admin bar.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar object.
 */
function dlck_add_store_admin_toggle_button( $wp_admin_bar ): void {
	if ( ! is_admin() || ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	$is_store_admin = dlck_store_admin_mode_is_active();
	$url            = add_query_arg( 'toggle_store_admin', $is_store_admin ? '0' : '1', admin_url( 'admin.php?page=wc-orders' ) );
	$url            = wp_nonce_url( $url, 'dlck_store_admin_toggle', 'dlck_store_admin_nonce' );

	$wp_admin_bar->add_node(
		array(
			'id'    => 'store-admin-toggle',
			'title' => $is_store_admin
				? '<span class="ab-icon dashicons dashicons-admin-generic"></span> ' . esc_html__( 'Switch to WP Admin', 'lc-tweaks' )
				: '<span class="ab-icon dashicons dashicons-cart"></span> ' . esc_html__( 'Switch to Store Admin', 'lc-tweaks' ),
			'href'  => $url,
		)
	);
}

/**
 * Set or delete the Store Admin cookie.
 */
function dlck_handle_store_admin_toggle(): void {
	if ( ! current_user_can( 'manage_woocommerce' ) || ! isset( $_GET['toggle_store_admin'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	if ( empty( $_GET['dlck_store_admin_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['dlck_store_admin_nonce'] ) ), 'dlck_store_admin_toggle' ) ) {
		return;
	}

	$enabled = sanitize_key( wp_unslash( $_GET['toggle_store_admin'] ) ) === '1';
	$path    = defined( 'ADMIN_COOKIE_PATH' ) && ADMIN_COOKIE_PATH ? ADMIN_COOKIE_PATH : COOKIEPATH;
	$options = array(
		'expires'  => $enabled ? time() + DAY_IN_SECONDS : time() - HOUR_IN_SECONDS,
		'path'     => $path,
		'domain'   => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
		'secure'   => is_ssl(),
		'httponly' => true,
		'samesite' => 'Lax',
	);

	setcookie( DLCK_STORE_ADMIN_COOKIE, $enabled ? '1' : '', $options );

	// Clear the legacy generic cookie if it exists.
	if ( isset( $_COOKIE['store_admin'] ) ) {
		setcookie( 'store_admin', '', array_merge( $options, array( 'expires' => time() - HOUR_IN_SECONDS ) ) );
		unset( $_COOKIE['store_admin'] );
	}

	if ( $enabled ) {
		$_COOKIE[ DLCK_STORE_ADMIN_COOKIE ] = '1';
	} else {
		unset( $_COOKIE[ DLCK_STORE_ADMIN_COOKIE ] );
	}

	$redirect = $enabled ? admin_url( 'admin.php?page=wc-orders' ) : admin_url();
	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Remove interactive maintenance checks from operational Store Admin requests.
 */
function dlck_store_admin_apply_performance_policy(): void {
	if ( ! dlck_store_admin_performance_is_active() || dlck_store_admin_is_background_request() || dlck_store_admin_is_maintenance_request() ) {
		return;
	}

	remove_action( 'admin_init', '_maybe_update_core' );
	remove_action( 'admin_init', '_maybe_update_plugins' );
	remove_action( 'admin_init', '_maybe_update_themes' );

	remove_action( 'admin_notices', 'update_nag', 3 );
	remove_action( 'network_admin_notices', 'update_nag', 3 );
	remove_action( 'admin_notices', 'maintenance_nag' );
	remove_action( 'admin_notices', 'dlck_lite_migration_notice' );
	remove_action( 'admin_notices', 'dlck_divi_accessibility_migration_notice' );
	remove_action( 'admin_notices', 'dlck_force_update_check_notice' );
}

/**
 * Filter admin menu items while in Store Admin mode.
 */
function dlck_filter_admin_menu_for_store_admin(): void {
	if ( ! dlck_store_admin_mode_is_active() ) {
		return;
	}

	global $menu;

	foreach ( $menu as $item ) {
		if ( ! isset( $item[2] ) ) {
			continue;
		}

		$slug   = (string) $item[2];
		$is_woo = $slug === 'woocommerce'
			|| strpos( $slug, 'wc-' ) === 0
			|| strpos( $slug, 'woocommerce' ) !== false
			|| strpos( $slug, 'edit.php?post_type=shop_' ) === 0
			|| strpos( $slug, 'edit.php?post_type=product' ) === 0
			|| $slug === 'profile.php';

		if ( ! $is_woo ) {
			remove_menu_page( $slug );
		}
	}
}

/**
 * Add a body class for screen-aware compatibility rules.
 */
function dlck_store_admin_body_class( string $classes ): string {
	if ( ! dlck_store_admin_mode_is_active() ) {
		return $classes;
	}

	return trim( $classes . ' dlck-store-admin-mode' . ( dlck_store_admin_performance_is_active() ? ' dlck-store-admin-performance' : '' ) );
}

/**
 * Add cached admin styles for the toggle button.
 */
function dlck_collect_store_admin_toggle_styles(): void {
	dlck_add_inline_css(
		'#wp-admin-bar-store-admin-toggle > .ab-item{background-color:#d63638;color:#fff;}' .
		'#wp-admin-bar-store-admin-toggle:hover > .ab-item{background-color:#b52d2f;}' .
		'#wp-admin-bar-store-admin-toggle > .ab-item .dashicons{color:#fff;}',
		'admin'
	);
}
