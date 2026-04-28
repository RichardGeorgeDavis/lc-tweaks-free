<?php
/**
 * @package Redirect Empty Paginated WooCommerce Category Pages
 * @version 1.0
 */

add_action( 'template_redirect', 'dlck_redirect_empty_product_cat_pagination' );

/**
 * Redirect empty paginated product category pages back to the category archive.
 */
function dlck_redirect_empty_product_cat_pagination() {
	if ( ! is_404() ) {
		return;
	}

	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}

	$permalinks = wc_get_permalink_structure();
	$cat_slug   = $permalinks['category_rewrite_slug'] ?? '';
	if ( $cat_slug === '' ) {
		return;
	}

	$requested_url = wp_unslash( $_SERVER['REQUEST_URI'] );
	$path          = wp_parse_url( $requested_url, PHP_URL_PATH );
	if ( ! $path ) {
		return;
	}

	if ( ! preg_match( '#^/' . preg_quote( $cat_slug, '#' ) . '/[^/]+/page/\d+/?#', $path ) ) {
		return;
	}

	if ( ! preg_match( '#/' . preg_quote( $cat_slug, '#' ) . '/([^/]+)/#', $path, $matches ) ) {
		return;
	}

	if ( empty( $matches[1] ) ) {
		return;
	}

	$term = get_term_by( 'slug', $matches[1], 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		wp_safe_redirect( get_term_link( $term ) );
		exit;
	}
}

?>
