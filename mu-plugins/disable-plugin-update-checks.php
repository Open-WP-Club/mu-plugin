<?php

/**
 * A utility plugin to disable automatic plugin update checks
 *
 * Plugin name:       Disable Plugin Update Checks
 * Plugin URI:        https://openwpclub.com
 * Description:       Disables automatic plugin update checks to reduce server load. Useful for managed/frozen environments where updates are controlled manually.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-plugin-update-checks
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Disable plugin update checks
 */
add_filter('auto_update_plugin', '__return_false', PHP_INT_MAX);

/**
 * Remove plugin update check from wp-cron
 */
remove_action('load-update-core.php', 'wp_update_plugins');
add_filter('pre_site_transient_update_plugins', '__return_null', PHP_INT_MAX);

/**
 * Disable manual plugin update checks
 */
add_filter(
  'wp_update_plugins',
  '__return_false',
  PHP_INT_MAX,
  0
);

/**
 * Clear existing plugin update data
 */
add_action(
  'admin_init',
  static function () {
    delete_site_transient('update_plugins');
  },
  1,
  0
);

/**
 * Hide plugin update notifications in admin
 */
add_action(
  'admin_init',
  static function () {
    remove_action('admin_notices', 'update_nag', 3);
    remove_action('network_admin_notices', 'update_nag', 3);
  },
  10,
  0
);

/**
 * Remove plugin updates from admin menu count
 */
add_filter(
  'wp_get_update_data',
  static function ($update_data) {
    $update_data['counts']['plugins'] = 0;
    $update_data['counts']['total'] = $update_data['counts']['total'] - $update_data['counts']['plugins'];
    return $update_data;
  },
  PHP_INT_MAX,
  1
);

/**
 * Add admin notice explaining update checks are disabled
 */
add_action(
  'admin_notices',
  static function () {
    // Only show on plugins page
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'plugins') {
      return;
    }

    // Check if user has dismissed the notice
    if (get_user_meta(get_current_user_id(), 'plugin_updates_disabled_notice_dismissed', true)) {
      return;
    }

    echo '<div class="notice notice-warning is-dismissible" id="plugin-updates-disabled-notice">
      <p>
        <strong>Plugin Update Checks Disabled:</strong> Automatic plugin update checks are turned off. 
        You must manually check for and apply updates when needed.
      </p>
    </div>';

    echo '<script>
      document.addEventListener("DOMContentLoaded", function() {
        const notice = document.getElementById("plugin-updates-disabled-notice");
        if (notice) {
          notice.addEventListener("click", function(e) {
            if (e.target.classList.contains("notice-dismiss")) {
              fetch(ajaxurl, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "action=dismiss_plugin_updates_disabled_notice&nonce=' . wp_create_nonce('plugin_updates_disabled_nonce') . '"
              });
            }
          });
        }
      });
    </script>';
  },
  10,
  0
);

/**
 * Handle notice dismissal
 */
add_action(
  'wp_ajax_dismiss_plugin_updates_disabled_notice',
  static function () {
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'plugin_updates_disabled_nonce')) {
      wp_die('Security check failed');
    }

    update_user_meta(get_current_user_id(), 'plugin_updates_disabled_notice_dismissed', true);
    wp_die();
  },
  10,
  0
);
