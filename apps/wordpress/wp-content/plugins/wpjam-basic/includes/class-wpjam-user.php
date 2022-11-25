<?php
class WPJAM_User{
	private $id;

	private function __construct($id){
		$this->id	= (int)$id;
	}

	public function __get($name){
		if(in_array($name, ['id', 'user_id'])){
			return $this->id;
		}elseif(in_array($name, ['user', 'data'])){
			return get_userdata($this->id);
		}elseif($name == 'avatarurl'){
			return get_user_meta($this->id, 'avatarurl', true);
		}else{
			return $this->data->$name;
		}
	}

	public function __isset($key){
		return $this->$key !== null;
	}

	public function parse_for_json($size=96){
		$json	= [];

		$json['id']			= $this->id;
		$json['nickname']	= $this->nickname;
		$json['name']		= $json['display_name'] = $this->display_name;
		$json['avatar']		= get_avatar_url($this->user, $size);

		return apply_filters('wpjam_user_json', $json, $this->id);
	}

	public function update_avatarurl($avatarurl){
		if($this->avatarurl != $avatarurl){
			update_user_meta($this->id, 'avatarurl', $avatarurl);
		}

		return true;
	}

	public function update_nickname($nickname){
		if($this->nickname != $nickname){
			self::update($this->id, ['nickname'=>$nickname, 'display_name'=>$nickname]);
		}

		return true;
	}

	public function add_role($role, $blog_id=0){
		$switched	= (is_multisite() && $blog_id) ? switch_to_blog($blog_id) : false;	// 不同博客的用户角色不同
		$wp_error	= null;

		if($this->roles){
			if(!in_array($role, $this->roles)){
				$wp_error	= new WP_Error('role_added', '你已有权限，如果需要更改权限，请联系管理员直接修改。');
			}
		}else{
			$this->user->add_role($role);
		}

		if($switched){
			restore_current_blog();
		}

		return $wp_error ?? $this->user;
	}

	public function get_openid($name, $appid=''){
		$signup_object	= wpjam_get_user_signup_object($name);

		return $signup_object->get_openid($this->id);
	}

	public function update_openid($name, $appid, $openid){
		$signup_object	= wpjam_get_user_signup_object($name);

		return $signup_object->update_openid($this->id, $openid);
	}

	public function delete_openid($name, $appid=''){
		$signup_object	= wpjam_get_user_signup_object($name);

		return $signup_object->delete_openid($this->id);
	}

	public function bind($name, $appid, $openid){
		$signup_object	= wpjam_get_user_signup_object($name);

		return $signup_object->bind($openid, $this->id);
	}

	public function unbind($name, $appid=''){
		$signup_object	= wpjam_get_user_signup_object($name);

		return $signup_object->unbind($this->id);
	}

	public function login(){
		wp_set_auth_cookie($this->id, true, is_ssl());
		wp_set_current_user($this->id);
		do_action('wp_login', $this->user_login, $this->user);
	}

	private static $instances	= [];

	public static function get_instance($id){
		$user	= self::validate($id);

		if(is_wp_error($user)){
			return null;
		}

		$id	= $user->ID;

		if(!isset($instances[$id])){
			$instances[$id]	= new self($id);
		}

		return $instances[$id];
	}

	public static function get_user($user){
		if($user && is_numeric($user)){	// 不存在情况下的缓存优化
			$user_id	= $user;
			$found		= false;
			$cache		= wp_cache_get($user_id, 'users', false, $found);

			if($found){
				return $cache ? get_userdata($user_id) : $cache;
			}else{
				$user	= get_userdata($user_id);

				if(!$user){	// 防止重复 SQL 查询。
					wp_cache_add($user_id, false, 'users', 10);
				}
			}
		}

		return $user;
	}

	public static function get($id){
		$user	= get_userdata($id);
		return $user ? $user->to_array() : [];
	}

	public static function insert($data){
		return wp_insert_user(wp_slash($data));
	}

	public static function update($user_id, $data){
		$data['ID'] = $user_id;

		return wp_update_user(wp_slash($data));
	}

	public static function create($args){
		$args	= wp_parse_args($args, [
			'user_pass'		=> wp_generate_password(12, false),
			'user_login'	=> '',
			'user_email'	=> '',
			'nickname'		=> '',
			'avatarurl'		=> '',
			'role'			=> '',
			'blog_id'		=> 0
		]);

		$users_can_register	= $args['users_can_register'] ?? get_option('users_can_register');

		if(!$users_can_register){
			return new WP_Error('register_disabled', '系统不开放注册，请联系管理员！');
		}

		$args['user_login']	= preg_replace('/\s+/', '', sanitize_user($args['user_login'], true));

		if(empty($args['user_login'])){
			return new WP_Error('empty_user_login', '用户名不能为空。');
		}

		if(empty($args['user_email'])){
			return new WP_Error('empty_user_email', '用户邮箱不能为空。');
		}

		$register_lock	= wp_cache_get($args['user_login'].'_register_lock', 'users');

		if($register_lock !== false){
			return new WP_Error('user_register_locked', '该用户名正在注册中，请稍后再试！');
		}

		$result	= wp_cache_add($args['user_login'].'_register_lock', 1, 'users', 15);

		if($result === false){
			return new WP_Error('user_register_locked', '该用户名正在注册中1，请稍后再试！');
		}

		$userdata	= wp_array_slice_assoc($args, ['user_login', 'user_pass', 'user_email']);

		if(!empty($args['nickname'])){
			$userdata['nickname']	= $userdata['display_name']	= $args['nickname'];
		}

		$switched	= (is_multisite() && $args['blog_id']) ? switch_to_blog($args['blog_id']) : false;

		$userdata['role']	= $args['role'] ?: get_option('default_role');

		$user_id	= self::insert($userdata);

		if($switched){
			restore_current_blog();
		}

		if(is_wp_error($user_id)){
			return $user_id;
		}

		return self::get_instance($user_id);
	}

	public static function signup($name, $appid, $openid, $args){
		$signup_object	= wpjam_get_user_signup_object($type);

		return $signup_object->signup($openid);
	}

	public static function get_meta($user_id, ...$args){
		_deprecated_function(__METHOD__, 'WPJAM Basic 6.0', 'wpjam_get_metadata');
		return wpjam_get_metadata('user', $user_id, ...$args);
	}

	public static function update_meta($user_id, ...$args){
		_deprecated_function(__METHOD__, 'WPJAM Basic 6.0', 'wpjam_update_metadata');
		return wpjam_update_metadata('user', $user_id, ...$args);
	}

	public static function update_metas($user_id, $data, $meta_keys=[]){
		_deprecated_function(__METHOD__, 'WPJAM Basic 6.0', 'wpjam_update_metadata');
		return wpjam_update_metadata('user', $user_id, $data, $meta_keys);
	}

	public static function value_callback($meta_key, $user_id){
		return wpjam_get_metadata('user', $user_id, $meta_key);
	}

	public static function validate($user_id){
		$user	= $user_id ? self::get_user($user_id) : null;

		if(!$user || !($user instanceof WP_User)){
			return new WP_Error('user_not_exists', '用户不存在');
		}

		return $user;
	}
}

class WPJAM_Bind extends WPJAM_Register{
	public function __construct($type, $appid, $args=[]){
		$args	= array_merge($args, [
			'type'		=> $type, 
			'appid'		=> $appid,
			'bind_key'	=> $appid ? $type.'_'.$appid : $type,
		]);

		parent::__construct($type.':'.$appid, $args);
	}

	public function bind_by_meta($meta_type, $object_id, $openid){
		$object	= wpjam_get_object_by_meta_type($meta_type, $object_id);

		if(!$object){
			return false;
		}

		$current	= $this->get_meta($meta_type, $object_id);

		if($current && $current != $openid){
			return new WP_Error('already_binded', '该账号已经绑定了其他账号，请先取消绑定再处理！');
		}

		$binded	= $this->get_by_meta($meta_type, $openid);

		if(is_wp_error($binded)){
			return $binded;
		}

		if($binded && $binded->id != $object_id){
			return new WP_Error('already_binded', '已绑定其他账号。');
		}

		$this->update_bind($openid, $meta_type.'_id', $object_id);

		if(!$current){
			return $this->update_meta($meta_type, $object_id, $openid);
		}else{
			return true;
		}
	}

	public function unbind_by_meta($meta_type, $object_id){
		$object	= wpjam_get_object_by_meta_type($meta_type, $object_id);

		if(!$object){
			return false;
		}

		$openid	= $this->get_meta($meta_type, $object_id);
		$openid	= $openid ?: $this->get_openid_by($meta_type.'_id', $object_id);

		if($openid){
			$this->delete_meta($meta_type, $object_id);
			$this->update_bind($openid, $meta_type.'_id', 0);
		}

		return $openid;
	}

	public function get_meta($meta_type, $object_id){
		return get_metadata($meta_type, $object_id, $this->bind_key, true);
	}

	public function update_meta($meta_type, $object_id, $openid){
		return update_metadata($meta_type, $object_id, $this->bind_key, $openid);
	}

	public function delete_meta($meta_type, $object_id){
		return delete_metadata($meta_type, $object_id, $this->bind_key);
	}

	public function get_by_meta($meta_type, $openid){
		if(!$this->get_user($openid)){
			return new WP_Error('invalid_openid', '无效的 Openid');
		}

		$object_id	= $this->get_bind($openid, $meta_type.'_id', true);
		$object		= wpjam_get_object_by_meta_type($meta_type, $object_id);

		if(!$object){
			$meta_data	= wpjam_get_by_meta($meta_type, $this->bind_key, $openid);

			if($meta_data){
				$object_id	= current($meta_data)[$meta_type.'_id'];
				$object		= wpjam_get_object_by_meta_type($meta_type, $object_id);
			}
		}

		if(!$object && $meta_type == 'user'){
			$user_id	= username_exists($openid);
			$object		= $user_id ? wpjam_get_user_object($user_id) : null;
		}

		return $object;
	}

	public function get_bind($openid, $bind_field, $unionid=false){
		return $this->get_value($openid, $bind_field, null);
	}

	public function update_bind($openid, $bind_field, $bind_value){
		$user	= $this->get_user($openid);

		if($user && isset($user[$bind_field]) && $user[$bind_field] != $bind_value){
			return $this->update_user($openid, [$bind_field=>$bind_value]);
		}

		return true;
	}

	public function get_appid(){
		return $this->appid;
	}

	public function get_email($openid){
		$domain	= $this->domain ?: $this->appid.'.'.$this->type;

		return $openid.'@'.$domain;
	}

	public function get_avatarurl($openid){
		return $this->get_value($openid, 'avatarurl');
	}

	public function get_nickname($openid){
		return $this->get_value($openid, 'nickname');
	}

	public function get_unionid($openid){
		return $this->get_value($openid, 'unionid');
	}

	public function get_value($openid, $field, $default=''){
		$user	= $this->get_user($openid);

		if($user){
			return $user[$field] ?? $default;
		}

		return $default;
	}

	public function get_openid_by($key, $value){
		return null;
	}

	public function get_user($openid){
		return ['openid'=>$openid];
	}

	public function update_user($openid, $user){
		return true;
	}

	public static function create($name, $appid, $args){
		if(is_array($args)){
			$object	= new WPJAM_Bind($name, $appid, $args);
		}else{
			$model	= $args;
			
			$object	= new $model($appid, []);
		}

		return WPJAM_Bind::register($object);
	}
}

class WPJAM_Qrcode_Bind extends WPJAM_Bind{
	public function verify_qrcode($scene, $code){
		if(empty($code)){
			return new WP_Error('invalid_code', '验证码不能为空');
		}

		$qrcode	= $this->get_qrcode($scene);

		if(is_wp_error($qrcode)){
			return $qrcode;
		}

		if(empty($qrcode['openid'])){
			return new WP_Error('invalid_code', '请先扫描二维码！');
		}

		if($code != $qrcode['code']){
			return new WP_Error('invalid_code', '验证码错误！');
		}

		$this->cache_delete($scene.'_scene');

		return $qrcode;
	}

	public function scan_qrcode($openid, $scene){
		$qrcode = $this->get_qrcode($scene);

		if(is_wp_error($qrcode)){
			return $qrcode;
		}

		if(!empty($qrcode['openid']) && $qrcode['openid'] != $openid){
			return new WP_Error('qrcode_scaned', '已有用户扫描该二维码！');
		}

		$this->cache_delete($qrcode['key'].'_qrcode');

		if(!empty($qrcode['id']) && !empty($qrcode['bind_callback']) && is_callable($qrcode['bind_callback'])){
			return call_user_func($qrcode['bind_callback'], $openid, $qrcode['id']);
		}else{
			$this->cache_set($scene.'_scene', array_merge($qrcode, ['openid'=>$openid]), 1200);

			return $qrcode['code'];
		}
	}

	public function get_qrcode($scene){
		if(empty($scene)){
			return new WP_Error('invalid_scene', '场景值不能为空');
		}

		$qrcode	= $this->cache_get($scene.'_scene');

		return $qrcode ?: new WP_Error('invalid_scene', '二维码无效或已过期，请刷新页面再来验证！');
	}

	public function create_qrcode($key, $args=[]){}
}

class WPJAM_User_Signup extends WPJAM_Register{
	public function __construct($name, $args=[]){
		if(is_array($args)){
			if(empty($args['type'])){
				$args['type']	= $name;
			}

			parent::__construct($name, $args);
		}	
	}

	public function __call($method, $args){
		$object	= wpjam_get_bind_object($this->type, $this->appid);

		if(in_array($method, ['get_openid', 'update_openid', 'delete_openid', 'get_by_openid', 'bind_by_openid', 'unbind_by_openid'])){
			$args	= array_merge(['user'], $args);
			$method	= str_replace('openid', 'meta', $method);
		}
	
		return call_user_func([$object, $method], ...$args);
	}

	public function signup($openid, $args){
		$user	= $this->get_by_openid($openid);

		if(is_wp_error($user)){
			return $user;
		}

		$args	= apply_filters('wpjam_user_signup_args', $args, $this->type, $this->appid, $openid);

		if(is_wp_error($args)){
			return $args;
		}

		if(!$user){
			$is_create	= true;

			$args['user_login']	= $openid;
			$args['user_email']	= $this->get_email($openid);
			$args['nickname']	= $this->get_nickname($openid);

			$user	= WPJAM_User::create($args);

			if(is_wp_error($user)){
				return $user;
			}
		}else{
			$is_create	= false;
		}

		if(!$is_create && !empty($args['role'])){
			$blog_id	= $args['blog_id'] ?? 0;
			$result		= $user->add_role($args['role'], $blog_id);

			if(is_wp_error($result)){
				return $result;
			}
		}

		$this->bind($openid, $user->id);

		$user->login();

		do_action('wpjam_user_signuped', $user->data, $args);

		return $user;
	}

	public function bind($openid, $user_id=null){
		$user_id	= $user_id ?? get_current_user_id();
		$user		= wpjam_get_user_object($user_id);

		if(!$user){
			return false;
		}

		$result	= $this->bind_by_openid($user_id, $openid);

		if(is_wp_error($result)){
			return $result;
		}

		$avatarurl	= $this->get_avatarurl($openid);

		if($avatarurl){
			$user->update_avatarurl($avatarurl);
		}

		$nickname	= $this->get_nickname($openid);

		if($nickname && (!$user->nickname || $user->nickname == $openid)){
			$user->update_nickname($nickname);
		}

		return $result;
	}

	public function unbind($user_id=null){
		$user_id	= $user_id ?? get_current_user_id();
		
		return $this->unbind_by_openid($user_id);
	}

	public function get_bind_fields(){}

	public function bind_callback(){}

	public function register_bind_user_action(){
		wpjam_register_list_table_action('bind_user', [
			'title'			=> '绑定用户',
			'capability'	=> is_multisite() ? 'manage_sites' : 'manage_options',
			'callback'		=> [$this, 'bind_user_callback'],
			'fields'		=> [
				'nickname'	=> ['title'=>'用户',		'type'=>'view'],
				'user_id'	=> ['title'=>'用户ID',	'type'=>'text',	'class'=>'all-options',	'description'=>'请输入 WordPress 的用户']
			]
		]);
	}

	public function bind_user_callback($openid, $data){
		$user_id	= $data['user_id'] ?? 0;

		if($user_id){
			if(get_userdata($user_id)){
				return $this->bind($openid, $user_id);
			}else{
				return new WP_Error('invalid_user_id', '无效的用户 ID，请确认之后再试！');
			}
		}else{
			$prev_id	= $this->get_bind($openid, 'user_id');

			if($prev_id && get_userdata($prev_id)){
				return $this->unbind($prev_id, $openid);
			}
		}
	}

	public static function create($name, $args){
		$model	= $args['model'] ?? null;
		$type	= $args['type'] ?? $name;
		$appid	= $args['appid'] ?? null;

		if(!wpjam_get_bind_object($type, $appid)){
			return null;
		}

		if(is_object($model)){
			$model	= get_class($model);	// 兼容
		}

		$args['type']	= $type;

		$object	= new $model($name, $args);

		return WPJAM_User_Signup::register($object);
	}
}

class WPJAM_User_Qrcode_Signup extends WPJAM_User_Signup{
	public function __construct($name, $args=[]){
		parent::__construct($name, $args);

		wpjam_register_ajax($name.'_qrcode_signup', [
			'nopriv'	=> true, 
			'callback'	=> [$this, 'ajax_qrcode_signup']
		]);
	}

	public function qrcode_signup($scene, $code, $args=[]){
		if($user = apply_filters('wpjam_qrcode_signup', null, $scene, $code)){
			return $user;
		}

		$qrcode	= $this->verify_qrcode($scene, $code);

		if(is_wp_error($qrcode)){
			if($qrcode->get_error_message() == 'invalid_code'){
				do_action('wpjam_qrcode_signup_failed', $scene);
			}

			return $qrcode;
		}

		return $this->signup($qrcode['openid'], $args);
	}

	public function get_fields($action='login', $from='admin'){
		if($action == 'bind'){
			$user_id	= get_current_user_id();
			$openid		= $this->get_openid($user_id);
		}else{
			$openid		= null;
		}

		$fields	= ['action'	=> ['type'=>'hidden',	'value'=>$action]];

		if($openid){
			$view	= '';

			if($avatar = $this->get_avatarurl($openid)){
				$view	.= '<img src="'.str_replace('/132', '/0', $avatar).'" width="272" />'."<br />";
			}

			if($nickname = $this->get_nickname($openid)){
				$view	.= '<strong>'.$nickname.'</strong>';
			}

			$view	= $view ?: $openid;

			return [
				'view'		=> ['type'=>'view',		'title'=>'绑定的微信账号',	'value'=>$view],
				'action'	=> ['type'=>'hidden',	'value'=>'bind'],
				'bind_type'	=> ['type'=>'hidden',	'value'=>'unbind']
			];
		}else{
			if($action == 'bind'){
				$qrcode	= $this->create_qrcode(md5('bind_'.$user_id), ['id'=>$user_id]);
				$title	= '微信扫码，一键绑定';
			}else{
				$qrcode	= $this->create_qrcode(wp_generate_password(32, false, false));
				$title	= '微信扫码，一键登录';
			}

			if(is_wp_error($qrcode)){
				return $qrcode;
			}

			$fields	= [
				'qrcode'	=> ['type'=>'view',		'title'=>$title,	'value'=>'<img src="'.$qrcode['qrcode_url'].'" width="272" />'],
				'code'		=> ['type'=>'number',	'title'=>'验证码',	'class'=>'input',	'required', 'size'=>20],
				'scene'		=> ['type'=>'hidden',	'value'=>$qrcode['scene']],
				'action'	=> ['type'=>'hidden',	'value'=>$action],
			];

			if($action == 'bind'){
				$fields['bind_type']	= ['type'=>'hidden',	'value'=>'bind'];
			}

			return $fields;
		}
	}

	public function get_bind_fields(){
		return $this->get_fields('bind', 'admin');
	}

	public function bind_callback(){
		$user_id	= get_current_user_id();
		
		if(wpjam_get_data_parameter('bind_type') == 'bind'){
			$scene	= wpjam_get_data_parameter('scene');
			$code	= wpjam_get_data_parameter('code');	

			$qrcode	= $this->verify_qrcode($scene, $code);

			if(is_wp_error($qrcode)){
				return $qrcode;
			}

			return $this->bind($qrcode['openid'], $user_id);
		}else{
			$openid	= $this->get_openid($user_id);

			return $this->unbind($user_id, $openid);
		}
	}

	public function ajax_qrcode_signup(){
		$action	= wpjam_get_data_parameter('action');

		if($action == 'bind'){
			$result	= $this->bind_callback();
		}else{
			$scene	= wpjam_get_data_parameter('scene');
			$code	= wpjam_get_data_parameter('code');
			$args	= wpjam_get_data_parameter('args') ?: [];

			$result	= $this->qrcode_signup($scene, $code, $args);
		}

		return is_wp_error($result) ? $result : [];
	}

	public function ajax_fetch_signup_data($action='login'){
		$fields	= $this->get_fields($action);

		if(is_wp_error($fields)){
			return $fields;
		}

		$attr	= wpjam_get_ajax_attributes($this->name.'_qrcode_signup');
		
		if($action == 'bind'){
			$openid	= $this->get_openid(get_current_user_id());
			$attr	+= ['submit_text'=> $openid ? '解除绑定' : '立刻绑定'];
		}

		return array_merge($attr, ['fields' => wpjam_fields($fields, ['wrap_tag'=>'p', 'echo'=>false])]);
	}
}