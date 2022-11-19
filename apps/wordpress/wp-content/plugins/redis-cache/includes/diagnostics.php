<?php
/**
 * Diagnostics template file
 *
 * @package Rhubarb\RedisCache
 */

defined( '\\ABSPATH' ) || exit;

global $wp_object_cache;

/** @var \Rhubarb\RedisCache\Plugin $roc */
$info = [];
$filesystem = $roc->test_filesystem_writing();
$dropin = $roc->validate_object_cache_dropin();
$disabled = defined( 'WP_REDIS_DISABLED' ) && WP_REDIS_DISABLED;

$info['Status'] = $roc->get_status();
$info['Client'] = $roc->get_redis_client_name();

$info['Drop-in'] = $roc->object_cache_dropin_exists()
    ? ( $dropin ? 'Valid' : 'Invalid' )
    : 'Not installed';

$info['Disabled'] = $disabled ? 'Yes' : 'No';

if ( $dropin && ! $disabled ) {
    $info[ 'Ping' ] = $wp_object_cache->diagnostics['ping'] ?? false;

    try {
        $cache = new WP_Object_Cache( false );
    } catch ( Exception $exception ) {
        $info[ 'Connection Exception' ] = sprintf( '%s (%s)', $exception->getMessage(), get_class( $exception ) );
    }

    $info[ 'Errors' ] = wp_json_encode(
        array_values( $wp_object_cache->errors ),
        JSON_PRETTY_PRINT
    );
}

$info['PhpRedis'] = class_exists( 'Redis' ) ? phpversion( 'redis' ) : 'Not loaded';
$info['Relay'] = class_exists( 'Relay\Relay' ) ? phpversion( 'relay' ) : 'Not loaded';
$info['Predis'] = class_exists( 'Predis\Client' ) ? Predis\Client::VERSION : 'Not loaded';
$info['Credis'] = class_exists( 'Credis_Client' ) ? Credis_Client::VERSION : 'Not loaded';

if ( defined( 'PHP_VERSION' ) ) {
    $info['PHP Version'] = PHP_VERSION;
}

if ( defined( 'HHVM_VERSION' ) ) {
    $info['HHVM Version'] = HHVM_VERSION;
}

$info['Plugin Version'] = WP_REDIS_VERSION;
$info['Redis Version'] = $roc->get_redis_version() ?: 'Unknown';

$info['Multisite'] = is_multisite() ? 'Yes' : 'No';

$info['Metrics'] = \Rhubarb\RedisCache\Metrics::is_active() ? 'Enabled' : 'Disabled';
$info['Metrics recorded'] = wp_json_encode( \Rhubarb\RedisCache\Metrics::count() );

$info['Filesystem'] = is_wp_error( $filesystem ) ? $filesystem->get_error_message() : 'Working';

if ( $dropin && ! $disabled ) {
    $info['Global Prefix'] = wp_json_encode( $wp_object_cache->global_prefix );
    $info['Blog Prefix'] = wp_json_encode( $wp_object_cache->blog_prefix );
}

$constants = [
    'WP_REDIS_DISABLED',
    'WP_REDIS_CLIENT',
    'WP_REDIS_SCHEME',
    'WP_REDIS_PATH',
    'WP_REDIS_HOST',
    'WP_REDIS_PORT',
    'WP_REDIS_DATABASE',
    'WP_REDIS_TIMEOUT',
    'WP_REDIS_READ_TIMEOUT',
    'WP_REDIS_RETRY_INTERVAL',
    'WP_REDIS_SERVERS',
    'WP_REDIS_CLUSTER',
    'WP_REDIS_SHARDS',
    'WP_REDIS_SENTINEL',
    'WP_REDIS_IGBINARY',
    'WP_REDIS_SERIALIZER',
    'WP_REDIS_MAXTTL',
    'WP_REDIS_PREFIX',
    'WP_CACHE_KEY_SALT',
    'WP_REDIS_GLOBAL_GROUPS',
    'WP_REDIS_IGNORED_GROUPS',
    'WP_REDIS_UNFLUSHABLE_GROUPS',
    'WP_REDIS_METRICS_MAX_TIME',
];

foreach ( $constants as $constant ) {
    if ( defined( $constant ) ) {
        $info[ $constant ] = wp_json_encode(
            constant( $constant ),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
    }
}

if ( defined( 'WP_REDIS_PASSWORD' ) ) {
    $password = WP_REDIS_PASSWORD;

    if ( is_array( $password ) ) {
        if ( isset( $password[1] ) && ! is_null( $password[1] ) && '' !== $password[1] ) {
            $password[1] = str_repeat( '•', 8 );
        }

        $info['WP_REDIS_PASSWORD'] = wp_json_encode( $password, JSON_UNESCAPED_UNICODE );
    } elseif ( ! is_null( $password ) && '' !== $password ) {
        $info['WP_REDIS_PASSWORD'] = str_repeat( '•', 8 );
    }
}

if ( $dropin && ! $disabled ) {
    $info['Global Groups'] = wp_json_encode(
        array_values( $wp_object_cache->global_groups ?? [] ),
        JSON_PRETTY_PRINT
    );

    $info['Ignored Groups'] = wp_json_encode(
        array_values( $wp_object_cache->ignored_groups ?? [] ),
        JSON_PRETTY_PRINT
    );

    $info['Unflushable Groups'] = wp_json_encode(
        array_values( $wp_object_cache->unflushable_groups ?? [] ),
        JSON_PRETTY_PRINT
    );

    $info['Groups Types'] = wp_json_encode(
        $wp_object_cache->group_type ?? null,
        JSON_PRETTY_PRINT
    );
}

$dropins = [];

foreach ( get_dropins() as $file => $details ) {
    $dropins[ $file ] = sprintf( '%s v%s by %s', $details['Name'], $details['Version'], $details['Author'] );
}

$info['Drop-ins'] = wp_json_encode(
    array_values( $dropins ),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
);

foreach ( $info as $name => $value ) {
    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        WP_CLI::line( "{$name}: $value" );
    } else {
        echo esc_html( "{$name}: {$value}\r\n" );
    }
}
