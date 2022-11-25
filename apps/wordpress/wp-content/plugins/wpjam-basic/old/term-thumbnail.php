<?php
add_filter('wpjam_pre_post_thumbnail_uri','wpjam_tag_pre_post_thumbnail_uri',10,2);
function wpjam_tag_pre_post_thumbnail_uri($post_thumbnail_uri,$post){
	if($post_taxonomies = get_post_taxonomies($post)){
		foreach($post_taxonomies as $taxonomy){
			if($taxonomy == 'category') continue;
			if($terms = get_the_terms($post,$taxonomy)){
				foreach ($terms as $term) {
					if($term_thumbnail = get_term_meta($term->term_id,'thumbnail',true)){
						return $term_thumbnail;
					}
				}
			}
		}
	}
	return '';
}

add_filter('wpjam_post_thumbnail_uri','wpjam_category_post_thumbnail_uri',10,2);
function wpjam_category_post_thumbnail_uri($post_thumbnail_uri,$post){
	if($post_taxonomies = get_post_taxonomies($post)){
		if(in_array('category',$post_taxonomies)){
			$categories = get_the_category($post);
			if($categories){
				foreach ($categories as $category) {
					if($term_thumbnail = get_term_meta($category->term_id,'thumbnail',true)){
						return $term_thumbnail;
					}
				}	   
			}
		}
	}
	return '';
}

function wpjam_has_term_thumbnail(){
	return (wpjam_get_term_thumbnail_uri())?true:false;
}

function wpjam_has_category_thumbnail(){
	return wpjam_has_term_thumbnail();
}

function wpjam_has_tag_thumbnail(){
	return wpjam_has_term_thumbnail();
}

function wpjam_get_term_thumbnail_uri($term=null){
	$term = ($term)?$term:get_queried_object();

	if ( !$term ) return false;

	$term_id = is_object($term)?$term->term_id:$term;

	return get_term_meta($term_id, 'thumbnail', true);
}

function wpjam_get_term_thumbnail_src($term=null, $size='thumbnail', $crop=1){
	if($term_thumbnail_uri = wpjam_get_term_thumbnail_uri($term)){
		extract(wpjam_get_dimensions($size));
		return apply_filters('wpjam_thumbnail', $term_thumbnail_uri, $width, $height, $crop);
	}
	return false;	
}

function wpjam_get_term_thumbnail($term=null, $size='thumbnail', $crop=1, $class="wp-term-image"){
	if($term_thumbnail_src = wpjam_get_term_thumbnail_src($term, $size, $crop)){
		extract(wpjam_get_dimensions($size));
		$hwstring = image_hwstring($width, $height);
		return  '<img src="'.$term_thumbnail_src.'" class="'.$class.'"'.$hwstring.' />';
	}
	return false;
}

function wpjam_term_thumbnail($size='thumbnail', $crop=1, $class="wp-term-image"){
	if($term_thumbnail =  wpjam_get_term_thumbnail(null, $size, $crop, $class)){
		echo $term_thumbnail;
	}
}

function wpjam_category_thumbnail($size='thumbnail', $crop=1, $class="wp-category-image"){
	wpjam_term_thumbnail($size,$crop,$class);
}

function wpjam_tag_thumbnail($size='thumbnail', $crop=1, $class="wp-tag-image"){
	wpjam_term_thumbnail($size,$crop,$class);
}
