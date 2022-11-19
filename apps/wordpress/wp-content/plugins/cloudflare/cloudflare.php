<?php
/*
Plugin Name: Cloudflare
Plugin URI: https://blog.cloudflare.com/new-wordpress-plugin/
Description: Cloudflare speeds up and protects your WordPress site.
Version: 4.11.0
Requires PHP: 7.2
Author: Cloudflare, Inc.
License: BSD-3-Clause
*/

// The following constants are available. Add them to wp-config.php to enable.

// To configure Cloudflare credentials via environment vars (defined elsewhere)
// define('CLOUDFLARE_EMAIL', $_ENV['CLOUDFLARE_EMAIL']);
// define('CLOUDFLARE_API_KEY', $_ENV['CLOUDFLARE_API_KEY']);
// define('CLOUDFLARE_DOMAIN_NAME', $_ENV['CLOUDFLARE_DOMAIN_NAME']);

// To enable HTTP/2 Server Push feature:
// define('CLOUDFLARE_HTTP2_SERVER_PUSH_ACTIVE', true);

// Cloudflare has a limit of how many resources can be pushed by HTTP/2 Server Push
// (3 KiB by default). Add the following to change that amount:
// define('CLOUDFLARE_HTTP2_SERVER_PUSH_HEADER_SIZE', 3072);

// To enable error logging when HTTP/2 Server Push header size is exceeded:
// define('CLOUDFLARE_HTTP2_SERVER_PUSH_LOG', true);

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

define('CLOUDFLARE_MIN_PHP_VERSION', '7.2');
define('CLOUDFLARE_MIN_WP_VERSION', '3.4');
define('CLOUDFLARE_PLUGIN_DIR', plugin_dir_path(__FILE__));

// PHP version check has to go here because the below code uses namespaces
if (version_compare(PHP_VERSION, CLOUDFLARE_MIN_PHP_VERSION, '<')) {
    // We need to load "plugin.php" manually to call "deactivate_plugins"
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    deactivate_plugins(plugin_basename(__FILE__), true);
    wp_die('<p>The Cloudflare plugin requires a PHP version of at least ' . CLOUDFLARE_MIN_PHP_VERSION . '; you have ' . PHP_VERSION . '.</p>', 'Plugin Activation Error', array('response' => 200, 'back_link' => true));
}

// Plugin uses namespaces. To support old PHP version which doesn't support
// namespaces we load everything in "cloudflare.loader.php"
require_once CLOUDFLARE_PLUGIN_DIR . 'cloudflare.loader.php';
