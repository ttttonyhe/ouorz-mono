<?php
/**
 * Settings page render class.
 *
 * @package    wp2fa
 * @subpackage views
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Admin\Views;

use WP2FA\WP2FA;
use WP2FA\Utils\User_Utils;
use WP2FA\Authenticator\Authentication;
use WP2FA\Admin\User;
use WP2FA\Admin\Controllers\Settings;
use WP2FA\Admin\Helpers\User_Helper;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * WP2FA Wizard Settings view controller
 *
 * @since 1.7
 */
class Wizard_Steps {

	/**
	 * Holds the current user
	 *
	 * @since 1.7
	 *
	 * @var \WP2FA\Admin\User
	 */
	private static $user = null;

	/**
	 * Is the totp method enabled
	 *
	 * @since 1.7
	 *
	 * @var bool
	 */
	private static $totp_enabled = null;

	/**
	 * Is the mail enabled
	 *
	 * @since 1.7
	 *
	 * @var bool
	 */
	private static $email_enabled = null;

	/**
	 * Holds the nonce for json calls
	 *
	 * @since 1.7
	 *
	 * @var string
	 */
	private static $json_nonce = null;

	/**
	 * Holds the url to which to redirect the user after the setup is finished
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	private static $redirect_url = null;

	/**
	 * Introduction step form
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public static function optional_user_welcome_step() {
		?>
		<div class="wizard-step active">
			<div class="mb-20">
				<?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( 'welcome', true ) ); ?>
			</div>

			<div class="wp2fa-setup-actions">
				<a href="#" class="button wp-2fa-button-primary button-primary" data-name="next_step_setting_modal_wizard" data-next-step="choose-2fa-method"><?php esc_html_e( 'Next Step', 'wp-2fa' ); ?></a>
				<button class="wp-2fa-button-secondary button button-secondary wp-2fa-button-secondary" data-close-2fa-modal aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'wp-2fa' ); ?></button>
			</div>
		</div>
		<?php
	}

	/**
	 * Introduction step form
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public static function introduction_step() {
		?>
		<form method="post" class="wp2fa-setup-form">
			<?php wp_nonce_field( 'wp2fa-step-addon' ); ?>
			<div class="mb-20">
				<?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( '2fa_required_intro', true ) ); ?>
			</div>

			<div class="wp2fa-setup-actions">
				<button class="button button-primary wp-2fa-button-primary"
				type="submit"
				name="save_step"
				value="<?php esc_attr_e( 'Next', 'wp-2fa' ); ?>">
				<?php esc_html_e( 'Next', 'wp-2fa' ); ?>
				</button>
			</div>
		</form>
		<?php
	}

	/**
	 * Welcome step of the wizard
	 *
	 * @since 1.7
	 *
	 * @param string $next_step - url of the next step.
	 *
	 * @return void
	 */
	public static function welcome_step( $next_step ) {
		$redirect = Settings::get_settings_page_link();

		?>
		<h3><?php esc_html_e( 'Let us help you get started', 'wp-2fa' ); ?></h3>
		<p><?php esc_html_e( 'Thank you for installing the WP 2FA plugin. This quick wizard will assist you with configuring the plugin and the two-factor authentication (2FA) settings for your user and the users on this website.', 'wp-2fa' ); ?></p>

		<div class="wp2fa-setup-actions">
			<a class="button button-primary"
				href="<?php echo esc_url( $next_step ); ?>">
				<?php esc_html_e( 'Let’s get started!', 'wp-2fa' ); ?>
			</a>
			<a class="button button-secondary wp-2fa-button-secondary first-time-wizard"
				href="<?php echo esc_url( $redirect ); ?>">
				<?php esc_html_e( 'Skip Wizard - I know how to do this', 'wp-2fa' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Shows the initial totp setup options based on enabled methods
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public static function totp_option() {
		if ( self::is_totp_enabled() ) {
			?>
			<div class="option-pill">
				<label for="basic">
					<input id="basic" name="wp_2fa_enabled_methods" type="radio" value="totp" checked>
						<?php esc_html_e( 'One-time code generated with your app of choice (most reliable and secure)', 'wp-2fa' ); ?><span class="wizard-tooltip" data-tooltip-content="data-totp-tooltip-content-wrapper">i</span>
				</label>
				<?php
					echo '<p class="description tooltip-content-wrapper" data-totp-tooltip-content-wrapper>';
					printf(
						/* translators: link to the knowledge base website */
						esc_html__( 'Refer to the %s for more information on how to setup these apps and which apps are supported.', 'wp-2fa' ),
						'<a href="https://wp2fa.io/support/kb/configuring-2fa-apps/" target="_blank">' . esc_html__( 'guide on how to set up 2FA apps', 'wp-2fa' ) . '</a>'
					);
					echo '</p>';
				?>
			</div>
			<?php
		}
	}

	/**
	 * Shows the initial email setup option based on enabled methods
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public static function email_option() {
		if ( self::is_mail_enabled() ) {
			?>
			<div class="option-pill">
				<label for="geek">
					<input id="geek" name="wp_2fa_enabled_methods" type="radio" value="email">
				<?php esc_html_e( 'One-time code sent to you over email', 'wp-2fa' ); ?>
				</label>
			</div>
			<?php
		}
	}


	/**
	 * Shows the option to reconfigure email (if applicable)
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public static function totp_re_configure() {

		if ( ! self::is_totp_enabled() ) {
			return;
		}

		$nonce = self::json_nonce();

		?>
		<div class="option-pill">
			<?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( 'totp_reconfigure_intro', true ) ); ?>
			<div class="wp2fa-setup-actions">
				<a href="#" class="button button-primary wp-2fa-button-primary" data-name="next_step_setting_modal_wizard" data-trigger-reset-key data-nonce="<?php echo esc_attr( $nonce ); ?>" data-user-id="<?php echo esc_attr( self::get_user()->get_2fa_wp_user()->ID ); ?>" data-next-step="2fa-wizard-totp"><?php esc_html_e( 'Reset Key', 'wp-2fa' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Shows the option for email method reconfiguring (if applicable)
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public static function email_re_configure() {

		if ( ! self::is_mail_enabled() ) {
			return;
		}

		$setupnonce = wp_create_nonce( 'wp-2fa-send-setup-email' );
		?>
			<div class="option-pill">
				<?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( 'hotp_reconfigure_intro', true ) ); ?>
				<div class="wp2fa-setup-actions">
					<a class="button button-primary wp-2fa-button-primary" data-name="next_step_setting_modal_wizard" value="<?php esc_attr_e( 'I\'m Ready', 'wp-2fa' ); ?>" data-user-id="<?php echo esc_attr( self::get_user()->get_2fa_wp_user()->ID ); ?>" data-nonce="<?php echo esc_attr( $setupnonce ); ?>" data-next-step="2fa-wizard-email"><?php esc_html_e( 'Change email address', 'wp-2fa' ); ?></a>
				</div>
			</div>
		<?php
	}

	/**
	 * Reconfigures the totp form
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public static function totp_configure() {

		if ( ! self::is_totp_enabled() ) {
			return;
		}
		/**
		 * Active on modal, additional attribute is required on standard HTML (check below)
		 */
		$add_step_attributes = 'active';

		/**
		 * Closing div for extra modal wrappers see lines above
		 */
		$close_div = '';

		$qr_code        = '<img class="qr-code" src="' . ( self::get_qr_code() ) . '" id="wp-2fa-totp-qrcode" />';
		$open30_wrapper = '
		<div class="mb-30 clear-both">
		';
		$open60_wrapper = '
			<div class="modal-60">
		';
		$open40_wrapper = '
			<div class="modal-40">
		';
		$close_div      = '
		</div>
		';
		$validate_nonce = wp_create_nonce( 'wp-2fa-validate-authcode' );

		?>
		<div class="step-setting-wrapper <?php echo $add_step_attributes; // phpcs:ignore ?>">
			<div class="mb-20">
				<?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( 'method_help_totp_intro', true ) ); ?>
			</div>
			<?php echo $open30_wrapper . $open40_wrapper; // phpcs:ignore ?>

			<div class="qr-code-wrapper">
				<?php echo $qr_code; // phpcs:ignore ?>
			</div>
			<?php
			echo $close_div; // phpcs:ignore
			echo $open60_wrapper; // phpcs:ignore
			?>

			<div class="radio-cells option-pill mb-0">
				<ol class="wizard-custom-counter">
					<li><?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( 'method_help_totp_step_1', true ) ); ?><span class="wizard-tooltip" data-tooltip-content="data-totp-setup-tooltip-content-wrapper">i</span></li>
					<li><?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( 'method_help_totp_step_2', true ) ); ?>
					
						<div class="app-key-wrapper">							
							<input type="text" id="app-key-input" readonly value="<?php echo esc_html( self::get_user()->get_totp_decrypted() ); ?>" class="app-key">
							<?php
							if ( is_ssl() ) {
								?>
								<span class="click-to-copy"><?php esc_html_e( 'COPY', 'wp-2fa' ); ?></span>
							<?php } ?>
						</div>				
					</li>
					<li><?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( 'method_help_totp_step_3', true ) ); ?></li>
				</ol>
			</div>
			<?php
			echo $close_div; // phpcs:ignore
			echo $close_div; // phpcs:ignore
			?>
			<div class="tooltip-content-wrapper" data-totp-setup-tooltip-content-wrapper>
				<p class="description"><?php esc_html_e( 'Click on the icon of the app that you are using for a detailed guide on how to set it up.', 'wp-2fa' ); ?></p>
				<div class="apps-wrapper">
				<?php foreach ( Authentication::get_apps() as $app ) { ?>
					<a href="https://www.wpwhitesecurity.com/support/kb/configuring-2fa-apps/#<?php echo $app['hash']; ?>" target="_blank" class="app-logo"><img src="<?php echo esc_url( WP_2FA_URL . 'dist/images/' . $app['logo'] ); // phpcs:ignore ?>"></a>
				<?php } ?>
			</div>
			</div>
			<div class="wp2fa-setup-actions">
				<button class="button wp-2fa-button-primary" name="next_step_setting" value="<?php esc_attr_e( 'I\'m Ready', 'wp-2fa' ); ?>" type="button"><?php esc_html_e( 'I\'m Ready', 'wp-2fa' ); ?></button>
			</div>
		</div>
		<div class="step-setting-wrapper" data-step-title="<?php esc_html_e( 'Verify configuration', 'wp-2fa' ); ?>">
			<div class="mb-20">
				<?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( 'method_verification_totp_pre', true ) ); ?>
			</div>
			<fieldset>
				<label for="2fa-totp-authcode">
					<input type="tel" name="wp-2fa-totp-authcode" id="wp-2fa-totp-authcode" class="input" value="" size="20" pattern="[0-9]*" placeholder="<?php esc_html_e( 'Authentication Code', 'wp-2fa' ); ?>"/>
				</label>
				<div class="verification-response"></div>
			</fieldset>
			<input type="hidden" name="wp-2fa-totp-key" value="<?php echo esc_attr( self::get_user()->get_totp_decrypted() ); ?>" />
			
			<a href="#" class="modal__btn button button-primary wp-2fa-button-primary" data-validate-authcode-ajax data-nonce="<?php echo esc_attr( $validate_nonce ); ?>"><?php esc_html_e( 'Validate & Save', 'wp-2fa' ); ?></a>
			<button class="modal__btn wp-2fa-button-secondary button button-secondary wp-2fa-button-secondary" data-close-2fa-modal aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'wp-2fa' ); ?></button>
		</div>

		<?php
	}

	/**
	 * Reconfigures email form
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public static function email_configure() {

		if ( ! self::is_mail_enabled() ) {
			return;
		}

		$setupnonce = wp_create_nonce( 'wp-2fa-send-setup-email' );

		$validate_nonce = wp_create_nonce( 'wp-2fa-validate-authcode' );
		?>
		<div class="step-setting-wrapper active">
			<div class="mb-20">
				<?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( 'method_help_hotp_intro', true ) ); ?>
			</div>
			<fieldset class="radio-cells">
			<div class="option-pill">
				<label for="use_wp_email">
					<input type="radio" name="wp_2fa_email_address" id="use_wp_email" value="<?php echo esc_attr( self::get_user()->get_2fa_wp_user()->user_email ); ?>" checked>
					<span><?php esc_html_e( 'Use my user email (', 'wp-2fa' ); ?><small><?php echo esc_attr( self::get_user()->get_2fa_wp_user()->user_email ); ?></small><?php esc_html_e( ')', 'wp-2fa' ); ?></span>
				</label>
			</div>
			<?php
			if ( Settings::get_role_or_default_setting( 'specify-email_hotp', self::get_user()->get_2fa_wp_user() ) ) {
				?>
			<div class="option-pill">
				<label for="use_custom_email">
					<input type="radio" name="wp_2fa_email_address" id="use_custom_email" value="use_custom_email">
					<span><?php esc_html_e( 'Use a different email address:', 'wp-2fa' ); ?></span>
					<input type="email" name="custom-email-address" id="custom-email-address" class="input" value="" placeholder="<?php esc_html_e( 'Email address', 'wp-2fa' ); ?>"/>
				</label>
			</div>
				<?php
			}
			?>
			</fieldset>
			<p class="description"><?php esc_html_e( 'Note: you should be able to access the mailbox of the email address to complete the following step.', 'wp-2fa' ); ?></p>
			<div class="wp2fa-setup-actions">
				<button class="button button-primary wp-2fa-button-primary" name="next_step_setting_email_verify" value="<?php esc_attr_e( 'I\'m Ready', 'wp-2fa' ); ?>" data-trigger-setup-email data-user-id="<?php echo esc_attr( self::get_user()->get_2fa_wp_user()->ID ); ?>" data-nonce="<?php echo esc_attr( $setupnonce ); ?>" type="button"><?php esc_html_e( 'I\'m Ready', 'wp-2fa' ); ?></button>

			</div>
		</div>

		<div class="step-setting-wrapper" data-step-title="<?php esc_html_e( 'Verify configuration', 'wp-2fa' ); ?>" id="2fa-wizard-email">
			<div class="mb-20">
				<?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( 'method_verification_hotp_pre', true ) ); ?>
			</div>
			<fieldset>
				<label for="2fa-email-authcode">
					<input type="tel" name="wp-2fa-email-authcode" id="wp-2fa-email-authcode" class="input" value="" size="20" pattern="[0-9]*" placeholder="<?php esc_html_e( 'Authentication Code', 'wp-2fa' ); ?>"/>
				</label>
				<div class="verification-response"></div>
			</fieldset>
			<br />
			<a href="#" class="button wp-2fa-button-primary" data-validate-authcode-ajax data-nonce="<?php echo esc_attr( $validate_nonce ); ?>"><?php esc_html_e( 'Validate & Save', 'wp-2fa' ); ?></a>
			<a href="#" class="button wp-2fa-button-primary resend-email-code" data-trigger-setup-email data-user-id="<?php echo esc_attr( self::get_user()->get_2fa_wp_user()->ID ); ?>" data-nonce="<?php echo esc_attr( $setupnonce ); ?>">
				<span class="resend-inner"><?php esc_html_e( 'Send me another code', 'wp-2fa' ); ?></span>
			</a>
			<button class="wp-2fa-button-secondary button" data-close-2fa-modal aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'wp-2fa' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Configure backup codes step
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public static function backup_codes_configure() {

		$user_type = User_Utils::determine_user_2fa_status( self::get_user()->get_2fa_wp_user() );

		$redirect = self::determine_redirect_url();

		$nonce = self::json_nonce();
		?>
		<div class="step-setting-wrapper active">
		<?php
		if ( in_array( 'user_needs_to_setup_backup_codes', $user_type, true ) ) {
			?>
			<div class="mb-20">
				<?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( 'backup_codes_intro_continue', true ) ); ?>
			</div>
		<?php } else { ?>
			<div class="mb-20">
				<?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( 'backup_codes_intro', true ) ); ?>
			</div>
			<?php } ?>
			<div class="wp2fa-setup-actions">
			<?php if ( in_array( 'user_needs_to_setup_backup_codes', $user_type, true ) ) { ?>
				<button class="button button-primary wp-2fa-button-primary" name="next_step_setting" value="<?php esc_attr_e( 'Generate backup codes', 'wp-2fa' ); ?>" data-trigger-generate-backup-codes data-nonce="<?php echo esc_attr( $nonce ); ?>">
					<?php esc_html_e( 'Generate list of backup codes', 'wp-2fa' ); ?>
				</button>
				<?php
				if ( ! empty( $redirect ) ) {
					?>
					<a href="<?php echo esc_url( $redirect ); ?>" class="button button-secondary wp-2fa-button-secondary wp-2fa-button-secondary close-first-time-wizard">
						<?php esc_html_e( 'I’ll generate them later', 'wp-2fa' ); ?>
					</a>
					<?php
				} else {
					?>
					<a href="#" class="button wp-2fa-button-secondary" data-close-2fa-modal value="<?php esc_attr_e( 'I’ll generate them later', 'wp-2fa' ); ?>">
						<?php esc_html_e( 'I’ll generate them later', 'wp-2fa' ); ?>
					</a>
				<?php } ?>
			<?php } else { ?>
				<?php
				if ( ! empty( $redirect ) ) {
					?>
					<a href="<?php echo esc_url( $redirect ); ?>" class="button button-secondary wp-2fa-button-secondary close-first-time-wizard">
					<?php esc_html_e( 'Close wizard', 'wp-2fa' ); ?>
					</a>
					<?php
				} else {
					?>
				<a href="#" class="button button-secondary wp-2fa-button-secondary" data-reload>
					<?php esc_html_e( 'Close wizard', 'wp-2fa' ); ?>
				</a>
				<?php } ?>
			<?php } ?>
			</div>
			</div>
			<?php
	}

	/**
	 * Generate backup codes step
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public static function generate_backup_codes() {
		$nonce = self::json_nonce();

		?>
		<div class="step-setting-wrapper active" data-step-title="<?php esc_html_e( 'Generate codes', 'wp-2fa' ); ?>">
			<div class="mb-20">
				<?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( 'backup_codes_generate_intro', true ) ); ?>
			</div>
			<div class="wp2fa-setup-actions">
				<button class="button button-primary" name="next_step_setting" value="<?php esc_attr_e( 'Generate backup codes', 'wp-2fa' ); ?>" data-trigger-generate-backup-codes data-nonce="<?php echo esc_attr( $nonce ); ?>">
					<?php esc_html_e( 'Generate list of backup codes', 'wp-2fa' ); ?>
				</button>
				<a href="#" class="button button-secondary wp-2fa-button-secondary" value="<?php esc_attr_e( 'I’ll generate them later', 'wp-2fa' ); ?>" data-close-2fa-modal="">
					<?php esc_html_e( 'I’ll generate them later', 'wp-2fa' ); ?>
				</a>
			</div>
		</div>

		<?php
	}

	/**
	 * Creates link for generating the backup codes
	 *
	 * @since 1.7
	 *
	 * @return string
	 */
	public static function get_generate_codes_link() {
		$nonce = self::json_nonce();

		$label = __( 'Backup 2FA methods:', 'wp-2fa' );

		return $label . '</th><td><a href="#" class="button button-primary remove-2fa" data-trigger-generate-backup-codes  data-nonce="' . esc_attr( $nonce ) . '" onclick="MicroModal.show( \'configure-2fa-backup-codes\' );">' . __( 'Generate list of backup codes', 'wp-2fa' ) . '</a>';
	}

	/**
	 * Shows the wrapper where backup code are generated and showed to the user
	 *
	 * @param boolean $backup_only - If we want to show backup window only - sets the class of the div to active.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public static function generated_backup_codes( $backup_only = false ) {
		$nonce = self::json_nonce();

		$redirect = self::determine_redirect_url();

		?>
		<div class="step-setting-wrapper align-center<?php echo ( $backup_only ) ? ' active' : ''; ?>" data-step-title="<?php esc_html_e( 'Your backup codes', 'wp-2fa' ); ?>">
			<div class="mb-20">
				<?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( 'backup_codes_generated', true ) ); ?>
			</div>
			<div class="backup-key-wrapper">
				<textarea id="backup-codes-wrapper" readonly rows="4" cols="50" class="app-key"></textarea>
			</div>
			<div class="wp2fa-setup-actions">
				<?php if ( is_ssl() ) { ?>
					<button class="button button-primary wp-2fa-button-primary" type="submit" value="<?php esc_attr_e( 'Download', 'wp-2fa' ); ?>" data-trigger-backup-code-copy>
						<?php esc_html_e( 'Copy', 'wp-2fa' ); ?>
					</button>
				<?php } else { ?>
					<button class="button button-primary wp-2fa-button-primary" type="submit" value="<?php esc_attr_e( 'Download', 'wp-2fa' ); ?>" data-trigger-backup-code-download data-user="<?php echo esc_attr( self::get_user()->get_2fa_wp_user()->display_name ); ?>" data-website-url="<?php echo esc_attr( get_home_url() ); ?>">
						<?php esc_html_e( 'Download', 'wp-2fa' ); ?>
					</button>
				<?php } ?>
				<button class="button button-primary wp-2fa-button-primary" type="submit" value="<?php esc_attr_e( 'Print', 'wp-2fa' ); ?>" data-trigger-print data-nonce="<?php echo esc_attr( $nonce ); ?>" data-user-id="<?php echo esc_attr( self::get_user()->get_2fa_wp_user()->display_name ); ?>" data-website-url="<?php echo esc_attr( get_home_url() ); ?>">
					<?php esc_html_e( 'Print', 'wp-2fa' ); ?>
				</button>
				<?php
				if ( ! empty( $redirect ) ) {
					?>
					<a href="<?php echo esc_url( $redirect ); ?>" class="button button-secondary wp-2fa-button-secondary wp-2fa-button-secondary close-first-time-wizard">
					<?php esc_html_e( 'I\'m ready, close the wizard', 'wp-2fa' ); ?>
					</a>
					<?php
				} else {
					?>
				<button class="button button-secondary wp-2fa-button-secondary wp-2fa-button-secondary" type="submit" data-close-2fa-modal-and-refresh>
					<?php esc_html_e( 'I\'m ready, close the wizard', 'wp-2fa' ); ?>
				</button>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Final step for congratulating the user
	 *
	 * @since 1.7
	 *
	 * @param boolean $setup_wizard - Is that a call from setup wizard or not.
	 *
	 * @return void
	 */
	public static function congratulations_step( $setup_wizard = false ) {

		if ( $setup_wizard ) {
			self::congratulations_step_plugin_wizard();
			return;
		}
		?>

		<div class="step-setting-wrapper active">
		<div class="mb-20">
			<?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( 'no_further_action', true ) ); ?>
		</div>
		<div class="wp2fa-setup-actions">
			<button class="modal__btn wp-2fa-button-secondary button" data-close-2fa-modal aria-label="Close this dialog window"><?php esc_html_e( 'Close wizard', 'wp-2fa' ); ?></button>
		</div>
		</div>
		<?php
	}

	/**
	 * Final step for congratulating the user
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public static function congratulations_step_plugin_wizard() {
		$redirect    = ( '' !== self::determine_redirect_url() ) ? self::determine_redirect_url() : get_edit_profile_url( self::get_user()->get_2fa_wp_user()->ID );
		$slide_title = ( User_Helper::is_excluded( self::get_user()->get_2fa_wp_user()->ID ) ) ? esc_html__( 'Congratulations.', 'wp-2fa' ) : esc_html__( 'Congratulations, you\'re almost there...', 'wp-2fa' );
		?>
		<h3><?php echo \esc_html( $slide_title ); ?></h3>
		<p><?php esc_html_e( 'Great job, the plugin and 2FA policies are now configured. You can always change the plugin settings and 2FA policies at a later stage from the WP 2FA entry in the WordPress menu.', 'wp-2fa' ); ?></p>

			<?php
			if ( User_Helper::is_excluded( self::get_user()->get_2fa_wp_user()->ID ) ) {
				?>
		<div class="wp2fa-setup-actions">
			<a href="<?php echo esc_url( $redirect ); ?>" class="button button-secondary wp-2fa-button-secondary close-first-time-wizard">
					<?php esc_html_e( 'Close wizard', 'wp-2fa' ); ?>
			</a>
		</div>
				<?php
			} else {
				?>
		<p><?php esc_html_e( 'Now you need to configure 2FA for your own user account. You can do this now (recommended) or later.', 'wp-2fa' ); ?></p>
		<div class="wp2fa-setup-actions">
			<a href="<?php echo esc_url( Settings::get_setup_page_link() ); ?>" class="button button-secondary wp-2fa-button-secondary">
				<?php esc_html_e( 'Configure 2FA now', 'wp-2fa' ); ?>
			</a>
			<a href="<?php echo esc_url( Settings::get_settings_page_link() ); ?>" class="button button-secondary wp-2fa-button-secondary close-first-time-wizard">
					<?php esc_html_e( 'Close wizard & configure 2FA later', 'wp-2fa' ); ?>
			</a>
		</div>
			<?php } ?>
		<?php
	}

	/**
	 * Shows the methods in the modal wizard, so the user can choose from the available ones
	 *
	 * @return void
	 */
	public static function show_modal_methods() {
		if ( self::is_totp_enabled() ) {
			?>
			<div class="wizard-step" id="2fa-wizard-totp">
				<fieldset>
					<?php self::totp_configure(); ?>
				</fieldset>
			</div>
			<?php
		}
		if ( self::is_mail_enabled() ) {
			?>
			<div class="wizard-step" id="2fa-wizard-email">
				<fieldset>
					<?php self::email_configure(); ?>
				</fieldset>
			</div>
			<?php
		}

		/**
		 * Add an option for external providers to add their own modal methods options.
		 *
		 * @since 2.0.0
		 */
		do_action( WP_2FA_PREFIX . 'modal_methods' );
	}

	/**
	 * Gets the current user
	 *
	 * @since 1.7
	 *
	 * @return \WP2FA\Admin\User
	 */
	public static function get_user() {
		if ( null === self::$user ) {
			self::$user = User::get_instance();
		}

		return self::$user;
	}

	/**
	 * Choosing backup method step
	 * When there are more than one backup method - give the user ability to choose one
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function choose_backup_method() {
		$redirect = self::determine_redirect_url();
		?>
		<div class="wizard-step" id="2fa-wizard-backup-methods">
			<div class="option-pill mb-20">
				<?php echo wp_kses_post( WP2FA::get_wp2fa_white_label_setting( 'backup_codes_intro_multi', true ) ); ?>
			</div>
			<div class="radio-cells">
			<?php
			$backup_methods = Settings::get_backup_methods();

			$i = 0;
			foreach ( $backup_methods as $method_name => $method ) {
				$checked = '';
				if ( ! $i ) {
					$checked = ' checked="checked"';
				}
				$i = 1;
				?>
				<div class="option-pill"><label for="<?php echo \esc_attr( $method_name ); ?>"><input name="backup_method_select" data-step="<?php echo \esc_attr( $method['wizard-step'] ); ?>" type="radio" id="<?php echo \esc_attr( $method_name ); ?>" <?php echo $checked; ?>><?php echo $method['button_name']; // phpcs:ignore ?></label><br /></div>
				<?php
			}
			?>
			</div>
			<div class="wp2fa-setup-actions">
				<a id="select-backup-method" href="<?php echo esc_url( Settings::get_setup_page_link() ); ?>" class="button button-primary wp-2fa-button-primary">
					<?php esc_html_e( 'Configure backup 2FA method', 'wp-2fa' ); ?>
				</a>
				<a href="<?php echo esc_url( $redirect ); ?>" class="button button-secondary wp-2fa-button-secondary close-first-time-wizard"  <?php echo ( ( '' === trim( $redirect ) ) ? 'data-close-it=""' : '' ); ?>  >
						<?php esc_html_e( 'Close wizard & configure 2FA later', 'wp-2fa' ); ?>
				</a>
				<script>
					const closeButton = document.querySelector('[data-close-it]');

					closeButton.addEventListener('click', (event) => {
						event.preventDefault();
						let url = new URL( location.href );
						let params = new URLSearchParams( url.search );
						params.delete('show'); 
						location.replace( `${location.pathname}?${params}` );
					});
				</script>
			</div>
		</div>
		<?php
	}

	/**
	 * Determines the redirect url for the user
	 *
	 * @return string
	 *
	 * @since 2.0.0
	 */
	public static function determine_redirect_url(): string {
		if ( null === self::$redirect_url ) {
			$redirect_page      = Settings::get_role_or_default_setting( 'redirect-user-custom-page-global', self::get_user()->get_2fa_wp_user() );
			self::$redirect_url = ( '' !== trim( $redirect_page ) ) ? \trailingslashit( get_site_url() ) . $redirect_page : '';

			if (
				'yes' === Settings::get_role_or_default_setting( 'create-custom-user-page', self::get_user()->get_2fa_wp_user() ) ||
				'yes' === Settings::get_role_or_default_setting( 'create-custom-user-page' ) ) {
				if (
					'' !== trim( Settings::get_role_or_default_setting( 'redirect-user-custom-page', self::get_user()->get_2fa_wp_user() ) ) ||
					'' !== trim( Settings::get_role_or_default_setting( 'redirect-user-custom-page' ) ) ) {
					if ( 'yes' === Settings::get_role_or_default_setting( 'create-custom-user-page', self::get_user()->get_2fa_wp_user() ) ) {
						self::$redirect_url = trailingslashit( get_site_url() ) . Settings::get_role_or_default_setting( 'redirect-user-custom-page', self::get_user()->get_2fa_wp_user() );
					} else {
						self::$redirect_url = trailingslashit( get_site_url() ) . Settings::get_role_or_default_setting( 'redirect-user-custom-page' );
					}
				}
			}
		}

		return self::$redirect_url;
	}

	/**
	 * Generates nonce for JSON calls
	 *
	 * @since 1.7
	 *
	 * @return string
	 */
	protected static function json_nonce() {
		if ( null === self::$json_nonce ) {
			self::$json_nonce = wp_create_nonce( 'wp-2fa-backup-codes-generate-json-' . self::get_user()->get_2fa_wp_user()->ID );
		}

		return self::$json_nonce;
	}

	/**
	 * Returns the status of the totp method (enabled | disabled)
	 *
	 * @since 1.7
	 *
	 * @return boolean
	 */
	private static function is_totp_enabled(): bool {
		if ( null === self::$totp_enabled ) {
			self::$totp_enabled = empty( Settings::get_role_or_default_setting( 'enable_totp', 'current' ) ) ? false : true;
		}

		return self::$totp_enabled;
	}

	/**
	 * Returns the status of the mail method (enabled | disabled)
	 *
	 * @since 1.7
	 *
	 * @return boolean
	 */
	private static function is_mail_enabled(): bool {
		if ( null === self::$email_enabled ) {
			self::$email_enabled = empty( Settings::get_role_or_default_setting( 'enable_email', 'current' ) ) ? false : true;
		}

		return self::$email_enabled;
	}

	/**
	 * Retrieves the QR code
	 *
	 * @since 1.7
	 *
	 * @return string
	 */
	private static function get_qr_code(): string {

		// Setup site information, used when generating our QR code.
		$site_name = get_bloginfo( 'name', 'display' );
		/**
		 * Changing the title of the login screen for the TOTP method.
		 *
		 * @param string $title - The default title.
		 * @param \WP_User $user - The WP user.
		 *
		 * @since 2.0.0
		 */
		$totp_title = apply_filters(
			WP_2FA_PREFIX . 'totp_title',
			$site_name . ':' . self::get_user()->get_2fa_wp_user()->user_login,
			self::get_user()->get_2fa_wp_user()
		);

		return Authentication::get_google_qr_code( $totp_title, self::get_user()->get_totp_key(), $site_name );
	}
}
