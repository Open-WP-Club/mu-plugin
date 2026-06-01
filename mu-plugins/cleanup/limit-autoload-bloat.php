<?php

/**
 * Detect and limit wp_options autoload bloat
 *
 * Plugin name:       Limit Autoload Bloat
 * Plugin URI:        https://openwpclub.com
 * Description:       Runs daily to detect wp_options rows with autoload=yes that exceed a configurable byte threshold. Logs offenders and optionally sets them to autoload=no. Configure threshold via 'mu_autoload_size_threshold' filter (default 10 000 bytes).
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       limit-autoload-bloat
 */

defined('ABSPATH') or die();

add_action(
    'wp',
    static function () {
        if (!wp_next_scheduled('mu_check_autoload_bloat')) {
            wp_schedule_event(time(), 'daily', 'mu_check_autoload_bloat');
        }
    },
    10,
    0
);

add_action(
    'mu_check_autoload_bloat',
    static function () {
        global $wpdb;

        $threshold = (int) apply_filters('mu_autoload_size_threshold', 10000);

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT option_name, LENGTH(option_value) AS size
             FROM {$wpdb->options}
             WHERE autoload = 'yes' AND LENGTH(option_value) > %d
             ORDER BY size DESC
             LIMIT 20",
            $threshold
        ));

        if (empty($rows)) {
            return;
        }

        foreach ($rows as $row) {
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                error_log(sprintf(
                    'Autoload bloat: option "%s" is %s bytes.',
                    $row->option_name,
                    number_format((int) $row->size)
                ));
            }

            // Set to autoload=no only when the filter explicitly returns true
            $disable = apply_filters('mu_autoload_disable_option', false, $row->option_name, (int) $row->size);
            if ($disable) {
                $wpdb->update(
                    $wpdb->options,
                    ['autoload' => 'no'],
                    ['option_name' => $row->option_name],
                    ['%s'],
                    ['%s']
                );
            }
        }
    },
    10,
    0
);
