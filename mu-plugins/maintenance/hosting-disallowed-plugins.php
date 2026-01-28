<?php

/**
 * A comprehensive plugin disallowance system based on major hosting provider lists
 *
 * Plugin name:       Hosting Disallowed Plugins
 * Plugin URI:        https://openwpclub.com
 * Description:       Disallows plugins known to cause issues on managed hosting platforms. Based on popular hosting platforms.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.1.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       hosting-disallowed-plugins
 * 
 * Sources:
 * WP Engine: https://wpengine.com/support/disallowed-plugins/ - Last checked: Sept 18, 2025
 * Kinsta: https://kinsta.com/docs/wordpress-hosting/wordpress-plugins-themes/wordpress-banned-incompatible-plugins/ - Last checked: Sept 18, 2025
 * Flywheel: https://getflywheel.com/wordpress-support/what-plugins-are-not-recommended/ - Last checked: Sept 18, 2025
 * GoDaddy: https://www.godaddy.com/help/blocklisted-plugins-8964 - Last checked: Sept 18, 2025
 * Bluehost: https://www.bluehost.com/hosting/help/mwp-disallowed-plugins - Last checked: Sept 18, 2025
 * HostGator: https://www.hostgator.com/help/article/managed-wordpress-disallowed-plugins - Last checked: Sept 18, 2025
 * Pagely: https://support.pagely.com/hc/en-us/articles/201255990-Which-Plugins-and-Themes-Are-Allowed - Last checked: Sept 18, 2025
 * WordPress.com: https://wordpress.com/support/plugins/incompatible-plugins/ - Last checked: Sept 18, 2025
 */

// Comprehensive list based on multiple hosting providers
// Last updated: September 18, 2025
const HOSTING_DISALLOWED_PLUGINS = [
  // Security plugins (often conflict with host security)
  'hello-dolly',
  'ithemes-security',
  'ithemes-security-pro',
  'loginizer',
  'ninjafirewall',
  'really-simple-ssl',
  'sucuri-scanner',
  'wordfence',
  'wps-hide-login',
  'limit-login-attempts-reloaded',
  'login-wall',
  'antihacker',
  'stopbadbots',
  'disable-json-api',
  'login-lockdown',
  'block-bad-queries',

  // Backup plugins (hosts provide their own backups)
  'updraftplus',
  'backupbuddy',
  'backup',
  'backup-scheduler',
  'backupwordpress',
  'backwpup',
  'backwpup-pro',
  'duplicator',
  'duplicator-pro',
  'ezpz-one-click-backup',
  'the-codetree-backup',
  'wp-db-backup',
  'wp-db-backup-made',
  'wpengine-migrate',
  'wpengine-snapshot',
  'wponlinebackup',
  'all-in-one-wp-migration',
  'snapshot',
  'versionpress',
  'backupcreator',
  'backup-to-dropbox',
  'backupwp',
  'simple-backup',
  'total-archive-by-fotan',
  'wp-complete-backup',
  'wp-time-machine',
  'xcloner-backup-and-restore',

  // Caching plugins (hosts handle caching server-side)
  'w3-total-cache',
  'wp-super-cache',
  'wp-fastest-cache',
  'wp-rocket', // Exception: Allowed on some hosts with caching disabled
  'litespeed-cache',
  'wp-file-cache',
  'wp-cache',
  'wp-cachecom',
  'wp-fast-cache',
  'hyper-cache',
  'quick-cache',
  'quick-cache-pro',
  'cache-enabler',
  'sg-cachepress',
  'cache-images',
  'db-cache-reloaded',

  // Performance plugins that cause issues
  'broken-link-checker',
  'wp-postviews',
  'wp-slimstat',
  'p3',
  'p3-profiler',
  'fuzzy-seo-booster',
  'google-sitemap-generator',
  'google-xml-sitemaps-with-multisite-support',
  'wordpress-gzip-compression',
  'better-wordpress-minify',
  'jch-optimize',
  'bwp-minify',
  'optimize-database-after-deleting-revisions',
  'rvg-optimize-database',
  'wp-database-optimizer',
  'wp-optimize',

  // Related posts plugins (database intensive)
  'contextual-related-posts',
  'dynamic-related-posts',
  'similar-posts',
  'seo-alrp',
  'yet-another-related-posts-plugin',
  'related-posts-by-taxonomy',

  // Stats plugins (resource intensive)
  'counterize',
  'firestats',
  'gosquared-livestats',
  'newstatpress',
  'simple-stats',
  'statpress',
  'statpress-reloaded',
  'statpress-visitors',
  'stats',
  'track-that-stat',
  'visitor-stats-widget',
  'vsf-simple-stats',
  'wassup',
  'wp-statistics',
  'async-google-analytics',
  'adsense-click-fraud-monitoring',

  // Database management plugins
  'adminer',
  'portable-phpmyadmin',
  'wp-phpmyadmin',
  'wp-dbmanager',
  'wpdbspringclean',
  'wordpress-database-backup',

  // File management plugins (security risk)
  'wp-file-manager',
  'file-commander',
  'allow-php-execute',
  'exec-php',
  'dynamic-widgets',

  // Email plugins (hosts block email ports)
  'worker',
  'wpremote',
  'mailit',
  'send-email-from-admin',
  'wp-mailinglist',
  'sendpress',

  // Jetpack (often conflicts with host features)
  'jetpack',

  // Redirection plugins
  'redirection',

  // Duplicate functionality plugins
  'no-revisions',
  'force-strong-passwords',
  'bad-behavior',
  'gd-system-plugin',
  'nginx-champuru',
  'missed-schedule',
  'wp-missed-schedule',

  // Social/External service plugins that can cause issues
  'facebook-instant-articles',
  'pagefrog',
  'wonderm00ns-simple-facebook-open-graph-tags',
  'facebook-open-graph-meta-tags',
  'tweet-blender',
  'recommend-a-friend',

  // Old/Problematic plugins
  'hello.php',
  'hcs.php',
  'gd-system-plugin.php',
  'toolspack',
  'ToolsPack',
  'pluginsamonsters',
  'pluginsmonsters',
  'sweetcaptcha-revolutionary-free-captcha-service',
  'si-captcha-for-wordpress',
  'text-passwords',
  'ozh-who-sees-ads',
  'jr-referrer',
  'jumpple',
  'spamreferrerblock',
  'super-post',
  'superslider',
  'ssclassic',
  'sspro',
  'wpsmilepack',

  // Security/Vulnerability plugins
  'clef',
  'infinitewp-client',
  'nextgen-gallery', // Specific versions
  'mailpoet', // Specific versions  
  'pipdig',
  'pipdig-power-pack',
  'slick-popup',
  'wp-copysafe-web',
  'wp-copysafe-pdf',
  'wp-live-chat-support',
  'timthumb-vulnerability-scanner',
  'exploit-scanner',

  // E-commerce integration plugins that cause issues
  'woocommerce-amazon-ebay-integration',
  'amazon-ebay-integration',
  'codistoconnect',

  // User management plugins
  'inactive-user-deleter',
  'user-role-editor', // Specific versions

  // Content plugins that can cause issues
  'wp-rss-multi-importer',
  'content-molecules',
  'wordpress-popular-posts',
  'real-time-find-and-replace',

  // Testing/Development plugins not suitable for production
  'wordpress-beta-tester',
  'synthesis',
  'wp-engine-common',

  // Image optimization plugins that conflict with host optimization
  'ewww-image-optimizer', // Server-based versions
  'smush-pro', // Specific configurations

  // Additional plugins found in hosting provider lists
  'hc-custom-wp-admin-url',
  'display-widgets',
  'delete-all-comments',
  'repress',
  'search-unleashed',
  'smestorage-multi-cloud-files',
  'backjacker',
  '6scan-protection',
  'wp-symposium-alerts',
];

// Deactivate disallowed plugins
add_action(
  'plugin_loaded',
  static function ($plugin_path) {
    $plugin = plugin_basename($plugin_path);
    $plugin_slug = explode('/', $plugin)[0];

    if (in_array($plugin_slug, HOSTING_DISALLOWED_PLUGINS, true)) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
      deactivate_plugins([$plugin], true);

      add_action(
        'admin_notices',
        static function () use ($plugin_slug) {
          $message = sprintf(
            'The plugin "%s" is disallowed in this hosting environment for performance or security reasons.',
            esc_html($plugin_slug)
          );
          printf('<div class="notice notice-error"><p>%s</p></div>', $message);
        },
        10,
        0
      );
    }
  },
  10,
  1
);

// Remove Activate plugin action
add_filter(
  'user_has_cap',
  static function ($allcaps, $caps, $args) {
    if (count($args) === 3 && $args[0] === 'activate_plugin') {
      $plugin_slug = explode('/', $args[2])[0];
      if (in_array($plugin_slug, HOSTING_DISALLOWED_PLUGINS, true)) {
        $allcaps['activate_plugins'] = false;
      }
    }
    return $allcaps;
  },
  PHP_INT_MAX,
  3
);
