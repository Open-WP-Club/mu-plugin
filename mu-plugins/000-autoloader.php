<?php
/**
 * Plugin Name: MU-Plugins Autoloader
 * Description: Automatically loads all PHP files from all subdirectories
 * Version: 2.0.0
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
 * Automatically loads ALL PHP files from ALL subdirectories.
 * No configuration needed - just delete what you don't want.
 *
 * Files are prefixed with 000- to ensure the autoloader loads first.
 */
class MU_Plugins_Autoloader {

    /**
     * Initialize the autoloader
     */
    public static function init() {
        self::load_plugins();

        // Add admin comment to show which mu-plugins are loaded
        if (is_admin()) {
            add_action('admin_footer', [__CLASS__, 'show_loaded_plugins']);
        }
    }

    /**
     * Load all PHP files from all subdirectories
     */
    private static function load_plugins() {
        $base_dir = __DIR__;
        $loaded = [];

        // Get all subdirectories
        $directories = glob($base_dir . '/*', GLOB_ONLYDIR);

        if (!$directories) {
            return;
        }

        foreach ($directories as $directory) {
            $files = glob($directory . '/*.php');

            if (!$files) {
                continue;
            }

            $category = basename($directory);

            foreach ($files as $file) {
                require_once $file;
                $loaded[] = $category . '/' . basename($file);
            }
        }

        // Store loaded plugins for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            update_option('mu_plugins_loaded', $loaded, false);
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

        if (empty($loaded)) {
            return;
        }

        echo '<!-- MU-Plugins: ' . count($loaded) . ' loaded -->';
    }
}

// Initialize the autoloader
MU_Plugins_Autoloader::init();
