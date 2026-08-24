<?php

/**
 * Plugin Name:       WooCommerce Action Scheduler Monitor
 * Plugin URI:        https://openwpclub.com
 * Description:       Warns administrators about failed or overdue Action Scheduler jobs used by WooCommerce and its extensions.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       action-scheduler-monitor
 */

defined('ABSPATH') || exit;

function mu_action_scheduler_health()
{
    if (!function_exists('as_get_scheduled_actions')) {
        return null;
    }

    $cached = get_transient('mu_action_scheduler_health');
    if (is_array($cached)) {
        return $cached;
    }

    $threshold = max(5, (int) apply_filters('mu_action_scheduler_overdue_minutes', 30));
    $failed = as_get_scheduled_actions([
        'status' => 'failed', 'per_page' => 5, 'orderby' => 'date', 'order' => 'DESC',
    ], 'ids');
    $overdue = as_get_scheduled_actions([
        'status' => 'pending',
        'date' => gmdate('Y-m-d H:i:s', time() - $threshold * MINUTE_IN_SECONDS),
        'date_compare' => '<',
        'per_page' => 5,
        'orderby' => 'date',
        'order' => 'ASC',
    ], 'ids');

    $summary = [
        'failed_ids' => array_map('absint', (array) $failed),
        'overdue_ids' => array_map('absint', (array) $overdue),
        'threshold' => $threshold,
    ];
    set_transient('mu_action_scheduler_health', $summary, 5 * MINUTE_IN_SECONDS);
    return $summary;
}

function mu_action_scheduler_admin_url()
{
    return admin_url('admin.php?page=wc-status&tab=action-scheduler');
}

add_action('admin_notices', static function () {
    if (!current_user_can('manage_woocommerce')) {
        return;
    }

    $health = mu_action_scheduler_health();
    if (!$health || (!$health['failed_ids'] && !$health['overdue_ids'])) {
        return;
    }

    echo '<div class="notice notice-warning"><p><strong>Action Scheduler Monitor:</strong> Background jobs need attention. ';
    echo '<a href="' . esc_url(mu_action_scheduler_admin_url()) . '">Review scheduled actions</a>.</p></div>';
});

add_action('wp_dashboard_setup', static function () {
    if (!current_user_can('manage_woocommerce')) {
        return;
    }

    wp_add_dashboard_widget('mu_action_scheduler_monitor', 'WooCommerce Scheduled Actions', static function () {
        $health = mu_action_scheduler_health();
        if (!$health) {
            echo '<p>Action Scheduler is not available.</p>';
            return;
        }

        echo '<p><strong>Failed (up to 5):</strong> ' . esc_html((string) count($health['failed_ids'])) . '</p>';
        echo '<p><strong>Pending over ' . esc_html((string) $health['threshold']) . ' minutes (up to 5):</strong> ' . esc_html((string) count($health['overdue_ids'])) . '</p>';
        echo '<p><a class="button" href="' . esc_url(mu_action_scheduler_admin_url()) . '">Open scheduled actions</a></p>';
    });
});
