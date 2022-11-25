<?php
/**
 * Class for displaying a list of caches.
 *
 * @link: https://www.acato.nl
 * @since 2018.4.2
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/API
 */

namespace WP_Rest_Cache_Plugin\Admin\Includes;

if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
/**
 * Class for displaying a list of caches.
 *
 * Retrieves a list of caches and displays it WordPress-style.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Admin/Includes
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class API_Caches_Table extends \WP_List_Table {

	/**
	 * The default number of items per page.
	 *
	 * @var int The default number of items per page.
	 */
	const ITEMS_PER_PAGE = 5;

	/**
	 * The current API Type for this list.
	 *
	 * @access private
	 * @var string Current API Type.
	 */
	private static $api_type;

	/**
	 * API_Caches_Table constructor.
	 *
	 * @param string $api_type Current API Type.
	 *
	 * @throws \Exception If invalid API Type is supplied.
	 */
	public function __construct( $api_type ) {
		if ( 'endpoint' !== $api_type ) {
			throw new \Exception(
				sprintf(
					/* translators: %s: api-type */
					__( 'Invalid API type: %s', 'wp-rest-cache' ),
					$api_type
				)
			);
		}

		self::$api_type = $api_type;

		$args = [
			'singular' => __( 'Endpoint API Cache', 'wp-rest-cache' ),
			'plural'   => __( 'Endpoint API Caches', 'wp-rest-cache' ),
			'ajax'     => false,
		];
		parent::__construct( $args );
	}

	/**
	 * Get a list of caches for the current page.
	 *
	 * @param int $per_page The number of items per page.
	 * @param int $page_number The current page number.
	 *
	 * @return array<int,array<string,mixed>> An array of caches.
	 */
	public static function get_caches( $per_page = self::ITEMS_PER_PAGE, $page_number = 1 ): array {
		return \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->get_api_data( self::$api_type, $per_page, $page_number );
	}

	/**
	 * Clear the selected cache record.
	 *
	 * @param string $cache_key The cache key for the cache that needs to be cleared.
	 * @param bool   $force Whether the cache should be deleted or just flushed.
	 *
	 * @return void
	 */
	public static function clear_cache( $cache_key, $force = false ) {
		\WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->delete_cache( $cache_key, $force );
	}

	/**
	 * Get the record count.
	 *
	 * @return int The record count.
	 */
	public static function record_count(): int {
		return \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->get_record_count( self::$api_type );
	}

	/**
	 * Echo the message for no records found.
	 *
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'No caches available', 'wp-rest-cache' );
	}

	/**
	 * Get the output for the cache_key column.
	 *
	 * @param array<string,mixed> $item The current item.
	 *
	 * @return string The HTML output.
	 */
	public function column_cache_key( $item ): string {
		$page         = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$sub          = filter_input( INPUT_GET, 'sub', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$flush_nonce  = wp_create_nonce( 'wp_rest_cache_flush_cache' );
		$delete_nonce = wp_create_nonce( 'wp_rest_cache_delete_cache' );
		$title        = sprintf(
			'<strong><a href="?page=%s&sub=%s&cache_key=%s">%s</a></strong>',
			esc_attr( $page ),
			'cache-details',
			esc_attr( $item['cache_key'] ),
			$item['cache_key']
		);

		$actions                  = [];
		$actions['cache-details'] = sprintf(
			'<a href="?page=%s&sub=%s&cache_key=%s">%s</a>',
			esc_attr( $page ),
			'cache-details',
			esc_attr( $item['cache_key'] ),
			__( 'Details', 'wp-rest-cache' )
		);
		if ( $item['is_active'] ) {
			$actions['flush'] = sprintf(
				'<a href="?page=%s&sub=%s&action=%s&cache_key=%s&wp_rest_cache_nonce=%s">%s</a>',
				esc_attr( $page ),
				esc_attr( $sub ),
				'flush',
				esc_attr( $item['cache_key'] ),
				$flush_nonce,
				__( 'Flush cache', 'wp-rest-cache' )
			);
		}
		$actions['delete'] = sprintf(
			'<a href="?page=%s&sub=%s&action=%s&cache_key=%s&wp_rest_cache_nonce=%s">%s</a>',
			esc_attr( $page ),
			esc_attr( $sub ),
			'delete',
			esc_attr( $item['cache_key'] ),
			$delete_nonce,
			__( 'Delete cache record', 'wp-rest-cache' )
		);

		return $title . $this->row_actions( $actions );
	}

	/**
	 * Get the output for the is_active column.
	 *
	 * @param array<string,mixed> $item The current item.
	 *
	 * @return string The HTML output.
	 */
	public function column_is_active( $item ): string {
		if ( $item['is_active'] ) {
			return sprintf(
				'<span class="dashicons dashicons-yes" style="color:green" title="%s"></span>
                <span class="screen-reader-text">%s</span>',
				__( 'Cache is ready to be served.', 'wp-rest-cache' ),
				__( 'Cache is ready to be served.', 'wp-rest-cache' )
			);
		}

		return sprintf(
			'<span class="dashicons dashicons-no" style="color:red" title="%s"></span>
            <span class="screen-reader-text">%s</span>',
			__( 'Cache is expired or flushed.', 'wp-rest-cache' ),
			__( 'Cache is expired or flushed.', 'wp-rest-cache' )
		);
	}

	/**
	 * The default output action for columns.
	 *
	 * @param array<string,mixed> $item The current item.
	 * @param string              $column_name The name of the current column.
	 *
	 * @return string The output for this column.
	 */
	public function column_default( $item, $column_name ): string {
		return $item[ $column_name ];
	}

	/**
	 * Get the HTML for the checkbox to select the current item.
	 *
	 * @param array<string,mixed> $item The item for this row.
	 *
	 * @return string HTML for the checkbox.
	 */
	public function column_cb( $item ): string {
		return sprintf(
			'<input type="checkbox" name="bulk-flush[]" value="%s" />',
			$item['cache_key']
		);
	}

	/**
	 * Get a list of all columns in the list view.
	 *
	 * @return array<string,string> An array of all columns in the view.
	 */
	public function get_columns() {
		$columns = [
			'cb'              => '<input type="checkbox" />',
			'cache_key'       => __( 'Cache Key', 'wp-rest-cache' ),
			'request_uri'     => __( 'Request URI', 'wp-rest-cache' ),
			'request_headers' => __( 'Request Headers', 'wp-rest-cache' ),
			'request_method'  => __( 'Request Method', 'wp-rest-cache' ),
			'object_type'     => __( 'Object Type', 'wp-rest-cache' ),
			'expiration'      => __( 'Expiration', 'wp-rest-cache' ),
			'cache_hits'      => __( '# Cache Hits', 'wp-rest-cache' ),
			'is_active'       => __( 'Active', 'wp-rest-cache' ),
		];

		return $columns;
	}

	/**
	 * Get a list of all sortable columns.
	 *
	 * @return array<string,array<int,bool|string>> An array of sortable columns.
	 */
	public function get_sortable_columns() {
		$sortable_columns = [
			'cache_key'      => [ 'cache_key', false ],
			'request_uri'    => [ 'request_uri', false ],
			'request_method' => [ 'request_method', false ],
			'object_type'    => [ 'object_type', false ],
			'expiration'     => [ 'expiration', true ],
			'cache_hits'     => [ 'cache_hits', true ],
		];

		return $sortable_columns;
	}

	/**
	 * Get all available bulk actions.
	 *
	 * @return array<string,string> An array of available bulk actions.
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-flush'  => __( 'Flush cache', 'wp-rest-cache' ),
			'bulk-delete' => __( 'Delete cache record', 'wp-rest-cache' ),
		];

		return $actions;
	}

	/**
	 * Prepare items for view.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$this->process_action();

		$columns               = $this->get_columns();
		$hidden                = [];
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$per_page     = $this->get_items_per_page( 'caches_per_page', self::ITEMS_PER_PAGE );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'per_page'    => $per_page,
			]
		);

		$this->items = self::get_caches( $per_page, $current_page );
	}

	/**
	 * Process an action on a single item or a selection of items.
	 *
	 * @return void
	 */
	public function process_action() {
		switch ( $this->current_action() ) {
			case 'flush':
			case 'delete':
				$this->process_single_action( $this->current_action() );
				break;
			case 'bulk-flush':
			case 'bulk-delete':
				$this->process_bulk_action( $this->current_action() );
				break;
		}
	}

	/**
	 * Process an action on a single item.
	 *
	 * @param string $action The action to be taken.
	 *
	 * @return void
	 */
	private function process_single_action( $action ) {
		if ( ! isset( $_GET['wp_rest_cache_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['wp_rest_cache_nonce'] ), 'wp_rest_cache_' . $action . '_cache' ) ) {
			die( 'No naughty business please' );
		}
		$cache_key = filter_input( INPUT_GET, 'cache_key', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		self::clear_cache( $cache_key, ( 'delete' === $action ) );
	}

	/**
	 * Process a bulk action on multiple selected items.
	 *
	 * @param string $action The action to be taken.
	 *
	 * @return void
	 */
	private function process_bulk_action( $action ) {
		$caches = filter_input( INPUT_GET, 'bulk-flush', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY );
		foreach ( $caches as $cache_key ) {
			self::clear_cache( $cache_key, ( 'bulk-delete' === $action ) );
		}
	}
}
