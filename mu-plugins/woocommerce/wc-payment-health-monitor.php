<?php

/**
 * Plugin Name:       WooCommerce Payment Health Monitor
 * Plugin URI:        https://openwpclub.com
 * Description:       Tracks failed orders and webhook deliveries, and reports stale pending or on-hold orders without storing customer or payload data.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       wc-payment-health-monitor
 */

defined('ABSPATH') || exit;

function mu_wc_payment_health_append($option, array $entry)
{
    $entries = get_option($option, []);
    $entries = is_array($entries) ? $entries : [];
    $entries[] = $entry;
    $limit = max(10, (int) apply_filters('mu_wc_payment_health_log_limit', 50));
    update_option($option, array_slice($entries, -$limit), false);
    delete_transient('mu_wc_payment_health_summary');
}

add_action('woocommerce_order_status_failed', static function ($order_id, $order = null) {
    if (!$order && function_exists('wc_get_order')) {
        $order = wc_get_order($order_id);
    }

    mu_wc_payment_health_append('mu_wc_failed_orders', [
        'time'           => time(),
        'order_id'       => absint($order_id),
        'payment_method' => $order && is_callable([$order, 'get_payment_method']) ? sanitize_key($order->get_payment_method()) : '',
    ]);
}, 10, 2);

add_action('woocommerce_webhook_delivery', static function ($http_args, $response, $duration, $arg, $webhook_id) {
    $is_error = is_wp_error($response);
    $status   = $is_error ? 0 : (int) wp_remote_retrieve_response_code($response);
    if (!$is_error && $status < 400) {
        return;
    }

    mu_wc_payment_health_append('mu_wc_webhook_failures', [
        'time'       => time(),
        'webhook_id' => absint($webhook_id),
        'status'     => $is_error ? sanitize_key((string) $response->get_error_code()) : 'http_' . $status,
        'duration'   => round((float) $duration, 3),
    ]);
}, 10, 5);

add_action('woocommerce_webhook_disabled_due_delivery_failures', static function ($webhook_id) {
    mu_wc_payment_health_append('mu_wc_webhook_failures', [
        'time'       => time(),
        'webhook_id' => absint($webhook_id),
        'status'     => 'disabled_after_failures',
        'duration'   => 0,
    ]);
});

function mu_wc_payment_health_summary()
{
    if (!function_exists('wc_get_orders')) {
        return null;
    }

    $cached = get_transient('mu_wc_payment_health_summary');
    if (is_array($cached)) {
        return $cached;
    }

    $pending_minutes = max(15, (int) apply_filters('mu_wc_stale_pending_minutes', 60));
    $on_hold_hours   = max(1, (int) apply_filters('mu_wc_stale_on_hold_hours', 24));
    $query_total = static function ($status, $before) {
        $result = wc_get_orders([
            'status'       => $status,
            'date_created' => '<' . gmdate('Y-m-d H:i:s', $before),
            'limit'        => 1,
            'paginate'     => true,
            'return'       => 'ids',
            'type'         => 'shop_order',
        ]);
        return is_object($result) && isset($result->total) ? (int) $result->total : count((array) $result);
    };

    $failed_orders = (array) get_option('mu_wc_failed_orders', []);
    $webhook_failures = (array) get_option('mu_wc_webhook_failures', []);
    $recent = static function ($entry) {
        return time() - (int) ($entry['time'] ?? 0) < DAY_IN_SECONDS;
    };
    $summary = [
        'stale_pending' => $query_total('pending', time() - $pending_minutes * MINUTE_IN_SECONDS),
        'stale_on_hold' => $query_total('on-hold', time() - $on_hold_hours * HOUR_IN_SECONDS),
        'pending_minutes' => $pending_minutes,
        'on_hold_hours' => $on_hold_hours,
        'failed_orders' => count($failed_orders),
        'webhook_failures' => count($webhook_failures),
        'recent_failed_orders' => count(array_filter($failed_orders, $recent)),
        'recent_webhook_failures' => count(array_filter($webhook_failures, $recent)),
    ];
    set_transient('mu_wc_payment_health_summary', $summary, 5 * MINUTE_IN_SECONDS);
    return $summary;
}
