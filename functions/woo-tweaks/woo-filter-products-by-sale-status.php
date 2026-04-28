<?php
/**
 * @package Filter Products By Sale Status @ WP Admin
 * @version 1.0
 */

add_action( 'restrict_manage_posts', 'dlck_filter_products_by_sale_status', 9999 );
add_filter( 'request', 'dlck_filter_products_query_by_sale_status' );

/**
 * Output a sale status dropdown on the Products list table.
 */
function dlck_filter_products_by_sale_status() {
	global $typenow;

	if ( $typenow !== 'product' ) {
		return;
	}

	$selected = isset( $_GET['sale_status'] ) ? sanitize_text_field( wp_unslash( $_GET['sale_status'] ) ) : '';
	?>
	<select name="sale_status">
		<option value=""><?php echo esc_html__( 'Filter by sale status', 'divi-lc-kit' ); ?></option>
		<option value="on_sale" <?php selected( $selected, 'on_sale' ); ?>><?php echo esc_html__( 'On Sale', 'divi-lc-kit' ); ?></option>
		<option value="not_on_sale" <?php selected( $selected, 'not_on_sale' ); ?>><?php echo esc_html__( 'Not on Sale', 'divi-lc-kit' ); ?></option>
	</select>
	<?php
}

/**
 * Adjust the products query based on selected sale status.
 *
 * @param array $query_vars Query vars.
 * @return array
 */
function dlck_filter_products_query_by_sale_status( $query_vars ) {
	if ( empty( $query_vars['post_type'] ) || $query_vars['post_type'] !== 'product' ) {
		return $query_vars;
	}

	if ( empty( $_GET['sale_status'] ) ) {
		return $query_vars;
	}

	$sale_status = sanitize_text_field( wp_unslash( $_GET['sale_status'] ) );
	if ( $sale_status === '' ) {
		return $query_vars;
	}

	$now = current_time( 'timestamp' );
	if ( ! isset( $query_vars['meta_query'] ) || ! is_array( $query_vars['meta_query'] ) ) {
		$query_vars['meta_query'] = array();
	}

	if ( $sale_status === 'on_sale' ) {
		$query_vars['meta_query'][] = array(
			'key'     => '_sale_price',
			'value'   => 0,
			'compare' => '>',
			'type'    => 'NUMERIC',
		);
		$query_vars['meta_query'][] = array(
			'relation' => 'OR',
			array(
				'key'     => '_sale_price_dates_from',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_sale_price_dates_from',
				'value'   => $now,
				'compare' => '<=',
				'type'    => 'NUMERIC',
			),
		);
		$query_vars['meta_query'][] = array(
			'relation' => 'OR',
			array(
				'key'     => '_sale_price_dates_to',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_sale_price_dates_to',
				'value'   => $now,
				'compare' => '>=',
				'type'    => 'NUMERIC',
			),
		);
	} elseif ( $sale_status === 'not_on_sale' ) {
		$query_vars['meta_query'][] = array(
			'relation' => 'OR',
			array(
				'key'     => '_sale_price',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_sale_price',
				'value'   => '',
				'compare' => '=',
			),
			array(
				'key'     => '_sale_price_dates_from',
				'value'   => $now,
				'compare' => '>',
				'type'    => 'NUMERIC',
			),
			array(
				'key'     => '_sale_price_dates_to',
				'value'   => $now,
				'compare' => '<',
				'type'    => 'NUMERIC',
			),
		);
	}

	return $query_vars;
}

?>
