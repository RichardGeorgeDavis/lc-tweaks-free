<?php
/**
 * @package Divi Builder Quick Fixes
 * @version 1.1
 */

 function dlck_divi_builder_quick_fixes() {
	$css = <<<CSS
/*Disable The Divi Inline Text Style Editor -hide Divi Builder text style popover*/
.et-db #et-boc .et-fb-popover--inverse.et-fb-popover--arrow {
    display: none;
}

/*Make The Text Style Options Sticky Within The Divi Builder Module Text Areas - make Divi Builder text style settings sticky on scroll*/
.mce-panel .mce-stack-layout-item.mce-first {
    position: sticky !important;
    top: -60px;
}

.et-fb-modal--expanded .mce-panel .mce-stack-layout-item.mce-first {
    top: -24px !important;
}

/*Increase The Default Height Of The Text (HTML) Tab In The Divi Builder*/
.et-db #et-boc .et-l .et-fb-option--tiny-mce .et-fb-tinymce-html-input {
    height: 400px;
}

/*increase the height of the Divi inner field settings modal*/
.et-fb-field-settings-modal {
    max-height: 500px !important;
}

/*increase the height of the Divi icon picker area*/
.et-db #et-boc .et-l .et-fb-font-icon-list {
    min-height: 400px !important;
}
CSS;
	dlck_add_inline_css( $css, 'admin' );
 }
add_action('admin_head', 'dlck_divi_builder_quick_fixes');
add_action('dlck_collect_inline_assets_admin', 'dlck_divi_builder_quick_fixes');
