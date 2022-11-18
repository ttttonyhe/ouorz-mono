<?php
if(!class_exists('WP_List_Table')){
	include ABSPATH.'wp-admin/includes/class-wp-list-table.php';
}

class WPJAM_List_Table extends WP_List_Table{
	public function __construct($args=[]){
		$this->_args	= $args	= wp_parse_args($args, [
			'title'			=> '',
			'plural'		=> '',
			'singular'		=> '',
			'model'			=> '',
			'data_type'		=> 'model',
			'capability'	=> 'manage_options',
			'per_page'		=> 50
		]);

		$GLOBALS['wpjam_list_table']	= $this;

		$primary_key	= $this->get_primary_key_by_model();

		if($primary_key && !is_wp_error($primary_key)){
			$args['primary_key']	= $primary_key;
		}

		parent::__construct($this->parse_args($args));
	}

	public function __get($name){
		if(in_array($name, $this->compat_fields, true)){
			return $this->$name;
		}elseif(isset($this->_args[$name])){
			return $this->_args[$name];
		}
	}

	public function __isset($name){
		return $this->$name !== null;
	}

	public function __set( $name, $value ) {
		if(in_array($name, $this->compat_fields, true)){
			return $this->$name	= $value;
		}else{
			$this->_args	= $this->_args ?? [];

			return $this->_args[$name]	= $value; 
		}
	}

	public function __call($method, $args){
		if(in_array($method, $this->compat_methods, true)){
			return $this->$method(...$args);
		}

		if(str_ends_with($method, '_by_model')){
			$method	= wpjam_remove_postfix($method, '_by_model');

			return $this->call_model_method($method, ...$args);
		}elseif(str_ends_with($method, '_by_data_type')){
			$object	= $this->data_type ? wpjam_get_data_type_object($this->data_type) : null;

			if($object){
				$method	= wpjam_remove_postfix($method, '_by_data_type');

				return call_user_func([$object, $method], $this->_args);
			}
		}
	}

	public function parse_args($args){
		$this->screen	= $args['screen'] = get_current_screen();
		$this->_args	= $this->_args ?? [];
		$this->_args	= array_merge($this->_args, $args);

		if(is_array($this->per_page)){
			add_screen_option('per_page', $this->per_page);
		}

		if($this->style){
			wp_add_inline_style('list-tables', $this->style);
		}

		$this->add_screen_item('ajax', true);
		$this->add_screen_item('form_id', 'list_table_form');
		$this->add_screen_item('query_id', $this->get_query_id());
		$this->add_screen_item('left_key', $this->left_key);

		if($this->sortable){
			$sortable		= is_array($this->sortable) ? $this->sortable : ['items'=>' >tr'];
			$action_args	= wpjam_array_pull($sortable, 'action_args', []);

			wpjam_register_list_table_action('move',	array_merge($action_args, ['page_title'=>'拖动',		'direct'=>true,	'dashicon'=>'move']));
			wpjam_register_list_table_action('up',		array_merge($action_args, ['page_title'=>'向上移动',	'direct'=>true,	'dashicon'=>'arrow-up-alt']));
			wpjam_register_list_table_action('down',	array_merge($action_args, ['page_title'=>'向下移动',	'direct'=>true,	'dashicon'=>'arrow-down-alt']));

			$this->add_screen_item('sortable', $sortable);
		}

		$this->register_list_table_action_by_data_type();

		$args['row_actions']	= $args['bulk_actions']	= $args['overall_actions']	= $next_actions = [];

		foreach(wpjam_list_sort(WPJAM_List_Table_Action::get_registereds()) as $key => $object){
			if(!$this->is_available($object)){
				continue;
			}

			$object->model		= $this->model;
			$object->capability	= $object->capability ?? $this->capability;
			$object->page_title	= $object->page_title ?? ($object->title ? wp_strip_all_tags($object->title.$this->title) : '');

			if($object->overall){
				$object->response	= $object->response ?: 'list';

				$args['overall_actions'][]	= $key;
			}else{
				if(is_null($object->response)){
					$object->response	= $key;
				}

				if($object->bulk){
					if($object->current_user_can()){
						$args['bulk_actions'][$key]	= $object->title ?: '';
					}
				}

				if($object->next && $object->response == 'form'){
					$next_actions[]	= $object->next;
				}

				if($key == 'add'){
					if($this->layout == 'left'){
						$args['overall_actions'][]	= $key;
					}
				}else{
					if(is_null($object->row_action) || $object->row_action){
						$args['row_actions'][]	= $key;
					}
				}
			}
		}

		$args['row_actions']	= array_diff($args['row_actions'], $next_actions);

		$args['columns']			= $args['columns'] ?? [];
		$args['sortable_columns']	= $args['sortable_columns'] ?? [];

		foreach(WPJAM_List_Table_Column::get_pre_registereds() as $object){
			if($data_type = $object->data_type){
				unset($object->data_type);
				unset($object->$data_type);
			}

			if($this->is_available($object)){
				WPJAM_List_Table_Column::register($object);
			}
		}

		$fields	= $this->get_fields_by_model() ?: [];

		WPJAM_List_Table_Column::register_by_fields($fields);

		foreach(wpjam_list_sort(WPJAM_List_Table_Column::get_registereds()) as $object){
			$column_name	= $object->name;

			if(is_null($object->filterable)){
				$object->filterable	= $this->is_filterable_column($column_name);
			}

			$args['columns'][$column_name]	= $object->column_title ?? $object->title;

			if($object->sortable_column){
				$args['sortable_columns'][$column_name] = [$column_name, true];
			}
		}

		return $args;
	}

	public function get_action_object($name){
		return WPJAM_List_Table_Action::get($name);
	}

	public function add_screen_item($key, $value){
		(WPJAM_Screen_Option::get_instance('wpjam_list_table'))->add_item($key, $value);
	}

	public function is_available($object){
		foreach(['data_type', 'post_type', 'taxonomy'] as $key){
			if($object->$key && !in_array($this->$key, (array)$object->$key)){
				return false;
			}
		}

		foreach(['plugin_page', 'current_tab'] as $key){
			if($object->$key){
				trigger_error($this->name.' '.$object->name.' '.$key.':'.var_export($object->$key, true));

				if(empty($GLOBALS[$key]) || !in_array($GLOBALS[$key], (array)$object->$key)){
					return false;
				}
			}
		}

		if($object->screen_base){
			trigger_error($this->name.' '.$object->name.' '.var_export($object->screen_base, true));

			if(!in_array($this->screen->base, (array)$object->screen_base)){
				return false;
			}
		}

		return true;
	}

	public function call_model_method($method, ...$args){
		if(method_exists($this->model, $method)){
			return call_user_func([$this->model, $method], ...$args);
		}

		$fallback	= [
			'render_item'	=> 'item_callback',
			'get_subtitle'	=> 'subtitle',
			'get_views'		=> 'views',
			'query_items'	=> 'list',
		];

		if(isset($fallback[$method]) && method_exists($this->model, $fallback[$method])){
			return call_user_func([$this->model, $fallback[$method]], ...$args);
		}

		if(in_array($method, [
			'render_item',
			'render_date'
		])){
			return $args[0];
		}elseif(in_array($method, [
			'get_subtitle',
			'get_views', 
			'get_fields',
			'extra_tablenav',
			'before_single_row',
			'after_single_row',
		])){
			return null;
		}elseif(method_exists($this->model, '__callStatic')){
			return call_user_func([$this->model, $method], ...$args);
		}else{
			return new WP_Error('undefined_method', $this->model.'的「'.$method.'」方法未定义');
		}
	}

	public function call_model_list_action($id, $data, $list_action){
		$object	= $this->get_action_object($list_action);

		$method	= $list_action;

		if($list_action == 'add'){
			$method	= 'insert';
		}elseif($list_action == 'edit'){
			$method	= 'update';
		}elseif($list_action == 'duplicate'){
			if(!is_null($data)){
				$method	= 'insert';
			}
		}elseif(in_array($list_action, ['up', 'down'], true)){
			$method	= 'move';
		}

		if($object->overall){
			$result	= $this->call_model_method($method, $data);
		}elseif($method == 'insert' || $object->response == 'add'){
			$result	= $this->call_model_method($method, $data, $object->last);
		}else{
			$meta_type	= $object->meta_type;

			if(method_exists($this->model, $method)){
				$reflection	= new ReflectionClass($this->model);

				if($reflection->getMethod($method)->isStatic()){
					$result	= call_user_func([$this->model, $method], $id, $data);
				}else{
					$instance	= wpjam_get_object_by_model($this->model, $id);

					if(is_wp_error($instance)){
						return $instance;
					}

					$result	= call_user_func([$instance, $method], $data);					
				}
			}elseif(!$meta_type && method_exists($this->model, '__callStatic')){
				$result	= call_user_func([$this->model, $method], $id, $data);

				if(!is_null($result)){
					return $result;
				}
			}else{
				if($meta_type === true || !$meta_type){
					$meta_type	= $this->get_meta_type_by_data_type();
				}

				if($meta_type){
					$fields		= $this->get_fields($list_action, $id, ['include_prev'=>true]);
					$defaults	= $fields ? wpjam_get_fields_defaults($fields) : null;

					return wpjam_update_metadata($meta_type, $id, $data, $defaults);
				}

				return new WP_Error('undefined_method', '「'.$method.'」方法未定义');
			}
		}

		return is_null($result) ? true : $result;
	}

	public function get_query_id(){
		if($this->current_action()){
			return null;
		}

		return wpjam_get_parameter('id', ['sanitize_callback'=>'sanitize_text_field']);
	}

	public function get_subtitle(){
		$subtitle		= $this->get_subtitle_by_model() ?: '';
		$search_term	= wpjam_get_data_parameter('s');

		if($search_term){
			$subtitle 	.= ' “'.esc_html($search_term).'”的搜索结果';
		}

		$subtitle	= $subtitle ? '<span class="subtitle">'.$subtitle.'</span>' : '';

		if($this->layout != 'left'){
			$subtitle	= $this->get_row_action('add', ['class'=>'page-title-action', 'subtitle'=>true]).$subtitle;
		}

		return $subtitle;
	}

	protected function get_row_actions($id){
		$row_actions	= [];

		foreach($this->row_actions as $key){
			$row_actions[$key] = $this->get_row_action($key, ['id'=>$id]);
		}

		return array_filter($row_actions);
	}

	public function get_row_action($action, $args=[]){
		$object = $this->get_action_object($action);

		return $object ? $object->get_row_action($args, $this->layout) : '';
	}

	public function get_filter_link($filters, $title, $class=''){
		$title_attr	= esc_attr(wp_strip_all_tags($title, true));
		$class		= $class ? ' '.$class : '';

		$query_args	= $this->query_args ?: [];	

		foreach($query_args as $query_arg){
			if(!isset($filters[$query_arg])){
				$filters[$query_arg]	= wpjam_get_data_parameter($query_arg);
			}
		}

		return '<a href="javascript:;" title="'.$title_attr.'" class="list-table-filter'.$class.'" data-filter=\''.wpjam_json_encode($filters).'\'>'.$title.'</a>';
	}

	public function get_single_row($id){		
		ob_start();
		$this->single_row($id);
		return ob_get_clean();
	}

	public function single_row($raw_item){
		if(!is_array($raw_item) || is_object($raw_item)){
			$result		= $this->get_by_model($raw_item);
			$raw_item	= is_wp_error($result) ? null : $result;
		}

		if(empty($raw_item)){
			return;
		}

		$raw_item	= (array)$raw_item;

		$this->before_single_row_by_model($raw_item);

		$attr	= '';
		$class	= '';

		if($this->primary_key){
			$id	= $raw_item[$this->primary_key];
			$id	= str_replace('.', '-', $id);

			$attr	.= ' id="'.$this->singular.'-'.$id.'"';
			$attr	.= ' data-id="'.$id.'"';

			if($this->multi_rows){
				$class	.= 'tr-'.$id;
			}
		}

		$item	= $this->render_item($raw_item);

		if(!empty($item['class'])){
			$class	.= ' '.$item['class'];
		}

		$attr	.= $class ? ' class="'.$class.'"' : '';
		$attr	.= isset($item['style']) ? ' style="'.$item['style'].'"' : '';

		echo '<tr'.$attr.'>';

		$this->single_row_columns($item);

		echo '</tr>';

		$this->after_single_row_by_model($item, $raw_item);
	}

	protected function render_item($raw_item){
		$item		= (array)$raw_item;
		$item_id	= $item[$this->primary_key];

		if($this->primary_key){
			$item['row_actions']	= $this->get_row_actions($item_id);

			if($this->primary_key == 'id'){
				$item['row_actions']['id']	= 'ID：'.$item_id;
			}
		}

		return $this->render_item_by_model($item);
	}

	public function get_column_value($id, $column_name, $value=null){
		$object	= WPJAM_List_Table_Column::get($column_name);

		if($object){
			if(is_null($value)){
				if(method_exists($this->model, 'value_callback')){
					$value	= call_user_func([$this->model, 'value_callback'], $column_name, $id);
				}else{
					$value	= $object->default;		
				}
			}

			return $object->callback($id, $value);
		}

		return $value;
	}

	public function column_default($item, $column_name){
		$value	= $item[$column_name] ?? null;

		if($this->primary_key){
			return $this->get_column_value($item[$this->primary_key], $column_name, $value);
		}

		return $value ?? '';
	}

	public function column_cb($item){
		if($this->primary_key){
			$item_id	= $item[$this->primary_key];

			if($this->capability == 'read' || current_user_can($this->capability, $item_id)){
				$name	= isset($item['name']) ? strip_tags($item['name']) : $item_id;

				return '<label class="screen-reader-text" for="cb-select-'.esc_attr($item_id).'">选择'.$name.'</label>'.'<input class="list-table-cb" type="checkbox" name="ids[]" value="'.esc_attr($item_id).'" id="cb-select-'.esc_attr($item_id). '" />';
			}
		}
		
		return '<span class="dashicons dashicons-minus"></span>';
	}

	public function render_column_items($id, $items, $args=[]){
		$item_type	= $args['item_type'] ?? 'image';
		$sortable	= $args['sortable'] ?? false;
		$max_items	= $args['max_items'] ?? 0;
		$per_row	= $args['per_row'] ?? 0;
		$width		= $args['width'] ?? 60;
		$height		= $args['height'] ?? 60;
		$style		= $args['style'] ?? '';
		$add_item	= $args['add_item'] ?? 'add_item';
		$edit_item	= $args['edit_item'] ?? 'edit_item';
		$move_item	= $args['move_item'] ?? 'move_item';
		$del_item	= $args['del_item'] ?? 'del_item';

		$i			= 0;

		$rendered	= '';
		
		if($item_type == 'image'){
			$key	= $args['image_key'] ?? 'image';
			
			foreach($items as $i => $item){
				$args		= ['id'=>$id,	'data'=>compact('i')];

				$class		= 'item';
				$image		= $item[$key];
				// $class		= $image ? $class : $class.' dashicons dashicons-plus-alt2';
				$image		= $image ? '<img src="'.wpjam_get_thumbnail($image, $width*2, $height*2).'" '.image_hwstring($width, $height).' />' : ' ';
				$image		= $this->get_row_action($move_item,	array_merge($args, ['class'=>'move-item', 'title'=>$image]));
				$image		.= $this->get_row_action($del_item,	array_merge($args, ['class'=>'del-item-icon dashicons dashicons-no-alt', 'title'=>' ']));

				$item_style	= 'width:'.$width.'px;';

				if(!empty($item['color'])){
					$item_style	.= ' color:'.$item['color'].';';
				}
				
				$title		= !empty($item['title']) ? '<span class="item-title" style="'.$item_style.'">'.$item['title'].'</span>' : '';

				$actions	= $this->get_row_action($move_item,	array_merge($args, [
					'class'	=>'move-item dashicons dashicons-move', 
					'title'	=>' ', 
					'wrap'	=>'<span class="%1$s">%2$s | </span>'
				])).$this->get_row_action($edit_item,	array_merge($args, [
					'class'	=>'', 
					'title'	=>'修改', 
					'wrap'	=>'<span class="%1$s">%2$s</span>'
				]));

				$actions	= $actions ? '<span class="row-actions" style="width:'.$width.'px;">'.$actions.'</span>':'';
				$rendered	.= '<div id="item-'.$i.'" data-i="'.$i.'" class="'.$class.'" style="width:'.$width.'px;">'.$image.$title.$actions.'</div>';
			}

			if(!$max_items || $i < $max_items-1){
				$rendered	.= $this->get_row_action($add_item, [
					'tag'	=> 'div',
					'id'	=> $id,
					'class'	=> 'add-item dashicons dashicons-plus-alt2',
					'style'	=> 'width:'.$width.'px; height:'.$height.'px; line-height:'.$height.'px;',
					'title'	=> ' '
				]);
			}
		}elseif($item_type == 'text'){
			$key	= $args['text_key'] ?? 'text';

			foreach($items as $i => $item){
				$args		= ['id'=>$id,	'data'=>compact('i')];
				
				$text		= $item[$key] ?: ' ';

				if(!empty($item['color'])){
					$text	= '<span style="color:'.$item['color'].'">'.$text.'</span>';
				}

				$text		= $this->get_row_action($move_item, array_merge($args, ['class'=>'move-item text',	'title'=>$text]));

				$actions	= $this->get_row_action($move_item, array_merge($args, [
					'class'	=> 'move-item dashicons dashicons-move',	
					'title'	=> ' ',
					'wrap'	=> '<span class="%1$s">%2$s | </span>'
				])).$this->get_row_action($edit_item, array_merge($args, [
					'class'	=> '',	
					'title'	=> '修改',
					'wrap'	=> '<span class="%1$s">%2$s | </span>'
				])).$this->get_row_action($del_item, array_merge($args, [
					'title'	=> '删除',
					'wrap'	=> '<span class="delete">%2$s</span>'
				]));

				$actions	= $actions ? '<span class="row-actions">'.$actions.'</span>':'';

				$rendered	.= '<div id="item-'.$i.'" data-i="'.$i.'" class="item">'.$text.$actions.'</div>';
			}

			if(!$max_items || $i < $max_items-1){
				$rendered	.= $this->get_row_action($add_item, ['id'=>$id,	'title'=>'新增']);
			}
		}

		if($per_row){
			$style	.= $style ? ' ' : '';
			$style	.= 'width:'.($per_row * ($width+30)).'px;';
		}

		$style	= $style ? ' style="'.$style.'"' : '';
		$class	= 'items '.$item_type.'-list';
		$class	.= $sortable ? ' sortable' : '';

		return '<div class="'.$class.'"'.$style.'>'.$rendered.'</div>';
	}

	protected function is_filterable_column($column_name){
		$fields	= $this->get_filterable_fields_by_model();

		return $fields && !is_wp_error($fields) && in_array($column_name, $fields);
	}

	public function get_list_table(){
		ob_start();
		$this->list_table();
		$data	= ob_get_clean();

		if($this->bulk_actions){
			$data	= WPJAM_List_Table_Action::set_bulk_data_attr($data);
		}

		return $data;
	}

	public function list_table(){
		$this->views();

		echo '<form action="#" id="list_table_form" method="POST">';

		if($this->is_searchable()){
			$this->search_box('搜索', 'wpjam');
			echo '<br class="clear" />';
		}

		$this->display(); 

		echo '</form>';
	}

	public function page_load(){
		if(wp_doing_ajax()){
			add_action('wp_ajax_wpjam-list-table-action',	[$this, 'ajax_response']);
		}else{
			$result = $this->prepare_items();

			if(is_wp_error($result)){
				wpjam_admin_add_error($result);
			}
		}
	}

	public function page(){
		$class	= 'list-table';

		if($this->layout){
			$class	.= ' layout-'.$this->layout;
		}

		echo '<div class="'.$class.'">'.$this->get_list_table().'</div>';

		return true;
	}

	public function ajax_response(){
		$referer	= wpjam_get_referer();

		if($referer){
			$referer_parts	= parse_url($referer);

			if($referer_parts['host'] == $_SERVER['HTTP_HOST']){
				$_SERVER['REQUEST_URI']	= $referer_parts['path'];
			}
		}else{
			wpjam_send_json(['errcode'=>'invalid_request', 'errmsg'=>'非法请求']);
		}

		$action_type	= wpjam_get_parameter('action_type', ['method'=>'POST', 'sanitize_callback'=>'sanitize_key']);

		if($action_type == 'query_item'){
			$id		= wpjam_get_parameter('id',	['method'=>'POST', 'default'=>'']);
			$data	= $this->get_single_row($id);

			wpjam_send_json(['id'=>$id, 'data'=>$data,	'type'=>'add']);
		}elseif($action_type == 'query_items'){
			foreach(wpjam_get_data_parameter() as $key=>$value){
				$_REQUEST[$key]	= $value;
			}

			$result	= $this->prepare_items();

			if(is_wp_error($result)){
				wpjam_send_json($result);
			}

			wpjam_send_json(['data'=>$this->get_list_table(), 'type'=>'list']);
		}

		$list_action	= wpjam_get_parameter('list_action', ['method'=>'POST']);
		$object			= $this->get_action_object($list_action);

		if(!$object){
			wpjam_send_json(['errcode'=>'invalid_action', 'errmsg'=>'非法操作']);
		}

		$id		= wpjam_get_parameter('id',		['method'=>'POST', 'default'=>'']);
		$ids	= wpjam_get_parameter('ids',	['method'=>'POST', 'sanitize_callback'=>'wp_parse_args', 'default'=>[]]);
		$bulk	= wpjam_get_parameter('bulk',	['method'=>'POST', 'sanitize_callback'=>'intval']);

		$verified	= $object->verify($action_type, ['id'=>$id, 'bulk'=>$bulk, 'ids'=>$ids]);

		if(is_wp_error($verified)){
			wpjam_send_json($verified);
		}

		if($action_type != 'form' && $bulk === 2){
			$bulk	= 0;
		}
		
		$response	= ['page_title'=>$object->page_title, 'list_action'=>$list_action, 'type'=>$object->response, 'bulk'=>$bulk, 'ids'=>$ids, 'id'=>$id];
		$form_args	= ['action_type'=>$action_type, 'response_type'=>$object->response, 'bulk'=>$bulk, 'ids'=>$ids, 'id'=>$id];

		$data	= wpjam_get_data_parameter();

		if($action_type == 'form'){
			$ajax_form	= $this->ajax_form($list_action, array_merge($form_args, ['data'=>$data]));

			if(is_wp_error($ajax_form)){
				wpjam_send_json($ajax_form);
			}

			wpjam_send_json(array_merge($response, ['form'=>$ajax_form, 'type'=>'form', 'width'=>($object->width ?: 720)]));
		}elseif($action_type == 'direct'){
			if($bulk){
				$result	= $object->callback($ids); 
			}else{
				if(in_array($list_action, ['move', 'up', 'down'])){
					$result	= $object->callback($id, $data);
				}else{
					$result	= $object->callback($id);

					if($list_action == 'duplicate'){
						$id = $result;
					}
				}
			}
		}elseif($action_type == 'submit'){
			if($object->response == 'form'){
				$form_args['data']	= $data;

				$result	= null;
			}else{
				$form_args['data']	= wpjam_get_defaults_parameter();

				$id_or_ids	= $bulk ? $ids : $id;
				$fields		= $this->get_fields($list_action, $id_or_ids, ['include_prev'=>true]);

				if($fields){
					$data	= wpjam_validate_fields_value($fields, $data);

					if(is_wp_error($data)){
						wpjam_send_json($data);
					}
				}

				$submit_name	= wpjam_get_parameter('submit_name',	['method'=>'POST', 'default'=>$object->name]);
				$submit_text	= $object->get_submit_text($id, $submit_name);

				if(!$submit_text){
					wpjam_send_json(['errcode'=>'invalid_submit_text', 'errmsg'=>'该操作不能提交']);
				}

				$response['type']	= $submit_text['response'];

				$result	= $object->callback($id_or_ids, $data, $submit_name);
			}
		}

		if($result && is_wp_error($result)){
			wpjam_send_json($result);
		}

		$result_as_response	= is_array($result) && (
			isset($result['type']) || isset($result['bulk']) || isset($result['ids']) || isset($result['id']) || isset($result['items'])
		);

		if($result_as_response){
			$response	= array_merge($response, $result);

			$bulk	= $response['bulk'] ?? $bulk;
			$ids	= $response['ids'] ?? $ids;
			$id		= $response['id'] ?? $id;
		}else{
			if(in_array($response['type'], ['add', 'duplicate']) || in_array($list_action, ['add', 'duplicate'])){
				if(is_array($result)){
					$dates	= $result['dates'] ?? $result;

					if(is_array(current($dates)) && isset(current($dates)[$this->primary_key])){
						$id	= current($dates)[$this->primary_key];
					}else{
						wpjam_send_json(['errcode'=>'invalid_id', '无效的ID']);
					}
				}else{
					$id		= $result;
				}
			}
		}

		$data	= '';

		$form_required	= true;

		if($response['type'] == 'append'){
			$response['data']	= $result;
			$response['width']	= $object->width ?: 720;

			wpjam_send_json($response);
		}elseif($response['type'] == 'redirect'){
			if(is_string($result)){
				$response['url']	= $result;
			}

			wpjam_send_json($response);
		}elseif(in_array($response['type'], ['delete', 'move', 'up', 'down', 'form'])){
			if($this->layout == 'calendar'){
				$data	= $this->render_dates($result);
			}
		}elseif($response['type'] == 'items' && isset($response['items'])){
			foreach($response['items'] as $id => &$response_item){
				$response_item['id']	= $id;

				if($response_item['type'] == 'delete'){
					$form_required	= false;
				}elseif($response_item['type'] != 'append'){
					if(!is_blank($id)){
						$response_item['data']	= $this->get_single_row($id);
					}
				}
			}

			unset($response_item);
		}elseif($response['type'] == 'list'){
			if(in_array($list_action, ['add', 'duplicate'])){
				$response['id']	= $id;
			}

			$result	= $this->prepare_items();

			if(is_wp_error($result)){
				wpjam_send_json($result);
			}

			$data	= $this->get_list_table();
		}else{
			if($bulk){
				$this->get_by_ids_by_model($ids);

				$data	= [];

				foreach($ids as $id){
					if(!is_blank($id)){
						$data[$id]	= $this->get_single_row($id);
					}
				}
			}else{
				if($this->layout == 'calendar'){
					$data	= $this->render_dates($result);
				}else{
					if(!$result_as_response && in_array($response['type'], ['add', 'duplicate'])){
						$response['id']	= $form_args['id'] = $id;
					}

					if(!is_blank($id)){
						$data	= $this->get_single_row($id);
					}
				}
			}
		}

		$response['layout']	= $this->layout;
		$response['data']	= $data;

		if($object->response != 'form'){
			if($result && is_array($result) && !empty($result['errmsg']) && $result['errmsg'] != 'ok'){ // 有些第三方接口返回 errmsg ： ok
				$response['errmsg'] = $result['errmsg'];
			}elseif($action_type == 'submit'){
				$response['errmsg'] = $submit_text['text'].'成功';
			}
		}

		if($action_type == 'submit'){
			if($response['type'] == 'delete'){
				$response['dismiss']	= true;
			}else{
				if($object->next){
					$response['next']		= $object->next;
					$response['page_title']	= $object->get_next_action()->page_title;

					if($response['type'] == 'form'){
						$response['errmsg']	= '';
					}
				}elseif($object->dismiss){
					$response['dismiss']	= true;
					$form_required			= false;
				}

				if($form_required){
					$ajax_form	= $this->ajax_form($list_action, $form_args);

					if(is_wp_error($ajax_form)){
						wpjam_send_json($ajax_form);
					}

					$response['form']	= $ajax_form;
				}
			}
		}

		wpjam_send_json($response);
	}

	public function ajax_form($list_action, $args=[]){
		$object			= $this->get_action_object($list_action);
		$prev_action	= null;

		if($args['action_type'] == 'submit' && $object->next){
			if($object->response == 'form'){
				$prev_action	= $object;	
			}

			$list_action	= $object->next;
			$object			= $object->get_next_action();
		}

		$data		= [];
		$bulk		= $args['bulk'];

		$id			= $bulk ? 0 : $args['id'];
		$id_or_ids	= $bulk ? $args['ids'] : $id;
		$fields		= $this->get_fields($list_action, $id_or_ids);

		if(is_wp_error($fields)){
			return $fields;
		}

		$fields_args	= ['id'=>$id];

		if(!$bulk){
			if($id && ($args['action_type'] != 'submit' || $args['response_type'] != 'form')){
				$data	= $object->get_data($id, $fields);

				if(is_wp_error($data)){
					return $data;
				}
			}

			if($object->value_callback){
				$fields_args['value_callback']	= $object->value_callback;
			}elseif(method_exists($this->model, 'value_callback')){
				$fields_args['value_callback']	= [$this->model, 'value_callback'];
			}
		}

		$form_fields	= wpjam_fields($fields, array_merge($fields_args, ['data'=>wp_parse_args($data, $args['data']), 'echo'=>false]));
		$submit_button	= '';

		if($object->prev && !$prev_action){
			$prev_action	= $object->get_prev_action();
		}

		if($prev_action){
			$submit_button	.= '<input type="button" class="list-table-action button large" '.$prev_action->generate_data_attr($args).' value="返回">&emsp;';
		}

		if($object->next && $object->response == 'form'){
			$submit_button	.= get_submit_button('下一步', 'primary', 'next', false);
		}else{
			foreach($object->get_submit_text($id) as $submit_key => $submit_item){
				$submit_class	= $submit_item['class'];
				$submit_button	.= get_submit_button($submit_item['text'], $submit_class, $submit_key, false).'&emsp;';
			}
		}

		$submit_button	= $submit_button ? '<p class="submit">'.$submit_button.'</p>' : '';

		$data_attrs		= $object->generate_data_attr(array_merge($args, ['type'=>'form']));

		return '<form method="post" id="list_table_action_form" action="#" '.$data_attrs.'>'.$form_fields.$submit_button.'</form>';
	}

	public function get_fields($key='', $id=0, $args=[]){
		$object	= $this->get_action_object($key);
		$fields	= $object ? $object->get_fields($id, $args) : [];

		if(is_wp_error($fields)){
			return $fields;
		}

		return $this->filter_fields($fields, $key, $id);
	}

	protected function filter_fields($fields, $key, $id){
		$primary_key	= $this->primary_key;

		if($primary_key && isset($fields[$primary_key]) && !in_array($key, ['add', 'duplicate'])){
			$fields[$primary_key]['type']	= 'view';
		}

		return $fields;
	}

	protected function get_bulk_actions(){
		return $this->bulk_actions;
	}

	protected function get_table_classes() {
		$classes = parent::get_table_classes();

		return $this->fixed ? $classes : array_diff($classes, ['fixed']);
	}

	public function get_singular(){
		return $this->singular;
	}

	protected function get_primary_column_name(){
		$primary_column	= $this->primary_column;

		if($this->columns && (!$primary_column || !isset($this->columns[$primary_column]))){
			return array_key_first($this->columns);
		}

		return $primary_column;
	}

	protected function handle_row_actions($item, $column_name, $primary){
		return ($primary === $column_name && !empty($item['row_actions'])) ? $this->row_actions($item['row_actions'], false) : '';
	}

	public function row_actions($actions, $always_visible=true){
		return parent::row_actions($actions, $always_visible);
	}

	public function get_per_page(){
		if($this->per_page && is_numeric($this->per_page)){
			return $this->per_page;
		}

		$option		= $this->screen->get_option('per_page', 'option');
		$default	= $this->screen->get_option('per_page', 'default') ?: 50;

		return $option ? $this->get_items_per_page($option, $default) : $default;
	}

	public function prepare_items(){
		$per_page	= $this->get_per_page();
		$offset		= ($this->get_pagenum()-1) * $per_page;

		if(method_exists($this->model, 'query_data')){
			$result	= $this->query_data_by_model(['number'=>$per_page, 'offset'=>$offset]);
		}else{
			$result	= $this->query_items_by_model($per_page, $offset);
		}

		if(is_wp_error($result)){
			return $result;
		}

		$this->items	= $result['items'] ?? [];
		$total_items	= $result['total'] ?? count($this->items);

		if($total_items){
			$this->set_pagination_args(['total_items'=>$total_items,	'per_page'=>$per_page]);
		}

		return true;
	}

	public function get_columns(){
		if($this->bulk_actions){
			return array_merge(['cb'=>'checkbox'], $this->columns);
		}

		return $this->columns;
	}

	public function get_sortable_columns(){
		return $this->sortable_columns;
	}

	public function get_views(){
		return $this->get_views_by_model() ?: [];
	}

	public function extra_tablenav($which='top'){
		$this->extra_tablenav_by_model($which);

		do_action(wpjam_get_filter_name($this->plural, 'extra_tablenav'), $which);

		if($which == 'top'){
			foreach($this->overall_actions as $action){
				$row_action	= $this->get_row_action($action, ['class'=>'button-primary button']);

				echo $row_action ? '<div class="alignleft actions overallactions">'.$row_action.'</div>' : '';
			}
		}
	}

	public function print_column_headers($with_id=true) {
		foreach(['orderby', 'order'] as $key){
			$value	= wpjam_get_data_parameter($key);

			if($value){
				$_GET[$key] = $value;
			}
		}

		parent::print_column_headers($with_id);
	}

	public function is_searchable(){
		if(isset($this->search)){
			return $this->search;
		}else{
			$fields	= $this->get_searchable_fields_by_model();

			return $fields && !is_wp_error($fields);
		}
	}

	public function current_action(){
		return wpjam_get_parameter('list_action', ['method'=>'REQUEST', 'default'=>parent::current_action()]);
	}
}

class WPJAM_Left_List_Table extends WPJAM_List_Table{
	protected $_left_pagination_args = [];

	public function get_col_left(){
		ob_start();
		$this->col_left();
		return ob_get_clean();
	}

	public function col_left(){
		$result	= $this->col_left_by_model();

		if(is_wp_error($result)){
			wp_die($result);
		}

		if($result && is_array($result)){
			$this->set_left_pagination_args($result);
		}

		echo '<div class="tablenav bottom" >';

		$this->left_pagination();

		echo '</div>';
	}

	public function set_left_pagination_args($args){
		$args = wp_parse_args($args, [
			'total_items'	=> 0,
			'total_pages'	=> 0,
			'per_page'		=> 10,
		]);

		if(!$args['total_pages'] && $args['per_page'] > 0){
			$args['total_pages']	= ceil($args['total_items']/$args['per_page']);
		}

		$this->_left_pagination_args = $args;
	}

	public function left_pagination(){
		if(!$this->_left_pagination_args){
			return;
		}

		$total_items	= $this->_left_pagination_args['total_items'];

		if(!$total_items){
			return;
		}

		$total_pages	= $this->_left_pagination_args['total_pages'];
		$current		= wpjam_get_data_parameter('left_paged') ?: 1;

		$disable_prev	= false;
		$disable_next	= false;

		if(1 == $current){
			$disable_prev	= true;
		}

		if($total_pages == $current){
			$disable_next	= true;
		}

		$page_links	= [];

		if($disable_prev){
			$page_links[]	= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
		}else{
			$page_links[]	= sprintf(
				"<a class='prev-page button left-pagination' href='javascript:;' data-left_paged='%d'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				max(1, $current - 1),
				__('Previous page'),
				'&lsaquo;'
			);
		}

		$html_current_page	= sprintf("<span class='current-page'>%s</span>", $current);
		$html_total_pages	= sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[]		= "<span class='tablenav-paging-text'>".$html_current_page.'/'.$html_total_pages.'</span>';

		if($disable_next){
			$page_links[]	= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
		} else {
			$page_links[]	= sprintf("<a class='next-page button left-pagination' href='javascript:;' data-left_paged='%d'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				min($total_pages, $current + 1),
				__('Next page'),
				'&rsaquo;'
			);
		}

		if($total_pages > 2){
			$page_links[]	= sprintf(
				"&emsp;<span class='paging-input'><input class='current-page' id='left-current-page-selector' type='text' name='paged' value='%d' size='%d' aria-describedby='table-paging' /><a class='button left-pagination goto' href='javascript:;'>&#10132;</a></span>",
				$current,
				strlen($total_pages)
			);
		}

		$output		= "\n<span class='left-pagination-links'>".join("\n", $page_links).'</span>';
		$page_class = $total_pages < 2 ? ' one-page' : '';

		echo "<div class='tablenav-pages{$page_class}'>$output</div>";
	}

	public function page(){
		echo '<div id="col-container" class="wp-clearfix">';

		echo '<div id="col-left">';
		echo '<div class="col-wrap left">';
		echo $this->get_col_left();
		echo '</div>';
		echo '</div>';

		echo '<div id="col-right">';
		echo '<div class="list-table col-wrap">';
		echo $this->get_list_table();
		echo '</div>';
		echo '</div>';

		echo '</div>';

		return true;
	}

	public function ajax_response(){
		$action_type	= wpjam_get_parameter('action_type', ['method'=>'POST', 'sanitize_callback'=>'sanitize_key']);

		if($action_type == 'left'){
			$result	= $this->prepare_items();

			if(is_wp_error($result)){
				wpjam_send_json($result);
			}

			wpjam_send_json(['data'=>$this->get_list_table(), 'left'=>$this->get_col_left(), 'type'=>'left']);
		}

		parent::ajax_response();
	}
}

class WPJAM_Calendar_List_Table extends WPJAM_List_Table{
	private $year;
	private $month;

	public function prepare_items(){
		$this->year		= (int)wpjam_get_data_parameter('year') ?: current_time('Y');
		$this->month	= (int)wpjam_get_data_parameter('month') ?: current_time('m');

		if($this->month > 12){
			$this->month	= 12;
		}elseif($this->month < 1){
			$this->month	= 1;
		}

		if($this->year > 2200){
			$this->year	= 2200;
		}elseif($this->year < 1970){
			$this->year	= 1970;
		}

		$items	= $this->query_dates_by_model($this->year, zeroise($this->month, 2));

		if(is_wp_error($items)){
			return $items;
		}

		$this->items	= $items;

		return true;
	}

	public function render_date($raw_item, $date){
		$day	= explode('-', $date)[2];
		$class	= 'day';

		if($date == current_time('Y-m-d')){
			$class	.= ' today';
		}

		$row_actions	= [];

		if($raw_item && !wp_is_numeric_array($raw_item)){
			$row_actions	= $this->get_row_actions($raw_item[$this->primary_key]);
		}else{
			$row_actions	= ['add'=>$this->get_row_action('add', ['data'=>['date'=>$date]])];
		}

		$out	= [];

		foreach($row_actions as $action => $link){
			if($link){
				$out[] = "<span class='$action'>$link</span>";
			}
		}

		$out	= implode(' ', $out);
		$out	= '<div class="row-actions alignright">'.$out.'</div>';

		$item	= '';
		$item	.= '<div class="date-meta">';
		$item	.= '<span class="'.$class.'">'.$day.'</span>'."\n";
		$item	.= $out;
		$item	.= '</div>'."\n";
		$item	.= '<div class="date-content">'."\n";
		$item	.= $this->render_date_by_model($raw_item, $date)."\n";
		$item	.= '</div>'."\n";

		return $item;
	}

	public function render_dates($result){
		$dates	= $result['dates'] ?? $result;
		$data	= [];

		foreach($dates as $date => $item){
			$data[$date]	= $this->render_date($item, $date);
		}

		return $data;
	}

	public function display(){
		global $wp_locale;

		$year		= $this->year;
		$month		= zeroise($this->month, 2);
		$month_ts	= mktime(0, 0, 0, $this->month, 1, $this->year);	// 每月开始的时间戳
		$week_start	= (int)get_option('start_of_week');
		$week_pad	= calendar_week_mod(date('w', $month_ts) - $week_start);

		$this->display_tablenav('top');

		$weekdays	= '';

		for($wd_count = 0; $wd_count <= 6; $wd_count++){
			$weekday	= ($wd_count + $week_start) % 7;
			$class		= in_array($weekday, [0, 6]) ? 'weekend' : 'weekday';
			$weekday	= $wp_locale->get_weekday($weekday);
			
			$weekdays	.= '<th scope="col" class="'.$class.'" title="'.$weekday.'">'.$wp_locale->get_weekday_abbrev($weekday).'</th>'."\n";
		}

		echo '<table id="wpjam_calendar" class="widefat fixed" cellpadding="10" cellspacing="0">'."\n";

		// echo '<caption>'.sprintf(__('%1$s %2$d'), $wp_locale->get_month($this->month), $this->year).'</caption>'."\n";
		
		echo '<thead>'."\n";
		echo '<tr>'."\n".$weekdays.'</tr>'."\n";
		echo '</thead>'."\n";

		echo '<tfoot>'."\n";
		echo '<tr>'."\n".$weekdays.'</tr>'."\n";
		echo '</tfoot>'."\n";

		echo '<tbody id="the-list" data-wp-lists="list:'.$this->singular.'">'."\n";

		echo '<tr>'."\n";

		if(0 != $week_pad){
			echo '<td colspan="'.(int)$week_pad.'" class="pad">&nbsp;</td>';
		}

		$new_row	= false;
		$days		= date('t', $month_ts);

		for($day=1; $day<=$days; ++$day){
			if($new_row){
				echo '</tr>'."\n";
				echo '<tr>'."\n";

				$new_row	= false;
			}

			$date	= $year.'-'.$month.'-'.zeroise($day, 2);
			$item	= $this->items[$date] ?? [];

			$class	= in_array($week_pad+$week_start, [0, 6, 7]) ? 'weekend' : 'weekday';

			echo '<td id="date_'.$date.'"" class="'.$class.'">'."\n";

			echo $this->render_date($item, $date);

			echo '</td>'."\n";

			$week_pad++;

			if($week_pad%7 == 0){
				$new_row 	= true;
				$week_pad	= 0;
			}
		}

		if($week_pad > 1){
			echo '<td class="pad" colspan="'.(int)(7-$week_pad).'">&nbsp;</td>'."\n";
		}

		echo '</tr>'."\n";
		echo '</tbody>'."\n";
		echo '</table>'."\n";

		$this->display_tablenav('bottom');
	}

	public function extra_tablenav($which='top'){
		global $wp_locale;

		if($which == 'top'){
			echo '<span style="font-size:x-large; padding:30px 0; text-align: center;">'.sprintf(__('%1$s %2$d'), $wp_locale->get_month($this->month), $this->year).'</span>';
		}

		parent::extra_tablenav($which);
	}

	public function pagination($which){
		global $wp_locale;

		if($this->month == 1){
			$prev_year	= $this->year - 1;
			$prev_month	= 12;
		}else{
			$prev_year	= $this->year;
			$prev_month	= $this->month - 1;
		}

		if($this->month == 12){
			$next_year	= $this->year + 1;
			$next_month	= 1;
		}else{
			$next_year	= $this->year;
			$next_month	= $this->month + 1;
		}

		$prev_text	= '<span class="screen-reader-text">'.sprintf(__('%1$s %2$d'), $wp_locale->get_month($prev_month), $prev_year).'</span><span aria-hidden="true">&lsaquo;</span>';
		$prev_text	= $this->get_filter_link(['year'=>$prev_year,'month'=>$prev_month], $prev_text, 'prev-month button');
		$next_text	= '<span class="screen-reader-text">'.sprintf(__('%1$s %2$d'), $wp_locale->get_month($next_month), $next_year).'</span><span aria-hidden="true">&rsaquo;</span>';
		$next_text	= $this->get_filter_link(['year'=>$next_year,'month'=>$next_month], $next_text, 'next-month button');

		$this_text	= $this->get_filter_link(['year'=>current_time('Y'),'month'=>current_time('m')], '&emsp;今日&emsp;', 'current-month button');

		echo '<div class="tablenav-pages">'."\n";
		echo '<span class="pagination-links">'."\n";
		echo $prev_text." \n".$this_text." \n".$next_text."\n";
		echo '</span">'."\n";
		echo '</div>'."\n";
	}

	public function get_views(){
		return [];
	}

	public function get_bulk_actions(){
		return [];
	}

	public function is_searchable(){
		return false;
	}
}

class WPJAM_List_Table_Setting extends WPJAM_Register{
	public static function get_object($name){
		$object	= self::get($name);

		if(!$object){
			if(wpjam_get_plugin_page_setting('model', true)){
				$args	= wpjam_get_plugin_page_setting('', true);
			}else{
				$args	= apply_filters(wpjam_get_filter_name($name, 'list_table'), []);

				if(!$args){
					return new WP_Error('list_table_unregistered', 'List Table 未注册');
				}
			}

			$object	= self::register($name, $args);
		}
		
		if(!$object->model || !class_exists($object->model)){
			return  new WP_Error('invalid_list_table_model', 'List Table 的 Model '.$object->model.'未定义或不存在');
		}
		
		foreach(['admin_head', 'admin_footer'] as $admin_hook){
			if(method_exists($object->model, $admin_hook)){
				add_action($admin_hook,	[$object->model, $admin_hook]);
			}
		}

		$args	= wp_parse_args($object->to_array(), ['primary_key'=>'id', 'name'=>$name, 'singular'=>$name, 'plural'=>$name.'s']);

		if($object->layout == 'left' || $object->layout == '2'){
			$args['layout']	= 'left';

			return new WPJAM_Left_List_Table($args);
		}elseif($object->layout == 'calendar'){
			return new WPJAM_Calendar_List_Table($args);
		}else{
			return new WPJAM_List_Table($args);
		}
	}
}

class WPJAM_List_Table_Action extends WPJAM_Register{
	public function get_fields($id, $args=[]){
		if($this->direct){
			return [];
		}

		$fields	= $this->fields;

		if($fields && is_callable($fields)){
			$fields	= call_user_func($fields, $id, $this->name);
		}

		if(!$fields && method_exists($this->model, 'get_fields')){
			$fields	= call_user_func([$this->model, 'get_fields'], $this->name, $id) ?: [];
		}

		if(is_wp_error($fields)){
			return $fields;
		}

		$fields	= is_array($fields) ? $fields : [];

		if(!empty($args['include_prev'])){
			if($this->prev){
				$prev_fields	= $this->get_prev_action()->get_fields($id, $args);

				if(is_wp_error($prev_fields)){
					return $prev_fields;
				}

				$fields	= array_merge($fields, $prev_fields);
			}
		}

		return $fields;
	}

	public function get_data($id, $fields=[]){
		$data_callback	= $this->data_callback;

		if($data_callback && is_callable($data_callback) && $fields){
			return call_user_func($data_callback, $id, $this->name, $fields);
		}

		if(method_exists($this->model, 'get') || method_exists($this->model, '__callStatic')){
			$data	= call_user_func([$this->model, 'get'], $id);

			return $data ?: new WP_Error('invalid_id', '无效的ID');
		}

		return new WP_Error('undefined_method', $this->model.'未定义「get」方法');
	}

	public function get_submit_text($id, $name=null){
		if($name){
			$submit_text	= $this->get_submit_text($id);

			return $submit_text[$name] ?? [];
		}

		if(isset($this->submit_text)){
			$submit_text	= $this->submit_text;

			if(!$submit_text){
				return [];
			}elseif(is_callable($submit_text)){
				$submit_text	= call_user_func($submit_text, $id, $this->name);
			}
		}else{
			$submit_text = wp_strip_all_tags($this->title) ?: $this->page_title;
		}

		if(!is_array($submit_text)){
			$submit_text	= [$this->name=>$submit_text];
		}

		foreach($submit_text as &$item){
			$item	= is_array($item) ? $item : ['text'=>$item];
			$item	= wp_parse_args($item, ['response'=>$this->response, 'class'=>'primary']);
		}

		return $submit_text;
	}

	public function get_next_action(){
		return $this->get_adjacent_action('next');
	}

	public function get_prev_action(){
		return $this->get_adjacent_action('prev');
	}

	public function get_adjacent_action($key){
		$value	= $this->$key;

		return $value ? self::get($value) : null;
	}

	public function callback($id=0, $data=null, $submit_name='', &$response_type=''){
		$bulk		= is_array($id); 
		$cb_key		= $bulk ? 'bulk_callback' : 'callback'; 
		$callback	= $this->$cb_key;

		if($submit_name){
			$submit_text	= $this->get_submit_text($id, $submit_name);

			if(!empty($submit_text[$cb_key])){
				$callback	= $submit_text[$cb_key];
			}
		}

		if($bulk){
			if(!$callback && method_exists($this->model, 'bulk_'.$this->name)){
				$callback	= [$this->model, 'bulk_'.$this->name];
			}

			if($callback){
				if(!is_callable($callback)){
					return new WP_Error('invalid_bulk_list_action', '定义的批量操作回调函数无效');
				}

				$result	= call_user_func($callback, $id, $data, $this->name, $submit_name);

				return is_null($result) ? new WP_Error('null_list_action_return', '操作回调函数没有正确返回') : $result;
			}else{
				$return	= [];

				foreach($id as $_id){
					$result	= $this->callback($_id, $data, $submit_name);

					if(is_wp_error($result)){
						return $result;
					}elseif(is_array($result)){
						$return	= wpjam_array_merge($return, $result);
					}
				}

				return $return ?: $result;
			}
		}else{
			if($callback){
				if(!is_callable($callback)){
					return new WP_Error('invalid_list_action', '定义的操作回调函数无效');
				}

				$id_parameter	= true;

				if($this->overall){
					$id_parameter	= false;
				}elseif($this->response == 'add' && !is_null($data)){
					if(is_array($callback)){
						$reflection	= new ReflectionMethod($callback[0], $callback[1]);
					}else{
						$reflection	= new ReflectionFunction($callback);
					}

					$parameters	= $reflection->getParameters();

					if(count($parameters) == 1 || $parameters[0]->name == 'data'){
						$id_parameter	= false;
					}
				}

				if($id_parameter){
					$result	= call_user_func($callback, $id, $data, $this->name, $submit_name);
				}else{
					$result	= call_user_func($callback, $data, $this->name, $submit_name);
				}

				return is_null($result) ? new WP_Error('null_list_action_return', '操作回调函数没有正确返回') : $result;
			}else{
				return $GLOBALS['wpjam_list_table']->call_model_list_action($id, $data, $this->name);
			}
		}
	}

	public function get_row_action($args=[], $layout=''){
		if($layout == 'calendar' && !$this->calendar){
			return '';
		}

		$args	= wp_parse_args($args, ['id'=>0, 'data'=>[], 'class'=>'', 'style'=>'', 'title'=>'', 'fallback'=>false]);

		if(!$this->show_if($args['id'])){
			return '';
		}

		if(!$this->current_user_can($args['id'])){
			if($args['fallback'] === true ){
				return $args['title'];
			}elseif($args['fallback']){
				return $args['fallback'];
			}else{
				return '';
			}
		}

		$attr	= 'title="'.esc_attr($this->page_title).'"';
		$tag	= $args['tag'] ?? 'a';

		if($this->redirect){
			$class	= 'list-table-redirect';
			$tag	= 'a';
			$href	= str_replace('%id%', $args['id'], $this->redirect);
		}elseif($this->filter){
			$class	= 'list-table-filter';
			$item	= (array)$this->get_data($args['id']);
				
			$data	= $this->data ?: [];
			$data	= array_merge($data, wp_array_slice_assoc($item, wp_parse_list($this->filter)));

			$data	= wp_parse_args($args['data'], $data);
			$attr	.= $data ? ' data-filter=\''.wpjam_json_encode($data).'\'' : '';
		}else{
			$class	= in_array($this->response, ['move', 'move_item']) ? 'list-table-move-action' : 'list-table-action';
			$attr	.= ' '.$this->generate_data_attr($args);
		}

		if($tag == 'a'){
			$href	= $href ?? 'javascript:;';
			$attr	.= ' href="'.$href.'" ';
		}

		if($args['class']){
			$class	.= ' '.$args['class'];
		}

		$attr	.= ' class="'.$class.'" ';

		if($args['style']){
			$attr	.= ' style="'.esc_attr($args['style']).'" ';
		}

		if(!empty($args['dashicon'])){
			$title	= '<span class="dashicons dashicons-'.esc_attr($args['dashicon']).'"></span>';
		}elseif(!is_blank($args['title'])){
			$title	= $args['title'];
		}elseif($this->dashicon && empty($args['subtitle']) && ($layout == 'calendar' || !$this->title)){
			$title	= '<span class="dashicons dashicons-'.esc_attr($this->dashicon).'"></span>';
		}else{
			$title	= $this->title ?: $this->page_title;
		}

		$row_action	= '<'.$tag.' '.$attr.'>'.$title.'</'.$tag.'>'; 

		return empty($args['wrap']) ? $row_action : sprintf($args['wrap'], esc_attr($this->name), $row_action);
	}

	public function generate_data_attr($args=[]){
		$args	= wp_parse_args($args, ['type'=>'button', 'id'=>0, 'data'=>[], 'bulk'=>false, 'ids'=>[]]);
		
		$data_attrs	= [
			'action'	=> $this->name,
			'nonce'		=> $this->create_nonce($args['id'])
		];

		if($args['bulk']){
			$data_attrs['bulk']	= $this->bulk;
			$data_attrs['ids']	= $args['ids'];
		}else{
			$data_attrs['id']	= $args['id'];
		}

		if($args['type'] == 'button'){
			$data_attrs['direct']	= $this->direct;
			$data_attrs['confirm']	= $this->confirm;
		}else{
			$data_attrs['next']		= $this->next;
		}

		$defaults	= $this->data ?: [];
		$data		= wp_parse_args($args['data'], $defaults);

		$data_attrs['data']	= $data;

		return wpjam_data_attribute_string($data_attrs);
	}

	protected function show_if($id){
		$show_if	= $this->show_if;

		if($show_if){
			if(is_callable($show_if)){
				return call_user_func($show_if, $id, $this->name);
			}elseif(is_array($show_if) && $id){
				$data	= $this->get_data($id);

				if(!is_wp_error($data)){
					return wpjam_show_if($data, $show_if);
				}
			}
		}

		return true;
	}

	public function current_user_can($id=0){
		return ($this->capability == 'read' || current_user_can($this->capability, $id, $this->name));
	}

	public function verify($action_type, $args=[]){
		$nonce	= wpjam_get_parameter('_ajax_nonce',['method'=>'POST', 'default'=>'']);

		if($args['bulk']){
			if($action_type != 'form'){
				if(!$this->verify_nonce($nonce)){
					return new WP_Error('invalid_nonce', '非法操作');
				}
			}

			if($action_type != 'form' && $args['bulk'] === 2){
				if(!$this->current_user_can($args['id'])){
					return new WP_Error('bad_authentication', '无权限');
				}
			}else{
				foreach($args['ids'] as $_id){
					if(!$this->current_user_can($_id)){
						return new WP_Error('bad_authentication', '无权限');
					}
				}
			}	
		}else{
			if($action_type != 'form'){
				if(!$this->verify_nonce($nonce, $args['id'])){
					return new WP_Error('invalid_nonce', '非法操作');
				}
			}

			if(!$this->current_user_can($args['id'])){
				return new WP_Error('bad_authentication', '无权限');
			}
		}

		return true;
	}

	protected function verify_nonce($nonce, $id=''){
		return wp_verify_nonce($nonce, $this->get_nonce_action($id));
	}

	protected function create_nonce($id=''){
		return wp_create_nonce($this->get_nonce_action($id));
	}

	public function get_nonce_action($id=0){
		$key	= $id ? $this->name.'-'.$id : 'bulk_'.$this->name;
		
		return wpjam_get_nonce_action($key);
	}

	public static function set_bulk_data_attr($html){
		return preg_replace_callback('/<select name="action.*?>(.*?)<\/select>/is', function($matches){
			return preg_replace_callback('/<option value="(.*?)".*?<\/option>/is', function($sub_matches){
				$key	= $sub_matches[1];

				if($key && $key != -1){
					$object = self::get($key);

					if($object){
						return str_replace('<option value="'.$key.'"', '<option value="'.$key.'" '.$object->generate_data_attr(['bulk'=>true]).' ', $sub_matches[0]);
					}
				}

				return $sub_matches[0];
			}, $matches[0]);
		}, $html);
	}
}

class WPJAM_List_Table_Column extends WPJAM_Register{
	public function parse_args(){
		return wp_parse_args($this->args, ['type'=>'view', 'show_admin_column'=>true]);
	}

	public function callback($id, $value){
		$callback	= $this->column_callback ?: $this->callback;

		if($callback){
			if(is_callable($callback)){
				return call_user_func($callback, $id, $this->name, $value);
			}
		}

		if($this->options){
			if($this->type == 'checkbox' && is_array($value)){
				foreach($value as &$item){
					$item	= $this->parse_option_value($item);
				}

				return implode(',', $value);
			}else{
				return $this->parse_option_value($value);
			}
		}else{
			if($this->filterable){
				$value	= wpjam_get_list_table_filter_link([$this->name=>$value], $value);
			}

			return $value;
		}
	}

	public function parse_option_value($value){
		$option_value	= $this->options[$value] ?? $value;

		if(is_array($option_value)){
			$option_value	= $option_value['title'] ?? '';
		}

		if($this->filterable){
			$option_value =	wpjam_get_list_table_filter_link([$this->name=>$value], $option_value);
		}

		return $option_value;
	}

	public static function register_by_fields($fields, $sub_fields=false){
		foreach($fields as $key => $field){
			if(!empty($field['show_admin_column'])){
				$field	= wpjam_array_except($field, ['data_type', 'post_type', 'taxonomy']);
				self::register($key, wp_parse_args($field, ['order'=>10.5]));
			}

			if($sub_fields == false && $field['type'] == 'fieldset' && wpjam_array_get($field, 'fieldset_type') != 'array'){
				self::register_by_fields($field['fields'], true);
			}
		}
	}
}