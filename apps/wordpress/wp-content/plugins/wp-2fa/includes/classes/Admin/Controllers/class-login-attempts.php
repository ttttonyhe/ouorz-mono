<?php
/**
 * Responsible for the plugin login attempts
 *
 * @package    wp2fa
 * @subpackage admin_controllers
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Admin\Controllers;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Login Attempts class
 */
if ( ! class_exists( '\WP2FA\Admin\Controllers\Login_Attempts' ) ) {

	/**
	 * Responsible for the login attempts
	 *
	 * @since 2.0.0
	 */
	class Login_Attempts {

		/**
		 * Holds the name of the meta key
		 *
		 * @var string
		 *
		 * @since 2.0.0
		 */
		private $meta_key = WP_2FA_PREFIX . 'login-attempts';

		/**
		 * Holds the number of allowed attempts to login
		 *
		 * @var integer
		 *
		 * @since 2.0.0
		 */
		private $number_of_allowed_attempts = 3;

		/**
		 * Default constructor
		 *
		 * @param string  $meta_key - The meta key name.
		 * @param integer $attempts - Number of the allowed login attempts.
		 *
		 * @since 2.0.0
		 */
		public function __construct( string $meta_key = '', int $attempts = 0 ) {
			if ( '' !== trim( $meta_key ) ) {
				$this->meta_key = $meta_key;
			}
			if ( 0 !== $attempts ) {
				$this->number_of_allowed_attempts = $attempts;
			}
		}

		/**
		 * Increasing login attempts for User
		 *
		 * @since 2.0.0
		 *
		 * @param \WP_User $user - the WP User.
		 *
		 * @return void
		 */
		public function increase_login_attempts( \WP_User $user ) {
			$attempts = $this->get_login_attempts( $user );
			if ( '' === $attempts ) {
				$attempts = 0;
			}
			\update_user_meta( $user->ID, $this->meta_key, ++$attempts );
		}

		/**
		 * Returns the number of unsuccessful attempts for the User
		 *
		 * @since 2.0.0
		 *
		 * @param \WP_User $user - the WP User.
		 *
		 * @return integer
		 */
		public function get_login_attempts( \WP_User $user ): int {
			return (int) \get_user_meta( $user->ID, $this->meta_key, true );
		}

		/**
		 * Clearing login attempts for User
		 *
		 * @since 2.0.0
		 *
		 * @param \WP_User $user - the WP User.
		 *
		 * @return void
		 */
		public function clear_login_attempts( \WP_User $user ) {
			\delete_user_meta( $user->ID, $this->meta_key );
		}

		/**
		 * Returns the number of allowed login attempts
		 *
		 * @return integer
		 *
		 * @since 2.0.0
		 */
		public function get_allowed_login_attempts(): int {
			return $this->number_of_allowed_attempts;
		}

		/**
		 * Sets the number of allowed attempts
		 *
		 * @param integer $number - The number of the allowed attempts.
		 *
		 * @return integer
		 *
		 * @since 2.0.0
		 */
		public function set_number_of_login_attempts( int $number ): int {
			$this->number_of_allowed_attempts = $number;

			return $this->number_of_allowed_attempts;
		}

		/**
		 * Returns the name of the meta key holding the login attempts for the user
		 *
		 * @return string
		 *
		 * @since 2.0.0
		 */
		public function get_meta_key(): string {

			return $this->meta_key;
		}

		/**
		 * Sets the login attempts meta key
		 *
		 * @param string $meta_key - The name of the meta.
		 *
		 * @return string
		 *
		 * @since 2.0.0
		 */
		public function set_meta_key( string $meta_key ): string {
			$this->meta_key = $meta_key;

			return $this->meta_key;
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
		public function check_number_of_attempts( \WP_User $user ):bool {
			if ( $this->get_allowed_login_attempts() < $this->get_login_attempts( $user ) ) {
				return false;
			}

			return true;
		}
	}
}
