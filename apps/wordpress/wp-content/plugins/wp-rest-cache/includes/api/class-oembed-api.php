<?php
/**
 * Caching of the Oembed enpoint.
 *
 * @link: https://www.acato.nl
 * @since 2023.1.0
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/API
 */

namespace WP_Rest_Cache_Plugin\Includes\API;

use WP_Rest_Cache_Plugin\Includes\Util;

/**
 * Caching of the Oembed endpoint.
 *
 * Makes caching of the Oembed endpoint possible and handles detection of object type and setting cache relations.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/API
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Oembed_Api {

	/**
	 * The base of the oembed endpoint.
	 *
	 * @var string
	 */
	private $rest_base = 'oembed/1.0';

	/**
	 * The endpoint of the oembed endpoint.
	 *
	 * @var string
	 */
	private $endpoint = 'embed';

	/**
	 * Add the WordPress oembed endpoint to the allowed endpoints for caching.
	 *
	 * @param array<string,array<int,string>> $allowed_endpoints The endpoints that are allowed to be cached.
	 *
	 * @return mixed An array of endpoints that are allowed to be cached.
	 */
	public function add_oembed_endpoint( array $allowed_endpoints ) {
		if ( ! isset( $allowed_endpoints[ $this->rest_base ] ) || ! in_array( $this->endpoint, $allowed_endpoints[ $this->rest_base ], true ) ) {
			$allowed_endpoints[ $this->rest_base ][] = $this->endpoint;
		}

		return $allowed_endpoints;
	}

	/**
	 * Determine the object type for caches of WordPress oembed endpoint.
	 *
	 * @param string $object_type The automatically determined object type ('unknown' if it couldn't be deterrmined).
	 * @param string $cache_key The cache key.
	 * @param mixed  $data The cached data.
	 * @param string $uri The requested URI.
	 *
	 * @return string The determined object type.
	 */
	public function determine_object_type( $object_type, $cache_key, $data, $uri ) {
		if ( 'unknown' !== $object_type ) {
			return $object_type;
		}

		$post_id = $this->get_oembed_post_id( $uri );

		if ( $post_id ) {
			$post_type = get_post_type( $post_id );
			if ( $post_type ) {
				return $post_type;
			}
		}

		return $object_type;
	}

	/**
	 * Determine if the current request is a single oembed item.
	 *
	 * @param boolean $is_single Whether the cache contains a single item (true) or a collection of items (false).
	 * @param mixed   $data The data that is to be cached.
	 * @param string  $uri The requested URI.
	 *
	 * @return boolean Whether the cache contains a single item (true) or a collection of items (false).
	 */
	public function is_single_oembed_item( $is_single, $data, $uri ) {
		if ( strpos( $uri, $this->rest_base . '/' . $this->endpoint ) !== false ) {
			return true;
		}

		return $is_single;
	}

	/**
	 * Process Oembed cache relations.
	 *
	 * @param int    $cache_id The row id of the current cache.
	 * @param mixed  $data The data that is to be cached.
	 * @param string $object_type Object type.
	 * @param string $uri The requested URI.
	 *
	 * @return void
	 */
	public function process_cache_relations( $cache_id, $data, $object_type, $uri ) {
		$post_id = $this->get_oembed_post_id( $uri );
		if ( $post_id ) {
			$post_type = get_post_type( $post_id );
			if ( $post_type ) {
				$object_type = $post_type;
			}
			\WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->insert_cache_relation( $cache_id, $post_id, $object_type );
		}
	}

	/**
	 * Get the oembed post id.
	 *
	 * @param string $uri The requested Oembed uri.
	 *
	 * @return false|int The requested post id or false if it could not be found.
	 */
	private function get_oembed_post_id( $uri ) {
		if ( strpos( $uri, $this->rest_base . '/' . $this->endpoint ) !== false ) {
			$uri_parts = wp_parse_url( $uri );
			if ( isset( $uri_parts['query'] ) && ! empty( $uri_parts['query'] ) ) {
				parse_str( $uri_parts['query'], $params );
				if ( isset( $params['url'] ) ) {
					// The oembed endpoint receives `url` as an absolute URL. Only prepend the
					// home URL when the value is a path (preserves behavior for any consumer
					// passing a path-only value, but no longer produces malformed URLs like
					// "https://example.orghttps://example.org/..." for the normal case).
					$url_param = $params['url'];
					if ( ! preg_match( '#^https?://#i', $url_param ) ) {
						$url_param = Util::get_home_url() . $url_param;
					}
					$post_id = url_to_postid( $url_param );

					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
					$post_id = apply_filters( 'oembed_request_post_id', $post_id, $params['url'] );

					return $post_id;
				}
			}
		}

		return false;
	}
}
