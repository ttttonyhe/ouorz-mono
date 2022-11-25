<?php
class WPJAM_Term{
	private $id;
	private $level	= null;

	private function __construct($id){
		$this->id	= (int)$id;
	}

	public function __get($key){
		if(in_array($key, ['id', 'term_id'])){
			return $this->id;
		}elseif($key == 'term'){
			return get_term($this->id);
		}elseif($key == 'thumbnail'){
			$supports	= $this->get_setting('supports') ?: [];

			if(in_array('thumbnail', $supports)){
				return get_term_meta($this->id, 'thumbnail', true);
			}else{
				return '';
			}
		}else{
			return $this->term->$key;
		}
	}

	public function __isset($key){
		return $this->$key !== null;
	}

	public function save($data){
		return self::update($this->id, $data);
	}

	public function get_setting($key, $default=null){
		return wpjam_get_taxonomy_setting($this->taxonomy, $key, $default);
	}

	public function get_thumbnail_url($size='full', $crop=1){
		$thumbnail	= $this->thumbnail ?: apply_filters('wpjam_term_thumbnail_url', '', $this->term);

		if($thumbnail){
			$size	= $size ?: $this->get_setting('thumbnail_size') ?: 'thumbnail';

			return wpjam_get_thumbnail($thumbnail, $size, $crop);
		}

		return '';
	}

	public function get_ancestors(){
		if(is_taxonomy_hierarchical($this->taxonomy)){
			return get_ancestors($this->id, $this->taxonomy, 'taxonomy');
		}else{
			return [];
		}
	}

	public function get_level(){
		if(is_null($this->level)){
			$this->level	= $this->parent ? count($this->get_ancestors()) : 0;
		}

		return $this->level;
	}

	private function get_children($children_terms=null, $max_depth=-1, $depth=0){
		$children	= [];

		if($children_terms && isset($children_terms[$this->id]) && ($max_depth == 0 || $max_depth > $depth+1)){
			foreach($children_terms[$this->id] as $child){
				$children[]	= self::get_instance($child)->parse_for_json($children_terms, $max_depth, $depth+1);
			}
		}

		return $children;
	}

	public function parse_for_json($children_terms=null, $max_depth=-1, $depth=0){
		$json	= [];

		$json['id']				= $this->id;
		$json['taxonomy']		= $this->taxonomy;
		$json['name']			= html_entity_decode($this->name);
		$json['count']			= (int)$this->count;
		$json['description']	= $this->description;

		$tax_obj	= get_taxonomy($this->taxonomy);

		if($tax_obj->public || $tax_obj->publicly_queryable || $tax_obj->query_var){
			$json['slug']	= $this->slug;
		}

		if($tax_obj->hierarchical){
			$json['parent']	= $this->parent;

			if($max_depth != -1){
				$json['children']	= $this->get_children($children_terms, $max_depth, $depth);
			}
		}

		foreach(WPJAM_Term_Option::get_by_taxonomy($this->taxonomy) as $to_obj){
			$json	= array_merge($json, $to_obj->prepare($this->id));
		}

		return apply_filters('wpjam_term_json', $json, $this->id);
	}

	private static $instances	= [];

	public static function get_instance($term=null, $taxonomy=''){
		$term	= self::validate($term, $taxonomy);

		if(is_wp_error($term)){
			return null;
		}

		$id	= $term->term_id;

		if(!isset($instances[$id])){
			$instances[$id]	= new self($id);
		}

		return $instances[$id];
	}

	public static function get($term){
		$data	= self::get_term($term, '', ARRAY_A);

		if($data && !is_wp_error($data)){
			$data['id']	= $data['term_id'];
		}

		return $data;
	}

	public static function insert($data){
		$taxonomy	= wpjam_array_pull($data, 'taxonomy');

		if(!$taxonomy){
			return new WP_Error('empty_taxonomy', '分类模式不能为空');
		}

		$name	= wpjam_array_pull($data, 'name');
		$args	= wp_array_slice_assoc($data, ['parent', 'slug', 'description', 'alias_of']);
		$term	= wp_insert_term(wp_slash($name), $taxonomy, wp_slash($args));

		if(is_wp_error($term)){
			return $term;
		}

		$term_id	= $term['term_id'];

		$meta_input	= wpjam_array_pull($data, 'meta_input');

		if(is_array($meta_input)){
			wpjam_update_metadata('term', $term_id, $meta_input);
		}

		return $term_id;
	}

	public static function update($term_id, $data){
		$taxonomy	= wpjam_array_pull($data, 'taxonomy');

		if(!$taxonomy){
			$object	= self::get_instance($term_id);

			if(is_wp_error($object)){
				return $object;
			}

			$taxonomy	= $object->taxonomy;
		}

		if($args = wp_array_slice_assoc($data, ['name', 'parent', 'slug', 'description', 'alias_of'])){
			$term	= wp_update_term($term_id, $taxonomy, wp_slash($args));

			if(is_wp_error($term)){
				return $term;
			}
		}

		if($meta_input = wpjam_array_pull($data, 'meta_input')){
			if(is_array($meta_input) && !wp_is_numeric_array($meta_input)){
				wpjam_update_metadata('term', $term_id, $meta_input);
			}
		}

		return true;
	}

	public static function delete($term_id){
		$term	= get_term($term_id);

		if(is_wp_error($term) || empty($term)){
			return $term;
		}

		return wp_delete_term($term_id, $term->taxonomy);
	}

	public static function move($term_id, $data){
		$term	= get_term($term_id);

		$term_ids	= get_terms([
			'parent'	=> $term->parent,
			'taxonomy'	=> $term->taxonomy,
			'orderby'	=> 'name',
			'hide_empty'=> false,
			'fields'	=> 'ids'
		]);

		if(empty($term_ids) || !in_array($term_id, $term_ids)){
			return new WP_Error('key_not_exists', $term_id.'的值不存在');
		}

		$terms	= array_map(function($term_id){
			return ['id'=>$term_id, 'order'=>get_term_meta($term_id, 'order', true) ?: 0];
		}, $term_ids);

		$terms	= wp_list_sort($terms, 'order', 'DESC');
		$terms	= wp_list_pluck($terms, 'order', 'id');

		$next	= $data['next'] ?? false;
		$prev	= $data['prev'] ?? false;

		if(!$next && !$prev){
			return new WP_Error('invalid_move', '无效移动位置');
		}

		unset($terms[$term_id]);

		if($next){
			if(!isset($terms[$next])){
				return new WP_Error('key_not_exists', $next.'的值不存在');
			}

			$offset	= array_search($next, array_keys($terms));

			if($offset){
				$terms	= array_slice($terms, 0, $offset, true) +  [$term_id => 0] + array_slice($terms, $offset, null, true);
			}else{
				$terms	= [$term_id => 0] + $terms;
			}
		}else{
			if(!isset($terms[$prev])){
				return new WP_Error('key_not_exists', $prev.'的值不存在');
			}

			$offset	= array_search($prev, array_keys($terms));
			$offset ++;

			if($offset){
				$terms	= array_slice($terms, 0, $offset, true) +  [$term_id => 0] + array_slice($terms, $offset, null, true);
			}else{
				$terms	= [$term_id => 0] + $terms;
			}
		}

		$count	= count($terms);
		foreach ($terms as $term_id => $order) {
			if($order != $count){
				update_term_meta($term_id, 'order', $count);
			}

			$count--;
		}

		return true;
	}

	public static function get_meta($term_id, ...$args){
		// _deprecated_function(__METHOD__, 'WPJAM Basic 6.0', 'wpjam_get_metadata');
		return wpjam_get_metadata('term', $term_id, ...$args);
	}

	public static function update_meta($term_id, ...$args){
		// _deprecated_function(__METHOD__, 'WPJAM Basic 6.0', 'wpjam_update_metadata');
		return wpjam_update_metadata('term', $term_id, ...$args);
	}

	public static function update_metas($term_id, $data, $meta_keys=[]){
		// _deprecated_function(__METHOD__, 'WPJAM Basic 6.0', 'wpjam_update_metadata');
		return self::update_meta($term_id, $data, $meta_keys);
	}

	public static function value_callback($meta_key, $term_id){
		return wpjam_get_metadata('term', $term_id, $meta_key);
	}

	public static function get_by_ids($term_ids){
		return self::update_caches($term_ids);
	}

	public static function update_caches($term_ids){
		if($term_ids){
			$term_ids 	= array_filter($term_ids);
			$term_ids 	= array_unique($term_ids);
		}

		if(empty($term_ids)) {
			return [];
		}

		_prime_term_caches($term_ids, false);

		$tids	= [];

		$cache_values	= wp_cache_get_multiple($term_ids, 'terms');

		foreach($term_ids as $term_id){
			if(empty($cache_values[$term_id])){
				wp_cache_add($term_id, false, 'terms', 10);	// 防止大量 SQL 查询。
			}else{
				$tids[]	= $term_id;
			}
		}

		$lazyloader	= wp_metadata_lazyloader();
		$lazyloader->queue_objects('term', $tids);

		return $cache_values;
	}

	public static function get_term($term, $taxonomy='', $output=OBJECT, $filter='raw'){
		if($term && is_numeric($term)){
			$found	= false;
			$cache	= wp_cache_get($term, 'terms', false, $found);

			if($found){
				if(is_wp_error($cache)){
					return $cache;
				}elseif(!$cache){
					return null;
				}
			}else{
				$_term	= WP_Term::get_instance($term, $taxonomy);

				if(is_wp_error($_term)){
					return $_term;
				}elseif(!$_term){	// 不存在情况下的缓存优化，防止重复 SQL 查询。
					wp_cache_add($term, false, 'terms', 10);
					return null;
				}
			}
		}

		$term	= $term ?: get_queried_object();

		return get_term($term, $taxonomy, $output, $filter);
	}

	public static function validate($term_id, $taxonomy=''){
		$term	= self::get_term($term_id);

		if(!$term || !($term instanceof WP_Term)){
			return new WP_Error('term_not_exists', '分类不存在');
		}

		if(!taxonomy_exists($term->taxonomy)){
			return new WP_Error('taxonomy_not_exists', '自定义分类不存在');
		}

		if($taxonomy && $taxonomy != 'any' && $taxonomy != $term->taxonomy){
			return new WP_Error('invalid_taxonomy', '无效的自定义分类');
		}

		return $term;
	}

	public static function get_terms($args, $max_depth=null){
		if(is_string($args) || wp_is_numeric_array($args)){
			$term_ids	= wp_parse_id_list($args);

			if(empty($term_ids)){
				return [];
			}

			$args		= ['orderby'=>'include', 'include'=>$term_ids];
			$max_depth	= $max_depth ?? -1;
		}
	
		if(is_null($max_depth)){
			$taxonomy	= $args['taxonomy'] ?? '';
			$tax_obj	= ($taxonomy && is_string($taxonomy)) ? get_taxonomy($taxonomy) : null;

			if(!$tax_obj){
				return [];
			}

			if($tax_obj->hierarchical){
				$max_depth	= wpjam_get_taxonomy_setting($taxonomy, 'levels', 0);
			}else{
				$max_depth	= -1;
			}
		}

		if($max_depth != -1){
			if(isset($args['child_of'])){
				$parent	= $args['child_of'];
			}else{
				$parent	= wpjam_array_pull($args, 'parent');

				if($parent){
					$args['child_of']	= $parent;
				}
			}
		}

		$args	= wp_parse_args($args, ['hide_empty'=>false]);
		$terms	= get_terms($args) ?: [];

		if(is_wp_error($terms) || empty($terms)){
			return $terms;
		}

		$children	= [];

		if($max_depth != -1){
			$top_level	= [];

			if($parent){
				$top_level[] = get_term($parent);
			}

			foreach($terms as $term){
				if($term->parent == 0){
					$top_level[] = $term;
				}elseif($max_depth != 1){
					$children[$term->parent][] = $term;
				}
			}

			$terms	= $top_level;
		}

		foreach($terms as &$term){
			$term	= self::get_instance($term)->parse_for_json($children, $max_depth, 0);
		}

		return $terms;
	}
}

class WPJAM_Taxonomy extends WPJAM_Register{
	private $_fields;

	public function parse_args(){
		if(!doing_filter('register_taxonomy_args')){
			$this->args = wp_parse_args($this->args, [
				'rewrite'			=> true,
				'show_ui'			=> true,
				'show_in_nav_menus'	=> false,
				'show_admin_column'	=> true,
				'hierarchical'		=> true,
				'by_wpjam'			=> true,
			]);

			if(is_admin() && $this->args['show_ui']){
				add_filter('taxonomy_labels_'.$this->name,	[$this, 'filter_labels']);
			}

			add_action('registered_taxonomy_'.$this->name,	[$this, 'registered_callback'], 10, 3);
		}

		if(empty($this->args['supports'])){
			$this->args['supports']	= ['slug', 'description', 'parent'];
		}

		if(empty($this->args['plural'])){
			if($this->name == 'category'){
				$this->args['plural']	= 'categories';
			}else{
				$this->args['plural']	= $this->name.'s';
			}
		}
	}

	public function to_array(){
		$this->filter_args();

		if(doing_filter('register_taxonomy_args')){
			if($this->permastruct){
				$this->permastruct	= str_replace('%term_id%', '%'.$this->name.'_id%', $this->permastruct);

				if(strpos($this->permastruct, '%'.$this->name.'_id%')){
					$this->supports		= array_diff($this->supports, ['slug']);
					$this->query_var	= $this->query_var ?? false;
				}

				if(!$this->rewrite){
					$this->rewrite	= true;
				}
			}

			if($this->levels == 1){
				$this->supports	= array_diff($this->supports, ['parent']);
			}else{
				$this->supports	= array_merge($this->supports, ['parent']);
			}

			if($this->rewrite && $this->by_wpjam){
				$this->rewrite	= is_array($this->rewrite) ? $this->rewrite : [];
				$this->rewrite	= wp_parse_args($this->rewrite, ['with_front'=>false, 'feed'=>false, 'hierarchical'=>false]);
			}
		}

		return $this->args;
	}

	public function get_keys(){
		return ['permastruct', 'levels', 'sortable', 'filterable', 'supports', 'thumbnail_size', 'thumbnail_type', 'plural'];
	}

	public function get_arg($key, $default=null){
		if(in_array($key, $this->get_keys()) && isset($this->$key)){
			return $this->$key;
		}

		return get_taxonomy($this->name)->$key ?? $default;
	}

	public function update_arg(...$args){
		if(is_array($args[0])){
			foreach($args[0] as $key => $value){
				$this->update_arg($key, $value);
			}
		}else{
			if(in_array($args[0], $this->get_keys())){
				$this->{$args[0]}	= $args[1];
			}
		}

		return true;
	}

	public function add_field($key, $field){
		$this->_fields	= array_merge($this->get_fields(), [$key => $field]);
	}

	public function remove_field($key){
		$this->_fields	= wpjam_array_except($this->get_fields(), $key);
	}

	public function get_fields(){
		if(is_null($this->_fields)){
			$fields	= [];

			if(in_array('thumbnail', $this->supports)){
				$fields['thumbnail']	= ['title'=>'缩略图'];

				if($this->thumbnail_type == 'image'){
					$fields['thumbnail']['type']		= 'image';
				}else{
					$fields['thumbnail']['type']		= 'img';
					$fields['thumbnail']['item_type']	= 'url';
				}

				if($this->thumbnail_size){
					$fields['thumbnail']['size']		= $this->thumbnail_size;
					$fields['thumbnail']['description']	= '尺寸：'.$this->thumbnail_size;
				}else{
					$fields['thumbnail']['size']		= 'thumbnail';
				}
			}

			$this->_fields	= $fields;
		}

		return $this->_fields;
	}

	public function registered_callback($taxonomy, $object_type, $args){
		if($this->name == $taxonomy){
			// print_r($this->name."\n");

			if($this->permastruct){
				if(strpos($this->permastruct, '%'.$taxonomy.'_id%')){
					wpjam_set_permastruct($taxonomy, $this->permastruct);

					add_rewrite_tag('%'.$taxonomy.'_id%', '([^/]+)', 'taxonomy='.$taxonomy.'&term_id=');

					remove_rewrite_tag('%'.$taxonomy.'%');
				}elseif(strpos($this->permastruct, '%'.$args['rewrite']['slug'].'%')){
					wpjam_set_permastruct($taxonomy, $this->permastruct);
				}
			}

			if($this->registered_callback && is_callable($this->registered_callback)){
				call_user_func($this->registered_callback, $taxonomy, $object_type, $args);
			}
		}
	}

	public function filter_labels($labels){
		$_labels	= $this->labels ?? [];

		$labels		= (array)$labels;
		$name		= $labels['name'];

		if($this->hierarchical){
			$search		= ['目录', '分类', 'categories', 'Categories', 'Category'];
			$replace	= ['', $name, $name, $name.'s', ucfirst($name).'s', ucfirst($name)];
		}else{
			$search		= ['标签', 'Tag', 'tag'];
			$replace	= [$name, ucfirst($name), $name];
		}

		foreach($labels as $key => &$label){
			if($label && empty($_labels[$key]) && $label != $name){
				$label	= str_replace($search, $replace, $label);
			}
		}

		return $labels;
	}

	public static function autoload(){
		add_filter('pre_term_link',	[self::class, 'filter_link'], 1, 2);

		foreach(self::get_registereds() as $taxonomy => $object){
			if(!get_taxonomy($taxonomy)){
				register_taxonomy($taxonomy, $object->object_type, $object->to_array());
			}
		}
	}

	public static function filter_register_args($args, $taxonomy, $object_type){
		if(did_action('init')){
			$object	= self::get($taxonomy);

			if($object){
				foreach(wp_array_slice_assoc($args, $object->get_keys()) as $key => $value){
					$object->$key	= $args[$key];
				}
			}else{
				$object	= self::register($taxonomy, array_merge($args, ['object_type'=>$object_type]));

				add_action('registered_taxonomy_'.$taxonomy, [$object, 'registered_callback'], 10, 3);
			}

			return $object->to_array();
		}

		return $args;
	}

	public static function on_registered($taxonomy, $object_type, $args){
		if(did_action('init')){
			(self::get($taxonomy))->registered_callback($taxonomy, $object_type, $args);
		}
	}

	public static function filter_link($term_link, $term){
		if(array_search('%'.$term->taxonomy.'_id%', $GLOBALS['wp_rewrite']->rewritecode, true)){
			$term_link	= str_replace('%'.$term->taxonomy.'_id%', $term->term_id, $term_link);
		}

		return $term_link;
	}

	public static function create($name, ...$args){
		if(count($args) == 2){
			$args	= array_merge($args[1], ['object_type'=>$args[0]]);
		}else{
			$args	= $args[0];
		}

		$object	= self::register($name, $args);

		if(did_action('init')){
			register_taxonomy($name, $object->object_type, $object->to_array());
		}

		return $object;
	}
}