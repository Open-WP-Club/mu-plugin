<?php

/**
 * Disable WordPress Site Health
 *
 * Plugin name:       Disable Site Health
 * Plugin URI:        https://openwpclub.com
 * Description:       Removes the Site Health admin page, dashboard widget, and its background cron check. For production sites managed externally where Site Health output is noise rather than signal.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-site-health
 */

defined('ABSPATH') or die();

// Remove from admin menu (Tools > Site Health)
add_action(
    'admin_menu',
    static function () {
        remove_submenu_page('tools.php', 'site-health.php');
    },
    999,
    0
);

// Remove dashboard widget
add_action(
    'wp_dashboard_setup',
    static function () {
        remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
    },
    10,
    0
);

// Stop the background cron check
add_action(
    'admin_init',
    static function () {
        $hook = 'wp_site_health_scheduled_check';
        if (wp_next_scheduled($hook)) {
            wp_clear_scheduled_hook($hook);
        }
        remove_action($hook, 'wp_cron_scheduled_check');
    },
    10,
    0
);

// Prevent the cron from being re-registered
add_filter(
    'pre_option_wp_site_health_scheduled_complete',
    static fn() => time(),
    10,
    0
);

// Redirect direct access to the Site Health page
add_action(
    'load-site-health.php',
    static function () {
        wp_redirect(admin_url());
        exit;
    },
    0,
    0
);
