<?php
/**
 * API for item caching.
 *
 * @link: https://www.acato.nl
 * @since 2018.2
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/API
 */

namespace WP_Rest_Cache_Plugin\Includes\API;

/**
 * API for (custom) post type and (custom) taxonomy caching.
 *
 * Make sure (custom) post types and (custom) taxonomies are automatically cached.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/API
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Item_Api {


	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
	}

	/**
	 * Hook into the registering of a post type and replace the REST Controller with an extension (if allowed).
	 *
	 * @param array<string,mixed> $args      Array of arguments for registering a post type.
	 * @param string              $post_type Post type key.
	 *
	 * @return array<string,mixed> Array of arguments for registering a post type.
	 */
	public function set_post_type_rest_controller( $args, $post_type ) {
		$rest_controller = isset( $args['rest_controller_class'] ) ? $args['rest_controller_class'] : null;
		if ( ! $this->should_use_custom_class( $rest_controller, 'post_type' ) ) {
			return $args;
		}

		if ( \WP_REST_Attachments_Controller::class === $rest_controller ) {
			$args['rest_controller_class'] = \WP_Rest_Cache_Plugin\Includes\Controller\Attachment_Controller::class;
		} else {
			$args['rest_controller_class'] = \WP_Rest_Cache_Plugin\Includes\Controller\Post_Controller::class;
		}

		return $args;
	}

	/**
	 * Hook into the registering of a taxonomy and replace the REST Controller with an extension (if allowed).
	 *
	 * @param array<string,mixed> $args     Array of arguments for registering a taxonomy.
	 * @param string              $taxonomy Taxonomy key.
	 *
	 * @return array<string,mixed> Array of arguments for registering a taxonomy.
	 */
	public function set_taxonomy_rest_controller( $args, $taxonomy ) {
		$rest_controller = isset( $args['rest_controller_class'] ) ? $args['rest_controller_class'] : null;
		if ( ! $this->should_use_custom_class( $rest_controller, 'taxonomy' ) ) {
			return $args;
		}

		$args['rest_controller_class'] = \WP_Rest_Cache_Plugin\Includes\Controller\Term_Controller::class;

		return $args;
	}

	/**
	 * Check if we can use an extension of the current REST Controller.
	 *
	 * @param string|null $class_name Class name of the current REST Controller.
	 * @param string      $type       Type of the object (taxonomy|post_type).
	 *
	 * @return bool True if a custom REST Controller can be used.
	 */
	protected function should_use_custom_class( $class_name, $type ) {
		if ( is_null( $class_name ) ) {
			return true;
		}
		switch ( $type ) {
			case 'taxonomy':
				return \WP_REST_Terms_Controller::class === $class_name
					|| \WP_Rest_Cache_Plugin\Includes\Controller\Term_Controller::class === $class_name;
			case 'post_type':
			default:
				return \WP_REST_Posts_Controller::class === $class_name
					|| \WP_Rest_Cache_Plugin\Includes\Controller\Post_Controller::class === $class_name
					|| \WP_REST_Attachments_Controller::class === $class_name
					|| \WP_Rest_Cache_Plugin\Includes\Controller\Attachment_Controller::class === $class_name;
		}
	}
}
