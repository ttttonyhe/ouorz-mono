<?php

add_action( 'admin_notices', 'wpjam_admin_notices' );
function wpjam_admin_notices() {
	if(!current_user_can('manage_options')){
		return;
	}

	if(!empty($_GET['notice_time']) && !empty($_GET['notice_key'])){
		wpjam_delete_admin_notice($_GET['notice_time'], $_GET['notice_key']);
	}

	$admin_notices	= get_option('admin_notices');

	if(!$admin_notices){
		return;
	}

	krsort($admin_notices);
	foreach ($admin_notices as $time => $admin_notice_list) {

		if(!$admin_notice_list){
			unset($admin_notices[$time]);
			update_option('admin_notices', $admin_notices);
			continue;
		}

		foreach ($admin_notice_list as $key => $admin_notice) {

			extract(wp_parse_args( $admin_notice, array(
				'page'		=> '',
				'type'		=> 'updated',
				'tab'		=> '',
				'link'		=> '',
				'notice'	=> ''
			)));

			if($link && $page){
				$link	= admin_url('admin.php?notice_time='.$time.'&notice_key='.$key.'&page='.$page);
				$link	= ($tab)?$link.'&tab='.$tab:$link;
				$link	= '<a href="'.$link.'">查看详情</a> | ';
			}

			$hide_link	= '<a href="javascript:" class="admin_notice_hide" data-key="'.$key.'" data-time="'.$time.'">我知道了</a>';

			echo '<div id="admin_notice_'.$key.'_'.$time.'" class="'.$type.'"><p><strong>'.$notice.'</strong>'.$link.$hide_link.'</p></div>';
		}	
	}
}


function wpjam_add_admin_notice($admin_notice){
	$admin_notices	= get_option('admin_notices');
	$admin_notices	= ($admin_notices)?$admin_notices:array();

	if(count($admin_notices) > 20){
		array_pop($admin_notices);	// 删除最后一个
	}

	$time	= time();
	$key 	= md5(serialize($admin_notice));

	$admin_notices[$time][$key] = $admin_notice;
	krsort($admin_notices);

	update_option('admin_notices', $admin_notices);
}

function wpjam_delete_admin_notice($time, $key){
	$admin_notices	= get_option('admin_notices');
	if(!$admin_notices || empty($admin_notices[$time]) || empty($admin_notices[$time][$key])){
		return false;
	}

	unset($admin_notices[$time][$key]);
	if(empty($admin_notices[$time])){
		unset($admin_notices[$time]);
	}

	// if($admin_notices){
		update_option('admin_notices', $admin_notices);
	// }else{
		update_option('admin_notices', '');
	// }

	global $wpdb;
}

add_action('wp_ajax_delete_admin_notice', 'wpjam_ajax_delete_admin_notice_action_callback');
function wpjam_ajax_delete_admin_notice_action_callback(){
	check_ajax_referer( "wpjam_setting_nonce" );
	$key	= $_POST['key'];
	$time	= $_POST['time'];
	wpjam_delete_admin_notice($time, $key);
}