<?php

/**
 * Disable file editing in WordPress admin
 *
 * Plugin name: Disable File Editing
 * Plugin URI: https://openwpclub.com
 * Description: Prevents editing of theme/plugin files from admin
 * Version: 1.0.0
 * Author: OpenWP Club
 * License: Apache-2.0
 */

// Disable file editing
if (!defined('DISALLOW_FILE_EDIT')) {
  define('DISALLOW_FILE_EDIT', true);
}

// Remove edit links from admin
function mu_remove_file_edit_capability()
{
  if (!current_user_can('manage_options')) {
    return;
  }

  // Remove theme editor
  remove_submenu_page('themes.php', 'theme-editor.php');
  // Remove plugin editor  
  remove_submenu_page('plugins.php', 'plugin-editor.php');
}
add_action('admin_init', 'mu_remove_file_edit_capability');
