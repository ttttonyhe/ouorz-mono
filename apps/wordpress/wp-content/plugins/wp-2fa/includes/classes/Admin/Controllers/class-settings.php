<?php
/**
 * Responsible for the plugin settings iterations
 *
 * @package    wp2fa
 * @subpackage admin_controllers
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Admin\Controllers;

use \WP2FA\Admin\Settings_Page;
use WP2FA\WP2FA;
use WP2FA\Admin\User;
use WP2FA\Admin\Helpers\WP_Helper;
use WP2FA\Admin\Helpers\User_Helper;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * WP2FA Settings controller
 */
class Settings {

	/**
	 * The link to the WP admin settings page
	 *
	 * @var string
	 */
	private static $settings_page_link = '';

	/**
	 * The name of the WP2FA WP admin setup page
	 *
	 * @var string
	 */
	private static $setup_page_name = 'wp-2fa-setup';

	/**
	 * The link to the WP admin setup page
	 *
	 * @var string
	 */
	private static $setup_page_link = '';

	/**
	 * The link to the custom settings page (if one is presented)
	 *
	 * @var string
	 */
	private static $custom_setup_page_link = null;

	/**
	 * Array with all the backup methods available
	 *
	 * @var array
	 *
	 * @since 2.0.0
	 */
	private static $backup_methods = null;

	/**
	 * All available providers for the plugin
	 * For the specific role @see get_all_providers_for_role()
	 *
	 * @var array
	 *
	 * @since 2.2.0
	 */
	private static $all_providers = array();

	/**
	 * All the available providers by user roles
	 *
	 * @var array
	 *
	 * @since 2.2.0
	 */
	private static $all_providers_for_roles = array();

	/**
	 * Returns the link to the WP admin settings page, based on the current WP install
	 *
	 * @return string
	 */
	public static function get_settings_page_link() {
		if ( '' === self::$settings_page_link ) {
			if ( WP_Helper::is_multisite() ) {
				self::$settings_page_link = add_query_arg( 'page', Settings_Page::TOP_MENU_SLUG, network_admin_url( 'admin.php' ) );
			} else {
				self::$settings_page_link = add_query_arg( 'page', Settings_Page::TOP_MENU_SLUG, admin_url( 'admin.php' ) );
			}
		}

		return self::$settings_page_link;
	}

	/**
	 * Returns the link to the WP admin settings page, based on the current WP install
	 *
	 * @return string
	 */
	public static function get_setup_page_link() {
		if ( '' === self::$setup_page_link ) {
			if ( WP_Helper::is_multisite() ) {
				self::$setup_page_link = add_query_arg( 'show', self::$setup_page_name, network_admin_url( 'profile.php' ) );
			} else {
				self::$setup_page_link = add_query_arg( 'show', self::$setup_page_name, admin_url( 'profile.php' ) );
			}
		}

		return self::$setup_page_link;
	}

	/**
	 * Extracts the custom settings page URL
	 *
	 * @param mixed $user - User for which to extract the setting, null, WP_User or user id - @see get_role_or_default_setting method of this class.
	 *
	 * @return string
	 */
	public static function get_custom_page_link( $user = null ): string {
		if ( null === self::$custom_setup_page_link ) {
			self::$custom_setup_page_link = self::get_role_or_default_setting( 'custom-user-page-id', $user );

			if ( ! empty( self::$custom_setup_page_link ) ) {
				$custom_slug = '';
				if ( WP_Helper::is_multisite() ) {
					switch_to_blog( get_main_site_id() );

					$custom_slug                  = get_post_field( 'post_name', get_post( self::$custom_setup_page_link ) );
					self::$custom_setup_page_link = trailingslashit( get_site_url() ) . $custom_slug;

					restore_current_blog();
				} else {
					$custom_slug                  = get_post_field( 'post_name', get_post( self::$custom_setup_page_link ) );
					self::$custom_setup_page_link = trailingslashit( get_site_url() ) . $custom_slug;
				}
			}
		}

		return self::$custom_setup_page_link;
	}

	/**
	 * Check all the roles for given setting
	 *
	 * @param string $setting_name - The name of the setting to check for.
	 *
	 * @return boolean
	 *
	 * @since 2.0.0
	 */
	public static function check_setting_in_all_roles( string $setting_name ): bool {
		$roles = WP_Helper::get_roles();

		foreach ( $roles as $role ) {
			if ( ! empty( WP2FA::get_wp2fa_setting( $setting_name, false, false, $role ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Return setting specific for the given role or default setting (based on user)
	 *
	 * @param string  $setting_name - The name of the setting.
	 * @param mixed   $user - \WP_User or any string or null - if string the current user will be used, if null global plugin setting will be used.
	 * @param mixed   $role - The name of the role (or null).
	 * @param boolean $get_default_on_empty - Get default setting on empty setting value.
	 * @param boolean $get_default_value - Extracts default value.
	 *
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	public static function get_role_or_default_setting( string $setting_name, $user = null, $role = null, $get_default_on_empty = false, $get_default_value = false ) {
		/**
		 * No user specified - get the default settings
		 */
		if ( null === $user || \WP_2FA_PREFIX . 'no-user' === $user ) {
			return WP2FA::get_wp2fa_setting( $setting_name, $get_default_on_empty, $get_default_value );
		}

		/**
		 * There is an User - extract the role
		 */
		if ( $user instanceof \WP_User || is_int( $user ) ) {
			if ( null === $role ) {
				$role = User_Helper::get_user_role( $user );
			}
			return WP2FA::get_wp2fa_setting( $setting_name, $get_default_on_empty, $get_default_value, $role );
		}

		/**
		 * Current user - lets extract the role
		 */
		if ( null === $role ) {
			/**
			 * No logged in current user, ergo no roles - fall back to defaults
			 */
			if ( 0 === User::get_instance()->get_2fa_wp_user()->ID ) {
				return WP2FA::get_wp2fa_setting( $setting_name, $get_default_on_empty, $get_default_value );
			}

			$role = User_Helper::get_user_role();
		}

		return WP2FA::get_wp2fa_setting( $setting_name, $get_default_on_empty, $get_default_value, $role );
	}

	/**
	 * Returns all the backup methods currently supported
	 *
	 * @return array
	 *
	 * @since 2.0.0
	 */
	public static function get_backup_methods(): array {

		if ( null === self::$backup_methods ) {

			/**
			 * Gives the ability to add additional backup methods
			 *
			 * @param array The array with all the backup methods currently supported.
			 *
			 * @since 2.0.0
			 */
			self::$backup_methods = apply_filters( WP_2FA_PREFIX . 'backup_methods_list', array() );
		}

		return self::$backup_methods;
	}

	/**
	 * Get backup methods enabled for user based on its role
	 *
	 * @param \WP_User $user - The WP user which we must check.
	 *
	 * @return array
	 *
	 * @since 2.0.0
	 */
	public static function get_enabled_backup_methods_for_user_role( \WP_User $user ): array {
		$backup_methods = self::get_backup_methods();

		/**
		 * Extensions could change the enabled backup methods array.
		 *
		 * @param array - Backup methods array.
		 * @param \WP_User - The user to check for.
		 *
		 * @since 2.0.0
		 */
		return apply_filters( WP_2FA_PREFIX . 'backup_methods_enabled', $backup_methods, $user );
	}

	/**
	 * Returns all enabled providers for specific role
	 *
	 * @param string $role - The name of the role to check for.
	 *
	 * @return array
	 *
	 * @throws \Exception - if the role is wrong - throws an exception.
	 *
	 * @since 2.2.0
	 */
	public static function get_enabled_providers_for_role( string $role ) {

		if ( WP_Helper::is_role_exists( $role ) ) {
			self::get_all_roles_providers();

			return self::$all_providers_for_roles[ $role ];
		}

		throw new \Exception( 'Role provided does not exists - "' . $role . '"' );
	}

	/**
	 * Checks if given provider is enabled for the given role.
	 *
	 * @param string $role - The name of the role.
	 * @param string $provider - The name of the provider.
	 *
	 * @return boolean
	 *
	 * @throws \Exception - If the provider is not registered in the plugin.
	 *
	 * @since 2.2.0
	 */
	public static function is_provider_enabled_for_role( string $role, string $provider ): bool {
		self::get_providers();

		if ( in_array( $provider, self::$all_providers, true ) ) {
			self::get_enabled_providers_for_role( $role );
			if ( isset( self::$all_providers_for_roles[ $role ][ $provider ] ) ) {
				return true;
			}

			return false;
		}

		throw new \Exception( 'Non existing provider ' . $provider );
	}

	/**
	 * Returns all providers by roles.
	 * If given role does not have specified settings set - falls back to the default settings.
	 *
	 * @return array
	 *
	 * @since 2.2.0
	 */
	public static function get_all_roles_providers() {
		if ( empty( self::$all_providers_for_roles ) ) {
			$roles     = WP_Helper::get_roles();
			$providers = self::get_providers();

			foreach ( $roles as $role ) {
				self::$all_providers_for_roles[ $role ] = array();
				foreach ( $providers as $provider ) {
					if ( 'backup_codes' === $provider ) {
						self::$all_providers_for_roles[ $role ][ $provider ] = WP2FA::get_wp2fa_setting( $provider . '_enabled', false, false, $role );
					} elseif ( 'backup_email' === $provider ) {
						self::$all_providers_for_roles[ $role ][ $provider ] = WP2FA::get_wp2fa_setting( 'enable-email-backup', false, false, $role );
					} elseif ( 'oob' === $provider ) {
						self::$all_providers_for_roles[ $role ][ $provider ] = WP2FA::get_wp2fa_setting( 'enable_' . $provider . '_email', false, false, $role );
					} else {
						self::$all_providers_for_roles[ $role ][ $provider ] = WP2FA::get_wp2fa_setting( 'enable_' . $provider, false, false, $role );
					}
				}
				self::$all_providers_for_roles[ $role ] = array_filter( self::$all_providers_for_roles[ $role ] );
			}
		}

		return self::$all_providers_for_roles;
	}

	/**
	 * Grab list of all register providers in the plugin.
	 *
	 * @return array
	 */
	public static function get_providers() {
		if ( empty( self::$all_providers ) ) {
			self::$all_providers = array(
				'totp',
				'email',
				'backup_codes',
			);

			/**
			 * Filter the supplied providers.
			 *
			 * This lets third-parties either remove providers (such as Email), or
			 * add their own providers (such as text message or Clef).
			 *
			 * @param array $provider array if available options.
			 */
			self::$all_providers = apply_filters( WP_2FA_PREFIX . 'providers', self::$all_providers );
		}

		return self::$all_providers;
	}
}
