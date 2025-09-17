<?php
defined('ABSPATH') or die();

/**
 * A utility plugin to disable WP version
 *
 * Plugin name:       Disable WP Version
 * Plugin URI:        https://openwpclub.com
 * Description:       Remove WordPress version
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0.
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-wp-version
 */

remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');
