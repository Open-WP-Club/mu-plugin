<?php

/**
 * Strip EXIF metadata from uploaded images
 *
 * Plugin name:       Strip EXIF Metadata
 * Plugin URI:        https://openwpclub.com
 * Description:       Removes GPS coordinates, camera model, and other EXIF data from uploaded JPEG images at save time using GD, reducing privacy exposure.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       strip-exif-metadata
 */

defined('ABSPATH') or die();

add_filter(
    'wp_handle_upload',
    static function (array $upload): array {
        $type = $upload['type'] ?? '';

        if (!isset($upload['file'])) {
            return $upload;
        }

        $file = $upload['file'];

        if ($type === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
            $image = @imagecreatefromjpeg($file);
            if ($image) {
                $quality = (int) apply_filters('mu_jpeg_quality', 90);
                imagejpeg($image, $file, $quality); // Re-save strips EXIF
                imagedestroy($image);
            }
        } elseif ($type === 'image/png' && function_exists('imagecreatefrompng')) {
            $image = @imagecreatefrompng($file);
            if ($image) {
                imagepng($image, $file, 9);
                imagedestroy($image);
            }
        }

        return $upload;
    },
    10,
    1
);
