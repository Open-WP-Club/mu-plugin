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
| [MU-Plugins Autoloader](mu-plugins/000-autoloader.php) | Automatically loads all organized mu-plugins from categorized subdirectories | 1.0.0 | N/A+ | N/A+ |

<!-- PLUGIN_LIST_END -->
