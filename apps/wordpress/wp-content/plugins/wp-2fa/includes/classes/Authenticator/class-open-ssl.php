<?php
/**
 * Open SSL encrypt / decrypt class.
 *
 * @package   wp2fa
 * @copyright 2021 WP White Security
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Authenticator;

use WP2FA\WP2FA;
use WP2FA\Utils\Debugging;

/**
 * Open_SSL - Class for encryption and decryption of the string using open_ssl method
 *
 * @since 2.0.0
 */
if ( ! class_exists( '\WP2FA\Authenticator\Open_SSL' ) ) {

	/**
	 * Responsible for SSL operations
	 */
	class Open_SSL {

		const CIPHER_METHOD    = 'aes-256-ctr';
		const BLOCK_BYTE_SIZE  = 16;
		const DIGEST_ALGORITHM = 'SHA256';

		/**
		 * Internal cache var for the PHP ssl functions availability
		 *
		 * @var mixed|boolean
		 *
		 * @since 2.0.0
		 */
		private static $ssl_enabled = null;

		/**
		 * Encrypts given text
		 *
		 * @param string $text - Text to be encrypted.
		 *
		 * @return string
		 *
		 * @since 2.0.0
		 */
		public static function encrypt( string $text ): string {
			Debugging::log( 'Encrypting a text: ' . $text );
			if ( self::is_ssl_available() ) {
				$iv   = self::secure_random( self::BLOCK_BYTE_SIZE );
				$key  = \openssl_digest( \base64_decode( \wp_salt() ), self::DIGEST_ALGORITHM, true ); //phpcs:ignore
				$text = \openssl_encrypt(
                    $text,
                    self::CIPHER_METHOD,
                    $key,
                    OPENSSL_RAW_DATA,
                    $iv
				);

				$text = \base64_encode( $iv . $text ); //phpcs:ignore
			}
			Debugging::log( 'Encrypted text: ' . $text );

			return $text;
		}

		/**
		 * Decrypts crypt text
		 *
		 * @param string $text - Encrypted text to be decrypted.
		 *
		 * @return string
		 *
		 * @since 2.0.0
		 */
		public static function decrypt( string $text ): string {
			Debugging::log( 'Decrypting a text: ' . $text );

			if ( self::is_ssl_available() ) {
				$decoded_base = \base64_decode( $text ); //phpcs:ignore

				$key = \openssl_digest( \base64_decode( \wp_salt() ), self::DIGEST_ALGORITHM, true ); //phpcs:ignore

				$ivlen = \openssl_cipher_iv_length( self::CIPHER_METHOD );

				$iv             = \substr( $decoded_base, 0, $ivlen );
				$ciphertext_raw = \substr( $decoded_base, $ivlen );
				$text           = \openssl_decrypt( $ciphertext_raw, self::CIPHER_METHOD, $key, OPENSSL_RAW_DATA, $iv );
			}
			Debugging::log( 'Decrypted text: ' . $text );

			return $text;
		}

		/**
		 * Decrypts crypt text
		 *
		 * @param string $text - Encrypted text to be decrypted.
		 *
		 * @return string
		 *
		 * @since 2.0.0
		 */
		public static function decrypt_legacy( string $text ): string {
			Debugging::log( 'Decrypting a text: ' . $text );

			if ( self::is_ssl_available() ) {
				$decoded_base = \base64_decode( $text ); //phpcs:ignore

				$key = \openssl_digest( \base64_decode( WP2FA::get_secret_key() ), self::DIGEST_ALGORITHM, true ); //phpcs:ignore

				$ivlen = \openssl_cipher_iv_length( self::CIPHER_METHOD );

				$iv             = \substr( $decoded_base, 0, $ivlen );
				$ciphertext_raw = \substr( $decoded_base, $ivlen );
				$text           = \openssl_decrypt( $ciphertext_raw, self::CIPHER_METHOD, $key, OPENSSL_RAW_DATA, $iv );
			}
			Debugging::log( 'Decrypted text: ' . $text );

			return $text;
		}

		/**
		 * Generates random bytes by given size
		 *
		 * @param integer $octets - Number of octets for use for random generator.
		 *
		 * @return string
		 *
		 * @since 2.0.0
		 */
		public static function secure_random( int $octets = 0 ): string {
			if ( 0 === $octets ) {
				$octets = self::BLOCK_BYTE_SIZE;
			}

			return \random_bytes( $octets );
		}

		/**
		 * Checks the open ssl methods existence
		 *
		 * @return boolean
		 *
		 * @since 2.0.0
		 */
		public static function is_ssl_available(): bool {
			if ( null === self::$ssl_enabled ) {
				self::$ssl_enabled = false;
				if ( \function_exists( 'openssl_encrypt' ) ) {
					self::$ssl_enabled = true;
				}
			}

			return self::$ssl_enabled;
		}
	}
}
