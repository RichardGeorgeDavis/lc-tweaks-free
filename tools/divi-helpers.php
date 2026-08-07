<?php
/**
 * Divi Helper settings imported/adapted from Divi Assistant.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dlck_divi_helpers_value' ) ) {
	function dlck_divi_helpers_value( string $key, $default = '' ) {
		return function_exists( 'dlck_get_setting_from_snapshot' )
			? dlck_get_setting_from_snapshot( $key, $default )
			: dlck_get_option( $key, $default );
	}
}

if ( ! function_exists( 'dlck_divi_helpers_toggle' ) ) {
	function dlck_divi_helpers_toggle( string $key, string $title, string $description = '', string $badge = '' ): void {
		$value = dlck_divi_helpers_value( $key );
		?>
		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo $badge !== '' ? '<span class="new">' . esc_html( $badge ) . '</span>' : ''; ?><?php echo esc_html( $title ); ?></h3>
				<?php if ( $description !== '' ) : ?>
					<div class="box-descr"><p><?php echo esc_html( $description ); ?></p></div>
				<?php endif; ?>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="<?php echo esc_attr( $key ); ?>" type="checkbox" value="1" <?php checked( '1', $value ); ?> />
				</div>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'dlck_divi_helpers_trigger_toggle' ) ) {
	function dlck_divi_helpers_trigger_toggle( string $key, string $title, string $description = '', string $badge = '' ): void {
		$value = dlck_divi_helpers_value( $key );
		?>
		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo $badge !== '' ? '<span class="new">' . esc_html( $badge ) . '</span>' : ''; ?><?php echo esc_html( $title ); ?></h3>
				<?php if ( $description !== '' ) : ?>
					<div class="box-descr"><p><?php echo esc_html( $description ); ?></p></div>
				<?php endif; ?>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="<?php echo esc_attr( $key ); ?>" type="checkbox" value="1" <?php checked( '1', $value ); ?> />
				</div>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'dlck_divi_helpers_number' ) ) {
	function dlck_divi_helpers_number( string $key, string $title, string $description, int $default, int $min = 0, int $max = 9999, int $step = 1 ): void {
		$value = (int) dlck_divi_helpers_value( $key, $default );
		?>
		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html( $title ); ?></h3>
				<div class="box-descr"><p><?php echo esc_html( $description ); ?></p></div>
			</div>
			<div class="box-content minibox">
				<input type="number" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" step="<?php echo esc_attr( $step ); ?>" />
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'dlck_divi_helpers_text' ) ) {
	function dlck_divi_helpers_text( string $key, string $title, string $description, string $default = '', string $type = 'text' ): void {
		$value = (string) dlck_divi_helpers_value( $key, $default );
		?>
		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html( $title ); ?></h3>
				<div class="box-descr"><p><?php echo esc_html( $description ); ?></p></div>
			</div>
			<div class="box-content minibox">
				<input type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'dlck_divi_helpers_textarea' ) ) {
	function dlck_divi_helpers_textarea( string $key, string $title, string $description, string $default = '', int $rows = 6 ): void {
		$value = (string) dlck_divi_helpers_value( $key, $default );
		?>
		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html( $title ); ?></h3>
				<div class="box-descr"><p><?php echo esc_html( $description ); ?></p></div>
			</div>
			<div class="box-content minibox">
				<textarea name="<?php echo esc_attr( $key ); ?>" rows="<?php echo esc_attr( $rows ); ?>" cols="72"><?php echo esc_textarea( $value ); ?></textarea>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'dlck_divi_helpers_select' ) ) {
	function dlck_divi_helpers_select( string $key, string $title, string $description, array $options, string $default = '' ): void {
		$value = (string) dlck_divi_helpers_value( $key, $default );
		?>
		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html( $title ); ?></h3>
				<div class="box-descr"><p><?php echo esc_html( $description ); ?></p></div>
			</div>
			<div class="box-content minibox">
				<select name="<?php echo esc_attr( $key ); ?>">
					<?php foreach ( $options as $option_value => $option_label ) : ?>
						<option value="<?php echo esc_attr( (string) $option_value ); ?>" <?php selected( (string) $option_value, $value ); ?>><?php echo esc_html( (string) $option_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'dlck_divi_helpers_color' ) ) {
	function dlck_divi_helpers_color( string $key, string $label, string $default ): void {
		$value = (string) dlck_divi_helpers_value( $key, $default );
		?>
		<p>
			<label>
				<?php echo esc_html( $label ); ?>
				<input type="text" class="dlck-color-field" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
			</label>
		</p>
		<?php
	}
}

$dlck_layouts = get_posts(
	array(
		'post_type'      => 'et_pb_layout',
		'post_status'    => 'publish',
		'posts_per_page' => 300,
		'orderby'        => 'title',
		'order'          => 'ASC',
	)
);
$dlck_layout_options = array( '0' => __( 'No layout selected', 'lc-tweaks' ) );
foreach ( $dlck_layouts as $dlck_layout ) {
	$dlck_layout_options[ (string) $dlck_layout->ID ] = get_the_title( $dlck_layout );
}
?>

<div id="divi-helpers" class="tool <?php echo $active_tab === 'divi-helpers' ? 'tool-active' : ''; ?>">
	<div class="toolbox" style="padding:0 0 30px;">
		<div class="info" style="background:transparent;">
			<h4><?php esc_html_e( 'What are the Divi Helpers?', 'lc-tweaks' ); ?></h4>
			<p><?php esc_html_e( 'Assistant-style Divi, media, frontend, and maintenance helpers adapted to LC Tweaks.', 'lc-tweaks' ); ?></p>
			<p><?php esc_html_e( 'All helpers are off by default and load only in the contexts they affect.', 'lc-tweaks' ); ?></p>
		</div>
	</div>

	<h2 class="tool-section"><?php esc_html_e( 'Visual Builder', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">
		<?php
		dlck_divi_helpers_toggle( 'dlck_divi_vb_escape_key', __( 'ESC closes builder modals', 'lc-tweaks' ), __( 'Use the Escape key to close common Divi Visual Builder dialogs.', 'lc-tweaks' ), 'new' );
		dlck_divi_helpers_toggle( 'dlck_divi_vb_sticky_toolbar', __( 'Sticky Visual Builder toolbar', 'lc-tweaks' ), __( 'Keep builder action bars visible while editing long option panels.', 'lc-tweaks' ) );
		dlck_divi_helpers_trigger_toggle( 'dlck_divi_vb_html_textarea_height_enabled', __( 'HTML textarea height', 'lc-tweaks' ), __( 'Set a taller default height for HTML/code text areas in builder settings.', 'lc-tweaks' ) );
		?>
		<div class="dlck-hide">
			<?php dlck_divi_helpers_number( 'dlck_divi_vb_html_textarea_height', __( 'Textarea height (px)', 'lc-tweaks' ), __( 'Applied to builder HTML and code text areas.', 'lc-tweaks' ), 360, 120, 1200, 10 ); ?>
		</div>
		<?php
		dlck_divi_helpers_trigger_toggle( 'dlck_divi_vb_icon_picker_height_enabled', __( 'Icon picker height', 'lc-tweaks' ), __( 'Set a taller icon picker list in builder option panels.', 'lc-tweaks' ) );
		?>
		<div class="dlck-hide">
			<?php dlck_divi_helpers_number( 'dlck_divi_vb_icon_picker_height', __( 'Icon picker height (px)', 'lc-tweaks' ), __( 'Applied to Divi icon selection panels.', 'lc-tweaks' ), 420, 120, 1200, 10 ); ?>
		</div>
		<?php
		dlck_divi_helpers_toggle( 'dlck_divi_vb_disable_inline_text_toolbar', __( 'Disable inline text toolbar', 'lc-tweaks' ), __( 'Hide the floating inline text editing toolbar when it gets in the way.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_vb_swap_global_saved_colors', __( 'Swap saved and global color buttons', 'lc-tweaks' ), __( 'Move saved colors ahead of global colors in compatible Divi color fields.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_vb_fullscreen', __( 'Disable block editor fullscreen', 'lc-tweaks' ), __( 'Disable fullscreen mode in the WordPress block editor when this is enabled.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_vb_hide_divi_builder_btn_ce', __( 'Hide Divi Builder button in Classic editor', 'lc-tweaks' ), __( 'Hide the “Use Divi” link when editing in the Classic editor screen.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_vb_hide_divi_builder_btn_be', __( 'Hide Divi Builder button in Block editor', 'lc-tweaks' ), __( 'Hide the Divi switch shown in the Block editor toolbar.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_vb_hide_editor_switch_buttons', __( 'Hide editor switch buttons', 'lc-tweaks' ), __( 'Hide editor toggle buttons for switching between Divi and Gutenberg.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_vb_hide_divi_cloud', __( 'Hide Divi Cloud actions', 'lc-tweaks' ), __( 'Hide Divi Cloud save/load upsell and action buttons in Visual Builder surfaces.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_vb_hide_layouts_btn', __( 'Hide Add Layouts button', 'lc-tweaks' ), __( 'Hide Divi Cloud layout modal upsell content in the Visual Builder.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_vb_full_width_post_content_rows', __( 'Full-width rows inside Post Content', 'lc-tweaks' ), __( 'Let rows nested in the Theme Builder Post Content module use the full content width.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_vb_hide_marketplace_layout_promo', __( 'Hide marketplace layout promos', 'lc-tweaks' ), __( 'Hide marketplace and promotional layout cards/buttons in builder library views.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_vb_hide_explore_modules', __( 'Hide Explore Modules buttons', 'lc-tweaks' ), __( 'Remove module-marketplace exploration links from builder module pickers.', 'lc-tweaks' ) );
		?>
	</div>

	<h2 class="tool-section"><?php esc_html_e( 'Granular Divi AI', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">
		<?php
		dlck_divi_helpers_toggle( 'dlck_divi_ai_hide_quick_sites', __( 'Hide AI Quick Sites', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_ai_hide_page', __( 'Hide AI page generation', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_ai_hide_section', __( 'Hide AI section generation', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_ai_hide_layout', __( 'Hide AI layout generation', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_ai_hide_text', __( 'Hide AI text actions', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_ai_hide_image', __( 'Hide AI image actions', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_ai_hide_code', __( 'Hide AI code actions', 'lc-tweaks' ) );
		?>
	</div>

	<h2 class="tool-section"><?php esc_html_e( 'Utility', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">
		<?php
		dlck_divi_helpers_toggle( 'dlck_remove_howdy', __( 'Remove Howdy from admin bar', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_admin_bar_frontend_hide', __( 'Hide admin bar on frontend', 'lc-tweaks' ), __( 'Hide the WordPress admin bar on the public site while keeping it available in wp-admin.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_admin_bar_hover', __( 'Reveal hidden admin bar on hover', 'lc-tweaks' ), __( 'When the frontend admin bar is hidden, reveal it when hovering near the top of the screen.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_disable_comment_settings', __( 'Disable comment settings (posts/pages)', 'lc-tweaks' ), __( 'Hide comments admin menu, remove post/page comments support, and hide comments in the admin bar.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_quick_links_enabled', __( 'Divi quick links', 'lc-tweaks' ), __( 'Add common Divi admin links to the WordPress admin bar.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_quick_links_in_builder', __( 'Divi quick links in builder', 'lc-tweaks' ), __( 'Add a compact quick-links button in Visual Builder views.', 'lc-tweaks' ) );
		dlck_divi_helpers_trigger_toggle( 'dlck_custom_quick_links_enabled', __( 'Custom quick links', 'lc-tweaks' ), __( 'Add your own admin-bar quick links. Use one Label | URL pair per line.', 'lc-tweaks' ) );
		?>
		<div class="dlck-hide">
			<?php
			dlck_divi_helpers_text( 'dlck_custom_quick_links_label', __( 'Custom quick links label', 'lc-tweaks' ), __( 'Admin-bar menu label.', 'lc-tweaks' ), __( 'Quick Links', 'lc-tweaks' ) );
			dlck_divi_helpers_textarea( 'dlck_custom_quick_links_items', __( 'Custom quick links', 'lc-tweaks' ), __( 'One link per line: Label | https://example.com/path. Relative admin URLs are supported.', 'lc-tweaks' ), '', 7 );
			dlck_divi_helpers_toggle( 'dlck_custom_quick_links_in_builder', __( 'Show custom quick links in builder', 'lc-tweaks' ) );
			?>
		</div>
		<?php
		dlck_divi_helpers_toggle( 'dlck_theme_builder_details_admin_bar', __( 'Theme Builder details in admin bar', 'lc-tweaks' ), __( 'Show quick Theme Builder links while viewing pages.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_theme_builder_details_builder_bar', __( 'Theme Builder details in builder', 'lc-tweaks' ), __( 'Show Theme Builder links in Visual Builder views.', 'lc-tweaks' ) );
		dlck_divi_helpers_trigger_toggle( 'dlck_environment_badge_enabled', __( 'Environment badge', 'lc-tweaks' ), __( 'Display a small admin-bar environment label for staging, production, or custom context.', 'lc-tweaks' ) );
		?>
		<div class="dlck-hide">
			<?php
			dlck_divi_helpers_select(
				'dlck_environment_badge_mode',
				__( 'Environment type', 'lc-tweaks' ),
				__( 'Used as the default badge label.', 'lc-tweaks' ),
				array(
					'custom'      => __( 'Custom', 'lc-tweaks' ),
					'development' => __( 'Development', 'lc-tweaks' ),
					'staging'     => __( 'Staging', 'lc-tweaks' ),
					'production'  => __( 'Production', 'lc-tweaks' ),
				),
				'custom'
			);
			dlck_divi_helpers_text( 'dlck_environment_badge_label', __( 'Custom badge label', 'lc-tweaks' ), __( 'Used when Environment type is Custom.', 'lc-tweaks' ), '' );
			?>
			<div class="lc-kit">
				<div class="box-title"><h3><?php esc_html_e( 'Badge colors', 'lc-tweaks' ); ?></h3></div>
				<div class="box-content">
					<?php
					dlck_divi_helpers_color( 'dlck_environment_badge_bg_color', __( 'Background', 'lc-tweaks' ), '#d63638' );
					dlck_divi_helpers_color( 'dlck_environment_badge_text_color', __( 'Text', 'lc-tweaks' ), '#ffffff' );
					?>
				</div>
			</div>
		</div>
		<?php
		dlck_divi_helpers_toggle( 'dlck_duplicate_posts_pages', __( 'Duplicate posts and pages', 'lc-tweaks' ), __( 'Add a duplicate row action to posts and pages.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_duplicate_divi_library_layouts', __( 'Duplicate Divi Library layouts', 'lc-tweaks' ), __( 'Add a duplicate row action to Divi Library layouts.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_library_shortcode_widget', __( 'Divi Library shortcode and widget', 'lc-tweaks' ), __( 'Enable [dlck_divi_layout id=\"123\"] and a simple Divi Library widget.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_shortcode_in_menus', __( 'Allow shortcodes in menus', 'lc-tweaks' ) );
		?>
	</div>

	<h2 class="tool-section"><?php esc_html_e( 'Library And Admin Lists', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">
		<?php
		dlck_divi_helpers_toggle( 'dlck_divi_library_view', __( 'Enhanced Divi Library preview/actions', 'lc-tweaks' ), __( 'Use the improved Divi Library front-end preview and row-action behavior.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_edit_in_visual_builder_link', __( 'Edit in Visual Builder row links', 'lc-tweaks' ), __( 'Keep the existing LC row action for pages and posts using Divi Builder.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_posts_builder_filter', __( 'Filter posts/pages by Divi Builder usage', 'lc-tweaks' ), __( 'Add Divi Builder filters and view counts to post/page admin lists.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_featured_image_admin_column', __( 'Featured image admin column', 'lc-tweaks' ), __( 'Add a thumbnail column to posts and pages.', 'lc-tweaks' ) );
		dlck_divi_helpers_trigger_toggle( 'dlck_default_featured_image_pages_enabled', __( 'Default featured image for pages', 'lc-tweaks' ), __( 'Fallback attachment ID when a page has no featured image.', 'lc-tweaks' ) );
		?>
		<div class="dlck-hide">
			<?php dlck_divi_helpers_number( 'dlck_default_featured_image_pages', __( 'Page fallback attachment ID', 'lc-tweaks' ), __( 'Enter a media attachment ID.', 'lc-tweaks' ), 0, 0, PHP_INT_MAX ); ?>
		</div>
		<?php
		dlck_divi_helpers_trigger_toggle( 'dlck_default_featured_image_posts_enabled', __( 'Default featured image for posts', 'lc-tweaks' ), __( 'Fallback attachment ID when a post has no featured image.', 'lc-tweaks' ) );
		?>
		<div class="dlck-hide">
			<?php dlck_divi_helpers_number( 'dlck_default_featured_image_posts', __( 'Post fallback attachment ID', 'lc-tweaks' ), __( 'Enter a media attachment ID.', 'lc-tweaks' ), 0, 0, PHP_INT_MAX ); ?>
		</div>
		<?php
		dlck_divi_helpers_toggle( 'dlck_divi_editor_back_links', __( 'Back links in Divi editors', 'lc-tweaks' ), __( 'Add a simple return link in Divi Library and classic edit screens.', 'lc-tweaks' ) );
			dlck_divi_helpers_toggle( 'dlck_admin_notes_enabled', __( 'Admin Notes', 'lc-tweaks' ), __( 'Add an internal note box to pages, posts, projects, and Divi Library layouts.', 'lc-tweaks' ) );
		?>
	</div>

	<h2 class="tool-section"><?php esc_html_e( 'Projects', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">
		<?php
		dlck_divi_helpers_trigger_toggle( 'dlck_divi_project_rename', __( 'Rename Divi Projects', 'lc-tweaks' ), __( 'Rename the Project post type and taxonomy labels without affecting post data.', 'lc-tweaks' ) );
		?>
		<div class="dlck-hide">
			<?php
			dlck_divi_helpers_text( 'dlck_divi_project_plural_name', __( 'Project plural label', 'lc-tweaks' ), __( 'Label used for the project menu and archive listings.', 'lc-tweaks' ), __( 'Projects', 'lc-tweaks' ) );
			dlck_divi_helpers_text( 'dlck_divi_project_singular_name', __( 'Project singular label', 'lc-tweaks' ), __( 'Single item label used in admin and UI.', 'lc-tweaks' ), __( 'Project', 'lc-tweaks' ) );
			dlck_divi_helpers_text( 'dlck_divi_project_slug', __( 'Project slug', 'lc-tweaks' ), __( 'Rewrite slug for project URLs.', 'lc-tweaks' ), __( 'projects', 'lc-tweaks' ) );

			dlck_divi_helpers_text( 'dlck_divi_project_plural_category', __( 'Project category plural label', 'lc-tweaks' ), __( 'Label used for project category archives and listings.', 'lc-tweaks' ), __( 'Project Categories', 'lc-tweaks' ) );
			dlck_divi_helpers_text( 'dlck_divi_project_singular_category', __( 'Project category singular label', 'lc-tweaks' ), __( 'Single category label used in taxonomy UI.', 'lc-tweaks' ), __( 'Project Category', 'lc-tweaks' ) );
			dlck_divi_helpers_text( 'dlck_divi_project_category_slug', __( 'Project category slug', 'lc-tweaks' ), __( 'Rewrite slug for the project category taxonomy.', 'lc-tweaks' ), __( 'project_category', 'lc-tweaks' ) );

			dlck_divi_helpers_text( 'dlck_divi_project_plural_tag', __( 'Project tag plural label', 'lc-tweaks' ), __( 'Label used for project tag archives and listings.', 'lc-tweaks' ), __( 'Project Tags', 'lc-tweaks' ) );
			dlck_divi_helpers_text( 'dlck_divi_project_singular_tag', __( 'Project tag singular label', 'lc-tweaks' ), __( 'Single tag label used in taxonomy UI.', 'lc-tweaks' ), __( 'Project Tag', 'lc-tweaks' ) );
			dlck_divi_helpers_text( 'dlck_divi_project_tag_slug', __( 'Project tag slug', 'lc-tweaks' ), __( 'Rewrite slug for the project tag taxonomy.', 'lc-tweaks' ), __( 'project_tag', 'lc-tweaks' ) );
			?>
		</div>
	</div>

	<h2 class="tool-section"><?php esc_html_e( 'Frontend', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">
		<?php dlck_divi_helpers_trigger_toggle( 'dlck_frontend_anchor_offset_enabled', __( 'Anchor link offset', 'lc-tweaks' ), __( 'Offset hash-link jumps below sticky headers.', 'lc-tweaks' ) ); ?>
		<div class="dlck-hide">
			<?php dlck_divi_helpers_number( 'dlck_frontend_anchor_offset_px', __( 'Anchor offset (px)', 'lc-tweaks' ), __( 'Scroll padding applied to hash links.', 'lc-tweaks' ), 90, 0, 600, 1 ); ?>
		</div>
		<?php dlck_divi_helpers_trigger_toggle( 'dlck_cursor_highlight_enabled', __( 'Cursor text highlight color', 'lc-tweaks' ), __( 'Customize the browser text selection color.', 'lc-tweaks' ) ); ?>
		<div class="dlck-hide">
			<div class="lc-kit">
				<div class="box-title"><h3><?php esc_html_e( 'Selection color', 'lc-tweaks' ); ?></h3></div>
				<div class="box-content"><?php dlck_divi_helpers_color( 'dlck_cursor_highlight_color', __( 'Highlight color', 'lc-tweaks' ), '#ffe45c' ); ?></div>
			</div>
		</div>
		<?php
		dlck_divi_helpers_toggle( 'dlck_disable_horizontal_scroll', __( 'Prevent horizontal scroll', 'lc-tweaks' ) );
		dlck_divi_helpers_trigger_toggle( 'dlck_custom_scrollbar_enabled', __( 'Custom scrollbar', 'lc-tweaks' ), __( 'Style desktop browser scrollbars.', 'lc-tweaks' ) );
		?>
		<div class="dlck-hide">
			<?php
			dlck_divi_helpers_number( 'dlck_custom_scrollbar_width', __( 'Scrollbar width (px)', 'lc-tweaks' ), __( 'Desktop scrollbar width.', 'lc-tweaks' ), 12, 4, 30, 1 );
			?>
			<div class="lc-kit">
				<div class="box-title"><h3><?php esc_html_e( 'Scrollbar colors', 'lc-tweaks' ); ?></h3></div>
				<div class="box-content">
					<?php
					dlck_divi_helpers_color( 'dlck_custom_scrollbar_track_color', __( 'Track', 'lc-tweaks' ), '#f1f1f1' );
					dlck_divi_helpers_color( 'dlck_custom_scrollbar_thumb_color', __( 'Thumb', 'lc-tweaks' ), '#777777' );
					dlck_divi_helpers_color( 'dlck_custom_scrollbar_thumb_hover_color', __( 'Thumb hover', 'lc-tweaks' ), '#444444' );
					?>
				</div>
			</div>
		</div>
		<?php dlck_divi_helpers_trigger_toggle( 'dlck_back_to_top_customizer_enabled', __( 'Custom back-to-top button', 'lc-tweaks' ), __( 'Add a lightweight scroll-to-top button.', 'lc-tweaks' ) ); ?>
		<div class="dlck-hide">
			<?php dlck_divi_helpers_number( 'dlck_back_to_top_size', __( 'Button size (px)', 'lc-tweaks' ), __( 'Square button size.', 'lc-tweaks' ), 44, 28, 90, 1 ); ?>
			<div class="lc-kit">
				<div class="box-title"><h3><?php esc_html_e( 'Button colors', 'lc-tweaks' ); ?></h3></div>
				<div class="box-content">
					<?php
					dlck_divi_helpers_color( 'dlck_back_to_top_bg_color', __( 'Background', 'lc-tweaks' ), '#111111' );
					dlck_divi_helpers_color( 'dlck_back_to_top_text_color', __( 'Icon', 'lc-tweaks' ), '#ffffff' );
					?>
				</div>
			</div>
		</div>
		<?php dlck_divi_helpers_trigger_toggle( 'dlck_text_replacement_enabled', __( 'Text replacement rules', 'lc-tweaks' ), __( 'Replace text in post content. Use one Search | Replace pair per line.', 'lc-tweaks' ) ); ?>
		<div class="dlck-hide">
			<?php dlck_divi_helpers_textarea( 'dlck_text_replacement_rules', __( 'Replacement rules', 'lc-tweaks' ), __( 'Example: Old phrase | New phrase', 'lc-tweaks' ), '', 7 ); ?>
		</div>
	</div>

	<h2 class="tool-section"><?php esc_html_e( 'Media', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">
		<?php dlck_divi_helpers_trigger_toggle( 'dlck_media_max_upload_size_enabled', __( 'Max upload size override', 'lc-tweaks' ), __( 'Raise or lower WordPress upload size limit for allowed users.', 'lc-tweaks' ) ); ?>
		<div class="dlck-hide">
			<?php dlck_divi_helpers_number( 'dlck_media_max_upload_size_kb', __( 'Max upload size (KB)', 'lc-tweaks' ), __( 'Example: 10240 for 10 MB.', 'lc-tweaks' ), 10240, 1, 1048576, 1 ); ?>
		</div>
		<?php
		dlck_divi_helpers_toggle( 'dlck_svg_img_class_enabled', __( 'Add class to SVG images', 'lc-tweaks' ), __( 'Adds style-svg to SVG image output for styling scripts and CSS.', 'lc-tweaks' ) );
		dlck_divi_helpers_trigger_toggle( 'dlck_media_filename_metadata_enabled', __( 'Generate attachment text from filenames', 'lc-tweaks' ), __( 'Populate title, alt, caption, and description from cleaned filenames.', 'lc-tweaks' ) );
		dlck_divi_helpers_toggle( 'dlck_divi_img_module', __( 'Image module metadata sync', 'lc-tweaks' ), __( 'Use the attachment library metadata to fill Divi image module alt and title attributes.', 'lc-tweaks' ) );
		?>
		<div class="dlck-hide">
			<?php dlck_divi_helpers_toggle( 'dlck_media_filename_metadata_override', __( 'Override existing attachment text', 'lc-tweaks' ), __( 'Replace existing title, alt, caption, and description values when attachment metadata is regenerated.', 'lc-tweaks' ) ); ?>
		</div>
	</div>

	<h2 class="tool-section"><?php esc_html_e( 'Maintenance', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">
		<?php
		dlck_divi_helpers_select(
			'dlck_divi_cache_auto_schedule',
			__( 'Scheduled Divi cache clear', 'lc-tweaks' ),
			__( 'Runs only when the main Clear Divi Cache helper is enabled.', 'lc-tweaks' ),
			array(
				'none'       => __( 'No schedule', 'lc-tweaks' ),
				'hourly'     => __( 'Hourly', 'lc-tweaks' ),
				'twicedaily' => __( 'Twice daily', 'lc-tweaks' ),
				'daily'      => __( 'Daily', 'lc-tweaks' ),
				'weekly'     => __( 'Weekly', 'lc-tweaks' ),
				'monthly'    => __( 'Monthly', 'lc-tweaks' ),
			),
			'none'
		);
		dlck_divi_helpers_toggle( 'dlck_divi_cache_builder_buttons', __( 'Divi Builder cache buttons', 'lc-tweaks' ), __( 'Show compact cache/local-storage buttons inside Visual Builder views.', 'lc-tweaks' ) );
		dlck_divi_helpers_select(
			'dlck_site_availability_mode',
			__( 'Site availability mode', 'lc-tweaks' ),
			__( 'Use a Divi Library layout as a coming-soon or maintenance screen.', 'lc-tweaks' ),
			array(
				'off'         => __( 'Off', 'lc-tweaks' ),
				'coming_soon' => __( 'Coming Soon (200)', 'lc-tweaks' ),
				'maintenance' => __( 'Maintenance (503)', 'lc-tweaks' ),
			),
			'off'
		);
		dlck_divi_helpers_select( 'dlck_site_availability_layout_id', __( 'Availability layout', 'lc-tweaks' ), __( 'Divi Library layout rendered for logged-out visitors.', 'lc-tweaks' ), $dlck_layout_options, '0' );
		dlck_divi_helpers_textarea( 'dlck_site_availability_excluded_paths', __( 'Excluded paths', 'lc-tweaks' ), __( 'One path per line. Supports * wildcards, for example /checkout or /preview/*.', 'lc-tweaks' ), '', 5 );
		dlck_divi_helpers_textarea( 'dlck_site_availability_allowed_ips', __( 'Allowed IPs', 'lc-tweaks' ), __( 'One IP address per line. Matching visitors bypass the availability screen.', 'lc-tweaks' ), '', 4 );
		dlck_divi_helpers_trigger_toggle( 'dlck_site_availability_bypass_enabled', __( 'Enable bypass link', 'lc-tweaks' ), __( 'Allows a private URL token to bypass the availability screen.', 'lc-tweaks' ) );
		?>
		<div class="dlck-hide">
			<?php dlck_divi_helpers_text( 'dlck_site_availability_bypass_token', __( 'Bypass token', 'lc-tweaks' ), __( 'Use a long random string. Visit any page with ?dlck_bypass=token.', 'lc-tweaks' ), wp_generate_password( 20, false, false ) ); ?>
		</div>
	</div>

	<?php
	if ( ! dlck_is_free_edition() ) {
		include DLCK_LC_KIT_PLUGIN_DIR . 'tools/pro-divi-helpers.php';
	}
	?>
</div>
