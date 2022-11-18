<?php
/**
 * UI utility class
 *
 * @package Rhubarb\RedisCache
 */

namespace Rhubarb\RedisCache;

defined( '\\ABSPATH' ) || exit;

/**
 * UI class definition
 */
class UI {

    /**
     * Holds all registered tabs
     *
     * @var array
     */
    private static $tabs = [];

    /**
     * Registers a settings tab
     *
     * @param string $slug   Unique slug to identify the tab.
     * @param string $label  Tab label.
     * @param array  $args   Optional arguments describing the tab.
     * @return void
     */
    public static function register_tab( $slug, $label, $args = [] ) {
        self::$tabs[ $slug ] = new UI\Tab( $slug, $label, $args );
    }

    /**
     * Retrieves all registered tabs
     *
     * @return array
     */
    public static function get_tabs() {
        return self::$tabs;
    }

}
