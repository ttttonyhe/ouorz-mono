<?php
/*
Name: 百度站长
URI: https://blog.wpjam.com/m/baidu-zz/
Description: 百度站长扩展实现主动，被动，自动以及批量方式提交链接到百度站长，让博客的文章能够更快被百度收录。
Version: 1.0
*/
class WPJAM_Baidu_ZZ extends WPJAM_Option_Model{
	public static function get_fields(){
		return [
			'site'	=> ['title'=>'站点 (site)',	'type'=>'text',	'class'=>'all-options'],
			'token'	=> ['title'=>'密钥 (token)',	'type'=>'password'],
			'mip'	=> ['title'=>'MIP',			'type'=>'checkbox', 'description'=>'博客已支持MIP'],
			'no_js'	=> ['title'=>'不加载推送JS',	'type'=>'checkbox', 'description'=>'插件已支持主动推送，不加载百度推送JS'],
		];
	}

	public static function notify($urls, $args=[]){
		$query_args	= [];

		$query_args['site']		= self::get_setting('site');
		$query_args['token']	= self::get_setting('token');

		if(empty($query_args['site']) || empty($query_args['token'])){
			return;
		}

		if(empty($args['type'])){
			if(self::get_setting('mip')){
				$query_args['type']	= 'mip';
			}
		}else{
			$query_args['type']	= $args['type'];
		}

		if(!empty($args['update'])){
			$api_url	= add_query_arg($query_args, 'http://data.zz.baidu.com/update');
		}else{
			$api_url	= add_query_arg($query_args, 'http://data.zz.baidu.com/urls');
		}

		return wp_remote_post($api_url, array(
			'headers'	=> ['Accept-Encoding'=>'','Content-Type'=>'text/plain'],
			'sslverify'	=> false,
			'blocking'	=> false,
			'body'		=> $urls
		));
	}

	public static function notify_post($post_id, $args=[]){
		if(is_array($post_id)){
			$wp_error	= false;
			$post_ids	= $post_id;
		}else{
			$wp_error	= true;
			$post_ids	= [$post_id];
		}
		
		$urls	= '';

		foreach ($post_ids as $post_id) {
			if(get_post($post_id)->post_status == 'publish'){
				if(wp_cache_get($post_id, 'wpjam_baidu_zz_notified') === false){
					wp_cache_set($post_id, true, 'wpjam_baidu_zz_notified', HOUR_IN_SECONDS);
					$urls	.= apply_filters('baiduz_zz_post_link', get_permalink($post_id), $post_id)."\n";
				}else{
					if($wp_error){
						return new WP_Error('has_submited', '一小时内已经提交过了');
					}
				}
			}else{
				if($wp_error){
					return new WP_Error('invalid_post_status', '未发布的文章不能同步到百度站长');
				}
			}
		}

		if(!$urls){
			return new WP_Error('empty_urls', '没有需要提交的链接');
		}

		return self::notify($urls, $args);
	}

	public static function batch_callback(){
		$offset		= (int)wpjam_get_data_parameter('offset',	['default'=>0]);
		$type		= wpjam_get_data_parameter('type',		['default'=>'post']);

		// $types	= apply_filters('wpjam_baidu_zz_batch_submit_types', ['post']);

		// if($type){
		// 	$index	= array_search($type, $types);
		// 	$types	= array_slice($types, $index, -1);
		// }

		// foreach ($types as $type) {
			if($type=='post'){
				$_query	= new WP_Query([
					'post_type'			=> 'any',
					'post_status'		=> 'publish',
					'posts_per_page'	=> 100,
					'fields'			=> 'ids',
					'offset'			=> $offset
				]);

				if($_query->have_posts()){
					$count	= count($_query->posts);
					$number	= $offset+$count;
					$args	= http_build_query(['type'=>$type, 'offset'=>$number]);

					self::notify_post($_query->posts);

					return ['done'=>0, 'errmsg'=>'批量提交中，请勿关闭浏览器，已提交了'.$number.'个页面。',	'args'=>$args];
				}else{
					return true;
				}
			}else{
				// do_action('wpjam_baidu_zz_batch_submit', $type, $offset);
				// wpjam_send_json();
			}
		// }
	}

	public static function on_after_insert_post($post_id, $post, $update, $post_before){
		if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || $post->post_status != 'publish' || !current_user_can('edit_post', $post_id)){
			return;
		}

		if($daily = wpjam_get_parameter('baidu_zz_daily', ['method'=>'POST'])){
			$args	= ['type'=>'daily'];
		}else{
			$args	= [];
		}

		if($update){
			if($daily){
				self::notify_post($post_id, $args);
			}
		}else{
			self::notify_post($post_id, $args);
		}
	}

	public static function on_publish_future_post($post_id){
		self::notify_post($post_id);
	}

	public static function on_enqueue_scripts(){
		if(self::get_setting('no_js')){
			return;
		}

		if(is_404() || is_preview()){
			return;
		}elseif(is_singular() && get_post_status() != 'publish'){
			return;
		}

		if(is_ssl()){
			wp_enqueue_script('baidu_zz_push', 'https://zz.bdstatic.com/linksubmit/push.js', '', '', true);
		}else{
			wp_enqueue_script('baidu_zz_push', 'http://push.zhanzhang.baidu.com/push.js', '', '', true);
		}
	}

	public static function on_post_submitbox_misc_actions(){ ?>
		<div class="misc-pub-section" id="baidu_zz_section">
			<input type="checkbox" name="baidu_zz_daily" id="baidu_zz" value="1">
			<label for="baidu_zz_daily">提交给百度站长快速收录</label>
		</div>
	<?php }

	public static function builtin_page_load($screen){
		if($screen->base == 'edit'){
			if(is_post_type_viewable($screen->post_type)){
				wpjam_register_list_table_action('notify_baidu_zz', [
					'title'			=> '提交到百度',
					'post_status'	=> ['publish'],
					'callback'		=> [self::class, 'notify_post'],
					'bulk'			=> true,
					'direct'		=> true
				]);
			}
		}elseif($screen->base == 'post'){
			if(is_post_type_viewable($screen->post_type)){
				add_action('wp_after_insert_post',			[self::class, 'on_after_insert_post'], 10, 4);
				add_action('post_submitbox_misc_actions',	[self::class, 'on_post_submitbox_misc_actions'],11);

				wp_add_inline_style('list-tables', '#post-body #baidu_zz_section:before {content: "\f103"; color:#82878c; font: normal 20px/1 dashicons; speak: none; display: inline-block; margin-left: -1px; padding-right: 3px; vertical-align: top; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }');
			}
		}
	}

	public static function load_plugin_page(){
		wpjam_register_plugin_page_tab('baidu-zz', [
			'title'			=> '百度站长',
			'function'		=> 'option',
			'option_name'	=> 'baidu-zz',
		]);

		wpjam_register_plugin_page_tab('batch', [
			'title'			=> '批量提交',
			'function'		=> 'form',
			'submit_text'	=> '批量提交',
			'callback'		=> [self::class, 'batch_callback'],
			'summary'		=> '使用百度站长更新内容接口批量将博客中的所有内容都提交给百度搜索资源平台。'
		]);
	}

	public static function init(){
		add_action('publish_future_post',	[self::class, 'on_publish_future_post'], 11);
		add_action('wp_enqueue_scripts',	[self::class, 'on_enqueue_scripts']);

		if(is_admin()){
			wpjam_add_basic_sub_page('baidu-zz',	[
				'menu_title'	=> '百度站长',
				'network'		=> false,
				'summary'		=> __FILE__,
				'function'		=> 'tab',
				'load_callback'	=> [self::class, 'load_plugin_page']
			]);

			wpjam_register_builtin_page_load('baidu-zz', [
				'base'		=> ['post','edit'], 
				'callback'	=> [self::class, 'builtin_page_load']
			]);
		}
	}
}

wpjam_register_option('baidu-zz',	['model'=>'WPJAM_Baidu_ZZ',]);

function wpjam_notify_baidu_zz($urls, $args=[]){
	return WPJAM_Baidu_ZZ::notify($urls, $args);
}

	