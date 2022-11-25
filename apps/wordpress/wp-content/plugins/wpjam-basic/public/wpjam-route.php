<?php
class WPJAM_Route extends WPJAM_Register{
	public function callback($wp){
		$callback	= $this->callback;

		if($callback && is_callable($callback)){
			$action	= wpjam_get_current_action($wp);

			return call_user_func($callback, $action, $this->name);
		}
	}

	public static function create($name, $args){
		if(!is_array($args) || wp_is_numeric_array($args)){
			$args	= is_callable($args) ? ['callback'=>$args] : (array)$args;
		}

		return self::register($name, $args);
	}

	public static function autoload(){
		$GLOBALS['wp']->add_query_var('module');
		$GLOBALS['wp']->add_query_var('action');
		$GLOBALS['wp']->add_query_var('term_id');

		add_action('parse_request',	[self::class, 'on_parse_request']);

		add_filter('determine_current_user',	[self::class, 'filter_determine_current_user']);
		add_filter('wp_get_current_commenter',	[self::class, 'filter_current_commenter']);
		add_filter('pre_get_avatar_data',		[self::class, 'filter_pre_avatar_data'], 10, 2);

		self::create('json',	['WPJAM_JSON', 'redirect']);
		self::create('txt',		['WPJAM_Verify_TXT', 'redirect']);
	}

	public static function on_parse_request($wp){
		$wp->query_vars	= wpjam_parse_query_vars($wp->query_vars);

		$module	= wpjam_get_current_module($wp);

		if($module){
			$object	= self::get($module);

			if($object){
				$object->callback($wp);
			}

			remove_action('template_redirect',	'redirect_canonical');

			add_filter('template_include',	[self::class, 'filter_template_include']);
		}
	}

	public static function filter_template_include($template){
		$module	= get_query_var('module');
		$action	= get_query_var('action');

		$file	= $action ? $action.'.php' : 'index.php';
		$file	= STYLESHEETPATH.'/template/'.$module.'/'.$file;
		$file	= apply_filters('wpjam_template', $file, $module, $action);

		return is_file($file) ? $file : $template;
	}

	public static function filter_determine_current_user($user_id){
		if(empty($user_id)){
			$wpjam_user	= wpjam_get_current_user();

			if($wpjam_user && !empty($wpjam_user['user_id'])){
				return $wpjam_user['user_id'];
			}
		}

		return $user_id;
	}

	public static function filter_current_commenter($commenter){
		if(empty($commenter['comment_author_email'])){
			$wpjam_user	= wpjam_get_current_user();

			if($wpjam_user && !empty($wpjam_user['user_email'])){
				$commenter['comment_author_email']	= $wpjam_user['user_email'];
				$commenter['comment_author']		= $wpjam_user['nickname'];
			}
		}

		return $commenter;
	}

	public static function filter_pre_avatar_data($args, $id_or_email){
		$user_id 	= 0;
		$avatarurl	= '';
		$email		= '';

		if(is_object($id_or_email) && isset($id_or_email->comment_ID)){
			$id_or_email	= get_comment($id_or_email);
		}

		if(is_numeric($id_or_email)){
			$user_id	= $id_or_email;
		}elseif(is_string($id_or_email)){
			$email		= $id_or_email;
		}elseif($id_or_email instanceof WP_User){
			$user_id	= $id_or_email->ID;
		}elseif($id_or_email instanceof WP_Post){
			$user_id	= $id_or_email->post_author;
		}elseif($id_or_email instanceof WP_Comment){
			$user_id	= $id_or_email->user_id;
			$email		= $id_or_email->comment_author_email;
			$avatarurl	= get_comment_meta($id_or_email->comment_ID, 'avatarurl', true);
		}

		if(!$avatarurl && $user_id){
			$avatarurl	= get_user_meta($user_id, 'avatarurl', true);
		}

		if($avatarurl){
			$url	= wpjam_get_thumbnail($avatarurl, [$args['width'], $args['height']]);

			return array_merge($args, ['found_avatar'=>true,	'url'=>$url,]);
		}

		if($user_id){
			$args['user_id']	= $user_id;
		}

		if($email){
			$args['email']		= $email;
		}

		return $args;
	}
}

class WPJAM_Load{
	private $hooks;
	private $callback;

	public function add_action($hooks, $callback){
		$this->hooks	= $hooks;
		$this->callback	= $callback;

		foreach($hooks as $hook){
			add_action($hook, [$this, 'callback']);
		}
	}

	public function callback(){
		foreach($this->hooks as $hook){
			if(!did_action($hook)){
				return;
			}
		}

		call_user_func($this->callback);
	}
}

function wpjam_load($hooks, $callback){
	$todo 	= [];

	foreach((array)$hooks as $hook){
		if(!did_action($hook)){
			$todo[]	= $hook;
		}
	}

	if(empty($todo)){
		call_user_func($callback);
	}elseif(count($todo) == 1){
		add_action(current($todo), $callback);
	}else{
		$object	= new WPJAM_Load();
		$object->add_action($todo, $callback);
	}
}

function wpjam_autoload(){
	foreach(get_declared_classes() as $class){
		if(is_subclass_of($class, 'WPJAM_Register') && method_exists($class, 'autoload')){
			call_user_func([$class, 'autoload']);
		}
	}
}

function wpjam_actives(){
	$actives = get_option('wpjam-actives', null);

	if(is_array($actives)){
		foreach($actives as $active){
			if(is_array($active) && isset($active['hook'])){
				add_action($active['hook'], $active['callback']);
			}else{
				add_action('wp_loaded', $active);
			}
		}

		update_option('wpjam-actives', []);
	}elseif(is_null($actives)){
		update_option('wpjam-actives', []);
	}
}

function wpjam_register_route_module($name, $args){
	return WPJAM_Route::create($name, $args);
}

function wpjam_is_module($module='', $action=''){
	$current_module	= wpjam_get_current_module();

	if($module){
		if($action && $action != wpjam_get_current_action()){
			return false;
		}

		return $module == $current_module;
	}else{
		return $current_module ? true : false;
	}
}

function wpjam_get_query_var($key, $wp=null){
	$wp	= $wp ?: $GLOBALS['wp'];

	return $wp->query_vars[$key] ?? null;
}

function wpjam_get_current_module($wp=null){
	return wpjam_get_query_var('module', $wp);
}

function wpjam_get_current_action($wp=null){
	return wpjam_get_query_var('action', $wp);
}

function wpjam_get_current_object(){
	return WPJAM_Current::get_instance();
}

function wpjam_get_current_var($name, &$isset=false){
	$object	= wpjam_get_current_object();

	if(isset($object->$name)){
		$isset	= true;

		return $object->$name;
	}else{
		return null;
	}
}

function wpjam_set_current_var($name, $value){
	$object	= wpjam_get_current_object();

	$object->$name	= $value;
}

function wpjam_get_device(){
	return wpjam_get_current_var('device');
}

function wpjam_get_os(){
	return wpjam_get_current_var('os');
}

function wpjam_get_browser(){
	return wpjam_get_current_var('browser');
}

function wpjam_get_app(){
	return wpjam_get_current_var('app');
}

function wpjam_get_browser_version(){
	return wpjam_get_current_var('browser_version');
}

function wpjam_get_app_version(){
	return wpjam_get_current_var('app_version');
}

function wpjam_get_os_version(){
	return wpjam_get_current_var('os_version');
}

function is_ipad(){
	return wpjam_get_device() == 'iPad';
}

function is_iphone(){
	return wpjam_get_device() == 'iPone';
}

function is_ios(){
	return wpjam_get_os() == 'iOS';
}

function is_macintosh(){
	return wpjam_get_os() == 'Macintosh';
}

function is_android(){
	return wpjam_get_os() == 'Android';
}

// 判断微信内置浏览器
function is_weixin(){ 
	if(isset($_GET['weixin_appid'])){
		return true;
	}

	return wpjam_get_app() == 'weixin';
}

// 判断微信小程序
function is_weapp(){ 
	if(isset($_GET['appid'])){
		return true;
	}

	return wpjam_get_app() == 'weapp';
}

// 判断字节小程序
function is_bytedance(){ 
	if(isset($_GET['bytedance_appid'])){
		return true;
	}

	return wpjam_get_app() == 'bytedance';
}

function wpjam_is_webp_supported(){
	return $GLOBALS['is_chrome'] || is_android() || (is_ios() && version_compare(wpjam_get_os_version(), 14) >= 0);
}

function wpjam_get_current_items($name){
	return wpjam_get_current_object()->get_items($name);
}

function wpjam_get_current_item($name, $key){
	return wpjam_get_current_object()->get_item($name, $key);
}

function wpjam_add_current_item($name, ...$args){
	return wpjam_get_current_object()->add_item($name, ...$args);
}

function wpjam_replace_current_item($name, $key, $item){
	return wpjam_get_current_object()->replace_item($name, $key, $item);
}

function wpjam_set_current_item($name, $key, $item){
	return wpjam_get_current_object()->set_item($name, $key, $item);
}

function wpjam_delete_current_item($name, $key){
	return wpjam_get_current_object()->delete_item($name, $key);
}

function wpjam_get_current_user($required=false){
	$user	= wpjam_get_current_var('user', $isset);

	if(!$isset){
		$user	= apply_filters('wpjam_current_user', null);

		wpjam_set_current_var('user', $user);
	}

	if($required){
		if(is_null($user)){
			return new WP_Error('bad_authentication', '无权限');
		}
	}else{
		if(is_wp_error($user)){
			return null;
		}
	}

	return $user;
}

function wpjam_get_current_commenter(){
	$commenter	= wp_get_current_commenter();

	if(empty($commenter['comment_author_email'])){
		return new WP_Error('bad_authentication', '无权限');
	}

	return $commenter;
}

function wpjam_json_encode($data){
	return wp_json_encode($data, JSON_UNESCAPED_UNICODE);
}

function wpjam_json_decode($json, $assoc=true){
	$json	= wpjam_strip_control_characters($json);

	if(!$json){
		return new WP_Error('empty_json', 'JSON 内容不能为空！');
	}

	$result	= json_decode($json, $assoc);

	if(is_null($result)){
		$result	= json_decode(stripslashes($json), $assoc);

		if(is_null($result)){
			if(wpjam_doing_debug()){
				print_r(json_last_error());
				print_r(json_last_error_msg());
			}
			trigger_error('json_decode_error '. json_last_error_msg()."\n".var_export($json,true));
			return new WP_Error('json_decode_error', json_last_error_msg());
		}
	}

	return $result;
}

function wpjam_send_json($response=[], $status_code=null, $echo=true){
	if(is_wp_error($response)){
		$response	= wpjam_parse_error($response);
	}else{
		if(is_array($response)){
			if(!$response || !wp_is_numeric_array($response)){
				$response	= wp_parse_args($response, ['errcode'=>0]);
			}
		}elseif($response === true){
			$response	= ['errcode'=>0];
		}elseif($response === false || is_null($response)){
			$response	= ['errcode'=>'-1', 'errmsg'=>'系统数据错误或者回调函数返回错误'];
		}

		$response	= wpjam_filter_error($response);
	}

	$result	= wpjam_json_encode($response);

	if(!headers_sent() && !wpjam_doing_debug()){
		if(!is_null($status_code)){
			status_header($status_code);
		}

		if(wp_is_jsonp_request()){
			$result	= '/**/' . $_GET['_jsonp'] . '(' . $result . ')';

			$content_type	= 'application/jsjavascripton';
		}else{
			$content_type	= 'application/json';
		}

		@header('Content-Type: '.$content_type.'; charset='.get_option('blog_charset'));
	}

	echo $result;

	exit;
}

function wpjam_parse_error($response){
	return WPJAM_Error::parse($response);
}

function wpjam_filter_error($response){
	return WPJAM_Error::filter($response);
}

function wpjam_register_json($name, $args=[]){
	return WPJAM_JSON::register($name, $args);
}

function wpjam_register_api($name, $args=[]){
	return wpjam_register_json($name, $args);
}

function wpjam_get_json_object($name){
	return WPJAM_JSON::get($name);
}

function wpjam_parse_json_module($module){
	$module	= wp_parse_args($module, ['type'=>'', 'args'=>[]]);				
	$object	= new WPJAM_JSON_Module($module['type'], $module['args']);

	return $object->parse();
}

function wpjam_get_current_json($return='name'){
	$json	= wpjam_get_current_var('json');

	if($return == 'object'){
		return $json ? wpjam_get_json_object($json) : null;
	}else{
		return $json;
	}
}

function wpjam_is_json_request(){
	if(get_option('permalink_structure')){
		if(preg_match("/\/api\/(.*)\.json/", $_SERVER['REQUEST_URI'])){ 
			return true;
		}
	}else{
		if(isset($_GET['module']) && $_GET['module'] == 'json'){
			return true;
		}
	}

	return false;
}


function wpjam_get_parameter($name, $args=[]){
	$object	= new WPJAM_Parameter($name, $args);

	return $object->get_value();
}

function wpjam_get_data_parameter($name='', $args=[]){
	if($name){
		$object	= new WPJAM_Parameter($name, $args);

		return $object->get_value('data');
	}else{
		return WPJAM_Parameter::get_data();
	}
}

function wpjam_get_defaults_parameter(){
	return WPJAM_Parameter::get_defaults();
}


function wpjam_method_allow($method, $send=true){
	if($_SERVER['REQUEST_METHOD'] != strtoupper($method)){
		$wp_error = new WP_Error('method_not_allow', '接口不支持 '.$_SERVER['REQUEST_METHOD'].' 方法，请使用 '.$method.' 方法！');

		return $send ? wpjam_send_json($wp_error): $wp_error;
	}

	return true;
}

function wpjam_http_request($url, $args=[], $err_args=[], &$headers=null){
	$object	= new WPJAM_Request($url, $args);

	return $object->request($err_args, $headers);
}

function wpjam_remote_request($url, $args=[], $err_args=[], &$headers=null){
	return wpjam_http_request($url, $args, $err_args, $headers);
}

wpjam_register_extend_option('wpjam-extends', WPJAM_BASIC_PLUGIN_DIR.'extends', [
	'sitewide'	=> true,
	'ajax'		=> false,
	'hook'		=> 'plugins_loaded',
	'priority'	=> 1,
	'menu_page'	=> [
		'parent'		=> 'wpjam-basic',
		'menu_title'	=> '扩展管理',
		'order'			=> 3,
		'function'		=> 'option',
	]
]);

wpjam_load_extends(get_template_directory().'/extends', [
	'hierarchical'	=>	true,
	'hook'			=> 'plugins_loaded',
	'priority'		=> 0,
]);

add_action('plugins_loaded', 'wpjam_actives', 0);

add_action('init',	'wpjam_autoload');

add_filter('register_post_type_args',	['WPJAM_Post_Type', 'filter_register_args'], 999, 2);
add_filter('register_taxonomy_args',	['WPJAM_Taxonomy', 'filter_register_args'], 999, 3);

if(version_compare($GLOBALS['wp_version'], '6.0', '<')){
	add_action('registered_post_type',	['WPJAM_Post_Type', 'on_registered'], 1, 2);
	add_action('registered_taxonomy',	['WPJAM_Taxonomy', 'on_registered'], 1, 3);
}

if(wpjam_is_json_request()){
	ini_set('display_errors', 0);

	remove_filter('the_title', 'convert_chars');

	remove_action('init', 'wp_widgets_init', 1);
	remove_action('init', 'maybe_add_existing_user_to_blog');
	remove_action('init', 'check_theme_switched', 99);

	remove_action('plugins_loaded', 'wp_maybe_load_widgets', 0);
	remove_action('plugins_loaded', 'wp_maybe_load_embeds', 0);
	remove_action('plugins_loaded', '_wp_customize_include');
	remove_action('plugins_loaded', '_wp_theme_json_webfonts_handler');

	remove_action('wp_loaded', '_custom_header_background_just_in_time');
	remove_action('wp_loaded', '_add_template_loader_filters');
}
