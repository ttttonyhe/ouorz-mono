<?php
// 输出日志自定义字段表单
add_action( 'add_meta_boxes', 'wpjam_post_options_meta_box', 10, 2 );
function wpjam_post_options_meta_box($post_type, $post) {
	global $pagenow;
	if($pagenow != 'post.php' && $pagenow != 'post-new.php') return;	// 只有在 post 编辑页面才添加 Meta Box

	if($wpjam_post_options = wpjam_get_post_options($post_type)){
		foreach($wpjam_post_options as $meta_key => $wpjam_post_option){
			extract($wpjam_post_option);
			add_meta_box($meta_key, $title, $callback, $post_type, $context, $priority, array('context'=>$context, 'fields'=>$fields));
		}
	}
}

// 获取自定义字段设置
function wpjam_get_post_options($post_type=''){
	$wpjam_post_options = apply_filters('wpjam_options', array()); // 逐步放弃
	$wpjam_post_options = apply_filters('wpjam_post_options', $wpjam_post_options);
	
	if(!$post_type){
		return $wpjam_post_options;
	}else{
		$wpjam_post_type_options = array();
		foreach($wpjam_post_options as $meta_key => $wpjam_post_option){
			$wpjam_post_option = wpjam_parse_post_option($wpjam_post_option);
			if( $wpjam_post_option['post_types'] == 'all' || in_array($post_type, $wpjam_post_option['post_types'])){
				$wpjam_post_type_options[$meta_key] = $wpjam_post_option;
			}
		}
		return $wpjam_post_type_options;
	}
}

function wpjam_get_post_fields($post_type=''){
	$wpjam_post_fields = array();
	
	if($wpjam_post_options = wpjam_get_post_options($post_type)) {
		foreach ($wpjam_post_options as $meta_key => $wpjam_post_option) {
			
			if(!$wpjam_post_option['fields']) continue;

			$wpjam_post_fields = array_merge($wpjam_post_fields, $wpjam_post_option['fields']);
		}
	}

	return $wpjam_post_fields;
}

// 获取自定义字段中需要显示到列表页的栏目
function wpjam_get_post_columns($post_type='',$column_type='admin'){
	$wpjam_post_columns = array();

	if($wpjam_post_options = wpjam_get_post_options($post_type)) {
		foreach ($wpjam_post_options as $meta_key => $wpjam_post_option) {
			
			if(!$wpjam_post_option['fields']) continue;

			foreach($wpjam_post_option['fields'] as $key => $field){
				if($column_type == 'sortable'){
					if(!empty($field['show_admin_column']) && !empty($field['sortable_column'])){
						$wpjam_post_columns[$key] = $key;
					}
				}else{
					if(!empty($field['show_admin_column'])){
						$wpjam_post_columns[$key] = $field['title'];
					}
				}
			}
		}
	}

	return $wpjam_post_columns;
}

// 处理和解析自定义字段的 meta_box
function wpjam_parse_post_option($wpjam_post_option){
	return wp_parse_args( $wpjam_post_option, array(
		'context'		=> 'normal',
		'priority'		=> 'high',
		'post_types'	=> 'all',
		'title'			=> ' ',
		'fields'		=> '',
		'callback'		=> 'wpjam_post_options_callback'
	) );
}

// 日志自定义字段的处理函数
function wpjam_post_options_callback($post, $meta_box){
	global $pagenow;

	$fields			= $meta_box['args']['fields'];
	$fields_type	= ($meta_box['args']['context']=='side')?'list':'table';

	foreach ($fields as $key => $field) {
		if($pagenow == 'post-new.php'){
			$fields[$key]['value']	= isset($field['default'])?$field['default']:'';
		}else{
			$fields[$key]['value']	= isset($_REQUEST[$key])?$_REQUEST[$key]:get_post_meta($post->ID, $key, true);
		}
	}
	
	wpjam_form_fields($fields, $fields_type);
}

// 在日志列表页输出自定义字段名
add_filter('manage_posts_columns', 'wpjam_manage_posts_columns', 99, 2);
function wpjam_manage_posts_columns($columns, $post_type){

	if($wpjam_post_columns = wpjam_get_post_columns($post_type)){
		$columns	= array_merge($columns, $wpjam_post_columns); 
	}

	// 把日期移到最后
	unset($columns['date']);
	$columns['date'] = '日期';

	return $columns;
}

// 在日志列表页输出自定义字段的值
add_action('manage_posts_custom_column','wpjam_manage_posts_custom_column',10,2);
function wpjam_manage_posts_custom_column($column_name, $post_id){
	$post		= get_post($post_id);
	$wpjam_post_fields	= wpjam_get_post_fields($post->post_type);

	if($wpjam_post_fields && isset($wpjam_post_fields[$column_name])){
		$column_value = get_post_meta($post_id, $column_name, true);
		if(isset($wpjam_post_fields[$column_name]['options'])){
			$column_options	= $wpjam_post_fields[$column_name]['options'];
			$column_value	= isset($column_options[$column_value])?$column_options[$column_value]:$column_value;
		}
		echo apply_filters('wpjam_manage_posts_'.$column_name.'_column', $column_value , $post_id);
	}
}

// 在日志列表页获取可用于排序的自定义字段
add_action('admin_init', 'wpjam_manage_posts_sortable_columns_init');
function wpjam_manage_posts_sortable_columns_init(){
	global $pagenow;
	if($pagenow != 'edit.php')	return;
	
	foreach (get_post_types(array('show_ui' => true)) as $post_type){
		add_filter('manage_edit-'.$post_type.'_sortable_columns', 'wpjam_manage_posts_sortable_columns');
	}
}

function wpjam_manage_posts_sortable_columns($sortable_columns){
	global $typenow;
	if($wpjam_post_sortable_columns = wpjam_get_post_columns($typenow, 'sortable')){
		$sortable_columns	= array_merge($sortable_columns, $wpjam_post_sortable_columns); 
	}

	return $sortable_columns;
}

// 使得可排序的自定义字段排序功能生效
add_action('pre_get_posts', 'wpjam_pre_get_posts_sortable_columns');
function wpjam_pre_get_posts_sortable_columns($wp_query) {
	if(!is_admin()) return;
	
	global $pagenow;
	if($pagenow != 'edit.php')	return;

	$orderby	= $wp_query->get('orderby');

	if($orderby){
		$post_type	= $wp_query->get('post_type');

		if($wpjam_post_columns = wpjam_get_post_columns($post_type, 'sortable')){
			$wpjam_post_fields = wpjam_get_post_fields($post_type);
			if(isset($wpjam_post_columns[$orderby])){
				$wp_query->set('meta_key', $orderby);
				$orderby_type = ($wpjam_post_fields[$orderby]['sortable_column'] == 'meta_value_num')?'meta_value_num':'meta_value';
				$wp_query->set('orderby', $orderby_type);
			}
		}
	}
}


// 保存日志自定义字段
add_action('save_post', 'wpjam_save_post_options', 999, 2);
function wpjam_save_post_options($post_ID, $post){
	
	if(!is_admin()) return;

	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

	global $pagenow;
	if($pagenow != 'post.php' && $pagenow != 'post-new.php') return;

	if($wpjam_post_fields = wpjam_get_post_fields($post->post_type))	{
		foreach ($wpjam_post_fields as $key => $field) {
			wpjam_save_field($key, $field, $post_ID);	
		}
	}
}

// 保存日志自定义字段和 Term Meta
function wpjam_save_field($key, $field, $object_id, $object_type='post'){

	if($field['type'] == 'fieldset'){
		if($field['fields']){
			foreach ($field['fields'] as $sub_key => $sub_field) {
				wpjam_save_field($sub_key, $sub_field, $object_id, $object_type);
			}
		}
		return;
	}

	$value = wpjam_form_field_validate($key, $field);

	if($value === false){
		return;
	}

	$get_function		= 'get_'.$object_type.'_meta';
	$update_function	= 'update_'.$object_type.'_meta';
	$delete_function	= 'delete_'.$object_type.'_meta';

	if($value){
		call_user_func($update_function, $object_id, $key, $value);
	}else{
		if(call_user_func($get_function, $object_id, $key, true)) {
			call_user_func($delete_function, $object_id, $key);
		}
	}
}