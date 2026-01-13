<?php

/**
 * Disable WordPress Welcome Panel
 *
 * Plugin name:       Disable Welcome Panel
 * Plugin URI:        https://openwpclub.com
 * Description:       Removes the welcome panel from the WordPress dashboard for a cleaner admin experience.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-welcome-panel
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Remove the welcome panel from the dashboard
 */
remove_action('welcome_panel', 'wp_welcome_panel');

/**
 * Hide the welcome panel for all users
 *
 * This sets the user meta to dismiss the welcome panel
 * so it doesn't appear even on first login.
 */
add_action(
    'load-index.php',
    static function () {
        $user_id = get_current_user_id();

        if (get_user_meta($user_id, 'show_welcome_panel', true) !== '0') {
            update_user_meta($user_id, 'show_welcome_panel', 0);
        }
    }
);

/**
 * Remove the "Welcome" option from Screen Options
 */
add_filter(
    'get_user_metadata',
    static function ($check, $object_id, $meta_key, $single) {
        if ($meta_key === 'show_welcome_panel') {
            return '0';
        }
        return $check;
    },
    10,
    4
);
