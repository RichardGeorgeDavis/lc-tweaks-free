<?php

$dlck_disable_plugin_check_val = dlck_get_option( 'dlck_disable_plugin_check' );
$dlck_fix_divi_flashing_content_val = dlck_get_option( 'dlck_fix_divi_flashing_content' );
$dlck_enable_divi_builder_by_default_val   = dlck_get_option( 'dlck_enable_divi_builder_by_default' );
$dlck_hide_gutenberg_std_editor_buttons_val = dlck_get_option( 'dlck_hide_gutenberg_std_editor_buttons' );
$dlck_divi_builder_quick_fixes_val         = dlck_get_option( 'dlck_divi_builder_quick_fixes' );
$dlck_deprecated_compat_messages = array(
	'dlck_fix_divi_flashing_content'         => function_exists( 'dlck_get_divi_option_compatibility_message' ) ? dlck_get_divi_option_compatibility_message( 'dlck_fix_divi_flashing_content' ) : '',
	'dlck_disable_plugin_check'              => function_exists( 'dlck_get_divi_option_compatibility_message' ) ? dlck_get_divi_option_compatibility_message( 'dlck_disable_plugin_check' ) : '',
	'dlck_enable_divi_builder_by_default'    => function_exists( 'dlck_get_divi_option_compatibility_message' ) ? dlck_get_divi_option_compatibility_message( 'dlck_enable_divi_builder_by_default' ) : '',
	'dlck_hide_gutenberg_std_editor_buttons' => function_exists( 'dlck_get_divi_option_compatibility_message' ) ? dlck_get_divi_option_compatibility_message( 'dlck_hide_gutenberg_std_editor_buttons' ) : '',
	'dlck_divi_builder_quick_fixes'          => function_exists( 'dlck_get_divi_option_compatibility_message' ) ? dlck_get_divi_option_compatibility_message( 'dlck_divi_builder_quick_fixes' ) : '',
);

?>
<div id="deprecated" class="tool <?php echo $active_tab === 'deprecated' ? 'tool-active' : ''; ?>">

	<div class="toolbox" style="padding:0 0 30px;">
		<div class="info" style="background:transparent;">
			<h4><?php esc_html_e('What are the deprecated functions?', 'divi-lc-kit'); ?></h4>
			<p><?php echo esc_html_e('On this page you\'ll  find a list of features, which are no longer required with the latest Divi version.', 'divi-lc-kit'); ?><br/>
			<p><?php echo esc_html_e('These features continue to work, but using the new Divi theme options to achieve the same results is recommended.', 'divi-lc-kit'); ?></p>
		</div>
	</div>		

	<?php if ( dlck_is_divi_theme_active() ) : ?>
		<h2 class="tool-section"><?php echo esc_html_e( 'Divi', 'divi-lc-kit' ); ?></h2>
		<div class="tool-wrap">

			<div class="lc-kit">
				<div class="box-title">
					<h3><span class="new divi4" data-tooltip="This option is designed for Divi 4. Divi 5 does not support this functionality.">Divi 4</span><?php echo esc_html_e( 'Fix Divi Flashing', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p>
							<?php echo esc_html_e( "Fix Divi Flashing Unstyled Content On Page Load - ensure to add 'elm.style.display' to WP Rocket's Excluded JavaScript Files", 'divi-lc-kit' ); ?>
						</p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_fix_divi_flashing_content" type="checkbox" value="1"
							<?php checked( '1', $dlck_fix_divi_flashing_content_val ); ?> />
					</div>
					<?php if ( $dlck_deprecated_compat_messages['dlck_fix_divi_flashing_content'] !== '' ) : ?>
						<p class="info"><?php echo esc_html( $dlck_deprecated_compat_messages['dlck_fix_divi_flashing_content'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><span class="new divi4" data-tooltip="This option is designed for Divi 4. Divi 5 does not support this functionality.">Divi 4</span><?php echo esc_html_e( 'Divi Disable Plugin Check', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p>
							<?php echo esc_html_e( 'Fix the Divi Builder timeout error.', 'divi-lc-kit' ); ?>
						</p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_disable_plugin_check" type="checkbox" value="1"
							<?php checked( '1', $dlck_disable_plugin_check_val ); ?> />
					</div>
					<?php if ( $dlck_deprecated_compat_messages['dlck_disable_plugin_check'] !== '' ) : ?>
						<p class="info"><?php echo esc_html( $dlck_deprecated_compat_messages['dlck_disable_plugin_check'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><span class="new divi4" data-tooltip="This option is designed for Divi 4. Divi 5 does not support this functionality.">Divi 4</span><?php echo esc_html_e( 'Enable Divi Builder by Default', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p>
							<?php echo esc_html_e( 'Enable Divi Builder by Default on New Posts / Pages.', 'divi-lc-kit' ); ?>
						</p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_enable_divi_builder_by_default" type="checkbox" value="1"
							<?php checked( '1', $dlck_enable_divi_builder_by_default_val ); ?> />
					</div>
					<?php if ( $dlck_deprecated_compat_messages['dlck_enable_divi_builder_by_default'] !== '' ) : ?>
						<p class="info"><?php echo esc_html( $dlck_deprecated_compat_messages['dlck_enable_divi_builder_by_default'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><span class="new divi4" data-tooltip="This option is designed for Divi 4. Divi 5 does not support this functionality.">Divi 4</span><?php echo esc_html_e( 'Hide The Gutenberg Editor Buttons', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p>
							<?php echo esc_html_e( 'Remove all of the Gutenberg and Classic Editor buttons so that you can always edit with the Divi Builder.', 'divi-lc-kit' ); ?>
						</p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_hide_gutenberg_std_editor_buttons" type="checkbox" value="1"
							<?php checked( '1', $dlck_hide_gutenberg_std_editor_buttons_val ); ?> />
					</div>
					<?php if ( $dlck_deprecated_compat_messages['dlck_hide_gutenberg_std_editor_buttons'] !== '' ) : ?>
						<p class="info"><?php echo esc_html( $dlck_deprecated_compat_messages['dlck_hide_gutenberg_std_editor_buttons'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><span class="new divi4" data-tooltip="This option is designed for Divi 4. Divi 5 does not support this functionality.">Divi 4</span><?php echo esc_html_e( 'Divi Builder Quick Fixes', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p>
							<?php echo esc_html_e( 'Enhancements include making text style options sticky within Divi Builder module text areas, increasing the default height of the Text (HTML) tab, expanding the height of the inner field settings modal, and enlarging the Divi icon picker area for a smoother editing experience.', 'divi-lc-kit' ); ?>
						</p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_divi_builder_quick_fixes" type="checkbox" value="1"
							<?php checked( '1', $dlck_divi_builder_quick_fixes_val ); ?> />
					</div>
					<?php if ( $dlck_deprecated_compat_messages['dlck_divi_builder_quick_fixes'] !== '' ) : ?>
						<p class="info"><?php echo esc_html( $dlck_deprecated_compat_messages['dlck_divi_builder_quick_fixes'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>

		</div>
	<?php endif; ?>

</div>
