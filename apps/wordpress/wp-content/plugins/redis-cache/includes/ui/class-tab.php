<?php
/**
 * Tab utility class
 *
 * @package Rhubarb\RedisCache
 */

namespace Rhubarb\RedisCache\UI;

use Rhubarb\RedisCache\Plugin;

defined( '\\ABSPATH' ) || exit;

/**
 * Tab class definition
 */
class Tab {

    /**
     * Tab slug
     *
     * @var string $slug
     */
    protected $slug = '';

    /**
     * Tab label
     *
     * @var string $label
     */
    protected $label = '';

    /**
     * Tab template file
     *
     * @var string $file
     */
    protected $file = '';

    /**
     * Tab default state
     *
     * @var bool $default
     */
    protected $default = false;

    /**
     * Tab disabled state
     *
     * @var bool $disabled
     */
    protected $disabled = false;

    /**
     * Tab custom data
     *
     * @var array $custom
     */
    protected $custom = [];

    /**
     * Constructor
     *
     * @param string $slug  Slug to identify the tab.
     * @param string $label Tab label.
     * @param array  $args  Optional arguments describing the tab.
     */
    public function __construct( $slug, $label, $args = [] ) {
        $args = wp_parse_args(
            $args,
            [
                'slug' => $slug,
                'label' => $label,
                'file' => WP_REDIS_PLUGIN_PATH . "/includes/ui/tabs/{$slug}.php",
            ]
        );

        foreach ( $args ?: [] as $property => $value ) {
            if ( property_exists( $this, $property ) ) {
                $this->{$property} = $value;
            } else {
                $this->custom[ $property ] = $value;
            }
        }
    }

    /**
     * Getter for tab slug
     *
     * @return string
     */
    public function slug() {
        return $this->slug;
    }

    /**
     * Getter for tab label
     *
     * @return string
     */
    public function label() {
        return $this->label;
    }

    /**
     * Getter for tab file
     *
     * @return string
     */
    public function file() {
        return $this->file;
    }

    /**
     * Getter for tab disabled state
     *
     * @return bool
     */
    public function is_disabled() {
        return $this->disabled;
    }

    /**
     * Getter for tab default state
     *
     * @return bool
     */
    public function is_default() {
        return $this->default;
    }

    /**
     * Getter for tab custom data
     *
     * @param string $key Custom data key.
     * @return mixed
     */
    public function custom( $key ) {
        if ( ! isset( $this->custom[ $key ] ) ) {
            return null;
        }

        return $this->custom[ $key ];
    }

    /**
     * Disabled notice for tab
     *
     * @return string
     */
    public function disabled_notice() {
        return sprintf(
            // translators: %s = Tab label.
            __( '%s are disabled for this site.', 'redis-cache' ),
            $this->label
        );
    }

    /**
     * Displays the tab template
     *
     * @return void
     */
    public function display() {
        $roc = Plugin::instance();

        include $this->file;
    }

    /**
     * Returns the tab nav id attribute
     *
     * @return string
     */
    public function nav_id() {
        $nav_id = "{$this->slug}-tab";

        /**
         * Filters the tab's nav id
         *
         * @since 2.0.12
         * @param string $nav_id   The id attribute of the current tab's nav element.
         * @param Tab    $instance The current tab.
         */
        return apply_filters( 'roc_tab_nav_id', $nav_id, $this );
    }

    /**
     * Returns the tab nav css classes
     *
     * @return string
     */
    public function nav_classes() {
        $classes = [
            'nav-tab',
        ];

        if ( $this->default ) {
            $classes[] = 'nav-tab-active';
        }

        if ( $this->disabled ) {
            $classes[] = 'nav-tab-disabled';
        }

        /**
         * Filters the current tab's nav element css classes
         *
         * @since 2.0.12
         * @param array $classes  Array of css classes.
         * @param Tab   $instance The current tab.
         */
        return implode( ' ', apply_filters( 'roc_tab_nav_classes', $classes, $this ) );
    }

    /**
     * Returns the tab id attribute
     *
     * @return string
     */
    public function id() {
        $tab_id = "{$this->slug}-pane";

        /**
         * Filters the tab's id
         *
         * @since 2.0.12
         * @param string $tab_id   The id attribute of the current tab element.
         * @param Tab    $instance The current tab.
         */
        return apply_filters( 'roc_tab_id', $tab_id, $this );
    }

    /**
     * Returns the tab css classes
     *
     * @return string
     */
    public function classes() {
        $classes = [
            'tab-pane',
            "tab-pane-{$this->slug}",
        ];

        if ( $this->default ) {
            $classes[] = 'active';
        }

        /**
         * Filters the current tab's css classes
         *
         * @since 2.0.12
         * @param array $classes  Array of css classes.
         * @param Tab   $instance The current tab.
         */
        return implode( ' ', apply_filters( 'roc_tab_classes', $classes, $this ) );
    }

}
