<?php
/**
 * Email settings class.
 *
 * @package    wp2fa
 * @subpackage settings-pages
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Admin\SettingsPages;

use WP2FA\Email_Template;
use \WP2FA\WP2FA as WP2FA;
use WP2FA\Admin\Controllers\Settings;
use WP2FA\Utils\Settings_Utils as Settings_Utils;

/**
 * Email settings tab
 */
if ( ! class_exists( '\WP2FA\Admin\SettingsPages\Settings_Page_Email' ) ) {
	/**
	 * Settings_Page_Email - Class for handling email settings
	 *
	 * @since 2.0.0
	 */
	class Settings_Page_Email {

		/**
		 * Render the settings
		 *
		 * @return void
		 *
		 * @since 2.0.0
		 */
		public function render() {
			settings_fields( WP_2FA_EMAIL_SETTINGS_NAME );
			$this->email_from_settings();
			$this->email_settings();
			submit_button( esc_html__( 'Save email settings and templates', 'wp-2fa' ) );
		}

		/**
		 * Handle saving email options to the network main site options.
		 *
		 * @return void
		 *
		 * @since 2.0.0
		 *
		 * @SuppressWarnings(PHPMD.ExitExpressions)
		 */
		public function update_wp2fa_network_options() {
			if ( isset( $_POST['email_from_setting'] ) ) { // phpcs:ignore
				$options = $this->validate_and_sanitize( wp_unslash( $_POST ) ); // phpcs:ignore

				if ( isset( $_POST['email_from_setting'] ) && 'use-custom-email' === $_POST['email_from_setting'] && isset( $_POST['custom_from_display_name'] ) && empty( $_POST['custom_from_display_name'] ) || isset( $_POST['email_from_setting'] ) && 'use-custom-email' === $_POST['email_from_setting'] && isset( $_POST['custom_from_email_address'] ) && empty( $_POST['custom_from_email_address'] ) ) { // phpcs:ignore
					// redirect back to our options page.
					wp_safe_redirect(
                        add_query_arg(
                            array(
								'page' => 'wp-2fa-settings',
								'wp_2fa_network_settings_updated' => 'false',
								'tab'  => 'email-settings',
                            ),
                            network_admin_url( 'admin.php' )
                        )
					);
					exit;
				}

				Settings_Utils::update_option( WP_2FA_EMAIL_SETTINGS_NAME, $options );
			}

			// redirect back to our options page.
			wp_safe_redirect(
                add_query_arg(
                    array(
						'page'                            => 'wp-2fa-settings',
						'wp_2fa_network_settings_updated' => 'true',
						'tab'                             => 'email-settings',
                    ),
                    network_admin_url( 'admin.php' )
                )
			);
			exit;
		}

		/**
		 * Email settings
		 *
		 * @return void
		 *
		 * @since 2.0.0
		 */
		private function email_from_settings() {
			?>
		<h3><?php esc_html_e( 'Which email address should the plugin use as a from address?', 'wp-2fa' ); ?></h3>
		<p class="description">
			<?php esc_html_e( 'Use these settings to customize the "from" name and email address for all correspondence sent from our plugin.', 'wp-2fa' ); ?>
		</p>
		<table class="form-table">
			<tbody>
				<tr>
					<th><label for="2fa-method"><?php esc_html_e( 'From email & name', 'wp-2fa' ); ?></label>
					</th>
					<td>
						<fieldset class="contains-hidden-inputs">
							<label for="use-defaults">
								<input type="radio" name="email_from_setting" id="use-defaults" value="use-defaults"
								<?php checked( WP2FA::get_wp2fa_email_templates( 'email_from_setting' ), 'use-defaults' ); ?>
								>
							<span><?php esc_html_e( 'Use the email address from the WordPress general settings.', 'wp-2fa' ); ?></span>
							</label>

							<br/>
							<label for="use-custom-email">
								<input type="radio" name="email_from_setting" id="use-custom-email" value="use-custom-email"
								<?php checked( WP2FA::get_wp2fa_email_templates( 'email_from_setting' ), 'use-custom-email' ); ?>
								data-unhide-when-checked=".custom-from-inputs">
								<span><?php esc_html_e( 'Use another email address', 'wp-2fa' ); ?></span>
							</label>
							<fieldset class="hidden custom-from-inputs">
								<br/>
								<span><?php esc_html_e( 'Email Address:', 'wp-2fa' ); ?></span> <input type="text" id="custom_from_email_address" name="custom_from_email_address" value="<?php echo esc_attr( WP2FA::get_wp2fa_email_templates( 'custom_from_email_address' ) ); ?>"><br><br>
								<span><?php esc_html_e( 'Display Name:', 'wp-2fa' ); ?></span> <input type="text" id="custom_from_display_name" name="custom_from_display_name" value="<?php echo esc_attr( WP2FA::get_wp2fa_email_templates( 'custom_from_display_name' ) ); ?>">
							</fieldset>

						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>

		<br>
		<hr>

		<h3><?php esc_html_e( 'Email delivery test', 'wp-2fa' ); ?></h3>
		<p class="description">
			<?php esc_html_e( 'The plugin sends emails with one-time codes, blocked account notifications and more. Use the button below to confirm the plugin can successfully send emails.', 'wp-2fa' ); ?>
		</p>
		<p>
			<button type="button" name="test_email_config_test"
					class="button js-button-test-email-trigger"
					data-email-id="config_test"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp-2fa-email-test-config_test' ) ); ?>">
				<?php esc_html_e( 'Test email delivery', 'wp-2fa' ); ?>
			</button>
		</p>

		<br>
		<hr>

			<?php
		}

		/**
		 * Creates the email notification definitions.
		 *
		 * @return Email_Template[]
		 *
		 * @since 2.0.0
		 */
		public function get_email_notification_definitions() {
			$result = array(
				new Email_Template(
					'login_code',
					esc_html__( 'Login code email', 'wp-2fa' ),
					esc_html__( 'This is the email sent to a user when a login code is required.', 'wp-2fa' )
				),
				new Email_Template(
					'account_locked',
					esc_html__( 'User account locked email', 'wp-2fa' ),
					esc_html__( 'This is the email sent to a user upon grace period expiry.', 'wp-2fa' )
				),
				new Email_Template(
					'account_unlocked',
					esc_html__( 'User account unlocked email', 'wp-2fa' ),
					esc_html__( 'This is the email sent to a user when the user\'s account has been unlocked.', 'wp-2fa' )
				),
			);

			/**
			 * Add an option for external providers to implement their own email template settings for the settings tab.
			 *
			 * @param array $result - The array with all the email templates.
			 *
			 * @since 2.0.0
			 */
			$result = apply_filters( WP_2FA_PREFIX . 'email_notification_definitions', $result );

			if ( count( $result ) > 3 ) {
				$result[0]->set_can_be_toggled( false );
				$result[1]->set_can_be_toggled( false );
				$result[2]->set_email_content_id( 'user_account_locked' );
				$result[3]->set_email_content_id( 'user_account_unlocked' );
			} else {
				$result[0]->set_can_be_toggled( false );
				$result[1]->set_email_content_id( 'user_account_locked' );
				$result[2]->set_email_content_id( 'user_account_unlocked' );
			}
			return $result;
		}

		/**
		 * Validate email templates before saving
		 *
		 * @since 2.0.0
		 *
		 * @SuppressWarnings(PHPMD.ExitExpressions)
		 */
		public function validate_and_sanitize() {

			// Bail if user doesn't have permissions to be here.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( empty( $_POST ) || ! isset( $_POST['_wpnonce'] ) || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], WP_2FA_PREFIX . 'email_settings-options' ) && ! wp_verify_nonce( $_POST['_wpnonce'], WP_2FA_PREFIX . 'settings-options' ) || ! wp_verify_nonce( $_POST['_wpnonce'], WP_2FA_PREFIX . 'email_settings-options' ) && ! wp_verify_nonce( $_POST['_wpnonce'], WP_2FA_PREFIX . 'settings-options' ) ) { // phpcs:ignore
				die( esc_html__( 'Nonce verification failed.', 'wp-2fa' ) );
			}

			$output = array();

			if ( isset( $_POST['email_from_setting'] ) && 'use-defaults' === $_POST['email_from_setting'] || isset( $_POST['email_from_setting'] ) && 'use-custom-email' === $_POST['email_from_setting'] ) {
				$output['email_from_setting'] = sanitize_text_field( wp_unslash( $_POST['email_from_setting'] ) );
			}

			if ( isset( $_POST['email_from_setting'] ) && 'use-custom-email' === $_POST['email_from_setting'] && isset( $_POST['custom_from_email_address'] ) && empty( $_POST['custom_from_email_address'] ) ) {
				add_settings_error(
                    WP_2FA_SETTINGS_NAME,
                    esc_attr( 'email_from_settings_error' ),
                    esc_html__( 'Please provide an email address', 'wp-2fa' ),
                    'error'
				);
				$output['custom_from_email_address'] = '';
			}

			if ( isset( $_POST['email_from_setting'] ) && 'use-custom-email' === $_POST['email_from_setting'] && isset( $_POST['custom_from_display_name'] ) && empty( $_POST['custom_from_display_name'] ) ) {
				add_settings_error(
                    WP_2FA_SETTINGS_NAME,
                    esc_attr( 'display_name_settings_error' ),
                    esc_html__( 'Please provide a display name.', 'wp-2fa' ),
                    'error'
				);
				$output['custom_from_email_address'] = '';
			}

			if ( isset( $_POST['custom_from_email_address'] ) && ! empty( $_POST['custom_from_email_address'] ) ) {
				if ( ! filter_var( wp_unslash( $_POST['custom_from_email_address'] ), FILTER_VALIDATE_EMAIL ) ) {
					add_settings_error(
                        WP_2FA_SETTINGS_NAME,
                        esc_attr( 'email_invalid_settings_error' ),
                        esc_html__( 'Please provide a valid email address. Your email address has not been updated.', 'wp-2fa' ),
                        'error'
					);
				}
				$output['custom_from_email_address'] = sanitize_email( wp_unslash( $_POST['custom_from_email_address'] ) );
			}

			if ( isset( $_POST['custom_from_display_name'] ) && ! empty( $_POST['custom_from_display_name'] ) ) {
				// Check if the string contains HTML/tags.
				preg_match( "/<\/?\w+((\s+\w+(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>/", sanitize_text_field( wp_unslash( $_POST['custom_from_display_name'] ) ), $matches );
				if ( count( $matches ) > 0 ) {
					add_settings_error(
                        WP_2FA_SETTINGS_NAME,
                        esc_attr( 'display_name_invalid_settings_error' ),
                        esc_html__( 'Please only use alphanumeric text. Your display name has not been updated.', 'wp-2fa' ),
                        'error'
					);
				} else {
					$output['custom_from_display_name'] = sanitize_text_field( wp_unslash( $_POST['custom_from_display_name'] ) );
				}
			}

			if ( isset( $_POST['login_code_email_subject'] ) ) {
				$output['login_code_email_subject'] = wp_kses_post( wp_unslash( $_POST['login_code_email_subject'] ) );
			}

			if ( isset( $_POST['login_code_email_body'] ) ) {
				$output['login_code_email_body'] = wpautop( wp_kses_post( wp_unslash( $_POST['login_code_email_body'] ) ) );
			}

			if ( isset( $_POST['user_account_locked_email_subject'] ) ) {
				$output['user_account_locked_email_subject'] = wp_kses_post( wp_unslash( $_POST['user_account_locked_email_subject'] ) );
			}

			if ( isset( $_POST['user_account_locked_email_body'] ) ) {
				$output['user_account_locked_email_body'] = wpautop( wp_kses_post( wp_unslash( $_POST['user_account_locked_email_body'] ) ) );
			}

			if ( isset( $_POST['user_account_unlocked_email_subject'] ) ) {
				$output['user_account_unlocked_email_subject'] = wp_kses_post( wp_unslash( $_POST['user_account_unlocked_email_subject'] ) );
			}

			if ( isset( $_POST['user_account_unlocked_email_body'] ) ) {
				$output['user_account_unlocked_email_body'] = wpautop( wp_kses_post( wp_unslash( $_POST['user_account_unlocked_email_body'] ) ) );
			}

			$output['send_account_locked_email'] = '';
			if ( isset( $_POST['send_account_locked_email'] ) && 'enable_account_locked_email' === $_POST['send_account_locked_email'] ) {
				$output['send_account_locked_email'] = sanitize_text_field( wp_unslash( $_POST['send_account_locked_email'] ) );
			}

			$output['send_account_unlocked_email'] = '';
			if ( isset( $_POST['send_account_unlocked_email'] ) && 'enable_account_unlocked_email' === $_POST['send_account_unlocked_email'] ) {
				$output['send_account_unlocked_email'] = sanitize_text_field( wp_unslash( $_POST['send_account_unlocked_email'] ) );
			}

			/**
			 * Filter the values we are about to store in the plugin settings.
			 *
			 * @param array $output - The output array with all the data we will store in the settings.
			 *
			 * @since 2.0.0
			 */
			$output = apply_filters( WP_2FA_PREFIX . 'filter_output_email_template_content', $output );

			// Remove duplicates from settings errors. We do this as this sanitization callback is actually fired twice, so we end up with duplicates when saving the settings for the FIRST TIME only. The issue is not present once the settings are in the DB as the sanitization wont fire again. For details on this core issue - https://core.trac.wordpress.org/ticket/21989.
			global $wp_settings_errors;
			if ( isset( $wp_settings_errors ) ) {
				$errors             = array_map( 'unserialize', array_unique( array_map( 'serialize', $wp_settings_errors ) ) );
				$wp_settings_errors = $errors; // phpcs:ignore
			}

			if ( isset( $output ) ) {
				return $output;
			} else {
				return;
			}
		}

		/**
		 * Email settings
		 *
		 * @return void
		 *
		 * @since 2.0.0
		 */
		private function email_settings() {
			$custom_user_page_id        = Settings::check_setting_in_all_roles( 'custom-user-page-id' );
			$email_template_definitions = $this->get_email_notification_definitions();
			?>
		<h1><?php esc_html_e( 'Email Templates', 'wp-2fa' ); ?></h1>
			<?php foreach ( $email_template_definitions as $email_template ) : ?>
				<?php $template_id = $email_template->get_id(); ?>
		<h3><?php echo esc_html( $email_template->get_title() ); ?></h3>
		<p class="description"><?php echo $email_template->get_description(); // phpcs:ignore ?></p>warning
		<table class="form-table">
			<tbody>
				<?php if ( $email_template->can_be_toggled() ) : ?>
				<tr>
					<th><label for="send_<?php echo esc_attr( $template_id ); ?>_email"><?php esc_html_e( 'Send this email', 'wp-2fa' ); ?></label></th>
					<td>
						<fieldset>
							<input type="checkbox" id="send_<?php echo esc_attr( $template_id ); ?>_email" name="send_<?php echo esc_attr( $template_id ); ?>_email" value="enable_<?php echo esc_attr( $template_id ); ?>_email"
							<?php checked( 'enable_' . $template_id . '_email', WP2FA::get_wp2fa_email_templates( 'send_' . $template_id . '_email' ) ); ?>
							>
							<label for="send_<?php echo esc_attr( $template_id ); ?>_email"><?php esc_html_e( 'Uncheck to disable this message.', 'wp-2fa' ); ?></label>
						</fieldset>
					</td>
				</tr>
			<?php endif; ?>
				<?php $template_id = $email_template->get_email_content_id(); ?>
				<tr>
					<th><label for="<?php echo esc_attr( $template_id ); ?>_email_subject"><?php esc_html_e( 'Email subject', 'wp-2fa' ); ?></label></th>
					<td>
						<fieldset>
							<input type="text" id="<?php echo esc_attr( $template_id ); ?>_email_subject" name="<?php echo esc_attr( $template_id ); ?>_email_subject" class="large-text" value="<?php echo esc_attr( WP2FA::get_wp2fa_email_templates( $template_id . '_email_subject' ) ); ?>">
						</fieldset>
					</td>
				</tr>
				<tr>
					<th>
						<label for="<?php echo esc_attr( $template_id ); ?>_email_body"><?php esc_html_e( 'Email body', 'wp-2fa' ); ?></label>
						</br>
						<label for="<?php echo esc_attr( $template_id ); ?>_email_tags" style="font-weight: 400;"><?php esc_html_e( 'Available template tags:', 'wp-2fa' ); ?></label>
						</br>
						</br>
						<span style="font-weight: 400;">
							{site_url}</br>
							{site_name}</br>
							{grace_period}</br>
							{user_login_name}</br>
							{user_first_name}</br>
							{user_last_name}</br>
							{user_display_name}</br>
							{login_code}</br>
							{user_ip_address}
							<?php
							if ( ! empty( $custom_user_page_id ) ) {
								echo '</br>{2fa_settings_page_url}';
							}
							?>
						</span>
					</th>
					<td>
						<fieldset>
							<?php
							$message   = WP2FA::get_wp2fa_email_templates( $template_id . '_email_body' );
							$content   = $message;
							$editor_id = $template_id . '_email_body';
							$settings  = array(
								'media_buttons' => false,
								'editor_height' => 200,
							);
							wp_editor( $content, $editor_id, $settings );
							?>
						</fieldset>
						<p>
							<button type="button" name="test_email_<?php echo esc_attr( $template_id ); ?>"
									class="button js-button-test-email-trigger"
									data-email-id="<?php echo esc_attr( $template_id ); ?>"
									data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp-2fa-email-test-' . $template_id ) ); ?>">
								<?php esc_html_e( 'Send test email', 'wp-2fa' ); ?>
							</button>
						</p>
					</td>
				</tr>
			</tbody>
		</table>

		<br>
		<hr>
		<?php endforeach; ?>
			<?php
		}
	}
}
