<?php

/**
 * Disable WordPress Command Palette
 *
 * Plugin name:       Disable Command Palette
 * Plugin URI:        https://openwpclub.com
 * Description:       Disables the Command Palette (Ctrl+K / Cmd+K) feature in the WordPress admin.
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-command-palette
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Disable command palette in block editor
 *
 * The command palette (Ctrl+K / Cmd+K) was introduced in WP 6.3
 * and provides quick access to editor commands and navigation.
 */
add_filter('block_editor_settings_all', static function ($settings) {
    // Disable command palette
    $settings['enableCommandPalette'] = false;

    return $settings;
}, 10, 1);

/**
 * Remove command palette scripts
 */
add_action(
    'admin_enqueue_scripts',
    static function () {
        wp_dequeue_script('wp-commands');
        wp_dequeue_script('wp-command-palette');
    },
    100
);

/**
 * Remove command palette from admin
 *
 * This prevents the command center from loading entirely
 */
add_action(
    'admin_init',
    static function () {
        remove_action('in_admin_header', 'wp_admin_bar_render_command_center');
        remove_action('admin_enqueue_scripts', 'wp_enqueue_command_palette');
    },
    100
);
