<?php
/**
 * Deprecated class, still available for old Must Use plugin versions.
 *
 * @link: https://www.acato.nl
 * @since 2018.4
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Deprecated
 */

/**
 * Deprecated class, still available for old Must Use plugin versions.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Deprecated
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class WP_Rest_Cache_Endpoint_Api {

	/**
	 * Variable for storing an instance of the Endpoint API.
	 *
	 * @access private
	 * @var \WP_Rest_Cache_Plugin\Includes\API\Endpoint_Api $endpoint_api The new Endpoint API class.
	 */
	private $endpoint_api;

	/**
	 * WP_Rest_Cache_Endpoint_Api constructor.
	 *
	 * @deprecated
	 */
	public function __construct() {
		$this->endpoint_api = new \WP_Rest_Cache_Plugin\Includes\API\Endpoint_Api();
	}

	/**
	 * Redirect all function calls to the new \WP_Rest_Cache_Plugin\Includes\API\Endpoint_Api class.
	 *
	 * @deprecated
	 *
	 * @param string $name      Name of the called function.
	 * @param mixed  $arguments Arguments supplied.
	 *
	 * @return mixed The result of the called function.
	 */
	public function __call( $name, $arguments ) {
		return call_user_func_array( [ $this->endpoint_api, $name ], $arguments );
	}
}
