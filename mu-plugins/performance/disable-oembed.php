<?php

/**
 * Disables WordPress oEmbed functionality
 *
 * Plugin name:       Disable oEmbed
 * Plugin URI:        https://openwpclub.com
 * Description:       Disables WordPress oEmbed functionality to reduce HTTP requests and improve performance. Use if you don't embed external content.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-oembed
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Remove oEmbed discovery links from head
remove_action('wp_head', 'wp_oembed_add_discovery_links');

// Remove oEmbed-specific JavaScript from front-end and back-end
remove_action('wp_head', 'wp_oembed_add_host_js');

// Remove all embeds rewrite rules
add_filter('rewrite_rules_array', function($rules) {
    foreach ($rules as $rule => $rewrite) {
        if (strpos($rewrite, 'embed=true') !== false) {
            unset($rules[$rule]);
        }
    }
    return $rules;
});

// Disable oEmbed auto discovery
add_filter('embed_oembed_discover', '__return_false');

// Remove oEmbed route from REST API
add_filter('rest_endpoints', function($endpoints) {
    if (isset($endpoints['/oembed/1.0/embed'])) {
        unset($endpoints['/oembed/1.0/embed']);
    }
    return $endpoints;
});

// Remove oEmbed JavaScript and CSS
function mu_disable_oembed_scripts() {
    wp_deregister_script('wp-embed');
}
add_action('wp_footer', 'mu_disable_oembed_scripts');

// Disable embeds on init
add_action('init', function() {
    // Remove the oembed/1.0/proxy REST route
    remove_action('rest_api_init', 'wp_oembed_register_route');

    // Turn off oEmbed auto discovery
    remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);

    // Remove oEmbed-specific query variables
    add_filter('query_vars', function($query_vars) {
        $query_vars = array_diff($query_vars, ['embed']);
        return $query_vars;
    });
}, 9999);
