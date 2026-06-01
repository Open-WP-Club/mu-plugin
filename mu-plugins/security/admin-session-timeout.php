<?php

/**
 * Admin session timeout
 *
 * Plugin name:       Admin Session Timeout
 * Plugin URI:        https://openwpclub.com
 * Description:       Automatically logs out inactive admin users. Default timeout is 60 minutes. Override with the 'mu_session_timeout' filter (value in seconds).
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       admin-session-timeout
 */

defined('ABSPATH') or die();

/**
 * Check inactivity and update last-activity timestamp on every admin request.
 */
add_action(
    'admin_init',
    static function () {
        if (!is_user_logged_in() || wp_doing_ajax()) {
            return;
        }

        $user_id      = get_current_user_id();
        $timeout      = (int) apply_filters('mu_session_timeout', HOUR_IN_SECONDS);
        $meta_key     = '_mu_last_activity';
        $last_activity = (int) get_user_meta($user_id, $meta_key, true);

        if ($last_activity && (time() - $last_activity) > $timeout) {
            wp_logout();
            wp_redirect(add_query_arg('mu_timeout', '1', wp_login_url()));
            exit;
        }

        update_user_meta($user_id, $meta_key, time());
    },
    1,
    0
);

/**
 * Show a friendly message on the login page after a timeout redirect.
 */
add_filter(
    'login_message',
    static function ($message) {
        if (!empty($_GET['mu_timeout'])) {
            $message .= '<p class="message">'
                . esc_html__('Your session expired due to inactivity. Please log in again.', 'admin-session-timeout')
                . '</p>';
        }
        return $message;
    },
    10,
    1
);
