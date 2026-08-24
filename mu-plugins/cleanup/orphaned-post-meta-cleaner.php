<?php

/**
 * Orphaned post meta cleaner
 *
 * Plugin name:       Safe Orphaned Post Meta Cleaner
 * Plugin URI:        https://openwpclub.com
 * Description:       Reports orphaned post metadata weekly and deletes it in bounded batches only when MU_CLEANUP_MODE is set to delete.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           2.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       orphaned-post-meta-cleaner
 */

defined('ABSPATH') or die();

add_action(
    'init',
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

        $mode = defined('MU_CLEANUP_MODE') ? MU_CLEANUP_MODE : 'report';
        $mode = (string) apply_filters('mu_orphaned_post_meta_cleanup_mode', $mode);
        $limit = max(1, (int) apply_filters('mu_orphaned_post_meta_cleanup_batch', 1000));
        $candidate_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT pm.meta_id FROM {$wpdb->postmeta} pm
                 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                 WHERE p.ID IS NULL
                 LIMIT %d",
                $limit
            )
        );
        $candidate_ids = array_map('absint', (array) $candidate_ids);
        $candidates = count($candidate_ids);
        $deleted = 0;

        if ('delete' === $mode && $candidates > 0) {
            $placeholders = implode(', ', array_fill(0, $candidates, '%d'));
            $deleted = (int) $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->postmeta} WHERE meta_id IN ({$placeholders})",
                    $candidate_ids
                )
            );
        }

        update_option('mu_cleanup_orphaned_meta_report', [
            'time'       => time(),
            'mode'       => 'delete' === $mode ? 'delete' : 'report',
            'candidates' => $candidates,
            'affected'   => max(0, $deleted),
        ], false);
    },
    10,
    0
);
