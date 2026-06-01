<?php

/**
 * Clean WordPress <head> output
 *
 * Plugin name:       Clean Head Output
 * Plugin URI:        https://openwpclub.com
 * Description:       Removes unnecessary tags from the frontend <head>: wlwmanifest, RSD, shortlink, adjacent-posts links, generator meta, REST API link, and feed links.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       clean-head-output
 */

defined('ABSPATH') or die();

// Windows Live Writer manifest link
remove_action('wp_head', 'wlwmanifest_link');

// Really Simple Discovery link
remove_action('wp_head', 'rsd_link');

// WordPress-generated shortlink (<link rel="shortlink">)
remove_action('wp_head', 'wp_shortlink_wp_head', 10);

// Prev/next post links (<link rel="prev"> / <link rel="next">)
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);

// <meta name="generator" content="WordPress x.x.x">
remove_action('wp_head', 'wp_generator');

// REST API link (<link rel="https://api.w.org/">)
// Note: also removes REST API discoverability for unauthenticated clients.
remove_action('wp_head', 'rest_output_link_wp_head', 10);

// Feed links — redundant if disable-rss-feeds.php is also active
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);

// Remove REST API link from HTTP headers
remove_action('template_redirect', 'rest_output_link_header', 11);
