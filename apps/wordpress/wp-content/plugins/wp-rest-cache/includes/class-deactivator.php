<?php
/**
 * Fired during plugin deactivation
 *
 * @link: https://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes
 */

namespace WP_Rest_Cache_Plugin\Includes;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Deactivator {

	/**
	 * Deactivate the plugin. Clear cache and delete Must-Use plugin.
	 *
	 * @return void
	 */
	public static function deactivate() {
		Caching\Caching::get_instance()->clear_caches( true );
		if ( file_exists( WPMU_PLUGIN_DIR . '/wp-rest-cache.php' ) ) {
			$active = false;
			if ( is_multisite() ) {
				$current_blog_id = get_current_blog_id();
				$site_ids        = get_sites( [ 'fields' => 'ids' ] );
				foreach ( $site_ids as $site_id ) {
					if ( $current_blog_id === $site_id ) {
						continue;
					}
					switch_to_blog( $site_id );
					$active = is_plugin_active( 'wp-rest-cache/wp-rest-cache.php' );
					restore_current_blog();
					if ( $active ) {
						break;
					}
				}
			}
			if ( ! $active ) {
				unlink( WPMU_PLUGIN_DIR . '/wp-rest-cache.php' );
			}
		}
	}
}
