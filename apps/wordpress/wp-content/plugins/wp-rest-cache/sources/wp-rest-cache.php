<?php
/**
 * The plugin Must-Use file
 *
 * @link:   https://www.acato.nl
 * @since   2018.2.0
 * @package WP_Rest_Cache
 *
 * @wordpress-plugin
 * Plugin Name:       WP REST Cache - Must-Use Plugin
 * Plugin URI:        https://www.acato.nl
 * Description:       This is the Must-Use version of the WP REST Cache plugin. Deactivating that plugin will remove this Must-Use plugin.
 * Version:           2021.3.0
 * Author:            Richard Korthuis - Acato
 * Author URI:        https://www.acato.nl
 * Text Domain:       wp-rest-cache
 * Domain Path:       /languages
 */

/**
 * Make sure plugin functions are loaded.
 */
require_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( is_plugin_active( 'wp-rest-cache/wp-rest-cache.php' ) ) {
	include_once WP_PLUGIN_DIR . '/wp-rest-cache/wp-rest-cache.php';
} elseif ( file_exists( WPMU_PLUGIN_DIR . '/wp-rest-cache/wp-rest-cache.php' ) ) {
	include_once WPMU_PLUGIN_DIR . '/wp-rest-cache/wp-rest-cache.php';
} else {
	return;
}

$wp_rest_cache_api = new \WP_Rest_Cache_Plugin\Includes\API\Endpoint_Api();
$wp_rest_cache_api->get_api_cache();
