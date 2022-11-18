<?php
/**
 * Responsible for rendering the short codes.
 *
 * @package    wp2fa
 * @subpackage short-codes
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Shortcodes;

use \WP2FA\WP2FA as WP2FA;
use \WP2FA\Core as Core;
use \WP2FA\Admin\User_Profile as User_Profile;
use \WP2FA\Admin\User_Notices as User_Notices;
use WP2FA\Admin\Controllers\Settings;

/**
 * Class for rendering shortcodes.
 */
class Shortcodes {

	/**
	 * Constructor.
	 */
	public static function init() {
		add_shortcode( 'wp-2fa-setup-form', array( __CLASS__, 'user_setup_2fa_form' ) );
		add_shortcode( 'wp-2fa-setup-notice', array( __CLASS__, 'user_setup_2fa_notice' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_2fa_shortcode_scripts' ) );
	}

	/**
	 * Register scripts and styles.
	 */
	public static function register_2fa_shortcode_scripts() {
		// Add our front end stuff, which we only want to load when the shortcode is present.
		wp_register_script( 'wp_2fa_frontend_scripts', Core\script_url( 'wp-2fa', 'admin' ), array( 'jquery', 'wp_2fa_micro_modals' ), WP_2FA_VERSION, true );
		wp_register_script( 'wp_2fa_micro_modals', Core\script_url( 'micromodal', 'admin' ), array(), WP_2FA_VERSION, true );
		wp_register_style( 'wp_2fa_styles', Core\style_url( 'styles', 'frontend' ), array(), WP_2FA_VERSION );

		$data_array = array(
			'ajaxURL'        => admin_url( 'admin-ajax.php' ),
			'roles'          => WP2FA::wp_2fa_get_roles(),
			'nonce'          => wp_create_nonce( 'wp-2fa-settings-nonce' ),
			'codesPreamble'  => esc_html__( 'These are the 2FA backup codes for the user', 'wp-2fa' ),
			'readyText'      => esc_html__( 'I\'m ready', 'wp-2fa' ),
			'codeReSentText' => esc_html__( 'New code sent', 'wp-2fa' ),
			'allDoneHeading' => esc_html__( 'All done.', 'wp-2fa' ),
			'allDoneText'    => esc_html__( 'Your login just got more secure.', 'wp-2fa' ),
			'closeWizard'    => esc_html__( 'Close Wizard', 'wp-2fa' ),
			'invalidEmail'   => esc_html__( 'Please use a valid email address', 'wp-2fa' ),
		);
		wp_localize_script( 'wp_2fa_frontend_scripts', 'wp2faData', $data_array );

		$data_array = array(
			'ajaxURL'        => admin_url( 'admin-ajax.php' ),
			'nonce'          => wp_create_nonce( 'wp2fa-verify-wizard-page' ),
			'codesPreamble'  => esc_html__( 'These are the 2FA backup codes for the user', 'wp-2fa' ),
			'readyText'      => esc_html__( 'I\'m ready', 'wp-2fa' ),
			'codeReSentText' => esc_html__( 'New code sent', 'wp-2fa' ),
			'invalidEmail'   => esc_html__( 'Please use a valid email address', 'wp-2fa' ),
		);

		$role                        = array_key_first( WP2FA::wp_2fa_get_roles() );
		$redirect_page               = Settings::get_role_or_default_setting( 'redirect-user-custom-page-global', 'current', $role );
		$data_array['redirectToUrl'] = ( '' !== trim( $redirect_page ) ) ? \trailingslashit( get_site_url() ) . $redirect_page : '';
		// Check and override if custom redirect page is selected and custom redirect is set.
		if (
			'yes' === Settings::get_role_or_default_setting( 'create-custom-user-page', 'current', $role ) ||
			'yes' === Settings::get_role_or_default_setting( 'create-custom-user-page' ) ) {
			if (
				'' !== trim( Settings::get_role_or_default_setting( 'redirect-user-custom-page', 'current', $role ) ) ||
				'' !== trim( Settings::get_role_or_default_setting( 'redirect-user-custom-page' ) ) ) {
				if ( 'yes' === Settings::get_role_or_default_setting( 'create-custom-user-page', 'current', $role ) ) {
					$data_array['redirectToUrl'] = trailingslashit( get_site_url() ) . Settings::get_role_or_default_setting( 'redirect-user-custom-page', 'current', $role );
				} else {
					$data_array['redirectToUrl'] = trailingslashit( get_site_url() ) . Settings::get_role_or_default_setting( 'redirect-user-custom-page' );
				}
			}
		}

		// Check for shortcode parameter - if one is present use it to redirect the user - highest priority.
		if ( isset( $redirect_after ) && ! empty( $redirect_after ) ) {
			$data_array['redirectToUrl'] = trailingslashit( get_site_url() ) . \urlencode( $redirect_after );
		} elseif ( isset( $_GET['return'] ) && ! empty( $_GET['return'] ) ) {
			$data_array['redirectToUrl'] = trailingslashit( get_site_url() ) . strip_tags( $_GET['return'] ); // phpcs:ignore
		}

		wp_localize_script( 'wp_2fa_frontend_scripts', 'wp2faWizardData', $data_array );

		/**
		 * Fires when the FE shortcode scripts are registered.
		 *
		 * @param bool $shortcodes - True if called from the short codes method.
		 *
		 * @since 2.2.0
		 */
		\do_action( WP_2FA_PREFIX . 'shortcode_scripts', true );
	}

	/**
	 * Output setup form.
	 *
	 * @param array $atts - Array with the attributes passed to shortcode.
	 *
	 * @return string
	 */
	public static function user_setup_2fa_form( $atts ) {

		/** Shortcode redirect_after is supported, with which the user can override all other settings */
		extract( // phpcs:ignore
			shortcode_atts(
				array(
					'show_preamble'  => 'true',
					'redirect_after' => '',
				),
				$atts
			)
		);

		if ( is_user_logged_in() ) {
			wp_enqueue_script( 'wp_2fa_frontend_scripts' );
			wp_enqueue_style( 'wp_2fa_styles' );

			$forms = new User_Profile();
			ob_start();
			echo '<form id="your-profile" class="wp-2fa-configuration-form">';
				$forms->inline_2fa_profile_form( 'output_shortcode', $show_preamble );
			echo '</form>';
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		} elseif ( ! is_admin() && ! is_user_logged_in() ) {
			$new_page_id = WP2FA::get_wp2fa_setting( 'custom-user-page-id' );
			$redirect_to = ! empty( $new_page_id ) ? get_permalink( $new_page_id ) : get_home_url();
			ob_start();
			echo '<p>' . esc_html__( 'You must be logged in to view this page.', 'wp-2fa' ) . ' <a href="' . esc_url( wp_login_url( $redirect_to ) ) . '">' . esc_html__( 'Login here.', 'wp-2fa' ) . '</a></p>';
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
	}

	/**
	 * Output setup nag.
	 *
	 * @param array $atts - Array with the attributes passed to shortcode.
	 *
	 * @return string
	 */
	public static function user_setup_2fa_notice( $atts ) {
		extract( // phpcs:ignore
			shortcode_atts(
				array(
					'configure_2fa_url' => '',
				),
				$atts
			)
		);
		$notice = new User_Notices();

		if ( ! is_admin() && is_user_logged_in() ) {
			wp_enqueue_script( 'wp_2fa_micro_modals' );
			wp_enqueue_script( 'wp_2fa_frontend_scripts' );
			wp_enqueue_style( 'wp_2fa_styles' );

			$data_array = array(
				'ajaxURL'        => admin_url( 'admin-ajax.php' ),
				'roles'          => WP2FA::wp_2fa_get_roles(),
				'nonce'          => wp_create_nonce( 'wp-2fa-settings-nonce' ),
				'codesPreamble'  => esc_html__( 'These are the 2FA backup codes for the user', 'wp-2fa' ),
				'readyText'      => esc_html__( 'I\'m ready', 'wp-2fa' ),
				'codeReSentText' => esc_html__( 'New code sent', 'wp-2fa' ),
				'allDoneHeading' => esc_html__( 'All done.', 'wp-2fa' ),
				'allDoneText'    => esc_html__( 'Your login just got more secure.', 'wp-2fa' ),
				'closeWizard'    => esc_html__( 'Close Wizard', 'wp-2fa' ),
			);
			wp_localize_script( 'wp_2fa_frontend_scripts', 'wp2faData', $data_array );

			ob_start();
			$notice->user_setup_2fa_nag( 'output_shortcode', $configure_2fa_url );
			$content = ob_get_contents();
			ob_end_clean();

			return $content;
		}

		return '';
	}
}
