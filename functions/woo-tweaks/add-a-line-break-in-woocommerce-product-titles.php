<?php
/**
* @package Add a line break in Woocommerce Product Titles
 * @version 1.1
 */

add_filter( 'the_title', 'custom_product_title', 10, 2 );
function custom_product_title( $title, $post_id ){
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $title;
	}

	$post_type = get_post_field( 'post_type', $post_id, true );
	if ( in_array( $post_type, array( 'product', 'product_variation' ), true ) ) {
		$title = str_replace( '|', '<br/>', (string) $title ); // we replace "|" by "<br>"
	}
	return $title;
}


?>
