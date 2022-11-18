<?php
/**
 * Responsible for WP2FA user's backup codes manipulation.
 *
 * @package    wp2fa
 * @subpackage backup-codes
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

/**
 * Class for handling backup codes
 *
 * @since 0.1-dev
 *
 * @package WP2FA
 */

namespace WP2FA\Authenticator;

use \WP2FA\Authenticator\Authentication as Authentication;
use WP2FA\Admin\Settings_Page;
use WP2FA\Admin\Helpers\User_Helper;
use WP2FA\Admin\Controllers\Login_Attempts;

/**
 * Backup code class, for handling backup code generation and such.
 */
class Backup_Codes {

	/**
	 * Holds the name of the meta key for the allowed login attempts
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	private static $login_num_meta_key = WP_2FA_PREFIX . 'backup-login-attempts';

	/**
	 * Key used for backup codes
	 *
	 * @var string
	 */
	const BACKUP_CODES_META_KEY = 'wp_2fa_backup_codes';

	/**
	 * The number backup codes.
	 *
	 * @type int
	 */
	const NUMBER_OF_CODES = 10;

	/**
	 * The name of the method
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	public static $method_name = 'backup_codes';

	/**
	 * The login attempts class
	 *
	 * @var \WP2FA\Admin\Controllers\Login_Attempts
	 *
	 * @since 2.0.0
	 */
	private static $login_attempts = null;

	/**
	 * Lets build!
	 */
	public static function init() {
		\add_filter( WP_2FA_PREFIX . 'backup_methods_list', array( __CLASS__, 'add_backup_method' ), 10, 2 );
		\add_filter( WP_2FA_PREFIX . 'backup_methods_enabled', array( __CLASS__, 'check_backup_method' ), 10, 2 );
		\add_action( 'wp_ajax_wp2fa_run_ajax_generate_json', array( __CLASS__, 'run_ajax_generate_json' ) );
	}

	/**
	 * Generate backup codes
	 *
	 * @param  object $user User data.
	 * @param  string $args possible args.
	 */
	public static function generate_codes( $user, $args = '' ) {
		$codes        = array();
		$codes_hashed = array();

		// Check for arguments.
		if ( isset( $args['number'] ) ) {
			$num_codes = (int) $args['number'];
		} else {
			$num_codes = self::NUMBER_OF_CODES;
		}

		// Append or replace (default).
		if ( isset( $args['method'] ) && 'append' === $args['method'] ) {
			$codes_hashed = (array) get_user_meta( $user->ID, self::BACKUP_CODES_META_KEY, true );
		}

		for ( $i = 0; $i < $num_codes; $i++ ) {
			$code           = Authentication::get_code();
			$codes_hashed[] = wp_hash_password( $code );
			$codes[]        = $code;
			unset( $code );
		}

		update_user_meta( $user->ID, self::BACKUP_CODES_META_KEY, $codes_hashed );

		// Unhashed.
		return $codes;
	}

	/**
	 * Returns instance of the LoginAttempts class
	 *
	 * @return \WP2FA\Admin\Controllers\Login_Attempts
	 *
	 * @since 2.0.0
	 */
	public static function get_login_attempts_instance() {
		if ( null === self::$login_attempts ) {

			self::$login_attempts = new Login_Attempts( self::$login_num_meta_key );

		}
		return self::$login_attempts;
	}

	/**
	 * Checks the number of login attempts
	 *
	 * @param \WP_User $user - The user we have to check for.
	 *
	 * @return boolean
	 *
	 * @since 2.0.0
	 */
	public static function check_number_of_attempts( \WP_User $user ):bool {
		return self::get_login_attempts_instance()->check_number_of_attempts( $user );
	}

	/**
	 * Generate codes and check remaining amount for user.
	 */
	public static function run_ajax_generate_json() {
		$user = wp_get_current_user();

		check_ajax_referer( 'wp-2fa-backup-codes-generate-json-' . $user->ID, 'nonce' );

		// Setup the return data.
		$codes = self::generate_codes( $user );

		$count = self::codes_remaining_for_user( $user );
		$i18n  = array(
			'count' => esc_html(
				sprintf(
					/* translators: %s: count */
					_n( '%s unused code remaining.', '%s unused codes remaining.', $count, 'wp-2fa' ),
					$count
				)
			),
			/* translators: %s: the site's domain */
			'title' => esc_html__( 'Two-Factor Backup Codes for %s', 'wp-2fa' ),
		);

		// Send the response.
		wp_send_json_success(
			array(
				'codes' => $codes,
				'i18n'  => $i18n,
			)
		);
	}

	/**
	 * Grab number of unused backup codes within the users position.
	 *
	 * @param  object $user User data.
	 * @return int          Count of codes.
	 */
	public static function codes_remaining_for_user( $user ) {
		$backup_codes = get_user_meta( $user->ID, self::BACKUP_CODES_META_KEY, true );
		if ( is_array( $backup_codes ) && ! empty( $backup_codes ) ) {

			return count( $backup_codes );
		}
		return 0;
	}

	/**
	 * Validate backup codes
	 *
	 * @param  object $user User data.
	 * @param  string $code The code we are checking.
	 * @return bool   Is is valid or not.
	 */
	public static function validate_code( $user, $code ) {
		$backup_codes = get_user_meta( $user->ID, self::BACKUP_CODES_META_KEY, true );
		if ( is_array( $backup_codes ) && ! empty( $backup_codes ) ) {
			foreach ( $backup_codes as $code_index => $code_hashed ) {
				if ( wp_check_password( $code, $code_hashed, $user->ID ) ) {
					self::delete_code( $user, $code_hashed );
					self::get_login_attempts_instance()->clear_login_attempts( $user );

					return true;
				}
			}
		}
		self::get_login_attempts_instance()->increase_login_attempts( $user );

		return false;
	}

	/**
	 * Delete code once its used.
	 *
	 * @param  object $user User data.
	 * @param  string $code_hashed Code to delete.
	 */
	public static function delete_code( $user, $code_hashed ) {
		$backup_codes = get_user_meta( $user->ID, self::BACKUP_CODES_META_KEY, true );

		// Delete the current code from the list since it's been used.
		$backup_codes = array_flip( $backup_codes );
		unset( $backup_codes[ $code_hashed ] );
		$backup_codes = array_values( array_flip( $backup_codes ) );

		// Update the backup code master list.
		update_user_meta( $user->ID, self::BACKUP_CODES_META_KEY, $backup_codes );
	}

	/**
	 * Add the method to the existing backup methods array
	 *
	 * @param array $backup_methods - Array with the currently supported backup methods.
	 *
	 * @return array
	 *
	 * @since 2.0.0
	 */
	public static function add_backup_method( array $backup_methods ): array {
		return array_merge(
			$backup_methods,
			array(
				self::$method_name => array(
					'wizard-step' => '2fa-wizard-config-backup-codes',
					'button_name' => sprintf(
							/* translators: URL with more information about the backup codes */
						esc_html__( 'Login with a backup code: you will get 10 backup codes and you can use one of them when you need to login and you cannot generate a code from the app. %s', 'wp-2fa' ),
						'<a href="https://www.wpwhitesecurity.com/2fa-backup-codes/" target="_blank">' . esc_html__( 'More information.', 'wp-2fa' ) . '</a>'
					),
				),
			)
		);
	}

	/**
	 * Changes the global backup methods array - removes the method if it is not enabled
	 *
	 * @param array    $backup_methods - Array with all global backup methods.
	 * @param \WP_User $user - User to check for is that method enabled.
	 *
	 * @return array
	 *
	 * @since 2.0.0
	 */
	public static function check_backup_method( array $backup_methods, \WP_User $user ): array {
		$enabled = Settings_Page::are_backup_codes_enabled( User_Helper::get_user_role( $user ) );

		if ( ! $enabled ) {
			unset( $backup_methods[ self::$method_name ] );
		}

		return $backup_methods;
	}

	/**
	 * Returns the name of the method
	 *
	 * @return string
	 *
	 * @since 2.0.0
	 */
	public static function get_method_name(): string {
		return self::$method_name;
	}
}
