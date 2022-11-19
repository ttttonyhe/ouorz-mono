<?php
function wpjam_basic_get_setting($setting_name){
	return wpjam_get_setting('wpjam-basic', $setting_name);
}

add_filter('wpjam-basic_defaults', 'wpjam_basic_get_defaults');
function wpjam_basic_get_defaults($defaults){
	$site_url = parse_url( site_url() );
	$path = ( !empty( $site_url['path'] ) ) ? $site_url['path'] : '';
	$seo_robots	= '';
	$seo_robots	.= "User-agent: *\n";
	$seo_robots	.= "Disallow: /wp-admin/\n";
	$seo_robots	.= "Disallow: /wp-includes/\n";
	$seo_robots	.= "Disallow: /cgi-bin/\n";
	$seo_robots	.= "Disallow: $path/wp-content/plugins/\n";
	$seo_robots	.= "Disallow: $path/wp-content/themes/\n";
	$seo_robots	.= "Disallow: $path/wp-content/cache/\n";
	$seo_robots	.= "Disallow: $path/author/\n";
	$seo_robots	.= "Disallow: $path/trackback/\n";
	$seo_robots	.= "Disallow: $path/feed/\n";
	$seo_robots	.= "Disallow: $path/comments/\n";
	$seo_robots	.= "Disallow: $path/search/\n";

	$toc_script = "jQuery(document).ready(function(){
	jQuery('#toc span').on('click',function(){
	    if(jQuery('#toc span').html() == '[显示]'){
	        jQuery('#toc span').html('[隐藏]');
	    }else{
	        jQuery('#toc span').html('[显示]');
	    }
	    jQuery('#toc ul').toggle();
	    jQuery('#toc small').toggle();
	});
});
";
	$toc_css = "#toc {
	float:right;
	max-width:240px;
	min-width:120px;
	padding:6px;
	margin:0 0 20px 20px;
	border:1px solid #EDF3DE;
	background:white;
	border-radius:6px;
}
#toc p {
	margin:0 4px;
}
#toc strong {
	border-bottom:1px solid #EDF3DE;
	display:block;
}
#toc span {
	display:block;
	margin:4px 0;
    cursor:pointer;
}
#toc ul{
	margin-bottom:0;
}
#toc li{
	margin:2px 0;
}
#toc small {
	float:right;
}";

	$defaults = array(
		'remove_head_links'		=> 1,
		'diable_revision'		=> 1,
		'disable_autoembed'		=> 1,
		'disable_trackbacks'	=> 1,
		'disable_xml_rpc'		=> 1,
		'disable_emoji'			=> 1,
		'disable_rest_api'		=> 1,
		'disable_post_embed'	=> 1,
		'disable_auto_update'	=> 1,
		'no_admin'				=> 0,
		'remove_admin_bar'		=> 1,
		'remove_dashboard_widgets'	=> 1,
		'remove_unuse_rewrite'	=> 1,
		'order_by_registered'	=> 1,
		'remove_tb=1_rewrite'			=> 1,
		'remove_type_rewrite'			=> 1,
		'remove_comment_rewrite'		=> 1,
		'remove_comment-page_rewrite'	=> 1,
		'remove_author_rewrite'			=> 1,
		'remove_feed=_rewrite'			=> 1,
		'remove_attachment_rewrite'		=> 1,

		'seo_robots'			=> $seo_robots,
		'toc_auto'				=> '1',
		'toc_copyright'			=> '1',
		'toc_individual'		=> '1',
		'toc_depth'				=> '6',
		'toc_script'			=> $toc_script,
		'toc_css'				=> $toc_css,
		'smtp_host'				=> 'smtp.gmail.com',
		'smtp_ssl'				=> 'ssl',
		'smtp_port'				=> '465',
		'google_universal'		=> 1,
		'image_default_link_type' => get_option('image_default_link_type'),
		'qzone_full_text'		=> 1,
		'admin_footer_text'		=> '<span id="footer-thankyou">感谢使用<a href="https://cn.wordpress.org/" target="_blank">WordPress</a>进行创作。</span> | <a href="http://wpjam.com/" title="WordPress JAM" target="_blank">WordPress JAM</a>'
	);

	return apply_filters('wpjam_basic_defaults',$defaults);	
}

function wpjam_include_admin_extends(){
	wpjam_include_extends($admin=true);	
}

function wpjam_include_extends($admin=false){
	$wpjam_extends	= get_option('wpjam-extends');
	unset($wpjam_extends['plugin_page']);

	if(is_multisite() && $wpjam_sitewide_extends = get_site_option('wpjam-extends')){
		unset($wpjam_sitewide_extends['plugin_page']);
		foreach ($wpjam_sitewide_extends as $wpjam_extend_file => $value) {
			if($value){
				$wpjam_extends[$wpjam_extend_file]	= $value;
			}
		}
	}

	if($wpjam_extends){
		$wpjam_extend_dir 	= WPJAM_BASIC_PLUGIN_DIR.'extends/';
		
		if($admin){
			$wpjam_extend_dir	.= 'admin/';
		}

		foreach ($wpjam_extends as $wpjam_extend_file => $value) {
			if($value){
				if(is_file($wpjam_extend_dir.$wpjam_extend_file)){
					include($wpjam_extend_dir.$wpjam_extend_file);
				}
			}
		}
	}
}