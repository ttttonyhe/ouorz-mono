<?php
/**
 * Metrics collection
 *
 * @package Rhubarb\RedisCache
 */

namespace Rhubarb\RedisCache;

use Exception;

defined( '\\ABSPATH' ) || exit;

/**
 * Metrics collection class
 */
class Metrics {

    /**
     * Unique identifier
     *
     * @var string
     */
    public $id;

    /**
     * Cache hits
     *
     * @var int
     */
    public $hits;

    /**
     * Cache misses
     *
     * @var int
     */
    public $misses;

    /**
     * Cache ratio
     *
     * @var float
     */
    public $ratio;

    /**
     * Bytes retrieves
     *
     * @var int
     */
    public $bytes;

    /**
     * Cache needed time
     *
     * @var float
     */
    public $time;

    /**
     * Cache calls
     *
     * @var int
     */
    public $calls;

    /**
     * Metrics timestamp
     *
     * @var int
     */
    public $timestamp;

    /**
     * Initializes the metrics collection
     *
     * @return void
     */
    public static function init() {
        if ( ! self::is_enabled() ) {
            return;
        }

        add_action( 'shutdown', [ self::class, 'record' ] );
        add_action( 'rediscache_discard_metrics', [ self::class, 'discard' ] );
    }

    /**
     * Checks if the collection of metrics is enabled.
     *
     * @return bool
     */
    public static function is_enabled() {
        return ! defined( 'WP_REDIS_DISABLE_METRICS' )
            || ! WP_REDIS_DISABLE_METRICS;
    }

    /**
     * Checks if metrics can be recorded.
     *
     * @return bool
     */
    public static function is_active() {
        global $wp_object_cache;

        return self::is_enabled()
            && Plugin::instance()->get_redis_status()
            && method_exists( $wp_object_cache, 'info' )
            && method_exists( $wp_object_cache, 'redis_instance' );
    }

    /**
     * Retrieves metrics max time
     *
     * @return int
     */
    public static function max_time() {
        if ( defined( 'WP_REDIS_METRICS_MAX_TIME' ) ) {
            return (int) WP_REDIS_METRICS_MAX_TIME;
        }

        return HOUR_IN_SECONDS;
    }

    /**
     * Records metrics and adds them to redis
     *
     * @return void
     */
    public static function record() {
        if ( ! self::is_active() ) {
            return;
        }

        $metrics = new self();
        $metrics->collect();
        $metrics->save();
    }

    /**
     * Collect metrics from object cache instance.
     */
    public function collect() {
        global $wp_object_cache;

        $info = $wp_object_cache->info();

        $this->id = substr( md5( uniqid( strval( mt_rand() ), true ) ), 12 );
        $this->hits = $info->hits;
        $this->misses = $info->misses;
        $this->ratio = $info->ratio;
        $this->bytes = $info->bytes;
        $this->time = round( $info->time, 5 );
        $this->calls = $info->calls;
        $this->timestamp = time();
    }

    /**
     * Retrieves metrics from redis
     *
     * @param int $seconds Number of seconds of the oldest entry to retrieve.
     * @return Metrics[]
     */
    public static function get( $seconds = null ) {
        global $wp_object_cache;

        if ( ! self::is_active() ) {
            return [];
        }

        if ( null === $seconds ) {
            $seconds = self::max_time();
        }

        try {
            $raw_metrics = $wp_object_cache->redis_instance()->zrangebyscore(
                $wp_object_cache->build_key( 'metrics', 'redis-cache' ),
                time() - $seconds,
                time() - MINUTE_IN_SECONDS,
                [ 'withscores' => true ]
            );
        } catch ( Exception $exception ) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log( $exception );

            return [];
        }

        $metrics = [];
        $prefix = sprintf( 'O:%d:"%s', strlen( self::class ), self::class );

        foreach ( $raw_metrics as $serialized => $timestamp ) {
            // Compatibility: Ignore all non serialized entries as they were used by prior versions.
            if ( strpos( $serialized, $prefix ) !== 0 ) {
                continue;
            }

            // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
            $metrics[] = unserialize( $serialized );
        }

        return $metrics;
    }

    /**
     * Saves the current metrics to redis
     *
     * @return void
     */
    public function save() {
        global $wp_object_cache;

        try {
            $wp_object_cache->redis_instance()->zadd(
                $wp_object_cache->build_key( 'metrics', 'redis-cache' ),
                $this->timestamp,
                // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
                serialize( $this )
            );
        } catch ( Exception $exception ) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log( $exception );
        }
    }

    /**
     * Removes recorded metrics after an hour
     *
     * @return void
     */
    public static function discard() {
        global $wp_object_cache;

        if ( ! self::is_active() ) {
            return;
        }

        try {
            $wp_object_cache->redis_instance()->zremrangebyscore(
                $wp_object_cache->build_key( 'metrics', 'redis-cache' ),
                0,
                time() - self::max_time()
            );
        } catch ( Exception $exception ) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log( $exception );
        }
    }

    /**
     * Counts the recorded metrics
     *
     * @return int
     */
    public static function count() {
        global $wp_object_cache;

        if ( ! self::is_active() ) {
            return 0;
        }

        try {
            return $wp_object_cache->redis_instance()->zcount(
                $wp_object_cache->build_key( 'metrics', 'redis-cache' ), '-inf', '+inf'
            );
        } catch ( Exception $exception ) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log( $exception );
            return 0;
        }
    }

}
