<?php

/**
 * A utility plugin to disable post-by-email functionality
 *
 * Plugin name:       Disable Post by Email
 * Plugin URI:        https://openwpclub.com
 * Description:       Disables WordPress post-by-email functionality to improve security and reduce attack surface.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-post-by-email
 */

add_filter(
  'enable_post_by_email_configuration',
  '__return_false',
  PHP_INT_MAX,
  0
);
