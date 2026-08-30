<?php
/**
 * Divi Assistant-inspired helpers adapted for LC Tweaks.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dlck_divi_helper_enabled' ) ) {
	function dlck_divi_helper_enabled( string $key ): bool {
		if ( ! function_exists( 'dlck_get_option' ) || dlck_get_option( $key ) !== '1' ) {
			return false;
		}
		if ( function_exists( 'dlck_scope_rules_allow_option' ) && ! dlck_scope_rules_allow_option( $key ) ) {
			return false;
		}
		return true;
	}
}

if ( ! function_exists( 'dlck_divi_helper_parse_lines' ) ) {
	/**
	 * Parse a textarea into unique non-empty lines.
	 *
	 * @param mixed $raw Raw setting.
	 * @return string[]
	 */
	function dlck_divi_helper_parse_lines( $raw ): array {
		if ( ! is_scalar( $raw ) ) {
			return array();
		}

		$lines = preg_split( '/\r\n|\r|\n/', (string) $raw );
		if ( ! is_array( $lines ) ) {
			return array();
		}

		$lines = array_map(
			static function ( $line ) {
				return trim( (string) $line );
			},
			$lines
		);

		return array_values( array_unique( array_filter( $lines, static fn( $line ) => $line !== '' ) ) );
	}
}

if ( ! function_exists( 'dlck_divi_helper_add_inline_assets' ) ) {
	/**
	 * Add collected helper CSS/JS to the inline asset system.
	 *
	 * @param array{css?:string[],js?:string[]} $assets  Asset strings.
	 * @param string                            $context Inline asset context.
	 */
	function dlck_divi_helper_add_inline_assets( array $assets, string $context ): void {
		$css = isset( $assets['css'] ) && is_array( $assets['css'] ) ? array_filter( array_map( 'trim', $assets['css'] ) ) : array();
		$js  = isset( $assets['js'] ) && is_array( $assets['js'] ) ? array_filter( array_map( 'trim', $assets['js'] ) ) : array();

		if ( ! empty( $css ) ) {
			dlck_add_inline_css( implode( "\n", array_unique( $css ) ), $context );
		}

		if ( ! empty( $js ) ) {
			dlck_add_inline_js( implode( "\n", array_unique( $js ) ), $context );
		}
	}

	/**
	 * Return the complete Divi 4/5 Cloud suppression rules shared by wp-admin
	 * and Visual Builder requests.
	 */
	function dlck_divi_helper_divi_cloud_css(): string {
		return ".et-cloud-toggle,.et-cloud-app__upsell,.et_fb_save_module_modal .et-fb-settings-option:has(+ .et-cloud-app__upsell),.et-tb-library-save-option:has(+ .et-cloud-app__upsell),.et-save-to-library-modal .et-save-to-library-option:has(+ .et-cloud-app__upsell),.et-fb-right-click-menu__item--saveCloud,button.et-vb-right-click-option[value='save-to-cloud'],.et-vb-modal.et-vb-modal--main[data-modal-name='divi/add-to-library'] div.et-vb-save-to-library-options div.et-vb-field:nth-child(3),.et-vb-modal.et-vb-modal--main[data-modal-name='divi/add-to-library'] div.et-vb-save-to-library-options div.et-vb-field:nth-child(4),.et-vb-modal.et-vb-modal--main[data-modal-name='divi/add-module'] div.et-vb-modal-panel--add-from-library div.et-cloud-app-sidebar__content div.et-cloud-app__upsell.card-library.card-default,.et-vb-modal.et-vb-modal--main[data-modal-name='divi/load-layout']:has(.et-vb-modal-tab:nth-child(2).et-vb-modal-tab--active) .et-cloud-app__upsell.card-library.card-default,.et-vb-modal.et-vb-modal--main[data-modal-name='divi/load-layout']:has(.et-vb-modal-tab:nth-child(3).et-vb-modal-tab--active) .et-cloud-app__upsell.card-library.card-default,.et-vb-modal.et-vb-modal--main[data-modal-name='divi/add-module'] .et-cloud-app-view-header .et-cloud-app-view-header--right .et-cloud-toggle,.et-vb-modal.et-vb-modal--main[data-modal-name='divi/load-layout'] .et-cloud-app-view-header .et-cloud-app-view-header--right .et-cloud-toggle,.et-vb-field:has(+ .et-vb-field-upsell-card),.et-vb-field-upsell-card,.et-common-icon--cloud{display:none!important;}.et-cloud-app-sort-menu{margin-right:0!important;}";
	}

	/**
	 * Collect helpers that belong on normal frontend pages.
	 *
	 * @return array{css:string[],js:string[]}
	 */
	function dlck_divi_helper_front_assets(): array {
		$css = array();
		$js  = array();

		if ( dlck_divi_helper_enabled( 'dlck_disable_horizontal_scroll' ) ) {
			$css[] = 'html,body{max-width:100%;overflow-x:hidden;}';
		}

		if ( dlck_divi_helper_enabled( 'dlck_frontend_anchor_offset_enabled' ) ) {
			$offset = max( 0, min( 600, absint( dlck_get_option( 'dlck_frontend_anchor_offset_px', 90 ) ) ) );
			$css[]  = 'html{scroll-padding-top:' . $offset . 'px;}';
			$js[]   = 'jQuery(function($){var o=' . $offset . ';$(document).on("click","a[href*=\'#\']",function(e){var h=this.hash;if(!h||h==="#"){return;}var $t=$(h);if(!$t.length){return;}e.preventDefault();$("html,body").animate({scrollTop:Math.max(0,$t.offset().top-o)},250);});if(window.location.hash){setTimeout(function(){var $t=$(window.location.hash);if($t.length){window.scrollTo(0,Math.max(0,$t.offset().top-o));}},80);}});';
		}

		if ( dlck_divi_helper_enabled( 'dlck_cursor_highlight_enabled' ) ) {
			$color = sanitize_hex_color( (string) dlck_get_option( 'dlck_cursor_highlight_color', '#ffe45c' ) ) ?: '#ffe45c';
			$css[] = '::selection{background:' . $color . ';}::-moz-selection{background:' . $color . ';}';
		}

		if ( dlck_divi_helper_enabled( 'dlck_custom_scrollbar_enabled' ) ) {
			$width = max( 4, min( 30, absint( dlck_get_option( 'dlck_custom_scrollbar_width', 12 ) ) ) );
			$track = sanitize_hex_color( (string) dlck_get_option( 'dlck_custom_scrollbar_track_color', '#f1f1f1' ) ) ?: '#f1f1f1';
			$thumb = sanitize_hex_color( (string) dlck_get_option( 'dlck_custom_scrollbar_thumb_color', '#777777' ) ) ?: '#777777';
			$hover = sanitize_hex_color( (string) dlck_get_option( 'dlck_custom_scrollbar_thumb_hover_color', '#444444' ) ) ?: '#444444';
			$css[] = 'body{scrollbar-width:thin;scrollbar-color:' . $thumb . ' ' . $track . ';}body::-webkit-scrollbar{width:' . $width . 'px;}body::-webkit-scrollbar-track{background:' . $track . ';}body::-webkit-scrollbar-thumb{background:' . $thumb . ';border-radius:999px;}body::-webkit-scrollbar-thumb:hover{background:' . $hover . ';}';
		}

		if ( dlck_divi_helper_enabled( 'dlck_back_to_top_customizer_enabled' ) ) {
			$size = max( 28, min( 90, absint( dlck_get_option( 'dlck_back_to_top_size', 44 ) ) ) );
			$bg   = sanitize_hex_color( (string) dlck_get_option( 'dlck_back_to_top_bg_color', '#111111' ) ) ?: '#111111';
			$text = sanitize_hex_color( (string) dlck_get_option( 'dlck_back_to_top_text_color', '#ffffff' ) ) ?: '#ffffff';
			$css[] = '#dlck-back-to-top{position:fixed;right:22px;bottom:22px;z-index:99999;width:' . $size . 'px;height:' . $size . 'px;border:0;border-radius:4px;background:' . $bg . ';color:' . $text . ';font-size:22px;line-height:1;display:none;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 8px 20px rgba(0,0,0,.18);}#dlck-back-to-top.is-visible{display:flex;}';
			$js[]  = 'jQuery(function($){var $b=$("#dlck-back-to-top");if(!$b.length){return;}$(window).on("scroll",function(){$b.toggleClass("is-visible",$(this).scrollTop()>320);}).trigger("scroll");$b.on("click",function(e){e.preventDefault();$("html,body").animate({scrollTop:0},260);});});';
		}

		if ( dlck_divi_helper_enabled( 'dlck_admin_bar_frontend_hide' ) ) {
			if ( dlck_divi_helper_enabled( 'dlck_admin_bar_hover' ) ) {
				$css[] = 'body:not(.wp-admin) #wpadminbar{transform:translateY(-28px);transition:transform .16s ease;}body:not(.wp-admin) #wpadminbar:hover,body:not(.wp-admin) #wpadminbar:focus-within{transform:translateY(0);}html{margin-top:0!important;}';
			} else {
				$css[] = 'body:not(.wp-admin) #wpadminbar{display:none!important;}html{margin-top:0!important;}';
			}
		}

		if ( dlck_divi_helper_enabled( 'dlck_environment_badge_enabled' ) ) {
			$bg   = sanitize_hex_color( (string) dlck_get_option( 'dlck_environment_badge_bg_color', '#d63638' ) ) ?: '#d63638';
			$text = sanitize_hex_color( (string) dlck_get_option( 'dlck_environment_badge_text_color', '#ffffff' ) ) ?: '#ffffff';
			$css[] = '#wpadminbar #wp-admin-bar-dlck-environment-badge>.ab-item{background:' . $bg . '!important;color:' . $text . '!important;font-weight:700;}';
		}

		return array(
			'css' => $css,
			'js'  => $js,
		);
	}

	/**
	 * Collect helpers that belong on normal wp-admin pages.
	 *
	 * @return array{css:string[],js:string[]}
	 */
	function dlck_divi_helper_admin_assets(): array {
		$css = array();

		if ( dlck_divi_helper_enabled( 'dlck_environment_badge_enabled' ) ) {
			$bg   = sanitize_hex_color( (string) dlck_get_option( 'dlck_environment_badge_bg_color', '#d63638' ) ) ?: '#d63638';
			$text = sanitize_hex_color( (string) dlck_get_option( 'dlck_environment_badge_text_color', '#ffffff' ) ) ?: '#ffffff';
			$css[] = '#wpadminbar #wp-admin-bar-dlck-environment-badge>.ab-item{background:' . $bg . '!important;color:' . $text . '!important;font-weight:700;}';
		}

		if ( dlck_divi_helper_enabled( 'dlck_divi_vb_hide_divi_builder_btn_ce' ) ) {
			$css[] = 'a#et_pb_use_the_builder.et_pb_ready{display:none!important;}';
		}

		if ( dlck_divi_helper_enabled( 'dlck_divi_vb_hide_divi_builder_btn_be' ) ) {
			$css[] = "button.editor-post-switch-to-divi[data-editor='divi']{display:none!important;}";
		}
		if ( dlck_divi_helper_enabled( 'dlck_divi_vb_hide_editor_switch_buttons' ) ) {
			$css[] = 'button#et-switch-to-gutenberg.components-button.is-default,.et-db #et-boc .et-l #et_pb_toggle_builder.et_pb_builder_is_used,#et_pb_toggle_builder[data-builder=\'divi\'][data-editor=\'visual-builder\'].et_pb_builder_is_used,button.editor-post-switch-to-gutenberg[data-editor=\'gutenberg\']{display:none!important;}.et-db #et-boc .et-l #et_pb_fb_cta{margin-left:0!important;}';
		}

		if ( dlck_divi_helper_enabled( 'dlck_divi_vb_hide_divi_cloud' ) ) {
			$css[] = dlck_divi_helper_divi_cloud_css();
		}

		return array(
			'css' => $css,
			'js'  => array(),
		);
	}

	/**
	 * Collect Visual Builder-only helper assets.
	 *
	 * @return array{css:string[],js:string[]}
	 */
	function dlck_divi_helper_builder_assets(): array {
		$css = array();
		$js  = array();

		if ( dlck_divi_helper_enabled( 'dlck_divi_vb_sticky_toolbar' ) ) {
			$css[] = '.et-fb-modal-settings-container .et-fb-form__actions,.et-vb-settings-modal__actions,.et-fb-settings-options-tab--active .et-fb-settings-options-tab__actions{position:sticky!important;bottom:0!important;z-index:100!important;}';
		}

		if ( dlck_divi_helper_enabled( 'dlck_divi_vb_html_textarea_height_enabled' ) ) {
			$height = max( 120, min( 1200, absint( dlck_get_option( 'dlck_divi_vb_html_textarea_height', 360 ) ) ) );
			$css[]  = '.et-fb-settings-options textarea,.et-vb-settings-modal textarea,.et-fb-form__field textarea{min-height:' . $height . 'px!important;}';
		}

		if ( dlck_divi_helper_enabled( 'dlck_divi_vb_icon_picker_height_enabled' ) ) {
			$height = max( 120, min( 1200, absint( dlck_get_option( 'dlck_divi_vb_icon_picker_height', 420 ) ) ) );
			$css[]  = '.et-fb-icon-picker,.et-vb-icon-picker,.et-fb-icon_picker,.et-vb-icon_picker{max-height:' . $height . 'px!important;overflow:auto!important;}';
		}

		if ( dlck_divi_helper_enabled( 'dlck_divi_vb_disable_inline_text_toolbar' ) ) {
			$css[] = '.et-fb-tinymce-inline-toolbar,.et-fb-tinymce-popover,.et-vb-tinymce-inline-toolbar,.et-vb-inline-text-toolbar{display:none!important;}';
		}

		if ( dlck_divi_helper_enabled( 'dlck_divi_vb_full_width_post_content_rows' ) ) {
			$css[] = '.et-db #et-boc .et-l .et_pb_post_content .et_pb_row,.et-db #et-boc .et-l .et_pb_theme_builder_layout .et_pb_post_content .et_pb_row{width:100%!important;max-width:100%!important;}';
		}

		if ( dlck_divi_helper_enabled( 'dlck_divi_vb_hide_marketplace_layout_promo' ) ) {
			$css[] = '.et-fb-layout-marketplace,.et-fb-layouts__marketplace,.et-fb-library__marketplace,.et-fb-load-layouts-marketplace,.et-vb-library-marketplace,.et-vb-layout-marketplace,[data-testid*="marketplace" i],[class*="marketplace" i][class*="layout" i]{display:none!important;}';
		}

		if ( dlck_divi_helper_enabled( 'dlck_divi_vb_hide_explore_modules' ) ) {
			$css[] = '.et-fb-modules-list__more,.et-fb-module-item--marketplace,.et-vb-module-marketplace,[data-testid*="explore" i],[aria-label*="Explore" i]{display:none!important;}';
		}
		if ( dlck_divi_helper_enabled( 'dlck_divi_vb_hide_divi_cloud' ) ) {
			$css[] = dlck_divi_helper_divi_cloud_css();
		}
		if ( dlck_divi_helper_enabled( 'dlck_divi_vb_hide_layouts_btn' ) ) {
			$css[] = ".et-vb-modal.et-vb-modal--main[data-modal-name='divi/load-layout']:has(.et-vb-modal-tab:nth-child(1).et-vb-modal-tab--active) .et-cloud-app__upsell.card-library.card-default{display:none!important;}";
		}

		$ai_selectors = array();
		if ( dlck_divi_helper_enabled( 'dlck_divi_ai_hide_quick_sites' ) ) {
			$ai_selectors[] = '[class*="quick-sites" i],[data-testid*="quick-sites" i]';
		}
		if ( dlck_divi_helper_enabled( 'dlck_divi_ai_hide_page' ) ) {
			$ai_selectors[] = '[class*="ai-page" i],[data-testid*="ai-page" i]';
		}
		if ( dlck_divi_helper_enabled( 'dlck_divi_ai_hide_section' ) ) {
			$ai_selectors[] = '[class*="ai-section" i],[data-testid*="ai-section" i]';
		}
		if ( dlck_divi_helper_enabled( 'dlck_divi_ai_hide_layout' ) ) {
			$ai_selectors[] = '[class*="ai-layout" i],[data-testid*="ai-layout" i]';
		}
		if ( dlck_divi_helper_enabled( 'dlck_divi_ai_hide_text' ) ) {
			$ai_selectors[] = '[class*="ai-text" i],[data-testid*="ai-text" i]';
		}
		if ( dlck_divi_helper_enabled( 'dlck_divi_ai_hide_image' ) ) {
			$ai_selectors[] = '[class*="ai-image" i],[data-testid*="ai-image" i]';
		}
		if ( dlck_divi_helper_enabled( 'dlck_divi_ai_hide_code' ) ) {
			$ai_selectors[] = '[class*="ai-code" i],[data-testid*="ai-code" i]';
		}
		if ( ! empty( $ai_selectors ) ) {
			$css[] = implode( ',', array_unique( $ai_selectors ) ) . '{display:none!important;}';
		}

		if ( dlck_divi_helper_enabled( 'dlck_divi_vb_escape_key' ) ) {
			$js[] = 'jQuery(function($){$(document).on("keydown",function(e){if(e.key!=="Escape"){return;}var s=".et-fb-modal__close-button,.et-vb-modal-close-button,.et-fb-modal-close,.et-vb-button--close,[aria-label=\'Close\']";var $b=$(s).filter(":visible").last();if($b.length){$b.trigger("click");}});});';
		}

		if ( dlck_divi_helper_enabled( 'dlck_divi_vb_swap_global_saved_colors' ) ) {
			$js[] = 'jQuery(function($){function swap(){ $(".et-fb-color-picker,.et-vb-color-picker").each(function(){var $saved=$(this).find("[class*=saved]").first();var $global=$(this).find("[class*=global]").first();if($saved.length&&$global.length&&$saved.index()>$global.index()){$saved.insertBefore($global);}});}swap();setInterval(swap,1500);});';
		}

		if ( ( dlck_divi_helper_enabled( 'dlck_divi_quick_links_in_builder' ) || dlck_divi_helper_enabled( 'dlck_custom_quick_links_in_builder' ) || dlck_divi_helper_enabled( 'dlck_theme_builder_details_builder_bar' ) || dlck_divi_helper_enabled( 'dlck_divi_cache_builder_buttons' ) ) && function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
			$builder_links = dlck_divi_helper_builder_links();
			if ( ! empty( $builder_links ) ) {
				$css[] = '#dlck-builder-quick-links{position:fixed;right:14px;bottom:14px;z-index:100000;background:#1d2327;color:#fff;border-radius:4px;box-shadow:0 8px 22px rgba(0,0,0,.22);font:13px/1.4 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;}#dlck-builder-quick-links button{background:transparent;color:#fff;border:0;padding:9px 12px;cursor:pointer;}#dlck-builder-quick-links ul{display:none;margin:0;padding:6px 0;list-style:none;border-top:1px solid rgba(255,255,255,.16);}#dlck-builder-quick-links.is-open ul{display:block;}#dlck-builder-quick-links a,#dlck-builder-quick-links .dlck-builder-action{display:block;padding:7px 12px;color:#fff;text-decoration:none;white-space:nowrap;background:transparent;border:0;width:100%;text-align:left;cursor:pointer;}#dlck-builder-quick-links a:hover,#dlck-builder-quick-links .dlck-builder-action:hover{background:rgba(255,255,255,.12);}';
				$js[]  = 'jQuery(function($){var links=' . wp_json_encode( $builder_links ) . ';var ajaxUrl=' . wp_json_encode( admin_url( 'admin-ajax.php' ) ) . ';if(!links||!links.length||$("#dlck-builder-quick-links").length){return;}var html="<div id=\"dlck-builder-quick-links\"><button type=\"button\">LC Helpers</button><ul>";links.forEach(function(l){if(l.action){html+="<li><button type=\"button\" class=\"dlck-builder-action\" data-action=\""+l.action+"\" data-nonce=\""+(l.nonce||"")+"\">"+l.label+"</button></li>";}else{html+="<li><a href=\""+l.url+"\" target=\"_blank\" rel=\"noopener\">"+l.label+"</a></li>";}});html+="</ul></div>";$("body").append(html);$("#dlck-builder-quick-links>button").on("click",function(){$("#dlck-builder-quick-links").toggleClass("is-open");});$("#dlck-builder-quick-links").on("click",".dlck-builder-action",function(){var $b=$(this);if($b.data("action")==="static-css"){$.post(window.ajaxurl||ajaxUrl,{action:"dlck_misc_clear_static_css",_wpnonce:$b.data("nonce")}).done(function(){try{window.localStorage.clear();}catch(e){}});}});});';
			}
		}

		return array(
			'css' => $css,
			'js'  => $js,
		);
	}

	function dlck_divi_helper_collect_front_assets(): void {
		dlck_divi_helper_add_inline_assets( dlck_divi_helper_front_assets(), 'front' );
	}
	add_action( 'dlck_collect_inline_assets_front', 'dlck_divi_helper_collect_front_assets' );

	function dlck_divi_helper_collect_admin_assets(): void {
		dlck_divi_helper_add_inline_assets( dlck_divi_helper_admin_assets(), 'admin' );
	}
	add_action( 'dlck_collect_inline_assets_admin', 'dlck_divi_helper_collect_admin_assets' );

if ( ! function_exists( 'dlck_divi_helper_disable_comment_settings_menu' ) ) {
	function dlck_divi_helper_disable_comment_settings_menu(): void {
		remove_menu_page( 'edit-comments.php' );
	}

	function dlck_divi_helper_disable_comment_settings_post_type_support(): void {
		remove_post_type_support( 'post', 'comments' );
		remove_post_type_support( 'page', 'comments' );
	}

	function dlck_divi_helper_disable_comment_settings_admin_bar(): void {
		global $wp_admin_bar;
		if ( isset( $wp_admin_bar ) ) {
			$wp_admin_bar->remove_menu( 'comments' );
		}
	}

	if ( dlck_divi_helper_enabled( 'dlck_disable_comment_settings' ) ) {
		add_action( 'admin_menu', 'dlck_divi_helper_disable_comment_settings_menu' );
		add_action( 'init', 'dlck_divi_helper_disable_comment_settings_post_type_support', 100 );
		add_action( 'wp_before_admin_bar_render', 'dlck_divi_helper_disable_comment_settings_admin_bar' );
	}
}

if ( ! function_exists( 'dlck_divi_helper_disable_block_editor_fullscreen' ) ) {
	function dlck_divi_helper_disable_block_editor_fullscreen(): void {
		if ( ! dlck_divi_helper_enabled( 'dlck_divi_vb_fullscreen' ) ) {
			return;
		}
		$script = "jQuery(window).on('load',function(){if(!window.wp||!wp.data||!wp.data.select||!wp.data.dispatch){return;}var editorData=wp.data.select('core/edit-post');if(!editorData){return;}if(editorData.isFeatureActive('fullscreenMode')){wp.data.dispatch('core/edit-post').toggleFeature('fullscreenMode');}});";
		wp_add_inline_script( 'wp-blocks', $script );
	}
	add_action( 'enqueue_block_editor_assets', 'dlck_divi_helper_disable_block_editor_fullscreen' );
}

	function dlck_divi_helper_is_builder_asset_request(): bool {
		if ( function_exists( 'dlck_is_divi_visual_builder_request' ) && dlck_is_divi_visual_builder_request() ) {
			return true;
		}

		foreach ( array( 'et_fb', 'et_vb_preview_id', 'app_window', 'et_builder', 'et_builder_frame' ) as $flag ) {
			if ( isset( $_GET[ $flag ] ) || isset( $_POST[ $flag ] ) ) {
				return true;
			}
		}

		return false;
	}

	function dlck_divi_helper_enqueue_builder_assets(): void {
		if ( ! dlck_divi_helper_is_builder_asset_request() ) {
			return;
		}

		$assets = dlck_divi_helper_builder_assets();
		$css    = ! empty( $assets['css'] ) ? implode( "\n", array_unique( array_filter( array_map( 'trim', $assets['css'] ) ) ) ) : '';
		$js     = ! empty( $assets['js'] ) ? implode( "\n", array_unique( array_filter( array_map( 'trim', $assets['js'] ) ) ) ) : '';

		if ( $css !== '' ) {
			wp_register_style( 'dlck-divi-helper-builder-inline', false, array(), null );
			wp_enqueue_style( 'dlck-divi-helper-builder-inline' );
			wp_add_inline_style( 'dlck-divi-helper-builder-inline', $css );
		}

		if ( $js !== '' ) {
			wp_register_script( 'dlck-divi-helper-builder-inline', false, array( 'jquery' ), null, true );
			wp_enqueue_script( 'dlck-divi-helper-builder-inline' );
			wp_add_inline_script( 'dlck-divi-helper-builder-inline', $js );
		}
	}
	add_action( 'wp_enqueue_scripts', 'dlck_divi_helper_enqueue_builder_assets', 60 );
	add_action( 'admin_enqueue_scripts', 'dlck_divi_helper_enqueue_builder_assets', 60 );
}

if ( ! function_exists( 'dlck_divi_helper_builder_links' ) ) {
	/**
	 * Return compact Visual Builder helper links/actions.
	 *
	 * @return array<int,array<string,string>>
	 */
	function dlck_divi_helper_builder_links(): array {
		$links = array();

		if ( dlck_divi_helper_enabled( 'dlck_divi_quick_links_in_builder' ) ) {
			$links[] = array( 'label' => __( 'Divi Theme Options', 'lc-tweaks' ), 'url' => admin_url( 'admin.php?page=et_divi_options' ) );
			$links[] = array( 'label' => __( 'Theme Builder', 'lc-tweaks' ), 'url' => admin_url( 'admin.php?page=et_theme_builder' ) );
			$links[] = array( 'label' => __( 'Divi Library', 'lc-tweaks' ), 'url' => admin_url( 'edit.php?post_type=et_pb_layout' ) );
			$links[] = array( 'label' => __( 'Role Editor', 'lc-tweaks' ), 'url' => admin_url( 'admin.php?page=et_divi_role_editor' ) );
		}

		if ( dlck_divi_helper_enabled( 'dlck_custom_quick_links_in_builder' ) ) {
			foreach ( dlck_divi_helper_parse_quick_links( dlck_get_option( 'dlck_custom_quick_links_items', '' ) ) as $link ) {
				$links[] = $link;
			}
		}

		if ( dlck_divi_helper_enabled( 'dlck_theme_builder_details_builder_bar' ) ) {
			$links[] = array( 'label' => __( 'Open Theme Builder', 'lc-tweaks' ), 'url' => admin_url( 'admin.php?page=et_theme_builder' ) );
		}

		if ( dlck_divi_helper_enabled( 'dlck_divi_cache_builder_buttons' ) ) {
			$links[] = array(
				'label'  => __( 'Clear Divi Cache + Local Storage', 'lc-tweaks' ),
				'action' => 'static-css',
				'nonce'  => wp_create_nonce( 'dlck_misc_clear_static_css' ),
			);
		}

		return $links;
	}
}

if ( ! function_exists( 'dlck_divi_helper_parse_quick_links' ) ) {
	/**
	 * Parse quick-link textarea lines.
	 *
	 * @param mixed $raw Raw links.
	 * @return array<int,array{label:string,url:string}>
	 */
	function dlck_divi_helper_parse_quick_links( $raw ): array {
		$links = array();
		foreach ( dlck_divi_helper_parse_lines( $raw ) as $line ) {
			$parts = array_map( 'trim', explode( '|', $line, 2 ) );
			if ( count( $parts ) !== 2 || $parts[0] === '' || $parts[1] === '' ) {
				continue;
			}
			$url = $parts[1];
			if ( str_starts_with( $url, 'admin:' ) ) {
				$url = admin_url( ltrim( substr( $url, 6 ), '/' ) );
			} elseif ( str_starts_with( $url, '/' ) ) {
				$url = home_url( $url );
			}
			$url = esc_url_raw( $url );
			if ( $url === '' ) {
				continue;
			}
			$links[] = array(
				'label' => sanitize_text_field( $parts[0] ),
				'url'   => $url,
			);
		}
		return $links;
	}
}

if ( ! function_exists( 'dlck_divi_helper_admin_bar' ) ) {
	/**
	 * Add utility menus to the admin bar.
	 *
	 * @param WP_Admin_Bar $admin_bar Admin bar.
	 */
	function dlck_divi_helper_admin_bar( $admin_bar ): void {
		if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		if ( dlck_divi_helper_enabled( 'dlck_remove_howdy' ) ) {
			$node = $admin_bar->get_node( 'my-account' );
			if ( $node ) {
				$user  = wp_get_current_user();
				$title = str_replace( 'Howdy, ', '', (string) $node->title );
				if ( $title === (string) $node->title && $user->exists() ) {
					$title = get_avatar( $user->ID, 26 ) . '<span class="display-name">' . esc_html( $user->display_name ) . '</span>';
				}
				$admin_bar->add_node(
					array(
						'id'    => 'my-account',
						'title' => $title,
					)
				);
			}
		}

		if ( dlck_divi_helper_enabled( 'dlck_environment_badge_enabled' ) ) {
			$mode  = sanitize_key( (string) dlck_get_option( 'dlck_environment_badge_mode', 'custom' ) );
			$label = sanitize_text_field( (string) dlck_get_option( 'dlck_environment_badge_label', '' ) );
			if ( $label === '' ) {
				$label = $mode !== 'custom' ? ucwords( str_replace( '_', ' ', $mode ) ) : __( 'Environment', 'lc-tweaks' );
			}
			$admin_bar->add_node(
				array(
					'id'    => 'dlck-environment-badge',
					'title' => esc_html( $label ),
					'href'  => admin_url( 'admin.php?page=lc_tweaks&tab=divi-helpers' ),
				)
			);
		}

		if ( dlck_divi_helper_enabled( 'dlck_divi_quick_links_enabled' ) ) {
			$admin_bar->add_node(
				array(
					'id'    => 'dlck-divi-quick-links',
					'title' => esc_html__( 'Divi Links', 'lc-tweaks' ),
					'href'  => admin_url( 'admin.php?page=et_divi_options' ),
				)
			);
			$divi_links = array(
				'theme-options' => array( __( 'Theme Options', 'lc-tweaks' ), admin_url( 'admin.php?page=et_divi_options' ) ),
				'theme-builder' => array( __( 'Theme Builder', 'lc-tweaks' ), admin_url( 'admin.php?page=et_theme_builder' ) ),
				'role-editor'   => array( __( 'Role Editor', 'lc-tweaks' ), admin_url( 'admin.php?page=et_divi_role_editor' ) ),
				'library'       => array( __( 'Divi Library', 'lc-tweaks' ), admin_url( 'edit.php?post_type=et_pb_layout' ) ),
			);
			foreach ( $divi_links as $id => $link ) {
				$admin_bar->add_node(
					array(
						'id'     => 'dlck-divi-link-' . $id,
						'parent' => 'dlck-divi-quick-links',
						'title'  => esc_html( $link[0] ),
						'href'   => esc_url( $link[1] ),
					)
				);
			}
		}

		if ( dlck_divi_helper_enabled( 'dlck_custom_quick_links_enabled' ) ) {
			$label = sanitize_text_field( (string) dlck_get_option( 'dlck_custom_quick_links_label', __( 'Quick Links', 'lc-tweaks' ) ) );
			$links = dlck_divi_helper_parse_quick_links( dlck_get_option( 'dlck_custom_quick_links_items', '' ) );
			if ( ! empty( $links ) ) {
				$admin_bar->add_node(
					array(
						'id'    => 'dlck-custom-quick-links',
						'title' => esc_html( $label !== '' ? $label : __( 'Quick Links', 'lc-tweaks' ) ),
						'href'  => '#',
					)
				);
				foreach ( $links as $index => $link ) {
					$admin_bar->add_node(
						array(
							'id'     => 'dlck-custom-quick-link-' . $index,
							'parent' => 'dlck-custom-quick-links',
							'title'  => esc_html( $link['label'] ),
							'href'   => esc_url( $link['url'] ),
							'meta'   => array( 'target' => '_blank' ),
						)
					);
				}
			}
		}

		if ( dlck_divi_helper_enabled( 'dlck_theme_builder_details_admin_bar' ) ) {
			$admin_bar->add_node(
				array(
					'id'    => 'dlck-theme-builder-details',
					'title' => esc_html__( 'Theme Builder', 'lc-tweaks' ),
					'href'  => admin_url( 'admin.php?page=et_theme_builder' ),
				)
			);
			if ( is_singular() ) {
				$admin_bar->add_node(
					array(
						'id'     => 'dlck-theme-builder-current-post',
						'parent' => 'dlck-theme-builder-details',
						'title'  => esc_html__( 'Edit current item', 'lc-tweaks' ),
						'href'   => get_edit_post_link( get_queried_object_id(), 'raw' ),
					)
				);
			}
		}
	}
	add_action( 'admin_bar_menu', 'dlck_divi_helper_admin_bar', 1000 );
}

if ( ! function_exists( 'dlck_divi_helper_featured_image_columns' ) ) {
	function dlck_divi_helper_featured_image_columns( array $columns ): array {
		if ( ! dlck_divi_helper_enabled( 'dlck_featured_image_admin_column' ) ) {
			return $columns;
		}
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( $key === 'cb' ) {
				$new['dlck_featured_image'] = __( 'Image', 'lc-tweaks' );
			}
		}
		return $new;
	}
	add_filter( 'manage_posts_columns', 'dlck_divi_helper_featured_image_columns' );
	add_filter( 'manage_pages_columns', 'dlck_divi_helper_featured_image_columns' );

	function dlck_divi_helper_featured_image_column( string $column, int $post_id ): void {
		if ( $column !== 'dlck_featured_image' || ! dlck_divi_helper_enabled( 'dlck_featured_image_admin_column' ) ) {
			return;
		}
		$image = get_the_post_thumbnail( $post_id, array( 70, 70 ), array( 'style' => 'width:70px;height:auto;' ) );
		echo $image ? wp_kses_post( $image ) : '&mdash;';
	}
	add_action( 'manage_posts_custom_column', 'dlck_divi_helper_featured_image_column', 10, 2 );
	add_action( 'manage_pages_custom_column', 'dlck_divi_helper_featured_image_column', 10, 2 );
}

if ( ! function_exists( 'dlck_divi_helper_builder_admin_filter' ) ) {
	function dlck_divi_helper_builder_admin_filter( string $post_type ): void {
		if ( ! dlck_divi_helper_enabled( 'dlck_divi_posts_builder_filter' ) || ! in_array( $post_type, array( 'post', 'page' ), true ) ) {
			return;
		}
		$value = isset( $_GET['dlck_divi_builder'] ) ? sanitize_key( wp_unslash( $_GET['dlck_divi_builder'] ) ) : '';
		?>
		<select name="dlck_divi_builder">
			<option value=""><?php esc_html_e( 'All builder states', 'lc-tweaks' ); ?></option>
			<option value="on" <?php selected( 'on', $value ); ?>><?php esc_html_e( 'Uses Divi Builder', 'lc-tweaks' ); ?></option>
			<option value="off" <?php selected( 'off', $value ); ?>><?php esc_html_e( 'Does not use Divi Builder', 'lc-tweaks' ); ?></option>
		</select>
		<?php
	}
	add_action( 'restrict_manage_posts', 'dlck_divi_helper_builder_admin_filter' );

	function dlck_divi_helper_builder_admin_views( array $views ): array {
		if ( ! dlck_divi_helper_enabled( 'dlck_divi_posts_builder_filter' ) ) {
			return $views;
		}
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, array( 'post', 'page' ), true ) ) {
			return $views;
		}
		$query = new WP_Query(
			array(
				'post_type'      => $screen->post_type,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_et_pb_use_builder',
				'meta_value'     => 'on',
			)
		);
		$count   = (int) $query->found_posts;
		$current = isset( $_GET['dlck_divi_builder'] ) && sanitize_key( wp_unslash( $_GET['dlck_divi_builder'] ) ) === 'on';
		$url     = add_query_arg(
			array(
				'post_type'          => $screen->post_type,
				'dlck_divi_builder' => 'on',
			),
			admin_url( 'edit.php' )
		);
		$views['dlck_divi_builder'] = sprintf(
			'<a href="%1$s" class="%2$s">%3$s <span class="count">(%4$d)</span></a>',
			esc_url( $url ),
			$current ? 'current' : '',
			esc_html__( 'Divi Builder', 'lc-tweaks' ),
			$count
		);
		return $views;
	}
	add_filter( 'views_edit-post', 'dlck_divi_helper_builder_admin_views' );
	add_filter( 'views_edit-page', 'dlck_divi_helper_builder_admin_views' );

	function dlck_divi_helper_builder_admin_query( WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() || ! dlck_divi_helper_enabled( 'dlck_divi_posts_builder_filter' ) ) {
			return;
		}
		$post_type = $query->get( 'post_type' );
		$post_type = is_array( $post_type ) ? reset( $post_type ) : $post_type;
		if ( ! in_array( $post_type ?: 'post', array( 'post', 'page' ), true ) ) {
			return;
		}
		$value = isset( $_GET['dlck_divi_builder'] ) ? sanitize_key( wp_unslash( $_GET['dlck_divi_builder'] ) ) : '';
		if ( $value === 'on' ) {
			$query->set( 'meta_key', '_et_pb_use_builder' );
			$query->set( 'meta_value', 'on' );
		} elseif ( $value === 'off' ) {
			$meta_query = (array) $query->get( 'meta_query' );
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => '_et_pb_use_builder',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_et_pb_use_builder',
					'value'   => 'on',
					'compare' => '!=',
				),
			);
			$query->set( 'meta_query', $meta_query );
		}
	}
	add_action( 'pre_get_posts', 'dlck_divi_helper_builder_admin_query' );
}

if ( ! function_exists( 'dlck_divi_helper_default_thumbnail_id' ) ) {
	function dlck_divi_helper_default_featured_image_id_for_type( string $post_type ): int {
		if ( $post_type === 'page' && dlck_divi_helper_enabled( 'dlck_default_featured_image_pages_enabled' ) ) {
			return absint( dlck_get_option( 'dlck_default_featured_image_pages' ) );
		}
		if ( $post_type === 'post' && dlck_divi_helper_enabled( 'dlck_default_featured_image_posts_enabled' ) ) {
			return absint( dlck_get_option( 'dlck_default_featured_image_posts' ) );
		}
		return 0;
	}

	function dlck_divi_helper_default_thumbnail_id( $thumbnail_id, $post = null ) {
		$post_id = $post instanceof WP_Post ? $post->ID : absint( $post );
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		$post_type = $post_id ? get_post_type( $post_id ) : '';
		if ( $thumbnail_id || ! in_array( $post_type, array( 'post', 'page' ), true ) ) {
			return $thumbnail_id;
		}

		return dlck_divi_helper_default_featured_image_id_for_type( $post_type ) ?: $thumbnail_id;
	}
	add_filter( 'post_thumbnail_id', 'dlck_divi_helper_default_thumbnail_id', 10, 2 );

	function dlck_divi_helper_default_featured_image_url_for_post( int $post_id ): string {
		$post_type     = $post_id ? get_post_type( $post_id ) : '';
		$attachment_id = $post_type ? dlck_divi_helper_default_featured_image_id_for_type( $post_type ) : 0;
		if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
			return '';
		}
		$url = wp_get_attachment_image_url( $attachment_id, 'full' );
		return is_string( $url ) ? $url : '';
	}

	function dlck_divi_helper_default_featured_image_divi4( $content, $settings, $post_id, $context = null, $overrides = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( is_string( $content ) && trim( wp_strip_all_tags( $content ) ) !== '' ) {
			return $content;
		}
		$url = dlck_divi_helper_default_featured_image_url_for_post( absint( $post_id ) );
		return $url !== '' ? $url : $content;
	}
	add_filter( 'et_builder_resolve_dynamic_content_post_featured_image', 'dlck_divi_helper_default_featured_image_divi4', 20, 5 );

	function dlck_divi_helper_default_featured_image_divi5( $value, $data_args = array() ) {
		if ( is_string( $value ) && trim( wp_strip_all_tags( $value ) ) !== '' ) {
			return $value;
		}
		$post_id = 0;
		if ( is_array( $data_args ) ) {
			foreach ( array( 'post_id', 'postId', 'id' ) as $key ) {
				if ( ! empty( $data_args[ $key ] ) ) {
					$post_id = absint( $data_args[ $key ] );
					break;
				}
			}
		}
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		$url = dlck_divi_helper_default_featured_image_url_for_post( absint( $post_id ) );
		return $url !== '' ? $url : $value;
	}
	add_filter( 'divi_module_dynamic_content_resolved_value_post_featured_image', 'dlck_divi_helper_default_featured_image_divi5', 20, 2 );
}

if ( ! function_exists( 'dlck_divi_helper_editor_back_link' ) ) {
	function dlck_divi_helper_editor_back_link(): void {
		if ( ! is_admin() || ! dlck_divi_helper_enabled( 'dlck_divi_editor_back_links' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
			return;
		}
		$post_type = $screen->post_type ?: 'post';
		$url       = $post_type === 'et_pb_layout' ? admin_url( 'edit.php?post_type=et_pb_layout' ) : admin_url( 'edit.php?post_type=' . $post_type );
		printf(
			'<div class="notice notice-info"><p><a href="%1$s">&larr; %2$s</a></p></div>',
			esc_url( $url ),
			esc_html__( 'Back to list', 'lc-tweaks' )
		);
	}
	add_action( 'admin_notices', 'dlck_divi_helper_editor_back_link' );
}

if ( ! function_exists( 'dlck_divi_helper_admin_notes_meta_box' ) ) {
	function dlck_divi_helper_admin_notes_meta_box(): void {
		if ( ! dlck_divi_helper_enabled( 'dlck_admin_notes_enabled' ) ) {
			return;
		}
		foreach ( array( 'post', 'page', 'project', 'et_pb_layout' ) as $post_type ) {
			add_meta_box( 'dlck_admin_notes', __( 'Admin Notes', 'lc-tweaks' ), 'dlck_divi_helper_render_admin_notes_meta_box', $post_type, 'side', 'default' );
		}
	}
	add_action( 'add_meta_boxes', 'dlck_divi_helper_admin_notes_meta_box' );

	function dlck_divi_helper_render_admin_notes_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'dlck_admin_notes_save', 'dlck_admin_notes_nonce' );
		$value = (string) get_post_meta( $post->ID, '_dlck_admin_notes', true );
		echo '<textarea name="dlck_admin_notes" rows="8" style="width:100%;">' . esc_textarea( $value ) . '</textarea>';
	}

	function dlck_divi_helper_save_admin_notes( int $post_id ): void {
		if ( ! dlck_divi_helper_enabled( 'dlck_admin_notes_enabled' ) || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		$nonce = isset( $_POST['dlck_admin_notes_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['dlck_admin_notes_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'dlck_admin_notes_save' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$value = isset( $_POST['dlck_admin_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['dlck_admin_notes'] ) ) : '';
		if ( $value === '' ) {
			delete_post_meta( $post_id, '_dlck_admin_notes' );
		} else {
			update_post_meta( $post_id, '_dlck_admin_notes', $value );
		}
	}
	add_action( 'save_post', 'dlck_divi_helper_save_admin_notes' );
}

if ( ! function_exists( 'dlck_divi_helper_render_layout_shortcode' ) ) {
	function dlck_divi_helper_render_layout_shortcode( array $atts ): string {
		if ( ! dlck_divi_helper_enabled( 'dlck_divi_library_shortcode_widget' ) ) {
			return '';
		}
		$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'dlck_divi_layout' );
		$id   = absint( $atts['id'] );
		if ( ! $id || get_post_type( $id ) !== 'et_pb_layout' ) {
			return '';
		}
		$content = get_post_field( 'post_content', $id );
		return $content ? apply_filters( 'the_content', $content ) : '';
	}
	add_shortcode( 'dlck_divi_layout', 'dlck_divi_helper_render_layout_shortcode' );

	class DLCK_Divi_Library_Widget extends WP_Widget {
		public function __construct() {
			parent::__construct(
				'dlck_divi_library_widget',
				__( 'LC Divi Library Layout', 'lc-tweaks' ),
				array( 'description' => __( 'Render a Divi Library layout by ID.', 'lc-tweaks' ) )
			);
		}

		public function widget( $args, $instance ): void {
			if ( ! dlck_divi_helper_enabled( 'dlck_divi_library_shortcode_widget' ) ) {
				return;
			}
			$id = isset( $instance['layout_id'] ) ? absint( $instance['layout_id'] ) : 0;
			if ( ! $id ) {
				return;
			}
			echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo do_shortcode( '[dlck_divi_layout id="' . absint( $id ) . '"]' );
			echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		public function form( $instance ): void {
			$id = isset( $instance['layout_id'] ) ? absint( $instance['layout_id'] ) : 0;
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'layout_id' ) ); ?>"><?php esc_html_e( 'Divi Library layout ID:', 'lc-tweaks' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'layout_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'layout_id' ) ); ?>" type="number" value="<?php echo esc_attr( $id ); ?>" min="1" />
			</p>
			<?php
		}

		public function update( $new_instance, $old_instance ): array {
			$instance              = is_array( $old_instance ) ? $old_instance : array();
			$instance['layout_id'] = isset( $new_instance['layout_id'] ) ? absint( $new_instance['layout_id'] ) : 0;
			return $instance;
		}
	}

	function dlck_divi_helper_register_layout_widget(): void {
		if ( dlck_divi_helper_enabled( 'dlck_divi_library_shortcode_widget' ) ) {
			register_widget( 'DLCK_Divi_Library_Widget' );
		}
	}
	add_action( 'widgets_init', 'dlck_divi_helper_register_layout_widget' );
}

if ( ! function_exists( 'dlck_divi_helper_allow_shortcodes_in_menus' ) ) {
	function dlck_divi_helper_allow_shortcodes_in_menus( string $item_output ): string {
		return dlck_divi_helper_enabled( 'dlck_shortcode_in_menus' ) ? do_shortcode( $item_output ) : $item_output;
	}
	add_filter( 'walker_nav_menu_start_el', 'dlck_divi_helper_allow_shortcodes_in_menus' );
}

if ( ! function_exists( 'dlck_divi_helper_duplicate_action_links' ) ) {
	function dlck_divi_helper_duplicate_action_links( array $actions, WP_Post $post ): array {
		$allowed = false;
		if ( in_array( $post->post_type, array( 'post', 'page' ), true ) && dlck_divi_helper_enabled( 'dlck_duplicate_posts_pages' ) ) {
			$allowed = true;
		}
		if ( $post->post_type === 'et_pb_layout' && dlck_divi_helper_enabled( 'dlck_duplicate_divi_library_layouts' ) ) {
			$allowed = true;
		}
		if ( ! $allowed || ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}
		$url = wp_nonce_url( admin_url( 'admin.php?action=dlck_duplicate_post&post=' . absint( $post->ID ) ), 'dlck_duplicate_post_' . $post->ID );
		$actions['dlck_duplicate'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Duplicate', 'lc-tweaks' ) . '</a>';
		return $actions;
	}
	add_filter( 'post_row_actions', 'dlck_divi_helper_duplicate_action_links', 10, 2 );
	add_filter( 'page_row_actions', 'dlck_divi_helper_duplicate_action_links', 10, 2 );

	function dlck_divi_helper_duplicate_post_action(): void {
		$post_id = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0;
		if ( ! $post_id || ! wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '', 'dlck_duplicate_post_' . $post_id ) ) {
			wp_die( esc_html__( 'Could not duplicate item.', 'lc-tweaks' ) );
		}
		$post = get_post( $post_id );
		if ( ! $post || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Could not duplicate item.', 'lc-tweaks' ) );
		}
		$allowed = ( in_array( $post->post_type, array( 'post', 'page' ), true ) && dlck_divi_helper_enabled( 'dlck_duplicate_posts_pages' ) )
			|| ( $post->post_type === 'et_pb_layout' && dlck_divi_helper_enabled( 'dlck_duplicate_divi_library_layouts' ) );
		if ( ! $allowed ) {
			wp_die( esc_html__( 'Duplicate is disabled.', 'lc-tweaks' ) );
		}
		$new_post_id = wp_insert_post(
			array(
				'post_author'  => get_current_user_id(),
				'post_content' => $post->post_content,
				'post_excerpt' => $post->post_excerpt,
				'post_parent'  => $post->post_parent,
				'post_status'  => 'draft',
				'post_title'   => $post->post_title . ' ' . __( '(Copy)', 'lc-tweaks' ),
				'post_type'    => $post->post_type,
				'menu_order'   => $post->menu_order,
			)
		);
		if ( is_wp_error( $new_post_id ) || ! $new_post_id ) {
			wp_die( esc_html__( 'Could not duplicate item.', 'lc-tweaks' ) );
		}
		foreach ( get_post_meta( $post_id ) as $key => $values ) {
			foreach ( $values as $value ) {
				add_post_meta( $new_post_id, $key, maybe_unserialize( $value ) );
			}
		}
		foreach ( get_object_taxonomies( $post->post_type ) as $taxonomy ) {
			$terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
			if ( ! is_wp_error( $terms ) ) {
				wp_set_object_terms( $new_post_id, $terms, $taxonomy );
			}
		}
		wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . absint( $new_post_id ) ) );
		exit;
	}
	add_action( 'admin_action_dlck_duplicate_post', 'dlck_divi_helper_duplicate_post_action' );
}

if ( ! function_exists( 'dlck_divi_helper_text_replacement' ) ) {
	function dlck_divi_helper_text_replacement( string $content ): string {
		if ( ! dlck_divi_helper_enabled( 'dlck_text_replacement_enabled' ) ) {
			return $content;
		}
		foreach ( dlck_divi_helper_parse_lines( dlck_get_option( 'dlck_text_replacement_rules', '' ) ) as $line ) {
			$parts = array_map( 'trim', explode( '|', $line, 2 ) );
			if ( count( $parts ) === 2 && $parts[0] !== '' ) {
				$content = str_replace( $parts[0], $parts[1], $content );
			}
		}
		return $content;
	}
	add_filter( 'the_content', 'dlck_divi_helper_text_replacement', 20 );
}

if ( ! function_exists( 'dlck_divi_helper_back_to_top_button' ) ) {
	function dlck_divi_helper_back_to_top_button(): void {
		if ( dlck_divi_helper_enabled( 'dlck_back_to_top_customizer_enabled' ) ) {
			echo '<button type="button" id="dlck-back-to-top" aria-label="' . esc_attr__( 'Back to top', 'lc-tweaks' ) . '">&#8593;</button>';
		}
	}
	add_action( 'wp_footer', 'dlck_divi_helper_back_to_top_button', 50 );
}

if ( ! function_exists( 'dlck_divi_helper_upload_size_limit' ) ) {
	function dlck_divi_helper_upload_size_limit( $size ) {
		if ( ! dlck_divi_helper_enabled( 'dlck_media_max_upload_size_enabled' ) || ! current_user_can( 'upload_files' ) ) {
			return $size;
		}
		$kb = absint( dlck_get_option( 'dlck_media_max_upload_size_kb', 10240 ) );
		return $kb > 0 ? $kb * 1024 : $size;
	}
	add_filter( 'upload_size_limit', 'dlck_divi_helper_upload_size_limit' );
}

if ( ! function_exists( 'dlck_divi_helper_svg_img_class' ) ) {
	function dlck_divi_helper_svg_img_class( array $attr, WP_Post $attachment ): array {
		if ( ! dlck_divi_helper_enabled( 'dlck_svg_img_class_enabled' ) ) {
			return $attr;
		}
		$mime = get_post_mime_type( $attachment );
		if ( $mime === 'image/svg+xml' ) {
			$attr['class'] = trim( ( $attr['class'] ?? '' ) . ' style-svg' );
		}
		return $attr;
	}
	add_filter( 'wp_get_attachment_image_attributes', 'dlck_divi_helper_svg_img_class', 10, 2 );
}

if ( ! function_exists( 'dlck_divi_helper_clean_filename_text' ) ) {
	function dlck_divi_helper_clean_filename_text( string $filename ): string {
		$name = pathinfo( $filename, PATHINFO_FILENAME );
		$name = preg_replace( '/[-_.]+/', ' ', $name );
		$name = preg_replace( '/\b\d+\b/', '', (string) $name );
		$name = trim( preg_replace( '/\s+/', ' ', (string) $name ) );
		return $name !== '' ? ucwords( strtolower( $name ) ) : '';
	}

	function dlck_divi_helper_fill_attachment_text( int $attachment_id ): void {
		if ( ! dlck_divi_helper_enabled( 'dlck_media_filename_metadata_enabled' ) || ! wp_attachment_is_image( $attachment_id ) ) {
			return;
		}
		$file = get_attached_file( $attachment_id );
		if ( ! is_string( $file ) || $file === '' ) {
			return;
		}
		$text     = dlck_divi_helper_clean_filename_text( basename( $file ) );
		$override = dlck_divi_helper_enabled( 'dlck_media_filename_metadata_override' );
		if ( $text === '' ) {
			return;
		}
		$post = get_post( $attachment_id );
		if ( $post && ( $override || $post->post_title === '' || preg_match( '/[-_]/', $post->post_title ) ) ) {
			wp_update_post(
				array(
					'ID'           => $attachment_id,
					'post_title'   => $text,
					'post_excerpt' => $override || $post->post_excerpt === '' ? $text : $post->post_excerpt,
					'post_content' => $override || $post->post_content === '' ? $text : $post->post_content,
				)
			);
		}
		$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		if ( $override || ! is_string( $alt ) || $alt === '' ) {
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', $text );
		}
	}
	add_action( 'add_attachment', 'dlck_divi_helper_fill_attachment_text' );
	add_action( 'edit_attachment', 'dlck_divi_helper_fill_attachment_text' );
}

if ( ! function_exists( 'dlck_divi_helper_cron_schedules' ) ) {
	function dlck_divi_helper_cron_schedules( array $schedules ): array {
		$schedules['weekly'] = array(
			'interval' => WEEK_IN_SECONDS,
			'display'  => __( 'Once Weekly', 'lc-tweaks' ),
		);
		$schedules['monthly'] = array(
			'interval' => 30 * DAY_IN_SECONDS,
			'display'  => __( 'Once Monthly', 'lc-tweaks' ),
		);
		return $schedules;
	}
	add_filter( 'cron_schedules', 'dlck_divi_helper_cron_schedules' );

	function dlck_divi_helper_sync_cache_schedule(): void {
		$schedule = sanitize_key( (string) dlck_get_option( 'dlck_divi_cache_auto_schedule', 'none' ) );
		if ( ! dlck_divi_helper_enabled( 'dlck_clear_divi_static_css_cache_local_storage' ) || ! in_array( $schedule, array( 'hourly', 'twicedaily', 'daily', 'weekly', 'monthly' ), true ) ) {
			wp_clear_scheduled_hook( 'dlck_divi_cache_scheduled_clear' );
			return;
		}
		$current = wp_get_schedule( 'dlck_divi_cache_scheduled_clear' );
		if ( $current !== $schedule ) {
			wp_clear_scheduled_hook( 'dlck_divi_cache_scheduled_clear' );
			wp_schedule_event( time() + 300, $schedule, 'dlck_divi_cache_scheduled_clear' );
		}
	}
	add_action( 'init', 'dlck_divi_helper_sync_cache_schedule', 30 );
	add_action( 'update_option_dlck_lc_kit', 'dlck_divi_helper_sync_cache_schedule', 30 );

	function dlck_divi_helper_run_scheduled_cache_clear(): void {
		if ( function_exists( 'dlck_csc_clear_static_css_generation' ) ) {
			dlck_csc_clear_static_css_generation();
		}
		if ( function_exists( 'dlck_rebuild_all_inline_caches' ) ) {
			dlck_rebuild_all_inline_caches();
		}
	}
	add_action( 'dlck_divi_cache_scheduled_clear', 'dlck_divi_helper_run_scheduled_cache_clear' );
}

if ( ! function_exists( 'dlck_divi_helper_path_matches' ) ) {
	function dlck_divi_helper_path_matches( string $pattern, string $path ): bool {
		$pattern = '/' . ltrim( trim( $pattern ), '/' );
		$path    = '/' . ltrim( trim( $path ), '/' );
		$regex   = '#^' . str_replace( '\*', '.*', preg_quote( untrailingslashit( $pattern ), '#' ) ) . '/?$#';
		return (bool) preg_match( $regex, untrailingslashit( $path ) );
	}

	function dlck_divi_helper_client_ip(): string {
		foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $key ) {
			if ( empty( $_SERVER[ $key ] ) ) {
				continue;
			}
			$value = trim( explode( ',', (string) wp_unslash( $_SERVER[ $key ] ) )[0] );
			if ( filter_var( $value, FILTER_VALIDATE_IP ) ) {
				return $value;
			}
		}
		return '';
	}

	function dlck_divi_helper_render_site_availability(): void {
		$mode = sanitize_key( (string) dlck_get_option( 'dlck_site_availability_mode', 'off' ) );
		if ( ! in_array( $mode, array( 'coming_soon', 'maintenance' ), true ) || ( function_exists( 'dlck_scope_rules_allow_option' ) && ! dlck_scope_rules_allow_option( 'dlck_site_availability_mode' ) ) || is_admin() || wp_doing_ajax() || is_customize_preview() ) {
			return;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}
		$layout_id = absint( dlck_get_option( 'dlck_site_availability_layout_id' ) );
		if ( ! $layout_id ) {
			return;
		}

		$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
		$path = (string) wp_parse_url( $path, PHP_URL_PATH );
		foreach ( dlck_divi_helper_parse_lines( dlck_get_option( 'dlck_site_availability_excluded_paths', '' ) ) as $pattern ) {
			if ( dlck_divi_helper_path_matches( $pattern, $path ) ) {
				return;
			}
		}

		$client_ip = dlck_divi_helper_client_ip();
		if ( $client_ip !== '' && in_array( $client_ip, dlck_divi_helper_parse_lines( dlck_get_option( 'dlck_site_availability_allowed_ips', '' ) ), true ) ) {
			return;
		}

		if ( dlck_divi_helper_enabled( 'dlck_site_availability_bypass_enabled' ) ) {
			$token = sanitize_text_field( (string) dlck_get_option( 'dlck_site_availability_bypass_token', '' ) );
			$given = isset( $_GET['dlck_bypass'] ) ? sanitize_text_field( wp_unslash( $_GET['dlck_bypass'] ) ) : '';
			if ( $token !== '' && hash_equals( $token, $given ) ) {
				return;
			}
		}

		if ( $mode === 'maintenance' ) {
			status_header( 503 );
			header( 'Retry-After: 240' );
		} else {
			status_header( 200 );
		}
		nocache_headers();

		if ( ! defined( 'DLCK_MAINTENANCE_LAYOUT_ACTIVE' ) ) {
			define( 'DLCK_MAINTENANCE_LAYOUT_ACTIVE', true );
		}
		$dlck_maintenance_layout_val = $layout_id;
		include DLCK_LC_KIT_PLUGIN_DIR . 'functions/divi-tweaks/show-maintenance-comingsoon-or-notice-page.php';
		exit;
	}
	add_action( 'template_redirect', 'dlck_divi_helper_render_site_availability', 1 );
}

if ( ! function_exists( 'dlck_divi_helper_project_labels_value' ) ) {
	function dlck_divi_helper_project_labels_value( string $option_key, string $fallback ): string {
		$value = dlck_get_option( $option_key, $fallback );
		if ( ! is_scalar( $value ) ) {
			$value = $fallback;
		}

		$value = sanitize_text_field( (string) $value );
		return $value !== '' ? $value : $fallback;
	}

	function dlck_divi_helper_project_slug_value( string $option_key, string $fallback ): string {
		$value = dlck_get_option( $option_key, $fallback );
		if ( ! is_scalar( $value ) ) {
			$value = $fallback;
		}
		$value = sanitize_title( (string) $value );
		return $value !== '' ? $value : $fallback;
	}

	function dlck_divi_helper_project_rename_post_type_args( array $args, string $post_type ): array {
		if ( ! dlck_divi_helper_enabled( 'dlck_divi_project_rename' ) || $post_type !== 'project' ) {
			return $args;
		}

		$plural = dlck_divi_helper_project_labels_value( 'dlck_divi_project_plural_name', __( 'Projects', 'lc-tweaks' ) );
		$singular = dlck_divi_helper_project_labels_value( 'dlck_divi_project_singular_name', __( 'Project', 'lc-tweaks' ) );
		$slug = dlck_divi_helper_project_slug_value( 'dlck_divi_project_slug', __( 'projects', 'lc-tweaks' ) );

		$args['labels'] = array(
			'name'                  => $plural,
			'singular_name'         => $singular,
			'add_new_item'          => sprintf( __( 'Add New %s', 'lc-tweaks' ), $singular ),
			'edit_item'             => sprintf( __( 'Edit %s', 'lc-tweaks' ), $singular ),
			'new_item'              => sprintf( __( 'New %s', 'lc-tweaks' ), $singular ),
			'all_items'             => sprintf( __( 'All %s', 'lc-tweaks' ), $plural ),
			'view_item'             => sprintf( __( 'View %s', 'lc-tweaks' ), $singular ),
			'search_items'          => sprintf( __( 'Search %s', 'lc-tweaks' ), $plural ),
			'not_found'             => sprintf( __( 'No %s found', 'lc-tweaks' ), strtolower( $plural ) ),
			'not_found_in_trash'    => sprintf( __( 'No %s found in Trash', 'lc-tweaks' ), strtolower( $plural ) ),
		);

		$args['rewrite'] = array_merge(
			is_array( $args['rewrite'] ?? null ) ? (array) $args['rewrite'] : array(),
			array( 'slug' => $slug )
		);

		return $args;
	}

	function dlck_divi_helper_project_rename_taxonomy_args( array $args, string $taxonomy ): array {
		if ( ! dlck_divi_helper_enabled( 'dlck_divi_project_rename' ) ) {
			return $args;
		}

		if ( $taxonomy === 'project_category' ) {
			$plural = dlck_divi_helper_project_labels_value( 'dlck_divi_project_plural_category', __( 'Project Categories', 'lc-tweaks' ) );
			$singular = dlck_divi_helper_project_labels_value( 'dlck_divi_project_singular_category', __( 'Project Category', 'lc-tweaks' ) );
			$slug = dlck_divi_helper_project_slug_value( 'dlck_divi_project_category_slug', __( 'project_category', 'lc-tweaks' ) );

			$args['labels'] = array(
				'name'                  => $plural,
				'singular_name'         => $singular,
				'search_items'          => sprintf( __( 'Search %s', 'lc-tweaks' ), $plural ),
				'all_items'             => sprintf( __( 'All %s', 'lc-tweaks' ), $plural ),
				'parent_item'           => sprintf( __( 'Parent %s', 'lc-tweaks' ), $singular ),
				'parent_item_colon'     => sprintf( __( 'Parent %s:', 'lc-tweaks' ), $singular ),
				'edit_item'             => sprintf( __( 'Edit %s', 'lc-tweaks' ), $singular ),
				'update_item'           => sprintf( __( 'Update %s', 'lc-tweaks' ), $singular ),
				'add_new_item'          => sprintf( __( 'Add New %s', 'lc-tweaks' ), $singular ),
				'new_item_name'         => sprintf( __( 'New %s Name', 'lc-tweaks' ), $singular ),
				'menu_name'             => $plural,
				'not_found'             => sprintf( __( 'You currently don\'t have any %s.', 'lc-tweaks' ), strtolower( $plural ) ),
			);

			$args['rewrite'] = array_merge(
				is_array( $args['rewrite'] ?? null ) ? (array) $args['rewrite'] : array(),
				array( 'slug' => $slug )
			);
		}

		if ( $taxonomy === 'project_tag' ) {
			$plural = dlck_divi_helper_project_labels_value( 'dlck_divi_project_plural_tag', __( 'Project Tags', 'lc-tweaks' ) );
			$singular = dlck_divi_helper_project_labels_value( 'dlck_divi_project_singular_tag', __( 'Project Tag', 'lc-tweaks' ) );
			$slug = dlck_divi_helper_project_slug_value( 'dlck_divi_project_tag_slug', __( 'project_tag', 'lc-tweaks' ) );

			$args['labels'] = array(
				'name'                  => $plural,
				'singular_name'         => $singular,
				'search_items'          => sprintf( __( 'Search %s', 'lc-tweaks' ), $plural ),
				'all_items'             => sprintf( __( 'All %s', 'lc-tweaks' ), $plural ),
				'parent_item'           => sprintf( __( 'Parent %s', 'lc-tweaks' ), $singular ),
				'parent_item_colon'     => sprintf( __( 'Parent %s:', 'lc-tweaks' ), $singular ),
				'edit_item'             => sprintf( __( 'Edit %s', 'lc-tweaks' ), $singular ),
				'update_item'           => sprintf( __( 'Update %s', 'lc-tweaks' ), $singular ),
				'add_new_item'          => sprintf( __( 'Add New %s', 'lc-tweaks' ), $singular ),
				'new_item_name'         => sprintf( __( 'New %s Name', 'lc-tweaks' ), $singular ),
				'menu_name'             => $plural,
				'not_found'             => sprintf( __( 'You currently don\'t have any  %s.', 'lc-tweaks' ), strtolower( $plural ) ),
			);

			$args['rewrite'] = array_merge(
				is_array( $args['rewrite'] ?? null ) ? (array) $args['rewrite'] : array(),
				array( 'slug' => $slug )
			);
		}

		return $args;
	}

	add_action(
		'init',
		static function (): void {
			if ( ! dlck_divi_helper_enabled( 'dlck_divi_project_rename' ) ) {
				return;
			}

			add_filter( 'register_post_type_args', 'dlck_divi_helper_project_rename_post_type_args', 20, 2 );
			add_filter( 'register_taxonomy_args', 'dlck_divi_helper_project_rename_taxonomy_args', 20, 2 );
		},
		20
	);

	function dlck_divi_helper_image_module_attachment_id_by_url( string $url ): int {
		static $cache = array();
		if ( isset( $cache[ $url ] ) ) {
			return $cache[ $url ];
		}

		$url = trim( $url );
		if ( '' === $url ) {
			$cache[ $url ] = 0;
			return 0;
		}

		$attachment_id = attachment_url_to_postid( $url );
		if ( $attachment_id ) {
			$cache[ $url ] = (int) $attachment_id;
			return (int) $attachment_id;
		}

		$without_size = preg_replace( '/-(\d+)x(\d+)\./', '.', $url );
		if ( is_string( $without_size ) ) {
			$attachment_id = attachment_url_to_postid( $without_size );
			if ( $attachment_id ) {
				$cache[ $url ] = (int) $attachment_id;
				return (int) $attachment_id;
			}
		}

		$clean_url = strtok( $url, '?' );
		$scaled = '';
		if ( is_string( $clean_url ) ) {
			$scaled = preg_replace( '/(\.[A-Za-z0-9]+)$/', '-scaled$1', preg_replace( '/-(\d+)x(\d+)\./', '.', $clean_url ) );
			if ( is_string( $scaled ) ) {
				$attachment_id = attachment_url_to_postid( $scaled );
				if ( $attachment_id ) {
					$cache[ $url ] = (int) $attachment_id;
					return (int) $attachment_id;
				}
			}
		}

		$parsed_url = wp_parse_url( $url );
		if ( empty( $parsed_url['path'] ) ) {
			$cache[ $url ] = 0;
			return 0;
		}

		global $wpdb;

		$filename = pathinfo( trim( $parsed_url['path'], '/' ), PATHINFO_FILENAME );
		if ( preg_match( '/%[0-9A-Fa-f]{2}/', $filename ) ) {
			$filename = urldecode( $filename );
		}
		$filename = sanitize_file_name( $filename );
		$base_filename = preg_replace( '/-\d+x\d+$/', '', $filename );
		$base_filename = preg_replace( '/-scaled$/', '', $base_filename );

		if ( '' === $base_filename ) {
			$cache[ $url ] = 0;
			return 0;
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.post_id, pm.meta_value
				 FROM $wpdb->postmeta pm
				 INNER JOIN $wpdb->posts p ON pm.post_id = p.ID
				 WHERE pm.meta_key = '_wp_attached_file'
				 AND pm.meta_value LIKE %s
				 AND p.post_type = 'attachment'
				 AND p.post_status = 'inherit'",
				'%' . $wpdb->esc_like( $base_filename ) . '%'
			)
		);
		// phpcs:enable

		if ( ! empty( $results ) ) {
			$candidates = array_unique( array_map( 'strtolower', array( $filename, $base_filename ) ) );
			foreach ( $results as $result ) {
				$meta_filename = strtolower( pathinfo( (string) $result->meta_value, PATHINFO_FILENAME ) );
				if ( in_array( $meta_filename, $candidates, true ) ) {
					$cache[ $url ] = (int) $result->post_id;
					return (int) $result->post_id;
				}
			}
		}

		$cache[ $url ] = 0;
		return 0;
	}

	function dlck_divi_helper_image_module_library_meta( string $image_url ): array {
		static $cache = array();
		if ( isset( $cache[ $image_url ] ) ) {
			return $cache[ $image_url ];
		}

		$meta = array(
			'alt'   => '',
			'title' => '',
		);

		$attachment_id = dlck_divi_helper_image_module_attachment_id_by_url( $image_url );
		if ( ! $attachment_id ) {
			$cache[ $image_url ] = $meta;
			return $meta;
		}

		$meta['title'] = get_the_title( $attachment_id );
		$meta['alt']   = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

		$cache[ $image_url ] = $meta;
		return $meta;
	}

	function dlck_divi_helper_image_module_metadata_with_fallback( array $module_value, array $library_meta ): array {
		$module_alt   = isset( $module_value['alt'] ) && is_scalar( $module_value['alt'] ) ? trim( (string) $module_value['alt'] ) : '';
		$module_title = '';
		foreach ( array( 'titleText', 'title' ) as $key ) {
			if ( isset( $module_value[ $key ] ) && is_scalar( $module_value[ $key ] ) && trim( (string) $module_value[ $key ] ) !== '' ) {
				$module_title = trim( (string) $module_value[ $key ] );
				break;
			}
		}

		return array(
			'alt'   => $module_alt !== '' ? $module_alt : (string) ( $library_meta['alt'] ?? '' ),
			'title' => $module_title !== '' ? $module_title : (string) ( $library_meta['title'] ?? '' ),
		);
	}

	function dlck_divi_helper_set_image_tag_attribute( string $tag, string $name, string $value ): string {
		$value = str_replace( '>', '&gt;', esc_attr( $value ) );
		$existing = '/(\s' . preg_quote( $name, '/' ) . ')\s*=\s*(["\']).*?\2/is';
		if ( preg_match( $existing, $tag ) ) {
			return preg_replace_callback(
				$existing,
				static function ( array $matches ) use ( $value ): string {
					return $matches[1] . '="' . $value . '"';
				},
				$tag,
				1
			);
		}

		return preg_replace_callback(
			'/\s*\/?>\s*$/',
			static function ( array $matches ) use ( $name, $value ): string {
				return ' ' . $name . '="' . $value . '"' . $matches[0];
			},
			$tag,
			1
		);
	}

	function dlck_divi_helper_image_tag_attribute_has_value( string $tag, string $name ): bool {
		$pattern = '/\s' . preg_quote( $name, '/' ) . '\s*=\s*(?:(["\'])(.*?)\1|([^\s>]+))/is';
		if ( ! preg_match( $pattern, $tag, $matches ) ) {
			return false;
		}

		$value = isset( $matches[2] ) && $matches[2] !== '' ? $matches[2] : ( $matches[3] ?? '' );
		return is_string( $value ) && trim( htmlspecialchars_decode( $value, ENT_QUOTES ) ) !== '';
	}

	function dlck_divi_helper_set_image_tag_metadata( string $tag, array $meta ): string {
		if ( '' !== ( $meta['alt'] ?? '' ) && ! dlck_divi_helper_image_tag_attribute_has_value( $tag, 'alt' ) ) {
			$tag = dlck_divi_helper_set_image_tag_attribute( $tag, 'alt', (string) $meta['alt'] );
		}
		if ( '' !== ( $meta['title'] ?? '' ) && ! dlck_divi_helper_image_tag_attribute_has_value( $tag, 'title' ) ) {
			$tag = dlck_divi_helper_set_image_tag_attribute( $tag, 'title', (string) $meta['title'] );
		}
		return $tag;
	}

	function dlck_divi_helper_override_image_module_attributes_divi5( string $element, array $args, $instance ): string {
		if ( ! dlck_divi_helper_enabled( 'dlck_divi_img_module' ) || ! is_string( $element ) || $element === '' ) {
			return $element;
		}

		$instance_name = '';
		if ( is_object( $instance ) && isset( $instance->name ) ) {
			$instance_name = (string) $instance->name;
		}
		if ( '' === $instance_name && is_array( $instance ) && isset( $instance['name'] ) ) {
			$instance_name = (string) $instance['name'];
		}

		if ( ( $args['attrName'] ?? '' ) !== 'image' || $instance_name !== 'divi/image' ) {
			return $element;
		}

		$module_attrs = is_object( $instance ) && isset( $instance->module_attrs ) ? (array) $instance->module_attrs : ( is_array( $instance ) && isset( $instance['module_attrs'] ) && is_array( $instance['module_attrs'] ) ? $instance['module_attrs'] : array() );
		$module_value = isset( $module_attrs['image']['innerContent']['desktop']['value'] ) && is_array( $module_attrs['image']['innerContent']['desktop']['value'] ) ? $module_attrs['image']['innerContent']['desktop']['value'] : array();
		$image_src    = isset( $module_value['src'] ) ? (string) $module_value['src'] : '';
		$image_src    = trim( $image_src );
		if ( $image_src === '' ) {
			return $element;
		}

		if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || wp_doing_ajax() || is_admin() || ( function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) ) {
			return $element;
		}

		$meta = dlck_divi_helper_image_module_metadata_with_fallback( $module_value, dlck_divi_helper_image_module_library_meta( $image_src ) );
		if ( '' === $meta['alt'] && '' === $meta['title'] ) {
			return $element;
		}

		$updated = $element;
		$updated = preg_replace_callback(
			'/<img\b(?:"[^"]*"|\'[^\']*\'|[^>"\'])*>/i',
			static function ( array $matches ) use ( $meta ): string {
				return dlck_divi_helper_set_image_tag_metadata( $matches[0], $meta );
			},
			$updated,
			1
		);

		return is_string( $updated ) ? $updated : $element;
	}

	function dlck_divi_helper_override_image_module_attributes_divi5_wrapper( string $module_wrapper, array $args ): string {
		if ( ! dlck_divi_helper_enabled( 'dlck_divi_img_module' ) || ! is_string( $module_wrapper ) || $module_wrapper === '' ) {
			return $module_wrapper;
		}

		$instance_name = isset( $args['name'] ) ? (string) $args['name'] : '';
		if ( 'divi/image' !== $instance_name ) {
			return $module_wrapper;
		}

		if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || wp_doing_ajax() || is_admin() || ( function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) ) {
			return $module_wrapper;
		}

		if ( stripos( $module_wrapper, '<img' ) === false ) {
			return $module_wrapper;
		}

		$module_attrs  = isset( $args['attrs'] ) && is_array( $args['attrs'] ) ? (array) $args['attrs'] : array();
		$target_src    = '';
		$wrapper_meta  = array(
			'alt'   => '',
			'title' => '',
		);
		$target_raw    = isset( $module_attrs['image']['innerContent']['desktop']['value']['src'] ) ? (string) $module_attrs['image']['innerContent']['desktop']['value']['src'] : '';
		$target_raw    = trim( $target_raw );
		$target_cmp    = '';
		if ( '' === $target_raw && isset( $args['module_attrs'] ) && is_array( $args['module_attrs'] ) ) {
			$module_attrs = (array) $args['module_attrs'];
			$target_raw = isset( $args['module_attrs']['image']['innerContent']['desktop']['value']['src'] ) ? (string) $args['module_attrs']['image']['innerContent']['desktop']['value']['src'] : '';
			$target_raw = trim( $target_raw );
		}
		$module_value = isset( $module_attrs['image']['innerContent']['desktop']['value'] ) && is_array( $module_attrs['image']['innerContent']['desktop']['value'] ) ? $module_attrs['image']['innerContent']['desktop']['value'] : array();

		if ( $target_raw !== '' ) {
			$target_src = $target_raw;
			$target_cmp = preg_replace( '/\?.*$/', '', $target_raw );
			$target_cmp = is_string( $target_cmp ) ? trim( $target_cmp ) : '';
			$wrapper_meta = dlck_divi_helper_image_module_metadata_with_fallback( $module_value, dlck_divi_helper_image_module_library_meta( $target_src ) );
			if ( '' === $wrapper_meta['alt'] && '' === $wrapper_meta['title'] ) {
				return $module_wrapper;
			}
		}

		$updated = preg_replace_callback(
			'/<img\b(?:"[^"]*"|\'[^\']*\'|[^>"\'])*>/i',
			static function ( array $matches ) use ( $target_src, $target_cmp, $wrapper_meta ): string {
				$tag = $matches[0];
				if ( ! preg_match( '/\bsrc\s*=\s*(?:(["\'])((?:(?!\1).)*)\1|([^\s>]+))/i', $tag, $src_match ) ) {
					return $tag;
				}

				$tag_src = is_string( $src_match[2] ?? '' ) && '' !== (string) $src_match[2] ? (string) $src_match[2] : ( is_string( $src_match[3] ?? '' ) ? (string) $src_match[3] : '' );
				$tag_src = trim( $tag_src );
				if ( '' === $tag_src ) {
					return $tag;
				}

				if ( '' !== $target_src ) {
					$cmp = preg_replace( '/\?.*$/', '', $tag_src );
					$cmp = is_string( $cmp ) ? trim( $cmp ) : '';
					if ( '' === $cmp || $cmp !== $target_cmp ) {
						return $tag;
					}

					if ( '' === trim( $wrapper_meta['alt'] ) && '' === trim( $wrapper_meta['title'] ) ) {
						return $tag;
					}

					return dlck_divi_helper_set_image_tag_metadata( $tag, $wrapper_meta );
				}

				$meta = dlck_divi_helper_image_module_library_meta( $tag_src );
				if ( '' === trim( $meta['alt'] ) && '' === trim( $meta['title'] ) ) {
					return $tag;
				}

				return dlck_divi_helper_set_image_tag_metadata( $tag, $meta );
			},
			$module_wrapper
		);

		return is_string( $updated ) ? $updated : $module_wrapper;
	}

	function dlck_divi_helper_override_image_module_attributes_divi4( array $props, array $attrs, string $render_slug, $_address = null, $content = null ): array {
		if ( ! dlck_divi_helper_enabled( 'dlck_divi_img_module' ) || ! in_array( $render_slug, array( 'et_pb_image', 'et_pb_fullwidth_image' ), true ) ) {
			return $props;
		}

		if ( ( function_exists( 'et_fb_is_enabled' ) && et_fb_is_enabled() ) || ( function_exists( 'et_builder_bfb_enabled' ) && et_builder_bfb_enabled() ) ) {
			return $props;
		}

		$image_src = isset( $props['src'] ) ? trim( (string) $props['src'] ) : '';
		if ( '' === $image_src ) {
			return $props;
		}

		$meta = dlck_divi_helper_image_module_library_meta( $image_src );
		if ( '' !== $meta['alt'] && ( ! isset( $props['alt'] ) || trim( (string) $props['alt'] ) === '' ) ) {
			$props['alt'] = $meta['alt'];
		}
		if ( '' !== $meta['title'] && ( ! isset( $props['title'] ) || trim( (string) $props['title'] ) === '' ) ) {
			$props['title'] = $meta['title'];
		}

		return $props;
	}

	add_filter( 'divi_module_elements_render', 'dlck_divi_helper_override_image_module_attributes_divi5', 10, 3 );
	add_filter( 'divi_module_wrapper_render', 'dlck_divi_helper_override_image_module_attributes_divi5_wrapper', 10, 2 );
	add_filter( 'et_pb_module_shortcode_attributes', 'dlck_divi_helper_override_image_module_attributes_divi4', 10, 5 );
}
