<?php

/**
 * A utility plugin to sanitize uploaded file names
 *
 * Plugin name:       Sanitize File Name
 * Plugin URI:        https://openwpclub.com
 * Description:       Automatically removes accents and special characters from uploaded file names for better compatibility.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       sanitize-file-name
 */

// Prevent direct access
defined('ABSPATH') or die();

add_filter('sanitize_file_name', 'remove_accents');
