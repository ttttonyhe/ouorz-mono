<?php
/**
 * View for the Clear Caches tab.
 *
 * @link: https://www.acato.nl
 * @since 2019.4
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Admin/Partials
 */

$wp_rest_cache_clear_cache = false;
if ( isset( $_REQUEST['wp_rest_cache_nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['wp_rest_cache_nonce'] ), 'wp_rest_cache_options' ) ) {
	$wp_rest_cache_clear_cache = true;
}
?>
	<style>
		#progressbar {
			margin-top: 5px;
		}

		.ui-progressbar {
			position: relative;
			color: #333;
		}

		.progress-label {
			position: absolute;
			left: 50%;
			top: 4px;
			font-weight: bold;
			text-shadow: 1px 1px 0 #fff;
			margin-left: -40px;
		}

		.ui-widget-header {
			border: 1px solid;
			background: #b3d4fc;
			font-weight: bold;
			margin-left: -40px;
		}
	</style>

	<div class="wrap">
		<form method="get" action="<?php echo esc_attr( admin_url( 'options-general.php' ) ); ?>">
			<input type="hidden" name="page" value="wp-rest-cache">
			<input type="hidden" name="sub" value="clear-cache">
			<?php wp_nonce_field( 'wp_rest_cache_options', 'wp_rest_cache_nonce' ); ?>
			<input type="checkbox" name="delete_caches" value="1">Delete all caches (this will make you lose all statistics)<br/><br/>
			<input type="submit" name="submit" id="submit"
				class="button button-<?php echo $wp_rest_cache_clear_cache ? 'disabled' : 'primary'; ?>" <?php echo $wp_rest_cache_clear_cache ? 'disabled' : ''; ?>
				value="<?php esc_attr_e( 'Clear REST Cache', 'wp-rest-cache' ); ?>">
		</form>

		<?php if ( $wp_rest_cache_clear_cache ) : ?>
			<div id="progressbar">
				<div class="progress-label"><?php esc_html_e( 'Starting...', 'wp-rest-cache' ); ?></div>
			</div>
		<?php endif; ?>
	</div>

<?php if ( $wp_rest_cache_clear_cache ) : ?>
	<script>
		jQuery(function () {
			var progressbar = jQuery("#progressbar"),
				progressLabel = jQuery(".progress-label");
			var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
			var ajaxnonce = '<?php echo esc_js( wp_create_nonce( 'wp_rest_cache_clear_cache_ajax' ) ); ?>';
			var deletecaches = '<?php echo esc_js( filter_input( INPUT_GET, 'delete_caches', FILTER_VALIDATE_BOOLEAN ) ); ?>';

			progressbar.progressbar({
				value: false,
				change: function () {
					progressLabel.text(progressbar.progressbar("value") + "%");
				},
				complete: function () {
					progressLabel.text('<?php esc_html_e( 'Caches cleared!', 'wp-rest-cache' ); ?>');
				}
			});

			function process(page) {
				jQuery.ajax({
					type: "post",
					dataType: "json",
					url: ajaxurl,
					data: {
						"action": "flush_caches",
						"page": page,
						"wp_rest_cache_nonce": ajaxnonce,
						"delete_caches": deletecaches
					},
					success: function (response) {
						progressbar.progressbar("value", response.percentage);
						if (response.percentage < 100) {
							process(page + 1);
						}
					}
				})
			}

			process(1);
		});
	</script>
<?php endif; ?>
