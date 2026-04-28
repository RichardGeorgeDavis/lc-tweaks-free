<?php
// Add custom meta box to display last edited information.
if ( ! function_exists( 'dlck_add_changelog_meta_box' ) ) {
	function dlck_add_changelog_meta_box() {
		add_meta_box(
			'changelog_meta_box',      // Unique ID for the meta box
			'Changelog',               // Title of the meta box
			'dlck_changelog_meta_box_html', // Callback function to display the content
			array( 'product', 'product_variation' ), // Post types where the meta box should appear
			'side'                     // Context (location) where the meta box should appear
		);
	}
	add_action( 'add_meta_boxes', 'dlck_add_changelog_meta_box' );
}

// Display the content of the custom meta box.
if ( ! function_exists( 'dlck_changelog_meta_box_html' ) ) {
	function dlck_changelog_meta_box_html( $post ) {
		$last_editor_id = get_post_meta( $post->ID, '_last_editor_id', true );
		$last_edit_date = get_post_meta( $post->ID, '_last_edit_date', true );

		// If the post is new (auto-draft), set default values.
		if ( 'auto-draft' === $post->post_status ) {
			$current_user   = wp_get_current_user();
			$last_editor_id = $current_user->ID;
			$last_edit_date = current_time( 'mysql' );
		}

		if ( $last_editor_id ) {
			$last_editor = get_userdata( $last_editor_id );
			echo '<p>Last Edited By: ' . esc_html( $last_editor ? $last_editor->display_name : $last_editor_id ) . '</p>';
		} else {
			echo '<p>Last Edited By: N/A</p>';
		}

		if ( $last_edit_date ) {
			echo '<p>Last Edit Date: ' . esc_html( $last_edit_date ) . '</p>';
		} else {
			echo '<p>Last Edit Date: N/A</p>';
		}
	}
}

// Save the last edited date and user whenever a product or variation is updated.
if ( ! function_exists( 'dlck_save_changelog_meta_data' ) ) {
	function dlck_save_changelog_meta_data( $post_id ) {
		$post_type = get_post_type( $post_id );

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( 'product' === $post_type || 'product_variation' === $post_type ) {
			$current_user_id = get_current_user_id();
			$now             = current_time( 'mysql' );

			update_post_meta( $post_id, '_last_editor_id', $current_user_id );
			update_post_meta( $post_id, '_last_edit_date', $now );

			// For variations, update parent product meta as well.
			if ( 'product_variation' === $post_type ) {
				$parent_id = wp_get_post_parent_id( $post_id );
				if ( $parent_id ) {
					update_post_meta( $parent_id, '_last_editor_id', $current_user_id );
					update_post_meta( $parent_id, '_last_edit_date', $now );
				}
			}
		}
	}
	add_action( 'save_post', 'dlck_save_changelog_meta_data' );
}

// Add columns to the product list table.
if ( ! function_exists( 'dlck_add_last_edited_columns' ) ) {
	function dlck_add_last_edited_columns( $columns ) {
		$columns['last_edited_by'] = __( 'Last Edited By', 'woocommerce' );
		return $columns;
	}
	add_filter( 'manage_edit-product_columns', 'dlck_add_last_edited_columns' );
}

// Populate the custom columns with data.
if ( ! function_exists( 'dlck_populate_last_edited_columns' ) ) {
	function dlck_populate_last_edited_columns( $column, $post_id ) {
		if ( 'last_edited_by' !== $column ) {
			return;
		}

		$last_user_id    = get_post_meta( $post_id, '_last_editor_id', true );
		$last_edit_date  = get_post_meta( $post_id, '_last_edit_date', true );
		$fallback_user   = get_post_meta( $post_id, '_edit_last', true );
		$fallback_date   = get_post_field( 'post_modified', $post_id );
		$display_user_id = $last_user_id ? $last_user_id : $fallback_user;
		$display_date    = $last_edit_date ? $last_edit_date : $fallback_date;

		if ( $display_user_id ) {
			$user = get_userdata( $display_user_id );
			echo esc_html( $user ? $user->display_name : $display_user_id );
		} else {
			echo esc_html__( 'Unknown', 'woocommerce' );
		}

		if ( $display_date ) {
			echo '<br>' . esc_html( $display_date );
		}
	}
	add_action( 'manage_product_posts_custom_column', 'dlck_populate_last_edited_columns', 10, 2 );
}

// Optional: Style the custom columns (cached via inline assets).
add_action(
	'dlck_collect_inline_assets_admin',
	static function () {
		dlck_add_inline_css( '.column-last_edited_by{width:150px;}', 'admin' );
	}
);
