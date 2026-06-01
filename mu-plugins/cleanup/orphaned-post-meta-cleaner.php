<?php

/**
 * Orphaned post meta cleaner
 *
 * Plugin name:       Orphaned Post Meta Cleaner
 * Plugin URI:        https://openwpclub.com
 * Description:       Weekly cron job that deletes wp_postmeta rows whose post_id no longer exists in wp_posts. Reclaims space after posts are permanently deleted.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       orphaned-post-meta-cleaner
 */

defined('ABSPATH') or die();

add_action(
    'wp',
    static function () {
        if (!wp_next_scheduled('mu_clean_orphaned_post_meta')) {
            wp_schedule_event(time(), 'weekly', 'mu_clean_orphaned_post_meta');
        }
    },
    10,
    0
);

add_action(
    'mu_clean_orphaned_post_meta',
    static function () {
        global $wpdb;

        $deleted = $wpdb->query(
            "DELETE pm FROM {$wpdb->postmeta} pm
             LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE p.ID IS NULL
             LIMIT 1000"
        );

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && $deleted) {
            error_log(sprintf('Orphaned Post Meta Cleaner: removed %d row(s).', $deleted));
        }
    },
    10,
    0
);
