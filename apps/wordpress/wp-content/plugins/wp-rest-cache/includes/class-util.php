<?php
/**
 * Util functions for usage throughout the plugin.
 *
 * @link: https://www.acato.nl
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes
 */

namespace WP_Rest_Cache_Plugin\Includes;

/**
 * Util functions for usage throughout the plugin.
 *
 * This class define util functions that can be used throughout the plugin.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes
 * @author     Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Util {

	/**
	 * Get the home url, without converting it to the current language.
	 *
	 * @return string The home url.
	 */
	public static function get_home_url() {
		add_filter( 'wpml_skip_convert_url_string', '__return_true' );
		$home_url = get_home_url();
		remove_filter( 'wpml_skip_convert_url_string', '__return_true' );
		return $home_url;
	}
}
