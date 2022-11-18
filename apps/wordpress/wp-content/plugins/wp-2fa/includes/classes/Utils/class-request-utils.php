<?php
/**
 * Responsible for the requests.
 *
 * @package    wp2fa
 * @subpackage utils
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 * @since      2.0.0
 */

namespace WP2FA\Utils;

/**
 * Utility class to extract info from current request.
 *
 * @package WP2FA\Utils
 * @since 2.0.0
 */
class Request_Utils {

	/**
	 * Extracts the IP address for the currently browsing user
	 *
	 * @return string
	 *
	 * @since 2.0.0
	 */
	public static function get_ip() {
		foreach (
			array(
				'HTTP_CLIENT_IP',
				'HTTP_X_FORWARDED_FOR',
				'HTTP_X_FORWARDED',
				'HTTP_X_CLUSTER_CLIENT_IP',
				'HTTP_FORWARDED_FOR',
				'HTTP_FORWARDED',
				'REMOTE_ADDR',
			) as $key
		) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				foreach ( array_map( 'trim', explode( ',', $_SERVER[ $key ] ) ) as $ip ) { // phpcs:ignore
					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
						return $ip;
					}
				}
			}
		}
	}

	/**
	 * Extracts the User agent for the currently request.
	 *
	 * @return string
	 *
	 * @since 2.0.0
	 */
	public static function get_user_agent() {
		if ( ! array_key_exists( 'HTTP_USER_AGENT', $_SERVER ) ) {
			return '';
		}

		return trim( $_SERVER['HTTP_USER_AGENT'] ); // phpcs:ignore
	}
}
