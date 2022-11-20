<?php
/**
 * REST Controller for Attachment caching.
 *
 * @link: https://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/Controller
 */

namespace WP_Rest_Cache_Plugin\Includes\Controller;

/**
 * REST Controller for Attachment caching.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/Controller
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Attachment_Controller extends \WP_REST_Attachments_Controller {

	use Controller_Trait;
}
