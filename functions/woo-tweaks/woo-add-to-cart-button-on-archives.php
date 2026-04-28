<?php
/**
 * @package Display Add to Cart Button on Archives
 * @version 1.1
 */

add_action( 'init', 'dlck_move_loop_add_to_cart_after_title' );

/**
 * Move the archive add-to-cart button below the product title.
 *
 * Removes the default placement to avoid duplicates.
 */
function dlck_move_loop_add_to_cart_after_title() {
	// Remove the default button placement.
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	// Add button after the title instead.
	add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_add_to_cart', 10 );
}

?>
