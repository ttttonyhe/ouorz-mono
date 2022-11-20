<?php
/**
 * REST Controller for (Custom) Post Type caching.
 *
 * @link: https://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/Controller
 */

namespace WP_Rest_Cache_Plugin\Includes\Controller;

/**
 * REST Controller for (Custom) Post Type caching.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/Controller
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Post_Controller extends \WP_REST_Posts_Controller {

	use Controller_Trait;
}
