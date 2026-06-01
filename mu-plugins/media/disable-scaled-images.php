<?php

/**
 * Disable scaled image generation
 *
 * Plugin name:       Disable Scaled Image Generation
 * Plugin URI:        https://openwpclub.com
 * Description:       Prevents WordPress from creating a -scaled copy of large images (added in WP 5.3). Keeps original dimensions intact for sites that do not require server-side downscaling.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-scaled-images
 */

defined('ABSPATH') or die();

// Returning false disables the big image threshold entirely
add_filter('big_image_size_threshold', '__return_false');
