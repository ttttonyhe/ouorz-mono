<?php
/**
 * View for the body of the WP REST Cache Settings page.
 *
 * @link: https://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Admin/Partials
 */

if ( ! isset( $wp_rest_cache_list ) ) {
	return;
}

$wp_rest_cache_sub = filter_input( INPUT_GET, 'sub', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
?>
<div id="poststuff">
	<div id="post-body" class="metabox-holder">
		<div class="meta-box-sortables ui-sortable">
			<form method="get">
				<input type="hidden" name="page" value="wp-rest-cache"/>
				<input type="hidden" name="sub" value="<?php echo esc_attr( $wp_rest_cache_sub ); ?>"/>
				<?php
				$wp_rest_cache_list->prepare_items();
				$wp_rest_cache_list->search_box( __( 'Search', 'wp-rest-cache' ), 'search_id' );
				$wp_rest_cache_list->display();
				?>
			</form>
		</div>
	</div>
</div>
<br class="clear">
