<?php
function wpjam_get_aliyun_oss_thumbnail($img_url, $args=[]){
	if($img_url && (!wpjam_is_image($img_url) || !wpjam_is_cdn_url($img_url))){
		return $img_url;
	}
	
	$args	= wp_parse_args($args, [
		'mode'		=> null,
		'crop'		=> 1,
		'width'		=> 0,
		'height'	=> 0,
		'webp'		=> wpjam_cdn_get_setting('webp'),
		'interlace'	=> wpjam_cdn_get_setting('interlace'),
		'quality'	=> wpjam_cdn_get_setting('quality'),
		'watermark'	=> ''
	]);

	$raw_width	= $width	= $args['width'];
	$raw_height	= $height	= $args['height'];

	$width	= $width > 4096 ? '' : $width;
	$height	= $height > 4096 ? '' : $height;

	$thumb_arg	= '';

	if($width || $height){
		$mode	= $args['mode'];

		if(is_null($mode)){
			$crop	= $args['crop'] && ($width && $height);	// 只有都设置了宽度和高度才裁剪
			$mode	= $crop ? ',m_fill' : '';
		}

		$thumb_arg	.= '/resize'.$mode;

		if($width){
			$thumb_arg .= ',w_'.$width;
		}

		if($height){
			$thumb_arg .= ',h_'.$height;
		}
	}

	if($args['webp'] && wpjam_is_webp_supported()){
		$thumb_arg	.= '/format,webp';
	}else{
		if($interlace = $args['interlace']){
			$thumb_arg	.= '/interlace,1';
		}
	}

	if($quality = $args['quality']){
		$thumb_arg	.= '/quality,Q_'.$quality;
	}

	if((!empty($args['content']) || !empty($args['watermark'])) 
		&& strpos($img_url, 'watermark/') === false 
		&& strpos($img_url, '.gif') === false 
		&& !isset($_GET['preview'])
	){
		$watermark	= $args['watermark'] ?: wpjam_cdn_get_setting('watermark');

		if($raw_width >= (int)wpjam_cdn_get_setting('wm_width') 
			&& $raw_height >= (int)wpjam_cdn_get_setting('wm_height') 
			&& $watermark && strpos($watermark, CDN_HOST.'/') !== false
		){
			if($watermark = str_replace(CDN_HOST.'/', '', $watermark)){
				$thumb_arg	.= '/watermark,image_'.str_replace(['+','/'], ['-','_'], base64_encode($watermark));

				$watermark_args	= wp_parse_args($args, [
					'dissolve'	=> wpjam_cdn_get_setting('dissolve') ?: '100',
					'gravity'	=> wpjam_cdn_get_setting('gravity', 'SouthEast'),
					'dx'		=> wpjam_cdn_get_setting('dx', 10),
					'dy'		=> wpjam_cdn_get_setting('dy', 10),
					'ws'		=> 0
				]);

				$dissolve	= $watermark_args['dissolve'];

				if($dissolve && $dissolve != 100){
					$thumb_arg	.= ',t_'.$dissolve;
				}

				if($gravity = $watermark_args['gravity']){
					$gravity_options = [
						'SouthEast'	=> 'se',
						'SouthWest'	=> 'sw',
						'NorthEast'	=> 'ne',
						'NorthWest'	=> 'nw',
						'Center'	=> 'center',
						'West'		=> 'west',
						'East'		=> 'east',
						'North'		=> 'north',
						'South'		=> 'south',
					];

					if(isset($gravity_options[$gravity])){
						$thumb_arg	.= ',g_'.$gravity_options[$gravity];
					}elseif(in_array($gravity, $gravity_options)){
						$thumb_arg	.= ',g_'.$gravity;
					}
				}

				if($dx	= $watermark_args['dx']){
					$thumb_arg	.= ',x_'.$dx;
				}
				
				if($dy	= $watermark_args['dy']){
					$thumb_arg	.= ',y_'.$dy;
				}
			}
		}
	}

	if($thumb_arg){
		if($query = parse_url($img_url, PHP_URL_QUERY)){
			$img_url	= str_replace('?'.$query, '', $img_url);
			$query_args	= wp_parse_args($query);
		}else{
			$query_args	= [];
		}

		$query_args['x-oss-process']	= 'image'.$thumb_arg;

		$img_url	= add_query_arg($query_args, $img_url);
	}

	return $img_url;
}

return 'wpjam_get_aliyun_oss_thumbnail';