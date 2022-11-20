<?php
/**
 * API for endpoint caching.
 *
 * @link: https://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/API
 */

namespace WP_Rest_Cache_Plugin\Includes\API;

/**
 * API for endpoint caching.
 *
 * Caches complete endpoints and handles the deletion if single items are updated.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/API
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Endpoint_Api {


	/**
	 * The requested URI.
	 *
	 * @access private
	 * @var    string $request_uri The requested URI string.
	 */
	private $request_uri;

	/**
	 * The current cache key.
	 *
	 * @access private
	 * @var    string $cache_key The current cache key.
	 */
	private $cache_key;

	/**
	 * The response headers that need to be send with the cached call.
	 *
	 * @access private
	 * @var    array<string,string> $response_headers The response headers.
	 */
	private $response_headers = array(
		'Content-Type'                  => 'application/json; charset=UTF-8',
		'X-WP-Cached-Call'              => 'served-cache',
		'X-Robots-Tag'                  => 'noindex',
		'X-Content-Type-Options'        => 'nosniff',
		'Access-Control-Expose-Headers' => 'X-WP-Total, X-WP-TotalPages',
		'Access-Control-Allow-Headers'  => 'Authorization, Content-Type',
	);

	/**
	 * The request headers that need to be used to distinguish separate caches.
	 *
	 * @access private
	 * @var    array<string,string> $request_headers The request headers.
	 */
	private $request_headers = array();

	/**
	 * The request object for the current request.
	 *
	 * @access private
	 * @var    \WP_REST_Request $request The request object.
	 */
	private $request;

	/**
	 * The default WordPress REST endpoints, that can be cached.
	 *
	 * @access private
	 * @var    array<string,array<int,string>> $wordpress_endpoints An array of default WordPress endpoints.
	 */
	private $wordpress_endpoints = array(
		'wp/v2' => array(
			'statuses',
			'taxonomies',
			'types',
			'users',
			'comments',
		),
	);

	/**
	 * Get the requested URI.
	 *
	 * @return string The request URI.
	 */
	private function build_request_uri() {
		// No filter_input, see https://stackoverflow.com/questions/25232975/php-filter-inputinput-server-request-method-returns-null/36205923.
		$request_uri = filter_var( $_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL );
		// Remove home_url from request_uri for uri's with WordPress in a subdir (like /wp).
		$request_uri = str_replace( get_home_url(), '', $request_uri );
		if ( '//' === substr( $request_uri, 0, 2 ) ) {
			$request_uri = substr( $request_uri, 1 );
		}
		$uri_parts    = wp_parse_url( $request_uri );
		$request_path = rtrim( $uri_parts['path'], '/' );

		if ( isset( $uri_parts['query'] ) && ! empty( $uri_parts['query'] ) ) {
			parse_str( $uri_parts['query'], $params );
			ksort( $params );
			$uncached_parameters = get_option( 'wp_rest_cache_uncached_parameters', [] );
			if ( $uncached_parameters ) {
				foreach ( $uncached_parameters as $uncached_parameter ) {
					if ( isset( $params[ $uncached_parameter ] ) ) {
						unset( $params[ $uncached_parameter ] );
					}
				}
			}
			$request_path .= '?' . http_build_query( $params );
		}

		$this->request_uri = $request_path;

		return $request_path;
	}

	/**
	 * Create an array of cacheable request headers based upon settings and hooks.
	 *
	 * @return void
	 */
	private function set_cacheable_request_headers() {
		$this->request = new \WP_REST_Request();
		$server        = new \WP_REST_Server();
		$this->request->set_headers( $server->get_headers( wp_unslash( $_SERVER ) ) );

		$cacheable_headers = \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->get_global_cacheable_request_headers();
		$cacheable_headers = explode( ',', $cacheable_headers );
		if ( is_array( $cacheable_headers ) ) {
			foreach ( $cacheable_headers as $header ) {
				if ( '' !== $header ) {
					$this->request_headers[ $header ] = $this->request->get_header( $header );
				}
			}
		}

		$rest_prefix               = sprintf( '/%s/', get_option( 'wp_rest_cache_rest_prefix', 'wp-json' ) );
		$cacheable_request_headers = get_option( 'wp_rest_cache_cacheable_request_headers', [] );
		if ( count( $cacheable_request_headers ) ) {
			foreach ( $cacheable_request_headers as $endpoint => $cacheable_headers ) {
				if ( false === strpos( $this->request_uri, $rest_prefix . $endpoint ) ) {
					continue;
				}

				$cacheable_headers = explode( ',', $cacheable_headers );
				if ( is_array( $cacheable_headers ) ) {
					foreach ( $cacheable_headers as $header ) {
						if ( strlen( $header ) ) {
							$this->request_headers[ $header ] = $this->request->get_header( $header );
						}
					}
				}
			}
		}

		ksort( $this->request_headers );
	}

	/**
	 * Build the cache key. A hashed combination of request uri and cacheable request headers.
	 *
	 * @return void
	 */
	private function build_cache_key() {
		$this->build_request_uri();
		$this->set_cacheable_request_headers();
		// No filter_input, see https://stackoverflow.com/questions/25232975/php-filter-inputinput-server-request-method-returns-null/36205923.
		$request_method = filter_var( $_SERVER['REQUEST_METHOD'], FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		// For backwards compatibility empty string for request method = GET.
		if ( 'GET' === $request_method ) {
			$request_method = '';
		}

		$this->cache_key = md5( $this->request_uri . wp_json_encode( $this->request_headers ) . $request_method );
	}

	/**
	 * Save the response headers so they can be added to the cache.
	 *
	 * @param bool              $served  Whether the request has already been served. Default false.
	 * @param \WP_HTTP_Response $result  Result to send to the client.
	 * @param \WP_REST_Request  $request Request used to generate the response.
	 * @param \WP_REST_Server   $server  Server instance.
	 *
	 * @return void
	 */
	public function save_cache_headers( $served, \WP_HTTP_Response $result, \WP_REST_Request $request, \WP_REST_Server $server ) {
		$headers = $result->get_headers();

		/**
		 * Filter the cache headers.
		 *
		 * Allow to filter the cache headers before they are send with the cache response.
		 *
		 * @since 2019.1.5
		 *
		 * @param array $headers An array of all headers for this cache response.
		 * @param string $request_uri The requested URI.
		 */
		$headers = apply_filters( 'wp_rest_cache/cache_headers', $headers, $this->request_uri );
		if ( ! empty( $headers ) && is_array( $headers ) ) {
			foreach ( $headers as $key => $value ) {
				/**
				 * Filter the cache header.
				 *
				 * Allow to change the cache header value.
				 *
				 * @since 2019.1.5
				 *
				 * @param string $value The value for the cache header.
				 * @param string $key The cache header field name.
				 * @param string $request_uri The requested URI.
				 */
				$value                          = apply_filters( 'wp_rest_cache/cache_header', $value, $key, $this->request_uri );
				$this->response_headers[ $key ] = $value;
			}
		}
	}

	/**
	 * Cache the response data.
	 *
	 * @param array<string,mixed> $result  Response data to send to the client.
	 * @param \WP_REST_Server     $server  Server instance.
	 * @param \WP_REST_Request    $request Request used to generate the response.
	 *
	 * @return array<string,mixed> Response data to send to the client.
	 */
	public function save_cache( $result, \WP_REST_Server $server, \WP_REST_Request $request ) {
		// Only Avoid cache if not 200.
		if ( ! empty( $result )
			&& is_array( $result )
			&& (
				(
					isset( $result['data'] )
					&& is_array( $result['data'] )
					&& isset( $result['data']['status'] )
					&& 200 !== (int) $result['data']['status']
				)
				|| 200 !== http_response_code()
			)
		) {
			return $result;
		}

		// Do not cache if empty result set.
		if ( empty( $result ) ) {
			return $result;
		}

		// No filter_input, see https://stackoverflow.com/questions/25232975/php-filter-inputinput-server-request-method-returns-null/36205923.
		$request_method = filter_var( $_SERVER['REQUEST_METHOD'], FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		// Force result to be valid JSON.
		$result = json_decode( wp_json_encode( $result ) );

		$data = array(
			'data'    => $result,
			'headers' => $this->response_headers,
		);
		\WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->set_cache( $this->cache_key, $data, 'endpoint', $this->request_uri, '', $this->request_headers, $request_method );

		return $result;
	}

	/**
	 * Check if caching should be skipped.
	 *
	 * @return bool True if no caching should be applied, false if caching can be applied.
	 */
	public function skip_caching() {
		$use_parameter = false;

		/**
		 * Allow for programmatically disabling of caching.
		 *
		 * Allows to programmatically skip caching.
		 *
		 * @since 2020.2.0
		 *
		 * @param bool $skip_caching True if cache should be skipped.
		 */
		if ( apply_filters( 'wp_rest_cache/skip_caching', false ) ) {
			return true;
		}

		$wp_nonce = $this->request->get_header( 'x_wp_nonce' );

		/**
		 * Allow for programmatically enable nonce caching.
		 *
		 * Allows to programmatically enable caching of requests with a nonce.
		 *
		 * @since 2021.1.0
		 *
		 * @param bool $skip_nonce_caching False if cache should not be skipped when nonce is present.
		 * @param \WP_REST_Request $request The current REST Request.
		 * @param string $request_uri The REST URI that is being requested.
		 */
		if ( apply_filters( 'wp_rest_cache/skip_nonce_caching', true, $this->request, $this->request_uri ) && ! is_null( $wp_nonce ) ) {
			return true;
		}

		// Default only cache GET-requests.
		$allowed_request_methods = get_option( 'wp_rest_cache_allowed_request_methods', [ 'GET' ] );
		// No filter_input, see https://stackoverflow.com/questions/25232975/php-filter-inputinput-server-request-method-returns-null/36205923.
		if ( ! in_array( filter_var( $_SERVER['REQUEST_METHOD'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ), $allowed_request_methods, true ) ) {
			return true;
		}

		// Parameter to skip caching.
		if ( true === filter_has_var( INPUT_GET, 'skip_cache' ) ) {
			return true;
		}

		// Make sure we only apply to allowed api calls.
		$rest_prefix = sprintf( '/%s/', get_option( 'wp_rest_cache_rest_prefix', 'wp-json' ) );
		if ( strpos( $this->request_uri, $rest_prefix ) === false ) {
			if ( strpos( $this->request_uri, 'rest_route=' ) !== false ) {
				$rest_prefix   = 'rest_route=';
				$use_parameter = true;
			} else {
				return true;
			}
		}

		$allowed_endpoints = get_option( 'wp_rest_cache_allowed_endpoints', [] );

		$allowed_endpoint = false;
		foreach ( $allowed_endpoints as $namespace => $endpoints ) {
			foreach ( $endpoints as $endpoint ) {
				$endpoint_uri = $rest_prefix . $namespace . '/' . $endpoint;
				if ( $use_parameter ) {
					$endpoint_uri = $rest_prefix . rawurlencode( '/' . $namespace . '/' . $endpoint );
				}
				if ( strpos( $this->request_uri, $endpoint_uri ) !== false ) {
					$allowed_endpoint = true;
					break 2;
				}
			}
		}

		if ( ! $allowed_endpoint ) {
			return true;
		}

		$disallowed_endpoints = get_option( 'wp_rest_cache_disallowed_endpoints', [] );

		foreach ( $disallowed_endpoints as $namespace => $endpoints ) {
			foreach ( $endpoints as $endpoint ) {
				$endpoint_uri = $rest_prefix . $namespace . '/' . $endpoint;
				if ( $use_parameter ) {
					$endpoint_uri = $rest_prefix . rawurlencode( '/' . $namespace . '/' . $endpoint );
				}
				if ( strpos( $this->request_uri, $endpoint_uri ) !== false ) {
					return true;
				}
			}
		}

		// We dont skip.
		return false;
	}

	/**
	 * Check if the current call is a REST API call, if so check if it has already been cached, otherwise cache it.
	 *
	 * @return void
	 */
	public function get_api_cache() {

		$this->build_cache_key();

		if ( $this->skip_caching() ) {
			return;
		}

		$cache = \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->get_cache( $this->cache_key );

		if ( false !== $cache ) {
			/**
			 * Filter cache data.
			 *
			 * Allow filtering of the cached data.
			 *
			 * @since 2022.2.0
			 *
			 * @param mixed $data The cached JSON data object.
			 */
			$data = apply_filters( 'wp_rest_cache/filter_cache_output', $cache['data'] );

			// We want the data to be json.
			$data       = wp_json_encode( $data );
			$last_error = json_last_error();

			if ( JSON_ERROR_NONE === $last_error ) {

				/**
				 * Disable CORS headers.
				 *
				 * Allows to disable the sending of CORS headers.
				 *
				 * @since 2021.4.0
				 *
				 * @param boolean $disable_cors_headers True if CORS headers should not be send.
				 */
				if ( false === apply_filters( 'wp_rest_cache/disable_cors_headers', false ) ) {
					$this->rest_send_cors_headers( '' );
				}

				foreach ( $cache['headers'] as $key => $value ) {
					$header = sprintf( '%s: %s', $key, $value );
					header( $header );
				}

				echo $data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				exit;
			}
		}

		// Catch the headers after serving.
		add_filter( 'rest_pre_serve_request', [ $this, 'save_cache_headers' ], 9999, 4 );

		// Catch the result after serving.
		add_filter( 'rest_pre_echo_response', [ $this, 'save_cache' ], 1000, 3 );
	}

	/**
	 * Sends Cross-Origin Resource Sharing headers with API requests.
	 *
	 * @param mixed $value Response data.
	 * @return mixed Response data.
	 */
	private function rest_send_cors_headers( $value ) {
		$origin = get_http_origin();

		if ( $origin ) {
			// Requests from file:// and data: URLs send "Origin: null".
			if ( 'null' !== $origin ) {
				$origin = esc_url_raw( $origin );
			}
			header( 'Access-Control-Allow-Origin: ' . $origin );
			header( 'Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, PATCH, DELETE' );
			header( 'Access-Control-Allow-Credentials: true' );
			header( 'Vary: Origin', false );
		} elseif ( ! headers_sent() && 'GET' === $_SERVER['REQUEST_METHOD'] ) {
			header( 'Vary: Origin' );
		}

		return $value;
	}

	/**
	 * Re-save the options if they have changed. We need them as options since we are going to use them early in the
	 * WordPress process even before several hooks are fired.
	 *
	 * @return void
	 */
	public function save_options() {
		$original_allowed_endpoints    = get_option( 'wp_rest_cache_allowed_endpoints', [] );
		$item_allowed_endpoints        = get_option( 'wp_rest_cache_item_allowed_endpoints', [] );
		$original_disallowed_endpoints = get_option( 'wp_rest_cache_disallowed_endpoints', [] );

		/**
		 * Override cache-enabled endpoints.
		 *
		 * Allows to override the endpoints that will be cached by the WP REST Cache plugin.
		 *
		 * @since 2018.2.0
		 *
		 * @param array $original_allowed_endpoints An array of endpoints that are allowed to be cached.
		 */
		$allowed_endpoints = apply_filters( 'wp_rest_cache/allowed_endpoints', $item_allowed_endpoints );
		if ( $original_allowed_endpoints !== $allowed_endpoints ) {
			update_option( 'wp_rest_cache_allowed_endpoints', $allowed_endpoints, false );
		}

		/**
		 * Override cache-disabled endpoints.
		 *
		 * Allows to override the endpoints that will not be cached by the WP REST Cache plugin.
		 *
		 * @since 2021.4.0
		 *
		 * @param array $original_disallowed_endpoints An array of endpoints that are not allowed to be cached.
		 */
		$disallowed_endpoints = apply_filters( 'wp_rest_cache/disallowed_endpoints', $original_disallowed_endpoints );
		if ( $original_disallowed_endpoints !== $disallowed_endpoints ) {
			update_option( 'wp_rest_cache_disallowed_endpoints', $disallowed_endpoints, false );
		}

		$original_rest_prefix = get_option( 'wp_rest_cache_rest_prefix' );
		$rest_prefix          = rest_get_url_prefix();
		if ( $original_rest_prefix !== $rest_prefix ) {
			update_option( 'wp_rest_cache_rest_prefix', $rest_prefix, false );
		}

		$original_cacheable_request_headers = get_option( 'wp_rest_cache_cacheable_request_headers', [] );

		/**
		 * Filter cacheable request headers.
		 *
		 * Allow to set cacheable request headers per endpoint in the format [ '/wp/v2/posts' => 'HEADER_1,HEADER_2' ].
		 *
		 * @since 2019.4.0
		 *
		 * @param array $original_cacheable_request_headers An array of endpoints and
		 */
		$cacheable_request_headers = apply_filters( 'wp_rest_cache/cacheable_request_headers', $original_cacheable_request_headers );
		if ( $original_cacheable_request_headers !== $cacheable_request_headers ) {
			update_option( 'wp_rest_cache_cacheable_request_headers', $cacheable_request_headers, false );
		}

		$original_allowed_request_methods = get_option( 'wp_rest_cache_allowed_request_methods', [ 'GET' ] );

		/**
		 * Override cache-enabled request methods.
		 *
		 * Allows to override the request methods that will be cached by the WP REST Cache plugin.
		 *
		 * @since 2020.1.0
		 *
		 * @param array $original_allowed_request_methods An array of request_methods that are allowed to be cached.
		 */
		$allowed_request_methods = apply_filters( 'wp_rest_cache/allowed_request_methods', $original_allowed_request_methods );
		if ( $original_allowed_request_methods !== $allowed_request_methods ) {
			update_option( 'wp_rest_cache_allowed_request_methods', $allowed_request_methods, false );
		}

		$original_uncached_parameters = get_option( 'wp_rest_cache_uncached_parameters', [] );

		/**
		 * Filter uncached query parameters.
		 *
		 * Allows to specify which query parameters should be omitted from the cacheable query string.
		 *
		 * @since 2020.1.0
		 *
		 * @param array $original_uncached_parameters An array of query parameters that should be omitted from the cacheable query string.
		 */
		$uncached_parameters = apply_filters( 'wp_rest_cache/uncached_parameters', $original_uncached_parameters );
		if ( $original_uncached_parameters !== $uncached_parameters ) {
			update_option( 'wp_rest_cache_uncached_parameters', $uncached_parameters, false );
		}

		$original_cache_hit_recording = get_option( 'wp_rest_cache_hit_recording', true );
		/**
		 * Filter to disable cache hit recording.
		 *
		 * Allows to override the cache hit recording.
		 *
		 * @since 2020.2.0
		 *
		 * @param boolean $original_cache_hit_recording Set to false to disable cache hit recording.
		 */
		$cache_hit_recording = apply_filters( 'wp_rest_cache/cache_hit_recording', $original_cache_hit_recording );
		if ( (int) $original_cache_hit_recording !== (int) $cache_hit_recording ) {
			update_option( 'wp_rest_cache_hit_recording', (int) $cache_hit_recording, true );
		}
	}

	/**
	 * Add the default WordPress endpoints to the allowed endpoints for caching.
	 *
	 * @param array<string,array<int,string>> $allowed_endpoints The endpoints that are allowed to be cached.
	 *
	 * @return mixed An array of endpoints that are allowed to be cached.
	 */
	public function add_wordpress_endpoints( array $allowed_endpoints ) {
		foreach ( $this->wordpress_endpoints as $rest_base => $endpoints ) {
			foreach ( $endpoints as $endpoint ) {
				if ( ! isset( $allowed_endpoints[ $rest_base ] ) || ! in_array( $endpoint, $allowed_endpoints[ $rest_base ], true ) ) {
					$allowed_endpoints[ $rest_base ][] = $endpoint;
				}
			}
		}

		return $allowed_endpoints;
	}

	/**
	 * Determine the object type for caches of WordPress endpoints (if it has not yet been automatically determined).
	 *
	 * @param string $object_type The automatically determined object type ('unknown' if it couldn't be deterrmined).
	 * @param string $cache_key   The cache key.
	 * @param mixed  $data        The cached data.
	 * @param string $uri         The requested URI.
	 *
	 * @return string The determined object type.
	 */
	public function determine_object_type( $object_type, $cache_key, $data, $uri ) {
		if ( 'unknown' !== $object_type ) {
			return $object_type;
		}

		foreach ( $this->wordpress_endpoints as $rest_base => $endpoints ) {
			foreach ( $endpoints as $endpoint ) {
				if ( strpos( $uri, $rest_base . '/' . $endpoint ) !== false ) {
					return $endpoint;
				}
			}
		}

		return $object_type;
	}
}
