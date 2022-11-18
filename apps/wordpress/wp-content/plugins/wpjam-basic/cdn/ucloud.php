<?php
function wpjam_get_ucloud_thumbnail($img_url, $args=array()){
	if(!wpjam_is_image($img_url) || !wpjam_is_cdn_url($img_url)){
		return $img_url;
	}
	
	extract(wp_parse_args($args, array(
		'crop'		=> 1,
		'width'		=> 0,
		'height'	=> 0,
		'mode'		=> null,
		'format'	=> '',
		'interlace'	=> 0,
		'quality'	=> 0,
	)));
	
	if($height > 10000){
		$height = 0;
	}

	if($width > 10000){
		$height = 0;
	}

	if($width || $height){
		$arg['iopcmd']	= 'thumbnail';

		if($width && $height){
			$arg['type']	= 13;
			$arg['height']	= $height;
			$arg['width']	= $width;
		}elseif($width){
			$arg['type']	= 4;
			$arg['width']	= $width;
		}elseif($height){
			$arg['type']	= 5;
			$arg['height']	= $height;
		}

		if(strpos($img_url, 'iopcmd=thumbnail') === false){
			$img_url	= add_query_arg($arg, $img_url );
			$img_url	= $img_url.'#';
		}
	}

	return $img_url;
}

return 'wpjam_get_ucloud_thumbnail';