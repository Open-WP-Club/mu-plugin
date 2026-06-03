<?php

/**
 * Disable WordPress core XML sitemap
 *
 * Plugin name:       Disable Core XML Sitemap
 * Plugin URI:        https://openwpclub.com
 * Description:       Disables the auto-generated /sitemap.xml introduced in WordPress 5.5 to prevent duplicate sitemaps when Rank Math, Yoast SEO, or another sitemap plugin is active.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-core-sitemap
 */

defined('ABSPATH') or die();

add_filter('wp_sitemaps_enabled', '__return_false');
