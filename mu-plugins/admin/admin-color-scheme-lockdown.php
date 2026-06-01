<?php

/**
 * Lock admin colour scheme for all users
 *
 * Plugin name:       Admin Color Scheme Lockdown
 * Plugin URI:        https://openwpclub.com
 * Description:       Forces a single admin colour scheme for all users. Useful for white-label admin panels. Set MU_ADMIN_COLOR_SCHEME to any built-in slug (default, light, blue, coffee, ectoplasm, midnight, ocean, sunrise).
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       admin-color-scheme-lockdown
 */

defined('ABSPATH') or die();

// Override in wp-config.php: define('MU_ADMIN_COLOR_SCHEME', 'midnight');
if (!defined('MU_ADMIN_COLOR_SCHEME')) {
    define('MU_ADMIN_COLOR_SCHEME', 'default');
}

// Force the colour scheme for every user
add_filter(
    'get_user_option_admin_color',
    static fn() => MU_ADMIN_COLOR_SCHEME,
    PHP_INT_MAX,
    0
);

// Remove the colour picker from the profile page
add_action(
    'admin_head-profile.php',
    static function () {
        echo '<style>#color-picker { display: none !important; }</style>';
    },
    10,
    0
);

add_action(
    'admin_head-user-edit.php',
    static function () {
        echo '<style>#color-picker { display: none !important; }</style>';
    },
    10,
    0
);
