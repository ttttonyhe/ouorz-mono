<?php
/**
 * REST Controller for (Custom) Term caching.
 *
 * @link: https://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/Controller
 */

namespace WP_Rest_Cache_Plugin\Includes\Controller;

/**
 * REST Controller for (Custom) Term caching.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/Controller
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Term_Controller extends \WP_REST_Terms_Controller {

	use Controller_Trait;
}
