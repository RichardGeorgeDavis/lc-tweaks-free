<?php
/**
 * Allow WOFF/WOFF2 uploads in the Divi Builder font selector.
 */

add_filter( 'et_pb_supported_font_formats', 'dlck_custom_font_formats', 1 );

function dlck_custom_font_formats() {
	return array( 'otf', 'ttf', 'woff', 'woff2' );
}
