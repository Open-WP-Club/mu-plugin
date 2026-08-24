<?php

/**
 * Plugin Name:       Cron Monitor
 * Plugin URI:        https://openwpclub.com
 * Description:       Tracks WP-Cron runs and scheduling errors, and reports overdue or orphaned scheduled hooks without deleting them.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           2.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       cron-monitor
 */

defined('ABSPATH') || exit;

const MU_CRON_MONITOR_LAST_RUN_OPTION = 'cron_last_run';
const MU_CRON_MONITOR_ERRORS_OPTION   = 'mu_cron_monitor_errors';

add_action('shutdown', static function () {
    if (defined('DOING_CRON') && DOING_CRON) {
        update_option(MU_CRON_MONITOR_LAST_RUN_OPTION, time(), false);
    }
});

/**
 * Store a bounded, payload-free cron error entry.
 *
 * @param WP_Error $error Error returned by WordPress.
 * @param string   $hook  Scheduled hook name.
 */
function mu_cron_monitor_record_error($error, $hook = '')
{
    if (!is_wp_error($error)) {
        return;
    }

    $entries   = get_option(MU_CRON_MONITOR_ERRORS_OPTION, []);
    $entries   = is_array($entries) ? $entries : [];
    $entries[] = [
        'time'    => time(),
        'hook'    => sanitize_key($hook),
        'code'    => sanitize_key((string) $error->get_error_code()),
        'message' => sanitize_text_field($error->get_error_message()),
    ];

    $limit = max(5, (int) apply_filters('mu_cron_monitor_error_limit', 20));
    update_option(MU_CRON_MONITOR_ERRORS_OPTION, array_slice($entries, -$limit), false);
}

add_action('cron_reschedule_event_error', static function ($error, $hook) {
    mu_cron_monitor_record_error($error, (string) $hook);
}, 10, 2);

add_action('cron_unschedule_event_error', static function ($error, $hook) {
    mu_cron_monitor_record_error($error, (string) $hook);
}, 10, 2);

/**
 * Find scheduled hooks for which no callback is currently registered.
 *
 * @return array<string,int>
 */
function mu_cron_monitor_orphaned_hooks()
{
    $cron = function_exists('_get_cron_array') ? _get_cron_array() : [];
    $orphans = [];

    if (!is_array($cron)) {
        return $orphans;
    }

    foreach ($cron as $hooks) {
        if (!is_array($hooks)) {
            continue;
        }

        foreach ($hooks as $hook => $events) {
            if (!has_action($hook)) {
                $orphans[$hook] = ($orphans[$hook] ?? 0) + count((array) $events);
            }
        }
    }

    ksort($orphans);
    return $orphans;
}

add_action('admin_notices', static function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    $last_run = (int) get_option(MU_CRON_MONITOR_LAST_RUN_OPTION, 0);
    if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON && (!$last_run || time() - $last_run > 2 * HOUR_IN_SECONDS)) {
        echo '<div class="notice notice-warning"><p><strong>Cron Monitor:</strong> DISABLE_WP_CRON is enabled, but no cron run has been recorded in the last two hours.</p></div>';
    }

    $errors = get_option(MU_CRON_MONITOR_ERRORS_OPTION, []);
    $latest = is_array($errors) ? end($errors) : false;
    if (is_array($latest) && !empty($latest['time']) && time() - (int) $latest['time'] < DAY_IN_SECONDS) {
        echo '<div class="notice notice-error"><p><strong>Cron Monitor:</strong> A scheduling error occurred in the last 24 hours. Check the dashboard widget for details.</p></div>';
    }
});

add_action('wp_dashboard_setup', static function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    wp_add_dashboard_widget('mu_cron_monitor_widget', 'Cron Monitor', 'mu_cron_monitor_dashboard_widget');
});

function mu_cron_monitor_dashboard_widget()
{
    $last_run = (int) get_option(MU_CRON_MONITOR_LAST_RUN_OPTION, 0);
    $errors   = get_option(MU_CRON_MONITOR_ERRORS_OPTION, []);
    $orphans  = mu_cron_monitor_orphaned_hooks();

    echo '<p><strong>Last run:</strong> ';
    echo $last_run ? esc_html(human_time_diff($last_run, time()) . ' ago') : 'Never recorded';
    echo '</p><p><strong>Recorded errors:</strong> ' . esc_html((string) count((array) $errors)) . '</p>';

    if ($errors) {
        $latest = end($errors);
        echo '<p><code>' . esc_html($latest['hook'] ?: 'unknown hook') . '</code>: ' . esc_html($latest['message']) . '</p>';
    }

    echo '<p><strong>Hooks without callbacks:</strong> ' . esc_html((string) count($orphans)) . '</p>';
    if ($orphans) {
        echo '<ul>';
        foreach (array_slice($orphans, 0, 5, true) as $hook => $count) {
            echo '<li><code>' . esc_html($hook) . '</code> (' . esc_html((string) $count) . ')</li>';
        }
        echo '</ul><p><em>Review these hooks before removing them; this monitor never deletes scheduled events.</em></p>';
    }
}
