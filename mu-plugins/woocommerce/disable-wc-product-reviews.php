<?php

/**
 * Disable WooCommerce product reviews
 *
 * Plugin name:       Disable WC Product Reviews
 * Plugin URI:        https://openwpclub.com
 * Description:       Disables the WooCommerce review system on products. Unlike the generic disable-comments plugin, this targets the WooCommerce review tab and rating option specifically.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-wc-product-reviews
 */

defined('ABSPATH') or die();

if (!defined('WC_PLUGIN_FILE')) {
    return;
}

// Override WooCommerce reviews setting
add_filter(
    'pre_option_woocommerce_enable_reviews',
    static function () {
        return 'no';
    }
);

// Remove Reviews tab from single product page
add_filter(
    'woocommerce_product_tabs',
    static function ($tabs) {
        unset($tabs['reviews']);
        return $tabs;
    },
    99
);

// Close comments on product post type
add_filter(
    'comments_open',
    static function ($open, $post_id) {
        if (get_post_type($post_id) === 'product') {
            return false;
        }
        return $open;
    },
    10,
    2
);
