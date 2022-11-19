<?php
/*
Name: 301 跳转
URI: https://blog.wpjam.com/m/301-redirects/
Description: 支持设置网站上的 404 页面跳转到正确页面。
Version: 1.0
*/
class WPJAM_301_Redirect extends WPJAM_Model{
	public static function get_handler(){
		return wpjam_get_handler('301-redirects');
	}

	public static function get_fields($action_key='', $id=0){
		return [
			'request'		=> ['title'=>'原地址',	'type'=>'url',	'show_admin_column'=>true],
			'destination'	=> ['title'=>'目标地址',	'type'=>'url',	'show_admin_column'=>true]
		];
	}

	public static function on_template_redirect(){
		if(!is_404()){
			return;
		}

		$request_url =  wpjam_get_current_page_url();

		if(strpos($request_url, 'feed/atom/') !== false){
			wp_redirect(str_replace('feed/atom/', '', $request_url), 301);
			exit;
		}

		if(strpos($request_url, 'comment-page-') !== false){
			wp_redirect(preg_replace('/comment-page-(.*)\//', '',  $request_url), 301);
			exit;
		}

		if(strpos($request_url, 'page/') !== false){
			wp_redirect(preg_replace('/page\/(.*)\//', '',  $request_url), 301);
			exit;
		}

		if($redirects = get_option('301-redirects')){
			foreach($redirects as $redirect){
				if($redirect['request'] == $request_url){
					wp_redirect($redirect['destination'], 301);
					exit;
				}
			}
		}
	}

	public static function init(){
		add_action('template_redirect',	[self::class, 'on_template_redirect'], 99);

		if(is_admin()){
			wpjam_register_plugin_page_tab('301-redirects', [
				'plugin_page'	=> 'wpjam-links',
				'title'			=> '301跳转',
				'function'		=> 'list',
				'plural'		=> 'redirects',
				'singular'		=> 'redirect',
				'model'			=> self::class,
				'per_page'		=> 50,
				'summary'		=> '301跳转扩展让一些404页面正确跳转到正常页面，详细介绍请点击：<a href="https://blog.wpjam.com/m/301-redirects/" target="_blank">301 跳转扩展</a>。'
			]);
		}
	}
}

wpjam_register_handler('301-redirects', [
	'type'			=> 'option_items',
	'init'			=> ['WPJAM_301_Redirect', 'init'],
	'primary_key'	=> 'id',
	'max_items'		=> 50,
]);
