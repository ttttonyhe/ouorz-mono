<?php

include(WPJAM_BASIC_PLUGIN_DIR.'admin/setting.php');		// 设置
include(WPJAM_BASIC_PLUGIN_DIR.'admin/function-list.php');	// 新增功能
include(WPJAM_BASIC_PLUGIN_DIR.'admin/lists.php');			// 内置列表
include(WPJAM_BASIC_PLUGIN_DIR.'admin/stats.php');			// 后台统计基础函数
include(WPJAM_BASIC_PLUGIN_DIR.'admin/users.php');			// 用户
include(WPJAM_BASIC_PLUGIN_DIR.'admin/thumbnail.php');		// 缩略图
include(WPJAM_BASIC_PLUGIN_DIR.'admin/server-status.php');	

wpjam_include_admin_extends();	// 加载扩展，获取扩展的后台设置文件

// 设置菜单
add_filter('wpjam_pages', 'wpjam_basic_admin_pages');
add_filter('wpjam_network_pages', 'wpjam_basic_admin_pages');
function wpjam_basic_admin_pages($wpjam_pages){
	$capability	= (is_multisite())?'manage_site':'manage_options';

	$subs = array();

    $subs['wpjam-basic']	= array('menu_title'=>'功能管理', 	'function'=>'option',	'page_type'=>'default', 'option_name'=>'wpjam-extends');
    $subs = apply_filters('wpjam_basic_sub_pages', $subs);

    if(is_multisite() && is_network_admin()){
    }else{
        if(wpjam_basic_get_setting('show_all_setting')){
            $wpjam_pages['options']['subs']['options.php']	= array('menu_title'=>'所有设置',		'function'=>'');
        }
    }

    $wpjam_pages['wpjam-basic']	= array(
		'menu_title'	=> 'WPJAM',
		'page_title'	=> '功能管理',
		'icon'			=> 'dashicons-performance',
		'function'		=> 'option',
		'subs'			=> $subs,
		'position'		=> '80.1',
        'option_name'=>'wpjam-extends'
	);

	return $wpjam_pages;
}

function wpjam_basic_check_domain(){
    $domain = parse_url(home_url(), PHP_URL_HOST);
    if(get_option('wpjam_net_domain_check_56') == md5($domain.'56')){
        return true;
    }

    $weixin_user = wpjam_topic_get_weixin_user();

    if($weixin_user && $weixin_user['subscribe']){
        return true;
    }

    return false;
}

function wpjam_basic_verify_page() {
    global $current_admin_url;
    $current_admin_url = admin_url('admin.php?page=wpjam-basic');
    wpjam_topic_setting_page('微信机器人','<p>请使用微信扫描下面的二维码，获取验证码之后提交即可验证通过！</p>');
}

register_activation_hook( WPJAM_BASIC_PLUGIN_FILE, 'wpjam_basic_activation' );
function wpjam_basic_activation(){
	global $wpdb;

	flush_rewrite_rules();
	// simple_term_meta_install();

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	if($wpdb->get_var("show tables like '{$wpdb->messages}'") != $wpdb->messages) {
		$sql = "
		CREATE TABLE IF NOT EXISTS `{$wpdb->messages}` (
		  `id` bigint(20) NOT NULL auto_increment,
		  `sender` bigint(20) NOT NULL,
		  `receiver` bigint(20) NOT NULL,
		  `content` longtext NOT NULL,
		  `status` int(1) NOT NULL,
		  `time` int(10) NOT NULL,
		  PRIMARY KEY  (`id`)
		) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		";
 
		dbDelta($sql);
	}
}

// 给测试版插件加上测试版标签
add_filter('all_plugins','wpjam_all_plugins');
function wpjam_all_plugins($all_plugins){
	foreach($all_plugins as $plugin_file => $plugin_data){
		if(strpos($plugin_file, 'test') !== false || strpos($plugin_file, 'beta') !== false){
			$all_plugins[$plugin_file]['Name'] = $plugin_data['Name'].'《测试版》';
		}
	}
	return $all_plugins;
}

// // 创建新博客的时候，顺手创建表
// add_action( 'wpmu_new_blog', 'wpjam_basic_new_blog', 10, 2 );
// function wpjam_basic_new_blog($blog_id, $user_id){
// 	switch_to_blog($blog_id);
// 	simple_term_meta_install();
// 	restore_current_blog();
// }

// // 删除博客的时候，将 termmeta 表删除了
// add_filter('wpmu_drop_tables', 'wpjam_basic_wpmu_drop_tables',10,2);
// function wpjam_basic_wpmu_drop_tables($tables, $blog_id){
// 	global $wpdb;
// 	$blog_prefix 	= $wpdb->get_blog_prefix( $blog_id );
// 	$tables[] = $blog_prefix.'termmeta';
// 	return $tables;
// }