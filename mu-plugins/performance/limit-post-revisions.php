<?php

/**
 * Limit WordPress post revisions
 *
 * Plugin name:       Limit Post Revisions
 * Plugin URI:        https://openwpclub.com
 * Description:       Limits the number of post revisions stored in the database to reduce bloat and improve performance.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       limit-post-revisions
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Limit post revisions to 5 per post
 *
 * This filter allows runtime configuration of revision limits
 * even if WP_POST_REVISIONS is defined in wp-config.php
 */
add_filter(
    'wp_revisions_to_keep',
    static function ($num, $post) {
        // Limit to 5 revisions for all post types
        return 5;
    },
    10,
    2
);
