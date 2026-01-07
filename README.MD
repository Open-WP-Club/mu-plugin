# OpenWPClub's MU Plugins

**MU (Must-Use) Plugins** are a special type of WordPress plugin that:

- **Always active** - Cannot be deactivated from the WordPress admin
- **Load automatically** - No activation required
- **Load early** - Before regular plugins and themes
- **No updates** - Must be manually updated (no auto-updates)
- **Single files only** - Each plugin must be a single PHP file (no subdirectories)

## ⚠️ Important: Read Before Using

**DO NOT blindly copy all these plugins to your site.** Each plugin modifies WordPress behavior in specific ways. You should:

1. **Understand what each plugin does** before installing it
2. **Only use plugins that solve problems you actually have**
3. **Test on a staging site first** before deploying to production
4. **Consider your specific use case** - what works for one site may break another

## Installation Instructions

1. **Create the directory**: `/wp-content/mu-plugins/` (if it doesn't exist)
2. **Upload individual files**: Copy only the `.php` files you need
3. **No activation needed**: They work immediately
4. **Verify functionality**: Test your site after adding each plugin

<!-- PLUGIN_LIST_START -->
| Plugin Name | Description | Version | WP | PHP |
|-------------|-------------|---------|----|-----|
| [Add Footer Signature](mu-plugins/admin/add-footer-signature.php) | Adds a developer signature comment to the website footer for attribution and contact purposes. | 1.0.0 | 6.6+ | 7.4+ |
| [AVIF Support](mu-plugins/media/avif-support.php) | Add AVIF image format support for uploads and display (next-generation image format, better compression than WebP) | 1.0.0 | N/A+ | N/A+ |
| [Cleanup Old Revisions](mu-plugins/cleanup/cleanup-old-revisions.php) | Automatically removes old post revisions older than 30 days and limits revisions to 5 per post. | 1.0.0 | 6.6+ | 7.4+ |
| [Cron Monitor](mu-plugins/cleanup/cron-monitor.php) | Records the timestamp of the last WordPress cron execution. | 1.1.0 | 6.6+ | 7.4+ |
| [Custom Login Logo](mu-plugins/admin/custom-login-logo.php) | Replaces the WordPress logo on the login page with your site logo, icon, or name. Links to your homepage instead of wordpress.org. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Admin Email Confirmation](mu-plugins/admin/disable-admin-email-confirmation.php) | Disables the periodic admin email confirmation prompt that WordPress shows every 6 months. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Admin New User Email](mu-plugins/admin/disable-admin-new-user-email.php) | Prevents admin from receiving email notifications when new users register. Users still receive their welcome email. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable AI Crawlers](mu-plugins/disable/disable-all-ai.php) | Blocks all known AI crawlers and bots from accessing your site content via robots.txt. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Autosave](mu-plugins/performance/disable-autosave.php) | Disables WordPress autosave functionality to improve performance and prevent unwanted draft saves. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Comments Globally](mu-plugins/disable/disable-comments.php) | Completely disables comments, trackbacks, and pingbacks across the entire site. Removes all comment-related functionality from admin. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Dashicons Frontend](mu-plugins/performance/disable-dashicons-frontend.php) | Remove Dashicons CSS from frontend for non-logged-in users to improve performance (saves ~40KB) | 1.0.0 | N/A+ | N/A+ |
| [Disable Emoji Support](mu-plugins/performance/disable-emoji-support.php) | A utility plugin to disable emoji support. | 1.0.0. | 6.6+ | 7.4+ |
| [Disable File Editing](mu-plugins/security/disable-file-editing.php) | Prevents editing of theme/plugin files from admin | 1.0.0 | N/A+ | N/A+ |
| [Disable FloC](mu-plugins/disable/disable-floc.php) | A utility plugin to disable FLoC. | 1.0.0. | 6.6+ | 7.4+ |
| [Disable oEmbed](mu-plugins/performance/disable-oembed.php) | Disables WordPress oEmbed functionality to reduce HTTP requests and improve performance. Use if you don't embed external content. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Pingback](mu-plugins/security/disable-pingback.php) | Disables pingback XML-RPC method to prevent IP disclosure attacks behind firewalls/proxies. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Plugin Update Checks](mu-plugins/disable/disable-plugin-update-checks.php) | Disables automatic plugin update checks to reduce server load. Useful for managed/frozen environments where updates are controlled manually. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Post by Email](mu-plugins/disable/disable-post-by-email.php) | Disables WordPress post-by-email functionality to improve security and reduce attack surface. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Rest API](mu-plugins/disable/disable-rest-api.php) | A utility plugin to disable Rest API. | 1.0.0. | 6.6+ | 7.4+ |
| [Disable Unnecessary Link Headers](mu-plugins/disable/disable-link-headers.php) | A utility plugin to disable Unnecessary Link Headers. | 1.0.0. | 6.6+ | 7.4+ |
| [Disable User Enumeration](mu-plugins/security/disable-user-enumeration.php) | Prevents user enumeration via author archives and REST API to protect usernames from discovery. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable User Registration](mu-plugins/security/disable-user-registration.php) | Completely disables user registration via wp-login.php, REST API, and all registration endpoints | 1.0.0 | N/A+ | N/A+ |
| [Disable WP Version](mu-plugins/disable/disable-wp-version.php) | Remove WordPress version | 1.0.0. | 6.6+ | 7.4+ |
| [Disable XML-RPC](mu-plugins/security/disable-xml-rpc.php) | A utility plugin to disable XML-RPC. | 1.0.0. | 6.6+ | 7.4+ |
| [Enable SVG Upload](mu-plugins/media/enable-svg-upload.php) | Enables SVG file uploads with security sanitization to prevent XSS attacks. Adds media library preview support. | 1.1.0 | 6.6+ | 7.4+ |
| [Environment Indicator](mu-plugins/development/environment-indicator.php) | Displays a visual indicator in the admin bar showing the current environment (Development/Staging/Production) to prevent editing the wrong site. | 1.0.0 | 6.6+ | 7.4+ |
| [Hosting Disallowed Plugins](mu-plugins/maintenance/hosting-disallowed-plugins.php) | Disallows plugins known to cause issues on managed hosting platforms. Based on popular hosting platforms. | 1.0.1 | 6.6+ | 7.4+ |
| [Lazy Load Gravatars](mu-plugins/performance/lazy-load-gravatars.php) | Defers loading of Gravatar images until they're needed, reducing initial page load time and external HTTP requests. | 1.0.0 | 6.6+ | 7.4+ |
| [Limit Login Attempts](mu-plugins/security/limit-login-attempts.php) | Blocks IP addresses after 4 failed login attempts for 24 hours to prevent brute force attacks. | 1.0.0 | 6.6+ | 7.4+ |
| [Limit Upload Size by Role](mu-plugins/media/limit-upload-size-by-role.php) | Sets different maximum upload file size limits for different user roles to prevent large uploads from non-admin users. | 1.0.0 | 6.6+ | 7.4+ |
| [OpenWP Club Core](mu-plugins/admin/openwpclub-message.php) | Core functionality and branding for OpenWP Club managed WordPress sites. | 1.0.0 | 6.6+ | 7.4+ |
| [Optimize WooCommerce Assets](mu-plugins/performance/optimize-woocommerce-assets.php) | Only loads WooCommerce scripts and styles on shop-related pages to improve site performance. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove Author Metabox](mu-plugins/admin/remove-author-metabox.php) | Removes the author meta box from post and page edit screens in WordPress admin. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove H1 Editor](mu-plugins/admin/disable-h1-editor.php) | Removes H1 heading option from both Classic Editor (TinyMCE) and Block Editor (Gutenberg) to improve SEO structure. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove jQuery Migrate](mu-plugins/performance/remove-jquery-migrate.php) | Removes jQuery Migrate script from WordPress to reduce page weight. Only use if your theme and plugins don't require legacy jQuery support. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove WordPress Branding](mu-plugins/admin/remove-wordpress-branding.php) | Removes WordPress logo, admin footer text, and blocks access to WordPress about pages for white-label admin experience. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove WP Embed](mu-plugins/performance/remove-wp-embed.php) | Remove WordPress embed script from frontend to improve performance. | 1.0.0 | 6.6+ | 7.4+ |
| [Sanitize File Name](mu-plugins/media/sanitize-file-names.php) | Automatically removes accents and special characters from uploaded file names for better compatibility. | 1.0.0 | 6.6+ | 7.4+ |
| [Simple Maintenance Mode](mu-plugins/maintenance/simple-maintenance-mode.php) | Simple maintenance mode that shows a branded page. Toggle by creating/deleting .maintenance file in wp-content. | 1.0.0 | 6.6+ | 7.4+ |

<!-- PLUGIN_LIST_END -->
