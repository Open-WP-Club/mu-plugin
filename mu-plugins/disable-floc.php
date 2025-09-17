<?php

/**
 * A utility plugin to disable FLoC
 *
 * Plugin name:       Disable FloC
 * Plugin URI:        https://openwpclub.com
 * Description:       A utility plugin to disable FLoC.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0.
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-floc
 */

function mu_disable_floc()
{
  $headers['Permissions-Policy'] = 'interest-cohort=()';
  return $headers;
}
add_filter('wp_headers', 'mu_disable_floc');
