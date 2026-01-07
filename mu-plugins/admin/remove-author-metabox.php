<?php

/**
 * A utility plugin to remove author meta box from posts and pages
 *
 * Plugin name:       Remove Author Metabox
 * Plugin URI:        https://openwpclub.com
 * Description:       Removes the author meta box from post and page edit screens in WordPress admin.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       remove-author-metabox
 */

// Prevent direct access
defined('ABSPATH') or die();

function mu_remove_author_metabox()
{
  // Remove author meta box from posts
  remove_meta_box('authordiv', 'post', 'normal');

  // Remove author meta box from pages
  remove_meta_box('authordiv', 'page', 'normal');

  // Remove author meta box from custom post types that support 'author'
  $post_types = get_post_types(['public' => true, '_builtin' => false]);

  foreach ($post_types as $post_type) {
    if (post_type_supports($post_type, 'author')) {
      remove_meta_box('authordiv', $post_type, 'normal');
    }
  }
}

add_action('admin_menu', 'mu_remove_author_metabox');
