<?php
/**
 * @package Disable RSS Feed
 * @version 1.0
 */

 function dlck_disable_rss_feed() {
 wp_die( __('Sorry, we do not use RSS!') );
 }
 add_action('do_feed', 'dlck_disable_rss_feed', 1);
 add_action('do_feed_rdf', 'dlck_disable_rss_feed', 1);
 add_action('do_feed_rss', 'dlck_disable_rss_feed', 1);
 add_action('do_feed_rss2', 'dlck_disable_rss_feed', 1);
 add_action('do_feed_atom', 'dlck_disable_rss_feed', 1);

?>
