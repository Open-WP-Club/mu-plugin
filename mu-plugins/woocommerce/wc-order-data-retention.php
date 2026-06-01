<?php

/**
 * WooCommerce order data retention
 *
 * Plugin name:       WooCommerce Order Data Retention
 * Plugin URI:        https://openwpclub.com
 * Description:       Monthly cron job that permanently deletes WooCommerce orders older than a configurable number of days in terminal statuses (completed, cancelled, refunded). Supports both HPOS and legacy post-based orders. Override age via 'mu_wc_order_retention_days' filter (default 730 = ~2 years).
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       wc-order-data-retention
 */

defined('ABSPATH') or die();

if (!defined('WC_PLUGIN_FILE')) {
    return;
}

add_action(
    'wp',
    static function () {
        if (!wp_next_scheduled('mu_wc_order_retention')) {
            wp_schedule_event(time(), 'monthly', 'mu_wc_order_retention');
        }
    },
    10,
    0
);

add_action(
    'mu_wc_order_retention',
    static function () {
        $days     = (int) apply_filters('mu_wc_order_retention_days', 730);
        $statuses = apply_filters('mu_wc_order_retention_statuses', ['completed', 'cancelled', 'refunded']);
        $cutoff   = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $batch    = (int) apply_filters('mu_wc_order_retention_batch', 50);

        $order_ids = wc_get_orders([
            'status'       => $statuses,
            'date_created' => '<' . $cutoff,
            'limit'        => $batch,
            'return'       => 'ids',
            'type'         => 'shop_order',
        ]);

        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            if ($order) {
                $order->delete(true);
            }
        }

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && !empty($order_ids)) {
            error_log(sprintf('WC Order Retention: deleted %d order(s) older than %d days.', count($order_ids), $days));
        }
    },
    10,
    0
);
