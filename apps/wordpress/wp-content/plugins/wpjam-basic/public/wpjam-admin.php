<?php
class WPJAM_Admin{
	public static function get_screen_id(){
		static $screen_id;

		if(!isset($screen_id)){
			if(isset($_POST['screen_id'])){
				$screen_id	= $_POST['screen_id'];
			}elseif(isset($_POST['screen'])){
				$screen_id	= $_POST['screen'];	
			}else{
				$ajax_action	= $_REQUEST['action'] ?? '';

				if($ajax_action == 'fetch-list'){
					$screen_id	= $_GET['list_args']['screen']['id'];
				}elseif($ajax_action == 'inline-save-tax'){
					$screen_id	= 'edit-'.sanitize_key($_POST['taxonomy']);
				}elseif(in_array($ajax_action, ['get-comments', 'replyto-comment'])){
					$screen_id	= 'edit-comments';
				}else{
					$screen_id	= false;
				}
			}

			if($screen_id){
				if('-network' === substr($screen_id, -8)){
					if(!defined('WP_NETWORK_ADMIN')){
						define('WP_NETWORK_ADMIN', true);
					}
				}elseif('-user' === substr($screen_id, -5)){
					if(!defined('WP_USER_ADMIN')){
						define('WP_USER_ADMIN', true);
					}
				}
			}
		}

		return $screen_id;	
	}

	public static function init($plugin_page){
		$GLOBALS['plugin_page']	= $plugin_page;

		do_action('wpjam_admin_init');

		if($plugin_page){
			WPJAM_Menu_Page::render(false);
		}

		$screen_id	= self::get_screen_id();
			
		if($screen_id == 'upload'){
			$GLOBALS['hook_suffix']	= $screen_id;

			set_current_screen();
		}else{
			set_current_screen($screen_id);
		}
	}

	public static function on_admin_menu(){
		do_action('wpjam_admin_init');

		WPJAM_Menu_Page::render();
	}

	public static function on_admin_notices(){
		$object	= WPJAM_Screen_Option::get_instance('admin_errors');

		foreach($object->get_option([]) as $error){
			echo '<div class="notice notice-'.$error['type'].' is-dismissible"><p>'.$error['message'].'</p></div>';
		}
	}

	public static function on_admin_init(){
		$plugin_page	= $_POST['plugin_page'] ?? null;

		self::init($plugin_page);	
	}

	public static function on_current_screen($screen=null){
		$object = wpjam_get_plugin_page_object();

		if(!$object && $screen){
			$object	= new WPJAM_Builtin_Page($screen);
		}

		if($object){
			$object->load();
		}
	}

	public static function on_admin_enqueue_scripts(){
		$screen	= get_current_screen();
		
		if($screen->base == 'customize'){
			return;
		}elseif($screen->base == 'post'){
			wp_enqueue_media(['post'=>wpjam_get_admin_post_id()]);
		}else{
			wp_enqueue_media();
		}

		$ver	= get_plugin_data(WPJAM_BASIC_PLUGIN_FILE)['Version'];
		$static	= WPJAM_BASIC_PLUGIN_URL.'static';

		wp_enqueue_script('thickbox');
		wp_enqueue_style('thickbox');

		wp_enqueue_style('wpjam-style',		$static.'/style.css',	['wp-color-picker', 'editor-buttons'], $ver);
		wp_enqueue_script('wpjam-script',	$static.'/script.js',	['jquery', 'thickbox', 'wp-backbone', 'jquery-ui-sortable', 'jquery-ui-tooltip', 'jquery-ui-tabs', 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-ui-autocomplete', 'wp-color-picker'], $ver);
		wp_enqueue_script('wpjam-form',		$static.'/form.js',		['wpjam-script', 'mce-view'], $ver);

		$setting	= [
			'screen_base'	=> $screen->base,
			'screen_id'		=> $screen->id,
			'post_type'		=> $screen->post_type,
			'taxonomy'		=> $screen->taxonomy,
		];

		$params		= wpjam_array_except($_REQUEST, array_merge(wp_removable_query_args(),['page', 'tab', 'post_type', 'taxonomy', '_wp_http_referer', '_wpnonce']));
		$params		= array_filter($params, 'is_populated');

		if($GLOBALS['plugin_page']){
			$setting['plugin_page']	= $GLOBALS['plugin_page'];
			$setting['current_tab']	= $GLOBALS['current_tab'] ?? null;
			$setting['admin_url']	= $GLOBALS['current_admin_url'] ?? '';

			$query_data	= wpjam_get_plugin_page_query_data();

			if($query_data){
				$params	= wpjam_array_except($params, array_keys($query_data));

				$setting['query_data']	= array_map('sanitize_textarea_field', $query_data);
			}
		}

		$setting['params']	= $params ? array_map('sanitize_textarea_field', $params) : new stdClass();

		if(!empty($GLOBALS['wpjam_list_table'])){
			$setting['list_table']	= $screen->get_option('wpjam_list_table');
		}

		wp_localize_script('wpjam-script', 'wpjam_page_setting', $setting);
	}

	public static function on_admin_action_update(){
		// 为了实现多个页面使用通过 option 存储。这个可以放弃了，使用 AJAX + Redirect
		// 注册设置选项，选用的是：'admin_action_' . $_REQUEST['action'] hook，
		// 因为在这之前的 admin_init 检测 $plugin_page 的合法性

		$referer_origin	= parse_url(wpjam_get_referer());

		if(!empty($referer_origin['query'])){
			$referer_args	= wp_parse_args($referer_origin['query']);

			if(!empty($referer_args['page'])){
				self::init($referer_args['page']);	// 实现多个页面使用同个 option 存储。
			}
		}
	}
}

class WPJAM_Screen_Option extends WPJAM_Register{
	public function get_option($default=''){
		if(did_action('current_screen')){
			return get_current_screen()->get_option($this->name) ?: $default;
		}else{
			return $default;
		}
	}

	public function set_option($value){
		if(did_action('current_screen')){
			add_screen_option($this->name, $value);
		}
	}

	public function append($string){
		if(did_action('current_screen')){
			$value	= $this->get_option().$string;

			$this->set_option($value);
		}
	}

	public function add_item(...$args){
		if(did_action('current_screen')){
			$value	= $this->get_option([]);

			if(count($args) >= 2){
				$key	= $args[0];
				
				if(array_key_exists($key, $value)){
					return;
				}

				$value[$key]	= $args[1];
			}else{
				$value[]		= $args[0];
			}

			$this->set_option($value);
		}
	}

	public function set_item($key, $item){
		if(did_action('current_screen')){
			$value			= $this->get_option([]);
			$value[$key]	= $item;

			$this->set_option($value);
		}
	}

	public function replace_item($key, $item){
		if(did_action('current_screen')){
			$value	= $this->get_option([]);

			if(array_key_exists($key, $value)){
				$value[$key]	= $item;

				$this->set_option($value);
			}
		}
	}

	public function delete_item($key){
		if(did_action('current_screen')){
			$value	= $this->get_option([]);

			if(array_key_exists($key, $value)){
				unset($value[$key]);

				$this->set_option($value);
			}
		}
	}

	public static function get_instance($name, $args=[]){
		$object	= self::get($name);

		if(!$object){
			$object	= self::register($name, $args);
		}

		return $object;
	}
}

function wpjam_register_builtin_page_load($name, $args){
	return WPJAM_Builtin_Page_Load::pre_register($name, $args);
}

function wpjam_register_plugin_page_load($name, $args){
	return WPJAM_Plugin_Page_Load::pre_register($name, $args);
}

function wpjam_generate_query_data($query_args){
	$query_data	= [];

	foreach($query_args as $query_arg){
		$query_data[$query_arg]	= wpjam_get_data_parameter($query_arg);
	}

	return $query_data;
}

function wpjam_admin_add_error($message='', $type='success'){
	$object	= WPJAM_Screen_Option::get_instance('admin_errors');

	if($message){
		if(is_wp_error($message)){
			$message	= $message->get_error_message();
			$type		= 'error';
		}

		if($type){
			$object->add_item(['message'=>$message, 'type'=>$type]);
		}
	}
}

function wpjam_get_page_summary($type='page'){
	$object	= WPJAM_Screen_Option::get_instance($type.'_summary');

	return $object->get_option();
}

function wpjam_set_page_summary($summary, $type='page', $append=true){
	$object	= WPJAM_Screen_Option::get_instance($type.'_summary');

	if($append){
		$object->append($summary);
	}else{
		$object->set_option($summary);
	}
}

function wpjam_set_plugin_page_summary($summary, $append=true){
	wpjam_set_page_summary($summary, 'page', $append);
}

function wpjam_set_builtin_page_summary($summary, $append=true){
	wpjam_set_page_summary($summary, 'page', $append);
}

function wpjam_get_plugin_page_object($plugin_page=''){
	if(!$plugin_page && !empty($GLOBALS['plugin_page'])){
		$plugin_page	= $GLOBALS['plugin_page'];
	}

	return $plugin_page ? WPJAM_Plugin_Page::get($plugin_page) : null;
}

function wpjam_get_plugin_page_setting($key='', $using_tab=false){
	$object = wpjam_get_plugin_page_object();

	if(!$object){
		return null;
	}

	$is_tab	= $object->function == 'tab';

	if(str_ends_with($key, '_name')){
		$using_tab	= $is_tab;
		$default	= $GLOBALS['plugin_page'];
	}else{
		$using_tab	= $using_tab ? $is_tab : false;
		$default	= null;
	}

	if($using_tab){
		if($tab_object = $object->current_tab){
			return $key ? ($tab_object->$key ?: $default) : $tab_object->to_array();
		}
	}else{
		return $key ? ($object->$key ?: $default) : $object->to_array();
	}
}

function wpjam_get_plugin_page_type(){
	return wpjam_get_plugin_page_setting('function');
}

function wpjam_get_current_tab_setting($key=''){
	return wpjam_get_plugin_page_setting($key, true);
}

function wpjam_get_plugin_page_query_data(){
	$value	= wpjam_get_plugin_page_setting('query_data') ?: [];
		
	if($query_data = wpjam_get_current_tab_setting('query_data', true)){
		$value	= array_merge($value, $query_data);
	}

	return $value;
}

function wpjam_admin_page(){
	echo '<div class="wrap">';

	$object	= wpjam_get_plugin_page_object();

	$object->title();

	if($object->function == 'tab'){
		$object->tab_page();
	}else{
		$object->page();
	}

	echo '</div>';
}

function wpjam_admin_tooltip($text, $tooltip){
	return '<div class="wpjam-tooltip">'.$text.'<div class="wpjam-tooltip-text">'.wpautop($tooltip).'</div></div>';
}

function wpjam_get_referer(){
	$referer	= wp_get_original_referer();
	$referer	= $referer ?: wp_get_referer();

	$removable_query_args	= array_merge(wp_removable_query_args(), ['_wp_http_referer', 'action', 'action2', '_wpnonce']);

	return remove_query_arg($removable_query_args, $referer);	
}

function wpjam_register_page_action($name, $args){
	return WPJAM_Page_Action::register($name, $args);
}

function wpjam_unregister_page_action($name){
	WPJAM_Page_Action::unregister($name);
}

function wpjam_get_nonce_action($key){
	$prefix	= $GLOBALS['plugin_page'] ?? $GLOBALS['current_screen']->id;

	return $prefix.'-'.$key;
}

function wpjam_get_page_button($name, $args=[]){
	$instance	= WPJAM_Page_Action::get($name);

	return $instance ? $instance->get_button($args) : '';
}

function wpjam_register_list_table($name, $args=[]){
	return WPJAM_List_Table_Setting::register($name, $args);
}

function wpjam_register_list_table_action($name, $args){
	return WPJAM_List_Table_Action::register($name, $args);
}

function wpjam_unregister_list_table_action($name){
	WPJAM_List_Table_Action::unregister($name);
}

function wpjam_register_list_table_column($name, $field){
	return WPJAM_List_Table_Column::pre_register($name, $field);
}

function wpjam_unregister_list_table_column($name, $field=[]){
	WPJAM_List_Table_Column::unregister_pre($name, $field);
}

function wpjam_register_plugin_page($name, $args){
	return WPJAM_Plugin_Page::register($name, $args);
}

function wpjam_register_plugin_page_tab($name, $args){
	return WPJAM_Plugin_Page::register(sanitize_key($name).'__tab', $args);
}

function wpjam_get_list_table_filter_link($filters, $title, $class=''){
	return $GLOBALS['wpjam_list_table']->get_filter_link($filters, $title, $class);
}

function wpjam_get_list_table_row_action($action, $args=[]){
	return $GLOBALS['wpjam_list_table']->get_row_action($action, $args);
}

function wpjam_render_list_table_column_items($id, $items, $args=[]){
	return $GLOBALS['wpjam_list_table']->render_column_items($id, $items, $args);
}

function wpjam_call_list_table_model_method($method, ...$args){
	return $GLOBALS['wpjam_list_table']->call_model_method($method, ...$args);
}

function wpjam_register_dashboard($name, $args){
	return WPJAM_Dashboard_Setting::register($name, $args);
}

function wpjam_unregister_dashboard($name){
	WPJAM_Dashboard_Setting::unregister($name);
}

function wpjam_register_dashboard_widget($name, $args){
	return wpjam_register('dashboard_widget', $name, $args);
}

function wpjam_unregister_dashboard_widget($name){
	wpjam_unregister('dashboard_widget', $name);
}

function wpjam_get_admin_post_id(){
	if(isset($_GET['post'])){
		return (int)$_GET['post'];
	}elseif(isset($_POST['post_ID'])){
		return (int)$_POST['post_ID'];
	}else{
		return 0;
	}
}

function wpjam_line_chart($counts_array, $labels, $args=[], $type = 'Line'){
	WPJAM_Chart::line($counts_array, $labels, $args, $type);
}

function wpjam_bar_chart($counts_array, $labels, $args=[]){
	wpjam_line_chart($counts_array, $labels, $args, 'Bar');
}

function wpjam_donut_chart($counts, $args=[]){
	WPJAM_Chart::donut($counts, $args);
}

function wpjam_get_chart_parameter($key){
	return WPJAM_Chart::get_parameter($key);
}

function wpjam_get_current_screen_id(){
	if(did_action('current_screen')){
		return get_current_screen()->id;
	}elseif(wp_doing_ajax()){
		return WPJAM_Admin::get_screen_id();
	}
}

function wpjam_get_admin_menu_hook($type='action'){
	if(is_network_admin()){
		$prefix	= 'network_';
	}elseif(is_user_admin()){
		$prefix	= 'user_';
	}else{
		$prefix	= '';
	}

	if($type == 'action'){
		return $prefix.'admin_menu';
	}else{
		return 'wpjam_'.$prefix.'pages';
	}
}

add_action('plugins_loaded', function(){	// 内部的 hook 使用 优先级 9，因为内嵌的 hook 优先级要低
	wpjam_register_page_action('delete_notice', [
		'button_text'	=> '删除',
		'tag'			=> 'span',
		'class'			=> 'hidden delete-notice',
		'callback'		=> ['WPJAM_Notice', 'ajax_delete'],
		'direct'		=> true,
	]);

	if($GLOBALS['pagenow'] == 'options.php'){
		add_action('admin_action_update',	['WPJAM_Admin', 'on_admin_action_update'], 9);
	}elseif(wp_doing_ajax()){
		if(wpjam_get_current_screen_id()){
			add_action('admin_init',	['WPJAM_Admin', 'on_admin_init'], 9);

			add_action('wp_ajax_wpjam-page-action',	['WPJAM_Page_Action', 'ajax_response']);
			add_action('wp_ajax_wpjam-query', 		['WPJAM_Data_Type', 'ajax_query']);
		}
	}else{
		$menu_action	= wpjam_get_admin_menu_hook('action');

		add_action($menu_action,			['WPJAM_Admin', 'on_admin_menu'], 9);
		add_action('admin_notices',			['WPJAM_Admin', 'on_admin_notices']);
		add_action('admin_notices',			['WPJAM_Notice', 'on_admin_notices']);
		add_action('admin_enqueue_scripts', ['WPJAM_Admin', 'on_admin_enqueue_scripts'], 9);
		add_action('print_media_templates', ['WPJAM_Field',	'print_media_templates'], 9);

		add_filter('wpjam_html', 			['WPJAM_Menu_Page', 'filter_html']);

		add_filter('set-screen-option', function($status, $option, $value){
			trigger_error('filter::set-screen-option');
			return isset($_GET['page']) ? $value : $status;
		}, 9, 3);
	}

	add_action('current_screen',	['WPJAM_Admin', 'on_current_screen'], 9);
});


