<?php

/**
 * A utility plugin to optimize WooCommerce asset loading
 *
 * Plugin name:       Optimize WooCommerce Assets
 * Plugin URI:        https://openwpclub.com
 * Description:       Only loads WooCommerce scripts and styles on shop-related pages to improve site performance.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       optimize-woocommerce-assets
 */

// Prevent direct access
defined('ABSPATH') or die();
add_action('wp_enqueue_scripts', 'mu_disable_woocommerce_scripts', 99);

function mu_disable_woocommerce_scripts()
{
  // If WooCommerce isn't active, bail
  if (!class_exists('WooCommerce')) {
    return;
  }

  // Check if we're on a WooCommerce page
  if (is_woocommerce_page()) {
    return; // Keep scripts on WooCommerce pages
  }

  // Check for WooCommerce shortcodes/blocks in content
  if (has_woocommerce_content()) {
    return; // Keep scripts if content has WooCommerce elements
  }

  // Not a WooCommerce page - remove the scripts!
  remove_woocommerce_assets();
}

function is_woocommerce_page()
{
  // WooCommerce conditional tags
  if (
    is_shop() ||           // Shop page
    is_product() ||        // Single product
    is_product_category() || // Product categories
    is_product_tag() ||    // Product tags
    is_cart() ||           // Cart page
    is_checkout() ||       // Checkout page
    is_account_page() ||   // My Account
    is_wc_endpoint_url()   // Any WooCommerce endpoint
  ) {
    return true;
  }

  return false;
}

function has_woocommerce_content()
{
  global $post;

  if (!$post) {
    return false;
  }

  // Check for WooCommerce shortcodes
  $wc_shortcodes = [
    'products',
    'product',
    'product_category',
    'add_to_cart',
    'woocommerce_cart',
    'woocommerce_checkout',
    'woocommerce_my_account',
  ];

  foreach ($wc_shortcodes as $shortcode) {
    if (has_shortcode($post->post_content, $shortcode)) {
      return true;
    }
  }

  // Check for WooCommerce blocks
  if (
    has_block('woocommerce/product-search') ||
    has_block('woocommerce/featured-product') ||
    has_block('woocommerce/handpicked-products') ||
    has_block('woocommerce/product-best-sellers') ||
    has_block('woocommerce/product-category') ||
    has_block('woocommerce/product-new') ||
    has_block('woocommerce/product-on-sale') ||
    has_block('woocommerce/product-top-rated') ||
    has_block('woocommerce/products-by-attribute')
  ) {
    return true;
  }

  return false;
}

function remove_woocommerce_assets()
{
  // Dequeue WooCommerce styles
  wp_dequeue_style('woocommerce-layout');
  wp_dequeue_style('woocommerce-smallscreen');
  wp_dequeue_style('woocommerce-general');

  // Dequeue WooCommerce scripts
  wp_dequeue_script('wc-cart-fragments');
  wp_dequeue_script('woocommerce');
  wp_dequeue_script('wc-add-to-cart');
  wp_dequeue_script('wc-add-to-cart-variation');
  wp_dequeue_script('wc-single-product');
  wp_dequeue_script('wc-checkout');

  // Deregister to prevent other code from loading them
  wp_deregister_script('wc-cart-fragments');
  wp_deregister_script('woocommerce');
  wp_deregister_script('wc-add-to-cart');
}
