<?php
// 注册设置选项
add_action('admin_init', 'wpjam_register_setting_admin_init');
function wpjam_register_setting_admin_init(){
	// 只有在 options.php 页面的时候才需要注册选项
	$option_name = isset($_POST['option_page'])?$_POST['option_page']:''; // options.php 页面

	if(empty($option_name)) return;

	if($wpjam_setting = wpjam_get_option_setting($option_name)){
		extract($wpjam_setting);
		register_setting($option_group, $option_name, $field_validate);	// 只需注册字段，add_settings_section 和 add_settings_field 可以在具体选项页面添加
	}
}

// 获取某个选项的所有设置
function wpjam_get_option_setting($option_name){
	$wpjam_settings = apply_filters('wpjam_settings', array());
	if(!$wpjam_settings) return false;

	if(empty($wpjam_settings[$option_name])) return false;

	return wp_parse_args($wpjam_settings[$option_name], array(
		'option_group'	=> $option_name, 
		'option_page'	=> $option_name, 
		'sections'		=> false, 
		'fields'		=> false, 
		'field_validate'=> 'wpjam_option_field_validate', 
		'field_callback'=> 'wpjam_option_field_callback',
	) );
}

// section 统一回调函数
function wpjam_option_section_callback($section){
	global $section_summary;
	if(isset($section_summary[$section['id']])){
		echo wpautop($section_summary[$section['id']]);
	}
}

// 后台选项页面
function wpjam_option_page($option_name, $args=array()){
	if(!$option_name) return;

	$wpjam_setting = wpjam_get_option_setting($option_name);
	if(!$wpjam_setting)	return;
	
	extract($wpjam_setting);
	extract(wp_parse_args($args, array('page_title'=>'', 'page_type'=>'tab')));

	if(!$sections) return;

	if(count($sections) == 1){
		$page_type	= 'default';
	}

	do_action($option_name.'_option_page');

	if(is_multisite() && is_network_admin()){	
		if($_SERVER['REQUEST_METHOD'] == 'POST'){	// 如果是 network 就自己保存到数据库		
			$value 	= wpjam_option_field_validate($_POST[$option_name], $option_name);
			update_site_option( $option_name,  $value);
			wpjam_admin_add_error(__( 'Options saved.' ));
			wpjam_admin_errors();
		}
		echo '<form action="'.add_query_arg(array('settings-updated'=>'true'), wpjam_get_current_page_url()).'" method="POST">';
	}else{
		echo '<form action="options.php" method="POST">';
	}

	$wpjam_option = wpjam_get_option($option_name);

	global $section_summary;
	$section_summary = array();

	foreach ($sections as $section_id => $section) {
		$section_title		= isset($section['title'])?$section['title']:'';
		$section_callback	= isset($section['callback'])?$section['callback']:'';
		
		if(isset($section['summary'])){
			$section_summary[$section_id]	= $section['summary'];
			$section_callback				= 'wpjam_option_section_callback';
		}
		
		add_settings_section($section_id, $section_title, $section_callback, $option_page);

		if(!$section['fields']) continue;
		
		foreach ($section['fields'] as $key => $field) {
			$field['key']	= $key;
			$field['name']	= $option_name.'['.$key.']';
			$field_title	= '<label for="'.$key.'">'.$field['title'].'</label>';

			if($field['type'] == 'fieldset'){
				
				foreach ($field['fields'] as $sub_key => $sub_field) {
					$field['fields'][$sub_key]['value']	= isset($sub_field['value'])?$sub_field['value']:(isset($wpjam_option[$sub_key])?$wpjam_option[$sub_key]:'');
					$field['fields'][$sub_key]['name']	= $option_name.'['.$sub_key.']';
				}
			}else{
				$field['value']	= isset($field['value'])?$field['value']:(isset($wpjam_option[$key])?$wpjam_option[$key]:'');
			}

			add_settings_field($key, $field_title, $field_callback, $option_page, $section_id, $field);	
		}
	}

	settings_fields($option_group);

	if($page_type == 'tab'){
		wpjam_do_settings_sections($option_page);
		if(!empty($_GET['settings-updated'])){
			if(($wpjam_option = wpjam_get_option($option_name)) && isset($wpjam_option['current_tab'])){
				echo '<input type="hidden" name="'.$option_name.'[current_tab]" id="current_tab" value="'.$wpjam_option['current_tab'].'" />';
			}
		}else{
			echo '<input type="hidden" name="'.$option_name.'[current_tab]" value="" />';
		}
	}else{
		echo ($page_title)?((preg_match("/<[^<]+>/",$page_title))?$page_title:'<h1>'.$page_title.'</h1>'):'';// 如 $page_title 里面有 <h1> <h2> 标签，就不再加入 <h2> <h3> 标签了。

		settings_errors();
		do_settings_sections($option_page);
	}
	submit_button();
	echo '</form>'; 
}

// 拷贝自 do_settings_sections 函数，用于选项页面 tab 显示选项。
function wpjam_do_settings_sections($page){
	global $wp_settings_sections, $wp_settings_fields;

	if (!isset($wp_settings_sections[$page])) return;

	$sections = (array)$wp_settings_sections[$page];

	echo '<h1 class="nav-tab-wrapper">';
	foreach ( $sections as $section_id => $section ) {
		echo '<a class="nav-tab" href="javascript:;" id="tab-title-'.$section_id.'">'.$section['title'].'</a>';
	}
	echo '</h1>';

	settings_errors();

	foreach ( $sections as $section_id => $section ) {
		if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section_id] ) ) continue;

		echo '<div id="tab-'.$section_id.'" class="div-tab hidden">';

		if ( $section['title'] ) echo "<h2>{$section['title']}</h2>\n";

		if ( $section['callback'] ) call_user_func( $section['callback'], $section );

		echo '<table class="form-table">';
		do_settings_fields($page, $section_id);
		echo '</table>';
		
		echo '</div>';
	}
}

// 选项字段基本验证函数
function wpjam_option_field_validate($value, $option_name = ''){
	global $plugin_page, $current_tab;

	// 用于下面数据获取时候页面判断
	$referer_origin	= parse_url(wpjam_get_referer());
	wp_parse_str($referer_origin['query'], $referer_args);

	$plugin_page	= isset($referer_args['page'])?$referer_args['page']:'';
	$current_tab	= isset($referer_args['tab'])?$referer_args['tab']:'';

	$option_name	= ($option_name)?$option_name:str_replace('sanitize_option_', '', current_filter()); 
	$wpjam_setting	= wpjam_get_option_setting($option_name);
	$sections 		= $wpjam_setting['sections'];

	if($sections){
		foreach ($sections as $section) {
			if($section['fields']){
				foreach ($section['fields'] as $key => $field) {
					if($field['type'] == 'fieldset' && $field['fields']){
						foreach ($field['fields'] as $sub_key => $sub_field) {
							if($sub_field['type'] == 'checkbox' && empty($value[$sub_key])){	// 如果是 checkbox，POST 的时候空是没有的。
								$value[$sub_key] = 0;
							}
						}
					}elseif($field['type'] == 'checkbox' && empty($value[$key])){				// 如果是 checkbox，POST 的时候空是没有的。
						$value[$key] = 0;
					}elseif( $field['type'] =='file' && isset($_FILES[$key]) && isset($_FILES[$key]['name'])) {
						if ( $upload_file = wp_handle_upload( $_FILES[$key], array( 'test_form' => false ) ) ) {
							$value[$key] = $upload_file['url'];
						}
					}
				}
			}
		}
	}

	$current	= wpjam_get_option($option_name);;
	$value		= wp_parse_args($value, $current);

	return apply_filters( $option_name.'_field_validate', $value );
}

// 选项的字段回调函数，显示具体 HTML 结构
function wpjam_option_field_callback($field) {
	echo wpjam_get_field_html($field);
}