<?php
/**
* @package Stop Divi Image Crop Blog
* @version 1.0
* https://www.peeayecreative.com/how-to-stop-divi-image-crop/
*/

function pa_blog_image_width($width) {
	return '9999';
}
function pa_blog_image_height($height) {
	return '9999';
}
add_filter( 'et_pb_blog_image_width', 'pa_blog_image_width' );
add_filter( 'et_pb_blog_image_height', 'pa_blog_image_height' );

?>
