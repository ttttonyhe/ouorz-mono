<?php
/*
Name: 相关文章
URI: https://blog.wpjam.com/m/wpjam-related-posts/
Description: 相关文章扩展根据文章的标签和分类自动生成相关文章列表，并显示在文章末尾。
Version: 1.0
*/
class WPJAM_Related_Posts extends WPJAM_Option_Model{
	public static function get_fields(){
		$show_if	= ['key'=>'thumb', 'value'=>1];
		$fields		= [
			'title'		=> ['title'=>'列表标题',	'type'=>'text',		'value'=>'相关文章',	'class'=>'all-options',	'description'=>'相关文章列表标题。'],
			'list_set'	=> ['title'=>'列表设置',	'type'=>'fieldset',	'fields'=>[
				'number'	=> ['type'=>'number',	'value'=>5,	'class'=>'small-text',	'description'=>'数量，不填或默认为5。'],
				'days'		=> ['type'=>'number',	'value'=>0,	'class'=>'small-text',	'description'=>'只展示最近多少天的文章，不填或默认则不限制。'],
			]],
			'thumb_set'	=> ['title'=>'列表内容',	'type'=>'fieldset',	'fields'=>[
				'_excerpt'	=> ['type'=>'checkbox',	'description'=>'显示文章摘要。',	'name'=>'excerpt'],
				'thumb'		=> ['type'=>'checkbox',	'description'=>'显示文章缩略图。',	'group'=>'size',	'value'=>1],
				'width'		=> ['type'=>'number',	'show_if'=>$show_if,	'group'=>'size',	'value'=>100,	'title'=>'尺寸：',	'class'=>'small-text'],
				'x'			=> ['type'=>'view',		'show_if'=>$show_if,	'group'=>'size',	'value'=>wpjam_field_get_icon('multiply')],
				'height'	=> ['type'=>'number',	'show_if'=>$show_if,	'group'=>'size',	'value'=>100,	'class'=>'small-text'],
				'_view'		=> ['type'=>'view',		'show_if'=>$show_if,	'value'=>'如勾选之后缩略图不显示，请到「<a href="'.admin_url('admin.php?page=wpjam-thumbnail').'">缩略图设置</a>」勾选「无需修改主题，程序自动使用 WPJAM 的缩略图设置」。']
			]],
			'style'		=> ['title'=>'列表样式',	'type'=>'fieldset',	'fields'=>[
				'div_id'	=> ['type'=>'text',	'class'=>'all-options',	'value'=>'related_posts',	'description'=>'外层 div id，不填则外层不添加 div。'],
				'class'		=> ['type'=>'text',	'class'=>'all-options',	'value'=>'',				'description'=>'相关文章列表 ul 的 class。'],
			]],
			'auto'		=> ['title'=>'自动附加',	'type'=>'checkbox',	'value'=>1,	'description'=>'自动附加到文章末尾。'],
		];

		$options = self::get_post_types();

		if(count($options) > 1){
			$fields['post_types']	= ['title'=>'文章类型',	'type'=>'checkbox',	'options'=>$options,	'description'=>'哪些文章类型显示相关文章。'];
		}

		return $fields;
	}

	public static function get_menu_page(){
		return [
			'tab_slug'		=> 'related-posts',
			'plugin_page'	=> 'wpjam-posts',
			'order'			=> 19,
			'title'			=> '相关文章',
			'function'		=> 'option',
			'option_name'	=> 'wpjam-related-posts',
			'summary'		=> __FILE__,
		];
	}

	public static function shortcode($atts, $content=''){
		$atts	= shortcode_atts(['tag'=>''], $atts);
		$tags	= $atts['tag'] ? explode(",", $atts['tag']) : '';

		return $tags ? wpjam_render_query(wpjam_query([
			'post_type'		=> 'any',
			'no_found_rows'	=> true,
			'post_status'	=> 'publish',
			'post__not_in'	=> [get_the_ID()],
			'tax_query'		=> [[
				'taxonomy'	=> 'post_tag',
				'terms'		=> $tags,
				'operator'	=> 'AND',
				'field'		=> 'name'
			]]
		]), ['thumb'=>false, 'class'=>'related-posts']) : '';
	}

	public static function get_post_types(){
		$ptypes	= ['post'=>__('Post')];

		foreach(get_post_types(['_builtin'=>false], 'objects') as $ptype => $object){
			if(is_post_type_viewable($ptype) && get_object_taxonomies($ptype)){
				$ptypes[$ptype]	= $object->labels->singular_name ?? $object->label;
			}
		}

		return $ptypes;
	}

	public static function has($post_type){
		$ptypes	= self::get_post_types();

		if(count($ptypes) > 1){
			if($post_types = self::get_setting('post_types')){
				$ptypes	= wp_array_slice_assoc($ptypes, $post_types);
			}

			return isset($ptypes[$post_type]);
		}else{
			return $post_type === 'post';
		}
	}

	public static function get_args($context=null){
		$args	= self::get_setting() ?: [];

		if(!empty($args['thumb'])){
			$context	= $context ?? current_filter();
			$ratio		= $context == 'the_content' ? 1 : 2;

			$args['size']	= wp_array_slice_assoc($args, ['width', 'height']);
			$args['size']	= wpjam_parse_size($args['size'], $ratio);
		}

		return $args;
	}

	public static function filter_the_content($content){
		return $content.wpjam_get_related_posts(get_the_ID(), self::get_args());
	}

	public static function filter_post_json($post_json){
		$post_json['related']	= wpjam_get_related_posts(get_the_ID(), self::get_args(), $parse_for_json=true);

		return $post_json;
	}

	public static function on_the_post($post, $wp_query){
		if($wp_query->is_main_query()
			&& !$wp_query->is_page()
			&& $wp_query->is_singular($post->post_type)
			&& $post->ID == $wp_query->get_queried_object_id()
			&& self::has($post->post_type)
		){
			if(wpjam_is_json_request()){
				add_filter('wpjam_post_json',	[self::class, 'filter_post_json'], 10, 2);
			}else{
				if(self::get_setting('auto')){
					add_filter('the_content',	[self::class, 'filter_the_content'], 11);
				}
			}
		}
	}

	public static function init(){
		if(!is_admin()){
			add_action('the_post', [self::class, 'on_the_post'], 10, 2);
		}

		add_shortcode('related', [self::class, 'shortcode']);
	}
}

wpjam_register_option('wpjam-related-posts',	['model'=>'WPJAM_Related_Posts',]);
