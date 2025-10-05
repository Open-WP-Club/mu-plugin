<?php

/**
 * A utility plugin to enable secure SVG upload support
 *
 * Plugin name:       Enable SVG Upload
 * Plugin URI:        https://openwpclub.com
 * Description:       Enables SVG file uploads with security sanitization to prevent XSS attacks. Adds media library preview support.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       enable-svg-upload
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Add SVG to allowed upload mime types
 */
add_filter(
  'upload_mimes',
  static function ($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
  },
  10,
  1
);

/**
 * Check and sanitize SVG files on upload
 */
add_filter(
  'wp_handle_upload_prefilter',
  static function ($file) {
    // Only process SVG files
    if (!isset($file['type']) || $file['type'] !== 'image/svg+xml') {
      return $file;
    }

    // Check if file exists and is readable
    if (!file_exists($file['tmp_name']) || !is_readable($file['tmp_name'])) {
      $file['error'] = 'Unable to read SVG file.';
      return $file;
    }

    // Read file content
    $svg_content = file_get_contents($file['tmp_name']);

    if ($svg_content === false) {
      $file['error'] = 'Unable to read SVG file content.';
      return $file;
    }

    // Sanitize SVG content
    $sanitized_content = mu_sanitize_svg($svg_content);

    if ($sanitized_content === false) {
      $file['error'] = 'SVG file contains potentially malicious code and was blocked.';
      return $file;
    }

    // Write sanitized content back to file
    if (file_put_contents($file['tmp_name'], $sanitized_content) === false) {
      $file['error'] = 'Unable to save sanitized SVG file.';
      return $file;
    }

    return $file;
  },
  10,
  1
);

/**
 * Sanitize SVG content to remove potentially dangerous elements
 *
 * @param string $svg_content Raw SVG content
 * @return string|false Sanitized SVG content or false if dangerous content detected
 */
function mu_sanitize_svg($svg_content)
{
  // List of dangerous tags to remove
  $dangerous_tags = [
    'script',
    'iframe',
    'object',
    'embed',
    'link',
    'style',
    'foreignObject',
    'base',
    'form',
    'input',
    'textarea',
    'button',
  ];

  // List of dangerous attributes to remove
  $dangerous_attributes = [
    'onload',
    'onerror',
    'onmouseover',
    'onmouseout',
    'onclick',
    'ondblclick',
    'onmousedown',
    'onmouseup',
    'onmousemove',
    'onkeydown',
    'onkeyup',
    'onkeypress',
    'onfocus',
    'onblur',
    'onchange',
    'onsubmit',
    'onreset',
    'onselect',
    'onabort',
    'onbeforeunload',
    'onunload',
    'onresize',
    'onscroll',
  ];

  // Check for dangerous patterns before parsing
  foreach ($dangerous_tags as $tag) {
    if (stripos($svg_content, '<' . $tag) !== false) {
      error_log("SVG Upload: Blocked file containing dangerous tag: {$tag}");
      return false;
    }
  }

  // Check for javascript: protocol
  if (stripos($svg_content, 'javascript:') !== false) {
    error_log('SVG Upload: Blocked file containing javascript: protocol');
    return false;
  }

  // Check for data: URIs with script
  if (preg_match('/data:.*script/i', $svg_content)) {
    error_log('SVG Upload: Blocked file containing data URI with script');
    return false;
  }

  // Load SVG with DOMDocument for deeper sanitization
  $dom = new DOMDocument();
  $dom->formatOutput = false;
  $dom->preserveWhiteSpace = true;
  $dom->strictErrorChecking = false;

  // Suppress warnings for malformed XML
  libxml_use_internal_errors(true);

  // Load SVG content
  if (!$dom->loadXML($svg_content, LIBXML_NONET | LIBXML_NOENT)) {
    error_log('SVG Upload: Failed to parse SVG file');
    libxml_clear_errors();
    return false;
  }

  libxml_clear_errors();

  // Get all elements
  $xpath = new DOMXPath($dom);
  $all_elements = $xpath->query('//*');

  if ($all_elements === false) {
    return false;
  }

  // Remove dangerous attributes from all elements
  foreach ($all_elements as $element) {
    foreach ($dangerous_attributes as $attr) {
      if ($element->hasAttribute($attr)) {
        error_log("SVG Upload: Removed dangerous attribute: {$attr}");
        $element->removeAttribute($attr);
      }
    }

    // Check for xlink:href with javascript
    if ($element->hasAttribute('xlink:href')) {
      $href = $element->getAttribute('xlink:href');
      if (stripos($href, 'javascript:') !== false || stripos($href, 'data:') !== false) {
        error_log('SVG Upload: Removed dangerous xlink:href');
        $element->removeAttribute('xlink:href');
      }
    }

    // Check for href with javascript
    if ($element->hasAttribute('href')) {
      $href = $element->getAttribute('href');
      if (stripos($href, 'javascript:') !== false || stripos($href, 'data:') !== false) {
        error_log('SVG Upload: Removed dangerous href');
        $element->removeAttribute('href');
      }
    }
  }

  // Save sanitized SVG
  $sanitized = $dom->saveXML();

  return $sanitized;
}

/**
 * Fix SVG display in media library
 */
add_filter(
  'wp_check_filetype_and_ext',
  static function ($data, $file, $filename, $mimes) {
    $filetype = wp_check_filetype($filename, $mimes);

    if ($filetype['ext'] === 'svg' || $filetype['ext'] === 'svgz') {
      $data['ext'] = $filetype['ext'];
      $data['type'] = $filetype['type'];
    }

    return $data;
  },
  10,
  4
);

/**
 * Enable SVG preview in media library
 */
add_filter(
  'wp_prepare_attachment_for_js',
  static function ($response, $attachment, $meta) {
    if ($response['type'] === 'image' && $response['subtype'] === 'svg+xml') {
      $response['image'] = [
        'src' => $response['url'],
        'width' => 300,
        'height' => 300,
      ];
      $response['thumb'] = [
        'src' => $response['url'],
        'width' => 150,
        'height' => 150,
      ];
      $response['sizes'] = [
        'full' => [
          'url' => $response['url'],
          'width' => 300,
          'height' => 300,
          'orientation' => 'portrait',
        ],
      ];
    }

    return $response;
  },
  10,
  3
);

/**
 * Add CSS to properly display SVG in media library
 */
add_action(
  'admin_head',
  static function () {
    $screen = get_current_screen();
    if ($screen && ($screen->base === 'upload' || $screen->base === 'post')) {
      echo '<style>
        img[src$=".svg"] {
          width: 100% !important;
          height: auto !important;
        }
        .media-icon img[src$=".svg"] {
          width: 100%;
          height: auto;
        }
      </style>';
    }
  },
  10,
  0
);

/**
 * Add admin notice about SVG security
 */
add_action(
  'admin_notices',
  static function () {
    // Only show on media library page
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'upload') {
      return;
    }

    // Check if user has already dismissed the notice
    if (get_user_meta(get_current_user_id(), 'svg_security_notice_dismissed', true)) {
      return;
    }

    echo '<div class="notice notice-info is-dismissible" id="svg-security-notice">
      <p>
        <strong>SVG Upload Enabled:</strong> SVG files are now allowed with automatic security sanitization. 
        Only upload SVG files from trusted sources.
      </p>
    </div>';

    echo '<script>
      document.addEventListener("DOMContentLoaded", function() {
        const notice = document.getElementById("svg-security-notice");
        if (notice) {
          notice.addEventListener("click", function(e) {
            if (e.target.classList.contains("notice-dismiss")) {
              fetch(ajaxurl, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "action=dismiss_svg_security_notice&nonce=' . wp_create_nonce('svg_security_nonce') . '"
              });
            }
          });
        }
      });
    </script>';
  },
  10,
  0
);

/**
 * Handle security notice dismissal
 */
add_action(
  'wp_ajax_dismiss_svg_security_notice',
  static function () {
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'svg_security_nonce')) {
      wp_die('Security check failed');
    }

    update_user_meta(get_current_user_id(), 'svg_security_notice_dismissed', true);
    wp_die();
  },
  10,
  0
);
