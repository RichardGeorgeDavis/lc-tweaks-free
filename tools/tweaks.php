<?php

$dlck_antispam_email_shortcode_val                  = dlck_get_option( 'dlck_antispam_email_shortcode' );
$dlck_footer_date_shortcode_val                     = dlck_get_option( 'dlck_footer_date_shortcode' );
$dlck_disable_image_scaling_val                     = dlck_get_option( 'dlck_disable_image_scaling' );
$dlck_restore_infinite_media_scrolling_val          = dlck_get_option( 'dlck_restore_infinite_media_scrolling' );
$dlck_disable_rss_feed_val                          = dlck_get_option( 'dlck_disable_rss_feed' );
$dlck_disable_wp_search_val                         = dlck_get_option( 'dlck_disable_wp_search' );
$dlck_wprocket_force_page_caching_val               = dlck_get_option( 'dlck_wprocket_force_page_caching' );
$dlck_wprocket_cache_wp_rest_api_val                = dlck_get_option( 'dlck_wprocket_cache_wp_rest_api' );
$dlck_disable_gutenberg_val                         = dlck_get_option( 'dlck_disable_gutenberg' );
$dlck_disable_block_editor_from_managing_widgets_val = dlck_get_option( 'dlck_disable_block_editor_from_managing_widgets' );
$dlck_disable_wordpress_image_sizes_val             = dlck_get_option( 'dlck_disable_wordpress_image_sizes' );
$dlck_body_class_user_role_val                      = dlck_get_option( 'dlck_body_class_user_role' );
$dlck_disable_all_comments_val                      = dlck_get_option( 'dlck_disable_all_comments' );
$dlck_mobile_theme_color_enable_val                 = dlck_get_option( 'dlck_mobile_theme_color_enable' );
$dlck_mobile_theme_color_val                        = dlck_get_option( 'dlck_mobile_theme_color' );
$dlck_disable_admin_new_user_notification_emails_val = dlck_get_option( 'dlck_disable_admin_new_user_notification_emails' );
$dlck_kill_jetpack_cron_val                         = dlck_get_option( 'dlck_kill_jetpack_cron' );
$dlck_speedup_scheduled_actions_val                 = dlck_get_option( 'dlck_speedup_scheduled_actions' );
$dlck_divi_hide_related_video_suggestions_val       = dlck_get_option( 'dlck_divi_hide_related_video_suggestions' );
$dlck_divi_disable_related_video_suggestions_val    = dlck_get_option( 'dlck_divi_disable_related_video_suggestions' );


?>

<div id="tweaks" class="tool <?php echo $active_tab == 'tweaks' ? 'tool-active' : ''; ?>">

	<div class="toolbox" style="padding:0 0 30px;">
		<div class="info" style="background:transparent;">
			<h4><?php esc_html_e( 'What are the WordPress tweaks?', 'lc-tweaks' ); ?></h4>
			<p><?php echo esc_html_e( 'Common WordPress housekeeping tweaks to harden, declutter, and speed up your site.', 'lc-tweaks' ); ?></p>
			<p><?php echo esc_html_e( 'Use these when you want global behaviour changes without touching code.', 'lc-tweaks' ); ?></p>
		</div>
	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Wordpress', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Antispam Email Shortcode', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e( 'Converts selected email addresses characters to HTML entities to block spam bots.', 'lc-tweaks' ); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_antispam_email_shortcode" type="checkbox" value="1"
						<?php checked( '1', $dlck_antispam_email_shortcode_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<h4><?php echo esc_html_e( 'How to use it:', 'lc-tweaks' ); ?></h4>
						<p>
							<?php echo esc_html_e( 'Add emails with a shortcode', 'lc-tweaks' ); ?>
							</u>:</p>
						<p>
							<strong>[email]name@website.com[/email]</strong> - converts email address characters to HTML
						</p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Footer Date Shortcode', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e( 'Add the current year to the copyright line.', 'lc-tweaks' ); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_footer_date_shortcode" type="checkbox" value="1"
						<?php checked( '1', $dlck_footer_date_shortcode_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<h4><?php echo esc_html_e( 'How to use it:', 'lc-tweaks' ); ?></h4>
						<p>
							<?php echo esc_html_e( 'Add shortcode in footer', 'lc-tweaks' ); ?>
							</u>:</p>
						<p>
							<strong><?php echo esc_html_e( '&#169;[footer_date_]', 'lc-tweaks' ); ?></strong> - adds the
							current year to the copyright line</p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Mobile Browser Theme Color', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( "Set the theme color used by mobile browsers for the address bar/header.", 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_mobile_theme_color_enable" type="checkbox" value="1"
						<?php checked( '1', $dlck_mobile_theme_color_enable_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title"></div>
				<div class="box-content">
					<?php $dlck_mobile_theme_color = ! empty( $dlck_mobile_theme_color_val ) ? $dlck_mobile_theme_color_val : '#ffffff'; ?>
					<input type="text" name="dlck_mobile_theme_color" value="<?php echo esc_attr( $dlck_mobile_theme_color ); ?>" class="dlck-color-field" />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Add User Role to Body Class', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Append the current user role(s) as body classes for targeted styling.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_body_class_user_role" type="checkbox" value="1" <?php checked( '1', $dlck_body_class_user_role_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Disable Admin New User Notification Emails', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Stop WordPress from emailing the site admin when a new user account is registered, while still allowing the new user email to be sent.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_admin_new_user_notification_emails" type="checkbox" value="1" <?php checked( '1', $dlck_disable_admin_new_user_notification_emails_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title"></div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'This applies globally to WordPress registration emails. If the WooCommerce customer-registration admin email tweak is also enabled, LC Tweaks will switch the Woo setting off because the global rule takes precedence.', 'lc-tweaks' ); ?></p>
					</div>
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Builders & Gutenberg', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Disable Gutenberg', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e( 'Turn off the Gutenberg editor and block library assets.', 'lc-tweaks' ); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_gutenberg" type="checkbox" value="1"
						<?php checked( '1', $dlck_disable_gutenberg_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Disable Widget Block Editor', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e( 'Use classic widgets instead of the block-based widget editor.', 'lc-tweaks' ); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_block_editor_from_managing_widgets" type="checkbox" value="1"
						<?php checked( '1', $dlck_disable_block_editor_from_managing_widgets_val ); ?> />
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Images', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Disable image scaling', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e( 'Disable WordPress auto resize function.', 'lc-tweaks' ); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_image_scaling" type="checkbox" value="1"
						<?php checked( '1', $dlck_disable_image_scaling_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Disable WordPress Image Sizes', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e( 'Remove the three WordPress sizes medium_large, 1536x1536, 2048x2048.', 'lc-tweaks' ); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_wordpress_image_sizes" type="checkbox" value="1"
						<?php checked( '1', $dlck_disable_wordpress_image_sizes_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Restore Infinite Media scrolling', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e( 'Restore Infinite WordPress Media Library scrolling.', 'lc-tweaks' ); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_restore_infinite_media_scrolling" type="checkbox" value="1"
						<?php checked( '1', $dlck_restore_infinite_media_scrolling_val ); ?> />
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Web Performance', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Disable Your RSS Feed', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e( 'Although its a great WordPress feature, if you aren’t interested in using RSS at all, you can disable it altogether.', 'lc-tweaks' ); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_rss_feed" type="checkbox" value="1"
						<?php checked( '1', $dlck_disable_rss_feed_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Disable WordPress Search Feature', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e( 'Using WordPress to power a simple site, like a single landing page, you may wish to disable the platform’s search feature.', 'lc-tweaks' ); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_wp_search" type="checkbox" value="1"
						<?php checked( '1', $dlck_disable_wp_search_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Disable All Comments', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e( 'Turn off comments and trackbacks everywhere, hide existing comments, and remove comment menus.', 'lc-tweaks' ); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_all_comments" type="checkbox" value="1"
						<?php checked( '1', $dlck_disable_all_comments_val ); ?> />
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e('Videos', 'lc-tweaks'); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e('Restrict suggestions to the same YouTube channel', 'lc-tweaks'); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("Prevent displaying related videos.", "lc-tweaks"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_divi_hide_related_video_suggestions" type="checkbox" value="1"
						<?php checked( '1', $dlck_divi_hide_related_video_suggestions_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e('Hide Related YouTube Video Suggestions', 'lc-tweaks'); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("Prevent displaying related videos.", "lc-tweaks"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_divi_disable_related_video_suggestions" type="checkbox" value="1"
						<?php checked( '1', $dlck_divi_disable_related_video_suggestions_val ); ?> />
				</div>
			</div>
		</div>

	</div>


</div>
