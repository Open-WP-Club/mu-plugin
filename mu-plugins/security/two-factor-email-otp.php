<?php

/**
 * Two-factor authentication via email OTP
 *
 * Plugin name:       Two-Factor Email OTP
 * Plugin URI:        https://openwpclub.com
 * Description:       Adds a second authentication step after a successful password login. A 6-digit code is emailed to the user; the session is not created until the code is verified.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       two-factor-email-otp
 */

defined('ABSPATH') or die();

/**
 * Obfuscate an email address for display (e.g. "ab***@example.com").
 */
function mu_2fa_obfuscate_email(string $email): string
{
    $parts = explode('@', $email, 2);
    return substr($parts[0], 0, 2) . '***@' . ($parts[1] ?? '');
}

/**
 * Step 1 (early authenticate filter): If an OTP token cookie + code are present,
 * verify them and return the user — bypassing password re-entry.
 */
add_filter(
    'authenticate',
    static function ($user, string $username, string $password) {
        if (empty($username) || empty($_POST['mu_otp_code']) || empty($_COOKIE['mu_otp_tok'])) {
            return $user;
        }

        $key    = 'mu_2fa_' . md5(sanitize_user($username, true));
        $stored = get_transient($key);

        if (
            is_array($stored)
            && isset($stored['token'], $stored['otp'], $stored['login'])
            && hash_equals($stored['token'], sanitize_text_field($_COOKIE['mu_otp_tok']))
            && hash_equals($stored['otp'], trim($_POST['mu_otp_code']))
        ) {
            delete_transient($key);
            setcookie('mu_otp_tok', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
            return get_user_by('login', $stored['login']);
        }

        return new WP_Error(
            'mu_otp_invalid',
            __('<strong>Error:</strong> Invalid or expired verification code. Please log in again.', 'two-factor-email-otp')
        );
    },
    5,
    3
);

/**
 * Step 2: After password is verified, generate and email the OTP instead of completing the login.
 */
add_filter(
    'wp_authenticate_user',
    static function ($user, string $password) {
        if (is_wp_error($user)) {
            return $user;
        }

        // Already in OTP verification step — let it through
        if (!empty($_POST['mu_otp_code'])) {
            return $user;
        }

        // Allow bypassing 2FA for specific users via filter
        if (!apply_filters('mu_2fa_required', true, $user)) {
            return $user;
        }

        $otp   = sprintf('%06d', random_int(100000, 999999));
        $token = bin2hex(random_bytes(16));
        $key   = 'mu_2fa_' . md5($user->user_login);

        set_transient($key, [
            'otp'   => $otp,
            'token' => $token,
            'login' => $user->user_login,
        ], 10 * MINUTE_IN_SECONDS);

        setcookie('mu_otp_tok', $token, time() + 600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);

        wp_mail(
            $user->user_email,
            sprintf('[%s] %s', get_bloginfo('name'), __('Login verification code', 'two-factor-email-otp')),
            sprintf(
                "%s\n\n%s\n\n%s",
                __('Your verification code is:', 'two-factor-email-otp'),
                $otp,
                __('This code expires in 10 minutes. If you did not attempt to log in, please ignore this email.', 'two-factor-email-otp')
            )
        );

        return new WP_Error(
            'mu_otp_sent',
            sprintf(
                __('<strong>Check your email.</strong> A 6-digit code was sent to %s. Enter it below.', 'two-factor-email-otp'),
                esc_html(mu_2fa_obfuscate_email($user->user_email))
            )
        );
    },
    20,
    2
);

/**
 * Inject the OTP input field into the login form when a token cookie is present.
 */
add_action(
    'login_form',
    static function () {
        if (empty($_COOKIE['mu_otp_tok'])) {
            return;
        }
        echo '<p>
            <label for="mu_otp_code">'
            . esc_html__('Verification Code', 'two-factor-email-otp')
            . '<br><input class="input" type="text" id="mu_otp_code" name="mu_otp_code"
                autocomplete="one-time-code" inputmode="numeric" pattern="[0-9]{6}"
                maxlength="6" size="20" placeholder="' . esc_attr__('6-digit code', 'two-factor-email-otp') . '">
            </label>
        </p>';
    },
    10,
    0
);
