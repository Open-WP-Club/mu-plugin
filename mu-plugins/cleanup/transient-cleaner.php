<?php

/**
 * Scheduled transient cleaner
 *
 * Plugin name:       Transient Cleaner
 * Plugin URI:        https://openwpclub.com
 * Description:       Runs a daily WP-Cron job to delete expired transients from wp_options. Keeps the database tidy on sites that accumulate plugin transients over time.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       transient-cleaner
 */

defined('ABSPATH') or die();

/**
 * Register the daily cron event on the first load after activation.
 */
add_action(
    'wp',
    static function () {
        if (!wp_next_scheduled('mu_clean_transients')) {
            wp_schedule_event(time(), 'daily', 'mu_clean_transients');
        }
    },
    10,
    0
);

/**
 * Delete expired transients (and their orphaned value rows) in batches of 500.
 */
add_action(
    'mu_clean_transients',
    static function () {
        global $wpdb;

        $batch_size = 500;

        // --- Single-site transients (wp_options) ---
        $expired_timeouts = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options}
                 WHERE option_name LIKE %s AND option_value < %d
                 LIMIT %d",
                $wpdb->esc_like('_transient_timeout_') . '%',
                time(),
                $batch_size
            )
        );

        if (!empty($expired_timeouts)) {
            $value_keys   = array_map(
                static fn($k) => str_replace('_transient_timeout_', '_transient_', $k),
                $expired_timeouts
            );
            $all_keys     = array_merge($expired_timeouts, $value_keys);
            $placeholders = implode(',', array_fill(0, count($all_keys), '%s'));

            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name IN ($placeholders)",
                ...$all_keys
            ));
        }

        // --- Multisite site transients (wp_sitemeta) ---
        if (is_multisite()) {
            $expired_site = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT meta_key FROM {$wpdb->sitemeta}
                     WHERE meta_key LIKE %s AND meta_value < %d
                     LIMIT %d",
                    $wpdb->esc_like('_site_transient_timeout_') . '%',
                    time(),
                    $batch_size
                )
            );

            if (!empty($expired_site)) {
                $value_keys   = array_map(
                    static fn($k) => str_replace('_site_transient_timeout_', '_site_transient_', $k),
                    $expired_site
                );
                $all_keys     = array_merge($expired_site, $value_keys);
                $placeholders = implode(',', array_fill(0, count($all_keys), '%s'));

                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $wpdb->query($wpdb->prepare(
                    "DELETE FROM {$wpdb->sitemeta} WHERE meta_key IN ($placeholders)",
                    ...$all_keys
                ));
            }
        }

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            $deleted = count($expired_timeouts ?? []);
            error_log("Transient Cleaner: removed {$deleted} expired transient(s).");
        }
    },
    10,
    0
);

/**
 * Deregister the cron event when the file is no longer present.
 * (Fires only if the action is somehow unhooked while the cron remains scheduled.)
 */
register_shutdown_function(static function () {
    // Intentionally empty — cron cleanup requires manual removal or
    // wp_clear_scheduled_hook('mu_clean_transients') in an uninstall routine.
});
