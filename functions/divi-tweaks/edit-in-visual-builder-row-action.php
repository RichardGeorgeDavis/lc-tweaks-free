<?php
/**
 * Add "Edit in Visual Builder" row actions for posts/pages that use Divi Builder.
 */

add_filter( 'post_row_actions', 'dlck_edit_in_visual_builder_row_action', 20, 2 );
add_filter( 'page_row_actions', 'dlck_edit_in_visual_builder_row_action', 20, 2 );

/**
 * Append an "Edit in Visual Builder" row action.
 *
 * @param array<string,string> $actions Existing row actions.
 * @param WP_Post              $post    Current post object.
 * @return array<string,string>
 */
function dlck_edit_in_visual_builder_row_action( $actions, $post ) {
	// If another plugin already added this action (e.g. Divi Pixel), just move it to the first position.
	if ( isset( $actions['edit_in_visual_builder'] ) ) {
		$edit_in_visual_builder_action = $actions['edit_in_visual_builder'];
		unset( $actions['edit_in_visual_builder'] );

		return array( 'edit_in_visual_builder' => $edit_in_visual_builder_action ) + $actions;
	}

	if ( ! $post instanceof WP_Post ) {
		return $actions;
	}

	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		return $actions;
	}

	if ( function_exists( 'et_builder_enabled_for_post_type' ) && ! et_builder_enabled_for_post_type( $post->post_type ) ) {
		return $actions;
	}

	if ( ! function_exists( 'et_pb_is_pagebuilder_used' ) || ! et_pb_is_pagebuilder_used( $post->ID ) ) {
		return $actions;
	}

	if ( ! in_array( $post->post_status, array( 'publish', 'draft' ), true ) ) {
		return $actions;
	}

	$permalink = get_permalink( $post->ID );
	if ( ! $permalink ) {
		return $actions;
	}

	$builder_url = function_exists( 'dlck_get_divi_visual_builder_url' )
		? dlck_get_divi_visual_builder_url( $permalink )
		: ( function_exists( 'et_fb_get_vb_url' ) ? et_fb_get_vb_url( $permalink ) : add_query_arg( 'et_fb', '1', $permalink ) );

	$edit_in_visual_builder_action = sprintf(
		'<a href="%1$s">%2$s</a>',
		esc_url( $builder_url ),
		esc_html__( 'Edit in Visual Builder', 'divi-lc-kit' )
	);

	return array( 'edit_in_visual_builder' => $edit_in_visual_builder_action ) + $actions;
}
