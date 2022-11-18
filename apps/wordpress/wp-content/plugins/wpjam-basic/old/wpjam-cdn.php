<?php
add_action('wp_loaded', 'wpjam_cdn_ob_cache');

// HTML 替换，镜像 CDN 主函数
function wpjam_cdn_ob_cache(){

	// 定义CDN和本地域名网址
	define( 'CDN_HOST',		untrailingslashit( apply_filters( 'wpjam_cdn_host', home_url() ) ) );
	define( 'LOCAL_HOST',	untrailingslashit( apply_filters( 'wpjam_local_host', home_url() ) ) );
	define( 'CDN_NAME',		apply_filters( 'wpjam_cdn_name', '') );	// CDN 名称

	$wpjam_google	= apply_filters('wpjam_google', '');

    if(strpos('https://', LOCAL_HOST) !== false){
        define( 'LOCAL_HOST2', str_replace('https://', 'http://', LOCAL_HOST) );
    }else{
        define( 'LOCAL_HOST2', str_replace('http://', 'https://', LOCAL_HOST) );
    }

	if($wpjam_google == 'useso'){			// 使用360 网站卫士常用前端公共库 CDN 服务
		add_filter('wpjam_html_replace', 'wpjam_useso_html_replace');
	}elseif($wpjam_google == 'ustc'){		// 使用中科大镜像服务
		add_filter('wpjam_html_replace', 'wpjam_ustc_html_replace');
	}elseif($wpjam_google == 'disabled'){	// 去除一切字体文件
		add_filter( 'gettext_with_context', 'wpjam_disable_google_fonts', 888, 4);
	}

	if(CDN_NAME){	// 如果有第三方 CDN
		//add_action('wpjam_rewrite_rules',	'wpjam_remote_image_rewrite_rules');
		add_action('generate_rewrite_rules','wpjam_remote_image_generate_rewrite_rules');
		add_filter('query_vars', 			'wpjam_remote_image_query_vars');
		add_action('template_redirect',		'wpjam_remote_image_template_redirect', 5);
		add_filter('the_content', 			'wpjam_cdn_content',1);
		// add_action('save_post', 			'wpjam_delete_cdn_content');
	}
	ob_start('wpjam_cdn_html_replace');
}

function wpjam_cdn_html_replace($html){
	return apply_filters('wpjam_html_replace',$html);
}

function wpjam_disable_google_fonts($translations, $text, $context, $domain ) {
	$google_fonts_contexts = array('Open Sans font: on or off','Lato font: on or off','Source Sans Pro font: on or off','Bitter font: on or off');
	if( $text == 'on' && in_array($context, $google_fonts_contexts ) ){
		$translations = 'off';
	}

	return $translations;
}

function wpjam_useso_html_replace($html){
	return str_replace(
		array(
			'https://ajax.googleapis.com',
			'https://fonts.googleapis.com'
		), 
		array(
			'//ajax.useso.com',
			'//fonts.useso.com'
		), 
		$html
	);
}

function wpjam_ustc_html_replace($html){
	return str_replace(
		array(
			'//fonts.googleapis.com',
			'//ajax.googleapis.com',
			'//themes.googleusercontent.com',
			'//fonts.gstatic.com',
		), 
		array(
			'//fonts.lug.ustc.edu.cn',
			'//ajax.lug.ustc.edu.cn',
			'//google-themes.lug.ustc.edu.cn',
			'//fonts-gstatic.lug.ustc.edu.cn',
		), 
		$html
	);
}



// 远程图片的 Rewrite 规则，第三方插件需要 flush rewrite
function wpjam_remote_image_generate_rewrite_rules($wp_rewrite){
    $new_rules[CDN_NAME.'/([^/]+)/image/([^/]+)\.([^/]+)?$']	= 'index.php?p=$matches[1]&'.CDN_NAME.'_image=$matches[2]&'.CDN_NAME.'_image_type=$matches[3]';
    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

// 远程图片的 Query String 变量
function wpjam_remote_image_query_vars($public_query_vars) {
    $public_query_vars[] = CDN_NAME.'_image';
    $public_query_vars[] = CDN_NAME.'_image_type';
    return $public_query_vars;
}

// 远程图片加载模板
function wpjam_remote_image_template_redirect(){
	$remote_image 		= get_query_var(CDN_NAME.'_image');
    $remote_image_type 	= get_query_var(CDN_NAME.'_image_type');

    if($remote_image && $remote_image_type){
    	include(WPJAM_BASIC_PLUGIN_DIR.'template/image.php');
    	exit;
	}
}

// function wpjam_delete_cdn_content($post_id){
// 	wp_cache_delete($post_id,'cdn_content_singular');
// 	wp_cache_delete($post_id,'cdn_content_list');
// }

function wpjam_cdn_content($content){
	// $type = (is_singular())?'singular':'list';
	// $cdn_content = wp_cache_get(get_the_ID(), 'cdn_content_'.$type);	// 获取内容中每张图片的缩略图和获取远程图，用了太多正则，这里做下缓存。
	// if($cdn_content === false){
		$cdn_content = preg_replace_callback('|<img.*?src=[\'"](.*?)[\'"].*?>|i','wpjam_cdn_content_image',$content);
		// wp_cache_set(get_the_ID(), $cdn_content, 'cdn_content_'.$type, 600);
	// }

	return $cdn_content;
}

function wpjam_cdn_content_image($matches){
	$img_url 	= trim($matches[1]);

	if(empty($img_url)) return;

	$width = $height = 0;

	if(preg_match('|<img.*?width=[\'"](.*?)[\'"].*?>|i', $matches[0], $width_matches)){
		$width = $width_matches[1];
	}

	if(preg_match('|<img.*?height=[\'"](.*?)[\'"].*?>|i', $matches[0], $height_matches)){
		$height = $height_matches[1];
	}

	$remote_img_url 	= wpjam_get_content_remote_img_url($img_url);								// 将远程图片抓取到本地
	$remote_img_url_1x 	= apply_filters( 'wpjam_content_image', $remote_img_url, $width, $height);	// 缩略内容中的图片
	$remote_img_url_2x	= apply_filters( 'wpjam_content_image', $remote_img_url, $width, $height, 2);

	$result = str_replace($img_url, $remote_img_url_1x, $matches[0]);
	return $result;
}

// 获取远程图片
function wpjam_get_content_remote_img_url($img_url){

	if( apply_filters('wpjam_remote_image', false, $img_url) && strpos($img_url, LOCAL_HOST) === false && strpos($img_url, CDN_HOST) === false ){	// 默认不去抓取远程图片，要第三方 CDN 启用才会
		$img_type = strtolower(pathinfo($img_url, PATHINFO_EXTENSION));
		if($img_type != 'gif'){
			$img_type	= ($img_type == 'png')?'png':'jpg';
			$img_url	= CDN_HOST.'/'.CDN_NAME.'/'.get_the_ID().'/image/'.md5($img_url).'.'.$img_type;
		}
	}

	$img_url = str_replace(LOCAL_HOST, CDN_HOST, $img_url);

	return $img_url;
}

// 通过 query string 强制刷新 CSS 和 JS
add_filter('script_loader_src',		'wpjam_cdn_loader_src',10,2);
add_filter('style_loader_src',		'wpjam_cdn_loader_src',10,2);
function wpjam_cdn_loader_src($src, $handle){
	if(get_option('timestamp')){
		$src = remove_query_arg(array('ver'), $src);
		$src = add_query_arg('ver',get_option('timestamp'),$src);
	}
	return $src;		
}