<?php

/**
 * Force strong passwords for WooCommerce customers
 *
 * Plugin name:       Force WooCommerce Strong Passwords
 * Plugin URI:        https://openwpclub.com
 * Description:       Applies password strength validation to WooCommerce registration and checkout forms. WooCommerce skips WordPress's default strength rules for customers; this closes that gap.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       force-wc-strong-passwords
 */

defined('ABSPATH') or die();

if (!defined('WC_PLUGIN_FILE')) {
    return;
}

/**
 * Validate WooCommerce password and return error message or empty string.
 */
function mu_wc_check_password_strength(string $password): string
{
    $min    = (int) apply_filters('mu_password_min_length', 12);
    $errors = [];

    if (strlen($password) < $min) {
        $errors[] = sprintf(__('at least %d characters', 'force-wc-strong-passwords'), $min);
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = __('an uppercase letter', 'force-wc-strong-passwords');
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = __('a lowercase letter', 'force-wc-strong-passwords');
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = __('a number', 'force-wc-strong-passwords');
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = __('a special character', 'force-wc-strong-passwords');
    }

    if (empty($errors)) {
        return '';
    }

    return sprintf(
        __('Your password must contain %s.', 'force-wc-strong-passwords'),
        implode(', ', $errors)
    );
}

// My Account / registration page
add_filter(
    'woocommerce_registration_errors',
    static function (WP_Error $errors, string $username, string $email): WP_Error {
        $password = $_POST['password'] ?? '';
        $message  = mu_wc_check_password_strength($password);
        if ($message) {
            $errors->add('weak_password', $message);
        }
        return $errors;
    },
    10,
    3
);

// Checkout page
add_action(
    'woocommerce_checkout_process',
    static function () {
        if (get_option('woocommerce_registration_generate_password') === 'yes') {
            return; // WC generates the password — nothing to validate
        }

        $password = $_POST['account_password'] ?? '';
        if (empty($password)) {
            return;
        }

        $message = mu_wc_check_password_strength($password);
        if ($message) {
            wc_add_notice($message, 'error');
        }
    },
    10,
    0
);
