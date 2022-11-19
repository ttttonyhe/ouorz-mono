<?php

function wpjam_topic_get_openid(){
	$current_user_id	= get_current_user_id();
	return get_user_meta($current_user_id, 'wpjam_openid', true);
}

function wpjam_topic_get_weixin_user($openid=''){
	if($openid == ''){
		if(!wpjam_topic_get_openid())	return false;

		$current_user_id	= get_current_user_id();
		$wpjam_weixin_user 	= get_transient('wpjam_weixin_user_'.$current_user_id);
		if($wpjam_weixin_user === false){
			$wpjam_weixin_user = wpjam_topic_remote_request('http://jam.wpweixin.com/api/get_user.json');
			if(is_wp_error($wpjam_weixin_user)){
				return $wpjam_weixin_user;
			}
			set_transient( 'wpjam_weixin_user_'.$current_user_id, $wpjam_weixin_user, DAY_IN_SECONDS*15 );	// 15天检查一次
		}
	}else{
		$wpjam_weixin_user = wp_cache_get($openid, 'wpjam_weixin_user');
		// if($wpjam_weixin_user === false){
			$wpjam_weixin_user = wpjam_topic_remote_request('http://jam.wpweixin.com/api/get_user.json?openid='.$openid);
		// 	if(is_wp_error($wpjam_weixin_user)){
		// 		return $wpjam_weixin_user;
		// 	}
		// 	wp_cache_set($openid, $wpjam_weixin_user, 'wpjam_weixin_user', HOUR_IN_SECONDS);	
		// }
	}

	return $wpjam_weixin_user;
}

function wpjam_topic_get_group_list(){
	return array(
		'wpjam'		=> 'WPJAM Basic',
		'weixin'	=> '微信机器人',
		'qiniu'		=> '七牛云存储',
		'xintheme'	=> 'xintheme',
		'share'		=> '资源分享',
		'project'	=> '项目交易',
		'wp'		=> '其他帖子'
	);
}

function wpjam_topic_remote_request($url,  $args=''){

	$openid 	= wpjam_topic_get_openid();
	$headers	= ($openid)?array('openid'=>$openid):array();

	$args = wp_parse_args( $args, array(
		'method'	=> 'GET',
		'body'		=> array(),
		'timeout'	=> 10,
		'sslverify'	=> false,
		'blocking'	=> true,	// 如果不需要立刻知道结果，可以设置为 false
		'stream'	=> false,	// 如果是保存远程的文件，这里需要设置为 true
		'filename'	=> null,	// 设置保存下来文件的路径和名字
		'headers'	=> $headers
	) );

	$method	= $args['method'];
	unset($args['method']);

	if($method == 'GET'){
		$response = wp_remote_get($url, $args);
	}elseif($method == 'POST'){
		$response = wp_remote_post($url, $args);
	}

	if(is_wp_error($response)){
		return $response;
	}

	$response = json_decode($response['body'],true);

	if(isset($response['errcode']) && $response['errcode']){
		return new WP_Error($response['errcode'],$response['errmsg']);
	}

	return $response;
}






function wpjam_topic_setting_page($title='', $description=''){
	global $current_admin_url;
	$form_fields = array(
		'qrcode'	=> array('title'=>'二维码',	'type'=>'view'),
		'code'		=> array('title'=>'验证码',	'type'=>'text',	'description'=>'验证码10分钟内有效！'),
		'scene'		=> array('title'=>'scene',	'type'=>'hidden'),
	);

	$nonce_action	= 'wpjam-topic';

	$current_user_id= get_current_user_id();

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
		$data		= wpjam_get_form_post($form_fields, $nonce_action, 'read');

		$response	= wpjam_topic_remote_request('http://jam.wpweixin.com/api/bind.json', array(
			'method'	=>'POST',
			'body'		=> $data
		));

		if(is_wp_error($response)){
			wpjam_admin_add_error($response->get_error_message(), 'error');
		}else{
			$wpjam_openid = $response['openid'];
			update_user_meta($current_user_id, 'wpjam_openid', $wpjam_openid);
			delete_transient('wpjam_weixin_user_'.$current_user_id);
			wp_redirect($current_admin_url);
			exit;
		}
		
	}else{
		$key		= md5(home_url().'_'.$current_user_id);
		$response	= wpjam_topic_remote_request('http://jam.wpweixin.com/api/get_qrcode.json?key='.$key);

		if(is_wp_error($response)){
			wpjam_admin_add_error($response->get_error_message(), 'error');
			wpjam_admin_add_error($response->get_error_message(), 'error');
		}else{
			$qrcode = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$response['ticket'];
			$form_fields['qrcode']['value']	= '<img srcset="'.$qrcode.' 2x" src="'.$qrcode.'" style="max-width:350px;" />';
			$form_fields['scene']['value']	= $response['scene'];
		}
	}

	$form_url		= $current_admin_url;

	$title			= ($title)?$title:'WordPress 问答';
	$description	= ($description)?$description:'<p>开始提问之前，需要绑定你的微信号。<br />请使用微信扫描下面的二维码，获取验证码之后提交即可完成绑定！</p>';
	
	echo '<h1>'.$title.'</h1>';
	echo $description;
	?>
	<style type="text/css">
	.form-table { max-width:640px; }
	.form-table th {width:60px; }
	</style>
	<?php

	wpjam_form($form_fields, $form_url, $nonce_action, '提交');
}


