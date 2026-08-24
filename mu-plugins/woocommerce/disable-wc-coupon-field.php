<?php

/**
 * Disable WooCommerce coupon field on cart and checkout
 *
 * Plugin name:       Disable WC Coupon Field
 * Plugin URI:        https://openwpclub.com
 * Description:       Hides the coupon field on cart and checkout pages. Prevents customers from abandoning checkout to search for discount codes on stores that don't use coupons.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.1
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-wc-coupon-field
 */

defined('ABSPATH') or die();

add_filter('woocommerce_coupons_enabled', '__return_false');
