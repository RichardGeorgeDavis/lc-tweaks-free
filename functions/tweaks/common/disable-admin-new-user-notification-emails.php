<?php
/**
 * Disable admin-facing new user registration emails while keeping user emails intact.
 *
 * Uses the modern core filter when available and falls back to replacing the
 * default registration actions on older WordPress versions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'dlck_disable_admin_new_user_notification_emails_init' );

/**
 * Bootstrap admin notification suppression for new user registrations.
 */
function dlck_disable_admin_new_user_notification_emails_init(): void {
	if ( has_filter( 'wp_send_new_user_notification_to_admin', 'dlck_disable_admin_new_user_notification_emails_filter' ) ) {
		return;
	}

	add_filter( 'wp_send_new_user_notification_to_admin', 'dlck_disable_admin_new_user_notification_emails_filter', 10, 2 );

	if ( function_exists( 'wp_send_new_user_notifications' ) ) {
		remove_action( 'register_new_user', 'wp_send_new_user_notifications' );
		remove_action( 'edit_user_created_user', 'wp_send_new_user_notifications', 10, 2 );

		add_action( 'register_new_user', 'dlck_send_new_user_notification_to_user_only' );
		add_action( 'edit_user_created_user', 'dlck_send_new_user_notification_to_user_only', 10, 2 );
	}
}

/**
 * Suppress admin notifications for new user registrations.
 *
 * @param bool    $send Whether WordPress should notify the admin.
 * @param WP_User $user User object for the new user.
 * @return bool
 */
function dlck_disable_admin_new_user_notification_emails_filter( bool $send, WP_User $user ): bool {
	unset( $send, $user );

	return false;
}

/**
 * Preserve user-facing registration emails on older registration hooks.
 *
 * @param int         $user_id New user ID.
 * @param string|null $notify  Notification target requested by WordPress.
 */
function dlck_send_new_user_notification_to_user_only( int $user_id, ?string $notify = 'both' ): void {
	unset( $notify );

	if ( ! function_exists( 'wp_send_new_user_notifications' ) ) {
		return;
	}

	wp_send_new_user_notifications( $user_id, 'user' );
}
