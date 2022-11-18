<?php
/**
 * Plugin Name: Redis Object Cache Drop-In
 * Plugin URI: https://wordpress.org/plugins/redis-cache/
 * Description: A persistent object cache backend powered by Redis. Supports Predis, PhpRedis, Relay, replication, sentinels, clustering and WP-CLI.
 * Version: 2.2.2
 * Author: Till Krüss
 * Author URI: https://objectcache.pro
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Requires PHP: 7.2
 *
 * @package Rhubarb\RedisCache
 */

defined( '\\ABSPATH' ) || exit;

// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact, Generic.WhiteSpace.ScopeIndent.Incorrect
if ( ! defined( 'WP_REDIS_DISABLED' ) || ! WP_REDIS_DISABLED ) :

/**
 * Determines whether the object cache implementation supports a particular feature.
 *
 * Possible values include:
 *  - `add_multiple`, `set_multiple`, `get_multiple` and `delete_multiple`
 *  - `flush_runtime` and `flush_group`
 *
 * @param string $feature Name of the feature to check for.
 * @return bool True if the feature is supported, false otherwise.
 */
function wp_cache_supports( $feature ) {
    switch ( $feature ) {
        case 'add_multiple':
        case 'set_multiple':
        case 'get_multiple':
        case 'delete_multiple':
        case 'flush_runtime':
            return true;

        case 'flush_group':
        default:
            return false;
    }
}


/**
 * Adds a value to cache.
 *
 * If the specified key already exists, the value is not stored and the function
 * returns false.
 *
 * @param string $key        The key under which to store the value.
 * @param mixed  $value      The value to store.
 * @param string $group      The group value appended to the $key.
 * @param int    $expiration The expiration time, defaults to 0.
 *
 * @return bool              Returns TRUE on success or FALSE on failure.
 */
function wp_cache_add( $key, $value, $group = '', $expiration = 0 ) {
    global $wp_object_cache;

    return $wp_object_cache->add( $key, $value, $group, $expiration );
}

/**
 * Adds multiple values to the cache in one call.
 *
 * @param array  $data   Array of keys and values to be set.
 * @param string $group  Optional. Where the cache contents are grouped. Default empty.
 * @param int    $expire Optional. When to expire the cache contents, in seconds.
 *                       Default 0 (no expiration).
 * @return bool[] Array of return values, grouped by key. Each value is either
 *                true on success, or false if cache key and group already exist.
 */
function wp_cache_add_multiple( array $data, $group = '', $expire = 0 ) {
    global $wp_object_cache;

    return $wp_object_cache->add_multiple( $data, $group, $expire );
}

/**
 * Closes the cache.
 *
 * This function has ceased to do anything since WordPress 2.5. The
 * functionality was removed along with the rest of the persistent cache. This
 * does not mean that plugins can't implement this function when they need to
 * make sure that the cache is cleaned up after WordPress no longer needs it.
 *
 * @return  bool    Always returns True
 */
function wp_cache_close() {
    return true;
}

/**
 * Decrement a numeric item's value.
 *
 * @param string $key    The key under which to store the value.
 * @param int    $offset The amount by which to decrement the item's value.
 * @param string $group  The group value appended to the $key.
 *
 * @return int|bool      Returns item's new value on success or FALSE on failure.
 */
function wp_cache_decr( $key, $offset = 1, $group = '' ) {
    global $wp_object_cache;

    return $wp_object_cache->decrement( $key, $offset, $group );
}

/**
 * Remove the item from the cache.
 *
 * @param string $key    The key under which to store the value.
 * @param string $group  The group value appended to the $key.
 * @param int    $time   The amount of time the server will wait to delete the item in seconds.
 *
 * @return bool          Returns TRUE on success or FALSE on failure.
 */
function wp_cache_delete( $key, $group = '', $time = 0 ) {
    global $wp_object_cache;

    return $wp_object_cache->delete( $key, $group, $time );
}

/**
 * Deletes multiple values from the cache in one call.
 *
 * @param array  $keys  Array of keys under which the cache to deleted.
 * @param string $group Optional. Where the cache contents are grouped. Default empty.
 * @return bool[] Array of return values, grouped by key. Each value is either
 *                true on success, or false if the contents were not deleted.
 */
function wp_cache_delete_multiple( array $keys, $group = '' ) {
    global $wp_object_cache;

    return $wp_object_cache->delete_multiple( $keys, $group );
}

/**
 * Invalidate all items in the cache. If `WP_REDIS_SELECTIVE_FLUSH` is `true`,
 * only keys prefixed with the `WP_REDIS_PREFIX` are flushed.
 *
 * @param int $delay  Number of seconds to wait before invalidating the items.
 *
 * @return bool       Returns TRUE on success or FALSE on failure.
 */
function wp_cache_flush( $delay = 0 ) {
    global $wp_object_cache;

    return $wp_object_cache->flush( $delay );
}

/**
 * Removes all cache items from the in-memory runtime cache.
 *
 * @return bool True on success, false on failure.
 */
function wp_cache_flush_runtime() {
    global $wp_object_cache;

    return $wp_object_cache->flush_runtime();
}

/**
 * Retrieve object from cache.
 *
 * Gets an object from cache based on $key and $group.
 *
 * @param string $key        The key under which to store the value.
 * @param string $group      The group value appended to the $key.
 * @param bool   $force      Optional. Whether to force an update of the local cache from the persistent
 *                           cache. Default false.
 * @param bool   $found      Optional. Whether the key was found in the cache. Disambiguates a return of false,
 *                           a storable value. Passed by reference. Default null.
 *
 * @return bool|mixed        Cached object value.
 */
function wp_cache_get( $key, $group = '', $force = false, &$found = null ) {
    global $wp_object_cache;

    return $wp_object_cache->get( $key, $group, $force, $found );
}

/**
 * Retrieves multiple values from the cache in one call.
 *
 * @param array  $keys  Array of keys under which the cache contents are stored.
 * @param string $group Optional. Where the cache contents are grouped. Default empty.
 * @param bool   $force Optional. Whether to force an update of the local cache
 *                      from the persistent cache. Default false.
 * @return array Array of values organized into groups.
 */
function wp_cache_get_multiple( $keys, $group = '', $force = false ) {
    global $wp_object_cache;

    return $wp_object_cache->get_multiple( $keys, $group, $force );
}

/**
 * Increment a numeric item's value.
 *
 * @param string $key    The key under which to store the value.
 * @param int    $offset The amount by which to increment the item's value.
 * @param string $group  The group value appended to the $key.
 *
 * @return int|bool      Returns item's new value on success or FALSE on failure.
 */
function wp_cache_incr( $key, $offset = 1, $group = '' ) {
    global $wp_object_cache;

    return $wp_object_cache->increment( $key, $offset, $group );
}

/**
 * Sets up Object Cache Global and assigns it.
 *
 * @return  void
 */
function wp_cache_init() {
    global $wp_object_cache;

    if ( ! defined( 'WP_REDIS_PREFIX' ) && getenv( 'WP_REDIS_PREFIX' ) ) {
        define( 'WP_REDIS_PREFIX', getenv( 'WP_REDIS_PREFIX' ) );
    }

    if ( ! defined( 'WP_REDIS_SELECTIVE_FLUSH' ) && getenv( 'WP_REDIS_SELECTIVE_FLUSH' ) ) {
        define( 'WP_REDIS_SELECTIVE_FLUSH', (bool) getenv( 'WP_REDIS_SELECTIVE_FLUSH' ) );
    }

    // Backwards compatibility: map `WP_CACHE_KEY_SALT` constant to `WP_REDIS_PREFIX`.
    if ( defined( 'WP_CACHE_KEY_SALT' ) && ! defined( 'WP_REDIS_PREFIX' ) ) {
        define( 'WP_REDIS_PREFIX', WP_CACHE_KEY_SALT );
    }

    // Set unique prefix for sites hosted on Cloudways
    if ( ! defined( 'WP_REDIS_PREFIX' ) && isset( $_SERVER['cw_allowed_ip'] ) )  {
        define( 'WP_REDIS_PREFIX', getenv( 'HTTP_X_APP_USER' ) );
    }

    if ( ! ( $wp_object_cache instanceof WP_Object_Cache ) ) {
        $fail_gracefully = ! defined( 'WP_REDIS_GRACEFUL' ) || WP_REDIS_GRACEFUL;

        // We need to override this WordPress global in order to inject our cache.
        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $wp_object_cache = new WP_Object_Cache( $fail_gracefully );
    }
}

/**
 * Replaces a value in cache.
 *
 * This method is similar to "add"; however, is does not successfully set a value if
 * the object's key is not already set in cache.
 *
 * @param string $key        The key under which to store the value.
 * @param mixed  $value      The value to store.
 * @param string $group      The group value appended to the $key.
 * @param int    $expiration The expiration time, defaults to 0.
 *
 * @return bool              Returns TRUE on success or FALSE on failure.
 */
function wp_cache_replace( $key, $value, $group = '', $expiration = 0 ) {
    global $wp_object_cache;

    return $wp_object_cache->replace( $key, $value, $group, $expiration );
}

/**
 * Sets a value in cache.
 *
 * The value is set whether or not this key already exists in Redis.
 *
 * @param string $key        The key under which to store the value.
 * @param mixed  $value      The value to store.
 * @param string $group      The group value appended to the $key.
 * @param int    $expiration The expiration time, defaults to 0.
 *
 * @return bool              Returns TRUE on success or FALSE on failure.
 */
function wp_cache_set( $key, $value, $group = '', $expiration = 0 ) {
    global $wp_object_cache;

    return $wp_object_cache->set( $key, $value, $group, $expiration );
}

/**
 * Sets multiple values to the cache in one call.
 *
 * @param array  $data   Array of keys and values to be set.
 * @param string $group  Optional. Where the cache contents are grouped. Default empty.
 * @param int    $expire Optional. When to expire the cache contents, in seconds.
 *                       Default 0 (no expiration).
 * @return bool[] Array of return values, grouped by key. Each value is either
 *                true on success, or false on failure.
 */
function wp_cache_set_multiple( array $data, $group = '', $expire = 0 ) {
    global $wp_object_cache;

    return $wp_object_cache->set_multiple( $data, $group, $expire );
}

/**
 * Switch the internal blog id.
 *
 * This changes the blog id used to create keys in blog specific groups.
 *
 * @param  int $_blog_id The blog ID.
 *
 * @return bool
 */
function wp_cache_switch_to_blog( $_blog_id ) {
    global $wp_object_cache;

    return $wp_object_cache->switch_to_blog( $_blog_id );
}

/**
 * Adds a group or set of groups to the list of Redis groups.
 *
 * @param   string|array $groups     A group or an array of groups to add.
 *
 * @return  void
 */
function wp_cache_add_global_groups( $groups ) {
    global $wp_object_cache;

    $wp_object_cache->add_global_groups( $groups );
}

/**
 * Adds a group or set of groups to the list of non-Redis groups.
 *
 * @param   string|array $groups     A group or an array of groups to add.
 *
 * @return  void
 */
function wp_cache_add_non_persistent_groups( $groups ) {
    global $wp_object_cache;

    $wp_object_cache->add_non_persistent_groups( $groups );
}

/**
 * Object cache class definition
 */
class WP_Object_Cache {
    /**
     * The Redis client.
     *
     * @var mixed
     */
    private $redis;

    /**
     * The Redis server version.
     *
     * @var null|string
     */
    private $redis_version = null;

    /**
     * Track if Redis is available.
     *
     * @var bool
     */
    private $redis_connected = false;

    /**
     * Check to fail gracefully or throw an exception.
     *
     * @var bool
     */
    private $fail_gracefully = true;

    /**
     * Holds the non-Redis objects.
     *
     * @var array
     */
    public $cache = [];

    /**
     * Holds the diagnostics values.
     *
     * @var array
     */
    public $diagnostics = null;

    /**
     * Holds the error messages.
     *
     * @var array
     */
    public $errors = [];

    /**
     * List of global groups.
     *
     * @var array
     */
    public $global_groups = [
        'blog-details',
        'blog-id-cache',
        'blog-lookup',
        'global-posts',
        'networks',
        'rss',
        'sites',
        'site-details',
        'site-lookup',
        'site-options',
        'site-transient',
        'users',
        'useremail',
        'userlogins',
        'usermeta',
        'user_meta',
        'userslugs',
    ];

    /**
     * List of groups that will not be flushed.
     *
     * @var array
     */
    public $unflushable_groups = [];

    /**
     * List of groups not saved to Redis.
     *
     * @var array
     */
    public $ignored_groups = [
        'counts',
        'plugins',
        'themes',
    ];

    /**
     * List of groups and their types.
     *
     * @var array
     */
    public $group_type = [];

    /**
     * Prefix used for global groups.
     *
     * @var string
     */
    public $global_prefix = '';

    /**
     * Prefix used for non-global groups.
     *
     * @var int
     */
    public $blog_prefix = 0;

    /**
     * Track how many requests were found in cache.
     *
     * @var int
     */
    public $cache_hits = 0;

    /**
     * Track how may requests were not cached.
     *
     * @var int
     */
    public $cache_misses = 0;

    /**
     * The amount of Redis commands made.
     *
     * @var int
     */
    public $cache_calls = 0;

    /**
     * The amount of microseconds (μs) waited for Redis commands.
     *
     * @var float
     */
    public $cache_time = 0;

    /**
     * Instantiate the Redis class.
     *
     * @param bool $fail_gracefully Handles and logs errors if true throws exceptions otherwise.
     */
    public function __construct( $fail_gracefully = true ) {
        global $blog_id, $table_prefix;

        $this->fail_gracefully = $fail_gracefully;

        if ( defined( 'WP_REDIS_GLOBAL_GROUPS' ) && is_array( WP_REDIS_GLOBAL_GROUPS ) ) {
            $this->global_groups = array_map( [ $this, 'sanitize_key_part' ], WP_REDIS_GLOBAL_GROUPS );
        }

        $this->global_groups[] = 'redis-cache';

        if ( defined( 'WP_REDIS_IGNORED_GROUPS' ) && is_array( WP_REDIS_IGNORED_GROUPS ) ) {
            $this->ignored_groups = array_map( [ $this, 'sanitize_key_part' ], WP_REDIS_IGNORED_GROUPS );
        }

        if ( defined( 'WP_REDIS_UNFLUSHABLE_GROUPS' ) && is_array( WP_REDIS_UNFLUSHABLE_GROUPS ) ) {
            $this->unflushable_groups = array_map( [ $this, 'sanitize_key_part' ], WP_REDIS_UNFLUSHABLE_GROUPS );
        }

        $this->cache_group_types();

        if ( defined( 'WP_REDIS_TRACE' ) && WP_REDIS_TRACE && function_exists( '_doing_it_wrong' ) ) {
            _doing_it_wrong( __FUNCTION__ , 'Tracing feature was removed.' , '2.1.2' );
        }

        $client = $this->determine_client();
        $parameters = $this->build_parameters();

        try {
            switch ( $client ) {
                case 'hhvm':
                    $this->connect_using_hhvm( $parameters );
                    break;
                case 'phpredis':
                    $this->connect_using_phpredis( $parameters );
                    break;
                case 'relay':
                    $this->connect_using_relay( $parameters );
                    break;
                case 'credis':
                    $this->connect_using_credis( $parameters );
                    break;
                case 'predis':
                default:
                    $this->connect_using_predis( $parameters );
                    break;
            }

            if ( defined( 'WP_REDIS_CLUSTER' ) ) {
                $connectionID = is_string( WP_REDIS_CLUSTER )
                    ? WP_REDIS_CLUSTER
                    : current( $this->build_cluster_connection_array() );

                $this->diagnostics[ 'ping' ] = $client === 'predis'
                    ? $this->redis->getClientFor( $connectionID )->ping()
                    : $this->redis->ping( $connectionID );
            } else {
                $this->diagnostics[ 'ping' ] = $this->redis->ping();
            }

            $this->fetch_info();

            $this->redis_connected = true;
        } catch ( Exception $exception ) {
            $this->handle_exception( $exception );
        }

        // Assign global and blog prefixes for use with keys.
        if ( function_exists( 'is_multisite' ) ) {
            $this->global_prefix = is_multisite() ? '' : $table_prefix;
            $this->blog_prefix = is_multisite() ? $blog_id : $table_prefix;
        }
    }

    /**
     * Set group type array
     *
     * @return void
     */
    protected function cache_group_types() {
        foreach ( $this->global_groups as $group ) {
            $this->group_type[ $group ] = 'global';
        }

        foreach ( $this->unflushable_groups as $group ) {
            $this->group_type[ $group ] = 'unflushable';
        }

        foreach ( $this->ignored_groups as $group ) {
            $this->group_type[ $group ] = 'ignored';
        }
    }

    /**
     * Determine the Redis client.
     *
     * @return string
     */
    protected function determine_client() {
        $client = 'predis';

        if ( class_exists( 'Redis' ) ) {
            $client = defined( 'HHVM_VERSION' ) ? 'hhvm' : 'phpredis';
        }

        if ( defined( 'WP_REDIS_CLIENT' ) ) {
            $client = (string) WP_REDIS_CLIENT;
            $client = str_replace( 'pecl', 'phpredis', $client );
        }

        return trim( strtolower( $client ) );
    }

    /**
     * Build the connection parameters from config constants.
     *
     * @return array
     */
    protected function build_parameters() {
        $parameters = [
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
            'database' => 0,
            'timeout' => 1,
            'read_timeout' => 1,
            'retry_interval' => null,
            'persistent' => false,
        ];

        $settings = [
            'scheme',
            'host',
            'port',
            'path',
            'password',
            'database',
            'timeout',
            'read_timeout',
            'retry_interval',
        ];

        foreach ( $settings as $setting ) {
            $constant = sprintf( 'WP_REDIS_%s', strtoupper( $setting ) );

            if ( defined( $constant ) ) {
                $parameters[ $setting ] = constant( $constant );
            }
        }

        if ( isset( $parameters[ 'password' ] ) && $parameters[ 'password' ] === '' ) {
            unset( $parameters[ 'password' ] );
        }

        return $parameters;
    }

    /**
     * Connect to Redis using the PhpRedis (PECL) extension.
     *
     * @param  array $parameters Connection parameters built by the `build_parameters` method.
     * @return void
     */
    protected function connect_using_phpredis( $parameters ) {
        $version = phpversion( 'redis' );

        $this->diagnostics[ 'client' ] = sprintf( 'PhpRedis (v%s)', $version );

        if ( defined( 'WP_REDIS_SHARDS' ) ) {
            $this->redis = new RedisArray( array_values( WP_REDIS_SHARDS ) );

            $this->diagnostics[ 'shards' ] = WP_REDIS_SHARDS;
        } elseif ( defined( 'WP_REDIS_CLUSTER' ) ) {
            if ( is_string( WP_REDIS_CLUSTER ) ) {
                $this->redis = new RedisCluster( WP_REDIS_CLUSTER );
            } else {
                $args = [
                    'cluster' => $this->build_cluster_connection_array(),
                    'timeout' => $parameters['timeout'],
                    'read_timeout' => $parameters['read_timeout'],
                    'persistent' => $parameters['persistent'],
                ];

                if ( isset( $parameters['password'] ) && version_compare( $version, '4.3.0', '>=' ) ) {
                    $args['password'] = $parameters['password'];
                }

                $this->redis = new RedisCluster( null, ...array_values( $args ) );
                $this->diagnostics += $args;
            }
        } else {
            $this->redis = new Redis();

            $args = [
                'host' => $parameters['host'],
                'port' => $parameters['port'],
                'timeout' => $parameters['timeout'],
                '',
                'retry_interval' => (int) $parameters['retry_interval'],
            ];

            if ( strcasecmp( 'tls', $parameters['scheme'] ) === 0 ) {
                $args['host'] = sprintf(
                    '%s://%s',
                    $parameters['scheme'],
                    str_replace( 'tls://', '', $parameters['host'] )
                );
            }

            if ( strcasecmp( 'unix', $parameters['scheme'] ) === 0 ) {
                $args['host'] = $parameters['path'];
                $args['port'] = -1;
            }

            if ( version_compare( $version, '3.1.3', '>=' ) ) {
                $args['read_timeout'] = $parameters['read_timeout'];
            }

            call_user_func_array( [ $this->redis, 'connect' ], array_values( $args ) );

            if ( isset( $parameters['password'] ) ) {
                $args['password'] = $parameters['password'];
                $this->redis->auth( $parameters['password'] );
            }

            if ( isset( $parameters['database'] ) ) {
                if ( ctype_digit( (string) $parameters['database'] ) ) {
                    $parameters['database'] = (int) $parameters['database'];
                }

                $args['database'] = $parameters['database'];

                if ( $parameters['database'] ) {
                    $this->redis->select( $parameters['database'] );
                }
            }

            $this->diagnostics += $args;
        }

        if ( defined( 'WP_REDIS_SERIALIZER' ) && ! empty( WP_REDIS_SERIALIZER ) ) {
            $this->redis->setOption( Redis::OPT_SERIALIZER, WP_REDIS_SERIALIZER );
        }
    }

    /**
     * Connect to Redis using the Relay extension.
     *
     * @param  array $parameters Connection parameters built by the `build_parameters` method.
     * @return void
     */
    protected function connect_using_relay( $parameters ) {
        $version = phpversion( 'relay' );

        $this->diagnostics[ 'client' ] = sprintf( 'Relay (v%s)', $version );

        if ( defined( 'WP_REDIS_SHARDS' ) ) {
            throw new Exception('Relay does not support sharding.');
        } elseif ( defined( 'WP_REDIS_CLUSTER' ) ) {
            throw new Exception('Relay does not cluster connections.');
        } else {
            $this->redis = new Relay\Relay;

            $args = [
                'host' => $parameters['host'],
                'port' => $parameters['port'],
                'timeout' => $parameters['timeout'],
                '',
                'retry_interval' => (int) $parameters['retry_interval'],
            ];

            if ( strcasecmp( 'tls', $parameters['scheme'] ) === 0 ) {
                $args['host'] = sprintf(
                    '%s://%s',
                    $parameters['scheme'],
                    str_replace( 'tls://', '', $parameters['host'] )
                );
            }

            if ( strcasecmp( 'unix', $parameters['scheme'] ) === 0 ) {
                $args['host'] = $parameters['path'];
                $args['port'] = -1;
            }

            $args['read_timeout'] = $parameters['read_timeout'];

            call_user_func_array( [ $this->redis, 'connect' ], array_values( $args ) );

            if ( isset( $parameters['password'] ) ) {
                $args['password'] = $parameters['password'];
                $this->redis->auth( $parameters['password'] );
            }

            if ( isset( $parameters['database'] ) ) {
                if ( ctype_digit( (string) $parameters['database'] ) ) {
                    $parameters['database'] = (int) $parameters['database'];
                }

                $args['database'] = $parameters['database'];

                if ( $parameters['database'] ) {
                    $this->redis->select( $parameters['database'] );
                }
            }

            $this->diagnostics += $args;
        }

        if ( defined( 'WP_REDIS_SERIALIZER' ) && ! empty( WP_REDIS_SERIALIZER ) ) {
            $this->redis->setOption( Relay\Relay::OPT_SERIALIZER, WP_REDIS_SERIALIZER );
        }
    }

    /**
     * Connect to Redis using the Predis library.
     *
     * @param  array $parameters Connection parameters built by the `build_parameters` method.
     * @throws \Exception If the Predis library was not found or is unreadable.
     * @return void
     */
    protected function connect_using_predis( $parameters ) {
        $client = 'Predis';

        // Load bundled Predis library.
        if ( ! class_exists( 'Predis\Client' ) ) {
            $predis = sprintf(
                '%s/redis-cache/dependencies/predis/predis/autoload.php',
                defined( 'WP_PLUGIN_DIR' ) ? WP_PLUGIN_DIR : WP_CONTENT_DIR . '/plugins'
            );

            if ( is_readable( $predis ) ) {
                require_once $predis;
            } else {
                throw new Exception(
                    'Predis library not found. Re-install Redis Cache plugin or delete the object-cache.php.'
                );
            }
        }

        $servers = false;
        $options = [];

        if ( defined( 'WP_REDIS_SHARDS' ) ) {
            $servers = WP_REDIS_SHARDS;
            $parameters['shards'] = $servers;
        } elseif ( defined( 'WP_REDIS_SENTINEL' ) ) {
            $servers = WP_REDIS_SERVERS;
            $parameters['servers'] = $servers;
            $options['replication'] = 'sentinel';
            $options['service'] = WP_REDIS_SENTINEL;
        } elseif ( defined( 'WP_REDIS_SERVERS' ) ) {
            $servers = WP_REDIS_SERVERS;
            $parameters['servers'] = $servers;
            $options['replication'] = 'predis';
        } elseif ( defined( 'WP_REDIS_CLUSTER' ) ) {
            $servers = $this->build_cluster_connection_array();
            $parameters['cluster'] = $servers;
            $options['cluster'] = 'redis';
        }

        if ( isset( $parameters['read_timeout'] ) && $parameters['read_timeout'] ) {
            $parameters['read_write_timeout'] = $parameters['read_timeout'];
        }

        foreach ( [ 'WP_REDIS_SERVERS', 'WP_REDIS_SHARDS', 'WP_REDIS_CLUSTER' ] as $constant ) {
            if ( defined( $constant ) ) {
                if ( $parameters['database'] ) {
                    $options['parameters']['database'] = $parameters['database'];
                }

                if ( isset( $parameters['password'] ) ) {
                    $options['parameters']['password'] = WP_REDIS_PASSWORD;
                }
            }
        }

        $this->redis = new Predis\Client( $servers ?: $parameters, $options );
        $this->redis->connect();

        $this->diagnostics = array_merge(
            [ 'client' => sprintf( '%s (v%s)', $client, Predis\Client::VERSION ) ],
            $parameters,
            $options
        );
    }

    /**
     * Connect to Redis using the Credis library.
     *
     * @param  array $parameters Connection parameters built by the `build_parameters` method.
     * @throws \Exception If the Credis library was not found or is unreadable.
     * @throws \Exception If redis sharding should be configured as Credis does not support sharding.
     * @throws \Exception If more than one seninel is configured as Credis does not support multiple sentinel servers.
     * @return void
     */
    protected function connect_using_credis( $parameters ) {
        _doing_it_wrong( __FUNCTION__ , 'Credis support will be removed in future versions.' , '2.0.26' );

        $client = 'Credis';

        $creds_path = sprintf(
            '%s/redis-cache/dependencies/colinmollenhour/credis/',
            defined( 'WP_PLUGIN_DIR' ) ? WP_PLUGIN_DIR : WP_CONTENT_DIR . '/plugins'
        );

        $to_load = [];

        if ( ! class_exists( 'Credis_Client' ) ) {
            $to_load[] = 'Client.php';
        }

        $has_shards = defined( 'WP_REDIS_SHARDS' );
        $has_sentinel = defined( 'WP_REDIS_SENTINEL' );
        $has_servers = defined( 'WP_REDIS_SERVERS' );
        $has_cluster = defined( 'WP_REDIS_CLUSTER' );

        if ( ( $has_shards || $has_sentinel || $has_servers || $has_cluster ) && ! class_exists( 'Credis_Cluster' ) ) {
            $to_load[] = 'Cluster.php';

            if ( defined( 'WP_REDIS_SENTINEL' ) && ! class_exists( 'Credis_Sentinel' ) ) {
                $to_load[] = 'Sentinel.php';
            }
        }

        foreach ( $to_load as $sub_path ) {
            $path = $creds_path . $sub_path;

            if ( file_exists( $path ) ) {
                require_once $path;
            } else {
                throw new Exception(
                    'Credis library not found. Re-install Redis Cache plugin or delete object-cache.php.'
                );
            }
        }

        if ( defined( 'WP_REDIS_SHARDS' ) ) {
            throw new Exception(
                'Sharding not supported by bundled Credis library. Please review your Redis Cache configuration.'
            );
        }

        if ( defined( 'WP_REDIS_SENTINEL' ) ) {
            if ( is_array( WP_REDIS_SERVERS ) && count( WP_REDIS_SERVERS ) > 1 ) {
                throw new Exception(
                    'Multipe sentinel servers are not supported by the bundled Credis library. Please review your Redis Cache configuration.'
                );
            }

            $connection_string = array_values( WP_REDIS_SERVERS )[0];
            $sentinel = new Credis_Sentinel( new Credis_Client( $connection_string ) );
            $this->redis = $sentinel->getCluster( WP_REDIS_SENTINEL );
            $args['servers'] = WP_REDIS_SERVERS;
        } elseif ( defined( 'WP_REDIS_CLUSTER' ) || defined( 'WP_REDIS_SERVERS' ) ) {
            $parameters['db'] = $parameters['database'];

            $is_cluster = defined( 'WP_REDIS_CLUSTER' );
            $clients = $is_cluster ? WP_REDIS_CLUSTER : WP_REDIS_SERVERS;

            foreach ( $clients as $index => $connection_string ) {
                // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
                $url_components = parse_url( $connection_string );

                if ( isset( $url_components['query'] ) ) {
                    parse_str( $url_components['query'], $add_params );
                }

                if ( ! $is_cluster && isset( $add_params['alias'] ) ) {
                    $add_params['master'] = 'master' === $add_params['alias'];
                }

                $add_params['host'] = $url_components['host'];
                $add_params['port'] = $url_components['port'];

                if ( ! isset( $add_params['alias'] ) ) {
                    $add_params['alias'] = "redis-$index";
                }

                $clients[ $index ] = array_merge( $parameters, $add_params );

                unset($add_params);
            }

            $this->redis = new Credis_Cluster( $clients );

            foreach ( $clients as $index => $_client ) {
                $connection_string = "{$_client['scheme']}://{$_client['host']}:{$_client['port']}";
                unset( $_client['scheme'], $_client['host'], $_client['port'] );

                $params = array_filter( $_client );

                if ( $params ) {
                    $connection_string .= '?' . http_build_query( $params, '', '&' );
                }

                $clients[ $index ] = $connection_string;
            }

            $args['servers'] = $clients;
        } else {
            $args = [
                'host' => $parameters['scheme'] === 'unix' ? $parameters['path'] : $parameters['host'],
                'port' => $parameters['port'],
                'timeout' => $parameters['timeout'],
                'persistent' => '',
                'database' => $parameters['database'],
                'password' => isset( $parameters['password'] ) ? $parameters['password'] : null,
            ];

            $this->redis = new Credis_Client( ...array_values( $args ) );
        }

        // Don't use PhpRedis if it is available.
        $this->redis->forceStandalone();

        $this->redis->connect();

        if ( $parameters['read_timeout'] ) {
            $args['read_timeout'] = $parameters['read_timeout'];
            $this->redis->setReadTimeout( $parameters['read_timeout'] );
        }

        $this->diagnostics = array_merge(
            [ 'client' => sprintf( '%s (v%s)', $client, Credis_Client::VERSION ) ],
            $args
        );
    }

    /**
     * Connect to Redis using HHVM's Redis extension.
     *
     * @param  array $parameters Connection parameters built by the `build_parameters` method.
     * @return void
     */
    protected function connect_using_hhvm( $parameters ) {
        _doing_it_wrong( __FUNCTION__ , 'HHVM support will be removed in future versions.' , '2.0.26' );

        $this->redis = new Redis();

        // Adjust host and port if the scheme is `unix`.
        if ( strcasecmp( 'unix', $parameters['scheme'] ) === 0 ) {
            $parameters['host'] = 'unix://' . $parameters['path'];
            $parameters['port'] = 0;
        }

        $this->redis->connect(
            $parameters['host'],
            $parameters['port'],
            $parameters['timeout'],
            null,
            $parameters['retry_interval']
        );

        if ( $parameters['read_timeout'] ) {
            $this->redis->setOption( Redis::OPT_READ_TIMEOUT, $parameters['read_timeout'] );
        }

        if ( isset( $parameters['password'] ) ) {
            $this->redis->auth( $parameters['password'] );
        }

        if ( isset( $parameters['database'] ) ) {
            if ( ctype_digit( (string) $parameters['database'] ) ) {
                $parameters['database'] = (int) $parameters['database'];
            }

            if ( $parameters['database'] ) {
                $this->redis->select( $parameters['database'] );
            }
        }

        $this->diagnostics = array_merge(
            [ 'client' => sprintf( 'HHVM Extension (v%s)', HHVM_VERSION ) ],
            $parameters
        );
    }

    /**
     * Fetches Redis `INFO` mostly for server version.
     *
     * @return void
     */
    public function fetch_info() {
        $options = method_exists( $this->redis, 'getOptions' )
            ? $this->redis->getOptions()
            : new stdClass();

        if ( isset( $options->replication ) && $options->replication ) {
            return;
        }

        if ( defined( 'WP_REDIS_CLUSTER' ) ) {
            $connectionID = is_string( WP_REDIS_CLUSTER )
                ? 'SERVER'
                : current( $this->build_cluster_connection_array() );

            $info = $this->determine_client() === 'predis'
                ? $this->redis->getClientFor( $connectionID )->info()
                : $this->redis->info( $connectionID );
        } else {
            $info = $this->redis->info();
        }

        if ( isset( $info['redis_version'] ) ) {
            $this->redis_version = $info['redis_version'];
        } elseif ( isset( $info['Server']['redis_version'] ) ) {
            $this->redis_version = $info['Server']['redis_version'];
        }
    }

    /**
     * Is Redis available?
     *
     * @return bool
     */
    public function redis_status() {
        return (bool) $this->redis_connected;
    }

    /**
     * Returns the Redis instance.
     *
     * @return mixed
     */
    public function redis_instance() {
        return $this->redis;
    }

    /**
     * Returns the Redis server version.
     *
     * @return null|string
     */
    public function redis_version() {
        return $this->redis_version;
    }

    /**
     * Adds a value to cache.
     *
     * If the specified key already exists, the value is not stored and the function
     * returns false.
     *
     * @param   string $key            The key under which to store the value.
     * @param   mixed  $value          The value to store.
     * @param   string $group          The group value appended to the $key.
     * @param   int    $expiration     The expiration time, defaults to 0.
     * @return  bool                   Returns TRUE on success or FALSE on failure.
     */
    public function add( $key, $value, $group = 'default', $expiration = 0 ) {
        return $this->add_or_replace( true, $key, $value, $group, $expiration );
    }

    /**
     * Adds multiple values to the cache in one call.
     *
     * @param array  $data   Array of keys and values to be added.
     * @param string $group  Optional. Where the cache contents are grouped.
     * @param int    $expire Optional. When to expire the cache contents, in seconds.
     *                       Default 0 (no expiration).
     * @return bool[] Array of return values, grouped by key. Each value is either
     *                true on success, or false if cache key and group already exist.
     */
    public function add_multiple( array $data, $group = 'default', $expire = 0 ) {
        if ( function_exists( 'wp_suspend_cache_addition' ) && wp_suspend_cache_addition() ) {
            return array_combine( array_keys( $data ), array_fill( 0, count( $data ), false ) );
        }

        if (
            $this->redis_status() &&
            method_exists( $this->redis, 'pipeline' ) &&
            ! $this->is_ignored_group( $group )
        ) {
            return $this->add_multiple_at_once( $data, $group, $expire );
        }

        $values = [];

        foreach ( $data as $key => $value ) {
            $values[ $key ] = $this->add( $key, $value, $group, $expire );
        }

        return $values;
    }

    /**
     * Adds multiple values to the cache in one call.
     *
     * @param array  $data   Array of keys and values to be added.
     * @param string $group  Optional. Where the cache contents are grouped.
     * @param int    $expire Optional. When to expire the cache contents, in seconds.
     *                       Default 0 (no expiration).
     * @return bool[] Array of return values, grouped by key. Each value is either
     *                true on success, or false if cache key and group already exist.
     */
    protected function add_multiple_at_once( array $data, $group = 'default', $expire = 0 )
    {
        $keys = array_keys( $data );

        $san_group = $this->sanitize_key_part( $group );

        $tx = $this->redis->pipeline();

        $orig_exp = $expire;
        $expire = $this->validate_expiration( $expire );
        $derived_keys = [];

        foreach ( $data as $key => $value ) {
            /**
             * Filters the cache expiration time
             *
             * @param int    $expiration The time in seconds the entry expires. 0 for no expiry.
             * @param string $key        The cache key.
             * @param string $group      The cache group.
             * @param mixed  $orig_exp   The original expiration value before validation.
             */
            $expire = apply_filters( 'redis_cache_expiration', $expire, $key, $group, $orig_exp );

            $san_key = $this->sanitize_key_part( $key );
            $derived_key = $derived_keys[ $key ] = $this->fast_build_key( $san_key, $san_group );

            $args = [ $derived_key, $this->maybe_serialize( $value ) ];

            if ( $this->is_predis() ) {
                $args[] = 'nx';

                if ( $expire ) {
                    $args[] = 'ex';
                    $args[] = $expire;
                }
            } else {
                if ( $expire ) {
                    $args[] = [ 'nx', 'ex' => $expire ];
                } else {
                    $args[] = [ 'nx' ];
                }
            }

            $tx->set( ...$args );
        }

        try {
            $start_time = microtime( true );

            $method = $this->is_predis() ? 'execute' : 'exec';

            $results = array_map( function ( $response ) {
                return (bool) $this->parse_redis_response( $response );
            }, $tx->{$method}() ?? [] );

            if ( count( $results ) !== count( $keys ) ) {
                throw new Exception( 'Redis pipeline returned unexpected result' );
            }

            $results = array_combine( $keys, $results );

            foreach ( $results as $key => $result ) {
                if ( $result ) {
                    $this->add_to_internal_cache( $derived_keys[ $key ], $data[ $key ] );
                }
            }

            $execute_time = microtime( true ) - $start_time;

            $this->cache_calls++;
            $this->cache_time += $execute_time;
        } catch ( Exception $exception ) {
            $this->handle_exception( $exception );

            return array_combine( $keys, array_fill( 0, count( $keys ), false ) );
        }

        return $results;
    }

    /**
     * Replace a value in the cache.
     *
     * If the specified key doesn't exist, the value is not stored and the function
     * returns false.
     *
     * @param   string $key            The key under which to store the value.
     * @param   mixed  $value          The value to store.
     * @param   string $group          The group value appended to the $key.
     * @param   int    $expiration     The expiration time, defaults to 0.
     * @return  bool                   Returns TRUE on success or FALSE on failure.
     */
    public function replace( $key, $value, $group = 'default', $expiration = 0 ) {
        return $this->add_or_replace( false, $key, $value, $group, $expiration );
    }

    /**
     * Add or replace a value in the cache.
     *
     * Add does not set the value if the key exists; replace does not replace if the value doesn't exist.
     *
     * @param   bool   $add            True if should only add if value doesn't exist, false to only add when value already exists.
     * @param   string $key            The key under which to store the value.
     * @param   mixed  $value          The value to store.
     * @param   string $group          The group value appended to the $key.
     * @param   int    $expiration     The expiration time, defaults to 0.
     * @return  bool                   Returns TRUE on success or FALSE on failure.
     */
    protected function add_or_replace( $add, $key, $value, $group = 'default', $expiration = 0 ) {
        $cache_addition_suspended = function_exists( 'wp_suspend_cache_addition' ) && wp_suspend_cache_addition();

        if ( $add && $cache_addition_suspended ) {
            return false;
        }

        $result = true;

        $san_key = $this->sanitize_key_part( $key );
        $san_group = $this->sanitize_key_part( $group );

        $derived_key = $this->fast_build_key( $san_key, $san_group );

        // Save if group not excluded and redis is up.
        if ( ! $this->is_ignored_group( $san_group ) && $this->redis_status() ) {
            try {
                $orig_exp = $expiration;
                $expiration = $this->validate_expiration( $expiration );

                /**
                 * Filters the cache expiration time
                 *
                 * @since 1.4.2
                 * @param int    $expiration The time in seconds the entry expires. 0 for no expiry.
                 * @param string $key        The cache key.
                 * @param string $group      The cache group.
                 * @param mixed  $orig_exp   The original expiration value before validation.
                 */
                $expiration = apply_filters( 'redis_cache_expiration', $expiration, $key, $group, $orig_exp );
                $start_time = microtime( true );

                if ( $add ) {
                    $args = [ $derived_key, $this->maybe_serialize( $value ) ];

                    if ( $this->is_predis() ) {
                        $args[] = 'nx';

                        if ( $expiration ) {
                            $args[] = 'ex';
                            $args[] = $expiration;
                        }
                    } else {
                        if ( $expiration ) {
                            $args[] = [
                                'nx',
                                'ex' => $expiration,
                            ];
                        } else {
                            $args[] = [ 'nx' ];
                        }
                    }

                    $result = $this->parse_redis_response(
                        $this->redis->set( ...$args )
                    );

                    if ( ! $result ) {
                        return false;
                    }
                } elseif ( $expiration ) {
                    $result = $this->parse_redis_response( $this->redis->setex( $derived_key, $expiration, $this->maybe_serialize( $value ) ) );
                } else {
                    $result = $this->parse_redis_response( $this->redis->set( $derived_key, $this->maybe_serialize( $value ) ) );
                }

                $execute_time = microtime( true ) - $start_time;

                $this->cache_calls++;
                $this->cache_time += $execute_time;
            } catch ( Exception $exception ) {
                $this->handle_exception( $exception );

                return false;
            }
        }

        $exists = array_key_exists( $derived_key, $this->cache );

        if ( (bool) $add === $exists ) {
            return false;
        }

        if ( $result ) {
            $this->add_to_internal_cache( $derived_key, $value );
        }

        return $result;
    }

    /**
     * Remove the item from the cache.
     *
     * @param   string $key        The key under which to store the value.
     * @param   string $group      The group value appended to the $key.
     * @return  bool               Returns TRUE on success or FALSE on failure.
     */
    public function delete( $key, $group = 'default', $deprecated = false ) {
        $result = false;

        $san_key = $this->sanitize_key_part( $key );
        $san_group = $this->sanitize_key_part( $group );

        $derived_key = $this->fast_build_key( $san_key, $san_group );

        if ( array_key_exists( $derived_key, $this->cache ) ) {
            unset( $this->cache[ $derived_key ] );
            $result = true;
        }

        $start_time = microtime( true );

        if ( $this->redis_status() && ! $this->is_ignored_group( $san_group ) ) {
            try {
                $result = $this->parse_redis_response( $this->redis->del( $derived_key ) );
            } catch ( Exception $exception ) {
                $this->handle_exception( $exception );

                return false;
            }
        }

        $execute_time = microtime( true ) - $start_time;

        $this->cache_calls++;
        $this->cache_time += $execute_time;

        if ( function_exists( 'do_action' ) ) {
            /**
             * Fires on every cache key deletion
             *
             * @since 1.3.3
             * @param string $key          The cache key.
             * @param string $group        The group value appended to the $key.
             * @param float  $execute_time Execution time for the request in seconds.
             */
            do_action( 'redis_object_cache_delete', $key, $group, $execute_time );
        }

        return (bool) $result;
    }

    /**
     * Deletes multiple values from the cache in one call.
     *
     * @param array  $keys  Array of keys to be deleted.
     * @param string $group Optional. Where the cache contents are grouped.
     * @return bool[] Array of return values, grouped by key. Each value is either
     *                true on success, or false if the contents were not deleted.
     */
    public function delete_multiple( array $keys, $group = 'default' ) {
        if (
            $this->redis_status() &&
            method_exists( $this->redis, 'pipeline' ) &&
            ! $this->is_ignored_group( $group )
        ) {
            return $this->delete_multiple_at_once( $keys, $group );
        }

        $values = [];

        foreach ( $keys as $key ) {
            $values[ $key ] = $this->delete( $key, $group );
        }

        return $values;
    }

    /**
     * Deletes multiple values from the cache in one call.
     *
     * @param array  $keys  Array of keys to be deleted.
     * @param string $group Optional. Where the cache contents are grouped.
     * @return bool[] Array of return values, grouped by key. Each value is either
     *                true on success, or false if the contents were not deleted.
     */
    protected function delete_multiple_at_once( array $keys, $group = 'default' ) {
        $start_time = microtime( true );

        try {
            $tx = $this->redis->pipeline();

            foreach ( $keys as $key ) {
                $derived_key = $this->build_key( (string) $key, $group );

                $tx->del( $derived_key );

                unset( $this->cache[ $derived_key ] );
            }

            $method = $this->is_predis() ? 'execute' : 'exec';

            $results = array_map( function ( $response ) {
                return (bool) $this->parse_redis_response( $response );
            }, $tx->{$method}() ?? [] );

            if ( count( $results ) !== count( $keys ) ) {
                throw new Exception( 'Redis pipeline returned unexpected result' );
            }

            $execute_time = microtime( true ) - $start_time;
        } catch ( Exception $exception ) {
            $this->handle_exception( $exception );

            return array_combine( $keys, array_fill( 0, count( $keys ), false ) );
        }

        if ( function_exists( 'do_action' ) ) {
            foreach ( $keys as $key ) {
                /**
                 * Fires on every cache key deletion
                 *
                 * @since 1.3.3
                 * @param string $key          The cache key.
                 * @param string $group        The group value appended to the $key.
                 * @param float  $execute_time Execution time for the request in seconds.
                 */
                do_action( 'redis_object_cache_delete', $key, $group, $execute_time );
            }
        }

        return array_combine( $keys, $results );
    }

    /**
     * Removes all cache items from the in-memory runtime cache.
     *
     * @return bool True on success, false on failure.
     */
    public function flush_runtime() {
        $this->cache = [];

        return true;
    }

    /**
     * Invalidate all items in the cache. If `WP_REDIS_SELECTIVE_FLUSH` is `true`,
     * only keys prefixed with the `WP_REDIS_PREFIX` are flushed.
     *
     * @param   int $delay      Number of seconds to wait before invalidating the items.
     * @return  bool            Returns TRUE on success or FALSE on failure.
     */
    public function flush( $delay = 0 ) {
        $delay = abs( (int) $delay );

        if ( $delay ) {
            sleep( $delay );
        }

        $results = [];
        $this->cache = [];

        if ( $this->redis_status() ) {
            $salt = defined( 'WP_REDIS_PREFIX' ) ? trim( WP_REDIS_PREFIX ) : null;
            $selective = defined( 'WP_REDIS_SELECTIVE_FLUSH' ) ? WP_REDIS_SELECTIVE_FLUSH : null;

            $start_time = microtime( true );

            if ( $salt && $selective ) {
                $script = $this->get_flush_closure( $salt );

                if ( defined( 'WP_REDIS_CLUSTER' ) ) {
                    try {
                        foreach ( $this->redis->_masters() as $master ) {
                            $redis = new Redis();
                            $redis->connect( $master[0], $master[1] );
                            $results[] = $this->parse_redis_response( $script() );
                            unset( $redis );
                        }
                    } catch ( Exception $exception ) {
                        $this->handle_exception( $exception );

                        return false;
                    }
                } else {
                    try {
                        $results[] = $this->parse_redis_response( $script() );
                    } catch ( Exception $exception ) {
                        $this->handle_exception( $exception );

                        return false;
                    }
                }
            } else {
                if ( defined( 'WP_REDIS_CLUSTER' ) ) {
                    try {
                        foreach ( $this->redis->_masters() as $master ) {
                            $results[] = $this->parse_redis_response( $this->redis->flushdb( $master ) );
                        }
                    } catch ( Exception $exception ) {
                        $this->handle_exception( $exception );

                        return false;
                    }
                } else {
                    try {
                        $results[] = $this->parse_redis_response( $this->redis->flushdb() );
                    } catch ( Exception $exception ) {
                        $this->handle_exception( $exception );

                        return false;
                    }
                }
            }

            if ( function_exists( 'do_action' ) ) {
                $execute_time = microtime( true ) - $start_time;

                /**
                 * Fires on every cache flush
                 *
                 * @since 1.3.5
                 * @param null|array $results      Array of flush results.
                 * @param int        $delay        Given number of seconds to waited before invalidating the items.
                 * @param bool       $seletive     Whether a selective flush took place.
                 * @param string     $salt         The defined key prefix.
                 * @param float      $execute_time Execution time for the request in seconds.
                 */
                do_action( 'redis_object_cache_flush', $results, $delay, $selective, $salt, $execute_time );
            }
        }

        if ( empty( $results ) ) {
            return false;
        }

        foreach ( $results as $result ) {
            if ( ! $result ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns a closure to flush selectively.
     *
     * @param   string $salt  The salt to be used to differentiate.
     * @return  callable      Generated callable executing the lua script.
     */
    protected function get_flush_closure( $salt ) {
        if ( $this->unflushable_groups ) {
            return $this->lua_flush_extended_closure( $salt );
        } else {
            return $this->lua_flush_closure( $salt );
        }
    }

    /**
     * Quotes a string for usage in the `glob` function
     *
     * @param string $string The string to quote.
     * @return string
     */
    protected function glob_quote( $string ) {
        $characters = [ '*', '+', '?', '!', '{', '}', '[', ']', '(', ')', '|', '@' ];

        return str_replace(
            $characters,
            array_map(
                function ( $character ) {
                    return "[{$character}]";
                },
                $characters
            ),
            $string
        );
    }

    /**
     * Returns a closure ready to be called to flush selectively ignoring unflushable groups.
     *
     * @param   string $salt  The salt to be used to differentiate.
     * @return  callable      Generated callable executing the lua script.
     */
    protected function lua_flush_closure( $salt ) {
        $salt = $this->glob_quote( $salt );

        return function () use ( $salt ) {
            $script = <<<LUA
                local cur = 0
                local i = 0
                local tmp
                repeat
                    tmp = redis.call('SCAN', cur, 'MATCH', '{$salt}*')
                    cur = tonumber(tmp[1])
                    if tmp[2] then
                        for _, v in pairs(tmp[2]) do
                            redis.call('del', v)
                            i = i + 1
                        end
                    end
                until 0 == cur
                return i
LUA;

            if ( version_compare( $this->redis_version(), '5', '<' ) && version_compare( $this->redis_version(), '3.2', '>=' ) ) {
                $script = 'redis.replicate_commands()' . "\n" . $script;
            }

            $args = $this->is_predis() ? [ $script, 0 ] : [ $script ];

            return call_user_func_array( [ $this->redis, 'eval' ], $args );
        };
    }

    /**
     * Returns a closure ready to be called to flush selectively.
     *
     * @param   string $salt  The salt to be used to differentiate.
     * @return  callable      Generated callable executing the lua script.
     */
    protected function lua_flush_extended_closure( $salt ) {
        $salt = $this->glob_quote( $salt );

        return function () use ( $salt ) {
            $salt_length = strlen( $salt );

            $unflushable = array_map(
                function ( $group ) {
                    return ":{$group}:";
                },
                $this->unflushable_groups
            );

            $script = <<<LUA
                local cur = 0
                local i = 0
                local d, tmp
                repeat
                    tmp = redis.call('SCAN', cur, 'MATCH', '{$salt}*')
                    cur = tonumber(tmp[1])
                    if tmp[2] then
                        for _, v in pairs(tmp[2]) do
                            d = true
                            for _, s in pairs(KEYS) do
                                d = d and not v:find(s, {$salt_length})
                                if not d then break end
                            end
                            if d then
                                redis.call('del', v)
                                i = i + 1
                            end
                        end
                    end
                until 0 == cur
                return i
LUA;
            if ( version_compare( $this->redis_version(), '5', '<' ) && version_compare( $this->redis_version(), '3.2', '>=' ) ) {
                $script = 'redis.replicate_commands()' . "\n" . $script;
            }

            $args = $this->is_predis()
                ? array_merge( [ $script, count( $unflushable ) ], $unflushable )
                : [ $script, $unflushable, count( $unflushable ) ];

            return call_user_func_array( [ $this->redis, 'eval' ], $args );
        };
    }

    /**
     * Retrieve object from cache.
     *
     * Gets an object from cache based on $key and $group.
     *
     * @param   string $key        The key under which to store the value.
     * @param   string $group      The group value appended to the $key.
     * @param   bool   $force      Optional. Whether to force a refetch rather than relying on the local
     *                             cache. Default false.
     * @param   bool   $found      Optional. Whether the key was found in the cache. Disambiguates a return of
     *                             false, a storable value. Passed by reference. Default null.
     * @return  bool|mixed         Cached object value.
     */
    public function get( $key, $group = 'default', $force = false, &$found = null ) {
        $san_key = $this->sanitize_key_part( $key );
        $san_group = $this->sanitize_key_part( $group );
        $derived_key = $this->fast_build_key( $san_key, $san_group );

        if ( array_key_exists( $derived_key, $this->cache ) && ! $force ) {
            $found = true;
            $this->cache_hits++;
            $value = $this->get_from_internal_cache( $derived_key );

            return $value;
        } elseif ( $this->is_ignored_group( $group ) || ! $this->redis_status() ) {
            $found = false;
            $this->cache_misses++;

            return false;
        }

        $start_time = microtime( true );

        try {
            $result = $this->redis->get( $derived_key );
        } catch ( Exception $exception ) {
            $this->handle_exception( $exception );

            return false;
        }

        $execute_time = microtime( true ) - $start_time;

        $this->cache_calls++;
        $this->cache_time += $execute_time;

        if ( $result === null || $result === false ) {
            $found = false;
            $this->cache_misses++;

            return false;
        } else {
            $found = true;
            $this->cache_hits++;
            $value = $this->maybe_unserialize( $result );
        }

        $this->add_to_internal_cache( $derived_key, $value );

        if ( function_exists( 'do_action' ) ) {
            /**
             * Fires on every cache get request
             *
             * @since 1.2.2
             * @param mixed  $value        Value of the cache entry.
             * @param string $key          The cache key.
             * @param string $group        The group value appended to the $key.
             * @param bool   $force        Whether a forced refetch has taken place rather than relying on the local cache.
             * @param bool   $found        Whether the key was found in the cache.
             * @param float  $execute_time Execution time for the request in seconds.
             */
            do_action( 'redis_object_cache_get', $key, $value, $group, $force, $found, $execute_time );
        }

        if ( function_exists( 'apply_filters' ) && function_exists( 'has_filter' ) ) {
            if ( has_filter( 'redis_object_cache_get_value' ) ) {
                /**
                 * Filters the return value
                 *
                 * @since 1.4.2
                 * @param mixed  $value Value of the cache entry.
                 * @param string $key   The cache key.
                 * @param string $group The group value appended to the $key.
                 * @param bool   $force Whether a forced refetch has taken place rather than relying on the local cache.
                 * @param bool   $found Whether the key was found in the cache.
                 */
                return apply_filters( 'redis_object_cache_get_value', $value, $key, $group, $force, $found );
            }
        }

        return $value;
    }

    /**
     * Retrieves multiple values from the cache in one call.
     *
     * @param array  $keys  Array of keys under which the cache contents are stored.
     * @param string $group Optional. Where the cache contents are grouped. Default empty.
     * @param bool   $force Optional. Whether to force an update of the local cache
     *                      from the persistent cache. Default false.
     * @return array|false Array of values organized into groups.
     */
    public function get_multiple( $keys, $group = 'default', $force = false ) {
        if ( ! is_array( $keys ) ) {
            return false;
        }

        $cache = [];
        $derived_keys = [];
        $start_time = microtime( true );

        $san_group = $this->sanitize_key_part( $group );

        foreach ( $keys as $key ) {
            $san_key = $this->sanitize_key_part( $key );
            $derived_keys[ $key ] = $this->fast_build_key( $san_key, $san_group );
        }

        if ( $this->is_ignored_group( $group ) || ! $this->redis_status() ) {
            foreach ( $keys as $key ) {
                $value = $this->get_from_internal_cache( $derived_keys[ $key ] );
                $cache[ $key ] = $value;

                if ($value === false) {
                    $this->cache_misses++;
                } else {
                    $this->cache_hits++;
                }
            }

            return $cache;
        }

        if ( ! $force ) {
            foreach ( $keys as $key ) {
                $value = $this->get_from_internal_cache( $derived_keys[ $key ] );

                if ( $value === false ) {
                    $this->cache_misses++;

                } else {
                    $cache[ $key ] = $value;
                    $this->cache_hits++;
                }
            }
        }

        $remaining_keys = array_filter(
            $keys,
            function ( $key ) use ( $cache ) {
                return ! array_key_exists( $key, $cache );
            }
        );

        if ( empty( $remaining_keys ) ) {
            return $cache;
        }

        $start_time = microtime( true );
        $results = [];

        $remaining_ids = array_map(
            function ( $key ) use ( $derived_keys ) {
                return $derived_keys[ $key ];
            },
            $remaining_keys
        );

        try {
            $results = array_combine(
                $remaining_keys,
                $this->redis->mget( $remaining_ids )
                    ?: array_fill( 0, count( $remaining_ids ), false )
            );
        } catch ( Exception $exception ) {
            $this->handle_exception( $exception );

            $results = array_combine(
                $remaining_keys,
                array_fill( 0, count( $remaining_ids ), false )
            );
        }

        $execute_time = microtime( true ) - $start_time;

        $this->cache_calls++;
        $this->cache_time += $execute_time;

        foreach ( $results as $key => $value ) {
            if ( $value === null || $value === false ) {
                $cache[ $key ] = false;
                $this->cache_misses++;
            } else {
                $cache[ $key ] = $this->maybe_unserialize( $value );
                $this->add_to_internal_cache( $derived_keys[ $key ], $cache[ $key ] );
                $this->cache_hits++;
            }
        }

        if ( function_exists( 'do_action' ) ) {
            /**
             * Fires on every cache get multiple request
             *
             * @since 2.0.6
             * @param array  $keys         Array of keys under which the cache contents are stored.
             * @param array  $cache        Cache items.
             * @param string $group        The group value appended to the $key.
             * @param bool   $force        Whether a forced refetch has taken place rather than relying on the local cache.
             * @param float  $execute_time Execution time for the request in seconds.
             */
            do_action( 'redis_object_cache_get_multiple', $keys, $cache, $group, $force, $execute_time );
        }

        if ( function_exists( 'apply_filters' ) && function_exists( 'has_filter' ) ) {
            if ( has_filter( 'redis_object_cache_get_value' ) ) {
                foreach ( $cache as $key => $value ) {
                    /**
                     * Filters the return value
                     *
                     * @since 1.4.2
                     * @param mixed  $value Value of the cache entry.
                     * @param string $key   The cache key.
                     * @param string $group The group value appended to the $key.
                     * @param bool   $force Whether a forced refetch has taken place rather than relying on the local cache.
                     */
                    $cache[ $key ] = apply_filters( 'redis_object_cache_get_value', $value, $key, $group, $force );
                }
            }
        }

        return $cache;
    }

    /**
     * Sets a value in cache.
     *
     * The value is set whether or not this key already exists in Redis.
     *
     * @param   string $key        The key under which to store the value.
     * @param   mixed  $value      The value to store.
     * @param   string $group      The group value appended to the $key.
     * @param   int    $expiration The expiration time, defaults to 0.
     * @return  bool               Returns TRUE on success or FALSE on failure.
     */
    public function set( $key, $value, $group = 'default', $expiration = 0 ) {
        $result = true;
        $start_time = microtime( true );

        $san_key = $this->sanitize_key_part( $key );
        $san_group = $this->sanitize_key_part( $group );

        $derived_key = $this->fast_build_key( $san_key, $san_group );

        // Save if group not excluded from redis and redis is up.
        if ( ! $this->is_ignored_group( $group ) && $this->redis_status() ) {
            $orig_exp = $expiration;
            $expiration = $this->validate_expiration( $expiration );

            /**
             * Filters the cache expiration time
             *
             * @since 1.4.2
             * @param int    $expiration The time in seconds the entry expires. 0 for no expiry.
             * @param string $key        The cache key.
             * @param string $group      The cache group.
             * @param mixed  $orig_exp   The original expiration value before validation.
             */
            $expiration = apply_filters( 'redis_cache_expiration', $expiration, $key, $group, $orig_exp );

            try {
                if ( $expiration ) {
                    $result = $this->parse_redis_response( $this->redis->setex( $derived_key, $expiration, $this->maybe_serialize( $value ) ) );
                } else {
                    $result = $this->parse_redis_response( $this->redis->set( $derived_key, $this->maybe_serialize( $value ) ) );
                }
            } catch ( Exception $exception ) {
                $this->handle_exception( $exception );

                return false;
            }

            $execute_time = microtime( true ) - $start_time;
            $this->cache_calls++;
            $this->cache_time += $execute_time;
        }

        // If the set was successful, or we didn't go to redis.
        if ( $result ) {
            $this->add_to_internal_cache( $derived_key, $value );
        }

        if ( function_exists( 'do_action' ) ) {
            $execute_time = microtime( true ) - $start_time;

            /**
             * Fires on every cache set
             *
             * @since 1.2.2
             * @param string $key          The cache key.
             * @param mixed  $value        Value of the cache entry.
             * @param string $group        The group value appended to the $key.
             * @param int    $expiration   The time in seconds the entry expires. 0 for no expiry.
             * @param float  $execute_time Execution time for the request in seconds.
             */
            do_action( 'redis_object_cache_set', $key, $value, $group, $expiration, $execute_time );
        }

        return $result;
    }

    /**
     * Sets multiple values to the cache in one call.
     *
     * @param array  $data   Array of key and value to be set.
     * @param string $group  Optional. Where the cache contents are grouped.
     * @param int    $expire Optional. When to expire the cache contents, in seconds.
     *                       Default 0 (no expiration).
     * @return bool[] Array of return values, grouped by key. Each value is always true.
     */
    public function set_multiple( array $data, $group = 'default', $expire = 0 ) {
        if (
            $this->redis_status() &&
            method_exists( $this->redis, 'pipeline' ) &&
            ! $this->is_ignored_group( $group )
        ) {
            return $this->set_multiple_at_once( $data, $group, $expire );
        }

        $values = [];

        foreach ( $data as $key => $value ) {
            $values[ $key ] = $this->set( $key, $value, $group, $expire );
        }

        return $values;
    }

    /**
     * Sets multiple values to the cache in one call.
     *
     * @param array  $data       Array of key and value to be set.
     * @param string $group      Optional. Where the cache contents are grouped.
     * @param int    $expiration Optional. When to expire the cache contents, in seconds.
     *                           Default 0 (no expiration).
     * @return bool[] Array of return values, grouped by key. Each value is always true.
     */
    protected function set_multiple_at_once( array $data, $group = 'default', $expiration = 0 )
    {
        $start_time = microtime( true );

        $san_group = $this->sanitize_key_part( $group );
        $derived_keys = [];

        $orig_exp = $expiration;
        $expiration = $this->validate_expiration( $expiration );
        $expirations = [];

        $tx = $this->redis->pipeline();
        $keys = array_keys( $data );

        foreach ( $data as $key => $value ) {
            $san_key = $this->sanitize_key_part( $key );
            $derived_key = $derived_keys[ $key ] = $this->fast_build_key( $san_key, $san_group );

            /**
             * Filters the cache expiration time
             *
             * @param int    $expiration The time in seconds the entry expires. 0 for no expiry.
             * @param string $key        The cache key.
             * @param string $group      The cache group.
             * @param mixed  $orig_exp   The original expiration value before validation.
             */
            $expiration = $expirations[ $key ] = apply_filters( 'redis_cache_expiration', $expiration, $key, $group, $orig_exp );

            if ( $expiration ) {
                $tx->setex( $derived_key, $expiration, $this->maybe_serialize( $value ) );
            } else {
                $tx->set( $derived_key, $this->maybe_serialize( $value ) );
            }
        }

        try {
            $method = $this->is_predis() ? 'execute' : 'exec';

            $results = array_map( function ( $response ) {
                return (bool) $this->parse_redis_response( $response );
            }, $tx->{$method}() ?? [] );

            if ( count( $results ) !== count( $keys ) ) {
                throw new Exception( 'Redis pipeline returned unexpected result' );
            }

            $results = array_combine( $keys, $results );

            foreach ( $results as $key => $result ) {
                if ( $result ) {
                    $this->add_to_internal_cache( $derived_keys[ $key ], $data[ $key ] );
                }
            }
        } catch ( Exception $exception ) {
            $this->handle_exception( $exception );

            return array_combine( $keys, array_fill( 0, count( $keys ), false ) );
        }

        $execute_time = microtime( true ) - $start_time;

        $this->cache_calls++;
        $this->cache_time += $execute_time;

        if ( function_exists( 'do_action' ) ) {
            foreach ( $data as $key => $value ) {
                /**
                 * Fires on every cache set
                 *
                 * @param string $key          The cache key.
                 * @param mixed  $value        Value of the cache entry.
                 * @param string $group        The group value appended to the $key.
                 * @param int    $expiration   The time in seconds the entry expires. 0 for no expiry.
                 * @param float  $execute_time Execution time for the request in seconds.
                 */
                do_action( 'redis_object_cache_set', $key, $value, $group, $expirations[ $key ], $execute_time );
            }
        }

        return $results;
    }

    /**
     * Increment a Redis counter by the amount specified
     *
     * @param  string $key    The key name.
     * @param  int    $offset Optional. The increment. Defaults to 1.
     * @param  string $group  Optional. The key group. Default is 'default'.
     * @return int|bool
     */
    public function increment( $key, $offset = 1, $group = 'default' ) {
        $offset = (int) $offset;
        $start_time = microtime( true );

        $san_key = $this->sanitize_key_part( $key );
        $san_group = $this->sanitize_key_part( $group );

        $derived_key = $this->fast_build_key( $san_key, $san_group );

        // If group is a non-Redis group, save to internal cache, not Redis.
        if ( $this->is_ignored_group( $group ) || ! $this->redis_status() ) {
            $value = $this->get_from_internal_cache( $derived_key );
            $value += $offset;
            $this->add_to_internal_cache( $derived_key, $value );

            return $value;
        }

        try {
            $result = $this->parse_redis_response( $this->redis->incrBy( $derived_key, $offset ) );

            $this->add_to_internal_cache( $derived_key, (int) $this->redis->get( $derived_key ) );
        } catch ( Exception $exception ) {
            $this->handle_exception( $exception );

            return false;
        }

        $execute_time = microtime( true ) - $start_time;

        $this->cache_calls += 2;
        $this->cache_time += $execute_time;

        return $result;
    }

    /**
     * Alias of `increment()`.
     *
     * @see self::increment()
     * @param  string $key    The key name.
     * @param  int    $offset Optional. The increment. Defaults to 1.
     * @param  string $group  Optional. The key group. Default is 'default'.
     * @return int|bool
     */
    public function incr( $key, $offset = 1, $group = 'default' ) {
        return $this->increment( $key, $offset, $group );
    }

    /**
     * Decrement a Redis counter by the amount specified
     *
     * @param  string $key    The key name.
     * @param  int    $offset Optional. The decrement. Defaults to 1.
     * @param  string $group  Optional. The key group. Default is 'default'.
     * @return int|bool
     */
    public function decrement( $key, $offset = 1, $group = 'default' ) {
        $offset = (int) $offset;
        $start_time = microtime( true );

        $san_key = $this->sanitize_key_part( $key );
        $san_group = $this->sanitize_key_part( $group );

        $derived_key = $this->fast_build_key( $san_key, $san_group );

        // If group is a non-Redis group, save to internal cache, not Redis.
        if ( $this->is_ignored_group( $group ) || ! $this->redis_status() ) {
            $value = $this->get_from_internal_cache( $derived_key );
            $value -= $offset;
            $this->add_to_internal_cache( $derived_key, $value );

            return $value;
        }

        try {
            $result = $this->parse_redis_response( $this->redis->decrBy( $derived_key, $offset ) );

            $this->add_to_internal_cache( $derived_key, (int) $this->redis->get( $derived_key ) );
        } catch ( Exception $exception ) {
            $this->handle_exception( $exception );

            return false;
        }

        $execute_time = microtime( true ) - $start_time;

        $this->cache_calls += 2;
        $this->cache_time += $execute_time;

        return $result;
    }

    /**
     * Alias of `decrement()`.
     *
     * @see self::decrement()
     * @param  string $key    The key name.
     * @param  int    $offset Optional. The decrement. Defaults to 1.
     * @param  string $group  Optional. The key group. Default is 'default'.
     * @return int|bool
     */
    public function decr( $key, $offset = 1, $group = 'default' ) {
        return $this->decrement( $key, $offset, $group );
    }

    /**
     * Render data about current cache requests
     * Used by the Debug bar plugin
     *
     * @return void
     */
    public function stats() {
        ?>
    <p>
        <strong>Redis Status:</strong>
        <?php echo $this->redis_status() ? 'Connected' : 'Not connected'; ?>
        <br />
        <strong>Redis Client:</strong>
        <?php echo $this->diagnostics['client'] ?: 'Unknown'; ?>
        <br />
        <strong>Cache Hits:</strong>
        <?php echo (int) $this->cache_hits; ?>
        <br />
        <strong>Cache Misses:</strong>
        <?php echo (int) $this->cache_misses; ?>
        <br />
        <strong>Cache Size:</strong>
        <?php echo number_format( strlen( serialize( $this->cache ) ) / 1024, 2 ); ?> KB
    </p>
        <?php
    }

    /**
     * Returns various information about the object cache.
     *
     * @return object
     */
    public function info() {
        $total = $this->cache_hits + $this->cache_misses;

        $bytes = array_map(
            function ( $keys ) {
                // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
                return strlen( serialize( $keys ) );
            },
            $this->cache
        );

        return (object) [
            'hits' => $this->cache_hits,
            'misses' => $this->cache_misses,
            'ratio' => $total > 0 ? round( $this->cache_hits / ( $total / 100 ), 1 ) : 100,
            'bytes' => array_sum( $bytes ),
            'time' => $this->cache_time,
            'calls' => $this->cache_calls,
            'groups' => (object) [
                'global' => $this->global_groups,
                'non_persistent' => $this->ignored_groups,
                'unflushable' => $this->unflushable_groups,
            ],
            'errors' => empty( $this->errors ) ? null : $this->errors,
            'meta' => [
                'Client' => $this->diagnostics['client'] ?? 'Unknown',
                'Redis Version' => $this->redis_version,
            ],
        ];
    }

    /**
     * Builds a key for the cached object using the prefix, group and key.
     *
     * @param   string $key        The key under which to store the value, pre-sanitized.
     * @param   string $group      The group value appended to the $key, pre-sanitized.
     *
     * @return  string
     */
    public function build_key( $key, $group = 'default' ) {
        if ( empty( $group ) ) {
            $group = 'default';
        }

        $san_key = $this->sanitize_key_part( $key );
        $san_group = $this->sanitize_key_part( $group );

        return $this->fast_build_key($san_key, $san_group);
    }

    /**
     * Builds a key for the cached object using the prefix, group and key.
     *
     * @param   string $key        The key under which to store the value, pre-sanitized.
     * @param   string $group      The group value appended to the $key, pre-sanitized.
     *
     * @return  string
     */
    public function fast_build_key( $key, $group = 'default' ) {
        if ( empty( $group ) ) {
            $group = 'default';
        }

        $salt = defined( 'WP_REDIS_PREFIX' ) ? trim( WP_REDIS_PREFIX ) : '';

        $prefix = $this->is_global_group( $group ) ? $this->global_prefix : $this->blog_prefix;
        $prefix = trim( $prefix, '_-:$' );

        return "{$salt}{$prefix}:{$group}:{$key}";
    }

    /**
     * Replaces the set group separator by another one
     *
     * @param   string $part  The string to sanitize.
     * @return  string        Sanitized string.
     */
    protected function sanitize_key_part( $part ) {
        return str_replace( ':', '-', $part );
    }

    /**
     * Checks if the given group is part the ignored group array
     *
     * @param string $group  Name of the group to check, pre-sanitized.
     * @return bool
     */
    protected function is_ignored_group( $group ) {
        return $this->is_group_of_type( $group, 'ignored' );
    }

    /**
     * Checks if the given group is part the global group array
     *
     * @param string $group  Name of the group to check, pre-sanitized.
     * @return bool
     */
    protected function is_global_group( $group ) {
        return $this->is_group_of_type( $group, 'global' );
    }

    /**
     * Checks if the given group is part the unflushable group array
     *
     * @param string $group  Name of the group to check, pre-sanitized.
     * @return bool
     */
    protected function is_unflushable_group( $group ) {
        return $this->is_group_of_type( $group, 'unflushable' );
    }

    /**
     * Checks the type of the given group
     *
     * @param string $group  Name of the group to check, pre-sanitized.
     * @param string $type   Type of the group to check.
     * @return bool
     */
    private function is_group_of_type( $group, $type ) {
        return isset( $this->group_type[ $group ] )
            && $this->group_type[ $group ] == $type;
    }

    /**
     * Convert Redis responses into something meaningful
     *
     * @param mixed $response Response sent from the redis instance.
     * @return mixed
     */
    protected function parse_redis_response( $response ) {
        if ( is_bool( $response ) ) {
            return $response;
        }

        if ( is_numeric( $response ) ) {
            return $response;
        }

        if ( is_object( $response ) && method_exists( $response, 'getPayload' ) ) {
            return $response->getPayload() === 'OK';
        }

        return false;
    }

    /**
     * Simple wrapper for saving object to the internal cache.
     *
     * @param   string $derived_key    Key to save value under.
     * @param   mixed  $value          Object value.
     */
    public function add_to_internal_cache( $derived_key, $value ) {
        if ( is_object( $value ) ) {
            $value = clone $value;
        }

        $this->cache[ $derived_key ] = $value;
    }

    /**
     * Get a value specifically from the internal, run-time cache, not Redis.
     *
     * @param   int|string $derived_key Key value.
     *
     * @return  bool|mixed              Value on success; false on failure.
     */
    public function get_from_internal_cache( $derived_key ) {
        if ( ! array_key_exists( $derived_key, $this->cache ) ) {
            return false;
        }

        if ( is_object( $this->cache[ $derived_key ] ) ) {
            return clone $this->cache[ $derived_key ];
        }

        return $this->cache[ $derived_key ];
    }

    /**
     * In multisite, switch blog prefix when switching blogs
     *
     * @param int $_blog_id Blog ID.
     * @return bool
     */
    public function switch_to_blog( $_blog_id ) {
        if ( ! function_exists( 'is_multisite' ) || ! is_multisite() ) {
            return false;
        }

        $this->blog_prefix = (int) $_blog_id;

        return true;
    }

    /**
     * Sets the list of global groups.
     *
     * @param array $groups List of groups that are global.
     */
    public function add_global_groups( $groups ) {
        $groups = (array) $groups;

        if ( $this->redis_status() ) {
            $this->global_groups = array_unique( array_merge( $this->global_groups, $groups ) );
        } else {
            $this->ignored_groups = array_unique( array_merge( $this->ignored_groups, $groups ) );
        }

        $this->cache_group_types();
    }

    /**
     * Sets the list of groups not to be cached by Redis.
     *
     * @param array $groups  List of groups that are to be ignored.
     */
    public function add_non_persistent_groups( $groups ) {
        /**
         * Filters list of groups to be added to {@see self::$ignored_groups}
         *
         * @since 2.1.7
         * @param string[] $groups List of groups to be ignored.
         */
        $groups = apply_filters( 'redis_cache_add_non_persistent_groups', (array) $groups );

        $this->ignored_groups = array_unique( array_merge( $this->ignored_groups, $groups ) );
        $this->cache_group_types();
    }

    /**
     * Sets the list of groups not to flushed cached.
     *
     * @param array $groups List of groups that are unflushable.
     */
    public function add_unflushable_groups( $groups ) {
        $groups = (array) $groups;

        $this->unflushable_groups = array_unique( array_merge( $this->unflushable_groups, $groups ) );
        $this->cache_group_types();
    }

    /**
     * Wrapper to validate the cache keys expiration value
     *
     * @param mixed $expiration  Incoming expiration value (whatever it is).
     */
    protected function validate_expiration( $expiration ) {
        $expiration = is_int( $expiration ) || ctype_digit( (string) $expiration ) ? (int) $expiration : 0;

        if ( defined( 'WP_REDIS_MAXTTL' ) ) {
            $max = (int) WP_REDIS_MAXTTL;

            if ( $expiration === 0 || $expiration > $max ) {
                $expiration = $max;
            }
        }

        return $expiration;
    }

    /**
     * Unserialize value only if it was serialized.
     *
     * @param string $original  Maybe unserialized original, if is needed.
     * @return mixed            Unserialized data can be any type.
     */
    protected function maybe_unserialize( $original ) {
        if ( defined( 'WP_REDIS_SERIALIZER' ) && ! empty( WP_REDIS_SERIALIZER ) ) {
            return $original;
        }

        if ( defined( 'WP_REDIS_IGBINARY' ) && WP_REDIS_IGBINARY && function_exists( 'igbinary_unserialize' ) ) {
            return igbinary_unserialize( $original );
        }

        // Don't attempt to unserialize data that wasn't serialized going in.
        if ( $this->is_serialized( $original ) ) {
            // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
            $value = @unserialize( $original );

            return is_object( $value ) ? clone $value : $value;
        }

        return $original;
    }

    /**
     * Serialize data, if needed.
     *
     * @param mixed $data  Data that might be serialized.
     * @return mixed       A scalar data
     */
    protected function maybe_serialize( $data ) {
        if ( is_object( $data ) ) {
            $data = clone $data;
        }

        if ( defined( 'WP_REDIS_SERIALIZER' ) && ! empty( WP_REDIS_SERIALIZER ) ) {
            return $data;
        }

        if ( defined( 'WP_REDIS_IGBINARY' ) && WP_REDIS_IGBINARY && function_exists( 'igbinary_serialize' ) ) {
            return igbinary_serialize( $data );
        }

        if ( is_array( $data ) || is_object( $data ) ) {
            // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
            return serialize( $data );
        }

        if ( $this->is_serialized( $data, false ) ) {
            // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
            return serialize( $data );
        }

        return $data;
    }

    /**
     * Check value to find if it was serialized.
     *
     * If $data is not an string, then returned value will always be false.
     * Serialized data is always a string.
     *
     * @param string $data    Value to check to see if was serialized.
     * @param bool   $strict  Optional. Whether to be strict about the end of the string. Default true.
     * @return bool           False if not serialized and true if it was.
     */
    protected function is_serialized( $data, $strict = true ) {
        // if it isn't a string, it isn't serialized.
        if ( ! is_string( $data ) ) {
            return false;
        }

        $data = trim( $data );

        if ( 'N;' === $data ) {
            return true;
        }

        if ( strlen( $data ) < 4 ) {
            return false;
        }

        if ( ':' !== $data[1] ) {
            return false;
        }

        if ( $strict ) {
            $lastc = substr( $data, -1 );

            if ( ';' !== $lastc && '}' !== $lastc ) {
                return false;
            }
        } else {
            $semicolon = strpos( $data, ';' );
            $brace = strpos( $data, '}' );

            // Either ; or } must exist.
            if ( false === $semicolon && false === $brace ) {
                return false;
            }

            // But neither must be in the first X characters.
            if ( false !== $semicolon && $semicolon < 3 ) {
                return false;
            }

            if ( false !== $brace && $brace < 4 ) {
                return false;
            }
        }
        $token = $data[0];

        switch ( $token ) {
            case 's':
                if ( $strict ) {
                    if ( '"' !== substr( $data, -2, 1 ) ) {
                        return false;
                    }
                } elseif ( false === strpos( $data, '"' ) ) {
                    return false;
                }
                // Or else fall through.
                // No break!
            case 'a':
            case 'O':
                return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';

                return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
        }

        return false;
    }

    /**
     * Handle the redis failure gracefully or throw an exception.
     *
     * @param \Exception $exception  Exception thrown.
     * @throws \Exception If `fail_gracefully` flag is set to a falsy value.
     * @return void
     */
    protected function handle_exception( $exception ) {
        $this->redis_connected = false;

        // When Redis is unavailable, fall back to the internal cache by forcing all groups to be "no redis" groups.
        $this->ignored_groups = array_unique( array_merge( $this->ignored_groups, $this->global_groups ) );

        if ( ! $this->fail_gracefully ) {
            throw $exception;
        }

        $this->errors[] = $exception->getMessage();

        error_log( $exception ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

        if ( function_exists( 'do_action' ) ) {
            /**
             * Fires on every cache error
             *
             * @since 1.5.0
             * @param \Exception $exception The exception triggered.
             */
            do_action( 'redis_object_cache_error', $exception );
        }
    }

    /**
     * Builds a clean connection array out of redis clusters array.
     *
     * @return  array
     */
    protected function build_cluster_connection_array() {
        $cluster = array_values( WP_REDIS_CLUSTER );

        foreach ( $cluster as $key => $server ) {
            $connection_string = parse_url( $server );

            $cluster[ $key ] = sprintf(
                "%s:%s",
                $connection_string['host'],
                $connection_string['port']
            );
        }

        return $cluster;
    }

    /**
     * Check whether Predis client is in use.
     *
     * @return bool
     */
    protected function is_predis() {
        return $this->redis instanceof Predis\Client;
    }

    /**
     * Allows access to private properties for backwards compatibility.
     *
     * @param string $name Name of the property.
     * @return mixed
     */
    public function __get( $name ) {
        return isset( $this->{$name} ) ? $this->{$name} : null;
    }
}

endif;
// phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact, Generic.WhiteSpace.ScopeIndent.Incorrect
