<?php

/**
 * WooCommerce order auto-complete for virtual / downloadable products
 *
 * Plugin name:       WooCommerce Order Auto-Complete
 * Plugin URI:        https://openwpclub.com
 * Description:       Automatically transitions orders containing only virtual or downloadable products from 'processing' to 'completed' immediately after payment. Eliminates manual order management for digital-goods stores.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       wc-order-auto-complete
 */

defined('ABSPATH') or die();

if (!defined('WC_PLUGIN_FILE')) {
    return;
}

add_action(
    'woocommerce_payment_complete',
    static function (int $order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        foreach ($order->get_items() as $item) {
            /** @var WC_Order_Item_Product $item */
            $product = $item->get_product();

            if (!$product || (!$product->is_virtual() && !$product->is_downloadable())) {
                return; // Physical item found — do not auto-complete
            }
        }

        $order->update_status(
            'completed',
            __('Order auto-completed: all items are virtual/downloadable.', 'wc-order-auto-complete')
        );
    },
    10,
    1
);
