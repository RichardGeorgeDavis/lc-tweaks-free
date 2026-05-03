<?php
/**
 * @package Set WooCommerce Product GTIN From SKU
 * @version 1.0
 */

add_filter( 'woocommerce_structured_data_product', 'dlck_set_gtin_from_sku', 9999, 2 );
add_action( 'woocommerce_admin_process_product_object', 'dlck_set_product_gtin_from_sku', 10, 1 );
add_action( 'save_post_product', 'dlck_set_product_gtin_from_sku_post_save', 20, 3 );
add_action( 'save_post_product_variation', 'dlck_set_product_gtin_from_sku_post_save', 20, 3 );
add_action( 'admin_post_dlck_gtin_cleanup_invalid', 'dlck_gtin_cleanup_invalid' );

/**
 * Set GTIN from SKU in structured data.
 *
 * @param array      $markup Structured data markup.
 * @param WC_Product $product Product object.
 * @return array
 */
function dlck_set_gtin_from_sku( $markup, $product ) {
	if ( ! function_exists( 'dlck_get_option' ) || dlck_get_option( 'dlck_woo_set_gtin_from_sku' ) !== '1' ) {
		return $markup;
	}
	if ( empty( $markup['sku'] ) ) {
		return $markup;
	}

	$gtin = dlck_normalize_gtin_from_sku( $markup['sku'] );
	if ( $gtin === '' ) {
		return $markup;
	}

	$markup['gtin'] = $gtin;
	return $markup;
}

/**
 * Set product GTIN field from SKU when empty (admin save).
 *
 * @param WC_Product $product Product object.
 */
function dlck_set_product_gtin_from_sku( $product ): void {
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	if ( ! function_exists( 'dlck_get_option' ) || dlck_get_option( 'dlck_woo_set_gtin_from_sku' ) !== '1' ) {
		return;
	}

	if ( $product->get_global_unique_id( 'edit' ) ) {
		return;
	}

	$gtin = dlck_normalize_gtin_from_sku( $product->get_sku( 'edit' ) );
	if ( $gtin === '' ) {
		return;
	}

	$product->set_global_unique_id( $gtin );
}

/**
 * Ensure GTIN is set on save when admin hook is skipped (e.g., block editor).
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post Post object.
 * @param bool    $update Whether this is an existing post.
 */
function dlck_set_product_gtin_from_sku_post_save( int $post_id, $post, bool $update ): void {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! function_exists( 'dlck_get_option' ) || dlck_get_option( 'dlck_woo_set_gtin_from_sku' ) !== '1' ) {
		return;
	}

	$product = wc_get_product( $post_id );
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	if ( $product->get_global_unique_id( 'edit' ) ) {
		return;
	}

	$gtin = dlck_normalize_gtin_from_sku( $product->get_sku( 'edit' ) );
	if ( $gtin === '' ) {
		return;
	}

	if ( function_exists( 'wc_product_has_global_unique_id' ) && wc_product_has_global_unique_id( $post_id, $gtin ) ) {
		return;
	}

	update_post_meta( $post_id, '_global_unique_id', $gtin );
	if ( function_exists( 'wc_update_product_lookup_tables' ) ) {
		wc_update_product_lookup_tables( $post_id );
	}
}

/**
 * Normalize SKU to a valid GTIN length (8, 12, 13, 14).
 *
 * @param string $sku Product SKU.
 * @return string
 */
if ( ! function_exists( 'dlck_normalize_gtin_from_sku' ) ) {
	function dlck_normalize_gtin_from_sku( $sku ): string {
		$digits = preg_replace( '/[^0-9]/', '', (string) $sku );
		$length = strlen( $digits );

		if ( in_array( $length, array( 8, 12, 13, 14 ), true ) ) {
			return $digits;
		}

		return '';
	}
}

/**
 * Cleanup invalid stored GTIN meta (admin action).
 */
function dlck_gtin_cleanup_invalid(): void {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'lc-tweaks' ) );
	}
	check_admin_referer( 'dlck_gtin_cleanup_invalid' );

	if ( get_transient( 'dlck_gtin_cleanup_lock' ) ) {
		wp_safe_redirect( add_query_arg( 'dlck_gtin_cleanup', 'locked', wp_get_referer() ) );
		exit;
	}
	set_transient( 'dlck_gtin_cleanup_lock', 1, 30 );

	$limit = 200;
	$product_ids = get_posts(
		array(
			'post_type'        => array( 'product', 'product_variation' ),
			'numberposts'      => $limit,
			'post_status'      => array( 'publish', 'private', 'draft' ),
			'fields'           => 'ids',
			'meta_query'       => array(
				array(
					'key'     => '_global_unique_id',
					'compare' => 'EXISTS',
				),
			),
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'no_found_rows'    => true,
			'suppress_filters' => true,
		)
	);

	$updated = 0;
	$deleted = 0;

	foreach ( $product_ids as $product_id ) {
		$raw = get_post_meta( $product_id, '_global_unique_id', true );
		if ( $raw === '' ) {
			continue;
		}

		$normalized = dlck_normalize_gtin_from_sku( $raw );
		if ( $normalized === '' ) {
			delete_post_meta( $product_id, '_global_unique_id' );
			$deleted++;
		} elseif ( $normalized !== $raw ) {
			update_post_meta( $product_id, '_global_unique_id', $normalized );
			$updated++;
		} else {
			continue;
		}

		if ( function_exists( 'wc_update_product_lookup_tables' ) ) {
			wc_update_product_lookup_tables( $product_id );
		}
	}

	delete_transient( 'dlck_gtin_cleanup_lock' );

	$query = array(
		'dlck_gtin_cleanup' => 'done',
		'dlck_gtin_updated' => $updated,
		'dlck_gtin_deleted' => $deleted,
	);
	wp_safe_redirect( add_query_arg( $query, wp_get_referer() ) );
	exit;
}

/**
 * Admin notice for GTIN cleanup.
 */
function dlck_gtin_cleanup_notice(): void {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || $screen->id !== 'toplevel_page_lc_tweaks' ) {
		return;
	}
	if ( empty( $_GET['dlck_gtin_cleanup'] ) ) {
		return;
	}

	if ( $_GET['dlck_gtin_cleanup'] === 'locked' ) {
		echo '<div class="notice notice-warning is-dismissible"><p>'
			. esc_html__( 'GTIN cleanup is already running. Please try again in a moment.', 'lc-tweaks' )
			. '</p></div>';
		return;
	}

	if ( $_GET['dlck_gtin_cleanup'] === 'done' ) {
		$updated = isset( $_GET['dlck_gtin_updated'] ) ? (int) $_GET['dlck_gtin_updated'] : 0;
		$deleted = isset( $_GET['dlck_gtin_deleted'] ) ? (int) $_GET['dlck_gtin_deleted'] : 0;

		echo '<div class="notice notice-success is-dismissible"><p>'
			. esc_html( sprintf( 'GTIN cleanup complete. Updated: %d. Deleted: %d.', $updated, $deleted ) )
			. '</p></div>';
	}
}
add_action( 'admin_notices', 'dlck_gtin_cleanup_notice' );

?>
