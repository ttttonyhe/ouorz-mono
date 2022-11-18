<?php
/**
 * Responsible for WP2FA user's manipulation.
 *
 * @package    wp2fa
 * @subpackage user-utils
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Admin;

use WP2FA\WP2FA;
use WP_Session_Tokens;
use WP2FA\Cron\Cron_Tasks;
use WP2FA\Admin\Settings_Page;
use WP2FA\Authenticator\Open_SSL;
use WP2FA\Admin\Helpers\WP_Helper;
use WP2FA\Admin\Controllers\Methods;
use WP2FA\Admin\Helpers\User_Helper;
use WP2FA\Admin\Controllers\Settings;
use WP2FA\Authenticator\Authentication;
use WP2FA\Utils\Settings_Utils as Settings_Utils;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * User class which holds all the user related data
 */
if ( ! class_exists( '\WP2FA\Admin\User' ) ) {

	/**
	 * WP2FA User controller
	 */
	class User {

		/**
		 * Holds the current user
		 *
		 * @var \WP_User
		 */
		private $user = null;

		/**
		 * Totp key assigned to user
		 *
		 * @var string
		 */
		private $totp_key = '';

		/**
		 * Local cache of created user instances. Associative array where the keys are user IDs.
		 *
		 * @var User[]
		 * @since 2.0.0
		 */
		private static $user_instances = array();

		/**
		 * This function is supposed to be used to get instance of User object in the plugin. This way we make sure we
		 * don't create the User object for the same user multiple times from different places.
		 *
		 * @param mixed $user You can use \WP_User, integer (representing ID of the user), or any value that returns true checked against empty in PHP.
		 *
		 * @return User
		 * @since 2.0.0
		 */
		public static function get_instance( $user = '' ) {
			$user = self::determine_user( $user );
			if ( ! array_key_exists( $user->ID, self::$user_instances ) ) {
				self::$user_instances[ $user->ID ] = new User( $user );
			}

			return self::$user_instances[ $user->ID ];
		}

		/**
		 * Default constructor
		 *
		 * @param \WP_User $user - The WP user.
		 */
		private function __construct( $user ) {
			$this->user = $user;
			$this->update_meta_if_necessary();
		}

		/**
		 * Updates necessary user metadata if necessary. The updated is necessary only if the settings hash stored
		 * against the user doesn't match the hash for the current copy of plugin settings.
		 */
		public function update_meta_if_necessary() {
			$global_settings_hash = Settings_Utils::get_option( WP_2FA_PREFIX . 'settings_hash' );
			if ( ! empty( $global_settings_hash ) ) {
				$stored_hash = User_Helper::get_global_settings_hash_for_user( $this->user );
				if ( $global_settings_hash !== $stored_hash ) {
					User_Helper::set_global_settings_hash_for_user( $global_settings_hash, $this->user );
					// update necessary user attributes (user meta) based on changed settings; the enforcement check
					// needs to run first as function "set_user_policies_and_grace" relies on having the correct values.
					$this->check_methods_and_set_user();
					User_Helper::update_user_state( $this->user );
					$this->set_user_policies_and_grace();
				}
			}
		}

		/**
		 * Runs the necessary checks to figure out if the user is enforced based on current plugin settings.
		 *
		 * @param \WP_User $user User to evaluate.
		 *
		 * @return bool True if the user is enforced based on current plugin settings.
		 * @since 2.0.0
		 */
		public static function run_user_enforcement_check( $user ) {
			$user_roles     = $user->roles;
			$current_policy = WP2FA::get_wp2fa_setting( 'enforcement-policy' );
			$enabled_method = User_Helper::get_enabled_method_for_user( $user );
			$user_eligible  = false;

			// Let's check the policy settings and if the user has setup totp/email by checking for the usermeta.
			if ( empty( $enabled_method ) && WP_Helper::is_multisite() && 'superadmins-only' === $current_policy ) {
				return is_super_admin( $user->ID );
			} elseif ( empty( $enabled_method ) && WP_Helper::is_multisite() && 'superadmins-siteadmins-only' === $current_policy ) {
				return User_Helper::is_admin();
			} elseif ( 'all-users' === $current_policy && empty( $enabled_method ) ) {

				if ( Settings_Utils::string_to_bool( WP2FA::get_wp2fa_setting( 'superadmins-role-exclude' ) ) && is_super_admin( $user->ID ) ) {
					return false;
				}

				$excluded_users = WP2FA::get_wp2fa_setting( 'excluded_users' );
				if ( ! empty( $excluded_users ) ) {
					// Compare our roles with the users and see if we get a match.
					$result = in_array( $user->user_login, $excluded_users, true );
					if ( $result ) {
						return false;
					}

					$user_eligible = true;
				}

				$excluded_roles = WP2FA::get_wp2fa_setting( 'excluded_roles' );
				if ( ! empty( $excluded_roles ) ) {

					if ( ! WP_Helper::is_multisite() ) {
						// Compare our roles with the users and see if we get a match.
						$result = array_intersect( $excluded_roles, $user->roles );

						if ( ! empty( $result ) ) {
							return false;
						}
					} else {
						$users_caps = array();
						$subsites   = get_sites();
						// Check each site and add to our array so we know each users actual roles.
						foreach ( $subsites as $subsite ) {
							$subsite_id   = get_object_vars( $subsite )['blog_id'];
							$users_caps[] = get_user_meta( $user->ID, 'wp_' . $subsite_id . '_capabilities', true );
						}

						foreach ( $users_caps as $key => $value ) {
							if ( ! empty( $value ) ) {
								foreach ( $value as $key => $value ) {
									$result = in_array( $key, $excluded_roles, true );
								}
							}
						}
						if ( ! empty( $result ) ) {
							return false;
						}
					}
				}

				if ( true === $user_eligible || empty( $enabled_method ) ) {
					return true;
				}
			} elseif ( 'certain-roles-only' === $current_policy && empty( $enabled_method ) ) {
				$enforced_users = WP2FA::get_wp2fa_setting( 'enforced_users' );
				if ( ! empty( $enforced_users ) ) {
					// Turn it into an array.
					$enforced_users_array = $enforced_users;
					// Compare our roles with the users and see if we get a match.
					$result = in_array( $user->user_login, $enforced_users_array, true );
					// The user is one of the chosen roles we are forcing 2FA onto, so lets show the nag.
					if ( ! empty( $result ) ) {
						return true;
					}
				}

				$enforced_roles = WP2FA::get_wp2fa_setting( 'enforced_roles' );
				if ( ! empty( $enforced_roles ) ) {
					// Turn it into an array.
					$enforced_roles_array = Settings_Page::extract_roles_from_input( $enforced_roles );

					if ( ! WP_Helper::is_multisite() ) {
						// Compare our roles with the users and see if we get a match.
						$result = array_intersect( $enforced_roles_array, $user_roles );

						// The user is one of the chosen roles we are forcing 2FA onto, so lets show the nag.
						if ( ! empty( $result ) ) {
							return true;
						}
					} else {
						$users_caps = array();
						$subsites   = get_sites();
						// Check each site and add to our array so we know each users actual roles.
						foreach ( $subsites as $subsite ) {
							$subsite_id   = get_object_vars( $subsite )['blog_id'];
							$users_caps[] = get_user_meta( $user->ID, 'wp_' . $subsite_id . '_capabilities', true );
						}

						foreach ( $users_caps as $key => $value ) {
							if ( ! empty( $value ) ) {
								foreach ( $value as $key => $value ) {
									$result = in_array( $key, $enforced_roles_array, true );
								}
							}
						}
						if ( ! empty( $result ) ) {
							return true;
						}
					}
				}

				if ( Settings_Utils::string_to_bool( WP2FA::get_wp2fa_setting( 'superadmins-role-add' ) ) ) {
					return is_super_admin( $user->ID );
				}
			} elseif ( 'certain-users-only' === $current_policy && empty( $enabled_method ) ) {
				$enforced_users = WP2FA::get_wp2fa_setting( 'enforced_users' );
				if ( ! empty( $enforced_users ) ) {
					// Compare our roles with the users and see if we get a match.
					$result = in_array( $user->user_login, $enforced_users, true );
					// The user is one of the chosen roles we are forcing 2FA onto, so lets show the nag.
					if ( ! empty( $result ) ) {
						return true;
					}
				}
			} elseif ( 'enforce-on-multisite' === $current_policy ) {
				$included_sites = WP2FA::get_wp2fa_setting( 'included_sites' );

				foreach ( $included_sites as $site_id ) {
					if ( is_user_member_of_blog( $user->ID, $site_id ) ) {
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Runs the necessary checks to figure out if the user is excluded based on current plugin settings.
		 *
		 * @param \WP_User $user User to evaluate.
		 *
		 * @return bool True if the user is excluded based on current plugin settings.
		 * @since 2.0.0
		 */
		public static function run_user_exclusion_check( $user ) {
			$user_roles     = $user->roles;
			$user_excluded  = false;
			$excluded_users = WP2FA::get_wp2fa_setting( 'excluded_users' );
			if ( is_array( $excluded_users ) || strlen( $excluded_users ) > 0 ) {
				// Turn it into an array.
				$excluded_users_array = is_string( $excluded_users ) ? explode( ',', $excluded_users ) : $excluded_users;

				// Compare our roles with the users and see if we get a match.
				$result = in_array( $user->user_login, $excluded_users_array, true );
				if ( $result ) {
					return true;
				}
			}

			$excluded_roles = WP2FA::get_wp2fa_setting( 'excluded_roles' );
			if ( ! empty( $excluded_roles ) ) {
				// Turn it into an array.
				$excluded_roles_array = is_string( $excluded_roles ) ? explode( ',', $excluded_roles ) : $excluded_roles;
				$excluded_roles_array = array_map( 'strtolower', $excluded_roles_array );
				// Compare our roles with the users and see if we get a match.
				$result = array_intersect( $excluded_roles_array, $user_roles );
				if ( ! empty( $result ) ) {
					return true;
				}
			}

			if ( WP_Helper::is_multisite() ) {
				$excluded_sites = WP2FA::get_wp2fa_setting( 'excluded_sites' );
				if ( ! empty( $excluded_sites ) && is_array( $excluded_sites ) ) {

					foreach ( $excluded_sites as $site_id ) {
						if ( is_user_member_of_blog( $user->ID, $site_id ) ) {
							// User is a member of the blog we are excluding from 2FA.
							return true;
						} else {
							// User is NOT a member of the blog we are excluding.
							$user_excluded = false;
						}
					}
				}

				$included_sites = WP2FA::get_wp2fa_setting( 'included_sites' );
				if ( $included_sites && is_array( $included_sites ) ) {
					foreach ( $included_sites as $site_id ) {
						if ( is_user_member_of_blog( $user->ID, $site_id ) ) {
							$user_excluded = false;
						}
					}
				}
			}

			return $user_excluded;
		}

		/**
		 * Locks the user account if the grace period setting is configured and the user is currently out of their grace
		 * period. It also takes care of sending the "account locked" email to the user if not already sent before.
		 *
		 * @return bool True if the user account is locked. False otherwise.
		 */
		public function lock_user_account_if_needed() {
			if ( ! $this->is_user_set() ) {
				return false;
			}

			$user_id  = $this->user->ID;
			$settings = Settings_Utils::get_option( WP_2FA_POLICY_SETTINGS_NAME );
			if ( ! is_array( $settings ) || ( isset( $settings['enforcement-policy'] ) && 'do-not-enforce' === $settings['enforcement-policy'] ) ) {
				// 2FA is not enforced, make sure to clear any related user meta previously created
				$this->delete_user_meta( WP_2FA_PREFIX . 'is_locked' );
				User_Helper::remove_user_expiry_date( $this->user );
				$this->delete_user_meta( WP_2FA_PREFIX . 'locked_account_notification' );

				return false;
			}

			$is_user_instantly_enforced = User_Helper::get_user_enforced_instantly( $user_id );
			if ( $is_user_instantly_enforced ) {
				// no need to lock the account if the user is enforced to set 2FA up instantly.
				return false;
			}

			if ( User_Helper::is_excluded( $user_id ) ) {
				return false;
			}

			// Do not lock if user has 2FA configured.
			$has_enabled_method = User_Helper::get_2fa_status( $user_id );
			if ( 'has_enabled_methods' === $has_enabled_method ) {
				return false;
			}

			$grace_period_expiry_time = User_Helper::get_user_expiry_date( $this->user );
			$grace_period_expired     = ( ! empty( $grace_period_expiry_time ) && $grace_period_expiry_time < time() );
			if ( $grace_period_expired ) {

				/**
				 * Filter can be used to prevent locking of the user account when the grace period expires.
				 *
				 * @param boolean $should_be_locked Should account be locked? True by default.
				 * @param User $user WP2FA User object.
				 *
				 * @return boolean True if the user account should be locked.
				 * @since 2.0.0
				 */
				$should_be_locked = apply_filters( WP_2FA_PREFIX . 'should_account_be_locked_on_grace_period_expiration', true, $this );
				if ( ! $should_be_locked ) {
					return false;
				}

				// set "grace period expired" flag.
				User_Helper::set_grace_period( true, $this->user );

				/**
				 * Allow 3rd party developers to execute additional code when grace period expires (account is locked)
				 *
				 * @param User $user WP2FA User object.
				 *
				 * @since 2.0.0
				 */
				do_action( WP_2FA_PREFIX . 'after_grace_period_expired', $this );

				/**
				 * Filter can be used to disable the email notification about locked user account.
				 *
				 * @param boolean $can_send Can the email notification be sent? True by default.
				 * @param User $user WP2FA User object.
				 *
				 * @return boolean True if the email notification can be sent.
				 * @since 2.0.0
				 */
				$notify_user = apply_filters( WP_2FA_PREFIX . 'send_account_locked_notification', true, $this );
				if ( $notify_user ) {
					// Send the email to alert the user, only if we have not done so before.
					$account_notification = get_user_meta( $user_id, WP_2FA_PREFIX . 'locked_account_notification', true );
					if ( ! $account_notification ) {
						Cron_Tasks::send_expired_grace_email( $user_id );
						$this->set_user_meta( WP_2FA_PREFIX . 'locked_account_notification', true );
					}
				}

				// Grab user session and kill it, preferably with fire.
				$manager = WP_Session_Tokens::get_instance( $user_id );
				$manager->destroy_all();

				return true;
			}

			return false;
		}

		/**
		 * Returns user object
		 *
		 * @return \WP_User|null
		 */
		public function get_2fa_wp_user() {
			return $this->user;
		}

		/**
		 * Turns dynamic $user parameter to WordPress user object.
		 *
		 * @param string|\WP_User $user This can be \WP_User, integer (representing ID of the user), or any value that returns true checked against empty in PHP.
		 *
		 * @return \WP_User
		 */
		private static function determine_user( $user = '' ) {
			// regular WordPress user object.
			if ( is_a( $user, 'WP_User' ) ) {
				return $user;
			}

			// user ID as number.
			if ( is_int( $user ) ) {
				return new \WP_User( $user );
			}

			// default to current user.
			return \wp_get_current_user();
		}

		/**
		 * Deletes user meta by given key
		 *
		 * @param string $meta_name - The meta key which should be deleted.
		 *
		 * @return void
		 */
		public function delete_user_meta( string $meta_name ) {
			if ( $this->is_user_set() ) {
				\delete_user_meta( $this->user->ID, $meta_name );
			}
		}

		/**
		 * Retrieves user meta by specific key
		 *
		 * @param string $meta - The meta key to use for extracting.
		 *
		 * @return mixed
		 */
		public function get_user_meta( string $meta ) {
			if ( $this->is_user_set() ) {
				return $this->user->get( $meta );
			}
		}

		/**
		 * User totp key getter
		 *
		 * @return string
		 */
		public function get_totp_key(): string {
			if ( '' === trim( $this->totp_key ) ) {
				$this->totp_key = Authentication::get_user_totp_key( $this->user->ID );
				if ( empty( $this->totp_key ) ) {
					$this->totp_key = Authentication::generate_key();

					User_Helper::set_user_totp_key( $this->totp_key, $this->user );
				} else {
					if ( Open_SSL::is_ssl_available() && false === \strpos( $this->totp_key, 'wps_' ) ) {
						$this->totp_key = 'wps_' . Open_SSL::encrypt( $this->totp_key );
						User_Helper::set_user_totp_key( $this->totp_key, $this->user );
					}
				}
			}

			return $this->totp_key;
		}

		/**
		 * Returns the encoded TOTP when we need to show the actual code to the user
		 * If for some reason the code is invalid it recreates it
		 *
		 * @return string
		 *
		 * @since 2.0.0
		 */
		public function get_totp_decrypted(): string {
			$key = $this->get_totp_key();
			if ( Open_SSL::is_ssl_available() && false !== \strpos( $key, 'ssl_' ) ) {

				/**
				 * Old key detected - convert.
				 */
				$key = Open_SSL::decrypt_legacy( substr( $key, 4 ) );

				User_Helper::remove_user_totp_key();
				$this->totp_key = '';

				$key = $this->get_totp_key();
			}

			if ( Open_SSL::is_ssl_available() && false !== \strpos( $key, 'wps_' ) ) {
				$key = Open_SSL::decrypt( substr( $key, 4 ) );

				/**
				 * If for some reason the key is not valid, that means that we have to clear the stored TOTP for the user, and create new on
				 * That could happen if the global stored secret (plugin level) is deleted.
				 *
				 * Lets check and if that is the case - create new one
				 */
				if ( ! Authentication::validate_base32_string( $key ) ) {
					$this->totp_key = '';
					User_Helper::remove_user_totp_key( $this->user );
					$key = $this->get_totp_key();
					$key = Open_SSL::decrypt( substr( $key, 4 ) );
				}
			}

			return $key;
		}

		/**
		 * Check if user has enabled the proper method based on globally enabled methods
		 * sets the flag that forces the user to reconfigure their 2FA method
		 *
		 * @return void
		 */
		public function check_methods_and_set_user() {
			if ( $this->is_user_set() && ! User_Helper::get_user_needs_to_reconfigure_2fa( $this->user ) ) {
				$enabled_methods_for_the_user = User_Helper::get_enabled_method_for_user( $this->user );

				if ( empty( $enabled_methods_for_the_user ) ) {
					return;
				}

				$global_methods = Methods::get_available_2fa_methods();
				if ( empty( \array_intersect( array( $enabled_methods_for_the_user ), $global_methods ) ) ) {
					User_Helper::remove_enabled_method_for_user( $this->user );
					if ( User_Helper::is_enforced( $this->user->ID ) ) {
						User_Helper::set_user_needs_to_reconfigure_2fa( true, $this->user );
					}
				}
			}
		}

		/**
		 * Checks if user needs to reconfigure the method
		 *
		 * @return boolean
		 */
		public function needs_to_reconfigure_method(): bool {
			if ( ! $this->is_user_set() ) {
				return false;
			}

			return ( ! empty( User_Helper::get_user_needs_to_reconfigure_2fa( $this->user ) ) && ! User_Helper::get_nag_status() && empty( User_Helper::get_enabled_method_for_user( $this->user ) ) );
		}

		/**
		 * Checks if the user variable is set
		 *
		 * @return boolean
		 */
		private function is_user_set() {
			if ( is_a( $this->user, '\WP_User' ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Sets proper grace period and policies for the user based on currently stored settings
		 * That method MUST be called only after User::check_methods_and_set_user();
		 *
		 * @return void
		 */
		private function set_user_policies_and_grace() {

			if ( ! isset( $this->user->ID ) || 0 === $this->user->ID ) {
				return;
			}

			$enabled_methods_for_the_user = User_Helper::get_enabled_method_for_user( $this->user );
			if ( ! empty( $enabled_methods_for_the_user ) ) {
				User_Helper::remove_user_enforced_instantly( $this->user );
				User_Helper::remove_user_expiry_date( $this->user );
				User_Helper::remove_user_needs_to_reconfigure_2fa( $this->user );
				User_Helper::set_user_status( $this->user );

				return;
			}

			if ( User_Helper::is_enforced( $this->user->ID ) ) {
				$grace_policy = Settings::get_role_or_default_setting( 'grace-policy', $this->user );

				// Check if want to apply the custom period, or instant expiry.
				if ( 'use-grace-period' === $grace_policy ) {
					$custom_grace_period_duration =
					Settings::get_role_or_default_setting( 'grace-period', $this->user ) . ' ' . Settings::get_role_or_default_setting( 'grace-period-denominator', $this->user );
					$grace_expiry                 = strtotime( $custom_grace_period_duration );
					User_Helper::remove_user_enforced_instantly( $this->user );
				} else {
					$grace_expiry = time();
				}

				User_Helper::set_user_expiry_date( $grace_expiry, $this->user );
				if ( 'no-grace-period' === $grace_policy ) {
					User_Helper::set_user_enforced_instantly( true, $this->user );
				}
			} else {
				User_Helper::remove_user_enforced_instantly( $this->user );
				User_Helper::remove_user_expiry_date( $this->user );
				User_Helper::remove_user_needs_to_reconfigure_2fa( $this->user );
			}

			// update the 2FA status meta field.
			User_Helper::set_user_status( $this->user );
		}

		/**
		 * Updates user meta with given value
		 *
		 * @param string $meta_key - The key to update.
		 * @param string $value - The value to set.
		 *
		 * @return mixed
		 */
		public function set_user_meta( $meta_key, $value ) {
			return \update_user_meta( $this->user->ID, $meta_key, $value );
		}
	}
}
