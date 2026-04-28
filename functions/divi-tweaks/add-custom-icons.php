<?php
/**
 * Add custom icons for Divi modules.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'dlck_divi_custom_icons_register' );
add_action( 'wp_head', 'dlck_divi_custom_icons_shared_css' );
add_action( 'wp_head', 'dlck_divi_custom_icons_button_css' );
add_action( 'wp_head', 'dlck_divi_custom_icons_icon_picker_css' );
add_action( 'admin_head', 'dlck_divi_custom_icons_icon_picker_css' );
add_action( 'wp_footer', 'dlck_divi_custom_icons_shared_js', 20 );

add_filter( 'the_content', 'dlck_divi_custom_icons_migrate_content' );
add_filter( 'content_edit_pre', 'dlck_divi_custom_icons_migrate_content' );
add_filter( 'et_fb_load_raw_post_content', 'dlck_divi_custom_icons_migrate_content' );
add_filter( 'db_filter_et_pb_layout', 'dlck_divi_custom_icons_migrate_content' );

add_filter( 'et_pb_toggle_shortcode_output', 'dlck_divi_custom_icons_add_toggle_data_attributes', 10, 3 );
add_filter( 'dbdb_get_extended_font_icon_symbols', 'dlck_divi_custom_icons_extend_icon_symbols' );

if ( ! function_exists( 'et_pb_get_extended_font_icon_symbols' ) ) {
	function et_pb_get_extended_font_icon_symbols() {
		$cache_key = 'et_pb_get_extended_font_icon_symbols';
		if ( function_exists( 'et_core_cache_has' ) && et_core_cache_has( $cache_key ) ) {
			return et_core_cache_get( $cache_key );
		}

		$full_icons_list_path = dlck_divi_custom_icons_path();
		if ( $full_icons_list_path && file_exists( $full_icons_list_path ) ) {
			// phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file read.
			$icons_data = json_decode( file_get_contents( $full_icons_list_path ), true );
			// phpcs:enable
			if ( JSON_ERROR_NONE === json_last_error() ) {
				$icons_data = apply_filters( 'dbdb_get_extended_font_icon_symbols', $icons_data );
				if ( function_exists( 'et_core_cache_set' ) ) {
					et_core_cache_set( $cache_key, $icons_data );
				}
				return $icons_data;
			}
		}

		if ( function_exists( 'et_wrong' ) ) {
			et_wrong( 'Problem with loading the icon data on this path: ' . $full_icons_list_path );
		}

		return array();
	}
}

if ( ! function_exists( 'dlck_divi_custom_icons_path' ) ) {
	function dlck_divi_custom_icons_path() {
		$plugin_active = ( defined( 'ET_BUILDER_PLUGIN_ACTIVE' ) && ET_BUILDER_PLUGIN_ACTIVE );
		if ( $plugin_active && defined( 'WP_PLUGIN_DIR' ) ) {
			return WP_PLUGIN_DIR . '/divi-builder/includes/builder/feature/icon-manager/full_icons_list.json';
		}
		if ( function_exists( 'get_template_directory' ) ) {
			return get_template_directory() . '/includes/builder/feature/icon-manager/full_icons_list.json';
		}
		return false;
	}
}

function dlck_divi_custom_icons_get_urls(): array {
	if ( ! function_exists( 'dlck_get_option' ) ) {
		return array();
	}
	$urls = dlck_get_option( 'dlck_divi_custom_icon_urls', array() );
	if ( ! is_array( $urls ) ) {
		return array();
	}
	$filtered = array();
	foreach ( $urls as $key => $url ) {
		if ( ! is_numeric( $key ) || $url === '' ) {
			continue;
		}
		$filtered[ (int) $key ] = esc_url_raw( $url );
	}
	return $filtered;
}

function dlck_divi_custom_icons_register(): void {
	$icons = dlck_divi_custom_icons_get_urls();
	if ( empty( $icons ) ) {
		return;
	}
	foreach ( $icons as $id => $url ) {
		$icon = new DLCK_Divi_Custom_Icon( $id, $url );
		$icon->init();
	}
}

function dlck_divi_custom_icons_shared_css(): void {
	if ( empty( dlck_divi_custom_icons_get_urls() ) ) {
		return;
	}
	?>
	<style>
		/* Custom icons */
		.db-custom-icon {
			line-height: unset !important;
		}

		.db-custom-icon img {
			height: 1em;
		}

		.et_pb_blurb_position_left .db-custom-icon,
		.et_pb_blurb_position_right .db-custom-icon {
			width: 1em;
			display: block;
		}

		.et_pb_blurb_position_left .dbdb-custom-icon-img,
		.et_pb_blurb_position_right .dbdb-custom-icon-img {
			height: auto;
			vertical-align: top;
		}

		/* Custom button icons */
		.et_pb_custom_button_icon[data-icon^="wtfdivi014-url"]:before,
		.et_pb_custom_button_icon[data-icon^="wtfdivi014-url"]:after,
		.db-custom-extended-icon:before,
		.db-custom-extended-icon:after {
			background-size: auto 1em;
			background-repeat: no-repeat;
			min-width: 20em;
			height: 100%;
			content: "" !important;
			position: absolute;
			top: 0;
		}

		.et_pb_custom_button_icon[data-icon^="wtfdivi014-url"]:before,
		.et_pb_custom_button_icon[data-icon^="wtfdivi014-url"]:after {
			background-position: left center;
		}

		.et_pb_custom_button_icon[data-icon^="wtfdivi014-url"],
		.db-custom-extended-icon {
			overflow: hidden;
		}

		.db-custom-extended-icon:before {
			left: 0;
			background-position: 2em;
		}

		.db-custom-extended-icon:after {
			right: 0;
			background-position: right 0.7em center;
		}

		.dbdb-icon-on-hover-off .db-custom-extended-icon:after {
			transition: none !important;
		}

		/* Inline icons */
		.et_pb_posts .et_pb_inline_icon[data-icon^="wtfdivi014-url"]:before,
		.et_pb_portfolio_item .et_pb_inline_icon[data-icon^="wtfdivi014-url"]:before {
			content: "" !important;
			-webkit-transition: all 0.4s;
			-moz-transition: all 0.4s;
			transition: all 0.4s;
		}

		.et_pb_posts .entry-featured-image-url:hover .et_pb_inline_icon[data-icon^="wtfdivi014-url"] img,
		.et_pb_portfolio_item .et_portfolio_image:hover .et_pb_inline_icon[data-icon^="wtfdivi014-url"] img {
			margin-top: 0px;
			transition: all 0.4s;
		}

		.et_pb_posts .entry-featured-image-url .et_pb_inline_icon[data-icon^="wtfdivi014-url"] img,
		.et_pb_portfolio_item .et_portfolio_image .et_pb_inline_icon[data-icon^="wtfdivi014-url"] img {
			margin-top: 14px;
		}

		/* Custom hover icons */
		.db014_custom_hover_icon {
			width: auto !important;
			max-width: 32px !important;
			min-width: 0 !important;
			height: auto !important;
			max-height: 32px !important;
			min-height: 0 !important;
			position: absolute;
			top: 50%;
			left: 50%;
			-webkit-transform: translate(-50%, -50%);
			-moz-transform: translate(-50%, -50%);
			-ms-transform: translate(-50%, -50%);
			transform: translate(-50%, -50%);
		}

		.et_pb_dmb_breadcrumbs a:first-child .db014_custom_hover_icon,
		.et_pb_dmb_breadcrumbs li .db014_custom_hover_icon {
			position: relative !important;
			left: 0%;
			transform: none;
			vertical-align: middle;
			margin-right: 8px;
		}

		.et_pb_dmb_breadcrumbs li .db014_custom_hover_icon {
			margin-left: 4px;
		}

		.et_pb_fullwidth_portfolio .et_overlay .db014_custom_hover_icon {
			top: 45%;
			-webkit-transition: all .3s;
			transition: all .3s;
		}

		.et_pb_fullwidth_portfolio .et_pb_portfolio_image:hover .et_overlay .db014_custom_hover_icon {
			top: 33%;
		}

		/* Hide extra icons */
		.et_pb_gallery .et_pb_gallery_image .et_pb_inline_icon[data-icon^="wtfdivi014-url"]:before,
		.et_pb_blog_grid .et_pb_inline_icon[data-icon^="wtfdivi014-url"]:before,
		.et_pb_image .et_pb_image_wrap .et_pb_inline_icon[data-icon^="wtfdivi014-url"]:before,
		.et_pb_dmb_breadcrumbs ol>li>a:first-child[data-icon^="wtfdivi014-url"]:before,
		.et_pb_dmb_breadcrumbs ol>li[data-icon^="wtfdivi014-url"]:before,
		.et_pb_module.et_pb_dmb_breadcrumbs li.db014_breadcrumb_with_custom_icon:before,
		.et_pb_module.et_pb_dmb_breadcrumbs a.db014_breadcrumb_with_custom_icon:before {
			display: none !important;
		}

		span.db-custom-icon {
			color: rgba(0, 0, 0, 0) !important;
		}

		.db-custom-icon:before,
		.db-custom-icon:after {
			content: "" !important;
		}

		/* Override styles added by customizer button section */
		.et_button_no_icon .db-custom-extended-icon.et_pb_button:after {
			display: inline-block;
		}

		.et_button_no_icon .et_pb_module:not(.dbdb-has-custom-padding) .db-custom-extended-icon.et_pb_button:hover {
			padding: .3em 2em .3em .7em !important;
		}

		/* === Custom toggle icons === */
		.et_pb_toggle .db014_custom_toggle_icon,
		.et_pb_toggle .db014_custom_toggle_icon_open {
			position: absolute;
			right: 0;
			top: 50%;
			transform: translateY(-50%);
			height: auto;
		}

		.et_pb_toggle.et_pb_toggle_close .db014_custom_toggle_icon_open {
			display: none;
		}

		.et_pb_toggle.et_pb_toggle_open .db014_custom_toggle_icon {
			display: none;
		}

		/* === Custom toggle icons height === */
		.et_pb_toggle .et_pb_toggle_title.db-custom-icon {
			display: flex;
			align-items: center;
		}

		.et_pb_toggle .et_pb_toggle_title.db-custom-icon::before {
			position: relative !important;
			/* Make the title get its height from the icon */
			margin-top: 0 !important;
			right: 0 !important;
			order: 2;
			/* Place after the title text */
			visibility: hidden;
		}

		.et_pb_toggle img.db014_custom_toggle_icon,
		.et_pb_toggle img.db014_custom_toggle_icon_open {
			height: 100%;
			/* Make the icon take up the full height of the title */
		}
	</style>
	<?php
}

function dlck_divi_custom_icons_button_css(): void {
	$icons = dlck_divi_custom_icons_get_urls();
	if ( empty( $icons ) ) {
		return;
	}
	foreach ( $icons as $id => $url ) {
		$icon_id   = 'wtfdivi014-url' . $id;
		$unicode   = '&#x' . ( 800 + $id ) . ';';
		$bg_img    = empty( $url ) ? 'none' : "url('" . esc_url( $url ) . "')";
		$icon      = '.et_pb_custom_button_icon[data-icon="' . esc_html( $icon_id ) . '"]';
		$unicode_char = html_entity_decode( $unicode, ENT_QUOTES, 'UTF-8' );
		$extended_icon = '.et_pb_button[data-icon="' . esc_html( $unicode_char ) . '"]';
		?>
		<style>
			<?php echo esc_html( $icon ); ?>:before,
			<?php echo esc_html( $icon ); ?>:after,
			<?php echo esc_html( $extended_icon ); ?>:before,
			<?php echo esc_html( $extended_icon ); ?>:after {
				background-image: <?php echo esc_html( $bg_img ); ?>;
			}
		</style>
		<?php
		if ( preg_match( '#\.svg(\?[^.]*)?$#', $url ) ) {
			?>
			<style>
				body.ie <?php echo esc_html( $icon ); ?>:before,
				body.ie <?php echo esc_html( $icon ); ?>:after,
				body.ie <?php echo esc_html( $extended_icon ); ?>:before,
				body.ie <?php echo esc_html( $extended_icon ); ?>:after {
					background-size: 1em 50%;
				}
			</style>
			<?php
		}
	}
}

function dlck_divi_custom_icons_icon_picker_css(): void {
	$icons = dlck_divi_custom_icons_get_urls();
	if ( empty( $icons ) ) {
		return;
	}
	foreach ( $icons as $id => $url ) {
		$icon_id     = 'wtfdivi014-url' . $id;
		$utf         = '&#x' . ( 800 + $id ) . ';';
		$unicode_val = '\\' . ( 800 + $id );
		?>
		<style>
			#et-fb-icon_picker li[data-icon-utf="<?php echo esc_attr( $utf ); ?>"]:after,
			#et-fb-scroll_down_icon li[data-icon="<?php echo esc_attr( $unicode_val ); ?>"]:after,
			.et-fb-option--select-icon li[data-icon="<?php echo esc_attr( $icon_id ); ?>"]:after,
			.et-pb-option--select_icon li[data-icon="<?php echo esc_attr( $icon_id ); ?>"]:before,
			.et-pb-option ul.et_font_icon li[data-icon="<?php echo esc_attr( $icon_id ); ?>"]::before {
				background: url("<?php echo esc_url( $url ); ?>") no-repeat center center;
				background-size: cover;
				content: "a" !important;
				width: 16px !important;
				height: 16px !important;
				color: rgba(0, 0, 0, 0) !important;
				filter: drop-shadow(0px 0px 1px #111111);
			}
		</style>
		<?php
	}
}

function dlck_divi_custom_icons_shared_js(): void {
	$icons = dlck_divi_custom_icons_get_urls();
	if ( empty( $icons ) ) {
		return;
	}
	$custom_icon_classes = apply_filters( 'dlck_custom_icon_classes', array( 'et-pb-icon' ) );
	$custom_icon_classes = array_map(
		static function ( $class ) {
			return '.' . esc_html( $class );
		},
		$custom_icon_classes
	);
	$custom_icon_classes_selector = implode( ',', $custom_icon_classes );

	$custom_inline_icon_classes = apply_filters( 'dlck_custom_inline_icon_classes', array( 'et_pb_inline_icon' ) );
	$custom_inline_icon_classes = array_map(
		static function ( $class ) {
			return '.' . esc_html( $class );
		},
		$custom_inline_icon_classes
	);
	$custom_inline_icon_classes_selector = implode( ',', $custom_inline_icon_classes );

	$custom_toggle_icon_classes = apply_filters( 'dlck_custom_toggle_icon_classes', array( 'et_pb_toggle_title' ) );
	$custom_toggle_icon_classes = array_map(
		static function ( $class ) {
			return '.' . esc_html( $class );
		},
		$custom_toggle_icon_classes
	);
	$custom_toggle_icon_classes_selector = implode( ',', $custom_toggle_icon_classes );

	$custom_icon_classes_regex = array_map(
		static function ( $class ) {
			return ltrim( $class, '.' );
		},
		$custom_icon_classes
	);
	$custom_icon_classes_regex = implode( '|', $custom_icon_classes_regex );

	$icon_data = array();
	foreach ( $icons as $id => $url ) {
		$entity = '&#x' . ( 800 + $id ) . ';';
		$icon_data[] = array(
			'id'      => 'wtfdivi014-url' . $id,
			'url'     => $url,
			'unicode' => html_entity_decode( $entity, ENT_QUOTES, 'UTF-8' ),
			'entity'  => $entity,
			'entity_weight' => $entity . '||divi||400',
		);
	}
	?>
	<script data-name="dlck-custom-icons">
		jQuery(function($) {
			function dlck_icon_matches(value, icon) {
				if (!value) {
					return false;
				}
				var raw = value;
				if (typeof raw !== 'string') {
					raw = String(raw);
				}
				var normalized = raw.toLowerCase();
				var entity = icon.entity.toLowerCase();
				var entityWeight = icon.entity_weight.toLowerCase();
				var iconId = icon.id.toLowerCase();
				if (
					raw === icon.id ||
					raw === icon.unicode ||
					raw === icon.entity ||
					raw === icon.entity_weight
				) {
					return true;
				}
				if (normalized.indexOf(entity) === 0 || normalized.indexOf(entityWeight) === 0) {
					return true;
				}
				if (normalized.indexOf(iconId) === 0) {
					return true;
				}
				return false;
			}

			function dlck_update_icon(icon) {
				dlck_update_icons($(document), icon);
				var $app_frame = $("#et-fb-app-frame");
				if ($app_frame.length) {
					dlck_update_icons($app_frame.contents(), icon);
				}
			}

			function dlck_update_icons(doc, icon) {
				dlck_update_custom_icons(doc, icon);
				dlck_update_custom_inline_icons(doc, icon);
				dlck_update_custom_toggle_icons(doc, icon);
				dlck_update_custom_toggle_icons_open(doc, icon);
			}

			function dlck_update_custom_icons(doc, icon) {
				var $custom_icons = doc.find(<?php echo wp_json_encode( $custom_icon_classes_selector ); ?>).filter(function() {
					var $icon = $(this);
					var text = $icon.text().trim();
					var dataIcon = $icon.attr('data-icon') || '';
					return dlck_icon_matches(text, icon) || dlck_icon_matches(dataIcon, icon);
				});
				var icon_visible = (icon.url !== '');
				$custom_icons.addClass('db-custom-icon');
				$custom_icons.html('<img class="dbdb-custom-icon-img" src="' + icon.url + '" />');
				$custom_icons.toggle(icon_visible);
			}

			function dlck_update_custom_inline_icons(doc, icon) {
				var $custom_inline_icons = doc.find(<?php echo wp_json_encode( $custom_inline_icon_classes_selector ); ?>).filter(function() {
					return dlck_icon_matches($(this).attr('data-icon'), icon);
				});
				var icon_visible = (icon.url !== '');
				var $icons_inline = $custom_inline_icons.filter(function() {
					return dlck_icon_matches($(this).attr('data-icon'), icon);
				});
				$icons_inline.addClass('db-custom-icon');
				$icons_inline.each(function() {
					var $this = $(this);
					if ($this.children('.db014_custom_hover_icon').length === 0) {
						if ($this.closest('.et_pb_dmb_breadcrumbs').length === 0) {
							$this.html('<img class="db014_custom_hover_icon" />');
						} else {
							$this.prepend($('<img class="db014_custom_hover_icon" />'));
							$this.addClass('db014_breadcrumb_with_custom_icon');
						}
					}
					$this.children('.db014_custom_hover_icon').attr('src', icon.url);
				});
				$icons_inline.toggle(icon_visible);
			}

			function dlck_update_custom_toggle_icons(doc, icon) {
				var $custom_toggle_icons = doc.find(<?php echo wp_json_encode( $custom_toggle_icon_classes_selector ); ?>).filter(function() {
					return dlck_icon_matches($(this).attr('data-icon'), icon);
				});
				var icon_visible = (icon.url !== '');
				var $icons_inline = $custom_toggle_icons.filter(function() {
					return dlck_icon_matches($(this).attr('data-icon'), icon);
				});

				$icons_inline.addClass('db-custom-icon');
				$icons_inline.each(function() {
					var $this = $(this);
					if ($this.children('.db014_custom_toggle_icon').length === 0) {
						$this.append('<img class="db014_custom_toggle_icon" />');
					}
					$this.children('.db014_custom_toggle_icon').attr('src', icon.url);
				});
				$icons_inline.toggle(icon_visible);
			}

			function dlck_update_custom_toggle_icons_open(doc, icon) {
				var $custom_toggle_icons = doc.find(<?php echo wp_json_encode( $custom_toggle_icon_classes_selector ); ?>).filter(function() {
					return dlck_icon_matches($(this).attr('data-icon-open'), icon);
				});
				var icon_visible = (icon.url !== '');
				var $icons_inline = $custom_toggle_icons.filter(function() {
					return dlck_icon_matches($(this).attr('data-icon-open'), icon);
				});

				$icons_inline.addClass('db-custom-icon');
				$icons_inline.each(function() {
					var $this = $(this);
					if ($this.children('.db014_custom_toggle_icon_open').length === 0) {
						$this.append('<img class="db014_custom_toggle_icon_open" />');
					}
					$this.children('.db014_custom_toggle_icon_open').attr('src', icon.url);
				});
				$icons_inline.toggle(icon_visible);
			}

			function dlck_update_all_icons() {
				var icons = <?php echo wp_json_encode( $icon_data ); ?>;
				icons.forEach(function(icon) {
					$('.et_pb_button').filter(function() {
						return dlck_icon_matches($(this).attr('data-icon'), icon);
					}).addClass('db-custom-extended-icon');
					dlck_update_icon(icon);
				});

				$('.dbdb-icon-on-left.dbdb-icon-on-hover-off .db-custom-extended-icon').each(function() {
					add_padding_to_icon(this, 'left', false);
				});
				$('.dbdb-icon-on-left.dbdb-icon-on-hover .db-custom-extended-icon:hover').each(function() {
					add_padding_to_icon(this, 'left', true);
				});
				$('.dbdb-icon-on-right.dbdb-icon-on-hover-off .db-custom-extended-icon').each(function() {
					add_padding_to_icon(this, 'right', false);
				});
				$('.dbdb-icon-on-right.dbdb-icon-on-hover .db-custom-extended-icon:hover').each(function() {
					add_padding_to_icon(this, 'right', true);
				});
			}

			function add_padding_to_icon(button, side, hoverOnly) {
				var $button = $(button);
				var icon = window.getComputedStyle($button[0], (side === 'left') ? '::before' : '::after');
				if (typeof window.Image === 'function') {
					var img = new Image();
					img.src = icon.getPropertyValue('background-image').replace(/^url\(["']?/, '').replace(/["']?\)$/, '');
					img.onload = function() {
						set_padding_css($button, icon_padding(this), side);
						if (hoverOnly) {
							$button.hover(
								function() {
									set_padding_css($button, icon_padding(this), side);
								},
								function() {
									setTimeout(function() {
										set_padding_css($button, '1em', side);
									}, 100);
								}
							);
						}
					};
				}
			}

			function icon_padding(icon) {
				var icon_standard_padding_in_em = 1.3;
				var icon_rendered_height_in_em = 1;
				return icon_standard_padding_in_em + (icon.width / icon.height) * icon_rendered_height_in_em + 'em';
			}

			function set_padding_css($button, padding, side) {
				$button.css('padding-' + side, padding);
			}

			if (typeof et_fb_enabled !== 'function' || !et_fb_enabled()) {
				setTimeout(function() {
					dlck_update_all_icons();
				}, 100);
			}
			$(document).on('dlck_custom_icons_updated', function() {
				dlck_update_all_icons();
			});

			$(document).on('mouseenter mouseleave', '.et_multi_view__hover_selector', function() {
				dlck_update_all_icons();
			});

			$(document).on('mouseleave', '#et-main-area', function() {
				setTimeout(function() {
					dlck_update_all_icons();
				}, 0);
			});

			dlck_watch_for_changes_that_might_update_icons();

			function dlck_watch_for_changes_that_might_update_icons() {
				if (window.top === window.self) {
					$(document).on(
						'mouseup touchend',
						'#et-fb-icon_picker li, #et-fb-scroll_down_icon li',
						function () {
							setTimeout(function() {
								var $app_frame = $("#et-fb-app-frame");
								if ($app_frame.length) {
									$app_frame.contents().find('.db-custom-icon:not(:has(.dbdb-custom-icon-img))').removeClass('db-custom-icon');
									$app_frame.contents().find('img.db014_custom_hover_icon').remove();
									$app_frame.contents().find('.db-custom-extended-icon').removeClass('db-custom-extended-icon');
								}
								$(document).trigger('dlck_custom_icons_updated');
							}, 0);
						}
					);
				}

				var observer = new MutationObserver(function(mutations) {
					mutations.forEach(function(mutation) {
						if (mutation.type === 'childList') {
							if (mutation.addedNodes.length > 0) {
								if (dlck_may_contain_icons(mutation.target)) {
									if (mutation.addedNodes.length === 1) {
										var classes = mutation.addedNodes[0].classList;
										var ignore = [
											'et-pb-draggable-spacing',
											'et-pb-draggable-spacing__tooltip',
											'et-fb-column-divider',
											'et-fb-no-children',
											'et-fb-row--no-module',
											'et_pb_column_empty',
											'et-pb-draggable-spacing__outer-margin-root',
											'et_pb_column',
											'db014_custom_hover_icon'
										];
										for (var i = 0; i < ignore.length; i++) {
											if (classes.contains(ignore[i])) {
												return;
											}
										}
									}

									var node_added = false;
									mutation.addedNodes.forEach(function(node) {
										if (node.nodeType === Node.ELEMENT_NODE) {
											node_added = true;
										}
									});
									if (!node_added) {
										return;
									}
									$(document).trigger('dlck_custom_icons_updated');
								}
							}
						} else if (mutation.type === 'attributes') {
							if (dlck_may_contain_icons(mutation.target)) {
								$(document).trigger('dlck_custom_icons_updated');
							}
						}
					});
				});

				var fb_app = document.getElementById('et-fb-app');
				if (fb_app) {
					observer.observe(
						fb_app,
						{
							attributes: true,
							attributeFilter: ['class'],
							childList: true,
							characterData: false,
							subtree: true
						}
					);
				}
			}

			function dlck_may_contain_icons(target) {
				if (target.className === undefined) {
					return false;
				}
				var classes = target.className;
				if (classes.search === undefined) {
					return false;
				}
				if (classes.search(/(<?php echo esc_html( $custom_icon_classes_regex ); ?>|et_pb_inline_icon|et-fb-post-content|et_pb_section|et_pb_row|et_pb_column)/i) !== -1) {
					return true;
				}
				return false;
			}
		});
	</script>
	<?php
}

function dlck_divi_custom_icons_migrate_content( $content ) {
	if ( function_exists( 'et_pb_get_all_font_icon_option_names_string' ) ) {
		$regex   = '/(' . et_pb_get_all_font_icon_option_names_string() . ')\=\"%%([^"]*)%%\"/mi';
		$content = preg_replace_callback( $regex, 'dlck_divi_custom_icons_migrate_content_callback', $content );
	}
	return $content;
}

function dlck_divi_custom_icons_migrate_content_callback( $matches ) {
	if ( isset( $matches[2] ) && intval( $matches[2] ) >= 380 ) {
		return $matches[1] . '="&#x' . esc_attr( intval( $matches[2] ) - 380 + 800 ) . ';||divi||400"';
	}
	return $matches[0];
}

function dlck_divi_custom_icons_add_toggle_data_attributes( $output, $render_slug, $module ) {
	if ( ! is_string( $output ) ) {
		return $output;
	}
	if ( $render_slug === 'et_pb_toggle' ) {
		$output = preg_replace_callback(
			'/class="et_pb_toggle_title"/',
			static function ( $matches ) use ( $module ) {
				$output = $matches[0];
				$icon = isset( $module->props['toggle_icon'] ) ? $module->props['toggle_icon'] : '';
				$icon_open = isset( $module->props['open_toggle_icon'] ) ? $module->props['open_toggle_icon'] : '';
				if ( preg_match( '/^(&#x[0-9a-f]+;)\|\|divi\|\|400$/', $icon, $icon_matches ) ) {
					$output = str_replace( 'class="et_pb_toggle_title"', 'class="et_pb_toggle_title" data-icon="' . esc_attr( $icon_matches[1] ) . '"', $output );
				}
				if ( preg_match( '/^(&#x[0-9a-f]+;)\|\|divi\|\|400$/', $icon_open, $icon_open_matches ) ) {
					$output = str_replace( 'class="et_pb_toggle_title"', 'class="et_pb_toggle_title" data-icon-open="' . esc_attr( $icon_open_matches[1] ) . '"', $output );
				}
				return $output;
			},
			$output
		);
	}
	return $output;
}

function dlck_divi_custom_icons_extend_icon_symbols( $symbols ) {
	$icons = dlck_divi_custom_icons_get_urls();
	if ( empty( $icons ) ) {
		return $symbols;
	}
	$custom_icons = array();
	foreach ( $icons as $id => $url ) {
		$custom_icons[] = array(
			'search_terms' => 'divi-lc-kit custom-icon',
			'unicode'      => '&#x' . ( 800 + $id ) . ';',
			'name'         => 'LC Kit Custom Icon',
			'styles'       => array( 'divi', 'solid' ),
			'is_divi_icon' => true,
			'font_weight'  => 400,
		);
	}
	if ( ! is_array( $symbols ) ) {
		$symbols = array();
	}
	return array_merge( $custom_icons, $symbols );
}

if ( ! class_exists( 'DLCK_Divi_Custom_Icon' ) ) {
	class DLCK_Divi_Custom_Icon {
		private int $id;
		private string $url;

		public function __construct( int $id, string $url ) {
			$this->id  = $id;
			$this->url = $url;
		}

		public function init(): void {
			add_filter( 'et_pb_font_icon_symbols', array( $this, 'add_to_font_symbols' ), 50 );
		}

		private function icon_id(): string {
			return 'wtfdivi014-url' . $this->id;
		}

		public function add_to_font_symbols( $symbols ) {
			$symbols[] = esc_html( $this->icon_id() );
			return $symbols;
		}
	}
}
