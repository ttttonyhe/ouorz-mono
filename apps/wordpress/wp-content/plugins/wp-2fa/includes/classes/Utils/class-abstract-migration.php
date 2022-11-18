<?php
/**
 * Abstract migration class.
 *
 * @package    wp2fa
 * @subpackage utils
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Utils;

use WP2FA\Utils\Settings_Utils as Settings_Utils;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Abstract AMigration class
 */
if ( ! class_exists( '\WP2FA\Utils\Abstract_Migration' ) ) {

	/**
	 * Utility class to ease the migration process.
	 *
	 * Every migration must go in its own method
	 * The naming convention is migrateUpTo_XXX where XXX is the number of the version,
	 * format is numbers only.
	 * Example: migration for version upto 1.4 must be in migrateUpTo_14 method
	 *
	 * The numbers in the names of the methods must have exact numbers count as in the selected
	 * version in use, even if there are silent numbers for some of the major versions as 1, 2, 3 etc. (the .0.0 is skipped / silent)
	 * Example:
	 *  - if X.X.X is selected for version number, then for version 1.1 method must have "...migrateUpTo_110..." in its name
	 *  - if X.X is selected for version number, then for version 1, method must have "...migrateUpTo_10..." in its name
	 *
	 * Note: you can add prefix to the migration method, if that is necessary, but "migrateUpTo_" is a must -
	 * the name must contain that @see getAllMigrationMethodsAsNumbers of that class.
	 * For version extraction the number following the last '_' will be used
	 * TODO: the mandatory part of the method name can be a setting in the class, but is that a good idea?
	 *
	 * Note: order of the methods is not preserved - version numbers will be used for ordering
	 *
	 * @package WP2FA\Utils
	 * @since 1.6
	 */
	class Abstract_Migration {

		/**
		 * Extracted version from the DB (WP option)
		 *
		 * @var string
		 */
		protected static $stored_version = '';

		/**
		 * The name of the option from which we should extact version
		 * Note: version is expected in version format - 1.0.0; 1; 1.0; 1.0.0.0
		 * Note: only numbers will be processed
		 *
		 * @var string
		 */
		protected static $version_option_name = '';

		/**
		 * The constant name where the plugin version is stored
		 * Note: version is expected in version format - 1.0.0; 1; 1.0; 1.0.0.0
		 * Note: only numbers will be processed
		 *
		 * @var string
		 *
		 * @since 2.2.0
		 */
		protected static $const_name_of_plugin_version = '';

		/**
		 * Used for adding proper pads for the missing numbers
		 * Version number format used here depends on selection for how many numbers will be used for representing version
		 *
		 * For X.X     use 2;
		 * For X.X.X   use 3;
		 * For X.X.X.X use 4;
		 *
		 * etc.
		 *
		 * Example: if selected version format is X.X.X that means that 3 digits are used for versioning.
		 * And current version is stored as 2 (no suffix 0.0) that means that it will be normalized as 200.
		 *
		 * @var integer
		 */
		protected static $pad_length = 3;

		/**
		 * Collects all the migration methods which needs to be executed in order and executes them
		 *
		 * @return void
		 */
		public static function migrate() {

			if ( version_compare( static::get_stored_version(), \constant( static::$const_name_of_plugin_version ), '<' ) ) {

				$stored_version_as_number  = static::normalize_version( static::get_stored_version() );
				$target_version_as_number  = static::normalize_version( \constant( static::$const_name_of_plugin_version ) );
				$method_as_version_numbers = static::get_all_migration_methods_as_numbers();

				$migrate_methods = array_filter(
					$method_as_version_numbers,
					function( $method, $key ) use ( &$stored_version_as_number, &$target_version_as_number ) {
						if ( $target_version_as_number > $stored_version_as_number ) {
							return ( in_array( $key, range( $stored_version_as_number, $target_version_as_number ), true ) );
						}

						return false;
					},
					ARRAY_FILTER_USE_BOTH
				);

				if ( ! empty( $migrate_methods ) ) {
					\ksort( $migrate_methods );
					foreach ( $migrate_methods as $method ) {
						static::{$method}();
					}
				}

				self::store_updated_version();
			}

			/**
			 * Downgrading the plugin? Set the version number.
			 * Leave the rest as is.
			 *
			 * @return void
			 *
			 * @since 2.2.0
			 */
			if ( version_compare( static::get_stored_version(), \constant( static::$const_name_of_plugin_version ), '>' ) ) {
				self::store_updated_version();
			}
		}

		/**
		 * Extracts currently stored version from the DB
		 *
		 * @return string
		 */
		private static function get_stored_version() {

			if ( '' === trim( static::$stored_version ) ) {
				static::$stored_version = Settings_Utils::get_option( static::$version_option_name, '0.0.0' );
			}

			return static::$stored_version;
		}

		/**
		 * Stores the version to which we migrated
		 *
		 * @return void
		 */
		private static function store_updated_version() {
			Settings_Utils::update_option( static::$version_option_name, \constant( static::$const_name_of_plugin_version ) );
		}

		/**
		 * Normalized the version numbers to numbers
		 *
		 * Version format is expected to be as follows:
		 * X.X.X
		 *
		 * All non numeric values will be removed from the version string
		 *
		 * Note: version is expected in version format - 1.0.0; 1; 1.0; 1.0.0.0
		 * Note: only numbers will be processed
		 *
		 * @param string $version - The version string we have to use.
		 *
		 * @return string
		 */
		private static function normalize_version( string $version ) {
			$version_as_number = (int) filter_var( $version, FILTER_SANITIZE_NUMBER_INT );

			if ( self::$pad_length > strlen( $version_as_number ) ) {
				$version_as_number = str_pad( $version_as_number, static::$pad_length, '0', STR_PAD_RIGHT );
			}

			return $version_as_number;
		}

		/**
		 * Collects all the migration methods from the class and stores them in the array
		 * Array is in following format:
		 * key - number of the version
		 * value - name of the method
		 *
		 * @return array
		 */
		private static function get_all_migration_methods_as_numbers() {
			$class_methods = \get_class_methods( get_called_class() );

			$method_as_version_numbers = array();
			foreach ( $class_methods as $method ) {
				if ( false !== \strpos( $method, 'migrate_up_to_' ) ) {
					$ver                               = \substr( $method, \strrpos( $method, '_' ) + 1, \strlen( $method ) );
					$method_as_version_numbers[ $ver ] = $method;
				}
			}

			return $method_as_version_numbers;
		}
	}
}
