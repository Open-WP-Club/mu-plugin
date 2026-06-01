<?php

/**
 * Preconnect resource hints
 *
 * Plugin name:       Preconnect Resource Hints
 * Plugin URI:        https://openwpclub.com
 * Description:       Outputs <link rel="preconnect"> tags for configurable external origins. Reduces round-trip latency before the first byte of external resources. Configure via the 'mu_preconnect_origins' filter.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       preconnect-resource-hints
 */

defined('ABSPATH') or die();

/**
 * Default origins — override or extend via the filter:
 *
 *   add_filter('mu_preconnect_origins', function($origins) {
 *       $origins[] = ['url' => 'https://cdn.example.com', 'crossorigin' => false];
 *       return $origins;
 *   });
 *
 * Each entry: ['url' => 'https://...', 'crossorigin' => true|false]
 */
add_action(
    'wp_head',
    static function () {
        $origins = apply_filters('mu_preconnect_origins', [
            ['url' => 'https://fonts.googleapis.com', 'crossorigin' => true],
            ['url' => 'https://fonts.gstatic.com',   'crossorigin' => true],
        ]);

        foreach ($origins as $origin) {
            $url         = esc_url($origin['url'] ?? '');
            $crossorigin = !empty($origin['crossorigin']) ? ' crossorigin' : '';
            if ($url) {
                echo "<link rel=\"preconnect\" href=\"{$url}\"{$crossorigin}>\n";
            }
        }
    },
    1,
    0
);
