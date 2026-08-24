<?php

/**
 * WooCommerce order data retention
 *
 * Plugin name:       Safe WooCommerce Order Data Retention
 * Plugin URI:        https://openwpclub.com
 * Description:       Reports old terminal WooCommerce orders monthly and only trashes or deletes them when the cleanup mode is explicitly changed.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           2.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       wc-order-data-retention
 */

defined('ABSPATH') or die();

add_filter('cron_schedules', static function ($schedules) {
    if (!isset($schedules['monthly'])) {
        $schedules['monthly'] = [
            'interval' => 30 * DAY_IN_SECONDS,
            'display'  => 'Once Monthly',
        ];
    }
    return $schedules;
});

add_action(
    'init',
    static function () {
        if (!class_exists('WooCommerce')) {
            return;
        }

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
        if (!function_exists('wc_get_orders')) {
            return;
        }

        $days     = (int) apply_filters('mu_wc_order_retention_days', 730);
        $statuses = apply_filters('mu_wc_order_retention_statuses', ['completed', 'cancelled', 'refunded']);
        $cutoff   = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $batch    = (int) apply_filters('mu_wc_order_retention_batch', 50);
        $mode     = defined('MU_CLEANUP_MODE') ? MU_CLEANUP_MODE : 'report';
        $mode     = (string) apply_filters('mu_wc_order_retention_mode', $mode);
        $mode     = in_array($mode, ['report', 'trash', 'delete'], true) ? $mode : 'report';

        $order_ids = wc_get_orders([
            'status'       => $statuses,
            'date_created' => '<' . $cutoff,
            'limit'        => $batch,
            'return'       => 'ids',
            'type'         => 'shop_order',
        ]);

        $affected = 0;
        if ('report' !== $mode) {
            foreach ($order_ids as $order_id) {
                $order = wc_get_order($order_id);
                if ($order && $order->delete('delete' === $mode)) {
                    $affected++;
                }
            }
        }

        update_option('mu_cleanup_wc_orders_report', [
            'time'       => time(),
            'mode'       => $mode,
            'candidates' => count($order_ids),
            'affected'   => $affected,
            'sample_ids' => array_map('absint', array_slice($order_ids, 0, 20)),
        ], false);
    },
    10,
    0
);
