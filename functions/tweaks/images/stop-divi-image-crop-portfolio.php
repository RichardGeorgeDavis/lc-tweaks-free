<?php
/**
* @package Stop Divi Image Crop Portfolio
* @version 1.0
* https://www.peeayecreative.com/how-to-stop-divi-image-crop/
*/

function pa_portfolio_image_width($width) {
	return '9999';
}
function pa_portfolio_image_height($height) {
	return '9999';
}
add_filter( 'et_pb_portfolio_image_width', 'pa_portfolio_image_width' );
add_filter( 'et_pb_portfolio_image_height', 'pa_portfolio_image_height' );

?>