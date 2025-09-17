<?php

/**
 * A utility plugin to disable emoji support
 *
 * Plugin name:       Disable Emoji Support
 * Plugin URI:        https://openwpclub.com
 * Description:       A utility plugin to disable emoji support.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0.
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-emoji-support
 */

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_styles', 'print_emoji_styles');

function mu_remove_dns_prefetch($hints, $relation_type)
{
  if ('dns-prefetch' === $relation_type) {
    return array_diff(wp_dependencies_unique_hosts(), $hints);
  }

  return $hints;
}
add_filter('wp_resource_hints', 'mu_remove_dns_prefetch', 10, 2);
