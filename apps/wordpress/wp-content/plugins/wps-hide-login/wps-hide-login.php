<?php
/*
Plugin Name: WPS Hide Login
Description: Protect your website by changing the login URL and preventing access to wp-login.php page and wp-admin directory while not logged-in
Donate link: https://www.paypal.me/donateWPServeur
Author: WPServeur, NicolasKulka, wpformation
Author URI: https://wpserveur.net
Version: 1.9.6
Requires at least: 4.1
Tested up to: 6.0
Requires PHP: 7.0
Domain Path: languages
Text Domain: wps-hide-login
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Plugin constants
define( 'WPS_HIDE_LOGIN_VERSION', '1.9.6' );
define( 'WPS_HIDE_LOGIN_FOLDER', 'wps-hide-login' );

define( 'WPS_HIDE_LOGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPS_HIDE_LOGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPS_HIDE_LOGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once WPS_HIDE_LOGIN_DIR . 'autoload.php';

register_activation_hook( __FILE__, array( '\WPS\WPS_Hide_Login\Plugin', 'activate' ) );

add_action( 'plugins_loaded', 'plugins_loaded_wps_hide_login_plugin' );
function plugins_loaded_wps_hide_login_plugin() {
	\WPS\WPS_Hide_Login\Plugin::get_instance();

	load_plugin_textdomain( 'wps-hide-login', false, dirname( WPS_HIDE_LOGIN_BASENAME ) . '/languages' );
}