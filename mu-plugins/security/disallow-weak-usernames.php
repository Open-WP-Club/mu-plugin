<?php

/**
 * Disallow weak / reserved usernames
 *
 * Plugin name:       Disallow Weak Usernames
 * Plugin URI:        https://openwpclub.com
 * Description:       Blocks creation of accounts with usernames commonly targeted by credential-stuffing attacks (admin, test, wordpress, etc.).
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disallow-weak-usernames
 */

defined('ABSPATH') or die();

const MU_DISALLOWED_USERNAMES = [
    'admin',
    'administrator',
    'root',
    'test',
    'demo',
    'user',
    'wordpress',
    'wp',
    'webmaster',
    'support',
    'info',
    'contact',
    'mail',
    'email',
    'postmaster',
    'hostmaster',
    'owner',
    'manager',
    'moderator',
    'editor',
    'author',
];

/**
 * Return true if the given username is disallowed.
 */
function mu_is_weak_username(string $username): bool
{
    $disallowed = apply_filters('mu_disallowed_usernames', MU_DISALLOWED_USERNAMES);
    return in_array(strtolower(trim($username)), array_map('strtolower', $disallowed), true);
}

// wp-login.php?action=register and wp_create_user()
add_filter(
    'registration_errors',
    static function (WP_Error $errors, string $sanitized_user_login, string $user_email): WP_Error {
        if (mu_is_weak_username($sanitized_user_login)) {
            $errors->add(
                'invalid_username',
                __('<strong>Error:</strong> That username is not allowed. Please choose a different one.', 'disallow-weak-usernames')
            );
        }
        return $errors;
    },
    10,
    3
);

// Admin user creation / profile update
add_action(
    'user_profile_update_errors',
    static function (WP_Error $errors, bool $update, stdObject $user) {
        if (!$update && mu_is_weak_username($user->user_login)) {
            $errors->add(
                'invalid_username',
                __('<strong>Error:</strong> That username is not allowed.', 'disallow-weak-usernames')
            );
        }
    },
    10,
    3
);

// validate_username filter (covers REST API and programmatic creation)
add_filter(
    'validate_username',
    static function (bool $valid, string $username): bool {
        if (mu_is_weak_username($username)) {
            return false;
        }
        return $valid;
    },
    10,
    2
);
