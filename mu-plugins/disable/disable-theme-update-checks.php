<?php

/**
 * Disable theme update checks
 *
 * Plugin name:       Disable Theme Update Checks
 * Plugin URI:        https://openwpclub.com
 * Description:       Prevents WordPress from checking for theme updates on wordpress.org. For fully managed environments where themes are updated via CI/CD. Complements the existing plugin update disabler.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-theme-update-checks
 */

defined('ABSPATH') or die();

// Remove the scheduled cron event that polls for theme updates
add_action(
    'init',
    static function () {
        remove_action('wp_update_themes', 'wp_update_themes');
    },
    10,
    0
);

// Remove the HTTP request hook that fires the check
add_filter('auto_update_theme', '__return_false');

// Return empty transient so the dashboard shows nothing
add_filter(
    'pre_site_transient_update_themes',
    static function () {
        $transient           = new stdClass();
        $transient->last_checked = time();
        $transient->checked      = [];
        $transient->response     = [];
        return $transient;
    },
    PHP_INT_MAX,
    0
);

// Disable update check cron event
add_action(
    'admin_init',
    static function () {
        $hook = 'wp_update_themes';
        if (wp_next_scheduled($hook)) {
            wp_clear_scheduled_hook($hook);
        }
    },
    10,
    0
);
