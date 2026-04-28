<?php
/**
 * @package Email Item Meta Tags @ WooCommerce Emails
 * @version 1.0
 */

add_filter( 'woocommerce_display_item_meta', 'dlck_item_meta_email_friendly_tags', 10, 3 );
add_filter( 'woocommerce_email_styles', 'dlck_add_email_item_meta_styles', 10, 2 );

/**
 * Rewrite item meta HTML for emails to simple tag-like spans.
 *
 * @param string        $html Item meta HTML.
 * @param WC_Order_Item $item Order item.
 * @param array         $args Args.
 * @return string
 */
function dlck_item_meta_email_friendly_tags( $html, $item, $args ) {
	if ( ! doing_action( 'woocommerce_email_order_details' ) ) {
		return $html;
	}

	if ( empty( $html ) ) {
		return '';
	}

	$html = '';

	foreach ( $item->get_formatted_meta_data() as $meta ) {
		$key   = $meta->display_key;
		$value = $meta->display_value;
		$html .= '<span>' . esc_html( $key ) . ': ' . esc_html( wp_strip_all_tags( $value ) ) . '</span>';
	}

	return $html;
}

/**
 * Add styles for item meta tags in WooCommerce emails.
 *
 * @param string   $css   Existing email CSS.
 * @param WC_Email $email Email object.
 * @return string
 */
function dlck_add_email_item_meta_styles( $css, $email ) {
	$css .= "\n.email-order-item-meta span{border:1px solid #999;border-radius:2px;padding:2px 4px;margin:0 6px 6px 0;background:#fbfbfb;color:#888;}\n";
	return $css;
}

?>
