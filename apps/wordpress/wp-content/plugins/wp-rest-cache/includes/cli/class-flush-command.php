<?php
/**
 * WP CLI command for flushing caches.
 *
 * @link: https://www.acato.nl
 * @since 2021.2.0
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/CLI
 */

namespace WP_Rest_Cache_Plugin\Includes\CLI;

/**
 * WP CLI command for flushing caches.
 *
 * Adds a WP CLI command for flushing caches from the command line.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/CLI
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Flush_Command extends \WP_CLI_Command {

	/**
	 * Flush caches from the WP REST Cache.
	 *
	 * [<object_type>]
	 * : The object type for which to flush caches.
	 *
	 * [--delete]
	 * : Delete all caches instead of flushing them.
	 *
	 * [--related=<related>]
	 * : Delete all caches related to the given object ID. (Only possible with an object type given.)
	 *
	 * ## EXAMPLES
	 *
	 *      wp wp-rest-cache flush                      # Flush all caches.
	 *      wp wp-rest-cache flush --delete             # Delete all caches.
	 *      wp wp-rest-cache flush post                 # Flush all caches for posts.
	 *      wp wp-rest-cache flush page --related=12    # Flush all related cache for page with ID=12.
	 *
	 * @subcommand flush
	 *
	 * @param   array<int,mixed>    $args All positional arguments for the command.
	 * @param   array<string,mixed> $assoc_args All associative arguments for the command.
	 *
	 * @return void
	 *
	 * @throws \Exception An exception is thrown when an error occurs.
	 */
	public function flush( $args, $assoc_args ) {
		$object_type = isset( $args[0] ) ? $args[0] : false;
		$delete      = \WP_CLI\Utils\get_flag_value( $assoc_args, 'delete', false );
		$related     = \WP_CLI\Utils\get_flag_value( $assoc_args, 'related', false );
		$caching     = \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance();

		if ( false !== $related && false === $object_type ) {
			\WP_CLI::error( __( 'Flag --related is only allowed when an object type is given.', 'wp-rest-cache' ) );
		}

		if ( ! $object_type || 'all' === $object_type ) {
			$nr_of_flushed = $caching->delete_all_caches( $delete );
		} else {
			if ( ! $related ) {
				$nr_of_flushed = $caching->delete_object_type_caches( $object_type, $delete );
			} else {
				$nr_of_flushed = $caching->delete_related_caches( $related, $object_type, false, $delete );
			}
		}

		\WP_CLI::success(
			sprintf(
				// translators: %1$s: Type of action Deleted|Flushed, %2$d: Number of flushed/deleted caches.
				__( '%1$s %2$d caches', 'wp-rest-cache' ),
				( $delete ? __( 'Deleted', 'wp-rest-cache' ) : __( 'Flushed', 'wp-rest-cache' ) ),
				$nr_of_flushed
			)
		);
	}
}
