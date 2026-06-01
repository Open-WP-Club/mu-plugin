<?php

/**
 * Disable query string versioning on assets
 *
 * Plugin name:       Disable Query String Versioning
 * Plugin URI:        https://openwpclub.com
 * Description:       Strips ?ver= query strings from enqueued script and style URLs so CDN and proxy caches can cache assets without per-version cache misses.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-query-string-versioning
 */

defined('ABSPATH') or die();

/**
 * Remove the ver= query arg from a URL.
 */
function mu_remove_ver_query_string(string $src): string
{
    if (str_contains($src, '?ver=') || str_contains($src, '&ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}

add_filter('script_loader_src', 'mu_remove_ver_query_string', 15, 1);
add_filter('style_loader_src',  'mu_remove_ver_query_string', 15, 1);
