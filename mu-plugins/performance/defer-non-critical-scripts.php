<?php

/**
 * Defer non-critical scripts
 *
 * Plugin name:       Defer Non-Critical Scripts
 * Plugin URI:        https://openwpclub.com
 * Description:       Adds the defer attribute to enqueued scripts not in the exclude list. Improves Time to Interactive without editing theme files. Extend the exclude list via the 'mu_defer_exclude_handles' filter.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       defer-non-critical-scripts
 */

defined('ABSPATH') or die();

// Never defer scripts in the admin or during cron/REST requests
if (is_admin() || (defined('DOING_CRON') && DOING_CRON) || (defined('REST_REQUEST') && REST_REQUEST)) {
    return;
}

add_filter(
    'script_loader_tag',
    static function (string $tag, string $handle, string $src): string {
        // Already has defer or async
        if (str_contains($tag, 'defer') || str_contains($tag, 'async')) {
            return $tag;
        }

        $exclude = apply_filters('mu_defer_exclude_handles', [
            'jquery',
            'jquery-core',
            'jquery-migrate',
        ]);

        if (in_array($handle, $exclude, true)) {
            return $tag;
        }

        return str_replace(' src=', ' defer src=', $tag);
    },
    10,
    3
);
