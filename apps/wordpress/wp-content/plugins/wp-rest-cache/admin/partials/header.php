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

if ( ! isset( $sub, $this->settings_panels ) ) {
	return;
}

?>
<h1>WP REST Cache</h1>
<h2 class="nav-tab-wrapper">
	<?php
	foreach ( $this->settings_panels as $wprc_key => $wprc_panel ) {
		?>
		<a href="<?php echo esc_attr( admin_url( 'options-general.php?page=wp-rest-cache&sub=' . $wprc_key ) ); ?>" id="<?php echo esc_attr( $wprc_key ); ?>"
			class="nav-tab <?php echo $wprc_key === $sub ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $wprc_panel['label'] ); ?></a>
		<?php
	}
	?>
</h2>
