<?php

/**
 * Custom WooCommerce order number prefix/suffix
 *
 * Plugin name:       WooCommerce Order Number Format
 * Plugin URI:        https://openwpclub.com
 * Description:       Adds a configurable prefix and/or suffix to the order number shown in admin, emails, and the customer account area. Define MU_WC_ORDER_NUMBER_PREFIX and/or MU_WC_ORDER_NUMBER_SUFFIX in wp-config.php. The underlying order ID is unchanged.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       wc-order-number-format
 */

defined('ABSPATH') or die();

if (!defined('WC_PLUGIN_FILE')) {
    return;
}

add_filter(
    'woocommerce_order_number',
    static function ($order_number, $order) {
        $prefix = defined('MU_WC_ORDER_NUMBER_PREFIX') ? MU_WC_ORDER_NUMBER_PREFIX : '';
        $suffix = defined('MU_WC_ORDER_NUMBER_SUFFIX') ? MU_WC_ORDER_NUMBER_SUFFIX : '';

        return $prefix . $order_number . $suffix;
    },
    10,
    2
);
