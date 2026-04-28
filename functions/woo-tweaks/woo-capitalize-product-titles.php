<?php
/**
 * @package Capitalize Product Title @ Shop, Single Product
 * @version 1.0
 */

add_filter( 'the_title', 'dlck_capitalize_product_title', 9999, 2 );

/**
 * Capitalize product titles on the front end.
 *
 * @param string $post_title Product title.
 * @param int    $post_id Post ID.
 * @return string
 */
function dlck_capitalize_product_title( $post_title, $post_id ) {
	if ( is_admin() ) {
		return $post_title;
	}

	if ( 'product' !== get_post_type( $post_id ) ) {
		return $post_title;
	}

	return ucwords( strtolower( $post_title ) );
}

?>
