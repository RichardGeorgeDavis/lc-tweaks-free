<?php
/**
 * Admin tool: replace an image while keeping the original attachment ID and URL.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_enqueue_scripts', 'dlck_replace_image_enqueue_scripts' );
add_filter( 'attachment_fields_to_edit', 'dlck_replace_image_attachment_fields', 10, 2 );
add_action( 'edit_attachment', 'dlck_replace_image_process_attachment_edit' );
add_filter( 'wp_calculate_image_srcset', 'dlck_replace_image_calculate_image_srcset' );
add_filter( 'wp_get_attachment_image_src', 'dlck_replace_image_get_attachment_image_src' );
add_filter( 'wp_prepare_attachment_for_js', 'dlck_replace_image_prepare_attachment_for_js' );

/**
 * Enqueue the replace-image helper script anywhere media modals may appear.
 */
function dlck_replace_image_enqueue_scripts(): void {
	if ( ! current_user_can( 'upload_files' ) ) {
		return;
	}

	wp_enqueue_script(
		'dlck-lc-kit-replace-image',
		DLCK_LC_KIT_PLUGIN_URI . '/assets/js/admin/lc-kit-replace-image.js',
		array( 'jquery', 'media-editor', 'media-views' ),
		'1.0.0',
		true
	);

	wp_localize_script(
		'dlck-lc-kit-replace-image',
		'dlckReplaceImage',
		array(
			'title'  => __( 'Choose Replacement Image', 'divi-lc-kit' ),
			'button' => __( 'Replace Image', 'divi-lc-kit' ),
		)
	);
}

/**
 * Add the Replace Image controls to image attachment edit forms.
 *
 * @param array   $fields Attachment edit fields.
 * @param WP_Post $attachment Attachment post object.
 * @return array
 */
function dlck_replace_image_attachment_fields( array $fields, $attachment ): array {
	if ( ! $attachment instanceof WP_Post ) {
		return $fields;
	}

	if ( ! wp_attachment_is_image( $attachment->ID ) || ! current_user_can( 'edit_post', $attachment->ID ) ) {
		return $fields;
	}

	wp_enqueue_media();

	$fields['dlck_replace_image'] = array(
		'label' => '',
		'input' => 'html',
		'html'  =>
			'<button type="button" class="button-secondary button-large" onclick="dlckReplaceImageOpen();">'
			. esc_html__( 'Replace Image', 'divi-lc-kit' )
			. '</button>'
			. '<input type="hidden" id="dlck_replace_image_with_fld" name="dlck_replace_image_with" />'
			. wp_nonce_field( 'dlck_replace_image', 'dlck_replace_image_nonce', false, false )
			. '<p><strong>'
			. esc_html__( 'Warning:', 'divi-lc-kit' )
			. '</strong> '
			. esc_html__( 'Replacing this image permanently removes the current image files and regenerates sizes using the replacement image while keeping the original attachment ID and URL.', 'divi-lc-kit' )
			. '</p>',
	);

	return $fields;
}

/**
 * Handle replacement after an attachment edit save.
 *
 * @param int $post_id Attachment ID being edited.
 */
function dlck_replace_image_process_attachment_edit( int $post_id ): void {
	if ( ! current_user_can( 'edit_post', $post_id ) || ! wp_attachment_is_image( $post_id ) ) {
		return;
	}

	$replacement_id = isset( $_POST['dlck_replace_image_with'] ) ? absint( wp_unslash( $_POST['dlck_replace_image_with'] ) ) : 0;
	$nonce          = isset( $_POST['dlck_replace_image_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['dlck_replace_image_nonce'] ) ) : '';

	if ( $replacement_id <= 0 || $replacement_id === $post_id ) {
		return;
	}

	if ( ! wp_verify_nonce( $nonce, 'dlck_replace_image' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $replacement_id ) || ! wp_attachment_is_image( $replacement_id ) ) {
		return;
	}

	$upload_dir     = wp_upload_dir();
	$replacement_rel = get_post_meta( $replacement_id, '_wp_attached_file', true );
	$replacement_file = is_string( $replacement_rel ) ? path_join( $upload_dir['basedir'], $replacement_rel ) : '';
	$original_rel    = get_post_meta( $post_id, '_wp_attached_file', true );
	$original_file   = is_string( $original_rel ) ? path_join( $upload_dir['basedir'], $original_rel ) : '';
	$same_file_path  = wp_normalize_path( $replacement_file ) === wp_normalize_path( $original_file );

	if ( $replacement_file === '' || ! is_file( $replacement_file ) || $original_file === '' ) {
		return;
	}

	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	$target_dir = dirname( $original_file );
	if ( ! file_exists( $target_dir ) ) {
		wp_mkdir_p( $target_dir );
	}

	global $wp_filesystem;
	if ( ! WP_Filesystem() || ! $wp_filesystem ) {
		return;
	}

	if ( ! $wp_filesystem->copy( $replacement_file, $original_file, true ) ) {
		return;
	}

	dlck_replace_image_delete_attachment_files( $post_id, false );

	$metadata = wp_generate_attachment_metadata( $post_id, $original_file );
	if ( is_array( $metadata ) ) {
		wp_update_attachment_metadata( $post_id, $metadata );
	}
	clean_attachment_cache( $post_id );

	if ( ! $same_file_path && current_user_can( 'delete_post', $replacement_id ) ) {
		wp_delete_attachment( $replacement_id, true );
	}
}

/**
 * Delete the physical files associated with an image attachment but keep the post.
 *
 * @param int  $post_id Attachment ID.
 * @param bool $delete_original Whether to remove the original file too.
 */
function dlck_replace_image_delete_attachment_files( int $post_id, bool $delete_original = true ): void {
	$metadata     = wp_get_attachment_metadata( $post_id );
	$backup_sizes = get_post_meta( $post_id, '_wp_attachment_backup_sizes', true );
	$file         = get_attached_file( $post_id );

	if ( ! is_string( $file ) || $file === '' ) {
		return;
	}

	if ( is_multisite() ) {
		delete_transient( 'dirsize_cache' );
	}

	$upload_path = wp_get_upload_dir();

	if ( ! empty( $metadata['thumb'] ) ) {
		$thumb_file = str_replace( basename( $file ), (string) $metadata['thumb'], $file );
		$thumb_file = apply_filters( 'wp_delete_file', $thumb_file );
		@unlink( $thumb_file );
	}

	if ( isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
		foreach ( $metadata['sizes'] as $size_info ) {
			if ( ! is_array( $size_info ) || empty( $size_info['file'] ) ) {
				continue;
			}

			$intermediate_file = str_replace( basename( $file ), (string) $size_info['file'], $file );
			$intermediate_file = apply_filters( 'wp_delete_file', $intermediate_file );
			@unlink( $intermediate_file );
		}
	}

	if ( is_array( $backup_sizes ) ) {
		foreach ( $backup_sizes as $size ) {
			if ( ! is_array( $size ) || empty( $size['file'] ) ) {
				continue;
			}

			$delete_file = path_join( dirname( (string) ( $metadata['file'] ?? '' ) ), (string) $size['file'] );
			$delete_file = apply_filters( 'wp_delete_file', $delete_file );
			@unlink( path_join( $upload_path['basedir'], $delete_file ) );
		}

		delete_post_meta( $post_id, '_wp_attachment_backup_sizes' );
	}

	if ( $delete_original ) {
		wp_delete_file( $file );
	}
}

/**
 * Append a cache-busting query string for admin image previews.
 *
 * @param string $url Image URL.
 * @return string
 */
function dlck_replace_image_cache_bust_url( string $url ): string {
	if ( $url === '' ) {
		return '';
	}

	return add_query_arg( '_dlck_replace_image_t', (string) time(), $url );
}

/**
 * Cache-bust srcset URLs in admin after replacement.
 *
 * @param array $sources Srcset sources.
 * @return array
 */
function dlck_replace_image_calculate_image_srcset( $sources ) {
	if ( ! is_admin() || ! is_array( $sources ) ) {
		return $sources;
	}

	foreach ( $sources as $size => $source ) {
		if ( isset( $source['url'] ) && is_string( $source['url'] ) ) {
			$source['url']  = dlck_replace_image_cache_bust_url( $source['url'] );
			$sources[ $size ] = $source;
		}
	}

	return $sources;
}

/**
 * Cache-bust single image preview URLs in admin after replacement.
 *
 * @param array $attr Attachment image src response.
 * @return array
 */
function dlck_replace_image_get_attachment_image_src( $attr ) {
	if ( ! is_admin() || ! is_array( $attr ) || empty( $attr[0] ) || ! is_string( $attr[0] ) ) {
		return $attr;
	}

	$attr[0] = dlck_replace_image_cache_bust_url( $attr[0] );
	return $attr;
}

/**
 * Cache-bust media modal preview URLs in admin after replacement.
 *
 * @param array $response Attachment JS response.
 * @return array
 */
function dlck_replace_image_prepare_attachment_for_js( $response ) {
	if ( ! is_admin() || ! is_array( $response ) ) {
		return $response;
	}

	if ( ! empty( $response['url'] ) && is_string( $response['url'] ) ) {
		$response['url'] = dlck_replace_image_cache_bust_url( $response['url'] );
	}

	if ( isset( $response['sizes'] ) && is_array( $response['sizes'] ) ) {
		foreach ( $response['sizes'] as $size_name => $size ) {
			if ( ! is_array( $size ) || empty( $size['url'] ) || ! is_string( $size['url'] ) ) {
				continue;
			}

			$response['sizes'][ $size_name ]['url'] = dlck_replace_image_cache_bust_url( $size['url'] );
		}
	}

	return $response;
}
