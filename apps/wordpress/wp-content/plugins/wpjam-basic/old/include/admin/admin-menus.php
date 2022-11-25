<?php

// 设置菜单
add_action('network_admin_menu', 'wpjam_admin_menu');
add_action('admin_menu', 'wpjam_admin_menu');
function wpjam_admin_menu() {
	global $plugin_page;

	$wpjam_pages = wpjam_get_admin_pages();
	if(!$wpjam_pages) return;

	$builtin_parent_pages = wpjam_get_builtin_parent_pages();

	foreach ($wpjam_pages as $menu_slug=>$wpjam_page) {
		if(isset($builtin_parent_pages[$menu_slug])){
			$parent_slug = $builtin_parent_pages[$menu_slug];
		}else{
			extract( wpjam_parse_admin_page($wpjam_page) );
			
			$function	= ($function !== '')?'wpjam_admin_page':'';
			$page_hook	= add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon, $position);

			if($plugin_page == $menu_slug){
				add_action('load-'.$page_hook, 'wpjam_admin_page_load');
			}

			$parent_slug	= $menu_slug;
		}
		if(!empty($wpjam_page['subs'])){
			foreach ($wpjam_page['subs'] as $menu_slug => $wpjam_page) {
				extract( wpjam_parse_admin_page($wpjam_page));

				$function	= ($function !== '')?'wpjam_admin_page':'';
				$page_hook	= add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
				
				if($plugin_page == $menu_slug){
					add_action('load-'.$page_hook, 'wpjam_admin_page_load');
				}
			}
		}
	}
}

// 如果是通过 wpjam_pages filter 定义的后台菜单，就需要设置 $current_screen->id=$plugin_page
// 否则隐藏列功能就会出问题。	
add_action('current_screen', 'wpjam_current_screen');
function wpjam_current_screen($current_screen){
	global $current_screen, $plugin_page;
	if($plugin_page && wpjam_get_admin_page($plugin_page)){
		$current_screen->id = $current_screen->base = $plugin_page;
	}
}

// 加载页面之前执行的函数
function wpjam_admin_page_load(){
	global $plugin_page, $current_tab;

	if($tabs = wpjam_get_admin_page_tabs()){
		$tab_keys		= array_keys($tabs);
		$current_tab	= isset($_GET['tab'])?$_GET['tab']:$tab_keys[0];
	}

	$action	= isset($_GET['action'])?$_GET['action']:'';
	if(in_array($action, array('add','edit','set','bulk-edit'))) return;

	do_action($plugin_page.'_page_load');
}

// 获取后台菜单
function wpjam_get_admin_pages(){
	if(is_multisite() && is_network_admin()){
		return apply_filters('wpjam_network_pages', array());
	}else{
		return apply_filters('wpjam_pages', array());
	}
}

// 获取指定 menu_slug 的后台菜单
function wpjam_get_admin_page($menu_slug){
	$wpjam_pages = wpjam_get_admin_pages();
	if(isset($wpjam_pages[$menu_slug])){
		return wpjam_parse_admin_page($wpjam_pages[$menu_slug]);
	}

	foreach ($wpjam_pages as $parent_slug => $wpjam_page){
		if(isset($wpjam_page['subs'][$menu_slug])){
			$wpjam_page['subs'][$menu_slug]['parent_slug'] = $parent_slug;
			return wpjam_parse_admin_page($wpjam_page['subs'][$menu_slug]);
		}
	}

	return false;
}

// 获取内置的后台一级菜单
function wpjam_get_builtin_parent_pages(){
	if(is_multisite() && is_network_admin()){
		return array(
			'settings'	=> 'settings.php',
			'theme'		=> 'themes.php',
			'themes'	=> 'themes.php',
			'plugins'	=> 'plugins.php',
			'users'		=> 'users.php',
			'sites'		=> 'sites.php',
		);
	}else{
		$parent_pages = array(
			'management'=> 'tools.php',
			'options'	=> 'options-general.php',
			'theme'		=> 'themes.php',
			'themes'	=> 'themes.php',
			'plugins'	=> 'plugins.php',
			'posts'		=> 'edit.php',
			'media'		=> 'upload.php',
			'links'		=> 'link-manager.php',
			'pages'		=> 'edit.php?post_type=page',
			'comments'	=> 'edit-comments.php',
			'users'		=> current_user_can('edit_users')?'users.php':'profile.php',
		);

		if($custom_post_types = get_post_types( array( '_builtin' => false, 'show_ui' => true ))){
			foreach ($custom_post_types as $custom_post_type) {
				$parent_pages[$custom_post_type.'s'] = 'edit.php?post_type='.$custom_post_type;
			}
		}

		return $parent_pages;
	}
}

// 菜单处理函数
function wpjam_parse_admin_page($wpjam_page){
	$wpjam_page = wp_parse_args( $wpjam_page, array(
		'menu_title'	=> '',
		'page_title'	=> '',
		'function'		=> null,
		'capability'	=> 'manage_options',
		'icon'			=> '',
		'position'		=> null,
		'load'			=> '',
		'fields'		=> ''
	) );

	if(!$wpjam_page['page_title']){
		$wpjam_page['page_title'] = $wpjam_page['menu_title'];
	}

	return $wpjam_page;
}

// 后台页面处理函数
function wpjam_admin_page(){
	global $plugin_page, $current_admin_url;
	?>
	<div class="wrap">
	<?php

	if($wpjam_page 	= wpjam_get_admin_page($plugin_page)){

		$builtin_parent_pages	= wpjam_get_builtin_parent_pages();
		$parent_slug 			= isset($wpjam_page['parent_slug'])?$wpjam_page['parent_slug']:'';

		if($parent_slug && isset($builtin_parent_pages[$parent_slug])){
			$current_admin_url	= $builtin_parent_pages[$parent_slug];
			$current_admin_url 	.= (strpos($current_admin_url, '?'))?'&page='.$plugin_page:'?page='.$plugin_page;
		}else{
			$current_admin_url	= 'admin.php?page='.$plugin_page;
		}

		$current_admin_url	= (is_network_admin())?network_admin_url($current_admin_url):admin_url($current_admin_url);
		$function			= $wpjam_page['function'];

		if($function == 'option'){
			$option_name 	= isset($wpjam_page['option_name'])?$wpjam_page['option_name']:$plugin_page;
			$page_type		= isset($wpjam_page['page_type'])?$wpjam_page['page_type']:'tab';
			call_user_func('wpjam_option_page', $option_name, array('page_type'=>$page_type,'page_title'=>$wpjam_page['page_title']));
		}elseif($function == 'tab'){
			call_user_func('wpjam_admin_tab_page');
		}elseif($function == 'dashboard'){
			$widgets_callback	= isset($wpjam_page['widgets_callback'])?$wpjam_page['widgets_callback']:str_replace('-', '_', $plugin_page).'_dashboard_widgets';

			$widgets_callback	= apply_filters('wpjam_widgets_callback', $widgets_callback, $plugin_page);

			add_filter('wpjam_dashboard_widgets', $widgets_callback);
			call_user_func('wpjam_admin_dashboard_page', $wpjam_page['page_title']);
		}else{
			$function	= ($function)?$function:str_replace('-','_',$plugin_page).'_page';
			call_user_func($function);
		}
	}
	?>
	</div>
	<?php
}

// Tab 后台页面
function wpjam_admin_tab_page($args=array()){
	global $plugin_page, $current_tab, $current_admin_url;

	$tabs = wpjam_get_admin_page_tabs();

	if(!$tabs) return;
	
	// $tab_keys		= array_keys($tabs);
	// $current_tab	= isset($_GET['tab'])?$_GET['tab']:$tab_keys[0];

	if(empty($tabs[$current_tab])){
		wp_die('无此Tab');
	}

	$current_admin_url = $current_admin_url.'&tab='.$current_tab;

	if($args) $current_admin_url = add_query_arg($args, $current_admin_url);

	if(count($tabs) == 1){ ?>
	<?php call_user_func($tabs[$current_tab]['function']); ?>
	<?php }else{ ?>
	<h1 class="nav-tab-wrapper">
	<?php foreach ($tabs as $tab_key => $tab) { ?>
		<?php 
		$tab_url = admin_url('admin.php?page='.$plugin_page.'&tab='.$tab_key);
		if($args) $tab_url = add_query_arg($args, $tab_url);	// 支持全局的参数
		if(isset($tab['args'])) $tab_url = add_query_arg($tab['args'], $tab_url);	// 支持单个参数
		?>
		<a class="nav-tab <?php if($current_tab == $tab_key){ echo 'nav-tab-active'; } ?>" href="<?php echo $tab_url;?>"><?php echo $tab['title']; ?></a>
	<?php }?>
	</h1>
	<?php call_user_func($tabs[$current_tab]['function']); ?>
	<?php }
}

// 获取当前页面的 Tabs
function wpjam_get_admin_page_tabs(){
	global $plugin_page;

	if($tabs =  apply_filters($plugin_page.'_tabs', array())){
		foreach ($tabs as $key => $tab) { 
			if(is_string($tab)){
				$function	= str_replace('-', '_', $plugin_page).'_'.$key.'_page';
				$tabs[$key]	= array('title'=>$tab, 'function'=>$function);
			}
		}
	}

	return $tabs;
}

add_action('admin_init', 'wpjam_admin_init',1);
function wpjam_admin_init(){
	global $plugin_page, $current_tab;

	if(!isset($current_tab)){
		$current_tab = isset($_GET['tab'])?$_GET['tab']:'';
	}

	if($plugin_page && wpjam_get_admin_page($plugin_page)){
		$function_prefix = str_replace('-', '_', $plugin_page);
	
		if(function_exists($function_prefix.'_tabs')){
			add_filter($plugin_page.'_tabs', $function_prefix.'_tabs',1);
		}

		if(function_exists($function_prefix.'_fields')){
			add_filter($plugin_page.'_fields', $function_prefix.'_fields',1);
		}

		if(function_exists($function_prefix.'_page_load')){
			add_action($plugin_page.'_page_load', $function_prefix.'_page_load',1);
		}
	}
}