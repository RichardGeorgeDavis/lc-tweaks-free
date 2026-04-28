<?php
/**
 * @package Antispam Email Shortcode
 * @version 1.0
 * [email]name@website.com[/email]
 */

if (!shortcode_exists('email')) {
    function wpcodex_hide_email_shortcode($atts, $content = null)
      {
          if (!is_email($content)) {
              return;
          }
          return '<a href="mailto:' . antispambot($content) . '">' . antispambot($content) . '</a>';
      }
      add_shortcode('email', 'wpcodex_hide_email_shortcode');
    }
?>
