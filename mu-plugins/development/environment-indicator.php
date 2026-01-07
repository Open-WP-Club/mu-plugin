<?php

/**
 * Visual environment indicator in admin bar
 *
 * Plugin name:       Environment Indicator
 * Plugin URI:        https://openwpclub.com
 * Description:       Displays a visual indicator in the admin bar showing the current environment (Development/Staging/Production) to prevent editing the wrong site.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       environment-indicator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Environment_Indicator {

    /**
     * Environment configuration
     */
    private static $environments = [
        'local' => [
            'label' => 'LOCAL',
            'color' => '#00a32a',
            'bg_color' => '#d5f4e6'
        ],
        'development' => [
            'label' => 'DEV',
            'color' => '#007cba',
            'bg_color' => '#cce5f2'
        ],
        'staging' => [
            'label' => 'STAGING',
            'color' => '#f0b849',
            'bg_color' => '#fcf3d9'
        ],
        'production' => [
            'label' => 'LIVE',
            'color' => '#d63638',
            'bg_color' => '#f7dede'
        ]
    ];

    /**
     * Initialize the plugin
     */
    public static function init() {
        add_action('admin_bar_menu', [__CLASS__, 'add_admin_bar_item'], 100);
        add_action('admin_head', [__CLASS__, 'add_admin_styles']);
        add_action('wp_head', [__CLASS__, 'add_admin_styles']);
    }

    /**
     * Detect the current environment
     *
     * @return string Environment name (local, development, staging, production)
     */
    private static function detect_environment() {
        // Check for defined constant first
        if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE) {
            $env_type = WP_ENVIRONMENT_TYPE;
            if (array_key_exists($env_type, self::$environments)) {
                return $env_type;
            }
        }

        // Check wp_get_environment_type() (WordPress 5.5+)
        if (function_exists('wp_get_environment_type')) {
            $env_type = wp_get_environment_type();
            if (array_key_exists($env_type, self::$environments)) {
                return $env_type;
            }
        }

        // Fallback: Detect by URL patterns
        $host = $_SERVER['HTTP_HOST'] ?? '';

        // Local environment indicators
        if (preg_match('/localhost|\.local|\.test|127\.0\.0\.1|::1/i', $host)) {
            return 'local';
        }

        // Staging environment indicators
        if (preg_match('/staging|stg|dev-|test\.|demo\./i', $host)) {
            return 'staging';
        }

        // Development environment indicators
        if (preg_match('/dev\.|development/i', $host)) {
            return 'development';
        }

        // Default to production
        return 'production';
    }

    /**
     * Add environment indicator to admin bar
     */
    public static function add_admin_bar_item($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }

        $environment = self::detect_environment();
        $config = self::$environments[$environment];

        $wp_admin_bar->add_node([
            'id'    => 'environment-indicator',
            'title' => sprintf(
                '<span class="environment-indicator environment-%s">%s</span>',
                esc_attr($environment),
                esc_html($config['label'])
            ),
            'meta'  => [
                'title' => sprintf('Environment: %s', ucfirst($environment))
            ]
        ]);
    }

    /**
     * Add custom styles for the indicator
     */
    public static function add_admin_styles() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $environment = self::detect_environment();
        $config = self::$environments[$environment];

        ?>
        <style>
            .environment-indicator {
                display: inline-block;
                padding: 2px 8px !important;
                border-radius: 3px;
                font-size: 11px !important;
                font-weight: 600 !important;
                letter-spacing: 0.5px;
                color: <?php echo esc_attr($config['color']); ?> !important;
                background-color: <?php echo esc_attr($config['bg_color']); ?> !important;
                line-height: 1.4 !important;
            }

            #wpadminbar #wp-admin-bar-environment-indicator .ab-item {
                height: auto !important;
                padding: 6px 10px !important;
            }

            /* Pulse animation for non-production environments */
            <?php if ($environment !== 'production'): ?>
            .environment-indicator {
                animation: env-pulse 2s ease-in-out infinite;
            }

            @keyframes env-pulse {
                0%, 100% {
                    opacity: 1;
                }
                50% {
                    opacity: 0.7;
                }
            }
            <?php endif; ?>
        </style>
        <?php
    }
}

// Initialize the environment indicator
Environment_Indicator::init();
