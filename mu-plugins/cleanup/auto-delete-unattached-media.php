<?php

/**
 * Auto-delete unattached media
 *
 * Plugin name:       Auto-Delete Unattached Media
 * Plugin URI:        https://openwpclub.com
 * Description:       Weekly cron job that permanently deletes media library items with no parent post older than a configurable number of days. Prevents library bloat from orphaned uploads. Override age with 'mu_unattached_media_age_days' filter (default 30).
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       auto-delete-unattached-media
 */

defined('ABSPATH') or die();

add_action(
    'wp',
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

        $attachments = get_posts([
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_parent'    => 0,
            'date_query'     => [['before' => $cutoff_date]],
            'posts_per_page' => 50,
            'fields'         => 'ids',
        ]);

        foreach ($attachments as $attachment_id) {
            wp_delete_attachment($attachment_id, true);
        }

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && !empty($attachments)) {
            error_log(sprintf('Auto-Delete Unattached Media: removed %d attachment(s).', count($attachments)));
        }
    },
    10,
    0
);
