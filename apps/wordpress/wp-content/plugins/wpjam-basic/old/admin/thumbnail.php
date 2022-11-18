<?php
add_filter('manage_posts_columns', 'wpjam_manage_posts_columns_add_thumbnail',10,2);
//add_filter('manage_pages_columns', 'wpjam_manage_posts_columns_add_thumbnail');
function wpjam_manage_posts_columns_add_thumbnail($posts_columns, $post_type){
	$post_type_object = get_post_type_object($post_type);
	if(!empty($post_type_object->thumbnail_column) || $post_type == 'post'){
		$posts_columns['thumbnail'] = '缩略图';
	}
	return $posts_columns;
}

add_action('manage_posts_custom_column','wpjam_manage_posts_custom_column_show_thumbnail',10,2);
//add_action('manage_pages_custom_column','wpjam_manage_posts_custom_column_show_thumbnail',10,2);
function wpjam_manage_posts_custom_column_show_thumbnail($column_name,$id){
	if ($column_name == 'thumbnail') {
		wpjam_post_thumbnail(array(60,60));
	}
}


add_filter('wpjam_term_options', 'wpjam_term_thumnail_options');
function wpjam_term_thumnail_options($wpjam_term_options){
	$thumbnail_taxonomies	= array();
	$taxonomies		= get_taxonomies(array( 'public' => true)); 
	foreach ($taxonomies as $taxonomy) {
		$taxonomy_object = get_taxonomy( $taxonomy );
		if(!empty($taxonomy_object->thumbnail_meta_box) || $taxonomy == 'post_tag' || $taxonomy == 'category'){
			$thumbnail_taxonomies[] = $taxonomy;
		}
	}

	if($thumbnail_taxonomies){
		$wpjam_term_options['thumbnail'] = array('title'=>'缩略图', 'type'=>'image',	'taxonomies'=>$thumbnail_taxonomies, 'show_admin_column'=>true);
	}
	
	return $wpjam_term_options;
}

add_filter('wpjam_manage_term_thumbnail_column', 'wpjam_manage_term_thumbnail_column', 10, 2);
function wpjam_manage_term_thumbnail_column($value, $term_id){
	return wpjam_get_term_thumbnail($term_id,array(60,60));
}

add_filter('wpjam_taxonomy_args', 'wpjam_thumbnail_taxonomy_args');
function wpjam_thumbnail_taxonomy_args($taxonomy_args){
	$taxonomy_args['thumbnail_meta_box'] = isset($taxonomy_args['thumbnail_meta_box'])?$taxonomy_args['thumbnail_meta_box']:true;
	return $taxonomy_args;
}

add_filter('wpjam_post_type_args', 'wpjam_thumbnail_post_type_args');
function wpjam_thumbnail_post_type_args($post_type_args){
	$post_type_args['thumbnail_column'] = isset($post_type_args['thumbnail_column'])?$post_type_args['thumbnail_column']:true;
	return $post_type_args;
}