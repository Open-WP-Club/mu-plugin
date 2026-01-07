<?php
/**
 * Plugin Name: AVIF Support
 * Description: Add AVIF image format support for uploads and display (next-generation image format, better compression than WebP)
 * Version: 1.0.0
 * Author: OpenWP Club
 * Author URI: https://openwpclub.com
 * License: GPL v2 or later
 */

/**
 *   Why AVIF?
 *    - ~30% smaller files than WebP at same quality
 *    - Better compression than JPEG/PNG/WebP
 *    - Supports HDR and wide color gamut
 *    - Modern browsers have good support (Chrome 85+, Firefox 93+, Safari 16+)
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add AVIF to allowed upload MIME types
 */
add_filter('upload_mimes', function($mimes) {
    $mimes['avif'] = 'image/avif';
    return $mimes;
});

/**
 * Add AVIF to list of allowed file extensions
 */
add_filter('wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {
    $filetype = wp_check_filetype($filename, $mimes);

    if ($filetype['ext'] === 'avif') {
        $data['ext'] = 'avif';
        $data['type'] = 'image/avif';
    }

    return $data;
}, 10, 4);

/**
 * Add AVIF support to file type checking
 */
add_filter('file_is_displayable_image', function($result, $path) {
    if (!$result) {
        $info = @getimagesize($path);
        if (isset($info['mime']) && $info['mime'] === 'image/avif') {
            return true;
        }
    }
    return $result;
}, 10, 2);

/**
 * Add AVIF to image editor supported formats (if GD/Imagick supports it)
 */
add_filter('image_editor_output_format', function($formats) {
    // Only add if server supports AVIF
    if (function_exists('imageavif') || (extension_loaded('imagick') && in_array('AVIF', \Imagick::queryFormats()))) {
        $formats['image/avif'] = 'avif';
    }
    return $formats;
});

/**
 * Set proper MIME type for AVIF files
 */
add_filter('getimagesize_mimes_to_exts', function($mime_to_ext) {
    $mime_to_ext['image/avif'] = 'avif';
    return $mime_to_ext;
});

/**
 * Add AVIF to attachment MIME type icon
 */
add_filter('wp_mime_type_icon', function($icon, $mime, $post_id) {
    if ($mime === 'image/avif') {
        // Use generic image icon
        return wp_includes_url('images/media/default.png');
    }
    return $icon;
}, 10, 3);

/**
 * Display admin notice if server doesn't support AVIF
 */
add_action('admin_notices', function() {
    // Only show to admins
    if (!current_user_can('manage_options')) {
        return;
    }

    // Check if GD or Imagick supports AVIF
    $gd_support = function_exists('imageavif');
    $imagick_support = extension_loaded('imagick') && in_array('AVIF', \Imagick::queryFormats());

    if (!$gd_support && !$imagick_support) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>AVIF Support:</strong> Your server can accept AVIF uploads, but cannot generate thumbnails. ';
        echo 'PHP GD (8.1+) or ImageMagick with AVIF support is required for image processing.</p>';
        echo '</div>';
    }
});
