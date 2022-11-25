<?php

add_filter('wpjam_basic_sub_pages', 'wpjam_custom_admin_page');
function wpjam_custom_admin_page($wpjam_basic_sub_pages)
{
    $wpjam_basic_sub_pages['wpjam-custom'] = array(
        'menu_title' => '样式定制',
        'function' => 'option',
        'option_name' => 'wpjam-basic'
    );
    return $wpjam_basic_sub_pages;
}

if(wpjam_basic_get_setting('disable_auto_update')){  
	remove_action( 'admin_init', '_maybe_update_core' );
	remove_action( 'admin_init', '_maybe_update_plugins' );
	remove_action( 'admin_init', '_maybe_update_themes' );
}


add_action('admin_head', 'wpjam_admin_head');
function wpjam_admin_head() {

	remove_action( 'admin_bar_menu', 'wp_admin_bar_wp_menu', 10 );
	add_action( 'admin_bar_menu', 'wpjam_admin_bar_wp_menu', 10 );

	if(is_multisite() && !current_user_can('manage_site')){
		// Remove Screen Help Tabs
		$screen = get_current_screen();
		$screen->remove_help_tabs();

		remove_action( 'admin_bar_menu', 'wp_admin_bar_my_sites_menu', 20 );
		remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
		remove_action( 'admin_bar_menu', 'wp_admin_bar_new_content_menu', 70 );

		remove_action( 'admin_bar_menu', 'wp_admin_bar_my_account_item', 7 );
		add_action( 'admin_bar_menu', 'wpjam_admin_bar_my_account_item', 7 );
	}

	echo wpjam_basic_get_setting('admin_head');

	if(wpjam_basic_get_setting('favicon')){ 
		echo '<link rel="shortcut icon" href="'.wpjam_basic_get_setting('favicon').'">';
	}
	/*if(wpjam_basic_get_setting('admin_logo')){
		$admin_logo_size = wpjam_basic_get_setting('admin_logo_size');
	?>
	<style type="text/css">
		#wpadminbar #wp-admin-bar-wp-logo > .ab-item{background:url(<?php echo wpjam_basic_get_setting('admin_logo');?>) no-repeat 6px 6px; width:<?php echo $admin_logo_size['width']+6;?>px; height:<?php echo $admin_logo_size['height']+6;?>px; }
		#wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon { display:none; }
	</style>
	<?php
	}*/
}

function wpjam_admin_bar_wp_menu($wp_admin_bar){
	if(wpjam_basic_get_setting('admin_logo')){
		$title 	= '<img src="'.wpjam_basic_get_setting('admin_logo').'" style="height:20px; padding:6px 0">';
	}else{
		$title	= '<span class="ab-icon"></span>';
	}
	$wp_admin_bar->add_menu( array(
		'id'    => 'wp-logo',
		'title' => $title,
		'href'  => self_admin_url(),
		'meta'  => array(
			'title' => __('About'),
		),
	) );
}

function wpjam_admin_bar_my_account_item($wp_admin_bar){
	$user_id      = get_current_user_id();
	$current_user = wp_get_current_user();
	$profile_url  = get_edit_profile_url( $user_id );

	if ( ! $user_id )
		return;

	$howdy  = sprintf( __('Howdy, %1$s'), $current_user->display_name );

	$wp_admin_bar->add_menu( array(
		'id'        => 'my-account',
		'parent'    => 'top-secondary',
		'title'     => $howdy ,
		'href'      => $profile_url,
		'meta'      => array(
			'title'     => __('My Account'),
		),
	) );

	$wp_admin_bar->remove_node( 'user-info' );
}

add_action('admin_menu', 'wpjam_remove_menu');
function wpjam_remove_menu(){
	if(is_multisite()){
		//remove_menu_page('tools.php');
		// remove_menu_page('users.php');
		// remove_menu_page('profile.php');
		// remove_menu_page('themes.php');
		// remove_menu_page('plugins.php');
		remove_submenu_page('index.php','my-sites.php');
		remove_submenu_page('tools.php','ms-delete-site.php');
	}
}

// 修改 WordPress Admin text
add_filter('admin_footer_text', 'wpjam_modify_admin_footer_text');
function wpjam_modify_admin_footer_text ($text) {
	if(wpjam_basic_get_setting('admin_footer')){
		return wpjam_basic_get_setting('admin_footer');
	}
	return $text;
}

//去除后台首页面板的功能 
if(wpjam_basic_get_setting('remove_dashboard_widgets')){
	add_action('wp_dashboard_setup', 'wpjam_remove_dashboard_widgets',1);
	function wpjam_remove_dashboard_widgets(){
		global $wp_meta_boxes;
		unset($wp_meta_boxes['dashboard']);
	}
}

//给页面添加摘要
add_action( 'add_meta_boxes', 'wpjam_add_page_excerpt_meta_box', 10, 2 );
function wpjam_add_page_excerpt_meta_box($post_type, $post) {
	if($post_type == 'page'){
		add_meta_box( 'postexcerpt', __('Excerpt'), 'post_excerpt_meta_box', 'page', 'normal', 'core' );
	}
}

// 屏蔽后台功能提示
// if(wpjam_basic_get_setting('disable_update')){
// 	add_filter ('pre_site_transient_update_core', '__return_null');

// 	remove_action ('load-update-core.php', 'wp_update_plugins');
// 	add_filter ('pre_site_transient_update_plugins', '__return_null');

// 	remove_action ('load-update-core.php', 'wp_update_themes');
// 	add_filter ('pre_site_transient_update_themes', '__return_null');
// }

// 移除 Google Fonts
// if(wpjam_basic_get_setting('disable_google_fonts')){
// 	//add_filter( 'gettext_with_context', 'wpjam_disable_google_fonts', 888, 4);
// 	function wpjam_disable_google_fonts($translations, $text, $context, $domain ) {
// 		$google_fonts_contexts = array('Open Sans font: on or off','Lato font: on or off','Source Sans Pro font: on or off','Bitter font: on or off');
// 		if( $text == 'on' && in_array($context, $google_fonts_contexts ) ){
// 			$translations = 'off';
// 		}

// 		return $translations;
// 	}
// }

add_action('admin_init', 'wpjam_show_id_init',99);
function wpjam_show_id_init(){

	// 在后台页面列表显示使用的页面模板
	add_filter('page_row_actions', 'wpjam_post_row_actionss_show_page_template', 10, 2);
	function wpjam_post_row_actionss_show_page_template($actions, $post){
		if($page_template = get_page_template_slug()) $actions['page_template'] = '<span style="color:green;" title="页面模板">'.$page_template.'</span>';
		return $actions;
	}

	// 显示 Post ID
	add_filter('post_row_actions', 'wpjam_post_row_actionss_show_post_id', 10, 2);
	add_filter('page_row_actions', 'wpjam_post_row_actionss_show_post_id', 10, 2);
	add_filter('media_row_actions', 'wpjam_post_row_actionss_show_post_id', 10, 2);
	function wpjam_post_row_actionss_show_post_id($actions, $post){
		$actions['post_id'] = 'ID: '.$post->ID;
		return $actions;
	}

	// 显示 标签，分类，tax ID
	$custom_taxonomies = get_taxonomies(array( 'public' => true )); 
	if($custom_taxonomies){
		foreach ($custom_taxonomies as $taxonomy) {
			add_filter($taxonomy.'_row_actions','wpjam_taxonomy_row_actions_show_term_id',10,2);
		}
	}
	function wpjam_taxonomy_row_actions_show_term_id($actions, $tag){
		$actions['term_id'] = 'ID：'.$tag->term_id;
		return $actions;

	}

	// 显示用户 ID
	add_filter('ms_user_row_actions','wpjam_user_row_actions_show_user_id',10,2);
	add_filter('user_row_actions', 'wpjam_user_row_actions_show_user_id', 10, 2);
	function wpjam_user_row_actions_show_user_id($actions, $user_object){
		$actions['user_id'] = 'ID: '.$user_object->ID;
		return $actions;
	}

	// 显示留言 ID
	add_filter('comment_row_actions','wpjam_comment_row_actions_show_comment_id',10,2);
	function wpjam_comment_row_actions_show_comment_id($actions, $comment){
		$actions['comment_id'] = 'ID：'.$comment->comment_ID;
		return $actions;
	}

	// remove_action( 'admin_notices', 'maintenance_nag' );
	// remove_action( 'network_admin_notices', 'maintenance_nag' );
}

add_filter('wpjam_post_options','wpjam_custom_post_options');
function wpjam_custom_post_options($wpjam_options){
	
	if(wpjam_basic_get_setting('custom_footer') ){
		$wpjam_options['wpjam-custom-footer'] = array(
			'title'			=> '日志底部代码',
			'fields'		=> array(
				'custom_footer'	=> array('title'=>'', 'type'=>'textarea', 'description'=>'自定义日志 Footer 代码可以让你在当前日志插入独有的 JS，CSS，iFrame 等类型的代码，让你可以对具体一篇日志设置不同样式和功能，展示不同的内容。'	)
			)
		);
	}
	return $wpjam_options;
}

add_filter('manage_pages_columns', 'wpjam_manage_pages_columns_add_template');
function wpjam_manage_pages_columns_add_template($column_headers){
    wpjam_array_push($column_headers, array('template'=>'模板文件'), 'comments');
    unset($column_headers['author']);
    return $column_headers;
}

add_action('manage_pages_custom_column','wpjam_manage_pages_custom_column_show_template',10,2);
function wpjam_manage_pages_custom_column_show_template($column_name,$id){
    if ($column_name == 'template') {
        echo get_page_template_slug();
    }
}
