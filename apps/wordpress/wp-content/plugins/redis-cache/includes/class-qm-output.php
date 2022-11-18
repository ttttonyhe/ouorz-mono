<?php
/**
 * Query Monitor output logic class
 *
 * @package Rhubarb\RedisCache
 */

namespace Rhubarb\RedisCache;

use QM_Output_Html;

defined( '\\ABSPATH' ) || exit;

/**
 * Query Monitor output logic class definition
 */
class QM_Output extends QM_Output_Html {

    /**
     * Constructor
     *
     * @param QM_Collector $collector The corresponding collector instance.
     */
    public function __construct( QM_Collector $collector ) {
        parent::__construct( $collector );

        add_filter( 'qm/output/menus', [ $this, 'admin_menu' ], 30 );
        add_filter( 'qm/output/panel_menus', [ $this, 'panel_menu' ] );
    }

    /**
     * Output class name
     *
     * @return string
     */
    public function name() {
        return __( 'Object Cache', 'redis cache' );
    }

    /**
     * Adds a menu to the panel navigation menu in Query Monitor's output
     *
     * @param array $menu Array of menus.
     */
    public function admin_menu( array $menu ) {
        $data = $this->collector->get_data();

        $title = $data['ratio']
            ? sprintf( '%s (%s%%)', $this->name(), $data['ratio'] )
            : $this->name();

        $args = [
            'title' => esc_html( $title ),
        ];

        if ( empty( $data['status'] ) ) {
            $args['meta']['classname'] = 'qm-alert';
        }

        if ( ! empty( $data['errors'] ) ) {
            $args['meta']['classname'] = 'qm-warning';
        }

        $menu[ $this->collector->id() ] = $this->menu( $args );

        return $menu;
    }

    /**
     * Adds a menu item in the panel navigation menu in Query Monitor's output.
     *
     * @param array $menu Array of menus.
     */
    public function panel_menu( array $menu ) {
        $ids = array_keys( $menu );
        $request = array_search( 'qm-request', $ids, true );
        $position = false === $request ? count( $menu ) : $request;

        $item = [
            $this->collector->id() => $this->menu( [ 'title' => $this->name() ] ),
        ];

        return array_merge(
            array_slice( $menu, 0, $position ),
            $item,
            array_slice( $menu, $position )
        );
    }

    /**
     * Renders the output
     *
     * @return void
     */
    public function output() {
        $data = $this->collector->get_data();

        if ( ! $data['has_dropin'] ) {
            $this->before_non_tabular_output();
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $this->build_notice(
                esc_html__( 'The Redis Object Cache drop-in is not installed. Use WP CLI or go to "Settings -> Redis" to enable drop-in.', 'redis-cache' )
            );
            $this->after_non_tabular_output();

            return;
        }

        if ( ! $data['valid_dropin'] ) {
            $this->before_non_tabular_output();
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $this->build_notice(
                esc_html__( 'WordPress is using a foreign object cache drop-in and Redis Object Cache is not being used. Use WP CLI or go to "Settings -> Redis" to enable drop-in.', 'redis-cache' )
            );
            $this->after_non_tabular_output();

            return;
        }

        require_once WP_REDIS_PLUGIN_PATH . '/includes/ui/query-monitor.php';
    }
}
