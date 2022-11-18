<?php
/*
Name: 统计代码
URI: https://blog.wpjam.com/m/wpjam-stats/
Description: 统计代码扩展最简化插入 Google 分析和百度统计的代码。
Version: 1.0
*/
class WPJAM_Site_Stats{
	public static function get_stats_page_url(){
		$remove_query_args	= ['from','isappinstalled','weixin_access_token','weixin_refer'];

		$page_url	= remove_query_arg($remove_query_args, $_SERVER["REQUEST_URI"]);
		$page_url	= is_404() ? '/404'.$page_url : $page_url;
		$page_url	= $page_url == $_SERVER["REQUEST_URI"] ? '' : $page_url;

		return apply_filters('wpjam_stats_page_url', $page_url);
	}

	public static function baidu_tongji(){
		$id		= wpjam_basic_get_setting('baidu_tongji_id');
		$form	= wpjam_get_parameter('from');
		$url	= self::get_stats_page_url();

		if($id){ 
		?>
		<script type="text/javascript">
		var _hmt = _hmt || [];
		<?php if($url){ ?>
		_hmt.push(['_setAutoPageview', false]);
		_hmt.push(['_trackPageview', '<?php echo $url; ?>']);
		<?php }else{ ?>
		_hmt.push(['_trackPageview']);
		<?php } ?>
		<?php if($form){ ?>
		_hmt.push(['_trackEvent', 'weixin', 'from', '<?php echo esc_js($form);?>']);
		<?php } ?>
		(function() {
		var hm = document.createElement("script");
		hm.src = "//hm.baidu.com/hm.js?<?php echo $id;?>";
		hm.setAttribute('async', 'true');
		document.getElementsByTagName('head')[0].appendChild(hm);
		})();
		</script>
		<?php } 
	}

	public static function google_analytics(){
		$id	= wpjam_basic_get_setting('google_analytics_id');

		if($id){ ?>
		
		<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $id; ?>"></script>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag('js', new Date());

		  gtag('config', '<?php echo $id; ?>');
		</script>

		<?php }
	}

	public static function plugin_page_load(){
		add_filter('wpjam_option_setting_sections', [self::class, 'filter_option_setting_sections']);
	}

	public static function filter_option_setting_sections(){
		return ['stats'	=>['title'=>'统计代码',	'fields'=>[
			'baidu_tongji_id'		=>['title'=>'百度统计',		'type'=>'text'],
			'google_analytics_id'	=>['title'=>'Google 分析',	'type'=>'text'],
		]]];
	}

	public static function on_head(){
		if(is_preview()){
			return;
		}

		self::google_analytics(); 
		self::baidu_tongji(); 
	}
}

add_action('wp_head', ['WPJAM_Site_Stats', 'on_head'], 11);

if(is_admin()){
	wpjam_add_basic_sub_page('wpjam-stats', [
		'menu_title'	=> '统计代码',
		'function'		=> 'option',
		'option_name'	=> 'wpjam-basic',
		'summary'		=> __FILE__,
		'site_default'	=> true,
		'load_callback'	=> ['WPJAM_Site_Stats', 'plugin_page_load']
	]);
}