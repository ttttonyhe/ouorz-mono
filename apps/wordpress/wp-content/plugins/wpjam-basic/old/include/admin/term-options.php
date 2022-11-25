<?php
// 设置 Term Options
add_action('admin_init', 'wpjam_term_admin_init',99);
function wpjam_term_admin_init(){
	if($wpjam_term_options = wpjam_get_term_options()) {
		$taxonomies = get_taxonomies(array( 'public' => true)); 	// init 之后才能获取 taxonomy 列表
		foreach ($taxonomies as $taxonomy) {
			add_action($taxonomy.'_add_form_fields', 	'wpjam_add_term_form_fields');
			add_action($taxonomy.'_edit_form_fields', 	'wpjam_edit_term_form_fields', 10, 2); 

			add_action('manage_edit-'.$taxonomy.'_columns',			'wpjam_manage_edit_term_columns');
			add_action('manage_edit-'.$taxonomy.'_sortable_columns','wpjam_manage_edit_term_sortable_columns');
			add_filter('manage_'.$taxonomy.'_custom_column',		'wpjam_manage_term_custom_column', 10, 3);
		}

		// 保存
		add_action('edited_term',	'wpjam_save_term_fields',10, 3);  
		add_action('created_term',	'wpjam_save_term_fields',10, 3);
	}
}

// 获取 Term Meta Options 
function wpjam_get_term_options($taxonomy=''){
	$wpjam_term_options = apply_filters('wpjam_term_options', array());

	if(!$taxonomy){
		return $wpjam_term_options;
	}else{
		$wpjam_taxonomy_options = array();
		foreach ($wpjam_term_options as $key => $wpjam_term_option) {
			$taxonomies = isset($wpjam_term_option['taxonomies'])?$wpjam_term_option['taxonomies']:'all';
			if($taxonomies == 'all' || in_array($taxonomy,$taxonomies)){
				$wpjam_term_option['value']		= isset($wpjam_term_option['default'])?$wpjam_term_option['default']:'';
				$wpjam_taxonomy_options[$key]	= $wpjam_term_option;
			}
		}
		return $wpjam_taxonomy_options;
	}
}

// 添加 Term Meta 添加表单
function wpjam_add_term_form_fields($taxonomy){
	if($wpjam_taxonomy_options = wpjam_get_term_options($taxonomy)) {
		wpjam_form_fields($wpjam_taxonomy_options, 'div', 'form-field');
	}
}

// 添加 Term Meta 编辑表单
function wpjam_edit_term_form_fields($term, $taxonomy=''){
	if($wpjam_taxonomy_options = wpjam_get_term_options($taxonomy)) {
		foreach ($wpjam_taxonomy_options as $key => $field) {
			$wpjam_taxonomy_options[$key]['value']	= get_term_meta($term->term_id, $key, true);
		}

		wpjam_form_fields($wpjam_taxonomy_options, 'tr', 'form-field');
	}
}

// Term 列表显示字段
function wpjam_manage_edit_term_columns($columns){
	$taxonomy = str_replace(array('manage_edit-','_columns'), '', current_filter());

	if($wpjam_taxonomy_options = wpjam_get_term_options($taxonomy)) {
		foreach ($wpjam_taxonomy_options as $key => $field) {
			if(!empty($field['show_admin_column'])){
				$columns[$key]	= $field['title'];
			}
		}
	}

	return $columns;
}

function wpjam_manage_term_custom_column($value, $column_name, $term_id){
	$taxonomy = str_replace(array('manage_','_custom_column'), '', current_filter());

	if($wpjam_taxonomy_options = wpjam_get_term_options($taxonomy)){
		if(isset($wpjam_taxonomy_options[$column_name])){
			return apply_filters('wpjam_manage_term_'.$column_name.'_column', get_term_meta($term_id, $column_name, true), $term_id);
		}
	}
}

function wpjam_manage_edit_term_sortable_columns($columns){
	$taxonomy = str_replace(array('manage_edit-','_sortable_columns'), '', current_filter());

	if($wpjam_taxonomy_options = wpjam_get_term_options($taxonomy)) {
		foreach ($wpjam_taxonomy_options as $key => $field) {
			if(!empty($field['show_admin_column']) && !empty($field['sortable_column'])){
				$columns[$key]	= $key;
			}
		}
	}

	return $columns;
}

// 保存 Term Meta
function wpjam_save_term_fields($term_id, $tt_id, $taxonomy) {
	if(!is_admin()) return;

	if(current_filter() == 'edited_term'){	// 防止点击快速编辑删除 meta 的问题
		global $pagenow;
		if($pagenow != 'edit-tags.php') return;
	}

	if($wpjam_taxonomy_options = wpjam_get_term_options($taxonomy)) {
		foreach ($wpjam_taxonomy_options as $key => $field) {
			wpjam_save_field($key, $field, $term_id, 'term');
		}
	}
}

