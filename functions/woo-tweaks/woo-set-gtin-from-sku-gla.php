<?php
/**
 * @package Set Google Listings & Ads Identifiers From SKU
 * @version 1.0
 */

add_filter( 'woocommerce_gla_product_data', 'dlck_set_gla_gtin_from_sku', 10, 2 );

/**
 * Use SKU-based identifiers for Google Listings & Ads when GTIN is missing.
 *
 * @param array      $data Product data for GLA.
 * @param WC_Product $product Product object.
 * @return array
 */
function dlck_set_gla_gtin_from_sku( $data, $product ) {
	if ( ! function_exists( 'dlck_get_option' ) || dlck_get_option( 'dlck_woo_set_gtin_from_sku_gla' ) !== '1' ) {
		return $data;
	}
	if ( function_exists( 'dlck_is_gla_active' ) && ! dlck_is_gla_active() ) {
		return $data;
	}

	$sku = $product instanceof WC_Product ? $product->get_sku() : '';

	if ( empty( $data['gtin'] ) ) {
		$gtin = dlck_normalize_gtin_from_sku( $sku );
		if ( $gtin !== '' ) {
			$data['gtin'] = $gtin;
		}
	} else {
		$normalized = dlck_normalize_gtin_from_sku( $data['gtin'] );
		if ( $normalized === '' ) {
			unset( $data['gtin'] );
		} else {
			$data['gtin'] = $normalized;
		}
	}

	if ( empty( $data['mpn'] ) && $sku !== '' ) {
		$data['mpn'] = $sku;
	}

	if ( empty( $data['gtin'] ) && empty( $data['brand'] ) && empty( $data['mpn'] ) ) {
		$data['identifier_exists'] = 'no';
	}

	return $data;
}

if ( ! function_exists( 'dlck_normalize_gtin_from_sku' ) ) {
	/**
	 * Normalize SKU to a valid GTIN length (8, 12, 13, 14).
	 *
	 * @param string $sku Product SKU.
	 * @return string
	 */
	function dlck_normalize_gtin_from_sku( $sku ): string {
		$digits = preg_replace( '/[^0-9]/', '', (string) $sku );
		$length = strlen( $digits );

		if ( in_array( $length, array( 8, 12, 13, 14 ), true ) ) {
			return $digits;
		}

		return '';
	}
}

?>
