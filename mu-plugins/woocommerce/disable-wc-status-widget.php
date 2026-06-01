<?php

/**
 * Disable WooCommerce status dashboard widget
 *
 * Plugin name:       Disable WooCommerce Status Dashboard Widget
 * Plugin URI:        https://openwpclub.com
 * Description:       Removes the WooCommerce status dashboard widget from the WordPress admin dashboard, reducing noise for sites that use WooCommerce Analytics or a custom overview instead.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-wc-status-widget
 */

defined('ABSPATH') or die();

if (!defined('WC_PLUGIN_FILE')) {
    return;
}

add_action(
    'wp_dashboard_setup',
    static function () {
        remove_meta_box('woocommerce_dashboard_status', 'dashboard', 'normal');
        remove_meta_box('woocommerce_dashboard_recent_reviews', 'dashboard', 'normal');
    },
    20,
    0
);
