<?php
/**
 * @package Show Custom Maintenance, Coming Soon Or Notice
 * @version 1.0
 */
// Ensure helper exists to render a Divi Library layout.
if ( ! function_exists( 'dlck_display_divi_section' ) ) {
	/**
	 * Render a Divi layout/section by post ID.
	 *
	 * @param int $layout_id Divi Library post ID.
	 * @return void
	 */
	function dlck_display_divi_section( $layout_id ) {
		$layout_id = absint( $layout_id );
		if ( ! $layout_id ) {
			return;
		}

		$content = get_post_field( 'post_content', $layout_id );
		if ( ! $content ) {
			return;
		}

		echo apply_filters( 'the_content', $content );
	}
}

// Only run when explicitly activated by the calling code.
if ( ! defined( 'DLCK_MAINTENANCE_LAYOUT_ACTIVE' ) || ! DLCK_MAINTENANCE_LAYOUT_ACTIVE ) {
	return;
}

// Using PHP's header function to send a 503 Service Temporarily Unavailable status code to the client.
header( $_SERVER['SERVER_PROTOCOL'] . ' 503 Service Temporarily Unavailable', true, 503 );
$retryAfterSeconds = 240;
header( 'Retry-After: ' . $retryAfterSeconds );

add_action('wp_footer', 'dlck_maintenance_override_footer');
function dlck_maintenance_override_footer()
{
    ?>
  <style type="text/css">
  #main-header,#top-header, #main-footer {display:none !important;}
#page-container {padding-top:0!important;}
  </style>
  <?php
}
get_header();
dlck_display_divi_section($dlck_maintenance_layout_val);
get_footer();
exit;

//Kill the PHP script.
exit;

?>
