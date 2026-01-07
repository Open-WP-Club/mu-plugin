<?php

/**
 * A utility plugin to disable admin email confirmation prompts
 *
 * Plugin name:       Disable Admin Email Confirmation
 * Plugin URI:        https://openwpclub.com
 * Description:       Disables the periodic admin email confirmation prompt that WordPress shows every 6 months.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-admin-email-confirmation
 */

// Prevent direct access
defined('ABSPATH') or die();

add_filter(
  'admin_email_check_interval',
  '__return_zero',
  PHP_INT_MAX,
  0
);
