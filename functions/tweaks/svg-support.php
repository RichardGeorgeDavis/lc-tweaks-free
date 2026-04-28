<?php
/**
 * Allow SVG uploads and preview sizing in the media library.
 */

function dlck_allow_svgimg_types( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter( 'upload_mimes', 'dlck_allow_svgimg_types' );

add_filter(
	'wp_check_filetype_and_ext',
	static function ( $dlck_svg_filetype_ext_data, $file, $filename, $mimes ) {
		if ( substr( $filename, -4 ) === '.svg' ) {
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
