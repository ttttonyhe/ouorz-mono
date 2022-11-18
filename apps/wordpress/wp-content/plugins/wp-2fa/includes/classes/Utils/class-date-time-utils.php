<?php
/**
 * Responsible for date / time manipulation.
 *
 * @package    wp2fa
 * @subpackage utils
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Utils;

use WP2FA\WP2FA as WP2FA;

/**
 * Utility class for date and time manipulation, format conversion and so on.
 *
 * @package WP2FA\Utils
 * @since 1.4.2
 */
class Date_Time_Utils {

	/**
	 * Formats the date string
	 *
	 * @param string|null $grace_policy Grace policy value.
	 * @param int         $grace_expiry Expiration time as unix based timestamp.
	 *
	 * @return string Translated grace period expiration string.
	 */
	public static function format_grace_period_expiration_string( $grace_policy = null, $grace_expiry = - 1 ) {
		if ( null === $grace_policy ) {
			$grace_policy = WP2FA::get_wp2fa_setting( 'grace-policy' );
		}

		if ( 'no-grace-period' === $grace_policy ) {
			return esc_html__( 'no grace period', 'wp-2fa' );
		}

		if ( -1 === $grace_expiry ) {
			if ( 'use-grace-period' === $grace_policy ) {
				$grace_period             = WP2FA::get_wp2fa_setting( 'grace-period' );
				$grace_period_denominator = WP2FA::get_wp2fa_setting( 'grace-period-denominator' );
				$grace_period_string      = $grace_period . ' ' . $grace_period_denominator;
				$grace_expiry             = (int) strtotime( $grace_period_string );
			} else {
				// this will probably never be reached, leaving it here for now just in case.
				$grace_expiry = time();
			}
		}

		$expiration_date_time = implode(
			' ',
			array(
				// Purposefully not using the SettingsUtil class as we don't want this prefixed.
				date_i18n( get_option( 'date_format' ), $grace_expiry ),
				date_i18n( get_option( 'time_format' ), $grace_expiry ),
			)
		);

		/* translators: Grace period expiration label. %s: Date and time formatted using WordPress date and time formats. */
		return sprintf( esc_html__( 'before %s', 'wp-2fa' ), $expiration_date_time );
	}
}
