<?php
/**
 * @package Move Labels Inside Inputs - WooCommerce Checkout
 * @version 1.1
 */

add_filter( 'woocommerce_checkout_fields', 'dlck_labels_inside_checkout_fields', 9999 );

function dlck_labels_inside_checkout_fields( $fields ) {
	if ( ! is_array( $fields ) ) {
		return $fields;
	}

	foreach ( $fields as $section => $section_fields ) {
		if ( ! is_array( $section_fields ) ) {
			continue;
		}
		foreach ( $section_fields as $section_field => $section_field_settings ) {
			if ( empty( $fields[ $section ][ $section_field ]['label'] ) ) {
				continue;
			}
			$fields[ $section ][ $section_field ]['placeholder'] = $fields[ $section ][ $section_field ]['label'];
			$fields[ $section ][ $section_field ]['label']       = '';
		}
	}

	return $fields;
}
