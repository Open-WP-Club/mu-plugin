<?php

/**
 * Force strong passwords
 *
 * Plugin name:       Force Strong Passwords
 * Plugin URI:        https://openwpclub.com
 * Description:       Rejects passwords shorter than 12 characters or missing uppercase, lowercase, number, and special character. Applied to profile updates and registration.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       force-strong-passwords
 */

defined('ABSPATH') or die();

/**
 * Validate password strength and append errors to the provided error object.
 */
function mu_validate_password_strength(WP_Error $errors, string $password): void
{
    if (empty($password)) {
        return;
    }

    $min_length = (int) apply_filters('mu_password_min_length', 12);
    $messages   = [];

    if (strlen($password) < $min_length) {
        $messages[] = sprintf(__('at least %d characters', 'force-strong-passwords'), $min_length);
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $messages[] = __('an uppercase letter', 'force-strong-passwords');
    }
    if (!preg_match('/[a-z]/', $password)) {
        $messages[] = __('a lowercase letter', 'force-strong-passwords');
    }
    if (!preg_match('/[0-9]/', $password)) {
        $messages[] = __('a number', 'force-strong-passwords');
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $messages[] = __('a special character', 'force-strong-passwords');
    }

    if (!empty($messages)) {
        $errors->add(
            'weak_password',
            sprintf(
                __('<strong>Weak password.</strong> Your password must contain %s.', 'force-strong-passwords'),
                implode(', ', $messages)
            )
        );
    }
}

// Profile / user edit page
add_action(
    'user_profile_update_errors',
    static function (WP_Error $errors, bool $update, stdObject $user) {
        $password = $_POST['pass1'] ?? '';
        mu_validate_password_strength($errors, $password);
    },
    10,
    3
);

// wp-login.php?action=register
add_filter(
    'registration_errors',
    static function (WP_Error $errors, string $sanitized_user_login, string $user_email): WP_Error {
        $password = $_POST['user_pass'] ?? $_POST['pass1'] ?? '';
        mu_validate_password_strength($errors, $password);
        return $errors;
    },
    10,
    3
);
