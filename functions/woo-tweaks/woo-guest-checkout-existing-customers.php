<?php
/**
 * @package Allow Guest Checkout For Existing WooCommerce Customers
 * @version 1.0
 */

add_action( 'woocommerce_checkout_process', 'dlck_faux_login_for_existing_email' );

/**
 * Attach guest checkout orders to existing customers by email.
 */
function dlck_faux_login_for_existing_email() {
	if ( is_user_logged_in() ) {
		return;
	}

	if ( empty( $_POST['billing_email'] ) ) {
		return;
	}

	$email = sanitize_email( wp_unslash( $_POST['billing_email'] ) );
	if ( $email === '' ) {
		return;
	}

	if ( email_exists( $email ) ) {
		$user = get_user_by( 'email', $email );
		if ( $user ) {
			wp_set_current_user( $user->ID );
		}
	}
}

?>
