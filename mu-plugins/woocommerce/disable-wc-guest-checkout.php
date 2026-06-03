<?php

/**
 * Disable WooCommerce guest checkout
 *
 * Plugin name:       Disable WC Guest Checkout
 * Plugin URI:        https://openwpclub.com
 * Description:       Forces account registration at checkout. Required for subscription or membership stores where an account is needed to manage orders.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-wc-guest-checkout
 */

defined('ABSPATH') or die();

if (!defined('WC_PLUGIN_FILE')) {
    return;
}

// Force registration to be required regardless of the WooCommerce setting
add_filter('woocommerce_checkout_registration_required', '__return_true');

// Also override the stored option so the checkout form renders correctly
add_filter(
    'pre_option_woocommerce_enable_guest_checkout',
    static function () {
        return 'no';
    }
);
