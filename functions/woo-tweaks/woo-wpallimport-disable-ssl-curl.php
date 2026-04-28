<?php
/**
 * @package Disable ssl cURL Error 60 in WP All Import
 * @version 1.1
 */

/**
 * Relax SSL verification for WP All Import endpoints only (temporary workaround for error 60).
 */
function dlck_curl_error_60_workaround( $handle, $r, $url ) {
	// Apply only when WP All Import is active.
	if ( ! class_exists( 'PMXI_Plugin' ) ) {
		return;
	}

	$host          = wp_parse_url( $url, PHP_URL_HOST );
	$allowed_hosts = array(
		'www.wpallimport.com',
		'wpallimport.com',
		'cdn.wpallimport.com',
		'api.wpallimport.com',
	);

	if ( ! $host || ! in_array( $host, $allowed_hosts, true ) ) {
		return;
	}

	curl_setopt( $handle, CURLOPT_SSL_VERIFYPEER, false );
}
add_action( 'http_api_curl', 'dlck_curl_error_60_workaround', 10, 3 );

?>
