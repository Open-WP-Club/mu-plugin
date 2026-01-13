# MU-Plugins Organization

All mu-plugins are organized into categorized folders and **automatically loaded** via `000-autoloader.php`.

**Zero configuration required** - the autoloader loads ALL PHP files from ALL subdirectories. Simply delete any plugins or folders you don't need.

## How It Works

1. The autoloader scans all subdirectories
2. Loads every `.php` file it finds
3. No configuration needed

**Don't want a plugin?** Delete it.
**Don't want a category?** Delete the folder.

## Folder Structure

```
mu-plugins/
├── 000-autoloader.php          # Loads everything automatically
├── security/                   # Security & hardening plugins
├── performance/                # Performance optimization plugins
├── admin/                      # Admin UI/UX improvements
├── media/                      # Media handling plugins
├── cleanup/                    # Cleanup & maintenance
├── disable/                    # Feature disable plugins
├── maintenance/                # Maintenance mode & hosting
└── development/                # Development & debugging tools
```

## Managing Plugins

### Remove a Plugin

Simply delete the file:

```bash
rm security/disable-xml-rpc.php
```

### Remove an Entire Category

Delete the folder:

```bash
rm -rf maintenance/
```

### Add New Plugins

Add your `.php` file to any existing folder:

```bash
cp my-security-plugin.php security/
```

### Create New Category

Just create a folder and add plugins - no configuration needed:

```bash
mkdir my-category
cp my-plugin.php my-category/
```

The autoloader will automatically pick it up.

## Debugging

When `WP_DEBUG` is enabled, the autoloader stores loaded plugins in the `mu_plugins_loaded` option.

Check HTML source footer for comment: `<!-- MU-Plugins: X loaded -->`

## WordPress 6.9 Compatible

All plugins are tested and compatible with WordPress 6.9+.
