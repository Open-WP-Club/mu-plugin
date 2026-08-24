<?php

/**
 * Plugin Name:       Update Policy Controller
 * Plugin URI:        https://openwpclub.com
 * Description:       Keeps update checks visible while applying balanced, automatic, manual, or frozen update policies; frozen also locks file modifications.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       update-policy-controller
 */

defined('ABSPATH') || exit;

function mu_update_policy()
{
    $policy = defined('MU_UPDATE_POLICY') ? strtolower((string) MU_UPDATE_POLICY) : 'balanced';
    return in_array($policy, ['balanced', 'automatic', 'manual', 'frozen'], true) ? $policy : 'balanced';
}

if ('frozen' === mu_update_policy() && !defined('DISALLOW_FILE_MODS')) {
    define('DISALLOW_FILE_MODS', true);
}

function mu_update_policy_item($current, $item, $type)
{
    $policy = mu_update_policy();
    if ('automatic' === $policy) {
        return true;
    }
    if ('frozen' === $policy) {
        return false;
    }

    $constant = 'plugin' === $type ? 'MU_AUTO_UPDATE_PLUGINS' : 'MU_AUTO_UPDATE_THEMES';
    if (defined($constant)) {
        $allowed = constant($constant);
        if (true === $allowed || false === $allowed) {
            return $allowed;
        }

        $identifier = 'plugin' === $type ? ($item->plugin ?? '') : ($item->theme ?? ($item->stylesheet ?? ''));
        return in_array($identifier, (array) $allowed, true);
    }

    if ('manual' === $policy) {
        return false;
    }

    // Preserve WordPress decisions, including forced security updates and per-item opt-ins.
    return $current;
}

add_filter('auto_update_plugin', static function ($update, $item) {
    return mu_update_policy_item($update, $item, 'plugin');
}, 20, 2);

add_filter('auto_update_theme', static function ($update, $item) {
    return mu_update_policy_item($update, $item, 'theme');
}, 20, 2);

add_filter('auto_update_core', static function ($update, $item) {
    $policy = mu_update_policy();
    if ('automatic' === $policy) {
        return true;
    }
    if ('frozen' === $policy || 'manual' === $policy) {
        return false;
    }

    // Balanced mode preserves security-team decisions and allows minor core releases.
    if (true === $update) {
        return true;
    }

    $current = isset($item->current) ? (string) $item->current : '';
    $target  = isset($item->response) ? (string) $item->response : '';
    if (!$current || !$target) {
        return $update;
    }

    $current_parts = array_slice(explode('.', $current), 0, 2);
    $target_parts  = array_slice(explode('.', $target), 0, 2);
    return $current_parts === $target_parts;
}, 20, 2);

add_action('admin_notices', static function () {
    if (!current_user_can('update_core')) {
        return;
    }
    $screen = get_current_screen();
    if (!$screen || 'update-core' !== $screen->id) {
        return;
    }

    echo '<div class="notice notice-info"><p><strong>Update Policy:</strong> ' . esc_html(ucfirst(mu_update_policy())) . '. Update checks remain enabled.</p></div>';
});
