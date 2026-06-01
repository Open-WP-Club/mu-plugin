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

**Zero configuration required** - the autoloader loads ALL PHP files from ALL subdirectories. Simply delete any plugins or folders you don't need.

## How It Works

1. The autoloader scans all subdirectories
2. Loads every `.php` file it finds
3. No configuration needed

**Don't want a plugin?** Delete it.
**Don't want a category?** Delete the folder.

<!-- PLUGIN_LIST_START -->
| Plugin Name | Description | Version | WP | PHP |
|-------------|-------------|---------|----|-----|
| [404 Logger](mu-plugins/security/404-logger.php) | Logs 404 errors to a file for security monitoring and broken link detection. | 1.0.0 | 6.6+ | 7.4+ |
| [Add Footer Signature](mu-plugins/admin/add-footer-signature.php) | Adds a developer signature comment to the website footer for attribution and contact purposes. | 1.0.0 | 6.6+ | 7.4+ |
| [Admin Color Scheme Lockdown](mu-plugins/admin/admin-color-scheme-lockdown.php) | Forces a single admin colour scheme for all users. Useful for white-label admin panels. Set MU_ADMIN_COLOR_SCHEME to any built-in slug (default, light, blue, coffee, ectoplasm, midnight, ocean, sunrise). | 1.0.0 | 6.6+ | 7.4+ |
| [Admin Footer Info](mu-plugins/admin/admin-footer-info.php) | Shows PHP version, MySQL version, WordPress version, and memory usage in the admin footer. | 1.0.0 | 6.6+ | 7.4+ |
| [Admin Session Timeout](mu-plugins/security/admin-session-timeout.php) | Automatically logs out inactive admin users. Default timeout is 60 minutes. Override with the 'mu_session_timeout' filter (value in seconds). | 1.0.0 | 6.6+ | 7.4+ |
| [Auto-Deactivate on Error](mu-plugins/maintenance/auto-deactivate-on-error.php) | Tracks which plugin is being activated via a short-lived transient. If a fatal error prevents wp_loaded from firing, the next admin request detects the stale transient and deactivates the offending plugin. | 1.0.0 | 6.6+ | 7.4+ |
| [Auto-Delete Unattached Media](mu-plugins/cleanup/auto-delete-unattached-media.php) | Weekly cron job that permanently deletes media library items with no parent post older than a configurable number of days. Prevents library bloat from orphaned uploads. Override age with 'mu_unattached_media_age_days' filter (default 30). | 1.0.0 | 6.6+ | 7.4+ |
| [Auto-Rotate JPEG on Upload](mu-plugins/media/auto-rotate-jpeg.php) | Reads EXIF orientation metadata from uploaded JPEG files and rotates the image to the correct orientation before thumbnails are generated. Fixes sideways photos from mobile cameras. | 1.0.0 | 6.6+ | 7.4+ |
| [AVIF Support](mu-plugins/media/avif-support.php) | Add AVIF image format support for uploads and display (next-generation image format, better compression than WebP) | 1.0.0 | N/A+ | N/A+ |
| [Clean Head Output](mu-plugins/performance/clean-head-output.php) | Removes unnecessary tags from the frontend <head>: wlwmanifest, RSD, shortlink, adjacent-posts links, generator meta, REST API link, and feed links. | 1.0.0 | 6.6+ | 7.4+ |
| [Cleanup Old Revisions](mu-plugins/cleanup/cleanup-old-revisions.php) | Automatically removes old post revisions older than 30 days and limits revisions to 5 per post. | 1.0.0 | 6.6+ | 7.4+ |
| [Cron Monitor](mu-plugins/cleanup/cron-monitor.php) | Records the timestamp of the last WordPress cron execution. | 1.1.0 | 6.6+ | 7.4+ |
| [Custom Login Logo](mu-plugins/admin/custom-login-logo.php) | Replaces the WordPress logo on the login page with your site logo, icon, or name. Links to your homepage instead of wordpress.org. | 1.0.0 | 6.6+ | 7.4+ |
| [Defer Non-Critical Scripts](mu-plugins/performance/defer-non-critical-scripts.php) | Adds the defer attribute to enqueued scripts not in the exclude list. Improves Time to Interactive without editing theme files. Extend the exclude list via the 'mu_defer_exclude_handles' filter. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Admin Email Confirmation](mu-plugins/admin/disable-admin-email-confirmation.php) | Disables the periodic admin email confirmation prompt that WordPress shows every 6 months. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Admin New User Email](mu-plugins/admin/disable-admin-new-user-email.php) | Prevents admin from receiving email notifications when new users register. Users still receive their welcome email. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable AI Crawlers](mu-plugins/disable/disable-all-ai.php) | Blocks all known AI crawlers and bots from accessing your site content via robots.txt. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Application Passwords](mu-plugins/security/disable-application-passwords.php) | Disables the Application Passwords feature introduced in WP 5.6 to reduce attack surface for sites not using it. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Autosave](mu-plugins/performance/disable-autosave.php) | Disables WordPress autosave functionality to improve performance and prevent unwanted draft saves. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Block Library CSS](mu-plugins/performance/disable-block-library-css.php) | Removes Gutenberg block library CSS from frontend if not using blocks, reducing page weight. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Block Notes](mu-plugins/disable/disable-block-notes.php) | Disables the collaboration notes feature in the block editor introduced in WordPress 6.9. | 1.0.0 | 6.9+ | 7.4+ |
| [Disable Command Palette](mu-plugins/disable/disable-command-palette.php) | Disables the Command Palette (Ctrl+K / Cmd+K) feature in the WordPress admin. | 1.0.0 | 6.3+ | 7.4+ |
| [Disable Comments Globally](mu-plugins/disable/disable-comments.php) | Completely disables comments, trackbacks, and pingbacks across the entire site. Removes all comment-related functionality from admin. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Dashicons Frontend](mu-plugins/performance/disable-dashicons-frontend.php) | Remove Dashicons CSS from frontend for non-logged-in users to improve performance (saves ~40KB) | 1.0.0 | N/A+ | N/A+ |
| [Disable Emoji Support](mu-plugins/performance/disable-emoji-support.php) | A utility plugin to disable emoji support. | 1.0.0. | 6.6+ | 7.4+ |
| [Disable File Editing](mu-plugins/security/disable-file-editing.php) | Prevents editing of theme/plugin files from admin | 1.0.0 | N/A+ | N/A+ |
| [Disable FloC](mu-plugins/disable/disable-floc.php) | A utility plugin to disable FLoC. | 1.0.0. | 6.6+ | 7.4+ |
| [Disable Heartbeat API](mu-plugins/performance/disable-heartbeat-api.php) | Disables or limits the Heartbeat API to reduce server load. Keeps it active only in the post editor where it's needed. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable oEmbed](mu-plugins/performance/disable-oembed.php) | Disables WordPress oEmbed functionality to reduce HTTP requests and improve performance. Use if you don't embed external content. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Pattern Directory](mu-plugins/disable/disable-pattern-directory.php) | Prevents the block editor from fetching block patterns from api.wordpress.org on every editor load. Eliminates the external HTTP request and brief pattern-loading delay. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Pingback](mu-plugins/security/disable-pingback.php) | Disables pingback XML-RPC method to prevent IP disclosure attacks behind firewalls/proxies. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Plugin Update Checks](mu-plugins/disable/disable-plugin-update-checks.php) | Disables automatic plugin update checks to reduce server load. Useful for managed/frozen environments where updates are controlled manually. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Post by Email](mu-plugins/disable/disable-post-by-email.php) | Disables WordPress post-by-email functionality to improve security and reduce attack surface. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Query String Versioning](mu-plugins/performance/disable-query-string-versioning.php) | Strips ?ver= query strings from enqueued script and style URLs so CDN and proxy caches can cache assets without per-version cache misses. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable REST API for Guests](mu-plugins/security/disable-rest-api-guests.php) | Blocks REST API access for unauthenticated users. Whitelisted routes (oEmbed) remain public. Use the 'mu_rest_guest_whitelist' filter to customise. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable RSS Feeds](mu-plugins/performance/disable-rss-feeds.php) | Redirects all RSS and Atom feed URLs to the homepage. Use only on sites that have no blog or do not need syndication. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Scaled Image Generation](mu-plugins/media/disable-scaled-images.php) | Prevents WordPress from creating a -scaled copy of large images (added in WP 5.3). Keeps original dimensions intact for sites that do not require server-side downscaling. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Site Health](mu-plugins/disable/disable-site-health.php) | Removes the Site Health admin page, dashboard widget, and its background cron check. For production sites managed externally where Site Health output is noise rather than signal. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Theme Update Checks](mu-plugins/disable/disable-theme-update-checks.php) | Prevents WordPress from checking for theme updates on wordpress.org. For fully managed environments where themes are updated via CI/CD. Complements the existing plugin update disabler. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Theme Update Notices](mu-plugins/admin/disable-theme-update-notices.php) | Hides "new version available" notices on the Themes screen for environments where the theme is deployed via CI/CD rather than updated through the admin. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable Unnecessary Link Headers](mu-plugins/disable/disable-link-headers.php) | A utility plugin to disable Unnecessary Link Headers. | 1.0.0. | 6.6+ | 7.4+ |
| [Disable User Enumeration](mu-plugins/security/disable-user-enumeration.php) | Prevents user enumeration via author archives and REST API to protect usernames from discovery. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable User Registration](mu-plugins/security/disable-user-registration.php) | Completely disables user registration via wp-login.php, REST API, and all registration endpoints | 1.0.0 | N/A+ | N/A+ |
| [Disable Welcome Panel](mu-plugins/admin/disable-welcome-panel.php) | Removes the welcome panel from the WordPress dashboard for a cleaner admin experience. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable WooCommerce Cart Fragments](mu-plugins/woocommerce/disable-wc-cart-fragments.php) | Prevents WooCommerce from enqueuing the cart-fragments AJAX script on pages where no cart interaction is possible, reducing unnecessary AJAX requests on every page load. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable WooCommerce Marketing Hub](mu-plugins/woocommerce/disable-wc-marketing-hub.php) | Removes the WooCommerce Marketing admin menu and disables marketing-related background cron events and HTTP calls to woocommerce.com. For stores that do not use Mailchimp or other WooCommerce marketing integrations. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable WooCommerce Status Dashboard Widget](mu-plugins/woocommerce/disable-wc-status-widget.php) | Removes the WooCommerce status dashboard widget from the WordPress admin dashboard, reducing noise for sites that use WooCommerce Analytics or a custom overview instead. | 1.0.0 | 6.6+ | 7.4+ |
| [Disable WP Version](mu-plugins/disable/disable-wp-version.php) | Remove WordPress version | 1.0.0. | 6.6+ | 7.4+ |
| [Disable XML-RPC](mu-plugins/security/disable-xml-rpc.php) | A utility plugin to disable XML-RPC. | 1.0.0. | 6.6+ | 7.4+ |
| [Disallow Weak Usernames](mu-plugins/security/disallow-weak-usernames.php) | Blocks creation of accounts with usernames commonly targeted by credential-stuffing attacks (admin, test, wordpress, etc.). | 1.0.0 | 6.6+ | 7.4+ |
| [Enable SVG Upload](mu-plugins/media/enable-svg-upload.php) | Enables SVG file uploads with security sanitization to prevent XSS attacks. Adds media library preview support. | 1.1.0 | 6.6+ | 7.4+ |
| [Environment Indicator](mu-plugins/development/environment-indicator.php) | Displays a visual indicator in the admin bar showing the current environment (Development/Staging/Production) to prevent editing the wrong site. | 1.0.0 | 6.6+ | 7.4+ |
| [Force Strong Passwords](mu-plugins/security/force-strong-passwords.php) | Rejects passwords shorter than 12 characters or missing uppercase, lowercase, number, and special character. Applied to profile updates and registration. | 1.0.0 | 6.6+ | 7.4+ |
| [Force WooCommerce Strong Passwords](mu-plugins/woocommerce/force-wc-strong-passwords.php) | Applies password strength validation to WooCommerce registration and checkout forms. WooCommerce skips WordPress's default strength rules for customers; this closes that gap. | 1.0.0 | 6.6+ | 7.4+ |
| [Hosting Disallowed Plugins](mu-plugins/maintenance/hosting-disallowed-plugins.php) | Disallows plugins known to cause issues on managed hosting platforms. Based on popular hosting platforms. | 1.1.0 | 6.6+ | 7.4+ |
| [Lazy Load Gravatars](mu-plugins/performance/lazy-load-gravatars.php) | Defers loading of Gravatar images until they're needed, reducing initial page load time and external HTTP requests. | 1.0.0 | 6.6+ | 7.4+ |
| [Limit Autoload Bloat](mu-plugins/cleanup/limit-autoload-bloat.php) | Runs daily to detect wp_options rows with autoload=yes that exceed a configurable byte threshold. Logs offenders and optionally sets them to autoload=no. Configure threshold via 'mu_autoload_size_threshold' filter (default 10 000 bytes). | 1.0.0 | 6.6+ | 7.4+ |
| [Limit Cron Concurrency](mu-plugins/performance/limit-cron-concurrency.php) | Uses a transient-based lock to prevent multiple simultaneous wp-cron.php processes from running the same scheduled hook, reducing database contention on busy sites. | 1.0.0 | 6.6+ | 7.4+ |
| [Limit Image Sizes](mu-plugins/media/limit-image-sizes.php) | Removes unnecessary intermediate image sizes (medium_large, 1536x1536, 2048x2048) that WordPress generates by default. Reduces disk usage and upload processing time. Keep list via 'mu_allowed_image_sizes' filter. | 1.0.0 | 6.6+ | 7.4+ |
| [Limit Login Attempts](mu-plugins/security/limit-login-attempts.php) | Blocks IP addresses after 4 failed login attempts for 24 hours to prevent brute force attacks. | 1.0.0 | 6.6+ | 7.4+ |
| [Limit Post Revisions](mu-plugins/performance/limit-post-revisions.php) | Limits the number of post revisions stored in the database to reduce bloat and improve performance. | 1.0.0 | 6.6+ | 7.4+ |
| [Limit Upload Size by Role](mu-plugins/media/limit-upload-size-by-role.php) | Sets different maximum upload file size limits for different user roles to prevent large uploads from non-admin users. | 1.0.0 | 6.6+ | 7.4+ |
| [OpenWP Club Core](mu-plugins/admin/openwpclub-message.php) | Core functionality and branding for OpenWP Club managed WordPress sites. | 1.0.0 | 6.6+ | 7.4+ |
| [Optimize WooCommerce Assets](mu-plugins/performance/optimize-woocommerce-assets.php) | Only loads WooCommerce scripts and styles on shop-related pages to improve site performance. | 1.0.0 | 6.6+ | 7.4+ |
| [Orphaned Post Meta Cleaner](mu-plugins/cleanup/orphaned-post-meta-cleaner.php) | Weekly cron job that deletes wp_postmeta rows whose post_id no longer exists in wp_posts. Reclaims space after posts are permanently deleted. | 1.0.0 | 6.6+ | 7.4+ |
| [Preconnect Resource Hints](mu-plugins/performance/preconnect-resource-hints.php) | Outputs <link rel="preconnect"> tags for configurable external origins. Reduces round-trip latency before the first byte of external resources. Configure via the 'mu_preconnect_origins' filter. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove Author Metabox](mu-plugins/admin/remove-author-metabox.php) | Removes the author meta box from post and page edit screens in WordPress admin. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove H1 Editor](mu-plugins/admin/disable-h1-editor.php) | Removes H1 heading option from both Classic Editor (TinyMCE) and Block Editor (Gutenberg) to improve SEO structure. | 1.1.0 | 6.6+ | 7.4+ |
| [Remove jQuery Migrate](mu-plugins/performance/remove-jquery-migrate.php) | Removes jQuery Migrate script from WordPress to reduce page weight. Only use if your theme and plugins don't require legacy jQuery support. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove Plugin Bloat](mu-plugins/admin/remove-plugin-bloat.php) | Removes admin bar items and source code footprints from popular plugins like WP Rocket, Redis Cache, etc. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove Unused Admin Menus](mu-plugins/admin/remove-unused-admin-menus.php) | Removes admin menu items defined in MU_REMOVE_ADMIN_MENUS. Useful for client sites where certain sections are irrelevant. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove WordPress Branding](mu-plugins/admin/remove-wordpress-branding.php) | Removes WordPress logo, admin footer text, and blocks access to WordPress about pages for white-label admin experience. | 1.0.0 | 6.6+ | 7.4+ |
| [Remove WP Embed](mu-plugins/performance/remove-wp-embed.php) | Remove WordPress embed script from frontend to improve performance. | 1.0.0 | 6.6+ | 7.4+ |
| [REST API Rate Limiting](mu-plugins/security/rest-api-rate-limiting.php) | Limits REST API requests per IP to prevent abuse. 60 requests/minute for guests, 200 for authenticated users. | 1.1.0 | 6.6+ | 7.4+ |
| [Restrict REST API Blocks](mu-plugins/security/restrict-rest-api-blocks.php) | Limits access to block-related REST API endpoints to authenticated users only. | 1.0.0 | 6.6+ | 7.4+ |
| [Sanitize File Name](mu-plugins/media/sanitize-file-names.php) | Automatically removes accents and special characters from uploaded file names for better compatibility. | 1.0.0 | 6.6+ | 7.4+ |
| [Scheduled Maintenance Window](mu-plugins/maintenance/scheduled-maintenance-window.php) | Shows a maintenance page automatically during a recurring time window. Define MU_MAINTENANCE_WINDOWS as an array of windows with 'start', 'end' (HH:MM), and optional 'days' (array of day names). Uses server time. | 1.0.0 | 6.6+ | 7.4+ |
| [Simple Maintenance Mode](mu-plugins/maintenance/simple-maintenance-mode.php) | Simple maintenance mode that shows a branded page. Toggle by creating/deleting .maintenance file in wp-content. | 1.0.0 | 6.6+ | 7.4+ |
| [Staging Robots Noindex](mu-plugins/development/staging-noindex.php) | Outputs X-Robots-Tag: noindex HTTP header and adds noindex to wp_robots on any environment that is not 'production'. Prevents staging or local sites from being indexed without editing wp-config.php. | 1.0.0 | 6.6+ | 7.4+ |
| [Strip EXIF Metadata](mu-plugins/media/strip-exif-metadata.php) | Removes GPS coordinates, camera model, and other EXIF data from uploaded JPEG images at save time using GD, reducing privacy exposure. | 1.0.0 | 6.6+ | 7.4+ |
| [Transient Cleaner](mu-plugins/cleanup/transient-cleaner.php) | Runs a daily WP-Cron job to delete expired transients from wp_options. Keeps the database tidy on sites that accumulate plugin transients over time. | 1.0.0 | 6.6+ | 7.4+ |
| [Trusted IP Allowlist](mu-plugins/security/trusted-ip-allowlist.php) | Blocks access to wp-admin and wp-login.php for IPs not in the allowlist. Define MU_TRUSTED_IPS as an array of allowed CIDR ranges or exact IPs. Only use on sites with a static management IP. | 1.0.0 | 6.6+ | 7.4+ |
| [Two-Factor Email OTP](mu-plugins/security/two-factor-email-otp.php) | Adds a second authentication step after a successful password login. A 6-digit code is emailed to the user; the session is not created until the code is verified. | 1.0.0 | 6.6+ | 7.4+ |
| [WooCommerce Order Auto-Complete](mu-plugins/woocommerce/wc-order-auto-complete.php) | Automatically transitions orders containing only virtual or downloadable products from 'processing' to 'completed' immediately after payment. Eliminates manual order management for digital-goods stores. | 1.0.0 | 6.6+ | 7.4+ |
| [WooCommerce Order Data Retention](mu-plugins/woocommerce/wc-order-data-retention.php) | Monthly cron job that permanently deletes WooCommerce orders older than a configurable number of days in terminal statuses (completed, cancelled, refunded). Supports both HPOS and legacy post-based orders. Override age via 'mu_wc_order_retention_days' filter (default 730 = ~2 years). | 1.0.0 | 6.6+ | 7.4+ |
| [WooCommerce Store Manager Mode](mu-plugins/woocommerce/store-manager.php) | Provides a simplified "Store Manager" view for WooCommerce users. Toggle between full WordPress admin and a decluttered, WooCommerce-focused interface via the admin bar. | 1.1.0 | 6.6+ | 7.4+ |

<!-- PLUGIN_LIST_END -->
