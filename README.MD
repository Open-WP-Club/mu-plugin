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
| Plugin Name | Description | Version | WP Version | PHP Version |
|-------------|-------------|---------|------------|-------------|
| [Add Footer Signature](mu-plugins/add-footer-signature.php) | Adds a developer signature comment to the website footer for attribution and contact purposes. | 1.0.0 | 6.6+ | 7.4+ |
| [Cleanup Old Revisions](mu-plugins/cleanup-old-revisions.php) | Automatically removes old post revisions older than 30 days and limits revisions to 5 per post. | 1.0.0 | 6.6+ | 7.4+ |
| [Cron Monitor](mu-plugins/cron-monitor.php) | Records the timestamp of the last WordPress cron execution. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Admin Email Confirmation](mu-plugins/disable-admin-email-confirmation.php) | Disables the periodic admin email confirmation prompt that WordPress shows every 6 months. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Admin New User Email](mu-plugins/disable-admin-new-user-email.php) | Prevents admin from receiving email notifications when new users register. Users still receive their welcome email. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable AI Crawlers](mu-plugins/disable-all-ai.php) | Blocks all known AI crawlers and bots from accessing your site content via robots.txt. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Autosave](mu-plugins/disable-autosave.php) | Disables WordPress autosave functionality to improve performance and prevent unwanted draft saves. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Comments Globally](mu-plugins/disable-comments.php) | Completely disables comments, trackbacks, and pingbacks across the entire site. Removes all comment-related functionality from admin. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Emoji Support](mu-plugins/disable-emoji-support.php) | A utility plugin to disable emoji support. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable File Editing](mu-plugins/disable-file-editing.php) | Prevents editing of theme/plugin files from admin | 1.0.0 | N/A+ | N/A+ |
| [Disable FloC](mu-plugins/disable-floc.php) | A utility plugin to disable FLoC. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Pingback](mu-plugins/disable-pingback.php) | Disables pingback XML-RPC method to prevent IP disclosure attacks behind firewalls/proxies. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Post by Email](mu-plugins/disable-post-by-email.php) | Disables WordPress post-by-email functionality to improve security and reduce attack surface. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Rest API](mu-plugins/disable-rest-api.php) | A utility plugin to disable Rest API. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Unnecessary Link Headers](mu-plugins/disable-link-headers.php) | A utility plugin to disable Unnecessary Link Headers. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable WP Version](mu-plugins/disable-wp-version.php) | Remove WordPress version | 1.0.0 | 6.6+ | 7.4+ |
| [Disable XML-RPC](mu-plugins/disable-xml-rpc.php) | A utility plugin to disable XML-RPC. | 1.0.0 | 6.6+ | 7.4+ |
| [Enable SVG Upload](mu-plugins/enable-svg-upload.php) | Enables SVG file uploads with security sanitization to prevent XSS attacks. Adds media library preview support. | 1.0.0 | 6.6+ | 7.4+ |
| [Hosting Disallowed Plugins](mu-plugins/hosting-disallowed-plugins.php) | Disallows plugins known to cause issues on managed hosting platforms. Based on popular hosting platforms. | 1.0.1 | 6.6+ | 7.4+ |
| [OpenWP Club Core](mu-plugins/openwpclub-message.php) | Core functionality and branding for OpenWP Club managed WordPress sites. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove Author Metabox](mu-plugins/remove-author-metabox.php) | Removes the author meta box from post and page edit screens in WordPress admin. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove H1 Editor](mu-plugins/disable-h1-editor.php) | Removes H1 heading option from both Classic Editor (TinyMCE) and Block Editor (Gutenberg) to improve SEO structure. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove WordPress Branding](mu-plugins/remove-wordpress-branding.php) | Removes WordPress logo, admin footer text, and blocks access to WordPress about pages for white-label admin experience. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove WP Embed](mu-plugins/remove-wp-embed.php) | Remove WordPress embed script from frontend to improve performance. | 1.0.0 | 6.6+ | 7.4+ |
| [Sanitize File Name](mu-plugins/sanitize-file-names.php) | Automatically removes accents and special characters from uploaded file names for better compatibility. | 1.0.0 | 6.6+ | 7.4+ |
| [Simple Maintenance Mode](mu-plugins/simple-maintenance-mode.php) | Simple maintenance mode that shows a branded page. Toggle by creating/deleting .maintenance file in wp-content. | 1.0.0 | 6.6+ | 7.4+ |

<!-- PLUGIN_LIST_END -->
