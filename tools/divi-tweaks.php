<?php

	$dlck_settings_snapshot = function_exists( 'dlck_get_settings_snapshot' ) ? dlck_get_settings_snapshot() : array();
	$dlck_setting = static function ( string $key, $default = '' ) use ( $dlck_settings_snapshot ) {
		if ( array_key_exists( $key, $dlck_settings_snapshot ) ) {
			$value = $dlck_settings_snapshot[ $key ];
			return ( $value === '' && $default !== '' ) ? $default : $value;
		}
		return $default;
	};

$dlck_clear_divi_static_css_cache_local_storage_val = $dlck_setting( 'dlck_clear_divi_static_css_cache_local_storage' );
$dlck_auto_clear_cache_after_updates_val = $dlck_setting( 'dlck_auto_clear_cache_after_updates' );
$dlck_auto_clear_cache_after_post_save_builder_exit_val = $dlck_setting( 'dlck_auto_clear_cache_after_post_save_builder_exit' );
$dlck_edit_in_visual_builder_link_val    = $dlck_setting( 'dlck_edit_in_visual_builder_link' );
$dlck_copy_sender_contact_form_val         = $dlck_setting( 'dlck_copy_sender_contact_form' );
$dlck_fix_divi_user_scalable_val           = $dlck_setting( 'dlck_fix_divi_user_scalable' );
$dlck_disable_premade_layouts_val          = $dlck_setting( 'dlck_disable_premade_layouts' );
$dlck_disable_divi_ai_val                  = $dlck_setting( 'dlck_disable_divi_ai' );
$dlck_stop_map_module_excerpts_loading_val = $dlck_setting( 'dlck_stop_map_module_excerpts_loading' );
$dlck_hide_divi_cloud_val                  = $dlck_setting( 'dlck_hide_divi_cloud' );
$dlck_disable_upsells_divi_dashboard_val   = $dlck_setting( 'dlck_disable_upsells_divi_dashboard' );
$dlck_divi_library_view_val                = $dlck_setting( 'dlck_divi_library_view' );
$dlck_full_width_divi_footer_val           = $dlck_setting( 'dlck_full_width_divi_footer' );
$dlck_sticky_footer_val                   = $dlck_setting( 'dlck_sticky_footer' );
$dlck_social_target_val                   = $dlck_setting( 'dlck_social_target' );
$dlck_divi_fix_Anchor_links_val            = $dlck_setting( 'dlck_divi_fix_Anchor_links' );
$dlck_divi_hide_related_video_suggestions_val = $dlck_setting( 'dlck_divi_hide_related_video_suggestions' );
$dlck_divi_disable_related_video_suggestions_val = $dlck_setting( 'dlck_divi_disable_related_video_suggestions' );
$dlck_divi_autoplay_video_on_hover_val     = $dlck_setting( 'dlck_divi_autoplay_video_on_hover' );
$dlck_divi_fix_youtube_loading_height_val  = $dlck_setting( 'dlck_divi_fix_youtube_loading_height' );
$dlck_divi_accordions_closed_default_val   = $dlck_setting( 'dlck_divi_accordions_closed_default' );
$dlck_maintenance_layout_val               = $dlck_setting( 'dlck_maintenance_layout' );
$dlck_make_phone_number_click_to_call_val  = $dlck_setting( 'dlck_make_phone_number_click_to_call' );
$dlck_move_sidebar_to_top_on_mobile_val    = $dlck_setting( 'dlck_move_sidebar_to_top_on_mobile' );
$dlck_hide_divi_image_tooltip_val          = $dlck_setting( 'dlck_hide_divi_image_tooltip' );
$dlck_stop_divi_image_crop_portfolio_val   = $dlck_setting( 'dlck_stop_divi_image_crop_portfolio' );
$dlck_stop_divi_image_crop_gallery_val     = $dlck_setting( 'dlck_stop_divi_image_crop_gallery' );
$dlck_stop_divi_image_crop_blog_val        = $dlck_setting( 'dlck_stop_divi_image_crop_blog' );
$dlck_remove_divi_resize_image_post_val    = $dlck_setting( 'dlck_remove_divi_resize_image_post' );
$dlck_remove_divi_resize_image_gallery_val = $dlck_setting( 'dlck_remove_divi_resize_image_gallery' );
$dlck_remove_divi_resize_image_portfolio_val = $dlck_setting( 'dlck_remove_divi_resize_image_portfolio' );
$dlck_autoplay_videos_hide_controls_val    = $dlck_setting( 'dlck_autoplay_videos_hide_controls' );
$dlck_hide_projects_val                   = $dlck_setting( 'dlck_hide_projects' );
$dlck_woff_uploads_val                    = $dlck_setting( 'dlck_woff_uploads' );
$dlck_divi_custom_icons_val              = $dlck_setting( 'dlck_divi_custom_icons' );
$dlck_divi_custom_icon_urls              = $dlck_setting( 'dlck_divi_custom_icon_urls', array() );
if ( ! is_array( $dlck_divi_custom_icon_urls ) ) {
	$dlck_divi_custom_icon_urls = array();
}
$dlck_divi_custom_icon_indices = array_filter(
	array_keys( $dlck_divi_custom_icon_urls ),
	static function ( $key ) {
		return is_numeric( $key );
	}
);
$dlck_divi_custom_icon_next_index = $dlck_divi_custom_icon_indices ? ( max( $dlck_divi_custom_icon_indices ) + 1 ) : 0;
$dlck_divi_lazy_loading_val = $dlck_setting( 'dlck_divi_lazy_loading' );
$dlck_divi_lazy_sections_initial = (int) $dlck_setting( 'dlck_divi_lazy_sections_initial', 2 );
$dlck_divi_lazy_sections_subsequent = (int) $dlck_setting( 'dlck_divi_lazy_sections_subsequent', 2 );
$dlck_divi_lazy_defer_sections_val = $dlck_setting( 'dlck_divi_lazy_defer_sections' );
$dlck_divi_lazy_defer_initial = (int) $dlck_setting( 'dlck_divi_lazy_defer_initial', 2 );
$dlck_divi_lazy_exclude_urls = $dlck_setting( 'dlck_divi_lazy_exclude_urls', '' );
$dlck_divi_lazy_preload_on_purge_val = $dlck_setting( 'dlck_divi_lazy_preload_on_purge' );
$dlck_divi_lazy_loader_color = $dlck_setting( 'dlck_divi_lazy_loader_color', '#666666' );
$dlck_divi_lazy_loader_bg_color = $dlck_setting( 'dlck_divi_lazy_loader_bg_color', '#ffffff' );
$dlck_divi_lazy_loader_size = (int) $dlck_setting( 'dlck_divi_lazy_loader_size', 64 );
$dlck_divi_lazy_prefetch_offset = (int) $dlck_setting( 'dlck_divi_lazy_prefetch_offset', 300 );
$dlck_divi_lazy_load_all_on_interaction_val = $dlck_setting( 'dlck_divi_lazy_load_all_on_interaction' );
$dlck_divi_lazy_load_all_on_idle_val = $dlck_setting( 'dlck_divi_lazy_load_all_on_idle' );
$dlck_divi_lazy_home_only_val = $dlck_setting( 'dlck_divi_lazy_home_only' );
if ( $dlck_divi_lazy_sections_initial <= 0 ) {
	$dlck_divi_lazy_sections_initial = 2;
}
if ( $dlck_divi_lazy_sections_subsequent <= 0 ) {
	$dlck_divi_lazy_sections_subsequent = 2;
}
if ( $dlck_divi_lazy_defer_initial <= 0 ) {
	$dlck_divi_lazy_defer_initial = 2;
}
if ( $dlck_divi_lazy_loader_size <= 0 ) {
	$dlck_divi_lazy_loader_size = 64;
}
if ( $dlck_divi_lazy_prefetch_offset < 0 ) {
	$dlck_divi_lazy_prefetch_offset = 0;
}

?>

<div id="divi-tweaks" class="tool <?php echo $active_tab === 'divi-tweaks' ? 'tool-active' : ''; ?>">

	<div class="toolbox" style="padding:0 0 30px;">
		<div class="info" style="background:transparent;">
			<h4><?php esc_html_e( 'What are the Divi tweaks?', 'divi-lc-kit' ); ?></h4>
			<p><?php echo esc_html_e( 'Targeted fixes and enhancements for Divi sites to smooth UX and tame defaults.', 'divi-lc-kit' ); ?></p>
			<p><?php echo esc_html_e( 'Enable only what you need to avoid conflicts with theme updates or custom work.', 'divi-lc-kit' ); ?></p>
		</div>
	</div>

	<h2 class="tool-section"><?php echo esc_html_e('Performance', 'divi-lc-kit'); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Lazy Load Divi Sections', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Split Divi content into section chunks and load them as visitors scroll.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_divi_lazy_loading" type="checkbox" value="1"
						<?php checked( '1', $dlck_divi_lazy_loading_val ); ?> />
				</div>
			</div>
		</div>

			<div class="dlck-hide dlck-cache-helper-subtoggles">
				<div class="lc-kit first nopad">
					<div class="box-title">
					</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Applies to logged-out visitors and only to the main content body. Theme Builder headers/footers are not affected.', 'divi-lc-kit' ); ?></p>
						<p><?php echo esc_html_e( 'Disable for content that is not built entirely from Divi sections at the top level.', 'divi-lc-kit' ); ?></p>
						<p><?php echo esc_html_e( 'Works with WP Rocket and is excluded from Delay JavaScript automatically.', 'divi-lc-kit' ); ?></p>
						<p><?php echo esc_html_e( 'SEO: lazy-loaded sections are injected after initial HTML, so critical content should be in the first sections or excluded.', 'divi-lc-kit' ); ?></p>
						<p><?php echo esc_html_e( 'Cache files are stored in wp-content/cache/lc-tweaks-lazy, are warmed after published content updates, and are also cleared when content updates or WP Rocket purges cache.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Initial sections to load', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'How many sections to render on first load before lazy loading begins.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<input type="number" min="1" step="1" name="dlck_divi_lazy_sections_initial" value="<?php echo esc_attr( $dlck_divi_lazy_sections_initial ); ?>" />
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Sections per subsequent request', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'How many sections to load each time the visitor scrolls to the end.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<input type="number" min="1" step="1" name="dlck_divi_lazy_sections_subsequent" value="<?php echo esc_attr( $dlck_divi_lazy_sections_subsequent ); ?>" />
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Prefetch distance (px)', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Start loading the next sections before they enter the viewport. Larger values can feel faster but use more bandwidth.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<input type="number" min="0" max="2000" step="50" name="dlck_divi_lazy_prefetch_offset" value="<?php echo esc_attr( $dlck_divi_lazy_prefetch_offset ); ?>" />
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Load all sections after first interaction', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'After the first user interaction (scroll, click, keypress), load all remaining sections in the background.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_divi_lazy_load_all_on_interaction" type="checkbox" value="1"
							<?php checked( '1', $dlck_divi_lazy_load_all_on_interaction_val ); ?> />
					</div>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Load all sections when the page is idle', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'When the browser is idle, load all remaining sections in the background. This may start a few seconds after the page renders.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_divi_lazy_load_all_on_idle" type="checkbox" value="1"
							<?php checked( '1', $dlck_divi_lazy_load_all_on_idle_val ); ?> />
					</div>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Only apply on the homepage', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Restrict lazy loading to the front page only.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_divi_lazy_home_only" type="checkbox" value="1"
							<?php checked( '1', $dlck_divi_lazy_home_only_val ); ?> />
					</div>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Exclude URLs', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'One path per line (e.g. /checkout or /portfolio/*). Uses * wildcards.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<textarea name="dlck_divi_lazy_exclude_urls" rows="6" cols="60"><?php echo esc_textarea( $dlck_divi_lazy_exclude_urls ); ?></textarea>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Lazy Load Spinner Styling', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Customize the loading spinner appearance while sections load.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
				<div class="box-content">
					<p>
						<label>
							<span class="screen-reader-text"><?php echo esc_html_e( 'Spinner dot color', 'divi-lc-kit' ); ?></span>
							<input type="text" class="dlck-color-field" name="dlck_divi_lazy_loader_color" value="<?php echo esc_attr( $dlck_divi_lazy_loader_color ); ?>" />
						</label>
						<p class="description"><?php echo esc_html_e( 'Spinner dot color', 'divi-lc-kit' ); ?></p>
					</p>
					<p>
						<label>
							<span class="screen-reader-text"><?php echo esc_html_e( 'Loader background color', 'divi-lc-kit' ); ?></span>
							<input type="text" class="dlck-color-field" name="dlck_divi_lazy_loader_bg_color" value="<?php echo esc_attr( $dlck_divi_lazy_loader_bg_color ); ?>" />
						</label>
						<p class="description"><?php echo esc_html_e( 'Loader background color', 'divi-lc-kit' ); ?></p>
					</p>
					<p>
						<label>
							<?php echo esc_html_e( 'Spinner size (px)', 'divi-lc-kit' ); ?>
							<input type="number" min="24" max="160" step="1" name="dlck_divi_lazy_loader_size" value="<?php echo esc_attr( $dlck_divi_lazy_loader_size ); ?>" />
						</label>
					</p>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Preload Lazy Cache on WP Rocket Purge', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'When WP Rocket preload is disabled, warm lazy cache for the homepage and purged posts after cache clears.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_divi_lazy_preload_on_purge" type="checkbox" value="1"
							<?php checked( '1', $dlck_divi_lazy_preload_on_purge_val ); ?> />
					</div>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Clear Lazy Load Cache', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Remove cached section chunks for all posts. They will regenerate on the next page load.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<button type="button" class="dlck-settings-button" id="dlck-clear-lazy-cache">
						<?php esc_html_e( 'Clear Lazy Load Cache', 'divi-lc-kit' ); ?>
					</button>
					<div id="dlck-clear-lazy-cache-result" class="dlck-clear-cache-result" style="display:none;"></div>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Defer Below-Fold Divi Sections', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Keep full HTML for SEO but let browsers defer rendering below the initial sections. Ignored when Lazy Load Divi Sections is enabled.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_divi_lazy_defer_sections" type="checkbox" value="1"
						<?php checked( '1', $dlck_divi_lazy_defer_sections_val ); ?> />
				</div>
			</div>
		</div>

			<div class="dlck-hide dlck-cache-helper-subtoggles">
				<div class="lc-kit first nopad">
					<div class="box-title">
					</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Defer keeps content in the HTML (good for SEO) while letting the browser skip rendering below-the-fold sections until they are needed.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Initial sections to render', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'How many sections to render immediately before deferring the rest.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<input type="number" min="1" step="1" name="dlck_divi_lazy_defer_initial" value="<?php echo esc_attr( $dlck_divi_lazy_defer_initial ); ?>" />
				</div>
			</div>
		</div>
	</div>

		<h2 class="tool-section"><?php echo esc_html_e('Helpers', 'divi-lc-kit'); ?></h2>
		<div class="tool-wrap">

				<div class="lc-kit">
					<div class="box-title">
						<h3><?php echo esc_html_e( 'Edit in Visual Builder (post/page list link)', 'divi-lc-kit' ); ?></h3>
						<div class="box-descr">
							<p><?php echo esc_html_e( 'Add an "Edit in Visual Builder" link to post and page row actions when the Divi Builder is used.', 'divi-lc-kit' ); ?></p>
							<p><?php echo esc_html_e( 'Compatible with Divi 4 and Divi 5.', 'divi-lc-kit' ); ?></p>
						</div>
					</div>
					<div class="box-content minibox">
						<div class="checkbox">
							<input name="dlck_edit_in_visual_builder_link" type="checkbox" value="1"
								<?php checked( '1', $dlck_edit_in_visual_builder_link_val ); ?> />
						</div>
					</div>
				</div>

			<div class="lc-kit trigger">
				<div class="box-title">
					<h3><span class="new">new</span><?php echo esc_html_e( 'Clear Divi static css cache + local storage', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e( 'Add a clear Divi static css cache + local storage button to the wordpress admin bar.', 'divi-lc-kit' ); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_clear_divi_static_css_cache_local_storage" type="checkbox" value="1"
						<?php checked( '1', $dlck_clear_divi_static_css_cache_local_storage_val ); ?> />
				</div>
			</div>
		</div>

			<div class="dlck-hide dlck-cache-helper-subtoggles">
				<div class="lc-kit">
					<div class="box-title">
						<h3><?php echo esc_html_e( 'Auto clear caches after updates', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p>
							<?php echo esc_html_e( 'After WordPress core, plugin, or theme updates complete, queue a single automated cache clear pass. Multiple update events are batched into one run.', 'divi-lc-kit' ); ?>
						</p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_auto_clear_cache_after_updates" type="checkbox" value="1"
							<?php checked( '1', $dlck_auto_clear_cache_after_updates_val ); ?> />
					</div>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Enable after post save + after exiting Visual Builder', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p>
							<?php echo esc_html_e( 'After saving a post or exiting the Visual Builder, clear Divi static CSS file generation and clear local storage for the current user on the next page load.', 'divi-lc-kit' ); ?>
						</p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_auto_clear_cache_after_post_save_builder_exit" type="checkbox" value="1"
							<?php checked( '1', $dlck_auto_clear_cache_after_post_save_builder_exit_val ); ?> />
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Add WOFF and WOFF2 Uploads in the Divi Builder', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Enable WOFF/WOFF2 font formats in the Divi Builder upload dialog.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_woff_uploads" type="checkbox" value="1"
						<?php checked( '1', $dlck_woff_uploads_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Add Custom Icons', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo wp_kses_post( __( 'Add custom icons for use in modules [recommended size 96x96px]. To use svg ensure the <strong>Allow Unfiltered Uploads</strong> or <strong>Allow SVG File Type Uploads</strong> is enabled on the Maintenance page.', 'divi-lc-kit' ) ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_divi_custom_icons" type="checkbox" value="1"
						<?php checked( '1', $dlck_divi_custom_icons_val ); ?> />
				</div>
			</div>
		</div>

		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info dlck-custom-icons">
						<div class="dlck-custom-icons-list" data-input-name="dlck_divi_custom_icon_urls" data-next-index="<?php echo esc_attr( $dlck_divi_custom_icon_next_index ); ?>" data-placeholder="<?php esc_attr_e( 'Image URL', 'divi-lc-kit' ); ?>" data-choose-label="<?php esc_attr_e( 'Choose Image', 'divi-lc-kit' ); ?>" data-remove-label="<?php esc_attr_e( 'Remove', 'divi-lc-kit' ); ?>">
							<?php
							if ( ! empty( $dlck_divi_custom_icon_urls ) ) {
								ksort( $dlck_divi_custom_icon_urls );
								foreach ( $dlck_divi_custom_icon_urls as $index => $icon_url ) {
									if ( ! is_numeric( $index ) || $icon_url === '' ) {
										continue;
									}
									?>
									<p class="dlck-custom-icon-row">
										<input type="url" class="background_image" size="36" maxlength="1024" placeholder="<?php esc_attr_e( 'Image URL', 'divi-lc-kit' ); ?>" name="dlck_divi_custom_icon_urls[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_url( $icon_url ); ?>" />
										<button type="button" class="button upload_image_button"><?php esc_html_e( 'Choose Image', 'divi-lc-kit' ); ?></button>
										<button type="button" class="button dlck-remove-custom-icon"><?php esc_html_e( 'Remove', 'divi-lc-kit' ); ?></button>
									</p>
									<?php
								}
							}
							?>
						</div>
						<button type="button" class="button dlck-add-custom-icon"><?php esc_html_e( 'Add Icon', 'divi-lc-kit' ); ?></button>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Hide Projects', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Hide the Divi Projects post type and portfolio modules.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_hide_projects" type="checkbox" value="1"
						<?php checked( '1', $dlck_hide_projects_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Stop Map Module Excerpts Loading', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Stop Divi map module excerpts from unnecessarily loading map scripts.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_stop_map_module_excerpts_loading" type="checkbox" value="1"
						<?php checked( '1', $dlck_stop_map_module_excerpts_loading_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><span class="new">top</span><?php echo esc_html_e('Hide The Elegant Themes Upsells In The Divi Dashboard', 'divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("A helper to hide the new ready-made layouts of Divi for users. Once activated Divi premade layouts are only accessible for administrators without any further changes. ", "divi-lc-kit"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_upsells_divi_dashboard" type="checkbox" value="1"
						<?php checked( '1', $dlck_disable_upsells_divi_dashboard_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><span class="new">top</span><?php echo esc_html_e('Disable Divi AI', 'divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("A helper to Disable Divi AI by default in the Divi Role Editor.", "divi-lc-kit"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_divi_ai" type="checkbox" value="1"
						<?php checked( '1', $dlck_disable_divi_ai_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><span class="new">top</span><?php echo esc_html_e( 'Hide Divi Cloud', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e( 'If you do not want to use Divi Cloud and would prefer to remove it from Divi Builder.', 'divi-lc-kit' ); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_hide_divi_cloud" type="checkbox" value="1"
						<?php checked( '1', $dlck_hide_divi_cloud_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><span class="new">top</span><?php echo esc_html_e('Disable Divi Premade Layouts', 'divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("A helper to hide the new ready-made layouts of Divi for users. Once activated Divi premade layouts are only accessible for administrators without any further changes. ", "divi-lc-kit"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_premade_layouts" type="checkbox" value="1"
						<?php checked( '1', $dlck_disable_premade_layouts_val ); ?> />
				</div>
			</div>
		</div>

		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<h4><?php echo esc_html_e('How to disable the premade layouts:', 'divi-lc-kit'); ?></h4>
						<p>
							<?php echo esc_html_e("Navigate to Divi → Divi Theme Options", 'divi-lc-kit'); ?>
							</u>:</p>
						<p>Enable "Layouts Disable User Check" and Change the user role "Layouts Change User Role"
							<br>
							<br>Here you can enter the user roles (hierarchical, e. g. editor, author…) and/or
							permissions (e. g. activate_plugins, delete_others_pages…) separated by a comma, for users
							who are allowed to continue using the Divi Premade layouts.</p>
						<br />
						<?php echo esc_html_e('Default ', 'divi-lc-kit'); ?>
						<strong><?php echo esc_html_e('administrator', 'divi-lc-kit'); ?></strong>
						<br />
						<br />
						<?php echo esc_html_e('Completely disable ', 'divi-lc-kit'); ?>
						<strong><?php echo esc_html_e('current_user_can ()', 'divi-lc-kit'); ?></strong>
						<br />
					</div>
				</div>
			</div>

		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e('Divi Library View', 'divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("Allow viewing Divi library layouts on the front end, set featured images and display them in the admin screen.", "divi-lc-kit"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_divi_library_view" type="checkbox" value="1"
						<?php checked( '1', $dlck_divi_library_view_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php esc_html_e('Show Custom Maintenance, Coming Soon Or Notice', 'divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p><?php esc_html_e('This layout will be displayed globally for logged out users', "divi-lc-kit"); ?>
					</p>
				</div>

			</div>
			<div class="box-content">
				<?php
					$layout_query = array(
						'post_type'      => 'et_pb_layout',
						'posts_per_page' => -1,
						'post_status'    => 'publish',
					);
					$layouts = get_posts( $layout_query );
					if ( $layouts ) {
						?>
						<select name="dlck_maintenance_layout">
							<option value="none">----- None -----</option>
							<?php
							foreach ( $layouts as $layout ) {
								echo '<option ' . selected( esc_attr( $layout->ID ), $dlck_maintenance_layout_val, false ) . ' value="' . esc_attr( $layout->ID ) . '">' . esc_attr( $layout->post_title ) . '</option>';
							}
							?>
						</select>
						<?php
					} else {
						printf( '<p class="info">%s</p>', esc_html__( 'Sorry, your Divi Library is empty. Create and save some layouts first...', 'divi-lc-kit' ) );
					}
				?>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e('Divi Modifers', 'divi-lc-kit'); ?></h2>
	<div class="tool-wrap">

	<div class="lc-kit">
			<div class="box-title">
				<h3><span class="new">top</span><?php echo esc_html_e('Hide Divi Image Tooltip', 'divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("Hide the Divi image title tooltip that appears on hover", "divi-lc-kit"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_hide_divi_image_tooltip" type="checkbox" value="1"
						<?php checked( '1', $dlck_hide_divi_image_tooltip_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e('Fix Divi user-scalable=”no” problem', 'divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("Replace Divi’s default viewport tag with one that’s more accessibility-friendly", "divi-lc-kit"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_fix_divi_user_scalable" type="checkbox" value="1"
						<?php checked( '1', $dlck_fix_divi_user_scalable_val ); ?> />
				</div>
			</div>
		</div>
		
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<h4><?php echo esc_html_e('How to access the Divi Responsive Helper settings:', 'divi-lc-kit'); ?>
						</h4>
						<p>
							<?php echo esc_html_e("Navigate to Divi → Divi Theme Options → Divi Responsive Helper", 'divi-lc-kit'); ?>
							</u>:</p>
						<p>Turn on the options required.</p>
					</div>
				</div>
			</div>

		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e('Make Phone Number Click To Call', 'divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("For use in the default Divi menu. When users click on your number in the menu, it'll automatically dial the number for them.", "divi-lc-kit"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_make_phone_number_click_to_call" type="checkbox" value="1"
						<?php checked( '1', $dlck_make_phone_number_click_to_call_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e('Copy to sender via Contact Form', 'divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("Send a copy of the form submission to the sender with the Contact Form module.", "divi-lc-kit"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_copy_sender_contact_form" type="checkbox" value="1"
						<?php checked( '1', $dlck_copy_sender_contact_form_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e('Full-Width Divi Footer', 'divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("Set up the Divi default footer to full-width, the same style as the header.", "divi-lc-kit"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_full_width_divi_footer" type="checkbox" value="1"
						<?php checked( '1', $dlck_full_width_divi_footer_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e('Sticky Footer (Divi)', 'divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e('Pin the footer to the bottom when the page content is shorter than the viewport.', 'divi-lc-kit'); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_sticky_footer" type="checkbox" value="1"
						<?php checked( '1', $dlck_sticky_footer_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e('Open Social Links in a New Tab', 'divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e('Force Divi social icon links to open in a new browser tab.', 'divi-lc-kit'); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_social_target" type="checkbox" value="1"
						<?php checked( '1', $dlck_social_target_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e('Fix Divi Anchor Links', 'divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("Fixing the anchor link scrolling in the Divi Theme options.", "divi-lc-kit"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_divi_fix_Anchor_links" type="checkbox" value="1"
						<?php checked( '1', $dlck_divi_fix_Anchor_links_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e('Make Divi Accordions Closed by Default', 'divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("Make accordions start fully closed by default.", "divi-lc-kit"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_divi_accordions_closed_default" type="checkbox" value="1"
						<?php checked( '1', $dlck_divi_accordions_closed_default_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e('Move the sidebar to the top on mobile', 'divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("Moves the sidebar above the content on mobile.", "divi-lc-kit"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_move_sidebar_to_top_on_mobile" type="checkbox" value="1"
						<?php checked( '1', $dlck_move_sidebar_to_top_on_mobile_val ); ?> />
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php esc_html_e('Images', 'divi-lc-kit'); ?></h2>
	<div class="tool-wrap">
	<div class="lc-kit">
		<div class="box-title fullw">
			<h3><?php esc_html_e('Disable image sizes not required', 'divi-lc-kit'); ?></h3>
			<div class="box-descr"><p><?php esc_html_e('Disable image creation For Divi custom sizes.', 'divi-lc-kit'); ?></p></div>			
		</div>
	</div>

		<div class="lc-kit css">
			<div class="box-content minibox">
				<div class="on-off">
					<input name="dlck_remove_divi_resize_image_gallery" type="checkbox" value="1"
						<?php checked( '1', $dlck_remove_divi_resize_image_gallery_val ); ?> />
				</div>
			</div>
			<div class="box-title">
				<div class="box-descr">
				<h5><?php esc_html_e('Disable Divi Gallery Images', 'divi-lc-kit'); ?></h5>
					<p><?php esc_html_e("Remove et-pb-gallery-module-image-portrait image creation.", "divi-lc-kit"); ?></p>
				</div>
			</div>
		</div>

		<div class="lc-kit css">
			<div class="box-content minibox">
				<div class="on-off">
					<input name="dlck_remove_divi_resize_image_portfolio" type="checkbox" value="1"
						<?php checked( '1', $dlck_remove_divi_resize_image_portfolio_val ); ?> />
				</div>
			</div>
			<div class="box-title">
				<div class="box-descr">
					<h5><?php esc_html_e('Disable Divi Portfolio Images', 'divi-lc-kit'); ?></h5>
					<p><?php esc_html_e("Remove et-pb-portfolio-image, et-pb-portfolio-module-image and et-pb-portfolio-image-single images creation.", "divi-lc-kit"); ?></p>
				</div>
			</div>
		</div>

		<div class="lc-kit css">
			<div class="box-content minibox">
				<div class="on-off">
					<input name="dlck_remove_divi_resize_image_post" type="checkbox" value="1"
						<?php checked( '1', $dlck_remove_divi_resize_image_post_val ); ?> />
				</div>
			</div>
			<div class="box-title">
				<div class="box-descr">
					<h5><?php esc_html_e('Disable Divi Post Images', 'divi-lc-kit'); ?></h5>
					<p><?php esc_html_e("Remove et-pb-post-main-image, et-pb-post-main-image-fullwidth and et-pb-post-main-image-fullwidth-large images creation.", "divi-lc-kit"); ?></p>
				</div>
			</div>
		</div>
		<div class="lc-kit">
		<div class="box-title fullw">
			<h3><?php esc_html_e('Stop Divi Image Crop', 'divi-lc-kit'); ?></h3>
			<div class="box-descr"><p><?php esc_html_e('For Blog, Portfolio, and Gallery Modules. This disables WordPress auto cropping images completely for these sizes.', 'divi-lc-kit'); ?></p></div>			
		</div>
	</div>

		<div class="lc-kit css">
			<div class="box-content minibox">
				<div class="on-off">
					<input name="dlck_stop_divi_image_crop_gallery" type="checkbox" value="1"
						<?php checked( '1', $dlck_stop_divi_image_crop_gallery_val ); ?> />
				</div>
			</div>
			<div class="box-title">
				<div class="box-descr">
					<h5><?php esc_html_e('Divi Gallery Images', 'divi-lc-kit'); ?></h5>
					<p><?php esc_html_e("Remove Divi Gallery Module image crop.", "divi-lc-kit"); ?></p>
				</div>
			</div>
		</div>

		<div class="lc-kit css">
			<div class="box-content minibox">
				<div class="on-off">
					<input name="dlck_stop_divi_image_crop_portfolio" type="checkbox" value="1"
						<?php checked( '1', $dlck_stop_divi_image_crop_portfolio_val ); ?> />
				</div>
			</div>
			<div class="box-title">
				<div class="box-descr">
					<h5><?php esc_html_e('Divi Portfolio Images', 'divi-lc-kit'); ?></h5>
					<p><?php esc_html_e("Remove Divi Portfolio and Filterable Portfolio featured image crop.", "divi-lc-kit"); ?></p>
				</div>
			</div>
		</div>

		<div class="lc-kit css">
			<div class="box-content minibox">
				<div class="on-off">
					<input name="dlck_stop_divi_image_crop_blog" type="checkbox" value="1"
						<?php checked( '1', $dlck_stop_divi_image_crop_blog_val ); ?> />
				</div>
			</div>
			<div class="box-title">
				<div class="box-descr">
					<h5><?php esc_html_e('Divi Blog Feed Images', 'divi-lc-kit'); ?></h5>
					<p><?php esc_html_e("Remove Divi Blog Module featured image crop.", "divi-lc-kit"); ?></p>
				</div>
			</div>
		</div>

		
	</div>

	<h2 class="tool-section"><?php echo esc_html_e('Videos', 'divi-lc-kit'); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e('Autoplay Standard Videos in Divi Video Module And Hide Controls', 'divi-lc-kit'); ?>
				</h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("Autoplay videos in the Divi video module and hide the controls on the video.", "divi-lc-kit"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_autoplay_videos_hide_controls" type="checkbox" value="1"
						<?php checked( '1', $dlck_autoplay_videos_hide_controls_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<h4><?php echo esc_html_e('How to use it:', 'divi-lc-kit'); ?></h4>
						<p>
							<?php echo esc_html_e("Add CSS Class To The Video Module", 'divi-lc-kit'); ?>
							</u>:</p>
						<p>
							<strong>dlck-video-autoplay</strong> - You can add the CSS Class inside the <strong>Video
								Module Settings > Advanced > CSS ID & Classes > CSS Class</strong></p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e('Autoplay Divi Video Module Clips on Hover', 'divi-lc-kit'); ?>
				</h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("The video will pause / return to the start when the user stops hovering over it.", "divi-lc-kit"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_divi_autoplay_video_on_hover" type="checkbox" value="1"
						<?php checked( '1', $dlck_divi_autoplay_video_on_hover_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<h4><?php echo esc_html_e('How to use it:', 'divi-lc-kit'); ?></h4>
						<p>
							<?php echo esc_html_e("Add CSS Class To The Video Module", 'divi-lc-kit'); ?>
							</u>:</p>
						<p>
							<strong>dlck-autoplay-video-hover</strong> - You can add the CSS Class inside the
							<strong>Video
								Module Settings > Advanced > CSS ID & Classes > CSS Class</strong></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e('Fix Youtube Loading Height in Divi Module', 'divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p>
						<?php echo esc_html_e("Add the missing video fluid-width-video-wrapper div tag to the Divi module.", "divi-lc-kit"); ?>
					</p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_divi_fix_youtube_loading_height" type="checkbox" value="1"
						<?php checked( '1', $dlck_divi_fix_youtube_loading_height_val ); ?> />
				</div>
			</div>
		</div>

	</div>


</div>
