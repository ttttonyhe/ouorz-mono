<?php
/**
 * Autoloader class adhering to WordPress naming scheme.
 *
 * @package Rhubarb\RedisCache
 */

namespace Rhubarb\RedisCache;

defined( '\\ABSPATH' ) || exit;

/**
 * Autoloader class
 */
class Autoloader {
    /**
     * An associative array where the key is a namespace prefix and the value
     * is an array of base directories for classes in that namespace.
     *
     * @var array
     */
    private $prefixes = [];

    /**
     * Prefix to the class files to adhere to WordPress coding guidelines.
     *
     * @var string
     */
    private $class_file_prefix = 'class-';

    /**
     * Register loader with SPL autoloader stack.
     *
     * @since  2.0.0
     * @return void
     */
    public function register() {
        spl_autoload_register( [ $this, 'load_class' ] );
    }

    /**
     * Adds a base directory for a namespace prefix.
     *
     * @since  2.0.0
     * @param  string $prefix   The namespace prefix.
     * @param  string $base_dir A base directory for class files in the
     *                          namespace.
     * @param  bool   $prepend  If true, prepend the base directory to the stack
     *                          instead of appending it; this causes it to be
     *                          searched first rather than last.
     * @return void
     */
    public function add_namespace( $prefix, $base_dir, $prepend = false ) {
        $prefix = trim( $prefix, '\\' ) . '\\';

        $base_dir = rtrim( $base_dir, \DIRECTORY_SEPARATOR ) . '/';

        if ( false === isset( $this->prefixes[ $prefix ] ) ) {
            $this->prefixes[ $prefix ] = [];
        }

        if ( $prepend ) {
            array_unshift( $this->prefixes[ $prefix ], $base_dir );
        } else {
            array_push( $this->prefixes[ $prefix ], $base_dir );
        }
    }

    /**
     * Loads the class file for a given class name.
     *
     * @since  2.0.0
     * @param  string $class The fully-qualified class name.
     * @return string|null   The mapped file name on success, or null on failure.
     */
    public function load_class( $class ) {
        $prefix = $class;

        while ( false !== ( $pos = strrpos( $prefix, '\\' ) ) ) { // phpcs:ignore
            $prefix = substr( $class, 0, $pos + 1 );

            $relative_class = substr( $class, $pos + 1 );

            $mapped_file = $this->load_mapped_file( $prefix, $relative_class );
            if ( $mapped_file ) {
                return $mapped_file;
            }

            $prefix = rtrim( $prefix, '\\' );
        }

        return null;
    }

    /**
     * Load the mapped file for a namespace prefix and relative class.
     *
     * @since  2.0.0
     * @param  string $prefix         The namespace prefix.
     * @param  string $relative_class The relative class name.
     * @return string|null            Null if no mapped file can be loaded, or
     *                                the name of the loaded mapped file.
     */
    private function load_mapped_file( $prefix, $relative_class ) {
        if ( false === isset( $this->prefixes[ $prefix ] ) ) {
            return null;
        }

        foreach ( $this->prefixes[ $prefix ] as $base_dir ) {
            $relative_class = strtolower( $relative_class );
            $relative_class = strtr( $relative_class, '_', '-' );

            $file = $base_dir
                . str_replace( '\\', '/', $relative_class )
                . '.php';

            if ( $this->class_file_prefix ) {
                $pos      = strrpos( $file, '/' );
                $filename = $this->class_file_prefix . substr( $file, $pos + 1 );
                $file     = substr_replace( $file, $filename, $pos + 1 );
            }

            if ( $this->require_file( $file ) ) {
                return $file;
            }
        }

        return null;
    }

    /**
     * If a file exists, require it from the file system.
     *
     * @since  2.0.0
     * @param  string $file The file to require.
     * @return bool   True if the file exists, false if not.
     */
    private function require_file( $file ) {
        if ( file_exists( $file ) ) {
            require $file;
            return true;
        }
        return false;
    }
}
