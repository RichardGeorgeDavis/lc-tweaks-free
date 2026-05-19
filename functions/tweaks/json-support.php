<?php
/**
 * Allow JSON uploads (Lottie).
 */

function dlck_allow_json_types( $mimes ) {
	$mimes['json'] = 'application/json';
	return $mimes;
}
add_filter( 'upload_mimes', 'dlck_allow_json_types' );

function dlck_json_upload_is_json_filename( string $filename ): bool {
	return strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) === 'json';
}

function dlck_json_upload_prefilter( $file ) {
	if ( empty( $file['name'] ) || ! is_string( $file['name'] ) || ! dlck_json_upload_is_json_filename( $file['name'] ) ) {
		return $file;
	}

	$tmp_name = isset( $file['tmp_name'] ) && is_string( $file['tmp_name'] ) ? $file['tmp_name'] : '';
	if ( $tmp_name === '' || ! is_readable( $tmp_name ) ) {
		$file['error'] = __( 'The JSON file could not be read.', 'lc-tweaks' );
		return $file;
	}

	$contents = file_get_contents( $tmp_name ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	json_decode( is_string( $contents ) ? $contents : '' );
	if ( json_last_error() !== JSON_ERROR_NONE ) {
		$file['error'] = __( 'The uploaded JSON file is not valid JSON.', 'lc-tweaks' );
	}

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'dlck_json_upload_prefilter' );

add_filter(
	'wp_check_filetype_and_ext',
	static function ( $data, $file, $filename, $mimes ) {
		if ( dlck_json_upload_is_json_filename( (string) $filename ) ) {
			$data['ext']  = 'json';
			$data['type'] = 'application/json';
		}
		return $data;
	},
	100,
	4
);
