<div id="settings" class="tool <?php echo $active_tab == 'settings' ? 'tool-active' : ''; ?>">
	
<?php $dlck_uninstall_data = dlck_get_option('dlck_uninstall_data'); ?>
<?php
$dlck_all_settings = function_exists( 'dlck_get_effective_lc_kit_settings' )
	? dlck_get_effective_lc_kit_settings()
	: maybe_unserialize( get_option( 'dlck_lc_kit' ) );
if ( ! is_array( $dlck_all_settings ) ) {
	$dlck_all_settings = array();
}
$dlck_preflight_conflicts = function_exists( 'dlck_get_preflight_conflicts' ) ? dlck_get_preflight_conflicts( $dlck_all_settings ) : array();
$dlck_settings_snapshots  = function_exists( 'dlck_get_settings_snapshots' ) ? dlck_get_settings_snapshots() : array();
$dlck_settings_presets    = function_exists( 'dlck_get_settings_presets' ) ? dlck_get_settings_presets() : array();
if ( ! is_array( $dlck_settings_presets ) ) {
	$dlck_settings_presets = array();
}
$dlck_last_applied_preset = sanitize_key( (string) get_option( 'dlck_last_applied_preset', '' ) );
$dlck_selected_preset_key = '';
if ( $dlck_last_applied_preset !== '' && isset( $dlck_settings_presets[ $dlck_last_applied_preset ] ) ) {
	$dlck_selected_preset_key = $dlck_last_applied_preset;
}
if ( isset( $_POST['dlck_preset_key'] ) ) {
	$posted_preset = sanitize_key( wp_unslash( $_POST['dlck_preset_key'] ) );
	if ( isset( $dlck_settings_presets[ $posted_preset ] ) ) {
		$dlck_selected_preset_key = $posted_preset;
	}
}
$dlck_preset_restore_payload     = function_exists( 'dlck_get_preset_restore_payload' ) ? dlck_get_preset_restore_payload() : array();
$dlck_has_preset_restore_payload = array_key_exists( 'settings', $dlck_preset_restore_payload ) && is_array( $dlck_preset_restore_payload['settings'] );
$dlck_preset_restore_label       = $dlck_has_preset_restore_payload ? (string) ( $dlck_preset_restore_payload['preset_label'] ?? '' ) : '';
$dlck_preset_restore_created     = $dlck_has_preset_restore_payload ? (int) ( $dlck_preset_restore_payload['created'] ?? 0 ) : 0;
$dlck_scope_rules_enabled = dlck_get_option( 'dlck_scope_rules_enabled' );
$dlck_scope_rules_options = (string) dlck_get_option( 'dlck_scope_rules_options', '' );
$dlck_scope_rules_logged_state = sanitize_key( (string) dlck_get_option( 'dlck_scope_rules_logged_state', 'all' ) );
if ( ! in_array( $dlck_scope_rules_logged_state, array( 'all', 'logged_in', 'logged_out' ), true ) ) {
	$dlck_scope_rules_logged_state = 'all';
}
$dlck_scope_rules_roles         = (string) dlck_get_option( 'dlck_scope_rules_roles', '' );
$dlck_scope_rules_include_paths = (string) dlck_get_option( 'dlck_scope_rules_include_paths', '' );
$dlck_scope_rules_exclude_paths = (string) dlck_get_option( 'dlck_scope_rules_exclude_paths', '' );
$dlck_scope_known_options       = function_exists( 'dlck_get_registered_option_keys' ) ? dlck_get_registered_option_keys() : array();
if ( ! is_array( $dlck_scope_known_options ) ) {
	$dlck_scope_known_options = array();
}
$dlck_is_multisite                    = is_multisite();
$dlck_can_manage_network_policy       = $dlck_is_multisite && current_user_can( 'manage_network_options' );
$dlck_multisite_policy                = function_exists( 'dlck_get_multisite_policy' ) ? dlck_get_multisite_policy() : array();
$dlck_multisite_default_settings      = function_exists( 'dlck_get_multisite_default_settings' ) ? dlck_get_multisite_default_settings() : array();
$dlck_multisite_policy_enabled        = isset( $dlck_multisite_policy['enabled'] ) ? (string) $dlck_multisite_policy['enabled'] : '0';
$dlck_multisite_allow_site_overrides  = isset( $dlck_multisite_policy['allow_site_overrides'] ) ? (string) $dlck_multisite_policy['allow_site_overrides'] : '1';
$dlck_multisite_policy_updated        = isset( $dlck_multisite_policy['updated'] ) ? (int) $dlck_multisite_policy['updated'] : 0;
$dlck_multisite_policy_source_blog_id = isset( $dlck_multisite_policy['source_blog_id'] ) ? (int) $dlck_multisite_policy['source_blog_id'] : 0;
$dlck_scope_rules_first_option  = '';
$dlck_scope_rules_option_lines  = preg_split( '/\r\n|\r|\n/', $dlck_scope_rules_options );
if ( is_array( $dlck_scope_rules_option_lines ) ) {
	foreach ( $dlck_scope_rules_option_lines as $line ) {
		$line = sanitize_key( trim( (string) $line ) );
		if ( strpos( $line, 'dlck_' ) === 0 ) {
			$dlck_scope_rules_first_option = $line;
			break;
		}
	}
}
?>

<div class="sett-wrap">

	<div class="tool-wrap dlck-scope-rules-card">
		<div class="lc-kit">
			<div class="box-title">
				<h3><?php esc_html_e( 'Scope Rules', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php esc_html_e( 'Limit selected tweaks to specific URLs, login state, and user roles on frontend requests.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content">
				<p>
					<label>
						<input type="checkbox" name="dlck_scope_rules_enabled" value="1" <?php checked( '1', $dlck_scope_rules_enabled ); ?> />
						<?php esc_html_e( 'Enable scope rules', 'divi-lc-kit' ); ?>
					</label>
				</p>
				<div id="dlck_scope_rules_options_wrap" class="dlck-scope-rules-options-wrap" <?php if ( '1' !== (string) $dlck_scope_rules_enabled ) : ?>style="display:none;"<?php endif; ?>>
					<p>
						<label for="dlck_scope_rules_options"><?php esc_html_e( 'Target option keys (one per line)', 'divi-lc-kit' ); ?></label>
						<textarea name="dlck_scope_rules_options" id="dlck_scope_rules_options" rows="6" cols="60" placeholder="dlck_divi_lazy_loading&#10;dlck_divi_lazy_defer_sections&#10;dlck_remove_woo_all_files"><?php echo esc_textarea( $dlck_scope_rules_options ); ?></textarea>
					</p>
					<p>
						<label for="dlck_scope_rules_logged_state"><?php esc_html_e( 'Apply only for', 'divi-lc-kit' ); ?></label>
						<select name="dlck_scope_rules_logged_state" id="dlck_scope_rules_logged_state">
							<option value="all" <?php selected( 'all', $dlck_scope_rules_logged_state ); ?>><?php esc_html_e( 'All visitors', 'divi-lc-kit' ); ?></option>
							<option value="logged_in" <?php selected( 'logged_in', $dlck_scope_rules_logged_state ); ?>><?php esc_html_e( 'Logged-in users only', 'divi-lc-kit' ); ?></option>
							<option value="logged_out" <?php selected( 'logged_out', $dlck_scope_rules_logged_state ); ?>><?php esc_html_e( 'Logged-out users only', 'divi-lc-kit' ); ?></option>
						</select>
					</p>
					<p>
						<label for="dlck_scope_rules_roles"><?php esc_html_e( 'Roles (one per line, optional)', 'divi-lc-kit' ); ?></label>
						<textarea name="dlck_scope_rules_roles" id="dlck_scope_rules_roles" rows="4" cols="60" placeholder="administrator&#10;editor"><?php echo esc_textarea( $dlck_scope_rules_roles ); ?></textarea>
					</p>
					<p>
						<label for="dlck_scope_rules_include_paths"><?php esc_html_e( 'Include paths (one per line, optional)', 'divi-lc-kit' ); ?></label>
						<textarea name="dlck_scope_rules_include_paths" id="dlck_scope_rules_include_paths" rows="4" cols="60" placeholder="/shop/*&#10;/landing-page"><?php echo esc_textarea( $dlck_scope_rules_include_paths ); ?></textarea>
					</p>
					<p>
						<label for="dlck_scope_rules_exclude_paths"><?php esc_html_e( 'Exclude paths (one per line, optional)', 'divi-lc-kit' ); ?></label>
						<textarea name="dlck_scope_rules_exclude_paths" id="dlck_scope_rules_exclude_paths" rows="4" cols="60" placeholder="/checkout&#10;/cart"><?php echo esc_textarea( $dlck_scope_rules_exclude_paths ); ?></textarea>
					</p>
					<p class="description"><?php esc_html_e( 'Wildcard tips: use * as a path wildcard (for example /shop/*). Exclude paths take priority when both lists match.', 'divi-lc-kit' ); ?></p>
					<div id="dlck_scope_rules_live_validation" class="dlck-scope-live-validation" aria-live="polite"></div>
					<div class="dlck-scope-rules-tester">
						<h4><?php esc_html_e( 'Scope Rules Tester', 'divi-lc-kit' ); ?></h4>
						<p class="description"><?php esc_html_e( 'Simulate a frontend request and check whether a target option would run.', 'divi-lc-kit' ); ?></p>
						<p>
							<label for="dlck_scope_test_option"><?php esc_html_e( 'Option key', 'divi-lc-kit' ); ?></label>
							<input type="text" id="dlck_scope_test_option" list="dlck_scope_known_options_list" value="<?php echo esc_attr( $dlck_scope_rules_first_option ); ?>" placeholder="dlck_remove_woo_all_files" />
							<datalist id="dlck_scope_known_options_list">
								<?php foreach ( $dlck_scope_known_options as $dlck_scope_known_option ) : ?>
									<?php if ( is_string( $dlck_scope_known_option ) && strpos( $dlck_scope_known_option, 'dlck_' ) === 0 ) : ?>
										<option value="<?php echo esc_attr( $dlck_scope_known_option ); ?>"></option>
									<?php endif; ?>
								<?php endforeach; ?>
							</datalist>
						</p>
						<p>
							<label for="dlck_scope_test_path"><?php esc_html_e( 'Request path', 'divi-lc-kit' ); ?></label>
							<input type="text" id="dlck_scope_test_path" value="/" placeholder="/shop/sale-item" />
							<button type="button" class="button dlck-scope-path-helper" id="dlck_scope_test_use_current_path"><?php esc_html_e( 'Use Current Browser Path', 'divi-lc-kit' ); ?></button>
						</p>
						<p>
							<label for="dlck_scope_test_user_state"><?php esc_html_e( 'User state', 'divi-lc-kit' ); ?></label>
							<select id="dlck_scope_test_user_state">
								<option value="logged_out"><?php esc_html_e( 'Logged out', 'divi-lc-kit' ); ?></option>
								<option value="logged_in"><?php esc_html_e( 'Logged in', 'divi-lc-kit' ); ?></option>
							</select>
						</p>
						<p>
							<label for="dlck_scope_test_roles"><?php esc_html_e( 'User roles (optional, one per line)', 'divi-lc-kit' ); ?></label>
							<textarea id="dlck_scope_test_roles" rows="3" cols="60" placeholder="administrator&#10;editor"></textarea>
						</p>
						<p>
							<button type="button" class="dlck-settings-button" id="dlck_scope_test_run"><?php esc_html_e( 'Run Scope Test', 'divi-lc-kit' ); ?></button>
						</p>
						<div id="dlck_scope_test_result" class="dlck-scope-test-result" aria-live="polite"></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php if ( $dlck_is_multisite ) : ?>
	<div class="tool-wrap">
		<div class="lc-kit">
			<div class="box-title">
				<h3><?php esc_html_e( 'Multisite Policy Mode', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php esc_html_e( 'Set network defaults for LC Tweaks and optionally allow per-site overrides.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content">
				<p>
					<strong><?php esc_html_e( 'Current status:', 'divi-lc-kit' ); ?></strong>
					<?php
					if ( $dlck_multisite_policy_enabled === '1' ) {
						echo esc_html__( 'Enabled', 'divi-lc-kit' );
						echo ' | ';
						echo ( $dlck_multisite_allow_site_overrides === '1' )
							? esc_html__( 'Per-site overrides allowed', 'divi-lc-kit' )
							: esc_html__( 'Per-site overrides locked', 'divi-lc-kit' );
					} else {
						echo esc_html__( 'Disabled', 'divi-lc-kit' );
					}
					?>
				</p>
				<p>
					<?php
					/* translators: %1$d defaults count, %2$s source site id */
					echo esc_html(
						sprintf(
							__( 'Network defaults: %1$d keys | Source site ID: %2$s', 'divi-lc-kit' ),
							count( $dlck_multisite_default_settings ),
							$dlck_multisite_policy_source_blog_id > 0 ? (string) $dlck_multisite_policy_source_blog_id : '-'
						)
					);
					?>
				</p>
				<?php if ( $dlck_multisite_policy_updated > 0 ) : ?>
					<p class="description">
						<?php
						/* translators: %s: date/time */
						echo esc_html( sprintf( __( 'Last policy update: %s', 'divi-lc-kit' ), wp_date( 'Y-m-d H:i:s', $dlck_multisite_policy_updated ) ) );
						?>
					</p>
				<?php endif; ?>

				<?php if ( $dlck_can_manage_network_policy ) : ?>
					<p>
						<label>
							<input type="checkbox" name="dlck_network_policy_enabled" value="1" <?php checked( '1', $dlck_multisite_policy_enabled ); ?> />
							<?php esc_html_e( 'Enable network policy mode', 'divi-lc-kit' ); ?>
						</label>
					</p>
					<p>
						<label>
							<input type="checkbox" name="dlck_network_policy_allow_site_overrides" value="1" <?php checked( '1', $dlck_multisite_allow_site_overrides ); ?> />
							<?php esc_html_e( 'Allow per-site overrides', 'divi-lc-kit' ); ?>
						</label>
					</p>
					<p>
						<label>
							<input type="checkbox" name="dlck_network_policy_sync_defaults" value="1" />
							<?php esc_html_e( 'Sync network defaults from this site\'s current settings', 'divi-lc-kit' ); ?>
						</label>
					</p>
					<p>
						<label>
							<input type="checkbox" name="dlck_network_policy_clear_local_overrides" value="1" />
							<?php esc_html_e( 'Clear this site\'s local overrides after saving policy', 'divi-lc-kit' ); ?>
						</label>
					</p>
					<p class="description"><?php esc_html_e( 'Tip: keep "Allow per-site overrides" off to enforce network defaults on all sites.', 'divi-lc-kit' ); ?></p>

					<input type="hidden" name="dlck_network_policy_action" value="dlck_save_network_policy" />
					<?php wp_nonce_field( 'dlck_save_network_policy_nonce', 'dlck_network_policy_nonce' ); ?>
					<?php submit_button( __( 'Save Multisite Policy', 'divi-lc-kit' ), 'dlck-settings-button', 'dlck_network_policy_submit', false, array( 'id' => 'dlck_network_policy_submit' ) ); ?>
				<?php else : ?>
					<p class="description"><?php esc_html_e( 'Only network administrators can modify this policy. Site admins can view the active status above.', 'divi-lc-kit' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<div class="tool-wrap">
		<div class="lc-kit">
			<div class="box-title">
				<h3><?php esc_html_e( 'Presets', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php esc_html_e( 'Apply curated bundles of settings for common site goals. Only preset-managed options are updated.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content">
				<?php if ( empty( $dlck_settings_presets ) ) : ?>
					<p><?php esc_html_e( 'No presets are currently available.', 'divi-lc-kit' ); ?></p>
				<?php else : ?>
					<p>
						<label for="dlck_preset_key"><?php esc_html_e( 'Choose preset', 'divi-lc-kit' ); ?></label>
						<select name="dlck_preset_key" id="dlck_preset_key">
							<option value="" <?php selected( '', $dlck_selected_preset_key ); ?>><?php esc_html_e( 'Select a preset', 'divi-lc-kit' ); ?></option>
							<?php foreach ( $dlck_settings_presets as $preset_id => $preset_data ) : ?>
								<?php $preset_label = isset( $preset_data['label'] ) ? (string) $preset_data['label'] : (string) $preset_id; ?>
								<option value="<?php echo esc_attr( $preset_id ); ?>" <?php selected( $dlck_selected_preset_key, (string) $preset_id ); ?>><?php echo esc_html( $preset_label ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<ul class="dlck-preset-list">
						<?php foreach ( $dlck_settings_presets as $preset_id => $preset_data ) : ?>
							<?php
							$preset_label       = isset( $preset_data['label'] ) ? (string) $preset_data['label'] : (string) $preset_id;
							$preset_description = isset( $preset_data['description'] ) ? (string) $preset_data['description'] : '';
							?>
							<li>
								<strong><?php echo esc_html( $preset_label ); ?></strong>
								<?php if ( $preset_description !== '' ) : ?>
									<span><?php echo esc_html( $preset_description ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
					<input type="hidden" name="dlck_apply_preset_action" value="dlck_apply_preset" />
					<?php wp_nonce_field( 'dlck_apply_preset_nonce', 'dlck_apply_preset_nonce' ); ?>
					<?php
					$dlck_apply_preset_button_args = array( 'id' => 'dlck_apply_preset_submit' );
					if ( $dlck_selected_preset_key === '' ) {
						$dlck_apply_preset_button_args['disabled'] = 'disabled';
					}
					?>
					<?php submit_button( __( 'Apply Preset', 'divi-lc-kit' ), 'dlck-settings-button', 'dlck_apply_preset_submit', false, $dlck_apply_preset_button_args ); ?>
					<?php if ( $dlck_has_preset_restore_payload ) : ?>
						<?php
						$dlck_restore_info_parts = array();
						if ( $dlck_preset_restore_label !== '' ) {
							/* translators: %s: preset label */
							$dlck_restore_info_parts[] = sprintf( __( 'last preset: %s', 'divi-lc-kit' ), $dlck_preset_restore_label );
						}
						if ( $dlck_preset_restore_created > 0 ) {
							/* translators: %s: date/time */
							$dlck_restore_info_parts[] = sprintf( __( 'backup created: %s', 'divi-lc-kit' ), wp_date( 'Y-m-d H:i:s', $dlck_preset_restore_created ) );
						}
						?>
						<?php if ( ! empty( $dlck_restore_info_parts ) ) : ?>
							<p class="description"><?php echo esc_html( implode( ' | ', $dlck_restore_info_parts ) ); ?></p>
						<?php endif; ?>
						<input type="hidden" name="dlck_restore_preset_action" value="dlck_restore_preset" />
						<?php wp_nonce_field( 'dlck_restore_preset_nonce', 'dlck_restore_preset_nonce' ); ?>
						<?php submit_button( __( 'Restore Previous Settings', 'divi-lc-kit' ), 'dlck-settings-button', 'dlck_restore_preset_submit', false, array( 'id' => 'dlck_restore_preset_submit' ) ); ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="tool-wrap">
		<div class="lc-kit">
			<div class="box-title">
				<h3><?php esc_html_e( 'Preflight Check', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php esc_html_e( 'Quick conflict scan for settings that depend on each other or are mutually exclusive.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content">
				<?php if ( empty( $dlck_preflight_conflicts ) ) : ?>
					<p><?php esc_html_e( 'No conflicts detected in current settings.', 'divi-lc-kit' ); ?></p>
				<?php else : ?>
					<ul style="list-style:disc;padding-left:20px;">
						<?php foreach ( $dlck_preflight_conflicts as $message ) : ?>
							<li><?php echo esc_html( $message ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="tool-wrap">
		<div class="lc-kit">
			<div class="box-title">
				<h3><?php esc_html_e( 'Snapshots & Rollback', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php esc_html_e( 'LC Tweaks stores up to 5 snapshots of previous settings so you can quickly roll back after changes.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content">
				<?php if ( empty( $dlck_settings_snapshots ) ) : ?>
					<p><?php esc_html_e( 'No snapshots available yet. Save settings once to create the first snapshot.', 'divi-lc-kit' ); ?></p>
				<?php else : ?>
					<p><?php esc_html_e( 'Latest 5 snapshots are kept automatically.', 'divi-lc-kit' ); ?></p>
					<select name="dlck_snapshot_id" id="dlck_snapshot_id">
						<?php foreach ( $dlck_settings_snapshots as $index => $snapshot ) : ?>
							<?php
							$snapshot_id   = isset( $snapshot['id'] ) ? (string) $snapshot['id'] : '';
							$created       = isset( $snapshot['created'] ) ? (int) $snapshot['created'] : 0;
							$user_login    = ! empty( $snapshot['user_login'] ) ? (string) $snapshot['user_login'] : __( 'unknown user', 'divi-lc-kit' );
							$reason        = function_exists( 'dlck_snapshot_reason_label' ) ? dlck_snapshot_reason_label( (string) ( $snapshot['reason'] ?? 'manual_save' ) ) : (string) ( $snapshot['reason'] ?? 'manual_save' );
							$option_count  = isset( $snapshot['settings'] ) && is_array( $snapshot['settings'] ) ? count( $snapshot['settings'] ) : 0;
							$created_label = $created > 0 ? wp_date( 'Y-m-d H:i:s', $created ) : __( 'unknown time', 'divi-lc-kit' );
							/* translators: 1: snapshot time, 2: reason, 3: user login, 4: option count */
							$label = sprintf( __( '%1$s | %2$s | by %3$s | %4$d options', 'divi-lc-kit' ), $created_label, $reason, $user_login, $option_count );
							?>
							<option value="<?php echo esc_attr( $snapshot_id ); ?>" <?php selected( 0, (int) $index ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<input type="hidden" name="dlck_restore_snapshot_action" value="dlck_restore_snapshot" />
					<?php wp_nonce_field( 'dlck_restore_snapshot_nonce', 'dlck_restore_snapshot_nonce' ); ?>
					<?php submit_button( __( 'Restore Selected Snapshot', 'divi-lc-kit' ), 'dlck-settings-button', 'dlck_restore_snapshot_submit', false ); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="tool-wrap">
		<div class="lc-kit">
			<div class="box-title">
				<h3><?php esc_html_e( 'Export', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php esc_html_e( 'Choose which settings you’d like to include in the export file.', 'divi-lc-kit' ); ?></p>
					<p><?php esc_html_e( 'Customizer options are legacy data from Divi LC Kit and may be empty on newer installs.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox" id="dlck_export_box">
				<div>
					<input type="checkbox" class="minicheckbox" name="dlck_import_type_settings" id="dlck_import_type_settings" checked>
					<label class="minicheckbox" for="dlck_import_type_settings"><?php esc_html_e( 'LC Tweaks Settings', 'divi-lc-kit' ); ?></label>
				</div>
				<div>
					<input type="checkbox" class="minicheckbox" name="dlck_import_type_customizer" id="dlck_import_type_customizer" checked>
					<label class="minicheckbox" for="dlck_import_type_customizer"><?php esc_html_e( 'LC Tweaks Customizer Options (legacy Divi LC Kit)', 'divi-lc-kit' ); ?></label>
					<input type="hidden" name="dlck_export_settings_action" value="dlck_export_settings" />
				</div>
				<div>
					<?php wp_nonce_field( 'dlck_export_settings_nonce', 'dlck_export_settings_nonce' ); ?>
					<?php submit_button( __( 'Export', 'divi-lc-kit' ), 'dlck-settings-button', 'dlck_export_submit', false ); ?>
				</div>
			</div>
		</div>
	</div>

	<div class="tool-wrap">
		<div class="lc-kit">
			<div class="box-title">
				<h3><?php esc_html_e( 'Diagnostics', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr"><p><?php esc_html_e( 'Download a support diagnostics report with active options, cache status, and key environment details.', 'divi-lc-kit' ); ?></p></div>
			</div>
			<div class="box-content minibox" id="dlck_diagnostics_box">
				<input type="hidden" name="dlck_export_diagnostics_action" value="dlck_export_diagnostics" />
				<?php wp_nonce_field( 'dlck_export_diagnostics_nonce', 'dlck_export_diagnostics_nonce' ); ?>
				<?php submit_button( __( 'Download Diagnostics', 'divi-lc-kit' ), 'dlck-settings-button', 'dlck_diagnostics_submit', false ); ?>
			</div>
		</div>
	</div>

	<div class="tool-wrap">
		<div class="lc-kit">
			<div class="box-title">
				<h3><?php esc_html_e( 'Import', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr"><p><?php esc_html_e( 'Choose the file to import LC Tweaks settings. Export your current settings first if you want a backup.', 'divi-lc-kit' ); ?></p></div>
			</div>
			<div class="box-content minibox" id="dlck_import_box">
				<input type="file" name="import_file" />
				<input type="hidden" name="dlck_import_settings_action" value="dlck_import_settings" />
				<?php wp_nonce_field( 'dlck_import_settings_nonce', 'dlck_import_settings_nonce' ); ?>
				<?php submit_button( __( 'Import', 'divi-lc-kit' ), 'dlck-settings-button', 'dlck_import_submit', false ); ?>
			</div>
		</div>
	</div>

	<div class="tool-wrap">
		<div class="lc-kit">
			<div class="box-title">
				<h3><?php esc_html_e('CSS/JS Cache', 'divi-lc-kit'); ?></h3>
				<div class="box-descr"><p><?php esc_html_e('Remove cached LC Tweaks CSS/JS files generated from active options.', 'divi-lc-kit') ?></p></div>
			</div>
			<div class="box-content minibox">
				<button type="button" class="dlck-settings-button" id="dlck-clear-cache">
					<?php esc_html_e( 'Clear LC Tweaks Cache' , 'divi-lc-kit') ?>
				</button>
				<div id="dlck-clear-cache-result" class="dlck-clear-cache-result" style="display:none;"></div>
			</div>
		</div>
	</div>
	
</div>

<h2 class="tool-section"><?php esc_html_e('Uninstalling the plugin', 'divi-lc-kit'); ?></h2>
<div class="tool-wrap">	
	<div class="lc-kit trigger">
		<div class="box-title">
			<h3><?php esc_html_e('Remove Plugin Data', 'divi-lc-kit'); ?></h3>	
			
					
			<div class="box-descr"><p><?php esc_html_e("Enable this option if you'd like to remove all custom settings and plugin data when deleting LC Tweaks in the Plugins dashboard.", "divi-lc-kit"); ?></p></div>			
		</div>
		<div class="box-content minibox">
			<div class="checkbox">
				<input name="dlck_uninstall_data" type="checkbox" value="1" <?php checked( '1', esc_attr($dlck_uninstall_data) ); ?> />
			</div>
		</div>
	</div>
</div>




</div>
<?php

?>
