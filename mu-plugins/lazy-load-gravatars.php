<?php

/**
 * A utility plugin to lazy load Gravatar images
 *
 * Plugin name:       Lazy Load Gravatars
 * Plugin URI:        https://openwpclub.com
 * Description:       Defers loading of Gravatar images until they're needed, reducing initial page load time and external HTTP requests.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       lazy-load-gravatars
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Modify avatar HTML to add lazy loading
 */
add_filter(
  'get_avatar',
  static function ($avatar, $id_or_email, $size, $default, $alt, $args) {
    // Only apply on frontend
    if (is_admin()) {
      return $avatar;
    }

    // Check if avatar contains an img tag
    if (empty($avatar) || strpos($avatar, '<img') === false) {
      return $avatar;
    }

    // Add loading="lazy" attribute if not already present
    if (strpos($avatar, 'loading=') === false) {
      $avatar = str_replace('<img', '<img loading="lazy"', $avatar);
    }

    // Add decoding="async" for better performance
    if (strpos($avatar, 'decoding=') === false) {
      $avatar = str_replace('<img', '<img decoding="async"', $avatar);
    }

    return $avatar;
  },
  10,
  6
);

/**
 * Add DNS prefetch for Gravatar domain to speed up loading when needed
 */
add_action(
  'wp_head',
  static function () {
    // Only add if comments are open or there might be avatars
    if (comments_open() || have_comments()) {
      echo '<link rel="dns-prefetch" href="//www.gravatar.com" />' . "\n";
      echo '<link rel="dns-prefetch" href="//secure.gravatar.com" />' . "\n";
    }
  },
  1,
  0
);

/**
 * Modify avatar URLs to use a placeholder initially (optional advanced feature)
 * This is more aggressive lazy loading using data attributes
 */
add_filter(
  'get_avatar',
  static function ($avatar) {
    // Only on frontend
    if (is_admin()) {
      return $avatar;
    }

    // Check if we should use advanced lazy loading
    if (!apply_filters('gravatar_use_advanced_lazy_loading', false)) {
      return $avatar;
    }

    // Extract src attribute
    if (preg_match('/src=["\']([^"\']+)["\']/', $avatar, $matches)) {
      $src = $matches[1];

      // Create a 1x1 transparent placeholder
      $placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

      // Replace src with placeholder and add data-src with original
      $avatar = str_replace(
        'src="' . $src . '"',
        'src="' . $placeholder . '" data-gravatar-src="' . esc_url($src) . '"',
        $avatar
      );

      // Add class for JavaScript targeting
      $avatar = str_replace('<img', '<img class="lazy-gravatar"', $avatar);
    }

    return $avatar;
  },
  20,
  1
);

/**
 * Add inline JavaScript for advanced lazy loading
 */
add_action(
  'wp_footer',
  static function () {
    // Only if advanced lazy loading is enabled
    if (!apply_filters('gravatar_use_advanced_lazy_loading', false)) {
      return;
    }

    // Only if there are comments or avatars on the page
    if (!comments_open() && !have_comments()) {
      return;
    }

?>
  <script>
    (function() {
      'use strict';

      // Use Intersection Observer if available
      if ('IntersectionObserver' in window) {
        const gravatarObserver = new IntersectionObserver(function(entries, observer) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              const img = entry.target;
              const src = img.getAttribute('data-gravatar-src');

              if (src) {
                img.src = src;
                img.removeAttribute('data-gravatar-src');
                img.classList.remove('lazy-gravatar');
                observer.unobserve(img);
              }
            }
          });
        }, {
          rootMargin: '50px 0px',
          threshold: 0.01
        });

        // Observe all lazy gravatars
        document.querySelectorAll('img.lazy-gravatar').forEach(function(img) {
          gravatarObserver.observe(img);
        });
      } else {
        // Fallback for older browsers - load immediately
        document.querySelectorAll('img.lazy-gravatar').forEach(function(img) {
          const src = img.getAttribute('data-gravatar-src');
          if (src) {
            img.src = src;
            img.removeAttribute('data-gravatar-src');
            img.classList.remove('lazy-gravatar');
          }
        });
      }
    })();
  </script>
<?php
  },
  PHP_INT_MAX,
  0
);
