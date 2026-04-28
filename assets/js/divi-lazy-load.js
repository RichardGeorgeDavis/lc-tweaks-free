/*
 * Lazy load Divi sections on scroll.
 * Based on Divi Rocket lazy loader behavior, adapted for LC Tweaks.
 */

var dlck_lazy_override_jq = 0;
var dlck_lazy_compat_callbacks = [];

function dlck_divi_lazy_compat(cb) {
	if (typeof cb === 'function') {
		dlck_lazy_compat_callbacks.push(cb);
	}
}

if (typeof window !== 'undefined') {
	if (typeof window.dlck_divi_lazy_compat === 'undefined') {
		window.dlck_divi_lazy_compat = dlck_divi_lazy_compat;
	}
	if (typeof window.divi_rocket_lazy_compat === 'undefined') {
		window.divi_rocket_lazy_compat = dlck_divi_lazy_compat;
	}
}

jQuery(document).ready(function($) {
	if (typeof window.dlckLazyLoad === 'undefined') {
		return;
	}

	var config = window.dlckLazyLoad || {};
	var nextChunk = 0;
	var loading = false;
	var chunkCount = parseInt(config.chunkCount, 10) || 0;
	var prefetchOffset = parseInt(config.prefetchOffset, 10) || 0;
	if (prefetchOffset < 0) {
		prefetchOffset = 0;
	}
	var loadAllOnInteraction = config.loadAllOnInteraction === true || config.loadAllOnInteraction === '1';
	var loadAllOnIdle = config.loadAllOnIdle === true || config.loadAllOnIdle === '1';
	if (loadAllOnInteraction && loadAllOnIdle) {
		loadAllOnIdle = false;
	}
	var loadAllTriggered = false;
	var loadAllPending = false;
	var loadedChunks = {0: true};
	var observer = null;
	var deferSections = config.deferSections === true || config.deferSections === '1';
	var deferInitial = parseInt(config.deferInitial, 10);
	if (isNaN(deferInitial) || deferInitial < 1) {
		deferInitial = 2;
	}
	var deferMargin = parseInt(config.deferMargin, 10);
	if (isNaN(deferMargin) || deferMargin < 0) {
		deferMargin = 600;
	}

	function executeScripts(scriptNodes) {
		if (!scriptNodes || !scriptNodes.length) {
			return;
		}

		var container = document.body || document.head;
		if (!container) {
			return;
		}

		for (var i = 0; i < scriptNodes.length; i++) {
			var node = scriptNodes[i];
			if (!node) {
				continue;
			}

			var src = node.getAttribute ? node.getAttribute('src') : '';
			var type = node.getAttribute ? node.getAttribute('type') : '';
			var asyncAttr = node.getAttribute ? node.getAttribute('async') : '';
			var deferAttr = node.getAttribute ? node.getAttribute('defer') : '';

			if (src) {
				if (document.querySelector('script[src="' + src + '"]')) {
					continue;
				}
				var scriptTag = document.createElement('script');
				scriptTag.src = src;
				if (type) {
					scriptTag.type = type;
				}
				if (asyncAttr !== null) {
					scriptTag.async = true;
				}
				if (deferAttr !== null) {
					scriptTag.defer = true;
				}
				container.appendChild(scriptTag);
				continue;
			}

			var code = node.text || node.textContent || node.innerHTML;
			if (!code) {
				continue;
			}

			var inlineTag = document.createElement('script');
			if (type) {
				inlineTag.type = type;
			}
			inlineTag.text = code;
			container.appendChild(inlineTag);
			container.removeChild(inlineTag);
		}
	}

	function collectExecutableScripts($root) {
		var scripts = [];
		var seen = [];
		var collect = function(node) {
			if (!node || seen.indexOf(node) !== -1) {
				return;
			}

			var type = node.getAttribute ? node.getAttribute('type') : '';
			if (type) {
				type = type.toLowerCase();
			}

			if (type && type !== 'text/javascript' && type !== 'application/javascript' && type !== 'module') {
				return;
			}

			seen.push(node);
			scripts.push(node);
			jQuery(node).remove();
		};

		$root.filter('script').each(function() {
			collect(this);
		});

		$root.find('script').each(function() {
			collect(this);
		});

		return scripts;
	}

	function getAnimationClasses() {
		return [
			'et_animated',
			'infinite',
			'et-waypoint',
			'fade',
			'fadeTop',
			'fadeRight',
			'fadeBottom',
			'fadeLeft',
			'slide',
			'slideTop',
			'slideRight',
			'slideBottom',
			'slideLeft',
			'bounce',
			'bounceTop',
			'bounceRight',
			'bounceBottom',
			'bounceLeft',
			'zoom',
			'zoomTop',
			'zoomRight',
			'zoomBottom',
			'zoomLeft',
			'flip',
			'flipTop',
			'flipRight',
			'flipBottom',
			'flipLeft',
			'fold',
			'foldTop',
			'foldRight',
			'foldBottom',
			'foldLeft',
			'roll',
			'rollTop',
			'rollRight',
			'rollBottom',
			'rollLeft',
			'transformAnim'
		];
	}

	function initGoPortfolioImages($context) {
		if (!$context || !$context.length) {
			return;
		}

		var $images = $context.find('.gw-gopf-post-media-wrap img[data-src]');
		if (!$images.length) {
			return;
		}

		$images.each(function() {
			var $img = jQuery(this);
			if ($img.data('dlckGoLoaded')) {
				return;
			}

			var dataSrc = $img.data('src');
			if (!dataSrc) {
				return;
			}

			$img.data('dlckGoLoaded', 1);

			var newImg = new Image();
			newImg.onload = function() {
				$img.attr('src', dataSrc);
				$img.closest('.gw-gopf-post-media-wrap').css('background-image', 'url(\"' + dataSrc + '\")');
				$img.closest('.gw-gopf-post-media-wrap-outer').css('opacity', 1);
			};
			newImg.onerror = function() {};
			newImg.src = dataSrc;
		});
	}

	function initGoPortfolioPopup($portfolio) {
		if (!$portfolio || !$portfolio.length) {
			return;
		}

		if (typeof jQuery.fn.magnificPopup !== 'function') {
			return;
		}

		if (!$portfolio.data('lbenabled')) {
			$portfolio.on(
				'click.dlckGoPortfolio',
				'.gw-gopf-magnific-popup, .gw-gopf-magnific-popup-html',
				function(e) {
					e.preventDefault();
				}
			);
			return;
		}

		if ($portfolio.data('dlckGoPopupInit')) {
			return;
		}

		$portfolio.data('dlckGoPopupInit', 1);

		var $this = $portfolio;
		var mfpOpened = false;
		$portfolio.magnificPopup({
			delegate:
				'.gw-gopf-magnific-popup[data-mfp-src!="#"][data-mfp-src!=""], .gw-gopf-magnific-popup-html[data-mfp-src!="#"][data-mfp-src!=""]',
			type: 'image',
			closeOnContentClick: true,
			removalDelay: 300,
			mainClass: 'my-mfp-slide-bottom',
			closeMarkup: '<a title="%title%" class="gw-gopf-mfp-close"></a>',
			titleSrc: 'title',
			gallery: {
				enabled: !!$this.data('lbgallery'),
				arrowMarkup: '<a title="%title%" class="gw-gopf-mfp-arrow mfp-arrow mfp-arrow-%dir%"></a>'
			},
			image: {
				markup:
					'<div class="mfp-figure">' +
					'<div class="mfp-close"></div>' +
					'<div class="mfp-img"></div>' +
					'<div class="mfp-bottom-bar">' +
					'<div class="gw-gopf-mfp-title mfp-title"></div>' +
					'<div class="gw-gopf-mfp-counter mfp-counter"></div>' +
					'</div>' +
					'</div>'
			},
			iframe: {
				patterns: {
					vimeo: {
						index: 'vimeo.com/',
						id: '/',
						src: '//player.vimeo.com/video/%id%&amp;autoplay=1'
					},
					dailymotion: {
						index: 'dailymotion.com/',
						id: '/',
						src: '//dailymotion.com/embed/video/%id%?autoPlay=1'
					},
					metacafe: {
						index: 'metacafe.com/',
						id: '/',
						src: 'http://www.metacafe.com/embed/%id%?ap=1'
					},
					soundcloud: {
						index: 'soundcloud.com',
						id: null,
						src: '%id%'
					},
					mixcloud: {
						index: 'mixcloud.com',
						id: null,
						src: '%id%'
					},
					beatport: {
						index: 'beatport.com',
						id: null,
						src: '%id%'
					}
				},
				markup:
					'<div class="mfp-iframe-scaler">' +
					'<div class="mfp-close"></div>' +
					'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>' +
					'<div class="mfp-bottom-bar" style="margin-top:4px;"><div class="gw-gopf-mfp-title mfp-title"></div><div class="gw-gopf-mfp-counter mfp-counter"></div></div>' +
					'</div>'
			},
			callbacks: {
				elementParse: function(item) {
					if (jQuery(item.el).hasClass('gw-gopf-magnific-popup-html')) {
						item.type = 'iframe';
					}
				},
				change: function() {
					var $currentItem = jQuery(this.currItem.el);
					if ($currentItem.hasClass('gw-gopf-magnific-popup-html')) {
						setTimeout(function() {
							jQuery('.mfp-title').html($currentItem.attr('title'));
						}, 5);
					}

					if ($this.data('deepLinking')) {
						mfpOpened = $currentItem;
						if (history.pushState) {
							location.hash =
								'#mpf-popup@' +
								$currentItem.attr('data-mfp-src') +
								'|' +
								$currentItem.data('id').split('_')[0] +
								'|' +
								$currentItem.data('id').split('_')[1];
							if (history.state === $currentItem.data('id')) {
								history.pushState(
									$currentItem.data('id'),
									null,
									window.location.href
										.replace(window.location.origin, '')
										.split('#')[0] +
										'#mpf-popup@' +
										$currentItem.attr('href') +
										'|' +
										$currentItem.data('id').split('_')[0] +
										'|' +
										$currentItem.data('id').split('_')[1]
								);
							}
						} else {
							location.hash =
								'#mpf-popup@' +
								$currentItem.attr('href') +
								'|' +
								$currentItem.data('id').split('_')[0] +
								'|' +
								$currentItem.data('id').split('_')[1];
						}
					}

					var forcedHeight = $currentItem.data('height');
					if (forcedHeight !== undefined) {
						setTimeout(function() {
							jQuery('.mfp-iframe-scaler').css({
								paddingTop: 0,
								display: 'table-cell',
								verticalAlign: 'middle',
								height: forcedHeight
							});
						}, 5);
					} else {
						setTimeout(function() {
							jQuery('.mfp-iframe-scaler').css({
								paddingTop: '56.25%',
								display: 'block',
								verticalAlign: 'baseline',
								height: 0
							});
						}, 5);
					}
				},
				beforeClose: function() {
					if ($this.data('deepLinking')) {
						if (history.pushState) {
							history.pushState('', null, window.location.pathname + window.location.search);
						} else {
							var scrollPosX = document.body.scrollTop;
							var scrollPosY = document.body.scrollLeft;
							window.location.hash = '';
							document.body.scrollTop = scrollPosX;
							document.body.scrollLeft = scrollPosY;
						}
					}

					if (jQuery(this.currItem.el).hasClass('gw-gopf-magnific-popup-html')) {
						jQuery('.mfp-wrap').css('display', 'none');
					}

					mfpOpened = false;
				},
				afterClose: function() {
					if (this.items[this.index] && this.items[this.index].type === 'iframe') {
						var timer = setInterval(function() {
							if (!jQuery('.mfp-bg').length) {
								clearInterval(timer);
								$this.find('.gw-gopf-post').css('opacity', '0.99');
								setTimeout(function() {
									$this.find('.gw-gopf-post').css('opacity', '1');
								}, 20);
							}
						}, 50);
					}
				}
			}
		});
	}

	function initGoPortfolioIsotope($portfolio) {
		if (
			!$portfolio ||
			!$portfolio.length ||
			typeof jQuery.fn.callIsotope !== 'function'
		) {
			return;
		}

		$portfolio
			.filter('.gw-gopf-grid-type')
			.each(function() {
				var $this = jQuery(this);
				var $posts = $this.find('.gw-gopf-posts');
				if ($posts.data('dlckGoIsotope')) {
					return;
				}
				$posts.data('dlckGoIsotope', 1);
				$posts.callIsotope('*');
				if (!$this.hasClass('gw-gopf-isotope-ready')) {
					$this.addClass('gw-gopf-isotope-ready');
				}
			});
	}

	function initGoPortfolioFilters($portfolio) {
		if (
			!$portfolio ||
			!$portfolio.length ||
			typeof jQuery.fn.callIsotope !== 'function'
		) {
			return;
		}

		$portfolio.find('.gw-gopf-filter').each(function() {
			var $filter = jQuery(this);
			if ($filter.data('dlckGoFilterBound')) {
				return;
			}
			$filter.data('dlckGoFilterBound', 1);

			$filter.on('click.dlckGoPortfolio', 'div a', function(e) {
				var $link = jQuery(this);
				var $parent = $link.closest('span');
				e.preventDefault();

				$parent.addClass('gw-gopf-current').siblings().removeClass('gw-gopf-current');
				if ($parent.data('filter') === undefined) {
					$portfolio.find('.gw-gopf-posts').callIsotope('*');
					$portfolio.find('.gw-gopf-col-wrap').removeClass('gw-gopf-disabled');
				} else {
					$portfolio
						.find('.gw-gopf-posts')
						.callIsotope('[data-filter~="' + $parent.data('filter') + '"]');
					$portfolio
						.find('.gw-gopf-col-wrap')
						.addClass('gw-gopf-disabled')
						.filter('[data-filter~="' + $parent.data('filter') + '"]')
						.removeClass('gw-gopf-disabled');
				}
			});
		});
	}

	function initGoPortfolioLoadMore($portfolio) {
		if (
			!$portfolio ||
			!$portfolio.length ||
			typeof window.gw_go_portfolio_settings === 'undefined' ||
			!window.gw_go_portfolio_settings.ajaxurl
		) {
			return;
		}

		if ($portfolio.data('dlckGoLoadMoreBound')) {
			return;
		}

		$portfolio.data('dlckGoLoadMoreBound', 1);
		$portfolio.on('click.dlckGoPortfolio', '.gw-gopf-pagination-load-more:not(.gw-gopf-disabled)', function(e) {
			e.preventDefault();

			var $this = jQuery(this);
			var $wrapper = $this.closest('.gw-gopf-pagination-wrapper');
			var $currentPortfolio = $this.closest('.gw-gopf');
			var parentId = $currentPortfolio.parent().attr('id') || '';
			var portfolioId = '';
			if (parentId.indexOf('gw_go_portfolio_') !== -1) {
				portfolioId = parentId.split('gw_go_portfolio_')[1];
			}
			if (!portfolioId) {
				return;
			}
			var currentPage = parseInt($wrapper.data('current-page') || 0, 10) + 1;
			$wrapper.data('current-page', currentPage);

			jQuery
				.ajax({
					type: 'post',
					url: window.gw_go_portfolio_settings.ajaxurl,
					data: jQuery.param({
						action: 'go_portfolio_ajax_load_portfolio',
						portfolio_id: portfolioId,
						current_page: $wrapper.data('current-page'),
						current_id: $wrapper.data('current-id'),
						loaded_ids: $wrapper.data('loaded'),
						taxonomy: $wrapper.data('tax'),
						term_slug: $wrapper.data('term'),
						post_per_page: $wrapper.data('posts-per-page')
					}),
					beforeSend: function() {
						$this.html($this.data('modified'));
						$this.addClass('gw-gopf-disabled');
					}
				})
				.always(function() {
					$this.html($this.data('original'));
					$this.removeClass('gw-gopf-disabled');
					if (parseInt($wrapper.data('current-page'), 10) === parseInt($wrapper.data('pages'), 10)) {
						$wrapper.stop().fadeTo(550, 0, function() {
							$wrapper.stop().slideUp();
						});
					}
				})
				.done(function(data) {
					var $ajaxResponse = jQuery('<div />', { class: 'ajax-response', html: data });
					var $newPosts = $ajaxResponse.find('.gw-gopf-col-wrap');

					if (!$newPosts.length) {
						return;
					}

					var currentPostsIds = ($currentPortfolio.find('.gw-gopf-pagination-wrapper').data('loaded') + '').split(',');
					currentPostsIds = jQuery.grep(currentPostsIds, function(n) {
						return n;
					});

					if ($ajaxResponse.find('.gw-gopf-pagination-wrapper').length) {
						var loadedPostsIds = ($ajaxResponse.find('.gw-gopf-pagination-wrapper').data('loaded') + '').split(',');
						loadedPostsIds = jQuery.grep(loadedPostsIds, function(n) {
							return n;
						});
						$currentPortfolio
							.find('.gw-gopf-pagination-wrapper')
							.data('loaded', loadedPostsIds.concat(currentPostsIds).join(','));
					}

					var $postsContainer = $currentPortfolio.find('.gw-gopf-posts');
					if (typeof jQuery.fn.GWisotope === 'function') {
						$currentPortfolio
							.removeClass('gw-gopf-isotope-ready')
							.addClass('gw-gopf-isotope-new-added')
							.find('.gw-gopf-posts')
							.GWisotope('insert', $newPosts, function() {
								$currentPortfolio.addClass('gw-gopf-isotope-ready');
								$currentPortfolio.removeClass('gw-gopf-isotope-new-added');
								var $currentFilter = $currentPortfolio.find('.gw-gopf-filter .gw-gopf-current');
								if ($currentFilter.length && $currentFilter.data('filter') !== undefined) {
									$currentPortfolio
										.find('.gw-gopf-posts')
										.callIsotope('[data-filter~="' + $currentFilter.data('filter') + '"]');
								}
							});
					} else {
						$postsContainer.append($newPosts);
					}

					initGoPortfolioImages($newPosts);
				});
		});
	}

	function initGoPortfolio($context) {
		if (!$context || !$context.length) {
			return;
		}

		var $portfolios = $context.find('.gw-gopf').addBack('.gw-gopf');
		if (!$portfolios.length) {
			return;
		}

		var isTouchDevice = 'ontouchstart' in window || navigator.msMaxTouchPoints;

		$portfolios.each(function() {
			var $portfolio = jQuery(this);
			if ($portfolio.data('dlckGoInit')) {
				initGoPortfolioImages($portfolio);
				return;
			}

			$portfolio.data('dlckGoInit', 1);
			if (isTouchDevice) {
				$portfolio.addClass('gw-gopf-touch');
			}

			if ($portfolio.hasClass('gw-gopf-slider-type')) {
				$portfolio.find('.gw-gopf-col-wrap').css({ display: 'block', visibility: 'visible' });
			}

			initGoPortfolioIsotope($portfolio);
			initGoPortfolioFilters($portfolio);
			initGoPortfolioLoadMore($portfolio);
			initGoPortfolioPopup($portfolio);
			initGoPortfolioImages($portfolio);
		});
	}

	function initDiviPixelBlogSlider($context) {
		if (!$context || !$context.length || typeof window.Swiper === 'undefined') {
			return;
		}

		var $sliders = $context.find('.dipi-blog-slider-main.preloading');
		if (!$sliders.length) {
			return;
		}

		$sliders.each(function() {
			var $slider = jQuery(this);
			if ($slider.hasClass('swiper-initialized')) {
				return;
			}

			var data = this.dataset || {};
			var $container = $slider.find('.swiper-container');
			var containerEl = $container[0];
			if (!containerEl) {
				return;
			}

			var navigation =
				data.navigation === 'on'
					? { nextEl: '.dipi-sbn' + data.ordernumber, prevEl: '.dipi-sbp' + data.ordernumber }
					: undefined;
			var pagination =
				data.pagination === 'on'
					? {
						el: '.dipi-sp' + data.ordernumber,
						clickable: true,
						dynamicBullets: data.dynamicbullets === 'on',
						dynamicMainBullets: 1
					}
					: undefined;
			var coverflowEffect = {
				rotate: Number(parseInt(data.rotate, 10)),
				stretch: 5,
				depth: 0,
				modifier: 1,
				slideShadows: data.shadow === 'true'
			};

			var options = {
				init: false,
				slidesPerView: Number(data.columnsphone),
				spaceBetween: Number(data.spacebetween_phone),
				speed: Number(data.speed),
				loop: data.loop === 'on',
				autoplay: data.autoplay === 'on' ? { delay: data.autoplayspeed, disableOnInteraction: false } : false,
				effect: data.effect,
				coverflowEffect: data.effect === 'coverflow' ? coverflowEffect : undefined,
				navigation: navigation,
				pagination: pagination,
				centeredSlides: data.centered === 'on' && data.effect === 'coverflow',
				slideClass: 'dipi-blog-post',
				wrapperClass: 'dipi-blog-slider-wrapper',
				setWrapperSize: true,
				observer: true,
				observeParents: true,
				observeSlideChildren: true,
				breakpoints: {
					768: {
						slidesPerView: Number(data.columnstablet),
						spaceBetween: Number(data.spacebetween_tablet) > 0 ? Number(data.spacebetween_tablet) : 0
					},
					981: {
						slidesPerView: Number(data.columnsdesktop),
						spaceBetween: Number(data.spacebetween) > 0 ? Number(data.spacebetween) : 0
					}
				}
			};

			var modules = [];
			if (window.Swiper.Autoplay) modules.push(window.Swiper.Autoplay);
			if (window.Swiper.EffectCoverflow) modules.push(window.Swiper.EffectCoverflow);
			if (window.Swiper.Navigation) modules.push(window.Swiper.Navigation);
			if (window.Swiper.Pagination) modules.push(window.Swiper.Pagination);
			if (window.SwiperAutoplay) modules.push(window.SwiperAutoplay);
			if (window.SwiperEffectCoverflow) modules.push(window.SwiperEffectCoverflow);
			if (window.SwiperNavigation) modules.push(window.SwiperNavigation);
			if (window.SwiperPagination) modules.push(window.SwiperPagination);
			if (modules.length) {
				options.modules = modules;
			}

			var swiper = new window.Swiper(containerEl, options);
			if (data.effect === 'coverflow') {
				$container.addClass('swiper-3d');
			}
			if (data.pauseonhover === 'on' && data.autoplay === 'on') {
				$container.on('mouseenter', function() {
					if (swiper.autoplay) {
						swiper.autoplay.stop();
					}
				});
				$container.on('mouseleave', function() {
					if (swiper.autoplay) {
						swiper.autoplay.start();
					}
				});
			}
			swiper.init();
			if (data.effect === 'coverflow' && !$container.hasClass('swiper-3d')) {
				$container.addClass('swiper-3d');
			}
			$slider.removeClass('preloading').addClass('swiper-initialized');
		});
	}

	function initDiviPixelSwiperModules($context) {
		if (!$context || !$context.length || typeof window.Swiper === 'undefined') {
			return;
		}

		var $sliders = $context.find('.dipi_swiper_container').addBack('.dipi_swiper_container');
		if (!$sliders.length) {
			return;
		}

		$sliders.each(function() {
			var $slider = jQuery(this);
			if ($slider.hasClass('swiper-initialized') || this.swiper) {
				return;
			}

			var rawConfig = $slider.attr('data-config');
			if (!rawConfig) {
				return;
			}

			var config;
			try {
				config = JSON.parse(rawConfig);
			} catch (e) {
				return;
			}

			var navigation =
				config.navigation === 'on'
					? { nextEl: '.' + config.order_class + ' .swiper-button-next', prevEl: '.' + config.order_class + ' .swiper-button-prev' }
					: undefined;
			var pagination =
				config.pagination === 'on'
					? {
						el: '.' + config.order_class + ' .swiper-pagination',
						clickable: true,
						dynamicBullets: config.dynamic_bullets === 'on',
						dynamicMainBullets: Number(config.dynamic_main_bullets)
					}
					: undefined;
			var coverflowEffect =
				config.effect === 'coverflow'
					? {
						depth: Number(parseInt(config.cfe_depth, 10)),
						modifier: Number(parseFloat(config.cfe_modifier)),
						rotate: Number(parseInt(config.cfe_rotate, 10)),
						slideShadows: config.cfe_slide_shadows === 'on',
						stretch: Number(parseInt(config.cfe_stretch, 10)) || 0
					}
					: undefined;
			var autoplay =
				config.autoplay === 'on'
					? { delay: Number(config.autoplay_speed), reverseDirection: config.autoplay_reverse === 'on' }
					: undefined;

			var swiper = new window.Swiper(this, {
				init: false,
				slidesPerView: Number(config.columns_phone),
				spaceBetween: Number(config.space_between_phone),
				speed: Number(config.speed),
				loop: config.loop === 'on',
				autoplay: autoplay,
				effect: config.effect,
				coverflowEffect: coverflowEffect,
				navigation: navigation,
				pagination: pagination,
				centeredSlides: config.centered === 'on',
				setWrapperSize: true,
				observer: true,
				observeParents: true,
				observeSlideChildren: true,
				breakpoints: {
					768: {
						slidesPerView: Number(config.columns_tablet),
						spaceBetween: Number(config.space_between_tablet)
					},
					981: {
						slidesPerView: Number(config.columns_desktop),
						spaceBetween: Number(config.space_between_desktop)
					}
				}
			});

			if (config.pause_on_hover === 'on' && config.autoplay === 'on') {
				var hoverTimer;
				$slider.closest('.et_pb_module').on('mouseenter', function() {
					clearTimeout(hoverTimer);
					swiper.autoplay && swiper.autoplay.stop();
				});
				$slider.closest('.et_pb_module').on('mouseleave', function() {
					clearTimeout(hoverTimer);
					hoverTimer = setTimeout(function() {
						swiper.autoplay && swiper.autoplay.start();
					}, Number(config.speed));
				});
			}

			swiper.init();
		});
	}

	function initDiviPixelSvgAnimator($context) {
		if (
			!$context ||
			!$context.length ||
			typeof window.Vivus === 'undefined' ||
			typeof window.et_builder_version !== 'undefined'
		) {
			return;
		}

		var $wrappers = $context.find('.dipi-svg-animator-inner-wrapper').addBack('.dipi-svg-animator-inner-wrapper');
		if (!$wrappers.length) {
			return;
		}

		$wrappers.each(function() {
			var $wrapper = jQuery(this);
			if ($wrapper.data('dlckSvgInit')) {
				return;
			}

			var dataAttr = this.dataset ? this.dataset.config : $wrapper.attr('data-config');
			if (!dataAttr) {
				return;
			}

			var config;
			try {
				config = typeof dataAttr === 'string' ? JSON.parse(dataAttr) : dataAttr;
			} catch (e) {
				return;
			}

			if (!config || !config.svg_id) {
				return;
			}

			var svgId = 'svg-' + config.svg_id;
			if (!document.getElementById(svgId)) {
				return;
			}

			$wrapper.parent().removeClass('preloading').css('opacity', '1');

			var vivus = new window.Vivus(svgId, {
				type: config.type,
				duration: config.duration,
				start: config.start,
				pathTimingFunction: window.Vivus[config.pathTimingFunction],
				animTimingFunction: window.Vivus[config.animTimingFunction],
				forceRender: /^((?!chrome|android).)*(msie|edge|trident|safari)/i.test(window.navigator.userAgent)
			});

			if (config.replay_on_click === 'on') {
				$wrapper.on('click', function() {
					vivus.stop().reset().play();
				});
			}

			$wrapper.data('dlckSvgInit', 1);
		});
	}

	function runDipiPlugin($context, selector, method, args, dataKey) {
		if (!$context || !$context.length || typeof jQuery.fn[method] !== 'function') {
			return;
		}

		var $targets = $context.find(selector).addBack(selector);
		if (!$targets.length) {
			return;
		}

		$targets.each(function() {
			var $el = jQuery(this);
			if (dataKey && $el.data(dataKey)) {
				return;
			}

			if (dataKey) {
				$el.data(dataKey, 1);
			}

			try {
				if (Array.isArray(args)) {
					$el[method].apply($el, args);
				} else if (typeof args !== 'undefined') {
					$el[method](args);
				} else {
					$el[method]();
				}
			} catch (e) {
				// Ignore plugin errors.
			}
		});
	}

	function getDipiGridMaxPages($item, $grid) {
		var maxPages = parseInt($item.data('pages'), 10) || parseInt($grid.data('pages'), 10) || 0;
		if (maxPages) {
			return maxPages;
		}

		$grid.find('.grid-item').each(function() {
			var match = (this.className || '').match(/(?:^|\s)page-(\d+)(?:\s|$)/);
			if (match) {
				var page = parseInt(match[1], 10);
				if (page > maxPages) {
					maxPages = page;
				}
			}
		});

		return maxPages;
	}

	function applyDipiGridAnimation($grid, animClass) {
		if (!animClass) {
			return;
		}

		var gridEl = $grid[0];
		if (!gridEl) {
			return;
		}

		try {
			gridEl.classList.remove(animClass);
			gridEl.offsetWidth;
			gridEl.classList.add(animClass);
		} catch (e) {
			// Ignore animation reset errors.
		}
	}

	function setDipiFilterActive($module, index, itemSelector) {
		if (!index && index !== 0) {
			return;
		}

		var idx = String(index);
		$module.find('.dipi-filter-bar-item').removeClass('active');
		$module.find('.dipi-filter-bar-item-' + idx).addClass('active');
		$module.find(itemSelector).removeClass('active');
		$module.find(itemSelector + '-' + idx).addClass('active');

		try {
			window.dispatchEvent(new Event('resize'));
		} catch (e) {
			// Ignore resize event errors.
		}
	}

	function bindDipiPagination($item) {
		var $grid = $item.find('.grid').first();
		if (!$grid.length) {
			return;
		}

		var maxPages = getDipiGridMaxPages($item, $grid);
		var animClass = $item.data('anim');
		var $pagination = $item.find('.dipi-pagination').first();
		var $loadMore = $item.find('.dipi-loadmore-btn').first();

		if ($pagination.length) {
			$pagination.find('.dipi-pagination-btn').off('click.dlckDipi').on('click.dlckDipi', function() {
				var rawPage = jQuery(this).data('page');
				var current = parseInt($pagination.find('.dipi-pagination-btn.active').data('page'), 10) || 1;
				var target = current;

				if (rawPage === 'prev') {
					target = current > 1 ? current - 1 : 1;
				} else if (rawPage === 'next') {
					target = maxPages && current < maxPages ? current + 1 : current;
				} else {
					target = parseInt(rawPage, 10);
				}

				if (!target || (maxPages && target > maxPages)) {
					return;
				}

				$pagination.find('.dipi-pagination-btn').removeClass('active active-prev active-next');
				$pagination.find('.dipi-pagination-btn-' + target).addClass('active');
				if (target > 1) {
					$pagination.find('.dipi-pagination-btn-' + (target - 1)).addClass('active-prev');
				}
				if (maxPages && target < maxPages) {
					$pagination.find('.dipi-pagination-btn-' + (target + 1)).addClass('active-next');
				}

				$grid.find('.grid-item').addClass('hidden');
				$grid.find('.grid-item.page-' + target).removeClass('hidden');
				applyDipiGridAnimation($grid, animClass);
				if ($grid.data('masonry')) {
					try {
						$grid.masonry('layout');
					} catch (e) {
						// Ignore masonry errors.
					}
				}
			});
		}

		if ($loadMore.length) {
			$loadMore.off('click.dlckDipi').on('click.dlckDipi', function() {
				if (!maxPages) {
					return;
				}

				var $btn = jQuery(this);
				var nextPage = parseInt($btn.data('page'), 10) || 1;
				if (nextPage >= maxPages) {
					$btn.hide();
					return;
				}

				nextPage += 1;
				$btn.data('page', nextPage);
				$grid.find('.grid-item.page-' + nextPage).removeClass('hidden');
				applyDipiGridAnimation($grid, animClass);
				if ($grid.data('masonry')) {
					try {
						$grid.masonry('layout');
					} catch (e) {
						// Ignore masonry errors.
					}
				}

				if (nextPage >= maxPages) {
					$btn.hide();
				}
			});
		}
	}

	function initDiviPixelFilterableGallery($context) {
		if (!$context || !$context.length || typeof jQuery.fn.dipi_filterable_gallery !== 'function') {
			return;
		}

		$context.find('.dipi_filterable_gallery').addBack('.dipi_filterable_gallery').each(function() {
			var $module = jQuery(this);

			$module.find('.grid').each(function() {
				var $grid = jQuery(this);
				if ($grid.data('dlckDipiFilterGalleryInit')) {
					return;
				}
				$grid.data('dlckDipiFilterGalleryInit', 1);
				$grid.dipi_filterable_gallery();
			});

			$module.find('.dipi-filter-bar-item').off('click.dlckDipi').on('click.dlckDipi', function() {
				setDipiFilterActive($module, jQuery(this).data('index'), '.dipi-filtered-gallery-item');
			});

			$module.find('.dipi-filtered-gallery-item').each(function() {
				bindDipiPagination(jQuery(this));
			});
		});
	}

	function initDiviPixelFilterableGrid($context) {
		if (!$context || !$context.length || typeof jQuery.fn.dipi_filterable_grid !== 'function') {
			return;
		}

		$context.find('.dipi_filterable_grid').addBack('.dipi_filterable_grid').each(function() {
			var $module = jQuery(this);

			$module.find('.grid').each(function() {
				var $grid = jQuery(this);
				if ($grid.data('dlckDipiFilterGridInit')) {
					return;
				}
				$grid.data('dlckDipiFilterGridInit', 1);
				$grid.dipi_filterable_grid();
			});

			$module.find('.dipi-filter-bar-item').off('click.dlckDipi').on('click.dlckDipi', function() {
				setDipiFilterActive($module, jQuery(this).data('index'), '.dipi-filtered-posts-item');
			});

			$module.find('.dipi-filtered-posts-item').each(function() {
				bindDipiPagination(jQuery(this));
			});
		});
	}

	function initDiviPixelMasonryGallery($context) {
		if (!$context || !$context.length || typeof jQuery.fn.dipi_masonry_gallery !== 'function') {
			return;
		}

		$context.find('.dipi_masonry_gallery').addBack('.dipi_masonry_gallery').each(function() {
			var $module = jQuery(this);
			$module.find('.grid').each(function() {
				var $grid = jQuery(this);
				if ($grid.data('dlckDipiMasonryInit')) {
					return;
				}
				$grid.data('dlckDipiMasonryInit', 1);
				$grid.dipi_masonry_gallery($module[0]);
			});
		});
	}

	function initDiviPixelExtraModules($context) {
		if (!$context || !$context.length) {
			return;
		}

		runDipiPlugin($context, '.dipi_before_after_slider .dipi_before_after_slider_container', 'dipi_before_after_slider');
		runDipiPlugin($context, '.dipi_counter', 'dipi_counter');
		runDipiPlugin($context, '.dipi_fancy_text', 'dipiFancyText');
		runDipiPlugin($context, '.dipi_scroll_image', 'dipiScrollImage');
		runDipiPlugin($context, '.dipi_image_accordion', 'dipi_image_accordion');
		runDipiPlugin($context, '.dipi_carousel', 'dipiCarousel');
		runDipiPlugin($context, '.dipi_reveal', 'dipi_reveal');
		runDipiPlugin($context, '.dipi_tile_scroll', 'dipi_tile_scroll');
		runDipiPlugin($context, '.dipi_timeline_item_custom_classes', 'dipi_timeline_item');
		runDipiPlugin($context, '.dipi-lottie-icon', 'dipi_lottie_icon');

		if (typeof jQuery.fn.dipiImageRotator === 'function') {
			$context
				.find('.dipi_image_rotator .dipi-image-rotator')
				.addBack('.dipi_image_rotator .dipi-image-rotator')
				.each(function() {
					var $rotator = jQuery(this);
					if ($rotator.data('dlckDipiImageRotatorInit')) {
						return;
					}
					$rotator.data('dlckDipiImageRotatorInit', 1);
					if (typeof $rotator.imagesLoaded === 'function') {
						$rotator.imagesLoaded(function() {
							$rotator.dipiImageRotator();
						});
					} else {
						$rotator.dipiImageRotator();
					}
				});
		}

		if (typeof jQuery.fn.dipi_gf_styler === 'function') {
			$context
				.find('.dipi_gf_styler_container')
				.addBack('.dipi_gf_styler_container')
				.each(function() {
					var $module = jQuery(this);
					if ($module.data('dlckDipiGfStylerInit')) {
						return;
					}
					$module.data('dlckDipiGfStylerInit', 1);
					$module.dipi_gf_styler(this);
				});
		}
	}

	function initDiviPixelCoreModules($context) {
		if (!$context || !$context.length) {
			return;
		}

		initDiviPixelContentToggle($context);

		if (typeof jQuery.fn.dipiContentSlider === 'function') {
			$context.find('.dipi_content_slider').each(function() {
				jQuery(this).dipiContentSlider(true);
			});
		}

		if (typeof jQuery.fn.dipiAdvancedTabs === 'function') {
			$context.find('.dipi-advanced-tabs-front').each(function() {
				jQuery(this).dipiAdvancedTabs();
			});
		}

		if (typeof jQuery.fn.dipi_text_highlighter === 'function') {
			$context.find('.dipi_text_highlighter').each(function() {
				jQuery(this).dipi_text_highlighter();
			});
		}

		if (typeof jQuery.fn.dipiTableMaker === 'function') {
			$context.find('.dipi_table_maker').each(function() {
				jQuery(this).dipiTableMaker(true);
			});
		}

		if (typeof jQuery.fn.dipi_expanding_cta === 'function') {
			$context
				.find('.dipi_expanding_cta')
				.addBack('.dipi_expanding_cta')
				.each(function() {
					var $module = jQuery(this);
					if ($module.data('dlckExpandingCtaInit')) {
						return;
					}
					$module.data('dlckExpandingCtaInit', 1);
					$module.dipi_expanding_cta();
				});
		}

		if (typeof jQuery.fn.dipi_faq_setup === 'function') {
			$context
				.find('.dipi_faq .dipi-faq-wrapper')
				.addBack('.dipi_faq .dipi-faq-wrapper')
				.each(function() {
					var $wrapper = jQuery(this);
					$wrapper.dipi_faq_setup();
					$wrapper.removeClass('loading').show();
				});
		}

		initDiviPixelSvgAnimator($context);
	}

	function initDiviPixelContentToggle($context) {
		if (!$context || !$context.length || typeof jQuery.fn.dipi_content_toggle !== 'function') {
			return;
		}

		var $containers = $context
			.find('.dipi-content-toggle-container')
			.addBack('.dipi-content-toggle-container');

		if ($containers.length) {
			$containers.each(function() {
				jQuery(this).dipi_content_toggle(true);
			});
		}

		$context
			.find('.dipi-content-toggle__content.disable_browser_lazyload img')
			.addBack('.dipi-content-toggle__content.disable_browser_lazyload img')
			.removeAttr('loading');

		$context
			.find('.dipi-content-toggle__content.disable_wprocket_lazyload img')
			.addBack('.dipi-content-toggle__content.disable_wprocket_lazyload img')
			.addClass('skip-lazy');

		$context
			.find('.dipi-content-toggle__switch')
			.addBack('.dipi-content-toggle__switch')
			.each(function() {
				var $switch = jQuery(this);
				if ($switch.data('dlckDipiToggleSwitchBound')) {
					return;
				}

				$switch.data('dlckDipiToggleSwitchBound', 1);
				$switch.on('change.dlckDipiToggle', function() {
					var $container = jQuery(this).closest('.dipi-content-toggle-container');
					if ($container.length) {
						$container.dipi_content_toggle(false);
					}
				});
			});

		$context
			.find('.dipi-content-toggle__first-text')
			.addBack('.dipi-content-toggle__first-text')
			.each(function() {
				var $text = jQuery(this);
				if ($text.data('dlckDipiToggleFirstBound')) {
					return;
				}

				$text.data('dlckDipiToggleFirstBound', 1);
				$text.on('click.dlckDipiToggle', function() {
					var $container = jQuery(this).closest('.dipi-content-toggle-container');
					var $switch = $container.find('.dipi-content-toggle__switch');
					if ($switch.length) {
						$switch.prop('checked', false).trigger('change');
					}
				});
			});

		$context
			.find('.dipi-content-toggle_second-text')
			.addBack('.dipi-content-toggle_second-text')
			.each(function() {
				var $text = jQuery(this);
				if ($text.data('dlckDipiToggleSecondBound')) {
					return;
				}

				$text.data('dlckDipiToggleSecondBound', 1);
				$text.on('click.dlckDipiToggle', function() {
					var $container = jQuery(this).closest('.dipi-content-toggle-container');
					var $switch = $container.find('.dipi-content-toggle__switch');
					if ($switch.length) {
						$switch.prop('checked', true).trigger('change');
					}
				});
			});
	}

	function dispatchDiviPixelEvents() {
		if (typeof window === 'undefined' || typeof window.dispatchEvent !== 'function') {
			return;
		}

		var events = [
			'dipi-content-loaded',
			'dipi-content-changed',
			'dipi-at-loaded',
			'dipi-at-tab-changed',
			'dipi-cs-slide-changed',
			'dipi-ct-changed'
		];

		for (var i = 0; i < events.length; i++) {
			try {
				window.dispatchEvent(new Event(events[i]));
			} catch (e) {
				// Ignore event errors.
			}

			try {
				if (typeof jQuery !== 'undefined' && jQuery(window).trigger) {
					jQuery(window).trigger(events[i]);
				}
			} catch (e) {
				// Ignore jQuery event errors.
			}
		}
	}

	function initDiviPixelModules($context) {
		if (!$context || !$context.length) {
			return;
		}

		if (!$context.find('[class*="dipi-"], [class*="dipi_"]').addBack('[class*="dipi-"], [class*="dipi_"]').length) {
			return;
		}

		initDiviPixelBlogSlider($context);
		initDiviPixelSwiperModules($context);
		initDiviPixelCoreModules($context);
		initDiviPixelExtraModules($context);
		initDiviPixelFilterableGallery($context);
		initDiviPixelFilterableGrid($context);
		initDiviPixelMasonryGallery($context);
		dispatchDiviPixelEvents();
	}

	var dlckScrollEffectsRefreshTimer = null;

	function hasDiviMotionElements() {
		if (typeof window === 'undefined' || typeof window.et_pb_motion_elements === 'undefined') {
			return false;
		}

		var devices = ['desktop', 'tablet', 'phone'];
		for (var i = 0; i < devices.length; i++) {
			var deviceElements = window.et_pb_motion_elements[devices[i]];
			if (Array.isArray(deviceElements) && deviceElements.length) {
				return true;
			}
			if (deviceElements && typeof deviceElements === 'object' && Object.keys(deviceElements).length) {
				return true;
			}
		}

		return false;
	}

	function refreshDiviScrollEffects() {
		if (dlckScrollEffectsRefreshTimer) {
			return;
		}

		dlckScrollEffectsRefreshTimer = setTimeout(function() {
			dlckScrollEffectsRefreshTimer = null;

			if (typeof window === 'undefined') {
				return;
			}

			var refreshed = false;

			try {
				if (window.et_pb_scroll_effects && typeof window.et_pb_scroll_effects.refresh === 'function') {
					window.et_pb_scroll_effects.refresh();
					refreshed = true;
				}
			} catch (e) {
				// Ignore scroll effect refresh errors.
			}

			try {
				if (!refreshed && window.et_pb_motion_effects && typeof window.et_pb_motion_effects.refresh === 'function') {
					window.et_pb_motion_effects.refresh();
					refreshed = true;
				}
			} catch (e) {
				// Ignore motion effect refresh errors.
			}

			if (refreshed) {
				return;
			}

			if (!hasDiviMotionElements()) {
				return;
			}

			forceDiviScrollEffectsResizeRefresh();
		}, 60);
	}

	function forceDiviScrollEffectsResizeRefresh() {
		if (typeof document === 'undefined' || typeof jQuery === 'undefined') {
			return;
		}

		var target = document.scrollingElement || document.documentElement || document.body || null;

		if (!target) {
			return;
		}

		try {
			target.getBoundingClientRect();
		} catch (e) {
			// Ignore layout read errors.
		}

		try {
			jQuery(window).trigger('resize');
		} catch (e) {
			// Ignore resize trigger errors.
		}

		setTimeout(function() {
			try {
				jQuery(window).trigger('resize');
			} catch (e) {
				// Ignore resize trigger errors.
			}
		}, 0);
	}

	function refreshDiviWaypoints() {
		if (typeof window !== 'undefined' && typeof window.et_reinit_waypoint_modules === 'function') {
			window.et_reinit_waypoint_modules();
		}

		if (typeof window !== 'undefined' && typeof window.et_pb_animation_init === 'function') {
			window.et_pb_animation_init();
		}

		if (typeof window !== 'undefined' && window.Waypoint && typeof window.Waypoint.refreshAll === 'function') {
			window.Waypoint.refreshAll();
		}

		try {
			jQuery(window).trigger('scroll');
		} catch (e) {
			// Ignore scroll trigger errors.
		}

		refreshDiviScrollEffects();
	}

	function requestChunk(chunkIndex, obsObject, classPrefix, doneCallback) {
		if (loading) {
			if (loadAllTriggered) {
				loadAllPending = true;
			}
			return;
		}
		if (loadedChunks[chunkIndex]) {
			if (typeof doneCallback === 'function') {
				doneCallback(true);
			}
			return;
		}

		loading = true;
		$('html').trigger('dlck_lazy_load_request');

		var request = jQuery.post(
			config.ajaxUrl,
			{
				action: 'dlck_lazy_load_section',
				post_id: config.postId,
				chunk: parseInt(chunkIndex, 10)
			},
			function(data) {
				if (!data) {
					loadedChunks[chunkIndex] = true;
					if (!loadAllTriggered) {
						removeLoader();
						$('html').trigger('dlck_lazy_load_end');
					}
					loading = false;
					if (loadAllTriggered && loadAllPending) {
						loadAllPending = false;
						processLoadAll();
					}
					if (typeof doneCallback === 'function') {
						doneCallback(false);
					}
					return;
				}

				$('html').trigger('dlck_lazy_load_receive');

				var parsed = jQuery.parseHTML(data, document, true) || [];
				var $data = jQuery(parsed);
				var $styles = $data.filter('style');
				if ($styles.length) {
					$styles.appendTo('head');
				}

				var scriptNodes = collectExecutableScripts($data);

				var lastSection = null;
				$data.filter('div').each(function() {
					if (!this.className) {
						return;
					}

					var indexClassStart = this.className.indexOf(classPrefix);
					if (indexClassStart === -1) {
						return;
					}

					var index = '';
					var pos = indexClassStart + classPrefix.length;
					while (this.className[pos] !== ' ' && !isNaN(this.className[pos])) {
						index = index.concat(this.className[pos]);
						++pos;
					}

					if (!index) {
						return;
					}

					if (!jQuery('#main-content .' + classPrefix + index).length) {
						var $prevSection;
						var prevIndex = index - 1;
						do {
							$prevSection = jQuery('#main-content .' + classPrefix + prevIndex);
						} while (!$prevSection.length && --prevIndex >= 0);

						if ($prevSection.length) {
							lastSection = jQuery(this).insertAfter($prevSection);
							if (lastSection.length) {
								initContent(lastSection);
							}
						}
					}
				});

				loadedChunks[chunkIndex] = true;

				executeScripts(scriptNodes);

				refreshDiviWaypoints();

				if (!loadAllTriggered) {
					if (lastSection && obsObject) {
						var et_pb_sections = jQuery('#main-content .et_pb_section:visible');
						if (et_pb_sections.length) {
							obsObject.observe(et_pb_sections[et_pb_sections.length - 1]);
						}
					} else if (!lastSection) {
						removeLoader();
						$('html').trigger('dlck_lazy_load_end');
					}
				}

				loading = false;
				if (loadAllTriggered && loadAllPending) {
					loadAllPending = false;
					processLoadAll();
				}
				if (typeof doneCallback === 'function') {
					doneCallback(true);
				}
			}
		);
		request.fail(function() {
			loading = false;
			removeLoader();
			$('html').trigger('dlck_lazy_load_end');
			if (loadAllTriggered && loadAllPending) {
				loadAllPending = false;
				processLoadAll();
			}
			if (typeof doneCallback === 'function') {
				doneCallback(false);
			}
		});
	}

	function observeObject(obsObject, chunkIndex, element, classPrefix) {
		obsObject.unobserve(element.target);
		if (loadAllTriggered) {
			return;
		}
		requestChunk(chunkIndex, obsObject, classPrefix);
	}

	function lazyDivs() {
		var IO = dlck_lazy_IO(window);
		var nextRow = nextChunk;
		var observedRows = [];

		insertLoader();

		var observerOptions = {};
		if (prefetchOffset > 0) {
			observerOptions.rootMargin = '0px 0px ' + prefetchOffset + 'px 0px';
		}

		observer = new IO(function(entries, obs) {
			entries.forEach(function(entry) {
				if (entry.isIntersecting !== false) {
					if (window.Waypoint && Waypoint.refreshAll) {
						Waypoint.refreshAll();
					}
					nextRow++;
					if (observedRows.indexOf(nextRow) === -1) {
						observedRows.push(nextRow);
						observeObject(obs, nextRow, entry, 'et_pb_section_');
					}
				}
			});
		}, observerOptions);

		var et_pb_sections = jQuery('#main-content .et_pb_section:visible');
		if (et_pb_sections.length) {
			observer.observe(et_pb_sections[et_pb_sections.length - 1]);
		}
	}

	function insertLoader() {
		if (jQuery('#dlck-lazy-loader').length) {
			return;
		}

		jQuery('<div>')
			.attr('id', 'dlck-lazy-loader')
			.append(
				jQuery('<div>')
					.attr('id', 'dlck-lazy-loader-inner')
					.html(
						'<div class="dlck-lazy-lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
					)
			)
			.insertAfter('#main-content .et_pb_section:last');
	}

	function removeLoader() {
		jQuery('#dlck-lazy-loader').remove();
	}

	function initContent($s) {
		var selectorPre = '#' + $s.parent().closest('[id]').attr('id');
		var sClasses = $s.attr('class').split(' ');
		for (var i = 0; i < sClasses.length; ++i) {
			if (sClasses[i].length > 14 && sClasses[i].substr(0, 14) === 'et_pb_section_') {
				var sNum = sClasses[i].substr(14);
				if (!isNaN(sNum)) {
					selectorPre += ' .et_pb_section_' + sNum;
					break;
				}
			}
		}

		var $numberCounters = $s.find('.et_pb_number_counter');
		if ($numberCounters.length) {
			window.et_pb_reinit_number_counters && et_pb_reinit_number_counters($numberCounters);
		}

		var diviRocketJq = jQuery.fn.init.prototype.init;
		jQuery.fn.init.prototype.init = function(selector, context) {
			if (dlck_lazy_override_jq === 1 && typeof selector === 'function') {
				dlck_lazy_override_jq = 2;
				selector(jQuery);
				dlck_lazy_override_jq = 0;
				return;
			}

			if (
				dlck_lazy_override_jq === 2 &&
				(!context || context === document) &&
				selector &&
				selector.substr
			) {
				var selectors = selector.split(',');
				var newSelectors = [];
				for (var i = 0; i < selectors.length; ++i) {
					selectors[i] = selectors[i].trim();
					if (
						selectors[i].substr(0, 4) !== 'body' &&
						selectors[i].substr(0, 4) !== 'html' &&
						selectors[i].indexOf('#') === -1
					) {
						newSelectors.push(selectorPre + ' ' + selectors[i]);
						if (selectors[i][0] === '.') {
							newSelectors.push(selectorPre + selectors[i]);
						}
					} else {
						if (selectors[i] === 'body' || selectors[i] === 'html') {
							selectors[i] = '#dlck-lazy-non-existent';
						}
						newSelectors.push(selectors[i]);
					}
				}

				selector = newSelectors.join(',');
			}

			return new diviRocketJq(selector, context);
		};

		dlck_lazy_override_jq = 1;

		var isBuilder = 'object' === typeof window.ET_Builder;
		var topWindow = isBuilder ? window.top : window;
		var $et_window = $(topWindow);
		if (typeof et_calculate_fullscreen_section_size === 'function') {
			$et_window.off('resize', et_calculate_fullscreen_section_size);
			$et_window.off('et-pb-header-height-calculated', et_calculate_fullscreen_section_size);
		}

		if (typeof window.et_pb_init_modules === 'function') {
			et_pb_init_modules();
		}

		jQuery.fn.init.prototype.init = diviRocketJq;

		$s.find('.et_pb_blog_grid').each(function() {
			if (!window.salvattore || typeof salvattore.registerGrid !== 'function') {
				return;
			}

			var currentHref = window.location.href;
			var $currentModule = $(this).closest('.et_pb_module');
			if (!$currentModule.length) {
				return;
			}

			var moduleClasses = ($currentModule.attr('class') || '').split(' ');
			var moduleClassProcessed = '';
			var animationClasses = getAnimationClasses();

			window.et_pb_ajax_pagination_cache = window.et_pb_ajax_pagination_cache || [];

			$.each(moduleClasses, function(index, value) {
				if ($.inArray(value, animationClasses) !== -1 || value === 'et_had_animation') {
					return;
				}
				if (value && value.trim() !== '') {
					moduleClassProcessed += '.' + value;
				}
			});

			window.et_pb_ajax_pagination_cache[currentHref + moduleClassProcessed] = $currentModule.find('.et_pb_ajax_pagination_container');
			window.et_pb_ajax_pagination_cache[
				currentHref + (currentHref.indexOf('?') === -1 ? '?' : '&') + 'et_blog' + moduleClassProcessed
			] = window.et_pb_ajax_pagination_cache[currentHref + moduleClassProcessed];

			var gridNode = $currentModule.find('.et_pb_salvattore_content')[0];
			if (gridNode) {
				salvattore.registerGrid(gridNode);
			}

			var $pagination = $(this)
				.find('.et_pb_ajax_pagination_container .wp-pagenavi, .et_pb_ajax_pagination_container .pagination')
				.first();

			if ($pagination.length) {
				jQuery('<a>')
					.attr('href', currentHref)
					.text((config && config.strings && config.strings.loading) ? config.strings.loading : 'Loading...')
					.appendTo($pagination)
					.click()
					.remove();
			}
		});

		for (var i = 0; i < dlck_lazy_compat_callbacks.length; i++) {
			try {
				dlck_lazy_compat_callbacks[i]($s);
			} catch (e) {
				// Ignore compat callback errors to avoid breaking lazy load.
			}
		}

		initGoPortfolioImages($s);
		initGoPortfolio($s);
		initDiviPixelModules($s);
	}

	function getNextUnloadedChunk() {
		for (var i = 1; i < chunkCount; i++) {
			if (!loadedChunks[i]) {
				return i;
			}
		}
		return null;
	}

	function processLoadAll(previousSuccess) {
		if (previousSuccess === false) {
			removeLoader();
			$('html').trigger('dlck_lazy_load_end');
			return;
		}

		var next = getNextUnloadedChunk();
		if (next === null) {
			removeLoader();
			$('html').trigger('dlck_lazy_load_end');
			return;
		}

		requestChunk(next, null, 'et_pb_section_', function(success) {
			processLoadAll(success);
		});
	}

	function startLoadAll() {
		if (loadAllTriggered || !chunkCount || chunkCount < 2) {
			return;
		}
		loadAllTriggered = true;
		if (observer && typeof observer.disconnect === 'function') {
			observer.disconnect();
		}
		insertLoader();
		if (loading) {
			loadAllPending = true;
			return;
		}
		processLoadAll();
	}

	function setupLoadAllOnInteraction() {
		if (!loadAllOnInteraction) {
			return;
		}

		var trigger = function() {
			startLoadAll();
		};

		var events = ['scroll', 'wheel', 'touchstart', 'keydown', 'mousedown'];
		events.forEach(function(eventName) {
			if (window.addEventListener) {
				window.addEventListener(eventName, trigger, { once: true, passive: true });
			} else if (window.attachEvent) {
				window.attachEvent('on' + eventName, trigger);
			}
		});
	}

	function setupLoadAllOnIdle() {
		if (!loadAllOnIdle) {
			return;
		}

		if (typeof window.requestIdleCallback === 'function') {
			window.requestIdleCallback(function() {
				startLoadAll();
			}, { timeout: 4000 });
			return;
		}

		setTimeout(function() {
			startLoadAll();
		}, 3000);
	}

	function initDeferSections() {
		if (!deferSections) {
			return;
		}

		if (chunkCount && chunkCount > 1) {
			return;
		}

		var $sections = $('.dlck-defer-section');
		if (!$sections.length) {
			var $fallback = $('#main-content .et_pb_section');
			if ($fallback.length) {
				$sections = $fallback.slice(deferInitial);
				if ($sections.length) {
					$sections.addClass('dlck-defer-section');
				}
			}
		}

		if (!$sections.length) {
			return;
		}

		$sections.each(function() {
			var $section = $(this);
			if (!$section.hasClass('is-revealed')) {
				$section.addClass('is-deferred');
			}
		});

		var Observer = null;
		if (typeof window.IntersectionObserver === 'function') {
			Observer = window.IntersectionObserver;
		} else if (typeof dlck_lazy_IO === 'function') {
			try {
				Observer = dlck_lazy_IO(window);
			} catch (e) {
				Observer = null;
			}
		}

		var revealSection = function(el) {
			var $el = $(el);
			$el.removeClass('is-deferred').addClass('is-revealed');
			refreshDiviWaypoints();
			initDiviPixelModules($el);
			try {
				jQuery(window).trigger('resize');
			} catch (e) {
				// Ignore resize trigger errors.
			}
		};

		if (!Observer) {
			$sections.each(function() {
				revealSection(this);
			});
			return;
		}

		var io = new Observer(function(entries, observerRef) {
			entries.forEach(function(entry) {
				if (!entry || !entry.isIntersecting) {
					return;
				}

				revealSection(entry.target);

				if (observerRef && typeof observerRef.unobserve === 'function') {
					observerRef.unobserve(entry.target);
				}
			});
		}, {
			root: null,
			rootMargin: deferMargin + 'px 0px',
			threshold: 0.01
		});

		$sections.each(function() {
			io.observe(this);
		});
	}

	if (chunkCount && chunkCount > 1) {
		lazyDivs();
		setupLoadAllOnInteraction();
		setupLoadAllOnIdle();
	}

	initDeferSections();
});

/*
 * IntersectionObserver polyfill (trimmed) for compatibility.
 */
function dlck_lazy_IO(_win) {
	'use strict';

	if ('IntersectionObserver' in _win) {
		return _win.IntersectionObserver;
	}

	var viewportH = _win.innerHeight;
	var nowOffset = Date.now();

	var constructError = "Failed to construct 'Intersection': ";
	var observeError = "Failed to execute 'observe': ";
	var unobserveError = "Failed to execute 'unobserve': ";

	var rAF = _win.requestAnimationFrame;

	var now = function() {
		return _win.performance && _win.performance.now ? performance.now() : Date.now() - nowOffset;
	};

	var IntersectionObserver = function(callback, options) {
		if (typeof callback !== 'function') {
			throw new TypeError(constructError + 'The callback provided as parameter 1 is not a function');
		}

		this.root = options.root || null;
		this.threshold = options.threshold || [0];
		this._callback = callback;
		this._observationTargets = [];
		this._queuedEntries = [];
	};

	IntersectionObserver.prototype.observe = function(target) {
		if (!target) {
			throw new TypeError(observeError + 'parameter 1 is not of type "Element".');
		}

		if (this._observationTargets.indexOf(target) === -1) {
			this._observationTargets.push(target);
			this._monitorIntersections();
		}
	};

	IntersectionObserver.prototype.unobserve = function(target) {
		var index = this._observationTargets.indexOf(target);
		if (index === -1) {
			throw new TypeError(unobserveError + 'parameter 1 is not of type "Element".');
		}

		this._observationTargets.splice(index, 1);
	};

	IntersectionObserver.prototype._monitorIntersections = function() {
		var self = this;
		var check = function() {
			self._checkForIntersections();
			if (self._observationTargets.length) {
				rAF(check);
			}
		};
		rAF(check);
	};

	IntersectionObserver.prototype._checkForIntersections = function() {
		var self = this;
		self._queuedEntries = [];

		self._observationTargets.forEach(function(target) {
			var boundingClientRect = target.getBoundingClientRect();
			var intersectionRect = {
				top: Math.max(0, boundingClientRect.top),
				bottom: Math.min(viewportH, boundingClientRect.bottom)
			};

			var isIntersecting = intersectionRect.bottom > intersectionRect.top;
			self._queuedEntries.push({ target: target, isIntersecting: isIntersecting });
		});

		if (self._queuedEntries.length) {
			self._callback(self._queuedEntries, self);
		}
	};

	return IntersectionObserver;
}
