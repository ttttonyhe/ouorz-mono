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
	<div class="postbox-container">
		<form method="post" action="options.php" class="postbox" style="margin: 10px">

			<h3 style="padding: 0 12px"><span><?php esc_html_e( 'Settings', 'wp-rest-cache' ); ?></span></h3>
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

			<table class="form-table" style="margin: 0 12px">
				<tbody>
				<tr>
					<th><?php esc_html_e( 'Cache timeout', 'wp-rest-cache' ); ?></th>
					<td>
						<input type="number" min="1" name="wp_rest_cache_timeout" class="small-text"
								value="<?php echo esc_attr( (string) $wp_rest_cache_timeout ); ?>">
						<select name="wp_rest_cache_timeout_interval" id="wp_rest_cache_timeout_interval"
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
						<p class="description"
							id="wp_rest_cache_timeout-description"><?php esc_html_e( 'Time until expiration of cache. (Default = 1 year)', 'wp-rest-cache' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Global cacheable request headers', 'wp-rest-cache' ); ?></th>
					<td>
						<input type="text" value="<?php echo esc_attr( $wp_rest_cache_global_cacheable_request_headers ); ?>"
							name="wp_rest_cache_global_cacheable_request_headers">
						<p class="description"
							id="wp_rest_cache_global_cacheable_request_headers-description"><?php esc_html_e( 'Which request headers should be cached (and used to distinguish separate caches). This can be a comma separated list of headers. If you want to use headers for only certain REST calls please see the FAQ.', 'wp-rest-cache' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Enable cache regeneration', 'wp-rest-cache' ); ?></th>
					<td>
						<input type="checkbox" value="1"
							name="wp_rest_cache_regenerate" <?php echo $wp_rest_cache_regenerate ? 'checked="checked"' : ''; ?>>
						<p class="description"
							id="wp_rest_cache_regenerate-description"><?php esc_html_e( 'Will enable a cron that regenerates expired or flushed caches.', 'wp-rest-cache' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Regeneration interval', 'wp-rest-cache' ); ?></th>
					<td>
						<select name="wp_rest_cache_regenerate_interval" id="wp_rest_cache_regenerate_interval"
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
					<th><?php esc_html_e( 'Max number regenerate caches', 'wp-rest-cache' ); ?></th>
					<td>
						<input type="number" min="1" name="wp_rest_cache_regenerate_number" class="small-text"
							value="<?php echo esc_attr( (string) $wp_rest_cache_regenerate_number ); ?>">
						<p class="description"
							id="wp_rest_cache_regenerate_number-description"><?php esc_html_e( 'How many caches should be regenerated at maximum per interval? Increasing this number will increase the load on your server when the regeneration process is running.', 'wp-rest-cache' ); ?></p>
					</td>
				</tr>
				<?php
				if ( wp_using_ext_object_cache()
					&& ( class_exists( 'Memcache' ) || class_exists( 'Memcached' ) ) ) :
					?>
					<tr>
						<th><?php esc_html_e( 'Memcache(d) used', 'wp-rest-cache' ); ?></th>
						<td>
							<input type="checkbox" value="1"
								name="wp_rest_cache_memcache_used" <?php echo $wp_rest_cache_memcache_used ? 'checked="checked"' : ''; ?>>
							<p class="description"
								id="wp_rest_cache_memcache_used-description"><?php esc_html_e( 'Are you using Memcache(d) as external object caching?', 'wp-rest-cache' ); ?></p>
						</td>
					</tr>
				<?php endif; ?>
				</tbody>
				<tfoot>
				<tr>
					<td colspan="2" align="center">
						<?php submit_button(); ?>
					</td>
				</tr>
				</tfoot>
			</table>
		</form>
	</div>
</div>
