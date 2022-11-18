<?php
/**
 * Contact us and help rendering class.
 *
 * @package    wp2fa
 * @subpackage admin
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 * @since      2.0.0
 */

namespace WP2FA\Admin;

use \WP2FA\Admin\Settings_Page;
use WP2FA\Admin\Helpers\WP_Helper;

/**
 * Handles contact us tab and content.
 */
class Help_Contact_Us {

	const TOP_MENU_SLUG = 'wp-2fa-help-contact-us';

	/**
	 * Create admin menu entry and settings page
	 */
	public static function add_extra_menu_item() {
		add_submenu_page(
			Settings_Page::TOP_MENU_SLUG,
			esc_html__( 'Help & Contact Us', 'wp-2fa' ),
			esc_html__( 'Help & Contact Us', 'wp-2fa' ),
			'manage_options',
			self::TOP_MENU_SLUG,
			array( __CLASS__, 'render' ),
			100
		);
	}

	/**
	 * Handles rendering the help tabs and their wrapping element.
	 *
	 * @return void
	 */
	public static function render() {
		?>
		<div class="wrap help-wrap">
			<div class="page-head">
				<h2><?php esc_html_e( 'Help', 'wp-2fa' ); ?></h2>
			</div>
			<div class="nav-tab-wrapper">
				<?php
					// Get current tab.
					$current_tab = isset( $_GET['tab'] ) ? \wp_unslash( $_GET['tab'] ) : 'help'; // phpcs:ignore
				?>
				<a href="<?php echo esc_url( remove_query_arg( 'tab' ) ); ?>" class="nav-tab<?php echo 'help' === $current_tab ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Help', 'wp-2fa' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'system-info' ) ); ?>" class="nav-tab<?php echo 'system-info' === $current_tab ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'System info', 'wp-2fa' ); ?></a>
			</div>
			<div class="wp2fa-help-section nav-tabs">
				<?php
					self::sidebar();
				if ( 'help' === $current_tab ) {
					self::help();
				} elseif ( 'system-info' === $current_tab ) {
					self::system_info();
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Help tab content.
	 *
	 * @return void
	 */
	public static function help() {
		?>
		<div class="wp2fa-help-main">
			<!-- getting started -->
			<div class="title">
				<h2><?php esc_html_e( 'Getting started', 'wp-2fa' ); ?></h2>
			</div>
			<p><?php esc_html_e( 'Getting started with WP 2FA and making 2FA compulsory is as easy as 1 2 3 with WP 2FA. This can be easily done through the install wizard or the plugin settings. If you are stuck, no problem! Below are a few links of guides to help you get started:', 'wp-2fa' ); ?></p>
			<ul>
				<li><?php echo wp_sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( 'https://wp2fa.io/support/kb/getting-started-wp-2fa-plugin/?utm_source=plugin&utm_medium=referral&utm_campaign=WP2FA&utm_content=help+page' ), esc_html__( 'Getting started with WP 2FA', 'wp-2fa' ) ); ?></li>
				<li><?php echo wp_sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( 'https://wp2fa.io/support/kb/configure-2fa-policies-enforce/?utm_source=plugin&utm_medium=referral&utm_campaign=WP2FA&utm_content=help+page' ), esc_html__( 'Configuring 2FA policies & making 2FA mandatory', 'wp-2fa' ) ); ?></li>
				<li><?php echo wp_sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( 'https://wp2fa.io/support/kb/configure-2fa-front-end-page-wordpress/?utm_source=plugin&utm_medium=referral&utm_campaign=WP2FA&utm_content=help+page' ), esc_html__( 'Allowing users to configure 2FA from a website page (no dashboard access)', 'wp-2fa' ) ); ?></li>
			</ul>
			<!-- End -->
			<br>
			<p><iframe title="<?php esc_html_e( 'Getting started', 'wp-2fa' ); ?>" class="wsal-youtube-embed" width="100%" height="315" src="https://www.youtube.com/embed/vRlX_NNGeFo" frameborder="0" allowfullscreen></iframe></p>

			<!-- Plugin documentation -->
			<div class="title">
				<h2><?php esc_html_e( 'Plugin documentation', 'wp-2fa' ); ?></h2>
			</div>
			<p><?php esc_html_e( 'For more technical information about the WP 2FA plugin please visit the plugin\'s knowledge base.', 'wp-2fa' ); ?></p>
			<div class="btn">
				<a href="<?php echo esc_url( 'https://wp2fa.io/support/kb/?utm_source=plugin&utm_medium=referral&utm_campaign=WP2FA&utm_content=help+page' ); ?>" class="button" target="_blank"><?php esc_html_e( 'Knowledge base', 'wp-2fa' ); ?></a>
			</div>
			<!-- End -->

			<!-- Plugin support -->
			<div class="title">
				<h2><?php esc_html_e( 'Plugin support', 'wp-2fa' ); ?></h2>
			</div>
			<p><?php esc_html_e( 'Do you need assistance with the plugin? Have you noticed or encountered an issue while using WP 2FA, or do you just want to report something to us?', 'wp-2fa' ); ?></p>
			<div class="btn">
				<a href="<?php echo esc_url( 'https://wp2fa.io/support/submit-ticket/?utm_source=plugin&utm_medium=referral&utm_campaign=WP2FA&utm_content=help+page' ); ?>" class="button" target="_blank"><?php esc_html_e( 'Open support ticket', 'wp-2fa' ); ?></a>
				<a href="<?php echo esc_url( 'https://www.wpwhitesecurity.com/contact-wp-white-security/?utm_source=plugin&utm_medium=referral&utm_campaign=WP2FA&utm_content=help+page' ); ?>" class="button" target="_blank"><?php esc_html_e( 'Contact us', 'wp-2fa' ); ?></a>
			</div>
			<!-- End -->
		</div>
		<?php
	}

	/**
	 * System info tab content.
	 *
	 * @return void
	 */
	public static function system_info() {
		?>
		<div class="wp2fa-help-main">
			<!-- getting started -->
			<div class="title">
				<h2><?php esc_html_e( 'System information', 'wp-2fa' ); ?></h2>
			</div>
			<form method="post" dir="ltr">
				<textarea readonly="readonly" onclick="this.focus(); this.select()" id="system-info-textarea" name="wsal-sysinfo"><?php echo self::get_sysinfo(); // phpcs:ignore ?></textarea>
				<p class="submit">
					<input type="hidden" name="ppmwp-action" value="download_sysinfo" />
					<?php submit_button( 'Download System Info File', 'primary', 'wp2fa-download-sysinfo', false ); ?>
				</p>
			</form>
			<script>

				function download(filename, text) {
					// Create temporary element.
					var element = document.createElement('a');
					element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
					element.setAttribute('download', filename);

					// Set the element to not display.
					element.style.display = 'none';
					document.body.appendChild(element);

					// Simlate click on the element.
					element.click();

					// Remove temporary element.
					document.body.removeChild(element);
				}
				jQuery( document ).ready( function() {
					var download_btn = jQuery( '#wp2fa-download-sysinfo' );
					download_btn.click( function( event ) {
						event.preventDefault();
						download( 'wp2fa-system-info.txt', jQuery( '#system-info-textarea' ).val() );
					} );
				} );
			</script>
		</div>
		<?php
	}

	/**
	 * Advertising sidebar.
	 *
	 * @return void
	 */
	public static function sidebar() {
		?>
		<div class="our-wordpress-plugins side-bar">
			<h3><?php esc_html_e( 'Our WordPress Plugins', 'wp-2fa' ); ?></h3>
			<ul>
				<li>
					<div class="plugin-box">
						<div class="plugin-img">
							<img src="<?php echo WP_2FA_URL; // phpcs:ignore ?>dist/images/wp-security-audit-log-img.jpg" alt="">
						</div>
						<div class="plugin-desc">
							<p><?php esc_html_e( 'Keep a log of users and under the hood site activity.', 'wp-2fa' ); ?></p>
							<div class="cta-btn">
								<a href="
								<?php
								echo esc_url(
									add_query_arg(
										array(
											'utm_source'   => 'plugin',
											'utm_medium'   => 'referral',
											'utm_campaign' => 'WSAL',
											'utm_content'  => 'WP2FA+banner',
										),
										'https://wpactivitylog.com'
									)
								);
								?>
								" target="_blank"><?php esc_html_e( 'LEARN MORE', 'wp-2fa' ); ?></a>
							</div>
						</div>
					</div>
				</li>
				<li>
					<div class="plugin-box">
						<div class="plugin-img">
							<img src="<?php echo WP_2FA_URL; // phpcs:ignore ?>dist/images/wp-password-img.jpg" alt="">
						</div>
						<div class="plugin-desc">
							<p><?php esc_html_e( 'Enforce strong password policies on WordPress.', 'wp-2fa' ); ?></p>
							<div class="cta-btn">
								<a href="
								<?php
								echo esc_url(
									add_query_arg(
										array(
											'utm_source'   => 'plugin',
											'utm_medium'   => 'referral',
											'utm_campaign' => 'WSAL',
											'utm_content'  => 'WP2FA+banner',
										),
										'https://www.wpwhitesecurity.com/wordpress-plugins/password-security/'
									)
								);
								?>
								" target="_blank"><?php esc_html_e( 'LEARN MORE', 'wp-2fa' ); ?></a>
							</div>
						</div>
					</div>
				</li>
				<li>
					<div class="plugin-box">
						<div class="plugin-img">
							<img src="<?php echo WP_2FA_URL; // phpcs:ignore ?>dist/images/website-file-changes-monitor.jpg" alt="">
						</div>
						<div class="plugin-desc">
							<p><?php esc_html_e( 'Automatically identify unauthorized file changes on your WordPress site.', 'wp-2fa' ); ?></p>
							<div class="cta-btn">
								<a href="
								<?php
								echo esc_url(
									add_query_arg(
										array(
											'utm_source'   => 'plugin',
											'utm_medium'   => 'referral',
											'utm_campaign' => 'WSAL',
											'utm_content'  => 'WP2FA+banner',
										),
										'https://www.wpwhitesecurity.com/wordpress-plugins/website-file-changes-monitor/'
									)
								);
								?>
								" target="_blank"><?php esc_html_e( 'LEARN MORE', 'wp-2fa' ); ?></a>
							</div>
						</div>
					</div>
				</li>
				<li>
					<div class="plugin-box">
						<div class="plugin-img">
							<img src="<?php echo WP_2FA_URL; // phpcs:ignore ?>dist/images/c4wp.jpg" alt="">
						</div>
						<div class="plugin-desc">
							<p><?php esc_html_e( 'Protect website forms & login pages from spam bots & automated attacks.', 'wp-2fa' ); ?></p>
							<div class="cta-btn">
								<a href="
								<?php
								echo esc_url(
									add_query_arg(
										array(
											'utm_source'   => 'plugin',
											'utm_medium'   => 'referral',
											'utm_campaign' => 'WSAL',
											'utm_content'  => 'WP2FA+banner',
										),
										'https://www.wpwhitesecurity.com/wordpress-plugins/captcha-plugin-wordpress/'
									)
								);
								?>
								" target="_blank"><?php esc_html_e( 'LEARN MORE', 'wp-2fa' ); ?></a>
							</div>
						</div>
					</div>
				</li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Gather basic settings and system information for use in the system info tab. Left untranslated (as is the case in all plugins)
	 * as its for our use only.
	 *
	 * @return string
	 */
	public static function get_sysinfo() {
		// System info.
		global $wpdb;

		$sysinfo = '### System Info → Begin ###' . "\n\n";

		// Start with the basics...
		$sysinfo .= '-- Site Info --' . "\n\n";
		$sysinfo .= 'Site URL (WP Address):    ' . site_url() . "\n";
		$sysinfo .= 'Home URL (Site Address):  ' . home_url() . "\n";
		$sysinfo .= 'Multisite:                ' . ( WP_Helper::is_multisite() ? 'Yes' : 'No' ) . "\n";

		// Get theme info.
		$theme_data   = wp_get_theme();
		$theme        = $theme_data->name . ' ' . $theme_data->version;
		$parent_theme = $theme_data->template;
		if ( ! empty( $parent_theme ) ) {
			$parent_theme_data = wp_get_theme( $parent_theme );
			$parent_theme      = $parent_theme_data->name . ' ' . $parent_theme_data->version;
		}

		// Language information.
		$locale = get_locale();

		// WordPress configuration.
		$sysinfo .= "\n" . '-- WordPress Configuration --' . "\n\n";
		$sysinfo .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
		$sysinfo .= 'Language:                 ' . ( ! empty( $locale ) ? $locale : 'en_US' ) . "\n";
		$sysinfo .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
		$sysinfo .= 'Active Theme:             ' . $theme . "\n";
		if ( $parent_theme !== $theme ) {
			$sysinfo .= 'Parent Theme:             ' . $parent_theme . "\n";
		}
		$sysinfo .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

		// Only show page specs if frontpage is set to 'page'.
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$front_page_id = (int) get_option( 'page_on_front' );
			$blog_page_id  = (int) get_option( 'page_for_posts' );

			$sysinfo .= 'Page On Front:            ' . ( 0 !== $front_page_id ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n";
			$sysinfo .= 'Page For Posts:           ' . ( 0 !== $blog_page_id ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n";
		}

		$sysinfo .= 'ABSPATH:                  ' . ABSPATH . "\n";
		$sysinfo .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? ( WP_DEBUG ? 'Enabled' : 'Disabled' ) : 'Not set' ) . "\n";
		$sysinfo .= 'WP Memory Limit:          ' . WP_MEMORY_LIMIT . "\n";

		// Get plugins that have an update.
		$updates = get_plugin_updates();

		// Must-use plugins.
		// NOTE: MU plugins can't show updates!
		$muplugins = get_mu_plugins();
		if ( count( $muplugins ) > 0 ) {
			$sysinfo .= "\n" . '-- Must-Use Plugins --' . "\n\n";

			foreach ( $muplugins as $plugin => $plugin_data ) {
				$sysinfo .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
			}
		}

		// WordPress active plugins.
		$sysinfo .= "\n" . '-- WordPress Active Plugins --' . "\n\n";

		$plugins        = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $plugins as $plugin_path => $plugin ) {
			if ( ! in_array( $plugin_path, $active_plugins, true ) ) {
				continue;
			}

			$update   = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
			$sysinfo .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		}

		// WordPress inactive plugins.
		$sysinfo .= "\n" . '-- WordPress Inactive Plugins --' . "\n\n";

		foreach ( $plugins as $plugin_path => $plugin ) {
			if ( in_array( $plugin_path, $active_plugins, true ) ) {
				continue;
			}

			$update   = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
			$sysinfo .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		}

		if ( WP_Helper::is_multisite() ) {
			// WordPress Multisite active plugins.
			$sysinfo .= "\n" . '-- Network Active Plugins --' . "\n\n";

			$plugins        = wp_get_active_network_plugins();
			$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

			foreach ( $plugins as $plugin_path ) {
				$plugin_base = plugin_basename( $plugin_path );

				if ( ! array_key_exists( $plugin_base, $active_plugins ) ) {
					continue;
				}

				$update   = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
				$plugin   = get_plugin_data( $plugin_path );
				$sysinfo .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
			}
		}

		// Server configuration.
		$server_software = ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) ? \sanitize_text_field( \wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
		$sysinfo        .= "\n" . '-- Webserver Configuration --' . "\n\n";
		$sysinfo        .= 'PHP Version:              ' . PHP_VERSION . "\n";
		$sysinfo        .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";

		if ( isset( $server_software ) ) {
			$sysinfo .= 'Webserver Info:           ' . $server_software . "\n";
		} else {
			$sysinfo .= 'Webserver Info:           Global $_SERVER array is not set.' . "\n";
		}

		// PHP configs.
		$sysinfo .= "\n" . '-- PHP Configuration --' . "\n\n";
		$sysinfo .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
		$sysinfo .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
		$sysinfo .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
		$sysinfo .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
		$sysinfo .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
		$sysinfo .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
		$sysinfo .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";

		$sysinfo .= "\n" . '-- WP 2FA Settings  --' . "\n\n";

		global $wpdb;

		$wp2fa_options = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'wp_2fa_%'", ARRAY_A ); // phpcs:ignore

		if ( ! empty( $wp2fa_options ) ) {
			foreach ( $wp2fa_options as $option => $value ) {
				$sysinfo .= 'Option: ' . $value['option_name'] . "\n";
				$sysinfo .= 'Value: ' . print_r( $value['option_value'], true ) . "\n\n"; // phpcs:ignore
			}
		}

		$sysinfo .= "\n" . '### System Info → End ###' . "\n\n";

		return $sysinfo;
	}
}
