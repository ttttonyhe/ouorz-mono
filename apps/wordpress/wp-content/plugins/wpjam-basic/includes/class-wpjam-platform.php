<?php
class WPJAM_Platform extends WPJAM_Register{
	public function verify(){
		return call_user_func($this->verify);
	}

	public static function get_sorted(){
		return wpjam_sort_items(self::get_registereds(), 'order', 'ASC');
	}

	public static function get_options($type='bit'){
		$objects	= [];

		foreach(self::get_sorted() as $key => $object){
			if(!empty($object->bit)){
				$object->key	= $key;

				$objects[$object->bit]	= $object;
			}
		}

		if($type == 'key' || $type == 'name'){
			return wp_list_pluck($objects, 'title', 'key');
		}elseif($type == 'bit'){
			return wp_list_pluck($objects, 'title');
		}else{
			return wp_list_pluck($objects, 'bit');
		}
	}

	public static function get_current($platforms=[], $type='bit'){
		foreach(self::get_sorted() as $name => $object){
			if($object->verify()){
				$return	= $type == 'bit' ? $object->bit : $name;

				if(($platforms && in_array($return, $platforms)) 
					|| empty($platforms))
				{
					return $return;
				}
			}	
		}

		return '';
	}

	public static function autoload(){
		self::register('weapp',		['bit'=>1,	'order'=>4,		'title'=>'小程序',	'verify'=>'is_weapp']);
		self::register('weixin',	['bit'=>2,	'order'=>4,		'title'=>'微信网页',	'verify'=>'is_weixin']);
		self::register('mobile',	['bit'=>4,	'order'=>8,		'title'=>'移动网页',	'verify'=>'wp_is_mobile']);
		self::register('web',		['bit'=>8,	'order'=>10,	'title'=>'网页',		'verify'=>'__return_true']);
		self::register('template',	['bit'=>8,	'order'=>10,	'title'=>'网页',		'verify'=>'__return_true']);
	}
}

class WPJAM_Path extends WPJAM_Register{
	private $types	= [];

	public function add_type($type, $item){
		$page_type	= $item['page_type'] ?? '';

		if($page_type 
			&& in_array($page_type, ['post_type', 'taxonomy'])
			&& empty($item[$page_type])
		){
			$item[$page_type]	= $this->name;
		}

		if(isset($item['group']) && is_array($item['group'])){
			if(isset($item['group']['key'], $item['group']['title'])){
				$group_key		= $item['group']['key'];
				$group_title	= $item['group']['title'];

				$item['group']	= $group_key;

				wpjam_register('path_group', $group_key, ['title'=>$group_title]);
			}else{
				unset($item['group']);
			}
		}

		$item['path_type']	= $type;

		$this->types[$type]	= $item;

		$this->args	= $this->args+$item;

		wpjam_add_current_item('platforms', $type, $type);
	}

	public function remove_type($type){
		unset($this->types[$type]);
	}

	public function get_type($type){
		return $this->types[$type] ?? [];
	}

	public function get_page_type_object($item){
		if(!empty($item['page_type'])){
			return wpjam_get_data_type_object($item['page_type']);
		}

		return null;
	}

	public function get_tabbar($type){
		$item	= $this->get_type($type);
		
		if($item && !empty($item['tabbar'])){
			$tabbar	= $item['tabbar'];

			if(!is_array($tabbar)){
				$tabbar	= ['text'=>$this->title];
			}

			return $tabbar;
		}

		return '';
	}

	public function get_raw_path($type){
		$item	= $this->get_type($type);

		return $item ? ($item['path'] ?? '') : '';
	}

	public function get_path($type, $args=[], $postfix='', $postfix_title=''){
		if($postfix){
			$_args	= [];

			foreach($this->get_fields() as $field_key => $path_field){
				$_args[$field_key]	= $args[$field_key.$postfix] ?? '';
			}

			$args	= $_args;
		}

		$item	= $this->get_type($type);

		if($item){
			$callback	= wpjam_array_pull($item, 'callback');

			$args	= array_filter($args, 'is_exists');
			$args	= wp_parse_args($args, $item);

			if($callback){
				if(is_callable($callback)){
					return call_user_func($callback, $args, $this->name) ?: '';
				}
			}else{
				$type_object	= $this->get_page_type_object($item);

				if($type_object){
					return $type_object->get_path($args);
				}
			}				
			
			if(isset($item['path'])){
				return $item['path'] ?: '';
			}
		}

		return new WP_Error('invalid_'.$postfix.'page_key', '无效的'.$postfix_title.'页面');
	}

	public function get_fields(){
		$fields	= [];

		foreach($this->types as $type => $item){
			$item_fields	= wpjam_array_pull($item, 'fields') ?: [];

			if($item_fields){
				if(is_callable($item_fields)){ 
					$item_fields	= call_user_func($item_fields, $item, $this->name);
				}
			}else{
				$type_object	= $this->get_page_type_object($item);

				if($type_object){
					$item_fields	= $type_object->get_fields($item);
				}
			}	

			if(is_array($item_fields)){
				$fields	= array_merge($fields, $item_fields);
			}
		}

		return $fields;
	}

	public function has($types, $operator='AND', $strict=false){
		$types	= (array)$types;

		foreach($types as $type){
			if($item = $this->get_type($type)){
				$has	= isset($item['path']) || isset($item['callback']);

				if($strict && $has && isset($item['path']) && $item['path'] === false){
					$has	= false;
				}
			}else{
				$has	= false;
			}

			if($operator == 'AND'){
				if(!$has){
					return false;
				}
			}elseif($operator == 'OR'){
				if($has){
					return true;
				}
			}
		}

		if($operator == 'AND'){
			return true;
		}elseif($operator == 'OR'){
			return false;
		}
	}

	public static function get_platforms(){
		return array_values(wpjam_get_current_items('platforms'));
	}

	public static function parse_item($item, $path_type, $backup=false){
		if($backup){
			$postfix	= '_backup';
			$default	= 'none';
			$title		= '备用';
		}else{
			$postfix	= '';
			$default	= '';
			$title		= '';
		}

		$page_key	= $item['page_key'.$postfix] ?? '';
		$page_key	= $page_key ?: $default;
		$parsed		= [];

		if($page_key == 'none'){
			if(!empty($item['video'])){
				$parsed['type']		= 'video';
				$parsed['video']	= $item['video'];
				$parsed['vid']		= wpjam_get_qqv_id($item['video']);
			}else{
				$parsed['type']		= 'none';
			}
		}elseif($page_key == 'external'){
			if(in_array($path_type, ['web', 'template'])){
				$parsed['type']		= 'external';
				$parsed['url']		= $item['url'];
			}
		}elseif($page_key == 'web_view'){
			if(in_array($path_type, ['web', 'template'])){
				$parsed['type']		= 'external';
				$parsed['url']		= $item['src'];
			}else{
				$parsed['type']		= 'web_view';
				$parsed['src']		= $item['src'];
			}
		}

		if(!$parsed && $page_key){
			$object	= self::get($page_key);

			if($object){
				$path	= $object->get_path($path_type, $item, $postfix, $title);

				if(!is_wp_error($path)){
					if(is_array($path)){
						$parsed	= $path;
					}else{
						$parsed['type']		= '';
						$parsed['page_key']	= $page_key;
						$parsed['path']		= $path;
					}
				}
			}
		}

		return $parsed;
	}

	public static function validate_item($item, $path_types, $backup=false){
		if($backup){
			$postfix	= '_backup';
			$default	= 'none';
			$title		= '备用';
		}else{
			$postfix	= '';
			$default	= '';
			$title		= '';
		}

		$page_key	= $item['page_key'.$postfix] ?: $default;

		if($page_key == 'none'){
			return true;
		}elseif($page_key == 'web_view'){
			if(!$backup){
				$path_types	= array_diff($path_types, ['web','template']);
			}
		}

		$object	= self::get($page_key);

		if($object){
			foreach($path_types as $path_type){
				$path	= $object->get_path($path_type, $item, $postfix, $title);

				if(is_wp_error($path)){
					return $path;
				}
			}
		}else{
			return new WP_Error('invalid_'.$postfix.'page_key', $title.'页面无效');
		}

		return true;
	}

	public static function parse_page_key_options(&$options, $object){
		$group_title	= '其他页面';
		$group_key		= 'others';

		if($object->tabbar){
			$group_title	= '菜单栏	/常用';
			$group_key		= 'tabbar';
		}elseif($object->group){
			$group_object	= wpjam_get_registered_object('path_group', $object->group);

			if($group_object){
				$group_title	= $group_object->title;
				$group_key		= $object->group;
			}
		}

		if(!isset($options[$group_key])){
			$options[$group_key]	= ['title'=>$group_title, 'options'=>[]];
		}

		$options[$group_key]['options'][$object->name]	= $object->title;
	}
	
	public static function get_path_fields($path_types, $for=''){
		if(empty($path_types)){
			return [];
		}

		$path_types	= (array)$path_types;

		$strict		= ($for == 'qrcode');

		$backup_required	= count($path_types) > 1 && !$strict;

		if($backup_required){
			$backup_fields	= ['page_key_backup'=>['title'=>'',	'type'=>'select',	'options'=>[],	'description'=>'跳转页面不生效时将启用备用页面']];
			$show_if_keys	= [];
		}

		$page_key_fields	= ['page_key'=>['title'=>'',	'type'=>'select',	'options'=>[]]];

		foreach(self::get_registereds() as $page_key => $object){
			if(!$object->has($path_types, 'OR', $strict)){
				continue;
			}

			self::parse_page_key_options($page_key_fields['page_key']['options'], $object);

			$path_fields	= $object->get_fields();

			foreach($path_fields as $field_key => $path_field){
				if(isset($path_field['show_if'])){
					$page_key_fields[$field_key]	= $path_field;
				}else{
					if(isset($page_key_fields[$field_key])){
						$page_key_fields[$field_key]['show_if']['value'][]	= $page_key;
					}else{
						$path_field['title']	= '';
						$path_field['show_if']	= ['key'=>'page_key','compare'=>'IN','value'=>[$page_key]];

						$page_key_fields[$field_key]	= $path_field;
					}
				}
			}

			if($backup_required){
				if($object->has($path_types, 'AND')){
					if(
						($page_key != 'module_page' && empty($path_fields)) 
						|| ($page_key == 'module_page' && $path_fields)
					){
						self::parse_page_key_options($backup_fields['page_key_backup']['options'], $object);
					}

					if($page_key == 'module_page' && $path_fields){
						foreach($path_fields as $field_key => $path_field){
							$path_field['show_if']	= ['key'=>'page_key_backup','value'=>$page_key];
							$backup_fields[$field_key.'_backup']	= $path_field;
						}
					}
				}else{
					if($page_key == 'web_view'){
						if(!$object->has(array_diff($path_types, ['web','template']), 'AND')){
							$show_if_keys[]	= $page_key;
						}
					}else{
						$show_if_keys[]	= $page_key;
					}
				}
			}
		}

		if($for == 'qrcode'){
			return ['page_key_set'=>['title'=>'页面',	'type'=>'fieldset',	'fields'=>$page_key_fields]];
		}else{
			$page_key_fields['page_key']['options']['tabbar']['options']['none']	= '只展示不跳转';

			$fields	= [];

			$fields['page_key_set']	= ['title'=>'页面',	'type'=>'fieldset',	'fields'=>$page_key_fields];

			if($backup_required){
				$show_if	= ['key'=>'page_key','compare'=>'IN','value'=>$show_if_keys];

				$backup_fields['page_key_backup']['options']['tabbar']['options']['none']	= '只展示不跳转';

				$fields['page_key_backup_set']	= ['title'=>'备用',	'type'=>'fieldset',	'fields'=>$backup_fields, 'show_if'=>$show_if];
			}

			return $fields;
		}
	}

	public static function get_tabbar_options($path_type){
		$options	= [];
	
		foreach(self::get_registereds() as $page_key => $object){
			$tabbar	= $object->get_tabbar($path_type);

			if($tabbar){
				$options[$page_key]	= $tabbar['text'];
			}
		}

		return $options;
	}

	public static function get_page_keys($path_type){
		$page_keys	= [];

		foreach(self::get_registereds() as $page_key => $object){
			$path	= $object->get_raw_path($path_type);

			if($path){
				$pos	= strrpos($path, '?');

				$page_keys[]	= [
					'page_key'	=> $page_key, 
					'page'		=> $pos ? substr($path, 0, $pos) : $path,
				];
			}
		}

		return $page_keys;
	}

	public static function get_item_link_tag($parsed, $text){
		if($parsed['type'] == 'none'){
			return $text;
		}elseif($parsed['type'] == 'external'){
			return '<a href_type="web_view" href="'.$parsed['url'].'">'.$text.'</a>';
		}elseif($parsed['type'] == 'web_view'){
			return '<a href_type="web_view" href="'.$parsed['src'].'">'.$text.'</a>';
		}elseif($parsed['type'] == 'mini_program'){
			return '<a href_type="mini_program" href="'.$parsed['path'].'" appid="'.$parsed['appid'].'">'.$text.'</a>';
		}elseif($parsed['type'] == 'contact'){
			return '<a href_type="contact" href="" tips="'.$parsed['tips'].'">'.$text.'</a>';
		}elseif($parsed['type'] == ''){
			return '<a href_type="path" page_key="'.$parsed['page_key'].'" href="'.$parsed['path'].'">'.$text.'</a>';
		}
	}

	public static function get_by(...$args){
		if(is_array($args[0])){
			$args	= $args[0];
		}else{
			$args	= [$args[0] => $args[1]];
		}

		$path_type	= wpjam_array_pull($args, 'path_type');
		$objects	= self::get_registereds($args);

		if($path_type){
			$objects	= array_filter($objects, function($object) use($path_type){
				return $object->has($path_type);
			});
		}
		
		return $objects;
	}

	public static function create($page_key, ...$args){
		$object	= self::get($page_key);
		$object	= $object ?: self::register($page_key, []);

		if(count($args) == 2){
			$args	= $args[1]+['path_type'=>$args[0]];
		}else{
			$args	= $args[0];
		}

		$args	= wp_is_numeric_array($args) ? $args : [$args];

		foreach($args as $item){
			$type	= wpjam_array_pull($item, 'path_type');

			$object->add_type($type, $item);
		}

		return $object;
	}

	public static function autoload(){
		self::create('home',		['path_type'=>'template',	'title'=>'首页',			'path'=>home_url()]);
		self::create('category',	['path_type'=>'template',	'title'=>'分类页',		'path'=>'',	'page_type'=>'taxonomy',	'taxonomy'=>'category']);
		self::create('post_tag',	['path_type'=>'template',	'title'=>'标签页',		'path'=>'',	'page_type'=>'taxonomy',	'taxonomy'=>'post_tag']);
		self::create('author',		['path_type'=>'template',	'title'=>'作者页',		'path'=>'',	'page_type'=>'author']);
		self::create('post',		['path_type'=>'template',	'title'=>'文章详情页',	'path'=>'',	'page_type'=>'post_type',	'post_type'=>'post']);
		self::create('external', 	['path_type'=>'template',	'title'=>'外部链接',		'path'=>'',	'fields'=>[
			'url'	=> ['title'=>'',	'type'=>'url',	'required'=>true,	'placeholder'=>'请输入外部链接地址，仅适用网页版。']
		]]);
	}
}

class WPJAM_Data_Type extends WPJAM_Register{
	public function __call($method, $args){
		$result	= $this->call_method($method, ...$args);
		
		if(in_array($method, ['query_value', 'parse_value', 'validate_value'])){
			return $result ?? $args[0];
		}elseif(in_array($method, ['get_field', 'get_fields'])){
			return $result ?: [];
		}

		return $result;
	}

	public function get_meta_type($args=[]){
		if($this->method_exists('get_meta_type')){
			return $this->call_method('get_meta_type', $args);
		}

		return $this->meta_type;
	}

	public function register_list_table_action($args){
		if(empty($args['builtin_class'])){
			$model	= $args['model'] ?? null;

			if($model && method_exists($model, 'get_actions')){
				$actions	= call_user_func([$model, 'get_actions']);
			}else{
				$actions	= $args['actions'] ?? [
					'add'		=> ['title'=>'新建',	'dismiss'=>true],
					'edit'		=> ['title'=>'编辑'],
					'delete'	=> ['title'=>'删除',	'direct'=>true, 'confirm'=>true,	'bulk'=>true],
				];
			}

			foreach($actions as $key => $action){
				wpjam_register_list_table_action($key, wp_parse_args($action, ['order'=>10.5]));
			}
		}

		$this->call_method('register_list_table_action', $args);
	}

	public static function parse_query_args(&$args){
		$data_type	= $args['data_type'];
		$query_args	= $args['query_args'] ?? [];
		$query_args	= $query_args ? wp_parse_args($query_args) : [];

		if(!empty($args[$data_type])){
			$query_args[$data_type]	= $args[$data_type];
		}

		$object	= self::get($data_type);

		if($object){
			$query_args	= $object->filter_query_args($query_args, $args);
		}

		return $query_args;
	}

	public static function ajax_query(){
		$data_type	= wpjam_get_parameter('data_type', ['method'=>'POST']);
		$object		= $data_type ? self::get($data_type) : null;

		if($object){
			$args	= wpjam_get_parameter('query_args', ['method'=>'POST']);
			$items	= $object->query_items($args) ?: [];
		}else{
			$items	= [];
		}

		wpjam_send_json(['items'=>$items]);
	}

	public static function autoload(){
		self::register('post_type',	['model'=>'WPJAM_Post_Type_Data_Type',	'meta_type'=>'post']);
		self::register('taxonomy',	['model'=>'WPJAM_Taxonomy_Data_Type',	'meta_type'=>'term']);
		self::register('author',	['model'=>'WPJAM_Author_Data_Type',		'meta_type'=>'user']);
		self::register('model',		['model'=>'WPJAM_Model_Data_Type']);
		self::register('video',		['model'=>'WPJAM_Video_Data_Type']);
	}
}

class WPJAM_Post_Type_Data_Type{
	public static function filter_query_args($query_args, $args){
		if(!empty($args['size'])){
			$query_args['thumbnal_size']	= $args['size'];
		}

		return $query_args;
	}

	public static function query_items($args){
		$query	= wpjam_query(wp_parse_args($args, [
			'posts_per_page'	=> 10,
			'post_status'		=> 'publish'
		]));

		return array_map(function($post){
			return ['label'=>$post->post_title, 'value'=>$post->ID];
		}, $query->posts);
	}

	public static function query_label($post_id){
		if($post_id && is_numeric($post_id)){
			return get_the_title($post_id) ?: (int)$post_id;
		}

		return '';
	}

	public static function validate_value($value, $args){
		return (is_numeric($value) && get_post($value)) ? (int)$value : null;
	}

	public static function parse_value($value, $args=[]){
		return wpjam_get_post($value, $args);
	}

	public static function parse_json_schema(){
		return ['type'=>'integer'];
	}

	public static function update_caches($ids){
		return WPJAM_Post::update_caches($ids);
	}

	public static function get_path($args){
		$post_type	= $args['post_type'];
		$post_id	= (int)wpjam_array_pull($args, $post_type.'_id');

		if($post_id){
			if($args['path_type'] == 'template'){
				return get_permalink($post_id);
			}else{
				if(strpos($args['path'], '%post_id%')){
					return str_replace('%post_id%', $post_id, $args['path']);
				}else{
					return $args['path'];
				}
			}
		}

		return new WP_Error('empty_'.$post_type.'_id', get_post_type_object($post_type)->label.'ID不能为空并且必须为数字');
	}

	public static function get_field($args){
		$title		= wpjam_array_pull($args, 'title');
		$post_type	= wpjam_array_pull($args, 'post_type');

		if(is_null($title)){
			$object	= ($post_type && is_string($post_type)) ? get_post_type_object($post_type) : null;
			$title	= $object ? $object->labels->singular_name : '';
		}

		return wp_parse_args($args, [
			'title'			=> $title,
			'type'			=> 'text',
			'class'			=> 'all-options',
			'data_type'		=> 'post_type',
			'post_type'		=> $post_type,
			'placeholder'	=> '请输入'.$title.'ID或者输入关键字筛选',
		]);
	}

	public static function get_fields($args){
		$post_type	= $args['post_type'];

		if(get_post_type_object($post_type)){
			return [$post_type.'_id' => self::get_field(['post_type'=>$post_type, 'required'=>true])];
		}else{
			return [];
		}
	}

	public static function register_list_table_action($args){
		$post_type	= $args['post_type'];

		foreach(WPJAM_Post_Option::get_by_post_type($post_type) as $object){
			$object->register_list_table_action();
		}
	}
}

class WPJAM_Taxonomy_Data_Type{
	public static function get_query_key($taxonomy){
		$query_keys	= ['category'=>'cat', 'post_tag'=>'tag_id'];

		return $query_keys[$taxonomy] ?? $taxonomy.'_id';
	}

	public static function filter_query_args($query_args, $args){
		if(!empty($args['creatable'])){
			$query_args['creatable']	= $args['creatable'];
		}

		return $query_args;
	}

	public static function query_items($args){
		$terms = get_terms(wp_parse_args($args, [
			'number'		=> 10,
			'hide_empty'	=> 0
		]));

		return $terms ? array_map(function($term){
			return ['label'=>$term->name, 'value'=>$term->term_id];
		}, $terms) : [];
	}

	public static function query_label($term_id, $args){
		if($term_id && is_numeric($term_id)){
			return get_term_field('name', $term_id, $args['taxonomy']) ?: (int)$term_id;
		}

		return '';
	}

	public static function validate_value($value, $args){
		if(!is_numeric($value)){
			$result	= term_exists($value, $args['taxonomy']);

			if($result){
				return is_array($result) ? $result['term_id'] : $result;
			}else{
				if(!empty($args['creatable'])){
					return WPJAM_Term::insert(['name'=>$value, 'taxonomy'=>$args['taxonomy']]);
				}else{
					return null;
				}
			}
		}else{
			return get_term($value, $args['taxonomy']) ? (int)$value : null;
		}
	}

	public static function parse_value($value, $args){
		return wpjam_get_term($value);
	}

	public static function parse_json_schema(){
		return ['type'=>'integer'];
	}

	public static function update_caches($ids){
		return WPJAM_Term::update_caches($ids);
	}

	public static function get_path($args){
		$taxonomy	= $args['taxonomy'];
		$query_key	= self::get_query_key($taxonomy);
		$term_id	= (int)wpjam_array_pull($args, $query_key);

		if($term_id){
			if($args['path_type'] == 'template'){
				return get_term_link($term_id, $taxonomy);
			}else{
				if(strpos($args['path'], '%term_id%')){
					return str_replace('%term_id%', $term_id, $args['path']);
				}else{
					return $args['path'];
				}
			}
		}

		return new WP_Error('empty_'.$query_key, get_taxonomy($taxonomy)->label.'ID不能为空并且必须为数字');
	}

	public static function get_field($args){
		$taxonomy	= wpjam_array_pull($args, 'taxonomy');
		$tax_obj	= ($taxonomy && is_string($taxonomy)) ? get_taxonomy($taxonomy) : null;

		if($tax_obj){
			$title	= $tax_obj->label;

			if($tax_obj->hierarchical
				&& (!is_admin()
					|| (is_admin() && wp_count_terms(['taxonomy'=>$taxonomy]) <= 30)
				)
			){
				$terms		= wpjam_get_terms(['taxonomy'=>$taxonomy, 'hide_empty'=>0]);
				$options	= $terms ? array_column(wpjam_list_flatten($terms), 'name', 'id') : [];

				if(isset($args['option_all'])){
					$option_all	= wpjam_array_pull($args, 'option_all');

					if($option_all !== false){
						$option_all	= $option_all === true ? '所有'.$title :  $option_all;
						$options	= [''=>$option_all]+$options;
					}
				}

				if(isset($args['type']) && $args['type'] == 'mu-text'){
					$args['item_type']	= 'select';
				}

				return wp_parse_args($args, [
					'title'		=> $title,
					'type'		=> 'select',
					'options'	=> $options,
				]);
			}else{
				return wp_parse_args($args, [
					'title'			=> $title,
					'type'			=> 'text',
					'class'			=> 'all-options',
					'data_type'		=> 'taxonomy',
					'taxonomy'		=> $taxonomy,
					'placeholder'	=> '请输入'.$title.'ID或者输入关键字筛选',
				]);
			}
		}

		return [];
	}

	public static function get_fields($args){
		$taxonomy	= $args['taxonomy'];

		if(get_taxonomy($taxonomy)){
			$query_key	= self::get_query_key($taxonomy);

			return [$query_key => self::get_field(['taxonomy'=>$taxonomy, 'required'=>true])];
		}else{
			return [];
		}
	}

	public static function register_list_table_action($args){
		$taxonomy	= $args['taxonomy'];

		foreach(WPJAM_Term_Option::get_by_taxonomy($taxonomy) as $object){
			$object->register_list_table_action();
		}
	}
}

class WPJAM_Author_Data_Type{
	public static function get_authors($args=[], $return='users'){
		if(version_compare($GLOBALS['wp_version'], '5.9', '<')){
			$args['who']		= 'authors';
		}else{
			$args['capability']	= ['edit_posts'];
		}

		return $return == 'args' ? $args : get_users($args);
	}

	public static function get_path($args){
		$author	= (int)wpjam_array_pull($args, 'author');

		if($author){
			if($args['path_type'] == 'template'){
				return get_author_posts_url($author);
			}else{
				if(strpos($args['path'], '%author%')){
					return str_replace('%author%', $author, $args['path']);
				}else{
					return $args['path'];
				}
			}	
		}

		return new WP_Error('empty_author', '作者ID不能为空并且必须为数字。');
	}

	public static function get_fields(){
		return ['author' => ['title'=>'',	'type'=>'select',	'options'=>wp_list_pluck(wpjam_get_authors(), 'display_name', 'ID')]];
	}
}

class WPJAM_Video_Data_Type{
	public static function get_video_mp4($id_or_url){
		if(filter_var($id_or_url, FILTER_VALIDATE_URL)){ 
			if(preg_match('#http://www.miaopai.com/show/(.*?).htm#i',$id_or_url, $matches)){
				return 'http://gslb.miaopai.com/stream/'.esc_attr($matches[1]).'.mp4';
			}elseif(preg_match('#https://v.qq.com/x/page/(.*?).html#i',$id_or_url, $matches)){
				return self::get_qqv_mp4($matches[1]);
			}elseif(preg_match('#https://v.qq.com/x/cover/.*/(.*?).html#i',$id_or_url, $matches)){
				return self::get_qqv_mp4($matches[1]);
			}else{
				return wpjam_zh_urlencode($id_or_url);
			}
		}else{
			return self::get_qqv_mp4($id_or_url);
		}
	}

	public static function get_qqv_id($id_or_url){
		if(filter_var($id_or_url, FILTER_VALIDATE_URL)){
			foreach([
				'#https://v.qq.com/x/page/(.*?).html#i',
				'#https://v.qq.com/x/cover/.*/(.*?).html#i'
			] as $pattern){
				if(preg_match($pattern,$id_or_url, $matches)){
					return $matches[1];
				}
			}

			return '';
		}else{
			return $id_or_url;
		}
	}

	public static function get_qqv_mp4($vid){
		if(strlen($vid) > 20){
			return new WP_Error('invalid_qqv_vid', '非法的腾讯视频 ID');
		}

		$mp4 = wp_cache_get($vid, 'qqv_mp4');

		if($mp4 === false){
			$response	= wpjam_remote_request(
				'http://vv.video.qq.com/getinfo?otype=json&platform=11001&vid='.$vid,
				['timeout'=>4,	'json_decode_required'=>false]
			);

			if(is_wp_error($response)){
				return $response;
			}

			$response	= trim(substr($response, strpos($response, '{')),';');
			$response	= wpjam_json_decode($response);

			if(is_wp_error($response)){
				return $response;
			}

			if(empty($response['vl'])){
				return new WP_Error('illegal_qqv', '该腾讯视频不存在或者为收费视频！');
			}

			$u		= $response['vl']['vi'][0];
			$p0		= $u['ul']['ui'][0]['url'];
			$p1		= $u['fn'];
			$p2		= $u['fvkey'];
			$mp4	= $p0.$p1.'?vkey='.$p2;

			wp_cache_set($vid, $mp4, 'qqv_mp4', HOUR_IN_SECONDS*6);
		}

		return $mp4;
	}

	public static function parse_value($value, $args){
		return self::get_video_mp4($value);
	}
}

class WPJAM_Model_Data_Type{
	public static function filter_query_args($query_args, $args){
		$model	= $query_args['model'] ?? null;

		if(!$model || !class_exists($model)){
			wp_die(' model 未定义');
		}

		$query_args	= wp_parse_args($query_args, ['label_key'=>'title', 'id_key'=>'id']);

		return $query_args;
	}

	public static function query_items($args){
		$label_key	= wpjam_array_pull($args, 'label_key');
		$id_key		= wpjam_array_pull($args, 'id_key');
		$model		= wpjam_array_pull($args, 'model');
		$args		= wp_parse_args($args, ['number'=>10]);
		$query		= call_user_func([$model, 'Query'], $args);
		$items		= $query->items;

		return $items ? array_map(function($item)use($label_key, $id_key){ 
			return ['label'=>$item[$label_key], 'value'=>$item[$id_key]]; 
		}, $items) : [];
	}

	public static function query_label($id, $args){
		$model	= wpjam_array_pull($args, 'model');

		if($id && $model && class_exists($model)){
			if($data = call_user_func([$model, 'get'], $id)){
				$label_key	= $args['label_key'];

				return $data[$label_key] ?: $id;
			}
		}

		return '';
	}

	public static function validate_value($value, $args){
		$model	= wpjam_array_pull($args, 'model');

		if($value && $model && class_exists($model)){
			if($data = call_user_func([$model, 'get'], $value)){
				return $value;
			}
		}

		return null;
	}

	public static function get_meta_type($args=[]){
		$model	= $query_args['model'] ?? null;

		if($model && (method_exists($model, 'get_meta_type') || method_exists($model, '__callStatic'))){
			return call_user_func([$model, 'get_meta_type']);
		}
	}
}