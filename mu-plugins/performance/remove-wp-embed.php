<?php

/**
 * A utility plugin to remove WordPress embed script
 *
 * Plugin name:       Remove WP Embed
 * Plugin URI:        https://openwpclub.com
 * Description:       Remove WordPress embed script from frontend to improve performance.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       remove-wp-embed
 */

// Prevent direct access
defined('ABSPATH') or die();

function mu_remove_wp_embed()
{
  if (!is_admin()) {
    wp_deregister_script('wp-embed.min.js');
    wp_deregister_script('wp-embed');
    wp_deregister_script('embed');
  }
}

add_action('init', 'mu_remove_wp_embed', PHP_INT_MAX);
