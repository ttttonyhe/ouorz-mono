<?php
/**
 * WP 2FA - Two-factor authentication for WordPress 
 *
 * @copyright Copyright (C) 2013-2022, WP White Security - support@wpwhitesecurity.com
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * @wordpress-plugin
 * Plugin Name: WP 2FA - Two-factor authentication for WordPress 
 * Version:     2.3.0
 * Plugin URI:  https://wp2fa.io/
 * Description: Easily add an additional layer of security to your WordPress login pages. Enable Two-Factor Authentication for you and all your website users with this easy to use plugin.
 * Author:      WP White Security
 * Author URI:  https://www.wpwhitesecurity.com/
 * Text Domain: wp-2fa
 * Domain Path: /languages/
 * License:     GPL v3
 * Requires at least: 5.0
 * Requires PHP: 7.3
 * Network: true
 *
 * @package WP2FA
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use WP2FA\WP2FA;
use WP2FA\Utils\Migration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Useful global constants.
if ( ! defined( 'WP_2FA_VERSION' ) ) {
	define( 'WP_2FA_VERSION', '2.3.0' );
	define( 'WP_2FA_URL', plugin_dir_url( __FILE__ ) );
	define( 'WP_2FA_PATH', plugin_dir_path( __FILE__ ) );
	define( 'WP_2FA_INC', WP_2FA_PATH . 'includes/' );
	define( 'WP_2FA_FILE', __FILE__ );
	define( 'WP_2FA_BASE', plugin_basename( __FILE__ ) );
	define( 'WP_2FA_LOGS_DIR', 'wp-2fa-logs' );

	// Prefix used in usermetas, settings and transients.
	define( 'WP_2FA_PREFIX', 'wp_2fa_' );
	define( 'WP_2FA_POLICY_SETTINGS_NAME', WP_2FA_PREFIX . 'policy' );
	define( 'WP_2FA_SETTINGS_NAME', WP_2FA_PREFIX . 'settings' );
	define( 'WP_2FA_WHITE_LABEL_SETTINGS_NAME', WP_2FA_PREFIX . 'white_label' );
	define( 'WP_2FA_EMAIL_SETTINGS_NAME', WP_2FA_PREFIX . 'email_settings' );
}
		// Include files.
		require_once WP_2FA_INC . 'functions/core.php';

		// Require Composer autoloader if it exists.
		if ( file_exists( WP_2FA_PATH . 'vendor/autoload.php' ) ) {
			require_once WP_2FA_PATH . 'vendor/autoload.php';
		}

		if ( file_exists( WP_2FA_PATH . 'third-party/vendor/autoload.php' ) ) {
			require_once WP_2FA_PATH . 'third-party/vendor/autoload.php';
		}

		// run any required update routines.
		Migration::migrate();

		$wp2fa = WP2FA::get_instance();
		$wp2fa->init();
/* @free:start */
if ( ! function_exists( 'wp2fa_free_on_plugin_activation' ) ) {
	/**
	 * Takes care of deactivation of the premium plugin when the free plugin is activated.
	 *
	 * Note: This code MUST NOT be present in the premium version an is removed automatically during the build process.
	 *
	 * @since 2.0.0
	 */
	function wp2fa_free_on_plugin_activation() {
		$premium_version_slug = 'wp-2fa-premium/wp-2fa.php';
		if ( is_plugin_active( $premium_version_slug ) ) {
			deactivate_plugins( $premium_version_slug, true );
		}
		check_ssl();
	}

	register_activation_hook( __FILE__, 'wp2fa_free_on_plugin_activation' );
}
/* @free:end */

/**
 * Clears the config cache from the DB
 *
 * @return void
 *
 * @since 2.2.0
 */
add_action(
    'upgrader_process_complete',
    function() {
		delete_transient( 'wp_2fa_config_file_hash' );
	},
    10,
    2
);

if ( ! function_exists( 'check_ssl' ) ) {
	/**
	 * Checks if the required library is installed and cancels the process if not.
	 *
	 * @return void
	 *
	 * @since 2.2.0
	 */
	function check_ssl() {
		if ( ! \WP2FA\Authenticator\Open_SSL::is_ssl_available() ) {
			$html = '<div class="updated notice is-dismissible">
			<p>' . esc_html__( 'This plugin requires OpenSSL. Contact your web host or website administrator so they can enable OpenSSL. Re-activate the plugin once the library has been enabled.', 'wp-2fa' )
			. '</p>
		</div>';

			echo $html; // phpcs:ignore

			exit();
		}
	}
}
