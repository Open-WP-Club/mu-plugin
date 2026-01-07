<?php

/**
 * A utility plugin to add developer signature to footer
 *
 * Plugin name:       Add Footer Signature
 * Plugin URI:        https://openwpclub.com
 * Description:       Adds a developer signature comment to the website footer for attribution and contact purposes.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       add-footer-signature
 */

add_action(
  'wp_footer',
  static function () {
    echo "\n<!-- OpenWP Club <hello@openwpclub.com> -->\n";
  },
  PHP_INT_MAX,
  0
);
