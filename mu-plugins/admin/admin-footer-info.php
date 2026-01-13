<?php

/**
 * Display system info in WordPress admin footer
 *
 * Plugin name:       Admin Footer Info
 * Plugin URI:        https://openwpclub.com
 * Description:       Shows PHP version, MySQL version, WordPress version, and memory usage in the admin footer.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       admin-footer-info
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Replace the default "Thank you for creating with WordPress" text
 */
add_filter(
    'admin_footer_text',
    static function ($text) {
        global $wpdb;

        // Get PHP version
        $php_version = phpversion();

        // Get MySQL/MariaDB version
        $mysql_version = $wpdb->get_var('SELECT VERSION()');

        // Get WordPress version
        $wp_version = get_bloginfo('version');

        // Get current memory usage
        $memory_usage = size_format(memory_get_usage(), 2);
        $memory_limit = ini_get('memory_limit');

        return sprintf(
            'PHP %s | MySQL %s | WP %s | Memory: %s / %s',
            esc_html($php_version),
            esc_html($mysql_version),
            esc_html($wp_version),
            esc_html($memory_usage),
            esc_html($memory_limit)
        );
    },
    10,
    1
);

/**
 * Replace the WordPress version in the right footer
 */
add_filter(
    'update_footer',
    static function ($text) {
        // Get peak memory usage
        $peak_memory = size_format(memory_get_peak_usage(), 2);

        // Get number of database queries
        $query_count = get_num_queries();

        // Get page generation time
        $timer_stop = timer_stop(0);

        return sprintf(
            '%d queries | %ss | Peak: %s',
            absint($query_count),
            esc_html($timer_stop),
            esc_html($peak_memory)
        );
    },
    99
);
