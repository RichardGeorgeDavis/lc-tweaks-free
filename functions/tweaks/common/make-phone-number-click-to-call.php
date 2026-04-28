<?php
/**
 * @package Make Phone Number Click To Call
 * @version 1.2
 */

add_action('wp_footer', 'dlck_make_phone_number_click_to_call');
add_action('dlck_collect_inline_assets_front', 'dlck_make_phone_number_click_to_call');
function dlck_make_phone_number_click_to_call()
{
    $collecting = dlck_is_inline_collecting();
    if ( ! $collecting && is_admin() ) {
        return;
    }
    $js = <<<'JS'
jQuery(document).ready(function($) {
  var $phone = $('#et-info-phone');
  if (!$phone.length) {
    return;
  }
  var unformatted = $.trim($phone.text());
  if (!unformatted) {
    return;
  }
  var formatted = unformatted.replace(/-|\s/g, "");
  $phone.wrapInner("<a href='tel:" + formatted + "'></a>");
});
JS;
    dlck_add_inline_js( $js );

}
