<?php

/**
 * Control WordPress Heartbeat API
 *
 * Plugin name:       Disable Heartbeat API
 * Plugin URI:        https://openwpclub.com
 * Description:       Disables or limits the Heartbeat API to reduce server load. Keeps it active only in the post editor where it's needed.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-heartbeat-api
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Disable Heartbeat API on most admin pages
 *
 * The Heartbeat API is needed for:
 * - Post locking (multiple editors)
 * - Autosave recovery
 * - Real-time notifications
 *
 * We keep it on post edit screens only
 */
add_action(
    'init',
    static function () {
        // Don't run on frontend
        if (!is_admin()) {
            return;
        }

        global $pagenow;

        // Keep heartbeat on post edit screens only
        $allowed_pages = ['post.php', 'post-new.php'];

        if (!in_array($pagenow, $allowed_pages, true)) {
            wp_deregister_script('heartbeat');
        }
    },
    1
);

/**
 * Slow down heartbeat frequency when active
 *
 * Default is 15-60 seconds depending on context
 * We increase it to 60 seconds everywhere
 */
add_filter(
    'heartbeat_settings',
    static function ($settings) {
        $settings['interval'] = 60;
        return $settings;
    },
    10,
    1
);
