<?php

// 注册自定义日志类型
add_action('init', 'wpjam_post_type_init', 11);
function wpjam_post_type_init(){
	$wpjam_post_types = wpjam_get_post_types();

	if(!$wpjam_post_types) return;

	global $wp_post_types;

	foreach ($wpjam_post_types as $post_type => $post_type_args) {
		$post_type_rewrite = isset($post_type_args['rewrite'])?$post_type_args['rewrite']:(isset($post_type_args['permastruct'])?true:false);

		if (is_array($post_type_rewrite)) {
			$post_type_rewrite	= wp_parse_args($post_type_rewrite, array('slug'=>$post_type, 'with_front'=>false, 'pages'=>true, 'feeds'=>false) );
		}else{
			$post_type_rewrite	= array('slug'=>$post_type, 'with_front'=>false, 'pages'=>true, 'feeds'=>false);
		}

		$post_type_args	= wpjam_parse_post_type_args($post_type, $post_type_args);
		register_post_type($post_type, $post_type_args);

		$wp_post_types[$post_type]->rewrite	= $post_type_rewrite;
	}
}

// 获取要注册自定义日志类型参数
function wpjam_get_post_types(){
	return apply_filters('wpjam_post_types', array());
}

function wpjam_get_post_type_args($post_type){
	$wpjam_post_types = wpjam_get_post_types();
	return isset($wpjam_post_types[$post_type]) ? wpjam_parse_post_type_args($post_type, $wpjam_post_types[$post_type]) : '';
}

// 自定义日志类型参数处理函数
function wpjam_parse_post_type_args($post_type, $post_type_args){

	$labels = isset($post_type_args['labels'])?$post_type_args['labels']:'';
	$label 	= isset($post_type_args['label'])?$post_type_args['label']:'';

	if(!$labels || is_string($labels)){
		$label_name	= ($labels) ? $labels : $label;
		$post_type_args['labels']  	= array(
			'name'					=> $label_name,
			'singular_name'			=> $label_name,
			'add_new'				=> '新增'.$label_name,
			'add_new_item'			=> '新增'.$label_name,
			'edit_item'				=> '编辑'.$label_name,
			'new_item'				=> '添加'.$label_name,
			'all_items'				=> '所有'.$label_name,
			'view_item'				=> '查看'.$label_name,
			'search_items'			=> '搜索'.$label_name,
			'not_found'				=> '找不到相关'.$label_name,
			'not_found_in_trash'	=> '回收站中没有'.$label_name, 
			'parent_item_colon'		=> '父级'.$label_name,
			'menu_name'				=> $label_name
		);
	}
	
	$permastruct	= isset($post_type_args['permastruct'])?$post_type_args['permastruct']:'';

	if($permastruct){
		wpjam_generate_post_type_rewrite_rules($post_type, $post_type_args);
		$post_type_args['rewrite'] = false;
	}

	return  apply_filters('wpjam_post_type_args', $post_type_args);
}

// 设置自定义类型的 Rewrite
function wpjam_generate_post_type_rewrite_rules($post_type, $post_type_args){
	global $wp_rewrite;

	$rewrite		= isset($post_type_args['rewrite'])?$post_type_args['rewrite']:false;
	$rewrite_slug	= isset($rewrite['slug'])?$rewrite['slug']:$post_type;

	$has_archive	= isset($post_type_args['has_archive'])?$post_type_args['has_archive']:false;

	if($has_archive){
		$archive_slug = $has_archive === true ? $rewrite_slug : $has_archive;
		$archive_slug = $wp_rewrite->root . $archive_slug;
		add_rewrite_rule( "{$archive_slug}/?$", "index.php?post_type=$post_type", 'top' );
		add_rewrite_rule( "{$archive_slug}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?post_type=$post_type" . '&paged=$matches[1]', 'top' );
	}

	$permastruct_args = array(
		'ep_mask' 		=> isset($post_type_args['permalink_epmask'])?$post_type_args['permalink_epmask']:EP_PERMALINK,
		'with_front'	=> false,
		'paged' 		=> false,
		'feed' 			=> false,
		'forcomments'	=> false,
		'walk_dirs'		=> false,
		'endpoints'		=> false,
	);

	$permastruct	= isset($post_type_args['permastruct'])?$post_type_args['permastruct']:'';

	if(strpos($permastruct, "%post_id%") || strpos($permastruct, "%{$post_type}_id%")){
		$permastruct	= str_replace("%post_id%", "%{$post_type}_id%", $permastruct); 
		add_rewrite_tag( "%{$post_type}_id%", '([0-9]+)', "post_type=$post_type&p=" );
	}else{
		$query_var		= isset($post_type_args['query_var'])?$post_type_args['query_var']:'';
		$hierarchical	= isset($post_type_args['hierarchical'])?$post_type_args['hierarchical']:'';

		if ( false !== $query_var ) {
			if ( true === $query_var ){
				$query_var	= $post_type;
			}else{
				$query_var	= sanitize_title_with_dashes( $query_var );
			}
		}

		if ( $hierarchical ){
			add_rewrite_tag( "%$post_type%", '(.+?)', $query_var ? "{$query_var}=" : "post_type=$post_type&pagename=" );
		}else{
			add_rewrite_tag( "%$post_type%", '([^/]+)', $query_var ? "{$query_var}=" : "post_type=$post_type&name=" );
		}
	}

	add_permastruct( $post_type, $permastruct, $permastruct_args);
}

// 设置自定义日志的链接
add_filter('post_type_link', 'wpjam_post_type_link', 1, 2);
function wpjam_post_type_link( $post_link, $post ){

	$post_type	= $post->post_type;
	$post_id	= $post->ID;

	$post_type_args	= wpjam_get_post_type_args($post_type);
	if(!$post_type_args) return $post_link;

	$permastruct	= isset($post_type_args['permastruct'])?$post_type_args['permastruct']:'';
	if(!$permastruct)	return $post_link;

	$post_link	= str_replace( '%'.$post_type.'_id%', $post_id, $post_link );

	$taxonomies = get_taxonomies(array('object_type'=>array($post_type)), 'objects');

	if($taxonomies){
		foreach ($taxonomies as $taxonomy=>$taxonomy_object) {
			if($taxonomy_rewrite = $taxonomy_object->rewrite){
				$terms = get_the_terms( $post_id, $taxonomy );
				if($terms){
					$term = current($terms);
					$post_link	= str_replace( '%'.$taxonomy_rewrite['slug'].'%', $term->slug, $post_link );
				}else{
					$post_link	= str_replace( '%'.$taxonomy_rewrite['slug'].'%', $taxonomy, $post_link );
				}
			}
		}
	}

	return $post_link;
}

// 在后台特色图片下面显示最佳图片大小
add_filter('admin_post_thumbnail_html', 'wpjam_admin_post_thumbnail_html',10,2);
function wpjam_admin_post_thumbnail_html($content, $post_id){
	$post		= get_post($post_id);
	$post_type	= $post->post_type;

	$post_type_args	= wpjam_get_post_type_args($post_type);
	if(!$post_type_args) return $content;

	$thumbnail_size	= isset($post_type_args['thumbnail_size'])?$post_type_args['thumbnail_size']:'';
	if(!$thumbnail_size)	return $content;

	return $content.'<p>大小：'.$thumbnail_size.'</p>';
}