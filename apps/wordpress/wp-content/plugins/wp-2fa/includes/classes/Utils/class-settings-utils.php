<?php
/**
 * Responsible for various settings manipulations.
 *
 * @package    wp2fa
 * @subpackage utils
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Utils;

use WP2FA\Admin\Helpers\WP_Helper;

/**
 * Utility class handling settings CRUD.
 *
 * @package WP2FA\Utils
 * @since 1.7.0
 */
class Settings_Utils {

	/**
	 * Creates a hash based on the passed settings array.
	 *
	 * @param array $settings - Settings array.
	 *
	 * @return string
	 */
	public static function create_settings_hash( array $settings ): string {
		return md5( json_encode( $settings ) ); // phpcs:ignore
	}

	/**
	 * Returns an option by given name
	 *
	 * @param string $setting_name - The name of the option.
	 * @param mixed  $default_value - The default value if there is no one stored.
	 *
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	public static function get_option( $setting_name, $default_value = false ) {
		$prefixed_setting_name = self::setting_prefixer( $setting_name );
		return ( WP_Helper::is_multisite() ) ? get_network_option( null, $prefixed_setting_name, $default_value ) : get_option( $prefixed_setting_name, $default_value );
	}

	/**
	 * Updates an option by a given name with a given value
	 *
	 * @param string $setting_name - The name of the setting to update.
	 * @param mixed  $new_value - The value to be stored.
	 *
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	public static function update_option( $setting_name, $new_value ) {
		$prefixed_setting_name = self::setting_prefixer( $setting_name );
		return ( WP_Helper::is_multisite() ) ? update_network_option( null, $prefixed_setting_name, $new_value ) : update_option( $prefixed_setting_name, $new_value, true );
	}

	/**
	 * Deletes an option by a given name
	 *
	 * @param string $setting_name - The name of the option to delete.
	 *
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	public static function delete_option( $setting_name ) {
		$prefixed_setting_name = self::setting_prefixer( $setting_name );
		return ( WP_Helper::is_multisite() ) ? delete_network_option( null, $prefixed_setting_name ) : delete_option( $prefixed_setting_name );
	}

	/**
	 * Created a prefixed setting name from supplied string.
	 *
	 * @param  string $setting_name - The name of the setting.
	 *
	 * @return string
	 */
	private static function setting_prefixer( $setting_name ) {
		// Ensure we have not already been passed a prefixed setting name.
		return ( strpos( $setting_name, 'wp_2fa_' ) === 0 ) ? $setting_name : WP_2FA_PREFIX . $setting_name;
	}

	/**
	 * Converts a string (e.g. 'yes' or 'no') to a bool.
	 *
	 * @since 2.0.0
	 * @param string $string String to convert.
	 * @return bool
	 */
	public static function string_to_bool( $string ) {
		return is_bool( $string ) ? $string : ( 'yes' === $string || 1 === $string || 'true' === $string || '1' === $string || 'on' === $string || 'enable' === $string );
	}

	/**
	 * Converts a bool to a 'yes' or 'no'.
	 *
	 * @since 2.0.0
	 * @param bool $bool String to convert.
	 * @return string
	 */
	public static function bool_to_string( $bool ) {
		if ( ! is_bool( $bool ) ) {
			$bool = self::string_to_bool( $bool );
		}
		return true === $bool ? 'yes' : 'no';
	}
}
