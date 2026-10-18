<?php
/*
Plugin Name: Ouorz REST Cache Policy
*/

if (!defined('ABSPATH')) {
  exit;
}

final class Ouorz_REST_Cache_Policy {
  private const CACHE_VERSION = '2';
  private bool $private_response = false;

  public function __construct() {
    add_filter('wp_rest_cache/skip_caching', [$this, 'skip'], PHP_INT_MAX);
    add_filter('wp_rest_cache/cache_key', [$this, 'cache_key'], PHP_INT_MAX);
    add_filter('rest_pre_serve_request', [$this, 'inspect_response'], PHP_INT_MAX, 2);
    add_filter('rest_pre_echo_response', [$this, 'protect_storage'], 999, 3);
  }

  public function skip(bool $skip, ?WP_REST_Request $request = null): bool {
    if ($skip || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET' || !empty($_COOKIE) || $this->uncacheable_params($_GET)) {
      return true;
    }
    foreach (['HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION', 'PHP_AUTH_USER', 'PHP_AUTH_PW', 'HTTP_COOKIE', 'HTTP_X_WP_NONCE', 'HTTP_X_HTTP_METHOD_OVERRIDE'] as $header) {
      if (isset($_SERVER[$header]) && $_SERVER[$header] !== '') {
        return true;
      }
    }
    if ($request) {
      foreach (['authorization', 'cookie', 'x_wp_nonce', 'x_http_method_override'] as $header) {
        if ($request->get_header($header)) {
          return true;
        }
      }
      if ($this->uncacheable_params($request->get_params())) {
        return true;
      }
    }
    return $this->has_body($request) || (function_exists('is_user_logged_in') && is_user_logged_in());
  }

  private function has_body(?WP_REST_Request $request): bool {
    return (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0
      || !empty($_SERVER['HTTP_TRANSFER_ENCODING'])
      || !empty($_SERVER['TRANSFER_ENCODING'])
      || ($request && ($request->get_body() ?? '') !== '')
      // Core retains the raw body so later REST parsing receives the complete input.
      || WP_REST_Server::get_raw_data() !== '';
  }

  private function uncacheable_params(array $params): bool {
    return isset($params['password']) || isset($params['_wpnonce']) || isset($params['_method'])
      || array_key_exists('_envelope', $params)
      || (isset($params['context']) && $params['context'] !== 'view');
  }

  public function cache_key(string $key): string {
    $plugin = get_file_data(WP_PLUGIN_DIR.'/wp-rest-cache/wp-rest-cache.php', ['Version' => 'Version']);
    return md5('ouorz-public-'.self::CACHE_VERSION.':'.$plugin['Version'].':'.$key);
  }

  public function inspect_response(bool $served, WP_HTTP_Response $response): bool {
    $headers = headers_list();
    foreach ($response->get_headers() as $name => $value) {
      $headers[] = $name.': '.$value;
    }
    foreach ($headers as $header) {
      if (preg_match('/^(set-cookie:|cache-control:.*(?:private|no-store|no-cache))/i', $header)) {
        $this->private_response = true;
        break;
      }
    }
    return $served;
  }

  public function protect_storage(mixed $result, WP_REST_Server $server, WP_REST_Request $request): mixed {
    global $wp_rest_cache_api;

    if ($this->private_response || $this->skip(false, $request)) {
      // The cache plugin attaches its writer before WordPress authenticates REST requests.
      if ($wp_rest_cache_api instanceof \WP_Rest_Cache_Plugin\Includes\API\Endpoint_Api) {
        remove_filter('rest_pre_echo_response', [$wp_rest_cache_api, 'save_cache'], 1000);
      }
    }
    return $result;
  }
}

new Ouorz_REST_Cache_Policy();
