<?php
/**
* @package Hide related video suggestions at the end of a YouTube embed by overlaying a thumbnail over the end-screen UI.
 * @version 1.1
 */

if ( defined( 'DLCK_YT_RELATED_HANDLER_LOADED' ) ) {
	return;
}
define( 'DLCK_YT_RELATED_HANDLER_LOADED', true );

add_action( 'wp_footer', function () {

	$collecting = dlck_is_inline_collecting();
	$js = <<<JS
document.addEventListener('DOMContentLoaded', function () {

	const iframes = document.querySelectorAll('iframe[src*="youtube.com"]');

	iframes.forEach(function (iframe) {

		// Force modest branding + allow JS API
		let src = iframe.getAttribute('src');
		if (!src.includes('enablejsapi')) {
			src += (src.includes('?') ? '&' : '?') + 'enablejsapi=1&modestbranding=1&rel=0';
			iframe.setAttribute('src', src);
		}

		// Build overlay thumbnail
		const parent = iframe.parentNode;
		const overlay = document.createElement('div');
		overlay.className = 'yt-end-overlay';
		overlay.style.cssText = `
			position:absolute;
			top:0;left:0;width:100%;height:100%;
			background:#000;
			display:none;
			z-index:5;
		`;
		parent.style.position = 'relative';
		parent.appendChild(overlay);

		// Use the YouTube IFrame API to detect end of playback
		new YT.Player(iframe, {
			events: {
				'onStateChange': function (event) {
					if (event.data === YT.PlayerState.ENDED) {
						overlay.style.display = 'block';
					}
				}
			}
		});
	});

});
JS;
	dlck_add_inline_js( $js );
}, 50);
add_action( 'dlck_collect_inline_assets_front', function () {

	$js = <<<JS
document.addEventListener('DOMContentLoaded', function () {

	const iframes = document.querySelectorAll('iframe[src*="youtube.com"]');

	iframes.forEach(function (iframe) {

		// Force modest branding + allow JS API
		let src = iframe.getAttribute('src');
		if (!src.includes('enablejsapi')) {
			src += (src.includes('?') ? '&' : '?') + 'enablejsapi=1&modestbranding=1&rel=0';
			iframe.setAttribute('src', src);
		}

		// Build overlay thumbnail
		const parent = iframe.parentNode;
		const overlay = document.createElement('div');
		overlay.className = 'yt-end-overlay';
		overlay.style.cssText = `
			position:absolute;
			top:0;left:0;width:100%;height:100%;
			background:#000;
			display:none;
			z-index:5;
		`;
		parent.style.position = 'relative';
		parent.appendChild(overlay);

		// Use the YouTube IFrame API to detect end of playback
		new YT.Player(iframe, {
			events: {
				'onStateChange': function (event) {
					if (event.data === YT.PlayerState.ENDED) {
						overlay.style.display = 'block';
					}
				}
			}
		});
	});

});
JS;
	dlck_add_inline_js( $js );
}, 50 );
?>
