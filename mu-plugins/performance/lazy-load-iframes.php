<?php

/**
 * Add lazy loading to iframes in post content
 *
 * Plugin name:       Lazy Load Iframes
 * Plugin URI:        https://openwpclub.com
 * Description:       Adds loading="lazy" to iframes in post content (YouTube embeds, maps, etc.), deferring off-screen requests until the user scrolls near them.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       lazy-load-iframes
 */

defined('ABSPATH') or die();

add_filter(
    'the_content',
    static function ($content) {
        if (is_admin() || empty($content) || strpos($content, '<iframe') === false) {
            return $content;
        }

        return preg_replace_callback(
            '/<iframe\b([^>]*)>/i',
            static function ($matches) {
                $attrs = $matches[1];

                if (stripos($attrs, 'loading=') === false) {
                    $attrs .= ' loading="lazy"';
                }

                return '<iframe' . $attrs . '>';
            },
            $content
        );
    },
    10,
    1
);
