<?php
/**
 * @package Fix Divi user-scalable=”no” problem https://divilover.com/how-to-fix-the-user-scalableno-accessibility-error-in-divi-one-simple-code-snippet/
 * @version 1.0
 */

if ( ! function_exists( 'dlck_fix_divi_zooming_should_override_viewport' ) ) :
	/**
	 * Skip override inside Visual Builder so Divi controls its own viewport.
	 */
	function dlck_fix_divi_zooming_should_override_viewport(): bool {
		return ! ( function_exists( 'dlck_is_divi_visual_builder_request' ) && dlck_is_divi_visual_builder_request() );
	}
endif;

if ( ! function_exists( 'dlck_fix_divi_zooming_remove_default_viewport_meta' ) ) :
	/**
	 * Remove Divi's default viewport meta tag when this tweak is active.
	 */
	function dlck_fix_divi_zooming_remove_default_viewport_meta(): void {
		if ( ! dlck_fix_divi_zooming_should_override_viewport() ) {
			return;
		}

		remove_action( 'wp_head', 'et_add_viewport_meta' );
	}

	add_action( 'wp_loaded', 'dlck_fix_divi_zooming_remove_default_viewport_meta' );
endif;

if ( ! function_exists( 'dlck_fix_divi_zooming_output_accessible_viewport_meta' ) ) :
	/**
	 * Output a zoom-friendly viewport meta tag.
	 */
	function dlck_fix_divi_zooming_output_accessible_viewport_meta(): void {
		static $did_output = false;

		if ( $did_output || ! dlck_fix_divi_zooming_should_override_viewport() ) {
			return;
		}

		$did_output = true;
		echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=1" />';
	}

	add_action( 'wp_head', 'dlck_fix_divi_zooming_output_accessible_viewport_meta', 1 );
endif;
