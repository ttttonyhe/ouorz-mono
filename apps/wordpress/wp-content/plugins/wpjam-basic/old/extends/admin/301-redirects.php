<?php
add_filter('wpjam_basic_sub_pages', 'wpjam_301_redirects_admin_pages');
function wpjam_301_redirects_admin_pages($wpjam_pages){
	$wpjam_pages['301-redirects'] = array(
		'menu_title'	=>'301跳转', 
		'function'		=>'option',   
		'page_type'		=>'default'
	);
	return $wpjam_pages;
}

add_filter('wpjam_settings', 'wpjam_301_redirects_settings');
function wpjam_301_redirects_settings($wpjam_settings){
	$wpjam_301_redirects = get_option('301-redirects');
	$filds = array();
	$counter = 0;
	if($wpjam_301_redirects){
		$request = $wpjam_301_redirects['request'];
		$destination = $wpjam_301_redirects['destination'];

		$total = count($request);

		while ($counter < $total) {
			if($request[$counter] && $destination[$counter]){
				$title = $counter+1;
				$title = '#'.$title;
				$fields['redirect_'.$counter] = array(
					'title'			=>$title, 
					'request'		=>$request[$counter], 
					'destination'	=>$destination[$counter], 'type'=>'');
			}
			$counter ++;
		}
	}

	$fields['redirect_'.$counter] = array('title'=>'新增 ', 'request'=>'', 'destination'=>'', 'type'=>'');

	$sections = array('wpjam-301-redirects'  => array(
		'title'	 => '', 
		'fields'	=> $fields, 
		'summary'   => '<p>301 跳转只能跳转 404 页面到正常页面，可以正常访问页面无法设置 301 跳转。</p>'
	));

	$wpjam_settings['301-redirects'] = array('sections'=>$sections, 'field_callback'=>'wpjam_301_redirects_field_callback');
	return $wpjam_settings;
}

function wpjam_301_redirects_field_callback($args) {
	echo '<input type="url" name="301-redirects[request][]" value="'.$args['request'].'" class="regular-text" />
	 >>>  
	<input type="url" name="301-redirects[destination][]" value="'.$args['destination'].'" class="regular-text" />';
}

