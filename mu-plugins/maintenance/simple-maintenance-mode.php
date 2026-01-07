<?php

/**
 * A simple maintenance mode toggle with company branding
 *
 * Plugin name:       Simple Maintenance Mode
 * Plugin URI:        https://openwpclub.com
 * Description:       Simple maintenance mode that shows a branded page. Toggle by creating/deleting .maintenance file in wp-content.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       simple-maintenance-mode
 */

// Check for maintenance mode on init
add_action('init', 'mu_check_maintenance_mode', 1);

function mu_check_maintenance_mode()
{
  // Skip for admin users
  if (current_user_can('manage_options')) {
    return;
  }

  // Skip for admin pages and login
  if (is_admin() || strpos($_SERVER['REQUEST_URI'], 'wp-login') !== false || strpos($_SERVER['REQUEST_URI'], 'wp-admin') !== false) {
    return;
  }

  // Check if maintenance file exists
  $maintenance_file = WP_CONTENT_DIR . '/.maintenance';

  if (file_exists($maintenance_file)) {
    mu_show_maintenance_page();
    exit;
  }
}

function mu_show_maintenance_page()
{
  // Set proper HTTP status
  status_header(503);
  header('Content-Type: text/html; charset=utf-8');
  header('Retry-After: 3600'); // Retry after 1 hour

  // Get site info
  $site_name = get_bloginfo('name');
  $site_logo = get_site_icon_url(180) ?: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMzIiIGN5PSIzMiIgcj0iMzIiIGZpbGw9IiMwMDczQUEiLz4KPHN2ZyB4PSIxNiIgeT0iMTYiIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJ3aGl0ZSI+CjxwYXRoIGQ9Ik0yMC41IDExSDE5VjdDMTkgNS44OSAxOC4xIDUgMTcgNUgxMy41VjMuNUMxMy41IDIuNjcgMTIuODMgMiAxMiAyUzEwLjUgMi42NyAxMC41IDMuNVY1SDdDNS45IDUgNSA1Ljg5IDUgN1YxMUgzLjVDMi42NyAxMSAyIDExLjY3IDIgMTIuNVMyLjY3IDE0IDMuNSAxNEg1VjE4QzUgMTkuMTEgNS45IDIwIDcgMjBIMTcuNUMxOC4zMyAyMCAxOSAxOS4zMyAxOSAxOC41UzE4LjMzIDE3IDE3LjUgMTdIN1YxMUgyMC41QzIxLjMzIDExIDIyIDEwLjMzIDIyIDkuNVMyMS4zMyA4IDIwLjUgOFoiLz4KPC9zdmc+Cg==';

?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance - <?php echo esc_html($site_name); ?></title>
    <style>
      * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
      }

      body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #333;
        line-height: 1.6;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .container {
        background: white;
        padding: 3rem;
        border-radius: 10px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        text-align: center;
        max-width: 500px;
        width: 90%;
      }

      .logo {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        border-radius: 50%;
      }

      h1 {
        color: #2c3e50;
        margin-bottom: 1rem;
        font-size: 2rem;
        font-weight: 600;
      }

      .subtitle {
        color: #7f8c8d;
        margin-bottom: 2rem;
        font-size: 1.1rem;
      }

      .message {
        color: #34495e;
        margin-bottom: 2rem;
        font-size: 1rem;
      }

      .spinner {
        margin: 2rem auto;
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
      }

      @keyframes spin {
        0% {
          transform: rotate(0deg);
        }

        100% {
          transform: rotate(360deg);
        }
      }

      .footer {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #ecf0f1;
        color: #95a5a6;
        font-size: 0.9rem;
      }
    </style>
  </head>

  <body>
    <div class="container">
      <img src="<?php echo esc_url($site_logo); ?>" alt="<?php echo esc_attr($site_name); ?>" class="logo">

      <h1><?php echo esc_html($site_name); ?></h1>

      <div class="subtitle">Under Maintenance</div>

      <div class="message">
        We're currently performing scheduled maintenance to improve your experience.
        We'll be back online shortly!
      </div>

      <div class="spinner"></div>

      <div class="footer">
        Please check back in a few minutes.<br>
        Thank you for your patience.
      </div>
    </div>
  </body>

  </html>
<?php
}

// Add admin notice with toggle instructions
add_action('admin_notices', 'mu_maintenance_mode_admin_notice');

function mu_maintenance_mode_admin_notice()
{
  $maintenance_file = WP_CONTENT_DIR . '/.maintenance';
  $is_active = file_exists($maintenance_file);

  if ($is_active) {
    echo '<div class="notice notice-warning"><p>';
    echo '<strong>Maintenance Mode is ACTIVE</strong> - Your site shows a maintenance page to visitors. ';
    echo 'To disable: Delete the <code>.maintenance</code> file from <code>wp-content/</code>';
    echo '</p></div>';
  }
}
