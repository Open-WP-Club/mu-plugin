<?php

/**
 * REST API Rate Limiting
 *
 * Plugin name:       REST API Rate Limiting
 * Plugin URI:        https://openwpclub.com
 * Description:       Limits REST API requests per IP to prevent abuse. 60 requests/minute for guests, 200 for authenticated users.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.1.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       rest-api-rate-limiting
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Configuration - adjust these values based on your needs
 */
define('MU_RATE_LIMIT_GUEST', 60);           // Requests per window for guests
define('MU_RATE_LIMIT_AUTHENTICATED', 200);  // Requests per window for logged-in users
define('MU_RATE_LIMIT_WINDOW', 60);          // Window in seconds (1 minute)

/**
 * Whitelist specific endpoints from rate limiting
 * These patterns are matched against the REST route
 */
function mu_rate_limit_get_whitelist()
{
    return apply_filters('mu_rate_limit_whitelist', [
        '/oembed/',           // oEmbed discovery
        '/wp-site-health/',   // Site health checks
    ]);
}

/**
 * Get client IP address with proxy support
 */
function mu_rate_limit_get_ip()
{
    $ip = '';

    // Check common proxy headers
    $headers = [
        'HTTP_CF_CONNECTING_IP',  // Cloudflare
        'HTTP_X_REAL_IP',         // Nginx proxy
        'HTTP_X_FORWARDED_FOR',   // Standard proxy
        'HTTP_CLIENT_IP',         // Shared internet
        'REMOTE_ADDR',            // Direct connection
    ];

    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            // X-Forwarded-For can contain multiple IPs, get the first one
            $ip = explode(',', $_SERVER[$header])[0];
            $ip = trim($ip);
            break;
        }
    }

    // Validate IP
    $ip = filter_var($ip, FILTER_VALIDATE_IP);

    return $ip ?: '0.0.0.0';
}

/**
 * Get transient key for rate limiting
 */
function mu_rate_limit_get_key($ip)
{
    return 'rest_rate_' . md5($ip);
}

/**
 * Check if route is whitelisted
 */
function mu_rate_limit_is_whitelisted($route)
{
    foreach (mu_rate_limit_get_whitelist() as $pattern) {
        if (strpos($route, $pattern) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * Apply rate limiting to REST API requests
 */
add_filter(
    'rest_pre_dispatch',
    static function ($result, $server, $request) {
        // Skip if already returning an error
        if (is_wp_error($result)) {
            return $result;
        }

        $route = $request->get_route();

        // Skip whitelisted routes
        if (mu_rate_limit_is_whitelisted($route)) {
            return $result;
        }

        $ip = mu_rate_limit_get_ip();
        $key = mu_rate_limit_get_key($ip);
        $is_authenticated = is_user_logged_in();

        // Determine rate limit based on auth status
        $limit = $is_authenticated ? MU_RATE_LIMIT_AUTHENTICATED : MU_RATE_LIMIT_GUEST;

        // Get current request data
        $data = get_transient($key);

        if ($data === false) {
            // First request in window
            $data = [
                'count' => 1,
                'start' => time(),
            ];
        } else {
            // Check if window has expired
            if ((time() - $data['start']) >= MU_RATE_LIMIT_WINDOW) {
                // Reset window
                $data = [
                    'count' => 1,
                    'start' => time(),
                ];
            } else {
                // Increment count
                $data['count']++;
            }
        }

        // Calculate remaining requests and reset time
        $remaining = max(0, $limit - $data['count']);
        $reset = $data['start'] + MU_RATE_LIMIT_WINDOW;

        // Store updated data
        set_transient($key, $data, MU_RATE_LIMIT_WINDOW);

        // Add rate limit headers to response
        add_filter(
            'rest_post_dispatch',
            static function ($response) use ($limit, $remaining, $reset) {
                if ($response instanceof WP_REST_Response) {
                    $response->header('X-RateLimit-Limit', $limit);
                    $response->header('X-RateLimit-Remaining', $remaining);
                    $response->header('X-RateLimit-Reset', $reset);
                }
                return $response;
            },
            10,
            1
        );

        // Check if rate limit exceeded
        if ($data['count'] > $limit) {
            $retry_after = $reset - time();

            // Log the rate limit hit
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                error_log("REST API rate limit exceeded for IP: {$ip} on route: {$route}");
            }

            // Return 429 Too Many Requests
            $response = new WP_REST_Response(
                [
                    'code'    => 'rate_limit_exceeded',
                    'message' => 'Too many requests. Please slow down.',
                    'data'    => [
                        'status'      => 429,
                        'retry_after' => $retry_after,
                    ],
                ],
                429
            );

            $response->header('X-RateLimit-Limit', $limit);
            $response->header('X-RateLimit-Remaining', 0);
            $response->header('X-RateLimit-Reset', $reset);
            $response->header('Retry-After', $retry_after);

            return $response;
        }

        return $result;
    },
    10,
    3
);

/**
 * Add admin notice about rate limiting being active
 */
add_action(
    'admin_notices',
    static function () {
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'dashboard' || !current_user_can('manage_options')) {
            return;
        }

        // Only show once
        if (get_option('mu_rate_limit_notice_dismissed')) {
            return;
        }

        echo '<div class="notice notice-info is-dismissible" id="rate-limit-notice" data-nonce="' . esc_attr(wp_create_nonce('rate_limit_nonce')) . '">
            <p>
                <strong>REST API Rate Limiting Active:</strong>
                ' . (int) MU_RATE_LIMIT_GUEST . ' requests/minute for guests,
                ' . (int) MU_RATE_LIMIT_AUTHENTICATED . ' for authenticated users.
            </p>
        </div>';

        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const notice = document.getElementById("rate-limit-notice");
                if (notice) {
                    notice.addEventListener("click", function(e) {
                        if (e.target.classList.contains("notice-dismiss")) {
                            fetch(ajaxurl, {
                                method: "POST",
                                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                                body: "action=dismiss_rate_limit_notice&nonce=" + notice.dataset.nonce
                            });
                        }
                    });
                }
            });
        </script>';
    },
    10,
    0
);

/**
 * Handle notice dismissal
 */
add_action(
    'wp_ajax_dismiss_rate_limit_notice',
    static function () {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'rate_limit_nonce')) {
            wp_die('Security check failed');
        }

        update_option('mu_rate_limit_notice_dismissed', true, false);
        wp_die();
    },
    10,
    0
);
