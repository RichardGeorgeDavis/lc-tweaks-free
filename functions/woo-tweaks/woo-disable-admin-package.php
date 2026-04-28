<?php
/**
 * @package Disables the new WooCommerce Admin package
 * @version 2.0
 */

 add_filter( 'woocommerce_admin_features', '__return_empty_array' );
 add_filter( 'woocommerce_admin_disabled', '__return_true' );

?>
