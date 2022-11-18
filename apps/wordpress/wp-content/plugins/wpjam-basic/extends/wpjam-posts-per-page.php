<?php
/*
Name: 文章数量
URI: https://blog.wpjam.com/m/wpjam-posts-per-page/
Description: 设置不同页面不同的文章列表数量，不同的分类不同文章列表数量。
Version: 1.0
*/
class WPJAM_Posts_Per_Page extends WPJAM_Option_Model{
	public static function sanitize_callback($value){
		foreach (['posts_per_page', 'posts_per_rss'] as $option_name) {
			if(isset($value[$option_name])){
				if($value[$option_name]){
					update_option($option_name, $value[$option_name]);
				}

				unset($value[$option_name]);
			}
		}
	}

	public static function get_fields(){
		$fields	= [];

		$fields['posts_per_page']	= ['title'=>'全局',	'type'=>'number',	'value'=>get_option('posts_per_page'),	'description'=>'博客全局设置的文章列表数量'];

		if($custom_ptypes = get_post_types(['exclude_from_search'=>false, '_builtin'=>false], 'objects')){
			$options		= ['post'=>'文章']+wp_list_pluck($custom_ptypes, 'label');
			$number_field 	= ['title'=>'文章数量',	'type'=>'number'];
			$ptype_field 	= ['title'=>'文章类型',	'type'=>'checkbox',	'value'=>['post'],	'options'=>$options];

			$fields['posts_per_rss_set']	= ['title'=>'Feed页',	'type'=>'fieldset',	'fields'=>[
				'posts_per_rss'				=> $number_field+['value'=>get_option('posts_per_rss')],
				'posts_per_rss_post_types'	=> $ptype_field,
			]];

			$fields['home_set']	= ['title'=>'首页',	'type'=>'fieldset',	'fields'=>[
				'home'				=> $number_field,
				'home_post_types'	=> $ptype_field,
			]];

			$fields['author_set']	= ['title'=>'作者页',	'type'=>'fieldset',	'fields'=>[
				'author'			=> $number_field,
				'author_post_types'	=> $ptype_field,
			]];

			$fields['search']	= ['title'=>'搜索页',	'type'=>'number'];
			$fields['archive']	= ['title'=>'存档页',	'type'=>'number'];
		}else{
			$fields['posts_per_rss']	= ['title'=>'Feed页',	'type'=>'number',	'value'=>get_option('posts_per_rss'),	'description'=>'Feed中最近文章列表数量'];

			foreach(['home'=>'首页','author'=>'作者页','search'=>'搜索页','archive'=>'存档页'] as $page_key=>$page_name){
				$fields[$page_key]	= ['title'=>$page_name,	'type'=>'number'];
			}
		}

		if($taxonomies = wpjam_array_except(get_taxonomies(['public'=>true,'show_ui'=>true],'objects'), 'series')){
			foreach(wp_list_sort($taxonomies, 'hierarchical', 'DESC', true) as $taxonomy=>$taxonomy_obj){
				$sub_fields	= [];

				$sub_fields[$taxonomy]	= ['title'=>'',	'type'=>'number'];

				if($taxonomy_obj->hierarchical){
					$sub_fields[$taxonomy.'_individual']	= ['title'=>'',	'type'=>'checkbox',	'description'=>'每个'.$taxonomy_obj->label.'可独立设置数量'];
				}

				$fields[$taxonomy.'_set']	= ['title'=>$taxonomy_obj->label,	'type'=>'fieldset',	'fields'=>$sub_fields];
			}
		}

		if($post_types = get_post_types(['public'=>true, 'has_archive'=>true],'objects')){
			$sub_fields = [];
			foreach ($post_types as $post_type=>$pt_obj) {
				$sub_fields[$post_type]	= ['title'=>$pt_obj->label,	'type'=>'number'];
			}

			if(count($post_types) == 1){
				$field	= $sub_fields[$post_type];
				$field['title']		.= '存档页';
				$fields[$post_type]	= $field;
			}else{
				$fields['post_type']	= ['title'=>'文章类型存档页',	'type'=>'fieldset',	'fields'=>$sub_fields];
			}
		}

		return $fields;
	}

	public static function get_menu_page(){
		return [
			'tab_slug'		=> 'posts-per-page',
			'title'			=> '文章数量',	
			'function'		=> 'option',	
			'option_name'	=> 'wpjam-posts-per-page',
			'plugin_page'	=> 'wpjam-posts',
			'order'			=> 18,
			'summary'		=> '文章数量扩展可以设置不同页面不同的文章列表数量和文章类型，也可开启不同的分类不同文章列表数量。<br />空或者0则使用全局设置，详细介绍请点击：<a href="https://blog.wpjam.com/m/wpjam-posts-per-page/" target="_blank">文章数量扩展</a>。'
		];
	}

	public static function filter_term_row_actions($actions, $term){
		if($posts_per_page = get_term_meta($term->term_id, 'posts_per_page', true)){
			$actions['posts_per_page']	= str_replace('>文章数量<', '>文章数量'.'（'.$posts_per_page.'）'.'<', $actions['posts_per_page']);
		}

		return $actions;
	}

	public static function on_pre_get_posts($wp_query) {
		if(!$wp_query->is_main_query()){
			return;
		}

		if(isset($wp_query->query['post_type'])){
			$required	= false;
		}else{
			$required	= (bool)get_post_types(['exclude_from_search'=>false, '_builtin'=>false], 'objects');
		}

		if(is_front_page()){
			$number		= self::get_setting('home');
			$post_types	= $required ? self::get_setting('home_post_types') : [];
		}elseif(is_feed()){
			$post_types	= $required ? self::get_setting('feed_post_types') : [];
		}elseif(is_author()){
			$number		= self::get_setting('author');
			$post_types	= $required ? self::get_setting('author_post_types') : [];
		}elseif(is_tax() || is_category() || is_tag()){
			if($term = $wp_query->get_queried_object()){
				$taxonomy	= $term->taxonomy;

				$number		= self::get_setting($taxonomy);
				$individual	= self::get_setting($taxonomy.'_individual');

				if($individual && metadata_exists('term', $term->term_id, 'posts_per_page')){
					$number	= get_term_meta($term->term_id, 'posts_per_page', true);
				}

				if(is_category() || is_tag()){
					$post_types	= get_taxonomy($taxonomy)->object_type;
					$post_types	= array_intersect($post_types, get_post_types(['public'=>true]));
				}
			}
		}elseif(is_post_type_archive()){
			$ptype_obj	= $wp_query->get_queried_object();
			$number		= self::get_setting($ptype_obj->name);
		}elseif(is_search()){
			$number		= self::get_setting('search');
		}elseif(is_archive()){
			$number		= self::get_setting('archive');
			$post_types	= 'any';
		}

		if(!empty($number)){
			$wp_query->set('posts_per_page', $number);
		}

		if(!isset($wp_query->query['post_type']) && !empty($post_types)){
			if(is_array($post_types) && count($post_types) == 1) {
				$post_types	= $post_types[0];
			}

			$wp_query->set('post_type', $post_types);
		}
	}

	public static function builtin_page_load($screen){
		if(is_taxonomy_hierarchical($screen->taxonomy) 
			&& self::get_setting($screen->taxonomy.'_individual')
		){
			$default	= self::get_setting($screen->taxonomy) ?: get_option('posts_per_page');

			wpjam_register_list_table_action('posts_per_page',[
				'title'			=> '文章数量',
				'page_title'	=> '设置文章数量',
				'submit_text'	=> '设置',
				'width'			=> 400,
				'fields'		=> [
					'default'			=> ['title'=>'默认数量',	'type'=>'view',		'value'=>$default],
					'posts_per_page'	=> ['title'=>'文章数量',	'type'=>'number',	'class'=>'']
				]
			]);

			add_filter($screen->taxonomy.'_row_actions', [self::class, 'filter_term_row_actions'], 10, 2);	
		}
	}

	public static function init(){
		if(is_admin()){
			if(!is_network_admin() && !is_user_admin()){
				wpjam_register_builtin_page_load('posts-per-page', [
					'base'		=> ['edit-tags', 'term'], 
					'callback'	=> [self::class, 'builtin_page_load']
				]);
			}
		}else{
			add_action('pre_get_posts',  [self::class, 'on_pre_get_posts']);
		}
	}
}

wpjam_register_option('wpjam-posts-per-page',	['model'=>'WPJAM_Posts_Per_Page',]);
