<?php
function wpjam_get_thumbnail($img_url, $width=0, $height=0, $crop=1){
	return apply_filters('wpjam_thumbnail', $img_url, $width, $height, $crop);
}

function wpjam_has_post_thumbnail(){
	return wpjam_get_post_thumbnail_uri()?true:false;
}

function wpjam_post_thumbnail($size='thumbnail', $crop=1, $class="wp-post-image"){
	if($post_thumbnail = wpjam_get_post_thumbnail(null, $size, $crop, $class)){
		echo $post_thumbnail;
	}
}

function wpjam_get_post_thumbnail($post=null, $size='thumbnail', $crop=1, $class="wp-post-image"){
	if($post_thumbnail_src = wpjam_get_post_thumbnail_src($post, $size, $crop)){
		extract(wpjam_get_dimensions($size));
		$hwstring = image_hwstring($width, $height);
		$post_thumbnail_src_2x = wpjam_get_post_thumbnail_src($post, $size, $crop, 2);
		return '<img src="'.$post_thumbnail_src.'" srcset="'.$post_thumbnail_src_2x.' 2x" alt="'.the_title_attribute(array('echo'=>false)).'" class="'.$class.'"'.$hwstring.' />';
	}else{
		return false;
	}
}

function wpjam_get_post_thumbnail_src($post=null, $size='thumbnail', $crop=1, $retina=1){
	if($post_thumbnail_uri = wpjam_get_post_thumbnail_uri($post, $size)){
		extract(wpjam_get_dimensions($size));
		return apply_filters('wpjam_thumbnail', $post_thumbnail_uri, $width*$retina, $height*$retina, $crop);
	}else{
		return false;
	}
}

//清理缓存
add_action('save_post','wpjam_delete_thumb_cache');
function wpjam_delete_thumb_cache($post_id){
	wp_cache_delete($post_id,'post_thumbnail_uri');
}

function wpjam_get_post_thumbnail_uri($post=null, $size='full'){
	$post = get_post($post);
	if(!$post)	return false;
	
	$post_id = $post->ID;

	if(CDN_NAME){
		$size = 'full';	// 有第三方CDN的话，就获取原图
	}

	// $post_thumbnail_uri = wp_cache_get($post_id,'post_thumbnail_uri');

	// if($post_thumbnail_uri === false){
		if(has_post_thumbnail($post_id)){
			$post_thumbnail_uri =  wpjam_get_post_image_url(get_post_thumbnail_id($post_id), $size);
		}elseif($post_thumbnail_uri = apply_filters('wpjam_pre_post_thumbnail_uri',false, $post)){
			// do nothing
		}elseif($post_thumbnail_uri = wpjam_get_post_first_image($post->post_content, $size)){
			$post_thumbnail_uri	= wpjam_get_content_remote_img_url($post_thumbnail_uri, $post);
		}elseif($post_thumbnail_uri = apply_filters('wpjam_post_thumbnail_uri',false, $post)){
			//do nothing
		}else{
			$post_thumbnail_uri = wpjam_get_default_thumbnail_uri();
		}
	// 	wp_cache_set($post_id, $post_thumbnail_uri, 'post_thumbnail_uri', 6000);
	// }
	return $post_thumbnail_uri;
}

function wpjam_get_default_thumbnail_src($size){
	extract(wpjam_get_dimensions($size));
	return apply_filters('wpjam_thumbnail', wpjam_get_default_thumbnail_uri(), $width, $height);
}

function wpjam_get_default_thumbnail_uri(){
	return apply_filters('wpjam_default_thumbnail_uri','');
}

function get_post_first_image($post_content=''){
	return wpjam_get_post_first_image($post_content);
}

function wpjam_get_post_first_image($post_content='', $size='full'){
	if(!$post_content){
		$the_post		= get_post();
		$post_content	= $the_post->post_content;
	}

	preg_match_all( '/class=[\'"].*?wp-image-([\d]*)[\'"]/i', $post_content, $matches );
	if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	
		$image_id = $matches[1][0];
		if($image_url = wpjam_get_post_image_url($image_id, $size)){
			return $image_url;
		}
	}

	preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($post_content), $matches);
	if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	   
		return $matches[1][0];
	}
		
	return false;
}

function wpjam_get_post_image_url($image_id, $size='full'){
	if($thumb = wp_get_attachment_image_src($image_id, $size)){
		return $thumb[0];
	}
	return false;	
}

//copy from image_constrain_size_for_editor
function wpjam_get_dimensions($size){
	global $content_width, $_wp_additional_image_sizes;

	$width	= 0;
	$height	= 0;

	if ( is_array($size) ) {
		$width	= isset($size[0])?$size[0]:0;
		$height	= isset($size[1])?$size[1]:0;
	}elseif ( $size == 'thumb' || $size == 'thumbnail' || $size == 'post-thumbnail' ) {
		$width = intval(get_option('thumbnail_size_w'));
		$height = intval(get_option('thumbnail_size_h'));

		// last chance thumbnail size defaults
		if ( !$width && !$height ) {
			$width	= 128;
			$height	= 96;
		}
	}elseif ( $size == 'medium' ) {
		$width = intval(get_option('medium_size_w'));
		$height = intval(get_option('medium_size_h'));
		// if no width is set, default to the theme content width if available
	}elseif ( $size == 'large' ) {
		// We're inserting a large size image into the editor. If it's a really
		// big image we'll scale it down to fit reasonably within the editor
		// itself, and within the theme's content width if it's known. The user
		// can resize it in the editor if they wish.
		$width	= intval(get_option('large_size_w'));
		$height	= intval(get_option('large_size_h'));
		if ( intval($content_width) > 0 )
			$width = min( intval($content_width), $width );
	}elseif ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) && in_array( $size, array_keys( $_wp_additional_image_sizes ) ) ) {
		$width	= intval( $_wp_additional_image_sizes[$size]['width'] );
		$height	= intval( $_wp_additional_image_sizes[$size]['height'] );
		if ( intval($content_width) > 0 && 'edit' == $context ) // Only in admin. Assume that theme authors know what they're doing.
			$width	= min( intval($content_width), $width );
	}else {	// $size == 'full' has no constraint
		//没了
	}

	return compact('width','height');
}


