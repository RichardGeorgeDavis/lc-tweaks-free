<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dlck_is_gla_active' ) || ! dlck_is_gla_active() ) {
	return;
}

/**
 * Get Google Listings & Ads status meta for a product.
 */
function dlck_gla_get_status( int $post_id ): array {
	$visibility  = get_post_meta( $post_id, '_wc_gla_visibility', true );
	$sync_status = get_post_meta( $post_id, '_wc_gla_sync_status', true );
	$mc_status   = get_post_meta( $post_id, '_wc_gla_mc_status', true );
	$synced_at   = get_post_meta( $post_id, '_wc_gla_synced_at', true );
	$google_ids  = get_post_meta( $post_id, '_wc_gla_google_ids', true );
	$errors      = get_post_meta( $post_id, '_wc_gla_errors', true );

	return array(
		'visibility'  => is_string( $visibility ) ? $visibility : '',
		'sync_status' => is_string( $sync_status ) ? $sync_status : '',
		'mc_status'   => is_string( $mc_status ) ? $mc_status : '',
		'synced_at'   => (string) $synced_at,
		'google_ids'  => $google_ids,
		'errors'      => $errors,
	);
}

/**
 * Normalise a product's GLA status into a stable flag for querying/sorting.
 *  - '1' = synced
 *  - '0' = not synced (has errors)
 *  - '' = unknown / not processed / no status
 */
function dlck_gla_status_to_flag( array $status ): string {
	$sync = strtolower( trim( (string) ( $status['sync_status'] ?? '' ) ) );

	if ( in_array( $sync, array( 'pending', 'processing', 'scheduled', 'in-progress', 'in_progress', 'queue' ), true ) ) {
		return '2';
	}
	if ( $sync === 'synced' ) {
		return '1';
	}
	if ( $sync === 'has-errors' ) {
		return '0';
	}
	if ( $sync !== '' ) {
		return '';
	}
	if ( ! empty( $status['visibility'] ) ) {
		return '';
	}

	return '';
}

/**
 * Extract a short human-readable first error message if present.
 */
function dlck_gla_first_error( $errors ): string {
	if ( empty( $errors ) ) {
		return '';
	}

	$val = is_array( $errors ) ? $errors : maybe_unserialize( $errors );
	if ( is_array( $val ) && ! empty( $val[0] ) && is_string( $val[0] ) ) {
		return $val[0];
	}

	return '';
}

/**
 * Store/update our stable flag meta for a product.
 */
function dlck_mc_sync_update_flag( int $post_id ): void {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	$status = dlck_gla_get_status( $post_id );
	$flag   = dlck_gla_status_to_flag( $status );

	if ( $flag === '' ) {
		delete_post_meta( $post_id, '_lc_mc_sync_flag' );
		return;
	}

	update_post_meta( $post_id, '_lc_mc_sync_flag', $flag );
}
add_action( 'save_post_product', 'dlck_mc_sync_update_flag', 20 );

/**
 * Add admin column.
 */
function dlck_add_gla_mc_sync_column( array $cols ): array {
	$cols['lc_mc_sync'] = esc_html__( 'Google Sync', 'divi-lc-kit' );
	return $cols;
}
add_filter( 'manage_edit-product_columns', 'dlck_add_gla_mc_sync_column', 20 );

/**
 * Render admin column.
 */
function dlck_render_gla_mc_sync_column( string $column, int $post_id ): void {
	if ( $column !== 'lc_mc_sync' ) {
		return;
	}

	$flag = get_post_meta( $post_id, '_lc_mc_sync_flag', true );
	$status = dlck_gla_get_status( $post_id );
	$visibility = $status['visibility'] ?? '';

	if ( $flag === '' ) {
		$derived = dlck_gla_status_to_flag( $status );
		if ( $derived !== '' ) {
			$flag = $derived;
			update_post_meta( $post_id, '_lc_mc_sync_flag', $flag );
		}
	}

	$raw_status  = (string) ( $status['sync_status'] ?? '' );
	$sync_status = strtolower( trim( $raw_status ) );

	if ( $sync_status === 'synced' || $flag === '1' ) {
		$mc = ! empty( $status['mc_status'] )
			? ' <span class="lc-mc-sub">(' . esc_html( $status['mc_status'] ) . ')</span>'
			: '';
		echo '<span class="lc-mc-synced">' . esc_html__( 'Synced', 'divi-lc-kit' ) . '</span>' . $mc;
		echo '<span class="lc-mc-raw">' . esc_html( $raw_status ?: '-' ) . '</span>';
		echo '<span class="lc-gla-vis" data-vis="' . esc_attr( $visibility ) . '" style="display:none"></span>';
		return;
	}

	if ( $sync_status === 'has-errors' || $flag === '0' ) {
		$err = dlck_gla_first_error( $status['errors'] ?? null );
		$err_html = $err ? '<span class="lc-mc-sub">' . esc_html( $err ) . '</span>' : '';
		echo '<span class="lc-mc-not-synced">' . esc_html__( 'Has errors', 'divi-lc-kit' ) . '</span>' . $err_html;
		echo '<span class="lc-mc-raw">' . esc_html( $raw_status ?: '-' ) . '</span>';
		echo '<span class="lc-gla-vis" data-vis="' . esc_attr( $visibility ) . '" style="display:none"></span>';
		return;
	}

	if ( in_array( $sync_status, array( 'pending', 'processing', 'scheduled', 'in-progress', 'in_progress', 'queue' ), true ) || $flag === '2' ) {
		echo '<span class="lc-mc-pending">' . esc_html__( 'Pending', 'divi-lc-kit' ) . '</span>';
		echo '<span class="lc-mc-raw">' . esc_html( $raw_status ?: '-' ) . '</span>';
		echo '<span class="lc-gla-vis" data-vis="' . esc_attr( $visibility ) . '" style="display:none"></span>';
		return;
	}

	echo '<span class="lc-mc-unknown">' . esc_html__( 'Unknown', 'divi-lc-kit' ) . '</span>';
	echo '<span class="lc-mc-raw">' . esc_html( $raw_status ?: '-' ) . '</span>';
	echo '<span class="lc-gla-vis" data-vis="' . esc_attr( $visibility ) . '" style="display:none"></span>';
}
add_action( 'manage_product_posts_custom_column', 'dlck_render_gla_mc_sync_column', 10, 2 );

/**
 * Make column sortable.
 */
function dlck_gla_mc_sync_sortable_columns( array $cols ): array {
	$cols['lc_mc_sync'] = 'lc_mc_sync';
	return $cols;
}
add_filter( 'manage_edit-product_sortable_columns', 'dlck_gla_mc_sync_sortable_columns' );

/**
 * Sorting + filtering logic (Products screen only).
 */
function dlck_gla_mc_sync_products_query( WP_Query $query ): void {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'product' ) {
		return;
	}

	$filter = isset( $_GET['lc_mc_sync_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['lc_mc_sync_filter'] ) ) : '';
	if ( $filter !== '' ) {
		if ( $filter === 'u' ) {
			$query->set(
				'meta_query',
				array(
					array(
						'key'     => '_lc_mc_sync_flag',
						'compare' => 'NOT EXISTS',
					),
				)
			);
		} elseif ( in_array( $filter, array( '0', '1', '2' ), true ) ) {
			$query->set(
				'meta_query',
				array(
					array(
						'key'     => '_lc_mc_sync_flag',
						'value'   => $filter,
						'compare' => '=',
					),
				)
			);
		}
	}

	if ( $query->get( 'orderby' ) === 'lc_mc_sync' ) {
		$query->set( 'meta_key', '_lc_mc_sync_flag' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
add_action( 'pre_get_posts', 'dlck_gla_mc_sync_products_query' );

/**
 * Filter dropdown (last, before the main Filter button).
 */
function dlck_gla_mc_sync_filter_dropdown(): void {
	global $typenow;

	if ( $typenow !== 'product' ) {
		return;
	}

	$current = isset( $_GET['lc_mc_sync_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['lc_mc_sync_filter'] ) ) : '';

	echo '<label for="lc_mc_sync_filter" class="screen-reader-text">' . esc_html__( 'Google Sync', 'divi-lc-kit' ) . '</label>';
	echo '<select name="lc_mc_sync_filter" id="lc_mc_sync_filter" style="margin-left:8px;">';
	echo '<option value="">' . esc_html__( 'Google Sync: All', 'divi-lc-kit' ) . '</option>';
	echo '<option value="1" ' . selected( $current, '1', false ) . '>' . esc_html__( 'Synced', 'divi-lc-kit' ) . '</option>';
	echo '<option value="2" ' . selected( $current, '2', false ) . '>' . esc_html__( 'Pending', 'divi-lc-kit' ) . '</option>';
	echo '<option value="0" ' . selected( $current, '0', false ) . '>' . esc_html__( 'Has errors', 'divi-lc-kit' ) . '</option>';
	echo '<option value="u" ' . selected( $current, 'u', false ) . '>' . esc_html__( 'Unknown', 'divi-lc-kit' ) . '</option>';
	echo '</select>';
}
add_action( 'restrict_manage_posts', 'dlck_gla_mc_sync_filter_dropdown', 9999 );

/**
 * Quick Edit field (Products list) for GLA visibility.
 */
function dlck_gla_mc_sync_quick_edit_field( string $column_name, string $post_type ): void {
	if ( $post_type !== 'product' || $column_name !== 'lc_mc_sync' ) {
		return;
	}

	wp_nonce_field( 'lc_gla_inline', 'lc_gla_inline_nonce' );

	echo '<fieldset class="inline-edit-col-right"><div class="inline-edit-col">';
	echo '<label class="alignleft">';
	echo '<span class="title">' . esc_html__( 'Google Visibility', 'divi-lc-kit' ) . '</span>';
	echo '<select name="lc_gla_visibility" class="lc-gla-visibility">';
	echo '<option value="">' . esc_html__( 'No change', 'divi-lc-kit' ) . '</option>';
	echo '<option value="sync-and-show">' . esc_html__( 'Sync and show', 'divi-lc-kit' ) . '</option>';
	echo '<option value="do-not-sync">' . esc_html__( 'Do not sync', 'divi-lc-kit' ) . '</option>';
	echo '</select>';
	echo '</label>';
	echo '</div></fieldset>';
}
add_action( 'quick_edit_custom_box', 'dlck_gla_mc_sync_quick_edit_field', 10, 2 );

/**
 * Bulk Edit field (Products list) for GLA visibility.
 */
function dlck_gla_mc_sync_bulk_edit_field( string $column_name, string $post_type ): void {
	if ( $post_type !== 'product' || $column_name !== 'lc_mc_sync' ) {
		return;
	}

	wp_nonce_field( 'lc_gla_inline', 'lc_gla_inline_nonce' );

	echo '<fieldset class="inline-edit-col-right"><div class="inline-edit-col">';
	echo '<label class="alignleft">';
	echo '<span class="title">' . esc_html__( 'Google Visibility', 'divi-lc-kit' ) . '</span>';
	echo '<select name="lc_gla_visibility_bulk">';
	echo '<option value="">' . esc_html__( 'No change', 'divi-lc-kit' ) . '</option>';
	echo '<option value="sync-and-show">' . esc_html__( 'Sync and show', 'divi-lc-kit' ) . '</option>';
	echo '<option value="do-not-sync">' . esc_html__( 'Do not sync', 'divi-lc-kit' ) . '</option>';
	echo '</select>';
	echo '</label>';
	echo '</div></fieldset>';
}
add_action( 'bulk_edit_custom_box', 'dlck_gla_mc_sync_bulk_edit_field', 10, 2 );

/**
 * Quick Edit JS (prefill visibility dropdown).
 */
function dlck_gla_mc_sync_admin_js(): void {
	$js = 'if(typeof jQuery==="undefined"){return;}'
		. 'jQuery(function($){'
		. 'if(!document.body||!document.body.classList||!document.body.classList.contains("post-type-product")){return;}'
		. 'if(typeof inlineEditPost==="undefined"){return;}'
		. 'var oldEdit=inlineEditPost.edit;'
		. 'inlineEditPost.edit=function(id){'
		. 'oldEdit.apply(this,arguments);'
		. 'var postId=0;'
		. 'if(typeof id==="object"){postId=parseInt(this.getId(id),10);}else{postId=parseInt(id,10);}'
		. 'if(!postId){return;}'
		. 'var $row=$("#post-"+postId);'
		. 'var vis=$row.find(".column-lc_mc_sync .lc-gla-vis").data("vis")||"";'
		. 'var $qe=$("#edit-"+postId);'
		. '$qe.find("select.lc-gla-visibility").val(vis);'
		. '};'
		. '});';

	dlck_add_inline_js( $js, 'admin' );
}
add_action( 'dlck_collect_inline_assets_admin', 'dlck_gla_mc_sync_admin_js' );

/**
 * Quick + Bulk Edit save handlers (inline-save).
 */
function dlck_gla_mc_sync_inline_save(): void {
	if ( empty( $_POST['post_type'] ) || $_POST['post_type'] !== 'product' ) {
		return;
	}
	if ( ! isset( $_POST['lc_gla_inline_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lc_gla_inline_nonce'] ) ), 'lc_gla_inline' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_products' ) ) {
		return;
	}

	$post_id = isset( $_POST['post_ID'] ) ? (int) $_POST['post_ID'] : 0;
	$quick_val = isset( $_POST['lc_gla_visibility'] ) ? sanitize_text_field( wp_unslash( $_POST['lc_gla_visibility'] ) ) : '';

	if ( $post_id > 0 && $quick_val !== '' && in_array( $quick_val, array( 'sync-and-show', 'do-not-sync' ), true ) && current_user_can( 'edit_post', $post_id ) ) {
		update_post_meta( $post_id, '_wc_gla_visibility', $quick_val );

		if ( $quick_val === 'sync-and-show' ) {
			delete_post_meta( $post_id, '_wc_gla_sync_status' );
			delete_post_meta( $post_id, '_wc_gla_mc_status' );
			delete_post_meta( $post_id, '_wc_gla_errors' );
		}

		dlck_mc_sync_update_flag( $post_id );
	}

	$bulk_val = isset( $_POST['lc_gla_visibility_bulk'] ) ? sanitize_text_field( wp_unslash( $_POST['lc_gla_visibility_bulk'] ) ) : '';
	$post_ids = array();
	if ( ! empty( $_POST['post'] ) && is_array( $_POST['post'] ) ) {
		$post_ids = array_map( 'intval', $_POST['post'] );
	}

	if ( ! empty( $post_ids ) && $bulk_val !== '' && in_array( $bulk_val, array( 'sync-and-show', 'do-not-sync' ), true ) ) {
		foreach ( $post_ids as $pid ) {
			if ( $pid <= 0 ) {
				continue;
			}
			if ( ! current_user_can( 'edit_post', $pid ) ) {
				continue;
			}

			update_post_meta( $pid, '_wc_gla_visibility', $bulk_val );

			if ( $bulk_val === 'sync-and-show' ) {
				delete_post_meta( $pid, '_wc_gla_sync_status' );
				delete_post_meta( $pid, '_wc_gla_mc_status' );
				delete_post_meta( $pid, '_wc_gla_errors' );
			}

			dlck_mc_sync_update_flag( $pid );
		}
	}
}
add_action( 'wp_ajax_inline-save', 'dlck_gla_mc_sync_inline_save', 5 );

/**
 * Bulk actions: add actions.
 */
function dlck_gla_mc_sync_bulk_actions( array $actions ): array {
	$actions['dlck_gla_sync_and_show'] = esc_html__( 'Google: Sync and show', 'divi-lc-kit' );
	$actions['dlck_gla_do_not_sync'] = esc_html__( 'Google: Do not sync', 'divi-lc-kit' );
	$actions['dlck_gla_retry_sync'] = esc_html__( 'Google: Retry sync', 'divi-lc-kit' );
	return $actions;
}
add_filter( 'bulk_actions-edit-product', 'dlck_gla_mc_sync_bulk_actions' );

/**
 * Bulk actions: handle actions.
 */
function dlck_gla_mc_sync_handle_bulk_actions( string $redirect_url, string $action, array $post_ids ): string {
	$map = array(
		'dlck_gla_sync_and_show' => 'sync-and-show',
		'dlck_gla_do_not_sync'   => 'do-not-sync',
	);

	if ( ! current_user_can( 'edit_products' ) ) {
		return add_query_arg( 'dlck_gla_bulk_updated', 0, $redirect_url );
	}

	$updated = 0;

	if ( $action === 'dlck_gla_retry_sync' ) {
		foreach ( (array) $post_ids as $pid ) {
			$pid = (int) $pid;
			if ( $pid <= 0 ) {
				continue;
			}
			if ( ! current_user_can( 'edit_post', $pid ) ) {
				continue;
			}

			update_post_meta( $pid, '_wc_gla_visibility', 'do-not-sync' );
			update_post_meta( $pid, '_wc_gla_visibility', 'sync-and-show' );

			delete_post_meta( $pid, '_wc_gla_sync_status' );
			delete_post_meta( $pid, '_wc_gla_mc_status' );
			delete_post_meta( $pid, '_wc_gla_errors' );

			dlck_mc_sync_update_flag( $pid );
			$updated++;
		}

		return add_query_arg( 'dlck_gla_bulk_retried', $updated, $redirect_url );
	}

	if ( ! isset( $map[ $action ] ) ) {
		return $redirect_url;
	}

	$val = $map[ $action ];

	foreach ( (array) $post_ids as $pid ) {
		$pid = (int) $pid;
		if ( $pid <= 0 ) {
			continue;
		}
		if ( ! current_user_can( 'edit_post', $pid ) ) {
			continue;
		}

		update_post_meta( $pid, '_wc_gla_visibility', $val );

		if ( $val === 'sync-and-show' ) {
			delete_post_meta( $pid, '_wc_gla_sync_status' );
			delete_post_meta( $pid, '_wc_gla_mc_status' );
			delete_post_meta( $pid, '_wc_gla_errors' );
		}

		dlck_mc_sync_update_flag( $pid );
		$updated++;
	}

	return add_query_arg( 'dlck_gla_bulk_updated', $updated, $redirect_url );
}
add_filter( 'handle_bulk_actions-edit-product', 'dlck_gla_mc_sync_handle_bulk_actions', 10, 3 );

/**
 * Bulk actions: notices.
 */
function dlck_gla_mc_sync_bulk_notices(): void {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || $screen->id !== 'edit-product' ) {
		return;
	}

	if ( isset( $_GET['dlck_gla_bulk_updated'] ) ) {
		$count = (int) $_GET['dlck_gla_bulk_updated'];
		echo '<div class="notice notice-success is-dismissible"><p>'
			. esc_html( sprintf( 'Google visibility updated for %d product(s).', $count ) )
			. '</p></div>';
	}

	if ( isset( $_GET['dlck_gla_bulk_retried'] ) ) {
		$count = (int) $_GET['dlck_gla_bulk_retried'];
		echo '<div class="notice notice-success is-dismissible"><p>'
			. esc_html( sprintf( 'Google: Retry sync queued for %d product(s).', $count ) )
			. '</p></div>';
	}
}
add_action( 'admin_notices', 'dlck_gla_mc_sync_bulk_notices' );

/**
 * Admin CSS (width + colors).
 */
function dlck_gla_mc_sync_admin_css(): void {
	$css = '.wp-list-table{table-layout:fixed!important;}'
		. '.wp-list-table th.column-lc_mc_sync,.wp-list-table td.column-lc_mc_sync,colgroup col.column-lc_mc_sync{width:240px!important;min-width:240px!important;max-width:240px!important;text-align:left;vertical-align:top;white-space:normal;overflow-wrap:anywhere;word-break:break-word;}'
		. '.lc-mc-synced{color:#1a7f37;font-weight:600;}'
		. '.lc-mc-not-synced{color:#b32d2e;font-weight:600;}'
		. '.lc-mc-pending{color:#b76e00;font-weight:600;}'
		. '.lc-mc-unknown{opacity:.7;}'
		. '.lc-mc-sub,.lc-mc-raw{display:block;margin-top:2px;opacity:.75;font-size:11px;white-space:normal;overflow-wrap:anywhere;}'
		. '.lc-mc-raw{opacity:.65;}';

	dlck_add_inline_css( $css, 'admin' );
}
add_action( 'dlck_collect_inline_assets_admin', 'dlck_gla_mc_sync_admin_css' );
