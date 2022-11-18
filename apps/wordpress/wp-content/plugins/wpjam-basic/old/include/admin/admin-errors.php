<?php

// 错误处理
function wpjam_admin_add_error($message='', $type='updated'){
	global $wpjam_errors;
	$wpjam_errors[$type][] = $message; 
}

function wpjam_admin_get_errors(){

	$removable_query_args	= wpjam_get_removable_query_args();

	if($removable_query_args = array_intersect($removable_query_args, array_keys($_GET))){
		foreach ($removable_query_args as $key) {
			if($key != 'message' && $key != 'settings-updated'){
				if($_GET[$key] === 'true' || $_GET[$key] === '1'){
					wpjam_admin_add_error('操作成功');
				}else{
					wpjam_admin_add_error($_GET[$key],'error');
				}
			}
		}
	}

	global $wpjam_errors;
	$msgs = '';
	foreach (array('updated', 'error') as $type) {
		$msg = '';
		if(isset($wpjam_errors[$type])){
			foreach ($wpjam_errors[$type] as $message) {
				if(is_wp_error($message)){
					$message = $message->get_error_code().'-'.$message->get_error_message();
				}
				$msg .= '<p><strong>'.$message.'</strong></p>';
			}
			$msg = '<div class="'.$type.'">'.$msg.'</div>';
		}
		$msgs .= $msg;
	}
	return $msgs;
}

function wpjam_admin_errors(){
	echo wpjam_admin_get_errors();
}