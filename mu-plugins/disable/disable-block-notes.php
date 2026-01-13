<?php

/**
 * Disable WordPress Block Editor Collaboration Notes
 *
 * Plugin name:       Disable Block Notes
 * Plugin URI:        https://openwpclub.com
 * Description:       Disables the collaboration notes feature in the block editor introduced in WordPress 6.9.
 * Requires at least: 6.9
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-block-notes
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Disable collaboration notes feature
 *
 * Block notes allow inline comments and collaboration
 * within the Gutenberg editor. Disable if not needed.
 */
add_filter('block_editor_settings_all', static function ($settings) {
    // Disable collaboration notes
    $settings['allowCollaborativeNotes'] = false;

    return $settings;
}, 10, 1);

/**
 * Remove block notes scripts and styles
 */
add_action(
    'admin_enqueue_scripts',
    static function () {
        wp_dequeue_script('wp-block-notes');
        wp_dequeue_style('wp-block-notes');
    },
    100
);

/**
 * Disable block notes REST API endpoint
 */
add_filter(
    'rest_endpoints',
    static function ($endpoints) {
        // Remove block notes endpoints if they exist
        $routes_to_remove = [
            '/wp/v2/block-notes',
            '/wp/v2/block-notes/(?P<id>[\d]+)',
        ];

        foreach ($routes_to_remove as $route) {
            if (isset($endpoints[$route])) {
                unset($endpoints[$route]);
            }
        }

        return $endpoints;
    },
    10,
    1
);
