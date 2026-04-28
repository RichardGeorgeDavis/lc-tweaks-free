<?php
/**
* @package Disable autocomplete checkout fields
 * @version 1.1
 */

add_filter( 'woocommerce_checkout_fields' , 'disable_autocomplete_checkout_fields' );

function disable_autocomplete_checkout_fields( $fields ) {
	if ( ! is_array( $fields ) ) {
		return $fields;
	}

	foreach ( $fields as &$fieldset ) {
		if ( ! is_array( $fieldset ) ) {
			continue;
		}
		foreach ( $fieldset as &$field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}
			// Set autocomplete attribute to "off" for all fields.
			$field['autocomplete'] = 'off';
		}
	}

	return $fields;
}

?>
