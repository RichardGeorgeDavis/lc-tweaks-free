<?php
/**
 * @package Remove Tax Suffix Labels @ WooCommerce
 * @version 1.0
 */

add_filter( 'woocommerce_countries_inc_tax_or_vat', '__return_empty_string' );
add_filter( 'woocommerce_countries_ex_tax_or_vat', '__return_empty_string' );

?>
