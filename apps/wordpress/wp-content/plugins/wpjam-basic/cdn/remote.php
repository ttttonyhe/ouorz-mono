<?php
if(did_action('init')){
	if(empty(CDN_NAME)){
		wp_die('你没开启云存储','你没开启云存储', ['response'=>404]);
	}

	global $post;

	$post 	= get_post(get_query_var('p'));
	$remote	= get_query_var(CDN_NAME);

	if(empty($remote)){
		wp_die('文件名不能为空','文件名不能为空', ['response'=>404]);
	}

	if(empty($post)){
		wp_die('文章不存在','文章不存在', ['response'=>404]);
	}

	$img_info	= pathinfo($remote);
	$filename	= $img_info['filename'];
	$extension	= $img_info['extension'];

	$url = '';
	if (preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($post->post_content), $matches)) {
		foreach ($matches[1] as $image_url) {
			if($filename == md5($image_url)){
				$url = $image_url;
				break;
			}
		}
	}

	if(!$url){
		wp_die('文章没有该图片','文章没有该图片', ['response'=>404]);
	}

	if(wpjam_doing_debug()){
		echo $url;
		exit;
	}

	switch ($extension) {
		case 'jpg':
			header('Content-Type: image/jpeg');
			//$img = imagecreatefromjpeg($url);
			$img = imagecreatefromstring(file_get_contents($url));
			imagejpeg($img,null,100);
			break;

		case 'png':
			header("Content-Type: image/png");
			$img = imagecreatefrompng($url);
			$background = imagecolorallocate($img, 0, 0, 0);
			imagecolortransparent($img, $background);
			imagealphablending($img, false);
			imagesavealpha($img, true);
			imagepng($img);
			break;

		case 'gif':
			header('Content-Type: image/gif');
			$img = imagecreatefromgif($url);
			// $background = imagecolorallocate($img, 0, 0, 0);
			// imagecolortransparent($img, $background);
			imagegif($img);
			break;
		
		default:
			# code...
			break;		
	}

	exit;

	// $image = wp_remote_get(trim($url));

	// if(is_wp_error($image)){
	// 	wp_die('原图不存在','原图不存在', ['response'=>404]);
	// }else{
	// 	header("HTTP/1.1 200 OK");
	// 	header("Content-Type: image/jpeg");
	// 	imagejpeg(imagecreatefromstring($image['body']),NULL,100);
	// }
}

add_action('init',function(){
	$GLOBALS['wp']->add_query_var(CDN_NAME);
	
	add_rewrite_rule(CDN_NAME.'/([0-9]+)/image/([^/]+)?$', 'index.php?p=$matches[1]&'.CDN_NAME.'=$matches[2]', 'top');

	// 远程图片加载模板
	add_action('template_redirect',	function(){
		if(get_query_var(CDN_NAME)){
			include __FILE__;
		}
	}, 5);

	add_filter('the_content', function($content){
		if(!preg_match_all('/<img.*?src=[\'"](.*?)[\'"].*?>/i', $content, $matches)){
			return $content;
		}

		$exceptions	= wpjam_cdn_get_setting('exceptions');	// 后台设置不加载的远程图片
		$exceptions	= $exceptions ? explode("\n", $exceptions) : [];

		$search	= $replace = [];

		foreach($matches[0] as $i => $img_tag){
			$img_url	= $matches[1][$i];

			if(empty($img_url)){
				continue;
			}

			foreach($exceptions as $exception){
				if(trim($exception) && strpos($img_url, trim($exception)) !== false){
					continue 2;
				}
			}

			if(wpjam_is_external_image($img_url, false)){
				$img_type	= strtolower(pathinfo($img_url, PATHINFO_EXTENSION));

				if($img_type != 'gif'){
					$img_type		= ($img_type == 'png')?'png':'jpg';
					$img_serach		= $img_url;
					$img_replace	= CDN_HOST.'/'.CDN_NAME.'/'.get_the_ID().'/image/'.md5($img_url).'.'.$img_type;

					$search[]		= $img_tag;
					$replace[]		= str_replace($img_serach, $img_replace, $img_tag);
				}
			}
		}

		if(!$search){
			return $content;
		}

		return str_replace($search, $replace, $content);
	}, 4);
});