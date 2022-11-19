<?php
include(WPJAM_BASIC_PLUGIN_DIR.'include/post-type.php');
include(WPJAM_BASIC_PLUGIN_DIR.'include/taxonomy.php');

if(is_admin()){
	include(WPJAM_BASIC_PLUGIN_DIR.'include/admin/admin-menus.php');
	include(WPJAM_BASIC_PLUGIN_DIR.'include/admin/form.php');
	include(WPJAM_BASIC_PLUGIN_DIR.'include/admin/options.php');
	include(WPJAM_BASIC_PLUGIN_DIR.'include/admin/post-options.php');
	include(WPJAM_BASIC_PLUGIN_DIR.'include/admin/term-options.php');
	include(WPJAM_BASIC_PLUGIN_DIR.'include/admin/dashboard.php');
	include(WPJAM_BASIC_PLUGIN_DIR.'include/admin/admin-errors.php');
	include(WPJAM_BASIC_PLUGIN_DIR.'include/admin/admin-notices.php');
	include(WPJAM_BASIC_PLUGIN_DIR.'include/admin/list-table.php');
}

// 获取设置
function wpjam_get_setting($option, $setting_name){
	if(is_string($option)) $option = wpjam_get_option($option);
	return isset($option[$setting_name])?str_replace("\r\n", "\n", $option[$setting_name]):'';
}

// 获取选项
function wpjam_get_option($option_name){
	$defaults	= wpjam_get_default_option( $option_name );
	$option		= ( is_multisite() && is_network_admin() ) ? get_site_option( $option_name ):get_option( $option_name );
	return wp_parse_args( $option, $defaults);
}

// 获取默认选项
function wpjam_get_default_option($option_name){
	$defaults	= apply_filters($option_name.'_defaults', array());

	if(is_multisite() && !is_network_admin()){
		$site_option	= get_site_option( $option_name );
		$defaults		= wp_parse_args( $site_option, $defaults);
	}

	return $defaults;
}

// term 排序显示
add_filter("terms_clauses", 'wpjam_admin_terms_clauses', 10,3 );
function wpjam_admin_terms_clauses( $pieces, $taxonomies, $args){
	if(is_admin() && !empty($args['orderby'])){
		$orderby	= $args['orderby'];
		$taxonomy	= $taxonomies[0];

		if($wpjam_taxonomy_options = wpjam_get_term_options($taxonomy)) {

			if(isset($wpjam_taxonomy_options[$orderby])){
				$sortable_column = isset($wpjam_taxonomy_options[$orderby]['sortable_column'])?$wpjam_taxonomy_options[$orderby]['sortable_column']:'';

				if(!$sortable_column) return $pieces;
				
				global $wpdb;
				$pieces['join']	= $pieces['join'] . " LEFT JOIN {$wpdb->prefix}termmeta AS tm ON t.term_id = tm.term_id";
				$pieces['where']= $pieces['where'] ." AND tm.meta_key = '{$orderby}'";

				if($sortable_column == 'meta_value_num'){
					$pieces['orderby']  = "GROUP BY t.term_id ORDER BY (tm.meta_value + 0)";
				}else{
					$pieces['orderby']  = "GROUP BY t.term_id ORDER BY tm.meta_value";
				}
			}
		}
	}
	return $pieces;
}


// 获取页面来源
function wpjam_get_referer(){
	$referer	= wp_get_original_referer();
	$referer	= ($referer)?$referer:wp_get_referer();

	$removable_query_args	= array_merge( 
		wpjam_get_removable_query_args(),
		array('_wp_http_referer','id','action',	'action2', '_wpnonce')
	);

	return remove_query_arg($removable_query_args, $referer);	
}

add_filter('removable_query_args', 'wpjam_get_removable_query_args');
function wpjam_get_removable_query_args(){
	$removable_query_args = array(
		'message', 'settings-updated', 'saved', 
		'update', 'updated', 'activated', 
		'activate', 'deactivate', 'locked', 
		'deleted', 'trashed', 'untrashed', 
		'enabled', 'disabled', 'skipped', 
		'spammed', 'unspammed', 'added',
		'duplicated', 'approved', 'unapproved',
		'geted', 'created', 'synced',
	);

	return $removable_query_args;
}





// 获取当前页面 url
function wpjam_get_current_page_url(){
	$ssl		= (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true:false;
	$sp			= strtolower($_SERVER['SERVER_PROTOCOL']);
	$protocol	= substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
	$port		= $_SERVER['SERVER_PORT'];
	$port		= ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
	$host		= isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
	return $protocol . '://' . $host . $port . $_SERVER['REQUEST_URI'];
}

// 打印
function wpjam_print_r($value){
	$capability	= (is_multisite())?'manage_site':'manage_options';
	if(current_user_can($capability)){
		echo '<pre>';
		print_r($value);
		echo '</pre>';
	}
}

function wpjam_var_dump($value){
	$capability	= (is_multisite())?'manage_site':'manage_options';
	if(current_user_can($capability)){
		echo '<pre>';
		var_dump($value);
		echo '</pre>';
	}
}

