<?php

/**
 * Stop Gravatar requests to prevent third-party tracking
 *
 * Plugin name:       Disable Gravatar
 * Plugin URI:        https://openwpclub.com
 * Description:       Replaces all Gravatar avatar requests with the local mystery-person image so no request is sent to gravatar.com. Prevents commenter/user emails being hashed and sent to a third party without consent (GDPR).
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-gravatar
 */

defined('ABSPATH') or die();

// Inline grey-silhouette SVG — no HTTP request, ever.
define('MU_GRAVATAR_FALLBACK', 'data:image/svg+xml;base64,' . base64_encode(
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="#ddd"/><circle cx="50" cy="38" r="18" fill="#aaa"/><ellipse cx="50" cy="88" rx="32" ry="24" fill="#aaa"/></svg>'
));

add_filter(
    'get_avatar_url',
    static function ($url) {
        return strpos($url, 'gravatar.com') !== false ? MU_GRAVATAR_FALLBACK : $url;
    }
);
