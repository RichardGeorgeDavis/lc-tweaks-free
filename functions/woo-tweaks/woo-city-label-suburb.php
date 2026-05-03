<?php
/**
 * @package WooCommerce City Label to Suburb
 * @version 1.0
 */

add_filter( 'woocommerce_default_address_fields', 'dlck_change_city_label_to_suburb' );

/**
 * Change the visible WooCommerce city field label and placeholder to "Suburb".
 *
 * @param array $fields Default WooCommerce address fields.
 * @return array
 */
function dlck_change_city_label_to_suburb( $fields ) {
	if ( isset( $fields['city'] ) ) {
		$fields['city']['label']       = __( 'Suburb', 'lc-tweaks' );
		$fields['city']['placeholder'] = __( 'Suburb', 'lc-tweaks' );
	}

	return $fields;
}

?>
