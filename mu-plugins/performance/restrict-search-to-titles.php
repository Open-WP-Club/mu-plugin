<?php

/**
 * Restrict frontend search to post titles only
 *
 * Plugin name:       Restrict Search to Titles
 * Plugin URI:        https://openwpclub.com
 * Description:       Rewrites the frontend search WHERE clause to match post_title only, instead of scanning post_content and excerpt with LIKE. Reduces query cost on large catalogs/archives that don't need full-text search. Skip on sites where searching body content is required.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       restrict-search-to-titles
 */

defined('ABSPATH') or die();

add_filter(
    'posts_search',
    static function ($search, WP_Query $query) {
        global $wpdb;

        if (empty($search) || !$query->is_search() || !$query->is_main_query()) {
            return $search;
        }

        $term = $query->get('s');
        if ($term === '') {
            return $search;
        }

        $like = '%' . $wpdb->esc_like($term) . '%';

        return $wpdb->prepare(" AND {$wpdb->posts}.post_title LIKE %s ", $like);
    },
    10,
    2
);
