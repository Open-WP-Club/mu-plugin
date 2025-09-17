<?php

/**
 * A utility plugin to disable XML-RPC
 *
 * Plugin name:       Disable XML-RPC 
 * Plugin URI:        https://openwpclub.com
 * Description:       A utility plugin to disable XML-RPC.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0.
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-xml-rpc
 */

add_filter('xmlrpc_enabled', '__return_false');
