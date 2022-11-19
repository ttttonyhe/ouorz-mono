<?php
function wpjam_get_volc_imagex_thumbnail($img_url, $args=[]){
	if($img_url && (!wpjam_is_image($img_url) || !wpjam_is_cdn_url($img_url))){
		return $img_url;
	}

	if($template = wpjam_cdn_get_setting('volc_imagex_template')){
		$width	= (int)($args['width'] ?? 0);
		$height	= (int)($args['height'] ?? 0);
		$webp	= $args['webp'] ?? wpjam_cdn_get_setting('webp');

		if($webp && wpjam_is_webp_supported()){
			$template	= explode('.', $template)[0].'.webp';
		}

		if(strpos($img_url, '~tplv-')){
			$img_url	= explode('~tplv-', $img_url)[0];
		}elseif(strpos($img_url, '?')){
			if($query = parse_url($img_url, PHP_URL_QUERY)){
				$img_url	= str_replace('?'.$query, '', $img_url);
			}
		}

		return $img_url.str_replace(
			['resize_width', 'resize_height', 'crop_width', 'crop_height'],
			[$width, $height, $width, $height],
			$template
		);
	}

	return $img_url;
}

return 'wpjam_get_volc_imagex_thumbnail';