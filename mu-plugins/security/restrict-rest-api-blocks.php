<?php

/**
 * Restrict REST API Block Endpoints
 *
 * Plugin name:       Restrict REST API Blocks
 * Plugin URI:        https://openwpclub.com
 * Description:       Limits access to block-related REST API endpoints to authenticated users only.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       restrict-rest-api-blocks
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Restrict access to block-related REST API endpoints
 *
 * Block endpoints can expose site structure information
 * and should be limited to authenticated users.
 */
add_filter(
    'rest_pre_dispatch',
    static function ($result, $server, $request) {
        $route = $request->get_route();

        // Block-related endpoints to restrict
        $restricted_patterns = [
            '/wp/v2/blocks',
            '/wp/v2/block-types',
            '/wp/v2/block-renderer',
            '/wp/v2/block-patterns',
            '/wp/v2/block-pattern-categories',
            '/wp/v2/block-directory',
            '/wp/v2/global-styles',
            '/wp/v2/navigation',
            '/wp/v2/templates',
            '/wp/v2/template-parts',
        ];

        // Check if current route matches restricted patterns
        foreach ($restricted_patterns as $pattern) {
            if (strpos($route, $pattern) === 0) {
                // Only allow authenticated users with edit capability
                if (!current_user_can('edit_posts')) {
                    return new WP_Error(
                        'rest_forbidden',
                        __('You do not have permission to access this endpoint.', 'restrict-rest-api-blocks'),
                        ['status' => 403]
                    );
                }
                break;
            }
        }

        return $result;
    },
    10,
    3
);

/**
 * Remove block endpoints from REST API index for unauthenticated users
 *
 * This hides the existence of these endpoints from public discovery
 */
add_filter(
    'rest_endpoints',
    static function ($endpoints) {
        if (is_user_logged_in() && current_user_can('edit_posts')) {
            return $endpoints;
        }

        // Remove block-related endpoints from index
        $patterns_to_hide = [
            '/wp/v2/blocks',
            '/wp/v2/block-types',
            '/wp/v2/block-renderer',
            '/wp/v2/block-patterns',
            '/wp/v2/block-pattern-categories',
            '/wp/v2/block-directory',
        ];

        foreach ($endpoints as $route => $data) {
            foreach ($patterns_to_hide as $pattern) {
                if (strpos($route, $pattern) === 0) {
                    unset($endpoints[$route]);
                    break;
                }
            }
        }

        return $endpoints;
    },
    10,
    1
);
