<?php
// Add new column to the product page in admin and apply custom CSS
add_action(
	'dlck_collect_inline_assets_admin',
	static function () {
		dlck_add_inline_css( '.column-stock_status{width:120px;}', 'admin' );
	}
);

add_filter(
	'manage_edit-product_columns',
	static function ( $columns ) {
		$columns['stock_status'] = 'Stock Status'; // Adds new column
		return $columns;
	},
	15
);

add_action(
	'manage_product_posts_custom_column',
	static function ( $column, $postid ) {
		if ( $column === 'stock_status' ) {
			$product      = wc_get_product( $postid );
			if ( ! $product ) {
				return;
			}

			$stock_status = $product->get_stock_status(); // Get stock status
			if ( $stock_status === 'instock' ) {
				echo 'In Stock';
			} elseif ( $stock_status === 'outofstock' ) {
				echo 'Out of Stock';
			} else {
				echo 'On Backorder';
			}
		}
	},
	10,
	2
);
