<?php
/**
 * @package Fix Divi Flashing Unstyled Content On Page Load / https://www.markhendriksen.com/how-to-fix-divi-flashing-unstyled-content-on-page-load/
 * @version 1.1
 */

add_action( 'dlck_collect_inline_assets_front_head', 'dlck_fix_divi_flashing_header_script' );

/**
 * Add a tiny head-safe script to avoid unstyled content flashes.
 */
function dlck_fix_divi_flashing_header_script() {
	// Skip in admin unless we're collecting for the cache.
if ( is_admin() && ! dlck_is_inline_collecting() ) {
	return;
}
	if ( wp_doing_ajax() ) {
		return;
	}

	$script = <<<'JS'
var dlckHtml=document.documentElement;
if(dlckHtml){dlckHtml.style.display="none";document.addEventListener("DOMContentLoaded",function(){dlckHtml.style.display="block";});}
JS;

	dlck_add_inline_js( $script, 'front_head' );
}

?>
