<?php

/**
 * Limit WordPress cron concurrency
 *
 * Plugin name:       Limit Cron Concurrency
 * Plugin URI:        https://openwpclub.com
 * Description:       Uses a transient-based lock to prevent multiple simultaneous wp-cron.php processes from running the same scheduled hook, reducing database contention on busy sites.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       limit-cron-concurrency
 */

defined('ABSPATH') or die();

if (!defined('DOING_CRON') || !DOING_CRON) {
    return;
}

add_action(
    'wp_loaded',
    static function () {
        $lock_key = 'mu_cron_running';
        $ttl      = (int) apply_filters('mu_cron_lock_ttl', 5 * MINUTE_IN_SECONDS);

        if (get_transient($lock_key)) {
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                error_log('Cron concurrency lock active — skipping duplicate run.');
            }
            exit;
        }

        set_transient($lock_key, 1, $ttl);

        register_shutdown_function(static function () use ($lock_key) {
            delete_transient($lock_key);
        });
    },
    1,
    0
);
