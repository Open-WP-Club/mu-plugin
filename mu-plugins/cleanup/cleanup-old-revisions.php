<?php

/**
 * A utility plugin to cleanup old post revisions
 *
 * Plugin name:       Cleanup Old Revisions
 * Plugin URI:        https://openwpclub.com
 * Description:       Automatically removes old post revisions older than 30 days and limits revisions to 5 per post.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       cleanup-old-revisions
 */

// Limit post revisions to 5 (if not already defined)
if (!defined('WP_POST_REVISIONS')) {
  define('WP_POST_REVISIONS', 5);
}
