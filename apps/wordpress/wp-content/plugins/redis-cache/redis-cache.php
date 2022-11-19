<?php
/**
 * Plugin Name: Redis Object Cache
 * Plugin URI: https://wordpress.org/plugins/redis-cache/
 * Description: A persistent object cache backend powered by Redis. Supports Predis, PhpRedis, Relay, replication, sentinels, clustering and WP-CLI.
 * Version: 2.2.2
 * Text Domain: redis-cache
 * Domain Path: /languages
 * Network: true
 * Requires PHP: 7.2
 * Author: Till Krüss
 * Author URI: https://objectcache.pro
 * GitHub Plugin URI: https://github.com/rhubarbgroup/redis-cache
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package RhubarbGroup/RedisCache
 */

defined( 'ABSPATH' ) || exit;

define( 'WP_REDIS_FILE', __FILE__ );
define( 'WP_REDIS_PLUGIN_PATH', __DIR__ );
define( 'WP_REDIS_BASENAME', plugin_basename( WP_REDIS_FILE ) );
define( 'WP_REDIS_PLUGIN_DIR', plugin_dir_url( WP_REDIS_FILE ) );

$meta = get_file_data( WP_REDIS_FILE, [ 'Version' => 'Version' ] );

define( 'WP_REDIS_VERSION', $meta['Version'] );

require_once WP_REDIS_PLUGIN_PATH . '/includes/class-autoloader.php';

$autoloader = new Rhubarb\RedisCache\Autoloader();
$autoloader->register();
$autoloader->add_namespace( 'Rhubarb\RedisCache', WP_REDIS_PLUGIN_PATH . '/includes' );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'redis', Rhubarb\RedisCache\CLI\Commands::class );
}

register_activation_hook(
    WP_REDIS_FILE,
    [ Rhubarb\RedisCache\Plugin::class, 'on_activation' ]
);

Rhubarb\RedisCache\Plugin::instance();

if ( ! function_exists( 'redis_object_cache' ) ) {
    /**
     * Returns the plugin instance.
     *
     * @return Rhubarb\RedisCache\Plugin
     */
    function redis_object_cache() {
        return Rhubarb\RedisCache\Plugin::instance();
    }
}
