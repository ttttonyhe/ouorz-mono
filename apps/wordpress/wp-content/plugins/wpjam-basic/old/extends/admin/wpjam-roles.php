<?php
// 角色管理菜单
add_filter('wpjam_pages', 'wpjam_roles_admin_pages');
function wpjam_roles_admin_pages($wpjam_pages){
	$capability	= (is_multisite())?'manage_site':'manage_options';

	$wpjam_pages['users']['subs']['roles']	= array('menu_title'=>'角色管理', 'capability'=>$capability,'function'=>'wpjam_roles_page');
	return $wpjam_pages;
}

add_filter('roles_fields', 'wpjam_roles_fields');
function wpjam_roles_fields($fields){
	return array(
		'role'			=> array('title'=>'角色',	'type'=>'text',		'show_admin_column'=>true,),
		'name'			=> array('title'=>'名称',	'type'=>'text',		'show_admin_column'=>true,),
		'user_count'	=> array('title'=>'用户数',	'type'=>'view',		'show_admin_column'=>'only',),
		'capabilities'	=> array('title'=>'权限',	'type'=>'mu-text',	'show_admin_column'=>true,),
	);
}

add_filter('roles_page_load', 'wpjam_roles_page_load');
function wpjam_roles_page_load(){
	global $wpjam_list_table;

	$wpjam_list_table = wpjam_list_table( array(
		'plural'			=> 'roles',
		'singular' 			=> 'role',
		'actions_column'	=> 'role'
	) );
}

function wpjam_roles_page(){
	$action = isset($_GET['action'])?$_GET['action']:'';
	if($action == 'edit' || $action == 'add' ){
		wpjam_role_edit_page();
	}else{
		wpjam_role_list_page();
	}
}

function wpjam_role_list_page(){
	
	global $wp_roles, $current_admin_url, $wpjam_list_table;

	$action = $wpjam_list_table->current_action();

	if($action == 'delete'){
		if( !current_user_can( 'manage_options' )){
	    	ob_clean();
	        wp_die('无权限');
	    }

	    if(!empty($_GET['role'])){
			check_admin_referer('delete-'.$wpjam_list_table->get_singular().'-'.$_GET['role']);
			remove_role($_GET['role']);
			wpjam_admin_add_error('删除成功');
		}
	}

	?>
	<h2>角色管理 <a title="新增用户角色" class="thickbox add-new-h2" href="<?php echo $current_admin_url.'&action=add'.'&TB_iframe=true&width=780&height=360'; ?>">新增</a></h2>

	<?php

	$roles 		= $wp_roles->roles;

	$new_roles	= array();

	$user_counts	= count_users();
	$user_counts	= $user_counts['avail_roles'];

	foreach ($roles as $key => $role) {
		$role['role']			= $key;
		$role['name']			= translate_user_role($role['name']);
		$role['user_count']		= isset($user_counts[$key])?('<a href="'.admin_url('users.php?role='.$key).'">'.$user_counts[$key].'</a>'):0;
		$role['capabilities']	= count($role['capabilities']);

		$role['row_actions'] 	= array(
			'edit'		=> '<a href="'.$current_admin_url.'&action=edit&role='.$key.'&TB_iframe=true&width=780&height=500'.'" title="编辑用户角色" class="thickbox" >编辑</a>',
			'duplicate'	=> '<a href="'.$current_admin_url.'&action=add&duplicate_role='.$key.'&TB_iframe=true&width=780&height=500'.'" title="新增用户角色" class="thickbox" >复制</a>'
		);
		if($key != 'administrator'){
			$role['row_actions']['delete']	= '<a href="'.esc_url(wp_nonce_url($current_admin_url.'&action=delete&role='.$key, 'delete-'.$wpjam_list_table->get_singular().'-'.$key)).'">删除</a>';
		}

		$new_roles[]			= $role;
	}

	$wpjam_list_table->prepare_items($new_roles, count($new_roles));
	$wpjam_list_table->display(array('search'=>false));
}

function wpjam_role_edit_page(){
	global $wp_roles, $plugin_page, $current_admin_url;
	$roles 			= $wp_roles->roles;

	$role			= isset($_GET['role'])?$_GET['role']:'';
	$duplicate_role	= isset($_GET['duplicate_role'])?$_GET['duplicate_role']:'';
	$action			= isset($_GET['action'])?$_GET['action']:'';

	$nonce_action	= $role ? 'edit-role-'.$role : 'add-role';

	$form_fields = wpjam_get_form_fields();

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){

		$data	= wpjam_get_form_post($form_fields, $nonce_action);
		$name	= $data['name'];

		$capabilities	= array();

		if($data['capabilities']){
			foreach ($data['capabilities'] as $capability) {
				if($capability){
					$capabilities[$capability]	= 1;
				}	
			}
		}

		if($role){ // 修改就是先移除，然后添加
			remove_role( $role );
			add_role( $role, $name, $capabilities );
			wpjam_admin_add_error('修改成功');
		}else{
			$role = $data['role'];
			add_role( $role, $name, $capabilities );
			wpjam_admin_add_error('添加成功');
		}	
		
		$roles 			= $wp_roles->roles;
	}


	
	
	if(($role && ($wp_role = $roles[$role]))|| ($duplicate_role && ($wp_role = $roles[$duplicate_role]))){

		$capabilities	= array();

		foreach ($wp_role['capabilities'] as $capability => $value) {
			if($value){
				$capabilities[]	= $capability;
			}
		}

		if($role){	// 编辑而不是复制
			$form_fields['role']['type']		= 'view';
			$form_fields['role']['value']		= $role;	
			$form_fields['name']['value']		= translate_user_role($wp_role['name']);
		}

		// $capabilities	= implode("\n", $capabilities);		
		$form_fields['capabilities']['value']	= $capabilities;

	}else{
		$form_fields['capabilities']['value']	= 'read'; // 默认添加的用户角色至少有 read 权限
	}

	$form_url		= ($action == 'add')?$current_admin_url.'&action=add':$current_admin_url.'&action=edit&role='.$role;
	$action_text	= $role?'修改':'新增';
	?>

	<h2><?php echo $action_text;?>用户角色</h2>

	<?php wpjam_form($form_fields, $form_url, $nonce_action, $action_text); ?>
<?php
}

add_filter('additional_capabilities_display', '__return_false' );

add_action('show_user_profile','wpjam_edit_user_capabilities_profile');
add_action('edit_user_profile','wpjam_edit_user_capabilities_profile');
function wpjam_edit_user_capabilities_profile($profileuser){

	if(current_user_can('edit_users')){
		$capabilities	= wpjam_get_additional_capabilities($profileuser);
		$capabilities	= implode("\n", $capabilities);

		echo '<h3>额外权限</h3>';

		$form_fields = array(
			'capabilities'	=> array('title'=>'权限',	'type'=>'textarea',	'value'=>$capabilities),
		);

		wpjam_form_fields($form_fields); 
	}
}

add_action('personal_options_update','wpjam_edit_user_capabilities_profile_update');
add_action('edit_user_profile_update','wpjam_edit_user_capabilities_profile_update');
function wpjam_edit_user_capabilities_profile_update($user_id){

	if(current_user_can('edit_users')){

		$user = get_userdata( $user_id );

		$old_capabilities 	= wpjam_get_additional_capabilities($user);

		$capabilities		= str_replace("\r\n", "\n",stripslashes(trim($_POST['capabilities'])));
		$capabilities 		= explode("\n", $capabilities);

		$remove_capabilities	= array_diff($old_capabilities, $capabilities);
		$add_capabilities		= array_diff($capabilities, $old_capabilities);

		if($remove_capabilities){
			foreach ($remove_capabilities as $cap) {
				$user->remove_cap($cap);
			}
		}

		if($add_capabilities){
			foreach ($add_capabilities as $cap) {
				$user->add_cap($cap);
			}
		}
	}
}

function wpjam_get_additional_capabilities($user){
	global $wp_roles;

	$capabilities	= array();

	foreach ( $user->caps as $cap => $value ) {
		if ( ! $wp_roles->is_role( $cap ) && $value ) {
			$capabilities[] = $cap;
		}
	}

	return $capabilities;
}
