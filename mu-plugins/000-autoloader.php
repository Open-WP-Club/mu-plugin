<?php
/**
 * Plugin Name: MU-Plugins Autoloader
 * Description: Automatically loads all organized mu-plugins from categorized subdirectories
 * Version: 1.0.0
 * Author: OpenWP Club
 * Author URI: https://openwpclub.com
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * MU-Plugins Autoloader Class
 *
 * Loads all PHP files from organized subdirectories.
 * Files are prefixed with 000- to ensure it loads first.
 */
class MU_Plugins_Autoloader {

    /**
     * Categories to load
     * Set category to false to disable loading that entire category
     */
    private static $categories = [
        'security'     => true,  // Security & hardening plugins
        'performance'  => true,  // Performance optimization plugins
        'admin'        => true,  // Admin UI/UX improvements
        'media'        => true,  // Media handling plugins
        'cleanup'      => true,  // Cleanup & maintenance
        'disable'      => true,  // Feature disable plugins
        'maintenance'  => true,  // Maintenance mode & hosting
        'development'  => true,  // Development & debugging tools
    ];

    /**
     * Individual plugins to skip (filename without .php)
     * Example: ['disable-comments', 'simple-maintenance-mode']
     */
    private static $skip_plugins = [];

    /**
     * Initialize the autoloader
     */
    public static function init() {
        self::load_plugins();

        // Add admin column to show which mu-plugins are loaded
        if (is_admin()) {
            add_action('admin_footer', [__CLASS__, 'show_loaded_plugins']);
        }
    }

    /**
     * Load all plugins from enabled categories
     */
    private static function load_plugins() {
        $base_dir = __DIR__;
        $loaded = [];
        $skipped = [];

        foreach (self::$categories as $category => $enabled) {
            if (!$enabled) {
                continue;
            }

            $category_dir = $base_dir . '/' . $category;

            if (!is_dir($category_dir)) {
                continue;
            }

            $files = glob($category_dir . '/*.php');

            if (!$files) {
                continue;
            }

            foreach ($files as $file) {
                $plugin_name = basename($file, '.php');

                // Skip if plugin is in skip list
                if (in_array($plugin_name, self::$skip_plugins)) {
                    $skipped[] = $category . '/' . basename($file);
                    continue;
                }

                require_once $file;
                $loaded[] = $category . '/' . basename($file);
            }
        }

        // Store loaded plugins for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            update_option('mu_plugins_loaded', $loaded, false);
            update_option('mu_plugins_skipped', $skipped, false);
        }
    }

    /**
     * Show loaded plugins in admin footer (only for administrators)
     */
    public static function show_loaded_plugins() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $loaded = get_option('mu_plugins_loaded', []);
        $skipped = get_option('mu_plugins_skipped', []);

        if (empty($loaded) && empty($skipped)) {
            return;
        }

        $total = count($loaded);
        echo '<!-- MU-Plugins: ' . $total . ' loaded';

        if (!empty($skipped)) {
            echo ', ' . count($skipped) . ' skipped';
        }

        echo ' -->';
    }
}

// Initialize the autoloader
MU_Plugins_Autoloader::init();
