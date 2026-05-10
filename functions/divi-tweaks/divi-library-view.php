<?php
/**
 * Enhanced Divi Library previews and admin actions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	static function () {
		if ( ! post_type_exists( 'et_pb_layout' ) ) {
			return;
		}

		add_post_type_support( 'et_pb_layout', 'thumbnail' );
		if ( function_exists( 'add_image_size' ) ) {
			add_image_size( 'dlck_divi_library_thumb', 120, 120, true );
		}
	},
	20
);

add_filter(
	'register_post_type_args',
	static function ( $args, $post_type ) {
		if ( $post_type !== 'et_pb_layout' ) {
			return $args;
		}

		$args['publicly_queryable']  = false;
		$args['exclude_from_search'] = true;

		return $args;
	},
	10,
	2
);

add_filter(
	'query_vars',
	static function ( array $vars ): array {
		$vars[] = 'dlck_divi_library_preview';
		return $vars;
	}
);

add_filter(
	'manage_et_pb_layout_posts_columns',
	static function ( array $columns ): array {
		$new_columns = array();
		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;
			if ( $key === 'cb' ) {
				$new_columns['dlck_featured_image'] = __( 'Thumbnail', 'lc-tweaks' );
			}
		}
		return $new_columns;
	}
);

add_action(
	'manage_et_pb_layout_posts_custom_column',
	static function ( string $column, int $post_id ): void {
		if ( $column !== 'dlck_featured_image' ) {
			return;
		}

		$image = get_the_post_thumbnail( $post_id, 'dlck_divi_library_thumb', array( 'style' => 'max-width:120px;height:auto;' ) );
		echo $image ? wp_kses_post( $image ) : '&mdash;';
	},
	10,
	2
);

if ( ! function_exists( 'dlck_divi_library_edit_with_divi_url' ) ) {
	/**
	 * Return a stable edit URL for Divi Library items.
	 *
	 * Divi Library layouts do not have a reliable public singular context for
	 * frontend Visual Builder URLs, so use the post editor where Divi loads its
	 * library builder UI.
	 */
	function dlck_divi_library_edit_with_divi_url( WP_Post $post ): string {
		$edit_url = get_edit_post_link( $post->ID, 'raw' );
		return is_string( $edit_url ) ? $edit_url : '';
	}
}

add_filter(
	'post_row_actions',
	static function ( array $actions, WP_Post $post ): array {
		if ( $post->post_type !== 'et_pb_layout' || ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}

		$preview_url = wp_nonce_url(
			add_query_arg( 'dlck_divi_library_preview', absint( $post->ID ), home_url( '/' ) ),
			'dlck_divi_library_preview_' . $post->ID
		);

		$actions['dlck_view_layout'] = sprintf(
			'<a href="%1$s" target="_blank" rel="noopener">%2$s</a>',
			esc_url( $preview_url ),
			esc_html__( 'View', 'lc-tweaks' )
		);

		$builder_url = dlck_divi_library_edit_with_divi_url( $post );
		if ( $builder_url !== '' ) {
			$actions['dlck_edit_layout_builder'] = sprintf(
				'<a href="%1$s" target="_blank" rel="noopener">%2$s</a>',
				esc_url( $builder_url ),
				esc_html__( 'Edit With Divi', 'lc-tweaks' )
			);
		}

		return $actions;
	},
	10,
	2
);

add_action(
	'template_redirect',
	static function (): void {
		$preview_id = absint( get_query_var( 'dlck_divi_library_preview' ) );
		if ( ! $preview_id ) {
			return;
		}

		if ( get_post_type( $preview_id ) !== 'et_pb_layout' || ! current_user_can( 'edit_post', $preview_id ) ) {
			status_header( 404 );
			return;
		}

		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'dlck_divi_library_preview_' . $preview_id ) ) {
			status_header( 403 );
			return;
		}

		nocache_headers();
		header( 'X-Robots-Tag: noindex, nofollow', true );

		add_filter( 'wp_robots', static fn( array $robots ): array => array_merge( $robots, array( 'noindex' => true, 'nofollow' => true ) ) );
		add_filter( 'redirect_canonical', '__return_false' );

		$content = get_post_field( 'post_content', $preview_id );
		get_header();
		echo '<main id="dlck-divi-library-preview" class="dlck-divi-library-preview">';
		echo $content ? apply_filters( 'the_content', $content ) : '';
		echo '</main>';
		get_footer();
		exit;
	},
	0
);

add_action(
	'template_redirect',
	static function (): void {
		if ( is_admin() || ! is_singular( 'et_pb_layout' ) ) {
			return;
		}

		global $wp_query;
		if ( $wp_query instanceof WP_Query ) {
			$wp_query->set_404();
		}
		status_header( 404 );
		nocache_headers();
	}
);
