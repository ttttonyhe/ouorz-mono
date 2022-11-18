<?php
/**
 * Responsible for logging.
 *
 * @package    wp2fa
 * @subpackage utils
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 * @since 1.4.2
 */

namespace WP2FA\Utils;

/**
 * Utility class for creating modal popup markup.
 *
 * @package WP2FA\Utils
 * @since 1.4.2
 */
class Debugging {

	/**
	 * Local cache for the logging dir so that it doesn't need to be repopulated each time get_logging_dir_path is called.
	 *
	 * @var string
	 */
	private static $logging_dir_path = '';

	/**
	 * Retrieve the logging status
	 *
	 * @return boolean
	 */
	private static function is_logging_enabled() {
		if ( WP_DEBUG ) {
			/**
			 * Enables / Disables the logging for the plugin.
			 *
			 * @param bool $disabled - Default logging for the plugin.
			 */
			return apply_filters( WP_2FA_PREFIX . 'logging_enabled', false );
		}

		return false;
	}

	/**
	 * Logs the given message
	 *
	 * @param string $message - The message to log.
	 *
	 * @return void
	 */
	public static function log( $message ) {
		if ( self::is_logging_enabled() ) {
			self::write_to_log( self::get_log_timestamp() . "\n" . $message . "\n" . __( 'Current memory usage: ', 'wp-2fa' ) . memory_get_usage( true ) . "\n" );
		}
	}

	/**
	 * Retrieves the path to the log file
	 *
	 * @return string
	 */
	private static function get_logging_dir_path() {
		if ( strlen( self::$logging_dir_path ) === 0 ) {
			$uploads_dir            = wp_upload_dir( null, false );
			self::$logging_dir_path = trailingslashit( trailingslashit( $uploads_dir['basedir'] ) . WP_2FA_LOGS_DIR );
		}

		return self::$logging_dir_path;
	}

	/**
	 * Write data to log file.
	 *
	 * @param string $data     - Data to write to file.
	 * @param bool   $override - Set to true if overriding the file.
	 * @return bool
	 */
	private static function write_to_log( $data, $override = false ) {
		$logging_dir_path = self::get_logging_dir_path();
		if ( ! is_dir( $logging_dir_path ) ) {
			self::create_index_file();
			self::create_htaccess_file();
		}

		$log_file_name = gmdate( 'Y-m-d' );
		return self::write_to_file( 'wp-2fa-debug-' . $log_file_name . '.log', $data, $override );
	}

	/**
	 * Create an index.php file, if none exists, in order to
	 * avoid directory listing in the specified directory.
	 *
	 * @return bool
	 */
	private static function create_index_file() {
		return self::write_to_file( 'index.php', '<?php // Silence is golden' );
	}

	/**
	 * Create an .htaccess file, if none exists, in order to
	 * block access to directory listing in the specified directory.
	 *
	 * @return bool
	 */
	private static function create_htaccess_file() {
		return self::write_to_file( '.htaccess', 'Deny from all' );
	}

	/**
	 * Write data to log file in the uploads directory.
	 *
	 * @param string $filename - File name.
	 * @param string $content  - Contents of the file.
	 * @param bool   $override - (Optional) True if overriding file contents.
	 * @return bool
	 */
	private static function write_to_file( $filename, $content, $override = false ) {
		global $wp_filesystem;
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();

		$logging_dir = self::get_logging_dir_path();

		$result = false;

		if ( ! is_dir( $logging_dir ) ) {
			if ( false === wp_mkdir_p( $logging_dir ) ) {
				return false;
			}
		}

		$filepath = $logging_dir . $filename;
		if ( ! $wp_filesystem->exists( $filepath ) || $override ) {
			$result = $wp_filesystem->put_contents( $filepath, $content );
		} else {
			$existing_content = $wp_filesystem->get_contents( $filepath );
			$result           = $wp_filesystem->put_contents( $filepath, $existing_content . $content );
		}

		return $result;
	}

	/**
	 * Returns the timestamp for log files.
	 *
	 * @return string
	 */
	private static function get_log_timestamp() {
		return '[' . gmdate( 'd-M-Y H:i:s' ) . ' UTC]';
	}
}
