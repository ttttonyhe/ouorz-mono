<?php
/**
 * Responsible for the plugin methods
 *
 * @package    wp2fa
 * @subpackage admin_controllers
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Admin\Controllers;

use WP2FA\WP2FA;
use WP2FA\Admin\Controllers\Settings;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Methods class
 */
if ( ! class_exists( '\WP2FA\Admin\Controllers\Methods' ) ) {

	/**
	 * All the methods related functionality must be extracted from this class. Responsible only for global methods data, not the user method related stuff.
	 *
	 * @since 2.2.0
	 */
	class Methods {

		/**
		 * Holds all the enabled methods in the plugin
		 *
		 * @var array
		 *
		 * @since 2.2.0
		 */
		private static $enabled_methods = null;

		/**
		 * Works our a list of available 2FA methods. It doesn't include the disabled ones.
		 *
		 * TODO: There is a high possibility that this method is duplication of the Settings::get_providers - check and make the changes as there must be only one way to extract that info
		 *
		 * @return string[]
		 * @since 2.0.0
		 */
		public static function get_available_2fa_methods(): array {
			$available_methods = array();

			if ( ! empty( Settings::get_role_or_default_setting( 'enable_email', 'current' ) ) ) {
				$available_methods[] = 'email';
			}

			if ( ! empty( Settings::get_role_or_default_setting( 'enable_totp', 'current' ) ) ) {
				$available_methods[] = 'totp';
			}

			/**
			 * Add an option for external providers to implement their own 2fa methods and set them as available.
			 *
			 * @param array $available_methods - The array with all the available methods.
			 *
			 * @since 2.0.0
			 */
			return apply_filters( WP_2FA_PREFIX . 'available_2fa_methods', $available_methods );
		}

		/**
		 * Returns array with all the enabled methods in the plugin for the current role
		 *
		 * @param string $role - Role to extract data for.
		 *
		 * @return array
		 *
		 * @since 2.2.0
		 */
		public static function get_enabled_methods( $role = 'global' ): array {
			if ( null === self::$enabled_methods || ! isset( self::$enabled_methods[ $role ] ) ) {
				self::$enabled_methods[ $role ] = array();
				$providers                      = Settings::get_providers();

				foreach ( $providers as $provider ) {
					if ( Settings::is_provider_enabled_for_role( $role, $provider ) ) {
						if ( 'backup_codes' === $provider ) {
							// Backup codes is a secondary provider - ignore it.
							continue;
						} elseif ( 'backup_email' === $provider ) {
							// Backup email codes is a secondary provider - ignore it.
							continue;
						} elseif ( 'oob' === $provider ) {
							self::$enabled_methods[ $role ][ $provider ] = WP2FA::get_wp2fa_setting( 'enable_' . $provider . '_email', false, false, $role );
						} else {
							self::$enabled_methods[ $role ][ $provider ] = WP2FA::get_wp2fa_setting( 'enable_' . $provider, false, false, $role );
						}
					}
				}

				self::$enabled_methods[ $role ] = array_filter( self::$enabled_methods[ $role ] );
			}

			return self::$enabled_methods;
		}

		/**
		 * Returns text with the number of methods supported for the given role
		 *
		 * @param string $role - Role to extract data for.
		 *
		 * @since 2.2.0
		 *
		 * @return string
		 */
		public static function get_number_of_methods_text( $role = 'global' ) {
			return esc_html__(
                'There are {available_methods_count} methods available to choose from for 2FA:',
                'wp-2fa'
            );
		}

	}
}
