<?php

/**
 * Store Manager Mode for WooCommerce
 *
 * Plugin name:       WooCommerce Store Manager Mode
 * Plugin URI:        https://openwpclub.com
 * Description:       Provides a simplified "Store Manager" view for WooCommerce users. Toggle between full WordPress admin and a decluttered, WooCommerce-focused interface via the admin bar.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       woo-store-manager
 *
 * ============================================================================
 * FEATURES:
 * - Admin bar toggle to switch between Full Admin and Store Manager modes
 * - Simplified sidebar showing only WooCommerce-related items
 * - Clean dashboard with store-focused widgets
 * - Per-user preference (remembered via user meta)
 * - Works with existing Shop Manager role or any WooCommerce-capable role
 * ============================================================================
 *
 * Inspired by the discussion: https://x.com/rmelogli/status/1923410199889866818
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Store Manager Mode Class
 */
class MU_Store_Manager_Mode
{
    /**
     * User meta key for storing mode preference
     */
    const META_KEY = 'mu_store_manager_mode';

    /**
     * Menu items to show in Store Manager mode
     */
    private static $allowed_menu_slugs = [
        'index.php',
        'woocommerce',
        'edit.php?post_type=product',
        'edit.php?post_type=shop_order',
        'wc-orders',
        'wc-admin&path=/analytics/overview',
        'users.php',
        'upload.php',
        'profile.php',
    ];

    /**
     * Top-level menus to keep in Store Manager mode
     */
    private static $allowed_top_menus = [
        'index.php',
        'woocommerce',
        'edit.php?post_type=product',
        'edit.php?post_type=shop_order',
        'wc-orders',
        'upload.php',
        'users.php',
    ];

    /**
     * Initialize the Store Manager Mode
     */
    public static function init()
    {
        if (!is_admin()) {
            return;
        }

        add_action('plugins_loaded', [__CLASS__, 'setup_hooks']);
    }

    /**
     * Setup hooks after plugins are loaded
     */
    public static function setup_hooks()
    {
        if (!class_exists('WooCommerce')) {
            return;
        }

        add_action('admin_bar_menu', [__CLASS__, 'add_mode_toggle'], 100);
        add_action('wp_ajax_mu_toggle_store_manager_mode', [__CLASS__, 'ajax_toggle_mode']);
        add_action('admin_init', [__CLASS__, 'handle_mode_toggle']);

        if (self::is_store_manager_mode()) {
            add_action('admin_menu', [__CLASS__, 'simplify_admin_menu'], 9999);
            add_action('wp_dashboard_setup', [__CLASS__, 'setup_dashboard_widgets'], 9999);
            add_action('admin_head', [__CLASS__, 'add_store_manager_styles']);
            add_filter('admin_title', [__CLASS__, 'modify_admin_title'], 10, 2);
        }

        add_action('admin_head', [__CLASS__, 'add_toggle_styles']);
    }

    /**
     * Check if current user is in Store Manager mode
     */
    public static function is_store_manager_mode()
    {
        if (!is_user_logged_in()) {
            return false;
        }

        $user_id = get_current_user_id();
        return (bool) get_user_meta($user_id, self::META_KEY, true);
    }

    /**
     * Check if user can use Store Manager mode
     */
    public static function can_use_store_manager()
    {
        return current_user_can('manage_woocommerce') || current_user_can('edit_products');
    }

    /**
     * Add mode toggle to admin bar
     */
    public static function add_mode_toggle($wp_admin_bar)
    {
        if (!self::can_use_store_manager()) {
            return;
        }

        $is_store_mode = self::is_store_manager_mode();
        $toggle_url = wp_nonce_url(
            add_query_arg('mu_toggle_store_mode', '1', admin_url()),
            'mu_toggle_store_mode'
        );

        $wp_admin_bar->add_node([
            'id'     => 'mu-store-manager-toggle',
            'title'  => $is_store_mode
                ? '<span class="ab-icon dashicons dashicons-admin-settings"></span><span class="ab-label">Full Admin</span>'
                : '<span class="ab-icon dashicons dashicons-store"></span><span class="ab-label">Store Manager</span>',
            'href'   => $toggle_url,
            'meta'   => [
                'title' => $is_store_mode
                    ? __('Switch to Full WordPress Admin', 'woo-store-manager')
                    : __('Switch to Store Manager Mode', 'woo-store-manager'),
                'class' => $is_store_mode ? 'mu-mode-active' : 'mu-mode-inactive',
            ],
        ]);

        if ($is_store_mode) {
            $wp_admin_bar->add_node([
                'id'     => 'mu-store-manager-indicator',
                'title'  => '<span class="ab-icon dashicons dashicons-store"></span> Store Mode',
                'parent' => 'top-secondary',
                'meta'   => [
                    'class' => 'mu-store-mode-indicator',
                ],
            ]);
        }
    }

    /**
     * Handle mode toggle via URL
     */
    public static function handle_mode_toggle()
    {
        if (!isset($_GET['mu_toggle_store_mode'])) {
            return;
        }

        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'mu_toggle_store_mode')) {
            wp_die(__('Security check failed.', 'woo-store-manager'));
        }

        if (!self::can_use_store_manager()) {
            wp_die(__('You do not have permission to use Store Manager mode.', 'woo-store-manager'));
        }

        $user_id = get_current_user_id();
        $current_mode = self::is_store_manager_mode();

        if ($current_mode) {
            delete_user_meta($user_id, self::META_KEY);
        } else {
            update_user_meta($user_id, self::META_KEY, '1');
        }

        wp_safe_redirect(admin_url());
        exit;
    }

    /**
     * AJAX handler for toggling mode
     */
    public static function ajax_toggle_mode()
    {
        check_ajax_referer('mu_store_manager_nonce', 'nonce');

        if (!self::can_use_store_manager()) {
            wp_send_json_error('Permission denied');
        }

        $user_id = get_current_user_id();
        $current_mode = self::is_store_manager_mode();

        if ($current_mode) {
            delete_user_meta($user_id, self::META_KEY);
            wp_send_json_success(['mode' => 'full']);
        } else {
            update_user_meta($user_id, self::META_KEY, '1');
            wp_send_json_success(['mode' => 'store']);
        }
    }

    /**
     * Simplify admin menu for Store Manager mode
     */
    public static function simplify_admin_menu()
    {
        global $menu, $submenu;

        $keep_menus = [
            'index.php',
            'separator1',
            'woocommerce',
            'edit.php?post_type=product',
            'edit.php?post_type=shop_order',
            'wc-orders',
            'wc-admin&path=/analytics/overview',
            'upload.php',
            'users.php',
            'separator-last',
        ];

        $keep_prefixes = [
            'wc-',
            'woocommerce',
        ];

        if (!is_array($menu)) {
            return;
        }

        foreach ($menu as $key => $item) {
            if (!isset($item[2])) {
                continue;
            }

            $menu_slug = $item[2];
            $keep = false;

            if (in_array($menu_slug, $keep_menus, true)) {
                $keep = true;
            }

            if (!$keep) {
                foreach ($keep_prefixes as $prefix) {
                    if (strpos($menu_slug, $prefix) === 0) {
                        $keep = true;
                        break;
                    }
                }
            }

            if (strpos($menu_slug, 'separator') !== false) {
                $keep = true;
            }

            if (!$keep) {
                remove_menu_page($menu_slug);
            }
        }

        $woo_submenu_remove = [
            'wc-admin&path=/marketing',
            'wc-admin&path=/extensions',
            'wc-addons',
        ];

        if (isset($submenu['woocommerce'])) {
            foreach ($submenu['woocommerce'] as $key => $item) {
                if (isset($item[2]) && in_array($item[2], $woo_submenu_remove, true)) {
                    unset($submenu['woocommerce'][$key]);
                }
            }
        }
    }

    /**
     * Setup dashboard widgets for Store Manager mode
     */
    public static function setup_dashboard_widgets()
    {
        global $wp_meta_boxes;

        $wp_meta_boxes['dashboard'] = [];

        wp_add_dashboard_widget(
            'mu_store_manager_welcome',
            __('Store Overview', 'woo-store-manager'),
            [__CLASS__, 'render_welcome_widget']
        );

        wp_add_dashboard_widget(
            'mu_store_manager_quick_stats',
            __('Quick Stats', 'woo-store-manager'),
            [__CLASS__, 'render_stats_widget']
        );

        wp_add_dashboard_widget(
            'mu_store_manager_recent_orders',
            __('Recent Orders', 'woo-store-manager'),
            [__CLASS__, 'render_recent_orders_widget']
        );

        wp_add_dashboard_widget(
            'mu_store_manager_low_stock',
            __('Low Stock Products', 'woo-store-manager'),
            [__CLASS__, 'render_low_stock_widget']
        );

        wp_add_dashboard_widget(
            'mu_store_manager_customers',
            __('Recent Customers', 'woo-store-manager'),
            [__CLASS__, 'render_customers_widget']
        );

        // Force fixed widget order
        add_filter('get_user_option_meta-box-order_dashboard', [__CLASS__, 'force_widget_order'], 10);
        add_filter('get_user_option_screen_layout_dashboard', [__CLASS__, 'force_two_columns'], 10);
    }

    /**
     * Force a consistent widget order for all users in Store Manager mode
     */
    public static function force_widget_order($order)
    {
        return [
            'normal'  => 'mu_store_manager_welcome,mu_store_manager_quick_stats,mu_store_manager_recent_orders',
            'side'    => 'mu_store_manager_low_stock,mu_store_manager_customers',
            'column3' => '',
            'column4' => '',
        ];
    }

    /**
     * Force two-column layout for Store Manager dashboard
     */
    public static function force_two_columns($columns)
    {
        return 2;
    }

    /**
     * Render the welcome widget
     */
    public static function render_welcome_widget()
    {
        $user = wp_get_current_user();
        ?>
        <div class="mu-store-welcome">
            <h3><?php printf(__('Welcome back, %s!', 'woo-store-manager'), esc_html($user->display_name)); ?></h3>
            <p class="mu-store-date"><?php echo esc_html(date_i18n(get_option('date_format') . ' - ' . get_option('time_format'))); ?></p>

            <div class="mu-quick-actions">
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=product')); ?>" class="button button-primary">
                    <?php esc_html_e('Add Product', 'woo-store-manager'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=shop_order')); ?>" class="button">
                    <?php esc_html_e('View Orders', 'woo-store-manager'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('users.php?role=customer')); ?>" class="button">
                    <?php esc_html_e('Customers', 'woo-store-manager'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wc-reports')); ?>" class="button">
                    <?php esc_html_e('Reports', 'woo-store-manager'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Render the stats widget
     */
    public static function render_stats_widget()
    {
        $orders_today = wc_orders_count('processing') + wc_orders_count('on-hold');

        $product_count = wp_count_posts('product');
        $published_products = $product_count->publish ?? 0;

        $low_stock_count = self::get_low_stock_count();
        $revenue_today = self::get_todays_revenue();

        $user_counts = count_users();
        $customer_count = $user_counts['avail_roles']['customer'] ?? 0;

        ?>
        <div class="mu-store-stats">
            <div class="mu-stat-box">
                <span class="mu-stat-number"><?php echo esc_html($orders_today); ?></span>
                <span class="mu-stat-label"><?php esc_html_e('Pending Orders', 'woo-store-manager'); ?></span>
            </div>
            <div class="mu-stat-box">
                <span class="mu-stat-number"><?php echo esc_html($published_products); ?></span>
                <span class="mu-stat-label"><?php esc_html_e('Products', 'woo-store-manager'); ?></span>
            </div>
            <div class="mu-stat-box mu-stat-customers">
                <span class="mu-stat-number"><?php echo esc_html($customer_count); ?></span>
                <span class="mu-stat-label"><?php esc_html_e('Customers', 'woo-store-manager'); ?></span>
            </div>
            <div class="mu-stat-box <?php echo $low_stock_count > 0 ? 'mu-stat-warning' : ''; ?>">
                <span class="mu-stat-number"><?php echo esc_html($low_stock_count); ?></span>
                <span class="mu-stat-label"><?php esc_html_e('Low Stock', 'woo-store-manager'); ?></span>
            </div>
            <?php if ($revenue_today !== null) : ?>
            <div class="mu-stat-box mu-stat-revenue">
                <span class="mu-stat-number"><?php echo wc_price($revenue_today); ?></span>
                <span class="mu-stat-label"><?php esc_html_e("Today's Revenue", 'woo-store-manager'); ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render recent orders widget
     */
    public static function render_recent_orders_widget()
    {
        $orders = wc_get_orders([
            'limit'   => 5,
            'orderby' => 'date',
            'order'   => 'DESC',
        ]);

        if (empty($orders)) {
            echo '<p>' . esc_html__('No orders yet.', 'woo-store-manager') . '</p>';
            return;
        }

        echo '<table class="mu-orders-table widefat">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__('Order', 'woo-store-manager') . '</th>';
        echo '<th>' . esc_html__('Customer', 'woo-store-manager') . '</th>';
        echo '<th>' . esc_html__('Status', 'woo-store-manager') . '</th>';
        echo '<th>' . esc_html__('Total', 'woo-store-manager') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($orders as $order) {
            $order_url = $order->get_edit_order_url();
            $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
            if (trim($customer_name) === '') {
                $customer_name = __('Guest', 'woo-store-manager');
            }

            echo '<tr>';
            echo '<td><a href="' . esc_url($order_url) . '">#' . esc_html($order->get_order_number()) . '</a></td>';
            echo '<td>' . esc_html($customer_name) . '</td>';
            echo '<td><span class="order-status status-' . esc_attr($order->get_status()) . '">' . esc_html(wc_get_order_status_name($order->get_status())) . '</span></td>';
            echo '<td>' . wp_kses_post($order->get_formatted_order_total()) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '<p class="mu-view-all"><a href="' . esc_url(admin_url('edit.php?post_type=shop_order')) . '">' . esc_html__('View all orders', 'woo-store-manager') . ' &rarr;</a></p>';
    }

    /**
     * Render low stock products widget
     */
    public static function render_low_stock_widget()
    {
        $low_stock_amount = absint(get_option('woocommerce_notify_low_stock_amount', 2));

        $products = wc_get_products([
            'limit'        => 5,
            'stock_status' => 'instock',
            'meta_query'   => [
                [
                    'key'     => '_stock',
                    'value'   => $low_stock_amount,
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                ],
            ],
        ]);

        $out_of_stock = wc_get_products([
            'limit'        => 5,
            'stock_status' => 'outofstock',
        ]);

        $all_products = array_merge($products, $out_of_stock);

        if (empty($all_products)) {
            echo '<p class="mu-all-good">' . esc_html__('All products are well stocked!', 'woo-store-manager') . '</p>';
            return;
        }

        echo '<ul class="mu-low-stock-list">';
        foreach (array_slice($all_products, 0, 5) as $product) {
            $stock_qty = $product->get_stock_quantity();
            $status_class = $stock_qty <= 0 ? 'out-of-stock' : 'low-stock';
            $status_text = $stock_qty <= 0 ? __('Out of stock', 'woo-store-manager') : sprintf(__('%d left', 'woo-store-manager'), $stock_qty);

            echo '<li class="' . esc_attr($status_class) . '">';
            echo '<a href="' . esc_url(get_edit_post_link($product->get_id())) . '">' . esc_html($product->get_name()) . '</a>';
            echo '<span class="stock-status">' . esc_html($status_text) . '</span>';
            echo '</li>';
        }
        echo '</ul>';
    }

    /**
     * Render customers widget
     */
    public static function render_customers_widget()
    {
        $customer_query = new WP_User_Query([
            'role'    => 'customer',
            'number'  => 5,
            'orderby' => 'registered',
            'order'   => 'DESC',
        ]);

        $customers = $customer_query->get_results();

        if (empty($customers)) {
            echo '<p>' . esc_html__('No customers yet.', 'woo-store-manager') . '</p>';
            return;
        }

        echo '<table class="mu-customers-table widefat">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__('Customer', 'woo-store-manager') . '</th>';
        echo '<th>' . esc_html__('Email', 'woo-store-manager') . '</th>';
        echo '<th>' . esc_html__('Orders', 'woo-store-manager') . '</th>';
        echo '<th>' . esc_html__('Spent', 'woo-store-manager') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($customers as $customer) {
            $wc_customer = new WC_Customer($customer->ID);
            $order_count = $wc_customer->get_order_count();
            $total_spent = $wc_customer->get_total_spent();

            echo '<tr>';
            echo '<td class="mu-customer-cell">';
            echo get_avatar($customer->ID, 24, '', '', ['class' => 'mu-customer-avatar']);
            echo '<a href="' . esc_url(get_edit_user_link($customer->ID)) . '">' . esc_html($customer->display_name) . '</a>';
            echo '</td>';
            echo '<td><a href="mailto:' . esc_attr($customer->user_email) . '">' . esc_html($customer->user_email) . '</a></td>';
            echo '<td>' . esc_html($order_count) . '</td>';
            echo '<td>' . wp_kses_post(wc_price($total_spent)) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';

        $total_customers = count_users();
        $customer_count = $total_customers['avail_roles']['customer'] ?? 0;

        echo '<p class="mu-view-all">';
        echo '<span class="mu-customer-count">' . sprintf(esc_html__('%d total customers', 'woo-store-manager'), $customer_count) . '</span>';
        echo '<a href="' . esc_url(admin_url('users.php?role=customer')) . '">' . esc_html__('View all', 'woo-store-manager') . ' &rarr;</a>';
        echo '</p>';
    }

    /**
     * Get count of low stock products
     */
    private static function get_low_stock_count()
    {
        $low_stock_amount = absint(get_option('woocommerce_notify_low_stock_amount', 2));

        $low_stock = wc_get_products([
            'limit'        => -1,
            'stock_status' => 'instock',
            'return'       => 'ids',
            'meta_query'   => [
                [
                    'key'     => '_stock',
                    'value'   => $low_stock_amount,
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                ],
            ],
        ]);

        $out_of_stock = wc_get_products([
            'limit'        => -1,
            'stock_status' => 'outofstock',
            'return'       => 'ids',
        ]);

        return count($low_stock) + count($out_of_stock);
    }

    /**
     * Get today's revenue
     */
    private static function get_todays_revenue()
    {
        $today_start = strtotime('today midnight');
        $today_end = strtotime('tomorrow midnight') - 1;

        $orders = wc_get_orders([
            'limit'        => -1,
            'status'       => ['completed', 'processing'],
            'date_created' => $today_start . '...' . $today_end,
        ]);

        $revenue = 0;
        foreach ($orders as $order) {
            $revenue += $order->get_total();
        }

        return $revenue;
    }

    /**
     * Add styles for the toggle button in admin bar
     */
    public static function add_toggle_styles()
    {
        ?>
        <style>
            /* Toggle Button Styles */
            #wp-admin-bar-mu-store-manager-toggle > a {
                background: #2271b1 !important;
                color: #fff !important;
            }
            #wp-admin-bar-mu-store-manager-toggle > a:hover {
                background: #135e96 !important;
            }
            #wp-admin-bar-mu-store-manager-toggle .ab-icon:before {
                color: #fff !important;
                top: 2px;
            }
            #wp-admin-bar-mu-store-manager-toggle.mu-mode-active > a {
                background: #d63638 !important;
            }
            #wp-admin-bar-mu-store-manager-toggle.mu-mode-active > a:hover {
                background: #b32d2e !important;
            }

            /* Store Mode Indicator */
            #wp-admin-bar-mu-store-manager-indicator > a {
                background: linear-gradient(135deg, #7f54b3 0%, #9b66c6 100%) !important;
                color: #fff !important;
                font-weight: 500;
            }
            #wp-admin-bar-mu-store-manager-indicator .ab-icon:before {
                color: #fff !important;
                top: 2px;
            }
        </style>
        <?php
    }

    /**
     * Add Store Manager mode specific styles
     */
    public static function add_store_manager_styles()
    {
        ?>
        <style>
            /* Store Manager Dashboard Styles */
            .mu-store-welcome {
                text-align: center;
                padding: 20px;
            }
            .mu-store-welcome h3 {
                margin: 0 0 5px;
                font-size: 1.4em;
            }
            .mu-store-date {
                color: #646970;
                margin-bottom: 20px;
            }
            .mu-quick-actions {
                display: flex;
                gap: 10px;
                justify-content: center;
                flex-wrap: wrap;
            }
            .mu-quick-actions .button {
                min-width: 120px;
            }

            /* Stats Widget */
            .mu-store-stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 15px;
            }
            .mu-stat-box {
                text-align: center;
                padding: 15px;
                background: #f6f7f7;
                border-radius: 4px;
            }
            .mu-stat-number {
                display: block;
                font-size: 2em;
                font-weight: 600;
                color: #1d2327;
            }
            .mu-stat-label {
                display: block;
                font-size: 0.85em;
                color: #646970;
                margin-top: 5px;
            }
            .mu-stat-warning {
                background: #fcf0f1;
            }
            .mu-stat-warning .mu-stat-number {
                color: #d63638;
            }
            .mu-stat-revenue {
                background: #edfaef;
            }
            .mu-stat-revenue .mu-stat-number {
                color: #00a32a;
            }
            .mu-stat-customers {
                background: #f0f6fc;
            }
            .mu-stat-customers .mu-stat-number {
                color: #2271b1;
            }

            /* Orders Table */
            .mu-orders-table {
                margin: 0;
            }
            .mu-orders-table th,
            .mu-orders-table td {
                padding: 8px 10px;
            }
            .mu-orders-table .order-status {
                display: inline-block;
                padding: 2px 8px;
                border-radius: 3px;
                font-size: 0.85em;
            }
            .mu-orders-table .status-processing {
                background: #c6e1c6;
                color: #5b841b;
            }
            .mu-orders-table .status-on-hold {
                background: #f8dda7;
                color: #94660c;
            }
            .mu-orders-table .status-pending {
                background: #e5e5e5;
                color: #646970;
            }
            .mu-orders-table .status-completed {
                background: #c8d7e1;
                color: #2e4453;
            }
            .mu-view-all {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin: 10px 0 0;
            }
            .mu-customer-count {
                color: #646970;
                font-size: 0.9em;
            }

            /* Customers Table */
            .mu-customers-table {
                margin: 0;
            }
            .mu-customers-table th,
            .mu-customers-table td {
                padding: 8px 10px;
                vertical-align: middle;
            }
            .mu-customer-cell {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .mu-customer-avatar {
                border-radius: 50%;
            }

            /* Low Stock List */
            .mu-low-stock-list {
                margin: 0;
                padding: 0;
                list-style: none;
            }
            .mu-low-stock-list li {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 0;
                border-bottom: 1px solid #f0f0f1;
            }
            .mu-low-stock-list li:last-child {
                border-bottom: none;
            }
            .mu-low-stock-list .stock-status {
                font-size: 0.85em;
                padding: 2px 8px;
                border-radius: 3px;
            }
            .mu-low-stock-list .low-stock .stock-status {
                background: #f8dda7;
                color: #94660c;
            }
            .mu-low-stock-list .out-of-stock .stock-status {
                background: #fcf0f1;
                color: #d63638;
            }
            .mu-all-good {
                text-align: center;
                color: #00a32a;
                padding: 20px;
            }

            /* Store Manager Mode - Cleaner Admin */
            body.store-manager-mode #adminmenu {
                background: linear-gradient(180deg, #1d2327 0%, #2c3338 100%);
            }
            body.store-manager-mode #adminmenu li.wp-menu-separator {
                background: #3c434a;
            }

            /* Lock widget positions - disable drag and drop */
            body.store-manager-mode .postbox .hndle {
                cursor: default;
            }
            body.store-manager-mode .postbox .hndle:hover {
                cursor: default;
            }
            body.store-manager-mode .meta-box-sortables {
                pointer-events: auto;
            }
            body.store-manager-mode .meta-box-sortables .postbox {
                pointer-events: auto;
            }
            body.store-manager-mode #dashboard-widgets .meta-box-sortables {
                min-height: 0 !important;
            }
            body.store-manager-mode .postbox .handlediv,
            body.store-manager-mode .postbox .handle-order-higher,
            body.store-manager-mode .postbox .handle-order-lower {
                display: none !important;
            }
            body.store-manager-mode #screen-options-link-wrap {
                display: none;
            }
        </style>
        <script>
            document.body.classList.add('store-manager-mode');
        </script>
        <?php
    }

    /**
     * Modify admin title in Store Manager mode
     */
    public static function modify_admin_title($admin_title, $title)
    {
        return str_replace(' &lsaquo; ', ' &lsaquo; Store &lsaquo; ', $admin_title);
    }
}

// Initialize
MU_Store_Manager_Mode::init();
