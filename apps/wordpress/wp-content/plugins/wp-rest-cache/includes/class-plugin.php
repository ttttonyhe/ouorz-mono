<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link: https://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes
 */

namespace WP_Rest_Cache_Plugin\Includes;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Plugin {


	/**
	 * The unique identifier of this plugin.
	 *
	 * @access protected
	 * @var    string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @access protected
	 * @var    string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 */
	public function __construct() {
		$this->plugin_name = 'wp-rest-cache';
		$this->version     = '2022.2.2';

		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_api_hooks();
		$this->define_caching_hooks();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WP_Rest_Cache_Plugin\Includes\I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @return void
	 */
	private function set_locale() {

		$plugin_i18n = new I18n();

		add_action( 'plugins_loaded', [ $plugin_i18n, 'load_plugin_textdomain' ] );

	}

	/**
	 * Register all the hooks related to the admin area functionality of the plugin.
	 *
	 * @return void
	 */
	private function define_admin_hooks() {

		$plugin_admin = new \WP_Rest_Cache_Plugin\Admin\Admin( $this->get_plugin_name(), $this->get_version() );

		add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts' ) );
		// Create custom plugin settings menu.
		add_action( 'admin_menu', [ $plugin_admin, 'create_menu' ] );
		add_action( 'admin_init', [ $plugin_admin, 'register_settings' ] );
		add_action( 'admin_init', [ $plugin_admin, 'check_muplugin_existence' ] );
		add_action( 'admin_init', [ $plugin_admin, 'check_memcache_ext_object_caching' ] );
		add_action( 'admin_init', [ $plugin_admin, 'handle_actions' ] );
		add_action( 'admin_notices', [ $plugin_admin, 'display_notices' ] );
		add_action( 'network_admin_notices', [ $plugin_admin, 'display_notices' ] );
		add_action( 'wp_before_admin_bar_render', [ $plugin_admin, 'admin_bar_item' ], 999 );

		// set-screen-option should be deprecated in favor of set_screen_option-{$option} but in WP version 5.4.2 there
		// is a bug making both calls necessary until that bug is fixed in the next version.
		// See: https://core.trac.wordpress.org/ticket/50392 for more info.
		add_filter( 'set-screen-option', [ $plugin_admin, 'set_screen_option' ], 10, 3 );
		add_filter( 'set_screen_option_caches_per_page', [ $plugin_admin, 'set_screen_option' ], 10, 3 );

		add_filter(
			'plugin_action_links_' . trailingslashit( dirname( plugin_basename( __DIR__ ) ) ) . 'wp-rest-cache.php',
			[
				$plugin_admin,
				'add_plugin_settings_link',
			]
		);
		add_action( 'update_option_wp_rest_cache_regenerate', [ $plugin_admin, 'regenerate_updated' ], 10, 3 );
		add_action(
			'update_option_wp_rest_cache_regenerate_interval',
			[
				$plugin_admin,
				'regenerate_interval_updated',
			],
			10,
			3
		);
		add_action( 'wp_ajax_flush_caches', [ $plugin_admin, 'flush_caches' ], 10, 1 );
		add_action( 'activated_plugin', [ $plugin_admin, 'activated_plugin' ], 10, 2 );
		add_action( 'deactivated_plugin', [ $plugin_admin, 'deactivated_plugin' ], 10, 2 );

		add_action( 'cli_init', [ $plugin_admin, 'add_cli_commands' ] );
	}

	/**
	 * Register all the hooks related to the api functionality of the plugin.
	 *
	 * @return void
	 */
	private function define_api_hooks() {
		$endpoint_api = new API\Endpoint_Api();

		add_action( 'init', [ $endpoint_api, 'save_options' ] );
		add_action( 'rest_api_init', [ $endpoint_api, 'save_options' ] );
		add_filter( 'wp_rest_cache/allowed_endpoints', [ $endpoint_api, 'add_wordpress_endpoints' ] );
		add_filter( 'wp_rest_cache/determine_object_type', [ $endpoint_api, 'determine_object_type' ], 10, 4 );

		$item_api = new API\Item_Api();

		add_filter( 'register_post_type_args', [ $item_api, 'set_post_type_rest_controller' ], 10, 2 );
		add_filter( 'register_taxonomy_args', [ $item_api, 'set_taxonomy_rest_controller' ], 10, 2 );
	}

	/**
	 * Register all the hooks related to the caching functionality of the plugin.
	 *
	 * @return void
	 */
	private function define_caching_hooks() {
		$caching = Caching\Caching::get_instance();

		add_action( 'init', [ $caching, 'update_database_structure' ] );

		add_action( 'save_post', [ $caching, 'save_post' ], 999, 3 );
		add_action( 'delete_post', [ $caching, 'delete_post' ] );
		add_action( 'transition_post_status', [ $caching, 'transition_post_status' ], 10, 3 );

		add_action( 'created_term', [ $caching, 'created_term' ], 999, 3 );
		add_action( 'edited_term', [ $caching, 'edited_term' ], 999, 3 );
		add_action( 'delete_term', [ $caching, 'delete_term' ], 10, 5 );

		add_action( 'profile_update', [ $caching, 'profile_update' ], 999, 2 );
		add_action( 'user_register', [ $caching, 'user_register' ], 999, 1 );
		add_action( 'deleted_user', [ $caching, 'deleted_user' ] );

		add_action( 'edit_comment', [ $caching, 'delete_comment_type_related_caches' ], 999, 2 );
		add_action( 'deleted_comment', [ $caching, 'delete_comment_related_caches' ], 10, 2 );
		add_action( 'trashed_comment', [ $caching, 'delete_comment_related_caches' ], 10, 2 );
		add_action( 'untrashed_comment', [ $caching, 'delete_comment_type_related_caches' ], 999, 2 );
		add_action( 'spammed_comment', [ $caching, 'delete_comment_related_caches' ], 10, 2 );
		add_action( 'unspammed_comment', [ $caching, 'delete_comment_type_related_caches' ], 999, 2 );
		add_action( 'wp_insert_comment', [ $caching, 'delete_comment_type_related_caches' ], 999, 2 );
		add_action( 'comment_post', [ $caching, 'delete_comment_type_related_caches' ], 999, 2 );

		add_action( 'wp_rest_cache_regenerate_cron', [ $caching, 'regenerate_expired_caches' ] );
		add_action( 'wp_rest_cache_cleanup_deleted_caches', [ $caching, 'cleanup_deleted_caches' ] );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
