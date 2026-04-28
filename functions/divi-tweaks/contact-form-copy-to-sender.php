<?php
/**
 * @package Send a copy to the sender with the Contact Form module / Copy to sender via Divi's contact form
 * @version 2.0
 */

/**
 * Append a CC header to Divi contact form emails so the sender
 * receives a copy of their own submission.
 *
 * Filter: et_contact_page_headers
 *
 * @param array  $headers        Existing headers.
 * @param string $contact_name   Sender name from the form.
 * @param string $contact_email  Sender email from the form.
 * @param string $contact_subject (unused here, but included for signature compatibility).
 *
 * @return array Modified headers.
 */
add_filter( 'et_contact_page_headers', function ( $headers, $contact_name, $contact_email, $contact_subject ) {

	$email = sanitize_email( $contact_email );
	if ( ! $email || ! is_email( $email ) ) {
		// Don't add a CC if the email is not valid.
		return $headers;
	}

	$name = trim( wp_strip_all_tags( $contact_name ) );

	$headers[] = sprintf(
		'Cc: "%s" <%s>',
		$name !== '' ? $name : $email,
		$email
	);

	return $headers;
}, 10, 4 );

?>
