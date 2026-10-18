<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link: https://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Admin
 */

namespace WP_Rest_Cache_Plugin\Admin;

use WP_Rest_Cache_Plugin\Includes\Caching\Caching;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Admin
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @access private
	 * @var    string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @access private
	 * @var    string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The settings panels for the WP REST Cache settings page.
	 *
	 * @access private
	 * @var array<string,array<string,mixed>> $settings_panels The settings panels for the WP REST Cache settings page.
	 */
	private $settings_panels;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name     = $plugin_name;
		$this->version         = $version;
		$this->settings_panels = [];
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-rest-cache-admin.css', [], $this->version, 'all' );
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only page/sub markers.
		$page = isset( $_GET['page'] ) ? filter_var( $_GET['page'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;
		$sub  = isset( $_GET['sub'] ) ? filter_var( $_GET['sub'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		if ( 'wp-rest-cache' === $page && 'clear-cache' === $sub ) {
			wp_enqueue_style( 'jquery-ui-progressbar', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css', [], $this->version, 'all' );
		}
	}

	/**
	 * Register the scripts for the admin area.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only page/sub markers.
		$page = isset( $_GET['page'] ) ? filter_var( $_GET['page'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;
		$sub  = isset( $_GET['sub'] ) ? filter_var( $_GET['sub'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		if ( 'wp-rest-cache' === $page && 'clear-cache' === $sub ) {
			wp_enqueue_script( 'jquery-ui-progressbar' );
		}
	}

	/**
	 * Add a new menu item under Settings.
	 *
	 * @return void
	 */
	public function create_menu() {
		/**
		 * Change who can see (and alter) the settings for the WP REST Cache.
		 *
		 * Allows to change the capability for the WP REST Cache settings page.
		 *
		 * @since 2019.4.3
		 *
		 * @param string $capability The capability for the settings page.
		 */
		$capability = apply_filters( 'wp_rest_cache/settings_capability', 'administrator' );

		$hook = add_submenu_page(
			'options-general.php',
			'WP REST Cache',
			'WP REST Cache',
			$capability,
			'wp-rest-cache',
			[
				$this,
				'settings_page',
			]
		);

		add_action( "load-$hook", [ $this, 'add_screen_options' ] );
	}

	/**
	 * Add screen options to the WP Admin
	 *
	 * @return void
	 */
	public function add_screen_options() {
		$args = [
			'label'   => __( 'Caches', 'wp-rest-cache' ),
			'default' => Includes\API_Caches_Table::ITEMS_PER_PAGE,
			'option'  => 'caches_per_page',
		];
		add_screen_option( 'per_page', $args );
	}

	/**
	 * Set the caches_per_pages screen option.
	 *
	 * @param int    $option_value Screen option value. Default false to skip.
	 * @param string $option The option name.
	 * @param int    $value The number of rows to use.
	 *
	 * @return int
	 */
	public function set_screen_option( $option_value, $option, $value ): int {
		if ( 'caches_per_page' === $option ) {
			return $value;
		}

		return $option_value;
	}

	/**
	 * Add a settings link to the plugin on the Plugin admin screen.
	 *
	 * @param array<int,string> $links An array of plugin action links.
	 *
	 * @return array<int,string> An array of plugin action links.
	 */
	public function add_plugin_settings_link( $links ): array {
		$links[] = '<a href="' .
					admin_url( 'options-general.php?page=wp-rest-cache' ) . '">' .
					__( 'Settings', 'wp-rest-cache' ) . '</a>';

		return $links;
	}

	/**
	 * Register plugin specific settings.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'wp-rest-cache-settings',
			'wp_rest_cache_timeout',
			[
				'type'              => 'integer',
				'sanitize_callback' => [ $this, 'sanitize_timeout' ],
			]
		);
		register_setting(
			'wp-rest-cache-settings',
			'wp_rest_cache_timeout_interval',
			[
				'type'              => 'integer',
				'sanitize_callback' => [ $this, 'sanitize_timeout_interval' ],
			]
		);
		register_setting(
			'wp-rest-cache-settings',
			'wp_rest_cache_regenerate',
			[
				'type'              => 'string',
				'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
			]
		);
		register_setting(
			'wp-rest-cache-settings',
			'wp_rest_cache_regenerate_interval',
			[
				'type'              => 'string',
				'sanitize_callback' => [ $this, 'sanitize_regenerate_interval' ],
			]
		);
		register_setting(
			'wp-rest-cache-settings',
			'wp_rest_cache_regenerate_number',
			[
				'type'              => 'integer',
				'sanitize_callback' => [ $this, 'sanitize_regenerate_number' ],
			]
		);
		register_setting(
			'wp-rest-cache-settings',
			'wp_rest_cache_memcache_used',
			[
				'type'              => 'string',
				'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
			]
		);
		register_setting(
			'wp-rest-cache-settings',
			'wp_rest_cache_global_cacheable_request_headers',
			[
				'type'              => 'string',
				'sanitize_callback' => [ $this, 'sanitize_cacheable_request_headers' ],
			]
		);
	}

	/**
	 * Sanitize the timeout setting.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return int The sanitized value.
	 */
	public function sanitize_timeout( $value ): int {
		$value = absint( $value );
		return max( 1, $value );
	}

	/**
	 * Sanitize the timeout interval setting.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return int The sanitized value.
	 */
	public function sanitize_timeout_interval( $value ): int {
		$allowed = [
			MINUTE_IN_SECONDS,
			HOUR_IN_SECONDS,
			DAY_IN_SECONDS,
			WEEK_IN_SECONDS,
			MONTH_IN_SECONDS,
			YEAR_IN_SECONDS,
		];
		$value   = absint( $value );
		return in_array( $value, $allowed, true ) ? $value : YEAR_IN_SECONDS;
	}

	/**
	 * Sanitize a checkbox setting.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return string The sanitized value ('1' or '').
	 */
	public function sanitize_checkbox( $value ): string {
		return '1' === $value ? '1' : '';
	}

	/**
	 * Sanitize the regenerate interval setting.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return string The sanitized value.
	 */
	public function sanitize_regenerate_interval( $value ): string {
		$schedules = wp_get_schedules();
		$value     = sanitize_text_field( $value );
		return isset( $schedules[ $value ] ) ? $value : 'twicedaily';
	}

	/**
	 * Sanitize the regenerate number setting.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return int The sanitized value.
	 */
	public function sanitize_regenerate_number( $value ): int {
		$value = absint( $value );
		return max( 1, $value );
	}

	/**
	 * Sanitize the global cacheable request headers setting.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return string The sanitized value.
	 */
	public function sanitize_cacheable_request_headers( $value ): string {
		$value   = sanitize_text_field( $value );
		$headers = array_map( 'trim', explode( ',', $value ) );
		$headers = array_filter( $headers );
		return implode( ',', $headers );
	}

	/**
	 * Display the plugin settings page.
	 *
	 * @return void
	 */
	public function settings_page() {
		/** This filter is documented in the function create_menu(). */
		if ( ! current_user_can( apply_filters( 'wp_rest_cache/settings_capability', 'administrator' ) ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wp-rest-cache' ) );
		}
		$this->settings_panels = apply_filters( 'wp_rest_cache/settings_panels', $this->settings_panels );

		// Sort panels by position.
		uasort(
			$this->settings_panels,
			function ( $a, $b ) {
				$pos_a = $a['position'] ?? 100;
				$pos_b = $b['position'] ?? 100;
				return $pos_a <=> $pos_b;
			}
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only sub marker.
		$sub = isset( $_GET['sub'] ) ? filter_var( $_GET['sub'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;
		if ( empty( $sub ) ) {
			$sub = 'settings';
		}

		// prevent path traversal attacks in the sub parameter.
		$potential_sub_file = __DIR__ . '/partials/sub-' . $sub . '.php';
		if ( dirname( $potential_sub_file ) !== __DIR__ . '/partials' ) {
			$sub = 'settings';
		}

		include_once __DIR__ . '/partials/header.php';
		if ( isset( $this->settings_panels[ $sub ]['template'] ) ) {
			include_once $this->settings_panels[ $sub ]['template'];
		} elseif ( file_exists( __DIR__ . '/partials/sub-' . $sub . '.php' ) ) {
			include_once __DIR__ . '/partials/sub-' . $sub . '.php';
		} else {
			include_once __DIR__ . '/partials/sub-settings.php';
		}
	}

	/**
	 * Add a 'Clear REST cache' button to the wp-admin top bar.
	 *
	 * @return void
	 */
	public function admin_bar_item() {
		/** This filter is documented in the function create_menu(). */
		if ( ! current_user_can( apply_filters( 'wp_rest_cache/settings_capability', 'administrator' ) ) ) {
			return;
		}
		/**
		 * Show or hide the 'Clear REST cache' button in the wp-admin bar.
		 *
		 * Allows to hide (or show under conditions) the 'Clear REST cache button in the wp-admin bar.
		 *
		 * @since 2018.3.1
		 *
		 * @param boolean $show Boolean whether the 'Clear REST cache' button should be shown.
		 */
		$show = apply_filters( 'wp_rest_cache/display_clear_cache_button', true );
		if ( true === $show ) {
			global $wp_admin_bar;

			$args = [
				'id'    => 'wp-rest-cache-clear',
				'title' => '<span class="ab-icon"></span>' . __( 'Clear REST cache', 'wp-rest-cache' ),
				'href'  => self::empty_cache_url(),
			];

			$wp_admin_bar->add_menu( $args );
		}
	}

	/**
	 * Create the url to empty the cache.
	 *
	 * @return string The url to empty the cache.
	 */
	public static function empty_cache_url(): string {
		return wp_nonce_url( admin_url( 'options-general.php?page=wp-rest-cache&sub=clear-cache' ), 'wp_rest_cache_options', 'wp_rest_cache_nonce' );
	}

	/**
	 * Handle the correct actions. I.e. dismiss a notice.
	 *
	 * @return void
	 */
	public function handle_actions() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified below via check_admin_referer.
		$dismiss = isset( $_GET['wp_rest_cache_dismiss'] ) ? sanitize_text_field( wp_unslash( $_GET['wp_rest_cache_dismiss'] ) ) : null;
		if ( null !== $dismiss
			&& check_admin_referer( 'wp-rest-cache-dismiss-notice-' . $dismiss )
		) {
			$user_id           = get_current_user_id();
			$dismissed_notices = get_user_meta( $user_id, 'wp_rest_cache_dismissed_notices', true );
			if ( ! is_array( $dismissed_notices ) ) {
				$dismissed_notices = [];
			}
			if ( ! in_array( $dismiss, $dismissed_notices, true ) ) {
				$dismissed_notices[] = $dismiss;
				update_user_meta( $user_id, 'wp_rest_cache_dismissed_notices', $dismissed_notices );
			}
		}
		if ( isset( $_REQUEST['action'] ) && -1 !== $_REQUEST['action'] ) {
			$wp_rest_cache_list = new \WP_Rest_Cache_Plugin\Admin\Includes\API_Caches_Table( 'endpoint' );
			$wp_rest_cache_list->process_action();
		}
	}

	/**
	 * Add a notice to the wp-admin.
	 *
	 * @param string               $type The type of message (error|warning|success|info).
	 * @param string               $message The message to display.
	 * @param string|bool          $dismissible Boolean, should the message be dismissible or 'permanent' for permanently dismissible notice.
	 * @param array<string,string> $button An array with label + url to display a button with the notice.
	 *
	 * @return void
	 */
	protected function add_notice( $type, $message, $dismissible = true, $button = [] ) {
		$notices = get_option( 'wp_rest_cache_admin_notices', [] );

		if ( ! array_key_exists( $type, $notices ) ) {
			$notices[ $type ] = [];
		}

		if ( ! in_array( $message, array_column( $notices[ $type ], 'message' ), true ) ) {
			$notices[ $type ][] = [
				'message'     => $message,
				'dismissible' => $dismissible,
				'button'      => $button,
			];
			update_option( 'wp_rest_cache_admin_notices', $notices, false );
		}
	}

	/**
	 * Check whether the MU plugin is installed and in sync with the source template.
	 *
	 * Also handles version bumps: if the main plugin's version changes, the previously
	 * installed mu-plugin will no longer match the desired stamped content and will be
	 * re-copied here. On non-direct filesystems (where automatic install is skipped), a
	 * warning notice asks the site owner to copy the file manually.
	 *
	 * @return void
	 */
	public function check_muplugin_existence() {
		if ( \WP_Rest_Cache_Plugin\Includes\Activator::mu_plugin_needs_install() ) {
			\WP_Rest_Cache_Plugin\Includes\Activator::create_mu_plugin();
			if ( \WP_Rest_Cache_Plugin\Includes\Activator::mu_plugin_needs_install() ) {

				$from = '<code>' . substr(
					plugin_dir_path( __DIR__ ) . 'sources/wp-rest-cache.php',
					strpos( plugin_dir_path( __DIR__ ), '/wp-content/' )
				) . '</code>';
				$to   = '<code>' . substr(
					WPMU_PLUGIN_DIR . '/wp-rest-cache.php',
					strpos( WPMU_PLUGIN_DIR, '/wp-content/' )
				) . '</code>';

				$this->add_notice(
					'warning',
					sprintf(
						/* translators: %1$s: source-directory, %2$s: target-directory */
						__( 'You are not getting the best caching result! <br/>Please copy %1$s to %2$s', 'wp-rest-cache' ),
						$from,
						$to
					),
					false
				);
			}
		}
	}

	/**
	 * Check if external object caching is being used, if so display a warning for needed Memcache(d) settings.
	 *
	 * @return void
	 */
	public function check_memcache_ext_object_caching() {
		if ( wp_using_ext_object_cache()
			&& ( class_exists( 'Memcache' ) || class_exists( 'Memcached' ) )
			&& ! Caching::get_instance()->get_memcache_used() ) {
			$this->add_notice(
				'warning',
				__( 'We have detected you are using external object caching. If you are using Memcache(d) as external object cache, please make sure you visit this plugin\'s settings page and check the `Using Memcache(d)` checkbox.', 'wp-rest-cache' ),
				'permanent'
			);
		}
	}

	/**
	 * Display notices (if any) on the Admin dashboard
	 *
	 * @return void
	 */
	public function display_notices() {
		if ( ! in_array( get_current_screen()->base, [ 'plugins', 'dashboard', 'settings_page_wp-rest-cache' ], true ) ) {
			return;
		}
		$notices = get_option( 'wp_rest_cache_admin_notices', [] );
		if ( is_array( $notices ) && count( $notices ) ) {
			$user_id           = get_current_user_id();
			$dismissed_notices = get_user_meta( $user_id, 'wp_rest_cache_dismissed_notices', true );
			foreach ( $notices as $type => $messages ) {
				foreach ( $messages as $message ) {
					if ( ! is_array( $dismissed_notices ) || ! in_array( esc_attr( md5( $message['message'] ) ), $dismissed_notices, true ) ) {
						?>
						<div
							class="notice notice-<?php echo esc_attr( $type ); ?> <?php echo ( true === $message['dismissible'] ) ? 'is-dismissible' : ''; ?>">
							<p><strong>WP REST Cache:</strong> <?php echo wp_kses_post( $message['message'] ); ?></p>
							<?php if ( isset( $message['button']['url'], $message['button']['label'] ) ) : ?>
								<p><a class="<?php echo esc_attr( $message['button']['class'] ?? 'button' ); ?>" href="<?php echo esc_attr( $message['button']['url'] ); ?>"><?php echo esc_html( $message['button']['label'] ); ?></a></p>
							<?php endif; ?>
							<?php if ( 'permanent' === $message['dismissible'] ) : ?>
								<?php
								$url = get_admin_url() . wp_nonce_url(
									'?wp_rest_cache_dismiss=' . esc_attr( md5( $message['message'] ) ),
									'wp-rest-cache-dismiss-notice-' . esc_attr( md5( $message['message'] ) )
								);
								?>
								<p><a class="button"
										href="<?php echo esc_attr( $url ); ?>"><?php echo esc_html_e( 'Hide this message', 'wp-rest-cache' ); ?></a></p>
							<?php endif; ?>
						</div>
						<?php
					}
				}
			}

			delete_option( 'wp_rest_cache_admin_notices' );
		}
	}

	/**
	 * Unschedule or schedule the cron based on the regenerate setting.
	 *
	 * @param mixed $old_value The old option value.
	 * @param mixed $value The new option value.
	 *
	 * @return void
	 */
	public function regenerate_updated( $old_value, $value ) {
		if ( '1' === $value ) {
			$wp_rest_cache_regenerate_interval = Caching::get_instance()->get_regenerate_interval();
			wp_schedule_event( time(), $wp_rest_cache_regenerate_interval, 'wp_rest_cache_regenerate_cron' );
		} else {
			wp_clear_scheduled_hook( 'wp_rest_cache_regenerate_cron' );
		}
	}

	/**
	 * Update regenerate interval based on new setting.
	 *
	 * @param mixed $old_value The old option value.
	 * @param mixed $value The new option value.
	 *
	 * @return void
	 */
	public function regenerate_interval_updated( $old_value, $value ) {
		if ( Caching::get_instance()->should_regenerate() ) {
			wp_clear_scheduled_hook( 'wp_rest_cache_regenerate_cron' );
			wp_schedule_event( time(), $value, 'wp_rest_cache_regenerate_cron' );
		}
	}

	/**
	 * Flush caches in batches. Used through an ajax call from the 'Clear REST Cache' button.
	 *
	 * @return void
	 */
	public function flush_caches() {
		check_ajax_referer( 'wp_rest_cache_clear_cache_ajax', 'wp_rest_cache_nonce' );
		/** This filter is documented in the function create_menu(). */
		if ( ! current_user_can( apply_filters( 'wp_rest_cache/settings_capability', 'administrator' ) ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'wp-rest-cache' ) );
		}
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- nonce verified above via check_ajax_referer.
		$delete_caches = isset( $_POST['delete_caches'] ) ? filter_var( $_POST['delete_caches'], FILTER_VALIDATE_BOOLEAN ) : false;
		$cache_filter  = isset( $_POST['cache_filter'] ) ? filter_var( $_POST['cache_filter'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$caching = Caching::get_instance();
		$caching->delete_all_caches( $delete_caches, ! empty( $cache_filter ) ? $cache_filter : false );

		// `percentage` is deprecated: we delete all caches at once now, so the progress bar should be removed.
		wp_send_json( [ 'percentage' => 100 ] );
	}

	/**
	 * Upon plugin activation show a message about flushing the REST cache (or flush specific caches when certain
	 * plugins are activated).
	 *
	 * @param string  $plugin The plugin that has just been activated.
	 * @param boolean $network_wide Whether the plugin has been activated network wide.
	 *
	 * @return void
	 */
	public function activated_plugin( $plugin, $network_wide ) {
		// Wordfence alters the output of the users endpoint, so flush all users endpoint caches.
		if ( 'wordfence/wordfence.php' === $plugin ) {
			$rest_prefix = sprintf( '/%s/', get_option( 'wp_rest_cache_rest_prefix', 'wp-json' ) );
			$caching     = Caching::get_instance();
			if ( $network_wide ) {
				$site_ids = get_sites( [ 'fields' => 'ids' ] );
				foreach ( $site_ids as $site_id ) {
					switch_to_blog( $site_id );
					$caching->delete_cache_by_endpoint( $rest_prefix . 'wp/v2/users', Caching::FLUSH_LOOSE );
					restore_current_blog();
				}
			} else {
				$caching->delete_cache_by_endpoint( $rest_prefix . 'wp/v2/users', Caching::FLUSH_LOOSE );
			}
		} else {
			$this->add_notice(
				'warning',
				__( 'A new plugin has been activated. This might effect the WP REST API output, so please consider if clearing the REST Cache is necessary.', 'wp-rest-cache' ),
				false,
				[
					'label' => __( 'Clear REST cache', 'wp-rest-cache' ),
					'url'   => self::empty_cache_url(),
				]
			);
		}
	}

	/**
	 * Upon plugin deactivation show a message about flushing the REST cache.
	 *
	 * @return void
	 */
	public function deactivated_plugin() {
		$this->add_notice(
			'warning',
			__( 'A plugin has been deactivated. This might effect the WP REST API output, so please consider if clearing the REST Cache is necessary.', 'wp-rest-cache' ),
			false,
			[
				'label' => __( 'Clear REST cache', 'wp-rest-cache' ),
				'url'   => self::empty_cache_url(),
			]
		);
	}

	/**
	 * Add custom WP CLI commands.
	 *
	 * @throws \Exception An exception is thrown if the command contains an error.
	 *
	 * @return void
	 */
	public function add_cli_commands() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'wp-rest-cache', new \WP_Rest_Cache_Plugin\Includes\CLI\Flush_Command() );
		}
	}

	/**
	 * Filter the settings panels for the WP REST Cache settings page.
	 *
	 * @param array<string,array<string,mixed>> $panels The settings panels for the WP REST Cache settings page.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function filter_settings_panels( $panels ) {
		$settings_panels = [
			'settings'     => [
				'label'    => __( 'Settings', 'wp-rest-cache' ),
				'position' => 10,
			],
			'endpoint-api' => [
				'label'    => __( 'Endpoint API Caches', 'wp-rest-cache' ),
				'position' => 20,
			],
			'clear-cache'  => [
				'label'    => __( 'Clear Caches', 'wp-rest-cache' ),
				'position' => 30,
			],
		];

		return array_merge( $panels, $settings_panels );
	}

	/**
	 * Display a message about the Pro version of the plugin.
	 *
	 * @return void
	 */
	public function display_pro_message() {
		$this->add_notice(
			'success',
			__( 'Did you know that with the new Pro version of the WP REST Cache plugin you can easily configure custom endpoints to be cached?', 'wp-rest-cache' ),
			'permanent',
			[
				'label' => __( 'Upgrade to Pro', 'wp-rest-cache' ),
				'url'   => 'https://plugins.acato.nl/',
				'class' => 'button button-primary',
			]
		);
	}
}
