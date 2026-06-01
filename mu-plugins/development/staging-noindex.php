<?php

/**
 * Staging / non-production noindex
 *
 * Plugin name:       Staging Robots Noindex
 * Plugin URI:        https://openwpclub.com
 * Description:       Outputs X-Robots-Tag: noindex HTTP header and adds noindex to wp_robots on any environment that is not 'production'. Prevents staging or local sites from being indexed without editing wp-config.php.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       staging-noindex
 */

defined('ABSPATH') or die();

// Only activate on non-production environments
if (wp_get_environment_type() === 'production') {
    return;
}

// HTTP header — respected by crawlers that don't execute JavaScript
add_action(
    'send_headers',
    static function () {
        if (!is_admin()) {
            header('X-Robots-Tag: noindex, nofollow');
        }
    },
    10,
    0
);

// wp_robots filter — adds noindex to the <meta name="robots"> tag
add_filter(
    'wp_robots',
    static function (array $robots): array {
        $robots['noindex']  = true;
        $robots['nofollow'] = true;
        unset($robots['max-image-preview']); // Not needed when noindex
        return $robots;
    },
    PHP_INT_MAX,
    1
);

// Show a persistent admin bar indicator so it's obvious which env you're on
add_action(
    'admin_bar_menu',
    static function (WP_Admin_Bar $bar) {
        $env = wp_get_environment_type();
        $bar->add_node([
            'id'    => 'mu-staging-noindex',
            'title' => sprintf(
                '<span style="color:#f0c33c">&#9888; %s: %s — noindex active</span>',
                esc_html__('ENV', 'staging-noindex'),
                esc_html(strtoupper($env))
            ),
            'href'  => false,
        ]);
    },
    100,
    1
);
