<?php

/**
 * Plugin Name:       HTTP API Failure Monitor
 * Plugin URI:        https://openwpclub.com
 * Description:       Tracks outbound WordPress HTTP API failures by host without storing URL paths, request bodies, headers, or credentials.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       http-api-failure-monitor
 */

defined('ABSPATH') || exit;

const MU_HTTP_FAILURES_OPTION = 'mu_http_api_failures';

add_action('http_api_debug', static function ($response, $context, $class, $parsed_args, $url) {
    if ('response' !== $context) {
        return;
    }

    $is_transport_error = is_wp_error($response);
    $status_code = $is_transport_error ? 0 : (int) wp_remote_retrieve_response_code($response);
    if (!$is_transport_error && 429 !== $status_code && $status_code < 500) {
        return;
    }

    $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
    $host = $host ?: 'unknown-host';
    $entries = get_option(MU_HTTP_FAILURES_OPTION, []);
    $entries = is_array($entries) ? $entries : [];
    $current = isset($entries[$host]) && is_array($entries[$host]) ? $entries[$host] : [];
    $message = $is_transport_error ? sanitize_text_field($response->get_error_message()) : 'Remote server returned HTTP ' . $status_code;
    $message = preg_replace('~https?://[^\s]+~i', '[redacted-url]', $message);
    $entries[$host] = [
        'count' => min(PHP_INT_MAX, ((int) ($current['count'] ?? 0)) + 1),
        'time' => time(),
        'code' => $is_transport_error ? sanitize_key((string) $response->get_error_code()) : 'http_' . $status_code,
        'message' => $message,
    ];

    uasort($entries, static function ($a, $b) {
        return ((int) $b['time']) <=> ((int) $a['time']);
    });
    $limit = max(5, (int) apply_filters('mu_http_failure_host_limit', 30));
    update_option(MU_HTTP_FAILURES_OPTION, array_slice($entries, 0, $limit, true), false);
}, 10, 5);

add_action('wp_dashboard_setup', static function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    wp_add_dashboard_widget('mu_http_api_failure_monitor', 'Outbound HTTP Failures', static function () {
        $entries = get_option(MU_HTTP_FAILURES_OPTION, []);
        if (!$entries || !is_array($entries)) {
            echo '<p>No outbound HTTP failures recorded.</p>';
            return;
        }

        echo '<table class="widefat striped"><thead><tr><th>Host</th><th>Count</th><th>Latest</th></tr></thead><tbody>';
        foreach (array_slice($entries, 0, 8, true) as $host => $entry) {
            echo '<tr><td><code>' . esc_html($host) . '</code></td><td>' . esc_html((string) $entry['count']) . '</td><td>' . esc_html(human_time_diff((int) $entry['time'], time())) . ' ago</td></tr>';
        }
        echo '</tbody></table>';
    });
});
