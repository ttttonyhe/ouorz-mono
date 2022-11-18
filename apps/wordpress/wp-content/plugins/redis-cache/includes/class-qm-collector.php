<?php
/**
 * Query Monitor data collector
 *
 * @package Rhubarb\RedisCache
 */

namespace Rhubarb\RedisCache;

use QM_Data;
use QM_Data_Cache;
use QM_Collector as QM_BaseCollector;

defined( '\\ABSPATH' ) || exit;

/**
 * Query Monitor data collector class definition
 */
class QM_Collector extends QM_BaseCollector {

    /**
     * Collector id
     *
     * @var string $id
     */
    public $id = 'cache';

    /**
     * Retrieves the collector name
     *
     * @return string
     */
    public function name() {
        return __( 'Object Cache', 'redis-cache' );
    }

    /**
     * The collector storage
     *
     * @return \QM_Data
     */
    public function get_storage(): QM_Data {
        return new QM_Data_Cache;
    }

    /**
     * Fills the collector with data
     *
     * @return void
     */
    public function process() {
        global $wp_object_cache;

        $this->process_defaults();

        $roc = Plugin::instance();

        $this->data['status'] = $roc->get_status();
        $this->data['has_dropin'] = $roc->object_cache_dropin_exists();
        $this->data['valid_dropin'] = $roc->validate_object_cache_dropin();

        if ( ! method_exists( $wp_object_cache, 'info' ) ) {
            return;
        }

        $info = $wp_object_cache->info();

        $this->data['hits'] = $info->hits;
        $this->data['misses'] = $info->misses;
        $this->data['ratio'] = $info->ratio;
        $this->data['bytes'] = $info->bytes;

        $this->data['errors'] = $info->errors;
        $this->data['meta'] = $info->meta;

        if ( defined( 'WP_REDIS_DISABLED' ) && WP_REDIS_DISABLED ) {
            $this->data['meta'][ __( 'Disabled', 'redis-cache' ) ] = __( 'Yes', 'redis-cache' );
        }

        $this->data['groups'] = [
            'global' => $info->groups->global,
            'non_persistent' => $info->groups->non_persistent,
            'unflushable' => $info->groups->unflushable,
        ];

        // These are used by Query Monitor
        $this->data['cache_hit_percentage'] = $info->ratio;

        if ( $this->data instanceof QM_Data_Cache ) {
            $this->data->stats['cache_hits'] = $info->hits;
            $this->data->stats['cache_misses'] = $info->misses;
        } else {
            $this->data['stats']['cache_hits'] = $info->hits;
            $this->data['stats']['cache_misses'] = $info->misses;
        }
    }

    /**
     * Sets collector defaults
     *
     * @return void
     */
    public function process_defaults() {
        $this->data['hits'] = 0;
        $this->data['misses'] = 0;
        $this->data['ratio'] = 0;
        $this->data['bytes'] = 0;

        $this->data['cache_hit_percentage'] = 0;

        $this->data['object_cache_extensions'] = [];
        $this->data['opcode_cache_extensions'] = [];

        if ( function_exists( 'extension_loaded' ) ) {
            $this->data['object_cache_extensions'] = array_map(
                'extension_loaded',
                [
                    'APCu' => 'apcu',
                    'Redis' => 'redis',
                    'Relay' => 'relay',
                    'Memcache' => 'memcache',
                    'Memcached' => 'memcached',
                ]
            );

            $this->data['opcode_cache_extensions'] = array_map(
                'extension_loaded',
                [
                    'APC' => 'APC',
                    'Zend OPcache' => 'Zend OPcache',
                ]
            );
        }

        $this->data['has_object_cache'] = (bool) wp_using_ext_object_cache();
        $this->data['has_opcode_cache'] = array_filter( $this->data['opcode_cache_extensions'] ) ? true : false;

        $this->data['display_hit_rate_warning'] = false;
        $this->data['ext_object_cache'] = $this->data['has_object_cache'];
    }
}
