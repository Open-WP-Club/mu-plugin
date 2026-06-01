<?php

/**
 * Disable theme update notices in the admin
 *
 * Plugin name:       Disable Theme Update Notices
 * Plugin URI:        https://openwpclub.com
 * Description:       Hides "new version available" notices on the Themes screen for environments where the theme is deployed via CI/CD rather than updated through the admin.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-theme-update-notices
 */

defined('ABSPATH') or die();

// Return an empty update object so WordPress shows no theme update notices
add_filter(
    'pre_site_transient_update_themes',
    static function ($transient) {
        $transient           = new stdClass();
        $transient->checked  = [];
        $transient->response = [];
        return $transient;
    },
    PHP_INT_MAX,
    1
);

// Remove the update nag from the themes list screen
add_action(
    'admin_head-themes.php',
    static function () {
        remove_action('admin_notices', 'update_nag', 3);
    },
    10,
    0
);
