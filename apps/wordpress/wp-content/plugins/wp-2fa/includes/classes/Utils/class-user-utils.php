<?php
/**
 * Responsible for different user's manipulations.
 *
 * @package    wp2fa
 * @subpackage user-utils
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Utils;

use \WP2FA\Authenticator\Backup_Codes as Backup_Codes;
use WP2FA\WP2FA as WP2FA;
use WP2FA\Admin\Helpers\User_Helper;

/**
 * Utility class for creating modal popup markup.
 *
 * @package WP2FA\Utils
 * @since 1.4.2
 */
class User_Utils {

	/**
	 * Holds map with human readable 2FA statuses
	 *
	 * @var array
	 */
	private static $statuses;

	/**
	 * Determines the proper 2FA status of the given user
	 *
	 * @param [type] $user - The user to check.
	 *
	 * @return array
	 *
	 * @since 2.2.0
	 */
	public static function determine_user_2fa_status( $user ) {

		// Get current user, we going to need this regardless.
		$current_user = wp_get_current_user();

		// Bail if we still dont have an object.
		if ( ! is_a( $user, '\WP_User' ) || ! is_a( $current_user, '\WP_User' ) ) {
			return array();
		}

		$roles = (array) $user->roles;

		// Grab grace period UNIX time.
		$grace_period_expired = User_Helper::get_grace_period( $user );
		$is_user_excluded     = User_Helper::is_excluded( $user->ID );
		$is_user_enforced     = User_Helper::is_enforced( $user->ID );
		$is_user_locked       = User_Helper::is_user_locked( $user->ID );
		$user_last_login      = get_user_meta( $user->ID, WP_2FA_PREFIX . 'login_date', true );

		// First lets see if the user already has a token.
		$enabled_methods = User_Helper::get_enabled_method_for_user( $user );

		$no_enforced_methods = false;
		if ( 'do-not-enforce' === WP2FA::get_wp2fa_setting( 'enforcement-policy' ) ) {
			$no_enforced_methods = true;
		}

		$user_type = array();

		if ( empty( $roles ) ) {
			$user_type[] = 'orphan_user'; // User has no role.
		}

		if ( current_user_can( 'manage_options' ) ) {
			$user_type[] = 'can_manage_options';
		}

		if ( current_user_can( 'read' ) ) {
			$user_type[] = 'can_read';
		}

		if ( $grace_period_expired ) {
			$user_type[] = 'grace_has_expired';
		}

		if ( $current_user->ID === $user->ID ) {
			$user_type[] = 'viewing_own_profile';
		}

		if ( ! empty( $enabled_methods ) ) {
			$user_type[] = 'has_enabled_methods';
		}

		if ( $no_enforced_methods && ! empty( $enabled_methods ) ) {
			$user_type[] = 'no_required_has_enabled';
		}

		if ( $no_enforced_methods && empty( $enabled_methods ) && ! $is_user_excluded ) {
			if ( empty( $user_last_login ) ) {
				$user_type[] = User_Helper::USER_UNDETERMINED_STATUS;
			} else {
				$user_type[] = 'no_required_not_enabled';
			}
		}

		if ( ! $no_enforced_methods && empty( $enabled_methods ) && ! $is_user_excluded && $is_user_enforced ) {
			$user_type[] = 'user_needs_to_setup_2fa';
		}

		if ( ! $no_enforced_methods && empty( $enabled_methods ) && ! $is_user_excluded && ! $is_user_enforced ) {
			if ( empty( $user_last_login ) ) {
				$user_type[] = User_Helper::USER_UNDETERMINED_STATUS;
			} else {
				$user_type[] = 'no_required_not_enabled';
			}
		}

		if ( $is_user_excluded ) {
			$user_type[] = 'user_is_excluded';
		}

		if ( $is_user_locked ) {
			$user_type[] = 'user_is_locked';
		}

		$codes_remaining = Backup_Codes::codes_remaining_for_user( $user );
		if ( 0 === $codes_remaining ) {
			$user_type[] = 'user_needs_to_setup_backup_codes';
		}

		/**
		 * Gives the ability to alter the user types for the user.
		 *
		 * @param string $user_type - Type of the user.
		 * @param \WP_User $user - The WP user.
		 *
		 * @since 2.0.0
		 */
		return apply_filters( WP_2FA_PREFIX . 'additional_user_types', $user_type, $user );
	}

	/**
	 * Checks is all values exist in given array
	 *
	 * @param array $needles - Which values to check.
	 * @param array $haystack - The array to check against.
	 *
	 * @return bool
	 *
	 * @since 2.2.0
	 */
	public static function in_array_all( $needles, $haystack ) {
		return empty( array_diff( $needles, $haystack ) );
	}

	/**
	 * Check if role is not in given array of roles
	 *
	 * @param array $roles - All roles.
	 * @param array $user_roles - The User roles.
	 *
	 * @return bool
	 */
	public static function role_is_not( $roles, $user_roles ) {
		if (
			empty(
				array_intersect(
					$roles,
					$user_roles
				)
			)
		) {
			return true;
		}

		return false;
	}

	/**
	 * Return all users, either by using a direct query or get_users.
	 *
	 * @param string $method Method to use.
	 * @param array  $users_args Query arguments.
	 *
	 * @return mixed              Array of IDs/Object of Users.
	 */
	public static function get_all_users_data( $method, $users_args ) {

		if ( 'get_users' === $method ) {
			return get_users( $users_args );
		}

		// method is "query", let's build the SQL query ourselves.
		global $wpdb;

		$batch_size = isset( $users_args['batch_size'] ) ? $users_args['batch_size'] : false;
		$offset     = isset( $users_args['count'] ) ? $users_args['count'] * $batch_size : false;

		// Default.
		$select = 'SELECT ID, user_login FROM ' . $wpdb->users . '';

		// If we want to grab users with a specific role.
		if ( isset( $users_args['role__in'] ) && ! empty( $users_args['role__in'] ) ) {
			$roles  = $users_args['role__in'];
			$select = '
					SELECT  ID, user_login
					FROM    ' . $wpdb->users . ' u INNER JOIN ' . $wpdb->usermeta . ' um
					ON      u.ID = um.user_id
					WHERE   um.meta_key LIKE \'' . $wpdb->base_prefix . '%capabilities' . '\'' . // phpcs:ignore
					' AND     (
			';
			$i      = 1;
			foreach ( $roles as $role ) {
				$select .= ' um.meta_value    LIKE    \'%"' . $role . '"%\' ';
				if ( $i < count( $roles ) ) {
					$select .= ' OR ';
				}
				$i ++;
			}
			$select .= ' ) ';

			$excluded_users = ( ! empty( $users_args['excluded_users'] ) ) ? $users_args['excluded_users'] : array();

			$excluded_users = array_map(
				function ( $excluded_user ) {
					return '"' . $excluded_user . '"';
				},
				$excluded_users
			);

			if ( ! empty( $excluded_users ) ) {
				$select .= '
						AND user_login NOT IN ( ' . implode( ',', $excluded_users ) . ' )
				';
			}

			$skip_existing_2fa_users = ( ! empty( $users_args['skip_existing_2fa_users'] ) ) ? $users_args['skip_existing_2fa_users'] : false;

			if ( $skip_existing_2fa_users ) {
				$select .= '
				AND u.ID NOT IN (
				  SELECT DISTINCT user_id FROM  ' . $wpdb->usermeta . ' WHERE meta_key = \'wp_2fa_enabled_methods\'
				)
				';
			}
		}

		if ( $batch_size ) {
			$select .= ' LIMIT ' . $batch_size . ' OFFSET ' . $offset . '';
		}

		return $wpdb->get_results( $select ); // phpcs:ignore
	}

	/**
	 * Collects all the users with 2FA meta data
	 *
	 * @param array $users_args - Arguments.
	 *
	 * @return string
	 */
	public static function get_all_user_ids_who_have_wp_2fa_metadata_present( $users_args ) {

		global $wpdb;

		$batch_size = isset( $users_args['batch_size'] ) ? $users_args['batch_size'] : false;
		$offset     = isset( $users_args['count'] ) ? $users_args['count'] * $batch_size : false;

		$select = '
			SELECT ID FROM ' . $wpdb->users . '
			INNER JOIN ' . $wpdb->usermeta . ' ON ' . $wpdb->users . '.ID = ' . $wpdb->usermeta . '.user_id
			WHERE ' . $wpdb->usermeta . '.meta_key LIKE \'wp_2fa_%\'
		';

		if ( $batch_size ) {
			$select .= '
				LIMIT ' . $batch_size . ' OFFSET ' . $offset . '
			';
		}

		$users = $wpdb->get_results( $select ); // phpcs:ignore

		$users = array_map(
			function ( $user ) {
				return (int) $user->ID;
			},
			$users
		);

		$users = implode( ',', $users );

		return $users;
	}

	/**
	 * Retrieve string of comma separated IDs.
	 *
	 * @param string $method Method to use.
	 * @param array  $users_args Query arguments.
	 *
	 * @return string             List of IDs.
	 */
	public static function get_all_user_ids( $method, $users_args ) {
		$user_data = self::get_all_users_data( $method, $users_args );

		$users = array_map(
			function ( $user ) {
				return (int) $user->ID;
			},
			$user_data
		);

		return implode( ',', $users );
	}

	/**
	 * Retrieve array if user IDs and login names.
	 *
	 * @param string $method Method to use.
	 * @param array  $users_args Query arguments.
	 *
	 * @return array              User details.
	 */
	public static function get_all_user_ids_and_login_names( $method, $users_args ) {
		$user_data = self::get_all_users_data( $method, $users_args );
		$user_item = array();

		$users = array_map(
			function ( $user ) {
				$user_item['ID']         = (int) $user->ID;
				$user_item['user_login'] = $user->user_login;

				return $user_item;
			},
			$user_data
		);

		return $users;
	}

	/**
	 * Returns the array with human readable statuses of the WP 2FA
	 *
	 * @since 1.6
	 *
	 * @return array
	 */
	public static function get_human_readable_user_statuses() {
		if ( null === self::$statuses ) {
			self::$statuses =
			array(
				'has_enabled_methods'                 => __( 'Configured', 'wp-2fa' ),
				'user_needs_to_setup_2fa'             => __( 'Required but not configured', 'wp-2fa' ),
				'no_required_has_enabled'             => __( 'Configured (but not required)', 'wp-2fa' ),
				'no_required_not_enabled'             => __( 'Not required & not configured', 'wp-2fa' ),
				'user_is_excluded'                    => __( 'Not allowed', 'wp-2fa' ),
				'user_is_locked'                      => __( 'Locked', 'wp-2fa' ),
				User_Helper::USER_UNDETERMINED_STATUS => __( 'User has not logged in yet, 2FA status is unknown', 'wp-2fa' ),
			);
		}

		return self::$statuses;
	}

	/**
	 * Gets the user types extracted with @see User_Utils::determine_user_2fa_status,
	 * checks values and generates human readable 2FA status text
	 *
	 * @param array $user_types - The types of the user.
	 *
	 * @return array An array with the id and label elements of user 2FA status. Empty in case there is not match.
	 *
	 * @since 1.7.0 Changed the function to return the id and label of the first match it finds instead of concatenated labels of all matched statuses.
	 */
	public static function extract_statuses( $user_types ) {
		if ( null === self::$statuses ) {
			self::get_human_readable_user_statuses();
		}

		foreach ( self::$statuses as $key => $value ) {
			if ( in_array( $key, $user_types, true ) ) {
				return array(
					'id'    => $key,
					'label' => $value,
				);
			}
		}

		return array();
	}
}
