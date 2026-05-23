<?php
include DLCK_LC_KIT_PLUGIN_DIR . '/tools/maintenance-data.php';

?>
<div id="maintenance" class="tool <?php echo $active_tab === 'maintenance' ? 'tool-active' : ''; ?>">

	<div class="toolbox" style="padding:0 0 30px;">
		<div class="info" style="background:transparent;">
			<h4><?php echo esc_html_e( 'What is the maintenance area?', 'lc-tweaks' ); ?></h4>
			<p><?php echo esc_html_e( 'Recovery and safety toggles to fix cron, caching, and integration issues.', 'lc-tweaks' ); ?></p>
			<p><?php echo esc_html_e( 'Use sparingly—these change core behaviours to keep a site stable during troubleshooting.', 'lc-tweaks' ); ?></p>
		</div>
	</div>

		<h2 class="tool-section"><?php echo esc_html_e( 'Updates', 'lc-tweaks' ); ?></h2>
		<div class="tool-wrap">

			<div class="lc-kit trigger">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Force Plugin & Theme Update Check', 'lc-tweaks' ); ?></h3>
					<div class="box-descr">
						<p>
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: plugins/themes scope label */
									__( 'Run a fresh WordPress.org %s update check immediately instead of waiting for the normal cache window.', 'lc-tweaks' ),
									$dlck_force_update_scope_label
								)
							);
							?>
						</p>
					</div>
				</div>
				<div class="box-content minibox">
					<?php if ( $dlck_can_force_update_check ) : ?>
						<a class="dlck-settings-button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dlck_force_update_check' ), 'dlck_force_update_check' ) ); ?>">
							<?php echo esc_html_e( 'Run Check Now', 'lc-tweaks' ); ?>
						</a>
					<?php else : ?>
						<button type="button" class="dlck-settings-button" disabled><?php echo esc_html_e( 'Not Available', 'lc-tweaks' ); ?></button>
					<?php endif; ?>
				</div>
			</div>
			<div class="dlck-hide">
				<div class="lc-kit first nopad">
					<div class="box-title">
					</div>
					<div class="box-content">
						<div class="info">
							<p>
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s: plugins/themes scope label */
										__( 'Useful when you have just released or installed an update and want WordPress to refresh %s availability immediately.', 'lc-tweaks' ),
										$dlck_force_update_scope_label
									)
								);
								?>
							</p>
							<p><?php echo esc_html_e( 'This refreshes core plugin/theme checks only. Some commercial or custom updaters may keep their own caches.', 'lc-tweaks' ); ?></p>
							<?php if ( ! $dlck_can_force_update_check ) : ?>
								<p><?php echo esc_html_e( 'This action is only available to users who can update plugins and/or themes on this site.', 'lc-tweaks' ); ?></p>
							<?php endif; ?>
							<p><a class="button" href="<?php echo esc_url( $dlck_updates_page_url ); ?>"><?php echo esc_html_e( 'Open Updates Page', 'lc-tweaks' ); ?></a></p>
						</div>
					</div>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><span class="new">top</span><?php echo esc_html_e('Skip New Bundled Themes on Core Updates','lc-tweaks'); ?></h3>
					<div class="box-descr">
					<p><?php echo esc_html_e( 'Define CORE_UPGRADE_SKIP_NEW_BUNDLED to avoid installing new default themes.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_core_upgrade_skip_new_bundled" type="checkbox" value="1" <?php checked( '1', $dlck_core_upgrade_skip_new_bundled_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><span class="new">top</span><?php echo esc_html_e('Disable Core Auto Updates','lc-tweaks'); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Define WP_AUTO_UPDATE_CORE to false to stop WordPress core auto-updates.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_wp_auto_update_core" type="checkbox" value="1" <?php checked( '1', $dlck_wp_auto_update_core_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><span class="new">top</span><?php echo esc_html_e('Disable Plugin Auto Updates','lc-tweaks'); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Prevent WordPress from automatically updating plugins.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_plugin_auto_updates" type="checkbox" value="1" <?php checked( '1', $dlck_disable_plugin_auto_updates_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><span class="new">top</span><?php echo esc_html_e('Disable Theme Auto Updates','lc-tweaks'); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Prevent WordPress from automatically updating themes.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_theme_auto_updates" type="checkbox" value="1" <?php checked( '1', $dlck_disable_theme_auto_updates_val ); ?> />
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Admin', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">

		<?php if ( $dlck_divi_theme_active ) : ?>
			<div class="lc-kit trigger">
				<div class="box-title">
					<h3><span class="new">new</span><?php echo esc_html_e( 'Builder-safe mode', 'lc-tweaks' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Temporarily disable most LC Tweaks runtime hooks while Divi Visual Builder/editor requests are active.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_builder_safe_mode" type="checkbox" value="1" <?php checked( '1', $dlck_builder_safe_mode_val ); ?> />
					</div>
				</div>
			</div>
			<div class="dlck-hide">
				<div class="lc-kit first nopad">
					<div class="box-title">
					</div>
					<div class="box-content">
						<div class="info">
							<p><?php echo esc_html_e( 'Use this when the Divi builder becomes unstable after enabling multiple tweaks.', 'lc-tweaks' ); ?></p>
							<p><?php echo esc_html_e( 'Normal frontend behavior remains unchanged; this only gates builder/editor request contexts.', 'lc-tweaks' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>

			<div class="lc-kit">
				<div class="box-title">
					<h3><span class="new">top</span><?php echo esc_html_e('Hide Dashboard Welcome Panel','lc-tweaks'); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Remove the default Welcome panel from the WordPress dashboard.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_hide_dashboard_welcome_panel" type="checkbox" value="1" <?php checked( '1', $dlck_hide_dashboard_welcome_panel_val ); ?> />
					</div>
				</div>
			</div>

			<div class="lc-kit trigger">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'All WP Settings', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'WordPress’s raw “all options” settings (the full wp_options list) for debugging.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_all_wp_settings_page" type="checkbox" value="1" <?php checked( '1', $dlck_all_wp_settings_page_val ); ?> />
				</div>
			</div>
			<a class="dlck-cust-link" href="<?php echo esc_attr( admin_url( 'options.php' ) ); ?>" target="_blank"><?php include DLCK_LC_KIT_PLUGIN_DIR . '/assets/img/gear-icon.php'; ?></a>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<h4><?php echo esc_html_e('IMPORTANT NOTE:', 'lc-tweaks'); ?></h4>
						<p><?php echo esc_html_e( 'This page lets you view/edit any option value directly.', 'lc-tweaks' ); ?></p>
						<p><?php echo esc_html_e( 'Useful for debugging.', 'lc-tweaks' ); ?></p>
						<p><?php echo esc_html_e( 'It’s also dangerous: changing the wrong option can break the site (URLs, active plugins, serialized arrays, etc.).', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Uploads', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Allow Unfiltered Uploads', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Permit admins to upload any file type via capability instead of wp-config.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_allow_unfiltered_uploads" type="checkbox" value="1" <?php checked( '1', $dlck_allow_unfiltered_uploads_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'This bypasses WordPress file restrictions for admins. It already allows SVG, JSON, and font uploads, so those toggles are disabled when this is on.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Allow SVG File Type Uploads', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Add SVG support in the WordPress Media Library.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_svg_uploads" type="checkbox" value="1" <?php checked( '1', $dlck_svg_uploads_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Allow JSON File Uploads (Lottie)', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Add JSON support in the WordPress Media Library for Lottie animations.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_json_uploads" type="checkbox" value="1" <?php checked( '1', $dlck_json_uploads_val ); ?> />
				</div>
			</div>
		</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Allow All Font Files Uploads', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Add TTF, OTF, WOFF, and WOFF2 support in WordPress.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_ttf_uploads" type="checkbox" value="1" <?php checked( '1', $dlck_ttf_uploads_val ); ?> />
				</div>
				</div>
			</div>

			<div class="lc-kit trigger">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Replace Image Tool', 'lc-tweaks' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Adds a Replace Image button to image Attachment Details so you can swap the file while keeping the same attachment ID and URL.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_replace_image_tool" type="checkbox" value="1" <?php checked( '1', $dlck_replace_image_tool_val ); ?> />
					</div>
				</div>
			</div>
			<div class="dlck-hide">
				<div class="lc-kit first nopad">
					<div class="box-title">
					</div>
					<div class="box-content">
						<div class="info">
							<p><?php echo esc_html_e( 'After enabling, open an image in the Media Library and use the new Replace Image button in Attachment Details.', 'lc-tweaks' ); ?></p>
							<p><?php echo esc_html_e( 'Disable browser and plugin caching while testing; old thumbnails can appear cached even when the replacement succeeded.', 'lc-tweaks' ); ?></p>
							<p><?php echo esc_html_e( 'Only image attachments are supported. The replacement image is copied over the existing file and new sizes are regenerated for the original attachment.', 'lc-tweaks' ); ?></p>
							<p><a class="button" href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>"><?php echo esc_html_e( 'Open Media Library', 'lc-tweaks' ); ?></a></p>
						</div>
					</div>
				</div>
			</div>

		</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Caching & Integrations', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'WP Rocket - Force Page Caching', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Use when other plugins/themes set DONOTCACHEPAGE but you still want caching.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_wprocket_force_page_caching" type="checkbox" value="1" <?php checked( '1', $dlck_wprocket_force_page_caching_val ); ?> />
					</div>
				</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'WP Rocket - Cache WP REST API', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Cache REST API responses with WP Rocket for better performance.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_wprocket_cache_wp_rest_api" type="checkbox" value="1" <?php checked( '1', $dlck_wprocket_cache_wp_rest_api_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'WP Rocket - Disable Above The Fold Optimization', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Treats “above-the-fold optimisation” as off, even if it’s enabled in the UI.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_wprocket_disable_above_fold_opt" type="checkbox" value="1" <?php checked( '1', $dlck_wprocket_disable_above_fold_opt_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'WP Rocket - Disable Priority Elements', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Disable Priority Elements optimizations (lazy render, critical images, and preconnect).', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_wprocket_disable_priority_elements" type="checkbox" value="1" <?php checked( '1', $dlck_wprocket_disable_priority_elements_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Disable SSL cURL Error 60 in \"WP All Import\"', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Disable peer verification to temporarily resolve error 60.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_ssl_curl_error_60_in_wpallimport" type="checkbox" value="1" <?php checked( '1', $dlck_disable_ssl_curl_error_60_in_wpallimport_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'ExactDN - Image Downsize Scale', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Prefer scaling (keeps the whole image, maintains aspect ratio) instead of cropping.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_exactdn_image_downsize_scale" type="checkbox" value="1" <?php checked( '1', $dlck_exactdn_image_downsize_scale_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Fewer “unexpected crops” on thumbnails/featured images generated via theme/page-builder code that passes hard dimensions.', 'lc-tweaks' ); ?></p>
						<p><?php echo esc_html_e( 'You may see images not matching the exact box ratio (unless the layout enforces cropping via CSS).', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Scheduling & Cron', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Kill Jetpack Cron', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Clear existing Jetpack cron events and block new ones from being scheduled.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_kill_jetpack_cron" type="checkbox" value="1" <?php checked( '1', $dlck_kill_jetpack_cron_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Speed Up Scheduled Actions', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Increase Action Scheduler throughput and trim retention to reduce overhead.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_speedup_scheduled_actions" type="checkbox" value="1" <?php checked( '1', $dlck_speedup_scheduled_actions_val ); ?> />
				</div>
			</div>
		</div>

	</div>

</div>
