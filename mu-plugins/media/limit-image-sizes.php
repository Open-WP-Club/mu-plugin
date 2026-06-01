<?php

/**
 * Limit generated intermediate image sizes
 *
 * Plugin name:       Limit Image Sizes
 * Plugin URI:        https://openwpclub.com
 * Description:       Removes unnecessary intermediate image sizes (medium_large, 1536x1536, 2048x2048) that WordPress generates by default. Reduces disk usage and upload processing time. Keep list via 'mu_allowed_image_sizes' filter.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       limit-image-sizes
 */

defined('ABSPATH') or die();

/**
 * Sizes to remove. Customise via filter:
 *
 *   add_filter('mu_remove_image_sizes', function($sizes) {
 *       return array_diff($sizes, ['medium_large']); // keep medium_large
 *   });
 */
add_filter(
    'intermediate_image_sizes_advanced',
    static function (array $sizes): array {
        $remove = apply_filters('mu_remove_image_sizes', [
            'medium_large',
            '1536x1536',
            '2048x2048',
        ]);

        foreach ($remove as $size) {
            unset($sizes[$size]);
        }

        return $sizes;
    },
    10,
    1
);
