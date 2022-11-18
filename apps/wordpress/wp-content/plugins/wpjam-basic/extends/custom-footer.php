<?php
/*
Name: 文章页代码
URI: https://blog.wpjam.com/m/custom-post/
Description: 在文章编辑页面可以单独设置每篇文章 head 和 Footer 代码。
Version: 1.0
*/
if(is_admin()){
	wpjam_register_builtin_page_load('custom-post', [
		'base'		=> ['post','edit'], 
		'callback'	=> function($screen){
			if($screen->post_type != 'attachment' && is_post_type_viewable($screen->post_type)){
				wpjam_register_post_option('custom_post', [
					'title'			=> '文章页代码',
					'summary'		=> '自定义文章代码可以让你在当前文章插入独有的 JS，CSS，iFrame 等类型的代码，让你可以对具体一篇文章设置不同样式和功能，展示不同的内容。',
					'list_table'	=> wpjam_basic_get_setting('custom-post'),
					'fields'		=> [
						'custom_head'	=>['title'=>'头部代码',	'type'=>'textarea'],
						'custom_footer'	=>['title'=>'底部代码',	'type'=>'textarea']
					]
				]);
			}
		}
	]);

	wpjam_register_plugin_page_load('custom-post', [
		'plugin_page'	=> 'wpjam-posts', 
		'current_tab'	=> 'posts',
		'callback'		=> function(){
			add_filter('wpjam_option_setting_sections', function($sections){
				$sections['posts']['fields']['custom-post']	= ['title'=>'文章页代码',	'type'=>'select',	'options'=>[0=>'不在文章列表页设置文章页代码', 1=>'在文章列表页设置文章页代码', 'only'=>'只在文章列表页设置文章页代码']];

				return $sections;
			});
		}
	]);
}else{
	add_action('wp_footer', function (){
		if(is_singular()){
			echo get_post_meta(get_the_ID(), 'custom_footer', true);
		}
	});

	add_action('wp_head', function (){
		if(is_singular()){
			echo get_post_meta(get_the_ID(), 'custom_head', true);
		}
	});

	add_filter('wpjam_post_json', function($post_json, $post_id){
		if(is_singular()){
			$post_json['custom_head']	= (string)get_post_meta($post_id, 'custom_head', true);
			$post_json['custom_footer']	= (string)get_post_meta($post_id, 'custom_footer', true);
		}

		return $post_json;
	}, 10, 2);
}