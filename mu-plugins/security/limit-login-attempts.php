<?php

/**
 * A utility plugin to limit login attempts and prevent brute force attacks
 *
 * Plugin name:       Limit Login Attempts
 * Plugin URI:        https://openwpclub.com
 * Description:       Blocks IP addresses after 4 failed login attempts for 24 hours to prevent brute force attacks.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       limit-login-attempts
 */

// Prevent direct access
defined('ABSPATH') or die();

// Configuration
define('MU_LOGIN_ATTEMPTS_MAX', 4);
define('MU_LOGIN_LOCKOUT_DURATION', 24 * HOUR_IN_SECONDS); // 24 hours

/**
 * Get the user's IP address
 */
function mu_get_user_ip()
{
  $ip = '';

  // Check for shared internet/ISP IP
  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
  } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    // Check for IP passed from proxy
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
    $ip = $_SERVER['REMOTE_ADDR'];
  }

  // Validate and sanitize IP
  $ip = filter_var($ip, FILTER_VALIDATE_IP);

  return $ip ? $ip : '0.0.0.0';
}

/**
 * Get transient key for IP address
 */
function mu_get_login_transient_key($ip)
{
  return 'login_attempts_' . md5($ip);
}

/**
 * Get lockout transient key for IP address
 */
function mu_get_lockout_transient_key($ip)
{
  return 'login_locked_' . md5($ip);
}

/**
 * Check if IP is currently locked out
 */
function mu_is_ip_locked($ip)
{
  $lockout_key = mu_get_lockout_transient_key($ip);
  return get_transient($lockout_key) !== false;
}

/**
 * Get remaining lockout time in seconds
 */
function mu_get_lockout_remaining($ip)
{
  $lockout_key = mu_get_lockout_transient_key($ip);
  $lockout_time = get_transient($lockout_key);

  if ($lockout_time === false) {
    return 0;
  }

  $remaining = $lockout_time - time();
  return max(0, $remaining);
}

/**
 * Block login if IP is locked out
 */
add_filter(
  'authenticate',
  static function ($user, $username, $password) {
    // Skip if already an error
    if (is_wp_error($user)) {
      return $user;
    }

    $ip = mu_get_user_ip();

    // Check if IP is locked
    if (mu_is_ip_locked($ip)) {
      $remaining = mu_get_lockout_remaining($ip);
      $hours = ceil($remaining / HOUR_IN_SECONDS);

      // Log the blocked attempt
      if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        error_log("Login blocked for IP: {$ip} - {$hours} hours remaining");
      }

      return new WP_Error(
        'login_locked',
        sprintf(
          '<strong>ERROR</strong>: Too many failed login attempts. Please try again in %d hours.',
          $hours
        )
      );
    }

    return $user;
  },
  30,
  3
);

/**
 * Track failed login attempts
 */
add_action(
  'wp_login_failed',
  static function ($username) {
    $ip = mu_get_user_ip();
    $transient_key = mu_get_login_transient_key($ip);
    $lockout_key = mu_get_lockout_transient_key($ip);

    // Get current attempts
    $attempts = get_transient($transient_key);
    if ($attempts === false) {
      $attempts = 0;
    }

    // Increment attempts
    $attempts++;

    // Log the failed attempt
    if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
      error_log("Failed login attempt #{$attempts} for IP: {$ip} (Username: {$username})");
    }

    // Check if we should lock the IP
    if ($attempts >= MU_LOGIN_ATTEMPTS_MAX) {
      // Lock the IP for 24 hours
      $lockout_until = time() + MU_LOGIN_LOCKOUT_DURATION;
      set_transient($lockout_key, $lockout_until, MU_LOGIN_LOCKOUT_DURATION);

      // Clear attempts counter
      delete_transient($transient_key);

      // Log the lockout
      if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        error_log("IP locked for 24 hours: {$ip} (Username: {$username})");
      }

      // Optional: Send email notification to admin
      $admin_email = get_option('admin_email');
      $site_name = get_bloginfo('name');
      $subject = "[{$site_name}] IP Address Locked Out";
      $message = "An IP address has been locked out due to too many failed login attempts.\n\n";
      $message .= "IP Address: {$ip}\n";
      $message .= "Username attempted: {$username}\n";
      $message .= "Locked until: " . wp_date('Y-m-d H:i:s', $lockout_until) . "\n";

      wp_mail($admin_email, $subject, $message);
    } else {
      // Store updated attempts count for 1 hour
      set_transient($transient_key, $attempts, HOUR_IN_SECONDS);
    }
  },
  10,
  1
);

/**
 * Clear failed attempts on successful login
 */
add_action(
  'wp_login',
  static function ($username, $user) {
    $ip = mu_get_user_ip();
    $transient_key = mu_get_login_transient_key($ip);

    // Clear any failed attempts for this IP
    delete_transient($transient_key);

    // Log successful login after failed attempts
    if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
      error_log("Successful login for IP: {$ip} (Username: {$username})");
    }
  },
  10,
  2
);

/**
 * Add custom error message styling
 */
add_action(
  'login_enqueue_scripts',
  static function () {
    $ip = mu_get_user_ip();
    if (mu_is_ip_locked($ip)) {
      echo '<style>
        #login_error {
          border-left-color: #dc3232;
          background: #fff;
          box-shadow: 0 1px 3px rgba(0,0,0,0.13);
        }
      </style>';
    }
  },
  10,
  0
);

/**
 * Show admin notice with statistics
 */
add_action(
  'admin_notices',
  static function () {
    // Only show on dashboard for admins
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'dashboard' || !current_user_can('manage_options')) {
      return;
    }

    // Check if user has dismissed the notice
    if (get_user_meta(get_current_user_id(), 'login_limit_notice_dismissed', true)) {
      return;
    }

    echo '<div class="notice notice-info is-dismissible" id="login-limit-notice">
      <p>
        <strong>Login Attempt Limiting Active:</strong> IP addresses are blocked for 24 hours after 4 failed login attempts. 
        Admins receive email notifications when IPs are locked out.
      </p>
    </div>';

    echo '<script>
      document.addEventListener("DOMContentLoaded", function() {
        const notice = document.getElementById("login-limit-notice");
        if (notice) {
          notice.addEventListener("click", function(e) {
            if (e.target.classList.contains("notice-dismiss")) {
              fetch(ajaxurl, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "action=dismiss_login_limit_notice&nonce=' . wp_create_nonce('login_limit_nonce') . '"
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
  'wp_ajax_dismiss_login_limit_notice',
  static function () {
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'login_limit_nonce')) {
      wp_die('Security check failed');
    }

    update_user_meta(get_current_user_id(), 'login_limit_notice_dismissed', true);
    wp_die();
  },
  10,
  0
);
