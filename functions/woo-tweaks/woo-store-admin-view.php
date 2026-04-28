<?php
/**
 * @package Store Admin View @ WordPress Dashboard
 * @version 1.0
 */

add_action( 'admin_bar_menu', 'dlck_add_store_admin_toggle_button', 100 );
add_action( 'admin_init', 'dlck_handle_store_admin_toggle' );
add_action( 'admin_menu', 'dlck_filter_admin_menu_for_store_admin', 9999 );
add_filter( 'admin_url', 'dlck_add_store_admin_param_to_admin_urls', 10, 3 );
add_action( 'dlck_collect_inline_assets_admin', 'dlck_collect_store_admin_toggle_styles' );

/**
 * Add Store Admin toggle button to the admin bar.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar object.
 */
function dlck_add_store_admin_toggle_button( $wp_admin_bar ) {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	$is_store_admin = isset( $_COOKIE['store_admin'] ) && $_COOKIE['store_admin'] === '1';
	$url            = add_query_arg( 'toggle_store_admin', $is_store_admin ? '0' : '1', admin_url( 'admin.php?page=wc-orders' ) );
	$url            = wp_nonce_url( $url, 'dlck_store_admin_toggle', 'dlck_store_admin_nonce' );

	$wp_admin_bar->add_node(
		array(
			'id'    => 'store-admin-toggle',
			'title' => $is_store_admin
				? '<span class="ab-icon dashicons dashicons-admin-generic"></span> Switch to WP Admin'
				: '<span class="ab-icon dashicons dashicons-cart"></span> Switch to Store Admin',
			'href'  => $url,
		)
	);
}

/**
 * Set or delete the store admin cookie.
 */
function dlck_handle_store_admin_toggle() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	if ( ! isset( $_GET['toggle_store_admin'] ) ) {
		return;
	}
	if ( empty( $_GET['dlck_store_admin_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['dlck_store_admin_nonce'] ) ), 'dlck_store_admin_toggle' ) ) {
		return;
	}

	if ( $_GET['toggle_store_admin'] === '1' ) {
		setcookie( 'store_admin', '1', time() + DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
		$_COOKIE['store_admin'] = '1';
	} else {
		setcookie( 'store_admin', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
		unset( $_COOKIE['store_admin'] );
	}

	wp_safe_redirect( remove_query_arg( array( 'toggle_store_admin', 'dlck_store_admin_nonce' ) ) );
	exit;
}

/**
 * Filter admin menu items while in store admin mode.
 */
function dlck_filter_admin_menu_for_store_admin() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	if ( ! isset( $_COOKIE['store_admin'] ) || $_COOKIE['store_admin'] !== '1' ) {
		return;
	}

	global $menu;

	foreach ( $menu as $item ) {
		if ( ! isset( $item[2] ) ) {
			continue;
		}

		$slug  = $item[2];
		$is_woo = (
			$slug === 'woocommerce' ||
			strpos( $slug, 'wc-' ) === 0 ||
			strpos( $slug, 'woocommerce' ) !== false ||
			strpos( $slug, 'edit.php?post_type=shop_' ) === 0 ||
			strpos( $slug, 'edit.php?post_type=product' ) === 0
		);

		if ( ! $is_woo ) {
			remove_menu_page( $slug );
		}
	}
}

/**
 * Append store admin param to admin URLs.
 *
 * @param string      $url    Admin URL.
 * @param string      $path   Admin path.
 * @param string|null $scheme URL scheme.
 * @return string
 */
function dlck_add_store_admin_param_to_admin_urls( $url, $path, $scheme ) {
	if ( ! isset( $_COOKIE['store_admin'] ) || $_COOKIE['store_admin'] !== '1' ) {
		return $url;
	}

	return add_query_arg( 'store_admin', '1', $url );
}

/**
 * Add cached admin styles for the toggle button.
 */
function dlck_collect_store_admin_toggle_styles() {
	dlck_add_inline_css(
		'#wp-admin-bar-store-admin-toggle > .ab-item{background-color:#d63638;color:#fff;}' .
		'#wp-admin-bar-store-admin-toggle:hover > .ab-item{background-color:#b52d2f;}' .
		'#wp-admin-bar-store-admin-toggle > .ab-item .dashicons{color:#fff;}',
		'admin'
	);
}

?>
