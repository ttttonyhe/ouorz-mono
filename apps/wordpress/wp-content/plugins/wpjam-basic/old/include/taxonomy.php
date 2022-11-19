<?php
// 注册自定义分类
add_action('init', 'wpjam_taxonomy_init', 11);
function wpjam_taxonomy_init(){
	$wpjam_taxonomies = wpjam_get_taxonomies();

	if(!$wpjam_taxonomies) return;

	foreach ($wpjam_taxonomies as $taxonomy=>$wpjam_taxonomy) {
		$object_type	= $wpjam_taxonomy['object_type'];
		$taxonomy_args	= wpjam_parse_taxonomy_args($taxonomy, $wpjam_taxonomy['args']);

		register_taxonomy( $taxonomy, $object_type, $taxonomy_args );
	}
}

function wpjam_get_taxonomies(){
	return apply_filters('wpjam_taxonomies', array());
}

// 自定义分类参数处理函数
function wpjam_parse_taxonomy_args($taxonomy, $taxonomy_args){

	$labels = isset($taxonomy_args['labels'])?$taxonomy_args['labels']:'';
	$label 	= isset($taxonomy_args['label'])?$taxonomy_args['label']:'';

	if(!$labels || is_string($labels)){
		$label_name	= ($labels) ? $labels : $label;
		$taxonomy_args['labels']  	= array(
			'name'					=> $label_name,
			'singular_name'			=> $label_name,
			'search_items'			=> '搜索'.$label_name,
			'popular_items'			=> '最受欢迎'.$label_name,
			'all_items'				=> '所有'.$label_name,
			'parent_item'			=> '父级'.$label_name,
			'parent_item_colon'		=> '父级'.$label_name,
			'edit_item'				=> '编辑'.$label_name,
			'view_item'				=> '查看'.$label_name,
			'update_item'			=> '更新'.$label_name,
			'add_new_item'			=> '新增'.$label_name,
			'new_item_name'			=> '新'.$label_name.'名',
			'separate_items_with_commas'	=> '多个'.$label_name.'请用英文逗号（,）分开',
			'add_or_remove_items'	=> '新增或者移除'.$label_name,
			'choose_from_most_used'	=> '从最常使用的'.$label_name.'中选择',
			'not_found'				=> '找不到'.$label_name,
		);
	}

	return  apply_filters('wpjam_taxonomy_args', $taxonomy_args);
}