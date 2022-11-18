<?php
/**
 * $Id: sitemap-wpmu.php 534582 2012-04-21 22:25:36Z arnee $
 *
 * Google XML Sitemaps Generator for WordPress MU activation
 * ==============================================================================
 *
 * If you want to use this plugin with a automatic network-wide activation, copy the "google-sitemaps-generator" directory
 * in wp-content/mu-plugins and copy this file into wp-content/mu-plugins directly:
 *
 * + wp-content/
 * | + mu-plugins/
 * | | - sitemap-wpmu.php
 * | | + google-sitemap-generator/
 * | | | - sitemap.php
 * | | | - [...]
 *
 * All files in the mu-plugins directory are included for all sites by WordPress by default, so there is no need to
 * activate this plugin anymore (and it also can not be deactivated).
 *
 * @package Sitemap
 */

if ( ! defined( 'WPINC' ) ) {
	return;
}

$gsg_file = dirname( __FILE__ ) . '/google-sitemap-generator/sitemap.php';

if ( file_exists( $gsg_file ) ) {
	require_once $gsg_file;
} else {
	// phpcs:disable
	esc_html( trigger_error( 'XML Sitemap Generator was loaded via mu-plugins directory, but the plugin was not found under $gsg_file', E_USER_WARNING ) );
	// phpcs:enable
}
