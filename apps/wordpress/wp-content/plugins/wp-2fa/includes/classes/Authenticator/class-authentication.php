<?php
/**
 * Responsible for WP2FA user's authentication.
 *
 * @package    wp2fa
 * @subpackage authentication
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

/**
 * Class for handling general authentication tasks.
 *
 * @since 0.1-dev
 *
 * @package WP2FA
 */

namespace WP2FA\Authenticator;

use Endroid\QrCode\QrCode;
use WP2FA\Authenticator\Open_SSL;
use Endroid\QrCode\Writer\SvgWriter;
use WP2FA\Admin\Helpers\User_Helper;
use WP2FA\Admin\Controllers\Login_Attempts;

/**
 * Authenticator class
 */
class Authentication {

	const DEFAULT_KEY_BIT_SIZE        = 160;
	const DEFAULT_CRYPTO              = 'sha1';
	const DEFAULT_DIGIT_COUNT         = 6;
	const DEFAULT_TIME_STEP_SEC       = 30;
	const DEFAULT_TIME_STEP_ALLOWANCE = 4;

	/**
	 * Holds the name of the meta key for the allowed login attempts
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	private static $login_num_meta_key = WP_2FA_PREFIX . 'email-login-attempts';

	/**
	 * The login attempts class
	 *
	 * @var \WP2FA\Admin\Controllers\Login_Attempts
	 *
	 * @since 2.0.0
	 */
	private static $login_attempts = null;

	/**
	 * String with the base32 characters
	 *
	 * @var string
	 */
	private static $base_32_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

	/**
	 * String with the decrypted key
	 *
	 * @var string
	 */
	private static $decrypted_key = '';

	/**
	 * Gemerate QR code
	 *
	 * @param  string $name  Username.
	 * @param  string $key   Auth key.
	 * @param  string $title Site title.
	 * @return string        QR code URL.
	 */
	public static function get_google_qr_code( $name, $key, $title = null ) {
		// Encode to support spaces, question marks and other characters.
		$name = rawurlencode( $name );

		self::decrypt_key_if_needed( $key );

		$target_url = ( 'otpauth://totp/' . $name . '?secret=' . $key );
		if ( isset( $title ) ) {
			$target_url .= ( '&issuer=' . rawurlencode( $title ) );
		}

		$qr = new QrCode( $target_url );
		$qr->setWriterOptions( array( 'exclude_xml_declaration' => true ) );
		$writer = new SvgWriter();
		$result = $writer->writeString( $qr );

		return 'data:image/svg+xml;base64,' . base64_encode( $result ); // phpcs:ignore
	}

	/**
	 * Generates key
	 *
	 * @param int $bitsize Nume of bits to use for key.
	 *
	 * @return string $bitsize long string composed of available base32 chars.
	 */
	public static function generate_key( $bitsize = self::DEFAULT_KEY_BIT_SIZE ) {
		$bytes  = ceil( $bitsize / 8 );
		$secret = wp_generate_password( $bytes, true, true );

		$secret = Open_SSL::encrypt( self::base32_encode( $secret ) );

		if ( Open_SSL::is_ssl_available() ) {
			$secret = 'wps_' . $secret;
		}

		return $secret;
	}
	/**
	 * Returns a base32 encoded string.
	 *
	 * @param string $string String to be encoded using base32.
	 *
	 * @return string base32 encoded string without padding.
	 */
	public static function base32_encode( $string ) {
		if ( empty( $string ) ) {
			return '';
		}

		$binary_string = '';

		foreach ( str_split( $string ) as $character ) {
			$binary_string .= str_pad( base_convert( ord( $character ), 10, 2 ), 8, '0', STR_PAD_LEFT );
		}

		$five_bit_sections = str_split( $binary_string, 5 );
		$base32_string     = '';

		foreach ( $five_bit_sections as $five_bit_section ) {
			$base32_string .= self::$base_32_chars[ base_convert( str_pad( $five_bit_section, 5, '0' ), 2, 10 ) ];
		}

		return $base32_string;
	}

	/**
	 * Get the TOTP secret key for a user.
	 *
	 * @param  int $user_id User ID.
	 *
	 * @return string
	 */
	public static function get_user_totp_key( $user_id ) {

		$key = (string) User_Helper::get_user_totp_key( $user_id );

		$test = $key;

		if ( Open_SSL::is_ssl_available() && false !== \strpos( $key, 'ssl_' ) ) {

			/**
			 * Old key detected - convert.
			 */
			$key = Open_SSL::decrypt_legacy( substr( $key, 4 ) );

			User_Helper::remove_user_totp_key();

			$secret = Open_SSL::encrypt( $key );

			if ( Open_SSL::is_ssl_available() ) {
				$secret = 'wps_' . $secret;
			}

			User_Helper::set_user_totp_key( $key, $user_id );

			$test = $key = (string) User_Helper::get_user_totp_key( $user_id ); // phpcs:ignore
		}

		self::decrypt_key_if_needed( $test );

		if ( ! self::is_valid_key( $test ) ) {
			$key = self::generate_key();
			User_Helper::set_user_totp_key( $key, $user_id );
		}

		return $key;
	}

	/**
	 * Check if the TOTP secret key has a proper format.
	 *
	 * @param  string $key TOTP secret key.
	 *
	 * @return boolean
	 */
	public static function is_valid_key( $key ) {
		self::decrypt_key_if_needed( $key );

		$check = sprintf( '/^[%s]+$/', self::$base_32_chars );

		if ( 1 === preg_match( $check, $key ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if a given code is valid for a given key, allowing for a certain amount of time drift
	 *
	 * @param string $key      The share secret key to use.
	 * @param string $authcode The code to test.
	 *
	 * @return bool Whether the code is valid within the time frame
	 */
	public static function is_valid_authcode( $key, $authcode ) {

		self::decrypt_key_if_needed( $key );
		/**
		 * That allows to change the amount of thick for decrypting the key.
		 *
		 * @param bool - Default at this point is true - no method is selected.
		 *
		 * @since 2.0.0
		 */
		$max_ticks = apply_filters( WP_2FA_PREFIX . 'totp_time_step_allowance', self::DEFAULT_TIME_STEP_ALLOWANCE );

		// Array of all ticks to allow, sorted using absolute value to test closest match first.
		$ticks = range( - $max_ticks, $max_ticks );
		usort( $ticks, array( __CLASS__, 'abssort' ) );

		$time = time() / self::DEFAULT_TIME_STEP_SEC;
		foreach ( $ticks as $offset ) {
			$log_time    = $time + $offset;
			$calculdated = (string) self::calc_totp( $key, $log_time );
			if ( $calculdated === $authcode ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Calculate a valid code given the shared secret key
	 *
	 * @param string $key        The shared secret key to use for calculating code.
	 * @param mixed  $step_count The time step used to calculate the code, which is the floor of time() divided by step size.
	 * @param int    $digits     The number of digits in the returned code.
	 * @param string $hash       The hash used to calculate the code.
	 * @param int    $time_step  The size of the time step.
	 *
	 * @return string The totp code
	 */
	public static function calc_totp( $key, $step_count = false, $digits = self::DEFAULT_DIGIT_COUNT, $hash = self::DEFAULT_CRYPTO, $time_step = self::DEFAULT_TIME_STEP_SEC ) {

		$secret = self::base32_decode( $key );

		if ( false === $step_count ) {
			$step_count = floor( time() / $time_step );
		}

		$timestamp = self::pack64( $step_count );

		$hash = hash_hmac( $hash, $timestamp, $secret, true );

		$offset = ord( $hash[19] ) & 0xf;

		$code = (
				( ( ord( $hash[ $offset + 0 ] ) & 0x7f ) << 24 ) |
				( ( ord( $hash[ $offset + 1 ] ) & 0xff ) << 16 ) |
				( ( ord( $hash[ $offset + 2 ] ) & 0xff ) << 8 ) |
				( ord( $hash[ $offset + 3 ] ) & 0xff )
			) % pow( 10, $digits );

		return str_pad( $code, $digits, '0', STR_PAD_LEFT );
	}

	/**
	 * Decode a base32 string and return a binary representation
	 *
	 * @param string $base32_string The base 32 string to decode.
	 *
	 * @throws \Exception If string contains non-base32 characters.
	 *
	 * @return string Binary representation of decoded string
	 */
	public static function base32_decode( $base32_string ) {

		$base32_string = strtoupper( $base32_string );

		if ( ! preg_match( '/^[' . self::$base_32_chars . ']+$/', $base32_string, $match ) ) {
			throw new \Exception( 'Invalid characters in the base32 string.' );
		}

		$l      = strlen( $base32_string );
		$n      = 0;
		$j      = 0;
		$binary = '';

		for ( $i = 0; $i < $l; $i++ ) {

			$n  = $n << 5; // Move buffer left by 5 to make room.
			$n  = $n + strpos( self::$base_32_chars, $base32_string[ $i ] );    // Add value into buffer.
			$j += 5; // Keep track of number of bits in buffer.

			if ( $j >= 8 ) {
				$j      -= 8;
				$binary .= chr( ( $n & ( 0xFF << $j ) ) >> $j );
			}
		}

		return $binary;
	}

	/**
	 * Used with usort to sort an array by distance from 0
	 *
	 * @param int $a First array element.
	 * @param int $b Second array element.
	 *
	 * @return int -1, 0, or 1 as needed by usort
	 */
	private static function abssort( $a, $b ) {
		$a = abs( $a );
		$b = abs( $b );
		if ( $a === $b ) {
			return 0;
		}
		return ( $a < $b ) ? -1 : 1;
	}

	/**
	 * Pack stuff
	 *
	 * @param string $value The value to be packed.
	 *
	 * @return string Binary packed string.
	 */
	public static function pack64( $value ) {
		// 64bit mode (PHP_INT_SIZE == 8).
		if ( PHP_INT_SIZE >= 8 ) {
			// If we're on PHP 5.6.3+ we can use the new 64bit pack functionality.
			if ( version_compare( PHP_VERSION, '5.6.3', '>=' ) && PHP_INT_SIZE >= 8 ) {
				return pack( 'J', $value );
			}
			$highmap = 0xffffffff << 32;
			$higher  = ( $value & $highmap ) >> 32;
		} else {
			/*
			 * 32bit PHP can't shift 32 bits like that, so we have to assume 0 for the higher
			 * and not pack anything beyond it's limits.
			 */
			$higher = 0;
		}

		$lowmap = 0xffffffff;
		$lower  = $value & $lowmap;

		return pack( 'NN', $higher, $lower );
	}

	/**
	 * Generate a random eight-digit string to send out as an auth code.
	 *
	 * @since 0.1-dev
	 *
	 * @param int          $length The code length.
	 * @param string|array $chars Valid auth code characters.
	 * @return string
	 */
	public static function get_code( $length = 8, $chars = '1234567890' ) {
		$code = '';
		if ( is_array( $chars ) ) {
			$chars = implode( '', $chars );
		}
		for ( $i = 0; $i < $length; $i++ ) {
			$code .= substr( $chars, wp_rand( 0, strlen( $chars ) - 1 ), 1 );
		}
		return $code;
	}

	/**
	 * Generate the user token.
	 *
	 * @since 0.1-dev
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public static function generate_token( $user_id ) {
		$token = self::get_code();

		User_Helper::set_email_token_for_user( wp_hash( $token ), $user_id );
		return $token;
	}

	/**
	 * Validate the user token.
	 *
	 * @since 0.1-dev
	 *
	 * @param \WP_User $user User ID.
	 * @param string   $token User token.
	 * @return boolean
	 */
	public static function validate_token( $user, $token ) {
		$user_id      = $user->ID;
		$hashed_token = self::get_user_token( $user_id );
		// Bail if token is empty or it doesn't match.
		// This code is here just because people have no idea what is the difference between preaching and real life.
		if ( empty( $hashed_token ) || ( ! hash_equals( wp_hash( $token ), $hashed_token ) ) ) {
			self::get_login_attempts_instance()->increase_login_attempts( $user );
			return false;
		}


		// Ensure that the token can't be re-used.
		self::delete_token( $user_id );
		self::get_login_attempts_instance()->clear_login_attempts( $user );

		return true;
	}

	/**
	 * Delete the user token.
	 *
	 * @since 0.1-dev
	 *
	 * @param int $user_id User ID.
	 */
	public static function delete_token( $user_id ) {
		User_Helper::remove_email_token_for_user( $user_id );
	}

	/**
	 * Check if user has a valid token already.
	 *
	 * @param  int $user_id User ID.
	 * @return boolean      If user has a valid email token.
	 */
	public static function user_has_token( $user_id ) {
		$hashed_token = self::get_user_token( $user_id );
		if ( ! empty( $hashed_token ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the authentication token for the user.
	 *
	 * @param  int $user_id    User ID.
	 *
	 * @return string|boolean  User token or `false` if no token found.
	 */
	public static function get_user_token( $user_id ) {


		$hashed_token = User_Helper::get_email_token_for_user( $user_id );

		if ( ! empty( $hashed_token ) && is_string( $hashed_token ) ) {
			return $hashed_token;
		}

		return false;
	}

	/**
	 * Returns list of all the auth apps and their properties
	 *
	 * @return array
	 */
	public static function get_apps(): array {
		return array(
			'authy'     => array(
				'logo' => 'authy-logo.png',
				'hash' => 'authy',
				'name' => 'Authy',
			),
			'google'    => array(
				'logo' => 'google-logo.png',
				'hash' => 'google',
				'name' => 'Google Authenticator',
			),
			'microsoft' => array(
				'logo' => 'microsoft-logo.png',
				'hash' => 'microsoft',
				'name' => 'Microsoft Authenticator',
			),
			'duo'       => array(
				'logo' => 'duo-logo.png',
				'hash' => 'duo',
				'name' => 'Duo Security',
			),
			'lastpass'  => array(
				'logo' => 'lastpass-logo.png',
				'hash' => 'lastpass',
				'name' => 'LastPass',
			),
			'freeotp'   => array(
				'logo' => 'free-otp-logo.png',
				'hash' => 'freeotp',
				'name' => 'FreeOTP',
			),
			'okta'      => array(
				'logo' => 'okta-logo.png',
				'hash' => 'okta',
				'name' => 'Okta',
			),
		);
	}

	/**
	 * Getter for the base32 character set
	 *
	 * @return string
	 *
	 * @since 2.0.0
	 */
	public static function get_base32_characters(): string {
		return self::$base_32_chars;
	}

	/**
	 * Validates base32 encoded string
	 *
	 * @param string $text = The text to be validated.
	 *
	 * @return boolean
	 *
	 * @since 2.0.0
	 */
	public static function validate_base32_string( string $text ): bool {
		if ( ! preg_match( '/^[' . self::$base_32_chars . ']+$/', $text, $match ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks the given key and decrypts it if necessarily
	 *
	 * @param string $key - The key to check.
	 *
	 * @return string
	 *
	 * @since 2.0.0
	 */
	public static function decrypt_key_if_needed( string &$key ): string {
		if ( '' === trim( self::$decrypted_key ) ) {
			if ( Open_SSL::is_ssl_available() && false !== \strpos( $key, 'wps_' ) ) {
				$key = self::$decrypted_key = Open_SSL::decrypt( substr( $key, 4 ) ); // phpcs:ignore
			} else {
				self::$decrypted_key = $key;
			}
		}

		return ( $key = self::$decrypted_key ); // phpcs:ignore
	}

	/**
	 * Returns instance of the LoginAttempts class
	 *
	 * @return \WP2FA\Admin\Controllers\Login_Attempts
	 *
	 * @since 2.0.0
	 */
	public static function get_login_attempts_instance() {
		if ( null === self::$login_attempts ) {

			self::$login_attempts = new Login_Attempts( self::$login_num_meta_key );

		}
		return self::$login_attempts;
	}

	/**
	 * Checks the number of login attempts
	 *
	 * @param \WP_User $user - The user we have to check for.
	 *
	 * @return boolean
	 *
	 * @since 2.0.0
	 */
	public static function check_number_of_attempts( \WP_User $user ):bool {
		return self::get_login_attempts_instance()->check_number_of_attempts( $user );
	}
}
