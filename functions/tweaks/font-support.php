<?php
/**
 * Allow font uploads in the media library.
 */

function dlck_font_files_check( $types, $file, $filename, $mimes ) {
	if ( false !== strpos( $filename, '.ttf' ) ) {
		$types['ext']  = 'ttf';
		$types['type'] = 'font/ttf|application/font-ttf|application/x-font-ttf|application/octet-stream';
	}

	if ( false !== strpos( $filename, '.otf' ) ) {
		$types['ext']  = 'otf';
		$types['type'] = 'font/otf|application/font-otf|application/x-font-otf|application/octet-stream';
	}

	if ( false !== strpos( $filename, '.woff' ) ) {
		$types['ext']  = 'woff';
		$types['type'] = 'font/woff|application/font-woff|application/x-font-woff|application/octet-stream';
	}

	if ( false !== strpos( $filename, '.woff2' ) ) {
		$types['ext']  = 'woff2';
		$types['type'] = 'font/woff2|application/font-woff2|application/x-font-woff2|application/octet-stream';
	}

	return $types;
}
add_filter( 'wp_check_filetype_and_ext', 'dlck_font_files_check', 10, 4 );

function dlck_allow_font_file_types( $mimes ) {
	$mimes['ttf']  = 'font/ttf|application/font-ttf|application/x-font-ttf|application/octet-stream';
	$mimes['otf']  = 'font/otf|application/font-otf|application/x-font-otf|application/octet-stream';
	$mimes['woff'] = 'application/font-woff';
	$mimes['woff2'] = 'application/font-woff2';

	return $mimes;
}
add_filter( 'upload_mimes', 'dlck_allow_font_file_types' );
