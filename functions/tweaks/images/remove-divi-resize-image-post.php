<?php
/**
* @package Disable WordPress Image Sizes - Post
* @version 1.0
* https://www.peeayecreative.com/how-to-stop-divi-image-crop/
*/

add_action('init', 'dlck_remove_divi_resize_image_post');
function dlck_remove_divi_resize_image_post() {
  remove_image_size('et-pb-post-main-image');
  remove_image_size('et-pb-post-main-image-fullwidth');
  remove_image_size('et-pb-post-main-image-fullwidth-large');
}

?>