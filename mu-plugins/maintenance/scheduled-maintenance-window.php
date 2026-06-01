<?php

/**
 * Scheduled maintenance window
 *
 * Plugin name:       Scheduled Maintenance Window
 * Plugin URI:        https://openwpclub.com
 * Description:       Shows a maintenance page automatically during a recurring time window. Define MU_MAINTENANCE_WINDOWS as an array of windows with 'start', 'end' (HH:MM), and optional 'days' (array of day names). Uses server time.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       scheduled-maintenance-window
 */

defined('ABSPATH') or die();

/**
 * Configure in wp-config.php:
 *
 *   define('MU_MAINTENANCE_WINDOWS', [
 *       ['start' => '02:00', 'end' => '04:00'],                          // every day
 *       ['start' => '01:00', 'end' => '03:00', 'days' => ['Sunday']],    // Sundays only
 *   ]);
 */
if (!defined('MU_MAINTENANCE_WINDOWS') || empty(MU_MAINTENANCE_WINDOWS)) {
    return;
}

/**
 * Return true if the current server time falls within any configured window.
 */
function mu_is_in_maintenance_window(): bool
{
    $now_time = (int) date('Hi'); // e.g. 0230 for 02:30
    $now_day  = date('l');        // e.g. "Sunday"

    foreach ((array) MU_MAINTENANCE_WINDOWS as $window) {
        $start = (int) str_replace(':', '', $window['start'] ?? '0000');
        $end   = (int) str_replace(':', '', $window['end']   ?? '0000');
        $days  = $window['days'] ?? null;

        if ($days !== null && !in_array($now_day, (array) $days, true)) {
            continue;
        }

        if ($start <= $end) {
            if ($now_time >= $start && $now_time < $end) {
                return true;
            }
        } else {
            // Overnight window (e.g. 23:00–01:00)
            if ($now_time >= $start || $now_time < $end) {
                return true;
            }
        }
    }

    return false;
}

add_action(
    'template_redirect',
    static function () {
        if (!mu_is_in_maintenance_window()) {
            return;
        }

        // Let logged-in admins through
        if (is_user_logged_in() && current_user_can('manage_options')) {
            return;
        }

        $message = apply_filters(
            'mu_maintenance_window_message',
            __('The site is undergoing scheduled maintenance. Please check back soon.', 'scheduled-maintenance-window')
        );

        http_response_code(503);
        header('Retry-After: 3600');
        wp_die(
            esc_html($message),
            esc_html__('Maintenance', 'scheduled-maintenance-window'),
            ['response' => 503]
        );
    },
    1,
    0
);
