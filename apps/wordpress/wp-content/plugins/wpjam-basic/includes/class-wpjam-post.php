<?php
class WPJAM_Post{
	protected $id;
	protected $viewd	= false;

	protected function __construct($id){
		$this->id	= (int)$id;
	}

	public function __get($key){
		if(in_array($key, ['id', 'post_id'])){
			return $this->id;
		}elseif(in_array($key, ['post', 'data'])){
			return get_post($this->id);
		}elseif($key == 'views'){
			return (int)get_post_meta($this->id, 'views', true);
		}elseif($key == 'thumbnail'){
			if($this->supports('thumbnail')){
				return get_the_post_thumbnail_url($this->id, 'full');
			}else{
				return '';
			}
		}elseif($key == 'images'){
			if($this->supports('images')){
				return get_post_meta($this->id, 'images', true) ?: [];
			}else{
				return [];
			}
		}else{
			$post	= $this->post;

			if(isset($post->$key)){
				return $post->$key;
			}else{
				return $post->{'post_'.$key} ?? null;
			}
		}
	}

	public function __isset($key){
		return $this->$key !== null;
	}

	public function __set($key, $value){
		if($key == 'views'){
			update_post_meta($this->id, 'views', $value);
		}
	}

	public function __call($method, $args){
		$post_type	= $this->post_type;

		if($method == 'get_taxonomies'){
			return get_object_taxonomies($post_type, ...$args);
		}elseif($method == 'supports'){
			return post_type_supports($post_type, ...$args);
		}elseif($method == 'is_type_viewable'){
			return is_post_type_viewable($post_type);
		}elseif($method == 'is_singular'){
			return is_singular($post_type);
		}elseif($method == 'get_type_setting'){
			return wpjam_get_post_type_setting($post_type, ...$args);
		}elseif($method == 'get_meta_options'){
			return WPJAM_Post_Option::get_by_post_type($post_type);
		}
	}

	public function save($data){
		$data['ID'] = $this->id;

		return wp_update_post(wp_slash($data), true, true);
	}

	public function filter_content($content){
		return str_replace(']]>', ']]&gt;', apply_filters('the_content', $content));
	}

	public function get_content($raw=false){
		$content	= get_the_content('', false, $this->post);

		return $raw ? $content : $this->filter_content($content);
	}

	public function get_excerpt($length=0, $more=null){
		if($this->excerpt){
			return wp_strip_all_tags($this->excerpt, true);
		}

		$excerpt	= $this->get_content(true);
		$excerpt	= strip_shortcodes($excerpt);
		$excerpt	= excerpt_remove_blocks($excerpt);
		$excerpt	= wp_strip_all_tags($excerpt, true);

		$length	= $length ?: apply_filters('excerpt_length', 200);
		$more	= $more ?? apply_filters('excerpt_more', ' &hellip;');

		return mb_strimwidth($excerpt, 0, $length, '', 'utf-8').$more;
	}

	public function get_thumbnail_url($size='thumbnail', $crop=1){
		if($this->thumbnail){
			$thumbnail	= $this->thumbnail;
		}elseif($this->images){
			$thumbnail	= $this->images[0];
		}else{
			$thumbnail	= apply_filters('wpjam_post_thumbnail_url', '', $this->post);
		}

		if($thumbnail){
			$size	= $size ?: ($this->get_type_setting('thumbnail_size') ?: 'thumbnail');

			return wpjam_get_thumbnail($thumbnail, $size, $crop);
		}

		return '';
	}

	public function get_images($large_size='', $thumbnail_size='', $full_size=true){
		$images	= [];

		if($this->images){
			$sizes	= [];

			$images_sizes	= $this->get_type_setting('images_sizes');

			if($large_size !== false){
				if($large_size){
					$sizes['large']	= $large_size;
				}else{
					if($images_sizes){
						$sizes['large']	= $images_sizes[0];
					}else{
						$sizes['large']	= $this->get_type_setting('large_size') ?: 'large';
					}
				}
			}

			if($thumbnail_size !== false){
				if($thumbnail_size){
					$sizes['thumbnail']	= $thumbnail_size;
				}else{
					if($images_sizes){
						$sizes['thumbnail']	= $images_sizes[1];
					}else{
						$sizes['thumbnail']	= $this->get_type_setting('thumbnail_size') ?: 'thumbnail';
					}
				}
			}

			foreach($this->images as $image){
				$image_arr = [];

				foreach($sizes as $name => $value){
					$image_arr[$name]	= wpjam_get_thumbnail($image, $value);
				}

				if($full_size){
					$image_arr['full']	= wpjam_get_thumbnail($image);
				}

				$images[]	= count($sizes) == 1 ? current($image_arr) : $image_arr;
			}
		}

		return $images;
	}

	public function get_first_image_url($size='full'){
		if($content	= $this->content){
			if(preg_match('/class=[\'"].*?wp-image-([\d]*)[\'"]/i', $content, $matches)){
				return wp_get_attachment_image_url($matches[1], $size);
			}

			if(preg_match('/<img.*?src=[\'"](.*?)[\'"].*?>/i', $content, $matches)){
				return wpjam_get_thumbnail($matches[1], $size);
			}
		}

		return '';
	}

	public function get_related_query($args=[]){
		$post_type	= [$this->post_type];
		$tt_ids		= [];

		foreach($this->get_taxonomies('objects') as $taxonomy => $tax_obj){
			if($taxonomy != 'post_format'){
				$terms	= get_the_terms($this->id, $taxonomy);

				if($terms){
					$post_type	= array_merge($post_type, $tax_obj->object_type);
					$tt_ids		= array_merge($tt_ids, array_column($terms, 'term_taxonomy_id'));
				}
			}
		}

		if(!$tt_ids){
			return false;
		}

		$query_vars	= wpjam_parse_query_vars([
			'related_query'		=> true,
			'cache_it'			=> 'query_vars',
			'post_status'		=> 'publish',
			'post__not_in'		=> [$this->id],
			'post_type'			=> array_unique($post_type),
			'term_taxonomy_ids'	=> array_unique(array_filter($tt_ids)),
		], $args);

		return wpjam_query($query_vars);
	}

	public function view($addon=1){
		if(!$this->viewd){	// 确保只加一次
			$this->viewd	= true;
			$this->views	= $this->views + $addon;
		}
	}

	public function parse_for_json($args=[]){
		$args	= wp_parse_args($args, [
			'list_query'		=> false,
			'content_required'	=> false,
			'raw_content'		=> false,
			'sticky_posts'		=> []
		]);

		$GLOBALS['post']	= $this->post;

		setup_postdata($this->post);

		$json	= [];

		$json['id']		= $this->id;
		$json['type']	= $json['post_type']	= $this->post_type;
		$json['status']	= $this->status;
		$json['title']	= $this->supports('title') ? html_entity_decode(get_the_title($this->post)) : '';

		if($this->supports('excerpt')){
			$json['excerpt']	= html_entity_decode(get_the_excerpt($this->post));
		}

		if($this->password){
			$json['password_protected']	= true;
			$json['password_required']	= post_password_required($this->post);
		}

		$json['timestamp']	= (int)strtotime(get_gmt_from_date($this->date));
		$json['time']		= wpjam_human_time_diff($json['timestamp']);
		$json['date']		= wp_date('Y-m-d', $json['timestamp']);

		if(is_new_day()){
			$GLOBALS['previousday']	= $GLOBALS['currentday'];

			$json['day']	= wpjam_human_date_diff($json['date']);
		}else{
			$json['day']	= '';
		}

		$modified_timestamp		= (int)strtotime($this->modified_gmt);
		$json['modified_time']	= wpjam_human_time_diff($modified_timestamp);
		$json['modified_date']	= wp_date('Y-m-d', $modified_timestamp);

		if($this->is_type_viewable()){
			$json['name']		= urldecode($this->name);
			$json['post_url']	= str_replace(home_url(), '', get_permalink($this->id));
		}

		$thumbnail_size		= wpjam_array_get($args, ['thumbnail_size', 'size']);
		$json['thumbnail']	= $this->get_thumbnail_url($thumbnail_size);

		if($this->supports('author')){
			$json['author']	= wpjam_get_user($this->author);
		}

		$json['user_id']	= (int)$this->author;

		if($this->supports('page-attributes')){
			$json['menu_order']	= (int)$this->menu_order;
		}

		if($this->supports('post-formats')){
			$json['format']	= get_post_format($this->post) ?: '';
		}

		$json['views']	= $this->views;

		if($args['list_query']){
			return $json;
		}

		foreach($this->get_taxonomies('objects') as $taxonomy => $taxonomy_object){
			if($taxonomy != 'post_format' && $taxonomy_object->public){
				$json[$taxonomy]	= [];

				$terms	= get_the_terms($this->id, $taxonomy) ?: [];

				foreach($terms as $term){
					$json[$taxonomy][]	= wpjam_get_term($term);
				}
			}
		}

		foreach($this->get_meta_options() as $mo_obj){
			$json	= array_merge($json, $mo_obj->prepare($this->id));
		}

		if($this->supports('images')){
			$json['images']	= $this->get_images();
		}

		if($this->is_singular() || $args['content_required']){
			if($this->supports('editor')){
				if($args['raw_content']){
					$json['raw_content']	= $this->content;
				}

				$json['content']	= $this->get_content();

				if($json['multipage'] = (bool)$GLOBALS['multipage']){
					$json['numpages']	= $GLOBALS['numpages'];
					$json['page']		= $GLOBALS['page'];
				}
			}

			if($this->is_singular()){
				$this->view();
			}
		}

		return apply_filters('wpjam_post_json', $json, $this->id, $args);
	}

	private static $instances	= [];

	public static function get_instance($post=null){
		$post	= self::validate($post);

		if(is_wp_error($post)){
			return null;
		}

		$id		= $post->ID;

		if(!isset($instances[$id])){
			$instances[$id]	= new self($id);
		}

		return $instances[$id];
	}

	public static function get($post){
		return self::get_post($post, ARRAY_A);
	}

	public static function insert($data){
		if(isset($data['post_type'])  && !post_type_exists($data['post_type'])){
			return new WP_Error('invalid_post_type', __('Invalid post type.'));
		}

		$data	= wp_parse_args($data, [
			'post_type'		=> 'post',
			'post_status'	=> 'publish',
			'post_author'	=> get_current_user_id(),
			'post_date'		=> get_date_from_gmt(date('Y-m-d H:i:s', time())),
		]);

		$data	= apply_filters('wpjam_pre_insert_post', $data, $data['post_type']);

		if(is_wp_error($data)){
			return $data;
		}

		return wp_insert_post(wp_slash($data), true, true);
	}

	public static function update($post_id, $data){
		if(!$post_id || !get_post($post_id)){
			return new WP_Error('post_not_exists', '文章不存在');
		}

		$data['ID'] = $post_id;

		return wp_update_post(wp_slash($data), true, true);
	}

	public static function delete($post_id, $force_delete=true){
		if(!$post_id || !get_post($post_id)){
			return new WP_Error('post_not_exists', '文章不存在');
		}

		$result	= wp_delete_post($post_id, $force_delete);

		return $result ? true : new WP_Error('delete_failed', '删除失败');
	}

	public static function value_callback($meta_key, $post_id){
		return wpjam_get_metadata('post', $post_id, $meta_key);
	}

	public static function get_meta($post_id, ...$args){
		// _deprecated_function(__METHOD__, 'WPJAM Basic 6.0', 'wpjam_get_metadata');
		return wpjam_get_metadata('post', $post_id, ...$args);
	}

	public static function update_meta($post_id, ...$args){
		// _deprecated_function(__METHOD__, 'WPJAM Basic 6.0', 'wpjam_update_metadata');
		return wpjam_update_metadata('post', $post_id, ...$args);
	}

	public static function update_metas($post_id, $data, $meta_keys=[]){
		// _deprecated_function(__METHOD__, 'WPJAM Basic 6.0', 'wpjam_update_metadata');
		return self::update_meta($post_id, $data, $meta_keys);
	}

	public static function duplicate($post_id){
		$post_arr	= get_post($post_id, ARRAY_A);
		$post_arr	= wpjam_array_except($post_arr, ['ID', 'post_date_gmt', 'post_modified_gmt', 'post_name']);

		$post_arr['post_status']	= 'draft';
		$post_arr['post_author']	= get_current_user_id();
		$post_arr['post_date_gmt']	= $post_arr['post_modified_gmt']	= date('Y-m-d H:i:s', time());
		$post_arr['post_date']		= $post_arr['post_modified']		= get_date_from_gmt($post_arr['post_date_gmt']);

		$post_arr['tax_input']		= [];

		foreach(get_object_taxonomies($post_arr['post_type']) as $taxonomy){
			$post_arr['tax_input'][$taxonomy]	= wp_get_object_terms($post_id, $taxonomy, ['fields' => 'ids']);
		}

		$new_post_id	= wp_insert_post(wp_slash($post_arr), true);

		if(!is_wp_error($new_post_id)){
			$meta_keys	= get_post_custom_keys($post_id) ?: [];

			foreach($meta_keys as $meta_key){
				if($meta_key == '_thumbnail_id' || ($meta_key != 'views' && !is_protected_meta($meta_key, 'post'))){
					foreach(get_post_meta($post_id, $meta_key) as $meta_value){
						add_post_meta($new_post_id, $meta_key, $meta_value, false);
					}
				}
			}
		}

		return $new_post_id;
	}

	public static function get_by_ids($post_ids){
		return self::update_caches($post_ids);
	}

	public static function prime_caches($posts, $args=[]){
		$post_ids	= $type_list = $authors = $attachment_ids = [];

		foreach($posts as $post){
			$post_type	= $post->post_type;
			$post_ids[]	= $post->ID;
			$authors[]	= $post->post_author;

			if(!isset($type_list[$post_type])){
				$type_list[$post_type]	= [];
			}

			$type_list[$post_type][]	= $post->ID;
		}

		if($type_list){
			foreach($type_list as $post_type => $type_ids){
				if(!empty($args['update_post_term_cache'])){
					update_object_term_cache($type_ids, $post_type);
				}else{
					wpjam_lazyload('post_term', $type_ids, $post_type);
				}
			}	
		}

		if(!empty($args['update_post_meta_cache'])){
			update_postmeta_cache($post_ids);
		}else{
			wpjam_lazyload('post_meta', $post_ids);
		}

		wpjam_lazyload('user', array_unique(array_filter($authors)));

		foreach($posts as $post){
			if(post_type_supports($post->post_type, 'thumbnail')){
				// $attachment_ids[]	= get_post_thumbnail_id($post_id);
				$attachment_ids[]	= get_post_meta($post->ID, '_thumbnail_id', true);
			}

			if($post->post_content && strpos($post->post_content, '<img') !== false){
				if(preg_match_all('/class=[\'"].*?wp-image-([\d]*)[\'"]/i', $post->post_content, $matches)){
					$attachment_ids	= array_merge($attachment_ids, $matches[1]);
				}
			}
		}

		$attachment_ids	= array_unique(array_filter($attachment_ids));

		wpjam_lazyload('attachment', $attachment_ids);
		// _prime_post_caches($attachment_ids,	false, true);
	}

	public static function update_caches($post_ids){
		$post_ids 	= array_filter($post_ids);
		$post_ids	= array_unique($post_ids);

		_prime_post_caches($post_ids, false, false);

		$cache_values	= wp_cache_get_multiple($post_ids, 'posts');

		self::prime_caches(array_filter($cache_values));

		return array_map('get_post', $cache_values);
	}

	public static function update_attachment_caches($attachment_ids){
		$attachment_ids = array_filter($attachment_ids);
		$attachment_ids	= array_unique($attachment_ids);

		_prime_post_caches($attachment_ids, false, false);

		wpjam_lazyload('post_meta', $attachment_ids);
	}

	public static function get_post($post, $output=OBJECT, $filter='raw'){
		if($post && is_numeric($post)){	// 不存在情况下的缓存优化
			$found	= false;
			$cache	= wp_cache_get($post, 'posts', false, $found);

			if($found){
				if(is_wp_error($cache)){
					return $cache;
				}elseif(!$cache){
					return null;
				}
			}else{
				$_post	= WP_Post::get_instance($post);

				if(!$_post){	// 防止重复 SQL 查询。
					wp_cache_add($post, false, 'posts', 10);
					return null;
				}
			}
		}

		return get_post($post, $output, $filter);
	}

	public static function find_by_name($post_name, $post_type='', $post_status='publish'){
		global $wpdb;

		$show_if	= $show_if_with_type = $show_if_for_meta = [];

		if($post_status && $post_status != 'any'){
			$show_if[]	= ['key'=>'post_status',	'compare'=>'IN',	'value'=>(array)$post_status];
		}

		if($post_type && $post_type != 'any'){
			$show_if_with_type		= $show_if;
			$show_if_with_type[]	= ['key'=>'post_type',	'compare'=>'IN',	'value'=>(array)$post_type];
		}

		$post_types	= get_post_types(['public'=>true, 'exclude_from_search'=>false]);
		$post_types	= wpjam_array_except($post_types, 'attachment');

		$show_if_for_meta	= $show_if;
		$show_if_for_meta[]	= ['key'=>'post_type',	'compare'=>'IN',	'value'=>$post_types];

		$meta		= wpjam_get_by_meta('post', '_wp_old_slug', $post_name);
		$post_ids	= $meta ? array_column($meta, 'post_id') : [];
		$posts		= $post_ids ? self::get_by_ids($post_ids) : [];

		if($show_if_with_type){
			foreach($posts as $post){
				if(wpjam_show_if($post, $show_if_with_type)){
					return $post;
				}
			}
		}

		foreach($posts as $post){
			if(wpjam_show_if($post, $show_if_for_meta)){
				return $post;
			}
		}

		// find by name like name%
		$post_types	= get_post_types(['public'=>true, 'hierarchical'=>false, 'exclude_from_search'=>false]);
		$post_types	= wpjam_array_except($post_types, 'attachment');

		$where	= "post_type in ('" . implode( "', '", array_map('esc_sql', $post_types)) . "')";
		$where	.= ' AND '.$wpdb->prepare("post_name LIKE %s", $wpdb->esc_like($post_name).'%');

		$post_ids	= $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE $where");
		$posts		= $post_ids ? self::get_by_ids($post_ids) : [];

		if($show_if_with_type){
			foreach($posts as $post){
				if(wpjam_show_if($post, $show_if_with_type)){
					return $post;
				}
			}
		}

		foreach($posts as $post){
			if($show_if){
				if(wpjam_show_if($post, $show_if)){
					return $post;
				}
			}else{
				return $post;
			}
		}

		return null;
	}

	public static function validate($post_id, $post_type=''){
		$post	= self::get_post($post_id);

		if(!$post || !($post instanceof WP_Post)){
			return new WP_Error('post_not_exists', '文章不存在');
		}

		if(!post_type_exists($post->post_type)){
			return new WP_Error('post_type_not_exists', '文章类型不存在');
		}

		if($post_type && $post_type != 'any' && $post_type != $post->post_type){
			return new WP_Error('invalid_post_type', '无效的文章类型');
		}

		return $post;
	}
}

class WPJAM_Post_Type extends WPJAM_Register{
	private $_fields;

	public function parse_args(){
		if(!$this->plural){
			$this->plural	= $this->name.'s';
		}

		if(!doing_filter('register_post_type_args')){
			if(isset($this->taxonomies) && !$this->taxonomies){
				unset($this->taxonomies);
			}

			$this->args	= wp_parse_args($this->args, [
				'public'		=> true,
				'show_ui'		=> true,
				'hierarchical'	=> false,
				'rewrite'		=> true,
				'permastruct'	=> false,
				'supports'		=> ['title'],
				'by_wpjam'		=> true,
			]);

			if(is_admin() && $this->args['show_ui']){
				add_filter('post_type_labels_'.$this->name,	[$this, 'filter_labels']);
			}

			add_action('registered_post_type_'.$this->name,	[$this, 'registered_callback'], 10, 2);
		}
	}

	public function to_array(){
		$this->filter_args();

		if(doing_filter('register_post_type_args')){
			if(!$this->_builtin && $this->permastruct){
				$this->permastruct	= str_replace('%post_id%', '%'.$this->name.'_id%', $this->permastruct);

				if(strpos($this->permastruct, '%'.$this->name.'_id%')){
					if($this->hierarchical){
						$this->permastruct	= false;
					}else{
						$this->query_var	= $this->query_var ?? false;

						if(!$this->rewrite){
							$this->rewrite	= true;
						}
					}
				}
			}

			if($this->by_wpjam){
				if($this->hierarchical){
					$this->supports		= array_merge($this->supports, ['page-attributes']);
				}

				if($this->rewrite){
					$this->rewrite	= is_array($this->rewrite) ? $this->rewrite : [];
					$this->rewrite	= wp_parse_args($this->rewrite, ['with_front'=>false, 'feeds'=>false]);
				}
			}
		}

		return $this->args;
	}

	public function get_keys(){
		return ['thumbnail_size', 'images_sizes', 'plural'];
	}

	public function get_arg($key, $default=null){
		if(in_array($key, $this->get_keys()) && isset($this->$key)){
			return $this->$key;
		}

		return get_post_type_object($this->name)->$key ?? $default;
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

			if(post_type_supports($this->name, 'images')){
				$fields['images']	= ['title'=>'图片', 'type'=>'mu-img',	'item_type'=>'url'];

				if($this->images_sizes){
					$fields['images']['size']			= $this->images_sizes[0];
					$fields['images']['description']	= '尺寸：'.$this->images_sizes[0];
				}
			}

			if(post_type_supports($this->name, 'video')){
				$fields['video']	= ['title'=>'视频',	'type'=>'url',	'data_type'=>'video'];
			}

			$this->_fields	= $fields;
		}

		return $this->_fields;
	}

	public function filter_labels($labels){
		$_labels	= $this->labels ?? [];

		$labels		= (array)$labels;
		$name		= $labels['name'];

		$search		= $this->hierarchical ? ['撰写新', '写文章', '页面', 'page', 'Page'] : ['撰写新', '写文章', '文章', 'post', 'Post'];
		$replace	= ['新建', '新建'.$name, $name, $name, ucfirst($name)];

		foreach ($labels as $key => &$label) {
			if($label && empty($_labels[$key])){
				if($key == 'all_items'){
					$label	= '所有'.$name;
				}elseif($key == 'archives'){
					$label	= $name.'归档';
				}elseif($label != $name){
					$label	= str_replace($search, $replace, $label);
				}
			}
		}

		return $labels;
	}

	public function registered_callback($post_type, $object){
		if($this->name == $post_type){
			// print_r($this->name."\n");

			if($this->permastruct){
				if(strpos($this->permastruct, '%'.$post_type.'_id%')){
					wpjam_set_permastruct($post_type, $this->permastruct);

					add_rewrite_tag('%'.$post_type.'_id%', '([0-9]+)', 'post_type='.$post_type.'&p=');

					remove_rewrite_tag('%'.$post_type.'%');
				}elseif(strpos($this->permastruct, '%postname%')){
					wpjam_set_permastruct($post_type, $this->permastruct);
				}
			}

			if($this->registered_callback && is_callable($this->registered_callback)){
				call_user_func($this->registered_callback, $post_type, $object);
			}
		}
	}

	public static function autoload(){
		add_filter('posts_clauses',				[self::class, 'filter_clauses'], 1, 2);
		add_filter('post_type_link',			[self::class, 'filter_link'], 1, 2);
		add_filter('post_password_required',	[self::class, 'filter_password_required'], 10, 2);

		foreach(self::get_registereds() as $post_type => $object){
			if(!get_post_type_object($post_type)){
				register_post_type($post_type, $object->to_array());
			}
		}
	}

	public static function filter_register_args($args, $post_type){
		if(did_action('init')){
			$object	= self::get($post_type);

			if($object){
				foreach(wp_array_slice_assoc($args, $object->get_keys()) as $key => $value){
					$object->$key	= $args[$key];
				}
			}else{
				$object	= self::register($post_type, $args);
			}

			return $object->to_array();
		}

		return $args;
	}

	public static function on_registered($post_type, $object){
		if(did_action('init')){
			(self::get($post_type))->registered_callback($post_type, $object);
		}
	}

	public static function filter_link($post_link, $post){
		$post_type	= get_post_type($post);

		if(array_search('%'.$post_type.'_id%', $GLOBALS['wp_rewrite']->rewritecode, true)){
			$post_link	= str_replace('%'.$post_type.'_id%', $post->ID, $post_link);
		}

		if(strpos($post_link, '%') !== false){
			foreach(get_object_taxonomies($post_type, 'objects') as $taxonomy => $taxonomy_object){
				if($taxonomy_object->rewrite){
					$tax_slug	= $taxonomy_object->rewrite['slug'];

					if(strpos($post_link, '%'.$tax_slug.'%') === false){
						continue;
					}

					if($terms = get_the_terms($post->ID, $taxonomy)){
						$post_link	= str_replace('%'.$tax_slug.'%', current($terms)->slug, $post_link);
					}else{
						$post_link	= str_replace('%'.$tax_slug.'%', $taxonomy, $post_link);
					}
				}
			}
		}

		return $post_link;
	}

	public static function filter_clauses($clauses, $wp_query){
		global $wpdb;

		if($wp_query->get('related_query')){
			if($term_taxonomy_ids = $wp_query->get('term_taxonomy_ids')){
				$clauses['fields']	.= ", count(tr.object_id) as cnt";
				$clauses['join']	.= "INNER JOIN {$wpdb->term_relationships} AS tr ON {$wpdb->posts}.ID = tr.object_id";
				$clauses['where']	.= " AND tr.term_taxonomy_id IN (".implode(",",$term_taxonomy_ids).")";
				$clauses['groupby']	.= " tr.object_id";
				$clauses['orderby']	= " cnt DESC, {$wpdb->posts}.ID DESC";
			}
		}else{
			$orderby	= $wp_query->get('orderby');
			$order		= $wp_query->get('order') ?: 'DESC';

			if($orderby == 'views'){
				$clauses['fields']	.= ", (COALESCE(jam_pm.meta_value, 0)+0) as {$orderby}";
				$clauses['join']	.= "LEFT JOIN {$wpdb->postmeta} jam_pm ON {$wpdb->posts}.ID = jam_pm.post_id AND jam_pm.meta_key = '{$orderby}' ";
				$clauses['orderby']	= "{$orderby} {$order}, " . $clauses['orderby'];
			}elseif(in_array($orderby, ['', 'date', 'post_date'])){
				$clauses['orderby']	.= ", {$wpdb->posts}.ID {$order}";
			}
		}

		return $clauses;
	}

	public static function filter_password_required($required, $post){
		if(!$required){
			return $required;
		}

		$hash	= wpjam_get_parameter('post_password', ['method'=>'REQUEST']);

		if(empty($hash) || 0 !== strpos($hash, '$P$B')){
			return true;
		}

		require_once ABSPATH . WPINC . '/class-phpass.php';

		$hasher	= new PasswordHash(8, true);

		return !$hasher->CheckPassword($post->post_password, $hash);
	}

	public static function create($name, $args){
		$object = self::register($name, $args);

		if(did_action('init')){
			register_post_type($name, $object->to_array());
		}

		return $object;
	}
}

class WPJAM_Query_Parser{
	private $wp_query;

	public function __construct($wp_query, &$args=[]){
		if(is_array($wp_query)){
			$query_vars		= self::parse_query_vars($wp_query, $args);

			$this->wp_query	= wpjam_query($query_vars);
		}else{
			$this->wp_query	= $wp_query;
		}
	}

	public function parse($args=[]){
		$parsed	= [];

		if(!$this->wp_query){
			return $parsed;
		}

		$filter	= wpjam_array_pull($args, 'filter');
		$args	= array_merge($args, ['list_query'=>true]);

		if($this->wp_query->have_posts()){
			while($this->wp_query->have_posts()){
				$this->wp_query->the_post();

				$post_id	= get_the_ID();
				$json		= wpjam_get_post($post_id, $args);

				if($filter){

					$json	= apply_filters($filter, $json, $post_id, $args);

				}

				$parsed[]	= $json;
			}
		}

		wp_reset_postdata();

		return $parsed;
	}

	public function render($args=[]){
		$output	= '';

		if(!$this->wp_query){
			return $output;
		}

		$item_callback	= wpjam_array_pull($args, 'item_callback');

		if(!$item_callback || !is_callable($item_callback)){
			$item_callback	= [$this, 'item_callback'];
		}

		$title_number	= wpjam_array_pull($args, 'title_number');
		$total_number	= count($this->wp_query->posts);

		if($this->wp_query->have_posts()){
			while($this->wp_query->have_posts()){
				$this->wp_query->the_post();

				if($title_number){
					$args['title_number']	= zeroise($this->wp_query->current_post+1, strlen($total_number));
				}

				$output .= call_user_func($item_callback, get_the_ID(), $args);
			}
		}

		wp_reset_postdata();

		$wrap_callback	= wpjam_array_pull($args, 'wrap_callback');

		if(!$wrap_callback || !is_callable($wrap_callback)){
			$wrap_callback	= [$this, 'wrap_callback'];
		}

		$output = call_user_func($wrap_callback, $output, $args);

		return $output;
	}

	public function item_callback($post_id, $args){
		$args	= wp_parse_args($args, [
			'title_number'	=> 0,
			'excerpt'		=> false,
			'thumb'			=> true,
			'size'			=> 'thumbnail',
			'thumb_class'	=> 'wp-post-image',
			'wrap'			=> '<li>%1$s</li>'
		]);

		$item	= get_the_title($post_id);

		if($args['title_number']){
			$item	= '<span class="title-number">'.$args['title_number'].'</span>. '.$item;
		}

		if($args['thumb'] || $args['excerpt']){
			$item = '<h4>'.$item.'</h4>';

			if($args['thumb']){
				$item	= get_the_post_thumbnail($post_id, $args['size'], ['class'=>$args['thumb_class']])."\n".$item;
			}

			if($args['excerpt']){
				$item	= $item."\n".wpautop(get_the_excerpt($post_id));
			}
		}

		$item	= '<a href="'.get_permalink($post_id).'" title="'.the_title_attribute(['post'=>$post_id, 'echo'=>false]).'">'.$item.'</a>';

		if($args['wrap']){
			$item	= sprintf($args['wrap'], $item)."\n";
		}

		return $item;
	}

	public function wrap_callback($output, $args){
		if(!$output){
			return '';
		}

		$args	= wp_parse_args($args, [
			'title'		=> '',
			'div_id'	=> '',
			'class'		=> '',
			'thumb'		=> true,
			'wrap'		=> '<ul %1$s>%2$s</ul>'
		]);

		if($args['thumb']){
			$args['class']	= $args['class'].' has-thumb';
		}

		$class	= $args['class'] ? ' class="'.$args['class'].'"' : '';

		if($args['wrap']){
			$output	= sprintf($args['wrap'], $class, $output)."\n";
		}

		if($args['title']){
			$output	= '<h3>'.$args['title'].'</h3>'."\n".$output;
		}

		if($args['div_id']){
			$output	= '<div id="'.$args['div_id'].'">'."\n".$output.'</div>'."\n";
		}

		return $output;
	}

	public static function parse_tax_query($taxonomy, $term_id){
		if($term_id == 'none'){
			return ['taxonomy'=>$taxonomy,	'field'=>'term_id',	'operator'=>'NOT EXISTS'];
		}else{
			return ['taxonomy'=>$taxonomy,	'field'=>'term_id',	'terms'=>[$term_id]];
		}
	}

	public static function parse_query_vars($query_vars, &$args=[]){
		$tax_query	= $query_vars['tax_query'] ?? [];
		$date_query	= $query_vars['date_query'] ?? [];

		$taxonomies	= array_values(get_taxonomies(['_builtin'=>false]));

		foreach(array_merge($taxonomies, ['category', 'post_tag']) as $taxonomy){
			$query_key	= wpjam_get_taxonomy_query_key($taxonomy);
			$term_id	= wpjam_array_pull($query_vars, $query_key);

			if($term_id){
				if($taxonomy == 'category' && $term_id != 'none'){
					$query_vars[$query_key]	= $term_id;
				}else{
					$tax_query[]	= self::parse_tax_query($taxonomy, $term_id);
				}
			}
		}

		if(!empty($query_vars['taxonomy']) && empty($query_vars['term'])){
			$term_id	= wpjam_array_pull($query_vars, 'term_id');

			if($term_id){
				if(is_numeric($term_id)){
					$taxonomy		= wpjam_array_pull($query_vars, 'taxonomy');
					$tax_query[]	= self::parse_tax_query($taxonomy, $term_id);
				}else{
					$query_vars['term']	= $term_id;
				}
			}
		}

		foreach(['cursor'=>'before', 'since'=>'after'] as $key => $query_key){
			$value	= wpjam_array_pull($query_vars, $key);

			if($value){
				$date_query[]	= [$query_key => get_date_from_gmt(date('Y-m-d H:i:s', $value))];
			}
		}

		if($args){
			$post_type	= wpjam_array_pull($args, 'post_type');
			$orderby	= wpjam_array_pull($args, 'orderby');
			$number		= wpjam_array_pull($args, 'number');
			$days		= wpjam_array_pull($args, 'days');

			if($post_type){
				$query_vars['post_type']	= $post_type;
			}

			if($orderby){
				$query_vars['orderby']	= $orderby;
			}

			if($number){
				$query_vars['posts_per_page']	= $number;
			}

			if($days){
				$after	= date('Y-m-d', current_time('timestamp') - DAY_IN_SECONDS * $days).' 00:00:00';
				$column	= wpjam_array_pull($args, 'column') ?: 'post_date_gmt';

				$date_query[]	= ['column'=>$column, 'after'=>$after];
			}
		}

		if($tax_query){
			$query_vars['tax_query']	= $tax_query;
		}

		if($date_query){
			$query_vars['date_query']	= $date_query;
		}

		return $query_vars;
	}
}
