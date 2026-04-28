<?php
/**
 * @package Email Fatal Errors to Admin
 * @version 1.0
 */

add_action( 'woocommerce_shutdown_error', 'dlck_email_fatal_errors' );

/**
 * Email WooCommerce fatal errors to the admin.
 *
 * @param array $error Error data.
 */
function dlck_email_fatal_errors( $error ) {
	if ( empty( $error['message'] ) || empty( $error['file'] ) || empty( $error['line'] ) ) {
		return;
	}

	$email_subject = 'Critical Error On Your WooCommerce Site';
	$email_content = sprintf(
		__( '%1$s in %2$s on line %3$s', 'woocommerce' ),
		$error['message'],
		$error['file'],
		$error['line']
	);

	wp_mail( get_option( 'admin_email' ), $email_subject, $email_content );
}

?>
