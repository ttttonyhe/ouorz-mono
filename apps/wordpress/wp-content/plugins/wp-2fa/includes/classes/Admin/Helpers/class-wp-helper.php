<?php
/**
 * Responsible for the WP core functionalities
 *
 * @package    wp2fa
 * @subpackage helpers
 * @since      2.2.0
 * @copyright  2022 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Admin\Helpers;

use WP2FA\Admin\Helpers\User_Helper;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * WP helper class
 */
if ( ! class_exists( '\WP2FA\Admin\Helpers\WP_Helper' ) ) {

	/**
	 * All the WP functionality must go trough this class
	 *
	 * @since 2.2.0
	 */
	class WP_Helper {

		/**
		 * Hold the user roles as array - Human readable is used for key of the array, and the internal role name is the value.
		 *
		 * @var array
		 *
		 * @since 2.2.0
		 */
		private static $user_roles = array();

		/**
		 * Hold the user roles as array - Internal role name is used for key of the array, and the human readable format is the value.
		 *
		 * @var array
		 *
		 * @since 2.2.0
		 */
		private static $user_roles_wp = array();

		/**
		 * Keeps the value of the multisite install of the WP
		 *
		 * @var bool
		 *
		 * @since 2.2.0
		 */
		private static $is_multisite = null;

		/**
		 * Holds array with all the sites in multisite WP installation
		 *
		 * @var array
		 */
		private static $sites = array();

		/**
		 * Inits the class, and fires all the necessarily methods
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function init() {
			if ( self::is_multisite() ) {
				\add_action( 'network_admin_notices', array( __CLASS__, 'show_critical_admin_notice' ) );
			} else {
				\add_action( 'admin_notices', array( __CLASS__, 'show_critical_admin_notice' ) );
			}
		}

		/**
		 * Checks if specific role exists
		 *
		 * @param string $role - The name of the role to check.
		 *
		 * @return boolean
		 *
		 * @since 2.2.0
		 */
		public static function is_role_exists( string $role ): bool {
			self::set_roles();

			if ( in_array( $role, self::$user_roles, true ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Returns the currently available WP roles - the Human readable format is the key
		 *
		 * @return array
		 *
		 * @since 2.2.0
		 */
		public static function get_roles() {
			self::set_roles();

			return self::$user_roles;
		}

		/**
		 * Returns the currently available WP roles
		 *
		 * @return array
		 *
		 * @since 2.2.0
		 */
		public static function get_roles_wp() {
			if ( empty( self::$user_roles_wp ) ) {
				self::set_roles();
				self::$user_roles_wp = array_flip( self::$user_roles );
			}

			return self::$user_roles_wp;
		}

		/**
		 * Shows critical notices to the admin
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function show_critical_admin_notice() {
			if ( User_Helper::is_admin() ) {
				/**
				 * Gives the ability to show notices to the admins
				 */
				\do_action( WP_2FA_PREFIX . 'critical_notice' );
			}
		}

		/**
		 * Check is this is a multisite setup.
		 *
		 * @return boolean
		 *
		 * @since 2.2.0
		 */
		public static function is_multisite() {
			if ( null === self::$is_multisite ) {
				self::$is_multisite = function_exists( 'is_multisite' ) && is_multisite();
			}
			return self::$is_multisite;
		}

		/**
		 * Collects all the sites from multisite WP installation
		 *
		 * @return array
		 */
		public static function get_multi_sites(): array {
			if ( self::is_multisite() ) {
				if ( empty( self::$sites ) ) {

					self::$sites = \get_sites();
				}

				return self::$sites;
			}

			return array();
		}

		/**
		 * Calculating the signature.
		 *
		 * @param array $data - Array with data to create a signature for.
		 *
		 * @return string
		 *
		 * @since 2.2.2
		 */
		public static function calculate_api_signature( array $data ): string {
			$now   = new \DateTime( 'now', new \DateTimeZone( 'UTC' ) );
			$nonce = $now->getTimestamp();

			$pk_hash               = hash( 'sha512', $data['license_key'] . '|' . $nonce );
			$authentication_string = base64_encode( $pk_hash . '|' . $nonce );

			return $authentication_string;
		}

		/**
		 * Sets the internal variable with all the existing WP roles
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		private static function set_roles() {
			if ( empty( self::$user_roles ) ) {
				global $wp_roles;

				if ( null === $wp_roles ) {
					wp_roles();
				}

				self::$user_roles = array_flip( $wp_roles->get_names() );
			}
		}
	}
}
