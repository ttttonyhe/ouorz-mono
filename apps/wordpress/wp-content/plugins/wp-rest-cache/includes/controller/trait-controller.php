<?php
/**
 * Trait for the REST Controller extensions.
 *
 * @link: https://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/Controller
 */

namespace WP_Rest_Cache_Plugin\Includes\Controller;

/**
 * Trait for the REST Controller extensions.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/Controller
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
trait Controller_Trait {

	/**
	 * Constructor.
	 *
	 * @param string $item Post type or taxonomy key.
	 */
	public function __construct( $item ) {
		parent::__construct( $item );

		$allowed_endpoints = get_option( 'wp_rest_cache_item_allowed_endpoints', [] );
		if ( ! isset( $allowed_endpoints[ $this->namespace ] ) || ! in_array( $this->rest_base, $allowed_endpoints[ $this->namespace ], true ) ) {
			$allowed_endpoints[ $this->namespace ][] = $this->rest_base;
			update_option( 'wp_rest_cache_item_allowed_endpoints', $allowed_endpoints, false );
		}
	}
}
