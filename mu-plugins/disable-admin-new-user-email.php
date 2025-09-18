<?php

/**
 * A utility plugin to disable new user registration email notifications to admin
 *
 * Plugin name:       Disable Admin New User Email
 * Plugin URI:        https://openwpclub.com
 * Description:       Prevents admin from receiving email notifications when new users register. Users still receive their welcome email.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-admin-new-user-email
 */

// Remove default new user notification (which notifies both admin and user)
remove_action('register_new_user', 'wp_send_new_user_notifications');

// Add custom notification that only notifies the user
add_action(
  'register_new_user',
  static function ($user_id) {
    wp_new_user_notification($user_id, null, 'user');
  },
  10,
  1
);
