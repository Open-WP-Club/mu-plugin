<?php

/**
 * Rate-limit password reset requests
 *
 * Plugin name:       Limit Password Reset Attempts
 * Plugin URI:        https://openwpclub.com
 * Description:       Blocks an IP address after 5 password reset requests within an hour, preventing reset-form spam and mail-bombing of targeted accounts. Independent of Limit Login Attempts, which only covers failed logins. Reuses its 'mu_get_user_ip' filter for sites behind a trusted reverse proxy.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       limit-password-reset-attempts
 */

defined('ABSPATH') or die();

define('MU_PWRESET_MAX_ATTEMPTS', 5);
define('MU_PWRESET_WINDOW', HOUR_IN_SECONDS);

/**
 * Get the requester's IP address.
 *
 * Defaults to REMOTE_ADDR to prevent spoofing via forged headers. Sites
 * behind a trusted reverse proxy can override via the 'mu_get_user_ip'
 * filter — the same one used by Limit Login Attempts, so one filter
 * configures both.
 */
function mu_pwreset_get_ip()
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ip = apply_filters('mu_get_user_ip', $ip);
    $ip = filter_var(trim($ip), FILTER_VALIDATE_IP);

    return $ip ?: '0.0.0.0';
}

function mu_pwreset_transient_key($ip)
{
    return 'mu_pwreset_attempts_' . md5($ip);
}

add_action(
    'lostpassword_post',
    static function ($errors) {
        $ip  = mu_pwreset_get_ip();
        $key = mu_pwreset_transient_key($ip);

        $attempts = (int) get_transient($key);
        $max      = (int) apply_filters('mu_pwreset_max_attempts', MU_PWRESET_MAX_ATTEMPTS);

        if ($attempts >= $max) {
            $errors->add(
                'mu_pwreset_rate_limited',
                '<strong>Error</strong>: Too many password reset requests from this IP address. Please try again later.'
            );
            return;
        }

        $window = (int) apply_filters('mu_pwreset_window', MU_PWRESET_WINDOW);
        set_transient($key, $attempts + 1, $window);
    },
    10,
    1
);
