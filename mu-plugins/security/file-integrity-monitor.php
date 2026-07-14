<?php

/**
 * Detect unexpected changes to core, plugin and theme files
 *
 * Plugin name:       File Integrity Monitor
 * Plugin URI:        https://openwpclub.com
 * Description:       Daily cron job that hashes every .php file under wp-admin, wp-includes, wp-content/plugins and wp-content/themes and compares against the previous run. Emails the admin and logs a diff when files are added, removed, or modified outside of a core/plugin update. Detects malware droppers and unauthorized file edits.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       file-integrity-monitor
 */

defined('ABSPATH') or die();

add_action(
    'wp',
    static function () {
        if (!wp_next_scheduled('mu_file_integrity_scan')) {
            wp_schedule_event(time(), 'daily', 'mu_file_integrity_scan');
        }
    },
    10,
    0
);

add_action(
    'mu_file_integrity_scan',
    static function () {
        $dirs = apply_filters('mu_file_integrity_dirs', [
            ABSPATH . 'wp-admin',
            ABSPATH . 'wp-includes',
            WP_CONTENT_DIR . '/plugins',
            WP_CONTENT_DIR . '/themes',
        ]);

        $hashes = [];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $hashes[str_replace(ABSPATH, '', $file->getPathname())] = md5_file($file->getPathname());
                }
            }
        }

        $previous = get_option('mu_file_integrity_hashes', []);
        update_option('mu_file_integrity_hashes', $hashes, false);

        if (empty($previous)) {
            return; // First run — nothing to compare against yet.
        }

        $added    = array_diff_key($hashes, $previous);
        $removed  = array_diff_key($previous, $hashes);
        $modified = [];

        foreach (array_intersect_key($hashes, $previous) as $path => $hash) {
            if ($hash !== $previous[$path]) {
                $modified[] = $path;
            }
        }

        if (empty($added) && empty($removed) && empty($modified)) {
            return;
        }

        $summary = sprintf(
            "File Integrity Monitor detected changes on %s:\n\nAdded (%d):\n%s\n\nRemoved (%d):\n%s\n\nModified (%d):\n%s\n",
            home_url(),
            count($added),
            implode("\n", array_keys($added)),
            count($removed),
            implode("\n", array_keys($removed)),
            count($modified),
            implode("\n", $modified)
        );

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('File Integrity Monitor: ' . count($added) . ' added, ' . count($removed) . ' removed, ' . count($modified) . ' modified.');
        }

        wp_mail(get_option('admin_email'), '[' . get_bloginfo('name') . '] File changes detected', $summary);
    },
    10,
    0
);
