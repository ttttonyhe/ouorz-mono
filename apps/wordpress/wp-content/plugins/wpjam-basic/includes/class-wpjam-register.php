<?php
class WPJAM_Register{
	protected $name;
	protected $args;
	protected $_group;
	protected $args_filtered	= false;

	public function __construct($name, $args=[], $group=''){
		$this->name		= $name;
		$this->_group	= self::parse_group($group);

		$active	= $args['active'] ?? $this->is_active();
		$init	= wpjam_array_pull($args, 'init');

		if($active && $init){
			$file	= wpjam_array_pull($args, 'file');

			if($file && is_file($file)){
				include_once $file;
			}

			if($init === true){
				$model	= $args['model'] ?? null;

				if($model && method_exists($model, 'init')){
					$init	= [$model, 'init'];
				}else{
					$init	= null;
				}
			}

			if($init && is_callable($init)){
				wpjam_load('init', $init);
			}
		}
		
		$this->args	= $args;
	}

	public function __get($key){
		if($key == 'name'){
			return $this->name;
		}else{
			$this->filter_args();

			return $this->args[$key] ?? null;
		}
	}

	public function __set($key, $value){
		if($key != 'name'){
			$this->filter_args();

			$this->args[$key]	= $value;
		}
	}

	public function __isset($key){
		return $this->$key !== null;
	}

	public function __unset($key){
		$this->filter_args();

		unset($this->args[$key]);
	}

	protected function get_called_method($method, $type=null){
		if($type == 'model'){
			if($this->model && method_exists($this->model, $method)){
				return [$this->model, $method];
			}
		}elseif($type == 'property'){
			if($this->$method && is_callable($this->$method)){
				return $this->$method;
			}
		}else{
			foreach(['model', 'property'] as $type){
				$called_method 	= $this->get_called_method($method, $type);

				if($called_method){
					return $called_method;
				}
			}
		}

		return null;
	}

	protected function call_method($method, ...$args){
		$called_method	= $this->get_called_method($method);

		if($called_method){
			return call_user_func($called_method, ...$args);
		}

		if(str_starts_with($method, 'filter_')){
			return $args[0] ?? null;
		}

		return null;
	}

	protected function method_exists($method, $type=null){
		return $this->get_called_method($method, $type) ? true : false;
	}

	public function get_arg($key, $default=null){
		$value	= $this->$key;

		if(is_null($value) && $this->model){
			$value	= $this->get_called_method('get_'.$key, 'model');
		}

		return $value ?? $default;
	}

	public function update_arg(...$args){
		$args	= is_array($args[0]) ? $args[0] : [$args[0]=>$args[1]];

		foreach($args as $key => $value){
			$this->$key	= $value;
		}

		return true;
	}

	public function parse_args(){	// 继承的子类实现
		return $this->args;
	}

	protected function get_filter(){
		$called_class	= strtolower(get_called_class());

		if($called_class == 'wpjam_register'){
			return 'wpjam_'.$this->_group.'_args';
		}else{
			return $called_class.'_args';
		}
	}

	protected function filter_args(){
		if(!$this->args_filtered){
			$this->args_filtered	= true;

			$args	= $this->parse_args();
			$args	= is_null($args) ? $this->args : $args;
			$filter	= $this->get_filter();

			if($filter){
				$this->args	= apply_filters($filter, $args, $this->name);
			}else{
				$this->args	= $args;
			}
		}

		return $this->args;
	}

	protected function get_args(){
		return $this->filter_args();
	}

	public function to_array(){
		return $this->get_args();
	}

	public function is_active(){
		return true;
	}

	protected static $_registereds		= [];
	protected static $_priorities		= [];
	protected static $_pre_registereds	= [];

	public static function parse_name($name){
		if(empty($name)){
			trigger_error(self::class.'的注册 name 为空');
			return null;
		}elseif(is_numeric($name)){
			trigger_error(self::class.'的注册 name「'.$name.'」'.'为纯数字');
			return null;
		}elseif(!is_string($name)){
			trigger_error(self::class.'的注册 name「'.var_export($name, true).'」不为字符串');
			return null;
		}

		return $name;
	}

	public static function parse_group($group){
		return $group ? strtolower($group): wpjam_remove_prefix(strtolower(get_called_class()), 'wpjam_');
	}

	public static function get_by_group($group, $type=''){
		$group	= self::parse_group($group);

		if($type == 'pre'){
			return self::$_pre_registereds[$group] ?? [];
		}else{
			$_registereds	= [];

			if(isset(self::$_priorities[$group])){
				foreach(self::$_priorities[$group] as $priority => $names){
					$_registereds	= array_merge($_registereds, wp_array_slice_assoc(self::$_registereds[$group], $names));
				}
			}

			return $_registereds;
		}
	}

	public static function register_by_group($group, $name, $args, $type='', $priority=10){
		$group	= self::parse_group($group);
		$object	= is_object($args) ? $args : new static($name, $args, $group);

		if($type == 'pre'){
			self::$_pre_registereds[$group][]	= $object;
		}else{
			if(isset(self::$_registereds[$group][$name])){
				trigger_error($group.'「'.$name.'」已经注册。');

				self::unregister_by_group($group, $name);
			}

			if(!isset(self::$_priorities[$group][$priority])){
				self::$_priorities[$group][$priority]	= [];

				ksort(self::$_priorities[$group], SORT_NUMERIC);
			}

			self::$_priorities[$group][$priority][]	= $name;
			self::$_registereds[$group][$name]		= $object;
		}

		return $object;
	}

	public static function unregister_by_group($group, $name, $args=[], $type=''){
		$group	= self::parse_group($group);

		if($type == 'pre'){
			if(isset(self::$_pre_registereds[$group])){
				foreach(self::$_pre_registereds[$group] as $i => $pre){
					if($pre->name == $name){
						if($args && array_diff($args, $pre->args)){
							continue;
						}

						unset(self::$_pre_registereds[$group][$i]);
					}
				}
			}
		}else{
			if(isset(self::$_registereds[$group])){
				foreach(self::$_priorities[$group] as $priority => &$names){
					if(in_array($name, $names)){
						$names	= array_diff($names, [$name]);
					}
				}

				unset(self::$_registereds[$group][$name]);
			}
		}
	}

	public static function register(...$args){
		if(is_object($args[0])){
			$priority	= $args[1] ?? 10;
			$name		= $args[0]->name;
			$args		= $args[0];
		}else{
			$priority	= $args[2] ?? 10;
			$name		= self::parse_name($args[0]);
			$args		= $args[1] ?? [];

			if(is_null($name)){
				return null;
			}
		}

		return self::register_by_group(null, $name, $args, '', $priority);
	}

	public static function unregister($name){
		self::unregister_by_group(null, $name);
	}

	public static function get_registereds($args=[], $output='objects', $operator='and'){
		$registereds	= self::get_by_group(null);
		$registereds	= $args ? wp_filter_object_list($registereds, $args, $operator, false) : $registereds;

		if($output == 'names'){
			return array_keys($registereds);
		}elseif(in_array($output, ['args', 'settings'])){
			return array_map(function($registered){
				return $registered->to_array();
			}, $registereds);
		}else{
			return $registereds;
		}
	}

	public static function get($name, $group=null){
		if($name){
			$registereds	= self::get_by_group($group);

			return $registereds[$name] ?? null;
		}

		return null;		
	}

	public static function exists($name){
		return self::get($name) ? true : false;
	}

	public static function pre_register($name, $args){
		if($name = self::parse_name($name)){
			return self::register_by_group(null, $name, $args, 'pre');
		}

		return null;
	}

	public static function unregister_pre($name, $args=[]){
		self::unregister_by_group(null, $name, $args, 'pre');
	}

	public static function get_pre_registereds(){
		return self::get_by_group(null, 'pre');
	}

	public static function get_model($args){
		$model	= $args['model'] ?? null;

		if(!$model){
			return null;
		}
		
		if(!class_exists($model)){
			$file	= wpjam_array_pull($args, 'file');

			if($file && is_file($file)){
				include($file);
			}
		}

		if(!class_exists($model)){
			return null;
		}

		return $model;
	}

	public static function get_active($value=null){
		$objects	= [];

		foreach(self::get_registereds() as $name => $object){
			$active	= $object->active;

			if(is_null($active)){
				if(isset($value)){	// sanitize option
					$active	= $value[$name] ?? false;
				}else{
					$active	= $object->is_active();
				}
			}

			if($active){
				$objects[$name]	= $object;
			}
		}

		return $objects;
	}

	public static function get_setting_fields(){
		$fields	= [];

		foreach(self::get_registereds() as $name => $object){
			if(is_null($object->active)){
				$field	= $object->field ?: [];

				$fields[$name]	= wp_parse_args($field, [
					'title'			=> $object->title,
					'type'			=> 'checkbox',
					'description'	=> $object->description ?: '开启'.$object->title
				]);
			}
		}

		return $fields;
	}

	protected static function call_all($active, $method, ...$args){
		if($active){
			$objects	= self::get_active();
		}else{
			$objects	= self::get_registereds();
		}

		$type	= '';

		if(str_starts_with($method, 'filter_')){
			$type	= 'filter_';
		}elseif(str_starts_with($method, 'get_')){
			$return	= [];
			$type	= 'get_';
		}

		foreach($objects as $object){
			$result	= $object->call_method($method, ...$args);

			if(is_wp_error($result)){
				return $result;
			}

			if($method == 'register_json'){
				if($result){
					return $result;
				}
			}elseif($type == 'filter_'){
				$args[0]	= $result;
			}elseif($type == 'get_'){
				if($result && is_array($result)){
					$return	= array_merge($return, $result);
				}
			}
		}

		if($type == 'filter_'){
			return $args[0];
		}elseif($type == 'get_'){
			return $return;
		}
	}

	public static function call_registereds($method, ...$args){
		return self::call_all(false, $method, ...$args);
	}

	public static function call_active($method, ...$args){
		return self::call_all(true, $method, ...$args);
	}
}

class WPJAM_Lazyloader extends WPJAM_Register{
	private $pending_objects	= [];

	public function callback($check){
		if($this->pending_objects){
			if($this->accepted_args && $this->accepted_args > 1){
				foreach($this->pending_objects as $object){
					call_user_func($this->callback, $object['ids'], ...$object['args']);
				}
			}else{
				call_user_func($this->callback, $this->pending_objects);
			}

			$this->pending_objects	= [];
		}

		remove_filter($this->filter, [$this, 'callback']);

		return $check;
	}

	public function queue_objects($object_ids, ...$args){
		if(!$object_ids){
			return;
		}

		if($this->accepted_args && $this->accepted_args > 1){
			if((count($args)+1) >= $this->accepted_args){
				$key	= wpjam_json_encode($args);

				if(!isset($this->pending_objects[$key])){
					$this->pending_objects[$key]	= ['ids'=>[], 'args'=>$args];
				}

				$this->pending_objects[$key]['ids']	= array_merge($this->pending_objects[$key]['ids'], $object_ids);
				$this->pending_objects[$key]['ids']	= array_unique($this->pending_objects[$key]['ids']);
			}
		}else{
			$this->pending_objects	= array_merge($this->pending_objects, $object_ids);
			$this->pending_objects	= array_unique($this->pending_objects);
		}

		add_filter($this->filter, [$this, 'callback']);
	}

	public static function autoload(){
		self::register('user',			['filter'=>'wpjam_get_userdata',	'callback'=>'cache_users']);
		self::register('post_term',		['filter'=>'loop_start',	'callback'=>'update_object_term_cache',	'accepted_args'=>2]);
		self::register('attachment',	['filter'=>'loop_start',	'callback'=>['WPJAM_Post', 'update_attachment_caches']]);
	}
}

class WPJAM_Verification_Code extends WPJAM_Register{
	public function parse_args(){
		return wp_parse_args($this->args, [
			'failed_times'	=> 5,
			'cache_time'	=> MINUTE_IN_SECONDS*30,
			'interval'		=> MINUTE_IN_SECONDS
		]);
	}

	public function __call($method, $args){
		if(in_array($method, ['cache_get', 'cache_set', 'cache_add', 'cache_delete'])){
			$cg_obj	= WPJAM_Cache_Group::get_instance('wpjam_verification_code', ['global'=>true, 'prefix'=>$this->name]);

			return call_user_func_array([$cg_obj, $method], $args);
		}
	}

	public function is_over($key){
		if($this->failed_times && (int)$this->cache_get($key.':failed_times') > $this->failed_times){
			return new WP_Error('too_many_retries', '你已尝试多次错误的验证码，请15分钟后重试！');
		}

		return false;
	}

	public function generate($key){
		if($over = $this->is_over($key)){
			return $over;
		}

		if($this->interval && $this->cache_get($key.':time') !== false){
			return new WP_Error('verification_code_sent', '验证码'.((int)($this->interval/60)).'分钟前已发送了。');
		}

		$code = rand(100000, 999999);

		$this->cache_set($key.':code', $code, $this->cache_time);

		if($this->interval){
			$this->cache_set($key.':time', time(), MINUTE_IN_SECONDS);
		}

		return $code;
	}

	public function verify($key, $code){
		if($over = $this->is_over($key)){
			return $over;
		}

		if(empty($code)){
			return new WP_Error('empty_verification_code', '验证码不能为空');
		}

		$current	= $this->cache_get($key.':code');

		if($current  === false){
			return new WP_Error('verification_code_not_exits', '验证码已过期');
		}

		if($code != $current){
			if($this->failed_times){
				$failed_times	= $this->cache_get($key.':failed_times') ?: 0;
				$failed_times	= $failed_times + 1;

				$this->cache_set($key.':failed_times', $failed_times, $this->cache_time/2);
			}

			return new WP_Error('invalid_verification_code', '验证码错误');
		}

		return true;
	}

	public static function autoload(){
		self::register('default');
	}
}

class WPJAM_Capability extends WPJAM_Register{
	public static function filter_map_meta_cap($caps, $cap, $user_id, $args){
		if(!in_array('do_not_allow', $caps) && $user_id){
			$object = self::get($cap);

			if($object){
				return call_user_func($object->map_meta_cap, $user_id, $args, $cap);
			}
		}

		return $caps;
	}

	public static function create($cap, $map_meta_cap){
		if(!has_filter('map_meta_cap', [self::class, 'filter_map_meta_cap'])){
			add_filter('map_meta_cap', [self::class, 'filter_map_meta_cap'], 10, 4);
		}

		return self::register($cap, ['map_meta_cap'=>$map_meta_cap]);
	}
}

class WPJAM_AJAX extends WPJAM_Register{
	public function __construct($name, $args=[]){
		parent::__construct($name, $args);

		add_action('wp_ajax_'.$name, [$this, 'callback']);

		if(!empty($args['nopriv'])){
			add_action('wp_ajax_nopriv_'.$name, [$this, 'callback']);
		}
	}

	public function create_nonce($args=[]){
		$nonce_action	= $this->name;

		if($this->nonce_keys){
			foreach($this->nonce_keys as $key){
				if(!empty($args[$key])){
					$nonce_action	.= ':'.$args[$key];
				}
			}
		}

		return wp_create_nonce($nonce_action);
	}

	public function verify_nonce($nonce){
		$nonce_action	= $this->name;

		if($this->nonce_keys){
			foreach($this->nonce_keys as $key){
				if($value = wpjam_get_data_parameter($key)){
					$nonce_action	.= ':'.$value;
				}
			}
		}

		return wp_verify_nonce($nonce, $nonce_action);
	}

	public function callback(){
		if(!$this->callback || !is_callable($this->callback)){
			wp_die('0', 400);
		}

		$nonce	= wpjam_get_parameter('_ajax_nonce', ['method'=>'POST']);

		if($this->verify !== false && !$this->verify_nonce($nonce)){
			wpjam_send_json(['errcode'=>'invalid_nonce', 'errmsg'=>'非法操作']);
		}

		wpjam_send_json(call_user_func($this->callback));
	}

	public function get_attributes($data){
		$attr	= ['action'=>$this->name];

		if($this->verify !== false){
			$attr['nonce']	= $this->create_nonce($data);
		}

		if($data){
			$attr['data']	= http_build_query($data);
		}

		return $attr;
	}

	public static function enqueue_scripts(){
		if(wpjam_get_current_var('ajax_enqueued')){
			return;
		}

		wpjam_set_current_var('ajax_enqueued', true);

		$scripts	= '
			if(typeof ajaxurl == "undefined"){
				var ajaxurl	= "'.admin_url('admin-ajax.php').'";
			}

			jQuery(function($){
				if(window.location.protocol == "https:"){
					ajaxurl	= ajaxurl.replace("http://", "https://");
				}

				$.fn.extend({
					wpjam_submit: function(callback){
						let _this	= $(this);

						$.post(ajaxurl, {
							action:			$(this).data(\'action\'),
							_ajax_nonce:	$(this).data(\'nonce\'),
							data:			$(this).serialize()
						},function(data, status){
							callback.call(_this, data);
						});
					},
					wpjam_action: function(callback){
						let _this	= $(this);

						$.post(ajaxurl, {
							action:			$(this).data(\'action\'),
							_ajax_nonce:	$(this).data(\'nonce\'),
							data:			$(this).data(\'data\')
						},function(data, status){
							callback.call(_this, data);
						});
					}
				});
			});
		';

		$scripts	= str_replace("\n\t\t\t", "\n", $scripts);

		if(did_action('wpjam_static') && !wpjam_is_login()){
			wpjam_register_static('wpjam-script',	['title'=>'AJAX 基础脚本', 'type'=>'script',	'source'=>'value',	'value'=>$scripts]);
		}else{
			wp_enqueue_script('jquery');
			wp_add_inline_script('jquery', $scripts);
		}
	}
}

class WPJAM_Verify_TXT extends WPJAM_Register{
	public function get_data($key=''){
		$data	= wpjam_get_setting('wpjam_verify_txts', $this->name) ?: [];

		return $key ? ($data[$key] ?? '') : $data;
	}

	public function set_data($data){
		return wpjam_update_setting('wpjam_verify_txts', $this->name, $data) || true;
	}

	public function get_fields(){
		$data	= $this->get_data();

		return [
			'name'	=>['title'=>'文件名称',	'type'=>'text',	'required', 'value'=>$data['name'] ?? '',	'class'=>'all-options'],
			'value'	=>['title'=>'文件内容',	'type'=>'text',	'required', 'value'=>$data['value'] ?? '']
		];
	}

	public static function __callStatic($method, $args){
		$name	= $args[0];

		if($object = self::get($name)){
			if(in_array($method, ['get_name', 'get_value'])){
				return $object->get_data(str_replace('get_', '', $method));
			}elseif($method == 'set' || $method == 'set_value'){
				return $object->set_data(['name'=>$args[1], 'value'=>$args[2]]);
			}
		}
	}

	public static function filter_root_rewrite_rules($root_rewrite){
		if(empty($GLOBALS['wp_rewrite']->root)){
			$home_path	= parse_url(home_url());

			if(empty($home_path['path']) || '/' == $home_path['path']){
				$root_rewrite	= array_merge(['([^/]+)\.txt?$'=>'index.php?module=txt&action=$matches[1]'], $root_rewrite);
			}
		}

		return $root_rewrite;
	}

	public static function autoload(){
		add_filter('root_rewrite_rules',	[self::class, 'filter_root_rewrite_rules']);
	}

	public static function redirect($action){
		if($values = wpjam_get_option('wpjam_verify_txts')){
			$name	= str_replace('.txt', '', $action).'.txt';

			foreach($values as $key => $value) {
				if($value['name'] == $name){
					header('Content-Type: text/plain');
					echo $value['value'];

					exit;
				}
			}
		}
	}
}

class WPJAM_Cron extends WPJAM_Register{
	public function __construct($name, $args=[]){
		parent::__construct($name, wp_parse_args($args, [
			'recurrence'	=> '',
			'time'			=> time(),
			'args'			=> []
		]));

		$this->schedule();
	}

	public function schedule(){
		if(is_null($this->callback)){
			$this->callback	= [$this, 'callback'];
		}

		if(is_callable($this->callback)){
			add_action($this->name, $this->callback);

			if(!wpjam_is_scheduled_event($this->name)){
				$args	= $this->args['args'] ?? [];

				if($this->recurrence){
					wp_schedule_event($this->time, $this->recurrence, $this->name, $args);
				}else{
					wp_schedule_single_event($this->time, $this->name, $args);
				}
			}
		}
	}

	public function callback(){
		if(get_site_transient($this->name.'_lock')){
			return;
		}

		set_site_transient($this->name.'_lock', 1, 5);

		if($jobs = $this->get_jobs()){
			$callbacks	= array_column($jobs, 'callback');
			$total		= count($callbacks);
			$index		= get_transient($this->name.'_index') ?: 0;
			$index		= $index >= $total ? 0 : $index;
			$callback	= $callbacks[$index];

			set_transient($this->name.'_index', $index+1, DAY_IN_SECONDS);

			$this->increment();

			if(is_callable($callback)){
				call_user_func($callback);
			}else{
				trigger_error('invalid_job_callback'.var_export($callback, true));
			}
		}
	}

	public function get_jobs($jobs=null){
		if(is_null($jobs)){
			$jobs	= $this->jobs;

			if($jobs && is_callable($jobs)){
				$jobs	= call_user_func($jobs);
			}
		}

		$jobs	= $jobs ?: [];

		if(!$jobs || !$this->weight){
			return array_values($jobs);
		}

		$queue	= [];
		$next	= [];

		foreach($jobs as $job){
			$job['weight']	= $job['weight'] ?? 1;

			if($job['weight']){
				$queue[]	= $job;

				if($job['weight'] > 1){
					$job['weight'] --;
					$next[]	= $job;
				}
			}
		}

		if($next){
			$queue	= array_merge($queue, $this->get_jobs($next));
		}

		return $queue;
	}

	public function get_counter($increment=false){
		$today		= date('Y-m-d', current_time('timestamp'));
		$counter	= get_transient($this->name.'_counter:'.$today) ?: 0;

		if($increment){
			$counter ++;
			set_transient($this->name.'_counter:'.$today, $counter, DAY_IN_SECONDS);
		}

		return $counter;
	}

	public function increment(){
		return $this->get_counter(true);
	}

	public static function autoload(){
		add_filter('cron_schedules',	[self::class, 'filter_schedules']);

		if(wp_using_ext_object_cache()){
			add_filter('pre_option_cron',			[self::class, 'filter_pre_option']);
			add_filter('pre_update_option_cron',	[self::class, 'filter_pre_update_option'], 10, 2);
		}

		WPJAM_Job::register_cron();
	}

	public static function is_scheduled($hook) {	// 不用判断参数
		$wp_crons	= _get_cron_array() ?: [];

		foreach($wp_crons as $timestamp => $cron){
			if(isset($cron[$hook])){
				return true;
			}
		}

		return false;
	}

	public static function filter_schedules($schedules){
		return array_merge($schedules, [
			'five_minutes'		=> ['interval'=>300,	'display'=>'每5分钟一次'],
			'fifteen_minutes'	=> ['interval'=>900,	'display'=>'每15分钟一次'],
		]);
	}

	public static function filter_pre_option($pre){
		return get_transient('wpjam_crons') ?: $pre;
	}

	public static function filter_pre_update_option($value, $old_value){
		if(wp_doing_cron()){
			set_transient('wpjam_crons', $value, HOUR_IN_SECONDS*6);

			return $old_value;
		}else{
			delete_transient('wpjam_crons');

			return $value;
		}
	}
}

class WPJAM_Job extends WPJAM_Register{
	public static function get_jobs($raw=false){
		$jobs	= [];
		$day	= (current_time('H') > 2 && current_time('H') < 6) ? 0 : 1;

		foreach(self::get_registereds() as $name => $object){
			if($raw || $object->day == -1 || $object->day == $day){
				$jobs[$name]	= $object->to_array();
			}
		}

		return $jobs;
	}

	public static function register_cron(){
		if(self::get_registereds()){
			wpjam_register_cron('wpjam_scheduled', [
				'recurrence'	=> 'five_minutes',
				'jobs'			=> [self::class, 'get_jobs'],
				'weight'		=> true
			]);
		}
	}

	public static function create($name, $args=[]){
		if(is_numeric($args)){
			$args	= ['weight'	=> $args];
		}else{
			$args	= is_array($args) ? $args : [];
		}

		if(is_callable($name)){
			$args['callback']	= $name;

			if(is_object($name)){
				$name	= get_class($name);
			}elseif(is_array($name)){
				$name	= implode(':', $name);
			}
		}else{
			if(empty($args['callback']) || !is_callable($args['callback'])){
				return null;
			}
		}

		return self::register($name, wp_parse_args($args, ['weight'=>1, 'day'=>-1]));
	}
}

class WPJAM_Cache_Group extends WPJAM_Register{
	public function __call($method, $args){
		if(!str_starts_with($method, 'cache_')){
			return;
		}

		$method	= wpjam_remove_prefix($method, 'cache_');
		$key	= $args[0];

		if($this->prefix){
			$key	= $key ? ':'.$key : '';
			$key	= $this->prefix.$key;
		}

		if(in_array($method, ['get', 'delete'])){
			return call_user_func('wp_cache_'.$method, $key, $this->name);
		}elseif(in_array($method, ['add', 'replace', 'set'])){
			$value	= $args[1];
			$time	= $args[2] ?? ($this->cache_time ?: DAY_IN_SECONDS);

			return call_user_func('wp_cache_'.$method, $key, $value, $this->name, $time);
		}elseif(str_ends_with($method, '_item')){
			$value	= $this->cache_get($key) ?:  [];

			if($method == 'add_item'){
				if(!isset($args[1])){
					return;
				}

				$item	= $args[1];

				if(isset($args[2])){
					$i	= $args[2];

					if(isset($value[$i])){
						return false;
					}

					$value[$i]	= $item;
				}else{
					$value[]	= $item;
				}
			}else{
				if(!isset($args[1])){
					return;
				}

				$i	= $args[1];

				if($method == 'get_item'){
					return $value[$i] ?? null;
				}elseif($method == 'delete_item' || $method == 'remove_item'){
					$value	= wpjam_array_except($value, $i);
				}elseif($method == 'replace_item' || $method == 'update_item' || $method == 'set_item'){
					if(!isset($args[2])){
						return;
					}

					if($method == 'replace_item'){
						if(!isset($value[$i])){
							return false;
						}
					}

					$value[$i]	= $args[2];
				}
			}

			return $this->cache_set($key, $value);
		}
	}

	public static function get_instance($group, $args=[]){
		$object	= self::get($group);

		if(!$object){
			$global	= wpjam_array_pull($args, 'global');
			$object	= self::register($group, $args);

			if($global){
				wp_cache_add_global_groups($group);
			}
		}

		return $object;
	}
}

class WPJAM_Updater extends WPJAM_Register{
	public function get_data($file){
		$type		= $this->type;
		$key		= $this->name.':update_'.$type.'s';
		$response	= get_transient($key);

		if($response === false){
			$response	= wpjam_remote_request($this->update_url);	// https://api.wordpress.org/plugins/update-check/1.1/

			if(!is_wp_error($response)){
				if(isset($response['template']['table'])){
					$response	= $response['template']['table'];
				}else{
					$response	= $response[$type.'s'];
				}

				set_transient($key, $response, MINUTE_IN_SECONDS);
			}else{
				$response	= false;
			}
		}

		if($response){
			if(isset($response['fields']) && isset($response['content'])){
				$fields	= array_column($response['fields'], 'index', 'title');
				$label	= $type == 'plugin' ? '插件' : '主题';
				$index	= $fields[$label];

				foreach($response['content'] as $item){
					if($item['i'.$index] == $file){
						$data	= [];

						foreach($fields as $name => $index){
							$data[$name]	= $item['i'.$index] ?? '';
						}

						return [
							$type			=> $file,
							'url'			=> $data['更新地址'],
							'package'		=> $data['下载地址'],
							'icons'			=> [],
							'banners'		=> [],
							'banners_rtl'	=> [],
							'new_version'	=> $data['版本'],
							'requires_php'	=> $data['PHP最低版本'],
							'requires'		=> $data['最低要求版本'],
							'tested'		=> $data['最新测试版本'],
						];
					}
				}
			}else{
				return $response[$file] ?? [];
			}
		}
	}

	public function filter_update($update, $data, $file, $locales){
		$new_data	= $this->get_data($file);

		if($new_data){
			return wp_parse_args($new_data, [
				'id'		=> $data['UpdateURI'], 
				'version'	=> $data['Version'],
			]);
		}

		return $update;
	}

	public static function create($type, $hostname, $update_url){
		if(in_array($type, ['plugin', 'theme'])){
			$object	= new self($type.':'.$hostname, ['type'=>$type, 'update_url'=>$update_url]);

			add_filter('update_'.$type.'s_'.$hostname, [$object, 'filter_update'], 10, 4);
		
			return $object;
		}
	}
}

class WPJAM_Gravatar extends WPJAM_Register{
	public function replace($url){
		if(is_ssl()){
			$search	= 'https://secure.gravatar.com/avatar/';
		}else{
			$search = [
				'http://0.gravatar.com/avatar/',
				'http://1.gravatar.com/avatar/',
				'http://2.gravatar.com/avatar/',
			];
		}

		return str_replace($search, $this->url, $url);
	}

	public static function get_fields(){
		$options	= wp_list_pluck(self::get_registereds(), 'title');
		$options	= [''=>'默认服务']+preg_filter('/$/', '加速服务', $options)+['custom'=>'自定义加速服务'];

		return [
			'gravatar'			=>['options'=>$options],
			'gravatar_custom'	=>['type'=>'text',	'show_if'=>['key'=>'gravatar','value'=>'custom'],	'placeholder'=>'请输入 Gravatar 加速服务地址']
		];
	}

	public static function autoload(){
		self::register('cravatar',	['title'=>'Cravatar',	'url'=>'https://cravatar.cn/avatar/']);
		self::register('geekzu',	['title'=>'极客族',		'url'=>'https://sdn.geekzu.org/avatar/']);
		self::register('loli',		['title'=>'loli',		'url'=>'https://gravatar.loli.net/avatar/']);
		self::register('sep_cc',	['title'=>'sep.cc',		'url'=>'https://cdn.sep.cc/avatar/']);
	}
}

class WPJAM_Google_Font extends WPJAM_Register{
	public function replace($html){
		$search		= preg_filter('/^/', '//', array_values(self::get_domains()));

		return str_replace($search, $this->replace, $html);
	}

	public static function get_domains(){
		return [
			'googleapis_fonts'			=> 'fonts.googleapis.com',
			'googleapis_ajax'			=> 'ajax.googleapis.com',
			'googleusercontent_themes'	=> 'themes.googleusercontent.com',
			'gstatic_fonts'				=> 'fonts.gstatic.com'
		];
	}

	public static function get_fields(){
		$options	= wp_list_pluck(self::get_registereds(), 'title');
		$options	= [''=>'默认服务']+preg_filter('/$/', '加速服务', $options)+['custom'=>'自定义加速服务'];
		$fields		= ['google_fonts'=>['options'=>$options]];

		foreach(self::get_domains() as $key => $domain){
			$fields[$key]	= ['type'=>'text',	'show_if'=>['key'=>'google_fonts','value'=>'custom'],	'placeholder'=>'请输入'.$domain.'加速服务地址'];
		}

		return $fields;
	}

	public static function autoload(){
		self::register('geekzu',	['title'=>'极客族',	'replace'=>[
			'//fonts.geekzu.org',
			'//gapis.geekzu.org/ajax',
			'//gapis.geekzu.org/g-themes',
			'//gapis.geekzu.org/g-fonts'
		]]);

		self::register('loli',		['title'=>'loli',	'replace'=>[
			'//fonts.loli.net',
			'//ajax.loli.net',
			'//themes.loli.net',
			'//gstatic.loli.net'
		]]);

		self::register('ustc',		['title'=>'中科大',	'replace'=>[
			'//fonts.lug.ustc.edu.cn',
			'//ajax.lug.ustc.edu.cn',
			'//google-themes.lug.ustc.edu.cn',
			'//fonts-gstatic.lug.ustc.edu.cn'
		]]);
	}
}