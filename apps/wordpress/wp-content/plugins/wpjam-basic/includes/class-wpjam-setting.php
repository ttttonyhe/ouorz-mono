<?php
class WPJAM_Setting{
	private $type;
	private $name;
	private $blog_id;
	private $value		= null;

	private function __construct($type, $name, $blog_id=0){
		$this->type		= $type;
		$this->name		= $name;
		$this->blog_id	= $blog_id;
	}

	public function get_value($default=[]){
		if($this->type == 'site_option'){
			$value	= get_site_option($this->name, $default);
		}else{
			if($this->blog_id){
				$value	= get_blog_option($this->blog_id, $this->name, $default);
			}else{
				$value	= get_option($this->name, $default);
			}
		}

		if($value === $default){
			return $default;
		}else{
			return $this->sanitize_option($value);
		}
	}

	public function get_option(){
		if(is_null($this->value)){
			$this->value	= $this->get_value();
		}

		return $this->value;
	}

	public function set_option($action, $value){
		$value	= $value ? $this->sanitize_option($value) : $value;

		if($this->type == 'site_option'){
			if($action == 'delete'){
				$result	= delete_site_option($this->name);
			}elseif($action == 'add'){
				$result	= add_site_option($this->name, $value);
			}else{
				$result	= update_site_option($this->name, $value);
			}
		}else{
			if($this->blog_id){
				if($action == 'delete'){
					$result	= delete_blog_option($this->blog_id, $this->name);
				}elseif($action == 'add'){
					$result	= add_blog_option($this->blog_id, $this->name, $value);
				}else{
					$result	= update_blog_option($this->blog_id, $this->name, $value);
				}
			}else{
				if($action == 'delete'){
					$result	= delete_option($this->name);
				}elseif($action == 'add'){
					$result	= add_option($this->name, $value);
				}else{
					$result	= update_option($this->name, $value);
				}
			}
		}

		$this->value	= $this->get_value();
		
		return $result;
	}

	public function update_option($value){
		return $this->set_option('update', $value);
	}

	public function add_option($value){
		return $this->set_option('add', $value);
	}

	public function delete_option(){
		return $this->set_option('delete');
	}

	public function get_setting($name){
		if(is_null($this->value)){
			$this->value	= $this->get_value();
		}

		if($name == ''){
			return $this->value;
		}

		if($this->value && is_array($this->value) && isset($this->value[$name])){
			$value	= $this->value[$name];

			if(is_wp_error($value)){
				return null;
			}

			if(is_string($value)){
				$value	= str_replace("\r\n", "\n", trim($value));
			}

			return $value;
		}else{
			return null;
		}
	}

	public function update_setting($name, $value){
		if(is_null($this->value)){
			$this->value	= $this->get_value();
		}

		return $this->update_option(array_merge($this->value, [$name => $value]));
	}

	public function delete_setting($name){
		if(is_null($this->value)){
			$this->value	= $this->get_value();
		}

		return $this->update_option(wpjam_array_except($this->value, $name));
	}
	
	private static $instances	= [];

	public static function get_instance($type, $name, $blog_id=0){
		if(is_multisite()){
			if($type == 'site_option'){
				$blog_id	= 0;
				$key		= $type.':'.$name;
			}else{
				$blog_id	= (int)$blog_id;
				$blog_id	= $blog_id ?: get_current_blog_id();
				$key		= $type.':'.$blog_id.':'.$name;
			}
		}else{
			$type		= 'option';
			$blog_id	= 0;
			$key		= $type.':'.$name;
		}

		if(!isset(self::$instances[$key])){
			self::$instances[$key]	= $object = new self($type, $name, $blog_id);
		}

		return self::$instances[$key];
	}

	public static function sanitize_option($value){
		return (is_wp_error($value) || empty($value)) ? [] : $value;
	}


	public static function __callStatic($method, $args){
		$function	= 'wpjam_'.$method;

		if(function_exists($function)){
			return call_user_func($function, ...$args);
		}
	}

	public static function get_option_settings(){	// 兼容代码
		return WPJAM_Option_Setting::get_registereds([], 'settings');
	}
}

class WPJAM_Option_Setting extends WPJAM_Register{
	public function __construct($name, $args=[]){
		$args	= is_callable($args) ? call_user_func($args, $name) : $args;
		$args	= apply_filters('wpjam_register_option_args', $args, $name);
		$args	= wp_parse_args($args, [
			'option_group'	=> $name, 
			'option_page'	=> $name, 
			'option_type'	=> 'array',
			'capability'	=> 'manage_options',
			'ajax'			=> true,
			'init'			=> true,
		]);

		if($args['option_type'] == 'array' && !doing_filter('sanitize_option_'.$name)){
			if(is_null(get_option($name, null))){
				add_option($name, []);
			}
		}

		parent::__construct($name, $args);
	}

	public function get_filter(){
		return null;
	}

	public function get_setting($name='', $default=null, $blog_id=0){
		$value	= wpjam_get_setting($this->name, $name, $blog_id);

		if(is_null($value) && $this->site_default && is_multisite()){
			$value	= wpjam_get_site_setting($this->name, $name);
		}

		if(is_null($value)){
			if(is_null($default) && $this->field_default){
				$defaults	= $this->get_fields('object')->get_defaults();

				if($name){
					return $defaults[$name] ?? null;
				}else{
					return $defaults;
				}
			}else{
				return $default;
			}
		}

		return $value;
	}

	public function update_setting($name, $value, $blog_id=0){
		return wpjam_update_setting($this->name, $name, $value, $blog_id);
	}

	public function delete_setting($name, $blog_id=0){
		return wpjam_delete_setting($this->name, $name, $blog_id);
	}

	public function get_sections(){
		if(!$this->sections_filtered){
			$this->sections_filtered	= true;

			$sections	= $this->get_arg('sections');
		
			if(!is_null($sections)){
				if(is_callable($sections)){
					$sections	= call_user_func($sections, $this->name);
				}

				$sections	= is_array($sections) ? $sections : [];
			}else{
				$fields		= $this->get_arg('fields');

				if(!is_null($fields)){
					$sections	= [$this->name => [
						'title'		=> $this->title, 	
						'fields'	=> $fields
					]];
				}else{
					$sections	= $this->args;
				}
			}

			foreach($sections as $section_id => &$section){
				if(is_callable($section['fields'])){
					$section['fields']	= call_user_func($section['fields'], $section_id, $this->name);
				}
			}

			$this->sections = apply_filters('wpjam_option_setting_sections', $sections, $this->name);
			$this->args		= apply_filters('wpjam_option_setting_args', $this->args, $this->name);
		}

		return $this->sections;
	}

	public function get_fields($type=''){
		$fields	= array_merge(...array_values(wp_list_pluck($this->get_sections(), 'fields')));

		if($type == 'object'){
			if(is_null($this->fields_object)){
				$this->fields_object	= WPJAM_Fields::create($fields);
			}

			return $this->fields_object;
		}else{
			return $fields;
		}
	}

	public function get_summary(){
		return $this->get_arg('summary');
	}

	public function prepare(){
		return $this->get_fields('object')->prepare(['value_callback'=>[$this, 'value_callback']]);
	}

	public function validate($value){
		return $this->get_fields('object')->validate($value);
	}

	public function value_callback($name='', $args=[]){
		$is_network_admin	= is_multisite() && is_network_admin();

		if($this->option_type == 'array'){
			if($is_network_admin){
				return wpjam_get_site_setting($this->name, $name);
			}else{
				return $this->get_setting($name);
			}
		}else{
			if($name){
				$callback	= $is_network_admin ? 'get_site_option' : 'get_option';
				$value		= call_user_func($callback, $name, null);

				return is_wp_error($value) ? null : $value;
			}else{
				return null;
			}
		}
	}

	public function register_settings(){
		if($this->capability && $this->capability != 'manage_options'){
			add_filter('option_page_capability_'.$this->option_page, [$this, 'filter_option_page_capability']);
		}

		$args		= ['sanitize_callback'	=> [$this, 'sanitize_callback']];
		$settings	= [];
		
		// 只需注册字段，add_settings_section 和 add_settings_field 可以在具体设置页面添加	
		if($this->option_type == 'single'){
			foreach($this->get_sections() as $section_id => $section){
				foreach($section['fields'] as $key => $field){
					if($field['type'] == 'fieldset' && wpjam_array_get($field, 'fieldset_type') != 'array'){
						foreach ($field['fields'] as $sub_key => $sub_field) {
							$settings[$sub_key]	= array_merge($args, ['field'=>$sub_field]);

							register_setting($this->option_group, $sub_key, $settings[$sub_key]);
						}

						continue;
					}

					$settings[$key]	= array_merge($args, ['field'=>$field]);

					register_setting($this->option_group, $key, $settings[$key]);
				}
			}
		}else{
			$settings[$this->name]	= array_merge($args, ['type'=>'object']);

			register_setting($this->option_group, $this->name, $settings[$this->name]);
		}

		return $settings;
	}

	public function filter_option_page_capability(){
		return $this->capability;
	}

	public function sanitize_callback($value){
		if($this->option_type == 'array'){
			$value		= $this->validate($value) ?: [];
			$current	= $this->value_callback();

			if(!is_wp_error($value)){
				$value	= array_merge($current, $value);
				$value	= wpjam_array_filter($value, 'is_exists');
				$result	= $this->call_method('sanitize_callback', $value, $this->name);

				if(!is_null($result)){
					$value	= $result;
				}
			}

			if(is_wp_error($value)){
				add_settings_error($this->name, $value->get_error_code(), $value->get_error_message());

				return $current;
			}
		}else{
			$option		= str_replace('sanitize_option_', '', current_filter());
			$registered	= get_registered_settings();

			if(!isset($registered[$option])){
				return $value;
			}

			$fields	= [$option=>$registered[$option]['field']];
			$value	= wpjam_validate_fields_value($fields, [$option=>$value]);

			if(is_wp_error($value)){
				add_settings_error($option, $value->get_error_code(), $value->get_error_message());

				return get_option($option);
			}else{
				$value	= $value[$option] ?? null;
			}
		}

		return $value;
	}

	public function ajax_response(){
		$option_page	= wpjam_get_data_parameter('option_page');
		$nonce			= wpjam_get_data_parameter('_wpnonce');

		if($option_page != $this->option_page || !wp_verify_nonce($nonce, $option_page.'-options')){
			wpjam_send_json(['errcode'=>'invalid_nonce',	'errmsg'=>'非法操作']);
		}

		$capability	= $this->capability ?: 'manage_options';

		if(!current_user_can($capability)){
			wpjam_send_json(['errcode'=>'bad_authentication',	'errmsg'=>'无权限']);
		}

		$options	= $this->register_settings();

		if(empty($options)){
			wpjam_send_json(['errcode'=>'invalid_option',	'errmsg'=>'字段未注册']);
		}

		$option_action		= wpjam_get_parameter('option_action', ['method'=>'POST']);
		$is_network_admin	= is_multisite() && is_network_admin();

		foreach($options as $option => $args){
			$option = trim($option);

			if($option_action == 'reset'){
				delete_option($option);
			}else{
				$value	= wpjam_get_data_parameter($option);

				if($this->update_callback && is_callable($this->update_callback)){
					call_user_func($this->update_callback, $option, $value, $is_network_admin);
				}else{
					$callback	= $is_network_admin ? 'update_site_option' : 'update_option';

					if($this->option_type == 'array'){
						$callback	= 'wpjam_'.$callback;
					}else{
						$value		= is_wp_error($value) ? null : $value;
					}

					call_user_func($callback, $option, $value);
				}
			}
		}

		if($settings_errors = get_settings_errors()){
			$errmsg = '';

			foreach ($settings_errors as $key => $details) {
				if (in_array($details['type'], ['updated', 'success', 'info'])) {
					continue;
				}

				$errmsg	.= $details['message'].'&emsp;';
			}

			wpjam_send_json(['errcode'=>'update_failed', 'errmsg'=>$errmsg]);
		}else{
			$response	= $this->response ?? ($this->ajax ? $option_action : 'redirect');
			$errmsg		= $option_action == 'reset' ? '设置已重置。' : '设置已保存。';

			wpjam_send_json(['type'=>$response,	'errmsg'=>$errmsg]);
		}
	}

	public function page_load(){
		if(wp_doing_ajax()){
			add_action('wp_ajax_wpjam-option-action',	[$this, 'ajax_response']);
		}else{
			add_action('admin_action_update', [$this, 'register_settings']);

			if(isset($_POST['response_type'])) {
				$message	= $_POST['response_type'] == 'reset' ? '设置已重置。' : '设置已保存。';

				wpjam_admin_add_error($message);
			}

			$this->register_settings();
		}
	}

	// 部分代码拷贝自 do_settings_sections 和 do_settings_fields 函数
	public function page(){
		$is_tab			= wpjam_get_plugin_page_type() == 'tab';
		$sections		= $this->get_sections();
		$section_count	= count($sections);

		if(!$is_tab && $section_count > 1){
			echo '<div class="tabs">';

			echo '<h2 class="nav-tab-wrapper wp-clearfix"><ul>';

			foreach($sections as $section_id => $section){
				$attr	= ['id'=>'tab_title_'.$section_id, 'class'=>[]];
				
				if(isset($section['show_if'])){
					if($show_if = wpjam_parse_show_if($section['show_if'], $attr['class'])){
						$attr['data']	= ['show_if'=>$show_if];
					}
				}

				echo '<li'.wpjam_attribute_string($attr).'><a class="nav-tab" href="#tab_'.$section_id.'">'.$section['title'].'</a></li>';
			}

			echo '</ul></h2>';
		}

		$attr	= ' id="wpjam_option"';

		echo '<form action="options.php" method="POST"'.$attr.'>';

		settings_errors();

		settings_fields($this->option_group);

		foreach($sections as $section_id => $section){
			echo '<div id="tab_'.$section_id.'"'.'>';

			if($section_count > 1 && !empty($section['title'])){
				$h_tag	= $is_tab ? 'h3' : 'h2';

				echo '<'.$h_tag.'>'.$section['title'].'</'.$h_tag.'>';
			}

			if(!empty($section['callback'])) {
				call_user_func($section['callback'], $section);
			}

			if(!empty($section['summary'])) {
				echo wpautop($section['summary']);
			}

			if(!$section['fields']) {
				echo '</div>';
				continue;
			}

			$args	= [
				'fields_type'		=> 'table',
				'value_callback'	=> [$this, 'value_callback']
			];

			if($this->option_type == 'array'){
				$args['name']	= $this->name;
			}

			wpjam_fields($section['fields'], $args);

			echo '</div>';
		}

		if($section_count > 1){
			echo '</div>';
		}

		echo '<p class="submit">';

		echo get_submit_button('', 'primary', 'option_submit', false, ['data-action'=>'save']);

		if(!empty($this->reset)){
			echo '&emsp;'.get_submit_button('重置选项', 'secondary', 'option_reset', false, ['data-action'=>'reset']);
		}

		echo '</p>';

		echo '</form>';
	}

	public function add_menu_page(){
		$menu_page	= $this->get_arg('menu_page');

		if($menu_page){
			if(is_callable($menu_page)){
				$menu_page	= call_user_func($menu_page, $this->name);
			}

			if(isset($menu_page['plugin_page']) && isset($menu_page['tab_slug'])){
				$tab_slug	= wpjam_array_pull($menu_page, 'tab_slug');

				wpjam_register_plugin_page_tab($tab_slug, $menu_page);
			}else{
				$menu_slug	= wpjam_array_pull($menu_page, 'menu_slug') ?: $this->name;

				wpjam_add_menu_page($menu_slug, $menu_page);
			}
		}
	}

	public static function get_object($name){
		$object	= self::get($name);

		if($object){
			return $object;
		}
	
		$args	= wpjam_get_plugin_page_setting('', true);
		$model	= wpjam_array_pull($args, 'model');

		if($model){
			return call_user_func([$model, 'register_option'], $args);
		}

		if(empty($args['sections']) && empty($args['fields'])){
			$args	= apply_filters(wpjam_get_filter_name($name, 'setting'), []);

			if(!$args){
				return new WP_Error('option_setting_unregistered', 'Option「'.$name.'」 未注册');
			}
		}

		return self::register($name, $args);
	}
}

class WPJAM_Option_Model{
	public static function get_object(){
		$model	= strtolower(get_called_class());

		foreach(WPJAM_Option_Setting::get_registereds() as $object){
			if($object->model && is_string($object->model) && strtolower($object->model) == $model){
				return $object;
			}
		}

		return null;
	}

	public static function get_setting($name='', $default=null){
		$object = self::get_object();

		return $object ? $object->get_setting($name, $default) : $default;
	}

	public static function update_setting($name, $value){
		$object = self::get_object();

		return $object ? $object->update_setting($name, $value) : null;
	}

	public static function delete_setting($name){
		$object = self::get_object();

		return $object ? $object->delete_setting($name) : null;
	}
}

class WPJAM_Extend{
	protected $dir;
	protected $args;
	protected $name;
	
	protected function __construct($dir, $args=[], $name=''){
		$this->dir	= $dir;
		$this->args	= $args;
		$this->name	= $name;
	
		if($this->hook){
			$priority	= $this->priority ?? 10;

			add_action($this->hook, [$this, 'load'], $priority);
		}else{
			$this->load();
		}
	}

	public function __get($key){
		if(in_array($key, ['name', 'dir'])){
			return $this->$key;
		}else{
			return $this->args[$key] ?? null;
		}
	}

	public function __isset($key){
		return $this->$key !== null;
	}

	public function parse_file($extend){
		if($extend == '.' || $extend == '..'){
			return '';
		}

		$file	= '';

		if($this->hierarchical){
			if(is_dir($this->dir.'/'.$extend)){
				$file	= $this->dir.'/'.$extend.'/'.$extend.'.php';
			}
		}else{
			if(pathinfo($extend, PATHINFO_EXTENSION) == 'php'){
				$file	= $this->dir.'/'.$extend;
			}
		}

		return ($file && is_file($file)) ? $file : '';
	}

	public function load_file($extend){
		$file	= $this->parse_file($extend);

		if($file){
			include $file;
		}
	}

	public function load(){
		if($this->name){
			if(is_admin()){
				$summary	= $this->summary ?: '';

				if($this->sitewide && is_multisite() && is_network_admin()){
					$summary	.= $summary ? '，' : '';
					$summary	.= '在管理网络激活将整个站点都会激活！';
				}

				wpjam_register_option($this->name, array_merge($this->args, [
					'fields'	=> [$this, 'get_fields'],
					'ajax'		=> false,
					'summary'	=> $summary,
				]));
			}

			foreach($this->get_values() as $extend => $value){
				$this->load_file($extend);
			}
		}else{
			if($handle = opendir($this->dir)){
				while(false !== ($extend = readdir($handle))){
					$this->load_file($extend);
				}

				closedir($handle);
			}
		}
	}

	public function get_values($type=''){
		if($type == 'blog'){
			$values	= wpjam_get_option($this->name);
			$values	= $values ? array_filter($values) : [];
		}elseif($type == 'site'){
			$values	= wpjam_get_site_option($this->name);
			$values	= $values ? array_filter($values) : [];
		}else{
			$values	= $this->get_values('blog');

			if($this->sitewide && is_multisite()){
				$values	= array_merge($values, $this->get_values('site'));
			}
		}

		return $values;
	}

	public function get_fields(){
		$values	= $this->get_values('blog');

		if(is_multisite() && $this->sitewide){
			$sitewide	= $this->get_values('site');

			if(is_network_admin()){
				$values	= $sitewide;
			}
		}

		$fields	= [];

		if($handle = opendir($this->dir)){
			while(false  !== ($extend = readdir($handle))){
				if(is_multisite() && $this->sitewide && !is_network_admin()){
					if(!empty($sitewide[$extend])){
						continue;
					}
				}

				$file	= $this->parse_file($extend);
				$data	= $this->get_file_data($file);

				if($data && ($data['Name'] || $data['PluginName'])){
					$title	= $data['Name'] ?: $data['PluginName'];
					// $uri	= $data['URI'] ?: $data['PluginURI'];
					$uri	= $data['URI'];

					if($uri){
						$title	= '<a href="'.$uri.'" target="_blank">'.$title.'</a>';
					}

					$fields[$extend] = [
						'title'			=> $title,
						'type'			=> 'checkbox',
						'value'			=> !empty($values[$extend]) ? 1 : 0,
						'description'	=> $data['Description']
					];
				}
			}

			closedir($handle);
		}

		return wp_list_sort($fields, 'value', 'DESC', true);
	}

	public static function get_file_data($file){
		return $file ? get_file_data($file, [
			'Name'			=> 'Name',
			'URI'			=> 'URI',
			'PluginName'	=> 'Plugin Name',
			'PluginURI'		=> 'Plugin URI',
			'Version'		=> 'Version',
			'Description'	=> 'Description'
		]) : [];
	}

	public static function get_file_summay($file){
		$data	= self::get_file_data($file);

		foreach(['URI', 'Name'] as $key){
			if(empty($data[$key])){
				$data[$key]	= $data['Plugin'.$key] ?? '';
			}
		}

		$summary	= str_replace('。', '，', $data['Description']);
		$summary	.= '详细介绍请点击：<a href="'.$data['URI'].'" target="_blank">'.$data['Name'].'</a>。';

		return $summary;
	}

	public static function create($dir, $args=[], $name=''){
		if($dir && is_dir($dir)){
			new self($dir, $args, $name);
		}
	}
}