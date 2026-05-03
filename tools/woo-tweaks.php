<?php

	$dlck_settings_snapshot = function_exists( 'dlck_get_settings_snapshot' ) ? dlck_get_settings_snapshot() : array();
	$dlck_setting = static function ( string $key, $default = '' ) use ( $dlck_settings_snapshot ) {
		if ( array_key_exists( $key, $dlck_settings_snapshot ) ) {
			$value = $dlck_settings_snapshot[ $key ];
			return ( $value === '' && $default !== '' ) ? $default : $value;
		}
		return $default;
	};

	$dlck_woo_resave_all_products_val = $dlck_setting( 'dlck_woo_resave_all_products' );
	$dlck_disable_woocommerce_admin_val = $dlck_setting( 'dlck_disable_woocommerce_admin' );
		$dlck_remove_woo_files_val = $dlck_setting( 'dlck_remove_woo_files' );
		$dlck_remove_woo_all_files_val = $dlck_setting( 'dlck_remove_woo_all_files' );
		$dlck_remove_woo_block_files_val = $dlck_setting( 'dlck_remove_woo_block_files' );
		$dlck_woo_cart_script_policy_val = sanitize_key( (string) $dlck_setting( 'dlck_woo_cart_script_policy' ) );
		if ( ! in_array( $dlck_woo_cart_script_policy_val, array( 'default', 'disable_non_woo', 'disable_everywhere', 'disable_non_woo_plus_add_to_cart' ), true ) ) {
			$dlck_woo_cart_script_policy_val = 'default';
		}
		$dlck_woo_session_expiration_val = $dlck_setting( 'dlck_woo_session_expiration' );
		$dlck_woo_session_cleanup_health = function_exists( 'dlck_get_woo_session_cleanup_health_snapshot' ) ? dlck_get_woo_session_cleanup_health_snapshot() : array();
		$dlck_woo_session_cleanup_enabled = ! empty( $dlck_woo_session_cleanup_health['enabled'] );
		$dlck_woo_session_cleanup_recurrence = isset( $dlck_woo_session_cleanup_health['recurrence'] ) ? (string) $dlck_woo_session_cleanup_health['recurrence'] : 'daily';
		$dlck_woo_session_cleanup_recurrence_label = $dlck_woo_session_cleanup_recurrence === 'twicedaily' ? __( 'Twice daily', 'lc-tweaks' ) : __( 'Daily', 'lc-tweaks' );
		$dlck_woo_session_cleanup_next_run = isset( $dlck_woo_session_cleanup_health['next_run'] ) ? (int) $dlck_woo_session_cleanup_health['next_run'] : 0;
		$dlck_woo_session_cleanup_last_run = isset( $dlck_woo_session_cleanup_health['last_run'] ) ? (int) $dlck_woo_session_cleanup_health['last_run'] : 0;
		$dlck_woo_session_cleanup_status = isset( $dlck_woo_session_cleanup_health['status'] ) ? sanitize_key( (string) $dlck_woo_session_cleanup_health['status'] ) : 'unknown';
		$dlck_woo_session_cleanup_deleted_count = isset( $dlck_woo_session_cleanup_health['deleted_count'] ) ? (int) $dlck_woo_session_cleanup_health['deleted_count'] : 0;
		$dlck_woo_session_cleanup_row_count = isset( $dlck_woo_session_cleanup_health['row_count'] ) ? (int) $dlck_woo_session_cleanup_health['row_count'] : null;
		$dlck_woo_session_cleanup_expired_count = isset( $dlck_woo_session_cleanup_health['expired_count'] ) ? (int) $dlck_woo_session_cleanup_health['expired_count'] : null;
		$dlck_woo_session_cleanup_message = isset( $dlck_woo_session_cleanup_health['message'] ) ? (string) $dlck_woo_session_cleanup_health['message'] : '';
	$dlck_woo_disable_persistent_cart_val = $dlck_setting( 'dlck_woo_disable_persistent_cart' );
	$dlck_wp_rocket_side_cart_exclusion_val = $dlck_setting( 'dlck_wp_rocket_side_cart_exclusion' );
	$dlck_add_a_line_break_in_woocommerce_product_titles_val = $dlck_setting( 'dlck_add_a_line_break_in_woocommerce_product_titles' );
	$dlck_woo_checkout_empty_defaults_val = $dlck_setting( 'dlck_woo_checkout_empty_defaults' );
	$dlck_woo_city_label_suburb_val = $dlck_setting( 'dlck_woo_city_label_suburb' );
	$dlck_wc_orders_admin_search_by_sku_val = $dlck_setting( 'dlck_wc_orders_admin_search_by_sku' );
	$dlck_wc_orders_admin_user_role_column_val = $dlck_setting( 'dlck_wc_orders_admin_user_role_column' );
	$dlck_wp_admin_users_order_counts_column_val = $dlck_setting( 'dlck_wp_admin_users_order_counts_column' );
	$dlck_wc_products_admin_stock_status_column_val = $dlck_setting( 'dlck_wc_products_admin_stock_status_column' );
	$dlck_wc_products_last_edited_meta_and_columns_val = $dlck_setting( 'dlck_wc_products_last_edited_meta_and_columns' );
	$dlck_woo_gla_mc_sync_column_val = $dlck_setting( 'dlck_woo_gla_mc_sync_column' );
	$dlck_woo_restrict_store_to_logged_in_val = $dlck_setting( 'dlck_woo_restrict_store_to_logged_in' );
	$dlck_disable_checkout_field_autocomplete_val = $dlck_setting( 'dlck_disable_checkout_field_autocomplete' );
	$dlck_notify_admin_when_a_new_customer_account_is_created_val = $dlck_setting( 'dlck_notify_admin_when_a_new_customer_account_is_created' );
	$dlck_disable_admin_new_user_notification_emails_val          = $dlck_setting( 'dlck_disable_admin_new_user_notification_emails' );
	$dlck_read_more_to_out_of_stock_val = $dlck_setting( 'dlck_read_more_to_out_of_stock' );
	$dlck_stop_woo_menu_item_from_displaying_for_anyone_but_administrator_val = $dlck_setting( 'dlck_stop_woo_menu_item_from_displaying_for_anyone_but_administrator' );
	$dlck_shop_single_column_on_mobile_val = $dlck_setting( 'dlck_shop_single_column_on_mobile' );
	$dlck_woo_add_to_cart_button_val = $dlck_setting( 'dlck_woo_add_to_cart_button' );
	$dlck_shop_masonry_layout_val = $dlck_setting( 'dlck_shop_masonry_layout' );
	$dlck_woocommerce_hide_price_and_add_to_cart_for_logged_out_users_val = $dlck_setting( 'dlck_woocommerce_hide_price_and_add_to_cart_for_logged_out_users' );
	$dlck_move_labels_inside_inputs_woo_checkout_val = $dlck_setting( 'dlck_move_labels_inside_inputs_woo_checkout' );
	$dlck_woo_body_css_class_on_single_product_val = $dlck_setting( 'dlck_woo_body_css_class_on_single_product' );
	$dlck_woo_hide_custom_fields_metabox_val = $dlck_setting( 'dlck_woo_hide_custom_fields_metabox' );
	$dlck_woo_disable_reviews_tab_val = $dlck_setting( 'dlck_woo_disable_reviews_tab' );
	$dlck_disable_woocommerce_brands_feature_val = $dlck_setting( 'dlck_disable_woocommerce_brands_feature' );
	$dlck_woo_refund_request_button_val = $dlck_setting( 'dlck_woo_refund_request_button' );
	$dlck_woo_get_order_ids_by_product_val = $dlck_setting( 'dlck_woo_get_order_ids_by_product' );
	$dlck_woo_buy_now_button_val = $dlck_setting( 'dlck_woo_buy_now_button' );
	$dlck_woo_move_orders_menu_item_val = $dlck_setting( 'dlck_woo_move_orders_menu_item' );
	$dlck_woo_store_admin_view_val = $dlck_setting( 'dlck_woo_store_admin_view' );
	$dlck_woo_email_item_meta_tags_val = $dlck_setting( 'dlck_woo_email_item_meta_tags' );
	$dlck_woo_email_product_name_symbols_val = $dlck_setting( 'dlck_woo_email_product_name_symbols' );
	$dlck_woo_order_items_sort_val = $dlck_setting( 'dlck_woo_order_items_sort' );
	$dlck_woo_order_items_sort_option_val = $dlck_setting( 'dlck_woo_order_items_sort_option' );
	$dlck_woo_redirect_empty_cat_pagination_val = $dlck_setting( 'dlck_woo_redirect_empty_cat_pagination' );
	$dlck_woo_complete_order_button_val = $dlck_setting( 'dlck_woo_complete_order_button' );
	$dlck_woo_guest_checkout_existing_customers_val = $dlck_setting( 'dlck_woo_guest_checkout_existing_customers' );
	$dlck_woo_prevent_duplicate_orders_val = $dlck_setting( 'dlck_woo_prevent_duplicate_orders' );
	$dlck_woo_hide_products_no_featured_image_val = $dlck_setting( 'dlck_woo_hide_products_no_featured_image' );
	$dlck_woo_sticky_product_update_button_val = $dlck_setting( 'dlck_woo_sticky_product_update_button' );
	$dlck_woo_filter_products_by_sale_status_val = $dlck_setting( 'dlck_woo_filter_products_by_sale_status' );
	$dlck_woo_simple_products_only_val = $dlck_setting( 'dlck_woo_simple_products_only' );
	$dlck_woo_remove_payments_menu_val = $dlck_setting( 'dlck_woo_remove_payments_menu' );
	$dlck_woo_hide_downloads_tab_no_downloads_val = $dlck_setting( 'dlck_woo_hide_downloads_tab_no_downloads' );
	$dlck_woo_order_history_meta_box_val = $dlck_setting( 'dlck_woo_order_history_meta_box' );
	$dlck_woo_add_to_cart_click_counter_val = $dlck_setting( 'dlck_woo_add_to_cart_click_counter' );
	$dlck_woo_remove_add_to_cart_param_val = $dlck_setting( 'dlck_woo_remove_add_to_cart_param' );
	$dlck_woo_remove_tax_suffixes_val = $dlck_setting( 'dlck_woo_remove_tax_suffixes' );
	$dlck_woo_cancelled_order_email_customer_val = $dlck_setting( 'dlck_woo_cancelled_order_email_customer' );
	$dlck_woo_send_pending_order_email_val = $dlck_setting( 'dlck_woo_send_pending_order_email' );
	$dlck_woo_email_fatal_errors_val = $dlck_setting( 'dlck_woo_email_fatal_errors' );
	$dlck_woo_set_gtin_from_sku_val = $dlck_setting( 'dlck_woo_set_gtin_from_sku' );
	$dlck_woo_set_gtin_from_sku_gla_val = $dlck_setting( 'dlck_woo_set_gtin_from_sku_gla' );
	$dlck_woo_logout_redirect_home_val = $dlck_setting( 'dlck_woo_logout_redirect_home' );
	$dlck_woo_capitalize_product_titles_val = $dlck_setting( 'dlck_woo_capitalize_product_titles' );
	if ( ! $dlck_woo_order_items_sort_option_val ) {
		$dlck_woo_order_items_sort_option_val = 'name_az';
	}

	?>

  <div id="woo-tweaks" class="tool <?php echo $active_tab == 'woo-tweaks' ? 'tool-active' : ''; ?>">

		<div class="toolbox" style="padding:0 0 30px;">
			<div class="info" style="background:transparent;">
				<h4><?php echo esc_html_e( 'What are the Woo tweaks?', 'lc-tweaks' ); ?></h4>
				<p><?php echo esc_html_e( 'Performance and UX controls tailored for WooCommerce storefronts.', 'lc-tweaks' ); ?></p>
				<p><?php echo esc_html_e( 'Use these to streamline scripts/styles and adjust store behaviour without custom code.', 'lc-tweaks' ); ?></p>
			</div>
		</div>

		<h2 class="tool-section"><?php echo esc_html_e( 'Performance & Cleanup', 'lc-tweaks' ); ?></h2>
    <div class="tool-wrap">

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e('Resave All Products', 'lc-tweaks'); ?></h3>
					<div class="box-descr">
						<p>
							<?php echo esc_html_e( 'Force each product to resave. Updates 50 per admin page load until complete.', 'lc-tweaks' ); ?>
						</p>
					</div>
			</div>
			<div class="box-content minibox">
				<?php if ( $dlck_woo_resave_all_products_val === '1' ) : ?>
					<button type="button" class="dlck-settings-button" disabled><?php echo esc_html_e( 'Running...', 'lc-tweaks' ); ?></button>
				<?php else : ?>
					<a class="dlck-settings-button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dlck_woo_resave_all_products' ), 'dlck_woo_resave_all_products' ) ); ?>">
						<?php echo esc_html_e( 'Run Resave', 'lc-tweaks' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Clean and Optimize the WooCommerce Sessions Table', 'lc-tweaks' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Shorten WooCommerce session expiration to keep the sessions table lean.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
				<div class="box-content">
					<select name="dlck_woo_session_expiration">
						<option value="" <?php selected( $dlck_woo_session_expiration_val, '' ); ?>><?php echo esc_html_e( 'WooCommerce default', 'lc-tweaks' ); ?></option>
						<option value="86400" <?php selected( $dlck_woo_session_expiration_val, '86400' ); ?>><?php echo esc_html_e( '24 hours', 'lc-tweaks' ); ?></option>
						<option value="172800" <?php selected( $dlck_woo_session_expiration_val, '172800' ); ?>><?php echo esc_html_e( '48 hours', 'lc-tweaks' ); ?></option>
						<option value="604800" <?php selected( $dlck_woo_session_expiration_val, '604800' ); ?>><?php echo esc_html_e( '1 week', 'lc-tweaks' ); ?></option>
						<option value="1209600" <?php selected( $dlck_woo_session_expiration_val, '1209600' ); ?>><?php echo esc_html_e( '2 weeks', 'lc-tweaks' ); ?></option>
						<option value="1814400" <?php selected( $dlck_woo_session_expiration_val, '1814400' ); ?>><?php echo esc_html_e( '3 weeks', 'lc-tweaks' ); ?></option>
						<option value="2592000" <?php selected( $dlck_woo_session_expiration_val, '2592000' ); ?>><?php echo esc_html_e( '1 month', 'lc-tweaks' ); ?></option>
					</select>
					<div class="info">
						<h4><?php echo esc_html_e( 'Session Cleanup Health', 'lc-tweaks' ); ?></h4>
						<?php if ( $dlck_woo_session_cleanup_enabled ) : ?>
							<p>
								<?php
								printf(
									/* translators: %s: recurrence label */
									esc_html__( 'Scheduler enabled (%s).', 'lc-tweaks' ),
									esc_html( $dlck_woo_session_cleanup_recurrence_label )
								);
								?>
							</p>
							<?php if ( $dlck_woo_session_cleanup_next_run > 0 ) : ?>
								<p>
									<?php
									printf(
										/* translators: %s: next scheduled run datetime */
										esc_html__( 'Next cleanup run: %s', 'lc-tweaks' ),
										esc_html( wp_date( 'Y-m-d H:i:s', $dlck_woo_session_cleanup_next_run ) )
									);
									?>
								</p>
							<?php else : ?>
								<p><?php echo esc_html_e( 'Next cleanup run: waiting for scheduler registration.', 'lc-tweaks' ); ?></p>
							<?php endif; ?>
						<?php else : ?>
							<p><?php echo esc_html_e( 'Scheduler disabled. Choose a custom session expiration and save settings to enable automatic cleanup.', 'lc-tweaks' ); ?></p>
						<?php endif; ?>

						<?php if ( $dlck_woo_session_cleanup_last_run > 0 ) : ?>
							<p>
								<?php
								printf(
									/* translators: 1: last run datetime, 2: deleted row count */
									esc_html__( 'Last run: %1$s (deleted %2$d expired sessions)', 'lc-tweaks' ),
									esc_html( wp_date( 'Y-m-d H:i:s', $dlck_woo_session_cleanup_last_run ) ),
									(int) $dlck_woo_session_cleanup_deleted_count
								);
								?>
							</p>
						<?php else : ?>
							<p><?php echo esc_html_e( 'Last run: not available yet.', 'lc-tweaks' ); ?></p>
						<?php endif; ?>

						<?php if ( $dlck_woo_session_cleanup_row_count !== null ) : ?>
							<p>
								<?php
								printf(
									/* translators: %d: total table rows */
									esc_html__( 'Current session rows: %d', 'lc-tweaks' ),
									(int) $dlck_woo_session_cleanup_row_count
								);
								?>
							</p>
						<?php endif; ?>

						<?php if ( $dlck_woo_session_cleanup_expired_count !== null ) : ?>
							<p>
								<?php
								printf(
									/* translators: %d: expired row count */
									esc_html__( 'Expired sessions still present: %d', 'lc-tweaks' ),
									(int) $dlck_woo_session_cleanup_expired_count
								);
								?>
							</p>
						<?php endif; ?>

						<?php if ( $dlck_woo_session_cleanup_message !== '' ) : ?>
							<p><?php echo esc_html( $dlck_woo_session_cleanup_message ); ?></p>
						<?php endif; ?>

						<?php if ( $dlck_woo_session_cleanup_status === 'error' ) : ?>
							<p><strong><?php echo esc_html_e( 'Last cleanup reported an error. Review WooCommerce status/tools and database access.', 'lc-tweaks' ); ?></strong></p>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="lc-kit trigger">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Disable Persistent Carts', 'lc-tweaks' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Stop WooCommerce from saving carts persistently for logged-in customers.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_disable_persistent_cart" type="checkbox" value="1" <?php checked( '1', $dlck_woo_disable_persistent_cart_val ); ?> />
				</div>
			</div>
			<a class="dlck-cust-link" href="<?php echo esc_attr( admin_url( 'admin.php?page=wc-status&tab=tools' ) ); ?>" target="_blank"><?php include DLCK_LC_KIT_PLUGIN_DIR . '/assets/img/gear-icon.php'; ?></a>
		</div>
				<div class="dlck-hide">
					<div class="lc-kit first nopad">
						<div class="box-title">
						</div>
					<div class="box-content">
						<div class="info">
							<h4><?php echo esc_html_e('IMPORTANT NOTE:', 'lc-tweaks'); ?></h4>
							<p>This only prevents future carts from being saved persistently. Manually Clear Existing Persistent Carts: To clear these Navigate to <strong>WooCommerce > Status > Tools</strong> in your WordPress admin panel and use the <strong>Clear customer sessions</strong> option. Be aware that this will clear all active and saved carts, including those currently in progress.</p>
						</div>
					</div>
					</div>
				</div>

				<div class="lc-kit">
					<div class="box-title">
						<h3><?php echo esc_html_e( 'Woo Cart Script Policy', 'lc-tweaks' ); ?></h3>
						<div class="box-descr">
							<p><?php echo esc_html_e( 'Control how WooCommerce cart fragments and add-to-cart scripts load for performance tuning.', 'lc-tweaks' ); ?></p>
						</div>
					</div>
					<div class="box-content">
						<select name="dlck_woo_cart_script_policy">
							<option value="default" <?php selected( $dlck_woo_cart_script_policy_val, 'default' ); ?>><?php echo esc_html_e( 'WooCommerce default', 'lc-tweaks' ); ?></option>
							<option value="disable_non_woo" <?php selected( $dlck_woo_cart_script_policy_val, 'disable_non_woo' ); ?>><?php echo esc_html_e( 'Disable cart fragments on non-Woo pages', 'lc-tweaks' ); ?></option>
							<option value="disable_everywhere" <?php selected( $dlck_woo_cart_script_policy_val, 'disable_everywhere' ); ?>><?php echo esc_html_e( 'Disable cart fragments everywhere', 'lc-tweaks' ); ?></option>
							<option value="disable_non_woo_plus_add_to_cart" <?php selected( $dlck_woo_cart_script_policy_val, 'disable_non_woo_plus_add_to_cart' ); ?>><?php echo esc_html_e( 'Disable cart fragments + add-to-cart on non-Woo pages', 'lc-tweaks' ); ?></option>
						</select>
						<p class="info"><?php echo esc_html_e( 'If mini-cart or AJAX add-to-cart behavior breaks, switch back to WooCommerce default.', 'lc-tweaks' ); ?></p>
					</div>
				</div>

				<div class="lc-kit">
        <div class="box-title">
          <h3><?php echo esc_html_e('Disable WooCommerce Admin', 'lc-tweaks'); ?></h3>
          <div class="box-descr">
            <p>
              <?php echo esc_html_e("Disables the new WooCommerce Admin package in WooCommerce.", "lc-tweaks"); ?>
            </p>
          </div>
        </div>
        <div class="box-content minibox">
          <div class="checkbox">
            <input name="dlck_disable_woocommerce_admin" type="checkbox" value="1" <?php checked( '1', $dlck_disable_woocommerce_admin_val ); ?> />
          </div>
        </div>
      </div>

			<div class="lc-kit">
        <div class="box-title">
          <h3><?php echo esc_html_e('Stop Woocommerce Files from Loading Safely', 'lc-tweaks'); ?></h3>
          <div class="box-descr">
            <p>
              <?php echo esc_html_e("Safely Remove Woocommerce script and css files from unnecessary pages.", "lc-tweaks"); ?>
            </p>
          </div>
        </div>
        <div class="box-content minibox">
          <div class="checkbox">
            <input name="dlck_remove_woo_files" type="checkbox" value="1" <?php checked( '1', $dlck_remove_woo_files_val ); ?> />
          </div>
        </div>
      </div>

	  <div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e('Stop All Woocommerce Files from Loading', 'lc-tweaks'); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("Remove all Woocommerce script and css files from unnecessary pages.", "lc-tweaks"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_remove_woo_all_files" type="checkbox" value="1"
						<?php checked( '1', $dlck_remove_woo_all_files_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<h4><?php echo esc_html_e('NOTE:', 'lc-tweaks'); ?></h4>
						<p>
							<?php echo esc_html_e("Enabling this option will remove the default styling and scripts from all non Woocommerce. ie featured section on home page", 'lc-tweaks'); ?>
							</u>:</p>
						<p>
							<strong>USE WITH CAUTION</strong> - This dequeues scripts (js) and removes styleing (css).</p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e('Stop WooCommerce Gutenberg Blocks from loading', 'lc-tweaks'); ?></h3>
				<div class="box-descr">
					<p>
              <?php echo esc_html_e("Remove Woocommerce Blocks script and css files from unnecessary pages.", "lc-tweaks"); ?>
            </p>
          </div>
        </div>
        <div class="box-content minibox">
          <div class="checkbox">
            <input name="dlck_remove_woo_block_files" type="checkbox" value="1" <?php checked( '1', $dlck_remove_woo_block_files_val ); ?> />
          </div>
        </div>
      </div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'WP Rocket: Exclude Side Cart (Xootix)', 'lc-tweaks' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Prevent WP Rocket LazyRender from optimizing the Side Cart for WooCommerce markup to keep the drawer working.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_wp_rocket_side_cart_exclusion" type="checkbox" value="1" <?php checked( '1', $dlck_wp_rocket_side_cart_exclusion_val ); ?> />
					</div>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Disable Reviews', 'lc-tweaks' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Remove the Reviews tab from single product pages.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_woo_disable_reviews_tab" type="checkbox" value="1" <?php checked( '1', $dlck_woo_disable_reviews_tab_val ); ?> />
					</div>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><span class="new">new</span><?php echo esc_html_e( 'Disable Brands Feature', 'lc-tweaks' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Turn off the WooCommerce Brands feature flag and remove its styles.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_disable_woocommerce_brands_feature" type="checkbox" value="1" <?php checked( '1', $dlck_disable_woocommerce_brands_feature_val ); ?> />
					</div>
				</div>
			</div>

    </div>

		<h2 class="tool-section"><?php echo esc_html_e( 'Admin Columns & Search', 'lc-tweaks' ); ?></h2>
		<div class="tool-wrap">

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Orders: Search by SKU', 'lc-tweaks' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Allow the Orders screen search to match products by SKU.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_wc_orders_admin_search_by_sku" type="checkbox" value="1" <?php checked( '1', $dlck_wc_orders_admin_search_by_sku_val ); ?> />
					</div>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Orders: User Role Column', 'lc-tweaks' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Show and sort by the customer role on the Orders list.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_wc_orders_admin_user_role_column" type="checkbox" value="1" <?php checked( '1', $dlck_wc_orders_admin_user_role_column_val ); ?> />
					</div>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Users: Order Counts Column', 'lc-tweaks' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Add order counts per status to the Users table.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_wp_admin_users_order_counts_column" type="checkbox" value="1" <?php checked( '1', $dlck_wp_admin_users_order_counts_column_val ); ?> />
					</div>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Products: Stock Status Column', 'lc-tweaks' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Show a quick stock status column in the Products list.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_wc_products_admin_stock_status_column" type="checkbox" value="1" <?php checked( '1', $dlck_wc_products_admin_stock_status_column_val ); ?> />
					</div>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Products: Last Edited Column & Meta', 'lc-tweaks' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Track and display who last edited products (and when).', 'lc-tweaks' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_wc_products_last_edited_meta_and_columns" type="checkbox" value="1" <?php checked( '1', $dlck_wc_products_last_edited_meta_and_columns_val ); ?> />
					</div>
				</div>
			</div>

		</div>


    <h2 class="tool-section"><?php echo esc_html_e( 'Catalog Layout & Display', 'lc-tweaks' ); ?></h2>
    <div class="tool-wrap">

			<div class="lc-kit">
        <div class="box-title">
          <h3><?php echo esc_html_e('Shop Single Column On Mobile', 'lc-tweaks'); ?></h3>
          <div class="box-descr">
            <p>
              <?php echo esc_html_e("Display WooCommerce Products in Single Column on Mobile Devices.", "lc-tweaks"); ?>
            </p>
          </div>
        </div>
        <div class="box-content minibox">
          <div class="checkbox">
            <input name="dlck_shop_single_column_on_mobile" type="checkbox" value="1" <?php checked( '1', $dlck_shop_single_column_on_mobile_val ); ?> />
          </div>
        </div>
      </div>

			<div class="lc-kit">
        <div class="box-title">
          <h3><?php echo esc_html_e('Display Add to Cart Button on Archives', 'lc-tweaks'); ?></h3>
          <div class="box-descr">
            <p>
              <?php echo esc_html_e("Add An “Add To Cart” Button To A WooCommerce Shop Module.", "lc-tweaks"); ?>
            </p>
          </div>
        </div>
        <div class="box-content minibox">
          <div class="checkbox">
            <input name="dlck_woo_add_to_cart_button" type="checkbox" value="1" <?php checked( '1', $dlck_woo_add_to_cart_button_val ); ?> />
          </div>
        </div>
      </div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e('Shop Masonry Layout', 'lc-tweaks'); ?></h3>
					<div class="box-descr">
						<p>
							<?php echo esc_html_e("Display products of WooCommerce with a masonry look.", "lc-tweaks"); ?>
						</p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_shop_masonry_layout" type="checkbox" value="1" <?php checked( '1', $dlck_shop_masonry_layout_val ); ?> />
					</div>
        </div>
      </div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e('Hide Price & Add to Cart for Logged Out Users', 'lc-tweaks'); ?></h3>
					<div class="box-descr">
						<p>
              <?php echo esc_html_e("You may want to force users to login in order to see prices and add products to cart.", "lc-tweaks"); ?>
            </p>
          </div>
        </div>
        <div class="box-content minibox">
          <div class="checkbox">
            <input name="dlck_woocommerce_hide_price_and_add_to_cart_for_logged_out_users" type="checkbox" value="1" <?php checked( '1', $dlck_woocommerce_hide_price_and_add_to_cart_for_logged_out_users_val ); ?> />
          </div>
        </div>
			</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Add a Line Break in WooCommerce Product Titles', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Replace "|" in product titles with a line break on the front end.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_add_a_line_break_in_woocommerce_product_titles" type="checkbox" value="1" <?php checked( '1', $dlck_add_a_line_break_in_woocommerce_product_titles_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Capitalize Product Titles', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Automatically format product titles in title case on the front end.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_capitalize_product_titles" type="checkbox" value="1" <?php checked( '1', $dlck_woo_capitalize_product_titles_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Only affects product titles on the front end; admin titles remain unchanged.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Change Out of Stock Button to "Read More"', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
						<p><?php echo esc_html_e( 'Replace the add-to-cart button with "Read More" when products are out of stock.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_read_more_to_out_of_stock" type="checkbox" value="1" <?php checked( '1', $dlck_read_more_to_out_of_stock_val ); ?> />
					</div>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e('Product Category > Body CSS Class on Single Product', 'lc-tweaks'); ?></h3>
					<div class="box-descr">
						<p>
						<?php echo esc_html_e("Apply CSS on the single product page based on the product category.", "lc-tweaks"); ?>
					</p>
					</div>
				</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_body_css_class_on_single_product" type="checkbox" value="1" <?php checked( '1', $dlck_woo_body_css_class_on_single_product_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e('Hide legacy Custom Fields metabox on products', 'lc-tweaks'); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e('Removes the old Custom Fields meta box from WooCommerce products to declutter the edit screen.', 'lc-tweaks'); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_hide_custom_fields_metabox" type="checkbox" value="1" <?php checked( '1', $dlck_woo_hide_custom_fields_metabox_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Hide Products Without Featured Image', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Exclude products missing a featured image from shop, category, and archive listings.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_hide_products_no_featured_image" type="checkbox" value="1" <?php checked( '1', $dlck_woo_hide_products_no_featured_image_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Keeps listings looking polished by hiding products without thumbnails across shop and archive views.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Redirect Empty Category Pagination', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Redirect 404s on paginated WooCommerce category pages back to the category archive.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_redirect_empty_cat_pagination" type="checkbox" value="1" <?php checked( '1', $dlck_woo_redirect_empty_cat_pagination_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'When a paginated category page is empty and returns a 404, redirect visitors back to the main category archive to avoid confusion.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Checkout UX', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Restrict Store to Logged-in Users', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Redirect guests away from shop/product/cart/checkout/account pages.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_restrict_store_to_logged_in" type="checkbox" value="1" <?php checked( '1', $dlck_woo_restrict_store_to_logged_in_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Disable Checkout Field Autocomplete', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Turn off browser autocomplete on WooCommerce checkout fields.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_checkout_field_autocomplete" type="checkbox" value="1" <?php checked( '1', $dlck_disable_checkout_field_autocomplete_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Empty Checkout Defaults', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Force WooCommerce checkout fields to start blank (no default or cached values).', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_checkout_empty_defaults" type="checkbox" value="1" <?php checked( '1', $dlck_woo_checkout_empty_defaults_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Change City Label to Suburb', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Rename the visible WooCommerce city field label and placeholder to "Suburb".', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_city_label_suburb" type="checkbox" value="1" <?php checked( '1', $dlck_woo_city_label_suburb_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'This only changes the visible label and placeholder on WooCommerce checkout and address forms. The underlying field key remains "city".', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e('Move Labels Inside Inputs on WooCommerce Checkout', 'lc-tweaks'); ?></h3>
				<div class="box-descr">
					<p>
					<?php echo esc_html_e("Move labels inside checkout fields.", "lc-tweaks"); ?>
				</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_move_labels_inside_inputs_woo_checkout" type="checkbox" value="1" <?php checked( '1', $dlck_move_labels_inside_inputs_woo_checkout_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Hide Woo Menu Item for Non-Administrators', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Remove the WooCommerce menu item for all users except administrators.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
		<div class="box-content minibox">
			<div class="checkbox">
				<input name="dlck_stop_woo_menu_item_from_displaying_for_anyone_but_administrator" type="checkbox" value="1" <?php checked( '1', $dlck_stop_woo_menu_item_from_displaying_for_anyone_but_administrator_val ); ?> />
			</div>
		</div>
	</div>

	<div class="lc-kit trigger">
		<div class="box-title">
			<h3><?php echo esc_html_e( 'Allow Guest Checkout for Existing Customers', 'lc-tweaks' ); ?></h3>
			<div class="box-descr">
				<p><?php echo esc_html_e( 'Let returning customers place orders without logging in by matching their billing email.', 'lc-tweaks' ); ?></p>
			</div>
		</div>
		<div class="box-content minibox">
			<div class="checkbox">
				<input name="dlck_woo_guest_checkout_existing_customers" type="checkbox" value="1" <?php checked( '1', $dlck_woo_guest_checkout_existing_customers_val ); ?> />
			</div>
		</div>
	</div>
	<div class="dlck-hide">
		<div class="lc-kit first nopad">
			<div class="box-title">
			</div>
			<div class="box-content">
				<div class="info">
					<p><?php echo esc_html_e( 'If guest checkout is disabled, this lets returning customers use their billing email to complete checkout without logging in. WooCommerce will attach the order to their account.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
		</div>
	</div>

	<div class="lc-kit trigger">
		<div class="box-title">
			<h3><?php echo esc_html_e( 'Remove add-to-cart URL Parameter', 'lc-tweaks' ); ?></h3>
			<div class="box-descr">
				<p><?php echo esc_html_e( 'Strip add-to-cart=ID after a successful add to cart to prevent duplicate adds on refresh.', 'lc-tweaks' ); ?></p>
			</div>
		</div>
		<div class="box-content minibox">
			<div class="checkbox">
				<input name="dlck_woo_remove_add_to_cart_param" type="checkbox" value="1" <?php checked( '1', $dlck_woo_remove_add_to_cart_param_val ); ?> />
			</div>
		</div>
	</div>
	<div class="dlck-hide">
		<div class="lc-kit first nopad">
			<div class="box-title">
			</div>
			<div class="box-content">
				<div class="info">
					<p><?php echo esc_html_e( 'Useful if redirect-to-cart is disabled and you want to avoid re-adding items when the page is reloaded.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
		</div>
	</div>

	<div class="lc-kit trigger">
		<div class="box-title">
			<h3><?php echo esc_html_e( 'Remove Tax Suffix Labels', 'lc-tweaks' ); ?></h3>
			<div class="box-descr">
				<p><?php echo esc_html_e( 'Hide the “incl. tax” and “ex. tax” labels on cart, checkout, and totals.', 'lc-tweaks' ); ?></p>
			</div>
		</div>
		<div class="box-content minibox">
			<div class="checkbox">
				<input name="dlck_woo_remove_tax_suffixes" type="checkbox" value="1" <?php checked( '1', $dlck_woo_remove_tax_suffixes_val ); ?> />
			</div>
		</div>
	</div>
	<div class="dlck-hide">
		<div class="lc-kit first nopad">
			<div class="box-title">
			</div>
			<div class="box-content">
				<div class="info">
					<p><?php echo esc_html_e( 'Best for stores entering prices inclusive of tax to keep totals cleaner.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
		</div>
	</div>

	<div class="lc-kit trigger">
		<div class="box-title">
			<h3><?php echo esc_html_e( 'Prevent Duplicate Orders', 'lc-tweaks' ); ?></h3>
			<div class="box-descr">
				<p><?php echo esc_html_e( 'Block duplicate paid orders placed within a short time window.', 'lc-tweaks' ); ?></p>
			</div>
		</div>
		<div class="box-content minibox">
			<div class="checkbox">
				<input name="dlck_woo_prevent_duplicate_orders" type="checkbox" value="1" <?php checked( '1', $dlck_woo_prevent_duplicate_orders_val ); ?> />
			</div>
		</div>
	</div>
	<div class="dlck-hide">
		<div class="lc-kit first nopad">
			<div class="box-title">
			</div>
			<div class="box-content">
				<div class="info">
					<p><?php echo esc_html_e( 'Helps avoid double-charges by stopping identical paid orders created in the last couple of minutes.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
		</div>
	</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Single Product', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Buy Now Button', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Add a Buy Now button that sends customers directly to checkout for the selected quantity/variation.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_buy_now_button" type="checkbox" value="1" <?php checked( '1', $dlck_woo_buy_now_button_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Adds a single-click path to checkout from the product page, skipping the cart for simple products and single variations.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Add to Cart Click Counter', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Track add-to-cart clicks and show conversion stats in the product admin.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_add_to_cart_click_counter" type="checkbox" value="1" <?php checked( '1', $dlck_woo_add_to_cart_click_counter_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Adds an AJAX counter on add-to-cart clicks and a Product admin meta box with conversion stats.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'My Account', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'My Account: Refund Request Button', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Add a refund request action on My Account orders (within 60 days, with pending state).', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_refund_request_button" type="checkbox" value="1" <?php checked( '1', $dlck_woo_refund_request_button_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Adds a refund request button in My Account that logs a customer note, stores the request meta, and emails the admin.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Hide Downloads Tab Without Downloads', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Hide the Downloads menu item when a customer has no downloadable products.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_hide_downloads_tab_no_downloads" type="checkbox" value="1" <?php checked( '1', $dlck_woo_hide_downloads_tab_no_downloads_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Keeps the My Account menu tidy by only showing Downloads when there are files available.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Custom Logout Redirect', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Send customers to the homepage after logging out.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_logout_redirect_home" type="checkbox" value="1" <?php checked( '1', $dlck_woo_logout_redirect_home_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Use this if you want logouts to land on the site homepage instead of My Account.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Orders & Admin', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Get Order IDs by Product', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Register a helper function to fetch paid order IDs containing a product.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_get_order_ids_by_product" type="checkbox" value="1" <?php checked( '1', $dlck_woo_get_order_ids_by_product_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Provides a HPOS-safe helper to fetch paid orders by product ID without direct database queries.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Move Orders Menu Item', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Move WooCommerce Orders to its own top-level menu.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_move_orders_menu_item" type="checkbox" value="1" <?php checked( '1', $dlck_woo_move_orders_menu_item_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Moves Orders to its own top-level menu so frequent order checks are one click away.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Complete Order Button', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Add a one-click Complete Order button on the order edit screen.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_complete_order_button" type="checkbox" value="1" <?php checked( '1', $dlck_woo_complete_order_button_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Adds a Complete Order action next to Update for faster manual fulfillment.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Order History Meta Box', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Show a customer order history panel on the order edit screen.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_order_history_meta_box" type="checkbox" value="1" <?php checked( '1', $dlck_woo_order_history_meta_box_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Lists a customer’s last 10 orders right inside the order editor for quick reference.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Sticky Product Update Button', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Keep the Update button visible while editing long product pages.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_sticky_product_update_button" type="checkbox" value="1" <?php checked( '1', $dlck_woo_sticky_product_update_button_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Makes the Publish/Update button stick to the viewport so you do not have to scroll back up to save.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Filter Products by Sale Status', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Add an admin filter to show products that are on sale or not on sale.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_filter_products_by_sale_status" type="checkbox" value="1" <?php checked( '1', $dlck_woo_filter_products_by_sale_status_val ); ?> />
				</div>
			</div>
			<a class="dlck-cust-link" href="<?php echo esc_attr( admin_url( 'edit.php?post_type=product' ) ); ?>" target="_blank"><?php include DLCK_LC_KIT_PLUGIN_DIR . '/assets/img/gear-icon.php'; ?></a>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Adds a dropdown to the Products list so you can quickly filter sale vs. regular-priced items.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Allow Only Simple Products', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Remove variable, grouped, and external product types from the editor.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_simple_products_only" type="checkbox" value="1" <?php checked( '1', $dlck_woo_simple_products_only_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Keeps product management focused by restricting the product type selector to Simple only.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Remove Payments Menu', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Remove the Payments menu entries from the admin sidebar.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_remove_payments_menu" type="checkbox" value="1" <?php checked( '1', $dlck_woo_remove_payments_menu_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Hides WooCommerce Payments-related admin menu items to keep the sidebar clean.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><span class="new divi4">top</span><?php echo esc_html_e( 'Store Admin View', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Add a toolbar toggle to switch into a WooCommerce-only admin menu view.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_store_admin_view" type="checkbox" value="1" <?php checked( '1', $dlck_woo_store_admin_view_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Cuts the admin menu down to WooCommerce-only items to reduce distractions when many plugins add menu entries.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Sort Order Items', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Sort order items across order views and emails using the selected rule.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_order_items_sort" type="checkbox" value="1" <?php checked( '1', $dlck_woo_order_items_sort_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Reorders items consistently (name, SKU, total, quantity) instead of cart order, useful for admin reviews, invoices, and packing slips.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
			<div class="lc-kit first nopad">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Sort Mode', 'lc-tweaks' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Choose how to sort the order items.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
				<div class="box-content">
					<select name="dlck_woo_order_items_sort_option">
						<option value="name_az" <?php selected( $dlck_woo_order_items_sort_option_val, 'name_az' ); ?>><?php echo esc_html_e( 'Product Name (A–Z)', 'lc-tweaks' ); ?></option>
						<option value="quantity_desc" <?php selected( $dlck_woo_order_items_sort_option_val, 'quantity_desc' ); ?>><?php echo esc_html_e( 'Quantity (High → Low)', 'lc-tweaks' ); ?></option>
						<option value="total_desc" <?php selected( $dlck_woo_order_items_sort_option_val, 'total_desc' ); ?>><?php echo esc_html_e( 'Line Total (High → Low)', 'lc-tweaks' ); ?></option>
						<option value="sku_az" <?php selected( $dlck_woo_order_items_sort_option_val, 'sku_az' ); ?>><?php echo esc_html_e( 'SKU (A–Z)', 'lc-tweaks' ); ?></option>
						<option value="category_az" <?php selected( $dlck_woo_order_items_sort_option_val, 'category_az' ); ?>><?php echo esc_html_e( 'Category (A–Z)', 'lc-tweaks' ); ?></option>
						<option value="product_id_asc" <?php selected( $dlck_woo_order_items_sort_option_val, 'product_id_asc' ); ?>><?php echo esc_html_e( 'Product ID (Low → High)', 'lc-tweaks' ); ?></option>
					</select>
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Emails', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Email Item Meta Tags', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Rewrite item meta markup and add tag styling for WooCommerce order emails.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_email_item_meta_tags" type="checkbox" value="1" <?php checked( '1', $dlck_woo_email_item_meta_tags_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Applies a cleaner, modern tag layout in order emails so item meta is easier to scan and less cluttered.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Fix Email Product Name Symbols', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Render trademark, registered, and copyright symbols more reliably in WooCommerce order emails.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_email_product_name_symbols" type="checkbox" value="1" <?php checked( '1', $dlck_woo_email_product_name_symbols_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Applies only to WooCommerce order email item names. Product titles on the website, cart and checkout, and admin product titles stay unchanged.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Cancelled Order Email to Customer', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Send the cancelled order notification to the customer as well.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_cancelled_order_email_customer" type="checkbox" value="1" <?php checked( '1', $dlck_woo_cancelled_order_email_customer_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Adds the billing email address to cancelled order recipients so customers are notified too.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Auto-send Pending Order Email', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Automatically send the customer invoice email for pending orders.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_send_pending_order_email" type="checkbox" value="1" <?php checked( '1', $dlck_woo_send_pending_order_email_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Triggers the customer invoice email when a manual order is created with pending status.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Email Admin on Woo Fatal Errors', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Email the site admin when WooCommerce logs a fatal error.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_email_fatal_errors" type="checkbox" value="1" <?php checked( '1', $dlck_woo_email_fatal_errors_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Sends an email when WooCommerce logs a fatal error so you can respond faster.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Notifications & Access', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Notify Admin When a New Customer Account Is Created', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Send an admin email when a customer registers through WooCommerce.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_notify_admin_when_a_new_customer_account_is_created" type="checkbox" value="1" <?php checked( '1', $dlck_notify_admin_when_a_new_customer_account_is_created_val ); ?> />
				</div>
				<?php if ( $dlck_disable_admin_new_user_notification_emails_val === '1' ) : ?>
					<p class="info"><?php echo esc_html_e( 'The global "Disable Admin New User Notification Emails" setting is enabled, so this WooCommerce-specific admin email is overridden and will be switched off on save.', 'lc-tweaks' ); ?></p>
				<?php endif; ?>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Product Identifiers', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Set Product GTIN from SKU (Search/Schema)', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Populate GTIN from SKU for structured data and the product GTIN field (when empty).', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woo_set_gtin_from_sku" type="checkbox" value="1" <?php checked( '1', $dlck_woo_set_gtin_from_sku_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Uses the SKU as GTIN for schema and fills the GTIN field on save when it is empty. Only applies when the SKU normalizes to a valid GTIN length.', 'lc-tweaks' ); ?></p>
						<p>
							<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dlck_gtin_cleanup_invalid' ), 'dlck_gtin_cleanup_invalid' ) ); ?>">
								<?php echo esc_html_e( 'Clean Invalid GTINs', 'lc-tweaks' ); ?>
							</a>
						</p>
					</div>
				</div>
			</div>
		</div>

		<?php if ( dlck_is_gla_active() ) : ?>
			<div class="lc-kit trigger">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Set Google Sync Identifiers from SKU', 'lc-tweaks' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Send SKU-based identifiers to Google Listings & Ads when GTIN is missing.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<label>
							<input name="dlck_woo_set_gtin_from_sku_gla" type="checkbox" value="1" <?php checked( '1', $dlck_woo_set_gtin_from_sku_gla_val ); ?> />
							<?php echo esc_html_e( 'Sync identifiers', 'lc-tweaks' ); ?>
						</label>
					</div>
					<div class="checkbox">
						<label>
							<input name="dlck_woo_gla_mc_sync_column" type="checkbox" value="1" <?php checked( '1', $dlck_woo_gla_mc_sync_column_val ); ?> />
							<?php echo esc_html_e( 'Enable Products: Google Sync Column', 'lc-tweaks' ); ?>
						</label>
					</div>
				</div>
			</div>
			<div class="dlck-hide">
				<div class="lc-kit first nopad">
					<div class="box-title">
					</div>
					<div class="box-content">
						<div class="info">
							<p><?php echo esc_html_e( 'Uses numeric SKUs as GTIN, sends SKU as MPN when GTIN is missing, and marks identifiers as missing when both GTIN and brand are empty.', 'lc-tweaks' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>

	</div>

	<?php
	if ( dlck_edition_allows_option( 'dlck_yith_activator' ) ) {
		include DLCK_LC_KIT_PLUGIN_DIR . '/tools/woo-yith-licence-activator.php';
	}
	?>


</div>
