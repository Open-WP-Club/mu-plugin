<?php
/**
 * Plugin Name: Disable User Registration
 * Description: Completely disables user registration via wp-login.php, REST API, and all registration endpoints
 * Version: 1.0.0
 * Author: OpenWP Club
 * Author URI: https://openwpclub.com
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Disable user registration in WordPress settings
 */
add_filter('pre_option_users_can_register', '__return_zero');

/**
 * Block registration via wp-login.php?action=register
 */
add_action('login_init', function() {
    if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'register') {
        wp_die(
            __('User registration is disabled for this site.'),
            __('Registration Disabled'),
            ['response' => 403]
        );
    }
});

/**
 * Remove registration link from login page
 */
add_filter('register_url', '__return_empty_string');

/**
 * Block REST API user registration endpoint
 */
add_filter('rest_pre_insert_user', function($user, $request) {
    return new WP_Error(
        'rest_user_cannot_create',
        __('User registration is disabled for this site.'),
        ['status' => 403]
    );
}, 10, 2);

/**
 * Block REST API access to user creation endpoint
 */
add_filter('rest_endpoints', function($endpoints) {
    // Block POST requests to /wp/v2/users (user creation)
    if (isset($endpoints['/wp/v2/users'])) {
        foreach ($endpoints['/wp/v2/users'] as $key => $handler) {
            if (isset($handler['methods']) &&
                (is_array($handler['methods']) && in_array('POST', $handler['methods']) ||
                 $handler['methods'] === 'POST' ||
                 $handler['methods'] === WP_REST_Server::CREATABLE)) {
                unset($endpoints['/wp/v2/users'][$key]);
            }
        }
    }
    return $endpoints;
});

/**
 * Prevent user registration via wp_insert_user() and wp_create_user()
 * Allow user creation only for admins in admin context
 */
add_action('user_register', function($user_id) {
    // Allow if current user is admin and in admin area
    if (is_admin() && current_user_can('create_users')) {
        return;
    }

    // Block programmatic user registration
    wp_delete_user($user_id);
    wp_die(
        __('User registration is disabled for this site.'),
        __('Registration Disabled'),
        ['response' => 403]
    );
}, 1);
