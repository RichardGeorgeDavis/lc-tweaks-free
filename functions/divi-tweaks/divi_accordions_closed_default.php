<?php
/**
 * @package Make Divi Accordions Closed by Default (jQuery-free)
 * @version 3.1
 */

add_action( 'wp_footer', function () {
	$js = <<<JS
document.addEventListener('DOMContentLoaded', function () {
    var toggles = document.querySelectorAll('.et_pb_accordion .et_pb_toggle_open');
    toggles.forEach(function (toggle) {
        toggle.classList.remove('et_pb_toggle_open');
        toggle.classList.add('et_pb_toggle_close');
    });
});
JS;
	dlck_add_inline_js( $js );
}, 20);
add_action( 'dlck_collect_inline_assets_front', function () {
	$js = <<<JS
document.addEventListener('DOMContentLoaded', function () {
    var toggles = document.querySelectorAll('.et_pb_accordion .et_pb_toggle_open');
    toggles.forEach(function (toggle) {
        toggle.classList.remove('et_pb_toggle_open');
        toggle.classList.add('et_pb_toggle_close');
    });
});
JS;
	dlck_add_inline_js( $js );
}, 20 );
?>
