<?php
/**
 * Allow SVG uploads and preview sizing in the media library.
 */

function dlck_svg_uploads_user_can_upload(): bool {
	return current_user_can( 'manage_options' );
}

function dlck_allow_svgimg_types( $mimes ) {
	if ( ! dlck_svg_uploads_user_can_upload() ) {
		return $mimes;
	}

	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter( 'upload_mimes', 'dlck_allow_svgimg_types' );

function dlck_svg_upload_is_svg_filename( string $filename ): bool {
	return strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) === 'svg';
}

function dlck_svg_upload_forbidden_value( string $value ): bool {
	return (bool) preg_match( '/(?:javascript|vbscript|data):|url\s*\(\s*(?![\'"]?#)/i', $value );
}

function dlck_svg_upload_sanitize_element( DOMElement $element ): void {
	$allowed_elements = array(
		'svg'            => true,
		'g'              => true,
		'path'           => true,
		'rect'           => true,
		'circle'         => true,
		'ellipse'        => true,
		'line'           => true,
		'polyline'       => true,
		'polygon'        => true,
		'defs'           => true,
		'lineargradient' => true,
		'radialgradient' => true,
		'stop'           => true,
		'clippath'       => true,
		'mask'           => true,
		'pattern'        => true,
		'title'          => true,
		'desc'           => true,
		'use'            => true,
		'symbol'         => true,
	);
	$allowed_attributes = array(
		'aria-hidden'           => true,
		'aria-label'            => true,
		'class'                 => true,
		'clip-path'             => true,
		'clip-rule'             => true,
		'cx'                    => true,
		'cy'                    => true,
		'd'                     => true,
		'fill'                  => true,
		'fill-opacity'          => true,
		'fill-rule'             => true,
		'gradienttransform'     => true,
		'gradientunits'         => true,
		'height'                => true,
		'href'                  => true,
		'id'                    => true,
		'mask'                  => true,
		'offset'                => true,
		'opacity'               => true,
		'patterncontentunits'   => true,
		'patternunits'          => true,
		'points'                => true,
		'preserveaspectratio'   => true,
		'r'                     => true,
		'role'                  => true,
		'rx'                    => true,
		'ry'                    => true,
		'stop-color'            => true,
		'stop-opacity'          => true,
		'stroke'                => true,
		'stroke-dasharray'      => true,
		'stroke-dashoffset'     => true,
		'stroke-linecap'        => true,
		'stroke-linejoin'       => true,
		'stroke-miterlimit'     => true,
		'stroke-opacity'        => true,
		'stroke-width'          => true,
		'transform'             => true,
		'version'               => true,
		'viewbox'               => true,
		'width'                 => true,
		'x'                     => true,
		'x1'                    => true,
		'x2'                    => true,
		'xlink:href'            => true,
		'xmlns'                 => true,
		'xmlns:xlink'           => true,
		'y'                     => true,
		'y1'                    => true,
		'y2'                    => true,
	);

	foreach ( iterator_to_array( $element->childNodes ) as $child ) {
		if ( $child instanceof DOMElement ) {
			$name = strtolower( $child->localName );
			if ( ! isset( $allowed_elements[ $name ] ) ) {
				$element->removeChild( $child );
				continue;
			}
			dlck_svg_upload_sanitize_element( $child );
		}
	}

	if ( ! $element->hasAttributes() ) {
		return;
	}

	foreach ( iterator_to_array( $element->attributes ) as $attribute ) {
		if ( ! $attribute instanceof DOMAttr ) {
			continue;
		}

		$name  = strtolower( $attribute->name );
		$value = trim( $attribute->value );
		if ( strpos( $name, 'on' ) === 0 || ! isset( $allowed_attributes[ $name ] ) || dlck_svg_upload_forbidden_value( $value ) ) {
			$element->removeAttributeNode( $attribute );
			continue;
		}

		if ( in_array( $name, array( 'href', 'xlink:href' ), true ) && ! preg_match( '/^#[A-Za-z0-9_.:-]+$/', $value ) ) {
			$element->removeAttributeNode( $attribute );
		}
	}
}

function dlck_svg_upload_sanitize_content( string $svg ): string {
	if ( ! class_exists( 'DOMDocument' ) || trim( $svg ) === '' ) {
		return '';
	}

	// SVG files do not need a document type declaration to render. Removing it
	// retains common exporter output while preventing entity declarations from
	// reaching the XML parser.
	$svg = preg_replace( '/<!DOCTYPE[^>\[]*(\[[^\]]*\])?\s*>/is', '', $svg );
	if ( ! is_string( $svg ) || false !== stripos( $svg, '<!ENTITY' ) ) {
		return '';
	}

	$previous = libxml_use_internal_errors( true );
	$document = new DOMDocument();
	$document->preserveWhiteSpace = false;
	$loaded = $document->loadXML( $svg, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( ! $loaded || ! $document->documentElement || strtolower( $document->documentElement->localName ) !== 'svg' ) {
		return '';
	}

	dlck_svg_upload_sanitize_element( $document->documentElement );
	$sanitized = $document->saveXML( $document->documentElement );

	return is_string( $sanitized ) ? $sanitized : '';
}

function dlck_svg_upload_prefilter( $file ) {
	if ( empty( $file['name'] ) || ! is_string( $file['name'] ) || ! dlck_svg_upload_is_svg_filename( $file['name'] ) ) {
		return $file;
	}

	if ( ! dlck_svg_uploads_user_can_upload() ) {
		$file['error'] = __( 'SVG uploads are restricted to administrators.', 'lc-tweaks' );
		return $file;
	}

	$tmp_name = isset( $file['tmp_name'] ) && is_string( $file['tmp_name'] ) ? $file['tmp_name'] : '';
	if ( $tmp_name === '' || ! is_readable( $tmp_name ) ) {
		$file['error'] = __( 'The SVG file could not be read.', 'lc-tweaks' );
		return $file;
	}

	$contents = file_get_contents( $tmp_name ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$sanitized = is_string( $contents ) ? dlck_svg_upload_sanitize_content( $contents ) : '';
	if ( $sanitized === '' ) {
		$file['error'] = __( 'The SVG file could not be sanitized.', 'lc-tweaks' );
		return $file;
	}

	if ( file_put_contents( $tmp_name, $sanitized ) === false ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		$file['error'] = __( 'The sanitized SVG file could not be saved.', 'lc-tweaks' );
	}

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'dlck_svg_upload_prefilter' );

add_filter(
	'wp_check_filetype_and_ext',
	static function ( $dlck_svg_filetype_ext_data, $file, $filename, $mimes ) {
		if ( dlck_svg_uploads_user_can_upload() && dlck_svg_upload_is_svg_filename( (string) $filename ) ) {
			$dlck_svg_filetype_ext_data['ext']  = 'svg';
			$dlck_svg_filetype_ext_data['type'] = 'image/svg+xml';
		}
		return $dlck_svg_filetype_ext_data;
	},
	100,
	4
);

function dlck_common_svg_media_thumbnails( $response, $attachment, $meta ) {
	if ( $response['type'] === 'image' && $response['subtype'] === 'svg+xml' && class_exists( 'SimpleXMLElement' ) ) {
		try {
			$path = get_attached_file( $attachment->ID );
			if ( @file_exists( $path ) ) {
				$svg    = new SimpleXMLElement( @file_get_contents( $path ) );
				$src    = $response['url'];
				$width  = (int) $svg['width'];
				$height = (int) $svg['height'];

				$response['image'] = compact( 'src', 'width', 'height' );
				$response['thumb'] = compact( 'src', 'width', 'height' );

				$response['sizes']['full'] = array(
					'height'      => $height,
					'width'       => $width,
					'url'         => $src,
					'orientation' => $height > $width ? 'portrait' : 'landscape',
				);
			}
		} catch ( Exception $e ) {
			// No-op: fallback to default handling when SVG metadata is unavailable.
		}
	}

	return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'dlck_common_svg_media_thumbnails', 10, 3 );
