<?php

/**
 * Harden Secure/HttpOnly/SameSite flags on outgoing cookies
 *
 * Plugin name:       Harden Cookie Flags
 * Plugin URI:        https://openwpclub.com
 * Description:       Adds a SameSite attribute (default Lax) and the Secure flag over HTTPS to every cookie set during the request, including WordPress's auth and logged-in cookies, without touching core's auth cookie logic. Configure via the 'mu_cookie_samesite' filter.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       harden-cookie-flags
 */

defined('ABSPATH') or die();

/**
 * WordPress core sets the auth/logged-in cookies directly via setcookie()
 * with no SameSite support and no filter reaching that call. Rewriting the
 * queued Set-Cookie headers right before they're sent is the only reliable
 * way to add the attribute to cookies we don't control the origin of.
 *
 * Note: PHP allows only one header_register_callback() per request. If a
 * theme or plugin registers its own callback after this one (mu-plugins
 * load first, so that is the common case), it replaces ours.
 */
if (function_exists('header_register_callback')) {
    header_register_callback(
        static function () {
            $samesite = (string) apply_filters('mu_cookie_samesite', 'Lax');
            if (!in_array($samesite, ['Lax', 'Strict', 'None'], true)) {
                $samesite = 'Lax';
            }
            if ('None' === $samesite && !is_ssl()) {
                // SameSite=None cookies are rejected by modern browsers without Secure.
                $samesite = 'Lax';
            }

            $cookie_headers = [];
            foreach (headers_list() as $header) {
                if (0 === stripos($header, 'Set-Cookie:')) {
                    $cookie_headers[] = trim(substr($header, strlen('Set-Cookie:')));
                }
            }

            if (!$cookie_headers) {
                return;
            }

            header_remove('Set-Cookie');

            foreach ($cookie_headers as $cookie) {
                if (false === stripos($cookie, 'samesite=')) {
                    $cookie .= '; SameSite=' . $samesite;
                }
                if (is_ssl() && false === stripos($cookie, 'secure')) {
                    $cookie .= '; Secure';
                }
                header('Set-Cookie: ' . $cookie, false);
            }
        }
    );
}
