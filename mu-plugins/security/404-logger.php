<?php

/**
 * Log 404 errors for monitoring
 *
 * Plugin name:       404 Logger
 * Plugin URI:        https://openwpclub.com
 * Description:       Logs 404 errors to a file for security monitoring and broken link detection.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       404-logger
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Log 404 errors to a file
 *
 * Logs are stored in wp-content/404-logs/ directory
 * with daily rotation.
 */
add_action(
    'template_redirect',
    static function () {
        if (!is_404()) {
            return;
        }

        // Get log directory
        $log_dir = WP_CONTENT_DIR . '/404-logs';

        // Create directory if it doesn't exist
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);

            // Add .htaccess to protect logs
            file_put_contents(
                $log_dir . '/.htaccess',
                "Order deny,allow\nDeny from all"
            );

            // Add index.php for additional protection
            file_put_contents(
                $log_dir . '/index.php',
                '<?php // Silence is golden'
            );
        }

        // Daily log file
        $log_file = $log_dir . '/404-' . date('Y-m-d') . '.log';

        // Gather information
        $timestamp = date('Y-m-d H:i:s');
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? 'Direct';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

        // Sanitize IP (take first if multiple)
        if (strpos($ip, ',') !== false) {
            $ip = trim(explode(',', $ip)[0]);
        }

        // Format log entry
        $log_entry = sprintf(
            "[%s] IP: %s | URI: %s | Referer: %s | UA: %s\n",
            $timestamp,
            $ip,
            $request_uri,
            $referer,
            substr($user_agent, 0, 200)
        );

        // Write to log file
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

        // Cleanup old logs (keep 30 days)
        static $cleanup_done = false;
        if (!$cleanup_done) {
            $cleanup_done = true;
            $files = glob($log_dir . '/404-*.log');
            $threshold = strtotime('-30 days');

            foreach ($files as $file) {
                if (filemtime($file) < $threshold) {
                    @unlink($file);
                }
            }
        }
    },
    1
);

/**
 * Add dashboard widget showing recent 404s
 */
add_action(
    'wp_dashboard_setup',
    static function () {
        if (!current_user_can('manage_options')) {
            return;
        }

        wp_add_dashboard_widget(
            '404_logger_widget',
            '404 Errors (Last 24h)',
            static function () {
                $log_file = WP_CONTENT_DIR . '/404-logs/404-' . date('Y-m-d') . '.log';

                if (!file_exists($log_file)) {
                    echo '<p>No 404 errors logged today.</p>';
                    return;
                }

                $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $count = count($lines);
                $recent = array_slice($lines, -10);

                echo '<p><strong>' . esc_html($count) . '</strong> errors today.</p>';

                if (!empty($recent)) {
                    echo '<div style="max-height: 200px; overflow-y: auto; font-size: 11px; font-family: monospace;">';
                    foreach (array_reverse($recent) as $line) {
                        // Extract URI from log line
                        if (preg_match('/URI: ([^\|]+)/', $line, $matches)) {
                            echo '<div style="padding: 2px 0; border-bottom: 1px solid #eee;">' . esc_html(trim($matches[1])) . '</div>';
                        }
                    }
                    echo '</div>';
                }
            }
        );
    }
);
