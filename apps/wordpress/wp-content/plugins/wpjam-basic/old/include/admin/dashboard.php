<?php

// Dashboard Widget
add_action('wp_dashboard_setup', 'wpjam_dashboard_setup' );
function wpjam_dashboard_setup() {
	global $wp_meta_boxes, $plugin_page;
	
	if(!empty($plugin_page)){ // 移除默认的 widget 
		// wpjam_print_r($wp_meta_boxes);
		unset($wp_meta_boxes[$plugin_page]);
	}

	if($wpjam_dashboard_widgets = apply_filters('wpjam_dashboard_widgets', array())){
		foreach ($wpjam_dashboard_widgets as $widget_id => $wpjam_dashboard_widget) {
			extract(wpjam_parse_dashboard_widget($widget_id, $wpjam_dashboard_widget));
			if($control == null){
				$screen = get_current_screen();
				add_meta_box( $widget_id, $title, $callback, $screen, $context, $priority, $args );
			}else{
				wp_add_dashboard_widget($widget_id, $title, $callback, $control, $args);
			}
		}
	}
}

function wpjam_parse_dashboard_widget($widget_id, $wpjam_dashboard_widget){
	return wp_parse_args( $wpjam_dashboard_widget, array(
		'title'		=> '',
		'callback'	=> str_replace('-', '_', $widget_id).'_dashboard_widget_callback',
		'control'	=> null,
		'args'		=> '',
		'context'	=> 'normal',	// 位置，normal 左侧, side 右侧
		'priority'	=> 'core'
	) );
}

function wpjam_admin_dashboard_page($title=''){
	require_once(ABSPATH . 'wp-admin/includes/dashboard.php');
	
	wp_dashboard_setup();

	wp_enqueue_script('dashboard');
	if(wp_is_mobile()) wp_enqueue_script('jquery-touch-punch');

	?>
	<h2><?php echo $title;?></h2>
	<div id="dashboard-widgets-wrap">
	<?php wp_dashboard(); ?>
	</div>
	<?php
}