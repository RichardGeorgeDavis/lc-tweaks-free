<?php
/**
 * @package Product Category > Body CSS Class @ Single Product
 * @version 1.1
 */

 add_filter( 'body_class', 'dlck_wc_product_cats_css_body_class' );

 function dlck_wc_product_cats_css_body_class( $classes ){
	 if ( is_singular( 'product' ) ) {
		 $current_product = wc_get_product();
		 if ( ! $current_product ) {
			 return $classes;
		 }

		 $custom_terms = get_the_terms( $current_product->get_id(), 'product_cat' );
		 if ( $custom_terms && ! is_wp_error( $custom_terms ) ) {
			 foreach ( $custom_terms as $custom_term ) {
				 $classes[] = 'product_cat_' . $custom_term->slug;
			 }
		 }
	 }
	 return $classes;
 }
