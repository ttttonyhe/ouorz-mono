<?php
//添加用户注册时间和其他字段
add_filter('manage_users_columns','wpjam_manage_users_columns');
function wpjam_manage_users_columns($column_headers){
	unset($column_headers['name']);  //隐藏姓名
	wpjam_array_push($column_headers, array('nickname'=>'昵称'), 'email');
	$column_headers['registered']	= '注册时间';
	return $column_headers;
}

//显示用户注册时间和其他字段
add_filter('manage_users_custom_column', 'wpjam_manage_users_custom_column',11,3);
function wpjam_manage_users_custom_column($value, $column_name, $user_id){
	if($column_name=='registered'){
		$user = get_userdata($user_id);
		return get_date_from_gmt($user->user_registered);
	}elseif($column_name=='nickname'){
		$user = get_userdata($user_id);
		return $user->display_name;
	}else{
		return $value;
	}
}

//设置注册时间为可排序列.
add_filter( "manage_users_sortable_columns", 'wpjam_manage_users_sortable_columns' );
function wpjam_manage_users_sortable_columns($sortable_columns){
	$sortable_columns['registered'] = 'registered';
	return $sortable_columns;
}

//按注册时间排序.
if(wpjam_basic_get_setting('order_by_registered')){
	add_action( 'pre_user_query', 'wpjam_pre_user_query_order_by_registered' );
	function wpjam_pre_user_query_order_by_registered($query){
		if(!isset($_REQUEST['orderby'])){
			if( empty($_REQUEST['order']) || !in_array($_REQUEST['order'],array('asc','desc')) ){
				$_REQUEST['order'] = 'desc';
			}
			$query->query_orderby = "ORDER BY user_registered ".$_REQUEST['order'];
		}
	}
}

// 后台可以根据显示的名字来搜索用户 
add_filter('user_search_columns','wpjam_user_search_columns',10,3);
function wpjam_user_search_columns($search_columns, $search, $query){
	return array( 'ID', 'user_login', 'user_email', 'user_url', 'user_nicename', 'display_name' );
}

//移除不必要的用户联系信息
add_filter('user_contactmethods', 'wpjam_remove_user_contactmethods', 10, 1 ); 
function wpjam_remove_user_contactmethods( $contactmethods ) {
	
	unset($contactmethods['aim']);
	unset($contactmethods['yim']);
	unset($contactmethods['jabber']);
	
	//也可以自己增加
	//$contactmethods['user_mobile'] = '手机号码';
	//$contactmethods['user_contact'] = '收货联系人';
	//$contactmethods['user_address'] = '收货地址';

	return $contactmethods;
}

add_action('show_user_profile','wpjam_edit_user_avatar_profile');
add_action('edit_user_profile','wpjam_edit_user_avatar_profile');
function wpjam_edit_user_avatar_profile($profileuser){

	echo '<h3>自定义头像</h3>';

	$user_avatar = get_user_meta($profileuser->ID,'avatar',true);

	$form_fields = array(
		'avatar'	=> array('title'=>'头像',	'type'=>'image',	'value'=>$user_avatar),
	);

	wpjam_form_fields($form_fields); 
}

add_action('personal_options_update','wpjam_edit_user_avatar_profile_update');
add_action('edit_user_profile_update','wpjam_edit_user_avatar_profile_update');
function wpjam_edit_user_avatar_profile_update($user_id){
	if(!empty($_POST['avatar'])){
		update_user_meta( $user_id, 'avatar', $_POST['avatar'] );
	}else{
		if(get_user_meta( $user_id, 'avatar', true )){
			delete_user_meta( $user_id, 'avatar' );
		}
	}
}

if(wpjam_basic_get_setting('strict_user')){   
	/* 在后台修改用户昵称的时候检查是否重复 */
	add_action('user_profile_update_errors', 'wpjam_user_profile_update_errors',10,3 );
	function wpjam_user_profile_update_errors($errors, $update, $user){
		if(!$user->ID) return;

		if($user->display_name && $check = wpjam_check_nickname($user->display_name,$user->ID)){
			if(is_wp_error($check)){
				$errors->add( 'display_name_'.$check->get_error_code, '<strong>错误</strong>：'.__('dispaly name').$check->get_error_message(), array( 'form-field' => 'display_name' ) );
			}
			return;
		}

		if($user->nickname && $check = wpjam_check_nickname($user->nickname,$user->ID)){
			if(is_wp_error($check)){
				$errors->add( 'nickname_'.$check->get_error_code, '<strong>错误</strong>：'.__('nickname').$check->get_error_message(), array( 'form-field' => 'nickname' ) );
			}
			return;
		}
	}
}