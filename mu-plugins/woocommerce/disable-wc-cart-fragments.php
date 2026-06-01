<?php

/**
 * Disable WooCommerce cart fragments on non-shop pages
 *
 * Plugin name:       Disable WooCommerce Cart Fragments
 * Plugin URI:        https://openwpclub.com
 * Description:       Prevents WooCommerce from enqueuing the cart-fragments AJAX script on pages where no cart interaction is possible, reducing unnecessary AJAX requests on every page load.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-wc-cart-fragments
 */

defined('ABSPATH') or die();

if (!defined('WC_PLUGIN_FILE')) {
    return;
}

add_action(
    'wp_enqueue_scripts',
    static function () {
        // Keep fragments on pages where the cart is visible or actionable
        if (
            is_cart()
            || is_checkout()
            || is_account_page()
            || apply_filters('mu_wc_cart_fragments_enabled', false)
        ) {
            return;
        }

        wp_dequeue_script('wc-cart-fragments');
    },
    99,
    0
);
