<?php
/**
 * @package Hide Products Without Featured Image @ WooCommerce Shop
 * @version 1.0
 */

add_action( 'woocommerce_product_query', 'dlck_hide_products_no_featured_image' );

/**
 * Exclude products without featured images from archive queries.
 *
 * @param WC_Query $query WooCommerce query object.
 */
function dlck_hide_products_no_featured_image( $query ) {
	$meta_query = $query->get( 'meta_query' );
	if ( ! is_array( $meta_query ) ) {
		$meta_query = array();
	}

	$meta_query[] = array(
		'key'     => '_thumbnail_id',
		'compare' => 'EXISTS',
	);

	$query->set( 'meta_query', $meta_query );
}

?>
