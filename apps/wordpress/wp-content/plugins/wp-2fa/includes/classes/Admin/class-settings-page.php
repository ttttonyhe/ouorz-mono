<?php
/**
 * Settings rendering class.
 *
 * @package    wp2fa
 * @subpackage settings
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Admin;

use \WP2FA\WP2FA as WP2FA;
use WP2FA\Utils\User_Utils;
use WP2FA\Utils\Settings_Utils;
use WP2FA\Admin\Views\Settings_Page_Render;
use WP2FA\Admin\SettingsPages\{
	Settings_Page_Policies,
	Settings_Page_General,
	Settings_Page_White_Label,
	Settings_Page_Email
};
use WP2FA\Admin\Helpers\WP_Helper;
use WP2FA\Admin\Helpers\User_Helper;
use WP2FA\Admin\Controllers\Settings;

/**
 * Class for handling settings
 */
if ( ! class_exists( '\WP2FA\Admin\Settings_Page' ) ) {
	/**
	 * Class for handling settings
	 */
	class Settings_Page {

		const TOP_MENU_SLUG = 'wp-2fa-policies';

		/**
		 * Holds the status of the backup codes functionality
		 *
		 * @var bool[]
		 */
		private static $backup_codes_enabled = array();

		/**
		 * Create admin menu entry and settings page
		 */
		public function create_settings_admin_menu() {
			// Create admin menu item.
			add_menu_page(
                esc_html__( 'WP 2FA', 'wp-2fa' ),
                esc_html__( 'WP 2FA', 'wp-2fa' ),
                'manage_options',
                self::TOP_MENU_SLUG,
                null,
			'data:image/svg+xml;base64,' . base64_encode( file_get_contents( WP_2FA_PATH . 'dist/images/wp-2fa-white-icon20x28.svg' ) ), // phpcs:ignore
                81
			);

			$settings_policies    = new Settings_Page_Policies();
			$settings_white_label = new Settings_Page_White_Label();
			$settings_email       = new Settings_Page_Email();
			$settings_render      = new Settings_Page_Render();

			add_submenu_page(
                self::TOP_MENU_SLUG,
                esc_html__( '2FA Policies', 'wp-2fa' ),
                esc_html__( '2FA Policies', 'wp-2fa' ),
                'manage_options',
                self::TOP_MENU_SLUG,
                array( $settings_policies, 'render' ),
                1
			);

			add_submenu_page(
                self::TOP_MENU_SLUG,
                esc_html__( 'WP 2FA Settings', 'wp-2fa' ),
                esc_html__( 'Settings', 'wp-2fa' ),
                'manage_options',
                'wp-2fa-settings',
                array( $settings_render, 'render' ),
                2
			);

			// Register our policy settings.
			register_setting(
                WP_2FA_POLICY_SETTINGS_NAME,
                WP_2FA_POLICY_SETTINGS_NAME,
                array( $settings_policies, 'validate_and_sanitize' )
			);

			// Register our white label settings.
			register_setting(
                WP_2FA_WHITE_LABEL_SETTINGS_NAME,
                WP_2FA_WHITE_LABEL_SETTINGS_NAME,
                array( $settings_white_label, 'validate_and_sanitize' )
			);

			// Register our settings page.
			register_setting(
                WP_2FA_SETTINGS_NAME,
                WP_2FA_SETTINGS_NAME,
                array( \WP2FA\Admin\SettingsPages\Settings_Page_General::class, 'validate_and_sanitize' )
			);

			register_setting(
                WP_2FA_EMAIL_SETTINGS_NAME,
                WP_2FA_EMAIL_SETTINGS_NAME,
                array( $settings_email, 'validate_and_sanitize' )
			);

			/**
			 * Fires after the main menu settings are registered.
			 *
			 * @param string - The menu slug.
			 * @param bool - Is that multisite install or not.
			 *
			 * @since 2.0.0
			 */
			do_action( WP_2FA_PREFIX . 'after_admin_menu_created', self::TOP_MENU_SLUG, false );
		}

		/**
		 * Create admin menu entry and settings page
		 */
		public function create_settings_admin_menu_multisite() {
			// Create admin menu item.
			add_menu_page(
                esc_html__( 'WP 2FA Settings', 'wp-2fa' ),
                esc_html__( 'WP 2FA', 'wp-2fa' ),
                'manage_options',
                self::TOP_MENU_SLUG,
                null,
			'data:image/svg+xml;base64,' . base64_encode( file_get_contents( WP_2FA_PATH . 'dist/images/wp-2fa-white-icon20x28.svg' ) ), // phpcs:ignore
                81
			);

			$settings_policies = new Settings_Page_Policies();
			add_submenu_page(
                self::TOP_MENU_SLUG,
                esc_html__( '2FA Policies', 'wp-2fa' ),
                esc_html__( '2FA Policies', 'wp-2fa' ),
                'manage_options',
                self::TOP_MENU_SLUG,
                array( $settings_policies, 'render' ),
                1
			);

			$settings_render = new Settings_Page_Render();
			add_submenu_page(
                self::TOP_MENU_SLUG,
                esc_html__( 'WP 2FA Settings', 'wp-2fa' ),
                esc_html__( 'Settings', 'wp-2fa' ),
                'manage_options',
                'wp-2fa-settings',
                array( $settings_render, 'render' ),
                2
			);

			/**
			 * Fires after the main menu settings are registered.
			 *
			 * @param string - The menu slug.
			 * @param bool - Is that multisite install or not.
			 *
			 * @since 2.0.0
			 */
			do_action( WP_2FA_PREFIX . 'after_admin_menu_created', self::TOP_MENU_SLUG, true );
		}

		/**
		 * Get all users
		 *
		 * @SuppressWarnings(PHPMD.ExitExpression)
		 */
		public function get_all_users() {
			// Die if user does not have permission to view.
			if ( ! current_user_can( 'manage_options' ) ) {
				die( 'Access Denied.' );
			}
			// Filter $_GET array for security.
			$get_array = filter_input_array( INPUT_GET );

			// Die if nonce verification failed.
			if ( ! wp_verify_nonce( sanitize_text_field( $get_array['wp_2fa_nonce'] ), 'wp-2fa-settings-nonce' ) ) {
				die( esc_html__( 'Nonce verification failed.', 'wp-2fa' ) );
			}

			$users_args = array(
				'fields' => array( 'ID', 'user_login' ),
			);
			if ( WP_Helper::is_multisite() ) {
				$users_args['blog_id'] = 0;
			}
			$users_data = User_Utils::get_all_user_ids_and_login_names( 'query', $users_args );

			// Create final array which we will fill in below.
			$users = array();

			foreach ( $users_data as $user ) {
				if ( strpos( $user['user_login'], $get_array['term'] ) !== false ) {
					array_push(
                        $users,
                        array(
							'value' => $user['user_login'],
							'label' => $user['user_login'],
                        )
					);
				}
			}

			echo wp_json_encode( $users );
			exit;
		}

		/**
		 * Get all network sites
		 *
		 * @SuppressWarnings(PHPMD.ExitExpression)
		 */
		public function get_all_network_sites() {
			// Die if user does not have permission to view.
			if ( ! current_user_can( 'manage_options' ) ) {
				die( 'Access Denied.' );
			}
			// Filter $_GET array for security.
			$get_array = filter_input_array( INPUT_GET );
			// Die if nonce verification failed.
			if ( ! wp_verify_nonce( sanitize_text_field( $get_array['wp_2fa_nonce'] ), 'wp-2fa-settings-nonce' ) ) {
				die( esc_html__( 'Nonce verification failed.', 'wp-2fa' ) );
			}
			// Fetch sites.
			$sites_found = array();

			foreach ( get_sites() as $site ) {
				$subsite_id                  = get_object_vars( $site )['blog_id'];
				$subsite_name                = get_blog_details( $subsite_id )->blogname;
				$site_details                = '';
				$site_details[ $subsite_id ] = $subsite_name;
				if ( false !== stripos( $subsite_name, $get_array['term'] ) ) {
					array_push(
                        $sites_found,
                        array(
							'label' => $subsite_id,
							'value' => $subsite_name,
                        )
					);
				}
			}
			echo wp_json_encode( $sites_found );
			exit;
		}

		/**
		 * Unlock users accounts if they have overrun grace period
		 *
		 * @param  int $user_id User ID.
		 *
		 * @SuppressWarnings(PHPMD.ExitExpression)
		 */
		public function unlock_account( $user_id ) {
			// Die if user does not have permission to view.
			if ( ! current_user_can( 'manage_options' ) ) {
				die( 'Access Denied.' );
			}

			$grace_period             = WP2FA::get_wp2fa_setting( 'grace-period' );
			$grace_period_denominator = WP2FA::get_wp2fa_setting( 'grace-period-denominator' );
			$create_a_string          = $grace_period . ' ' . $grace_period_denominator;
			// Turn that string into a time.
			$grace_expiry = strtotime( $create_a_string );

			// Filter $_GET array for security.
			$get_array = filter_input_array( INPUT_GET );
			$nonce     = sanitize_text_field( $get_array['wp_2fa_nonce'] );

			// Die if nonce verification failed.
			if ( ! wp_verify_nonce( $nonce, 'wp-2fa-unlock-account-nonce' ) ) {
				die( esc_html__( 'Nonce verification failed.', 'wp-2fa' ) );
			}

			if ( isset( $get_array['user_id'] ) ) {
				global $wpdb;
				$wpdb->query( // phpcs:ignore
                    $wpdb->prepare(
                        "
			   DELETE FROM $wpdb->usermeta
				 WHERE user_id = %d
				 AND meta_key IN ( %s, %s )
			   ",
                        array(
							intval( $get_array['user_id'] ),
							User_Helper::USER_GRACE_KEY,
							WP_2FA_PREFIX . 'locked_account_notification',
                        )
                    )
				);
				User_Helper::set_user_expiry_date( $grace_expiry, intval( $get_array['user_id'] ) );
				$this->send_account_unlocked_email( intval( $get_array['user_id'] ) );
				add_action( 'admin_notices', array( $this, 'user_unlocked_notice' ) );
			}
		}

		/**
		 * Remove user 2fa config
		 *
		 * @param  int $user_id User ID.
		 *
		 * @SuppressWarnings(PHPMD.ExitExpression)
		 */
		public function remove_user_2fa( $user_id ) {
			// Filter $_GET array for security.
			$get_array = filter_input_array( INPUT_GET );
			$nonce     = sanitize_text_field( $get_array['wp_2fa_nonce'] );

			if ( ! wp_verify_nonce( $nonce, 'wp-2fa-remove-user-2fa-nonce' ) ) {
				die( esc_html__( 'Nonce verification failed.', 'wp-2fa' ) );
			}

			if ( isset( $get_array['user_id'] ) ) {
				$user_id = intval( $get_array['user_id'] );

				if ( ! current_user_can( 'manage_options' ) && get_current_user_id() !== $user_id ) {
					return;
				}

				User_Helper::remove_2fa_for_user( $user_id );

				if ( isset( $get_array['admin_reset'] ) ) {
					add_action( 'admin_notices', array( $this, 'admin_deleted_2fa_notice' ) );
				} else {
					add_action( 'admin_notices', array( $this, 'user_deleted_2fa_notice' ) );
				}
			}
		}

		/**
		 * Send account unlocked notification via email.
		 *
		 * @param int $user_id user ID.
		 *
		 * @return boolean
		 */
		public static function send_account_unlocked_email( $user_id ) {
			// Bail if the user has not enabled this email.
			if ( 'enable_account_unlocked_email' !== WP2FA::get_wp2fa_email_templates( 'send_account_unlocked_email' ) ) {
				return false;
			}

			// Grab user data.
			$user = get_userdata( $user_id );
			// Grab user email.
			$email = $user->user_email;
			// Setup the email contents.
			$subject = wp_strip_all_tags( WP2FA::replace_email_strings( WP2FA::get_wp2fa_email_templates( 'user_account_unlocked_email_subject' ) ) );
			$message = wpautop( WP2FA::replace_email_strings( WP2FA::get_wp2fa_email_templates( 'user_account_unlocked_email_body' ), $user_id ) );

			return self::send_email( $email, $subject, $message );
		}

		/**
		 * Hide settings menu item
		 */
		public function hide_settings() {
			$user = wp_get_current_user();

			// Check we have a user before doing anything else.
			if ( is_a( $user, '\WP_User' ) ) {
				if ( ! empty( WP2FA::get_wp2fa_setting( '2fa_settings_last_updated_by' ) ) ) {
					$main_user = (int) WP2FA::get_wp2fa_setting( '2fa_settings_last_updated_by' );
				} else {
					$main_user = get_current_user_id();
				}
				if ( ! empty( WP2FA::get_wp2fa_general_setting( 'limit_access' ) ) && $user->ID !== $main_user ) {
					// Remove admin menu item.
					remove_submenu_page( 'options-general.php', self::TOP_MENU_SLUG );
				}
			}
		}

		/**
		 * Add unlock user link to user actions.
		 *
		 * @param array $links Default row content.
		 *
		 * @return array
		 * @throws \Freemius_Exception - freemius exception.
		 */
		public function add_plugin_action_links( $links ) {
			// add link to the external free trial page in free version and also in premium version if license is not active.
			if ( ! function_exists( 'wp2fa_freemius' ) || ! wp2fa_freemius()->has_active_valid_license() ) {
				$trial_link = 'https://wp2fa.io/get-wp-2fa-premium-trial/?utm_source=plugin&utm_medium=referral&utm_campaign=WP2FA';
				$links      = array_merge(
                    array(
						'<a style="font-weight:bold" href="' . $trial_link . '" target="_blank">' . __( 'Free 14-day Premium Trial', 'wp-2fa' ) . '</a>',
                    ),
                    $links
				);
			}

			// add link to the plugin settings page.
			$url   = Settings::get_settings_page_link();
			$links = array_merge(
                array(
					'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Configure 2FA Settings', 'wp-2fa' ) . '</a>',
                ),
                $links
			);

			return $links;
		}

		/**
		 * User unlocked notice.
		 */
		public function user_unlocked_notice() {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'User account successfully unlocked. User can login again.', 'wp-2fa' ); ?></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'wp-2fa' ); ?></span>
				</button>
			</div>
			<?php
		}

		/**
		 * User deleted 2FA settings notification
		 */
		public function user_deleted_2fa_notice() {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Your 2FA settings have been removed.', 'wp-2fa' ); ?></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'wp-2fa' ); ?></span>
				</button>
			</div>
			<?php
		}

		/**
		 * Admin deleted user 2FA settings notification
		 */
		public function admin_deleted_2fa_notice() {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'User 2FA settings have been removed.', 'wp-2fa' ); ?></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'wp-2fa' ); ?></span>
				</button>
			</div>
			<?php
		}

		/**
		 * Updates options for multisite
		 *
		 * @return void
		 *
		 * @since 2.0.0
		 */
		public function update_wp2fa_network_options() {

			$settings_policies = new Settings_Page_Policies();

			$settings_policies->update_wp2fa_network_options();

			Settings_Page_General::update_wp2fa_network_options();

			$settings_white_label = new Settings_Page_White_Label();

			$settings_white_label->update_wp2fa_network_options();

			/**
			 * Gives the ability for extensions to set their settings in the plugin.
			 *
			 * @since 2.2.0
			 */
			do_action( WP_2FA_PREFIX . 'update_network_settings' );
		}

		/**
		 * Handle saving email options to the network main site options.
		 */
		public function update_wp2fa_network_email_options() {
			$settings_email = new Settings_Page_Email();

			$settings_email->update_wp2fa_network_options();
		}

		/**
		 * These are used instead of add_settings_error which in a network site. Used to show if settings have been updated or failed.
		 */
		public function settings_saved_network_admin_notice() {
			if ( isset( $_GET['wp_2fa_network_settings_updated'] ) && 'true' === $_GET['wp_2fa_network_settings_updated'] ) { // phpcs:ignore
				?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( '2FA Settings Updated', 'wp-2fa' ); ?></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'wp-2fa' ); ?></span>
				</button>
			</div>
				<?php
			}
			if ( isset( $_GET['wp_2fa_network_settings_updated'] ) && 'false' === $_GET['wp_2fa_network_settings_updated'] ) { // phpcs:ignore
				?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e( 'Please ensure both custom email address and display name are provided.', 'wp-2fa' ); ?></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'wp-2fa' ); ?></span>
				</button>
			</div>
				<?php
			}
			if ( isset( $_GET['wp_2fa_network_settings_error'] ) ) { // phpcs:ignore
				?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo \esc_attr( \esc_url_raw( \urldecode_deep( \wp_unslash( $_GET['wp_2fa_network_settings_error'] ) ) ) ); // phpcs:ignore ?></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'wp-2fa' ); ?></span>
				</button>
			</div>
				<?php
			}
		}

		/**
		 * These are used instead of add_settings_error which in a network site. Used to show if settings have been updated or failed.
		 *
		 * @return void
		 *
		 * @since 2.0.0
		 */
		public function settings_saved_admin_notice() {
			if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) { // phpcs:ignore
				$wp_settings_errors = get_settings_errors();

				if ( count( $wp_settings_errors ) ) {
					foreach ( $wp_settings_errors as $error ) {
						?>
			<div class="notice notice-<?php echo \esc_attr( $error['type'] ); ?> is-dismissible">
				<p><?php echo \esc_html( $error['message'] ); ?></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'wp-2fa' ); ?></span>
				</button>
			</div>
						<?php
					}
				} else {
					?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( '2FA Settings Updated', 'wp-2fa' ); ?></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'wp-2fa' ); ?></span>
				</button>
			</div>
					<?php
				}
			}
			if ( isset( $_GET['settings-updated'] ) && 'false' === $_GET['settings-updated'] ) { // phpcs:ignore
				?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e( 'Please ensure both custom email address and display name are provided.', 'wp-2fa' ); ?></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'wp-2fa' ); ?></span>
				</button>
			</div>
				<?php
			}
			if ( isset( $_GET['settings_error'] ) ) { // phpcs:ignore
				?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo \esc_attr( \esc_url_raw( \urldecode_deep( \wp_unslash( $_GET['settings_error'] ) ) ) ); // phpcs:ignore ?></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'wp-2fa' ); ?></span>
				</button>
			</div>
				<?php
			}
		}

		/**
		 * Add our custom state to our created page.
		 *
		 * @param array   $post_states - array with the post states.
		 * @param WP_Post $post - the WP post.
		 *
		 * @return array
		 */
		public function add_display_post_states( $post_states, $post ) {
			if ( ! empty( WP2FA::get_wp2fa_setting( 'custom-user-page-id' ) ) ) {
				if ( WP2FA::get_wp2fa_setting( 'custom-user-page-id' ) === $post->ID ) {
					$post_states['wp_2fa_page_for_user'] = __( 'WP 2FA User Page', 'wp-2fa' );
				}
			}

			return $post_states;
		}

		/**
		 * Handles sending of an email. It sets necessary header such as content type and custom from email address and name.
		 *
		 * @param string $recipient_email Email address to send message to.
		 * @param string $subject Email subject.
		 * @param string $message Message contents.
		 *
		 * @return bool Whether the email contents were sent successfully.
		 */
		public static function send_email( $recipient_email, $subject, $message ) {

			// Specify our desired headers.
			$headers = 'Content-type: text/html;charset=utf-8' . "\r\n";

			if ( 'use-custom-email' === WP2FA::get_wp2fa_email_templates( 'email_from_setting' ) ) {
				$headers .= 'From: ' . WP2FA::get_wp2fa_email_templates( 'custom_from_display_name' ) . ' <' . WP2FA::get_wp2fa_email_templates( 'custom_from_email_address' ) . '>' . "\r\n";
			} else {
				$headers .= 'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n";
			}

			// Fire our email.
			return wp_mail( $recipient_email, $subject, $message, $headers );

		}

		/**
		 * Turns user roles data in any form and shape to an array of strings.
		 *
		 * @param mixed $value User role names (slugs) as raw value.
		 *
		 * @return string[] List of user role names (slugs).
		 */
		public static function extract_roles_from_input( $value ) {
			if ( is_array( $value ) ) {
				return $value;
			}

			if ( is_string( $value ) && ! empty( $value ) ) {
				return explode( ',', $value );
			}

			return array();
		}

		/**
		 * Determine if any BG processes are currently running.
		 *
		 * @return int|false Number of jobs.
		 */
		public function get_current_number_of_active_bg_processes() {
			global $wpdb;

			$bg_jobs = $wpdb->get_results( // phpcs:ignore
                "SELECT option_value FROM $wpdb->options
				WHERE option_name LIKE '%_2fa_bg_%'"
			);

			return count( $bg_jobs );
		}

		/**
		 * Checks if the backup codes option is enabled for the role
		 *
		 * @param string $role - The role name.
		 *
		 * @return bool
		 */
		public static function are_backup_codes_enabled( $role = 'global' ) {

			$role = ( is_null( $role ) || empty( $role ) ) ? 'global' : $role;

			if ( ! isset( self::$backup_codes_enabled[ $role ] ) ) {
				self::$backup_codes_enabled[ $role ] = false;

				if ( 'global' === $role ) {
					$setting_value = Settings::get_role_or_default_setting( 'backup_codes_enabled' );
				} else {
					$setting_value = Settings::get_role_or_default_setting( 'backup_codes_enabled', 'current', $role );
				}
				self::$backup_codes_enabled[ $role ] = Settings_Utils::string_to_bool( $setting_value );
			}

			return self::$backup_codes_enabled[ $role ];
		}
	}
}
