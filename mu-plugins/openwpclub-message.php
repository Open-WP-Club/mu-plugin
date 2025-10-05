<?php

/**
 * OpenWP Club Core Functionality Plugin
 *
 * Plugin name:       OpenWP Club Core
 * Plugin URI:        https://openwpclub.com
 * Description:       Core functionality and branding for OpenWP Club managed WordPress sites.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       openwpclub-core
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Add custom dashboard welcome widget
 */
add_action(
  'wp_dashboard_setup',
  static function () {
    wp_add_dashboard_widget(
      'openwpclub_welcome_widget',
      '🚀 OpenWP Club',
      'openwpclub_welcome_widget_content'
    );
  },
  10,
  0
);

/**
 * Dashboard widget content
 */
function openwpclub_welcome_widget_content()
{
  // Array of quick tips (will be rotated randomly)
  $tips = [
    'Keep WordPress core, themes, and plugins updated regularly for security and performance.',
    'Use strong, unique passwords for all admin accounts. Consider using a password manager.',
    'Always backup your site before making major changes or updates.',
    'Optimize images before uploading to reduce page load times and save storage space.',
    'Use descriptive file names and alt text for images to improve SEO and accessibility.',
    'Enable two-factor authentication (2FA) for an extra layer of security.',
    'Regularly review and remove unused plugins and themes to reduce security risks.',
    'Monitor your site\'s performance using tools like GTmetrix or Google PageSpeed Insights.',
    'Use SSL/HTTPS for your entire site to protect user data and improve SEO.',
    'Set up automatic backups to ensure you can recover your site if something goes wrong.',
    'Limit login attempts to prevent brute force attacks on your admin panel.',
    'Use child themes when customizing themes to preserve changes during updates.',
    'Keep your media library organized with proper folders and naming conventions.',
    'Review user roles and permissions regularly to ensure proper access control.',
    'Test major changes on a staging site before applying them to production.',
    'Compress and minify CSS/JS files to improve site loading speed.',
    'Use caching to reduce server load and improve page load times.',
    'Regularly check for broken links and fix them to improve user experience and SEO.',
    'Enable lazy loading for images to improve initial page load times.',
    'Keep your database clean by removing spam comments, revisions, and transients.',
    'Use descriptive permalinks that include keywords for better SEO.',
    'Disable XML-RPC if you don\'t need it to reduce security vulnerabilities.',
    'Monitor your site uptime and set up alerts for when your site goes down.',
    'Use a Content Delivery Network (CDN) to serve static assets faster globally.',
    'Regularly audit your site for accessibility to ensure it\'s usable by everyone.'
  ];

  // Get 3 random tips
  shuffle($tips);
  $random_tips = array_slice($tips, 0, 3);

?>
  <div style="padding: 15px;">
    <p style="font-size: 14px; line-height: 1.6; margin-top: 0;">
      <strong>OpenWP Club</strong> is a community-driven initiative focused on building
      high-quality, open-source WordPress tools and plugins. We create must-use plugins
      that improve security, performance, and the overall WordPress experience.
    </p>

    <div style="margin: 20px 0; padding: 15px; background: #f0f6fc; border-left: 4px solid #0073aa; border-radius: 4px;">
      <p style="margin: 0 0 10px 0;">
        <strong>🌐 Website:</strong>
        <a href="https://openwpclub.com" target="_blank" rel="noopener">openwpclub.com</a>
      </p>
      <p style="margin: 0;">
        <strong>💻 GitHub:</strong>
        <a href="https://github.com/Open-WP-Club" target="_blank" rel="noopener">github.com/Open-WP-Club</a>
      </p>
    </div>

    <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">

    <h3 style="font-size: 16px; margin: 15px 0 10px 0; color: #0073aa;">💡 WordPress Tips</h3>
    <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
      <?php foreach ($random_tips as $tip): ?>
        <li><?php echo esc_html($tip); ?></li>
      <?php endforeach; ?>
    </ul>

    <p style="font-size: 12px; color: #666; margin: 15px 0 0 0; font-style: italic;">
      💡 Tips rotate on each page load. Refresh to see more!
    </p>
  </div>
<?php
}
