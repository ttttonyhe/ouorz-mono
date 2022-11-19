<?php
function wpjam_get_qcloud_cos_thumbnail($img_url, $args=[]){
	if($img_url && (!wpjam_is_image($img_url) || !wpjam_is_cdn_url($img_url))){
		return $img_url;
	}

	$args	= wp_parse_args($args, [
		'crop'		=> 1,
		'width'		=> 0,
		'height'	=> 0,
		'webp'		=> wpjam_cdn_get_setting('webp'),
		'interlace'	=> wpjam_cdn_get_setting('interlace'),
		'quality'	=> wpjam_cdn_get_setting('quality'),
		'watermark'	=> wpjam_cdn_get_setting('watermark'),
		'dissolve'	=> wpjam_cdn_get_setting('dissolve') ?: 100,
		'gravity'	=> wpjam_cdn_get_setting('gravity') ?: 'SouthEast',
		'dx'		=> wpjam_cdn_get_setting('dx') ?: 10,
		'dy'		=> wpjam_cdn_get_setting('dy') ?: 10,
		'spcent'	=> 10
	]);

	$raw_width	= $width	= $args['width'];
	$raw_height	= $height	= $args['height'];

	$width	= $width > 10000 ? '' : $width;
	$height	= $height > 10000 ? '' : $height;

	$thumb_arg	= '';

	if($width || $height){
		$thumb_arg	.= '/thumbnail/';

		if($width && $height){
			$thumb_arg	.= '!'.$width.'x'.$height.'r';

			if($args['crop']){	// 只有都设置了宽度和高度才裁剪
				$thumb_arg	.= '|imageMogr2/gravity/Center/crop/'.$width.'x'.$height.'';
			}
		}else{
			$thumb_arg	.= $width.'x'.$height;
		}
	}

	if($args['webp'] && wpjam_is_webp_supported()){
		$thumb_arg	.= '/format/webp';
	}else{
		if($interlace = $args['interlace']){
			$thumb_arg	.= '/interlace/'.$interlace;
		}
	}

	if($quality = $args['quality']){
		$thumb_arg	.= '/quality/'.$quality;
	}

	if($thumb_arg){
		$thumb_arg	= 'imageMogr2'.$thumb_arg;
	}

	if(!empty($args['content']) && strpos($img_url, '.gif') === false){
		$watermark	= $args['watermark'];

		if($raw_width >= (int)wpjam_cdn_get_setting('wm_width') 
			&& $raw_height >= (int)wpjam_cdn_get_setting('wm_height') 
			&& $watermark && strpos($watermark, CDN_HOST.'/') !== false
		){
			$watermark	= str_replace(['+','/'], ['-','_'], base64_encode($watermark));
			$thumb_arg	.= $thumb_arg ? '|' : '';
			$thumb_arg	.= 'watermark/1/image/'.$watermark;

			foreach(['dissolve', 'gravity', 'dx', 'dy', 'spcent'] as $key){
				$thumb_arg	.= '/'.$key.'/'.$args[$key];
			}
		}
	}

	if($thumb_arg){
		if($query = parse_url($img_url, PHP_URL_QUERY)){
			$img_url	= str_replace('?'.$query, '', $img_url);

			if($query_args	= wp_parse_args($query)){
				$query_args	= array_filter($query_args, function($v, $k){
					return strpos($k, 'imageMogr2/') === false && strpos($k, 'watermark/') === false;
				}, ARRAY_FILTER_USE_BOTH);
			}
		}else{
			$query_args	= [];
		}

		$query_args[$thumb_arg]	= '';

		return add_query_arg($query_args, $img_url);
	}

	return $img_url;
}

return 'wpjam_get_qcloud_cos_thumbnail';