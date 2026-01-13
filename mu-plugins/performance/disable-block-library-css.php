<?php

/**
 * Disable WordPress Block Library CSS
 *
 * Plugin name:       Disable Block Library CSS
 * Plugin URI:        https://openwpclub.com
 * Description:       Removes Gutenberg block library CSS from frontend if not using blocks, reducing page weight.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-block-library-css
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Remove block library CSS from frontend
 *
 * This removes:
 * - wp-block-library (core block styles)
 * - wp-block-library-theme (theme-specific block styles)
 * - wc-blocks-style (WooCommerce blocks)
 * - global-styles (theme.json styles)
 */
add_action(
    'wp_enqueue_scripts',
    static function () {
        // Core block library styles
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');

        // Classic theme styles
        wp_dequeue_style('classic-theme-styles');

        // Global styles from theme.json
        wp_dequeue_style('global-styles');

        // WooCommerce block styles (if present)
        wp_dequeue_style('wc-blocks-style');
        wp_dequeue_style('wc-blocks-vendors-style');
    },
    100
);

/**
 * Remove inline block styles
 *
 * WordPress 5.9+ adds inline CSS for global styles
 */
remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
