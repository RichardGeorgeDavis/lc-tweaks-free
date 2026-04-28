<?php
/**
 * Allow JSON uploads (Lottie).
 */

function dlck_allow_json_types( $mimes ) {
	$mimes['json'] = 'application/json';
	return $mimes;
}
add_filter( 'upload_mimes', 'dlck_allow_json_types' );

add_filter(
	'wp_check_filetype_and_ext',
	static function ( $data, $file, $filename, $mimes ) {
		if ( substr( $filename, -5 ) === '.json' ) {
			$data['ext']  = 'json';
			$data['type'] = 'application/json';
		}
		return $data;
	},
	100,
	4
);
