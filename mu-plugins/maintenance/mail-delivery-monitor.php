<?php

/**
 * Plugin Name:       Mail Delivery Monitor
 * Plugin URI:        https://openwpclub.com
 * Description:       Records bounded, privacy-safe wp_mail failures and shows administrators the latest sending status.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       mail-delivery-monitor
 */

defined('ABSPATH') || exit;

const MU_MAIL_MONITOR_FAILURES_OPTION = 'mu_mail_monitor_failures';
const MU_MAIL_MONITOR_SUCCESS_OPTION  = 'mu_mail_monitor_last_success';

add_action('wp_mail_failed', static function ($error) {
    if (!is_wp_error($error)) {
        return;
    }

    $entries   = get_option(MU_MAIL_MONITOR_FAILURES_OPTION, []);
    $entries   = is_array($entries) ? $entries : [];
    $message = sanitize_text_field($error->get_error_message());
    $message = preg_replace('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', '[redacted-email]', $message);
    $entries[] = [
        'time'    => time(),
        'code'    => sanitize_key((string) $error->get_error_code()),
        'message' => $message,
    ];

    $limit = max(5, (int) apply_filters('mu_mail_monitor_failure_limit', 20));
    update_option(MU_MAIL_MONITOR_FAILURES_OPTION, array_slice($entries, -$limit), false);
});

add_action('wp_mail_succeeded', static function () {
    $last_success = (int) get_option(MU_MAIL_MONITOR_SUCCESS_OPTION, 0);
    $interval = max(MINUTE_IN_SECONDS, (int) apply_filters('mu_mail_monitor_success_interval', 5 * MINUTE_IN_SECONDS));

    if (time() - $last_success >= $interval) {
        update_option(MU_MAIL_MONITOR_SUCCESS_OPTION, time(), false);
    }
});

add_action('admin_notices', static function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    $failures = get_option(MU_MAIL_MONITOR_FAILURES_OPTION, []);
    $latest   = is_array($failures) ? end($failures) : false;
    $success  = (int) get_option(MU_MAIL_MONITOR_SUCCESS_OPTION, 0);

    if (is_array($latest) && (int) $latest['time'] > $success && time() - (int) $latest['time'] < DAY_IN_SECONDS) {
        echo '<div class="notice notice-error"><p><strong>Mail Delivery Monitor:</strong> The latest wp_mail attempt failed. See the dashboard widget for details.</p></div>';
    }
});

add_action('wp_dashboard_setup', static function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    wp_add_dashboard_widget('mu_mail_delivery_monitor', 'Mail Delivery Monitor', static function () {
        $failures = get_option(MU_MAIL_MONITOR_FAILURES_OPTION, []);
        $success  = (int) get_option(MU_MAIL_MONITOR_SUCCESS_OPTION, 0);
        $latest   = is_array($failures) ? end($failures) : false;

        echo '<p><strong>Last accepted send:</strong> ';
        echo $success ? esc_html(human_time_diff($success, time()) . ' ago') : 'Not recorded';
        echo '</p><p><strong>Recorded failures:</strong> ' . esc_html((string) count((array) $failures)) . '</p>';

        if (is_array($latest)) {
            echo '<p><strong>Latest failure:</strong> ' . esc_html(human_time_diff((int) $latest['time'], time())) . ' ago</p>';
            echo '<p><code>' . esc_html($latest['code']) . '</code>: ' . esc_html($latest['message']) . '</p>';
        }

        echo '<p><em>A successful hook means WordPress handed the message to the mailer; it does not confirm inbox delivery.</em></p>';
    });
});
