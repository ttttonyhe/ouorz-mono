<?php
/*
Name: 缩略图设置
URI: https://mp.weixin.qq.com/s/93TRBqSdiTzissW-c0bLRQ
Description: 缩略图设置可以无需预定义就可以进行动态裁图，并且还支持文章和分类缩略图
Version: 2.0
*/
class WPJAM_Thumbnail extends WPJAM_Option_Model{
	public static function get_fields(){
		$tax_options	= [];

		foreach(get_object_taxonomies('post', 'objects') as $tax => $object){
			if($object->show_ui && $object->public){
				$tax_options[$tax]	= ['title'=>$object->label,	'show_if'=>['key'=>'term_thumbnail_taxonomies','value'=>$tax,'postfix'=>'']];
			}
		}

		$order_options	= [
			''			=> '请选择来源',
			'first'		=> '第一张图',
			'post_meta'	=> '自定义字段',
			'term'		=>[
				'title'		=> '分类缩略图',
				'show_if'	=> ['key'=>'term_thumbnail_type', 'compare'=>'IN', 'value'=>['img','image'], 'postfix'=>'']
			]
		];

		$term_show_if	= ['key'=>'term_thumbnail_type', 'compare'=>'!=', 'value'=>''];

		return [
			'auto'		=> ['title'=>'缩略图设置',	'type'=>'radio',	'sep'=>'<br />',	'options'=>[
				0	=>'修改主题代码，手动使用 <a href="https://blog.wpjam.com/m/wpjam-basic-thumbnail-functions/" target="_blank">WPJAM 的相关缩略图函数</a>。',
				1	=>'无需修改主题，程序自动使用 WPJAM 的缩略图设置。'
			]],
			'default'	=> ['title'=>'默认缩略图',	'type'=>'mu-img',	'item_type'=>'url'],
			'term_set'	=> ['title'=>'分类缩略图',	'type'=>'fieldset',	'fields'=>[
				'term_thumbnail_type'		=> ['type'=>'select',	'options'=>[''=>'关闭分类缩略图', 'img'=>'本地媒体模式', 'image'=>'输入图片链接模式']],
				'term_thumbnail_taxonomies'	=> ['type'=>'checkbox',	'show_if'=>$term_show_if,	'title'=>'支持的分类模式：',	'options'=>wp_list_pluck($tax_options, 'title')],
				'term_thumbnail_width'		=> ['type'=>'number',	'show_if'=>$term_show_if,	'group'=>'term',	'title'=>'缩略图尺寸：',	'class'=>'small-text'],
				'term_thumbnail_plus'		=> ['type'=>'view',		'show_if'=>$term_show_if,	'group'=>'term',	'title'=>wpjam_field_get_icon('multiply')],
				'term_thumbnail_height'		=> ['type'=>'number',	'show_if'=>$term_show_if,	'group'=>'term',	'class'=>'small-text']
			]],
			'post_set'	=> ['title'=>'文章缩略图',	'type'=>'fieldset',	'fields'=>[
				'post_thumbnail_view'	=> ['type'=>'view',			'value'=>'首先使用文章特色图片，如未设置，将按照下面的顺序获取：'],
				'post_thumbnail_orders'	=> ['type'=>'mu-fields',	'group'=>true,	'max_items'=>5,	'fields'=>[
					'type'		=> ['type'=>'select',	'options'=>$order_options],
					'taxonomy'	=> ['type'=>'select',	'show_if'=>['key'=>'type', 'value'=>'term'],		'options'=>[''=>'请选择分类模式']+$tax_options],
					'post_meta'	=> ['type'=>'text',		'show_if'=>['key'=>'type', 'value'=>'post_meta'],	'class'=>'all-options',	'placeholder'=>'请输入自定义字段的 meta_key'],
				]]
			]]
		];
	}

	public static function get_menu_page(){
		return [
			'parent'		=> 'wpjam-basic',
			'menu_title'	=> '缩略图设置',
			'function'		=> 'option',
			'order'			=> 19,
			'summary'		=> __FILE__,
		];
	}

	public static function filter_post_thumbnail_url($thumbnail_url, $post){
		if(!is_object_in_taxonomy($post, 'category')){
			return $thumbnail_url;
		}

		foreach(self::get_setting('post_thumbnail_orders', []) as $order){
			if($order['type'] == 'first'){
				if($value = wpjam_get_post_first_image_url($post)){
					return $value;
				}
			}elseif($order['type'] == 'post_meta'){
				if($order['post_meta']){
					if($value = get_post_meta($post->ID, $order['post_meta'], true)){
						return $value;
					}
				}
			}elseif($order['type'] == 'term'){
				if($order['taxonomy'] && is_object_in_taxonomy($post, $order['taxonomy'])){
					if($terms = get_the_terms($post, $order['taxonomy'])){
						foreach($terms as $term){
							if($value = wpjam_get_term_thumbnail_url($term)){
								return $value;
							}
						}
					}
				}
			}
		}

		return $thumbnail_url ?: wpjam_get_default_thumbnail_url();
	}

	public static function filter_has_post_thumbnail($has_thumbnail, $post){
		if(!$has_thumbnail && self::get_setting('auto')){
			return (bool)wpjam_get_post_thumbnail_url($post);
		}

		return $has_thumbnail;
	}

	public static function filter_post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr){
		$post_type	= get_post_type($post_id);

		if(!post_type_supports($post_type, 'thumbnail') || empty($html)){
			$thumbnail_url	= '';

			if(self::get_setting('auto')){
				$thumbnail_url	= wpjam_get_post_thumbnail_url($post_id, wpjam_parse_size($size, 2));
			}elseif(post_type_supports($post_type, 'images')){
				$images			= wpjam_get_post_images($post_id, 'full', false);
				$thumbnail_url	= $images ? current($images) : '';
			}

			if(!$thumbnail_url){
				return $html;
			}

			if(function_exists('wp_get_loading_attr_default')){
				$loading	= wp_get_loading_attr_default('the_post_thumbnail');
			}elseif(wp_lazy_loading_enabled('img', 'wp_get_attachment_image')){
				$loading	= 'lazy';
			}else{
				$loading	= '';
			}

			$class	= is_array($size) ? join('x', $size) : $size;

			$attr	= wp_parse_args($attr, [
				'src'		=> $thumbnail_url,
				'class'		=> "attachment-$class size-$class wp-post-image",
				'loading'	=> $loading
			]);

			$size	= wpjam_parse_size($size);
			$html	= rtrim('<img '.image_hwstring($size['width'], $size['height']));

			foreach($attr as $name => $value){
				if($name == 'loading' && !$value){
					continue;
				}

				$html	.= " $name=".'"'.esc_attr($value).'"';
			}

			$html	.= ' />';
		}

		return $html;
	}

	public static function init(){
		$taxonomies	= self::get_setting('term_thumbnail_taxonomies', []);

		if($taxonomies){
			$settings	= ['thumbnail_type'=>''];

			if(self::get_setting('term_thumbnail_type') == 'img'){
				$width	= self::get_setting('term_thumbnail_width', 200);
				$height	= self::get_setting('term_thumbnail_height', 200);

				if($width || $height){
					$settings['thumbnail_size']	= $width.'x'.$height;
				}

				$settings['thumbnail_type']	= 'img';
			}else{
				$settings['thumbnail_type']	= 'image';
			}

			foreach($taxonomies as $taxonomy){
				if(is_object_in_taxonomy('post', $taxonomy)){
					$supports	= wpjam_get_taxonomy_setting($taxonomy, 'supports');

					$settings['supports'] = array_merge($supports, ['thumbnail']);

					wpjam_update_taxonomy_setting($taxonomy, array_filter($settings));
				}
			}
		}

		add_filter('wpjam_post_thumbnail_url',	[self::class, 'filter_post_thumbnail_url'], 1, 2);
		add_filter('has_post_thumbnail',		[self::class, 'filter_has_post_thumbnail'], 10, 2);
		add_filter('post_thumbnail_html',		[self::class, 'filter_post_thumbnail_html'], 10, 5);
	}
}

// 1. $img_url
// 2. $img_url, array('width'=>100, 'height'=>100)	// 这个为最标准版本
// 3. $img_url, 100x100
// 4. $img_url, 100
// 5. $img_url, array(100,100)
// 6. $img_url, array(100,100), $crop=1, $ratio=1
// 7. $img_url, 100, 100, $crop=1, $ratio=1
function wpjam_get_thumbnail($img_url, ...$args){
	$img_url	= wpjam_zh_urlencode($img_url);	// 中文名
	$args_num	= count($args);

	if($args_num == 0){
		// 1. $img_url 简单替换一下 CDN 域名

		$thumb_args = [];
	}elseif($args_num == 1){
		// 2. $img_url, ['width'=>100, 'height'=>100]	// 这个为最标准版本
		// 3. $img_url, [100,100]
		// 4. $img_url, 100x100
		// 5. $img_url, 100

		$thumb_args = wpjam_parse_size($args[0]);
	}else{
		if(is_numeric($args[0])){
			// 6. $img_url, 100, 100, $crop=1

			$width	= $args[0] ?? 0;
			$height	= $args[1] ?? 0;
			$crop	= $args[2] ?? 1;
		}else{
			// 7. $img_url, array(100,100), $crop=1

			$size	= wpjam_parse_size($args[0]);
			$width	= $size['width'];
			$height	= $size['height'];
			$crop	= $args[1] ?? 1;
		}

		$thumb_args = compact('width','height','crop');
	}

	$thumb_args	= apply_filters('wpjam_thumbnail_args', $thumb_args);

	return apply_filters('wpjam_thumbnail', $img_url, $thumb_args);
}

function wpjam_parse_size($size, $ratio=1){
	if(is_array($size)){
		if(!wp_is_numeric_array($size)){
			$size['width']	= !empty($size['width']) ? ((int)$size['width'])*$ratio : 0;
			$size['height']	= !empty($size['height']) ? ((int)$size['height'])*$ratio : 0;
			$size['crop']	= $size['crop'] ?? ($size['width'] && $size['height']);

			return $size;
		}else{
			$width	= $size[0] ?? 0;
			$height	= $size[1] ?? 0;
		}
	}else{
		$size	= str_replace(['*','X'], 'x', $size);

		if(strpos($size, 'x') !== false){
			$size	= explode('x', $size);
			$width	= $size[0];
			$height	= $size[1];
			$crop	= true;
		}elseif(is_numeric($size)){
			$width	= $size;
			$height	= 0;
		}elseif($size == 'thumb' || $size == 'thumbnail'){
			$width	= get_option('thumbnail_size_w') ?: 100;
			$height = get_option('thumbnail_size_h') ?: 100;
			$crop	= get_option('thumbnail_crop');
		}elseif($size == 'medium'){
			$width	= get_option('medium_size_w') ?: 300;
			$height	= get_option('medium_size_h') ?: 300;
			$crop	= false;
		}else{
			if($size == 'medium_large'){
				$width	= get_option('medium_large_size_w');
				$height	= get_option('medium_large_size_h');
				$crop	= false;
			}elseif($size == 'large'){
				$width	= get_option('large_size_w') ?: 1024;
				$height	= get_option('large_size_h') ?: 1024;
				$crop	= false;
			}else{
				$_sizes = wp_get_additional_image_sizes();

				if(isset($_sizes[$size])){
					$width	= $_sizes[$size]['width'];
					$height	= $_sizes[$size]['height'];
					$crop	= $_sizes[$size]['crop'];
				}else{
					$width	= $height = 0;
				}
			}

			if($width && !empty($GLOBALS['content_width'])){
				$max_width	= $GLOBALS['content_width'] * $ratio;
				$width		= min($max_width, $width);
			}
		}
	}

	return [
		'crop'		=> $crop ?? ($width && $height),
		'width'		=> (int)$width * $ratio,
		'height'	=> (int)$height * $ratio
	];
}

function wpjam_get_default_thumbnail_url($size='full', $crop=1){
	$default	= WPJAM_Thumbnail::get_setting('default', []);

	if($default && is_array($default)){
		$default	= $default[array_rand($default)];
	}else{
		$default	= '';
	}

	$default = apply_filters('wpjam_default_thumbnail_url', $default);

	return $default ? wpjam_get_thumbnail($default, $size, $crop) : '';
}

wpjam_register_option('wpjam-thumbnail', [
	'site_default'	=> true,
	'model'			=> 'WPJAM_Thumbnail',
]);
