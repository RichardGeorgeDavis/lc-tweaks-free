<?php
/**
 * Additional WooCommerce settings sections.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlck_woo_block_bad_cart_query_abuse_val = dlck_get_option( 'dlck_woo_block_bad_cart_query_abuse' );
$dlck_divi_theme_active                  = function_exists( 'dlck_is_divi_theme_active' ) && dlck_is_divi_theme_active();
?>

<h2 class="tool-section"><?php esc_html_e( 'Request Protection', 'lc-tweaks' ); ?></h2>
<div class="tool-wrap">
	<div class="lc-kit">
		<div class="box-title">
			<h3><?php esc_html_e( 'Block Bad WooCommerce Query Abuse', 'lc-tweaks' ); ?></h3>
			<div class="box-descr">
				<p><?php esc_html_e( 'Return a 403 when WooCommerce cart-style query actions are combined with common ad click tracking parameters.', 'lc-tweaks' ); ?></p>
			</div>
		</div>
		<div class="box-content minibox">
			<div class="checkbox">
				<input name="dlck_woo_block_bad_cart_query_abuse" type="checkbox" value="1" <?php checked( '1', $dlck_woo_block_bad_cart_query_abuse_val ); ?> />
			</div>
		</div>
	</div>
</div>

<?php if ( $dlck_divi_theme_active ) : ?>
	<?php
	$dlck_layout_query = array(
		'post_type'      => 'et_pb_layout',
		'posts_per_page' => -1,
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => '_et_pb_predefined_layout',
				'compare' => 'NOT EXISTS',
			),
		),
	);
	$dlck_divi_layouts             = get_posts( $dlck_layout_query );
	$dlck_shop_header_layout       = get_option( 'dlck_shop_header_layout' );
	$dlck_shop_cat_header_layout   = get_option( 'dlck_shop_cat_header_layout' );
	$dlck_product_header_layout    = get_option( 'dlck_product_header_layout' );
	$dlck_woo_layout_select_fields = array(
		'dlck_shop_header_layout'     => array(
			'value'       => $dlck_shop_header_layout,
			'title'       => __( 'After Header - Woo Shop Page Layout', 'lc-tweaks' ),
			'description' => __( 'Display a Divi Library layout after the main header and navigation on the WooCommerce shop page.', 'lc-tweaks' ),
		),
		'dlck_shop_cat_header_layout' => array(
			'value'       => $dlck_shop_cat_header_layout,
			'title'       => __( 'After Header - Woo Shop Category Layout', 'lc-tweaks' ),
			'description' => __( 'Display a Divi Library layout after the main header and navigation on WooCommerce product categories.', 'lc-tweaks' ),
		),
		'dlck_product_header_layout'  => array(
			'value'       => $dlck_product_header_layout,
			'title'       => __( 'After Header - Woo Product Page Layout', 'lc-tweaks' ),
			'description' => __( 'Display a Divi Library layout after the main header and navigation on WooCommerce product pages.', 'lc-tweaks' ),
		),
	);
	?>

	<h2 class="tool-section"><?php esc_html_e( 'Divi Custom Layouts', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">
		<?php foreach ( $dlck_woo_layout_select_fields as $dlck_layout_field_name => $dlck_layout_field ) : ?>
			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html( $dlck_layout_field['title'] ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html( $dlck_layout_field['description'] ); ?></p>
					</div>
				</div>
				<div class="box-content">
					<?php if ( ! empty( $dlck_divi_layouts ) ) : ?>
						<select name="<?php echo esc_attr( $dlck_layout_field_name ); ?>">
							<option value=""><?php esc_html_e( 'None', 'lc-tweaks' ); ?></option>
							<?php foreach ( $dlck_divi_layouts as $dlck_layout ) : ?>
								<option value="<?php echo esc_attr( $dlck_layout->ID ); ?>" <?php selected( $dlck_layout->ID, $dlck_layout_field['value'] ); ?>>
									<?php echo esc_html( $dlck_layout->post_title ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					<?php else : ?>
						<p class="info"><?php esc_html_e( 'Your Divi Library is empty. Create and save a layout first.', 'lc-tweaks' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
