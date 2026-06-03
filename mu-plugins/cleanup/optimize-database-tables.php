<?php

/**
 * Schedule weekly database table optimization
 *
 * Plugin name:       Optimize Database Tables
 * Plugin URI:        https://openwpclub.com
 * Description:       Runs OPTIMIZE TABLE weekly on core WordPress tables to reclaim fragmented space and maintain query performance.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       optimize-database-tables
 */

defined('ABSPATH') or die();

add_action(
    'mu_optimize_db_tables',
    static function () {
        global $wpdb;

        $tables = [
            $wpdb->posts,
            $wpdb->postmeta,
            $wpdb->options,
            $wpdb->comments,
            $wpdb->commentmeta,
            $wpdb->termmeta,
        ];

        foreach ($tables as $table) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query("OPTIMIZE TABLE `{$table}`");
        }
    }
);

add_action(
    'init',
    static function () {
        if (!wp_next_scheduled('mu_optimize_db_tables')) {
            wp_schedule_event(time(), 'weekly', 'mu_optimize_db_tables');
        }
    }
);
