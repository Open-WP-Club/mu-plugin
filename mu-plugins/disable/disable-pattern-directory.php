<?php

/**
 * Disable Gutenberg remote pattern directory
 *
 * Plugin name:       Disable Pattern Directory
 * Plugin URI:        https://openwpclub.com
 * Description:       Prevents the block editor from fetching block patterns from api.wordpress.org on every editor load. Eliminates the external HTTP request and brief pattern-loading delay.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-pattern-directory
 */

defined('ABSPATH') or die();

// Primary toggle — stops the remote pattern API call entirely
add_filter('should_load_remote_block_patterns', '__return_false');

// Belt-and-suspenders: clear patterns from block editor settings
add_filter(
    'block_editor_settings_all',
    static function (array $settings): array {
        $settings['__experimentalBlockPatterns'] = [];
        return $settings;
    },
    PHP_INT_MAX,
    1
);
