<?php
/**
 * Metrics tab template
 *
 * @package Rhubarb\RedisCache
 */

defined( '\\ABSPATH' ) || exit;

?>

<div id="widget-redis-stats" class="card">

    <ul>
        <li>
            <a href="#" class="active" data-chart="time" title="<?php esc_attr_e( 'The total amount of time (in milliseconds) it took Redis to return cache data.', 'redis-cache' ); ?>">
                <?php esc_html_e( 'Time', 'redis-cache' ); ?>
            </a>
        </li>
        <li>
            <a href="#" data-chart="bytes" title="<?php esc_attr_e( 'The total amount of bytes that was retrieved from Redis.', 'redis-cache' ); ?>">
                <?php esc_html_e( 'Bytes', 'redis-cache' ); ?>
            </a>
        </li>
        <li>
            <a href="#" data-chart="ratio" title="<?php esc_attr_e( 'The hit/miss ratio of cache data that was already cached.', 'redis-cache' ); ?>">
                <?php esc_html_e( 'Ratio', 'redis-cache' ); ?>
            </a>
        </li>
        <li>
            <a href="#" data-chart="calls" title="<?php esc_attr_e( 'The total amount of commands sent to Redis.', 'redis-cache' ); ?>">
                <?php esc_html_e( 'Calls', 'redis-cache' ); ?>
            </a>
        </li>
    </ul>

    <div id="redis-stats-chart"></div>

</div>
