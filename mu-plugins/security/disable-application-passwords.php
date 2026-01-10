<?php

/**
 * Disable WordPress Application Passwords feature
 *
 * Plugin name:       Disable Application Passwords
 * Plugin URI:        https://openwpclub.com
 * Description:       Disables the Application Passwords feature introduced in WP 5.6 to reduce attack surface for sites not using it.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-application-passwords
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Completely disable Application Passwords feature
 *
 * Application Passwords allow external applications to authenticate
 * with WordPress via the REST API. If you're not using headless WP
 * or external integrations, this feature is an unnecessary attack vector.
 */

// Disable the feature entirely
add_filter('wp_is_application_passwords_available', '__return_false');

// Remove the Application Passwords section from user profile
add_action(
    'admin_init',
    static function () {
        // Remove the application passwords section from user edit screens
        remove_action('show_user_profile', 'wp_ajax_application_passwords');
        remove_action('edit_user_profile', 'wp_ajax_application_passwords');
    },
    99
);

// Block REST API endpoints for application passwords
add_filter(
    'rest_endpoints',
    static function ($endpoints) {
        // Remove application password endpoints
        $routes_to_remove = [
            '/wp/v2/users/(?P<user_id>(?:[\\d]+|me))/application-passwords',
            '/wp/v2/users/(?P<user_id>(?:[\\d]+|me))/application-passwords/introspect',
            '/wp/v2/users/(?P<user_id>(?:[\\d]+|me))/application-passwords/(?P<uuid>[\\w\\-]+)',
        ];

        foreach ($routes_to_remove as $route) {
            if (isset($endpoints[$route])) {
                unset($endpoints[$route]);
            }
        }

        return $endpoints;
    },
    10,
    1
);

// Disable application password authentication
add_filter('wp_is_application_passwords_available_for_user', '__return_false');

// Log any attempts to use application passwords (for security monitoring)
add_action(
    'application_password_failed_authentication',
    static function ($error) {
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            error_log("Application Password auth attempted from IP: {$ip} - Feature is disabled");
        }
    },
    10,
    1
);
