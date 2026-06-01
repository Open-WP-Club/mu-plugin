<?php

/**
 * Auto-rotate JPEG images on upload
 *
 * Plugin name:       Auto-Rotate JPEG on Upload
 * Plugin URI:        https://openwpclub.com
 * Description:       Reads EXIF orientation metadata from uploaded JPEG files and rotates the image to the correct orientation before thumbnails are generated. Fixes sideways photos from mobile cameras.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       auto-rotate-jpeg
 */

defined('ABSPATH') or die();

add_filter(
    'wp_handle_upload',
    static function (array $upload): array {
        if (!isset($upload['file']) || ($upload['type'] ?? '') !== 'image/jpeg') {
            return $upload;
        }

        if (!function_exists('exif_read_data') || !function_exists('imagecreatefromjpeg')) {
            return $upload;
        }

        $file        = $upload['file'];
        $exif        = @exif_read_data($file);
        $orientation = isset($exif['Orientation']) ? (int) $exif['Orientation'] : 1;

        if ($orientation === 1) {
            return $upload; // Already correct
        }

        $image = @imagecreatefromjpeg($file);
        if (!$image) {
            return $upload;
        }

        $rotated = match ($orientation) {
            3       => imagerotate($image, 180, 0),
            6       => imagerotate($image, -90, 0),
            8       => imagerotate($image, 90, 0),
            default => null,
        };

        if ($rotated) {
            $quality = (int) apply_filters('mu_jpeg_quality', 90);
            imagejpeg($rotated, $file, $quality);
            imagedestroy($rotated);
        }

        imagedestroy($image);

        return $upload;
    },
    10,
    1
);
