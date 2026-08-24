<?php

/**
 * Plugin Name:       Security.txt Provider
 * Plugin URI:        https://openwpclub.com
 * Description:       Serves an RFC 9116 security.txt file when MU_SECURITY_CONTACT is configured in wp-config.php.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       security-txt
 */

defined('ABSPATH') || exit;

add_action('template_redirect', static function () {
    if (!defined('MU_SECURITY_CONTACT')) {
        return;
    }

    $request_path = (string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $home_url     = wp_parse_url(home_url('/'));
    $home_path    = rtrim((string) ($home_url['path'] ?? ''), '/');
    $target_paths = array_unique(['/.well-known/security.txt', $home_path . '/.well-known/security.txt']);

    if (!in_array(untrailingslashit($request_path), $target_paths, true)) {
        return;
    }

    $contacts = is_array(MU_SECURITY_CONTACT) ? MU_SECURITY_CONTACT : [MU_SECURITY_CONTACT];
    $lines = [];
    foreach ($contacts as $contact) {
        $contact = esc_url_raw(trim((string) $contact), ['https', 'mailto']);
        if ($contact) {
            $lines[] = 'Contact: ' . $contact;
        }
    }

    if (!$lines) {
        status_header(404);
        nocache_headers();
        exit;
    }

    $expires = defined('MU_SECURITY_EXPIRES') ? strtotime((string) MU_SECURITY_EXPIRES) : strtotime('+180 days');
    if (!$expires || $expires <= time()) {
        $expires = strtotime('+180 days');
    }

    $scheme = isset($home_url['scheme']) ? $home_url['scheme'] : 'https';
    $host   = isset($home_url['host']) ? $home_url['host'] : '';
    $port   = isset($home_url['port']) ? ':' . $home_url['port'] : '';
    $canonical = $host ? $scheme . '://' . $host . $port . '/.well-known/security.txt' : home_url('/.well-known/security.txt');
    $lines[] = 'Expires: ' . gmdate('Y-m-d\TH:i:s\Z', $expires);
    $lines[] = 'Canonical: ' . $canonical;

    if (defined('MU_SECURITY_POLICY')) {
        $policy = esc_url_raw((string) MU_SECURITY_POLICY, ['https']);
        if ($policy) {
            $lines[] = 'Policy: ' . $policy;
        }
    }

    $languages = defined('MU_SECURITY_LANGUAGES') ? sanitize_text_field((string) MU_SECURITY_LANGUAGES) : 'en';
    $lines[] = 'Preferred-Languages: ' . $languages;
    $lines = (array) apply_filters('mu_security_txt_lines', $lines);

    status_header(200);
    header('Content-Type: text/plain; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: public, max-age=3600');
    echo implode("\n", array_map('sanitize_text_field', $lines)) . "\n";
    exit;
}, 0);
