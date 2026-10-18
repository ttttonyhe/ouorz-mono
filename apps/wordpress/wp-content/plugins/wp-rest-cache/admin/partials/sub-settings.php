<?php
/**
 * View for the settings tab.
 *
 * @link: https://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Admin/Partials
 */

?>
<div class="wrap">
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<!-- Main content area -->
			<div id="post-body-content">
				<div class="postbox">
					<div class="inside">
						<form method="post" action="options.php">
							<?php settings_fields( 'wp-rest-cache-settings' ); ?>
							<?php do_settings_sections( 'wp-rest-cache-settings' ); ?>
							<?php $wp_rest_cache_timeout = \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->get_timeout( false ); ?>
							<?php $wp_rest_cache_timeout_interval = \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->get_timeout_interval(); ?>
							<?php $wp_rest_cache_regenerate = \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->should_regenerate(); ?>
							<?php $wp_rest_cache_regenerate_interval = \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->get_regenerate_interval(); ?>
							<?php $wp_rest_cache_regenerate_number = \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->get_regenerate_number(); ?>
							<?php $wp_rest_cache_schedules = wp_get_schedules(); ?>
							<?php $wp_rest_cache_memcache_used = \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->get_memcache_used(); ?>
							<?php $wp_rest_cache_global_cacheable_request_headers = \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->get_global_cacheable_request_headers(); ?>

							<table class="form-table" role="presentation">
								<tbody>
								<tr>
									<th scope="row"><?php esc_html_e( 'Cache timeout', 'wp-rest-cache' ); ?></th>
									<td>
										<input type="number" min="1" name="wp_rest_cache_timeout" class="small-text"
											<?php echo defined( 'WP_REST_CACHE_TIMEOUT' ) ? 'disabled="disabled"' : ''; ?>
												value="<?php echo esc_attr( (string) $wp_rest_cache_timeout ); ?>">
										<select name="wp_rest_cache_timeout_interval" id="wp_rest_cache_timeout_interval"
											<?php echo defined( 'WP_REST_CACHE_TIMEOUT_INTERVAL' ) ? 'disabled="disabled"' : ''; ?>
											style="vertical-align: initial">
											<option value="<?php echo esc_attr( (string) MINUTE_IN_SECONDS ); ?>"
												<?php selected( $wp_rest_cache_timeout_interval, MINUTE_IN_SECONDS ); ?>>
												<?php esc_html_e( 'Minute(s)', 'wp-rest-cache' ); ?>
											</option>
											<option value="<?php echo esc_attr( (string) HOUR_IN_SECONDS ); ?>"
												<?php selected( $wp_rest_cache_timeout_interval, HOUR_IN_SECONDS ); ?>>
												<?php esc_html_e( 'Hour(s)', 'wp-rest-cache' ); ?>
											</option>
											<option value="<?php echo esc_attr( (string) DAY_IN_SECONDS ); ?>"
												<?php selected( $wp_rest_cache_timeout_interval, DAY_IN_SECONDS ); ?>>
												<?php esc_html_e( 'Day(s)', 'wp-rest-cache' ); ?>
											</option>
											<option value="<?php echo esc_attr( (string) WEEK_IN_SECONDS ); ?>"
												<?php selected( $wp_rest_cache_timeout_interval, WEEK_IN_SECONDS ); ?>>
												<?php esc_html_e( 'Week(s)', 'wp-rest-cache' ); ?>
											</option>
											<option value="<?php echo esc_attr( (string) MONTH_IN_SECONDS ); ?>"
												<?php selected( $wp_rest_cache_timeout_interval, MONTH_IN_SECONDS ); ?>>
												<?php esc_html_e( 'Month(s)', 'wp-rest-cache' ); ?>
											</option>
											<option value="<?php echo esc_attr( (string) YEAR_IN_SECONDS ); ?>"
												<?php selected( $wp_rest_cache_timeout_interval, YEAR_IN_SECONDS ); ?>>
												<?php esc_html_e( 'Year(s)', 'wp-rest-cache' ); ?>
											</option>
										</select>
										<p class="description" id="wp_rest_cache_timeout-description">
											<?php esc_html_e( 'Time until expiration of cache. (Default = 1 year)', 'wp-rest-cache' ); ?>
										</p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Global cacheable request headers', 'wp-rest-cache' ); ?></th>
									<td>
										<input type="text" name="wp_rest_cache_global_cacheable_request_headers" class="regular-text"
											<?php echo defined( 'WP_REST_CACHE_GLOBAL_CACHEABLE_REQUEST_HEADERS' ) ? 'disabled="disabled"' : ''; ?>
												value="<?php echo esc_attr( $wp_rest_cache_global_cacheable_request_headers ); ?>">
										<p class="description" id="wp_rest_cache_global_cacheable_request_headers-description">
											<?php esc_html_e( 'Which request headers should be cached (and used to distinguish separate caches). This can be a comma separated list of headers. If you want to use headers for only certain REST calls please see the FAQ.', 'wp-rest-cache' ); ?>
										</p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Enable cache regeneration', 'wp-rest-cache' ); ?></th>
									<td>
										<label>
											<input type="checkbox" value="1" name="wp_rest_cache_regenerate"
												<?php echo defined( 'WP_REST_CACHE_REGENERATE' ) ? 'disabled="disabled"' : ''; ?>
												<?php echo $wp_rest_cache_regenerate ? 'checked="checked"' : ''; ?>>
											<?php esc_html_e( 'Enable cache regeneration', 'wp-rest-cache' ); ?>
										</label>
										<p class="description" id="wp_rest_cache_regenerate-description">
											<?php esc_html_e( 'Will enable a cron that regenerates expired or flushed caches.', 'wp-rest-cache' ); ?>
										</p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Regeneration interval', 'wp-rest-cache' ); ?></th>
									<td>
										<select name="wp_rest_cache_regenerate_interval" id="wp_rest_cache_regenerate_interval"
											<?php echo defined( 'WP_REST_CACHE_REGENERATE_INTERVAL' ) ? 'disabled="disabled"' : ''; ?>
											style="vertical-align: initial">
											<?php foreach ( $wp_rest_cache_schedules as $wp_rest_cache_key => $wp_rest_cache_schedule ) : ?>
												<option value="<?php echo esc_attr( $wp_rest_cache_key ); ?>"
													<?php selected( $wp_rest_cache_regenerate_interval, $wp_rest_cache_key ); ?>>
													<?php echo esc_html( $wp_rest_cache_schedule['display'] ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Max number regenerate caches', 'wp-rest-cache' ); ?></th>
									<td>
										<input type="number" min="1" name="wp_rest_cache_regenerate_number" class="small-text"
											<?php echo defined( 'WP_REST_CACHE_REGENERATE_NUMBER' ) ? 'disabled="disabled"' : ''; ?>
												value="<?php echo esc_attr( (string) $wp_rest_cache_regenerate_number ); ?>">
										<p class="description" id="wp_rest_cache_regenerate_number-description">
											<?php esc_html_e( 'How many caches should be regenerated at maximum per interval? Increasing this number will increase the load on your server when the regeneration process is running.', 'wp-rest-cache' ); ?>
										</p>
									</td>
								</tr>
								<?php
								if ( wp_using_ext_object_cache()
									&& ( class_exists( 'Memcache' ) || class_exists( 'Memcached' ) ) ) :
									?>
									<tr>
										<th scope="row"><?php esc_html_e( 'Memcache(d) used', 'wp-rest-cache' ); ?></th>
										<td>
											<label>
												<input type="checkbox" value="1" name="wp_rest_cache_memcache_used"
													<?php echo defined( 'WP_REST_CACHE_MEMCACHE_USED' ) ? 'disabled="disabled"' : ''; ?>
													<?php echo $wp_rest_cache_memcache_used ? 'checked="checked"' : ''; ?>>
												<?php esc_html_e( 'Use Memcache(d)', 'wp-rest-cache' ); ?>
											</label>
											<p class="description" id="wp_rest_cache_memcache_used-description">
												<?php esc_html_e( 'Are you using Memcache(d) as external object caching?', 'wp-rest-cache' ); ?>
											</p>
										</td>
									</tr>
								<?php endif; ?>
								</tbody>
							</table>

							<?php submit_button(); ?>
						</form>
					</div>
				</div>
			</div>

			<!-- Sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				<!-- Pro Version -->
				<div class="postbox">
					<h3 class="hndle"><span><?php esc_html_e( '⚡ Upgrade to Pro', 'wp-rest-cache' ); ?></span></h3>
					<div class="inside">
						<p><strong><?php esc_html_e( 'Get more features with WP REST Cache Pro:', 'wp-rest-cache' ); ?></strong></p>
						<p>Everything from the free version, plus a user interface to:</p>
						<ul style="list-style-type: disc; padding-left: 20px;">
							<li><?php esc_html_e( 'Configure custom endpoints for caching', 'wp-rest-cache' ); ?></li>
							<li><?php esc_html_e( 'Configure relationships within endpoints', 'wp-rest-cache' ); ?></li>
							<li><?php esc_html_e( 'No coding required', 'wp-rest-cache' ); ?></li>
						</ul>
						<p>
							<a href="https://plugins.acato.nl" class="button button-primary" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Learn More About Pro', 'wp-rest-cache' ); ?>
							</a>
						</p>
					</div>
				</div>

				<!-- Support -->
				<div class="postbox">
					<h3 class="hndle"><span><?php esc_html_e( '💬 Need Help?', 'wp-rest-cache' ); ?></span></h3>
					<div class="inside">
						<p><strong><?php esc_html_e( 'Get support for WP REST Cache:', 'wp-rest-cache' ); ?></strong></p>
						<ul>
							<li><a href="https://wordpress.org/support/plugin/wp-rest-cache/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Community Support Forum', 'wp-rest-cache' ); ?></a></li>
						</ul>
						<p class="description">
							<?php esc_html_e( 'Pro customers receive priority email support with faster response times.', 'wp-rest-cache' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>