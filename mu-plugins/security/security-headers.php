<?php

/**
 * Send baseline security-related HTTP headers
 *
 * Plugin name:       Security Headers
 * Plugin URI:        https://openwpclub.com
 * Description:       Sends X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy, and (over HTTPS) Strict-Transport-Security headers on every response. Reduces clickjacking, MIME-sniffing, and referrer-leak risk with no configuration. Skips the frontend if a caching/CDN layer already sets these.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.1.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       security-headers
 */

defined('ABSPATH') or die();

add_action(
    'send_headers',
    static function () {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        // Only sent over HTTPS: telling browsers to remember HTTP is unsafe here
        // would be actively wrong on a site not fully served over TLS.
        if (is_ssl()) {
            $hsts = apply_filters('mu_hsts_header', 'max-age=31536000; includeSubDomains');
            if ($hsts) {
                header('Strict-Transport-Security: ' . $hsts);
            }
        }
    }
);
