<?php

/**
 * A utility plugin to replace WordPress login logo with site branding
 *
 * Plugin name:       Custom Login Logo
 * Plugin URI:        https://openwpclub.com
 * Description:       Replaces the WordPress logo on the login page with your site logo, icon, or name. Links to your homepage instead of wordpress.org.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       custom-login-logo
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Replace login logo with site logo/icon
 */
add_action(
  'login_enqueue_scripts',
  static function () {
    $site_name = get_bloginfo('name');
    $custom_logo_id = get_theme_mod('custom_logo');
    $site_icon_url = get_site_icon_url(512);

    // Try to get custom logo first
    $logo_url = '';
    $logo_width = 84;
    $logo_height = 84;

    if ($custom_logo_id) {
      $logo_data = wp_get_attachment_image_src($custom_logo_id, 'full');
      if ($logo_data) {
        $logo_url = $logo_data[0];
        $original_width = $logo_data[1];
        $original_height = $logo_data[2];

        // Calculate dimensions to fit within max 320x84
        if ($original_width > 320) {
          $logo_width = 320;
          $logo_height = ($original_height / $original_width) * 320;
        } else {
          $logo_width = $original_width;
          $logo_height = $original_height;
        }

        // If height is too tall, scale down
        if ($logo_height > 84) {
          $logo_width = ($logo_width / $logo_height) * 84;
          $logo_height = 84;
        }
      }
    }

    // Fallback to site icon
    if (empty($logo_url) && $site_icon_url) {
      $logo_url = $site_icon_url;
      $logo_width = 84;
      $logo_height = 84;
    }

    // If we have a logo/icon, use it
    if (!empty($logo_url)) {
?>
    <style type="text/css">
      #login h1 a,
      .login h1 a {
        background-image: url(<?php echo esc_url($logo_url); ?>);
        background-size: contain;
        background-position: center center;
        background-repeat: no-repeat;
        width: <?php echo esc_attr($logo_width); ?>px;
        height: <?php echo esc_attr($logo_height); ?>px;
        max-width: 320px;
        margin: 0 auto;
        padding: 0;
      }
    </style>
  <?php
    } else {
      // No logo or icon - use site name as text
  ?>
    <style type="text/css">
      #login h1 a,
      .login h1 a {
        background-image: none !important;
        text-indent: 0 !important;
        width: auto !important;
        height: auto !important;
        font-size: 24px;
        font-weight: 600;
        line-height: 1.3;
        color: #2c3338;
        text-decoration: none;
        padding: 20px 0;
        display: block;
      }

      #login h1 a::after,
      .login h1 a::after {
        content: "<?php echo esc_js($site_name); ?>";
      }

      #login h1 a:hover,
      .login h1 a:hover {
        color: #135e96;
      }
    </style>
  <?php
    }
  },
  10,
  0
);

/**
 * Change login logo URL to homepage
 */
add_filter(
  'login_headerurl',
  static function () {
    return home_url('/');
  },
  10,
  0
);

/**
 * Change login logo title to site name
 */
add_filter(
  'login_headertext',
  static function () {
    return get_bloginfo('name');
  },
  10,
  0
);

/**
 * Add custom styles to login page for better branding
 */
add_action(
  'login_enqueue_scripts',
  static function () {
  ?>
  <style type="text/css">
    /* Center and style the login form */
    .login #login {
      padding-top: 40px;
    }

    /* Style the login form */
    .login form {
      margin-top: 20px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    /* Style login button to match site */
    .login .button-primary {
      background: #2271b1;
      border-color: #2271b1;
      text-shadow: none;
      box-shadow: none;
    }

    .login .button-primary:hover,
    .login .button-primary:focus {
      background: #135e96;
      border-color: #135e96;
    }

    /* Hide language switcher if not needed */
    <?php if (!apply_filters('custom_login_show_language_switcher', true)): ?>.login .language-switcher {
      display: none;
    }

    <?php endif; ?>

    /* Optional: Add custom background color */
    <?php
    $bg_color = apply_filters('custom_login_background_color', '');
    if (!empty($bg_color)):
    ?>body.login {
      background: <?php echo esc_attr($bg_color); ?>;
    }

    <?php endif; ?>
  </style>
<?php
  },
  20,
  0
);

/**
 * Remove WordPress branding from footer
 */
add_filter(
  'login_footer',
  static function () {
?>
  <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
      // Remove "Powered by WordPress" or similar text
      const footer = document.querySelector('#login + div, .login #backtoblog + p');
      if (footer && footer.textContent.toLowerCase().includes('wordpress')) {
        footer.style.display = 'none';
      }
    });
  </script>
<?php
  },
  10,
  0
);
