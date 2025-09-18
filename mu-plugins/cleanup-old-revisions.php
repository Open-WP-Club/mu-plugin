<?php

/**
 * A utility plugin to cleanup old post revisions
 *
 * Plugin name:       Cleanup Old Revisions
 * Plugin URI:        https://openwpclub.com
 * Description:       Automatically removes old post revisions older than 30 days and limits revisions to 5 per post.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       cleanup-old-revisions
 */

// Limit post revisions to 5 (if not already defined)
if (!defined('WP_POST_REVISIONS')) {
  define('WP_POST_REVISIONS', 5);
}

// Cleanup old revisions weekly
add_action('wp', 'mu_schedule_revision_cleanup');

function mu_schedule_revision_cleanup()
{
  if (!wp_next_scheduled('mu_cleanup_old_revisions')) {
    wp_schedule_event(time(), 'weekly', 'mu_cleanup_old_revisions');
  }
}

// Cleanup function
add_action('mu_cleanup_old_revisions', 'mu_cleanup_old_revisions_callback');

function mu_cleanup_old_revisions_callback()
{
  global $wpdb;

  // Delete revisions older than 30 days
  $thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));

  $old_revisions = $wpdb->get_results($wpdb->prepare("
        SELECT ID FROM {$wpdb->posts} 
        WHERE post_type = 'revision' 
        AND post_date < %s
    ", $thirty_days_ago));

  $deleted_count = 0;

  foreach ($old_revisions as $revision) {
    if (wp_delete_post_revision($revision->ID)) {
      $deleted_count++;
    }
  }

  // Log cleanup if WP_DEBUG_LOG is enabled
  if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && $deleted_count > 0) {
    error_log(sprintf(
      'MU Cleanup: Deleted %d old post revisions older than 30 days',
      $deleted_count
    ));
  }
}

// Clean up scheduled event on deactivation (if plugin is moved)
register_deactivation_hook(__FILE__, function () {
  wp_clear_scheduled_hook('mu_cleanup_old_revisions');
});
