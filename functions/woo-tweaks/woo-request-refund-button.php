<?php
/**
 * @package Refund Button @ WooCommerce My Account Order Actions
 * @version 1.0
 */

add_filter( 'woocommerce_my_account_my_orders_actions', 'dlck_add_refund_request_action', 10, 2 );
add_action( 'woocommerce_view_order', 'dlck_output_refund_modal' );
add_action( 'woocommerce_after_account_orders', 'dlck_output_refund_modal' );
add_action( 'wp_ajax_dlck_submit_refund_request', 'dlck_submit_refund_request' );
add_action( 'dlck_collect_inline_assets_front', 'dlck_collect_refund_request_assets' );

/**
 * Add "Request Refund" action on My Account > Orders.
 *
 * @param array    $actions Order action links.
 * @param WC_Order $order   WooCommerce order object.
 * @return array
 */
function dlck_add_refund_request_action( $actions, $order ) {
	if ( is_order_received_page() ) {
		return $actions;
	}

	if ( ! $order instanceof WC_Order ) {
		return $actions;
	}

	if ( in_array( $order->get_status(), array( 'completed', 'processing' ), true ) ) {
		$refund_requested = $order->get_meta( '_bb_refund_requested' );
		if ( $refund_requested ) {
			$actions['bb-refund-pending'] = array(
				'url'  => '#',
				'name' => '✓ Pending Refund',
			);
			return $actions;
		}

		$order_date = $order->get_date_created();
		if ( $order_date ) {
			$days_since_order = ( time() - $order_date->getTimestamp() ) / DAY_IN_SECONDS;

			if ( $days_since_order <= 60 ) {
				$actions['bb_request_refund'] = array(
					'url'        => '#refund-' . $order->get_id(),
					'name'       => 'Ask for a Refund',
					'aria-label' => 'Ask for a Refund',
				);
			}
		}
	}

	return $actions;
}

/**
 * Add CSS to grey out pending refund button.
 */
function dlck_collect_refund_request_assets() {
	dlck_add_inline_css(
		'a.bb-refund-pending{opacity:0.5;cursor:not-allowed!important;pointer-events:none;}'
	);

	dlck_add_inline_js(
		"document.addEventListener('DOMContentLoaded',function(){\n" .
		"var dialog=document.getElementById('bb-refund-dialog');\n" .
		"if(!dialog){return;}\n" .
		"var ajaxUrl=dialog.getAttribute('data-ajax-url');\n" .
		"var nonce=dialog.getAttribute('data-nonce');\n" .
		"if(!ajaxUrl||!nonce){return;}\n" .
		"document.querySelectorAll('a.bb_request_refund').forEach(function(btn){\n" .
		"btn.addEventListener('click',function(e){\n" .
		"e.preventDefault();\n" .
		"var orderID=this.getAttribute('href').replace('#refund-','');\n" .
		"document.getElementById('bb-order-id').value=orderID;\n" .
		"document.getElementById('bb-reason').value='';\n" .
		"dialog.showModal();\n" .
		"});\n" .
		"});\n" .
		"var cancelBtn=document.getElementById('bb-refund-cancel');\n" .
		"if(cancelBtn){\n" .
		"cancelBtn.addEventListener('click',function(e){\n" .
		"e.preventDefault();\n" .
		"dialog.close();\n" .
		"});\n" .
		"}\n" .
		"var submitBtn=document.getElementById('bb-refund-submit');\n" .
		"if(submitBtn){\n" .
		"submitBtn.addEventListener('click',function(e){\n" .
		"e.preventDefault();\n" .
		"var orderID=document.getElementById('bb-order-id').value;\n" .
		"var reason=document.getElementById('bb-reason').value;\n" .
		"if(!orderID||reason.trim()===''){return;}\n" .
		"fetch(ajaxUrl,{\n" .
		"method:'POST',\n" .
		"headers:{'Content-Type':'application/x-www-form-urlencoded'},\n" .
		"body:new URLSearchParams({action:'dlck_submit_refund_request',order_id:orderID,reason:reason,nonce:nonce})\n" .
		"})\n" .
		".then(function(r){return r.json();})\n" .
		".then(function(data){\n" .
		"dialog.close();\n" .
		"if(data.success){\n" .
		"alert('Your refund request has been submitted.');\n" .
		"location.reload();\n" .
		"}else{\n" .
		"alert('Error: '+(data.message||'Something went wrong'));\n" .
		"}\n" .
		"})\n" .
		".catch(function(){\n" .
		"alert('Network error. Please try again.');\n" .
		"});\n" .
		"});\n" .
		"}\n" .
		"});"
	);
}

/**
 * Output the HTML dialog modal + small JS handler.
 */
function dlck_output_refund_modal() {
	static $rendered = false;

	if ( $rendered ) {
		return;
	}

	$rendered = true;
	$ajax_url = admin_url( 'admin-ajax.php' );
	$nonce    = wp_create_nonce( 'dlck_refund_nonce' );
	?>

	<dialog id="bb-refund-dialog" data-ajax-url="<?php echo esc_url( $ajax_url ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>">
		<form method="dialog" id="bb-refund-form">
			<h3>Request a Refund</h3>

			<input type="hidden" name="order_id" id="bb-order-id" value="">

			<label for="bb-reason">Reason for refund:</label>
			<textarea id="bb-reason" name="reason" required style="width:100%;height:120px;"></textarea>

			<div style="margin-top:1rem;">
				<button id="bb-refund-cancel" type="button">Cancel</button>
				<button id="bb-refund-submit" type="button">Submit</button>
			</div>
		</form>
	</dialog>

	<?php
}

/**
 * AJAX handler: add customer note + send admin email.
 */
function dlck_submit_refund_request() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'dlck_refund_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed' ) );
	}

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'You must be logged in.' ) );
	}

	if ( empty( $_POST['order_id'] ) || empty( $_POST['reason'] ) ) {
		wp_send_json_error( array( 'message' => 'Missing required fields' ) );
	}

	$order_id = absint( $_POST['order_id'] );
	$reason   = sanitize_textarea_field( $_POST['reason'] );

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		wp_send_json_error( array( 'message' => 'Invalid order' ) );
	}

	$current_user_id = get_current_user_id();
	if ( ! current_user_can( 'manage_woocommerce' ) && (int) $order->get_user_id() !== $current_user_id ) {
		wp_send_json_error( array( 'message' => 'Permission denied' ) );
	}

	$existing_request = $order->get_meta( '_bb_refund_requested' );
	if ( $existing_request ) {
		wp_send_json_error( array( 'message' => 'Refund request already submitted' ) );
	}

	$order_date = $order->get_date_created();
	if ( $order_date ) {
		$days_since_order = ( time() - $order_date->getTimestamp() ) / DAY_IN_SECONDS;
		if ( $days_since_order > 60 ) {
			wp_send_json_error( array( 'message' => 'Refund window has expired' ) );
		}
	}

	$order->update_meta_data( '_bb_refund_requested', current_time( 'mysql' ) );
	$order->update_meta_data( '_bb_refund_reason', $reason );
	$order->save();

	$order->add_order_note( 'Customer requested a refund: ' . $reason, true );

	wp_mail(
		get_option( 'admin_email' ),
		'Refund Request for Order #' . $order_id,
		"A customer has requested a refund.\n\nOrder: #{$order_id}\nReason:\n{$reason}\n\nView order: " . admin_url( 'post.php?post=' . $order_id . '&action=edit' )
	);

	wp_send_json_success( array( 'message' => 'Refund request submitted' ) );
}

?>
