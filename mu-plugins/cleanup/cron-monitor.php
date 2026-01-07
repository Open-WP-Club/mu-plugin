<?php

/**
 * Plugin Name:       Cron Monitor
 * Plugin URI:        https://openwpclub.com
 * Description:       Records the timestamp of the last WordPress cron execution.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.1.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       cron-monitor
 */

// Record when cron completes (reliable way)
add_action('shutdown', function () {
  if (defined('DOING_CRON') && DOING_CRON) {
    update_option('cron_last_run', current_time('timestamp'), false);

    // Optional logging if WP_DEBUG_LOG is enabled
    if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
      error_log('WP-Cron completed at: ' . wp_date('Y-m-d H:i:s'));
    }
  }
});

// Function to get last cron run time
function get_last_cron_run()
{
  $last_run = get_option('cron_last_run', 0);
  return $last_run ? wp_date('Y-m-d H:i:s', $last_run) : 'Never recorded';
}

// Add a dashboard widget for admins
add_action('wp_dashboard_setup', function () {
  if (current_user_can('manage_options')) {
    wp_add_dashboard_widget(
      'cron_monitor_widget',
      'Cron Monitor',
      'cron_monitor_dashboard_widget'
    );
  }
});

// Dashboard widget content
function cron_monitor_dashboard_widget()
{
  $last_run = get_option('cron_last_run', 0);

  if ($last_run) {
    $last_run_formatted = wp_date('Y-m-d H:i:s', $last_run);
    $time_ago = human_time_diff($last_run, current_time('timestamp'));
    $status = (current_time('timestamp') - $last_run) < 3600 ? '🟢' : '🟡';
  } else {
    $last_run_formatted = 'Never recorded';
    $time_ago = 'Unknown';
    $status = '🔴';
  }

  echo '<p><strong>Status:</strong> ' . $status . '</p>';
  echo '<p><strong>Last Run:</strong> ' . esc_html($last_run_formatted) . '</p>';
  if ($last_run) {
    echo '<p><strong>Time Ago:</strong> ' . esc_html($time_ago) . ' ago</p>';
  } else {
    echo '<p><em>No cron activity detected yet.</em></p>';
  }
}
