<?php

/**
 * A utility plugin to remove WordPress branding from admin interface
 *
 * Plugin name:       Remove WordPress Branding
 * Plugin URI:        https://openwpclub.com
 * Description:       Removes WordPress logo, admin footer text, and blocks access to WordPress about pages for white-label admin experience.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       remove-wordpress-branding
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Remove WordPress logo from admin bar menu
 */
add_action(
  'add_admin_bar_menus',
  static function () {
    remove_action('admin_bar_menu', 'wp_admin_bar_wp_menu', 10);
  },
  10,
  0
);

/**
 * Block access to WordPress about pages
 */
array_map(
  static function ($page) {
    add_action(
      $page,
      static function () {
        wp_redirect(admin_url());
        exit;
      },
      0,
      0
    );
  },
  [
    'load-about.php',
    'load-credits.php',
    'load-freedoms.php',
    'load-privacy.php',
    'load-contribute.php',
  ]
);

/**
 * Remove admin footer text
 */
add_action(
  'admin_footer_text',
  '__return_empty_string',
  0,
  0
);
