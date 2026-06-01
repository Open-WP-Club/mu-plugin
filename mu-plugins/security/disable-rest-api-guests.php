<?php

/**
 * Disable REST API for unauthenticated users
 *
 * Plugin name:       Disable REST API for Guests
 * Plugin URI:        https://openwpclub.com
 * Description:       Blocks REST API access for unauthenticated users. Whitelisted routes (oEmbed) remain public. Use the 'mu_rest_guest_whitelist' filter to customise.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-rest-api-guests
 */

defined('ABSPATH') or die();

add_filter(
    'rest_authentication_errors',
    static function ($result) {
        if (!empty($result)) {
            return $result;
        }

        if (is_user_logged_in()) {
            return $result;
        }

        // Resolve current REST route from REQUEST_URI
        $rest_prefix  = trailingslashit(rest_get_url_prefix());
        $request_path = ltrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');

        if (strpos($request_path, $rest_prefix) !== 0) {
            return $result;
        }

        $route = '/' . substr($request_path, strlen($rest_prefix));

        $whitelist = apply_filters('mu_rest_guest_whitelist', [
            '/oembed/1.0/embed',
            '/oembed/1.0/proxy',
        ]);

        foreach ($whitelist as $prefix) {
            if (strpos($route, $prefix) === 0) {
                return $result;
            }
        }

        return new WP_Error(
            'rest_not_logged_in',
            __('REST API access requires authentication.', 'disable-rest-api-guests'),
            ['status' => 401]
        );
    },
    10,
    1
);
