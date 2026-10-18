<?php
/**
 * View for the Cache details.
 *
 * @link: https://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Admin/Partials
 */

/** This filter is documented in admin/class-admin.php in the function create_menu(). */
if ( ! current_user_can( apply_filters( 'wp_rest_cache/settings_capability', 'administrator' ) ) ) {
	wp_die( esc_html__( 'You do not have permission to access this page.', 'wp-rest-cache' ) );
}
?>
<div class="wrap">
	<h3><?php esc_html_e( 'Cache details', 'wp-rest-cache' ); ?></h3>
	<?php
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only navigation markers; flush/delete actions below verify their own nonces.
	$wprc_cache_key = isset( $_GET['cache_key'] ) ? filter_var( $_GET['cache_key'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;
	$wp_rest_cache  = \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->get_cache_data( $wprc_cache_key );
	if ( ! is_null( $wp_rest_cache ) ) {
		$wprc_page = isset( $_GET['page'] ) ? filter_var( $_GET['page'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;
		$wprc_sub  = isset( $_GET['sub'] ) ? filter_var( $_GET['sub'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
		?>
		<p>
			<?php if ( $wp_rest_cache['row']['is_active'] ) : ?>
				<a href="
				<?php
				printf(
					'?page=%s&sub=%s&action=%s&cache_key=%s&wp_rest_cache_nonce=%s',
					esc_attr( $wprc_page ),
					esc_attr( $wprc_sub ),
					'flush',
					esc_attr( $wp_rest_cache['row']['cache_key'] ),
					esc_attr( wp_create_nonce( 'wp_rest_cache_flush_cache' ) )
				);
				?>
				" class="button button-primary" rel="noopener noreferrer">
					<?php esc_html_e( 'Flush cache', 'wp-rest-cache' ); ?>
				</a>
			<?php endif; ?>
			<a href="
			<?php
			printf(
				'?page=%s&sub=%s&action=%s&cache_key=%s&wp_rest_cache_nonce=%s',
				esc_attr( $wprc_page ),
				esc_attr( $wprc_sub ),
				'delete',
				esc_attr( $wp_rest_cache['row']['cache_key'] ),
				esc_attr( wp_create_nonce( 'wp_rest_cache_delete_cache' ) )
			);
			?>
			" class="button button-secondary" rel="noopener noreferrer">
				<?php esc_html_e( 'Delete cache', 'wp-rest-cache' ); ?>
			</a>
		</p>
		<?php
	}
	?>
	<div class="poststuff">
		<div id="post-body" class="metabox-holder">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3 class="hndle"><?php esc_html_e( 'Cache info', 'wp-rest-cache' ); ?></h3>
						<div class="inside">
							<p>
								<?php if ( is_null( $wp_rest_cache ) ) : ?>
									<?php esc_html_e( 'Sorry the cache could not be found.', 'wp-rest-cache' ); ?>
								<?php else : ?>
							<table class="form-table">
								<tr valign="top">
									<th scope="row"><?php esc_html_e( 'Cache Key', 'wp-rest-cache' ); ?></th>
									<td><?php echo esc_html( $wp_rest_cache['row']['cache_key'] ); ?></td>
								</tr>
								<tr valign="top" class="alternate">
									<th scope="row"><?php esc_html_e( 'Cache Type', 'wp-rest-cache' ); ?></th>
									<td><?php echo esc_html( $wp_rest_cache['row']['cache_type'] ); ?></td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php esc_html_e( 'Request URI', 'wp-rest-cache' ); ?></th>
									<td><?php echo esc_html( $wp_rest_cache['row']['request_uri'] ); ?></td>
								</tr>
								<tr valign="top" class="alternate">
									<th scope="row"><?php esc_html_e( 'Object Type', 'wp-rest-cache' ); ?></th>
									<td><?php echo esc_html( $wp_rest_cache['row']['object_type'] ); ?></td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php esc_html_e( 'Expiration', 'wp-rest-cache' ); ?></th>
									<td><?php echo esc_html( $wp_rest_cache['row']['expiration'] ); ?></td>
								</tr>
								<tr valign="top" class="alternate">
									<th scope="row"><?php esc_html_e( '# Cache Hits', 'wp-rest-cache' ); ?></th>
									<td><?php echo esc_html( $wp_rest_cache['row']['cache_hits'] ); ?></td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php esc_html_e( 'Active', 'wp-rest-cache' ); ?></th>
									<td>
										<?php
										if ( $wp_rest_cache['row']['is_active'] ) {
											printf(
												'<span class="dashicons dashicons-yes" style="color:green" title="%s"></span>
                                                    <span class="screen-reader-text">%s</span>',
												esc_html__( 'Cache is ready to be served.', 'wp-rest-cache' ),
												esc_html__( 'Cache is ready to be served.', 'wp-rest-cache' )
											);
										} else {

											printf(
												'<span class="dashicons dashicons-no" style="color:red" title="%s"></span>
                                                    <span class="screen-reader-text">%s</span>',
												esc_html__( 'Cache is expired or flushed.', 'wp-rest-cache' ),
												esc_html__( 'Cache is expired or flushed.', 'wp-rest-cache' )
											);
										}
										?>
									</td>
								</tr>
									<?php
									/**
									 * Action to add extra rows to the cache details info table.
									 *
									 * @since 2026.2.0
									 *
									 * @param array $wp_rest_cache The cache data array.
									 */
									do_action( 'wp_rest_cache/cache_details_info_rows', $wp_rest_cache );
									?>
							</table>
							<?php endif; ?>
							</p>
						</div>
					</div>
					<?php if ( $wp_rest_cache['row']['request_headers'] ) : ?>
						<?php
						$wprc_request_headers = json_decode( $wp_rest_cache['row']['request_headers'], true );

						/**
						 * Filter the request headers before displaying in cache details.
						 *
						 * Use this to sanitize or mask sensitive headers like Authorization tokens.
						 *
						 * @since 2026.2.0
						 *
						 * @param array $wprc_request_headers The request headers array.
						 * @param array $wp_rest_cache        The full cache data array.
						 */
						$wprc_request_headers = apply_filters( 'wp_rest_cache/cache_details_request_headers', $wprc_request_headers, $wp_rest_cache );
						?>
						<?php if ( ! empty( $wprc_request_headers ) ) : ?>
						<div class="postbox">
							<h3 class="hndle"><?php esc_html_e( 'Cached request headers', 'wp-rest-cache' ); ?></h3>
							<div class="inside">
								<p>
								<pre><?php echo esc_html( wp_json_encode( $wprc_request_headers, JSON_PRETTY_PRINT ) ); ?></pre>
								</p>
							</div>
						</div>
						<?php endif; ?>
					<?php endif; ?>
					<div class="postbox">
						<h3 class="hndle"><?php esc_html_e( 'Cache data', 'wp-rest-cache' ); ?></h3>
						<div class="inside">
							<p>
								<?php if ( empty( $wp_rest_cache['data'] ) ) : ?>
									<?php esc_html_e( 'Cache is expired or flushed.', 'wp-rest-cache' ); ?>
								<?php else : ?>
							<pre><?php echo esc_html( wp_json_encode( $wp_rest_cache['data']['data'], JSON_PRETTY_PRINT ) ); ?></pre>
							<?php endif; ?>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
