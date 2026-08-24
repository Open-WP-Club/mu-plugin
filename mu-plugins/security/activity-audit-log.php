<?php

/**
 * Plugin Name:       Activity Audit Log
 * Plugin URI:        https://openwpclub.com
 * Description:       Keeps a small privacy-aware audit trail of critical user, settings, login, and software update events.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       activity-audit-log
 */

defined('ABSPATH') || exit;

const MU_ACTIVITY_LOG_OPTION = 'mu_activity_audit_log';

/**
 * Add an audit event without storing option values, passwords, email addresses, or IPs.
 *
 * @param string $event   Stable event name.
 * @param array  $context Non-sensitive scalar context.
 */
function mu_activity_log_event($event, array $context = [])
{
    $entries = get_option(MU_ACTIVITY_LOG_OPTION, []);
    $entries = is_array($entries) ? $entries : [];
    $safe_context = [];

    foreach ($context as $key => $value) {
        if (is_scalar($value)) {
            $safe_context[sanitize_key($key)] = sanitize_text_field((string) $value);
        }
    }

    $entries[] = [
        'time'    => time(),
        'event'   => sanitize_key($event),
        'actor'   => get_current_user_id(),
        'context' => $safe_context,
    ];

    $limit = max(20, (int) apply_filters('mu_activity_log_limit', 100));
    update_option(MU_ACTIVITY_LOG_OPTION, array_slice($entries, -$limit), false);
}

add_action('wp_login', static function ($user_login, $user) {
    if ($user instanceof WP_User && user_can($user, 'manage_options')) {
        mu_activity_log_event('administrator_login', ['user_id' => $user->ID]);
    }
}, 10, 2);

add_action('user_register', static function ($user_id) {
    mu_activity_log_event('user_created', ['user_id' => $user_id]);
});

add_action('delete_user', static function ($user_id, $reassign) {
    mu_activity_log_event('user_deleted', [
        'user_id' => $user_id,
        'reassigned_to' => $reassign ?: 0,
    ]);
}, 10, 2);

add_action('set_user_role', static function ($user_id, $role, $old_roles) {
    mu_activity_log_event('user_role_changed', [
        'user_id' => $user_id,
        'new_role' => $role,
        'old_roles' => implode(',', (array) $old_roles),
    ]);
}, 10, 3);

add_action('updated_option', static function ($option) {
    if (MU_ACTIVITY_LOG_OPTION === $option) {
        return;
    }

    $critical = [
        'admin_email', 'blog_public', 'default_role', 'home', 'permalink_structure',
        'siteurl', 'users_can_register',
    ];
    $critical = (array) apply_filters('mu_activity_log_options', $critical);

    if (in_array($option, $critical, true)) {
        mu_activity_log_event('critical_option_changed', ['option' => $option]);
    }
}, 10, 1);

add_action('upgrader_process_complete', static function ($upgrader, $data) {
    $type   = isset($data['type']) ? sanitize_key($data['type']) : 'unknown';
    $action = isset($data['action']) ? sanitize_key($data['action']) : 'unknown';
    mu_activity_log_event('software_change', ['type' => $type, 'action' => $action]);
}, 10, 2);

add_action('wp_dashboard_setup', static function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    wp_add_dashboard_widget('mu_activity_audit_log', 'Recent Administrative Activity', static function () {
        $entries = get_option(MU_ACTIVITY_LOG_OPTION, []);
        $entries = is_array($entries) ? array_slice(array_reverse($entries), 0, 10) : [];

        if (!$entries) {
            echo '<p>No tracked activity yet.</p>';
            return;
        }

        echo '<ul>';
        foreach ($entries as $entry) {
            $context = [];
            foreach ((array) $entry['context'] as $key => $value) {
                $context[] = $key . ': ' . $value;
            }
            echo '<li><strong>' . esc_html(str_replace('_', ' ', $entry['event'])) . '</strong> — ';
            echo esc_html(human_time_diff((int) $entry['time'], time())) . ' ago';
            if ($context) {
                echo '<br><small>' . esc_html(implode(' · ', $context)) . '</small>';
            }
            echo '</li>';
        }
        echo '</ul><p><em>Values, passwords, email addresses, and IP addresses are never logged.</em></p>';
    });
});
