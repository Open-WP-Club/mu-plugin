<?php

/**
 * Disable RSS / Atom feeds
 *
 * Plugin name:       Disable RSS Feeds
 * Plugin URI:        https://openwpclub.com
 * Description:       Redirects all RSS and Atom feed URLs to the homepage. Use only on sites that have no blog or do not need syndication.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-rss-feeds
 */

defined('ABSPATH') or die();

/**
 * Redirect any feed request to the homepage.
 */
function mu_redirect_feeds(): void
{
    wp_redirect(home_url('/'), 301);
    exit;
}

add_action('do_feed',              'mu_redirect_feeds', 1);
add_action('do_feed_rdf',          'mu_redirect_feeds', 1);
add_action('do_feed_rss',          'mu_redirect_feeds', 1);
add_action('do_feed_rss2',         'mu_redirect_feeds', 1);
add_action('do_feed_atom',         'mu_redirect_feeds', 1);
add_action('do_feed_rss2_comments','mu_redirect_feeds', 1);
add_action('do_feed_atom_comments','mu_redirect_feeds', 1);

// Remove feed discovery links from <head>
// Note: also handled by clean-head-output.php if active — safe to duplicate.
remove_action('wp_head', 'feed_links',       2);
remove_action('wp_head', 'feed_links_extra', 3);
