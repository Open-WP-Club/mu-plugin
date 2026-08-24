<?php

/**
 * Dashboard widget listing WooCommerce products below stock threshold
 *
 * Plugin name:       WooCommerce Low Stock Notice
 * Plugin URI:        https://openwpclub.com
 * Description:       Adds a dashboard widget listing stock-managed products at or below their low-stock threshold, so store admins see restock needs without opening WooCommerce reports. Override the fallback threshold (used when a product has no threshold set) via the 'mu_wc_low_stock_fallback' filter (default 5).
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.1
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       wc-low-stock-notice
 */

defined('ABSPATH') or die();

add_action(
    'wp_dashboard_setup',
    static function () {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        wp_add_dashboard_widget(
            'mu_wc_low_stock_widget',
            __('Low Stock Products', 'wc-low-stock-notice'),
            static function () {
                $fallback = (int) apply_filters('mu_wc_low_stock_fallback', 5);

                // ponytail: scans first 200 in-stock managed products only; switch to a
                // direct wc_get_low_stock_amount meta_query if catalog exceeds that.
                $products = wc_get_products([
                    'status'       => 'publish',
                    'stock_status' => 'instock',
                    'manage_stock' => true,
                    'limit'        => 200,
                    'orderby'      => 'title',
                ]);

                $low = array_filter($products, static function ($product) use ($fallback) {
                    $threshold = $product->get_low_stock_amount();
                    $threshold = $threshold !== '' ? (int) $threshold : $fallback;
                    return $product->get_stock_quantity() !== null && $product->get_stock_quantity() <= $threshold;
                });

                if (empty($low)) {
                    echo '<p>' . esc_html__('No products are low on stock.', 'wc-low-stock-notice') . '</p>';
                    return;
                }

                echo '<ul>';
                foreach ($low as $product) {
                    printf(
                        '<li><a href="%s">%s</a> — %d %s</li>',
                        esc_url(get_edit_post_link($product->get_id())),
                        esc_html($product->get_name()),
                        (int) $product->get_stock_quantity(),
                        esc_html__('left', 'wc-low-stock-notice')
                    );
                }
                echo '</ul>';
            }
        );
    }
);
