<?php

/**
 * Plugin Name:       Media Accessibility Check
 * Plugin URI:        https://openwpclub.com
 * Description:       Flags images that need alt-text review while allowing editors to mark intentionally decorative images.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       media-accessibility-check
 */

defined('ABSPATH') || exit;

add_filter('attachment_fields_to_edit', static function ($fields, $post) {
    if (0 !== strpos((string) $post->post_mime_type, 'image/')) {
        return $fields;
    }

    $checked = '1' === get_post_meta($post->ID, '_mu_decorative_image', true);
    $fields['mu_decorative_image'] = [
        'label' => 'Decorative image',
        'input' => 'html',
        'html'  => '<label><input type="checkbox" name="attachments[' . esc_attr((string) $post->ID) . '][mu_decorative_image]" value="1" ' . checked($checked, true, false) . '> Uses an intentionally empty alt attribute</label>',
        'helps' => 'Use only when the image adds no information or function.',
    ];
    return $fields;
}, 10, 2);

add_filter('attachment_fields_to_save', static function ($post, $attachment) {
    if (!current_user_can('edit_post', $post['ID'])) {
        return $post;
    }

    if (!empty($attachment['mu_decorative_image'])) {
        update_post_meta($post['ID'], '_mu_decorative_image', '1');
        update_post_meta($post['ID'], '_wp_attachment_image_alt', '');
    } else {
        delete_post_meta($post['ID'], '_mu_decorative_image');
    }
    return $post;
}, 10, 2);

add_filter('manage_media_columns', static function ($columns) {
    $columns['mu_alt_status'] = 'Alt status';
    return $columns;
});

add_action('manage_media_custom_column', static function ($column, $attachment_id) {
    if ('mu_alt_status' !== $column || !wp_attachment_is_image($attachment_id)) {
        return;
    }

    if ('1' === get_post_meta($attachment_id, '_mu_decorative_image', true)) {
        echo 'Decorative';
        return;
    }

    $alt = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
    echo $alt ? '<span style="color:#008a20">Present</span>' : '<strong style="color:#b32d2e">Review</strong>';
}, 10, 2);

add_action('wp_dashboard_setup', static function () {
    if (!current_user_can('upload_files')) {
        return;
    }

    wp_add_dashboard_widget('mu_media_accessibility_check', 'Media Accessibility', static function () {
        $query = new WP_Query([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'relation' => 'OR',
                    ['key' => '_wp_attachment_image_alt', 'compare' => 'NOT EXISTS'],
                    ['key' => '_wp_attachment_image_alt', 'value' => '', 'compare' => '='],
                ],
                [
                    'relation' => 'OR',
                    ['key' => '_mu_decorative_image', 'compare' => 'NOT EXISTS'],
                    ['key' => '_mu_decorative_image', 'value' => '1', 'compare' => '!='],
                ],
            ],
        ]);

        echo '<p><strong>' . esc_html((string) $query->found_posts) . '</strong> image(s) need alt-text review.</p>';
        if ($query->posts) {
            echo '<ul>';
            foreach ($query->posts as $attachment_id) {
                echo '<li><a href="' . esc_url(get_edit_post_link($attachment_id)) . '">' . esc_html(get_the_title($attachment_id) ?: 'Untitled image') . '</a></li>';
            }
            echo '</ul>';
        }
        echo '<p><em>Empty alt text can be correct for decorative images; mark those images explicitly instead of adding redundant text.</em></p>';
    });
});
