<?php

/**
 * Disable WooCommerce Marketing Hub
 *
 * Plugin name:       Disable WooCommerce Marketing Hub
 * Plugin URI:        https://openwpclub.com
 * Description:       Removes the WooCommerce Marketing admin menu and disables marketing-related background cron events and HTTP calls to woocommerce.com. For stores that do not use Mailchimp or other WooCommerce marketing integrations.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-wc-marketing-hub
 */

defined('ABSPATH') or die();

if (!defined('WC_PLUGIN_FILE')) {
    return; // WooCommerce not active
}

// Remove the Marketing top-level menu
add_action(
    'admin_menu',
    static function () {
        remove_menu_page('woocommerce-marketing');
    },
    99,
    0
);

// Disable the remote inbox notifications fetch (calls home.woocommerce.com)
add_filter('woocommerce_allow_marketplace_suggestions', '__return_false');
add_filter('woocommerce_marketing_menu_items',          '__return_empty_array');

// Disable WooCommerce helper / subscription check cron
add_filter('woocommerce_helper_updates_apply', '__return_false');

// Stop WC from showing the marketing hub onboarding notice
add_filter('woocommerce_show_admin_notice', static function (bool $show, string $notice): bool {
    $marketing_notices = ['wc_marketplaces_show_extensions_notice', 'wc_remote_inbox_notifications'];
    return in_array($notice, $marketing_notices, true) ? false : $show;
}, 10, 2);
