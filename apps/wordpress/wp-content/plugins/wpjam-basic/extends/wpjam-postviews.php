<?php
/*
Name: 文章浏览
URI: https://blog.wpjam.com/m/wpjam-postviews/
Description: 统计文章阅读数，激活该扩展，请不要再激活 WP-Postviews 插件。
Version: 1.0
*/
class WPJAM_Postviews{
	public static function filter_the_content($content){
		return is_feed() ? $content."\n".'<p><img src="'.home_url('postviews/'.get_the_ID().'.png').'" /></p>'."\n" : $content;
	}

	public static function on_pre_get_posts($wp_query){
		if(get_query_var('module') == 'postviews'){	// 不指定 post_type ，默认查询 post，这样custom post type 的文章页面就会显示 404
			$wp_query->set('post_type', 'any');
		}
	}

	public static function image(){
		$post_id	= $GLOBALS['wp']->query_vars['p'];

		if(isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
			ob_start('ob_gzhandler'); 
		}else{
			ob_start(); 
		}

		$views	= wpjam_get_post_views($post_id)+1;

		wpjam_update_post_views($post_id);

		header("Content-Type: image/png");

		$im			= @imagecreate(120, 32) or die("Cannot Initialize new GD image stream");
		$background	= imagecolorallocate($im, 0, 0, 0);
		$text_color	= imagecolorallocate($im, 255, 0, 0);

		if($views > 100000){
			$x	= 6;
		}elseif($views > 10000){
			$x	= 10;
		}elseif($views > 1000){
			$x	= 14;
		}elseif($views > 100){
			$x	= 18;
		}else{
			$x	= 18;
		}

		$font	= 5;
		$y		= 8;

		imagestring($im, $font, $x, $y,  $views.' views', $text_color);

		imagepng($im);
		imagedestroy($im);

		exit;
	}

	public static function column_callback($post_id){
		$views	= wpjam_get_post_views($post_id, false) ?: 0;

		return wpjam_get_list_table_row_action('update_views', ['id'=>$post_id, 'title'=>$views, 'fallback'=>true]);
	}

	public static function add_views($post_id, $data){
		if(!empty($data['addon'])){
			wpjam_update_post_views($post_id, $data['addon']);
		}

		return true;
	}

	public static function builtin_page_load($screen){
		$ptype	= $screen->post_type;
		$pt_obj	= $ptype ? get_post_type_object($ptype) : null;

		if(!$pt_obj || (empty($pt_obj->viewable) && !is_post_type_viewable($ptype))){
			return;
		}

		wp_add_inline_style('list-tables', '.fixed .column-views{width:7%;}');

		wpjam_register_list_table_action('update_views', [
			'title'			=> '修改浏览数',
			'page_title'	=> '修改浏览数',
			'submit_text'	=> '修改',	
			'capability'	=> $pt_obj->cap->edit_others_posts,
			'row_action'	=> false,
			'width'			=> 500,
			'fields'		=> ['views'=>['title'=>'浏览数', 'type'=>'number']]
		]);

		wpjam_register_list_table_action('add_views', [
			'title'			=> '增加浏览数',
			'page_title'	=> '增加浏览数',
			'submit_text'	=> '增加',	
			'capability'	=> $pt_obj->cap->edit_others_posts,
			'row_action'	=> false,
			'bulk'			=> true,
			'width'			=> 500,
			'callback'		=> [self::class, 'add_views'],
			'fields'		=> [
				'view'	=>['title'=>'使用说明',	'type'=>'view',	'value'=>'批量处理是在原有的浏览量上增加'],
				'addon'	=>['title'=>'浏览数增量',	'type'=>'number']
			]
		]);

		wpjam_register_posts_column('views', [
			'title'				=> '浏览',
			'sortable_column'	=> 'views',
			'column_callback'	=> [self::class, 'column_callback']
		]);
	}
}

function wpjam_get_post_total_views($post_id){
	return wpjam_get_post_views($post_id);
}

if(!function_exists('the_views')){
	//显示浏览次数
	function the_views(){
		$views = wpjam_get_post_views(get_the_ID()) ?: 0;
		echo '<span class="view">浏览：'.$views.'</span>';
	}

	add_action('wp_head', function(){
		if(is_single()){
			wpjam_update_post_views(get_queried_object_id());
		}
	});
}

add_action('init', function(){
	add_rewrite_rule($GLOBALS['wp_rewrite']->root.'postviews/([0-9]+)\.png?$', 'index.php?module=postviews&p=$matches[1]', 'top');

	wpjam_register_route_module('postviews', ['callback'=>['WPJAM_Postviews', 'image']]);

	add_filter('the_content',	['WPJAM_Postviews', 'filter_the_content'], 999);
	add_action('pre_get_posts',	['WPJAM_Postviews', 'on_pre_get_posts']);
});

if(is_admin()){
	wpjam_register_builtin_page_load('post-views', [
		'base'		=> 'edit',
		'callback'	=> ['WPJAM_Postviews', 'builtin_page_load']
	]);
}
