<?php

/**
 * A utility plugin to disable comments globally
 *
 * Plugin name:       Disable Comments Globally
 * Plugin URI:        https://openwpclub.com
 * Description:       Completely disables comments, trackbacks, and pingbacks across the entire site. Removes all comment-related functionality from admin.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-comments-globally
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Disable comments on frontend
 */
add_filter('comments_open', '__return_false', PHP_INT_MAX);
add_filter('pings_open', '__return_false', PHP_INT_MAX);

/**
 * Hide existing comments
 */
add_filter('comments_array', '__return_empty_array', PHP_INT_MAX);

/**
 * Remove comment support from all post types
 */
add_action(
  'init',
  static function () {
    // Get all post types
    $post_types = get_post_types(['public' => true], 'names');

    foreach ($post_types as $post_type) {
      // Remove comment support
      if (post_type_supports($post_type, 'comments')) {
        remove_post_type_support($post_type, 'comments');
      }
      // Remove trackback support
      if (post_type_supports($post_type, 'trackbacks')) {
        remove_post_type_support($post_type, 'trackbacks');
      }
    }
  },
  PHP_INT_MAX,
  0
);

/**
 * Close comments on existing posts
 */
add_action(
  'admin_init',
  static function () {
    // Update default comment status
    update_option('default_comment_status', 'closed');
    update_option('default_ping_status', 'closed');
  },
  10,
  0
);

/**
 * Remove comments menu from admin
 */
add_action(
  'admin_menu',
  static function () {
    remove_menu_page('edit-comments.php');
    remove_submenu_page('options-general.php', 'options-discussion.php');
  },
  PHP_INT_MAX,
  0
);

/**
 * Remove comments from admin bar
 */
add_action(
  'wp_before_admin_bar_render',
  static function () {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
  },
  PHP_INT_MAX,
  0
);

/**
 * Remove comment-related meta boxes
 */
add_action(
  'admin_init',
  static function () {
    // Get all post types
    $post_types = get_post_types(['public' => true], 'names');

    foreach ($post_types as $post_type) {
      remove_meta_box('commentsdiv', $post_type, 'normal');
      remove_meta_box('commentstatusdiv', $post_type, 'normal');
      remove_meta_box('trackbacksdiv', $post_type, 'normal');
    }
  },
  PHP_INT_MAX,
  0
);

/**
 * Remove comments from dashboard widgets
 */
add_action(
  'wp_dashboard_setup',
  static function () {
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
  },
  PHP_INT_MAX,
  0
);

/**
 * Remove comment count from "At a Glance" dashboard widget
 */
add_filter(
  'dashboard_glance_items',
  static function ($items) {
    return array_filter($items, static function ($item) {
      return strpos($item, 'comment-count') === false;
    });
  },
  PHP_INT_MAX,
  1
);

/**
 * Redirect any comment-related admin pages
 */
add_action(
  'admin_init',
  static function () {
    global $pagenow;

    if ($pagenow === 'edit-comments.php' || $pagenow === 'options-discussion.php') {
      wp_safe_redirect(admin_url());
      exit;
    }
  },
  10,
  0
);

/**
 * Remove comment-related columns from post list tables
 */
add_filter(
  'manage_posts_columns',
  static function ($columns) {
    unset($columns['comments']);
    return $columns;
  },
  PHP_INT_MAX,
  1
);

add_filter(
  'manage_pages_columns',
  static function ($columns) {
    unset($columns['comments']);
    return $columns;
  },
  PHP_INT_MAX,
  1
);

/**
 * Remove comments links from admin bar
 */
add_action(
  'init',
  static function () {
    if (is_admin_bar_showing()) {
      remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
  },
  PHP_INT_MAX,
  0
);

/**
 * Hide comment-related quick edit options
 */
add_action(
  'admin_print_footer_scripts',
  static function () {
    $screen = get_current_screen();
    if ($screen && in_array($screen->base, ['edit', 'post'], true)) {
      echo '<style>
        .inline-edit-col .comment_status_wrapper,
        .inline-edit-col .ping_status_wrapper,
        #edit-slug-box .comment-status,
        .misc-pub-section.comment-status,
        .misc-pub-section.num-comments {
          display: none !important;
        }
      </style>';
    }
  },
  PHP_INT_MAX,
  0
);

/**
 * Remove comment RSS feeds
 */
add_filter(
  'feed_links_show_comments_feed',
  '__return_false',
  PHP_INT_MAX,
  0
);

/**
 * Disable comment feed
 */
add_action(
  'do_feed_rss2_comments',
  static function () {
    wp_die(
      esc_html__('Comments are disabled on this site.', 'disable-comments-globally'),
      '',
      ['response' => 403]
    );
  },
  1,
  0
);

add_action(
  'do_feed_atom_comments',
  static function () {
    wp_die(
      esc_html__('Comments are disabled on this site.', 'disable-comments-globally'),
      '',
      ['response' => 403]
    );
  },
  1,
  0
);

/**
 * Remove comment-related REST API endpoints
 */
add_filter(
  'rest_endpoints',
  static function ($endpoints) {
    if (isset($endpoints['/wp/v2/comments'])) {
      unset($endpoints['/wp/v2/comments']);
    }
    if (isset($endpoints['/wp/v2/comments/(?P<id>[\d]+)'])) {
      unset($endpoints['/wp/v2/comments/(?P<id>[\d]+)']);
    }
    return $endpoints;
  },
  PHP_INT_MAX,
  1
);

/**
 * Remove comment fields from REST API responses
 */
add_filter(
  'rest_prepare_post',
  static function ($response) {
    if (isset($response->data['comment_status'])) {
      unset($response->data['comment_status']);
    }
    if (isset($response->data['ping_status'])) {
      unset($response->data['ping_status']);
    }
    return $response;
  },
  PHP_INT_MAX,
  1
);

add_filter(
  'rest_prepare_page',
  static function ($response) {
    if (isset($response->data['comment_status'])) {
      unset($response->data['comment_status']);
    }
    if (isset($response->data['ping_status'])) {
      unset($response->data['ping_status']);
    }
    return $response;
  },
  PHP_INT_MAX,
  1
);

/**
 * Remove xmlrpc methods related to comments
 */
add_filter(
  'xmlrpc_methods',
  static function ($methods) {
    unset($methods['wp.newComment']);
    unset($methods['wp.getCommentCount']);
    unset($methods['wp.getComment']);
    unset($methods['wp.getComments']);
    unset($methods['wp.deleteComment']);
    unset($methods['wp.editComment']);
    unset($methods['wp.getCommentStatusList']);
    return $methods;
  },
  PHP_INT_MAX,
  1
);

/**
 * Remove "Recent Comments" widget
 */
add_action(
  'widgets_init',
  static function () {
    unregister_widget('WP_Widget_Recent_Comments');
  },
  1,
  0
);

/**
 * Remove inline comment reply script
 */
add_action(
  'wp_enqueue_scripts',
  static function () {
    wp_dequeue_script('comment-reply');
    wp_deregister_script('comment-reply');
  },
  PHP_INT_MAX,
  0
);

/**
 * Add admin notice confirming comments are disabled
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
    if (get_user_meta(get_current_user_id(), 'comments_disabled_notice_dismissed', true)) {
      return;
    }

    echo '<div class="notice notice-info is-dismissible" id="comments-disabled-notice">
      <p>
        <strong>Comments Disabled:</strong> All commenting functionality has been removed from this site.
      </p>
    </div>';

    echo '<script>
      document.addEventListener("DOMContentLoaded", function() {
        const notice = document.getElementById("comments-disabled-notice");
        if (notice) {
          notice.addEventListener("click", function(e) {
            if (e.target.classList.contains("notice-dismiss")) {
              fetch(ajaxurl, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "action=dismiss_comments_disabled_notice&nonce=' . wp_create_nonce('comments_disabled_nonce') . '"
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
  'wp_ajax_dismiss_comments_disabled_notice',
  static function () {
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'comments_disabled_nonce')) {
      wp_die('Security check failed');
    }

    update_user_meta(get_current_user_id(), 'comments_disabled_notice_dismissed', true);
    wp_die();
  },
  10,
  0
);
