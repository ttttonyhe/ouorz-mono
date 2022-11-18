<?php
global $post;
$post 	= get_post(get_query_var('p'));
$md5	= get_query_var('qiniu_image');
$type	= get_query_var('qiniu_image_type');


if($post){
	// do nothing
}else{
	wp_die('该日志不存在','该日志不存在',array( 'response' => 404 ));
}

$preg = preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($post->post_content), $matches);

$url = '';
if ($preg) {
	foreach ($matches[1] as $image_url) {
		if($md5 == md5($image_url)){
			$url = $image_url;
			break;
		}
	}
}

if(!$url){
	wp_die('该日志没有图片','该日志没有图片',array( 'response' => 404 ));
}

if(isset($_GET['url']) && $_GET['url']){
	echo $url;
}else{
	if($url){
		switch ($type) {
			case 'jpg':
				header('Content-Type: image/jpeg');
            	//$img = imagecreatefromjpeg($url);
            	$img = imagecreatefromstring(file_get_contents($url));
            	imagejpeg($img,null,100);
				break;

			case 'png':
				header("Content-Type: image/png");
				//imagepng(imagecreatefromstring(get_url_contents('http://blog.wpjam.com/thumb/'.$_GET['p'].'.png')));
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

		// $image = wp_remote_get(trim($url));

		// if(is_wp_error($image)){
		// 	wp_die('原图不存在','原图不存在',array( 'response' => 404 ));
		// }else{
		// 	header("HTTP/1.1 200 OK");
		// 	header("Content-Type: image/jpeg");
		// 	imagejpeg(imagecreatefromstring($image['body']),NULL,100);
		// }

	}else{
		wp_die('该日志没有图片','该日志没有图片',array( 'response' => 404 ));
	}
}