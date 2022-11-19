<?php
/*
Name: 移动主题
URI: https://blog.wpjam.com/m/mobile-theme/
Description: 给当前站点设置移动设备设置上使用单独的主题。
Version: 1.0
*/
class WPJAM_Mobile_Theme{
	public static function filter_stylesheet($stylesheet=''){
		if(isset($_GET['wpjam_theme'])){
			return $_GET['wpjam_theme'];
		}else{
			return wpjam_basic_get_setting('mobile_stylesheet');
		}
	}

	public static function filter_template($template){
		$mobile_template	= wpjam_get_current_var('mobile_template');

		if(is_null($mobile_template)){
			$stylesheet			= self::filter_stylesheet();
			$mobile_template	= wp_get_theme($stylesheet)->get_template();

			wpjam_set_current_var('mobile_template', $mobile_template);
		}

		return $mobile_template;
	}

	public static function filter_option_setting_sections(){
		$current_theme	= wp_get_theme();
		$theme_options	= [];
		
		$theme_options[$current_theme->get_stylesheet()]	= $current_theme->get('Name');

		foreach(wp_get_themes() as $theme){
			$theme_options[$theme->get_stylesheet()]	= $theme->get('Name');
		}

		return ['mobile'	=>['title'=>'移动主题',	'fields'=>[
			'mobile_stylesheet'=>['title'=>'选择移动主题',	'type'=>'select',	'options'=>$theme_options]]
		]];
	}

	public static function load_option_page(){
		add_filter('wpjam_option_setting_sections', [self::class, 'filter_option_setting_sections']);
	}
}

add_action('plugins_loaded', function(){
	if(wp_is_mobile() && wpjam_basic_get_setting('mobile_stylesheet')){
		add_filter('stylesheet',	['WPJAM_Mobile_Theme', 'filter_stylesheet']);
		add_filter('template',		['WPJAM_Mobile_Theme', 'filter_template']);
	}else{
		if(isset($_GET['wpjam_theme'])){
			add_filter('stylesheet',	['WPJAM_Mobile_Theme', 'filter_stylesheet']);
			add_filter('template',		['WPJAM_Mobile_Theme', 'filter_template']);
		}
	}

	if(is_admin()){
		wpjam_add_menu_page('mobile-theme', [
			'menu_title'	=> '移动主题',
			'parent'		=> 'themes',
			'function'		=> 'option',
			'option_name'	=> 'wpjam-basic',
			'load_callback'	=> ['WPJAM_Mobile_Theme', 'load_option_page']
		]);
	}
}, 0);
