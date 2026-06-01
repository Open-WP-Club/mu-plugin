<?php

/**
 * Remove unused admin menu items
 *
 * Plugin name:       Remove Unused Admin Menus
 * Plugin URI:        https://openwpclub.com
 * Description:       Removes admin menu items defined in MU_REMOVE_ADMIN_MENUS. Useful for client sites where certain sections are irrelevant.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       remove-unused-admin-menus
 */

defined('ABSPATH') or die();

/**
 * Define in wp-config.php (menu slug => minimum capability to hide from):
 *
 *   define('MU_REMOVE_ADMIN_MENUS', [
 *       'edit.php',            // Posts
 *       'edit-comments.php',   // Comments
 *       'tools.php',           // Tools
 *   ]);
 *
 * The items are hidden from ALL users. To restrict by role, use the
 * 'mu_remove_admin_menus' filter and check current_user_can() there.
 */
if (!defined('MU_REMOVE_ADMIN_MENUS') || empty(MU_REMOVE_ADMIN_MENUS)) {
    return;
}

add_action(
    'admin_menu',
    static function () {
        $slugs = apply_filters('mu_remove_admin_menus', MU_REMOVE_ADMIN_MENUS);

        foreach ((array) $slugs as $slug) {
            remove_menu_page($slug);
        }
    },
    999,
    0
);
