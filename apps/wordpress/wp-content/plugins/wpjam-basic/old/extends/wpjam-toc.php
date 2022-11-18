<?php 
/*
Plugin Name: 文章目录
Plugin URI: http://blog.wpjam.com/project/wpjam-toc/
Description: 自动根据文章内容里的子标题提取出文章目录，并显示在内容前。
Version: 1.0
*/

//内容中自动加入文章目录
add_filter('the_content','wpjam_toc_content');
function wpjam_toc_content($content){
	if(is_singular()){

		if(get_post_meta(get_the_ID(),'toc_hidden',true) == false){

			global $toc_count;
			global $toc;
			$toc = array();
			$toc_count = 0;

			$toc_depth = get_post_meta(get_the_ID(),'toc_depth',true);
			if(!$toc_depth) $toc_depth = wpjam_basic_get_setting('toc_depth');

			if($toc_depth == 1 ){
				$regex = '#<h1(.*?)>(.*?)</h1>#';
			}else{
				$regex = '#<h([1-'.$toc_depth.'])(.*?)>(.*?)</h\1>#';
			}
			$content = preg_replace_callback( $regex, 'wpjam_toc_replace_heading', $content);

			if(!has_shortcode( $content, 'toc') && $toc_count ){
				$content = wpjam_get_toc().$content;
			}
		}
	}

	return $content;
}

function wpjam_toc_replace_heading($content) {
	global $toc_count;
	global $toc;
	$toc_count ++;

	$toc[] = array('text' => trim(strip_tags($content[3])), 'depth' => $content[1], 'count' => $toc_count);

	return "<h{$content[1]} {$content[2]}><a name=\"toc-{$toc_count}\"></a>{$content[3]}</h{$content[1]}>";
}

// 根据 $TOC 数组输出文章目录 HTML 代码 
function wpjam_get_toc(){
	global $toc;

	$index = wp_cache_get(get_the_ID(),'wpjam-toc');

	if($index === false && $toc){

		$index = '<ul>'."\n";
		$prev_depth='';
		$to_depth = 0;

		foreach($toc as $toc_item){
			$toc_depth = $toc_item['depth'];
			if($prev_depth){
				if($toc_depth == $prev_depth){
					$index .= '</li>'."\n";
				}elseif($toc_depth > $prev_depth){
					$to_depth++;
					$index .= '<ul>'."\n";
				}else{
					$to_depth2 = ( $to_depth > ($prev_depth - $toc_depth) )? ($prev_depth - $toc_depth) : $to_depth;

					if($to_depth2){

						for ($i=0; $i<$to_depth2; $i++){
							$index .= '</li>'."\n".'</ul>'."\n";
							$to_depth--;
						}
					} 
					
					$index .= '</li>';
				}
			}
			$index .= '<li><a href="#toc-'.$toc_item['count'].'">'.$toc_item['text'].'</a>';
			$prev_depth = $toc_item['depth'];
		}

		for($i=0; $i<=$to_depth; $i++){
			$index .= '</li>'."\n".'</ul>'."\n";
		}

		wp_cache_set(get_the_ID(), $index, 'wpjam-toc', 360000);
	}

	if(wpjam_basic_get_setting('toc_copyright')){
		$index .= '<a href="http://blog.wpjam.com/project/wpjam-toc/"><small>WPJAM TOC</small></a>'."\n";
	}

	$index = '<div id="toc-container">'."\n".'<div id="toc">'."\n\n".'<strong>文章目录</strong><span>[隐藏]</span>'."\n".$index.'</div>'."\n".'</div>'."\n";

	return $index;
}

// 使用 Shortcode 方式插入
add_shortcode('toc', 'wpjam_toc_shortcode');
function wpjam_toc_shortcode($atts, $content='') {
	if(is_singular()){
		return wpjam_get_toc();
	}else{
		return '';
	}
}

if(wpjam_basic_get_setting('toc_auto')){
	add_action('wp_head', 'wpjam_toc_head');
	function wpjam_toc_head(){
		if(is_singular()){
			echo '<script type="text/javascript">'."\n".wpjam_basic_get_setting('toc_script')."\n".'</script>'."\n";
			echo '<style type="text/css">'."\n".wpjam_basic_get_setting('toc_css')."\n".'</style>'."\n";
		}
	}
}


