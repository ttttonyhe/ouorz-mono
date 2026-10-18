<?php
/**
 * Fired during plugin activation
 *
 * @link: https://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes
 */

namespace WP_Rest_Cache_Plugin\Includes;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Activator {

	/**
	 * Activate the plugin. Add default options and copy Must-Use plugin to correct directory.
	 *
	 * @return void
	 */
	public static function activate() {
		if ( ! get_option( 'wp_rest_cache_allowed_endpoints' ) ) {
			add_option( 'wp_rest_cache_allowed_endpoints', [], '', false );
		}
		if ( ! get_option( 'wp_rest_cache_disallowed_endpoints' ) ) {
			add_option( 'wp_rest_cache_disallowed_endpoints', [], '', false );
		}
		if ( ! get_option( 'wp_rest_cache_rest_prefix' ) ) {
			add_option( 'wp_rest_cache_rest_prefix', rest_get_url_prefix(), '', false );
		}
		if ( ! get_option( 'wp_rest_cache_cacheable_request_headers' ) ) {
			add_option( 'wp_rest_cache_cacheable_request_headers', [], '', false );
		}
		if ( ! get_option( 'wp_rest_cache_allowed_request_methods' ) ) {
			add_option( 'wp_rest_cache_allowed_request_methods', [ 'GET' ], '', false );
		}
		if ( ! get_option( 'wp_rest_cache_uncached_parameters' ) ) {
			add_option( 'wp_rest_cache_uncached_parameters', [], '', false );
		}
		if ( is_null( get_option( 'wp_rest_cache_hit_recording', null ) ) ) {
			add_option( 'wp_rest_cache_hit_recording', 1, '', true );
		}

		self::create_mu_plugin();
	}

	/**
	 * Create a Must-Use plugin to handle caching asap. Before loading of other plugins and/or theme.
	 *
	 * The mu-plugin's `Version:` header is stamped with the main plugin's current version so it
	 * automatically stays in sync when the main plugin is upgraded.
	 *
	 * @return void
	 */
	public static function create_mu_plugin() {
		// Make sure filesystem methods are loaded (not always the case when loaded through mu-plugin).
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$access_type = get_filesystem_method();
		if ( 'direct' !== $access_type ) {
			return;
		}

		$desired = self::get_desired_mu_plugin_content();
		if ( false === $desired ) {
			return;
		}

		// No filter_input, see https://stackoverflow.com/questions/25232975/php-filter-inputinput-server-request-method-returns-null/36205923.
		$request_uri = filter_var( $_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL );
		$url         = Util::get_home_url() . $request_uri;
		$creds       = request_filesystem_credentials( $url );
		WP_Filesystem( $creds );
		global $wp_filesystem;

		if ( ! $wp_filesystem->is_dir( WPMU_PLUGIN_DIR ) ) {
			$wp_filesystem->mkdir( WPMU_PLUGIN_DIR );
		}

		$target = WPMU_PLUGIN_DIR . '/wp-rest-cache.php';
		$wp_filesystem->put_contents( $target, $desired );
	}

	/**
	 * Determine whether the installed mu-plugin is missing or differs from the desired source.
	 *
	 * Used by the admin_init drift check to detect both first-run installs and version bumps.
	 *
	 * @return bool True when the mu-plugin needs to be (re-)installed.
	 */
	public static function mu_plugin_needs_install() {
		$target = WPMU_PLUGIN_DIR . '/wp-rest-cache.php';
		if ( ! file_exists( $target ) ) {
			return true;
		}

		$desired = self::get_desired_mu_plugin_content();
		if ( false === $desired ) {
			// Cannot determine desired state — treat as in-sync to avoid a spurious warning.
			return false;
		}

		return md5_file( $target ) !== md5( $desired );
	}

	/**
	 * Build the desired mu-plugin file contents by stamping the main plugin's current
	 * `Version:` header value onto the source template.
	 *
	 * @return string|false Content to write, or false when the source cannot be read.
	 */
	private static function get_desired_mu_plugin_content() {
		$source   = plugin_dir_path( __DIR__ ) . 'sources/wp-rest-cache.php';
		$contents = file_get_contents( $source ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reading a plugin-owned template file.
		if ( false === $contents ) {
			return false;
		}

		$plugin_data = get_file_data(
			plugin_dir_path( __DIR__ ) . 'wp-rest-cache.php',
			[ 'Version' => 'Version' ]
		);

		if ( ! empty( $plugin_data['Version'] ) ) {
			$contents = preg_replace(
				'/^(\s*\*\s*Version:\s*)[^\r\n]+/m',
				'${1}' . $plugin_data['Version'],
				$contents,
				1
			);
		}

		return $contents;
	}
}
