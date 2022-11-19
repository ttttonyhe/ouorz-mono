<?php
class WPJAM_DB{
	protected $table;
	protected $wpdb;
	protected $args;
	protected $query_vars;
	protected $where		= [];
	protected $meta_query	= false;

	public function __construct($table, array $args = []){
		$args = wp_parse_args($args, [
			'primary_key'		=> 'id',
			'meta_type'			=> '',
			'cache'				=> true,
			'cache_key'			=> '',
			'cache_prefix'		=> '',
			'cache_group'		=> $table,
			'cache_time'		=> DAY_IN_SECONDS,
			'field_types'		=> [],
			'searchable_fields'	=> [],
			'filterable_fields'	=> [],
			'lazyload_callback'	=> null
		]);

		if($args['cache'] && empty($args['cache_key'])){
			$args['cache_key']	= $args['primary_key'];
		}

		if(is_array($args['cache_group'])){
			$group	= $args['cache_group'][0];
			$global	= $args['cache_group'][1] ?? false;

			if($global){
				wp_cache_add_global_groups($group);
			}

			$args['cache_group']	= $group;
		}

		$this->wpdb		= $GLOBALS['wpdb'];
		$this->table	= $table;
		$this->args		= $args;

		$this->clear();
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

	public function __call($method, $args){
		if(in_array($method, [
			'get_table',
			'get_primary_key',
			'get_meta_type',
			'get_cache_group',
			'get_cache_prefix',
			'get_searchable_fields',
			'get_filterable_fields'
		])){
			$key	= substr($method, 4);

			return $this->args[$key];
		}elseif(in_array($method, [
			'set_meta_query',
			'set_searchable_fields',
			'set_filterable_fields'
		])){
			$key	= substr($method, 4);

			$this->args[$key]	= $args[0];
		}elseif(in_array($method, [
			'cache_get',
			'cache_get_force',
			'cache_get_by_primary_key',
			'cache_delete',
			'cache_delete_force',
			'cache_delete_by_primary_key',
			'cache_add',
			'cache_add_force',
			'cache_add_by_primary_key',
			'cache_set',
			'cache_set_force',
			'cache_set_by_primary_key'
		])){
			if(str_ends_with($method, '_force')){
				$method	= str_replace('_force', '', $method);
			}else{
				if(!$this->cache){
					return false;
				}
			}

			$key	= $args[0];

			if(!is_scalar($key)){
				trigger_error(var_export($key, true));
				return false;
			}

			$primary	= str_ends_with($method, '_by_primary_key');
			$method		= str_replace('_by_primary_key', '', $method);
			$key		= $this->get_cache_key($key, $primary);
			$group		= $this->cache_group;

			if($method == 'cache_get'){
				return wp_cache_get($key, $group);
			}elseif($method == 'cache_delete'){
				return wp_cache_delete($key, $group);
			}else{
				$data	= $args[1];
				$time	= !empty($args[2]) ? (int)$args[2] : $this->cache_time;

				if($method == 'cache_add'){
					return wp_cache_add($key, $data, $group, $time);
				}else{
					return wp_cache_set($key, $data, $group, $time);
				}
			}
		}elseif(in_array($method, [
			'get_meta',
			'add_meta',
			'update_meta',
			'delete_meta',
			'lazyload_meta',
			'delete_meta_by_key',
			'update_meta_cache',
			'create_meta_table',
			'get_meta_table',
		])){
			if($this->meta_type){
				if($mt_obj = wpjam_get_meta_type_object($this->meta_type)){
					return call_user_func([$mt_obj, $method], ...$args);
				}
			}
		}

		return new WP_Error('undefined_method', '「'.$method.'」方法未定义');
	}

	public function clear(){
		$this->query_vars	= [
			'limit'			=> 0,
			'offset'		=> 0,
			'orderby'		=> null,
			'order'			=> null,
			'groupby'		=> null,
			'having'		=> null,
			'search_term'	=> null
		];

		$this->where	= [];
	}

	private function get_query_var($var, $default=null){
		return $this->query_vars[$var] ?? $default;
	}

	private function set_query_var($var, $value){
		$this->query_vars[$var]	= $value;

		return $this;
	}

	public function get_last_changed(){
		return wp_cache_get_last_changed($this->cache_group);
	}

	public function set_last_changed(){
		wp_cache_set('last_changed', microtime(), $this->cache_group);
	}

	public function get_cache_key($key, $primary=false){
		if(!$primary && $this->cache_key != $this->primary_key){
			$key	= $this->cache_key.':'.$key;
		}

		return $this->cache_prefix ? $this->cache_prefix.':'.$key : $key;
	}

	public function get_primary_cache_key($id){
		return $this->get_cache_key($id, true);
	}

	public function cache_delete_multi($keys){
		foreach($keys as $key){
			$this->cache_delete($key);
		}
	}

	public function cache_delete_multi_by_primary_key($ids){
		foreach($ids as $id){
			$this->cache_delete_by_primary_key($id);
		}
	}

	public function cache_delete_by_conditions($conditions){
		if($this->cache){
			if(empty($conditions)){
				return;
			}

			if(is_array($conditions)){
				$conditions	= $this->where_any($conditions, 'fragment');
			}

			$fields	= "{$this->primary_key}";

			if($this->cache_key != $this->primary_key){
				$fields	.= ", {$this->cache_key}";
			}

			if($results = $this->wpdb->get_results("SELECT {$fields} FROM `{$this->table}` WHERE {$conditions}", ARRAY_A)){
				foreach($results as $result){
					$this->cache_delete_by_primary_key($result[$this->primary_key]);

					if($this->cache_key != $this->primary_key){
						$this->cache_delete($result[$this->cache_key]);
					}
				}
			}
		}
	}

	public function find_by($field, $value, $order='ASC'){
		$format	= $this->process_field_formats($field);
		$sql	= $this->wpdb->prepare("SELECT * FROM `{$this->table}` WHERE `{$field}` = {$format} ORDER BY `{$this->primary_key}` {$order}", $value);

		return $this->wpdb->get_results($sql, ARRAY_A);
	}

	public function find_one_by($field, $value){
		$format	= $this->process_field_formats($field);
		$sql	= $this->wpdb->prepare("SELECT * FROM `{$this->table}` WHERE `{$field}` = {$format}", $value);

		return $this->wpdb->get_row($sql, ARRAY_A);
	}

	public function find_one($id){
		return $this->find_one_by($this->primary_key, $id);
	}

	public function get($id){
		if($this->cache){
			$result = $this->cache_get_by_primary_key($id);

			if($result === false){
				$result		= $this->find_one($id);
				$cache_time	= $result ? $this->cache_time : MINUTE_IN_SECONDS;

				$this->cache_set_by_primary_key($id, $result, $cache_time);
			}

			return $result;
		}else{
			return $this->find_one($id);
		}
	}

	public function get_by($field, $value, $order='ASC'){
		if($this->cache && $field == $this->cache_key){
			$result = $this->cache_get($value);

			if($result === false){
				$result		= $this->find_by($field, $value, $order);
				$cache_time	= $result ? $this->cache_time : MINUTE_IN_SECONDS;

				$this->cache_set($value, $result, $cache_time);
			}

			return $result;
		}else{
			return $this->find_by($field, $value, $order);
		}
	}

	public function get_values_by($ids, $field){
		$result = $this->wpdb->get_results($this->where_in($field, $ids)->get_sql(), ARRAY_A);

		if($result){
			if($field == $this->primary_key){
				return array_combine(array_column($result, $this->primary_key), $result);
			}else{
				$return = [];
				foreach($ids as $id){
					$return[$id]	= array_values(wp_list_filter($result, [$field => $id]));
				}
				return $return;
			}
		}else{
			return [];
		}
	}

	public function update_caches($ids, $primary=false){
		if($ids && is_array($ids)){
			$ids = array_filter($ids);
			$ids = array_unique($ids);
		}else{
			return [];
		}

		if($this->meta_type){
			$this->lazyload_meta($ids);
		}

		$field	= $primary ? $this->primary_key : $this->cache_key;

		if(!$this->cache){
			return $this->get_values_by($ids, $field);
		}
		
		$cache_ids	= $non_cached_ids = $cache_values = [];

		foreach($ids as $id){
			$cache_key	= $this->get_cache_key($id, $primary);

			$cache_ids[$cache_key]	= $id;
		}

		$caches	= wp_cache_get_multiple(array_keys($cache_ids), $this->cache_group);

		foreach($caches as $cache_key => $cache_value){
			$id	= $cache_ids[$cache_key];

			if($cache_value === false){
				$non_cached_ids[$cache_key]	= $id;
			}else{
				$cache_values[$id]	= $cache_value;
			}
		}

		unset($cache_ids);

		if(empty($non_cached_ids)){
			return $cache_values;
		}

		$datas	= $this->get_values_by(array_values($non_cached_ids), $field);

		foreach($non_cached_ids as $cache_key => $id){
			$cache_values[$id]	= $cache_value	= $datas[$id] ?? [];
			$cache_time			= $cache_value ? $this->cache_time : MINUTE_IN_SECONDS;

			wp_cache_set($cache_key, $cache_value, $this->cache_group, $cache_time);
		}

		unset($non_cached_ids);

		return $cache_values;
	}

	public function get_ids($ids){
		return self::update_caches($ids, $primary=true);
	}

	public function get_by_ids($ids){
		return self::update_caches($ids, $primary=true);
	}

	protected function parse_orderby($orderby){
		if($orderby == 'rand'){
			return 'RAND()';
		}elseif(preg_match('/RAND\(([0-9]+)\)/i', $orderby, $matches)){
			return sprintf('RAND(%s)', (int)$matches[1]);
		}elseif(str_ends_with($orderby, '__in')){
			return '';	// 应该在 WPJAM_Query 里面处理
			// $field	= str_replace('__in', '', $orderby);
		}

		if($this->meta_type && $this->meta_query){
			$primary_meta_key	= '';
			$primary_meta_query	= false;
			$meta_clauses		= $this->meta_query->get_clauses();

			if(!empty($meta_clauses)){
				$primary_meta_query	= reset($meta_clauses);

				if(!empty($primary_meta_query['key'])){
					$primary_meta_key	= $primary_meta_query['key'];
				}

				if($orderby == $primary_meta_key || $orderby == 'meta_value'){
					if(!empty($primary_meta_query['type'])){
						return "CAST({$primary_meta_query['alias']}.meta_value AS {$primary_meta_query['cast']})";
					}else{
						return "{$primary_meta_query['alias']}.meta_value";
					}
				}elseif($orderby == 'meta_value_num'){
					return "{$primary_meta_query['alias']}.meta_value+0";
				}elseif(array_key_exists($orderby, $meta_clauses)){
					$meta_clause	= $meta_clauses[$orderby];
					return "CAST({$meta_clause['alias']}.meta_value AS {$meta_clause['cast']})";
				}
			}
		}
		
		if($orderby == 'meta_value_num' || $orderby == 'meta_value'){
			return '';
		}

		return '`'.sanitize_key($orderby).'`';
	}

	protected function parse_order($order){
		if(!is_string($order) || empty($order)){
			return 'DESC';
		}

		return 'ASC' === strtoupper($order) ? 'ASC' : 'DESC';
	}

	public function get_clauses($fields=[]){
		$distinct	= '';
		$groupby	= $this->get_query_var('groupby');

		if($this->meta_type && $this->meta_query){
			$clauses	= $this->meta_query->get_sql($this->meta_type, $this->table, $this->primary_key, $this);

			$where	= $clauses['where'];
			$join	= $clauses['join'];

			if(!empty($this->meta_query->queries)){
				$groupby	= $groupby ?: $this->table.'.'.$this->primary_key;
				$fields		= $fields ?: $this->table.'.*';
			}
		}else{
			$where	= '';
			$join	= '';
		}

		if($fields){
			if(is_array($fields)){
				$fields	= '`'.implode( '`, `', $fields ). '`';
				$fields	= esc_sql($fields); 
			}
		}else{
			$fields = '*';
		}

		if($groupby){
			if(strstr($groupby, ',') !== false || strstr($groupby, '(') !== false || strstr($groupby, '.') !== false){
				$groupby	= ' GROUP BY ' . $groupby;
			}else{
				$groupby	= ' GROUP BY `' . $groupby . '`';
			}
		}else{
			$groupby	= '';
		}

		if($having = $this->get_query_var('having')){
			$having	= ' HAVING ' . $having;
		}else{
			$having	= '';
		}

		$orderby = $this->get_query_var('orderby');
		$orderby = is_null($orderby) ? $this->primary_key : $orderby;

		if($orderby){
			if(is_array($orderby)){
				$orderby_array	= [];

				foreach($orderby as $_orderby => $order){
					$_orderby	= addslashes_gpc(urldecode($_orderby));

					if($parsed = $this->parse_orderby($_orderby)){
						$orderby_array[]	=  $parsed . ' ' . $this->parse_order($order);
					}
				}

				$orderby	= $orderby_array ? ' ORDER BY '.implode(', ', $orderby_array) : '';
			}elseif(strstr($orderby, '(') !== false && strstr($orderby, ')') !== false){
				$orderby	= ' ORDER BY ' . $orderby;
			}elseif(strstr($orderby, ',') !== false ){
				$orderby	= ' ORDER BY ' . $orderby;
			}else{
				$orderby	= addslashes_gpc(urldecode($orderby));

				if($parsed = $this->parse_orderby($orderby)){
					if($orderby == 'RAND()'){
						$order	= '';
					}else{
						$order	= $this->get_query_var('order') ?: 'DESC';
					}
					
					$orderby	= ' ORDER BY ' . $parsed . ' ' . $order;
				}else{
					$orderby	= '';
				}
			}
		}else{
			$orderby	= '';
		}

		$limits	= '';

		if($this->get_query_var('limit') > 0){
			$limits .= ' LIMIT ' . $this->get_query_var('limit');
		}

		if($this->get_query_var('offset') > 0){
			$limits .= ' OFFSET ' . $this->get_query_var('offset');
		}

		if(!empty($limits) && $this->get_query_var('found_rows')){
			$found_rows	= 'SQL_CALC_FOUND_ROWS';
		}else{
			$found_rows	= '';
		}

		$conditions	= $this->get_conditions();

		if(!$conditions && $where){
			$where	= 'WHERE 1=1 '.$where;
		}else{
			$where	= $conditions.$where;
			$where	= $where ? ' WHERE '.$where : '';
		}

		return compact('where', 'groupby', 'join', 'orderby', 'distinct', 'having', 'fields', 'limits', 'found_rows');
	}

	public function get_sql_by_clauses($clauses){
		$distinct	= $clauses['distinct'];
		$fields		= $clauses['fields'];
		$join		= $clauses['join'];
		$where		= $clauses['where'];
		$groupby	= $clauses['groupby'];
		$having		= $clauses['having'];
		$orderby	= $clauses['orderby'];
		$limits		= $clauses['limits'];
		$found_rows	= $clauses['found_rows'];

		return "SELECT $found_rows $distinct $fields FROM `{$this->table}` $join $where $groupby $having $orderby $limits";
	}

	public function get_sql($fields=[]){
		return $this->get_results($fields, 'sql');
	}

	public function filter_results($results, $fields){
		if(!$results || ($fields != '*' && $fields != $this->table.'.*')){
			return $results;
		}
		
		$ids	= [];

		foreach($results as $result){
			if(!empty($result[$this->primary_key])){
				$id		= $result[$this->primary_key];
				$ids[]	= $id;

				$this->cache_set_by_primary_key($id, $result);
			}
		}

		if($ids){
			if($this->lazyload_callback){
				call_user_func($this->lazyload_callback, $ids, $results);
			}

			if($this->meta_type){
				$this->lazyload_meta($ids);
			}
		}

		return $results;
	}

	public function get_results($fields=[], $return=''){
		$clauses	= $this->get_clauses($fields);
		$sql		= $this->get_sql_by_clauses($clauses);

		if($return == 'sql'){
			return $sql;
		}

		$results	= $this->wpdb->get_results($sql, ARRAY_A);

		return $this->filter_results($results, $clauses['fields']);
	}

	public function get_col($field=''){
		$sql	= $this->get_sql($field);

		return $this->wpdb->get_col($sql);
	}

	public function get_var($field=''){
		$sql	= $this->get_sql($field);

		return $this->wpdb->get_var($sql);
	}

	public function get_row($fields=[]){
		$sql	= $this->get_sql($fields);

		return $this->wpdb->get_row($sql, ARRAY_A);
	}

	public function find($fields=[], $func='get_results'){
		return $this->$func($fields);
	}

	public function find_total($groupby=false){
		return $this->wpdb->get_var("SELECT FOUND_ROWS();");
	}

	public function get_request(){
		return $this->wpdb->last_query;
	}

	public function last_query(){
		return $this->wpdb->last_query;
	}

	public function insert_multi($datas){	// 使用该方法，自增的情况可能无法无法删除缓存，请注意
		$this->set_last_changed();

		if(empty($datas)){
			return new WP_Error('empty_datas', '数据为空');
		}

		$data		= current($datas);
		$formats	= $this->process_field_formats($data);
		$values		= [];
		$fields		= '`'.implode('`, `', array_keys($data)).'`';
		$updates	= implode(', ', array_map(function($field){ return "`$field` = VALUES(`$field`)"; }, array_keys($data)));

		$cache_keys		= [];
		$primary_keys	= [];

		foreach($datas as $data){
			if($data){
				foreach($data as $k => $v){
					if(is_array($v)){
						trigger_error($k.'的值是数组：'.var_export($data,true));
						continue;
					}
				}

				$values[]	= $this->wpdb->prepare('('.implode(', ', $formats).')', array_values($data));

				if(!empty($data[$this->primary_key])){
					$this->cache_delete_by_primary_key($data[$this->primary_key]);

					$primary_keys[]	= $data[$this->primary_key];
				}

				if($this->cache_key != $this->primary_key && !empty($data[$this->cache_key])){
					$this->cache_delete($data[$this->cache_key]);

					$cache_keys[]	= $data[$this->cache_key];
				}
			}
		}

		if($this->cache_key != $this->primary_key){
			$conditions	= [];

			if($primary_keys){
				$conditions[$this->primary_key]	= ['value'=>$primary_keys,	'compare'=>'IN'];
			}

			if($cache_keys){
				$conditions[$this->cache_key]	= ['value'=>$cache_keys,	'compare'=>'IN'];
			}

			$this->cache_delete_by_conditions($conditions);
		}

		$values	= implode(',', $values);
		$sql	= "INSERT INTO `$this->table` ({$fields}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";

		if(wpjam_doing_debug()){
			echo $sql;
		}

		$result	= $this->wpdb->query($sql);

		if(false === $result){
			return new WP_Error('insert_error', $this->wpdb->last_error);
		}else{
			return $result;
		}
	}

	public function insert($data){
		$this->set_last_changed();

		if(!empty($data[$this->primary_key])){
			$this->cache_delete_by_primary_key($data[$this->primary_key]);
		}

		if($this->primary_key != $this->cache_key){
			$conditions = [];

			if(!empty($data[$this->primary_key])){
				$this->cache_delete_by_primary_key($data[$this->primary_key]);

				$conditions[$this->primary_key]	= $data[$this->primary_key];
			}

			if(!empty($data[$this->cache_key])){
				$this->cache_delete($data[$this->cache_key]);

				$conditions[$this->cache_key]	= $data[$this->cache_key];
			}

			$this->cache_delete_by_conditions($conditions);
		}

		if(!empty($data[$this->primary_key])){
			$data 		= array_filter($data, 'is_exists');

			$formats	= $this->process_field_formats($data);
			$fields		= implode(', ', array_keys($data));
			$values		= $this->wpdb->prepare(implode(', ',$formats), array_values($data));
			$updates	= implode(', ', array_map(function($field){ return "`$field` = VALUES(`$field`)"; }, array_keys($data)));

			$this->wpdb->check_current_query = false;

			if(false === $this->wpdb->query("INSERT INTO `$this->table` ({$fields}) VALUES ({$values}) ON DUPLICATE KEY UPDATE {$updates}")){
				return new WP_Error('insert_error', $this->wpdb->last_error);
			}else{
				return $data[$this->primary_key];
			}
		}else{
			$formats	= $this->process_field_formats($data);
			$result 	= $this->wpdb->insert($this->table, $data, $formats);

			if($result === false){
				return new WP_Error('insert_error', $this->wpdb->last_error);
			}else{
				$this->cache_delete_by_primary_key($this->wpdb->insert_id);
				return $this->wpdb->insert_id;
			}
		}
	}

	/*
	用法：
	update($data, $where);
	update($id, $data);
	update($data); // $where各种 参数通过 where() 方法事先传递
	*/
	public function update(...$args){
		$this->set_last_changed();

		$args_num	= count($args);

		if($args_num == 2){
			if(is_array($args[0])){
				$data	= $args[0];
				$where 	= $args[1];

				$conditions	= [];

				$conditions[] = $this->where_all($where, 'fragment');

				if(!empty($data[$this->primary_key])){
					$this->cache_delete_by_primary_key($data[$this->primary_key]);

					$conditions[$this->primary_key]	= $data[$this->primary_key];
				}

				if($this->primary_key != $this->cache_key){
					if(!empty($data[$this->cache_key])){
						$this->cache_delete($data[$this->cache_key]);

						$conditions[$this->cache_key]	= $data[$this->cache_key];
					}
				}

				$this->cache_delete_by_conditions($conditions);
			}else{
				$id		= $args[0];
				$data	= $args[1];
				$where	= [$this->primary_key=>$id];

				$conditions = [];

				$this->cache_delete_by_primary_key($id);

				$conditions[$this->primary_key]	= ['compare'=>'IN', 'value'=>[$id]];

				if(!empty($data[$this->primary_key])){
					if($data[$this->primary_key] != $id){
						$this->cache_delete_by_primary_key($data[$this->primary_key]);

						$conditions[$this->primary_key]['value'][]	= $data[$this->primary_key];
					}
				}

				if($this->primary_key != $this->cache_key && !empty($data[$this->cache_key])){
					$this->cache_delete($data[$this->cache_key]);

					$conditions[$this->cache_key] = $data[$this->cache_key];
				}

				$this->cache_delete_by_conditions($conditions);
			}

			$format			= $this->process_field_formats($data);
			$where_format	= $this->process_field_formats($where);

			$result			= $this->wpdb->update($this->table, $data, $where, $format, $where_format);

			if($result === false){
				return new WP_Error('update_error', $this->wpdb->last_error);
			}else{
				return $result;
			}
		}
		// 如果为空，则需要事先通过各种 where 方法传递进去
		elseif($args_num == 1){
			$data	= $args[0];
			$where	= $this->get_conditions();

			if($data && $where){
				$this->cache_delete_by_conditions($where);

				$conditions	= [];

				if(!empty($data[$this->primary_key])){
					$this->cache_delete_by_primary_key($data[$this->primary_key]);

					$conditions[$this->primary_key] = $data[$this->primary_key];
				}

				if($this->primary_key != $this->cache_key && !empty($data[$this->cache_key])){
					$this->cache_delete($data[$this->cache_key]);

					$conditions[$this->cache_key] = $data[$this->cache_key];
				}

				$this->cache_delete_by_conditions($conditions);

				$fields = $values = [];

				foreach($data as $field => $value){
					if(is_null($value)){
						$fields[] = "`$field` = NULL";
					}else{
						$fields[] = "`$field` = " . $this->process_field_formats($field);
						$values[] = $value;
					}
				}

				$fields = implode(', ', $fields);

				$sql	= $this->wpdb->prepare("UPDATE `{$this->table}` SET {$fields} WHERE {$where}", $values);

				if(wpjam_doing_debug()){
					echo $sql;
				}

				return $this->wpdb->query($sql);
			}else{
				return new WP_Error('update_error', 'WHERE 为空！');
			}
		}
	}

	/*
	用法：
	delete($where);
	delete($id);
	delete(); // $where 参数通过各种 where() 方法事先传递
	*/
	public function delete($where = ''){
		$this->set_last_changed();

		if($where){
			// 如果传递进来字符串或者数字，认为根据主键删除
			if(!is_array($where)){
				$id		= $where; 
				$where	= [$this->primary_key=>$id];

				$this->cache_delete_by_primary_key($id);

				if($this->cache_key != $this->primary_key){
					$this->cache_delete_by_conditions([$this->primary_key => $id]);
				}
			}
			// 传递数组，采用 wpdb 默认方式
			else{
				$this->cache_delete_by_conditions($this->where_all($where, 'fragment'));
			}

			$where_format	= $this->process_field_formats($where);
			$result			= $this->wpdb->delete($this->table, $where, $where_format);

			if($result === false){
				return new WP_Error('delele_error', $this->wpdb->last_error);
			}else{
				return $result;
			}
		}
		// 如果为空，则 $where 参数通过各种 where() 方法事先传递
		else{
			if($where = $this->get_conditions()){
				$this->cache_delete_by_conditions($where);

				$sql = "DELETE FROM `{$this->table}` WHERE {$where}";

				if(wpjam_doing_debug()){
					echo $sql;
				}

				$result = $this->wpdb->query($sql);

				if(false === $result ){
					return new WP_Error('delele_error', $this->wpdb->last_error);
				}else{
					return $result ;
				}
			}else{
				return new WP_Error('delele_error', 'WHERE 为空！');
			}
		}
	}

	public function delete_by($field, $value){
		return $this->delete([$field => $value]);
	}

	public function delete_multi($ids){
		$this->set_last_changed();

		if(empty($ids)){
			return new WP_Error('empty_datas', '数据为空');
		}

		foreach($ids as $id){
			$this->cache_delete_by_primary_key($id);
		}

		if($this->primary_key != $this->cache_key){
			$this->cache_delete_by_conditions([$this->primary_key =>['value'=>$ids, 'compare'=>'IN']]);
		}

		$values = [];

		foreach($ids as $id){
			$values[] = $this->wpdb->prepare($this->process_field_formats($this->primary_key), $id);
		}

		$where = 'WHERE `' . $this->primary_key . '` IN ('.implode(',', $values).') ';

		$sql = "DELETE FROM `{$this->table}` {$where}";

		if(wpjam_doing_debug()){
			echo $sql;
		}

		$result = $this->wpdb->query($sql);

		if(false === $result ){
			return new WP_Error('delele_error', $this->wpdb->last_error);
		}else{
			return $result ;
		}
	}

	public function parse_list($list){
		if(!is_array($list)){
			$list	= preg_split('/[\s,]+/', $list);
		}

		return array_values(array_unique($list));
	}

	private function parse_where($qs=null){
		$where	= [];

		if(is_null($qs)){
			$qs	= $this->where;
		}

		foreach($qs as $q){
			if(isset($q['column'])){
				if(strstr($q['column'], '(') !== false){
					$q_column	= ' '.$q['column'].' ';
					$format		= '%s';
				}else{
					$q_column	= ' `' . $q['column']. '` ';
					$format		= $this->process_field_formats($q['column']);
				}
			}

			if(in_array($q['compare'], ['=', '!=', '<', '<=', '>', '>=', 'LIKE', 'NOT LIKE'])){
				$where[] = $this->wpdb->prepare($q_column.' '.$q['compare'].' '.$format, $q['value']);
			}elseif($q['compare'] == 'IN'){
				$values = [];

				foreach($this->parse_list($q['value']) as $value){
					$values[] = $this->wpdb->prepare($format, $value);
				}

				if(count($values) == 1){
					$where[] = $q_column.' = '.$values[0];
				}elseif($values){
					$where[] = $q_column.' IN ('.implode(',', $values).') ';
				}else{
					$where[] = $q_column.' = \'\'';
				}
			}elseif($q['compare'] == 'NOT IN'){
				$values = [];

				foreach($this->parse_list($q['value']) as $value){
					$values[] = $this->wpdb->prepare($format, $value);
				}

				if(count($values) == 1){
					$where[] = $q_column.' != '.$values[0];
				}elseif($values){
					$where[] = $q_column.' NOT IN ('.implode(',', $values).') ';
				}else{
					$where[] = $q_column.' != \'\'';
				}
			}elseif($q['compare'] == 'fragment'){
				$where[] = $q['fragment'];
			}elseif($q['compare'] == 'find_in_set'){
				$where[] = ' FIND_IN_SET ('.$q['item'].', '.$q['list'].')';
			}
		}

		return $where;
	}

	private function parse_search(){
		if($this->searchable_fields){
			if($search_term = $this->get_query_var('search_term')){
				$where = [];

				foreach($this->searchable_fields as $field){
					$like		= '%'.$this->wpdb->esc_like($search_term).'%';
					$where[]	= $this->wpdb->prepare('`' . $field . '` LIKE  %s', $like);
				}

				return implode(' OR ', $where);
			}
		}

		return '';
	}

	public function get_conditions($return=''){
		$where	= $this->parse_where();

		if($search_where = $this->parse_search()){
			$where[] = ' (' . $search_where . ')';
		}

		$this->clear();

		if($return === 'array'){
			return $where;
		}

		return $where ? implode(' AND ', $where) : '';
	}

	public function get_wheres(){	// 以后放弃，目前统计在用
		return $this->get_conditions();
	}

	private function process_field_formats($data){
		if(is_array($data)){
			$format	= [];

			foreach($data as $field => $value){
				$format[]	= $this->field_types[$field] ?? '%s';
			}
		}else{
			$format	= $this->field_types[$data] ?? '%s';
		}

		return $format;
	}

	public function found_rows($found_rows=true){
		return $this->set_query_var('found_rows', (bool)$found_rows);
	}

	public function limit($limit){
		return $this->set_query_var('limit', (int)$limit);
	}

	public function offset($offset){
		return $this->set_query_var('offset', (int)$offset);
	}

	public function order_by($orderby=''){
		return $this->orderby($orderby);
	}

	public function orderby($orderby=''){
		return !is_null($orderby) ? $this->set_query_var('orderby', $orderby) : $this;
	}

	public function group_by($group_by=''){
		return $this->groupby($group_by);
	}

	public function groupby($groupby=''){
		return $groupby ? $this->set_query_var('groupby', $groupby) : $this;
	}

	public function having($having=''){
		return $having ? $this->set_query_var('having', $having) : $this;
	}

	public function order($order='DESC'){
		$order	= (strtoupper($order) == 'ASC') ? 'ASC':'DESC';

		return $this->set_query_var('order', $order);
	}

	public function search($search_term=''){
		return $search_term ? $this->set_query_var('search_term', $search_term) : $this;
	}

	public function convert_where($value, $column=null){
		if(is_array($value)){
			if(!isset($value['value'])){
				return [];
			}

			if(is_numeric($column) || is_null($column)){
				if(!isset($value['column'])){
					if(!empty($value['fragment'])){
						return ['compare'=>'fragment', 'fragment'=>' ( '.$value['fragment'].' ) '];
					}else{
						return [];
					}
				}
			}else{
				$value['column']	= $column;
			}

			$non_numeric_operators	= [
				'=',
				'!=',
				'LIKE',
				'NOT LIKE',
				'IN',
				'NOT IN',
				// 'RLIKE',
				// 'REGEXP',
				// 'NOT REGEXP',
			];

			$numeric_operators		= [
				'>',
				'>=',
				'<',
				'<=',
				// 'BETWEEN',
				// 'NOT BETWEEN',
			];

			if(isset($value['compare'])){
				if(!in_array($value['compare'], $non_numeric_operators, true) && !in_array($value['compare'], $numeric_operators, true)){
					$value['compare']	= '=';
				}
			}else{
				$value['compare']	= '=';
			}

			return $value;
		}else{
			if(is_null($value)){
				return [];
			}

			if(is_numeric($column) || is_null($column)){
				return ['compare'=>'fragment', 'fragment'=>' ( '.$value.' ) '];
			}else{
				return ['compare'=>'=', 'column'=>$column, 'value'=>$value];
			}
		}

		return [];
	}

	public function where(...$args){
		if(count($args) >= 3){
			if($args[1] !== null){
				$this->where[]	= ['column'=>$args[0], 'value'=>$args[1], 'compare'=>$args[2]];
			}
		}elseif(count($args) == 2){
			if($where = $this->convert_where($args[1], $args[0])){
				$this->where[]	= $where;
			}
		}elseif(count($args) == 1){
			if(wp_is_numeric_array($args[0])){
				$this->where_all($args[0]);
			}else{
				if($where = $this->convert_where($args[0])){
					$this->where[]	= $where;
				}
			}
		}

		return $this;
	}

	public function where_not($column, $value){
		return $this->where($column, $value, '!=');
	}

	public function where_lt($column, $value){
		return $this->where($column, $value, '<');
	}

	public function where_lte($column, $value){
		return $this->where($column, $value, '<=');
	}

	public function where_gt($column, $value){
		return $this->where($column, $value, '>');
	}

	public function where_gte($column, $value){
		return $this->where($column, $value, '>=');
	}

	public function where_in($column, $value){
		return $this->where($column, $value, 'IN');
	}

	public function where_not_in($column, $value){
		return $this->where($column, $value, 'NOT IN');
	}

	public function where_like($column, $value){
		return $this->where($column, $value, 'LIKE');
	}

	public function where_not_like($column, $value){
		return $this->where($column, $value, 'NOT LIKE');
	}

	public function where_any($any, $return='object'){
		$fragment	= '';

		if($any && is_array($any)){
			$where_any	= [];

			foreach($any as $column => $value){
				if($where = $this->convert_where($value, $column)){
					$where_any[]	= $where;
				}
			}

			if($where_any = $this->parse_where($where_any)){
				$fragment	= implode(' OR ', $where_any);
			}
		}

		if($return != 'object'){
			return $fragment ? ' ( '.$fragment.' ) ' : '';
		}

		return $this->where_fragment($fragment);
	}

	public function where_all($all, $return='object'){
		$fragment	= '';

		if($all && is_array($all)){
			$where_all	= [];

			foreach($all as $column => $value){
				if($where = $this->convert_where($value, $column)){
					$where_all[]	= $where;
				}
			}

			if($where_all = $this->parse_where($where_all)){
				$fragment	= implode(' AND ', $where_all);
			}
		}

		if($return != 'object'){
			return $fragment ? ' ( '.$fragment.' ) ' : '';
		}else{
			return $this->where_fragment($fragment);
		}
	}

	public function where_fragment($fragment){
		if($fragment){
			$this->where[] = ['compare'=>'fragment', 'fragment'=>' ( '.$fragment.' ) '];
		}

		return $this;
	}

	public function find_in_set($item, $list){
		if($list){
			$this->where[] = ['compare'=>'find_in_set', 'item'=>$item, 'list'=>$list];
		}

		return $this;
	}

	public function query_items($limit, $offset){ 
		$this->limit($limit)->offset($offset)->found_rows();

		if(is_null($this->get_query_var('orderby'))){
			$this->orderby(wpjam_get_data_parameter('orderby'));
		}

		if(is_null($this->get_query_var('order'))){
			$this->order(wpjam_get_data_parameter('order'));
		}

		if($this->searchable_fields && is_null($this->get_query_var('search_term'))){
			$this->search(wpjam_get_data_parameter('s'));
		}

		foreach($this->filterable_fields as $filter_key){
			$this->where($filter_key, wpjam_get_data_parameter($filter_key));
		}

		return ['items'=>$this->get_results(), 'total'=>$this->find_total($this->get_query_var('groupby'))];
	}
}

class WPJAM_DBTransaction{
	public static function beginTransaction(){
		return $GLOBALS['wpdb']->query("START TRANSACTION;");
	}

	public static function queryException(){
		$error = $GLOBALS['wpdb']->last_error;
		if(!empty($error)){
			throw new Exception($error);
		}
	}

	public static function commit(){
		self::queryException();
		return $GLOBALS['wpdb']->query("COMMIT;");
	}

	public static function rollBack(){
		return $GLOBALS['wpdb']->query("ROLLBACK;");
	}
}