<?php
abstract class WPJAM_Model{
	protected $_id;
	protected $_data;
	protected $_modified	= []; 

	public function __construct($data=[], $id=null){
		$this->_data	= $data;
		$this->_id		= $id;
	}

	public function __get($key){
		return $this->get_data($key);
	}

	public function __set($key, $value){
		$this->set_data($key, $value);
	}

	public function __isset($key){
		return isset($this->_data[$key]);
	}

	public function __unset($key){
		unset($this->_data[$key]);
		unset($this->_modified[$key]);
	}

	public function get_primary_id(){
		$key	= self::get_primary_key();

		return $this->get_data($key);
	}

	public function get_data($key=''){
		if($key){
			return $this->_data[$key] ?? null;
		}

		return $this->_data;
	}

	public function set_data($key, $value){
		if(!is_null($this->_id) && self::get_primary_key() == $key){
			trigger_error('不能修改主键的值');
		}else{
			if($this->get_data($key) !== $value){
				$this->_data[$key]		= $value;
				$this->_modified[$key]	= $value;
			}
		}

		return $this;
	}

	public function reset_data(){
		$this->_modified	= [];
		$this->_data		= static::get($this->_id);
	}

	public function to_array(){
		return $this->_data;
	}

	public function save($data=[]){
		if($this->_id){
			$data	= array_merge($this->_modified, $data);
			$data	= wpjam_array_except($data, static::get_primary_key());
			$result	= $data ? static::update($this->_id, $data) : false;
		}else{
			$data	= array_merge($this->_data, $data);

			if($data){
				$result	= static::insert($data);

				if(!is_wp_error($result)){
					$this->_id	= $result;
				}
			}else{
				$result	= false;
			}
		}

		if(!is_wp_error($result)){
			$this->reset_data();
		}

		return $result;
	}

	public static function find($id){
		return static::get_instance($id);
	}

	public static function get_instance($id){
		$data = $id ? static::get($id) : null;

		return $data ? new static($data, $id) : null;
	}

	protected static $_handlers	= [];

	public static function get_handler(){
		$called_class	= get_called_class();

		if(property_exists($called_class, 'handler')){
			return static::$handler;
		}else{
			$called_class	= strtolower($called_class);

			return self::$_handlers[$called_class] ?? null;
		}	
	}

	public static function set_handler($handler){
		$called_class	= get_called_class();

		if(property_exists($called_class, 'handler')){
			static::$handler	= $handler;
		}else{
			$called_class	= strtolower($called_class);

			self::$_handlers[$called_class] = $handler;
		}
	}
	
	// get($id)
	// get_by($field, $value, $order='ASC')
	// get_by_ids($ids)
	// get_searchable_fields()
	// get_filterable_fields()
	// update_caches($values)
	// insert($data)
	// insert_multi($datas)
	// update($id, $data)
	// delete($id)
	// move($id, $data)
	// get_primary_key()
	// get_cache_key($key)
	// get_last_changed
	// get_cache_group
	// cache_get($key)
	// cache_set($key, $data, $cache_time=DAY_IN_SECONDS)
	// cache_add($key, $data, $cache_time=DAY_IN_SECONDS)
	// cache_delete($key)
	public static function __callStatic($method, $args){
		$handler	= static::get_handler();

		if(in_array($method, ['item_callback', 'render_item', 'parse_item', 'render_date'])){
			return $args[0];
		}elseif($method == 'query_data'){
			$args	= $args[0];

			if(!isset($args['orderby'])){
				$args['orderby']	= wpjam_get_data_parameter('orderby');	
			}

			if(!isset($args['order'])){
				$args['order']		= wpjam_get_data_parameter('order');	
			}

			if(!isset($args['search'])){
				$args['search']		= wpjam_get_data_parameter('s');	
			}

			foreach(static::get_filterable_fields() as $filter_key){
				if(!isset($args[$filter_key])){
					$args[$filter_key]	= wpjam_get_data_parameter($filter_key);
				}
			}
			
			$_query = new WPJAM_Query($handler, $args);

			return ['items'=>$_query->items, 'total'=>$_query->total];
		}

		$method_map	= [
			'list'		=> 'query_items',
			'get_ids'	=> 'get_by_ids',
			'get_all'	=> 'get_results'
		];

		if(isset($method_map[$method])){
			$method	= $method_map[$method];
		}

		if(method_exists($handler, $method) || method_exists($handler, '__call')){
			// WPJAM_DB 可能因为 cache 设置为 false
			// 不能直接调用 WPJAM_DB 的 cache_xxx 方法
			if(in_array($method, ['cache_get', 'cache_set', 'cache_add', 'cache_delete'])){
				$method	.= '_force';
			}

			return call_user_func_array([$handler, $method], $args);
		}else{
			return new WP_Error('undefined_method', '「'.$method.'」方法未定义');
		}
	}

	public static function Query($args=[]){
		$handler	= static::get_handler();

		return $args ? new WPJAM_Query($handler, $args) : $handler;
	}

	public static function get_one_by($field, $value, $order='ASC'){
		$items = static::get_by($field, $value, $order);
		return $items ? current($items) : [];
	}

	public static function delete_multi($ids){
		$handler	= static::get_handler();

		if(method_exists($handler, 'delete_multi')){
			return $handler->delete_multi($ids);
		}elseif($ids){
			foreach($ids as $id){
				$result	= $handler->delete($id);

				if(is_wp_error($result)){
					return $result;
				}
			}

			return true;
		}
	}

	public static function get_by_cache_keys($values){
		_deprecated_function(__METHOD__, 'WPJAM Basic 4.4', 'WPJAM_Model::update_caches');
		return static::update_caches($values);
	}
}

class WPJAM_Query{
	public $query;
	public $query_vars;
	public $request;
	public $items;
	public $total	= 0;
	public $handler;

	public function __construct($handler, $query=''){
		$this->handler	= $handler;

		if(!empty($query)){
			$this->query($query);
		}
	}

	public function __call($method, $args){
		return call_user_func([$this->handler, $method], ...$args);
	}

	public function __get($key){
		if($key == 'datas'){
			return $this->items;
		}elseif($key == 'found_rows'){
			return $this->total;
		}elseif($key == 'max_num_pages'){
			if($this->total && $this->query_vars['number'] && $this->query_vars['number'] != -1){
				return ceil($this->total / $this->query_vars['number']);
			}

			return 0;
		}elseif($key == 'next_cursor'){
			if($this->items && $this->max_num_pages > 1){
				$orderby	= $this->query_vars['orderby'];

				return (int)(end($this->items)[$orderby]);
			}

			return 0;
		}else{
			return null;
		}
	}

	public function __isset($key){
		return $this->$key !== null;
	}

	public function query($query){
		$this->query		= $query;
		$this->query_vars	= wp_parse_args($query, [
			'number'	=> 50,
			'orderby'	=> $this->get_primary_key()
		]);

		if($this->get_meta_type()){
			$meta_query	= new WP_Meta_Query();
			$meta_query->parse_query_vars($query);

			$this->set_meta_query($meta_query);
			$this->query_vars	= wpjam_array_except($this->query_vars, ['meta_key', 'meta_value', 'meta_value_num', 'meta_compare', 'meta_query']);
		}

		$this->query_vars	= apply_filters_ref_array('wpjam_query_vars', [$this->query_vars, $this]);

		$orderby 	= $this->query_vars['orderby'];
		$fields		= wpjam_array_pull($this->query_vars, 'fields');

		$total_required	= false;
		$cache_required	= $orderby != 'rand';

		foreach($this->query_vars as $key => $value){
			if(is_null($value)){
				continue;
			}

			if(strpos($key, '__in_set')){
				$this->find_in_set($value, str_replace('__in_set', '', $key));
			}elseif(strpos($key, '__in')){
				$this->where_in(str_replace('__in', '', $key), $value);
			}elseif(strpos($key, '__not_in')){
				$this->where_not_in(str_replace('__not_in', '', $key), $value);
			}elseif(is_array($value)){
				$this->where($key, $value);
			}elseif($key == 'number'){
				if($value != -1){
					$total_required	= true;

					$this->limit($value);
				}
			}elseif($key == 'offset'){
				$total_required	= true;

				$this->offset($value);
			}elseif($key == 'orderby'){
				$this->orderby($value);
			}elseif($key == 'order'){
				$this->order($value);
			}elseif($key == 'first'){
				$this->where_gt($orderby, $value);
			}elseif($key == 'cursor'){
				if($value > 0){
					$this->where_lt($orderby, $value);
				}
			}elseif($key == 'search'){
				$this->search($value);
			}else{
				$this->where($key, $value);
			}
		}

		if($total_required){
			$this->found_rows(true);
		}

		$clauses	= apply_filters_ref_array('wpjam_clauses', [$this->get_clauses($fields), &$this]);
		$request	= apply_filters_ref_array('wpjam_request', [$this->get_sql_by_clauses($clauses), &$this]);

		$this->request	= $request;

		if($cache_required){
			$last_changed	= $this->get_last_changed();
			$cache_group	= $this->get_cache_group();
			$cache_prefix	= $this->get_cache_prefix();
			$key			= md5(maybe_serialize($this->query).$request);
			$cache_key		= 'wpjam_query:'.$key.':'.$last_changed;
			$cache_key		= $cache_prefix ? $cache_prefix.':'.$cache_key : $cache_key;

			$result			= wp_cache_get($cache_key, $cache_group);
		}else{
			$result			= false;
		}

		if($result === false){
			$items	= $GLOBALS['wpdb']->get_results($request, ARRAY_A);
			$items	= $this->filter_results($items, $clauses['fields']);

			$result	= ['items'=>$items];

			if($total_required){
				$result['total']	= $this->find_total();
			}

			if($cache_required){
				wp_cache_set($cache_key, $result, $cache_group, DAY_IN_SECONDS);
			}
		}else{
			// 兼容代码
			$result['items']	= $result['items'] ?? $result['datas'];

			if($total_required){
				$result['total']	= $result['total'] ?? $result['found_rows'];
			}
		}

		$this->items	= apply_filters_ref_array('wpjam_queried_items', [$result['items'], &$this]);
		
		if($total_required){
			$this->total	= $result['total'];
		}

		return $this->items;
	}
}

class WPJAM_Handler extends WPJAM_Register{
	public function __construct($name, $args=[]){
		$init	= wpjam_array_pull($args, 'init');
		$type	= wpjam_array_pull($args, 'type');

		if($type == 'option_items'){
			$handler	= new WPJAM_Option_Items($name, $args);
		}elseif($type == 'db'){
			$handler	= new WPJAM_DB($name, $args);
		}else{
			$handler	= null;
		}

		parent::__construct($name, ['handler'=>$handler, 'init'=>$init]);
	}
}

abstract class WPJAM_Items{
	protected $args;

	public function __construct($args=[]){
		if(!isset($args['max_items'])){
			$args['max_items']	= $args['total'] ?? 0;	// 兼容
		}

		$this->args = wp_parse_args($args, [
			'primary_key'	=> 'id',
			'primary_title'	=> 'ID'
		]);
	}

	public function __get($key){
		return $this->args[$key] ?? null;
	}

	public function __set($key, $value){
		$this->args[$key]	= $value;
	}

	public function __isset($key){
		return $this->$key !== null;
	}

	abstract public function get_items();
	abstract public function update_items($items);

	public function delete_items(){
		return true;
	}

	public function query_items($limit, $offset){
		$items	= $this->get_items();

		return ['items'=>$this->parse_items($items), 'total'=>count($items)];
	}

	public function parse_items($items=null){
		$items	= $items ?? $this->get_items();

		if($items && is_array($items)){
			foreach($items as $id => &$item){
				$item	= $this->parse_item($item, $id);
			}
		}else{
			$items	= [];
		}

		return $items;
	}

	public function parse_item($item, $id){
		return array_merge($item, [$this->primary_key => $id]);
	}

	public function get_primary_key(){
		return $this->primary_key;
	}

	public function get_results(){
		return $this->parse_items();
	}

	public function reset(){
		return $this->delete_items();
	}

	public function exists($value){
		$items	= $this->get_items();

		return $items ? in_array($value, array_column($items, $this->unique_key)) : false;
	}

	public function get($id){
		$items	= $this->get_items();
		$item	= $items[$id] ?? false;

		return $item ? $this->parse_item($item, $id) : false;
	}

	public function insert($item, $last=null){
		$items	= $this->get_items();

		if($this->max_items && count($items) >= $this->max_items){
			return $this->error('over_total', '最大允许数量：'.$this->max_items);
		}

		$item	= wpjam_array_filter($item, 'is_exists');

		if(in_array($this->primary_key, ['option_key', 'id'])){
			if($this->unique_key){
				$value	= $item[$this->unique_key] ?? null;

				if(empty($value)){
					return $this->error('empty', '不能为空', 'unique');
				}

				if($this->exists($value)){
					return $this->error('duplicate', '值重复', 'unique');
				}
			}

			if($items){
				$ids	= array_keys($items);
				$ids	= array_map(function($id){	return (int)(str_replace('option_key_', '', $id)); }, $ids);

				$id		= max($ids);
				$id		= $id+1;
			}else{
				$id		= 1;
			}

			if($this->primary_key == 'option_key'){
				$id		= 'option_key_'.$id;
			}

			$item[$this->primary_key]	= $id;
		}else{
			$id	= $item[$this->primary_key] ?? null;

			if(empty($id)){
				return $this->error('empty', '不能为空', 'primary');
			}

			if(isset($items[$id])){
				return $this->error('duplicate', '值重复', 'primary');
			}
		}

		$last	= $last ?? $this->last;

		if($last){
			$items[$id]	= $item;
		}else{
			$items		= [$id=>$item]+$items;
		}

		$result	= $this->update_items($items);

		if(is_wp_error($result)){
			return $result;
		}

		return ['id'=>$id,	'last'=>(bool)$last];
	}

	public function update($id, $item){
		$items	= $this->get_items();

		if(!isset($items[$id])){
			return $this->error('invalid', '为「'.$id.'」的数据的不存在', 'primary');
		}

		if(in_array($this->primary_key, ['option_key', 'id'])){
			if($this->unique_key && isset($item[$this->unique_key])){
				$value	= $item[$this->unique_key];

				if(!$value){
					return $this->error('empty', '不能为空', 'unique');
				}

				if($value != $items[$id][$this->unique_key]){
					if($this->exists($value)){
						return $this->error('duplicate', '值重复', 'unique');
					}
				}
			}
		}

		$item[$this->primary_key] = $id;

		$item	= wp_parse_args($item, $items[$id]);
		$item	= wpjam_array_filter($item, 'is_exists');

		$items[$id]	= $item;

		return $this->update_items($items);
	}

	public function delete($id){
		$items	= $this->get_items();

		if(!isset($items[$id])){
			return $this->error('invalid', '为「'.$id.'」的数据的不存在', 'primary');
		}

		return $this->update_items(wpjam_array_except($items, $id));
	}

	public function move($id, $data){
		$items	= $this->get_items();

		if(empty($items) || empty($items[$id])){
			return $this->error('key_not_exists', $id.'的值不存在');
		}

		$next	= $data['next'] ?? false;
		$prev	= $data['prev'] ?? false;

		if(!$next && !$prev){
			return $this->error('invalid_move', '无效移动位置');
		}

		$item	= wpjam_array_pull($items, $id);

		if($next){
			if(empty($items[$next])){
				return $this->error('key_not_exists', $next.'的值不存在');
			}

			$offset	= array_search($next, array_keys($items));

			if($offset){
				$items	= array_slice($items, 0, $offset, true) +  [$id => $item] + array_slice($items, $offset, null, true);
			}else{
				$items	= [$id => $item] + $items;
			}
		}else{
			if(empty($items[$prev])){
				return $this->error('key_not_exists', $prev.'的值不存在');
			}

			$offset	= array_search($prev, array_keys($items));
			$offset ++;

			if($offset){
				$items	= array_slice($items, 0, $offset, true) +  [$id => $item] + array_slice($items, $offset, null, true);
			}else{
				$items	= [$id => $item] + $items;
			}
		}

		return $this->update_items($items);
	}

	public function error($errcode, $errmsg, $type=''){
		if($type == 'unique'){
			$errcode	.= '_'.$this->unique_key;
			$errmsg		= $this->unique_title.$errmsg;
		}elseif($type == 'primary'){
			$errcode	.= '_'.$this->primary_key;
			$errmsg		= $this->primary_title.$errmsg;
		}

		return new WP_Error($errcode, $errmsg);
	}
}

class WPJAM_Option_Items extends WPJAM_Items{
	private $option_name;
	
	public function __construct($option_name, $args=[]){
		$this->option_name	= $option_name;

		if(!is_array($args)){
			$args	= ['primary_key' => $args];
		}else{
			$args	= wp_parse_args($args, ['primary_key'=>'option_key']);
		}

		parent::__construct($args);
	}

	public function get_items(){
		return get_option($this->option_name) ?: [];
	}

	public function update_items($items){
		if($items && in_array($this->get_primary_key(), ['option_key','id'])){
			array_walk($items, function(&$item){
				unset($item[$this->get_primary_key()]);
			});
		}

		return update_option($this->option_name, $items);
	}

	public function delete_items(){
		return delete_option($this->option_name);
	}

	protected static $instances	= [];

	public static function get_instance(){
		$class	= get_called_class();
		$name	= strtolower($class);

		if(!isset(self::$instances[$name])){
			$r	= new ReflectionMethod($class, '__construct');

			if($r->getNumberOfParameters()){
				return null;
			}

			self::$instances[$name]	= new static();
		}

		return self::$instances[$name];
	}
}

class WPJAM_Meta_Items extends WPJAM_Items{
	private $meta_type;
	private $object_id;
	private $meta_key;

	public function __construct($meta_type, $object_id, $meta_key, $args=[]){
		$this->meta_type	= $meta_type;
		$this->object_id	= $object_id;
		$this->meta_key		= $meta_key;

		parent::__construct($args);
	}

	public function get_items(){
		return get_metadata($this->meta_type, $this->object_id, $this->meta_key, true) ?: [];
	}

	public function update_items($items){
		if($items && in_array($this->get_primary_key(), ['option_key','id'])){
			array_walk($items, function(&$item){
				unset($item[$this->get_primary_key()]);
				unset($item[$this->meta_type.'_id']);
			});
		}

		return update_metadata($this->meta_type, $this->object_id, $this->meta_key, $items);
	}

	public function delete_items(){
		return delete_metadata($this->meta_type, $this->object_id, $this->meta_key);
	}
}

class WPJAM_Content_Items extends WPJAM_Items{
	private $post_id;

	public function __construct($post_id, $args=[]){
		$this->post_id	= $post_id;

		parent::__construct($args);
	}

	public function get_items(){
		$_post	= get_post($this->post_id);

		return ($_post && $_post->post_content) ? maybe_unserialize($_post->post_content) : [];
	}

	public function update_items($items){
		if($items && in_array($this->get_primary_key(), ['option_key','id'])){
			array_walk($items, function(&$item){
				unset($item[$this->get_primary_key()]);
				unset($item['post_id']);
			});

			$content	= maybe_serialize($items);
		}else{
			$content	= '';
		}
		
		return WPJAM_Post::update($this->post_id, ['post_content'=>$content]);
	}

	public function delete_items(){
		return WPJAM_Post::update($this->post_id, ['post_content'=>'']);
	}
}