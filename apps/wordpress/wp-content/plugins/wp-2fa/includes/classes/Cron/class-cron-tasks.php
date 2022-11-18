<?php // phpcs:ignore
/**
 * Responsible for cron tasks.
 *
 * @package    wp2fa
 * @subpackage cron
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Cron;

use \WP2FA\WP2FA as WP2FA;
use WP2FA\Utils\User_Utils;
use WP2FA\Admin\User;
use WP2FA\Admin\Settings_Page;
use WP2FA\Admin\Helpers\WP_Helper;

/**
 * Class for handling our crons.
 */
class Cron_Tasks {

	/**
	 * Constructor.
	 */
	public static function init() {
		add_action( WP_2FA_PREFIX . 'check_grace_period_status', array( __CLASS__, 'wp_2fa_check_users_grace_period_status' ) );
		add_action( 'init', array( __CLASS__, 'register_check_users_grace_period_status_event' ) );
	}

	/**
	 * This function will run once the 'wp_2fa_check_users_grace_period_status' is called
	 *
	 * @return void
	 */
	public static function wp_2fa_check_users_grace_period_status() {
		// check if the cronjob is enabled in plugin settings.
		if ( empty( WP2FA::get_wp2fa_general_setting( 'enable_grace_cron' ) ) ) {
			return;
		}

		// grab all users.
		$users_args = array(
			'fields' => array( 'ID' ),
		);

		if ( WP_Helper::is_multisite() ) {
			$users_args['blog_id'] = 0;
		}

		$users = User_Utils::get_all_user_ids( 'query', $users_args );
		if ( ! is_array( $users ) ) {
			$users = explode( ',', $users );
		}

		if ( empty( $users ) ) {
			return;
		}

		foreach ( $users as $user_id ) {
			// creating the user object will update their meta fields to reflect latest plugin settings.

			$user       = get_userdata( $user_id );
			$wp2fa_user = User::get_instance( $user );

			// run a check to see if user account needs to be locked (this happens only here and during the login).
			$wp2fa_user->lock_user_account_if_needed();
		}
	}

	/**
	 * Function which will register the event
	 *
	 * @return void
	 */
	public static function register_check_users_grace_period_status_event() {
		// Make sure this event hasn't been scheduled.
		if ( ! wp_next_scheduled( 'wp_2fa_check_grace_period_status' ) && ! empty( WP2FA::get_wp2fa_general_setting( 'enable_grace_cron' ) ) ) {
			// Schedule the event.
			wp_schedule_event( time(), 'hourly', 'wp_2fa_check_grace_period_status' );
		}
	}

	/**
	 * Send email to setup authentication
	 *
	 * @param [type] $user_id - The ID of the user.
	 *
	 * @return bool
	 */
	public static function send_expired_grace_email( $user_id ) {
		// Bail if the user has not enabled this email.
		if ( 'enable_account_locked_email' !== WP2FA::get_wp2fa_email_templates( 'send_account_locked_email' ) ) {
			return false;
		}

		// Grab user data.
		$user = get_userdata( $user_id );
		// Grab user email.
		$email = $user->user_email;

		$subject = wp_strip_all_tags( WP2FA::replace_email_strings( WP2FA::get_wp2fa_email_templates( 'user_account_locked_email_subject' ), $user_id ) );
		$message = wpautop( WP2FA::replace_email_strings( WP2FA::get_wp2fa_email_templates( 'user_account_locked_email_body' ), $user_id ) );

		return Settings_Page::send_email( $email, $subject, $message );
	}
}
