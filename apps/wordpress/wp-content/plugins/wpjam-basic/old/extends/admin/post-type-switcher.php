<?php

add_action('save_post', 'wpjam_pts_save_post',990,2);
function wpjam_pts_save_post($post_id, $post){
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;

	if ( ! current_user_can( 'edit_post', $post_id ) ) 
		return;

	if ( !empty( $_REQUEST['pts_post_type'] ) && !in_array( $post->post_type, array( $_REQUEST['pts_post_type'], 'revision' ) ) ) {

		$new_post_type_object = get_post_type_object( $_REQUEST['pts_post_type'] );
		if($new_post_type_object && current_user_can( $new_post_type_object->cap->publish_posts )){
			set_post_type( $post_id, $new_post_type_object->name );
		}	
	}
}

add_action('post_submitbox_misc_actions','wpjam_pts_post_submitbox_misc');
function wpjam_pts_post_submitbox_misc( ) {
	$args = (array) apply_filters( 'pts_post_type_filter', array(
		'show_ui' => true
	) );

	$post_types = get_post_types( $args, 'objects' );
	unset($post_types['attachment']);

	$cpt_object = get_post_type_object( get_post_type() );

	if ( empty( $cpt_object ) || is_wp_error( $cpt_object ) ){
		return; 
	}
	
	?>

	<div class="misc-pub-section misc-pub-section-last post-type-switcher">
		<label for="pts_post_type">日志类型：</label>
		<span id="post-type-display"><?php echo esc_html( $cpt_object->labels->singular_name ); ?></span>

		<?php if ( current_user_can( $cpt_object->cap->publish_posts ) ) : ?>

			<a href="#" id="edit-post-type-switcher" class="hide-if-no-js"><?php _e( 'Edit' ); ?></a>

			<div id="post-type-select">
				<select name="pts_post_type" id="pts_post_type">

					<?php foreach ( $post_types as $post_type => $pt ) : ?>

						<?php if ( ! current_user_can( $pt->cap->publish_posts ) ) continue; ?>

						<option value="<?php echo esc_attr( $pt->name ); ?>" <?php selected( get_post_type(), $post_type ); ?>><?php echo esc_html( $pt->labels->singular_name ); ?></option>

					<?php endforeach; ?>

				</select>
				<a href="#" id="save-post-type-switcher" class="hide-if-no-js button"><?php _e( 'OK' ); ?></a>
				<a href="#" id="cancel-post-type-switcher" class="hide-if-no-js"><?php _e( 'Cancel' ); ?></a>
			</div>

		<?php endif; ?>

	</div>

	<script type="text/javascript">
		jQuery( document ).ready( function( $ ) {
			jQuery( '.misc-pub-section.curtime.misc-pub-section-last' ).removeClass( 'misc-pub-section-last' );
			jQuery( '#edit-post-type-switcher' ).click( function(e) {
				jQuery( this ).hide();
				jQuery( '#post-type-select' ).slideDown();
				e.preventDefault();
			});

			jQuery( '#save-post-type-switcher' ).click( function(e) {
				jQuery( '#post-type-select' ).slideUp();
				jQuery( '#edit-post-type-switcher' ).show();
				jQuery( '#post-type-display' ).text( jQuery( '#pts_post_type :selected' ).text() );
				e.preventDefault();
			});

			jQuery( '#cancel-post-type-switcher' ).click( function(e) {
				jQuery( '#post-type-select' ).slideUp();
				jQuery( '#edit-post-type-switcher' ).show();
				e.preventDefault();
			});
		});
	</script>
	<style type="text/css">
		#post-type-select {
			line-height: 2.5em;
			margin-top: 3px;
			display: none;
		}
		#post-type-display {
			font-weight: bold;
		}
		
		#post-body .post-type-switcher::before {
			content: '\f109';
			font: 400 20px/1 dashicons;
			speak: none;
			display: inline-block;
			padding: 0 2px 0 0;
			top: 0;
			left: -1px;
			position: relative;
			vertical-align: top;
			-webkit-font-smoothing: antialiased;
			-moz-osx-font-smoothing: grayscale;
			text-decoration: none !important;
			color: #888;
		}
	</style>

	<?php
}