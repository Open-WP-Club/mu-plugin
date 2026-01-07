<?php

/**
 * A utility plugin to prevent user enumeration attacks
 *
 * Plugin name:       Disable User Enumeration
 * Plugin URI:        https://openwpclub.com
 * Description:       Prevents user enumeration via author archives and REST API to protect usernames from discovery.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-user-enumeration
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Block user enumeration via ?author=N queries
 */
add_action(
  'template_redirect',
  static function () {
    // Check if this is an author query
    if (is_author() || isset($_GET['author'])) {
      // Allow if user is logged in and can edit posts
      if (is_user_logged_in() && current_user_can('edit_posts')) {
        return;
      }

      // Log the enumeration attempt
      if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        error_log("User enumeration attempt blocked - IP: {$ip}, User Agent: {$user_agent}");
      }

      // Redirect to homepage
      wp_redirect(home_url('/'), 301);
      exit;
    }
  },
  1,
  0
);

/**
 * Remove author links from REST API
 */
add_filter(
  'rest_endpoints',
  static function ($endpoints) {
    // Remove user endpoints
    if (isset($endpoints['/wp/v2/users'])) {
      unset($endpoints['/wp/v2/users']);
    }
    if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
      unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }
    if (isset($endpoints['/wp/v2/users/me'])) {
      unset($endpoints['/wp/v2/users/me']);
    }

    return $endpoints;
  },
  10,
  1
);

/**
 * Remove author information from REST API post responses
 */
add_filter(
  'rest_prepare_post',
  static function ($response) {
    // Remove author field from response
    if (isset($response->data['author'])) {
      unset($response->data['author']);
    }

    return $response;
  },
  10,
  1
);

/**
 * Remove author information from REST API page responses
 */
add_filter(
  'rest_prepare_page',
  static function ($response) {
    // Remove author field from response
    if (isset($response->data['author'])) {
      unset($response->data['author']);
    }

    return $response;
  },
  10,
  1
);

/**
 * Block oEmbed discovery to prevent author URL leaking
 */
add_filter(
  'oembed_response_data',
  static function ($data) {
    // Remove author information from oEmbed
    if (isset($data['author_name'])) {
      unset($data['author_name']);
    }
    if (isset($data['author_url'])) {
      unset($data['author_url']);
    }

    return $data;
  },
  10,
  1
);

/**
 * Remove author from sitemap
 */
add_filter(
  'wp_sitemaps_add_provider',
  static function ($provider, $name) {
    // Remove users from sitemap
    if ($name === 'users') {
      return false;
    }
    return $provider;
  },
  10,
  2
);

/**
 * Block direct access to author feed URLs
 */
add_action(
  'template_redirect',
  static function () {
    if (is_author() && is_feed()) {
      // Log the attempt
      if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        error_log("Author feed enumeration attempt blocked - IP: {$ip}");
      }

      wp_redirect(home_url('/'), 301);
      exit;
    }
  },
  1,
  0
);

/**
 * Modify author_link to prevent username leaking in themes
 */
add_filter(
  'author_link',
  static function ($link) {
    // Return homepage instead of author archive
    return home_url('/');
  },
  10,
  1
);

/**
 * Remove author pages from search engine indexing
 */
add_action(
  'wp_head',
  static function () {
    if (is_author()) {
      echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
    }
  },
  1,
  0
);

/**
 * Block login enumeration via XML-RPC
 */
add_filter(
  'xmlrpc_methods',
  static function ($methods) {
    // Remove methods that can be used for user enumeration
    unset($methods['wp.getUsersBlogs']);
    unset($methods['wp.getAuthors']);
    unset($methods['wp.getUsers']);

    return $methods;
  },
  10,
  1
);

/**
 * Prevent username hints in login error messages
 */
add_filter(
  'login_errors',
  static function ($error) {
    // Generic error message that doesn't reveal if username exists
    if (strpos($error, 'incorrect') !== false || strpos($error, 'Invalid') !== false) {
      return '<strong>ERROR</strong>: Invalid username or password.';
    }
    return $error;
  },
  10,
  1
);

/**
 * Add admin notice explaining user enumeration protection
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
    if (get_user_meta(get_current_user_id(), 'user_enum_notice_dismissed', true)) {
      return;
    }

    echo '<div class="notice notice-info is-dismissible" id="user-enum-notice">
      <p>
        <strong>User Enumeration Protection Active:</strong> Author archives and REST API user endpoints are blocked to prevent username discovery. 
        Logged-in editors can still access author pages.
      </p>
    </div>';

    echo '<script>
      document.addEventListener("DOMContentLoaded", function() {
        const notice = document.getElementById("user-enum-notice");
        if (notice) {
          notice.addEventListener("click", function(e) {
            if (e.target.classList.contains("notice-dismiss")) {
              fetch(ajaxurl, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "action=dismiss_user_enum_notice&nonce=' . wp_create_nonce('user_enum_nonce') . '"
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
  'wp_ajax_dismiss_user_enum_notice',
  static function () {
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'user_enum_nonce')) {
      wp_die('Security check failed');
    }

    update_user_meta(get_current_user_id(), 'user_enum_notice_dismissed', true);
    wp_die();
  },
  10,
  0
);
