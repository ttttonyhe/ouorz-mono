<?php
/**
 * Responsible for WP2FA user's notifying.
 *
 * @package    wp2fa
 * @subpackage user-utils
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Admin;

use WP2FA\Admin\User;
use \WP2FA\WP2FA as WP2FA;
use WP2FA\Utils\Date_Time_Utils;
use WP2FA\Admin\Helpers\WP_Helper;
use WP2FA\Admin\Helpers\User_Helper;
use WP2FA\Admin\Controllers\Settings;

/**
 * User_Notices - Class for displaying notices to our users.
 */
class User_Notices {
	/**
	 * The WP User
	 *
	 * @var User
	 */
	private $wp2fa_user;

	/**
	 * Lets set things up
	 */
	public function __construct() {
		$enforcement_policy = WP2FA::get_wp2fa_setting( 'enforcement-policy' );
		if ( ! empty( $enforcement_policy ) ) {
			// Check we are supposed to, before adding action to show nag.
			if ( in_array( $enforcement_policy, array( 'all-users', 'certain-roles-only', 'certain-users-only', 'superadmins-only', 'superadmins-siteadmins-only', 'enforce-on-multisite', true ), true ) ) {
				add_action( 'admin_notices', array( $this, 'user_setup_2fa_nag' ) );
				add_action( 'network_admin_notices', array( $this, 'user_setup_2fa_nag' ) );
			} elseif ( 'do-not-enforce' === WP2FA::get_wp2fa_setting( 'enforcement-policy' ) ) {
				add_action( 'admin_notices', array( $this, 'user_reconfigure_2fa_nag' ) );
				add_action( 'network_admin_notices', array( $this, 'user_setup_2fa_nag' ) );
			}
		}
	}

	/**
	 * The nag content
	 *
	 * @param string $is_shortcode - Is that a call from shortcode.
	 * @param string $configure_2fa_url - The configuration url.
	 *
	 * @return void
	 */
	public function user_setup_2fa_nag( $is_shortcode = '', $configure_2fa_url = '' ) {

		$this->ensure_user();

		if ( isset( $_GET['user_id'] ) ) { // phpcs:ignore
			$current_profile_user_id = (int) $_GET['user_id']; // phpcs:ignore
		} elseif ( ! is_null( $this->wp2fa_user->get_2fa_wp_user() ) ) {
			$current_profile_user_id = $this->wp2fa_user->get_2fa_wp_user()->ID;
		} else {
			$current_profile_user_id = false;
		}

		if ( ! $current_profile_user_id ||
			isset( $_GET['user_id'] ) && // phpcs:ignore
			$_GET['user_id'] !== $this->wp2fa_user->get_2fa_wp_user()->ID || // phpcs:ignore
			User_Helper::get_user_enforced_instantly( $this->wp2fa_user->get_2fa_wp_user() ) ) {
			return;
		}

		$grace_expiry = (int) User_Helper::get_user_expiry_date( $this->wp2fa_user->get_2fa_wp_user() );

		$class = 'notice notice-info wp-2fa-nag';

		if ( User_Helper::get_user_needs_to_reconfigure_2fa( $this->wp2fa_user->get_2fa_wp_user() ) ) {
			$message = esc_html__( 'The 2FA method you were using is no longer allowed on this website. Please reconfigure 2FA using one of the supported methods within', 'wp-2fa' );
		} else {
			$message = esc_html__( 'This website’s administrator requires you to enable 2FA authentication', 'wp-2fa' );
		}

		$is_nag_dismissed   = User_Helper::get_nag_status();
		$is_nag_needed      = User_Helper::is_enforced( $this->wp2fa_user->get_2fa_wp_user()->ID );
		$is_user_excluded   = User_Helper::is_excluded( $this->wp2fa_user->get_2fa_wp_user()->ID );
		$enabled_methods    = User_Helper::get_enabled_method_for_user( $this->wp2fa_user->get_2fa_wp_user() );
		$new_page_id        = WP2FA::get_wp2fa_setting( 'custom-user-page-id' );
		$new_page_permalink = get_permalink( $new_page_id );

		$setup_url = Settings::get_setup_page_link();

		// Allow setup URL to be customized if outputting via shortcode.
		if ( isset( $is_shortcode ) && 'output_shortcode' === $is_shortcode && ! empty( $configure_2fa_url ) ) {
			$setup_url = $configure_2fa_url;
		}

		// Stop the page from being a link to a page this user cant access if needed.
		if ( WP_Helper::is_multisite() && ! is_user_member_of_blog( $this->wp2fa_user->get_2fa_wp_user()->ID ) ) {
			$new_page_id = false;
		}

		// If we have a custom page generated, lets use it.
		if ( ! empty( $new_page_id ) && $new_page_permalink ) {
			$setup_url = $new_page_permalink;
		}

		// If the nag has not already been dismissed, and of course if the user is eligible, lets show them something.
		if ( ! $is_nag_dismissed && $is_nag_needed && empty( $enabled_methods ) && ! $is_user_excluded && ! empty( $grace_expiry ) ) {
			echo '<div class="' . esc_attr( $class ) . '">';
			echo '<p>' . esc_html( $message );
			echo ' <span class="grace-period-countdown">' . esc_attr( Date_Time_Utils::format_grace_period_expiration_string( null, $grace_expiry ) ) . '</span>';
			echo ' <a href="' . esc_url( $setup_url ) . '" class="button button-primary">' . esc_html__( 'Configure 2FA now', 'wp-2fa' ) . '</a>';
			echo ' <a href="#" class="button button-secondary dismiss-user-configure-nag">' . esc_html__( 'Remind me on next login', 'wp-2fa' ) . '</a></p>';
			echo '</div>';
		} else {
			$this->user_reconfigure_2fa_nag();
		}
	}

	/**
	 * The nag content
	 */
	public function user_reconfigure_2fa_nag() {

		$this->ensure_user();

		// If the nag has not already been dismissed, and of course if the user is eligible, lets show them something.
		if ( $this->wp2fa_user->needs_to_reconfigure_method() ) {
			$class = 'notice notice-info wp-2fa-nag';

			$message = esc_html__( 'The 2FA method you were using is no longer allowed on this website. Please reconfigure 2FA using one of the supported methods.', 'wp-2fa' );

			echo '<div class="' . esc_attr( $class ) . '"><p>' . esc_html( $message );
			echo ' <a href="' . esc_url( Settings::get_setup_page_link() ) . '" class="button button-primary">' . esc_html__( 'Configure 2FA now', 'wp-2fa' ) . '</a>';
			echo '  <a href="#" class="button button-secondary wp-2fa-button-secondary dismiss-user-reconfigure-nag">' . esc_html__( 'I\'ll do it later', 'wp-2fa' ) . '</a></p>';
			echo '</div>';
		}
	}

	/**
	 * Dismiss notice and setup a user meta value so we know its been dismissed
	 */
	public function dismiss_nag() {
		User_Helper::set_nag_status( true );
	}

	/**
	 * Reset the nag when the user logs out, so they get it again next time.
	 *
	 * @param [type] $user_id - The ID of the user.
	 *
	 * @return void
	 */
	public function reset_nag( $user_id ) {
		User_Helper::remove_nag_status( $user_id );
	}

	/**
	 * Sets user variable
	 *
	 * @return void
	 */
	private function ensure_user() {
		if ( ! isset( $this->wp2fa_user ) ) {
			$this->wp2fa_user = User::get_instance();
		}
	}
}
