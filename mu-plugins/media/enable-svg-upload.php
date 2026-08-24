<?php

/**
 * A utility plugin to enable secure SVG upload support
 *
 * Plugin name:       Enable SVG Upload
 * Plugin URI:        https://openwpclub.com
 * Description:       Enables sanitized SVG uploads and Media Library previews. Still required on WordPress 7.1+, whose SVG Icon API does not enable SVG media uploads.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.2.0
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
    $extension = isset($file['name']) ? strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) : '';

    // MIME values supplied by clients are not trustworthy; use the extension to
    // decide whether the file must pass through the SVG sanitizer.
    if ($extension !== 'svg') {
      return $file;
    }

    $file['type'] = 'image/svg+xml';
    $temporary_file = $file['tmp_name'] ?? '';

    // Check if file exists and is readable
    if ($temporary_file === '' || !is_file($temporary_file) || !is_readable($temporary_file)) {
      $file['error'] = 'Unable to read SVG file.';
      return $file;
    }

    // Read file content
    $svg_content = file_get_contents($temporary_file);

    if ($svg_content === false) {
      $file['error'] = 'Unable to read SVG file content.';
      return $file;
    }

    // Sanitize SVG content
    $sanitized_content = openwpclub_sanitize_svg($svg_content);

    if ($sanitized_content === false) {
      $file['error'] = 'SVG file contains potentially malicious code and was blocked.';
      return $file;
    }

    // Write sanitized content back to file
    if (file_put_contents($temporary_file, $sanitized_content) === false) {
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
function openwpclub_sanitize_svg($svg_content)
{
  if (!class_exists('DOMDocument')) {
    error_log('SVG Upload: The PHP DOM extension is required');
    return false;
  }

  // DTDs and entities are unnecessary for images and can enable XXE or entity
  // expansion attacks. Reject them before asking libxml to parse the document.
  if (preg_match('/<!DOCTYPE|<!ENTITY/i', $svg_content)) {
    error_log('SVG Upload: Blocked file containing a DTD or entity declaration');
    return false;
  }

  $allowed_elements = array_fill_keys([
    'svg',
    'g',
    'path',
    'rect',
    'circle',
    'ellipse',
    'line',
    'polyline',
    'polygon',
    'defs',
    'lineargradient',
    'radialgradient',
    'stop',
    'clippath',
    'mask',
    'pattern',
    'symbol',
    'use',
    'title',
    'desc',
    'text',
    'tspan',
  ], true);

  $allowed_attributes = array_fill_keys([
    'xmlns',
    'xmlns:xlink',
    'viewbox',
    'width',
    'height',
    'x',
    'y',
    'x1',
    'y1',
    'x2',
    'y2',
    'cx',
    'cy',
    'r',
    'rx',
    'ry',
    'd',
    'points',
    'fill',
    'fill-opacity',
    'fill-rule',
    'stroke',
    'stroke-width',
    'stroke-linecap',
    'stroke-linejoin',
    'stroke-miterlimit',
    'stroke-dasharray',
    'stroke-dashoffset',
    'stroke-opacity',
    'opacity',
    'transform',
    'preserveaspectratio',
    'id',
    'class',
    'role',
    'aria-hidden',
    'aria-label',
    'focusable',
    'clip-path',
    'mask',
    'gradientunits',
    'gradienttransform',
    'offset',
    'stop-color',
    'stop-opacity',
    'patternunits',
    'patterncontentunits',
    'patterntransform',
    'text-anchor',
    'dominant-baseline',
    'font-family',
    'font-size',
    'font-style',
    'font-weight',
    'letter-spacing',
    'word-spacing',
    'dx',
    'dy',
    'xml:space',
    'href',
    'xlink:href',
  ], true);

  // Load SVG with DOMDocument for deeper sanitization
  $dom = new DOMDocument();
  $dom->formatOutput = false;
  $dom->preserveWhiteSpace = true;
  $dom->strictErrorChecking = false;

  $previous_error_setting = libxml_use_internal_errors(true);

  // LIBXML_NOENT must not be used for untrusted uploads: it substitutes entities.
  if (!$dom->loadXML($svg_content, LIBXML_NONET)) {
    error_log('SVG Upload: Failed to parse SVG file');
    libxml_clear_errors();
    libxml_use_internal_errors($previous_error_setting);
    return false;
  }

  libxml_clear_errors();
  libxml_use_internal_errors($previous_error_setting);

  $root = $dom->documentElement;
  if (
    !$root ||
    strtolower($root->localName) !== 'svg' ||
    $root->namespaceURI !== 'http://www.w3.org/2000/svg'
  ) {
    error_log('SVG Upload: Invalid SVG root element or namespace');
    return false;
  }

  // Get all elements
  $xpath = new DOMXPath($dom);
  $all_elements = $xpath->query('//*');

  if ($all_elements === false) {
    return false;
  }

  $processing_instructions = $xpath->query('//processing-instruction()');
  if ($processing_instructions !== false) {
    $instructions = [];
    foreach ($processing_instructions as $instruction) {
      $instructions[] = $instruction;
    }

    foreach ($instructions as $instruction) {
      $instruction->parentNode->removeChild($instruction);
    }
  }

  $elements = [];
  foreach ($all_elements as $element) {
    $elements[] = $element;
  }

  foreach ($elements as $element) {
    $element_name = strtolower($element->localName);
    if (
      !isset($allowed_elements[$element_name]) ||
      ($element->namespaceURI !== null && $element->namespaceURI !== 'http://www.w3.org/2000/svg')
    ) {
      if ($element === $root) {
        return false;
      }

      $element->parentNode->removeChild($element);
      continue;
    }

    $attributes = [];
    foreach ($element->attributes as $attribute) {
      $attributes[] = $attribute;
    }

    foreach ($attributes as $attribute) {
      $attribute_name = strtolower($attribute->nodeName);
      $value = html_entity_decode($attribute->nodeValue, ENT_QUOTES | ENT_HTML5, 'UTF-8');
      $remove = !isset($allowed_attributes[$attribute_name]);

      if (strpos($attribute_name, 'on') === 0) {
        $remove = true;
      }

      if (in_array($attribute_name, ['href', 'xlink:href'], true)) {
        $remove = !preg_match('/^#[A-Za-z_][A-Za-z0-9_.:-]*$/', trim($value));
      }

      // Paint-server references are allowed only as local fragment URLs.
      if (stripos($value, 'url(') !== false) {
        $remove = !preg_match('/^url\(\s*["\']?#[A-Za-z_][A-Za-z0-9_.:-]*["\']?\s*\)$/i', trim($value));
      }

      if (preg_match('/(?:javascript|vbscript|data)\s*:/i', $value)) {
        $remove = true;
      }

      if ($remove) {
        $element->removeAttributeNode($attribute);
      }
    }
  }

  // Saving only the root element also strips document-level declarations.
  $sanitized = $dom->saveXML($root);

  return $sanitized === false ? false : $sanitized;
}

/**
 * Fix SVG display in media library
 */
add_filter(
  'wp_check_filetype_and_ext',
  static function ($data, $file, $filename, $mimes) {
    $filetype = wp_check_filetype($filename, $mimes);

    if (isset($filetype['ext'], $filetype['type']) && $filetype['ext'] === 'svg') {
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
    if (
      isset($response['type'], $response['subtype'], $response['url']) &&
      $response['type'] === 'image' &&
      $response['subtype'] === 'svg+xml'
    ) {
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
    if ($screen && $screen->base === 'upload') {
      echo '<style>
        /* Only target SVGs in media library grid view */
        .wp-list-table .media-icon img[src*=".svg"] {
          width: 100%;
          height: auto;
        }
        /* Only target SVGs in media library list view */
        .attachments-browser .attachment img[src*=".svg"] {
          width: 100%;
          height: auto;
        }
        /* Modal attachment details */
        .attachment-details img[src*=".svg"] {
          max-width: 100%;
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
    if (!current_user_can('upload_files')) {
      wp_die('Insufficient permissions', 403);
    }

    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'svg_security_nonce')) {
      wp_die('Security check failed');
    }

    update_user_meta(get_current_user_id(), 'svg_security_notice_dismissed', true);
    wp_die();
  },
  10,
  0
);
