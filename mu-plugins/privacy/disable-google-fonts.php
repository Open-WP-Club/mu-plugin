<?php

/**
 * Remove Google Fonts requests to prevent GDPR violations
 *
 * Plugin name:       Disable Google Fonts
 * Plugin URI:        https://openwpclub.com
 * Description:       Dequeues all Google Fonts stylesheets and removes preconnect hints to fonts.googleapis.com. EU courts have ruled that loading Google Fonts without prior consent violates GDPR.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-google-fonts
 */

defined('ABSPATH') or die();

/**
 * Dequeue any registered stylesheet whose src points to Google Fonts.
 * Runs late (priority 100) so themes and plugins have already enqueued their assets.
 */
$mu_remove_google_fonts = static function () {
    global $wp_styles;

    if (empty($wp_styles->registered)) {
        return;
    }

    foreach (array_keys($wp_styles->registered) as $handle) {
        $src = $wp_styles->registered[$handle]->src ?? '';

        if (
            strpos($src, 'fonts.googleapis.com') !== false
            || strpos($src, 'fonts.gstatic.com') !== false
        ) {
            wp_dequeue_style($handle);
            wp_deregister_style($handle);
        }
    }
};

add_action('wp_print_styles', $mu_remove_google_fonts, 100);
add_action('admin_print_styles', $mu_remove_google_fonts, 100);

// Remove Google Fonts preconnect / dns-prefetch resource hints
add_filter(
    'wp_resource_hints',
    static function ($hints, $relation_type) {
        if (!in_array($relation_type, ['preconnect', 'dns-prefetch'], true)) {
            return $hints;
        }

        return array_values(array_filter($hints, static function ($hint) {
            $url = is_array($hint) ? ($hint['href'] ?? '') : $hint;
            return strpos($url, 'fonts.googleapis.com') === false
                && strpos($url, 'fonts.gstatic.com') === false;
        }));
    },
    10,
    2
);
