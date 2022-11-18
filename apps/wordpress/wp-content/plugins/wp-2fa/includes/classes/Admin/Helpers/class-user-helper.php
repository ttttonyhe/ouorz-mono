<?php
/**
 * Responsible for the User's operations
 *
 * @package    wp2fa
 * @subpackage helpers
 * @since      2.2.0
 * @copyright  2022 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Admin\Helpers;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

use WP2FA\Admin\User;
use WP2FA\Utils\User_Utils;
use WP2FA\Admin\Helpers\WP_Helper;
use WP2FA\Admin\Controllers\Settings;

/**
 * User's settings class
 */
if ( ! class_exists( '\WP2FA\Admin\Helpers\User_Helper' ) ) {

	/**
	 * All the user related settings must go trough this class.
	 *
	 * @since 2.2.0
	 */
	class User_Helper {

		/**
		 * Secret TOTP key meta name
		 */
		const SECRET_META_KEY = WP_2FA_PREFIX . 'totp_key';
		/**
		 * Enabled 2fa method for user meta name
		 */
		const ENABLED_METHODS_META_KEY = WP_2FA_PREFIX . 'enabled_methods';
		/**
		 * Email token for user meta name
		 */
		const TOKEN_META_KEY = WP_2FA_PREFIX . 'email_token';
		/**
		 * Global settings hash for user meta name
		 * That is used to check if user needs to be re-checked / re-configured, if the settings of the plugin are changed, probably the user settings also need to be changed - that meta holds the key to check against
		 */
		const USER_SETTINGS_HASH = WP_2FA_PREFIX . 'global_settings_hash';
		/**
		 * The meta name for the user 2FA status in the plugin
		 */
		const USER_2FA_STATUS = WP_2FA_PREFIX . '2fa_status';
		/**
		 * The user grace period expired meta key
		 */
		const USER_GRACE_KEY = WP_2FA_PREFIX . 'user_grace_period_expired';
		/**
		 * The user grace period expiry date meta key
		 */
		const USER_GRACE_EXPIRY_KEY = WP_2FA_PREFIX . 'grace_period_expiry';
		/**
		 * The user enforcement status
		 */
		const USER_ENFORCED_INSTANTLY = WP_2FA_PREFIX . 'user_enforced_instantly';
		/**
		 * The user reconfigure 2fa status
		 */
		const USER_NEEDS_TO_RECONFIGURE_2FA = WP_2FA_PREFIX . 'user_needs_to_reconfigure_2fa';
		/**
		 * The user enforcement state
		 */
		const USER_ENFORCEMENT_STATE = WP_2FA_PREFIX . 'enforcement_state';
		/**
		 * The user nag dismissed flag
		 */
		const USER_NAG_DISMISSED = WP_2FA_PREFIX . 'update_nag_dismissed';
		/**
		 * The default status of the user which has no status set yet
		 */
		const USER_UNDETERMINED_STATUS = 'no_determined_yet';
		/**
		 * The default status of the user which has no status set yet
		 */
		const USER_STATE_STATUSES = array(
			'optional',
			'excluded',
			'enforced',
		);

		/**
		 * The class user variable
		 *
		 * @var \WP_User
		 *
		 * @since 2.2.0
		 */
		private static $user = null;

		/**
		 * Returns the enabled 2FA method for the user.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function get_enabled_method_for_user( $user = null ) {
			self::set_proper_user( $user );

			/**
			 * Checks the enabled methods fo the user.
			 *
			 * @param mixed - Value of the method.
			 * @param \WP_User - The user which must be checked.
			 *
			 * @since 2.0.0
			 */
			return apply_filters( WP_2FA_PREFIX . 'user_enabled_methods', self::get_meta( self::ENABLED_METHODS_META_KEY ) );
		}

		/**
		 * Sets the enabled 2FA method for the user.
		 *
		 * @param string            $method - The name of the method to set.
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function set_enabled_method_for_user( string $method, $user = null ) {
			self::set_proper_user( $user );

			$set_method = self::set_meta( self::ENABLED_METHODS_META_KEY, $method );

			/**
			 * Fires when the user method is set.
			 *
			 * @param string - The method set for the user.
			 * @param \WP_User $user - The user for which the method has been set.
			 *
			 * @since 2.2.2
			 */
			\do_action( WP_2FA_PREFIX . 'method_has_been_set', $method, self::get_user() );

			return $set_method;
		}

		/**
		 * Removes the 2FA method for the user.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function remove_enabled_method_for_user( $user = null ) {
			self::set_proper_user( $user );

			self::remove_meta( self::ENABLED_METHODS_META_KEY, self::$user );
		}

		/**
		 * Returns the email token for the user.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function get_email_token_for_user( $user = null ) {
			self::set_proper_user( $user );

			return self::get_meta( self::TOKEN_META_KEY );
		}

		/**
		 * Sets the email token for the user.
		 *
		 * @param string            $token - The token to set for the user.
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function set_email_token_for_user( string $token, $user = null ) {
			self::set_proper_user( $user );

			return self::set_meta( self::TOKEN_META_KEY, $token );
		}

		/**
		 * Removes the email token for the user.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function remove_email_token_for_user( $user = null ) {
			self::set_proper_user( $user );

			self::remove_meta( self::TOKEN_META_KEY, self::$user );
		}

		/**
		 * Returns the global settings hash for the user.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function get_global_settings_hash_for_user( $user = null ) {
			self::set_proper_user( $user );

			return self::get_meta( self::USER_SETTINGS_HASH );
		}

		/**
		 * Sets the global settings hash for the user.
		 *
		 * @param string            $hash - The global settings hash to set for the user.
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function set_global_settings_hash_for_user( string $hash, $user = null ) {
			self::set_proper_user( $user );

			return self::set_meta( self::USER_SETTINGS_HASH, $hash );
		}

		/**
		 * Removes the global settings hash for the user.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function remove_global_settings_hash_for_user( $user = null ) {
			self::set_proper_user( $user );

			self::remove_meta( self::USER_SETTINGS_HASH, self::$user );
		}

		/**
		 * Returns the current 2FA status for the user.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function get_2fa_status( $user = null ) {
			self::set_proper_user( $user );

			$status = self::get_meta( self::USER_2FA_STATUS );

			if ( '' === trim( $status ) ) {
				$status = self::USER_UNDETERMINED_STATUS;
				self::set_2fa_status( self::USER_UNDETERMINED_STATUS );
			}

			return $status;
		}

		/**
		 * Sets the 2FA status for the user.
		 *
		 * @param string            $status - The name of the status to set.
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function set_2fa_status( string $status, $user = null ) {
			self::set_proper_user( $user );

			return self::set_meta( self::USER_2FA_STATUS, $status );
		}

		/**
		 * Removes the 2FA status for the user.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function remove_2fa_status( $user = null ) {
			self::set_proper_user( $user );

			self::remove_meta( self::USER_2FA_STATUS, self::$user );
		}


		/**
		 * Returns the current nag status for the user.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.3
		 */
		public static function get_nag_status( $user = null ) {
			self::set_proper_user( $user );

			return self::get_meta( self::USER_NAG_DISMISSED );
		}

		/**
		 * Sets the nag status for the user.
		 *
		 * @param bool              $status - The name of the status to set.
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.3
		 */
		public static function set_nag_status( bool $status, $user = null ) {
			self::set_proper_user( $user );

			return self::set_meta( self::USER_NAG_DISMISSED, $status );
		}

		/**
		 * Removes the nag status for the user.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return void
		 *
		 * @since 2.3
		 */
		public static function remove_nag_status( $user = null ) {
			self::set_proper_user( $user );

			self::remove_meta( self::USER_NAG_DISMISSED, self::$user );
		}

		/**
		 * Returns the current 2FA status for the user.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function get_user_expiry_date( $user = null ) {
			self::set_proper_user( $user );

			return self::get_meta( self::USER_GRACE_EXPIRY_KEY );
		}

		/**
		 * Sets the 2FA status for the user.
		 *
		 * @param string            $date - The period to set.
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function set_user_expiry_date( string $date, $user = null ) {
			self::set_proper_user( $user );

			return self::set_meta( self::USER_GRACE_EXPIRY_KEY, $date );
		}

		/**
		 * Removes the 2FA status for the user.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function remove_user_expiry_date( $user = null ) {
			self::set_proper_user( $user );

			self::remove_meta( self::USER_GRACE_EXPIRY_KEY, self::$user );
		}

		/**
		 * Returns the current 2FA enforcement status for the user.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function get_user_enforced_instantly( $user = null ) {
			self::set_proper_user( $user );

			return self::get_meta( self::USER_ENFORCED_INSTANTLY );
		}

		/**
		 * Sets the 2FA enforcement status for the user.
		 *
		 * @param bool              $status - The status for user enforcement.
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function set_user_enforced_instantly( bool $status, $user = null ) {
			self::set_proper_user( $user );

			return self::set_meta( self::USER_ENFORCED_INSTANTLY, $status );
		}

		/**
		 * Removes the 2FA enforcement status for the user.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function remove_user_enforced_instantly( $user = null ) {
			self::set_proper_user( $user );

			self::remove_meta( self::USER_ENFORCED_INSTANTLY, self::$user );
		}

		/**
		 * Returns the current 2FA needs to reconfigure status for the user.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function get_user_needs_to_reconfigure_2fa( $user = null ) {
			self::set_proper_user( $user );

			return self::get_meta( self::USER_NEEDS_TO_RECONFIGURE_2FA );
		}

		/**
		 * Sets the 2FA needs to reconfigure status for the user.
		 *
		 * @param bool              $status - The status for user enforcement.
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function set_user_needs_to_reconfigure_2fa( bool $status, $user = null ) {
			self::set_proper_user( $user );

			return self::set_meta( self::USER_NEEDS_TO_RECONFIGURE_2FA, $status );
		}

		/**
		 * Removes the 2FA needs to reconfigure status for the user.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function remove_user_needs_to_reconfigure_2fa( $user = null ) {
			self::set_proper_user( $user );

			self::remove_meta( self::USER_NEEDS_TO_RECONFIGURE_2FA, self::$user );
		}

		/**
		 * Every meta call for the user must go through this method, so we can unify the code.
		 *
		 * @param string            $meta - The meta name that we should check.
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function get_meta( string $meta, $user = null ) {
			self::set_proper_user( $user );

			return \get_user_meta( self::$user->ID, $meta, true );
		}

		/**
		 * Every meta storing call for the user must go through this method
		 *
		 * @param string            $meta - The meta name that we should check.
		 * @param mixed             $value - The value which should be stored.
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function set_meta( string $meta, $value, $user = null ) {
			self::set_proper_user( $user );

			return \update_user_meta( self::$user->ID, $meta, $value );
		}

		/**
		 * Removes meta for the given user
		 *
		 * @param string            $meta - The name of the meta.
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function remove_meta( string $meta, $user = null ) {
			self::set_proper_user( $user );

			return \delete_user_meta( self::$user->ID, $meta );
		}

		/**
		 * Returns the currently set user.
		 *
		 * @return \WP_User
		 *
		 * @since 2.2.0
		 */
		public static function get_user() {
			if ( null === self::$user ) {
				self::set_user();
			}

			return self::$user;
		}

		/**
		 * Returns WP User object.
		 *
		 * @param null|int|\WP_User $user - The WP user that must be used.
		 *
		 * @return \WP_User
		 *
		 * @since 2.2.0
		 */
		public static function get_user_object( $user = null ) {
			self::set_user( $user );

			return self::$user;
		}

		/**
		 * Sets the user
		 *
		 * @param null|int|\WP_User $user - The WP user that must be used.
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function set_user( $user = null ) {
			if ( $user instanceof \WP_User ) {
				if ( isset( self::$user ) && $user === self::$user ) {
					return;
				}
				self::$user = $user;
			} elseif ( false !== ( filter_var( $user, FILTER_VALIDATE_INT ) ) ) {
				if ( isset( self::$user ) && $user === self::$user->ID ) {
					return;
				}
				if ( ! function_exists( 'get_user_by' ) ) {
					require ABSPATH . WPINC . '/pluggable.php';
				}
				self::$user = \get_user_by( 'id', $user );
			} elseif ( is_string( $user ) ) {
				if ( isset( self::$user ) && $user === self::$user->ID ) {
					return;
				}
				if ( ! function_exists( 'get_user_by' ) ) {
					require ABSPATH . WPINC . '/pluggable.php';
				}
				self::$user = \get_user_by( 'login', $user );
			} else {
				self::$user = \wp_get_current_user();
			}
		}

		/**
		 * Returns the default role for the given user
		 *
		 * @param null|int|\WP_User $user - The WP user.
		 *
		 * @return string
		 *
		 * @since 2.2.0
		 */
		public static function get_user_role( $user = null ): string {
			self::set_proper_user( $user );

			if ( \is_multisite() ) {
				$blog_id = \get_current_blog_id();

				if ( ! is_user_member_of_blog( self::$user->ID, $blog_id ) ) {

					$user_blog_id = \get_active_blog_for_user( self::$user->ID );

					if ( ! null === $user_blog_id ) {

						self::$user = new \WP_User(
						// $user_id
							self::$user->ID,
							// $name | login, ignored if $user_id is set
							'',
							// $blog_id
							$user_blog_id->blog_id
						);
					}
				}
			}

			$role = reset( self::$user->roles );

			/**
			 * The code looks like this for clearness only
			 */
			if ( \is_multisite() ) {
				/**
				 * On multi site we can have user which has no assigned role, but it is superadmin.
				 * If the check confirms that - assign the role of the administrator to the user in order not to break our code.
				 *
				 * Unfortunately we could never be sure what is the name of the administrator role (someone could change this default value),
				 * in order to continue working we will use the presumption that if given role has 'manage_options' capability, then it is
				 * most probably administrator - so we will assign that role to the user.
				 */
				if ( false === $role && is_super_admin( self::$user->ID ) ) {

					$wp_roles = WP_Helper::get_roles_wp();
					foreach ( $wp_roles as $role_name => $wp_role ) {
						$admin_role_set = get_role( $role_name )->capabilities;
						if ( $admin_role_set['manage_options'] ) {
							$role = $role_name;

							break;
						}
					}
				}
			}

			return (string) $role;
		}

		/**
		 * Checks if the user method is within the selected methods for the given role
		 *
		 * @param null|int|\WP_User $user - The WP user.
		 *
		 * @return boolean
		 *
		 * @since 2.2.0
		 */
		public static function is_user_method_in_role_enabled_methods( $user = null ): bool {
			$enabled_method = self::get_enabled_method_for_user( $user );
			if ( empty( $enabled_method ) ) {
				return false;
			}
			$is_method_available = Settings::is_provider_enabled_for_role( self::get_user_role( $user ), $enabled_method );

			return $is_method_available;
		}

		/**
		 * Deletes the TOTP secret key for a user.
		 *
		 * @param null|int|\WP_User $user - The WP user that must be used.
		 *
		 * @return void
		 */
		public static function remove_user_totp_key( $user = null ) {
			self::set_proper_user( $user );

			self::remove_meta( self::SECRET_META_KEY, self::$user );
		}

		/**
		 * Returns the TOTP secret key for a user.
		 *
		 * @param null|int|\WP_User $user - The WP user that must be used.
		 *
		 * @return string
		 */
		public static function get_user_totp_key( $user = null ) {
			self::set_proper_user( $user );

			return self::get_meta( self::SECRET_META_KEY, self::$user );
		}

		/**
		 * Updates the TOTP secret key for a user.
		 *
		 * @param string            $value - The value of the TOTP key.
		 * @param null|int|\WP_User $user - The WP user that must be used.
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function set_user_totp_key( string $value, $user = null ) {
			self::set_proper_user( $user );

			self::set_meta( self::SECRET_META_KEY, $value, self::$user );
		}

		/**
		 * Removes all the meta keys associated with the given user
		 *
		 * @param null|int|\WP_User $user - The WP user for which we have to remove the meta data.
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function remove_all_2fa_meta_for_user( $user = null ) {
			self::set_proper_user( $user );

			$user_meta_values = array_filter(
				\get_user_meta( self::$user->ID ),
				function( $key ) {
					return strpos( $key, WP_2FA_PREFIX ) === 0;
				},
				ARRAY_FILTER_USE_KEY
			);

			foreach ( array_keys( $user_meta_values ) as $meta_name ) {
				self::remove_meta( $meta_name, $user );
			}

			/**
			 * Fires when the user method is removed.
			 *
			 * @param \WP_User $user - The user for which the method has been removed.
			 *
			 * @since 2.2.2
			 */
			\do_action( WP_2FA_PREFIX . 'method_has_been_removed', self::get_user() );

		}

		/**
		 * Quick boolean check for whether a given user is using two-step.
		 *
		 * @since 2.2.0
		 *
		 * @param null|int|\WP_User $user - The WP user that must be used.
		 * @return bool
		 */
		public static function is_user_using_two_factor( $user = null ) {
			self::set_proper_user( $user );

			return ! empty( self::get_enabled_method_for_user() );
		}

		/**
		 * Gets the user grace period from meta
		 *
		 * @param null|int|\WP_User $user - The WP user that must be used.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function get_grace_period( $user = null ) {
			self::set_proper_user( $user );

			return self::get_meta( self::USER_GRACE_KEY, self::$user );
		}

		/**
		 * Sets the user grace period from meta
		 *
		 * @param string            $value - The value of the meta key.
		 * @param null|int|\WP_User $user - The WP user that must be used.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function set_grace_period( $value, $user = null ) {
			self::set_proper_user( $user );

			return self::set_meta( self::USER_GRACE_KEY, $value, self::$user );
		}

		/**
		 * Sets the user grace period from meta
		 *
		 * @param null|int|\WP_User $user - The WP user that must be used.
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function remove_grace_period( $user = null ) {
			self::set_proper_user( $user );

			self::remove_meta( self::USER_GRACE_KEY, $user );
		}

		/**
		 * Checks if the user is locked. It only checks a single user meta field to keep this as fast as possible. The
		 * value of the field is updated elsewhere.
		 *
		 * @param null|int|\WP_User $user - The WP user that must be used.
		 *
		 * @return bool True if the user account is locked. False otherwise.
		 *
		 * @since 2.2.0
		 */
		public static function is_user_locked( $user = null ): bool {
			return (bool) self::get_grace_period( $user );
		}

		/**
		 * Checks if the given user has administrator or super administrator privileges
		 *
		 * @param null|int|\WP_User $user - The WP user that must be used.
		 *
		 * @return boolean
		 *
		 * @since 2.2.0
		 */
		public static function is_admin( $user = null ): bool {
			self::set_proper_user( $user );

			$is_admin = in_array( 'administrator', self::$user->roles, true ) || ( function_exists( 'is_super_admin' ) && is_super_admin( self::$user->ID ) );

			if ( ! $is_admin ) {
				return false;
			}
			return true;
		}

		/**
		 * Checks if user is excluded
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function is_excluded( $user = null ) {
			$state = self::get_user_state( $user );

			return 'excluded' === $state;
		}

		/**
		 * Updates the user state based on the current plugin settings.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return string
		 *
		 * @since 2.3
		 */
		public static function update_user_state( $user = null ) {
			self::set_proper_user( $user );

			$enforcement_state = 'optional';
			if ( User::run_user_exclusion_check( self::get_user() ) ) {
				$enforcement_state = 'excluded';
			} elseif ( User::run_user_enforcement_check( self::get_user() ) ) {
				$enforcement_state = 'enforced';
			}

			self::set_user_state( $enforcement_state );

			// Clear enabled methods if excluded.
			if ( 'excluded' === $enforcement_state ) {
				self::remove_enabled_method_for_user();
			}

			return $enforcement_state;
		}

		/**
		 * Checks if user is enforced
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return mixed
		 *
		 * @since 2.2.0
		 */
		public static function is_enforced( $user = null ) {
			$state = self::get_user_state( $user );

			return 'enforced' === $state;
		}

		/**
		 * Returns the current user state stored
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return string
		 *
		 * @since 2.2.0
		 */
		public static function get_user_state( $user = null ) {
			self::set_proper_user( $user );

			$state = self::get_meta( self::USER_ENFORCEMENT_STATE );

			if ( empty( $state ) ) {
				$state = self::update_user_state();
			}

			return $state;
		}

		/**
		 * Returns the current user state stored
		 *
		 * @param string            $state - The 2FA user state.
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		public static function set_user_state( $state, $user = null ) {
			self::set_proper_user( $user );

			if ( ! in_array( $state, self::USER_STATE_STATUSES, true ) ) {
				$state = self::USER_STATE_STATUSES[0];
			}

			self::set_meta( self::USER_ENFORCEMENT_STATE, $state );
		}

		/**
		 * Removes 2FA meta for the given user
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return void
		 *
		 * @since 2.2.2
		 */
		public static function remove_2fa_for_user( $user = null ) {
			self::set_proper_user( $user );

			self::remove_all_2fa_meta_for_user( $user );
		}

		/**
		 * Figures out the correct 2FA status of a user and stores it against the user in DB. The method is static
		 * because it is temporarily used in user listing to update user accounts created prior to version 1.7.0.
		 *
		 * @param \WP_User $user - The user which status should be set.
		 *
		 * @return string
		 * @see \WP2FA\Admin\User_Listing
		 * @since 1.7.0
		 */
		public static function set_user_status( \WP_User $user ) {
			$status      = User_Utils::determine_user_2fa_status( $user );
			$status_data = User_Utils::extract_statuses( $status );
			if ( ! empty( $status_data ) ) {
				self::set_2fa_status( $status_data['id'], $user );

				return $status_data['label'];
			}

			return '';
		}

		/**
		 * Sets the local variable class based on the given parameter.
		 *
		 * @param null|int|\WP_User $user - The WP user we should extract the meta data for.
		 *
		 * @return void
		 *
		 * @since 2.2.0
		 */
		private static function set_proper_user( $user = null ) {
			if ( null !== $user ) {
				self::set_user( $user );
			} else {
				self::get_user();
			}
		}
	}
}
