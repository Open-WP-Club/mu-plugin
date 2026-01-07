# MU-Plugins Organization

All mu-plugins are organized into categorized folders and automatically loaded via `000-autoloader.php`.

## Folder Structure

```
mu-plugins/
├── 000-autoloader.php          # Loads all plugins from subdirectories
├── security/                   # Security & hardening plugins
├── performance/                # Performance optimization plugins
├── admin/                      # Admin UI/UX improvements
├── media/                      # Media handling plugins
├── cleanup/                    # Cleanup & maintenance
├── disable/                    # Feature disable plugins
└── maintenance/                # Maintenance mode & hosting
```

## Categories

### Security (36 plugins)
- `disable-xml-rpc.php` - Disable XML-RPC
- `disable-file-editing.php` - Disable theme/plugin editor
- `disable-pingback.php` - Disable pingback
- `limit-login-attempts.php` - Limit login attempts
- `disable-user-enumeration.php` - Block user enumeration
- `disable-user-registration.php` - Lock down registration

### Performance (7 plugins)
- `disable-emoji-support.php` - Remove emoji scripts
- `disable-autosave.php` - Disable autosave
- `remove-wp-embed.php` - Remove embed scripts
- `remove-jquery-migrate.php` - Remove jQuery Migrate
- `lazy-load-gravatars.php` - Lazy load avatars
- `optimize-woocommerce-assets.php` - Optimize WooCommerce
- `disable-dashicons-frontend.php` - Remove Dashicons on frontend

### Admin (8 plugins)
- `remove-author-metabox.php` - Remove author metabox
- `disable-h1-editor.php` - Disable H1 in editor
- `remove-wordpress-branding.php` - Remove WP branding
- `disable-admin-email-confirmation.php` - Disable email confirm
- `disable-admin-new-user-email.php` - Disable new user emails
- `custom-login-logo.php` - Custom login logo
- `openwpclub-message.php` - OpenWP Club message
- `add-footer-signature.php` - Footer signature

### Media (4 plugins)
- `sanitize-file-names.php` - Sanitize uploaded filenames
- `enable-svg-upload.php` - Enable SVG uploads
- `limit-upload-size-by-role.php` - Limit upload by role
- `avif-support.php` - Add AVIF format support

### Cleanup (2 plugins)
- `cleanup-old-revisions.php` - Clean old revisions
- `cron-monitor.php` - Monitor cron jobs

### Disable (8 plugins)
- `disable-wp-version.php` - Hide WP version
- `disable-floc.php` - Disable FLoC
- `disable-rest-api.php` - Disable REST API
- `disable-link-headers.php` - Remove link headers
- `disable-post-by-email.php` - Disable post by email
- `disable-comments.php` - Disable comments
- `disable-plugin-update-checks.php` - Disable update checks
- `disable-all-ai.php` - Disable AI features

### Maintenance (2 plugins)
- `simple-maintenance-mode.php` - Maintenance mode
- `hosting-disallowed-plugins.php` - Block certain plugins

## Managing Plugins

### Disable an Entire Category

Edit `000-autoloader.php` and set the category to `false`:

```php
private static $categories = [
    'security'     => true,
    'performance'  => false,  // Disable all performance plugins
    'admin'        => true,
    // ...
];
```

### Disable Individual Plugins

Edit `000-autoloader.php` and add plugin names to `$skip_plugins`:

```php
private static $skip_plugins = [
    'disable-comments',
    'simple-maintenance-mode',
];
```

### Add New Plugins

Simply add your `.php` file to the appropriate category folder:

```bash
# Add a new security plugin
cp my-security-plugin.php security/

# Add a new performance plugin
cp my-performance-plugin.php performance/
```

### Create New Category

1. Create the folder
2. Add it to `$categories` in `000-autoloader.php`
3. Add your plugins to the folder

```bash
mkdir custom-category
```

```php
private static $categories = [
    // ... existing categories
    'custom-category' => true,
];
```

## Debugging

When `WP_DEBUG` is enabled, the autoloader stores loaded/skipped plugins in options:
- `mu_plugins_loaded` - Array of loaded plugins
- `mu_plugins_skipped` - Array of skipped plugins

Check HTML source footer for comment: `<!-- MU-Plugins: X loaded, Y skipped -->`

## WordPress 6.9 Compatible

All plugins are tested and compatible with WordPress 6.9+.
