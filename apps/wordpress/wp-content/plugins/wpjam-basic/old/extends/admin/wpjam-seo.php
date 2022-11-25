<?php
add_filter('wpjam_basic_sub_pages','wpjam_basic_add_seo_sub_page');
function wpjam_basic_add_seo_sub_page($subs){
	$subs['wpjam-seo']	= array('menu_title'=>'SEO设置', 	'function'=>'option',	'option_name'=>'wpjam-basic');
	return $subs;
}

add_filter('wpjam-seo_sections', 'wpjam_seo_sections');
function wpjam_seo_sections($sections){
	
	$seo_fields = array(
		'seo_individual'		=> array('title'=>'独立 SEO 设置',		'type'=>'checkbox', 'description'=>'文章页面和分类页面独立的 SEO TDK 设置。'),
		'seo_robots'			=> array('title'=>'robots.txt',			'type'=>'textarea',	'description'=>'如果博客的根目录下已经有 robots.txt 文件，请先删除，否则这里设置的无法生效。'),
	);

	$home_fields = array(
		'seo_home_title'		=> array('title'=>'首页 SEO 标题',		'type'=>'text'),
		'seo_home_description'	=> array('title'=>'首页 SEO 描述',		'type'=>'textarea', 'rows'=>4	),
		'seo_home_keywords'		=> array('title'=>'首页 SEO Keywords',	'type'=>'text' ),
	);

	$sections = array( 
		'wpjam-seo'				=> array('title'=>'设置',		'fields'=>$seo_fields,	'callback'=>'wpjam_seo_section_callback'),
		'home-seo'				=> array('title'=>'首页',		'fields'=>$home_fields),
	);

	if(!is_multisite()){
		$post_types = get_post_types(array('public'   => true, '_builtin' => false, 'has_archive'=>true),'objects');
		if($post_types){
			foreach ($post_types as $post_type) {
				$post_type_object = get_post_type_object($post_type);
				if(!empty($post_type_object->seo_meta_box) || $post_type == 'post'){
					$post_type_fields = array(
						'seo_'.$post_type->name.'_title'		=> array('title'=>$post_type->label.' SEO 标题',		'type'=>'text'),
						'seo_'.$post_type->name.'_description'	=> array('title'=>$post_type->label.' SEO 描述',		'type'=>'textarea', 'rows'=>4	),
						'seo_'.$post_type->name.'_keywords'		=> array('title'=>$post_type->label.' SEO Keywords','type'=>'text' ),
					);

					$sections[$post_type->name.'-seo']	= array('title'=>$post_type->label, 'fields'=>$post_type_fields);
				}
			}
		}
	}
	
	return $sections;
}

function wpjam_seo_section_callback(){
	echo '<ol>';
	echo '
	<li>可以设置首页的TDK。</li>
	<li>如果没有单独设置，自动获取文章摘要作为文章页面的 Meta Description，可以将文章页面的 Tag 作为 Meta Keywords。</li>
	<li>如果没有单独设置，自动获取分类和 Tag 的描述作为分类和 Tag 页面的 Meta Description。</li>
	<li>如果博客支持并开启固定链接，自动生成 <a href="'.home_url('/robots.txt').'" target="_blank">robots.txt</a> 和 <a href="'.home_url('/sitemap.xml').'" target="_blank">sitemap.xml</a>。</li>
	';
	echo '</ol>';
}

add_filter('wpjam_post_options','wpjam_seo_post_options');
function wpjam_seo_post_options($wpjam_options){
	global $pagenow, $post_type;

	if(wpjam_basic_get_setting('seo_individual') ){
		$post_type_object = get_post_type_object($post_type);
		if(!empty($post_type_object->seo_meta_box) || $post_type == 'post'){

			$wpjam_options['wpjam-seo'] = array(
				'title'			=> 'SEO设置',
				'fields'		=> array(
					'seo_title'			=> array('title'=>'标题', 	'type'=>'text'),
					'seo_description'	=> array('title'=>'描述', 	'type'=>'textarea'),
					'seo_keywords'		=> array('title'=>'关键字',	'type'=>'text')
				)
			);
		}
	}
	return $wpjam_options;
}

add_filter('wpjam_term_options', 'wpjam_seo_term_options');
function wpjam_seo_term_options($wpjam_term_options){
	if(wpjam_basic_get_setting('seo_individual') ){
		$seo_taxonomies	= array();
		$taxonomies		= get_taxonomies(array( 'public' => true)); 
		foreach ($taxonomies as $taxonomy) {
			$taxonomy_object = get_taxonomy( $taxonomy );
			if(!empty($taxonomy_object->seo_meta_box) || $taxonomy == 'tag' || $taxonomy == 'category'){
				$seo_taxonomies[] = $taxonomy;
			}
		}

		if($seo_taxonomies){
			$wpjam_term_options['seo_title'] 		= array('title'=>'SEO 标题',		'taxonomies'=>$seo_taxonomies,	'type'=>'text');
			$wpjam_term_options['seo_description']	= array('title'=>'SEO 描述',		'taxonomies'=>$seo_taxonomies, 	'type'=>'textarea');
			$wpjam_term_options['seo_keywords']		= array('title'=>'SEO 关键字',	'taxonomies'=>$seo_taxonomies, 	'type'=>'text');
		}
	}
	return $wpjam_term_options;
}

add_filter('wpjam_taxonomy_args', 'wpjam_seo_taxonomy_args');
function wpjam_seo_taxonomy_args($taxonomy_args){
	$taxonomy_args['seo_meta_box'] = isset($taxonomy_args['seo_meta_box'])?$taxonomy_args['seo_meta_box']:true;
	return $taxonomy_args;
}

add_filter('wpjam_post_type_args', 'wpjam_seo_post_type_args');
function wpjam_seo_post_type_args($post_type_args){
	$post_type_args['seo_meta_box'] = isset($post_type_args['seo_meta_box'])?$post_type_args['seo_meta_box']:true;
	return $post_type_args;
}





