<?php
include DLCK_LC_KIT_PLUGIN_DIR . '/tools/seo-schema-data.php';

?>
<div id="seo-schema" class="tool <?php echo $active_tab === 'seo-schema' ? 'tool-active' : ''; ?>">

	<div class="toolbox" style="padding:0 0 30px;">
		<div class="info" style="background:transparent;">
			<h4><?php echo esc_html_e( 'What is the SEO & Schema area?', 'lc-tweaks' ); ?></h4>
			<p><?php echo esc_html_e( 'Search, structured data, LLM discovery, and AI agent readiness controls for public content.', 'lc-tweaks' ); ?></p>
			<p><?php echo esc_html_e( 'These tools live separately from maintenance because they affect how crawlers, search engines, and agents understand the site.', 'lc-tweaks' ); ?></p>
		</div>
	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'SEO & Schema', 'lc-tweaks' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Enrich Rank Math Schema Graph', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Merge additional Organization, LocalBusiness, WebSite, WebPage, and Place fields into Rank Math JSON-LD for richer SEO and LLM context.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_rank_math_schema_enrichment" type="checkbox" value="1" <?php checked( '1', $dlck_rank_math_schema_enrichment_val ); ?> />
				</div>
			</div>
			<?php if ( $dlck_rank_math_active ) : ?>
				<a class="dlck-cust-link" href="<?php echo esc_attr( $dlck_rank_math_schema_settings_url ); ?>" target="_blank"><?php include DLCK_LC_KIT_PLUGIN_DIR . '/assets/img/gear-icon.php'; ?></a>
			<?php endif; ?>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title"></div>
				<div class="box-content dlck-rank-math-schema-panel">
					<div class="info">
						<p><?php echo esc_html_e( 'Requires Rank Math. LC Tweaks extends Rank Math’s existing graph instead of outputting a second competing schema block.', 'lc-tweaks' ); ?></p>
						<p><?php echo esc_html_e( 'Use the settings link for Rank Math’s own Local SEO Description, Social Profiles, and Additional Organization Info fields like founding date, employee count, website alternate name, and business identifiers. Leave LC Tweaks fields blank to keep Rank Math defaults.', 'lc-tweaks' ); ?></p>
						<?php if ( ! $dlck_rank_math_active ) : ?>
							<p><?php echo esc_html_e( 'Rank Math is not active on this site. These values can still be saved here, but they will not affect frontend schema until Rank Math is active again.', 'lc-tweaks' ); ?></p>
						<?php endif; ?>
					</div>

					<div class="info" style="margin-top:15px;">
						<h4><?php echo esc_html_e( 'Current Rank Math Local SEO', 'lc-tweaks' ); ?></h4>
						<p><?php echo esc_html_e( 'These values are read-only here and come directly from Rank Math. If anything below is wrong, update it in Rank Math. Use the LC Tweaks fields further down only for additional enrichment.', 'lc-tweaks' ); ?></p>
						<p><?php echo wp_kses_post( sprintf( __( '<strong>Open Rank Math:</strong> <a href="%1$s" target="_blank">Local SEO / Titles settings</a>', 'lc-tweaks' ), esc_url( $dlck_rank_math_schema_settings_url ) ) ); ?></p>
						<?php dlck_rank_math_seo_schema_render_summary_rows( $dlck_rank_math_local_summary_rows ); ?>
					</div>

					<div class="info" style="margin-top:15px;">
						<h4><?php echo esc_html_e( 'Current Rank Math LLMS Txt', 'lc-tweaks' ); ?></h4>
						<p><?php echo esc_html_e( 'These values are read-only here and come directly from Rank Math. Manage the module, post types, taxonomies, limits, and extra content in Rank Math.', 'lc-tweaks' ); ?></p>
						<p><?php echo wp_kses_post( sprintf( __( '<strong>Open Rank Math:</strong> <a href="%1$s" target="_blank">LLMS Txt settings</a> | <a href="%2$s" target="_blank">%3$s</a>', 'lc-tweaks' ), esc_url( $dlck_rank_math_llms_settings_url ), esc_url( $dlck_rank_math_llms_url ), esc_html( $dlck_rank_math_llms_url ) ) ); ?></p>
						<?php dlck_rank_math_seo_schema_render_summary_rows( $dlck_rank_math_llms_summary_rows ); ?>
					</div>

						<div class="info" style="margin-top:15px;">
							<h4><?php echo esc_html_e( 'Preview Final Schema', 'lc-tweaks' ); ?></h4>
							<p><?php echo esc_html_e( 'Fetch a frontend URL on this site and inspect the final emitted JSON-LD after Rank Math and LC Tweaks have both run. Use a relative path or a same-origin full URL.', 'lc-tweaks' ); ?></p>
							<div class="dlck-rank-math-preview-controls">
								<input type="text" id="dlck_rank_math_schema_preview_url" value="<?php echo esc_attr( home_url( '/' ) ); ?>" style="width:100%;" placeholder="https://example.com/" />
								<input type="text" id="dlck_rank_math_schema_preview_ignore_keys" value="" style="width:100%;" placeholder="Ignore summary keys: description, image, contactPoint" />
								<div class="dlck-rank-math-preview-filter-chips" aria-label="<?php echo esc_attr__( 'Common diff filter keys', 'lc-tweaks' ); ?>">
									<button type="button" class="button dlck-rank-math-filter-chip" data-key="description">description</button>
									<button type="button" class="button dlck-rank-math-filter-chip" data-key="image">image</button>
									<button type="button" class="button dlck-rank-math-filter-chip" data-key="contactPoint">contactPoint</button>
									<button type="button" class="button dlck-rank-math-filter-chip" data-key="sameAs">sameAs</button>
									<button type="button" class="button dlck-rank-math-filter-chip" data-key="url">url</button>
									<button type="button" class="button dlck-rank-math-filter-chip" data-key="dateModified">dateModified</button>
								</div>
								<div class="dlck-rank-math-preview-actions">
									<button type="button" class="dlck-settings-button" id="dlck_rank_math_schema_preview_run"><?php echo esc_html_e( 'Preview Final JSON-LD', 'lc-tweaks' ); ?></button>
									<button type="button" class="button" id="dlck_rank_math_schema_preview_copy_diff" style="display:none;"><?php echo esc_html_e( 'Copy Diff Summary', 'lc-tweaks' ); ?></button>
									<button type="button" class="button" id="dlck_rank_math_schema_preview_copy_report" style="display:none;"><?php echo esc_html_e( 'Copy URL + Diff', 'lc-tweaks' ); ?></button>
									<button type="button" class="button" id="dlck_rank_math_schema_preview_copy" style="display:none;"><?php echo esc_html_e( 'Copy Output', 'lc-tweaks' ); ?></button>
								</div>
								<p class="description"><?php echo esc_html_e( 'Optional: ignore comma-separated top-level keys in the diff summary only. The raw JSON-LD output below is never filtered.', 'lc-tweaks' ); ?></p>
								<p id="dlck_rank_math_schema_preview_status" class="description dlck-rank-math-preview-status"></p>
								<pre id="dlck_rank_math_schema_preview_diff" class="dlck-rank-math-json-preview dlck-rank-math-preview-diff" style="display:none;"></pre>
								<pre id="dlck_rank_math_schema_preview_output" class="dlck-rank-math-json-preview dlck-rank-math-preview-output" style="display:none;"></pre>
							</div>
					</div>

					<p><strong><?php echo esc_html_e( 'Knows About Topics', 'lc-tweaks' ); ?></strong></p>
					<textarea name="dlck_rank_math_schema_knows_about" rows="4" cols="60" style="width:100%;" placeholder="Industry Expertise&#10;Customer Support&#10;Product Development&#10;Operations"><?php echo esc_textarea( $dlck_rank_math_schema_knows_about_val ); ?></textarea>

					<p><strong><?php echo esc_html_e( 'Areas Served', 'lc-tweaks' ); ?></strong></p>
					<textarea name="dlck_rank_math_schema_area_served" rows="4" cols="60" style="width:100%;" placeholder="South Africa&#10;United States&#10;Europe&#10;Global"><?php echo esc_textarea( $dlck_rank_math_schema_area_served_val ); ?></textarea>

					<p><strong><?php echo esc_html_e( 'Founders / CEO', 'lc-tweaks' ); ?></strong></p>
					<textarea name="dlck_rank_math_schema_founders" rows="3" cols="60" style="width:100%;" placeholder="Jane Founder | Founder&#10;John Doe | CEO"><?php echo esc_textarea( $dlck_rank_math_schema_founders_val ); ?></textarea>

					<p><strong><?php echo esc_html_e( 'Employees / Team', 'lc-tweaks' ); ?></strong></p>
					<textarea name="dlck_rank_math_schema_employees" rows="5" cols="60" style="width:100%;" placeholder="Jane Doe | Senior Designer&#10;John Smith | Lead Developer"><?php echo esc_textarea( $dlck_rank_math_schema_employees_val ); ?></textarea>

					<p><strong><?php echo esc_html_e( 'Contact Languages', 'lc-tweaks' ); ?></strong></p>
					<textarea name="dlck_rank_math_schema_contact_languages" rows="4" cols="60" style="width:100%;" placeholder="en-ZA&#10;en-GB&#10;en-US"><?php echo esc_textarea( $dlck_rank_math_schema_contact_languages_val ); ?></textarea>
					<p class="description"><?php echo esc_html_e( 'Applies to the default support/contact point built from Rank Math\'s Contact Page. Use the field below for channel-specific contact methods.', 'lc-tweaks' ); ?></p>

					<p><strong><?php echo esc_html_e( 'Additional Contact Points', 'lc-tweaks' ); ?></strong></p>
					<textarea name="dlck_rank_math_schema_contact_points" rows="5" cols="60" style="width:100%;" placeholder="Customer Support | support@example.com | +27 11 555 1234 | https://example.com/contact/ | en-ZA,en-GB,en-US&#10;WhatsApp Support |  | +27 82 123 4567 | https://wa.me/27821234567 | en-ZA&#10;Licensing | licensing@example.com |  | https://example.com/licensing/ | en-US"><?php echo esc_textarea( $dlck_rank_math_schema_contact_points_val ); ?></textarea>
					<p class="description"><?php echo esc_html_e( 'Public-facing only. One per line in this format: Contact Type | Email | Telephone | URL | Languages. Use the URL column for WhatsApp, booking, sales, or support links. Use a distinct Contact Type per channel if you want separate schema entries.', 'lc-tweaks' ); ?></p>

					<p><strong><?php echo esc_html_e( 'Advanced JSON Merge', 'lc-tweaks' ); ?></strong></p>
					<pre class="dlck-rank-math-json-preview"><?php echo esc_html( $dlck_rank_math_schema_advanced_json_preview ); ?></pre>
					<p class="description"><?php echo esc_html_e( 'Read-only preview of the currently saved advanced merge JSON.', 'lc-tweaks' ); ?></p>
						<details class="dlck-rank-math-advanced-editor">
							<summary><?php echo esc_html_e( 'Edit Saved Advanced JSON', 'lc-tweaks' ); ?></summary>
							<p class="description"><?php echo esc_html_e( 'This raw editor is intentionally separate from the main schema fields. Use a top-level JSON object keyed by entity type. Invalid JSON, or top-level arrays, will be rejected on save and the previously saved value will be kept.', 'lc-tweaks' ); ?></p>
							<textarea name="dlck_rank_math_schema_advanced_json" rows="14" cols="60" style="width:100%;" placeholder="{&#10;  &quot;organization&quot;: {&#10;    &quot;location&quot;: [&#10;      {&#10;        &quot;@type&quot;: &quot;Place&quot;,&#10;        &quot;name&quot;: &quot;Cape Town Office&quot;&#10;      }&#10;    ]&#10;  },&#10;  &quot;localbusiness&quot;: {&#10;    &quot;areaServed&quot;: [&quot;South Africa&quot;, &quot;Europe&quot;]&#10;  },&#10;  &quot;place&quot;: {&#10;    &quot;publicAccess&quot;: true&#10;  },&#10;  &quot;webpage&quot;: {&#10;    &quot;speakable&quot;: {&#10;      &quot;@type&quot;: &quot;SpeakableSpecification&quot;&#10;    }&#10;  }&#10;}"><?php echo esc_textarea( $dlck_rank_math_schema_advanced_json_val ); ?></textarea>
						</details>
				</div>
			</div>
		</div>

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'AI Agent Readiness', 'lc-tweaks' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Serve public WordPress content in agent-friendly Markdown, publish Content Signals, and expose honest discovery hints without faking MCP, API, OAuth, or commerce capabilities.', 'lc-tweaks' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_agent_readiness_enabled" type="checkbox" value="1" <?php checked( '1', $dlck_agent_readiness_enabled_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title"></div>
				<div class="box-content dlck-rank-math-schema-panel dlck-agent-readiness-panel">
					<div class="info">
						<p><?php echo esc_html_e( 'This feature targets normal public content sites. It improves what agents can discover and read, but it does not publish placeholder protocol endpoints for capabilities the site does not actually provide.', 'lc-tweaks' ); ?></p>
						<p><?php echo esc_html_e( 'If this site already uses Cloudflare Pro, Business, or Enterprise, you can also enable Cloudflare Markdown for Agents at the edge. LC Tweaks keeps an origin-level WordPress fallback for non-Cloudflare sites and local testing.', 'lc-tweaks' ); ?></p>
					</div>

					<div class="dlck-agent-readiness-options">
						<label>
							<input type="checkbox" class="minicheckbox" name="dlck_agent_readiness_markdown_accept" value="1" <?php checked( '1', $dlck_agent_readiness_markdown_accept_val ); ?> />
							<?php esc_html_e( 'Serve Markdown for public pages that request Accept: text/markdown', 'lc-tweaks' ); ?>
						</label>
						<label>
							<input type="checkbox" class="minicheckbox" name="dlck_agent_readiness_index_md" value="1" <?php checked( '1', $dlck_agent_readiness_index_md_val ); ?> />
							<?php esc_html_e( 'Enable /index.md Markdown fallback URLs', 'lc-tweaks' ); ?>
						</label>
						<label>
							<input type="checkbox" class="minicheckbox" name="dlck_agent_readiness_exclude_woo" value="1" <?php checked( '1', $dlck_agent_readiness_exclude_woo_val ); ?> />
							<?php esc_html_e( 'Exclude WooCommerce products and store pages from Markdown and discovery links', 'lc-tweaks' ); ?>
						</label>
						<label>
							<input type="checkbox" class="minicheckbox" name="dlck_agent_readiness_woo_markdown" value="1" <?php checked( '1', $dlck_agent_readiness_woo_markdown_val ); ?> />
							<?php esc_html_e( 'Enhance included WooCommerce products and shop pages with product/catalog Markdown', 'lc-tweaks' ); ?>
						</label>
						<label>
							<input type="checkbox" class="minicheckbox" name="dlck_agent_readiness_robots_signals" value="1" <?php checked( '1', $dlck_agent_readiness_robots_signals_val ); ?> />
							<?php esc_html_e( 'Add Content Signals to WordPress virtual robots.txt', 'lc-tweaks' ); ?>
						</label>
						<label>
							<input type="checkbox" class="minicheckbox" name="dlck_agent_readiness_discovery_headers" value="1" <?php checked( '1', $dlck_agent_readiness_discovery_headers_val ); ?> />
							<?php esc_html_e( 'Add discovery Link headers and a Markdown alternate link for real resources', 'lc-tweaks' ); ?>
						</label>
						<label>
							<input type="checkbox" class="minicheckbox" name="dlck_agent_readiness_llms_enrichment" value="1" <?php checked( '1', $dlck_agent_readiness_llms_enrichment_val ); ?> />
							<?php esc_html_e( 'Add a concise AI Agent Readiness section to Rank Math llms.txt output', 'lc-tweaks' ); ?>
						</label>
					</div>

					<div class="info" style="margin-top:15px;">
						<h4><?php echo esc_html_e( 'Content Signals', 'lc-tweaks' ); ?></h4>
						<p><?php echo esc_html_e( 'Defaults allow search and real-time AI input while reserving rights against AI training. Set a signal to Unset only when the site owner has not chosen a preference for that use.', 'lc-tweaks' ); ?></p>
						<div class="dlck-agent-readiness-signal-grid">
							<label>
								<span><?php esc_html_e( 'Search', 'lc-tweaks' ); ?></span>
								<select name="dlck_agent_readiness_signal_search">
									<option value="yes" <?php selected( 'yes', $dlck_agent_readiness_signal_search_val ); ?>><?php esc_html_e( 'Yes', 'lc-tweaks' ); ?></option>
									<option value="no" <?php selected( 'no', $dlck_agent_readiness_signal_search_val ); ?>><?php esc_html_e( 'No', 'lc-tweaks' ); ?></option>
									<option value="unset" <?php selected( 'unset', $dlck_agent_readiness_signal_search_val ); ?>><?php esc_html_e( 'Unset', 'lc-tweaks' ); ?></option>
								</select>
							</label>
							<label>
								<span><?php esc_html_e( 'AI Input', 'lc-tweaks' ); ?></span>
								<select name="dlck_agent_readiness_signal_ai_input">
									<option value="yes" <?php selected( 'yes', $dlck_agent_readiness_signal_ai_input_val ); ?>><?php esc_html_e( 'Yes', 'lc-tweaks' ); ?></option>
									<option value="no" <?php selected( 'no', $dlck_agent_readiness_signal_ai_input_val ); ?>><?php esc_html_e( 'No', 'lc-tweaks' ); ?></option>
									<option value="unset" <?php selected( 'unset', $dlck_agent_readiness_signal_ai_input_val ); ?>><?php esc_html_e( 'Unset', 'lc-tweaks' ); ?></option>
								</select>
							</label>
							<label>
								<span><?php esc_html_e( 'AI Training', 'lc-tweaks' ); ?></span>
								<select name="dlck_agent_readiness_signal_ai_train">
									<option value="yes" <?php selected( 'yes', $dlck_agent_readiness_signal_ai_train_val ); ?>><?php esc_html_e( 'Yes', 'lc-tweaks' ); ?></option>
									<option value="no" <?php selected( 'no', $dlck_agent_readiness_signal_ai_train_val ); ?>><?php esc_html_e( 'No', 'lc-tweaks' ); ?></option>
									<option value="unset" <?php selected( 'unset', $dlck_agent_readiness_signal_ai_train_val ); ?>><?php esc_html_e( 'Unset', 'lc-tweaks' ); ?></option>
								</select>
							</label>
						</div>
						<p class="description"><?php echo esc_html( sprintf( __( 'Current header/directive value: %s', 'lc-tweaks' ), $dlck_agent_readiness_content_signal !== '' ? $dlck_agent_readiness_content_signal : __( 'none', 'lc-tweaks' ) ) ); ?></p>
					</div>

					<div class="info" style="margin-top:15px;">
						<h4><?php echo esc_html_e( 'Diagnostics', 'lc-tweaks' ); ?></h4>
						<?php dlck_rank_math_seo_schema_render_summary_rows( $dlck_agent_readiness_diagnostic_rows ); ?>
					</div>

					<div class="info" style="margin-top:15px;">
						<h4><?php echo esc_html_e( 'Cached Homepage Link Header Fallback', 'lc-tweaks' ); ?></h4>
						<p><?php echo esc_html_e( 'Some page caches and CDNs serve homepage HTML without PHP-generated Link response headers. This optional Apache block adds the same discovery header at .htaccess level for the homepage only.', 'lc-tweaks' ); ?></p>
						<p><?php echo esc_html( sprintf( __( 'Target file: %s', 'lc-tweaks' ), $dlck_agent_readiness_htaccess_path ) ); ?></p>
						<div class="dlck-agent-readiness-actions">
							<?php if ( $dlck_agent_readiness_htaccess_writable ) : ?>
								<a class="dlck-settings-button" href="<?php echo esc_url( $dlck_agent_readiness_htaccess_install_url ); ?>"><?php echo esc_html( $dlck_agent_readiness_htaccess_installed ? __( 'Refresh .htaccess Block', 'lc-tweaks' ) : __( 'Add .htaccess Block', 'lc-tweaks' ) ); ?></a>
							<?php else : ?>
								<button type="button" class="dlck-settings-button" disabled><?php echo esc_html_e( '.htaccess Not Writable', 'lc-tweaks' ); ?></button>
							<?php endif; ?>
							<?php if ( $dlck_agent_readiness_htaccess_installed ) : ?>
								<a class="button" href="<?php echo esc_url( $dlck_agent_readiness_htaccess_remove_url ); ?>"><?php echo esc_html_e( 'Remove .htaccess Block', 'lc-tweaks' ); ?></a>
							<?php endif; ?>
						</div>
						<p class="description"><?php echo esc_html_e( 'If the button is unavailable, copy this block into the site root .htaccess file, then clear page cache, object cache, and CDN cache before retesting Link headers.', 'lc-tweaks' ); ?></p>
						<pre class="dlck-agent-readiness-command dlck-agent-readiness-snippet"><?php echo esc_html( $dlck_agent_readiness_htaccess_snippet ); ?></pre>
					</div>

					<div class="info" style="margin-top:15px;">
						<h4><?php echo esc_html_e( 'Copyable Test Commands', 'lc-tweaks' ); ?></h4>
						<pre class="dlck-agent-readiness-command">curl -I <?php echo esc_html( $dlck_agent_readiness_home_url ); ?></pre>
						<pre class="dlck-agent-readiness-command">curl -H "Accept: text/markdown" <?php echo esc_html( $dlck_agent_readiness_home_url ); ?></pre>
						<pre class="dlck-agent-readiness-command">curl <?php echo esc_html( $dlck_agent_readiness_index_md_url ); ?></pre>
						<pre class="dlck-agent-readiness-command">curl <?php echo esc_html( $dlck_agent_readiness_robots_url ); ?></pre>
						<pre class="dlck-agent-readiness-command"><?php echo esc_html( $dlck_agent_readiness_link_header_command ); ?></pre>
					</div>
				</div>
			</div>
		</div>

	</div>


</div>
