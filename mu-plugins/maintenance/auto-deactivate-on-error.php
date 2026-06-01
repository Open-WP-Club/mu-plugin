<?php

/**
 * Automatically deactivate plugins that cause fatal errors on activation
 *
 * Plugin name:       Auto-Deactivate on Error
 * Plugin URI:        https://openwpclub.com
 * Description:       Tracks which plugin is being activated via a short-lived transient. If a fatal error prevents wp_loaded from firing, the next admin request detects the stale transient and deactivates the offending plugin.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       auto-deactivate-on-error
 */

defined('ABSPATH') or die();

// Mark which plugin is being activated (TTL 30 s is enough for any normal request)
add_action(
    'activate_plugin',
    static function (string $plugin) {
        set_transient('mu_activating_plugin', $plugin, 30);
    },
    1,
    1
);

// Successful load — clear the marker
add_action(
    'wp_loaded',
    static function () {
        delete_transient('mu_activating_plugin');
    },
    1,
    0
);

// If the marker survived to the next admin request, the plugin caused a fatal error
add_action(
    'admin_init',
    static function () {
        $failed = get_transient('mu_activating_plugin');
        if (!$failed) {
            return;
        }

        delete_transient('mu_activating_plugin');

        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if (!is_plugin_active($failed)) {
            return; // Already deactivated (e.g. by WP recovery mode)
        }

        deactivate_plugins([$failed]);

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log("Auto-Deactivate: deactivated \"{$failed}\" due to a fatal error during activation.");
        }

        $plugin_name = $failed;
        add_action(
            'admin_notices',
            static function () use ($plugin_name) {
                printf(
                    '<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
                    esc_html__('Plugin deactivated:', 'auto-deactivate-on-error'),
                    sprintf(
                        esc_html__('%s was automatically deactivated because it caused a fatal error during activation.', 'auto-deactivate-on-error'),
                        '<code>' . esc_html($plugin_name) . '</code>'
                    )
                );
            },
            10,
            0
        );
    },
    1,
    0
);
