<?php
/*
Plugin Name: SMTP
Plugin URI: http://wpjam.net/item/wpjam-basic/
Description: 使用 SMTP 发送邮件。
Version: 1.0
*/

add_action('phpmailer_init','wpjam_phpmailer_init');
function wpjam_phpmailer_init($phpmailer) {
	
	$phpmailer->IsSMTP(); 

	//$phpmailer->SMTPDebug  = 1;

	$phpmailer->SMTPAuth = true;
	$phpmailer->SMTPSecure = wpjam_basic_get_setting('smtp_ssl');
	
	if( isset($_POST['qzone_sync']) ){
		$phpmailer->Host 		= 'smtp.qq.com';
		$phpmailer->Port 		= 465;
		$phpmailer->Username	= wpjam_basic_get_setting('qq_number').'@qq.com';
		$phpmailer->Password	= wpjam_basic_get_setting('qq_password');
		$phpmailer->From		= wpjam_basic_get_setting('qq_number').'@qq.com';
		$phpmailer->FromName	= wpjam_basic_get_setting('qq_number');
		$phpmailer->ContentType	= 'text/html';
	}else{
		$phpmailer->Host		= wpjam_basic_get_setting('smtp_host'); 
		$phpmailer->Port		= wpjam_basic_get_setting('smtp_port');
		$phpmailer->Username	= wpjam_basic_get_setting('smtp_user');
		$phpmailer->Password	= wpjam_basic_get_setting('smtp_pass');
		$phpmailer->From		= wpjam_basic_get_setting('smtp_user');
		$phpmailer->FromName	= wpjam_basic_get_setting('smtp_mail_from_name');

		if(wpjam_basic_get_setting('smtp_reply_to_mail') && wpjam_basic_get_setting('smtp_reply_to_name')){
			$phpmailer->AddReplyTo(wpjam_basic_get_setting('smtp_reply_to_mail'),wpjam_basic_get_setting('smtp_reply_to_name'));
		}
	}
	$phpmailer->Sender = $phpmailer->From;
}

/*add_filter('wp_mail_from','wpjam_wp_mail_from');
function wpjam_wp_mail_from ($mail_from) {
	if( isset($_POST['qzone_sync']) ){
		return wpjam_basic_get_setting('qq_number').'@qq.com';
	}else{
		return wpjam_basic_get_setting('smtp_user');
	}
} 

add_filter('wp_mail_from_name','wpjam_wp_mail_from_name');
function wpjam_wp_mail_from_name ($mail_from_name) {
	if( isset($_POST['qzone_sync']) ){
		return wpjam_basic_get_setting('qq_number');
	}else{
		return wpjam_basic_get_setting('smtp_mail_from_name');
	}
}*/