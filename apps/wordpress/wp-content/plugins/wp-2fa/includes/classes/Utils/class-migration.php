<?php
/**
 * Responsible for plugin updates.
 *
 * @package    wp2fa
 * @subpackage utils
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Utils;

use \WP2FA\Utils\User_Utils as User_Utils;
use WP2FA\Utils\Settings_Utils as Settings_Utils;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Migration class
 */
if ( ! class_exists( '\WP2FA\Utils\Migration' ) ) {

	/**
	 * Put all you migration methods here
	 *
	 * @package WP2FA\Utils
	 * @since 1.6
	 */
	class Migration extends Abstract_Migration {

		/**
		 * The name of the option from which we should extract version
		 * Note: version is expected in version format - 1.0.0; 1; 1.0; 1.0.0.0
		 * Note: only numbers will be processed
		 *
		 * @var string
		 *
		 * @since 1.6.0
		 */
		protected static $version_option_name = WP_2FA_PREFIX . 'plugin_version';

		/**
		 * The constant name where the plugin version is stored
		 * Note: version is expected in version format - 1.0.0; 1; 1.0; 1.0.0.0
		 * Note: only numbers will be processed
		 *
		 * @var string
		 *
		 * @since 1.6.0
		 */
		protected static $const_name_of_plugin_version = 'WP_2FA_VERSION';

		/**
		 * The name of the plugin settings
		 *
		 * @var string
		 */
		private static $plugin_settings_name = WP_2FA_SETTINGS_NAME;

		/**
		 * The name of the plugin policy settings
		 *
		 * @var string
		 */
		private static $plugin_policy_name = WP_2FA_POLICY_SETTINGS_NAME;

		/**
		 * The name of the plugin white label settings
		 *
		 * @var string
		 */
		private static $plugin_white_label_name = WP_2FA_WHITE_LABEL_SETTINGS_NAME;

		/**
		 * The name of the plugin email settings
		 *
		 * @var string
		 */
		private static $plugin_email_settings_name = WP_2FA_EMAIL_SETTINGS_NAME;

		/**
		 * Migration for version upto 1.6.0
		 *
		 * @return void
		 * @since 1.6.0
		 */
		protected static function migrate_up_to_160() {
			$settings = self::get_settings( self::$plugin_settings_name );
			if ( ! is_array( $settings ) ) {
				return;
			}

			$needs_update = false;

			$settings_to_convert = array( 'enforced_roles', 'enforced_users', 'excluded_users', 'excluded_roles' );
			foreach ( $settings_to_convert as $setting_name ) {
				if ( array_key_exists( $setting_name, $settings ) && ! is_array( $settings[ $setting_name ] ) ) {
					$settings[ $setting_name ] = array_filter(
						explode( ',', $settings[ $setting_name ] )
					);
					$needs_update              = true;
				}
			}

			if ( ! isset( $settings['backup_codes_enabled'] ) ) {
				$settings['backup_codes_enabled'] = 'yes';
				$needs_update                     = true;
			}

			if ( $needs_update ) {
				// Update settings.
				self::set_settings( self::$plugin_settings_name, $settings );
			}
		}

		/**
		 * Migration for version upto 1.6.2
		 *
		 * @return void
		 * @since 1.6.2
		 */
		protected static function migrate_up_to_162() {
			$settings = self::get_settings( self::$plugin_settings_name );
			if ( ! is_array( $settings ) ) {
				return;
			}

			$needs_update = false;

			$settings_to_convert = array( 'excluded_sites' );
			foreach ( $settings_to_convert as $setting_name ) {
				if ( array_key_exists( $setting_name, $settings ) && ! is_array( $settings[ $setting_name ] ) ) {
					$original_settings_split   = array_filter(
						explode( ',', $settings[ $setting_name ] )
					);
					$settings[ $setting_name ] = array();
					foreach ( $original_settings_split as $value ) {
						$settings[ $setting_name ][] = mb_substr( $value, mb_strrpos( $value, ':' ) + 1 );
					}
					$needs_update = true;
				}
			}

			self::migrate_up_to_160();

			if ( $needs_update ) {
				// Update settings.
				self::set_settings( self::$plugin_settings_name, $settings );
			}
		}

		/**
		 * Migration for version upto 1.5.0
		 *
		 * @return void
		 */
		protected static function migrate_up_to_150() {
			$settings = self::get_settings( self::$plugin_settings_name );

			if ( is_array( $settings ) && array_key_exists( 'enforcment-policy', $settings ) ) {
				// Correct setting name.
				$settings['enforcement-policy'] = $settings['enforcment-policy'];
				// Remove old setting.
				unset( $settings['enforcment-policy'] );
				// Update settings.
				self::set_settings( self::$plugin_settings_name, $settings );
			}
		}

		/**
		 * Migration for version upto 1.7.0
		 *
		 * @return void
		 */
		protected static function migrate_up_to_170() {
			$settings = self::get_settings( self::$plugin_settings_name );

			if ( is_array( $settings ) && array_key_exists( 'notify_users', $settings ) ) {
				// Remove old setting.
				unset( $settings['notify_users'] );
				// Update settings.
				self::set_settings( self::$plugin_settings_name, $settings );
			}

			$email_settings  = self::get_settings( self::$plugin_email_settings_name );
			$items_to_remove = array( 'send_enforced_email', 'enforced_email_subject', 'enforced_email_body' );

			if ( is_array( $email_settings ) && User_Utils::in_array_all( $items_to_remove, $email_settings ) ) {
				foreach ( $items_to_remove as $item ) {
					if ( isset( $email_settings[ $item ] ) ) {
						unset( $email_settings[ $item ] );
					}
				}
				// Update settings.
				self::set_settings( self::$plugin_email_settings_name, $email_settings );
			}
		}

		/**
		 * Migration for version upto 2.0.0
		 * Separates the current settings into 3 different types of settings:
		 *  - Policy
		 *  - General
		 *  - White label
		 *
		 * @return void
		 */
		protected static function migrate_up_to_200() {
			$settings = self::get_settings( self::$plugin_settings_name );

			if ( is_array( $settings ) ) {

				$new_settings_array = array_flip(
					array(
						'enable_grace_cron',
						'limit_access',
						'delete_data_upon_uninstall',
						'enable_destroy_session',
					)
				);

				$new_white_label_array = array_flip(
					array(
						'default-text-code-page',
					)
				);

				$settings_array = array_intersect_key(
					$settings,
					$new_settings_array
				);

				$settings = array_diff_key( $settings, $new_settings_array );

				self::set_settings( self::$plugin_settings_name, $settings_array );

				$white_label_settings = array_intersect_key(
					$settings,
					$new_white_label_array
				);

				$settings = array_diff_key( $settings, $new_white_label_array );

				self::set_settings( self::$plugin_white_label_name, $white_label_settings );

				self::set_settings( self::$plugin_policy_name, $settings );
			}
		}

		/**
		 * Migration for version upto 2.2.0
		 *
		 * @return void
		 */
		protected static function migrate_up_to_220() {
			global $wpdb;

			$new_prefix = 'wp_2fa_trusted_device_';
			$old_prefix = 'wp2fa_trusted_device_';

			delete_transient( 'wp_2fa_config_file_hash' );

			$wpdb->query(
				$wpdb->prepare(
					"
				 UPDATE $wpdb->usermeta
				 SET meta_key = REPLACE( meta_key, %s, %s )
				 WHERE meta_key LIKE %s
				 ",
					array(
						$old_prefix,
						$new_prefix,
						$old_prefix . '%',
					)
				)
			);
		}

		/**
		 * Migration for version upto 2.3.0
		 *
		 * @return void
		 */
		protected static function migrate_up_to_230() {

			$version = self::get_settings( self::$version_option_name );

			if ( $version && version_compare( $version, '2.2.1', '<=' ) ) {
				$settings = self::get_settings( self::$plugin_white_label_name );
				
				if ( isset( $settings['enable_wizard_styling'] ) ) {
					$settings['enable_wizard_styling'] = false;
				} else {
					$settings = array();
					$settings['enable_wizard_styling'] = false;
				}

				self::set_settings( self::$plugin_white_label_name, $settings );
			}
		}

		/**
		 * Returns the plugin settings by a given setting type
		 *
		 * @param mixed $setting_name - The setting which needs to be extracted.
		 *
		 * @return mixed
		 */
		private static function get_settings( $setting_name ) {
			return Settings_Utils::get_option( $setting_name );
		}

		/**
		 * Updates the plugin settings
		 *
		 * @param mixed $setting_name - The setting which needs to be updated.
		 * @param mixed $settings - The settings values.
		 *
		 * @return void
		 */
		private static function set_settings( $setting_name, $settings ) {
			Settings_Utils::update_option( $setting_name, $settings );
		}
	}
}
