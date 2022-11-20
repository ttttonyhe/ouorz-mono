<?php
/**
 * Autoload all plugin classes
 *
 * @link: https://www.acato.nl
 * @since 2018.5
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes
 */

namespace WP_Rest_Cache_Plugin\Includes;

/**
 * Autoload all plugin classes.
 *
 * This class autoloads the plugin classes when they are being used.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Autoloader {


	/**
	 * Autoload classes related to this plugin.
	 *
	 * @param string $class_name The requested class.
	 *
	 * @return void
	 */
	public static function autoload( $class_name ) {
		$file_path = explode( '\\', $class_name );
		if ( isset( $file_path[0] ) && 'WP_Rest_Cache_Plugin' === $file_path[0] ) {
			$file_name = strtolower( $file_path[ count( $file_path ) - 1 ] );
			unset( $file_path[ count( $file_path ) - 1 ] );
			unset( $file_path[0] );
			$subdir = '';
			if ( count( $file_path ) ) {
				$subdir = strtolower( implode( DIRECTORY_SEPARATOR, $file_path ) );
			}
			$subdir .= DIRECTORY_SEPARATOR;

			$file_name       = str_ireplace( '_', '-', $file_name );
			$file_name_parts = explode( '-', $file_name );

			switch ( $file_name_parts[ count( $file_name_parts ) - 1 ] ) {
				case 'trait':
				case 'interface':
					$type = $file_name_parts[ count( $file_name_parts ) - 1 ];
					unset( $file_name_parts[ count( $file_name_parts ) - 1 ] );
					$file_name = $type . '-' . implode( '-', $file_name_parts ) . '.php';
					break;
				default:
					$file_name = 'class-' . $file_name . '.php';
			}

			include_once plugin_dir_path( __DIR__ ) . $subdir . $file_name;
		} elseif ( 'WP_Rest_Cache_Endpoint_Api' === $class_name ) {
			include_once plugin_dir_path( __DIR__ ) . DIRECTORY_SEPARATOR . 'deprecated' . DIRECTORY_SEPARATOR . 'class-wp-rest-cache-endpoint-api.php';
		}
	}

}
