<?php
/**
 * Allow font uploads in the media library.
 */

function dlck_font_upload_mimes(): array {
	return array(
		'ttf'   => 'font/ttf',
		'otf'   => 'font/otf',
		'woff'  => 'font/woff',
		'woff2' => 'font/woff2',
	);
}

function dlck_font_upload_final_extension( string $filename ): string {
	return strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
}

function dlck_font_upload_has_executable_segment( string $filename ): bool {
	$without_final_extension = preg_replace( '/\.[^.]+$/', '', strtolower( $filename ) );
	if ( ! is_string( $without_final_extension ) ) {
		return false;
	}

	return (bool) preg_match( '/(?:^|\.)(?:php[0-9]?|phtml|phar|cgi|pl|asp|aspx|jsp)(?:\.|$)/i', $without_final_extension );
}

function dlck_font_file_has_valid_signature( string $path, string $extension ): bool {
	if ( $path === '' || ! is_readable( $path ) ) {
		return false;
	}

	$handle = fopen( $path, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	if ( ! $handle ) {
		return false;
	}
	$signature = fread( $handle, 4 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
	fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

	switch ( $extension ) {
		case 'ttf':
			return in_array( $signature, array( "\x00\x01\x00\x00", 'true', 'typ1' ), true );
		case 'otf':
			return $signature === 'OTTO';
		case 'woff':
			return $signature === 'wOFF';
		case 'woff2':
			return $signature === 'wOF2';
		default:
			return false;
	}
}

function dlck_font_upload_prefilter( $file ) {
	if ( empty( $file['name'] ) || ! is_string( $file['name'] ) ) {
		return $file;
	}

	$extension = dlck_font_upload_final_extension( $file['name'] );
	$mimes     = dlck_font_upload_mimes();
	if ( ! isset( $mimes[ $extension ] ) ) {
		return $file;
	}

	if ( dlck_font_upload_has_executable_segment( $file['name'] ) ) {
		$file['error'] = __( 'Font uploads cannot include executable filename segments.', 'lc-tweaks' );
		return $file;
	}

	$tmp_name = isset( $file['tmp_name'] ) && is_string( $file['tmp_name'] ) ? $file['tmp_name'] : '';
	if ( ! dlck_font_file_has_valid_signature( $tmp_name, $extension ) ) {
		$file['error'] = __( 'The uploaded font file does not match the selected font format.', 'lc-tweaks' );
	}

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'dlck_font_upload_prefilter' );

function dlck_font_files_check( $types, $file, $filename, $mimes ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	$extension = dlck_font_upload_final_extension( (string) $filename );
	$font_mimes = dlck_font_upload_mimes();
	if ( ! isset( $font_mimes[ $extension ] ) || dlck_font_upload_has_executable_segment( (string) $filename ) ) {
		return $types;
	}

	$path = is_string( $file ) ? $file : '';
	if ( ! dlck_font_file_has_valid_signature( $path, $extension ) ) {
		return $types;
	}

	$types['ext']  = $extension;
	$types['type'] = $font_mimes[ $extension ];

	return $types;
}
add_filter( 'wp_check_filetype_and_ext', 'dlck_font_files_check', 10, 4 );

function dlck_allow_font_file_types( $mimes ) {
	foreach ( dlck_font_upload_mimes() as $extension => $mime ) {
		$mimes[ $extension ] = $mime;
	}

	return $mimes;
}
add_filter( 'upload_mimes', 'dlck_allow_font_file_types' );
