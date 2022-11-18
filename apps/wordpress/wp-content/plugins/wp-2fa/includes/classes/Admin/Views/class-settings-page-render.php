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
use WP2FA\Admin\Helpers\WP_Helper;

if ( ! class_exists( '\WP2FA\Admin\Views\Settings_Page_Render' ) ) {
	/**
	 * Settings_Page_Render - Class for rendering the plugin settings settings
	 *
	 * @since 2.0.0
	 */
	class Settings_Page_Render {

		/**
		 * Render the settings
		 */
		public function render() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$user = wp_get_current_user();
			if ( ! empty( WP2FA::get_wp2fa_setting( '2fa_settings_last_updated_by' ) ) ) {
				$main_user = (int) WP2FA::get_wp2fa_setting( '2fa_settings_last_updated_by' );
			} else {
				$main_user = get_current_user_id();
			}
			?>

		<div class="wrap wp-2fa-settings-wrapper wp2fa-form-styles">
			<h2><?php esc_html_e( 'WP 2FA Settings', 'wp-2fa' ); ?></h2>
			<hr>
			<?php if ( ! empty( WP2FA::get_wp2fa_general_setting( 'limit_access' ) ) && $main_user !== $user->ID ) { ?>
				<?php
				echo esc_html__( 'These settings have been disabled by your site administrator, please contact them for further assistance.', 'wp-2fa' );
				?>
			<?php } else { ?>
				<?php
					/**
					 * Fires before the plugin settings rendering.
					 *
					 * @since 2.0.0
					 */
					do_action( WP_2FA_PREFIX . 'before_plugin_settings' );
				?>
				<div class="nav-tab-wrapper">
					<?php
					$settings = self::settings_array();

					/**
					 * Stores the default settings key, so there is no need to walk the entire array again to extract that value
					 */
					$default_settings_key = 'generic-settings';

					foreach ( $settings as $setting_tab => $setting_values ) {
						$active_class = '';
						if ( ! isset( $_REQUEST['tab'] ) && $setting_values['default'] ) { // phpcs:ignore
							$active_class         = 'nav-tab-active';
							$default_settings_key = $setting_tab;
						} elseif ( isset( $_REQUEST['tab'] ) && $setting_tab === $_REQUEST['tab'] ) { // phpcs:ignore
							$active_class = 'nav-tab-active';
						}
						echo '<a href="' . $setting_values['url'] . '" class="nav-tab ' . $active_class . '">' . $setting_values['name'] . '</a>'; // phpcs:ignore
					}
					?>
				</div>
					<?php
					$show_tab = $default_settings_key;

					if ( isset( $_REQUEST['tab'] ) && array_key_exists( $_REQUEST['tab'], $settings ) ) { // phpcs:ignore
						$show_tab = \sanitize_text_field( \wp_unslash( $_REQUEST['tab'] ) ); // phpcs:ignore
					}

					if ( WP_Helper::is_multisite() ) {
						$action = 'edit.php?action=' . $settings[ $show_tab ]['network_action'];
					} else {
						$action = 'options.php';
					}
					?>
					<br/>
					<?php
						$settings[ $show_tab ]['description'];
					?>
					<br/>
					<form id="wp-2fa-admin-settings" action='<?php echo esc_attr( $action ); ?>' method='post' autocomplete="off" >
						<?php
						$settings_show = new $settings[ $show_tab ]['class']();
						$settings_show->{$settings[ $show_tab ]['method']}();
						?>
					</form>
			<?php } ?>
		</div>
			<?php
		}

		/**
		 * Holds the array with all the settings of the plugin. Fires filter, so third parties could change these settings.
		 *
		 * @return array
		 *
		 * @since 2.2.0
		 */
		private static function settings_array(): array {
			$settings_tabs = array(
				'generic-settings'     => array(
					'url'            => esc_url(
						add_query_arg(
							array(
								'page' => 'wp-2fa-settings',
								'tab'  => 'generic-settings',
							),
							network_admin_url( 'admin.php' )
						)
					),
					'name'           => esc_html__( 'General plugin settings', 'wp-2fa' ),
					'default'        => true,
					'description'    => sprintf(
						'<p class="description">%1$s <a href="mailto:support@wpwhitesecurity.com">%2$s</a></p>',
						esc_html__( 'Use the settings below to configure the properties of the two-factor authentication on your website and how users use it. If you have any questions send us an email at', 'wp-2fa' ),
						esc_html__( 'support@wpwhitesecurity.com', 'wp-2fa' )
					),
					'class'          => 'WP2FA\Admin\SettingsPages\Settings_Page_General',
					'method'         => 'render',
					'network_action' => 'update_wp2fa_network_options',
				),
				'email-settings'       => array(
					'url'            => esc_url(
						add_query_arg(
							array(
								'page' => 'wp-2fa-settings',
								'tab'  => 'email-settings',
							),
							network_admin_url( 'admin.php' )
						)
					),
					'name'           => esc_html__( 'Email Settings & Templates', 'wp-2fa' ),
					'default'        => false,
					'description'    => sprintf(
						'<p class="description">%1$s <a href="mailto:support@wpwhitesecurity.com">%2$s</a></p>',
						esc_html__( 'Use the settings below to configure the properties of the two-factor authentication on your website and how users use it. If you have any questions send us an email at', 'wp-2fa' ),
						esc_html__( 'support@wpwhitesecurity.com', 'wp-2fa' )
					),
					'class'          => 'WP2FA\Admin\SettingsPages\Settings_Page_Email',
					'method'         => 'render',
					'network_action' => 'update_wp2fa_network_email_options',
				),
				'white-label-settings' => array(
					'url'            => esc_url(
						add_query_arg(
							array(
								'page' => 'wp-2fa-settings',
								'tab'  => 'white-label-settings',
							),
							network_admin_url( 'admin.php' )
						)
					),
					'name'           => esc_html__( 'White labeling', 'wp-2fa' ),
					'default'        => false,
					'description'    => sprintf(
						'<p class="description">%1$s <a href="mailto:support@wpwhitesecurity.com">%2$s</a></p>',
						esc_html__( 'Use the settings below to configure the emails which are sent to users as part of the 2FA plugin. If you have any questions send us an email at', 'wp-2fa' ),
						esc_html__( 'support@wpwhitesecurity.com', 'wp-2fa' )
					),
					'class'          => 'WP2FA\Admin\SettingsPages\Settings_Page_White_Label',
					'method'         => 'render',
					'network_action' => 'update_wp2fa_network_options',
				),
			);

			/**
			* Filter: `Settings tabs`
			*
			* Gives an option for third parties to alter the plugin settings page
			*
			* @param array $settings_tabs – Settings tabs.
			*/
			return apply_filters( WP_2FA_PREFIX . 'settings_tabs', $settings_tabs );
		}
	}
}
