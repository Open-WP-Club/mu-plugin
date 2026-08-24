<?php
/**
 * Plugin Name:       Disable User Registration
 * Description:       Disables public WordPress self-registration through wp-login.php and the REST API without blocking users created by administrators, WooCommerce, or trusted integrations.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.1.0
 * Author:            OpenWP Club
 * Author URI:        https://openwpclub.com
 * License:           GPL v2 or later
 * Text Domain:       disable-user-registration
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
