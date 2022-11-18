<?php
/**
 * Admin settings page template
 *
 * @package Rhubarb\RedisCache
 */

namespace Rhubarb\RedisCache\UI;

use Rhubarb\RedisCache\UI;

defined( '\\ABSPATH' ) || exit;

?>
<div id="rediscache" class="wrap">

    <h1>
        <?php esc_html_e( 'Redis Object Cache', 'redis-cache' ); ?>
    </h1>

    <?php is_network_admin() && settings_errors(); ?>

    <div class="columns">

        <div class="content-column">

            <h2 class="nav-tab-wrapper">
                <?php foreach ( UI::get_tabs() as $ui_tab ) : ?>
                    <?php if ( $ui_tab->is_disabled() ) : ?>

                        <span
                            class="<?php echo esc_attr( $ui_tab->nav_classes() ); ?>"
                            title="<?php echo esc_attr( $ui_tab->disabled_notice() ); ?>"
                        >
                            <?php echo esc_html( $ui_tab->label() ); ?>
                        </span>

                    <?php else : ?>

                        <a
                            id="<?php echo esc_attr( $ui_tab->nav_id() ); ?>"
                            class="<?php echo esc_attr( $ui_tab->nav_classes() ); ?>"
                            data-toggle="<?php echo esc_attr( $ui_tab->slug() ); ?>"
                            href="#<?php echo esc_attr( $ui_tab->slug() ); ?>"
                        >
                            <?php echo esc_html( $ui_tab->label() ); ?>
                        </a>

                    <?php endif; ?>
                <?php endforeach; ?>
            </h2>

            <div class="tab-content">
                <?php foreach ( UI::get_tabs() as $ui_tab ) : ?>
                    <?php if ( ! $ui_tab->is_disabled() ) : ?>
                        <div id="<?php echo esc_attr( $ui_tab->id() ); ?>"
                            class="<?php echo esc_attr( $ui_tab->classes() ); ?>"
                        >
                            <?php $ui_tab->display(); ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

        </div>

        <div class="sidebar-column">

            <h6>
                <?php esc_html_e( 'Resources', 'redis-cache' ); ?>
            </h6>

            <div class="section-pro">

                <div class="card">
                    <h2 class="title" style="line-height: 1.4">
                        Need more performance and reliability?<br>
                        Check out <span style="color: #dc2626;">Object Cache Pro</span>!
                    </h2>
                    <p>
                        <?php wp_kses_post( __( '<strong>A business class object cache backend.</strong> Truly reliable, highly-optimized and fully customizable, with a <u>dedicated engineer</u> when you most need it.', 'redis-cache' ) ); ?>
                    </p>
                    <ul>
                        <li><?php esc_html_e( 'Rewritten for raw performance', 'redis-cache' ); ?></li>
                        <li><?php esc_html_e( '100% WordPress API compliant', 'redis-cache' ); ?></li>
                        <li><?php esc_html_e( 'Faster serialization and compression', 'redis-cache' ); ?></li>
                        <li><?php esc_html_e( 'Easy debugging & logging', 'redis-cache' ); ?></li>
                        <li><?php esc_html_e( 'Cache prefetching and analytics', 'redis-cache' ); ?></li>
                        <li><?php esc_html_e( 'Fully unit tested (100% code coverage)', 'redis-cache' ); ?></li>
                        <li><?php esc_html_e( 'Secure connections with TLS', 'redis-cache' ); ?></li>
                        <li><?php esc_html_e( 'Health checks via WordPress & WP CLI', 'redis-cache' ); ?></li>
                        <li><?php esc_html_e( 'Optimized for WooCommerce, Jetpack & Yoast SEO', 'redis-cache' ); ?></li>
                    </ul>
                    <p>
                        <a class="button button-primary" target="_blank" rel="noopener" href="https://objectcache.pro/?ref=oss&amp;utm_source=wp-plugin&amp;utm_medium=settings">
                            <?php esc_html_e( 'Learn more', 'redis-cache' ); ?>
                        </a>
                    </p>
                </div>

                <?php $is_php7 = version_compare( phpversion(), '7.2', '>=' ); ?>
                <?php $is_phpredis311 = version_compare( phpversion( 'redis' ), '3.1.1', '>=' ); ?>
                <?php $phpredis_installed = (bool) phpversion( 'redis' ); ?>

                <?php if ( $is_php7 && $is_phpredis311 ) : ?>

                    <p class="compatibility">
                        <span class="dashicons dashicons-yes"></span>
                        <span><?php esc_html_e( 'Your site meets the system requirements for the Pro version.', 'redis-cache' ); ?></span>
                    </p>

                <?php else : ?>

                    <p class="compatibility">
                        <span class="dashicons dashicons-no"></span>
                        <span><?php echo wp_kses_post( __( 'Your site <i>does not</i> meet the requirements for the Pro version:', 'redis-cache' ) ); ?></span>
                    </p>

                    <ul>
                        <?php if ( ! $is_php7 ) : ?>
                            <li>
                                <?php
                                    printf(
                                        // translators: %s = PHP Version.
                                        esc_html__( 'The current version of PHP (%s) is too old. PHP 7.2 or newer is required.', 'redis-cache' ),
                                        esc_html( phpversion() )
                                    );
                                ?>
                            </li>
                        <?php endif; ?>

                        <?php if ( ! $phpredis_installed ) : ?>
                            <li>
                                <?php esc_html_e( 'The PhpRedis extension is not installed.', 'redis-cache' ); ?>
                            </li>
                        <?php elseif ( ! $is_phpredis311 ) : ?>
                            <li>
                                <?php
                                    printf(
                                        // translators: %s = Version of the PhpRedis extension.
                                        esc_html__( 'The current version of the PhpRedis extension (%s) is too old. PhpRedis 3.1.1 or newer is required.', 'redis-cache' ),
                                        esc_html( phpversion( 'redis' ) )
                                    );
                                ?>
                            </li>
                        <?php endif; ?>
                    </ul>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>
