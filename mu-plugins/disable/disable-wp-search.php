<?php

/**
 * Disable WordPress frontend search
 *
 * Plugin name:       Disable WordPress Search
 * Plugin URI:        https://openwpclub.com
 * Description:       Redirects /?s= search queries to the homepage, eliminating unnecessary database queries on sites without search functionality.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-wp-search
 */

defined('ABSPATH') or die();

add_action(
    'parse_query',
    static function ($query) {
        if (is_admin() || !$query->is_main_query()) {
            return;
        }

        if ($query->is_search()) {
            wp_redirect(home_url('/'), 301);
            exit;
        }
    },
    1
);
