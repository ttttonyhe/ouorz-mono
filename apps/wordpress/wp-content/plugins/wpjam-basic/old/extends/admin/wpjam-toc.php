<?php
add_filter('wpjam_basic_sub_pages','wpjam_basic_add_toc_sub_page');
function wpjam_basic_add_toc_sub_page($subs){
	$subs['wpjam-toc']	= array('menu_title'=>'文章目录',		'function'=>'option',	'option_name'=>'wpjam-basic',	'page_type'=>'default');
	return $subs;
}

add_filter('wpjam-toc_sections', 'wpjam_toc_sections');
function wpjam_toc_sections($sections){
	
	$toc_fields = array(
		'toc_depth'				=> array('title'=>'目录显示到第几级',		'type'=>'select',	'options'=>array( '1'=>'h1','2'=>'h2','3'=>'h3','4'=>'h4','5'=>'h5','6'=>'h6')),
    	'toc_individual'		=> array('title'=>'文章编辑页面选项',		'type'=>'checkbox',	'description'=>'允许在每篇文章编辑页面单独设置是否显示文章目录以及显示到第几级。'),
		'toc_auto'				=> array('title'=>'自动插入脚本',			'type'=>'checkbox', 'description'=>'自动插入 JavaScript 和 CSS 代码。'),
		'toc_script'			=> array('title'=>'JavaScript 代码',		'type'=>'textarea',	'description'=>'如果你没有选择自动插入脚本，可以将下面的 JavaScript 代码复制你主题的 JavaScript 文件中。'),
		'toc_css'				=> array('title'=>'CSS 代码',			'type'=>'textarea',	'description'=>'根据你的主题对下面的 CSS 代码做适当的修改。<br />如果你没有选择自动插入脚本，可以将下面的 CSS 代码复制你主题的 CSS 文件中。'),
    	'toc_copyright'			=> array('title'=>'版权信息',				'type'=>'checkbox', 'description'=>'在文章目录下面显示版权信息。')
	);

	return array( 'wpjam-toc' => array('title'=>'', 'fields'=>$toc_fields ));
}

add_filter('wpjam_post_options','wpjam_toc_post_options');
function wpjam_toc_post_options($wpjam_options){
	if(wpjam_basic_get_setting('toc_individual')){
		$wpjam_options['wpjam-toc'] = array(
			'title'			=> '文章目录设置',
			'context'		=> 'side',
			'fields'		=> array(
				'toc_hidden'	=> array('title'=>'',				'type'=>'checkbox',		'description'=>'隐藏文章目录'),
				'toc_depth'		=> array('title'=>'显示到第几级目录：',	'type'=>'select',		'options'=>array('1'=>'h1','2'=>'h2','3'=>'h3','4'=>'h4','5'=>'h5','6'=>'h6',),	'description'=>'')
			)
		);
	}

	return $wpjam_options;
}


add_action('save_post', 'wpjam_toc_save_post',990,2);
function wpjam_toc_save_post($post_id, $post){
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;

	if ( ! current_user_can( 'edit_post', $post_id ) ) 
		return;

	wp_cache_delete($post_id,'wpjam-toc');
}