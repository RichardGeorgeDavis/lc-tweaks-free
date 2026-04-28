<?php
/**
 * Keep WooCommerce checkout fields empty by default.
 */

add_filter( 'woocommerce_checkout_get_value', '__return_empty_string', 10 );
