<?php

/**
 * A utility plugin to remove jQuery Migrate
 *
 * Plugin name:       Remove jQuery Migrate
 * Plugin URI:        https://openwpclub.com
 * Description:       Removes jQuery Migrate script from WordPress to reduce page weight. Only use if your theme and plugins don't require legacy jQuery support.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       remove-jquery-migrate
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Remove jQuery Migrate from frontend
 */
add_action(
  'wp_default_scripts',
  static function ($scripts) {
    if (!is_admin() && isset($scripts->registered['jquery'])) {
      $script = $scripts->registered['jquery'];

      // Check if jquery-core is set as dependency
      if ($script->deps) {
        // Remove jquery-migrate from dependencies
        $script->deps = array_diff($script->deps, ['jquery-migrate']);
      }
    }
  },
  10,
  1
);

/**
 * Dequeue and deregister jQuery Migrate
 */
add_action(
  'wp_enqueue_scripts',
  static function () {
    if (!is_admin()) {
      wp_dequeue_script('jquery-migrate');
      wp_deregister_script('jquery-migrate');
    }
  },
  PHP_INT_MAX,
  0
);

/**
 * Add admin notice about jQuery Migrate removal
 */
add_action(
  'admin_notices',
  static function () {
    // Only show on dashboard
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'dashboard') {
      return;
    }

    // Check if user has dismissed the notice
    if (get_user_meta(get_current_user_id(), 'jquery_migrate_removed_notice_dismissed', true)) {
      return;
    }

    echo '<div class="notice notice-info is-dismissible" id="jquery-migrate-removed-notice">
      <p>
        <strong>jQuery Migrate Removed:</strong> The jQuery Migrate script has been removed from your site. 
        If you notice JavaScript errors or broken functionality, this plugin may be the cause.
      </p>
    </div>';

    echo '<script>
      document.addEventListener("DOMContentLoaded", function() {
        const notice = document.getElementById("jquery-migrate-removed-notice");
        if (notice) {
          notice.addEventListener("click", function(e) {
            if (e.target.classList.contains("notice-dismiss")) {
              fetch(ajaxurl, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "action=dismiss_jquery_migrate_removed_notice&nonce=' . wp_create_nonce('jquery_migrate_removed_nonce') . '"
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
  'wp_ajax_dismiss_jquery_migrate_removed_notice',
  static function () {
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'jquery_migrate_removed_nonce')) {
      wp_die('Security check failed');
    }

    update_user_meta(get_current_user_id(), 'jquery_migrate_removed_notice_dismissed', true);
    wp_die();
  },
  10,
  0
);
