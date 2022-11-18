<?php
/**
 * Setup wizard rendering class.
 *
 * @package    wp2fa
 * @subpackage setup
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Admin;

use WP2FA\Admin\User;
use \WP2FA\Core as Core;
use \WP2FA\WP2FA as WP2FA;
use WP2FA\Admin\Helpers\WP_Helper;
use WP2FA\Admin\Views\Wizard_Steps;
use WP2FA\Admin\Helpers\User_Helper;
use WP2FA\Admin\Controllers\Settings;
use \WP2FA\Utils\User_Utils as User_Utils;
use WP2FA\Admin\Views\First_Time_Wizard_Steps;
use \WP2FA\Admin\Settings_Page as Settings_Page;
use WP2FA\Utils\Settings_Utils as Settings_Utils;
use \WP2FA\Utils\Generate_Modal as Generate_Modal;
use WP2FA\Admin\SettingsPages\Settings_Page_Policies;
use \WP2FA\Authenticator\Authentication as Authentication;

/**
 * Our class for creating a step by step wizard for easy configuration.
 */
class Setup_Wizard {

	/**
	 * Wizard Steps
	 *
	 * @var array
	 */
	private $wizard_steps;

	/**
	 * Current Step
	 *
	 * @var string
	 */
	private $current_step;

	/**
	 * Method: Constructor.
	 */
	public function __construct() { }

	/**
	 * Add setup admin page. This is empty on purpose.
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'read', 'wp-2fa-setup', '' );
	}

	/**
	 * Adding menus for multisite install
	 *
	 * @return void
	 *
	 * @since 2.2.0
	 */
	public function network_admin_menus() {
		add_dashboard_page( 'index.php', '', 'read', 'wp-2fa-setup', '' );
	}

	/**
	 * Setup Page Start.
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	public function setup_page() {

		// Get page argument from $_GET array.
		$page = ( isset( $_GET['page'] ) ) ? \sanitize_text_field( \wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore
		if ( empty( $page ) || 'wp-2fa-setup' !== $page ) {
			return;
		}

		// Clear out any old notices.
		$user = wp_get_current_user();

		// First lets check if any options have been saved.
		$settings_saved = true;
		$settings       = WP2FA::get_wp2fa_setting();
		if ( empty( $settings ) || ! isset( $settings ) ) {
			$settings_saved = false;
		}

		/**
		 * Wizard Steps.
		 */
		$get_array = filter_input_array( INPUT_GET );
		if ( isset( $get_array['wizard_type'] ) ) {
			$wizard_type = sanitize_text_field( $get_array['wizard_type'] );
		} else {
			$wizard_type = 'default';
		}

		$is_user_forced_to_setup = User_Helper::get_user_enforced_instantly( $user );
		if ( ! empty( $is_user_forced_to_setup ) ) {
			add_filter( 'wp_2fa_wizard_default_steps', array( $this, 'wp_2fa_add_intro_step' ) );
		}

		$user_type = User_Utils::determine_user_2fa_status( $user );

		$wizard_steps = array(
			'welcome'                => array(
				'name'        => esc_html__( 'Welcome', 'wp-2fa' ),
				'content'     => array( $this, 'wp_2fa_step_welcome' ),
				'wizard_type' => 'welcome_wizard',
			),
			'settings_configuration' => array(
				'name'        => esc_html__( 'Select 2FA Methods', 'wp-2fa' ),
				'content'     => array( $this, 'wp_2fa_step_global_2fa_methods' ),
				'save'        => array( $this, 'wp_2fa_step_global_2fa_methods_save' ),
				'wizard_type' => 'welcome_wizard',
			),
			'finish'                 => array(
				'name'        => esc_html__( 'Setup Finish', 'wp-2fa' ),
				'content'     => array( $this, 'wp_2fa_step_finish' ),
				'save'        => array( $this, 'wp_2fa_step_finish_save' ),
				'wizard_type' => 'welcome_wizard',
			),
		);

		// Admin user setting up fresh install of 2FA plugin.
		if ( in_array( 'can_manage_options', $user_type, true ) && ! $settings_saved ) {
			unset( $wizard_steps['user_choose_2fa_method'] );
			unset( $wizard_steps['reconfigure_method'] );
		}

		// We will use this setting to determine if defaults have already been saved to the DB.
		$have_defaults_been_applied = Settings_Utils::get_option( 'default_settings_applied', false );
		// If we have settings, but they are the defaults, then we want to consider the settings to be unsaved at this point.
		if ( in_array( 'can_manage_options', $user_type, true ) && $settings_saved && $have_defaults_been_applied ) {
			$settings_saved = false;
		}

		// Ensure user has minimum capabitlies needed to be here.
		if ( in_array( 'can_read', $user_type, true ) && $settings_saved ) {

			switch ( $wizard_type ) {
				case 'user_2fa_config':
					$wizard_steps = array_intersect_key( $wizard_steps, array_flip( array( 'user_choose_2fa_method', 'setup_method', 'finish', 'backup_codes' ) ) );
					break;

				case 'backup_codes_config':
					$wizard_steps = array_intersect_key( $wizard_steps, array_flip( array( 'backup_codes' ) ) );
					break;

				case 'user_reconfigure_config':
					$wizard_steps = array_intersect_key( $wizard_steps, array_flip( array( 'reconfigure_method' ) ) );
					break;

				default:
		   			$wizard_steps = array_intersect_key( $wizard_steps, array_flip( array( 'choose_2fa_method', 'setup_method', 'finish', 'backup_codes', 'reconfigure_method' ) ) );
			}

			// Remove 1st step if only one method is available.
			if ( empty( WP2FA::get_wp2fa_setting( 'enable_totp' ) ) || empty( WP2FA::get_wp2fa_setting( 'enable_email' ) ) ) {
				unset( $wizard_steps['choose_2fa_method'] );
			}

			// If the user has codes setup already, no need to add the slide.
			if ( ! in_array( 'user_needs_to_setup_backup_codes', $user_type, true ) && 'backup_codes_config' !== $wizard_type ) {
				unset( $wizard_steps['backup_codes'] );
			}
		}

		/**
		 * Filter: `Wizard Default Steps`
		 *
		 * WSAL filter to filter wizard steps before they are displayed.
		 *
		 * @param array $wizard_steps – Wizard Steps.
		 */
		$this->wizard_steps = apply_filters( WP_2FA_PREFIX . 'wizard_default_steps', $wizard_steps );

		// Set current step.
		$current_step       = ( isset( $_GET['current-step'] ) ) ? \sanitize_text_field( \wp_unslash( $_GET['current-step'] ) ) : ''; // phpcs:ignore
		$this->current_step = ! empty( $current_step ) ? $current_step : current( array_keys( $this->wizard_steps ) );

		if ( 'backup_codes' === $this->current_step && ! Settings_Page::are_backup_codes_enabled( User_Helper::get_user_role( $user ) ) ) {

			$redirect_to_finish = add_query_arg(
				array(
					'current-step' => 'finish',
					'all-set'      => 1,
				)
			);
			wp_safe_redirect( esc_url_raw( $redirect_to_finish ) );
		}

		/**
		 * Enqueue Scripts.
		 */
		wp_enqueue_style(
			'wp_2fa_setup_wizard',
			Core\style_url( 'setup-wizard', 'admin' ),
			array( 'select2' ),
			WP_2FA_VERSION
		);

		wp_enqueue_style(
			'wp_2fa_admin-style',
			Core\style_url( 'admin-style', 'admin' ),
			array(),
			WP_2FA_VERSION
		);

		\WP2FA\Core\enqueue_select2_scripts();

		if ( \WP2FA\Admin\Helpers\WP_Helper::is_multisite() ) {
			\WP2FA\Core\enqueue_multi_select_scripts();
		}

		wp_enqueue_script(
			'wp_2fa_admin',
			Core\script_url( 'admin', 'admin' ),
			array( 'jquery-ui-widget', 'jquery-ui-core', 'jquery-ui-autocomplete', 'select2' ),
			WP_2FA_VERSION,
			true
		);

		wp_enqueue_script(
			'wp_2fa_micromodal',
			Core\script_url( 'micromodal', 'admin', 'select2' ),
			array(),
			WP_2FA_VERSION,
			true
		);

		// Data array.
		$data_array = array(
			'ajaxURL'      => admin_url( 'admin-ajax.php' ),
			'roles'        => WP2FA::wp_2fa_get_roles(),
			'nonce'        => wp_create_nonce( 'wp-2fa-settings-nonce' ),
			'invalidEmail' => esc_html__( 'Please use a valid email address', 'wp-2fa' ),
		);
		wp_localize_script( 'wp_2fa_admin', 'wp2faData', $data_array );

		// Data array.
		$data_array = array(
			'ajaxURL'        => admin_url( 'admin-ajax.php' ),
			'nonce'          => wp_create_nonce( 'wp2fa-verify-wizard-page' ),
			'codesPreamble'  => esc_html__( 'These are the 2FA backup codes for the user', 'wp-2fa' ),
			'readyText'      => esc_html__( 'I\'m ready', 'wp-2fa' ),
			'codeReSentText' => esc_html__( 'New code sent', 'wp-2fa' ),
		);

		/**
		 * Gives the ability to change the default JS wizard settings.
		 *
		 * @param int $data_array - The array with all the JS wizard settings.
		 *
		 * @since 2.2.0
		 */
		$data_array = apply_filters( WP_2FA_PREFIX . 'js_wizard_settings', $data_array );
		wp_localize_script( 'wp_2fa_admin', 'wp2faWizardData', $data_array );

		/**
		 * Save Wizard Settings.
		 */
		$save_step = ( isset( $_POST['save_step'] ) ) ? \sanitize_text_field( \wp_unslash( $_POST['save_step'] ) ) : ''; // phpcs:ignore
		if ( ! empty( $save_step ) && ! empty( $this->wizard_steps[ $this->current_step ]['save'] ) ) {
			call_user_func( $this->wizard_steps[ $this->current_step ]['save'] );
		}

		$this->setup_page_header();
		$this->setup_page_steps();
		$this->setup_page_content();
		$this->setup_page_footer();

		exit();
	}

	/**
	 * Setup Page Header.
	 */
	private function setup_page_header() {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php esc_html_e( 'WP 2FA &rsaquo; Setup Wizard', 'wp-2fa' ); ?></title>
			<?php wp_print_scripts( 'jquery' ); ?>
			<?php wp_print_scripts( 'jquery-ui-core' ); ?>
			<?php wp_print_scripts( 'wp_2fa_setup_wizard' ); ?>
			<?php wp_print_scripts( 'wp_2fa_micromodal' ); ?>
			<?php wp_print_scripts( 'wp_2fa_admin' ); ?>
			<?php wp_print_scripts( 'multi-site-select' ); ?>
			<?php
				/**
				 * Gives the ability for 3rd party scripts to add their own JS to the plugin setup page.
				 *
				 * @since 2.2.0
				 */
				\do_action( WP_2FA_PREFIX . 'setup_page_scripts' );
			?>
			<?php wp_print_styles( 'common' ); ?>
			<?php wp_print_styles( 'forms' ); ?>
			<?php wp_print_styles( 'buttons' ); ?>
			<?php wp_print_styles( 'wp-jquery-ui-dialog' ); ?>
			<?php wp_print_styles( 'wp_2fa_admin' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
		</head>
		<body class="wp2fa-setup wp-core-ui">
			<div class="setup-wizard-wrapper wp-2fa-settings-wrapper wp2fa-form-styles">
				<h1 id="wp2fa-logo"><a href="https://wpsecurityauditlog.com" target="_blank"><img src="<?php echo esc_url( WP_2FA_URL . 'dist/images/wizard-logo.png' ); ?>"></a></h1>
		<?php
	}

	/**
	 * Setup Page Footer.
	 */
	private function setup_page_footer() {
		$user = wp_get_current_user();

		$redirect = Settings::get_settings_page_link();
		?>
			<div class="wp2fa-setup-footer">
				<?php if ( 'welcome' !== $this->current_step && 'finish' !== $this->current_step ) { // Don't show the link on the first & last step. ?>
					<?php if ( ! User_Helper::get_user_enforced_instantly( $user ) ) { ?>
						<a class="close-wizard-link" href="<?php echo esc_url( $redirect ); ?>"><?php esc_html_e( 'Close Wizard', 'wp-2fa' ); ?></a>
						<?php
					}
				}
				?>
			</div>
		</div>
		</body>
		</html>
		<?php
			// phpcs:ignore
			echo Generate_Modal::generate_modal(
				'notify-admin-settings-page',
				'',
				__( 'If you cancel this wizard, the default plugin settings will be applied. You can always configure the plugin settings and two-factor authentication policies at a later stage from the ', 'wp-2fa' ) . ' <b>' . __( 'WP 2FA', 'wp-2fa' ) . '</b>' . __( ' entry in your WordPress dashboard menu.', 'wp-2fa' ),
				array(
					'<a href="#" id="close-settings" class="button button-primary wp-2fa-button-primary" data-redirect-url="' . esc_url( $redirect ) . '">' . __( 'OK, close wizard', 'wp-2fa' ) . '</a>',
					'<a href="#" class="button button-secondary wp-2fa-button-secondary wp-2fa-button-secondary" data-close-2fa-modal>' . __( 'Continue with wizard', 'wp-2fa' ) . '</a>',
				),
				'',
				'580px'
			);
		?>
		<?php
	}

	/**
	 * Setup Page Steps.
	 */
	private function setup_page_steps() {
		?>
		<ul class="steps">
			<?php
			foreach ( $this->wizard_steps as $key => $step ) :
				if ( 'welcome_wizard' === $step['wizard_type'] || is_array( $step['wizard_type'] ) && in_array( 'welcome_wizard', $step['wizard_type'], true ) ) :
					if ( $key === $this->current_step ) :
						?>
						<li class="is-active"><?php echo esc_html( $step['name'] ); ?></li>
						<?php
									else :
										?>
						<li><?php echo esc_html( $step['name'] ); ?></li>
										<?php
									endif;
				endif;
			endforeach;
			?>
		</ul>
		<?php
	}

	/**
	 * Get Next Step URL.
	 *
	 * @return string
	 */
	private function get_next_step() {
		// Get current step.
		$current_step = $this->current_step;

		// Array of step keys.
		$keys = array_keys( $this->wizard_steps );
		if ( end( $keys ) === $current_step ) { // If last step is active then return WP Admin URL.
			return admin_url();
		}

		// Search for step index in step keys.
		$step_index = array_search( $current_step, $keys, true );
		if ( false === $step_index ) { // If index is not found then return empty string.
			return '';
		}

		// Return next step.
		return add_query_arg( 'current-step', $keys[ $step_index + 1 ] );
	}

	/**
	 * Setup Page Content.
	 */
	private function setup_page_content() {
		?>
		<div class="wp2fa-setup-content">
			<?php
			if ( ! empty( $this->wizard_steps[ $this->current_step ]['content'] ) ) {
				call_user_func( $this->wizard_steps[ $this->current_step ]['content'] );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Step View: `Welcome`
	 */
	private function wp_2fa_step_welcome() {
		Wizard_Steps::welcome_step( $this->get_next_step() );
	}

	/**
	 * Step View: `Finish`
	 */
	private function wp_2fa_step_finish() {
		$wp2fa_user = User::get_instance();
		User_Helper::remove_user_needs_to_reconfigure_2fa( $wp2fa_user->get_2fa_wp_user() );
		Wizard_Steps::congratulations_step( true );
	}

	/**
	 * Step Save: `Finish`
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	private function wp_2fa_step_finish_save() {
		// Verify nonce.
		check_admin_referer( 'wp2fa-step-finish' );
		wp_safe_redirect( esc_url_raw( $this->get_next_step() ) );
		exit();
	}

	/**
	 * Step View: `Choose Methods`
	 */
	private function wp_2fa_step_global_2fa_methods() {
		?>
		<form method="post" class="wp2fa-setup-form wp2fa-form-styles" autocomplete="off">
			<?php wp_nonce_field( 'wp2fa-step-choose-method' ); ?>
			<div class="step-setting-wrapper active" data-step-title="<?php esc_html_e( 'Choose 2FA methods', 'wp-2fa' ); ?>">
				<?php First_Time_Wizard_Steps::select_method( true ); ?>
				<div class="wp2fa-setup-actions">
					<a class="button button-primary" name="next_step_setting" value="<?php esc_attr_e( 'Continue Setup', 'wp-2fa' ); ?>"><?php esc_html_e( 'Continue Setup', 'wp-2fa' ); ?></a>
				</div>
			</div>
			<div class="step-setting-wrapper" data-step-title="<?php esc_html_e( '2FA policy', 'wp-2fa' ); ?>">
				<?php First_Time_Wizard_Steps::enforcement_policy( true ); ?>
				<div class="wp2fa-setup-actions">
					<a class="button button-primary" name="next_step_setting" value="<?php esc_attr_e( 'Continue Setup', 'wp-2fa' ); ?>"><?php esc_html_e( 'Continue Setup', 'wp-2fa' ); ?></a>
				</div>
			</div>
			<div class="step-setting-wrapper hidden" data-step-title="<?php esc_html_e( 'Exclude users', 'wp-2fa' ); ?>">
			<?php First_Time_Wizard_Steps::exclude_users( true ); ?>
				<div class="wp2fa-setup-actions">
					<a class="button button-primary" name="next_step_setting" value="<?php esc_attr_e( 'Continue Setup', 'wp-2fa' ); ?>"><?php esc_html_e( 'Continue Setup', 'wp-2fa' ); ?></a>
				</div>
			</div>

			<?php if ( WP_Helper::is_multisite() ) : ?>
				<div class="step-setting-wrapper" data-step-title="<?php esc_html_e( 'Exclude sites', 'wp-2fa' ); ?>">
				<?php First_Time_Wizard_Steps::excluded_network_sites( true ); ?>
					<div class="wp2fa-setup-actions">
						<a class="button button-primary" name="next_step_setting" value="<?php esc_attr_e( 'Continue Setup', 'wp-2fa' ); ?>"><?php esc_html_e( 'Continue Setup', 'wp-2fa' ); ?></a>
					</div>
				</div>
			<?php endif; ?>

			<div class="step-setting-wrapper" data-step-title="<?php esc_html_e( 'Grace period', 'wp-2fa' ); ?>">
				<h3><?php esc_html_e( 'How long should the grace period for your users be?', 'wp-2fa' ); ?></h3>
				<p class="description"><?php esc_html_e( 'When you configure the 2FA policies and require users to configure 2FA, they can either have a grace period to configure 2FA, or can be required to configure 2FA before the next time they login. Choose which method you\'d like to use:', 'wp-2fa' ); ?></p>
				<?php First_Time_Wizard_Steps::grace_period( true ); ?>
				<div class="wp2fa-setup-actions">
					<button class="button button-primary save-wizard" type="submit" name="save_step" value="<?php esc_attr_e( 'All done', 'wp-2fa' ); ?>"><?php esc_html_e( 'All done', 'wp-2fa' ); ?></button>
				</div>
			</div>

		</form>
		<?php
	}

	/**
	 * Step Save: `Choose Method`
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	private function wp_2fa_step_global_2fa_methods_save() {
		// Check nonce.
		check_admin_referer( 'wp2fa-step-choose-method' );

		$input = ( isset( $_POST[ WP_2FA_POLICY_SETTINGS_NAME ] ) ) ? wp_unslash( $_POST[ WP_2FA_POLICY_SETTINGS_NAME ] ) : array(); // phpcs:ignore

		if ( ! WP_Helper::is_multisite() ) {
			unregister_setting(
				WP_2FA_POLICY_SETTINGS_NAME,
				WP_2FA_POLICY_SETTINGS_NAME
			);
		}
		$settings_page      = new Settings_Page_Policies();
		$sanitized_settings = $settings_page->validate_and_sanitize( $input, 'setup_wizard' );
		WP2FA::update_plugin_settings( $sanitized_settings );

		wp_safe_redirect( esc_url_raw( $this->get_next_step() ) );
		exit();
	}

	/**
	 * Send email with fresh code, or to setup email 2fa.
	 *
	 * @param int    $user_id User id we want to send the message to.
	 * @param string $nominated_email_address - The user custom address to use (name of the meta key to check for).
	 *
	 * @return bool
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	public static function send_authentication_setup_email( $user_id, $nominated_email_address = 'nominated_email_address' ) {

		// If we have a nonce posted, check it.
		if ( \wp_doing_ajax() && isset( $_POST['nonce'] ) ) {
			$nonce_check = \wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_POST['nonce'] ) ), 'wp-2fa-send-setup-email' );
			if ( ! $nonce_check ) {
				return false;
			}
		}

		if ( isset( $_POST['user_id'] ) ) {
			$user = get_userdata( intval( $_POST['user_id'] ) );
		} else {
			$user = get_userdata( $user_id );
		}

		// Grab email address is its provided.
		if ( isset( $_POST['email_address'] ) ) {
			$email = sanitize_email( \wp_unslash( $_POST['email_address'] ) );
		} else {
			$email = sanitize_email( $user->user_email );
		}

		if ( wp_doing_ajax() && isset( $_POST['nonce'] ) ) {
			update_user_meta( $user->ID, WP_2FA_PREFIX . 'nominated_email_address', $email );
		}

		$enabled_email_address = '';
		if ( ! empty( $nominated_email_address ) ) {
			$enabled_email_address = get_user_meta( $user->ID, WP_2FA_PREFIX . $nominated_email_address, true );
		}

		// Generate a token and setup email.
		$token  = Authentication::generate_token( $user->ID );


		$subject = wp_strip_all_tags( WP2FA::replace_email_strings( WP2FA::get_wp2fa_email_templates( 'login_code_email_subject' ), $user->ID ) );
		$message = wpautop( WP2FA::replace_email_strings( WP2FA::get_wp2fa_email_templates( 'login_code_email_body' ), $user->ID, $token ) );

		if ( ! empty( $enabled_email_address ) ) {
			$email_address = $enabled_email_address;
		} else {
			$email_address = $user->user_email;
		}

		return Settings_Page::send_email( $email_address, $subject, $message );
	}

	/**
	 * Send email to setup authentication
	 */
	public function regenerate_authentication_key() {
		// Grab current user.
		$user = wp_get_current_user();

		$key = Authentication::generate_key();

		$site_name = get_bloginfo( 'name', 'display' );
		/**
		 * Changing the title of the login screen for the TOTP method.
		 *
		 * @param string $title - The default title.
		 * @param \WP_User $user - The WP user.
		 *
		 * @since 2.0.0
		 */
		$totp_title = apply_filters( WP_2FA_PREFIX . 'totp_title', $site_name . ':' . $user->user_login, $user );
		$new_qr     = Authentication::get_google_qr_code( $totp_title, $key, $site_name );

		wp_send_json_success(
			array(
				'key' => Authentication::decrypt_key_if_needed( $key ),
				'qr'  => $new_qr,
			)
		);
	}

	/**
	 * 3rd Party plugins
	 *
	 * @param array $wizard_steps - Array with the current wizard steps.
	 *
	 * @return array
	 */
	public function wp_2fa_add_intro_step( $wizard_steps ) {
		$new_wizard_steps = array(
			'test' => array(
				'name'        => __( 'Welcome to WP 2FA', 'wp-2fa' ),
				'content'     => array( $this, 'introduction_step' ),
				'save'        => array( $this, 'introduction_step_save' ),
				'wizard_type' => 'welcome_wizard',
			),
		);

		// combine the two arrays.
		$wizard_steps = $new_wizard_steps + $wizard_steps;

		return $wizard_steps;
	}

	/**
	 * Shows introduction step of the wizard
	 *
	 * @return void
	 */
	private function introduction_step() {
		Wizard_Steps::introduction_step();
	}

	/**
	 * Step Save: `Addons`
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	private function introduction_step_save() {
		// Check nonce.
		check_admin_referer( 'wp2fa-step-addon' );

		wp_safe_redirect( esc_url_raw( $this->get_next_step() ) );
		exit();
	}
}
