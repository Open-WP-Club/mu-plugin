<?php

/**
 * Blocks PHP execution inside the uploads directory
 *
 * Plugin name:       Block PHP Execution in Uploads
 * Plugin URI:        https://openwpclub.com
 * Description:       Writes and maintains an .htaccess file in wp-content/uploads that denies execution of PHP and other server-side script files. Mitigates malware droppers that plant a disguised script through a vulnerable plugin, theme, or upload form. Apache/LiteSpeed only — nginx needs an equivalent rule in the server config.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       block-uploads-php-execution
 */

defined('ABSPATH') or die();

define('MU_UPLOADS_HTACCESS_MARKER', '# BEGIN MU Block PHP Execution');

/**
 * Denial rules wrapped in a marker block, so the .htaccess can be checked for
 * and left alone (or repaired) without disturbing any other rules a site
 * owner may have added to the same file.
 */
function mu_uploads_php_block_rules()
{
    return implode("\n", [
        MU_UPLOADS_HTACCESS_MARKER,
        '<FilesMatch "\.(?i:php|phtml|php[0-9]|phar|pht)$">',
        '    <IfModule mod_authz_core.c>',
        '        Require all denied',
        '    </IfModule>',
        '    <IfModule !mod_authz_core.c>',
        '        Order allow,deny',
        '        Deny from all',
        '    </IfModule>',
        '</FilesMatch>',
        '<IfModule mod_php.c>',
        '    php_flag engine off',
        '</IfModule>',
        '<IfModule mod_php7.c>',
        '    php_flag engine off',
        '</IfModule>',
        '<IfModule mod_php8.c>',
        '    php_flag engine off',
        '</IfModule>',
        '# END MU Block PHP Execution',
    ]) . "\n";
}

/**
 * Write the block into the uploads .htaccess if it is missing. Runs on every
 * admin_init: the check is a single file read, cheap enough to skip a cron
 * schedule while still self-healing after a migration or backup restore
 * drops the file.
 */
add_action(
    'admin_init',
    static function () {
        $upload_dir = wp_upload_dir();
        $basedir    = $upload_dir['basedir'] ?? '';

        if (!$basedir || !is_dir($basedir) || !wp_is_writable($basedir)) {
            return;
        }

        $htaccess_path = trailingslashit($basedir) . '.htaccess';
        $existing       = file_exists($htaccess_path) ? (string) file_get_contents($htaccess_path) : '';

        if (strpos($existing, MU_UPLOADS_HTACCESS_MARKER) !== false) {
            return;
        }

        $contents = ltrim(rtrim($existing) . "\n\n" . mu_uploads_php_block_rules());
        file_put_contents($htaccess_path, $contents, LOCK_EX);
    }
);
