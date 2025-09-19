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
| [Add Footer Signature](https://openwpclub.com) | Adds a developer signature comment to the website footer for attribution and contact purposes. | 1.0.0 | 6.6+ | 7.4+ |
| [Cleanup Old Revisions](https://openwpclub.com) | Automatically removes old post revisions older than 30 days and limits revisions to 5 per post. | 1.0.0 | 6.6+ | 7.4+ |
| [Cron Monitor](https://openwpclub.com) | Records the timestamp of the last WordPress cron execution. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Admin Email Confirmation](https://openwpclub.com) | Disables the periodic admin email confirmation prompt that WordPress shows every 6 months. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Admin New User Email](https://openwpclub.com) | Prevents admin from receiving email notifications when new users register. Users still receive their welcome email. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable AI Crawlers](https://openwpclub.com) | Blocks all known AI crawlers and bots from accessing your site content via robots.txt. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Autosave](https://openwpclub.com) | Disables WordPress autosave functionality to improve performance and prevent unwanted draft saves. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Emoji Support](https://openwpclub.com) | A utility plugin to disable emoji support. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable File Editing](https://openwpclub.com) | Prevents editing of theme/plugin files from admin | 1.0.0 | N/A+ | N/A+ |
| [Disable FloC](https://openwpclub.com) | A utility plugin to disable FLoC. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Pingback](https://openwpclub.com) | Disables pingback XML-RPC method to prevent IP disclosure attacks behind firewalls/proxies. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Post by Email](https://openwpclub.com) | Disables WordPress post-by-email functionality to improve security and reduce attack surface. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Rest API](https://openwpclub.com) | A utility plugin to disable Rest API. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Unnecessary Link Headers](https://openwpclub.com) | A utility plugin to disable Unnecessary Link Headers. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable WP Version](https://openwpclub.com) | Remove WordPress version | 1.0.0 | 6.6+ | 7.4+ |
| [Disable XML-RPC](https://openwpclub.com) | A utility plugin to disable XML-RPC. | 1.0.0 | 6.6+ | 7.4+ |
| [Hosting Disallowed Plugins](https://openwpclub.com) | Disallows plugins known to cause issues on managed hosting platforms. Based on popular hosting platforms. | 1.0.1 | 6.6+ | 7.4+ |
| [Remove Author Metabox](https://openwpclub.com) | Removes the author meta box from post and page edit screens in WordPress admin. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove H1 Editor](https://openwpclub.com) | Removes H1 heading option from both Classic Editor (TinyMCE) and Block Editor (Gutenberg) to improve SEO structure. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove WordPress Branding](https://openwpclub.com) | Removes WordPress logo, admin footer text, and blocks access to WordPress about pages for white-label admin experience. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove WP Embed](https://openwpclub.com) | Remove WordPress embed script from frontend to improve performance. | 1.0.0 | 6.6+ | 7.4+ |
| [Sanitize File Name](https://openwpclub.com) | Automatically removes accents and special characters from uploaded file names for better compatibility. | 1.0.0 | 6.6+ | 7.4+ |
| [Simple Maintenance Mode](https://openwpclub.com) | Simple maintenance mode that shows a branded page. Toggle by creating/deleting .maintenance file in wp-content. | 1.0.0 | 6.6+ | 7.4+ |

<!-- PLUGIN_LIST_END -->
