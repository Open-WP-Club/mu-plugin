<?php

/**
 * Plugin Name:       OpenWP Operations Center
 * Plugin URI:        https://openwpclub.com
 * Description:       Consolidates operational health, alerts, privacy-safe logs, retention controls, exports, and safe cleanup reports.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       openwp-operations-center
 */

defined('ABSPATH') || exit;

const MU_OPERATIONS_SETTINGS_OPTION = 'mu_operations_settings';
const MU_OPERATIONS_ALERT_STATE_OPTION = 'mu_operations_alert_state';

function mu_operations_center_enabled()
{
    return true;
}

function mu_operations_settings()
{
    $defaults = [
        'email_alerts'  => 0,
        'alert_throttle_hours' => 12,
        'cron_limit'    => 20,
        'mail_limit'    => 20,
        'http_limit'    => 30,
        'audit_limit'   => 100,
        'woo_limit'     => 50,
        'cleanup_mode'  => 'report',
    ];
    $saved = get_option(MU_OPERATIONS_SETTINGS_OPTION, []);
    return wp_parse_args(is_array($saved) ? $saved : [], $defaults);
}

function mu_operations_cleanup_mode()
{
    $settings = mu_operations_settings();
    $mode = defined('MU_CLEANUP_MODE') ? strtolower((string) MU_CLEANUP_MODE) : $settings['cleanup_mode'];
    return in_array($mode, ['report', 'trash', 'delete'], true) ? $mode : 'report';
}

add_filter('mu_cron_monitor_error_limit', static function () {
    return (int) mu_operations_settings()['cron_limit'];
});
add_filter('mu_mail_monitor_failure_limit', static function () {
    return (int) mu_operations_settings()['mail_limit'];
});
add_filter('mu_http_failure_host_limit', static function () {
    return (int) mu_operations_settings()['http_limit'];
});
add_filter('mu_activity_log_limit', static function () {
    return (int) mu_operations_settings()['audit_limit'];
});
add_filter('mu_wc_payment_health_log_limit', static function () {
    return (int) mu_operations_settings()['woo_limit'];
});
add_filter('mu_unattached_media_cleanup_mode', static function () {
    return mu_operations_cleanup_mode();
});
add_filter('mu_orphaned_post_meta_cleanup_mode', static function () {
    return mu_operations_cleanup_mode();
});
add_filter('mu_wc_order_retention_mode', static function () {
    return mu_operations_cleanup_mode();
});

function mu_operations_log_sources()
{
    return [
        'activity' => ['label' => 'Administrative activity', 'option' => 'mu_activity_audit_log'],
        'cron' => ['label' => 'Cron errors', 'option' => 'mu_cron_monitor_errors'],
        'mail' => ['label' => 'Mail failures', 'option' => 'mu_mail_monitor_failures'],
        'http' => ['label' => 'HTTP failures', 'option' => 'mu_http_api_failures'],
        'failed_orders' => ['label' => 'Failed WooCommerce orders', 'option' => 'mu_wc_failed_orders'],
        'webhooks' => ['label' => 'WooCommerce webhook failures', 'option' => 'mu_wc_webhook_failures'],
        '404' => ['label' => '404 requests', 'option' => ''],
    ];
}

/**
 * Normalize logs for safe display/export.
 *
 * @return array<int,array<string,string>>
 */
function mu_operations_log_rows($source)
{
    $sources = mu_operations_log_sources();
    if (!isset($sources[$source])) {
        return [];
    }

    if ('404' === $source) {
        $file = WP_CONTENT_DIR . '/404-logs/404-' . gmdate('Y-m-d') . '.log';
        if (!is_readable($file)) {
            return [];
        }
        $lines = array_slice((array) file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -100);
        $rows = [];
        foreach ($lines as $line) {
            preg_match('/^\[([^\]]+)\]/', $line, $time_match);
            preg_match('/URI: ([^|]+)/', $line, $uri_match);
            $path = isset($uri_match[1]) ? (string) wp_parse_url(trim($uri_match[1]), PHP_URL_PATH) : '';
            $rows[] = ['time' => $time_match[1] ?? '', 'event' => 'not_found', 'details' => $path];
        }
        return array_reverse($rows);
    }

    $entries = get_option($sources[$source]['option'], []);
    if (!is_array($entries)) {
        return [];
    }

    $rows = [];
    if ('http' === $source) {
        foreach ($entries as $host => $entry) {
            $rows[] = [
                'time' => !empty($entry['time']) ? gmdate('Y-m-d H:i:s', (int) $entry['time']) . ' UTC' : '',
                'event' => (string) ($entry['code'] ?? 'http_error'),
                'details' => sanitize_text_field($host . ' · count ' . (int) ($entry['count'] ?? 0)),
            ];
        }
        return $rows;
    }

    foreach (array_reverse($entries) as $entry) {
        $event = $entry['event'] ?? ($entry['code'] ?? ($entry['status'] ?? $source));
        $details = '';
        if ('activity' === $source) {
            $parts = [];
            foreach ((array) ($entry['context'] ?? []) as $key => $value) {
                $parts[] = sanitize_key($key) . ': ' . sanitize_text_field((string) $value);
            }
            $details = implode(' · ', $parts);
        } elseif ('cron' === $source) {
            $details = sanitize_key((string) ($entry['hook'] ?? '')) . ' · ' . sanitize_text_field((string) ($entry['message'] ?? ''));
        } elseif ('mail' === $source) {
            $details = sanitize_text_field((string) ($entry['message'] ?? ''));
        } elseif ('failed_orders' === $source) {
            $details = 'order #' . absint($entry['order_id'] ?? 0) . ' · ' . sanitize_key((string) ($entry['payment_method'] ?? ''));
        } elseif ('webhooks' === $source) {
            $details = 'webhook #' . absint($entry['webhook_id'] ?? 0) . ' · ' . (float) ($entry['duration'] ?? 0) . 's';
        }

        $rows[] = [
            'time' => !empty($entry['time']) ? gmdate('Y-m-d H:i:s', (int) $entry['time']) . ' UTC' : '',
            'event' => sanitize_text_field((string) $event),
            'details' => $details,
        ];
    }
    return $rows;
}

function mu_operations_collect_alerts()
{
    $alerts = [];
    $now = time();
    $cron = get_option('mu_cron_monitor_errors', []);
    $latest_cron = is_array($cron) ? end($cron) : false;
    if (is_array($latest_cron) && $now - (int) ($latest_cron['time'] ?? 0) < DAY_IN_SECONDS) {
        $alerts['cron'] = 'Recent WP-Cron scheduling error';
    }

    $mail = get_option('mu_mail_monitor_failures', []);
    $latest_mail = is_array($mail) ? end($mail) : false;
    if (is_array($latest_mail) && (int) ($latest_mail['time'] ?? 0) > (int) get_option('mu_mail_monitor_last_success', 0)) {
        $alerts['mail'] = 'Latest WordPress mail attempt failed';
    }

    $http = get_option('mu_http_api_failures', []);
    $recent_http = array_filter((array) $http, static function ($entry) use ($now) {
        return $now - (int) ($entry['time'] ?? 0) < DAY_IN_SECONDS;
    });
    if ($recent_http) {
        $alerts['http'] = count($recent_http) . ' remote host(s) failed in the last 24 hours';
    }

    if (function_exists('mu_action_scheduler_health')) {
        $actions = mu_action_scheduler_health();
        if ($actions && ($actions['failed_ids'] || $actions['overdue_ids'])) {
            $alerts['actions'] = 'WooCommerce scheduled actions need attention';
        }
    }

    if (function_exists('mu_wc_payment_health_summary')) {
        $payments = mu_wc_payment_health_summary();
        if ($payments && ($payments['stale_pending'] || $payments['stale_on_hold'] || $payments['recent_failed_orders'] || $payments['recent_webhook_failures'])) {
            $alerts['payments'] = 'WooCommerce payment or webhook health needs attention';
        }
    }

    return (array) apply_filters('mu_operations_alerts', $alerts);
}

function mu_operations_alert_channels_enabled()
{
    $settings = mu_operations_settings();
    return !empty($settings['email_alerts']) || defined('MU_OPERATIONS_WEBHOOK_URL');
}

add_action('init', static function () {
    $hook = 'mu_operations_alert_check';
    if (mu_operations_alert_channels_enabled()) {
        if (!wp_next_scheduled($hook)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'hourly', $hook);
        }
    } elseif (wp_next_scheduled($hook)) {
        wp_clear_scheduled_hook($hook);
    }
});

add_action('mu_operations_alert_check', 'mu_operations_send_alerts');

function mu_operations_send_alerts()
{
    $alerts = mu_operations_collect_alerts();
    if (!$alerts) {
        delete_option(MU_OPERATIONS_ALERT_STATE_OPTION);
        return;
    }

    $settings = mu_operations_settings();
    $fingerprint = md5(wp_json_encode($alerts));
    $state = get_option(MU_OPERATIONS_ALERT_STATE_OPTION, []);
    $throttle = max(1, (int) $settings['alert_throttle_hours']) * HOUR_IN_SECONDS;
    if (($state['fingerprint'] ?? '') === $fingerprint && time() - (int) ($state['time'] ?? 0) < $throttle) {
        return;
    }

    $message = "Operational issues detected on " . home_url('/') . ":\n\n- " . implode("\n- ", $alerts);
    $message .= "\n\nReview: " . admin_url('tools.php?page=mu-operations');

    if (!empty($settings['email_alerts'])) {
        wp_mail(get_option('admin_email'), '[' . get_bloginfo('name') . '] Operational health alert', $message);
    }
    if (defined('MU_OPERATIONS_WEBHOOK_URL')) {
        $url = esc_url_raw((string) MU_OPERATIONS_WEBHOOK_URL, ['https']);
        if ($url) {
            wp_remote_post($url, [
                'timeout' => 5,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => wp_json_encode(['site' => home_url('/'), 'alerts' => array_values($alerts)]),
            ]);
        }
    }

    update_option(MU_OPERATIONS_ALERT_STATE_OPTION, ['fingerprint' => $fingerprint, 'time' => time()], false);
}

add_action('admin_menu', static function () {
    add_management_page('OpenWP Operations', 'OpenWP Operations', 'manage_options', 'mu-operations', 'mu_operations_render_page');
});

add_action('admin_notices', static function () {
    if (!current_user_can('manage_options')) {
        return;
    }
    $alerts = mu_operations_collect_alerts();
    if (!$alerts) {
        return;
    }
    echo '<div class="notice notice-warning"><p><strong>OpenWP Operations:</strong> ' . esc_html((string) count($alerts)) . ' issue(s) need attention. <a href="' . esc_url(admin_url('tools.php?page=mu-operations')) . '">Open Operations Center</a>.</p></div>';
});

add_action('wp_dashboard_setup', static function () {
    if (!current_user_can('manage_options')) {
        return;
    }
    foreach (['cron_monitor_widget', 'mu_mail_delivery_monitor', 'mu_action_scheduler_monitor', 'mu_activity_audit_log', 'mu_http_api_failure_monitor', 'mu_media_accessibility_check', '404_logger_widget'] as $widget_id) {
        remove_meta_box($widget_id, 'dashboard', 'normal');
        remove_meta_box($widget_id, 'dashboard', 'side');
    }

    wp_add_dashboard_widget('mu_operations_summary', 'OpenWP Operations', static function () {
        $alerts = mu_operations_collect_alerts();
        echo $alerts ? '<p><strong style="color:#b32d2e">' . esc_html((string) count($alerts)) . ' issue(s) need attention.</strong></p>' : '<p><strong style="color:#008a20">No current operational alerts.</strong></p>';
        echo '<p><a class="button" href="' . esc_url(admin_url('tools.php?page=mu-operations')) . '">Open Operations Center</a></p>';
    });
}, 100);

function mu_operations_render_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'overview';
    $tabs = ['overview' => 'Overview', 'logs' => 'Logs', 'settings' => 'Settings'];
    echo '<div class="wrap"><h1>OpenWP Operations Center</h1><nav class="nav-tab-wrapper">';
    foreach ($tabs as $slug => $label) {
        $class = $tab === $slug ? ' nav-tab-active' : '';
        echo '<a class="nav-tab' . esc_attr($class) . '" href="' . esc_url(admin_url('tools.php?page=mu-operations&tab=' . $slug)) . '">' . esc_html($label) . '</a>';
    }
    echo '</nav>';

    if ('logs' === $tab) {
        mu_operations_render_logs();
    } elseif ('settings' === $tab) {
        mu_operations_render_settings();
    } else {
        mu_operations_render_overview();
    }
    echo '</div>';
}

function mu_operations_render_overview()
{
    $alerts = mu_operations_collect_alerts();
    $last_cron = (int) get_option('cron_last_run', 0);
    $last_mail = (int) get_option('mu_mail_monitor_last_success', 0);
    $status_rows = [
        'WP-Cron' => $last_cron ? human_time_diff($last_cron, time()) . ' ago' : 'Not recorded',
        'Mail accepted by WordPress' => $last_mail ? human_time_diff($last_mail, time()) . ' ago' : 'Not recorded',
        'Update policy' => function_exists('mu_update_policy') ? ucfirst(mu_update_policy()) : 'Not available',
        'Alert channels' => mu_operations_alert_channels_enabled() ? 'Enabled' : 'Dashboard only',
    ];
    if (function_exists('mu_action_scheduler_health')) {
        $actions = mu_action_scheduler_health();
        if ($actions) {
            $status_rows['Action Scheduler'] = count($actions['failed_ids']) . ' failed, ' . count($actions['overdue_ids']) . ' overdue (up to 5 each)';
        }
    }
    if (function_exists('mu_wc_payment_health_summary')) {
        $payments = mu_wc_payment_health_summary();
        if ($payments) {
            $status_rows['WooCommerce orders'] = $payments['stale_pending'] . ' stale pending, ' . $payments['stale_on_hold'] . ' stale on-hold';
            $status_rows['WooCommerce failures (24h)'] = $payments['recent_failed_orders'] . ' orders, ' . $payments['recent_webhook_failures'] . ' webhooks';
        }
    }
    echo '<h2>System status</h2><table class="widefat striped"><tbody>';
    foreach ($status_rows as $label => $status) {
        echo '<tr><th style="width:240px">' . esc_html($label) . '</th><td>' . esc_html($status) . '</td></tr>';
    }
    echo '</tbody></table>';

    echo '<h2>Current health</h2>';
    if (!$alerts) {
        echo '<div class="notice notice-success inline"><p>No current operational alerts.</p></div>';
    } else {
        echo '<table class="widefat striped"><thead><tr><th>Area</th><th>Finding</th></tr></thead><tbody>';
        foreach ($alerts as $area => $message) {
            echo '<tr><td><strong>' . esc_html(ucfirst($area)) . '</strong></td><td>' . esc_html($message) . '</td></tr>';
        }
        echo '</tbody></table>';
    }

    echo '<h2>Safe cleanup</h2><p>Mode: <strong>' . esc_html(mu_operations_cleanup_mode()) . '</strong>. Report mode never changes content.</p>';
    $jobs = [
        'media' => ['label' => 'Unattached media', 'hook' => 'mu_delete_unattached_media', 'option' => 'mu_cleanup_unattached_media_report'],
        'meta' => ['label' => 'Orphaned post meta', 'hook' => 'mu_clean_orphaned_post_meta', 'option' => 'mu_cleanup_orphaned_meta_report'],
        'orders' => ['label' => 'Old WooCommerce orders', 'hook' => 'mu_wc_order_retention', 'option' => 'mu_cleanup_wc_orders_report'],
    ];
    echo '<table class="widefat striped"><thead><tr><th>Job</th><th>Last result</th><th>Action</th></tr></thead><tbody>';
    foreach ($jobs as $key => $job) {
        $report = get_option($job['option'], []);
        $result = $report ? sprintf('%d candidates, %d affected (%s)', (int) $report['candidates'], (int) $report['affected'], $report['mode']) : 'Not run yet';
        echo '<tr><td>' . esc_html($job['label']) . '</td><td>' . esc_html($result) . '</td><td><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('mu_operations_run_cleanup');
        echo '<input type="hidden" name="action" value="mu_operations_run_cleanup"><input type="hidden" name="job" value="' . esc_attr($key) . '"><button class="button" onclick="return confirm(\'Run this cleanup job using the configured mode?\')">Run now</button></form></td></tr>';
    }
    echo '</tbody></table>';
}

function mu_operations_render_logs()
{
    $sources = mu_operations_log_sources();
    $source = isset($_GET['source']) ? sanitize_key(wp_unslash($_GET['source'])) : 'activity';
    $source = isset($sources[$source]) ? $source : 'activity';
    $rows = mu_operations_log_rows($source);

    echo '<form method="get"><input type="hidden" name="page" value="mu-operations"><input type="hidden" name="tab" value="logs"><select name="source">';
    foreach ($sources as $slug => $data) {
        echo '<option value="' . esc_attr($slug) . '" ' . selected($source, $slug, false) . '>' . esc_html($data['label']) . '</option>';
    }
    echo '</select> <button class="button">View</button></form>';
    echo '<table class="widefat striped" style="margin-top:1em"><thead><tr><th>Time</th><th>Event</th><th>Details</th></tr></thead><tbody>';
    foreach (array_slice($rows, 0, 100) as $row) {
        echo '<tr><td>' . esc_html($row['time']) . '</td><td><code>' . esc_html($row['event']) . '</code></td><td>' . esc_html($row['details']) . '</td></tr>';
    }
    if (!$rows) {
        echo '<tr><td colspan="3">No entries.</td></tr>';
    }
    echo '</tbody></table><p>';
    $export = wp_nonce_url(admin_url('admin-post.php?action=mu_operations_export&source=' . $source), 'mu_operations_export');
    echo '<a class="button" href="' . esc_url($export) . '">Export CSV</a> ';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline">';
    wp_nonce_field('mu_operations_clear');
    echo '<input type="hidden" name="action" value="mu_operations_clear"><input type="hidden" name="source" value="' . esc_attr($source) . '"><button class="button button-link-delete" onclick="return confirm(\'Permanently clear this log?\')">Clear log</button></form></p>';
}

function mu_operations_render_settings()
{
    $settings = mu_operations_settings();
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    wp_nonce_field('mu_operations_save_settings');
    echo '<input type="hidden" name="action" value="mu_operations_save_settings"><table class="form-table">';
    echo '<tr><th>Email alerts</th><td><label><input type="checkbox" name="email_alerts" value="1" ' . checked(!empty($settings['email_alerts']), true, false) . '> Send aggregated alerts to the WordPress administration email</label></td></tr>';
    echo '<tr><th>Alert throttle</th><td><input type="number" min="1" max="168" name="alert_throttle_hours" value="' . esc_attr((string) $settings['alert_throttle_hours']) . '"> hours</td></tr>';
    foreach (['cron_limit' => 'Cron entries', 'mail_limit' => 'Mail entries', 'http_limit' => 'HTTP hosts', 'audit_limit' => 'Audit entries', 'woo_limit' => 'WooCommerce entries'] as $key => $label) {
        echo '<tr><th>' . esc_html($label) . '</th><td><input type="number" min="5" max="500" name="' . esc_attr($key) . '" value="' . esc_attr((string) $settings[$key]) . '"></td></tr>';
    }
    echo '<tr><th>Cleanup mode</th><td><select name="cleanup_mode"' . (defined('MU_CLEANUP_MODE') ? ' disabled' : '') . '>';
    foreach (['report' => 'Report only (recommended)', 'trash' => 'Move supported content to Trash', 'delete' => 'Permanently delete'] as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected(mu_operations_cleanup_mode(), $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    if (defined('MU_CLEANUP_MODE')) {
        echo '<input type="hidden" name="cleanup_mode" value="' . esc_attr(mu_operations_cleanup_mode()) . '"><p class="description">Locked by MU_CLEANUP_MODE.</p>';
    }
    echo '<p class="description">Orphaned metadata cannot be trashed, so Trash mode reports it without deleting.</p></td></tr></table>';
    submit_button('Save settings');
    echo '</form><h2>Configuration constants</h2><p>Optional HTTPS webhook alerts: <code>MU_OPERATIONS_WEBHOOK_URL</code>.</p>';
    echo '<p>Update policy: <code>MU_UPDATE_POLICY</code> accepts <code>balanced</code> (default), <code>automatic</code>, <code>manual</code>, or <code>frozen</code>. Optional per-item allowlists: <code>MU_AUTO_UPDATE_PLUGINS</code> and <code>MU_AUTO_UPDATE_THEMES</code>.</p>';
}

add_action('admin_post_mu_operations_save_settings', static function () {
    check_admin_referer('mu_operations_save_settings');
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden', 'Forbidden', ['response' => 403]);
    }
    $integer = static function ($key, $default, $min, $max) {
        $value = isset($_POST[$key]) ? absint($_POST[$key]) : $default;
        return min($max, max($min, $value));
    };
    $mode = isset($_POST['cleanup_mode']) ? sanitize_key(wp_unslash($_POST['cleanup_mode'])) : 'report';
    update_option(MU_OPERATIONS_SETTINGS_OPTION, [
        'email_alerts' => empty($_POST['email_alerts']) ? 0 : 1,
        'alert_throttle_hours' => $integer('alert_throttle_hours', 12, 1, 168),
        'cron_limit' => $integer('cron_limit', 20, 5, 500),
        'mail_limit' => $integer('mail_limit', 20, 5, 500),
        'http_limit' => $integer('http_limit', 30, 5, 500),
        'audit_limit' => $integer('audit_limit', 100, 20, 500),
        'woo_limit' => $integer('woo_limit', 50, 10, 500),
        'cleanup_mode' => in_array($mode, ['report', 'trash', 'delete'], true) ? $mode : 'report',
    ], false);
    mu_operations_apply_retention();
    wp_safe_redirect(admin_url('tools.php?page=mu-operations&tab=settings&updated=1'));
    exit;
});

function mu_operations_apply_retention()
{
    $settings = mu_operations_settings();
    $limits = [
        'mu_cron_monitor_errors' => (int) $settings['cron_limit'],
        'mu_mail_monitor_failures' => (int) $settings['mail_limit'],
        'mu_activity_audit_log' => (int) $settings['audit_limit'],
        'mu_wc_failed_orders' => (int) $settings['woo_limit'],
        'mu_wc_webhook_failures' => (int) $settings['woo_limit'],
    ];
    foreach ($limits as $option => $limit) {
        $entries = get_option($option, []);
        if (is_array($entries) && count($entries) > $limit) {
            update_option($option, array_slice($entries, -$limit), false);
        }
    }

    $http = get_option('mu_http_api_failures', []);
    if (is_array($http) && count($http) > (int) $settings['http_limit']) {
        update_option('mu_http_api_failures', array_slice($http, 0, (int) $settings['http_limit'], true), false);
    }
    delete_transient('mu_wc_payment_health_summary');
}

add_action('admin_post_mu_operations_clear', static function () {
    check_admin_referer('mu_operations_clear');
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden', 'Forbidden', ['response' => 403]);
    }
    $source = isset($_POST['source']) ? sanitize_key(wp_unslash($_POST['source'])) : '';
    $sources = mu_operations_log_sources();
    if (isset($sources[$source])) {
        if ('404' === $source) {
            $directory = WP_CONTENT_DIR . '/404-logs';
            foreach ((array) glob($directory . '/404-*.log') as $file) {
                if (dirname($file) === $directory && is_file($file)) {
                    wp_delete_file($file);
                }
            }
        } elseif ($sources[$source]['option']) {
            delete_option($sources[$source]['option']);
        }
        delete_transient('mu_wc_payment_health_summary');
        delete_option(MU_OPERATIONS_ALERT_STATE_OPTION);
    }
    wp_safe_redirect(admin_url('tools.php?page=mu-operations&tab=logs&source=' . $source));
    exit;
});

add_action('admin_post_mu_operations_export', static function () {
    check_admin_referer('mu_operations_export');
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden', 'Forbidden', ['response' => 403]);
    }
    $source = isset($_GET['source']) ? sanitize_key(wp_unslash($_GET['source'])) : '';
    if (!isset(mu_operations_log_sources()[$source])) {
        wp_die('Unknown log source', 'Bad request', ['response' => 400]);
    }
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=openwp-' . $source . '-' . gmdate('Y-m-d') . '.csv');
    $stream = fopen('php://output', 'w');
    fputcsv($stream, ['time', 'event', 'details']);
    foreach (mu_operations_log_rows($source) as $row) {
        fputcsv($stream, $row);
    }
    fclose($stream);
    exit;
});

add_action('admin_post_mu_operations_run_cleanup', static function () {
    check_admin_referer('mu_operations_run_cleanup');
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden', 'Forbidden', ['response' => 403]);
    }
    $job = isset($_POST['job']) ? sanitize_key(wp_unslash($_POST['job'])) : '';
    $hooks = ['media' => 'mu_delete_unattached_media', 'meta' => 'mu_clean_orphaned_post_meta', 'orders' => 'mu_wc_order_retention'];
    if (isset($hooks[$job])) {
        do_action($hooks[$job]);
    }
    wp_safe_redirect(admin_url('tools.php?page=mu-operations&cleanup-ran=' . $job));
    exit;
});
