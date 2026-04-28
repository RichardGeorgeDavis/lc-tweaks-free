<?php
/**
* @package Disable WordPress Image Sizes
* @version 1.0
* https://www.peeayecreative.com/how-to-stop-divi-image-crop/
*/

add_filter('intermediate_image_sizes', function($sizes) {
	return array_diff($sizes, ['medium_large']);  // Medium Large (768 x 0)
  });
  
  add_action( 'init', 'dlck_remove_large_image_sizes' );
  function dlck_remove_large_image_sizes() {
	remove_image_size( '1536x1536' );             // 2 x Medium Large (1536 x 1536)
	remove_image_size( '2048x2048' );             // 2 x Large (2048 x 2048)
  }

?>
