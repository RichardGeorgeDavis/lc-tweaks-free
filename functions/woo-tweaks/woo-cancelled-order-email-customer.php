<?php
/**
 * @package Add Customer to Cancelled Order Email Recipients
 * @version 1.0
 */

add_filter( 'woocommerce_email_recipient_cancelled_order', 'dlck_cancelled_order_email_to_customer', 9999, 3 );

/**
 * Add billing email to cancelled order recipients.
 *
 * @param string   $email_recipient Email recipients.
 * @param WC_Order $email_object Order object.
 * @param WC_Email $email Email instance.
 * @return string
 */
function dlck_cancelled_order_email_to_customer( $email_recipient, $email_object, $email ) {
	if ( is_admin() ) {
		return $email_recipient;
	}

	if ( ! $email_object instanceof WC_Order ) {
		return $email_recipient;
	}

	$billing_email = $email_object->get_billing_email();
	if ( $billing_email ) {
		$email_recipient .= ', ' . $billing_email;
	}

	return $email_recipient;
}

?>
