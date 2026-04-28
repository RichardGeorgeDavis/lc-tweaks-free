<?php
/**
* @package Stop Divi Image Crop Gallery
* @version 1.0
* https://www.peeayecreative.com/how-to-stop-divi-image-crop/
*/

function pa_gallery_image_width( $size ) {
	return 9999;
	}
	function pa_gallery_image_height( $size ) {
	return 9999;
	}
	add_filter( 'et_pb_gallery_image_width', 'pa_gallery_image_width' );
	add_filter( 'et_pb_gallery_image_height', 'pa_gallery_image_height' );

?>