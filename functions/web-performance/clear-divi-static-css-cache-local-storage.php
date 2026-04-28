<?php
/**
 * @package Clear Divi Static CSS Cache + Local Storage
 * @version 1.0
 * https://www.peeayecreative.com/how-to-add-a-clear-divi-static-css-cache-local-storage-button-to-the-wordpress-admin-bar/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dlck_csc_lazy_cache_enabled' ) ) :
	/**
	 * Check if the lazy-load cache feature is enabled.
	 */
	function dlck_csc_lazy_cache_enabled(): bool {
		return function_exists( 'dlck_get_option' ) && dlck_get_option( 'dlck_divi_lazy_loading' ) === '1';
	}
endif;

if ( ! function_exists( 'dlck_csc_main_menu_enabled' ) ) :
	/**
	 * Check if the plugin's Clear Divi Cache feature is enabled.
	 */
	function dlck_csc_main_menu_enabled(): bool {
		return function_exists( 'dlck_get_option' ) && dlck_get_option( 'dlck_clear_divi_static_css_cache_local_storage' ) === '1';
	}
endif;

if ( ! function_exists( 'dlck_csc_post_save_builder_exit_enabled' ) ) :
	/**
	 * Check if post-save and Visual Builder-exit hook clears are enabled.
	 */
	function dlck_csc_post_save_builder_exit_enabled(): bool {
		return dlck_csc_main_menu_enabled()
			&& function_exists( 'dlck_get_option' )
			&& dlck_get_option( 'dlck_auto_clear_cache_after_post_save_builder_exit' ) === '1';
	}
endif;

if ( ! function_exists( 'dlck_csc_build_clear_all_menu_title' ) ) :
	/**
	 * Build the admin-bar title markup used for the automated cache clear item.
	 */
	function dlck_csc_build_clear_all_menu_title(): string {
		return sprintf(
			'<span data-allnonce="%1$s" data-staticnonce="%2$s" data-lazynonce="%3$s" data-lazyenabled="%4$s">%5$s</span>',
			esc_attr( wp_create_nonce( 'dlck_misc_clear_all_cache' ) ),
			esc_attr( wp_create_nonce( 'dlck_misc_clear_static_css' ) ),
			esc_attr( wp_create_nonce( 'dlck_clear_lazy_cache' ) ),
			dlck_csc_lazy_cache_enabled() ? '1' : '0',
			esc_html__( 'Clear All Caches (Automated)', 'divi-lc-kit' )
		);
	}
endif;

if ( ! function_exists( 'dlck_csc_clear_static_css_generation' ) ) :
	/**
	 * Clear Divi static CSS file generation cache.
	 */
	function dlck_csc_clear_static_css_generation(): bool {
		if ( ! class_exists( 'ET_Core_PageResource' ) || ! method_exists( 'ET_Core_PageResource', 'remove_static_resources' ) ) {
			return false;
		}

		try {
			ET_Core_PageResource::remove_static_resources( 'all', 'all' );
			return true;
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Try compatibility fallback signature used by newer Divi internals.
		}

		try {
			ET_Core_PageResource::remove_static_resources( 'all', 'all', true );
			return true;
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			return false;
		}
	}
endif;

if ( ! function_exists( 'dlck_csc_flag_local_storage_clear_for_user' ) ) :
	/**
	 * Mark local storage for one-time clear on the next page load for the given user.
	 */
	function dlck_csc_flag_local_storage_clear_for_user( int $user_id ): void {
		if ( $user_id <= 0 ) {
			return;
		}

		update_user_meta( $user_id, 'dlck_csc_clear_local_storage_once', time() );
	}
endif;

if ( ! function_exists( 'dlck_csc_maybe_output_local_storage_clear_script' ) ) :
	/**
	 * Clear local storage once for users flagged by a cache hook.
	 */
	function dlck_csc_maybe_output_local_storage_clear_script(): void {
		if ( ! dlck_csc_main_menu_enabled() || ! is_user_logged_in() ) {
			return;
		}

		$user_id = get_current_user_id();
		$flagged = (int) get_user_meta( $user_id, 'dlck_csc_clear_local_storage_once', true );
		if ( $flagged <= 0 ) {
			return;
		}

		delete_user_meta( $user_id, 'dlck_csc_clear_local_storage_once' );
		echo "<script>(function(){try{if(window.localStorage){window.localStorage.clear();}}catch(e){}})();</script>\n";
	}

	add_action( 'admin_footer', 'dlck_csc_maybe_output_local_storage_clear_script', 99 );
	add_action( 'wp_footer', 'dlck_csc_maybe_output_local_storage_clear_script', 99 );
endif;

if ( ! function_exists( 'dlck_csc_run_post_save_cache_hook' ) ) :
	/**
	 * After post save, clear static CSS and clear local storage for the current user.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	function dlck_csc_run_post_save_cache_hook( int $post_id, $post ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( ! dlck_csc_post_save_builder_exit_enabled() ) {
			return;
		}

		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		dlck_csc_clear_static_css_generation();

		if ( is_user_logged_in() ) {
			dlck_csc_flag_local_storage_clear_for_user( get_current_user_id() );
		}
	}

	add_action( 'save_post', 'dlck_csc_run_post_save_cache_hook', 20, 2 );
endif;

if ( ! function_exists( 'dlck_csc_maybe_handle_visual_builder_exit' ) ) :
	/**
	 * Detect exiting Divi Visual Builder and run cache-clear hook once.
	 */
	function dlck_csc_maybe_handle_visual_builder_exit(): void {
		if ( ! dlck_csc_post_save_builder_exit_enabled() ) {
			return;
		}

		if ( wp_doing_ajax() || ! is_user_logged_in() ) {
			return;
		}

		if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) ) {
			return;
		}

		$user_id       = get_current_user_id();
		$builder_state = (int) get_user_meta( $user_id, 'dlck_csc_visual_builder_active', true );
		$in_builder    = function_exists( 'dlck_is_divi_visual_builder_request' )
			? dlck_is_divi_visual_builder_request()
			: ( isset( $_GET['et_fb'] ) && wp_unslash( $_GET['et_fb'] ) === '1' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $in_builder ) {
			update_user_meta( $user_id, 'dlck_csc_visual_builder_active', time() );
			return;
		}

		if ( $builder_state <= 0 ) {
			return;
		}

		delete_user_meta( $user_id, 'dlck_csc_visual_builder_active' );
		dlck_csc_clear_static_css_generation();
		dlck_csc_flag_local_storage_clear_for_user( $user_id );
	}

	add_action( 'init', 'dlck_csc_maybe_handle_visual_builder_exit', 20 );
endif;

if ( ! function_exists( 'dlck_csc_maybe_admin_bar_link' ) ) :
	/**
	 * Add cache tools under the plugin's admin bar menu.
	 *
	 * @param WP_Admin_Bar $admin_bar Admin bar instance.
	 */
	function dlck_csc_maybe_admin_bar_link( $admin_bar ) {
		if ( ! dlck_csc_main_menu_enabled() ) {
			return;
		}

		if ( ! is_user_logged_in() || ( function_exists( 'is_admin_bar_showing' ) && ! is_admin_bar_showing() ) ) {
			return;
		}

		$admin_bar->add_menu(
			array(
				'id'    => 'dlck_misc_csc',
				'title' => '<span class="ab-icon"></span><span class="ab-label">Clear Divi Cache</span>',
				'href'  => '',
				'meta'  => array(
					'title' => '',
				),
			)
		);

		$clear_all_fallback_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=dlck_misc_clear_all_cache_fallback' ),
			'dlck_misc_clear_all_cache'
		);

		$admin_bar->add_menu(
			array(
				'id'     => 'dlck_misc_clear_all_cache',
				'parent' => 'dlck_misc_csc',
				'title'  => dlck_csc_build_clear_all_menu_title(),
				'href'   => $clear_all_fallback_url,
			)
		);

		$admin_bar->add_menu(
			array(
				'id'     => 'dlck_misc_clear_static_css',
				'parent' => 'dlck_misc_csc',
				'title'  => sprintf(
					'<span data-wpnonce="%1$s">%2$s</span>',
					esc_attr( wp_create_nonce( 'dlck_misc_clear_static_css' ) ),
					esc_html__( 'Clear Static CSS File Generation', 'divi-lc-kit' )
				),
				'href'   => '#',
			)
		);

		$admin_bar->add_menu(
			array(
				'id'     => 'dlck_misc_csc_clear_local_storage',
				'parent' => 'dlck_misc_csc',
				'title'  => esc_html__( 'Clear Local Storage', 'divi-lc-kit' ),
				'href'   => '#',
			)
		);

		if ( dlck_csc_lazy_cache_enabled() ) {
			$admin_bar->add_menu(
				array(
					'id'     => 'dlck_misc_clear_lazy_cache',
					'parent' => 'dlck_misc_csc',
					'title'  => sprintf(
						'<span data-wpnonce="%1$s">%2$s</span>',
						esc_attr( wp_create_nonce( 'dlck_clear_lazy_cache' ) ),
						esc_html__( 'Clear Lazy Load Cache', 'divi-lc-kit' )
					),
					'href'   => '#',
				)
			);
		}
	}

	add_action( 'admin_bar_menu', 'dlck_csc_maybe_admin_bar_link', 999 );
endif;

if ( ! function_exists( 'dlck_misc_csc_collect_admin_scripts' ) ) :
	/**
	 * Add admin JS/CSS for cache buttons and automation progress.
	 */
	function dlck_misc_csc_collect_admin_scripts() {
		if ( ! dlck_csc_main_menu_enabled() ) {
			return;
		}

		$asset_context = is_admin() ? 'admin' : 'front';
		if ( $asset_context === 'front' ) {
			if ( ! is_user_logged_in() ) {
				return;
			}

			if ( function_exists( 'is_admin_bar_showing' ) && ! is_admin_bar_showing() ) {
				return;
			}
		}

		$css = <<<'CSS'
#dlck-cache-automation-panel{position:fixed;top:56px;right:20px;z-index:100000;background:#fff;border:1px solid #dcdcde;border-radius:4px;box-shadow:0 8px 22px rgba(0,0,0,.14);width:440px;max-width:calc(100vw - 40px);padding:14px;display:none;}
#dlck-cache-automation-panel h4{margin:0 0 10px;font-size:14px;line-height:1.4;}
#dlck-cache-automation-progress-track{width:100%;height:8px;background:#f0f0f1;border-radius:99px;overflow:hidden;}
#dlck-cache-automation-progress-bar{height:100%;width:0;background:#2271b1;transition:width .25s ease;}
#dlck-cache-automation-progress-text{margin-top:6px;font-size:12px;color:#646970;}
#dlck-cache-automation-steps{margin:12px 0 8px;padding:0;list-style:none;max-height:300px;overflow:auto;}
#dlck-cache-automation-steps li{border-bottom:1px solid #f0f0f1;padding:7px 0;}
#dlck-cache-automation-steps li:last-child{border-bottom:0;}
#dlck-cache-automation-steps .dlck-step-head{display:flex;justify-content:space-between;gap:10px;align-items:flex-start;}
#dlck-cache-automation-steps .dlck-step-label{font-size:12px;color:#1d2327;}
#dlck-cache-automation-steps .dlck-step-state{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.03em;color:#646970;}
#dlck-cache-automation-steps .dlck-step-message{margin-top:3px;font-size:12px;color:#646970;}
#dlck-cache-automation-steps li.running .dlck-step-state{color:#dba617;}
#dlck-cache-automation-steps li.success .dlck-step-state{color:#008a20;}
#dlck-cache-automation-steps li.skipped .dlck-step-state{color:#646970;}
#dlck-cache-automation-steps li.error .dlck-step-state{color:#b32d2e;}
#dlck-cache-automation-summary{margin-top:8px;font-size:12px;font-weight:600;color:#1d2327;}
#dlck-cache-automation-summary.error{color:#b32d2e;}
#dlck-cache-automation-close{margin-top:10px;}
#dlck-cache-front-notices{position:fixed;top:56px;right:20px;z-index:100001;display:flex;flex-direction:column;gap:8px;max-width:min(420px,calc(100vw - 40px));}
.dlck-cache-front-notice{padding:10px 12px;border-radius:4px;border:1px solid #c3c4c7;background:#fff;color:#1d2327;box-shadow:0 8px 22px rgba(0,0,0,.14);font-size:13px;line-height:1.35;}
.dlck-cache-front-notice.error{border-color:#d63638;}
.dlck-cache-front-notice.success{border-color:#00a32a;}
CSS;
		dlck_add_inline_css( $css, $asset_context );

		$js = <<<'JS'
jQuery(function ($) {
    var adminAjaxURL = window.ajaxurl || '/wp-admin/admin-ajax.php';
    var isAdmin = $('body').hasClass('wp-admin');
    var isAutomationRunning = false;
    var automationCloseTimer = null;
    if (window.dlckCacheAutomationInit) {
        return;
    }
    window.dlckCacheAutomationInit = true;

    function showNotice(msgText, type) {
        var noticeType = type === 'error' ? 'notice-error' : 'notice-success';
        if (isAdmin) {
            var messageHTML = '<div class="notice ' + noticeType + ' pac-misc-message"><p>' + msgText + '</p></div>';
            if ($('body .wrap h1').length > 0) {
                $('body .wrap h1').after(messageHTML);
            } else {
                $('body #wpbody-content').prepend(messageHTML);
            }
            setTimeout(function () {
                $('.pac-misc-message').remove();
            }, 4500);
        } else {
            var frontClass = type === 'error' ? 'error' : 'success';
            var $frontNotices = $('#dlck-cache-front-notices');
            if (!$frontNotices.length) {
                $frontNotices = $('<div id="dlck-cache-front-notices" aria-live="polite"></div>');
                $('body').append($frontNotices);
            }
            var $notice = $('<div class="dlck-cache-front-notice ' + frontClass + '"></div>').text(msgText);
            $frontNotices.append($notice);
            setTimeout(function () {
                $notice.fadeOut(180, function () {
                    $(this).remove();
                    if ($frontNotices.children().length === 0) {
                        $frontNotices.remove();
                    }
                });
            }, 4500);
        }
    }

    function requestAction(data, fallbackError) {
        var dfd = $.Deferred();
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: adminAjaxURL,
            data: data
        }).done(function (response) {
            if (response && response.success) {
                if (response.data && typeof response.data === 'object' && response.data.status) {
                    dfd.resolve({
                        status: response.data.status,
                        message: response.data.message || ''
                    });
                    return;
                }
                dfd.resolve({
                    status: 'success',
                    message: (response && response.data) ? response.data : ''
                });
                return;
            }
            var errorMessage = fallbackError;
            if (response && response.data) {
                if (typeof response.data === 'string') {
                    errorMessage = response.data;
                } else if (response.data.message) {
                    errorMessage = response.data.message;
                }
            }
            dfd.resolve({
                status: 'error',
                message: errorMessage
            });
        }).fail(function (xhr) {
            var errorMessage = fallbackError;
            if (xhr && xhr.responseJSON && xhr.responseJSON.data) {
                if (typeof xhr.responseJSON.data === 'string') {
                    errorMessage = xhr.responseJSON.data;
                } else if (xhr.responseJSON.data.message) {
                    errorMessage = xhr.responseJSON.data.message;
                }
            }
            dfd.resolve({
                status: 'error',
                message: errorMessage
            });
        });
        return dfd.promise();
    }

    function resolvedResult(status, message) {
        return $.Deferred().resolve({
            status: status,
            message: message
        }).promise();
    }

    function ensureAutomationPanel() {
        var $panel = $('#dlck-cache-automation-panel');
        if ($panel.length) {
            return $panel;
        }
        $panel = $(
            '<div id="dlck-cache-automation-panel">' +
                '<h4>Cache Automation Progress</h4>' +
                '<div id="dlck-cache-automation-progress-track"><div id="dlck-cache-automation-progress-bar"></div></div>' +
                '<div id="dlck-cache-automation-progress-text">0% complete</div>' +
                '<ul id="dlck-cache-automation-steps"></ul>' +
                '<div id="dlck-cache-automation-summary"></div>' +
                '<button type="button" class="button button-secondary" id="dlck-cache-automation-close">Close</button>' +
            '</div>'
        );
        $('body').append($panel);
        return $panel;
    }

    function renderAutomationSteps($panel, steps) {
        var html = '';
        $.each(steps, function (index, step) {
            html += '<li class="pending" data-step-index="' + index + '">' +
                '<div class="dlck-step-head">' +
                    '<span class="dlck-step-label">' + (index + 1) + '. ' + step.label + '</span>' +
                    '<span class="dlck-step-state">Pending</span>' +
                '</div>' +
                '<div class="dlck-step-message"></div>' +
            '</li>';
        });
        $panel.find('#dlck-cache-automation-steps').html(html);
        $panel.find('#dlck-cache-automation-summary').removeClass('error').text('');
        $panel.find('#dlck-cache-automation-progress-bar').css('width', '0%');
        $panel.find('#dlck-cache-automation-progress-text').text('0% complete');
        $panel.show();
    }

    function setStepState($panel, index, status, message) {
        var $step = $panel.find('#dlck-cache-automation-steps li[data-step-index="' + index + '"]');
        var label = 'Pending';
        if (status === 'running') {
            label = 'Running';
        } else if (status === 'success') {
            label = 'Done';
        } else if (status === 'skipped') {
            label = 'Skipped';
        } else if (status === 'error') {
            label = 'Failed';
        }
        $step.removeClass('pending running success skipped error').addClass(status);
        $step.find('.dlck-step-state').text(label);
        $step.find('.dlck-step-message').text(message || '');
    }

    function updateProgress($panel, doneCount, totalCount) {
        var percent = totalCount > 0 ? Math.round((doneCount / totalCount) * 100) : 100;
        $panel.find('#dlck-cache-automation-progress-bar').css('width', percent + '%');
        $panel.find('#dlck-cache-automation-progress-text').text(percent + '% complete');
    }

    function fetchAutomationCapabilities(config) {
        var dfd = $.Deferred();
        var fallback = {
            lazy: !!config.lazyEnabled,
            wpRocket: false,
            liteSpeedCache: false,
            w3TotalCache: false,
            nginxHelperCache: false,
            sitegroundCache: false,
            wpEngineCache: false,
            stackCache: false,
            bluehostCache: false
        };

        $.ajax({
            type: 'post',
            dataType: 'json',
            url: adminAjaxURL,
            data: {
                action: 'dlck_misc_clear_all_cache_capabilities',
                _wpnonce: config.allNonce
            }
        }).done(function (response) {
            if (response && response.success && response.data && typeof response.data === 'object') {
                dfd.resolve({
                    lazy: !!config.lazyEnabled,
                    wpRocket: String(response.data.wp_rocket) === '1' || response.data.wp_rocket === true,
                    liteSpeedCache: String(response.data.litespeed_cache) === '1' || response.data.litespeed_cache === true,
                    w3TotalCache: String(response.data.w3_total_cache) === '1' || response.data.w3_total_cache === true,
                    nginxHelperCache: String(response.data.nginx_helper_cache) === '1' || response.data.nginx_helper_cache === true,
                    sitegroundCache: String(response.data.siteground_cache) === '1' || response.data.siteground_cache === true,
                    wpEngineCache: String(response.data.wp_engine_cache) === '1' || response.data.wp_engine_cache === true,
                    stackCache: String(response.data.stack_cache) === '1' || response.data.stack_cache === true,
                    bluehostCache: String(response.data.bluehost_cache) === '1' || response.data.bluehost_cache === true
                });
                return;
            }

            dfd.resolve(fallback);
        }).fail(function () {
            dfd.resolve(fallback);
        });

        return dfd.promise();
    }

    function runAllCacheAutomation(config) {
        if (isAutomationRunning) {
            return;
        }

        isAutomationRunning = true;
        if (automationCloseTimer) {
            clearTimeout(automationCloseTimer);
            automationCloseTimer = null;
        }
        var $panel = ensureAutomationPanel();
        fetchAutomationCapabilities(config).done(function (capabilities) {
            var steps = [
                {
                    label: 'Clear Static CSS File Generation',
                    run: function () {
                        return requestAction({
                            action: 'dlck_misc_clear_static_css',
                            _wpnonce: config.staticNonce
                        }, 'Could not clear Divi static CSS cache.');
                    }
                },
                {
                    label: 'Clear Local Storage',
                    run: function () {
                        try {
                            window.localStorage.clear();
                            return resolvedResult('success', 'Local storage cleared for this browser.');
                        } catch (err) {
                            return resolvedResult('error', 'Could not clear local storage in this browser.');
                        }
                    }
                },
                {
                    label: 'Clear LC Tweaks CSS/JS Cache',
                    run: function () {
                        return requestAction({
                            action: 'dlck_misc_clear_all_cache_step',
                            _wpnonce: config.allNonce,
                            step: 'lc_tweaks_css_js'
                        }, 'Could not clear LC Tweaks CSS/JS cache.');
                    }
                }
            ];

            if (capabilities.lazy) {
                steps.push({
                    label: 'Clear Lazy Load Cache',
                    run: function () {
                        return requestAction({
                            action: 'dlck_clear_lazy_cache',
                            _wpnonce: config.lazyNonce
                        }, 'Could not clear lazy-load cache.');
                    }
                });
            }

            if (capabilities.wpRocket) {
                steps.push({
                    label: 'WP Rocket > Clear and Preload Cache',
                    run: function () {
                        return requestAction({
                            action: 'dlck_misc_clear_all_cache_step',
                            _wpnonce: config.allNonce,
                            step: 'wp_rocket'
                        }, 'WP Rocket cache step failed.');
                    }
                });
            }

            if (capabilities.liteSpeedCache) {
                steps.push({
                    label: 'Clear LiteSpeed Cache',
                    run: function () {
                        return requestAction({
                            action: 'dlck_misc_clear_all_cache_step',
                            _wpnonce: config.allNonce,
                            step: 'litespeed_cache'
                        }, 'LiteSpeed cache step failed.');
                    }
                });
            }

            if (capabilities.w3TotalCache) {
                steps.push({
                    label: 'Clear W3 Total Cache',
                    run: function () {
                        return requestAction({
                            action: 'dlck_misc_clear_all_cache_step',
                            _wpnonce: config.allNonce,
                            step: 'w3_total_cache'
                        }, 'W3 Total Cache step failed.');
                    }
                });
            }

            if (capabilities.nginxHelperCache) {
                steps.push({
                    label: 'Clear Nginx Helper Cache',
                    run: function () {
                        return requestAction({
                            action: 'dlck_misc_clear_all_cache_step',
                            _wpnonce: config.allNonce,
                            step: 'nginx_helper_cache'
                        }, 'Nginx Helper cache step failed.');
                    }
                });
            }

            if (capabilities.sitegroundCache) {
                steps.push({
                    label: 'Clear SiteGround Optimizer Cache',
                    run: function () {
                        return requestAction({
                            action: 'dlck_misc_clear_all_cache_step',
                            _wpnonce: config.allNonce,
                            step: 'siteground_cache'
                        }, 'SiteGround cache step failed.');
                    }
                });
            }

            if (capabilities.wpEngineCache) {
                steps.push({
                    label: 'Clear WP Engine Cache',
                    run: function () {
                        return requestAction({
                            action: 'dlck_misc_clear_all_cache_step',
                            _wpnonce: config.allNonce,
                            step: 'wp_engine_cache'
                        }, 'WP Engine cache step failed.');
                    }
                });
            }

            if (capabilities.stackCache) {
                steps.push({
                    label: 'Clear 20i Stack Cache',
                    run: function () {
                        return requestAction({
                            action: 'dlck_misc_clear_all_cache_step',
                            _wpnonce: config.allNonce,
                            step: 'stack_cache'
                        }, '20i Stack Cache step failed.');
                    }
                });
            }

            if (capabilities.bluehostCache) {
                steps.push({
                    label: 'Clear Bluehost Cache',
                    run: function () {
                        return requestAction({
                            action: 'dlck_misc_clear_all_cache_step',
                            _wpnonce: config.allNonce,
                            step: 'bluehost_cache'
                        }, 'Bluehost cache step failed.');
                    }
                });
            }

            var doneCount = 0;
            var results = [];
            renderAutomationSteps($panel, steps);

            function finishAutomation() {
                var failed = 0;
                var skipped = 0;
                $.each(results, function (_, result) {
                    if (result === 'error') {
                        failed += 1;
                    } else if (result === 'skipped') {
                        skipped += 1;
                    }
                });

                var summaryText = 'Cache automation completed.';
                var noticeType = 'success';
                if (failed > 0) {
                    summaryText = 'Cache automation finished with ' + failed + ' failed step(s).';
                    noticeType = 'error';
                    $panel.find('#dlck-cache-automation-summary').addClass('error');
                } else if (skipped > 0) {
                    summaryText = 'Cache automation completed. ' + skipped + ' optional step(s) were skipped.';
                }

                $panel.find('#dlck-cache-automation-summary').text(summaryText);
                showNotice(summaryText, noticeType);
                isAutomationRunning = false;
                automationCloseTimer = setTimeout(function () {
                    $panel.hide();
                    automationCloseTimer = null;
                }, 8000);
            }

            function runStep(index) {
                if (index >= steps.length) {
                    finishAutomation();
                    return;
                }

                setStepState($panel, index, 'running', 'Running...');
                steps[index].run().done(function (result) {
                    var status = (result && result.status) ? result.status : 'success';
                    var message = (result && result.message) ? result.message : '';
                    setStepState($panel, index, status, message);
                    results.push(status);
                    doneCount += 1;
                    updateProgress($panel, doneCount, steps.length);
                    setTimeout(function () {
                        runStep(index + 1);
                    }, 220);
                });
            }

            runStep(0);
        });
    }

    $(document).on('click', '#dlck-cache-automation-close', function () {
        if (automationCloseTimer) {
            clearTimeout(automationCloseTimer);
            automationCloseTimer = null;
        }
        $('#dlck-cache-automation-panel').hide();
    });

    $(document).on('click', '#wp-admin-bar-dlck_misc_clear_static_css > .ab-item', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $menuNode = $(this).closest('#wp-admin-bar-dlck_misc_clear_static_css');
        requestAction({
            action: 'dlck_misc_clear_static_css',
            _wpnonce: $menuNode.find('span').data('wpnonce')
        }, 'Could not clear Divi static CSS cache.').done(function (result) {
            if (result.status === 'success') {
                showNotice(result.message || 'The static CSS file generation has been cleared!', 'success');
            } else {
                showNotice(result.message || 'Could not clear Divi static CSS cache.', 'error');
            }
        });
    });

    $(document).on('click', '#wp-admin-bar-dlck_misc_csc_clear_local_storage > .ab-item', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var msgText = 'The local storage has been cleared!';
        try {
            window.localStorage.clear();
            showNotice(msgText, 'success');
        } catch (err) {
            showNotice('Could not clear local storage in this browser.', 'error');
        }
    });

    $(document).on('click', '#wp-admin-bar-dlck_misc_clear_lazy_cache > .ab-item', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $menuNode = $(this).closest('#wp-admin-bar-dlck_misc_clear_lazy_cache');
        requestAction({
            action: 'dlck_clear_lazy_cache',
            _wpnonce: $menuNode.find('span').data('wpnonce')
        }, 'Could not clear lazy-load cache.').done(function (result) {
            if (result.status === 'success') {
                showNotice(result.message || 'Lazy load cache cleared.', 'success');
            } else {
                showNotice(result.message || 'Could not clear lazy-load cache.', 'error');
            }
        });
    });

    $(document).on('click', '#wp-admin-bar-dlck_misc_clear_all_cache > .ab-item', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (isAutomationRunning) {
            return;
        }

        var $menuNode = $(this).closest('#wp-admin-bar-dlck_misc_clear_all_cache');
        var $dataSpan = $menuNode.find('span[data-allnonce]').first();
        if (!$dataSpan.length) {
            showNotice('Could not start cache automation (missing nonce data).', 'error');
            return;
        }

        runAllCacheAutomation({
            allNonce: $dataSpan.data('allnonce') || '',
            staticNonce: $dataSpan.data('staticnonce') || '',
            lazyNonce: $dataSpan.data('lazynonce') || '',
            lazyEnabled: String($dataSpan.data('lazyenabled')) === '1'
        });
    });
});
JS;
		dlck_add_inline_js( $js, $asset_context );
	}

	add_action( 'dlck_collect_inline_assets_admin', 'dlck_misc_csc_collect_admin_scripts' );
	add_action( 'dlck_collect_inline_assets_front', 'dlck_misc_csc_collect_admin_scripts' );
endif;

if ( is_admin() && ! function_exists( 'dlck_misc_csc_maybe_ajax_request' ) ) :
	/**
	 * AJAX handler: clear Divi static CSS file generation.
	 */
	function dlck_misc_csc_maybe_ajax_request() {
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'dlck_misc_clear_static_css' ) ) {
			wp_send_json_error( __( 'Invalid nonce.', 'divi-lc-kit' ), 403 );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have permission to do that.', 'divi-lc-kit' ), 403 );
		}

		if ( dlck_csc_clear_static_css_generation() ) {
			wp_send_json_success( __( 'The static CSS file generation has been cleared!', 'divi-lc-kit' ), 200 );
		}

		wp_send_json_error( __( 'Divi static CSS clear function is unavailable.', 'divi-lc-kit' ), 500 );
	}

	add_action( 'wp_ajax_dlck_misc_clear_static_css', 'dlck_misc_csc_maybe_ajax_request' );
endif;

if ( ! function_exists( 'dlck_misc_csc_call_first_function' ) ) :
	/**
	 * Run the first available function from a list.
	 *
	 * @param string[] $functions Candidate function names.
	 * @return string Function name used, or empty when none worked.
	 */
	function dlck_misc_csc_call_first_function( array $functions ): string {
		foreach ( $functions as $function ) {
			if ( ! function_exists( $function ) ) {
				continue;
			}
			try {
				call_user_func( $function );
				return $function;
			} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				// Continue trying fallback functions.
			}
		}

		return '';
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_call_first_method' ) ) :
	/**
	 * Run the first available callable method on an object/class target.
	 *
	 * @param object|string $target Object instance or class name.
	 * @param string[]      $methods Candidate method names.
	 * @return string Method name used, or empty when none worked.
	 */
	function dlck_misc_csc_call_first_method( $target, array $methods ): string {
		foreach ( $methods as $method ) {
			if ( is_object( $target ) && method_exists( $target, $method ) ) {
				try {
					call_user_func( array( $target, $method ) );
					return $method;
				} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
					// Continue trying fallback methods.
				}
			}

			if ( is_string( $target ) && class_exists( $target ) && method_exists( $target, $method ) ) {
				try {
					$reflection = new ReflectionMethod( $target, $method );
					if ( ! $reflection->isStatic() ) {
						continue;
					}
					call_user_func( array( $target, $method ) );
					return $method;
				} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
					// Continue trying fallback methods.
				}
			}
		}

		return '';
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_stack_cache_targets' ) ) :
	/**
	 * Collect plausible 20i Stack Cache object/class targets.
	 *
	 * The wrapper shown in ref/wp-stack-cache.php instantiates a global
	 * `$wpsc = new WPStackCache();`, but the instance name is not guaranteed.
	 *
	 * @return array<int,object|string>
	 */
	function dlck_misc_csc_stack_cache_targets(): array {
		$targets = array();

		if ( isset( $GLOBALS['wpsc'] ) && is_object( $GLOBALS['wpsc'] ) ) {
			$targets[] = $GLOBALS['wpsc'];
		}

		if ( class_exists( 'WPStackCache' ) ) {
			foreach ( $GLOBALS as $value ) {
				if ( ! is_object( $value ) || ! ( $value instanceof WPStackCache ) ) {
					continue;
				}

				$targets[] = $value;
			}

			$targets[] = 'WPStackCache';
		}

		$unique_targets = array();
		$seen           = array();

		foreach ( $targets as $target ) {
			$key = is_object( $target ) ? 'object:' . spl_object_hash( $target ) : 'class:' . $target;
			if ( isset( $seen[ $key ] ) ) {
				continue;
			}

			$seen[ $key ]    = true;
			$unique_targets[] = $target;
		}

		return $unique_targets;
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_stack_cache_candidate_methods' ) ) :
	/**
	 * Discover zero-argument Stack Cache purge methods.
	 *
	 * The 20i library is not bundled here, so prefer known purge names first and
	 * then reflect the public API for likely purge/clear methods.
	 *
	 * @param object|string $target Object instance or class name.
	 * @return string[]
	 */
	function dlck_misc_csc_stack_cache_candidate_methods( $target ): array {
		$methods = array(
			'purge_all',
			'purgeAll',
			'clear_cache',
			'clearCache',
			'purge_cache',
			'purgeCache',
			'clear_all',
			'clearAll',
			'flush_cache',
			'flushCache',
			'flush',
			'purge',
			'clear',
		);

		$class_name = '';
		if ( is_object( $target ) ) {
			$class_name = get_class( $target );
		} elseif ( is_string( $target ) && class_exists( $target ) ) {
			$class_name = $target;
		}

		if ( $class_name === '' ) {
			return $methods;
		}

		try {
			$reflection = new ReflectionClass( $class_name );

			foreach ( $reflection->getMethods( ReflectionMethod::IS_PUBLIC ) as $method ) {
				$name       = $method->getName();
				$normalized = strtolower( $name );

				if ( in_array( $name, $methods, true ) ) {
					continue;
				}

				if ( $method->getNumberOfRequiredParameters() > 0 ) {
					continue;
				}

				if ( is_string( $target ) && ! $method->isStatic() ) {
					continue;
				}

				if ( ! preg_match( '/(?:purge|clear|flush)/', $normalized ) ) {
					continue;
				}

				if ( ! preg_match( '/(?:cache|all|site|domain|stack|everything)/', $normalized ) ) {
					continue;
				}

				$methods[] = $name;
			}
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Fall back to the known method list when reflection is unavailable.
		}

		return $methods;
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_stack_cache_target_supports_method' ) ) :
	/**
	 * Check whether a Stack Cache target exposes any supported purge method.
	 *
	 * @param object|string $target Object instance or class name.
	 */
	function dlck_misc_csc_stack_cache_target_supports_method( $target ): bool {
		foreach ( dlck_misc_csc_stack_cache_candidate_methods( $target ) as $method ) {
			if ( is_object( $target ) && method_exists( $target, $method ) ) {
				return true;
			}

			if ( is_string( $target ) && class_exists( $target ) && method_exists( $target, $method ) ) {
				try {
					$reflection = new ReflectionMethod( $target, $method );
					if ( $reflection->isStatic() ) {
						return true;
					}
				} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
					// Continue checking fallbacks.
				}
			}
		}

		return false;
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_stack_cache_ajax_request' ) ) :
	/**
	 * Discover the Stack Cache admin-bar AJAX endpoint.
	 *
	 * The 20i UI exposes a "Purge Cache" admin-bar button that points at
	 * `admin-ajax.php?action=purge-all-cache&nonce=...`.
	 *
	 * @return array{action:string,nonce_key:string,nonce:string}
	 */
	function dlck_misc_csc_stack_cache_ajax_request(): array {
		static $request = null;

		if ( is_array( $request ) ) {
			return $request;
		}

		$request = array(
			'action'    => '',
			'nonce_key' => '',
			'nonce'     => '',
		);

		if ( ! is_user_logged_in() ) {
			return $request;
		}

		if ( ! class_exists( 'WP_Admin_Bar' ) ) {
			$admin_bar_class = ABSPATH . WPINC . '/class-wp-admin-bar.php';
			if ( ! file_exists( $admin_bar_class ) ) {
				return $request;
			}

			require_once $admin_bar_class;
		}

		try {
			$admin_bar = new WP_Admin_Bar();
			if ( method_exists( $admin_bar, 'initialize' ) ) {
				$admin_bar->initialize();
			}

			do_action_ref_array( 'admin_bar_menu', array( &$admin_bar ) );

			$node = $admin_bar->get_node( 'stack-cache-purge-cache' );
			if ( ! is_object( $node ) || empty( $node->href ) ) {
				return $request;
			}

			$query = wp_parse_url( (string) $node->href, PHP_URL_QUERY );
			if ( ! is_string( $query ) || $query === '' ) {
				return $request;
			}

			$params = array();
			wp_parse_str( $query, $params );

			$action    = isset( $params['action'] ) ? sanitize_key( (string) $params['action'] ) : '';
			$nonce_key = isset( $params['nonce'] ) ? 'nonce' : ( isset( $params['_wpnonce'] ) ? '_wpnonce' : '' );
			$nonce     = $nonce_key !== '' ? sanitize_text_field( (string) $params[ $nonce_key ] ) : '';

			if ( $action === '' ) {
				return $request;
			}

			$request = array(
				'action'    => $action,
				'nonce_key' => $nonce_key,
				'nonce'     => $nonce,
			);
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Fall back to direct method detection when the admin-bar node is unavailable.
		}

		return $request;
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_stack_cache_wp_die_handler' ) ) :
	/**
	 * Convert `wp_die()` calls during nested AJAX execution into exceptions.
	 *
	 * @param mixed $message Optional response body.
	 */
	function dlck_misc_csc_stack_cache_wp_die_handler( $message = '' ): void {
		if ( is_scalar( $message ) ) {
			throw new RuntimeException( (string) $message );
		}

		throw new RuntimeException( 'wp_die' );
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_run_stack_cache_ajax_step' ) ) :
	/**
	 * Invoke the 20i "Purge Cache" AJAX action when its admin-bar link is available.
	 *
	 * @return array{status:string,message:string}
	 */
	function dlck_misc_csc_run_stack_cache_ajax_step(): array {
		$request = dlck_misc_csc_stack_cache_ajax_request();
		if ( $request['action'] === '' ) {
			return array(
				'status'  => 'skipped',
				'message' => __( '20i Stack Cache AJAX purge action is unavailable.', 'divi-lc-kit' ),
			);
		}

		$hook = 'wp_ajax_' . $request['action'];
		if ( ! has_action( $hook ) ) {
			return array(
				'status'  => 'skipped',
				'message' => __( '20i Stack Cache AJAX purge hook is unavailable.', 'divi-lc-kit' ),
			);
		}

		$original_request = $_REQUEST;
		$original_get     = $_GET;
		$original_post    = $_POST;

		add_filter( 'wp_die_ajax_handler', 'dlck_misc_csc_stack_cache_wp_die_handler' );
		add_filter( 'wp_die_handler', 'dlck_misc_csc_stack_cache_wp_die_handler' );

		$response_body = '';
		$die_message   = '';

		ob_start();

		try {
			$_REQUEST['action'] = $request['action'];
			$_GET['action']     = $request['action'];
			$_POST['action']    = $request['action'];

			if ( $request['nonce_key'] !== '' ) {
				$_REQUEST[ $request['nonce_key'] ] = $request['nonce'];
				$_GET[ $request['nonce_key'] ]     = $request['nonce'];
				$_POST[ $request['nonce_key'] ]    = $request['nonce'];
			}

			do_action( $hook );
		} catch ( Throwable $e ) {
			$die_message = $e->getMessage();
		}

		$response_body = trim( (string) ob_get_clean() );

		remove_filter( 'wp_die_ajax_handler', 'dlck_misc_csc_stack_cache_wp_die_handler' );
		remove_filter( 'wp_die_handler', 'dlck_misc_csc_stack_cache_wp_die_handler' );

		$_REQUEST = $original_request;
		$_GET     = $original_get;
		$_POST    = $original_post;

		$combined_response = trim( $response_body . ' ' . $die_message );
		$lowered_response  = strtolower( $combined_response );

		if ( $combined_response === '-1' || strpos( $lowered_response, 'nonce' ) !== false || strpos( $lowered_response, 'forbidden' ) !== false || strpos( $lowered_response, 'permission' ) !== false || strpos( $lowered_response, 'error' ) !== false || strpos( $lowered_response, 'fail' ) !== false ) {
			return array(
				'status'  => 'error',
				'message' => __( '20i Stack Cache purge failed.', 'divi-lc-kit' ),
			);
		}

		if ( $response_body !== '' ) {
			$decoded = json_decode( $response_body, true );
			if ( is_array( $decoded ) && array_key_exists( 'success', $decoded ) ) {
				return array(
					'status'  => ! empty( $decoded['success'] ) ? 'success' : 'error',
					'message' => ! empty( $decoded['success'] ) ? __( '20i Stack Cache cleared.', 'divi-lc-kit' ) : __( '20i Stack Cache purge failed.', 'divi-lc-kit' ),
				);
			}
		}

		if ( $combined_response === '' || $combined_response === '1' || strpos( $lowered_response, 'success' ) !== false || strpos( $lowered_response, 'purged' ) !== false ) {
			return array(
				'status'  => 'success',
				'message' => __( '20i Stack Cache cleared.', 'divi-lc-kit' ),
			);
		}

		return array(
			'status'  => 'error',
			'message' => __( '20i Stack Cache purge failed.', 'divi-lc-kit' ),
		);
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_stack_cache_available' ) ) :
	/**
	 * Check if 20i Stack Cache looks purge-capable.
	 */
	function dlck_misc_csc_stack_cache_available(): bool {
		foreach ( dlck_misc_csc_stack_cache_targets() as $target ) {
			if ( dlck_misc_csc_stack_cache_target_supports_method( $target ) ) {
				return true;
			}
		}

		$request = dlck_misc_csc_stack_cache_ajax_request();

		return $request['action'] !== '' && has_action( 'wp_ajax_' . $request['action'] );
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_run_wp_rocket_step' ) ) :
	/**
	 * Clear and preload WP Rocket cache when available.
	 *
	 * @return array{status:string,message:string}
	 */
	function dlck_misc_csc_run_wp_rocket_step(): array {
		$purge_function = dlck_misc_csc_call_first_function(
			array(
				'rocket_clean_domain',
			)
		);

		if ( $purge_function === '' ) {
			return array(
				'status'  => 'skipped',
				'message' => __( 'WP Rocket is not active, so this step was skipped.', 'divi-lc-kit' ),
			);
		}

		$preload_function = dlck_misc_csc_call_first_function(
			array(
				'run_rocket_sitemap_preload',
				'rocket_preload_cache',
				'rocket_preload_activate_cron',
			)
		);

		if ( $preload_function !== '' ) {
			return array(
				'status'  => 'success',
				'message' => __( 'WP Rocket cache cleared and preload started.', 'divi-lc-kit' ),
			);
		}

		return array(
			'status'  => 'success',
			'message' => __( 'WP Rocket cache cleared. Preload function was not available.', 'divi-lc-kit' ),
		);
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_litespeed_available' ) ) :
	/**
	 * Check if LiteSpeed cache purge hooks are available.
	 */
	function dlck_misc_csc_litespeed_available(): bool {
		return has_action( 'litespeed_purge_all' )
			|| is_callable( 'LiteSpeed_Cache_API::purge_all' )
			|| is_callable( 'LiteSpeed_Cache::get_instance' );
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_run_litespeed_step' ) ) :
	/**
	 * Clear LiteSpeed Cache when available.
	 *
	 * @return array{status:string,message:string}
	 */
	function dlck_misc_csc_run_litespeed_step(): array {
		if ( ! dlck_misc_csc_litespeed_available() ) {
			return array(
				'status'  => 'skipped',
				'message' => __( 'LiteSpeed Cache is not active, so this step was skipped.', 'divi-lc-kit' ),
			);
		}

		try {
			if ( has_action( 'litespeed_purge_all' ) ) {
				do_action( 'litespeed_purge_all' );
				return array(
					'status'  => 'success',
					'message' => __( 'LiteSpeed Cache cleared.', 'divi-lc-kit' ),
				);
			}

			if ( is_callable( 'LiteSpeed_Cache_API::purge_all' ) ) {
				call_user_func( array( 'LiteSpeed_Cache_API', 'purge_all' ) );
				return array(
					'status'  => 'success',
					'message' => __( 'LiteSpeed Cache cleared.', 'divi-lc-kit' ),
				);
			}

			if ( is_callable( 'LiteSpeed_Cache::get_instance' ) ) {
				$litespeed = call_user_func( array( 'LiteSpeed_Cache', 'get_instance' ) );
				if ( is_object( $litespeed ) && method_exists( $litespeed, 'purge_all' ) ) {
					$litespeed->purge_all();
					return array(
						'status'  => 'success',
						'message' => __( 'LiteSpeed Cache cleared.', 'divi-lc-kit' ),
					);
				}
			}
		} catch ( Throwable $e ) {
			return array(
				'status'  => 'error',
				'message' => __( 'LiteSpeed cache purge failed.', 'divi-lc-kit' ),
			);
		}

		return array(
			'status'  => 'skipped',
			'message' => __( 'LiteSpeed Cache was detected but no compatible clear method was found.', 'divi-lc-kit' ),
		);
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_w3_total_cache_available' ) ) :
	/**
	 * Check if W3 Total Cache purge hooks are available.
	 */
	function dlck_misc_csc_w3_total_cache_available(): bool {
		return function_exists( 'w3tc_flush_all' )
			|| has_action( 'w3tc_flush_posts' )
			|| has_action( 'w3tc_flush_all' );
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_run_w3_total_cache_step' ) ) :
	/**
	 * Clear W3 Total Cache when available.
	 *
	 * @return array{status:string,message:string}
	 */
	function dlck_misc_csc_run_w3_total_cache_step(): array {
		if ( ! dlck_misc_csc_w3_total_cache_available() ) {
			return array(
				'status'  => 'skipped',
				'message' => __( 'W3 Total Cache is not active, so this step was skipped.', 'divi-lc-kit' ),
			);
		}

		try {
			if ( function_exists( 'w3tc_flush_all' ) ) {
				w3tc_flush_all();
				return array(
					'status'  => 'success',
					'message' => __( 'W3 Total Cache cleared.', 'divi-lc-kit' ),
				);
			}

			if ( has_action( 'w3tc_flush_posts' ) ) {
				do_action( 'w3tc_flush_posts' );
				return array(
					'status'  => 'success',
					'message' => __( 'W3 Total Cache cleared.', 'divi-lc-kit' ),
				);
			}

			if ( has_action( 'w3tc_flush_all' ) ) {
				do_action( 'w3tc_flush_all' );
				return array(
					'status'  => 'success',
					'message' => __( 'W3 Total Cache cleared.', 'divi-lc-kit' ),
				);
			}
		} catch ( Throwable $e ) {
			return array(
				'status'  => 'error',
				'message' => __( 'W3 Total Cache purge failed.', 'divi-lc-kit' ),
			);
		}

		return array(
			'status'  => 'skipped',
			'message' => __( 'W3 Total Cache was detected but no compatible clear method was found.', 'divi-lc-kit' ),
		);
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_nginx_helper_available' ) ) :
	/**
	 * Check if Nginx Helper purge hooks are available.
	 */
	function dlck_misc_csc_nginx_helper_available(): bool {
		return has_action( 'rt_nginx_helper_purge_all' )
			|| ( isset( $GLOBALS['nginx_purger'] ) && is_object( $GLOBALS['nginx_purger'] ) );
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_run_nginx_helper_step' ) ) :
	/**
	 * Clear Nginx Helper cache when available.
	 *
	 * @return array{status:string,message:string}
	 */
	function dlck_misc_csc_run_nginx_helper_step(): array {
		if ( ! dlck_misc_csc_nginx_helper_available() ) {
			return array(
				'status'  => 'skipped',
				'message' => __( 'Nginx Helper is not active, so this step was skipped.', 'divi-lc-kit' ),
			);
		}

		try {
			if ( has_action( 'rt_nginx_helper_purge_all' ) ) {
				do_action( 'rt_nginx_helper_purge_all' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
				return array(
					'status'  => 'success',
					'message' => __( 'Nginx Helper cache cleared.', 'divi-lc-kit' ),
				);
			}

			if ( isset( $GLOBALS['nginx_purger'] ) && is_object( $GLOBALS['nginx_purger'] ) ) {
				$method = dlck_misc_csc_call_first_method(
					$GLOBALS['nginx_purger'],
					array(
						'purge_all',
						'purgeAll',
						'purge_cache',
						'purgeCache',
						'clear_cache',
						'clearCache',
					)
				);

				if ( $method !== '' ) {
					return array(
						'status'  => 'success',
						'message' => __( 'Nginx Helper cache cleared.', 'divi-lc-kit' ),
					);
				}
			}
		} catch ( Throwable $e ) {
			return array(
				'status'  => 'error',
				'message' => __( 'Nginx Helper cache purge failed.', 'divi-lc-kit' ),
			);
		}

		return array(
			'status'  => 'skipped',
			'message' => __( 'Nginx Helper was detected but no compatible clear method was found.', 'divi-lc-kit' ),
		);
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_siteground_available' ) ) :
	/**
	 * Check if SiteGround cache purge hooks are available.
	 */
	function dlck_misc_csc_siteground_available(): bool {
		return ( isset( $GLOBALS['sg_cachepress_supercacher'] ) && is_object( $GLOBALS['sg_cachepress_supercacher'] ) )
			|| function_exists( 'sg_cachepress_purge_cache' )
			|| is_callable( 'SiteGround_Optimizer\\Supercacher\\Supercacher::purge_cache' );
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_run_siteground_step' ) ) :
	/**
	 * Clear SiteGround Optimizer cache when available.
	 *
	 * @return array{status:string,message:string}
	 */
	function dlck_misc_csc_run_siteground_step(): array {
		if ( ! dlck_misc_csc_siteground_available() ) {
			return array(
				'status'  => 'skipped',
				'message' => __( 'SiteGround cache is not active, so this step was skipped.', 'divi-lc-kit' ),
			);
		}

		try {
			if ( is_callable( 'SiteGround_Optimizer\\Supercacher\\Supercacher::purge_cache' ) ) {
				call_user_func( array( 'SiteGround_Optimizer\\Supercacher\\Supercacher', 'purge_cache' ) );
				return array(
					'status'  => 'success',
					'message' => __( 'SiteGround cache cleared.', 'divi-lc-kit' ),
				);
			}

			if ( isset( $GLOBALS['sg_cachepress_supercacher'] ) && is_object( $GLOBALS['sg_cachepress_supercacher'] ) && method_exists( $GLOBALS['sg_cachepress_supercacher'], 'purge_cache' ) ) {
				$GLOBALS['sg_cachepress_supercacher']->purge_cache( true );
				return array(
					'status'  => 'success',
					'message' => __( 'SiteGround cache cleared.', 'divi-lc-kit' ),
				);
			}

			if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
				sg_cachepress_purge_cache();
				return array(
					'status'  => 'success',
					'message' => __( 'SiteGround cache cleared.', 'divi-lc-kit' ),
				);
			}
		} catch ( Throwable $e ) {
			return array(
				'status'  => 'error',
				'message' => __( 'SiteGround cache purge failed.', 'divi-lc-kit' ),
			);
		}

		return array(
			'status'  => 'skipped',
			'message' => __( 'SiteGround cache was detected but no compatible clear method was found.', 'divi-lc-kit' ),
		);
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_wp_engine_available' ) ) :
	/**
	 * Check if WP Engine cache purge methods are available.
	 */
	function dlck_misc_csc_wp_engine_available(): bool {
		if ( ! class_exists( 'WpeCommon' ) ) {
			return false;
		}

		return is_callable( 'WpeCommon::purge_memcached' )
			|| is_callable( 'WpeCommon::clear_maxcdn_cache' )
			|| is_callable( 'WpeCommon::purge_varnish_cache' )
			|| is_callable( 'WpeCommon::instance' );
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_run_wp_engine_step' ) ) :
	/**
	 * Clear WP Engine cache when available.
	 *
	 * @return array{status:string,message:string}
	 */
	function dlck_misc_csc_run_wp_engine_step(): array {
		if ( ! dlck_misc_csc_wp_engine_available() ) {
			return array(
				'status'  => 'skipped',
				'message' => __( 'WP Engine cache is not active, so this step was skipped.', 'divi-lc-kit' ),
			);
		}

		$runs = 0;

		try {
			if ( is_callable( 'WpeCommon::purge_memcached' ) ) {
				call_user_func( array( 'WpeCommon', 'purge_memcached' ) );
				$runs++;
			}

			if ( is_callable( 'WpeCommon::clear_maxcdn_cache' ) ) {
				call_user_func( array( 'WpeCommon', 'clear_maxcdn_cache' ) );
				$runs++;
			}

			if ( is_callable( 'WpeCommon::purge_varnish_cache' ) ) {
				call_user_func( array( 'WpeCommon', 'purge_varnish_cache' ) );
				$runs++;
			}

			if ( is_callable( 'WpeCommon::instance' ) ) {
				$instance = call_user_func( array( 'WpeCommon', 'instance' ) );
				if ( is_object( $instance ) && method_exists( $instance, 'purge_object_cache' ) ) {
					$instance->purge_object_cache();
					$runs++;
				}
			}
		} catch ( Throwable $e ) {
			return array(
				'status'  => 'error',
				'message' => __( 'WP Engine cache purge failed.', 'divi-lc-kit' ),
			);
		}

		if ( $runs > 0 ) {
			return array(
				'status'  => 'success',
				'message' => __( 'WP Engine cache cleared.', 'divi-lc-kit' ),
			);
		}

		return array(
			'status'  => 'skipped',
			'message' => __( 'WP Engine cache was detected but no compatible clear method was found.', 'divi-lc-kit' ),
		);
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_run_stack_cache_step' ) ) :
	/**
	 * Clear 20i Stack Cache when available.
	 *
	 * @return array{status:string,message:string}
	 */
	function dlck_misc_csc_run_stack_cache_step(): array {
		if ( ! dlck_misc_csc_stack_cache_available() ) {
			return array(
				'status'  => 'skipped',
				'message' => __( '20i Stack Cache is not active, so this step was skipped.', 'divi-lc-kit' ),
			);
		}

		foreach ( dlck_misc_csc_stack_cache_targets() as $target ) {
			$method = dlck_misc_csc_call_first_method( $target, dlck_misc_csc_stack_cache_candidate_methods( $target ) );
			if ( $method !== '' ) {
				return array(
					'status'  => 'success',
					'message' => __( '20i Stack Cache cleared.', 'divi-lc-kit' ),
				);
			}
		}

		$ajax_result = dlck_misc_csc_run_stack_cache_ajax_step();
		if ( $ajax_result['status'] === 'success' ) {
			return $ajax_result;
		}

		return array(
			'status'  => $ajax_result['status'] === 'error' ? 'error' : 'skipped',
			'message' => $ajax_result['status'] === 'error'
				? $ajax_result['message']
				: __( '20i Stack Cache was detected but no compatible clear method was found.', 'divi-lc-kit' ),
		);
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_run_bluehost_cache_step' ) ) :
	/**
	 * Clear Bluehost/Newfold performance cache when available.
	 *
	 * @return array{status:string,message:string}
	 */
	function dlck_misc_csc_run_bluehost_cache_step(): array {
		$container_function = '\\NewfoldLabs\\WP\\ModuleLoader\\container';

		if ( function_exists( $container_function ) ) {
			try {
				$container = call_user_func( $container_function );
				if ( is_object( $container ) && method_exists( $container, 'has' ) && $container->has( 'cachePurger' ) && method_exists( $container, 'get' ) ) {
					$purger = $container->get( 'cachePurger' );
					if ( is_object( $purger ) && method_exists( $purger, 'purge_all' ) ) {
						$purger->purge_all();
						return array(
							'status'  => 'success',
							'message' => __( 'Bluehost cache cleared.', 'divi-lc-kit' ),
						);
					}
				}
			} catch ( Throwable $e ) {
				return array(
					'status'  => 'error',
					'message' => __( 'Bluehost cache purge failed.', 'divi-lc-kit' ),
				);
			}
		}

		if ( class_exists( '\\NewfoldLabs\\WP\\Module\\Performance\\RestApi\\CacheController' ) ) {
			try {
				$controller = new \NewfoldLabs\WP\Module\Performance\RestApi\CacheController();
				$controller->purge_all();
				return array(
					'status'  => 'success',
					'message' => __( 'Bluehost cache cleared.', 'divi-lc-kit' ),
				);
			} catch ( Throwable $e ) {
				return array(
					'status'  => 'error',
					'message' => __( 'Bluehost cache purge failed.', 'divi-lc-kit' ),
				);
			}
		}

		return array(
			'status'  => 'skipped',
			'message' => __( 'Bluehost cache plugin is not active, so this step was skipped.', 'divi-lc-kit' ),
		);
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_run_lc_tweaks_css_js_cache_step' ) ) :
	/**
	 * Clear and rebuild LC Tweaks generated CSS/JS cache files.
	 *
	 * @return array{status:string,message:string}
	 */
	function dlck_misc_csc_run_lc_tweaks_css_js_cache_step(): array {
		if ( ! function_exists( 'dlck_rebuild_all_inline_caches' ) || ! function_exists( 'dlck_inline_assets_get_cache_dir' ) ) {
			return array(
				'status'  => 'skipped',
				'message' => __( 'LC Tweaks CSS/JS cache system is unavailable, so this step was skipped.', 'divi-lc-kit' ),
			);
		}

		try {
			$dir = dlck_inline_assets_get_cache_dir();
			if ( ! is_dir( $dir ) ) {
				wp_mkdir_p( $dir );
			}

			$files = glob( $dir . 'dlck-inline-*.*' );
			if ( is_array( $files ) ) {
				foreach ( $files as $path ) {
					if ( file_exists( $path ) ) {
						@unlink( $path );
					}

					$base = basename( $path );
					delete_option( 'dlck_' . md5( $base ) . '_hash' );
				}
			}

			dlck_rebuild_all_inline_caches();

			return array(
				'status'  => 'success',
				'message' => __( 'LC Tweaks CSS/JS cache cleared and rebuilt.', 'divi-lc-kit' ),
			);
		} catch ( Throwable $e ) {
			return array(
				'status'  => 'error',
				'message' => __( 'Could not clear LC Tweaks CSS/JS cache.', 'divi-lc-kit' ),
			);
		}
	}
endif;

if ( ! function_exists( 'dlck_misc_csc_run_full_cache_clear_server_side' ) ) :
	/**
	 * Run the full cache-clear sequence server-side.
	 *
	 * @param int $user_id Current user ID for one-time local storage clear flag.
	 * @return array{failed:int,skipped:int}
	 */
	function dlck_misc_csc_run_full_cache_clear_server_side( int $user_id = 0 ): array {
		$failed  = 0;
		$skipped = 0;

		if ( ! dlck_csc_clear_static_css_generation() ) {
			$skipped++;
		}

		if ( $user_id > 0 ) {
			dlck_csc_flag_local_storage_clear_for_user( $user_id );
		} else {
			$skipped++;
		}

		$lc_tweaks_cache_result = dlck_misc_csc_run_lc_tweaks_css_js_cache_step();
		if ( $lc_tweaks_cache_result['status'] === 'error' ) {
			$failed++;
		} elseif ( $lc_tweaks_cache_result['status'] === 'skipped' ) {
			$skipped++;
		}

		if ( dlck_csc_lazy_cache_enabled() ) {
			if ( function_exists( 'dlck_divi_lazy_clear_cache_all' ) ) {
				try {
					dlck_divi_lazy_clear_cache_all();
				} catch ( Throwable $e ) {
					$failed++;
				}
			} else {
				$skipped++;
			}
		}

		$step_functions = array(
			'dlck_misc_csc_run_wp_rocket_step',
			'dlck_misc_csc_run_litespeed_step',
			'dlck_misc_csc_run_w3_total_cache_step',
			'dlck_misc_csc_run_nginx_helper_step',
			'dlck_misc_csc_run_siteground_step',
			'dlck_misc_csc_run_wp_engine_step',
			'dlck_misc_csc_run_stack_cache_step',
			'dlck_misc_csc_run_bluehost_cache_step',
		);

		foreach ( $step_functions as $function ) {
			if ( ! function_exists( $function ) ) {
				continue;
			}

			$result = call_user_func( $function );
			$status = ( is_array( $result ) && isset( $result['status'] ) ) ? (string) $result['status'] : 'error';
			if ( $status === 'error' ) {
				$failed++;
			} elseif ( $status === 'skipped' ) {
				$skipped++;
			}
		}

		return array(
			'failed'  => $failed,
			'skipped' => $skipped,
		);
	}
endif;

if ( is_admin() && ! function_exists( 'dlck_misc_csc_clear_all_cache_fallback_action' ) ) :
	/**
	 * Fallback handler when JS cache automation click handling is unavailable.
	 */
	function dlck_misc_csc_clear_all_cache_fallback_action(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'divi-lc-kit' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( 'dlck_misc_clear_all_cache' );

		$summary = dlck_misc_csc_run_full_cache_clear_server_side( is_user_logged_in() ? get_current_user_id() : 0 );

		$redirect = wp_get_referer();
		if ( ! $redirect ) {
			$redirect = admin_url( 'admin.php?page=lc_tweaks&tab=divi-tweaks' );
		}

		$redirect = remove_query_arg(
			array(
				'dlck_cache_automation_done',
				'dlck_cache_automation_failed',
				'dlck_cache_automation_skipped',
			),
			$redirect
		);

		$redirect = add_query_arg(
			array(
				'dlck_cache_automation_done'    => '1',
				'dlck_cache_automation_failed'  => (int) $summary['failed'],
				'dlck_cache_automation_skipped' => (int) $summary['skipped'],
			),
			$redirect
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	add_action( 'admin_post_dlck_misc_clear_all_cache_fallback', 'dlck_misc_csc_clear_all_cache_fallback_action' );
endif;

if ( is_admin() && ! function_exists( 'dlck_misc_csc_cache_fallback_notice' ) ) :
	/**
	 * Admin notice for non-JS cache automation fallback runs.
	 */
	function dlck_misc_csc_cache_fallback_notice(): void {
		if ( ! isset( $_GET['dlck_cache_automation_done'] ) || ! current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$failed  = isset( $_GET['dlck_cache_automation_failed'] ) ? absint( wp_unslash( $_GET['dlck_cache_automation_failed'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$skipped = isset( $_GET['dlck_cache_automation_skipped'] ) ? absint( wp_unslash( $_GET['dlck_cache_automation_skipped'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $failed > 0 ) {
			printf(
				'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: %1$d failed steps, %2$d skipped steps. */
						__( 'Cache automation finished with %1$d failed step(s) and %2$d skipped optional step(s).', 'divi-lc-kit' ),
						$failed,
						$skipped
					)
				)
			);
			return;
		}

		if ( $skipped > 0 ) {
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: %d skipped steps. */
						__( 'Cache automation completed. %d optional step(s) were skipped.', 'divi-lc-kit' ),
						$skipped
					)
				)
			);
			return;
		}

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Cache automation completed.', 'divi-lc-kit' ) . '</p></div>';
	}

	add_action( 'admin_notices', 'dlck_misc_csc_cache_fallback_notice' );
endif;

if ( ! function_exists( 'dlck_csc_auto_clear_updates_enabled' ) ) :
	/**
	 * Check if automatic post-update cache clearing is enabled.
	 */
	function dlck_csc_auto_clear_updates_enabled(): bool {
		return dlck_csc_main_menu_enabled()
			&& function_exists( 'dlck_get_option' )
			&& dlck_get_option( 'dlck_auto_clear_cache_after_updates' ) === '1';
	}
endif;

if ( ! function_exists( 'dlck_csc_auto_clear_hook' ) ) :
	/**
	 * Return the cron hook used for delayed auto-clear runs.
	 */
	function dlck_csc_auto_clear_hook(): string {
		return 'dlck_csc_run_auto_clear_after_updates';
	}
endif;

if ( ! function_exists( 'dlck_csc_auto_clear_delay_seconds' ) ) :
	/**
	 * Delay used to debounce update bursts before running one clear pass.
	 */
	function dlck_csc_auto_clear_delay_seconds(): int {
		return 180;
	}
endif;

if ( ! function_exists( 'dlck_csc_auto_clear_schedule' ) ) :
	/**
	 * Queue or re-queue the debounced auto-clear worker.
	 */
	function dlck_csc_auto_clear_schedule(): void {
		if ( ! dlck_csc_auto_clear_updates_enabled() ) {
			return;
		}

		$hook   = dlck_csc_auto_clear_hook();
		$run_at = time() + dlck_csc_auto_clear_delay_seconds();
		$next   = wp_next_scheduled( $hook );

		// Push the scheduled run out while updates are still happening.
		if ( $next && $next < $run_at ) {
			wp_unschedule_event( $next, $hook );
			$next = false;
		}

		if ( ! $next ) {
			wp_schedule_single_event( $run_at, $hook );
			$next = $run_at;
		}

		update_option( 'dlck_csc_auto_clear_after_updates_run_at', (int) $next, false );
	}
endif;

if ( ! function_exists( 'dlck_csc_auto_clear_after_upgrader' ) ) :
	/**
	 * Queue auto-clear after manual/bulk core, plugin, and theme updates.
	 *
	 * @param WP_Upgrader $upgrader   Upgrader instance.
	 * @param array       $hook_extra Update context.
	 */
	function dlck_csc_auto_clear_after_upgrader( $upgrader, $hook_extra ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( ! is_array( $hook_extra ) ) {
			return;
		}

		$action = isset( $hook_extra['action'] ) ? (string) $hook_extra['action'] : '';
		$type   = isset( $hook_extra['type'] ) ? (string) $hook_extra['type'] : '';

		if ( $action !== 'update' ) {
			return;
		}

		if ( ! in_array( $type, array( 'core', 'plugin', 'theme', 'translation' ), true ) ) {
			return;
		}

		dlck_csc_auto_clear_schedule();
	}

	add_action( 'upgrader_process_complete', 'dlck_csc_auto_clear_after_upgrader', 20, 2 );
endif;

if ( ! function_exists( 'dlck_csc_auto_clear_after_automatic_updates' ) ) :
	/**
	 * Queue auto-clear after background auto-updates.
	 *
	 * @param array $results Automatic updates results.
	 */
	function dlck_csc_auto_clear_after_automatic_updates( $results ): void {
		if ( ! is_array( $results ) ) {
			return;
		}

		$has_updates = ! empty( $results['core'] )
			|| ! empty( $results['plugin'] )
			|| ! empty( $results['theme'] )
			|| ! empty( $results['translation'] );

		if ( ! $has_updates ) {
			return;
		}

		dlck_csc_auto_clear_schedule();
	}

	add_action( 'automatic_updates_complete', 'dlck_csc_auto_clear_after_automatic_updates', 20, 1 );
endif;

if ( ! function_exists( 'dlck_csc_auto_clear_run_scheduled' ) ) :
	/**
	 * Run one server-side clear pass after update bursts.
	 *
	 * Local storage is intentionally skipped because this is server-side only.
	 */
	function dlck_csc_auto_clear_run_scheduled(): void {
		if ( ! dlck_csc_auto_clear_updates_enabled() ) {
			delete_option( 'dlck_csc_auto_clear_after_updates_run_at' );
			return;
		}

		$hook      = dlck_csc_auto_clear_hook();
		$queued_at = (int) get_option( 'dlck_csc_auto_clear_after_updates_run_at', 0 );

		// If a newer update burst pushed the target time out, reschedule and stop.
		if ( $queued_at > ( time() + 5 ) ) {
			if ( ! wp_next_scheduled( $hook ) ) {
				wp_schedule_single_event( $queued_at, $hook );
			}
			return;
		}

		if ( get_transient( 'dlck_csc_auto_clear_lock' ) ) {
			return;
		}

		set_transient( 'dlck_csc_auto_clear_lock', '1', 10 * MINUTE_IN_SECONDS );

		try {
			if ( class_exists( 'ET_Core_PageResource' ) && method_exists( 'ET_Core_PageResource', 'remove_static_resources' ) ) {
				ET_Core_PageResource::remove_static_resources( 'all', 'all' );
			}

			dlck_misc_csc_run_lc_tweaks_css_js_cache_step();

			if ( dlck_csc_lazy_cache_enabled() && function_exists( 'dlck_divi_lazy_clear_cache_all' ) ) {
				try {
					dlck_divi_lazy_clear_cache_all();
				} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
					// Continue with remaining steps.
				}
			}

			dlck_misc_csc_run_wp_rocket_step();
			dlck_misc_csc_run_litespeed_step();
			dlck_misc_csc_run_w3_total_cache_step();
			dlck_misc_csc_run_nginx_helper_step();
			dlck_misc_csc_run_siteground_step();
			dlck_misc_csc_run_wp_engine_step();
			dlck_misc_csc_run_stack_cache_step();
			dlck_misc_csc_run_bluehost_cache_step();
		} finally {
			delete_option( 'dlck_csc_auto_clear_after_updates_run_at' );
			delete_transient( 'dlck_csc_auto_clear_lock' );
		}
	}

	add_action( 'dlck_csc_run_auto_clear_after_updates', 'dlck_csc_auto_clear_run_scheduled' );
endif;

if ( is_admin() && ! function_exists( 'dlck_misc_csc_cache_automation_step_ajax' ) ) :
	/**
	 * AJAX handler: report available automation cache steps.
	 */
	function dlck_misc_csc_cache_automation_capabilities_ajax() {
		check_ajax_referer( 'dlck_misc_clear_all_cache' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do that.', 'divi-lc-kit' ) ), 403 );
		}

		wp_send_json_success(
			array(
				'wp_rocket'          => function_exists( 'rocket_clean_domain' ) ? '1' : '0',
				'litespeed_cache'    => dlck_misc_csc_litespeed_available() ? '1' : '0',
				'w3_total_cache'     => dlck_misc_csc_w3_total_cache_available() ? '1' : '0',
				'nginx_helper_cache' => dlck_misc_csc_nginx_helper_available() ? '1' : '0',
				'siteground_cache'   => dlck_misc_csc_siteground_available() ? '1' : '0',
				'wp_engine_cache'    => dlck_misc_csc_wp_engine_available() ? '1' : '0',
				'stack_cache'        => dlck_misc_csc_stack_cache_available() ? '1' : '0',
				'bluehost_cache'     => ( function_exists( '\\NewfoldLabs\\WP\\ModuleLoader\\container' ) || class_exists( '\\NewfoldLabs\\WP\\Module\\Performance\\RestApi\\CacheController' ) ) ? '1' : '0',
			)
		);
		}

	add_action( 'wp_ajax_dlck_misc_clear_all_cache_capabilities', 'dlck_misc_csc_cache_automation_capabilities_ajax' );

	/**
	 * AJAX handler for individual automated cache steps.
	 */
	function dlck_misc_csc_cache_automation_step_ajax() {
		check_ajax_referer( 'dlck_misc_clear_all_cache' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do that.', 'divi-lc-kit' ) ), 403 );
		}

		$step = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';
		$result = array(
			'status'  => 'error',
			'message' => __( 'Invalid cache automation step.', 'divi-lc-kit' ),
		);

		switch ( $step ) {
			case 'lc_tweaks_css_js':
				$result = dlck_misc_csc_run_lc_tweaks_css_js_cache_step();
				break;
			case 'wp_rocket':
				$result = dlck_misc_csc_run_wp_rocket_step();
				break;
			case 'litespeed_cache':
				$result = dlck_misc_csc_run_litespeed_step();
				break;
			case 'w3_total_cache':
				$result = dlck_misc_csc_run_w3_total_cache_step();
				break;
			case 'nginx_helper_cache':
				$result = dlck_misc_csc_run_nginx_helper_step();
				break;
			case 'siteground_cache':
				$result = dlck_misc_csc_run_siteground_step();
				break;
			case 'wp_engine_cache':
				$result = dlck_misc_csc_run_wp_engine_step();
				break;
			case 'stack_cache':
				$result = dlck_misc_csc_run_stack_cache_step();
				break;
			case 'bluehost_cache':
				$result = dlck_misc_csc_run_bluehost_cache_step();
				break;
		}

		if ( $result['status'] === 'error' ) {
			wp_send_json_error( array( 'message' => $result['message'] ), 500 );
		}

		wp_send_json_success( $result );
	}

	add_action( 'wp_ajax_dlck_misc_clear_all_cache_step', 'dlck_misc_csc_cache_automation_step_ajax' );
endif;
