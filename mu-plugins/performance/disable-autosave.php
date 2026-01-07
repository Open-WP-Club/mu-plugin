<?php

/**
 * A utility plugin to disable WordPress autosave functionality
 *
 * Plugin name:       Disable Autosave
 * Plugin URI:        https://openwpclub.com
 * Description:       Disables WordPress autosave functionality to improve performance and prevent unwanted draft saves.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-autosave
 */

// Prevent direct access
defined('ABSPATH') or die();

function mu_disable_autosave()
{
  wp_deregister_script('autosave');
}
add_action('admin_init', 'mu_disable_autosave');
