<?php
/*
Name: 优化设置
URI: https://mp.weixin.qq.com/s/zkA0Nx4u81PCZWByQq3iiA
Description: 优化设置通过屏蔽和增强功能来加快 WordPress 的加载
Version: 2.0
*/
class WPJAM_Basic extends WPJAM_Option_Model{
	public static function get_sections(){
		if($GLOBALS['plugin_page'] == 'wpjam-basic'){
			return [
				'disabled'	=>['title'=>'功能屏蔽',	'fields'=>self::get_fields('disabled')],
				'enhance'	=>['title'=>'增强优化',	'fields'=>self::get_fields('enhance')],
			];
		}elseif($GLOBALS['plugin_page'] == 'wpjam-posts'){
			return [
				'posts'		=>['title'=>'文章设置',	'fields'=>self::get_fields('posts')]
			];
		}
	}

	public static function get_fields($type){
		$fields	= [];

		if($type == 'disabled'){
			return self::parse_fields([
				'disable_revision'			=>['title'=>'屏蔽文章修订',		'short'=>'S9PBDUtk0jax7eL5kDFiQg',	'description'=>'屏蔽文章修订功能，精简文章表数据。'],
				'disable_trackbacks'		=>['title'=>'屏蔽Trackbacks',	'short'=>'FZ7zOYOTnqo65U-lx6QpYw',	'description'=>'彻底关闭Trackbacks，防止垃圾留言。'],
				'disable_emoji'				=>['title'=>'屏蔽Emoji图片',		'short'=>'BMYGDB7GfK5rb4PlwD5xIg',	'description'=>'屏蔽Emoji图片转换功能，直接使用Emoji。'],
				'disable_texturize'			=>['title'=>'屏蔽字符转码',		'short'=>'9sSXaK5r5XO7xB-3yjV1zQ',	'description'=>'屏蔽字符换成格式化的HTML实体功能。'],
				'disable_feed'				=>['title'=>'屏蔽站点Feed',		'short'=>'YgJT8Mlhv08p9lvVLC5L1Q',	'description'=>'屏蔽站点Feed，防止文章被快速被采集。'],
				'disable_admin_email_check'	=>['title'=>'屏蔽邮箱验证',		'short'=>'GUPxPQQo3Qa2AMuKuM7CzQ',	'description'=>'屏蔽站点管理员邮箱定期验证功能。'],
				'disable_auto_update'		=>['title'=>'屏蔽自动更新',		'short'=>'bxVdrLhGo075s4TKmi6p3A',	'description'=>'关闭自动更新功能，通过手动或SSH方式更新。'],
				'disable_privacy'			=>['title'=>'屏蔽后台隐私',		'short'=>'aXx1ggscmzvxAgo4tM9bWQ',	'description'=>'移除为欧洲通用数据保护条例而生成的隐私页面。'],
				'disable_autoembed'			=>['title'=>'屏蔽Auto Embeds',	'short'=>'cg5cVPGj2Hwq0Jxn7DJw6Q',	'description'=>'禁用Auto Embeds功能，加快页面解析速度。'],
				'disable_post_embed'		=>['title'=>'屏蔽文章Embed',		'short'=>'Flm2ggE3VE-qcoZnY4Nycg',	'description'=>'屏蔽嵌入其他WordPress文章的Embed功能。'],
				'disable_block_editor'		=>['title'=>'屏蔽古腾堡编辑器',	'short'=>'LsVZX7p64-fcICwwA6IEQw',	'description'=>'屏蔽Gutenberg编辑器，换回经典编辑器。'],
				'disable_xml_rpc'			=>['title'=>'屏蔽XML-RPC',		'short'=>'hYAe_a497ZkkwlkM-cFsOg',	'description'=>'关闭XML-RPC功能，只在后台发布文章。']
			]);
		}elseif($type == 'enhance'){
			$x_frame_options	= [''=>'所有网页', 'SAMEORIGIN'=>'只允许同域名网页', 'DENY'=>'不允许任何网页'];

			return self::parse_fields([
				'google_fonts_set'		=>['title'=>'Google字体加速','type'=>'fieldset',	'short'=>'Sz0QlZ-kW0C70NkbpoDpag',	'fields'=>WPJAM_Google_Font::get_fields()],
				'gravatar_set'			=>['title'=>'Gravatar加速',	'type'=>'fieldset',	'short'=>'eyHr2r-vrqSqADwMkPh95Q',	'fields'=>WPJAM_Gravatar::get_fields()],
				'x-frame-options'		=>['title'=>'Frame嵌入',		'short'=>'P__N3Srj-4WvZdkLORlBRg',	'options'=>$x_frame_options],
				'no_category_base_set'	=>['title'=>'分类链接简化',	'type'=>'fieldset',	'group'=>true,	'fields'=>self::get_fields('no_category_base')],
				'timestamp_file_name'	=>['title'=>'图片时间戳',		'short'=>'yodHw7W-qlnkbYqBPbHTEA',	'description'=>'给上传的图片加上时间戳，防止大量的SQL查询。'],
				'frontend_set'			=>['title'=>'前台页面优化',	'type'=>'fieldset',	'fields'=>self::get_fields('frontend')],
				'backend_set'			=>['title'=>'后台页面优化',	'type'=>'fieldset',	'fields'=>self::get_fields('backend')],
				'optimized_by_wpjam'	=>['title'=>'WPJAM Basic',	'description'=>'在网站底部显示：Optimized by WPJAM Basic。']
			]);
		}elseif($type == 'frontend'){
			return [
				'remove_head_links'			=>['short'=>'7W119jeuzNNehWzokGEb3A',	'description'=>'移除页面头部版本号和服务发现标签代码。'],
				'remove_admin_bar'			=>['short'=>'_YpFZ4gttJObcD7-CbWNhg',	'description'=>'移除工具栏和后台个人资料中工具栏相关的选项。'],
				'remove_capital_P_dangit'	=>['short'=>'0tVQbq9cWvFjpcDYwSVY_w',	'description'=>'移除 WordPress 大小写修正，自己决定怎么写。'],
			];
		}elseif($type == 'backend'){
			return [
				'remove_help_tabs'			=>['short'=>'p6HWVHIos2h5h6M78Lbclw',	'description'=>'移除后台界面右上角的帮助。'],
				'remove_screen_options'		=>['short'=>'p6HWVHIos2h5h6M78Lbclw',	'description'=>'移除后台界面右上角的选项。'],
			];
		}elseif($type == 'no_category_base'){
			if($GLOBALS['wp_rewrite']->use_verbose_page_rules){
				$fields['no_category_base']	= ['type'=>'view',	'value'=>'站点当前的固定链接设置不支持去掉分类目录链接中的 category，请先修改固定链接设置。'];
			}else{
				$fields['no_category_base']	= ['short'=>'AD0w1d8NLibtc6CFYsLiJg',	'description'=>'去掉分类目录链接中的 category。'];

				$taxonomies	= get_taxonomies(['public'=>true,'hierarchical'=>true], 'objects');

				if(count($taxonomies) > 1){
					$options	= array_column($taxonomies, 'label', 'name');

					$fields['no_category_base_for']	= ['title'=>'分类模式：',	'show_if'=>['key'=>'no_category_base','value'=>1],	'options'=>$options];
				}else{
					$fields['no_category_base_for']	= ['type'=>'hidden',	'value'=>array_key_first($taxonomies)];
				}
			}
		}elseif($type == 'posts'){
			return self::parse_fields([
				'post_list_fieldset'	=> ['title'=>'后台列表',	'type'=>'fieldset',	'fields'=>self::get_fields('post_list')],
				'excerpt_fieldset'		=> ['title'=>'文章摘要',	'type'=>'fieldset',	'fields'=>self::get_fields('excerpt')],
				'remove_post_tag'		=> ['title'=>'移除标签',	'value'=>0,	'description'=>'移除默认文章类型的标签功能支持'],
				'404_optimization'		=> ['title'=>'404 跳转',	'value'=>0,	'description'=>'增强404页面跳转到文章页面能力']
			]);
		}elseif($type == 'post_list'){
			return [
				'post_list_ajax'			=> ['value'=>1,	'description'=>'支持全面的 <strong>AJAX操作</strong>'],
				'post_list_set_thumbnail'	=> ['value'=>1,	'description'=>'显示和设置<strong>文章缩略图</strong>'],
				'post_list_sort_selector'	=> ['value'=>1,	'description'=>'显示<strong>排序下拉选择框</strong>'],
				'post_list_author_filter'	=> ['value'=>1,	'description'=>'支持<strong>通过作者进行过滤</strong>'],
				'upload_external_images'	=> ['value'=>0,	'description'=>'支持<strong>上传外部图片</strong>'],
			];
		}elseif($type == 'excerpt'){
			$excerpt_show_if	= ['key'=>'excerpt_optimization', 'value'=>1];
			$excerpt_options	= [0=>'WordPress 默认方式截取', 1=>'按照中文最优方式截取', 2=>'直接不显示摘要'];

			return [
				'excerpt_optimization'	=> ['title'=>'未设文章摘要：',	'options'=>$excerpt_options],
				'excerpt_length'		=> ['title'=>'文章摘要长度：',	'type'=>'number',	'show_if'=>$excerpt_show_if,	'value'=>200],
				'excerpt_cn_view2'		=> ['title'=>'中文截取算法：',	'type'=>'view',		'show_if'=>$excerpt_show_if,	'short'=>'QB6zUXA_QI1lseAfNV29Lg',	'value'=>'<strong>中文算2个字节，英文算1个字节</strong>']
			];
		}

		return $fields;
	}

	public static function parse_fields($fields){
		foreach($fields as &$field){
			if(empty($field['type']) && !isset($field['options'])){
				$field['type']	= 'checkbox';
			}

			$field['type']	= $field['type'] ?? '';

			if($field['type'] == 'fieldset'){
				$field['fields']	= self::parse_fields($field['fields']);
			}

			if($short = wpjam_array_pull($field, 'short')){
				$link	= 'https://mp.weixin.qq.com/s/'.$short;
				$key	= $field['type'] == 'view' ? 'value' : 'description';

				if(isset($field[$key])){
					$field[$key]	= '<a target="_blank" href="'.$link.'">'.$field[$key].'</a>';
				}else{
					$field['title']	.= '<a target="_blank" href="'.$link.'" class="dashicons-before dashicons-editor-help"></a>';
				}
			}
		}

		return $fields;
	}

	public static function sanitize_callback(){
		flush_rewrite_rules();
	}

	public static function get_menu_page(){
		return [
			'menu_title'	=> 'WPJAM',
			'icon'			=> 'dashicons-performance',
			'position'		=> '58.99',
			'network'		=> true,
			'subs'			=> ['wpjam-basic'=>[
				'menu_title'	=> '优化设置',
				'function'		=> 'option',
				'summary'		=> __FILE__,
			]]
		];
	}

	public static function get_defaults(){
		return [
			'disable_revision'			=> 1,
			'disable_trackbacks'		=> 1,
			'disable_emoji'				=> 1,
			'disable_texturize'			=> 1,
			'disable_privacy'			=> 1,
			'remove_head_links'			=> 1,
			'remove_capital_P_dangit'	=> 1,
		];
	}

	public static function get_setting($name='', $default=null){
		$value	= parent::get_setting($name, $default);

		if($name == 'no_category_base'){
			if(!$value || $GLOBALS['wp_rewrite']->use_verbose_page_rules){
				return '';
			}

			return self::get_setting('no_category_base_for', 'category');
		}

		return $value;
	}

	public static function filter_html($html){
		$name	= self::get_setting('google_fonts');

		if($name){
			if($name == 'custom'){
				$replace = [];

				foreach(WPJAM_Google_Font::get_domains() as $font_key => $domain){
					$replace[]	= str_replace(['http://','https://'], '//', self::get_setting($font_key));
				}

				WPJAM_Google_Font::register($name, ['title'=>'自定义', 'replace'=>$replace]);
			}

			$object	= WPJAM_Google_Font::get($name);

			if($object){
				$html	= $object->replace($html);
			}
		}

		return apply_filters('wpjam_html', $html);
	}

	public static function filter_avatar_url($url){
		$name	= self::get_setting('gravatar');

		if($name){
			if($name == 'custom'){
				$custom = self::get_setting('gravatar_custom');

				if($custom){
					WPJAM_Gravatar::register($name,	['title'=>'自定义', 'url'=>$custom]);
				}
			}

			$object	= WPJAM_Gravatar::get($name);

			return $object ? $object->replace($url) : $url;
		}

		return $url;
	}

	public static function filter_loader_src($src){
		return remove_query_arg('ver', $src);
	}

	public static function filter_pre_upload($file){
		if(self::get_setting('timestamp_file_name')){
			return array_merge($file, ['name'=> time().'-'.$file['name']]);
		}

		return $file;
	}

	public static function filter_register_post_type_args($args, $post_type){
		if(did_action('init') && !empty($args['supports']) && is_array($args['supports'])){
			foreach(['trackbacks'=>'disable_trackbacks', 'revisions'=>'disable_revision'] as $support => $setting_name){
				if(self::get_setting($setting_name, 1) && in_array($support, $args['supports'])){
					$args['supports']	= array_diff($args['supports'], [$support]);

					remove_post_type_support($post_type, $support);	// create_initial_post_types 会执行两次
				}
			}
		}

		return $args;
	}

	public static function filter_request($query_vars){
		$taxonomy	= self::get_setting('no_category_base');

		if($taxonomy
			&& !isset($query_vars['module'])
			&& !empty($query_vars['pagename'])
			&& !isset($_GET['page_id'])
			&& !isset($_GET['pagename'])
		){
			$pagename	= wp_basename(strtolower($query_vars['pagename']));

			$term_slugs	= get_categories([
				'taxonomy'		=> $taxonomy,
				'fields'		=> 'slugs',
				'hide_empty'	=> false,
			]);

			if($term_slugs && in_array($pagename, $term_slugs)){
				unset($query_vars['pagename']);

				if($taxonomy == 'category'){
					$query_vars['category_name']	= $pagename;
				}else{
					$query_vars['taxonomy']	= $taxonomy;
					$query_vars['term']		= $pagename;
				}
			}
		}

		return $query_vars;
	}

	public static function filter_pre_term_link($term_link, $term){
		if($term->taxonomy == self::get_setting('no_category_base')){
			return '%'.$term->taxonomy.'%';
		}

		return $term_link;
	}

	public static function filter_old_slug_redirect_post_id($post_id){
		// WP 原始解决函数 'wp_old_slug_redirect' 和 'redirect_canonical'
		if(empty($post_id) && self::get_setting('404_optimization')){
			$post	= WPJAM_Post::find_by_name(get_query_var('name'), get_query_var('post_type'));

			return $post ? $post->ID : $post_id;
		}

		return $post_id;
	}

	public static function filter_get_the_excerpt($text='', $post=null){
		$optimization	= self::get_setting('excerpt_optimization');

		if(empty($text) && $optimization){
			remove_filter('get_the_excerpt', 'wp_trim_excerpt');

			if($optimization != 2){
				remove_filter('the_excerpt', 'wp_filter_content_tags');
				remove_filter('the_excerpt', 'shortcode_unautop');

				$length	= self::get_setting('excerpt_length') ?: 200;
				$text	= wpjam_get_post_excerpt($post, $length);
			}
		}

		return $text;
	}

	public static function filter_update_attachment_metadata($data){
		if(isset($data['thumb'])){
			$data['thumb'] = basename($data['thumb']);
		}

		return $data;
	}

	public static function init(){
		if(self::get_setting('disable_trackbacks', 1)){
			$GLOBALS['wp']->remove_query_var('tb');
		}

		if(self::get_setting('disable_post_embed')){
			$GLOBALS['wp']->remove_query_var('embed');
		}

		if(self::get_setting('remove_post_tag')){
			unregister_taxonomy_for_object_type('post_tag', 'post');
		}
	}
}

wpjam_register_option('wpjam-basic', [
	'site_default'	=> true,
	'model'			=> 'WPJAM_Basic',
]);

function wpjam_basic_get_setting($name, $default=null){
	return WPJAM_Basic::get_setting($name, $default);
}

function wpjam_basic_update_setting($name, $value){
	return WPJAM_Basic::update_setting($name, $value);
}

function wpjam_basic_delete_setting($name){
	return WPJAM_Basic::delete_setting($name);
}

function wpjam_basic_get_default_settings(){
	return WPJAM_Basic::get_defaults();
}

function wpjam_add_basic_sub_page($sub_slug, $args=[]){
	wpjam_add_menu_page($sub_slug, array_merge($args, ['parent'=>'wpjam-basic']));
}

add_filter('register_post_type_args',	['WPJAM_Basic', 'filter_register_post_type_args'], 10, 2);
add_filter('old_slug_redirect_post_id',	['WPJAM_Basic', 'filter_old_slug_redirect_post_id']);	// 解决日志改变 post type 之后跳转错误的问题

// 修正任意文件删除漏洞
add_filter('wp_update_attachment_metadata',	['WPJAM_Basic', 'filter_update_attachment_metadata']);

// 防止重名造成大量的 SQL 请求
add_filter('wp_handle_sideload_prefilter',	['WPJAM_Basic', 'filter_pre_upload']);
add_filter('wp_handle_upload_prefilter',	['WPJAM_Basic', 'filter_pre_upload']);

add_filter('get_avatar_url',	['WPJAM_Basic', 'filter_avatar_url']);

// 优化文章摘要
add_filter('get_the_excerpt',	['WPJAM_Basic', 'filter_get_the_excerpt'], 9, 2);

// 去掉URL中category
add_filter('request',		['WPJAM_Basic', 'filter_request']);
add_filter('pre_term_link',	['WPJAM_Basic', 'filter_pre_term_link'], 1, 2);

add_action('wp_loaded', function(){
	ob_start(['WPJAM_Basic', 'filter_html']);
});

add_action('template_redirect', function(){
	if(is_feed()){
		if(wpjam_basic_get_setting('disable_feed')){	// 屏蔽站点 Feed
			wp_die('Feed已经关闭, 请访问<a href="'.get_bloginfo('url').'">网站首页</a>！', 'Feed关闭'	, 200);
		}
	}else{
		$taxonomy	= WPJAM_Basic::get_setting('no_category_base');

		if($taxonomy){	// 开启去掉URL中category，跳转到 no base 的 link
			if((is_category() && $taxonomy == 'category') || is_tax($taxonomy)){
				if(strpos($_SERVER['REQUEST_URI'], '/'.$taxonomy.'/') !== false){
					wp_redirect(site_url(str_replace('/'.$taxonomy, '', $_SERVER['REQUEST_URI'])), 301);
					exit;
				}
			}
		}
	}
});

//移除 WP_Head 无关紧要的代码
if(wpjam_basic_get_setting('remove_head_links', 1)){
	add_filter('the_generator', '__return_empty_string');

	add_filter('style_loader_src',	['WPJAM_Basic', 'filter_loader_src']);
	add_filter('script_loader_src',	['WPJAM_Basic', 'filter_loader_src']);

	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );

	remove_action( 'wp_head', 'feed_links_extra', 3 );
	//remove_action( 'wp_head', 'feed_links', 2 );

	remove_action( 'wp_head', 'index_rel_link' );
	remove_action( 'wp_head', 'parent_post_rel_link', 10);
	remove_action( 'wp_head', 'start_post_rel_link', 10);
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10);

	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10);

	remove_action( 'template_redirect',	'wp_shortlink_header', 11);
	remove_action( 'template_redirect',	'rest_output_link_header', 11);
}

//让用户自己决定是否书写正确的 WordPress
if(wpjam_basic_get_setting('remove_capital_P_dangit', 1)){
	remove_filter( 'the_content', 'capital_P_dangit', 11 );
	remove_filter( 'the_title', 'capital_P_dangit', 11 );
	remove_filter( 'wp_title', 'capital_P_dangit', 11 );
	remove_filter( 'document_title', 'capital_P_dangit', 11 );
	remove_filter( 'comment_text', 'capital_P_dangit', 31 );
	remove_filter( 'widget_text_content', 'capital_P_dangit', 11 );
}

// 屏蔽字符转码
if(wpjam_basic_get_setting('disable_texturize', 1)){
	add_filter('run_wptexturize', '__return_false');
}

//移除 admin bar
if(wpjam_basic_get_setting('remove_admin_bar')){
	add_filter('show_admin_bar', '__return_false');
}

//禁用 XML-RPC 接口
if(wpjam_basic_get_setting('disable_xml_rpc')){
	add_filter('xmlrpc_enabled', '__return_false');
	add_filter('xmlrpc_methods', '__return_empty_array');
	remove_action('xmlrpc_rsd_apis', 'rest_output_rsd');
}

// 屏蔽古腾堡编辑器
if(wpjam_basic_get_setting('disable_block_editor')){
	remove_action('wp_enqueue_scripts', 'wp_common_block_scripts_and_styles');
	remove_action('admin_enqueue_scripts', 'wp_common_block_scripts_and_styles');
	remove_filter('the_content', 'do_blocks', 9);
}

// 屏蔽站点管理员邮箱验证功能
if(wpjam_basic_get_setting('disable_admin_email_check')){
	add_filter('admin_email_check_interval', '__return_false');
}

// 屏蔽 Emoji
if(wpjam_basic_get_setting('disable_emoji', 1)){
	add_action('admin_init', function(){
		remove_action('admin_print_scripts',	'print_emoji_detection_script');
		remove_action('admin_print_styles',		'print_emoji_styles');
	});

	remove_action('wp_head',			'print_emoji_detection_script',	7);
	remove_action('wp_print_styles',	'print_emoji_styles');

	remove_action('embed_head',			'print_emoji_detection_script');

	remove_filter('the_content_feed',	'wp_staticize_emoji');
	remove_filter('comment_text_rss',	'wp_staticize_emoji');
	remove_filter('wp_mail',			'wp_staticize_emoji_for_email');

	add_filter('emoji_svg_url',		'__return_false');

	add_filter('tiny_mce_plugins',	function($plugins){
		return array_diff($plugins, ['wpemoji']);
	});
}

//禁用文章修订功能
if(wpjam_basic_get_setting('disable_revision', 1)){
	if(!defined('WP_POST_REVISIONS')){
		define('WP_POST_REVISIONS', false);
	}

	remove_action('pre_post_update', 'wp_save_post_revision');
}

// 屏蔽Trackbacks
if(wpjam_basic_get_setting('disable_trackbacks', 1)){
	if(!wpjam_basic_get_setting('disable_xml_rpc')){
		//彻底关闭 pingback
		add_filter('xmlrpc_methods', function($methods){
			return array_merge($methods, [
				'pingback.ping'						=> '__return_false',
				'pingback.extensions.getPingbacks'	=> '__return_false'
			]);
		});
	}

	//禁用 pingbacks, enclosures, trackbacks
	remove_action('do_pings', 'do_all_pings', 10);

	//去掉 _encloseme 和 do_ping 操作。
	remove_action('publish_post','_publish_post_hook',5);
}

//禁用 Auto OEmbed
if(wpjam_basic_get_setting('disable_autoembed')){
	remove_filter('the_content',			[$GLOBALS['wp_embed'], 'autoembed'], 8);
	remove_filter('widget_text_content',	[$GLOBALS['wp_embed'], 'autoembed'], 8);
	remove_filter('widget_block_content',	[$GLOBALS['wp_embed'], 'autoembed'], 8);
}

// 屏蔽文章Embed
if(wpjam_basic_get_setting('disable_post_embed')){
	remove_action('wp_head', 'wp_oembed_add_discovery_links');
	remove_action('wp_head', 'wp_oembed_add_host_js');
}

// 屏蔽自动更新和更新检查作业
if(wpjam_basic_get_setting('disable_auto_update')){
	add_filter('automatic_updater_disabled', '__return_true');

	remove_action('init', 'wp_schedule_update_checks');
	remove_action('wp_version_check', 'wp_version_check');
	remove_action('wp_update_plugins', 'wp_update_plugins');
	remove_action('wp_update_themes', 'wp_update_themes');
}

add_action('send_headers', function(){
	$x_frame_options = wpjam_basic_get_setting('x-frame-options');

	if($x_frame_options){
		header('X-Frame-Options: '.$x_frame_options);
	}
});

// 屏蔽后台隐私
if(wpjam_basic_get_setting('disable_privacy', 1)){
	remove_action('user_request_action_confirmed', '_wp_privacy_account_request_confirmed');
	remove_action('user_request_action_confirmed', '_wp_privacy_send_request_confirmation_notification', 12);
	remove_action('wp_privacy_personal_data_exporters', 'wp_register_comment_personal_data_exporter');
	remove_action('wp_privacy_personal_data_exporters', 'wp_register_media_personal_data_exporter');
	remove_action('wp_privacy_personal_data_exporters', 'wp_register_user_personal_data_exporter', 1);
	remove_action('wp_privacy_personal_data_erasers', 'wp_register_comment_personal_data_eraser');
	remove_action('init', 'wp_schedule_delete_old_privacy_export_files');
	remove_action('wp_privacy_delete_old_export_files', 'wp_privacy_delete_old_export_files');

	add_filter('option_wp_page_for_privacy_policy', '__return_zero');
}

if(is_admin()){
	if(wpjam_basic_get_setting('disable_auto_update')){
		remove_action('admin_init', '_maybe_update_core');
		remove_action('admin_init', '_maybe_update_plugins');
		remove_action('admin_init', '_maybe_update_themes');
	}

	if(wpjam_basic_get_setting('disable_block_editor')){
		add_filter('use_block_editor_for_post_type', '__return_false');
	}

	if(wpjam_basic_get_setting('remove_help_tabs')){
		add_action('in_admin_header', function(){
			$GLOBALS['current_screen']->remove_help_tabs();
		});
	}

	if(wpjam_basic_get_setting('remove_screen_options')){
		add_filter('screen_options_show_screen', '__return_false');
		add_filter('hidden_columns', '__return_empty_array');
	}

	if(wpjam_basic_get_setting('disable_privacy', 1)){
		add_action('admin_menu', function(){
			remove_submenu_page('options-general.php', 'options-privacy.php');
			remove_submenu_page('tools.php', 'export-personal-data.php');
			remove_submenu_page('tools.php', 'erase-personal-data.php');
		}, 11);

		add_action('admin_init', function(){
			remove_action('admin_init', ['WP_Privacy_Policy_Content', 'text_change_check'], 100);
			remove_action('edit_form_after_title', ['WP_Privacy_Policy_Content', 'notice']);
			remove_action('admin_init', ['WP_Privacy_Policy_Content', 'add_suggested_content'], 1);
			remove_action('post_updated', ['WP_Privacy_Policy_Content', '_policy_page_updated']);
			remove_filter('list_pages', '_wp_privacy_settings_filter_draft_page_titles', 10, 2);
		}, 1);
	}
}