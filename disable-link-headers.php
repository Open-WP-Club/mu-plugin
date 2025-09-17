<?php

/**
 * A utility plugin to disable Unnecessary Link Headers
 *
 * Plugin name: Disable Unnecessary Link Headers
 * Plugin URI: https://openwpclub.com
 * Description: A utility plugin to disable Unnecessary Link Headers.
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Version: 1.0.0.
 * Author: OpenWP Club
 * License: Apache-2.0
 * Text Domain: disable-unnecessary-link-headers
 */

remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
remove_action('wp_head', 'wp_oembed_add_discovery_links');
