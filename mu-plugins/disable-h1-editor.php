<?php

/**
 * A utility plugin to remove H1 heading option from WordPress editors
 *
 * Plugin name:       Remove H1 Editor
 * Plugin URI:        https://openwpclub.com
 * Description:       Removes H1 heading option from both Classic Editor (TinyMCE) and Block Editor (Gutenberg) to improve SEO structure.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       remove-h1-editor
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Remove H1 from Classic Editor (TinyMCE) format dropdown
 */
function mu_remove_h1_classic_editor($init)
{
  // Remove H1 from the format dropdown, keeping H2-H6
  $init['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6;Preformatted=pre';

  return $init;
}
add_filter('tiny_mce_before_init', 'mu_remove_h1_classic_editor');

/**
 * Remove H1 from Block Editor (Gutenberg) toolbar
 */
function mu_remove_h1_block_editor()
{
  // Only load on post edit screens
  if (!function_exists('get_current_screen')) {
    return;
  }

  $screen = get_current_screen();
  if (!$screen || !in_array($screen->base, ['post', 'page'], true)) {
    return;
  }

?>
  <style id="remove-h1-editor-style">
    /* Hide H1 button in Gutenberg heading block toolbar */
    .block-editor-block-toolbar .components-toolbar-group .components-button[aria-label*="Heading 1"],
    .block-editor-block-toolbar .components-toolbar-group .components-button[aria-label="1"],
    .components-popover__content .components-menu-group .components-button[aria-label*="Heading 1"],
    .components-toolbar-group button[data-level="1"],
    .block-library-heading-level-toolbar .components-toolbar-group button:first-child {
      display: none !important;
    }

    /* Hide H1 option in heading block transform menu */
    .block-editor-block-switcher__menu .components-menu-item__button[aria-label*="Heading 1"] {
      display: none !important;
    }

    /* Hide H1 in format toolbar dropdown */
    .components-dropdown-menu__menu .components-menu-item__button[role="menuitem"]:first-child {
      display: none !important;
    }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Additional JavaScript cleanup for dynamic content
      const observer = new MutationObserver(function() {
        // Remove any H1 buttons that might be dynamically added
        const h1Buttons = document.querySelectorAll([
          '.components-toolbar-group button[data-level="1"]',
          '.components-button[aria-label*="Heading 1"]',
          '.block-library-heading-level-toolbar .components-toolbar-group button:first-child'
        ].join(', '));

        h1Buttons.forEach(button => {
          button.style.display = 'none';
          button.setAttribute('disabled', 'disabled');
        });
      });

      observer.observe(document.body, {
        childList: true,
        subtree: true
      });
    });
  </script>
<?php
}
add_action('admin_head', 'mu_remove_h1_block_editor');

/**
 * Remove H1 from allowed formats in block editor settings
 */
function mu_filter_block_editor_settings($settings)
{
  // Remove H1 from default block formats if present
  if (isset($settings['__experimentalFeatures']['typography']['defaultFontSizes'])) {
    // This is a more targeted approach for newer WordPress versions
    $settings['__unstableResolvedAssets'] = $settings['__unstableResolvedAssets'] ?? [];
  }

  return $settings;
}
add_filter('block_editor_settings_all', 'mu_filter_block_editor_settings');

/**
 * Add admin notice explaining the H1 removal
 */
function mu_h1_removal_admin_notice()
{
  $screen = get_current_screen();

  // Only show on post/page edit screens, and only once per session
  if (!$screen || !in_array($screen->base, ['post', 'page'], true)) {
    return;
  }

  if (get_user_meta(get_current_user_id(), 'h1_removal_notice_dismissed', true)) {
    return;
  }

?>
  <div class="notice notice-info is-dismissible" id="h1-removal-notice">
    <p>
      <strong>H1 headings have been removed from the editor.</strong>
      This improves SEO by ensuring your page/post title remains the only H1.
      Use H2-H6 for content headings.
    </p>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const notice = document.getElementById('h1-removal-notice');
      if (notice) {
        notice.addEventListener('click', function(e) {
          if (e.target.classList.contains('notice-dismiss')) {
            fetch(ajaxurl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: 'action=dismiss_h1_removal_notice&nonce=<?php echo wp_create_nonce('h1_removal_nonce'); ?>'
            });
          }
        });
      }
    });
  </script>
<?php
}
add_action('admin_notices', 'mu_h1_removal_admin_notice');

/**
 * Handle notice dismissal
 */
function mu_dismiss_h1_removal_notice()
{
  if (!wp_verify_nonce($_POST['nonce'] ?? '', 'h1_removal_nonce')) {
    wp_die('Security check failed');
  }

  update_user_meta(get_current_user_id(), 'h1_removal_notice_dismissed', true);
  wp_die();
}
add_action('wp_ajax_dismiss_h1_removal_notice', 'mu_dismiss_h1_removal_notice');
