<?php
/**
* @package Hide The Gutenberg And Standard Editor Buttons In Divi
 * @version 1.1
* https://www.peeayecreative.com/how-to-hide-the-gutenberg-and-standard-editor-buttons-in-divi/
*/

add_action('admin_head', 'dlck_hide_gutenberg_standard_editor_css');
add_action('dlck_collect_inline_assets_admin', 'dlck_hide_gutenberg_standard_editor_css');

function dlck_hide_gutenberg_standard_editor_css() {
	$css = <<<CSS
/*-- [hide buttons when the Enable Classic Editor toggle is ENABLED] --*/
  /*hide the Return To Standard Editor button*/
  .et-db #et-boc .et-l #et_pb_toggle_builder.et_pb_builder_is_used {
    display: none;
  }
  /*adjust button left margin*/
  .et-db #et-boc .et-l #et_pb_fb_cta {
    margin-left: 0;
  }
/*-- [hide buttons when Enable Classic Editor toggle is DISABLED] --*/
  /*hide the Return To Default Editor button*/
  .block-editor__container .editor-post-switch-to-gutenberg.components-button.is-default {
    display: none;
  }
  /*hide the Use Default Editor button*/
  .block-editor__container #et-switch-to-gutenberg,
  .block-editor__container #et-switch-to-gutenberg.components-button.is-default {
    display: none;
  }
CSS;
	dlck_add_inline_css( $css, 'admin' );
}

?>
