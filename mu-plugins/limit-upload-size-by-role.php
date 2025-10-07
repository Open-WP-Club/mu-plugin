<?php

/**
 * A utility plugin to limit file upload sizes based on user role
 *
 * Plugin name:       Limit Upload Size by Role
 * Plugin URI:        https://openwpclub.com
 * Description:       Sets different maximum upload file size limits for different user roles to prevent large uploads from non-admin users.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       limit-upload-size-by-role
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Get upload size limit for current user's role (in MB)
 * 
 * Default limits:
 * - Administrator: Unlimited (uses server/PHP limit)
 * - Editor: 50 MB
 * - Author: 25 MB
 * - Contributor: 10 MB
 * - Subscriber: 5 MB
 * 
 * @return int|false Size limit in MB, or false for unlimited
 */
function mu_get_upload_limit_for_user()
{
  $user = wp_get_current_user();

  if (!$user->ID) {
    return 5; // Default for non-logged-in users
  }

  // Get role-based limits (filterable)
  $role_limits = apply_filters('upload_size_limit_by_role', [
    'administrator' => false, // Unlimited
    'editor'        => 50,    // 50 MB
    'author'        => 25,    // 25 MB
    'contributor'   => 10,    // 10 MB
    'subscriber'    => 5,     // 5 MB
  ]);

  // Check user's role and return limit
  // If user has multiple roles, use the highest limit
  $user_limit = 5; // Default fallback
  $has_unlimited = false;

  foreach ($user->roles as $role) {
    if (isset($role_limits[$role])) {
      // False means unlimited
      if ($role_limits[$role] === false) {
        $has_unlimited = true;
        break;
      }

      // Get highest limit if user has multiple roles
      if ($role_limits[$role] > $user_limit) {
        $user_limit = $role_limits[$role];
      }
    }
  }

  return $has_unlimited ? false : $user_limit;
}

/**
 * Filter upload size limit based on user role
 */
add_filter(
  'upload_size_limit',
  static function ($size) {
    $user_limit = mu_get_upload_limit_for_user();

    // If unlimited (false), return original size
    if ($user_limit === false) {
      return $size;
    }

    // Convert MB to bytes
    $user_limit_bytes = $user_limit * 1024 * 1024;

    // Return the smaller of the two limits
    return min($size, $user_limit_bytes);
  },
  10,
  1
);

/**
 * Check file size before upload and block if exceeds limit
 */
add_filter(
  'wp_handle_upload_prefilter',
  static function ($file) {
    $user_limit = mu_get_upload_limit_for_user();

    // If unlimited, allow upload
    if ($user_limit === false) {
      return $file;
    }

    // Get file size
    $file_size = isset($file['size']) ? $file['size'] : 0;
    $user_limit_bytes = $user_limit * 1024 * 1024;

    // Check if file exceeds user's limit
    if ($file_size > $user_limit_bytes) {
      $file['error'] = sprintf(
        'File size exceeds your upload limit. Your maximum file size is %s MB. This file is %s MB.',
        number_format($user_limit, 1),
        number_format($file_size / 1024 / 1024, 2)
      );
    }

    return $file;
  },
  10,
  1
);

/**
 * Modify plupload settings to show correct size limit to user
 */
add_filter(
  'plupload_default_settings',
  static function ($settings) {
    $user_limit = mu_get_upload_limit_for_user();

    // If unlimited, use default
    if ($user_limit === false) {
      return $settings;
    }

    $user_limit_bytes = $user_limit * 1024 * 1024;

    // Set max file size for plupload
    $settings['max_file_size'] = $user_limit_bytes . 'b';

    return $settings;
  },
  10,
  1
);

/**
 * Show upload limit info in media uploader
 */
add_action(
  'post-plupload-upload-ui',
  static function () {
    $user_limit = mu_get_upload_limit_for_user();

    if ($user_limit === false) {
      echo '<p class="upload-size-limit-info">';
      echo '<strong>Upload limit:</strong> Unlimited (server limit applies)';
      echo '</p>';
    } else {
      echo '<p class="upload-size-limit-info">';
      echo '<strong>Your upload limit:</strong> ' . esc_html($user_limit) . ' MB per file';
      echo '</p>';
    }
  },
  10,
  0
);

add_action(
  'admin_head',
  static function () {
    $screen = get_current_screen();
    if ($screen && in_array($screen->base, ['upload', 'post', 'media-upload-popup'], true)) {
?>
    <style type="text/css">
      .upload-size-limit-info {
        margin: 10px 0;
        padding: 8px 12px;
        background: #f0f6fc;
        border-left: 4px solid #2271b1;
        font-size: 13px;
      }
    </style>
<?php
    }
  },
  10,
  0
);

/**
 * Add column to media library showing file sizes
 */
add_filter(
  'manage_media_columns',
  static function ($columns) {
    $columns['file_size'] = 'File Size';
    return $columns;
  },
  10,
  1
);

add_action(
  'manage_media_custom_column',
  static function ($column_name, $post_id) {
    if ($column_name === 'file_size') {
      $file_path = get_attached_file($post_id);

      if ($file_path && file_exists($file_path)) {
        $file_size = filesize($file_path);
        echo esc_html(size_format($file_size, 2));
      } else {
        echo '—';
      }
    }
  },
  10,
  2
);

/**
 * Add admin notice showing current user's upload limit
 */
add_action(
  'admin_notices',
  static function () {
    // Only show on media library or upload pages
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->base, ['upload', 'media'], true)) {
      return;
    }

    // Check if user has dismissed the notice
    if (get_user_meta(get_current_user_id(), 'upload_limit_notice_dismissed', true)) {
      return;
    }

    $user_limit = mu_get_upload_limit_for_user();
    $user = wp_get_current_user();
    $role_name = ucfirst($user->roles[0] ?? 'User');

    if ($user_limit === false) {
      $limit_text = 'unlimited (server limit applies)';
    } else {
      $limit_text = $user_limit . ' MB per file';
    }

    echo '<div class="notice notice-info is-dismissible" id="upload-limit-notice">
      <p>
        <strong>Upload Size Limits Active:</strong> As a ' . esc_html($role_name) . ', your maximum upload size is ' . esc_html($limit_text) . '.
      </p>
    </div>';

    echo '<script>
      document.addEventListener("DOMContentLoaded", function() {
        const notice = document.getElementById("upload-limit-notice");
        if (notice) {
          notice.addEventListener("click", function(e) {
            if (e.target.classList.contains("notice-dismiss")) {
              fetch(ajaxurl, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "action=dismiss_upload_limit_notice&nonce=' . wp_create_nonce('upload_limit_nonce') . '"
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
  'wp_ajax_dismiss_upload_limit_notice',
  static function () {
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'upload_limit_nonce')) {
      wp_die('Security check failed');
    }

    update_user_meta(get_current_user_id(), 'upload_limit_notice_dismissed', true);
    wp_die();
  },
  10,
  0
);
