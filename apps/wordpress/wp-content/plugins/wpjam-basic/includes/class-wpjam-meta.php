<?php
class WPJAM_Meta_Type extends WPJAM_Register{
	public function __construct($name, $args=[]){
		$name	= sanitize_key($name);
		$args	= wp_parse_args($args, [
			'table_name'		=> $name.'meta',
			'table'				=> $GLOBALS['wpdb']->prefix.$name.'meta',
			'object_callback'	=> 'wpjam_get_'.$name.'_object',
		]);

		if(!isset($GLOBALS['wpdb']->{$args['table_name']})){
			$GLOBALS['wpdb']->{$args['table_name']} = $args['table'];
		}
		
		parent::__construct($name, $args);

		wpjam_register_lazyloader($name.'_meta', [
			'filter'	=> 'get_'.$name.'_metadata',
			'callback'	=> [$this, 'update_cache']
		]);
	}

	public function __call($method, $args){
		if(str_ends_with($method, '_meta')){
			$method	= str_replace('_meta', '_data', $method);
		}elseif(str_contains($method, '_meta')){
			$method	= str_replace('_meta', '', $method);
		}else{
			return;
		}

		if(method_exists($this, $method)){
			return call_user_func([$this, $method], ...$args);
		}else{
			return null;
		}
	}

	public function lazyload_data($ids){
		wpjam_lazyload($this->name.'_meta', $ids);
	}

	public function get_object($id, ...$args){
		$callback	= $this->object_callback;

		if($callback && is_callable($callback)){
			return call_user_func($callback, $id, ...$args);
		}

		return null;
	}

	public function get_data($id, $meta_key='', $single=false){
		return get_metadata($this->name, $id, $meta_key, $single);
	}

	public function get_data_with_default($id, ...$args){
		if(is_array($args[0])){
			$meta_keys	= $args[0];
			$data		= [];

			if($id){
				foreach($this->parse_defaults($meta_keys) as $meta_key => $default){
					$data[$meta_key]	= $this->get_data_with_default($id, $meta_key, $default);
				}	
			}

			return $data;
		}else{
			$meta_key	= $args[0];
			$default	= $args[1] ?? null;

			if($id && metadata_exists($this->name, $id, $meta_key)){
				return $this->get_data($id, $meta_key, true);
			}

			return $default;
		}
	}

	public function add_data($id, $meta_key, $meta_value, $unique=false){
		return add_metadata($this->name, $id, $meta_key, wp_slash($meta_value), $unique);
	}

	public function update_data($id, $meta_key, $meta_value, $prev_value=''){
		return update_metadata($this->name, $id, $meta_key, wp_slash($meta_value), $prev_value);
	}

	public function update_data_with_default($id, ...$args){
		if(is_array($args[0])){
			$data		= $args[0];
			$defaults	= (isset($args[1]) && is_array($args[1])) ? $args[1] : array_keys($data);
			
			foreach($this->parse_defaults($defaults) as $meta_key => $default){
				$meta_value	= $data[$meta_key] ?? null;

				$this->update_data_with_default($id, $meta_key, $meta_value, $default);
			}

			return true;
		}else{
			$meta_key	= $args[0];
			$meta_value	= $args[1];
			$default	= $args[2] ?? null;

			if(is_array($meta_value)){
				if((!is_array($default) && $meta_value) 
					|| (is_array($default) && array_diff_assoc($default, $meta_value))
				){
					return $this->update_data($id, $meta_key, $meta_value);
				}else{
					return $this->delete_data($id, $meta_key);
				}
			}else{
				if(!is_null($meta_value)
					&& $meta_value !== ''
					&& ((is_null($default) && $meta_value)
						|| (!is_null($default) && $meta_value != $default)
					)
				){
					return $this->update_data($id, $meta_key, $meta_value);
				}else{
					return $this->delete_data($id, $meta_key);
				}
			}
		}
	}

	public function parse_defaults($defaults){
		$return	= [];

		foreach($defaults as $meta_key => $default){
			if(is_numeric($meta_key)){
				if(is_numeric($default)){
					continue;
				}

				$meta_key	= $default;
				$default	= null;
			}

			$return[$meta_key]	= $default;
		}

		return $return;
	}

	public function delete_data($id, $meta_key, $meta_value=''){
		return delete_metadata($this->name, $id, $meta_key, $meta_value);
	}

	public function delete_by_key($meta_key, $meta_value=''){
		return delete_metadata($this->name, null, $meta_key, $meta_value, true);
	}

	public function get_by_key(...$args){
		global $wpdb;

		if(empty($args)){
			return [];
		}

		if(is_array($args[0])){
			$meta_key	= wpjam_array_get($args[0], ['meta_key', 'key']);
			$meta_value	= wpjam_array_get($args[0], ['meta_value', 'value']);
		}else{
			$meta_key	= $args[0];
			$meta_value	= $args[1] ?? null;
		}

		$where	= [];

		if($meta_key){
			$where[]	= $wpdb->prepare('meta_key=%s', $meta_key);
		}

		if(!is_null($meta_value)){
			$where[]	= $wpdb->prepare('meta_value=%s', maybe_serialize($meta_value));
		}

		if(!$where){
			return [];
		}

		$where	= implode(' AND ', $where);
		$table	= _get_meta_table($this->name);
		$data	= $wpdb->get_results("SELECT * FROM {$table} WHERE {$where}", ARRAY_A) ?: [];

		foreach($data as &$item){
			$item['meta_value']	= maybe_unserialize($item['meta_value']);
		}

		return $data;
	}

	public function update_cache($ids){
		if($ids){
			update_meta_cache($this->name, $ids);
		}
	}

	public function create_table(){
		$table	= _get_meta_table($this->name);

		if($GLOBALS['wpdb']->get_var("show tables like '{$table}'") != $table){
			$column	= $this->name.'_id';

			$GLOBALS['wpdb']->query("CREATE TABLE {$table} (
				meta_id bigint(20) unsigned NOT NULL auto_increment,
				{$column} bigint(20) unsigned NOT NULL default '0',
				meta_key varchar(255) default NULL,
				meta_value longtext,
				PRIMARY KEY  (meta_id),
				KEY {$column} ({$column}),
				KEY meta_key (meta_key(191))
			)");
		}
	}

	public static function autoload(){
		self::register('post');
		self::register('term');
		self::register('user');
		self::register('comment');

		if(is_multisite()){
			self::register('blog');
			self::register('site');
		}
	}
}

class WPJAM_Meta_Option extends WPJAM_Register{
	public function parse_args(){
		$args	= $this->args;

		if(empty($args['value_callback']) || !is_callable($args['value_callback'])){
			$args['value_callback']	= [$this, 'value_callback'];
		}

		if(empty($args['callback'])){
			$update_callback	= wpjam_array_pull($args, 'update_callback');

			if($update_callback && is_callable($update_callback)){
				$args['callback']	= $update_callback;
			}
		}

		return $args;
	}

	public function get_meta_type(){
		return wpjam_remove_postfix($this->_group, '_option');
	}

	public function get_fields($id=null, $type=''){
		if(is_callable($this->fields)){
			$fields	= call_user_func($this->fields, $id, $this->name);

			return $type == 'object' ? WPJAM_Fields::create($fields) : $fields;
		}else{
			if($type == 'object'){
				if(is_null($this->fields_object)){
					$this->fields_object	= WPJAM_Fields::create($this->fields);
				}

				return $this->fields_object;
			}else{
				return $this->fields;
			}
		}
	}

	public function value_callback($meta_key, $id){
		return wpjam_get_metadata($this->get_meta_type(), $id, $meta_key);
	}

	public function prepare($id=null){
		if($this->callback){
			return [];
		}

		return $this->get_fields($id, 'object')->prepare([
			'value_callback'	=> $this->value_callback,
			'id'				=> $id
		]);
	}

	public function validate($id=null){
		return $this->get_fields($id, 'object')->validate();
	}

	public function callback($id, $data=null){
		if(is_null($data)){
			$data	= $this->validate($id);
		}

		if(is_wp_error($data)){
			return $data;
		}elseif(empty($data)){
			return true;
		}

		if($this->callback){
			if(is_callable($this->callback)){
				$fields	= $this->get_fields($id);
				$result	= call_user_func($this->callback, $id, $data, $fields);

				if(!is_wp_error($result) && $result === false){
					return new WP_Error('invalid_response', '回调函数返回未知错误');
				}

				return $result;
			}else{
				return new WP_Error('invalid_callback', '无效的回调函数');
			}
		}else{
			$defaults	= $this->get_fields($id, 'object')->get_defaults();

			return wpjam_update_metadata($this->get_meta_type(), $id, $data, $defaults);
		}
	}

	public function register_list_table_action(){
		if($this->title && $this->list_table){
			wpjam_register_list_table_action('set_'.$this->name, wp_parse_args($this->to_array(), [
				'page_title'	=> '设置'.$this->title,
				'submit_text'	=> '设置',
				'meta_type'		=> $this->get_meta_type(),
				'fields'		=> [$this, 'get_fields']
			]));
		}	
	}
}

class WPJAM_Post_Option extends WPJAM_Meta_Option{
	public function parse_args(){
		$args	= parent::parse_args();
		$args	= wp_parse_args($args, ['fields'=>[],	'priority'=>'default']);

		if(!isset($args['post_type'])){
			$post_types = wpjam_array_pull($args, 'post_types');

			if($post_types){
				$args['post_type']	= $post_types;
			}
		}

		if(!isset($args['list_table']) && did_action('current_screen') && !in_array(get_current_screen()->base, ['edit', 'upload'])){
			$args['list_table']	= true;
		}

		return $args;
	}

	public function meta_box_cb($post, $meta_box){
		if($this->meta_box_cb){
			call_user_func($this->meta_box_cb, $post, $meta_box);
		}else{
			echo $this->summary ? wpautop($this->summary) : '';

			$args	= [
				'fields_type'	=> $this->context == 'side' ? 'list' : 'table',
				'is_add'		=> $GLOBALS['current_screen']->action == 'add'
			];

			if(!$args['is_add']){
				$args['id']	= $post->ID;

				if($this->data){
					$args['data']	= $this->data;
				}else{
					$args['value_callback']	= $this->value_callback;
				}
			}

			$this->get_fields($post->ID, 'object')->render($args);
		}
	}

	public function is_available($post_type){
		return is_null($this->post_type) || wpjam_compare($post_type, (array)$this->post_type);
	}

	public function is_available_for_post_type($post_type){	// 兼容
		return $this->is_available($post_type);
	}

	public static function on_edit_form($post){
		// 下面代码 copy 自 do_meta_boxes
		$context	= 'wpjam';
		$page		= $GLOBALS['current_screen']->id;
		$meta_boxes	= $GLOBALS['wp_meta_boxes'][$page][$context] ?? [];

		if(empty($meta_boxes)) {
			return;
		}

		$nav_tab_title	= '';
		$meta_box_count	= 0;

		foreach(['high', 'core', 'default', 'low'] as $priority){
			if(empty($meta_boxes[$priority])){
				continue;
			}

			foreach ((array)$meta_boxes[$priority] as $meta_box) {
				if(empty($meta_box['id']) || empty($meta_box['title'])){
					continue;
				}

				$meta_box_count++;
				$meta_box_title	= $meta_box['title'];
				$nav_tab_title	.= '<li><a class="nav-tab" href="#tab_'.$meta_box['id'].'">'.$meta_box_title.'</a></li>';
			}
		}

		if(empty($nav_tab_title)){
			return;
		}

		echo '<div id="'.htmlspecialchars($context).'-sortables">';
		echo '<div id="'.$context.'" class="postbox tabs">' . "\n";

		if($meta_box_count == 1){
			echo '<div class="postbox-header">';
			echo '<h2 class="hndle">'.$meta_box_title.'</h2>';
			echo '</div>';
		}else{
			echo '<h2 class="nav-tab-wrapper"><ul>'.$nav_tab_title.'</ul></h2>';
		}

		echo '<div class="inside">';

		foreach (['high', 'core', 'default', 'low'] as $priority) {
			if (!isset($meta_boxes[$priority])){
				continue;
			}

			foreach ((array) $meta_boxes[$priority] as $meta_box) {
				if(empty($meta_box['id']) || empty($meta_box['title'])){
					continue;
				}

				echo '<div id="tab_'.$meta_box['id'].'">';
				call_user_func($meta_box['callback'], $post, $meta_box);
				echo "</div>\n";
			}
		}

		echo "</div>\n";

		echo "</div>\n";
		echo "</div>";
	}

	public static function on_add_meta_boxes($post_type, $post){
		$context	= use_block_editor_for_post_type($post_type) ? 'normal' : 'wpjam';

		// 输出日志自定义字段表单
		foreach(self::get_by_post_type($post_type) as $name => $object){
			if($object->list_table !== 'only' && $object->title){
				$callback	= [$object, 'meta_box_cb'];
				$context	= $object->context ?: $context;

				add_meta_box($name, $object->title, $callback, $post_type, $context, $object->priority);
			}
		}
	}

	public static function on_after_insert_post($post_id, $post){
		// 非 POST 提交不处理
		// 自动草稿不处理
		// 自动保存不处理
		// 预览不处理
		if($_SERVER['REQUEST_METHOD'] != 'POST'
			|| $post->post_status == 'auto-draft'
			|| (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			|| (!empty($_POST['wp-preview']) && $_POST['wp-preview'] == 'dopreview')
		){
			return;
		}

		$post_type	= get_post_type($post_id);

		foreach(self::get_by_post_type($post_type) as $name => $object){
			if($object->list_table !== 'only'){
				$result	= $object->callback($post_id);

				if(is_wp_error($result)){
					wp_die($result);
				}
			}
		}
	}

	public static function get_by_post_type($post_type){
		if(!$post_type){
			return [];
		}

		if(!self::get($post_type.'_base')){
			$fields	= wpjam_get_post_type_fields($post_type);

			if($fields){
				self::register($post_type.'_base', [
					'title'			=> '基础信息',
					'fields'		=> $fields,
					'list_table'	=> false,
				], 1);
			}
		}

		$objects	= [];

		foreach(self::get_registereds() as $name => $object){
			if($object->is_available($post_type)){
				$objects[$name]	= $object;
			}
		}

		return $objects;
	}
}

class WPJAM_Term_Option extends WPJAM_Meta_Option{
	public function __construct($name, $args=[]){
		if(is_callable($args)){
			trigger_error('callable_term_option_args');
			$args	= ['fields'=>$fields];
		}elseif(!isset($args['fields'])){
			$args['fields']		= [$name => wpjam_array_except($args, 'taxonomy')];
			$args['from_field']	= true;
		}

		parent::__construct($name, $args);
	}

	public function parse_args(){
		$args	= parent::parse_args();

		if(!isset($args['taxonomy'])){
			$taxonomies	= wpjam_array_pull($args, 'taxonomies');

			if($taxonomies){
				$args['taxonomy']	= $taxonomies;
			}
		}

		if(!isset($args['list_table']) && did_action('current_screen') && get_current_screen()->base != 'edit-tags'){
			$args['list_table']	= true;
		}

		return $args;
	}

	public function is_available($taxonomy){
		return is_null($this->taxonomy) || wpjam_compare($taxonomy, (array)$this->taxonomy);
	}

	public function is_available_for_taxonomy($taxonomy){	// 兼容
		return $this->is_available($taxonomy);
	}

	public static function form_fields($taxonomy, $action, $args){
		foreach(self::get_by_taxonomy($taxonomy) as $object){
			if($object->list_table !== 'only'
				&& (!$object->action || $object->action == $action)
			){
				if(!$args['is_add']){
					$args['value_callback']	= $object->value_callback;
				}

				$object->get_fields($args['id'], 'object')->render($args);
			}
		}
	}

	public static function on_add_form_fields($taxonomy){
		self::form_fields($taxonomy, 'add', [
			'fields_type'	=> 'div',
			'wrap_class'	=> 'form-field',
			'is_add'		=> true,
			'id'			=> null,
		]);
	}

	public static function on_edit_form_fields($term){
		self::form_fields($term->taxonomy, 'edit', [
			'fields_type'	=> 'tr',
			'wrap_class'	=> 'form-field',
			'is_add'		=> false,
			'id'			=> $term->term_id,
		]);
	}

	public static function update_data($taxonomy, $action, $term_id=null){
		foreach(self::get_by_taxonomy($taxonomy) as $object){
			if($object->list_table !== 'only'
				&& (!$object->action || $object->action == $action)
			){
				if($term_id){
					$result	= $object->callback($term_id);
				}else{
					$result	= $object->validate();
				}

				if(is_wp_error($result)){
					return $result;
				}
			}
		}
	}

	public static function filter_pre_insert_term($term, $taxonomy){
		$result = self::update_data($taxonomy, 'add');

		if(is_wp_error($result)){
			return $result;
		}

		return $term;
	}

	public static function on_created_term($term_id, $tt_id, $taxonomy){
		$result	= self::update_data($taxonomy, 'add', $term_id);

		if(is_wp_error($result)){
			wp_die($result);
		}
	}

 	public static function on_edited_term($term_id, $tt_id, $taxonomy){
 		$result	= self::update_data($taxonomy, 'edit', $term_id);

 		if(is_wp_error($result)){
			wp_die($result);
		}
	}

	public static function get_by_taxonomy($taxonomy){
		if(!self::get($taxonomy.'_base')){
			$fields	= wpjam_get_taxonomy_fields($taxonomy);

			if($fields){
				self::register($taxonomy.'_base', [
					'taxonomy'		=> $taxonomy,
					'title'			=> '基础信息',
					'fields'		=> $fields,
					'list_table'	=> false,
				]);
			}
		}

		$objects	= [];

		foreach(self::get_registereds() as $name => $object){
			if($object->is_available($taxonomy)){
				$objects[$name]	= $object;
			}
		}

		return $objects;
	}
}