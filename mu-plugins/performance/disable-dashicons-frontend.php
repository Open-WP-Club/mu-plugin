<?php
/**
 * Plugin Name: Disable Dashicons Frontend
 * Description: Remove Dashicons CSS from frontend for non-logged-in users to improve performance (saves ~40KB)
 * Version: 1.0.0
 * Author: OpenWP Club
 * Author URI: https://openwpclub.com
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Disable Dashicons on frontend for non-logged-in users
 *
 * Dashicons are only needed in wp-admin and for logged-in users with admin bar.
 * Removing them on frontend saves ~40KB (25KB CSS + 15KB font file)
 */
add_action('wp_enqueue_scripts', function() {
    // Keep Dashicons if user is logged in (admin bar needs them)
    if (!is_user_logged_in()) {
        wp_dequeue_style('dashicons');
        wp_deregister_style('dashicons');
    }
}, 100);

/**
 * Optional: Remove Dashicons from login page too
 * Uncomment if you don't use Dashicons in custom login page designs
 */
// add_action('login_enqueue_scripts', function() {
//     wp_dequeue_style('dashicons');
//     wp_deregister_style('dashicons');
// });
