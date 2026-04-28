<?php
/**
 * @package Hide Divi Cloud
 * @version 1.0
 */

add_action( 'dlck_collect_inline_assets_admin', 'dlck_hide_divi_cloud_css' );
add_action( 'dlck_collect_inline_assets_front', 'dlck_hide_divi_cloud_css' );

/**
 * Hide Divi Cloud UI elements in the builder.
 */
function dlck_hide_divi_cloud_css() {
	$collecting = dlck_is_inline_collecting();
	$is_builder = function_exists( 'dlck_is_divi_visual_builder_request' )
		? dlck_is_divi_visual_builder_request()
		: ( isset( $_GET['et_fb'] ) && wp_unslash( $_GET['et_fb'] ) === '1' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( ! $collecting && ! is_admin() && ! $is_builder ) {
		return;
	}

	$context = false !== strpos( current_filter(), '_admin' ) ? 'admin' : 'front';
	$css = <<<CSS
/* === Hide Divi Cloud === */

/* Remove "Sign In To Divi Cloud" button on Load from Library modal */
.et-cloud-toggle {
    display: none !important;
}

.et-cloud-app-sort-menu {
    margin-right: 0 !important;
}

/* Remove Divi Cloud upsells */
.et-cloud-app__upsell {
    display: none !important;
}

/* Hide Divi Cloud in "Add To Module Library" modal */
.et_fb_save_module_modal .et-fb-settings-option:has(+ .et-cloud-app__upsell) {
    display: none !important;
}

/* Hide Divi Cloud in "Save Theme Builder Template */
.et-tb-library-save-option:has(+ .et-cloud-app__upsell) {
    display: none !important;
}

/* Hide Divi Cloud in "Save HTML/JS Snippet" modal */
.et-save-to-library-modal .et-save-to-library-option:has(+ .et-cloud-app__upsell) {
    display: none !important;
}

/* Remove Divi Cloud from module right-click menu */
.et-fb-right-click-menu__item--saveCloud {
    display: none !important;
}

/* === Divi 5 Builder === */
/* Hide "Save to Divi Cloud" in right-click menu */
.et-vb-right-click-option[value="save-to-cloud"],
/* Save to Divi Cloud option in Save to Module Library modal */
.et-vb-field:has(+ .et-vb-field-upsell-card),
/* Upsell in Save to Module Library Modal */
.et-vb-field-upsell-card,
/* Cloud icon in Add From Library item hover icons */
.et-common-icon--cloud {
    display: none !important;
}

/* === End: Hide Divi Cloud === */
CSS;

	dlck_add_inline_css( $css, $context );
}
