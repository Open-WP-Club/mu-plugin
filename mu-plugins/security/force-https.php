<?php

/**
 * Force HTTPS across the frontend, admin, and login
 *
 * Plugin name:       Force HTTPS
 * Plugin URI:        https://openwpclub.com
 * Description:       Redirects any HTTP request to HTTPS with a 301, covering the frontend, wp-admin, and wp-login.php. Only activates when the site's home URL is already configured for https, so an intentionally HTTP-only environment is left untouched. Behind a reverse proxy that terminates TLS, enable the 'mu_force_https_trust_proxy' filter to trust the X-Forwarded-Proto header.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       force-https
 */

defined('ABSPATH') or die();

add_action(
    'init',
    static function () {
        if ('https' !== wp_parse_url(home_url(), PHP_URL_SCHEME)) {
            return;
        }

        if ((defined('WP_CLI') && WP_CLI) || (defined('DOING_CRON') && DOING_CRON)) {
            return;
        }

        $is_ssl = is_ssl();
        if (!$is_ssl && apply_filters('mu_force_https_trust_proxy', false)) {
            $is_ssl = 'https' === ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
        }

        if ($is_ssl) {
            return;
        }

        $host = isset($_SERVER['HTTP_HOST'])
            ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', wp_unslash($_SERVER['HTTP_HOST']))
            : wp_parse_url(home_url(), PHP_URL_HOST);
        $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';

        wp_redirect('https://' . $host . $uri, 301);
        exit;
    },
    1
);
