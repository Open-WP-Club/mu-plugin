<?php

/**
 * Auto-delete unattached media
 *
 * Plugin name:       Safe Unattached Media Cleanup
 * Plugin URI:        https://openwpclub.com
 * Description:       Reports old unattached media weekly and only trashes or deletes it when the cleanup mode is explicitly changed.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           2.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       auto-delete-unattached-media
 */

defined('ABSPATH') or die();

add_action(
    'init',
    static function () {
        if (!wp_next_scheduled('mu_delete_unattached_media')) {
            wp_schedule_event(time(), 'weekly', 'mu_delete_unattached_media');
        }
    },
    10,
    0
);

add_action(
    'mu_delete_unattached_media',
    static function () {
        $days        = (int) apply_filters('mu_unattached_media_age_days', 30);
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $mode        = defined('MU_CLEANUP_MODE') ? MU_CLEANUP_MODE : 'report';
        $mode        = (string) apply_filters('mu_unattached_media_cleanup_mode', $mode);
        $mode        = in_array($mode, ['report', 'trash', 'delete'], true) ? $mode : 'report';

        $attachments = get_posts([
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_parent'    => 0,
            'date_query'     => [['before' => $cutoff_date]],
            'posts_per_page' => 50,
            'fields'         => 'ids',
        ]);

        $affected = 0;
        if ('report' !== $mode) {
            foreach ($attachments as $attachment_id) {
                $result = 'delete' === $mode
                    ? wp_delete_attachment($attachment_id, true)
                    : wp_trash_post($attachment_id);
                if ($result) {
                    $affected++;
                }
            }
        }

        update_option('mu_cleanup_unattached_media_report', [
            'time'       => time(),
            'mode'       => $mode,
            'candidates' => count($attachments),
            'affected'   => $affected,
            'sample_ids' => array_map('absint', array_slice($attachments, 0, 20)),
        ], false);
    },
    10,
    0
);
