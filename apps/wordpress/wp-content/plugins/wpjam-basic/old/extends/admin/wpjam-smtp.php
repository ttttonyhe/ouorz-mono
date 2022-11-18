<?php
add_filter('wpjam_basic_sub_pages','wpjam_basic_add_smtp_sub_page');
function wpjam_basic_add_smtp_sub_page($subs){
	$subs['wpjam-smtp']	= array('menu_title'=>'发信设置',	'function'=>'tab');
	return $subs;
}

function wpjam_smtp_tabs(){
	return array(
		'smtp'	=> array('title'=>'发信设置',	'function'=>'wpjam_smtp_setting_page'),
		'send'	=> array('title'=>'发送测试',	'function'=>'wpjam_smtp_send_page'),
	);
}

function wpjam_smtp_setting_page(){
	wpjam_option_page('wpjam-basic',array('page_type'=>'default'));
}

add_filter('wpjam-smtp_sections', 'wpjam_smtp_sections');
function wpjam_smtp_sections($sections){
	
	$smtp_fields = array(
		'smtp_mail_from_name'	=> array('title'=>'发送者姓名',	'type'=>'text'),

		'smtp'		=> array('title'=>'SMTP 设置',	'type'=>'fieldset','fields'=>array(
			'smtp_host'	=> array('title'=>'地址',	'type'=>'text'),
			'smtp_ssl'	=> array('title'=>'发送协议',	'type'=>'text'),
			'smtp_port'	=> array('title'=>'SSL端口',	'type'=>'number'),
			'smtp_user'	=> array('title'=>'邮箱账号',	'type'=>'email'),
			'smtp_pass'	=> array('title'=>'邮箱密码',	'type'=>'password'),
		)),

		'smtp_reply'=> array('title'=>'默认回复',	'type'=>'fieldset','fields'=>array(
			'smtp_reply_to_mail'	=> array('title'=>'邮箱地址',	'type'=>'email'),
			'smtp_reply_to_name'	=> array('title'=>'邮箱姓名',	'type'=>'text'),
		)),
	);

	return array(
		'wpjam-smtp'	=> array(
			'title'		=>'', 
			'fields'	=>$smtp_fields, 
			'summary'	=>'<p>点击这里查看：<a target="_blank" href="http://blog.wpjam.com/m/gmail-qmail-163mail-imap-smtp-pop3/">常用邮箱的 SMTP 设置</a>。</p>'
		)
	);
}

function wpjam_smtp_send_page(){
	global $current_admin_url;

	$form_fields = array(
		'to'		=> array('title'=>'收件人',	'type'=>'email'),
		'subject'	=> array('title'=>'主题',	'type'=>'text'),
		'message'	=> array('title'=>'内容',	'type'=>'textarea',	'style'=>'max-width:640px;',	'rows'=>8),
	);

	$nonce_action = 'send_mail';

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
		$data	= wpjam_get_form_post($form_fields, $nonce_action);
		foreach ($form_fields as $key => $form_field) {
			$form_fields[$key]['value']	= $data[$key];
		}

		extract($data);
		
		if(wp_mail($to, $subject, $message)){
			wpjam_admin_add_error('发送成功');
		}else{
			wpjam_admin_add_error('发送失败','error');
		}
	}
	?>

	<h2>发送测试</h2>

	<?php wpjam_form($form_fields, $current_admin_url, $nonce_action, '发送'); ?>
	<?php
}

add_action('wp_mail_failed', 'wpjam_smtp_mail_failed');
function wpjam_smtp_mail_failed($mail_failed){
	trigger_error($mail_failed->get_error_code().$mail_failed->get_error_message());
	var_dump($mail_failed);
}