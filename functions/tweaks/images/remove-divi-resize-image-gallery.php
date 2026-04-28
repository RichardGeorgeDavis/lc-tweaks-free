<?php
/**
* @package Disable Divi Image Sizes - Gallery
* @version 1.0
* https://www.peeayecreative.com/how-to-stop-divi-image-crop/
*/

add_action('init', 'dlck_remove_divi_resize_image_gallery');
function dlck_remove_divi_resize_image_gallery() {
  remove_image_size('et-pb-gallery-module-image-portrait');
}

?>
