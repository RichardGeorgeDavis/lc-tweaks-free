<?php
/**
* @package Disable WordPress Image Sizes - portfolio
* @version 1.0
* https://www.peeayecreative.com/how-to-stop-divi-image-crop/
*/

add_action('init', 'dlck_remove_divi_resize_image_portfolio');
function dlck_remove_divi_resize_image_portfolio() {
  remove_image_size('et-pb-portfolio-image');
  remove_image_size('et-pb-portfolio-module-image');
  remove_image_size('et-pb-portfolio-image-single');
}

?>
