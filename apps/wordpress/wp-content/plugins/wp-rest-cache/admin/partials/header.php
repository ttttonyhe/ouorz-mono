<?php
/**
 * View for the header of the WP REST Cache Settings page.
 *
 * @link: https://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Admin/Partials
 */

if ( ! isset( $sub ) ) {
	return;
}

?>
<h1>WP REST Cache</h1>
<h2 class="nav-tab-wrapper">
	<a href="<?php echo esc_attr( admin_url( 'options-general.php?page=wp-rest-cache&sub=settings' ) ); ?>" id="settings"
		class="nav-tab <?php echo 'settings' === $sub ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Settings', 'wp-rest-cache' ); ?></a>
	<a href="<?php echo esc_attr( admin_url( 'options-general.php?page=wp-rest-cache&sub=endpoint-api' ) ); ?>" id="endpoint-api"
		class="nav-tab <?php echo 'endpoint-api' === $sub ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Endpoint API Caches', 'wp-rest-cache' ); ?></a>
	<a href="<?php echo esc_attr( admin_url( 'options-general.php?page=wp-rest-cache&sub=clear-cache' ) ); ?>" id="clear-cache"
		class="nav-tab <?php echo 'clear-cache' === $sub ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Clear Caches', 'wp-rest-cache' ); ?></a>
</h2>
