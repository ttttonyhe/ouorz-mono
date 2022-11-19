<?php

add_shortcode( 'related', 'wpjam_related_posts_shortcode' );
function wpjam_related_posts_shortcode( $atts, $content='' ) {
	extract( shortcode_atts( array(
		'tag' => '0'
	), $atts ) );

	if($tag){

		if(isset($_GET['update'])) wp_cache_delete(md5($tag),'related_posts_query');

		$related_posts_query = wp_cache_get(md5($tag),'related_posts_query');

		if($related_posts_query === false){
			$tags = explode(",", $tag);

			$tag_id_array = array();

			foreach($tags as $a_tag){
				$tag_object = get_term_by('name', trim($a_tag), 'post_tag');
				$tag_id_array[] = $tag_object->term_id;
			}

			$post_types = apply_filters('wpjam_related_posts_post_types',array(get_post_type()));

			if(count($tag_id_array) > 1){
				$related_posts_query = new WP_Query( array( 'post_type'=>$post_types, 'post_status'=>'publish', 'tag__and' => $tag_id_array, 'post__not_in'=>array(get_the_ID()) ) );
			}else{
				$related_posts_query = new WP_Query( array( 'post_type'=>$post_types, 'post_status'=>'publish', 'tag_id' => $tag_id_array[0], 'post__not_in'=>array(get_the_ID()) ) );
			}

			wp_cache_set(md5($tag),$related_posts_query,'related_posts_query',3600);
		}

		return  wpjam_get_post_list($related_posts_query,array('thumb'=>false,'class'=>'related_posts'));
	}
}

function wpjam_related_posts_join($posts_join){
	global $wpdb;
	return "INNER JOIN {$wpdb->term_relationships} AS tr ON {$wpdb->posts}.ID = tr.object_id";
}

function wpjam_related_posts_where($posts_where){
	global $wpdb;
	$term_taxonomy_ids = array();
	$term_cats = get_the_terms(get_the_ID(), 'category');
	if($term_cats==false) {
		$term_cats = array();
	}

	$term_tags = get_the_terms(get_the_ID(), 'post_tag');
	if($term_tags==false) {
		$term_tags = array();
	}

	$terms = apply_filters('wpjam_related_posts_terms', array_merge($term_tags,$term_cats));

	if($terms){
		foreach ($terms as $term){
			$term_taxonomy_ids[]=$term->term_taxonomy_id;
		}
		$term_taxonomy_ids = array_unique($term_taxonomy_ids);
		$term_taxonomy_ids = implode(",",$term_taxonomy_ids);
		return $posts_where . " AND tr.term_taxonomy_id IN ({$term_taxonomy_ids}) AND {$wpdb->posts}.ID != ".get_the_ID();
	}else{
		// trigger_error(get_the_title(). '（ID:'.get_the_ID().'）没有任何 tag！',E_USER_NOTICE);
		return $posts_where;
	}
}

function wpjam_related_posts_groupby($posts_groupby){
	return " tr.object_id";
}

function wpjam_related_posts_orderby($posts_orderby){
	global $wpdb;
	return " cnt DESC, {$wpdb->posts}.post_date_gmt DESC";
}

function wpjam_related_posts_fields($posts_fields){
	return $posts_fields.", count(tr.object_id) as cnt";
}

function wpjam_get_related_posts_query($number=5){
	$related_posts_query = wp_cache_get(get_the_ID(),'related_posts_query');
	if( $related_posts_query === false) {

		add_filter('posts_join',	'wpjam_related_posts_join');
		add_filter('posts_where',	'wpjam_related_posts_where');
		add_filter('posts_groupby',	'wpjam_related_posts_groupby');
		add_filter('posts_orderby',	'wpjam_related_posts_orderby');
		add_filter('posts_fields',	'wpjam_related_posts_fields');

		$post_types = apply_filters('wpjam_related_posts_post_types',array(get_post_type()));
		$related_posts_query = new WP_Query(array('post_type'=>$post_types,'posts_per_page'=>$number));

		remove_filter('posts_join',		'wpjam_related_posts_join');
		remove_filter('posts_where',	'wpjam_related_posts_where');
		remove_filter('posts_groupby',	'wpjam_related_posts_groupby');
		remove_filter('posts_orderby',	'wpjam_related_posts_orderby');
		remove_filter('posts_fields',	'wpjam_related_posts_fields'); 

		wp_cache_set(get_the_ID(), $related_posts_query, 'related_posts_query', 36000);
	}
	return $related_posts_query;
}

function wpjam_get_related_posts($number=5, $args=array()){
	$related_posts_query = wpjam_get_related_posts_query($number);
	return wpjam_get_post_list($related_posts_query,$args);
}

function wpjam_related_posts($number=5, $args){
	if($output = wpjam_get_related_posts($number, $args)){
		echo $output;
	}
}

add_action('save_post','wpjam_delete_related_posts_cache');
function wpjam_delete_related_posts_cache($post_id){
	wp_cache_delete($post_id,'related_posts_query');
}

function wpjam_get_new_posts($number=5, $post_type="post", $args = array()){

	$paged = rand(0, 3);

	$wp_query_tags = array('post_type'=>$post_type, 'posts_per_page'=>$number, 'orderby'=> 'modified', 'paged'=>$paged);

	$new_posts_query = wpjam_query($wp_query_tags);

	return wpjam_get_post_list($new_posts_query,$args);
}

function wpjam_new_posts($number=5, $post_type="post", $args= array()){
	if($output = wpjam_get_new_posts($number, $post_type, $args)){
		echo $output;
	}
}

function wpjam_get_top_viewd_posts($number=5, $days=0, $args = array()){

	$paged = rand(0, 3);

	$post_types = apply_filters('wpjam_top_viewd_posts_post_types',array('post'));
	
	if($days){
		$date_query = array(
			array(
				'column' => 'post_modified_gmt',
				'after' => $days.' days ago',
			)
		);

		$wp_query_tags = array('post_type'=>$post_types, 'posts_per_page'=>$number, 'paged'=>$paged, 'orderby'=> 'meta_value_num', 'meta_key' => 'views', 'date_query'=>$date_query );
	}else{
		
		$wp_query_tags = array('post_type'=>$post_types, 'posts_per_page'=>$number, 'paged'=>$paged, 'orderby'=> 'meta_value_num', 'meta_key' => 'views' );
	}

	$top_viewd_posts_query = wpjam_query($wp_query_tags);

	return wpjam_get_post_list($top_viewd_posts_query,$args);
}

function wpjam_top_viewd_posts($number=5, $days=0, $args= array()){
	if($output = wpjam_get_top_viewd_posts($number, $days, $args)){
		echo $output;
	}
}

function wpjam_get_post_list($wpjam_query, $args){

	extract(wp_parse_args($args, array(
		'class'			=>'', 
		'thumb'			=> true,	
		'excerpt'		=> false, 
		'size'			=> 'thumbnail', 
		'crop'			=> true, 
		'thumb_class'	=>'wp-post-image',
	) ) );

	if($thumb)			$class		= $class.' has-thumb';
	if($class)			$class		= ' class="'.$class.'"';
	if(is_singular())	$post_id	= get_the_ID();

	$output = '';
	$i = 0;

	if($wpjam_query->have_posts()){
		while($wpjam_query->have_posts()){
			$wpjam_query->the_post();

			$li = '';

			if($thumb){ 
				$li .=	wpjam_get_post_thumbnail(null, $size, $crop, $thumb_class)."\n";		
				$li .=	'<h4>'.get_the_title().'</h4>';
			}else{
				$li .= get_the_title();
			}

			if($excerpt){
				$li .= '<p>'.get_the_excerpt().'</p>';
			}

			if(!is_singular() || (is_singular() && $post_id != get_the_ID())) {
				$li =	'<a href="'.get_permalink().'" title="'.the_title_attribute(array('echo'=>false)).'">'.$li.'</a>';
			}
			$output .=	'<li>'.$li.'</li>'."\n";
		}

		$output = '<ul'.$class.'>'."\n".$output.'</ul>'."\n";

	}else{
		$output = false;
	}

	wp_reset_postdata();
	return $output;	
}
