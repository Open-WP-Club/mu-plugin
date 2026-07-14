<?php

/**
 * A utility plugin to disable pingback XML-RPC method
 *
 * Plugin name:       Disable Pingback
 * Plugin URI:        https://openwpclub.com
 * Description:       Disables pingback XML-RPC method and removes the X-Pingback HTTP header to prevent IP disclosure attacks behind firewalls/proxies.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-pingback
 * Information from:  https://www.netsparker.com/blog/web-security/xml-rpc-protocol-ip-disclosure-attacks/
 */

defined('ABSPATH') or die();

add_filter(
  'xmlrpc_methods',
  static function ($methods) {
    unset($methods['pingback.ping']);
    return $methods;
  },
  11,
  1
);

add_filter(
    'wp_headers',
    static function ($headers) {
        unset($headers['X-Pingback']);
        return $headers;
    }
);
