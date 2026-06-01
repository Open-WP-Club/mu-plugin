<?php

/**
 * Trusted IP allowlist for wp-admin and wp-login.php
 *
 * Plugin name:       Trusted IP Allowlist
 * Plugin URI:        https://openwpclub.com
 * Description:       Blocks access to wp-admin and wp-login.php for IPs not in the allowlist. Define MU_TRUSTED_IPS as an array of allowed CIDR ranges or exact IPs. Only use on sites with a static management IP.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       trusted-ip-allowlist
 */

defined('ABSPATH') or die();

/**
 * Define trusted IPs/CIDRs in wp-config.php:
 *
 *   define('MU_TRUSTED_IPS', ['203.0.113.10', '198.51.100.0/24']);
 *
 * Leave undefined or empty to disable the restriction.
 */

if (!defined('MU_TRUSTED_IPS') || empty(MU_TRUSTED_IPS)) {
    return;
}

/**
 * Check if an IP is within a CIDR range.
 */
function mu_ip_in_cidr(string $ip, string $cidr): bool
{
    if (!str_contains($cidr, '/')) {
        return $ip === $cidr;
    }

    [$subnet, $bits] = explode('/', $cidr, 2);
    $bits     = (int) $bits;
    $ip_long  = ip2long($ip);
    $sub_long = ip2long($subnet);

    if ($ip_long === false || $sub_long === false) {
        return false;
    }

    $mask = $bits === 0 ? 0 : (~0 << (32 - $bits));
    return ($ip_long & $mask) === ($sub_long & $mask);
}

/**
 * Check if the current REMOTE_ADDR is in the trusted list.
 */
function mu_is_trusted_ip(): bool
{
    $ip      = $_SERVER['REMOTE_ADDR'] ?? '';
    $trusted = apply_filters('mu_trusted_ips', MU_TRUSTED_IPS);

    foreach ($trusted as $cidr) {
        if (mu_ip_in_cidr($ip, $cidr)) {
            return true;
        }
    }

    return false;
}

add_action(
    'init',
    static function () {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $is_admin    = str_contains($request_uri, '/wp-admin');
        $is_login    = str_contains($request_uri, '/wp-login.php');

        if (!$is_admin && !$is_login) {
            return;
        }

        // Always allow AJAX and cron even from wp-admin path
        if (wp_doing_ajax() || (defined('DOING_CRON') && DOING_CRON)) {
            return;
        }

        if (!mu_is_trusted_ip()) {
            http_response_code(403);
            exit('Access denied.');
        }
    },
    1,
    0
);
