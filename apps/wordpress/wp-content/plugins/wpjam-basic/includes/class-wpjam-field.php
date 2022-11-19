<?php
class WPJAM_Field{
	protected $_json_schema;
	protected $_field_group;
	protected $_field_name;
	protected $_fields_object;
	protected $field;

	private function __construct($field){
		$this->field	= $field;
	}

	public function __get($key){
		if($key == 'field'){
			return $this->field;
		}else{
			$value	= $this->field[$key] ?? null;

			if($key == 'show_in_rest'){
				return is_null($value) ? $this->is_editable() : $value;
			}elseif(in_array($key, ['min', 'max', 'minlength', 'maxlength', 'max_items', 'min_items'])){
				return is_numeric($value) ? $value : null;
			}

			return $value;
		}
	}

	public function __set($key, $value){
		if($key == 'field'){
			$this->field	= $value;
		}else{
			$this->field[$key]	= $value;
		}
	}

	public function __isset($key){
		return $this->$key !== null;
	}

	public function __unset($key){
		unset($this->field[$key]);
	}

	public function __call($method, $args){
		$postfix	= '';

		if(in_array($method, ['get_objects', 'get_show_if_keys', 'get_data', 'get_defaults'])){
			$postfix	= 'fields';
		}elseif(in_array($method, ['render_group', 'reset_group'])){
			$postfix	= 'group';
			$method		= wpjam_remove_postfix($method, '_group');
		}elseif(in_array($method, ['parse_by_name', 'wrap_by_name'])){
			$postfix	= 'name';
			$method		= str_replace('_by_name', '_value', $method);
		}elseif($method == 'get_top_name'){
			$postfix	= 'name';
			$method		= 'get_top';
		}

		if($postfix){
			return $this->call_by($postfix, $method, $args);
		}

		foreach(['json_schema', 'data_type', 'fields'] as $postfix){
			if(str_ends_with($method, '_by_'.$postfix)){
				$method	= wpjam_remove_postfix($method, '_by_'.$postfix);

				return $this->call_by($postfix, $method, $args);
			}
		}
	}

	public function call_by($postfix, $method, $args){
		if($postfix == 'fields'){
			$object	= $this->get_fields_object();
		}elseif($postfix == 'json_schema'){
			$object	= $this->get_json_schema('object');
		}elseif($postfix == 'data_type'){
			$object	= $this->has_data_type();

			if(!$object){
				return $args[0] ?? null;
			}

			$args[]	= $this->query_args;
		}elseif($postfix == 'name'){
			if(is_null($this->_field_name)){
				$this->_field_name	= new WPJAM_Field_Name($this->name);
			}

			$object	= $this->_field_name;
		}elseif($postfix == 'group'){
			if(is_null($this->_field_group)){
				$this->_field_group	= new WPJAM_Field_Group();
			}

			$object	= $this->_field_group;
		}

		return call_user_func([$object, $method], ...$args);
	}

	public function get_fields_object(){
		if(is_null($this->_fields_object)){
			$fields	= $this->fields ?: [];

			foreach($fields as $sub_key => &$sub_field){
				$sub_field	= $this->parse_sub_field($sub_field, $sub_key);
			}

			$this->_fields_object	= WPJAM_Fields::create($fields);
		}

		return $this->_fields_object;
	}

	public function get_json_schema($return=''){
		if(is_null($this->_json_schema)){
			$this->_json_schema	= WPJAM_JSON_Schema::create_by_field($this);
		}

		if($return == 'object'){
			return $this->_json_schema;
		}else{
			return $this->_json_schema->get_value();
		}
	}

	public function has_data_type(){
		return $this->data_type ? wpjam_get_data_type_object($this->data_type) : null;
	}

	public function parse_sub_field($sub_field, $sub_key){
		return array_merge($sub_field, ['key'=>$sub_key, 'sub_field'=>true]);
	}

	public function is_editable(){
		if($this->show_admin_column === 'only' || $this->disabled || $this->readonly){
			return false;
		}

		return true;
	}

	public function show_if($values){
		if($this->show_if && wpjam_show_if($values, $this->show_if) === false){
			return false;
		}

		return true;
	}

	public function get_display_title(){
		return $this->title.'「'.$this->key.'」';
	}

	public function validate($value, $validate=true){
		$title		= $this->get_display_title();
		$required	= $validate ? $this->required : false;

		if(is_null($value) && $required){
			return new WP_Error('value_required', $title.'的值不能为空');
		}

		if($this->validate_callback){
			_deprecated_argument('validate_callback', '6.0', '请使用 JSON Schema 进行数据验证');
		}

		$value	= $this->validate_value($value, $required);

		if(is_null($value) && $required){
			return new WP_Error('value_required', $title.'的值为空或无效');
		}

		$value	= $this->validate_by_json_schema($value, $title, $this);

		if(is_wp_error($value)){
			return $validate ? $value : null;
		}

		if($this->sanitize_callback){
			_deprecated_argument('sanitize_callback', '6.0', '请使用 JSON Schema 进行数据转义');
		}

		return $value;
	}

	protected function validate_value($value, $required){
		if(is_blank($value) && $required){
			$value	= null;
		}

		return $value ? $this->validate_value_by_data_type($value) : $value;
	}

	public function parse_json_schema(){
		$schema	= ['type'=>'string'];

		if($this->type == 'email'){
			$schema['format']	= 'email';
		}elseif($this->type == 'color'){
			// $schema['format']	= 'hex-color';	// 空白的时候也会报错
		}elseif($this->type == 'url'){
			$schema['format']	= 'uri';
		}elseif(in_array($this->type, ['number', 'range'])){
			if($this->step && ($this->step == 'any' || strpos($this->step, '.'))){
				$schema['type']	= 'number';
			}else{
				$schema['type']	= 'integer';
			}
		}elseif(in_array($this->type, ['radio', 'select'])){
			$schema['enum']	= $this->get_option_values();
		}elseif($this->type == 'checkbox'){
			if($this->options){
				$schema['type']		= 'array';
				$schema['items']	= ['type'=>'string',	'enum'=>$this->get_option_values()];
			}else{
				$schema['type']		= 'boolean';
			}
		}

		$_schema = $this->parse_json_schema_by_data_type();

		if($_schema){
			$schema	= array_merge($schema, $_schema);
		}

		return $schema;
	}

	public function get_option_values(){
		$values	= [];

		foreach($this->options as $opt_value => $opt_title){
			if(is_array($opt_title) && !empty($opt_title['options'])){
				$values		= array_merge($values, array_keys($opt_title['options']));
			}else{
				$values[]	= $opt_value;
			}
		}

		return $values;
	}

	public function get_default(){
		$show_in_rest	= $this->show_in_rest;

		if($show_in_rest && is_array($show_in_rest)){
			if(isset($show_in_rest['default'])){
				return $show_in_rest['default'];
			}
		}

		return $this->value;
	}

	public function value_callback($name, $args){
		$cb_arg	= $args['id'] ?? $args;

		if($this->value_callback){
			// _deprecated_argument('value_callback', '6.0', '请使用统一的 value callback');

			if(!is_callable($this->value_callback)){
				wp_die($this->key.'的 value_callback「'.$this->value_callback.'」无效');
			}

			return call_user_func($this->value_callback, $name, $cb_arg);
		}else{
			if(!empty($args['data']) && isset($args['data'][$name])){
				return $args['data'][$name];
			}elseif(!empty($args['value_callback'])){
				return call_user_func($args['value_callback'], $name, $cb_arg);
			}else{
				return null;
			}
		}
	}

	public function prepare_image($value, $item_type='url'){
		if($value){
			if($item_type != 'url'){
				$value	= wp_get_attachment_url($value);
			}

			if($value){
				$size	= $this->size ?: [];

				return wpjam_get_thumbnail($value, $size);
			}
		}

		return '';
	}

	protected function prepare_value($value){
		if($value && $this->parse_required){
			$value	= $this->parse_value_by_data_type($value);
		}

		return $value;
	}

	public function prepare($value){
		$title	= $this->get_display_title();
		$value	= $this->prepare_by_json_schema($value, $title, $this);

		return $this->prepare_value($value);
	}

	public function parse_value($args=[]){
		$name	= $this->get_top_name();
		$value	= $this->value_callback($name, $args);
		$value	= $this->parse_by_name($value);

		return is_null($value) ? $this->get_default() : $value;
	}

	protected function get_default_class($type=''){
		$type	= $type ?: $this->type;

		if(in_array($type, ['textarea', 'editor'], true)){
			return ['large-text'];
		}elseif(in_array($type, ['text', 'password', 'url', 'image', 'file'], true)){
			return ['regular-text'];
		}else{
			return [];
		}
	}

	protected function wrap_description(){
		if($this->type == 'checkbox' && !$this->options){
			return '&thinsp;'.$this->description;
		}elseif(in_array($this->type, ['img', 'color', 'checkbox', 'radio', 'textarea'])
			|| array_intersect($this->class, ['large-text','regular-text'])
		){
			return '<p class="description">'.$this->description.'</p>';
		}else{
			return '&ensp;<span class="description">'.$this->description.'</span>';
		}
	}

	public function render($args=[]){
		if(empty($args['is_add'])){
			$this->value	= $this->parse_value($args);
		}

		if(!empty($args['show_if_keys']) && in_array($this->key, $args['show_if_keys'])){
			$this->show_if_key	= true;
		}

		if(!empty($args['name'])){
			$this->name	=  WPJAM_Field_Name::combine($args['name'], $this->name);
		}

		if(is_null($this->value)){
			if($this->type == 'radio' && $this->options){
				$this->value	= array_key_first($this->options);
			}else{
				$this->value	= '';
			}
		}

		if(is_null($this->class)){
			$this->class	= $this->get_default_class();
		}elseif($this->class){
			if(!is_array($this->class)){
				$this->class	= explode(' ', $this->class);
			}
		}else{
			$this->class	= [];
		}

		if($this->description){
			$this->description	= $this->wrap_description();
		}else{
			$this->description	= '';
		}

		if($this->buttons){
			foreach($this->buttons as $btn_key => $btn){
				$btn_name	= $btn['name'] ?? $btn_key;
				$btn['key']	= $btn_name;

				$this->description	.= WPJAM_Field::create($btn)->render();
			}
		}

		return apply_filters('wpjam_field_html', $this->render_component(), $this->field);
	}

	protected function render_component(){
		return $this->render_element();
	}

	protected function render_element($args=[], $lable_attr=[]){
		$field_backup	= $this->field;
		$this->field	= array_merge($this->field, $args);

		$type			= $this->type;
		$value			= $this->value;
		$class			= $this->class;
		$options		= $this->options;
		$description	= $this->description;

		if($options && in_array($type, ['radio', 'checkbox'])){
			$args['required']	= false;
			$args['options']	= [];

			if($type == 'checkbox'){
				$args['name']	= $this->name.'[]';
			}

			if($type == 'checkbox' && !is_array($value) && !is_blank($value)){
				$value	= [$value];
			}

			$wrap_id	= $this->id.'_options';
			$sep		= $this->sep ?? '&emsp;';
			$items		= [];

			foreach($options as $opt_value => $opt_title){
				$opt_title	= $this->parse_option_title($opt_title, $lable_attr);

				if($type == 'checkbox'){
					$checked	= is_array($value) && in_array($opt_value, $value);
				}else{
					$checked	= $opt_value == $value;
				}

				if($checked){
					$lable_attr['class'][]	= 'checked';
				}

				$args['id']				= $this->id.'_'.$opt_value;
				$args['wrap_id']		= $wrap_id;
				$args['value']			= $opt_value;
				$args['checked']		= $checked ? 'checked' : false;
				$args['description']	= '&thinsp;'.$opt_title;

				$this->field	= $field_backup;

				$items[]	= $this->render_element($args, $lable_attr);
			}

			return '<div id="'.esc_attr($wrap_id).'"'.'>'.implode($sep, $items).'</div>'.$description;
		}else{
			if($type == 'checkbox'){
				if(!isset($args['checked'])){
					$this->checked	= $value == 1 ? 'checked' : false;

					$value	= 1;
				}
			}elseif($type == 'color'){
				$class[]	= 'color';
			}elseif($type == 'textarea'){
				$this->rows	= $this->rows ?: 6;
				$this->cols	= $this->cols ?: 50;
			}elseif($type == 'editor'){
				$this->rows	= $this->rows ?: 12;
				$this->cols	= $this->cols ?: 50;

				$type		= 'textarea';
				$class[]	= 'wpjam-editor';
			}

			foreach(['readonly', 'disabled'] as $attr_key){
				if($this->$attr_key){
					$class[]	= $attr_key;
				}
			}

			if($this->show_if_key || in_array($type, ['checkbox', 'radio', 'select'], true)){
				$class[]	= 'show-if-key';
			}

			if($this->has_data_type()){
				$query_label	= $this->query_label_by_data_type($value) ?: '';
				$query_class	= $class ? ' '.implode(' ', array_unique($class)) : '';

				if($query_label){
					$class[]		= 'hidden';
				}else{
					$query_class	.= ' hidden';
				}

				$class[]	= 'wpjam-autocomplete';

				$query_label = '<span class="wpjam-query-title'.$query_class.'">
				<span class="dashicons dashicons-dismiss"></span>
				<span class="wpjam-query-text">'.$query_label.'</span>
				</span>';
			}else{
				$query_label	= '';
			}

			$this->class	= $class ?  implode(' ', array_unique($class)) : '';

			$attr	= [];

			$data_keys	= ['key', 'wrap_id', 'data_type', 'query_args', 'creatable', 'max_items', 'min_items', 'unique_items', 'item_type', 'group', 'post_type','taxonomy', 'settings'];
			$keys		= ['type', 'value', 'default', 'options', 'description', 'title', 'sep', 'fields', 'buttons', 'sub_field', 'sub_i', 'parse_required', 'show_if_key', 'show_if', 'show_in_rest', 'sortable_column', 'show_admin_column', 'wrap_class'];

			foreach($this->field as $attr_key => $attr_value){
				if(in_array($attr_key, $data_keys)){
					if(is_array($attr_value)){
						$attr[]	= 'data-'.$attr_key.'=\''.esc_attr(wpjam_json_encode($attr_value)).'\'';
					}else{
						$attr[]	= 'data-'.$attr_key.'="'.esc_attr($attr_value).'"';
					}
				}elseif(!in_array($attr_key, $keys) && !str_ends_with($attr_key, '_callback')){
					if(is_object($attr_value) || is_array($attr_value)){
						trigger_error($attr_key.' '.var_export($attr_value, true).var_export($this->field, true));
					}elseif(is_int($attr_value) || $attr_value){
						$attr[]	= $attr_key.'="'.esc_attr($attr_value).'"';
					}
				}
			}

			asort($attr);
			$attr	= implode(' ', $attr);

			if($type == 'select'){
				$html	= '<select '.$attr.'>'.$this->render_select_options($options, $value).'</select>'.$description;
			}elseif($type == 'textarea'){
				$html	= '<textarea '.$attr.'>'.esc_textarea($value).'</textarea>'.$description;
			}else{
				$attr	.= 'type="'.($type == 'color' ? 'text' : $type).'" ';
				$html	= '<input value="'.esc_attr($value).'" '.$attr.' />'.$query_label;

				if(($lable_attr || $description) && $type != 'hidden'){
					$lable_attr	= self::generate_attr_string(array_merge($lable_attr, [
						'id'	=> 'label_'.$this->id,
						'for'	=> $this->id,
					]));

					if(in_array($type, ['color'])){
						$html	= '<label '.$lable_attr.'>'.$html.'</label>'.$description;
					}else{
						$html	= '<label '.$lable_attr.'>'.$html.$description.'</label>';
					}
				}
			}

			$this->field	= $field_backup;

			return $html;
		}
	}

	protected function render_select_options($options, $value){
		$items	= [];

		foreach($options as $opt_value => $opt_title){
			if(is_array($opt_title) && !empty($opt_title['options'])){
				$sub_opts	= wpjam_array_pull($opt_title, 'options');
			}else{
				$sub_opts	= [];
			}

			$opt_title	= $this->parse_option_title($opt_title, $attr);
			$attr		= self::generate_attr_string($attr);

			if($sub_opts){
				$items[]	= '<optgroup '.$attr.' label="'.esc_attr($opt_title).'" >'.$this->render_select_options($sub_opts, $value).'</optgroup>';
			}else{
				$items[]	= '<option '.$attr.' value="'.esc_attr($opt_value).'" '.selected($opt_value, $value, false).'>'.$opt_title.'</option>';
			}
		}

		return implode('', $items);
	}

	protected function parse_option_title($opt_title, &$attr){
		$attr		= ['class'=>[], 'data'=>[]];
		$opt_arr	= is_array($opt_title) ? $opt_title : [];

		foreach($opt_arr as $k => $v){
			if($k == 'title'){
				$opt_title	= $opt_arr['title'];
			}elseif($k == 'show_if'){
				if(isset($this->sub_i) && !isset($v['postfix'])){
					$v['postfix']	= '__'.$this->sub_i;
				}

				$show_if	= wpjam_parse_show_if($v);

				if($show_if){
					$attr['data']['show_if']	= $show_if;
					$attr['class'][]			= 'show-if-'.$show_if['key'];
				}
			}elseif($k == 'class'){
				$attr['class']		= array_merge($attr['class'], explode(' ', $v));
			}elseif(!is_array($v)){
				$attr['data'][$k]	= $v;
			}
		}

		return $opt_title;
	}

	public function wrap($html, $tag='div', $class=[]){
		if($this->type == 'hidden'){
			return $html;
		}

		$title	= $this->title;

		if($title && $this->type != 'fieldset'){
			$title	= '<label'.($this->sub_field ? ' class="sub-field-label"' : '').' for="'.esc_attr($this->id).'">'.$title.'</label>';
		}

		if($tag){
			$class	= (array)$class;

			if($this->wrap_class){
				$class[]	= $this->wrap_class;
			}

			if($this->sub_field){
				$class[]	= 'sub-field';
				$html		= '<div class="sub-field-detail">'.$html.'</div>';
			}

			$data		= [];
			$show_if	= $this->show_if;

			if($show_if){
				if(isset($this->sub_i) && !isset($show_if['postfix'])){
					$show_if['postfix']	= '__'.$this->sub_i;
				}

				$show_if	= wpjam_parse_show_if($show_if);

				if($show_if){
					$data['show_if']	= $show_if;

					$class[]	= 'show-if-'.$show_if['key'];
				}
			}

			$attr	= ['class'=>$class, 'data'=>$data, 'id'=>$tag.'_'.esc_attr($this->id)];

			if($tag == 'tr'){
				$attr['valign']	= 'top';

				$html	= $title ? '<th scope="row">'.$title.'</th><td>'.$html.'</td>' : '<td colspan="2">'.$html.'</td>';
			}elseif($tag == 'p'){
				$html	= $title ? $title.'<br />'.$html : $html;
			}else{
				$html	= $title.$html;
			}

			return '<'.$tag.' '.self::generate_attr_string($attr).'>'.$html.'</'.$tag.'>';
		}else{
			return $title.$html;
		}
	}

	public function callback($args=[]){
		_deprecated_function(__METHOD__, '6.0', '请使用 WPJAM_Field::Render');
		return $this->render($args);
	}

	public static function is_bool_attr($attr){
		return in_array($attr, ['allowfullscreen', 'allowpaymentrequest', 'allowusermedia', 'async', 'autofocus', 'autoplay', 'checked', 'controls', 'default', 'defer', 'disabled', 'download', 'formnovalidate', 'hidden', 'ismap', 'itemscope', 'loop', 'multiple', 'muted', 'nomodule', 'novalidate', 'open', 'playsinline', 'readonly', 'required', 'reversed', 'selected', 'typemustmatch'], true);
	}

	public static function generate_attr_string($attr){
		$string	= '';

		foreach($attr as $key => $value){
			if($value || $value === 0){
				if(is_array($value)){
					if($key == 'data'){
						$string	.= ' '.self::generate_data_attr_string($value, 'data');
					}elseif($key == 'class'){
						$string	.= 'class="'.implode(' ', array_filter($value)).'"';
					}
				}else{
					$string	.= ' '.$key.'="'.esc_attr($value).'"';
				}
			}
		}

		return $string;
	}

	public static function generate_data_attr_string($attr){
		$string	= '';

		foreach($attr as $key => $value){
			if($value || $value === 0){
				if(is_array($value)){
					if($key == 'data'){
						$value	= http_build_query($value);
					}else{
						$value	= wpjam_json_encode($value);
					}
				}else{
					$value	= esc_attr($value);
				}

				$string	.= ' data-'.$key.'=\''.$value.'\'';
			}
		}

		return $string;
	}

	public static function get_icon($name){
		$return	= '';

		foreach(wp_parse_list($name) as $name){
			if($name == 'move'){
				$return	.= ' <span class="dashicons dashicons-menu"></span>';
			}elseif($name == 'multiply'){
				$return .= '<span class="dashicons dashicons-no-alt"></span>';
			}elseif($name == 'del_btn'){
				$return	.= ' <a href="javascript:;" class="button wpjam-del-item">删除</a>';
			}elseif($name == 'del_icon' || $name == 'del_img'){
				$class	= $name == 'del_img' ? 'wpjam-del-img' : 'wpjam-del-item';
				$return	.= ' <a href="javascript:;" class="del-item-icon dashicons dashicons-no-alt '.$class.'"></a>';
			}
		}

		return $return;
	}

	public  static function print_media_templates(){
		$tmpls	= [
			'mu-action'	=> self::get_icon('del_btn,move'),
			'img'		=> '<img style="{{ data.img_style }}" src="{{ data.img_url }}{{ data.thumb_args }}" alt="" />',
			'mu-img'	=> '<img src="{{ data.img_url }}{{ data.thumb_args }}" /><input type="hidden" name="{{ data.name }}" value="{{ data.img_value }}" />',
			'mu-file'	=> '<input type="url" name="{{ data.name }}" class="regular-text" value="{{ data.img_url }}" />'
		];

		foreach($tmpls as $tmpl_id => $tmpl){
			echo self::generate_tmpl($tmpl_id, $tmpl);
		}

		echo '<div id="tb_modal"></div>';
	}

	public  static function generate_tmpl($tmpl_id, $tmpl){
		return "\n".'<script type="text/html" id="tmpl-wpjam-'.$tmpl_id.'">'.$tmpl.'</script>'."\n";
	}

	public static function create($args, $key=''){
		if(empty($args['key']) && $key){
			$args['key']	= $key;
		}

		if(is_numeric($args['key'])){
			trigger_error('Field 的 key「'.$args['key'].'」'.'不能为纯数字');
			return;
		}

		$args['options']	= $args['options'] ??  [];
		$args['options']	= wp_parse_args($args['options']);

		$args['type']		= $args['type'] ?? '';

		if(!$args['type']){
			$args['type']	= $args['options'] ? 'select' : 'text';
		}

		$total	= wpjam_array_pull($args, 'total');

		if($total && !isset($args['max_items'])){
			$args['max_items']	= $total;
		}

		if(!empty($args['data_type'])){
			if(in_array($args['data_type'], ['qq-video', 'qq_video'])) {
				$args['data_type']	= 'video';
			}

			$args['query_args']	= wpjam_parse_data_type_query_args($args);
		}

		$field	= [];

		foreach($args as $attr => $value){
			if(is_numeric($attr)){
				$attr	= $value = strtolower(trim($value));

				if(!self::is_bool_attr($attr)){
					continue;
				}
			}else{
				$attr	= strtolower(trim($attr));

				if(self::is_bool_attr($attr)){
					if(!$value){
						continue;
					}

					$value	= $attr;
				}
			}

			$field[$attr]	= $value;
		}

		foreach(['id', 'name'] as $k){
			if(empty($field[$k])){
				$field[$k]	= $field['key'];
			}
		}

		$type	= $field['type'];

		if($type  == 'fieldset'){
			return new WPJAM_Fieldset($field);
		}elseif($type == 'mu-fields'){
			return new WPJAM_MU_Fields_Field($field);
		}elseif($type == 'mu-text'){
			return new WPJAM_MU_Text_Field($field);
		}elseif(in_array($type, ['mu-img', 'mu-image', 'mu-file'], true)){
			return new WPJAM_MU_Image_Field($field);
		}elseif(in_array($type, ['img', 'image', 'file'], true)){
			return new WPJAM_Image_Field($field);
		}elseif($type == 'editor'){
			return new WPJAM_Editor_Field($field);
		}elseif(in_array($type, ['view', 'br', 'hr'], true)){
			return new WPJAM_View_Field($field);
		}

		return new WPJAM_Field($field);
	}
}

class WPJAM_FieldSet extends WPJAM_Field{
	public function parse_sub_field($sub_field, $sub_key){
		$sub_field	= parent::parse_sub_field($sub_field, $sub_key);

		if($this->fieldset_type == 'array'){
			$sub_name	= $sub_field['name'] ?? $sub_key;

			$sub_field['name']	= WPJAM_Field_Name::combine($this->name, $sub_name);
			$sub_field['key']	= $this->key.'_'.$sub_key;
		}else{
			if(!isset($sub_field['show_in_rest'])){
				$sub_field['show_in_rest']	= $this->show_in_rest;
			}
		}

		return $sub_field;
	}

	public function render($args=[]){
		$html	= '';

		foreach($this->get_objects() as $object){
			if($object->type == 'fieldset'){
				wp_die('fieldset 不允许内嵌 fieldset');
			}

			$html	.= $this->render_group($object->group);
			$html	.= $object->wrap($object->render($args));
		}

		$html	.= $this->reset_group();

		if($this->title){
			$html	= '<legend class="screen-reader-text"><span>'.$this->title.'</span></legend>'.$html;
		}

		if($this->description){
			$html	.= '<p class="description">'.$this->description.'</p>';
		}

		if($this->group){
			$html	= '<div class="field-group">'.$html.'</div>';
		}

		return $html;
	}
}

class WPJAM_Image_Field extends WPJAM_Field{
	public function parse_json_schema(){
		if($this->type == 'img' && $this->item_type != 'url'){
			return ['type'=>'integer'];
		}else{
			return ['type'=>'string',	'format'=>'uri'];
		}
	}

	protected function prepare_value($value){
		$item_type	= $this->type == 'img' ? $this->item_type : 'url';

		return $value ? $this->prepare_image($value, $item_type) : '';
	}

	protected function render_component(){
		if(!current_user_can('upload_files')){
			return '';
		}

		if($this->type == 'img'){
			$attr	= [];

			$attr['item_type']	= $this->item_type ?: '';
			$attr['uploader_id']= 'wpjam_uploader_'.$this->id;
			$attr['img_style']	= '';

			$size	= $this->size;

			unset($this->size);

			if($size){
				$size	= wpjam_parse_size($size);

				list($width, $height)	= wp_constrain_dimensions($size['width'], $size['height'], 600, 600);

				$attr['img_style']	.= $width > 2 ? 'width:'.($width/2).'px;' : '';
				$attr['img_style']	.= $height > 2 ? ' height:'.($height/2).'px;' : '';

				$attr['thumb_args']	= wpjam_get_thumbnail('', $size);
			}else{
				$attr['thumb_args']	= wpjam_get_thumbnail('', 400);
			}

			$class		= '';
			$img_tag	= '';

			if(!empty($this->value)){
				$img_url	= $attr['item_type'] == 'url' ? $this->value : wp_get_attachment_url($this->value);

				if($img_url){
					$class		.= ' has-img';
					$img_tag	= '<img style="'.$attr['img_style'].'" src="'.wpjam_get_thumbnail($img_url, $size).'" alt="" />';
				}
			}

			if(!$this->readonly && !$this->disabled){
				$img_tag	.= self::get_icon('del_img').'<div class="wp-media-buttons"><button type="button" class="button add_media"><span class="wp-media-buttons-icon"></span> 添加图片</button></div>';
			}else{
				$class	.= ' readonly';
			}

			$html	= '<div class="wpjam-img'.$class.'" '.wpjam_data_attribute_string($attr).'>'.$img_tag.'</div>';
			$html	.= ((!$this->readonly && !$this->disabled) ? $this->render_element(['type'=>'hidden']) : '');
		}else{
			if($this->type == 'image'){
				$btn_name	= '图片';
				$item_type	= 'image';
			}else{
				$btn_name	= '文件';
				$item_type	= $this->item_type ?: '';
			}

			$button	= sprintf('<a class="button" data-uploader_id="%s" data-item_type="%s">选择%s</a>', 'wpjam_uploader_'.$this->id, $item_type, $btn_name);
			$html	= $this->render_element(['type'=>'url', 'description'=>'']).' '.$button;
			$html	= '<div class="wpjam-file">'.$html.'</div>';
		}

		return $html.$this->description;
	}
}

class WPJAM_View_Field extends WPJAM_Field{
	public function is_editable(){
		return false;
	}

	public function value_callback($name, $args){
		if(!is_null($this->value)){
			return $this->value;
		}else{
			return parent::value_callback($name, $args);
		}
	}

	protected function render_component(){
		if(in_array($this->type, ['view','br'], true)){
			$options	= $this->options;

			if($options){
				$values	= $this->value ? [$this->value] : ['', 0];

				foreach($values as $v){
					if(isset($options[$v])){
						return $options[$v];
					}
				}
			}

			return $this->value;
		}elseif($this->type == 'hr'){
			return '<hr />';
		}
	}
}

class WPJAM_Editor_Field extends WPJAM_Field{
	protected function render_component(){
		$settings	= $this->settings ?: [];
		$settings	= wp_parse_args($settings, [
			'tinymce'		=>[
				'wpautop'	=> true,
				'plugins'	=> 'charmap colorpicker compat3x directionality hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
				'toolbar1'	=> 'bold italic underline strikethrough | bullist numlist | blockquote hr | alignleft aligncenter alignright alignjustify | link unlink | wp_adv',
				'toolbar2'	=> 'formatselect forecolor backcolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help'
			],
			'quicktags'		=> true,
			'mediaButtons'	=> true
		]);

		if(wp_doing_ajax()){
			return $this->render_element(['id'=>'editor_'.$this->id]);
		}else{
			ob_start();

			wp_editor($this->value, 'editor_'.$this->id, $settings);

			$editor	= ob_get_clean();

			$style	= $this->style ? ' style="'.$this->style.'"' : '';
			return '<div'.$style.'>'.$editor.'</div>'.$this->description;
		}
	}
}

class WPJAM_MU_Field extends WPJAM_Field{
	protected $_last;

	protected function validate_value($value, $required=false){
		if($value){
			if(!is_array($value)){
				$value	= wpjam_json_decode($value);
			}else{
				$value	= wpjam_array_filter($value, 'is_populated');
			}
		}

		if(empty($value) || is_wp_error($value)){
			return $required ? null : [];
		}else{
			return array_values($value);
		}
	}

	public function wrap_description(){
		return '<p class="description">'.$this->description.'</p>';
	}

	protected function item_wrap($item_html, $item_class, $i=0){
		if(!$this->readonly && !$this->disabled){
			$item_html	.= $this->mu_button($item_class, $i);
		}

		return '<div class="'.$item_class.'">'.$item_html.'</div>';
	}

	protected function mu_wrap($html, $mu_class){
		return '<div class="'.$mu_class.'" id="'.$this->id.'" data-max_items="'.$this->max_items.'">'.$html.'</div>'.$this->description;
	}

	protected function mu_button($item_class='', $i=0){
		if($this->_last === $i){
			return ' <a class="wpjam-mu-text button">添加选项</a>';
		}else{
			return self::get_icon('del_btn,move');
		}
	}

	protected function render_component(){
		$value	= $this->value;

		if(!is_blank($value)){
			if(is_array($value)){
				$value	= wpjam_array_filter($value, 'is_populated');

				$value	= array_values($value);

				if($this->max_items && count($value) >= $this->max_items){
					$value	= array_slice($value, 0, $this->max_items);
				}
			}else{
				$value	= (array)$value;
			}
		}else{
			$value	= [];
		}

		$this->_last	= count($value);

		$reached	= $this->max_items && count($value) >= $this->max_items;

		if($reached){
			$this->_last	-=1;
		}else{
			$value[]		= '';
		}

		return $this->mu_render($value, 'mu-item');
	}

	protected function mu_render($value, $item_class){
		return '';
	}
}

class WPJAM_MU_Text_Field extends WPJAM_MU_Field{
	private $item_object;

	public function get_item_object(){
		if(is_null($this->item_object)){
			$item_field	= wpjam_array_except($this->field, 'required');	// 提交时才验证
			$item_type	= $this->item_type = $this->item_type ?: 'text';

			$this->item_object	= WPJAM_Field::create(array_merge($item_field, ['type'=>$item_type]));
		}

		return $this->item_object;
	}

	public function parse_json_schema(){
		return ['type'=>'array',	'items'=>$this->get_item_object()->get_json_schema()];
	}

	protected function prepare_value($value){
		if($value && is_array($value)){
			foreach($value as &$item){
				$item	= $this->get_item_object()->prepare_value($item);
			}
		}

		return $value;
	}

	protected function validate_value($value, $required=false){
		$value	= parent::validate_value($value, $required);

		if($value && is_array($value)){
			foreach($value as &$item){
				$item	= $this->get_item_object()->validate_value($item, $required);
			}
		}

		return $value;
	}

	public function get_default_class($type=''){
		$item_type	= $this->item_type ?: 'text';

		return parent::get_default_class($item_type);
	}

	protected function mu_render($value, $item_class){
		$html	= '';

		foreach($value as $i => $item){
			$item_args	= ['value'=>$item, 'id'=>'', 'class'=>$this->class, 'name'=>$this->name.'[]', 'description'=>''];
			$item_html	= $this->get_item_object()->render_element($item_args);
			$html		.= $this->item_wrap($item_html, $item_class, $i);
		}

		return $this->mu_wrap($html, 'mu-texts');
	}
}

class WPJAM_MU_Fields_Field extends WPJAM_MU_Field{
	public function parse_json_schema(){
		$properties	= array_map(function($object){ return $object->get_json_schema(); }, $this->get_objects());

		return ['type'=>'array',	'items'=>['type'=>'object',	'properties'=>$properties]];
	}

	protected function prepare_value($value){
		if($value && is_array($value)){
			foreach($value as &$item){
				foreach($this->get_objects() as $name => $object){
					if(isset($item[$name])){
						$item[$name]	= $object->prepare_value($item[$name]);
					}
				}
			}
		}

		return $value;
	}

	protected function validate_value($value, $required=false){
		$value	= parent::validate_value($value, $required);

		if($value && is_array($value)){
			foreach($value as &$item){
				foreach($this->get_objects() as $name => $object){
					if(isset($item[$name])){
						$item[$name]	= $object->validate_value($item[$name], $required);
					}
				}
			}
		}

		return $value;
	}

	protected function mu_button($item_class='', $i=0){
		if(!is_numeric($i) || $this->_last === $i){
			return sprintf(' <a class="wpjam-mu-fields button" data-i="%s" data-item_class="'.$item_class.'" data-tmpl_id="'.md5($this->name).'">添加选项</a>', $i);
		}else{
			return self::get_icon('del_btn,move');
		}
	}

	protected function mu_render($value, $item_class){
		if(!$this->get_objects()){
			return '';
		}

		if($this->group){
			$item_class	.= ' field-group';
		}

		$html	= '';

		foreach($value as $i => $item){
			$item_html	= $this->render_item($i, $item);
			$html		.= $this->item_wrap($item_html, $item_class, $i);
		}

		$html	.= self::generate_tmpl(md5($this->name), $this->render_item('{{ data.i }}').$this->mu_button($item_class, '{{ data.i }}'));

		return $this->mu_wrap($html, 'mu-fields');
	}

	protected function render_item($i, $value=[]){
		$show_if_keys	= $this->get_show_if_keys();

		$html	= '';

		foreach($this->get_objects() as $name => $object){
			if(preg_match('/\[([^\]]*)\]/', $name)){
				wp_die('mu-fields 类型里面子字段不允许[]模式');
			}

			if(in_array($object->type, ['fieldset', 'mu-fields'])){
				wp_die('mu-fields 不允许内嵌 '.$object->type);
			}

			$raw_field = $object->field;

			if($value && isset($value[$name])){
				$object->value	= $value[$name];
			}

			if($show_if_keys && in_array($object->key, $show_if_keys)){
				$object->show_if_key	= true;
			}

			$object->sub_i	= $i;
			$object->name	= $this->name.'['.$i.']'.'['.$name.']';
			$object->key	= $object->key.'__'.$i;
			$object->id		= $object->id.'__'.$i;

			$html	.= $this->render_group($object->group);
			$html	.= $object->wrap($object->render());

			$object->field	= $raw_field;
		}

		return $html.$this->reset_group();
	}
}

class WPJAM_MU_Image_Field extends WPJAM_MU_Field{
	public function parse_json_schema(){
		if($this->type == 'mu-img' && $this->item_type != 'url'){
			return ['type'=>'array',	'items'=>['type'=>'integer']];
		}else{
			return ['type'=>'array',	'items'=>['type'=>'string',	'format'=>'uri']];
		}
	}

	protected function prepare_value($value){
		$item_type	= $this->type  == 'mu-img' ? $this->item_type : 'url';

		if($value && is_array($value)){
			foreach($value as &$item){
				$item	= $this->prepare_image($item, $item_type);
			}
		}

		return $value;
	}

	public function get_default_class($type=''){
		return parent::get_default_class('url');
	}

	protected function mu_button($item_class='', $i=0){
		$attr	= ['name'=>$this->name.'[]', 'item_class'=>$item_class, 'item_type'=>$this->item_type, 'uploader_id'=>'wpjam_uploader_'.$this->id];

		if($this->type == 'mu-img'){
			if($i == -1){
				$attr['thumb_args']	= wpjam_get_thumbnail('', [200,200]);

				return '<div class="wpjam-mu-img dashicons dashicons-plus-alt2" '.wpjam_data_attribute_string($attr).'>'.self::get_icon('del_icon').'</div>';
			}else{
				return self::get_icon('del_icon');
			}
		}else{
			if($this->_last === $i){
				$title	= $this->type == 'mu-image' ? '选择图片' : '选择文件';

				return '<a class="wpjam-mu-file button" '.wpjam_data_attribute_string(array_merge($attr, ['title'=>$title])).'>'.$title.'[多选]</a>';
			}else{
				return self::get_icon('del_btn,move');
			}
		}
	}

	protected function mu_render($value, $item_class){
		if(!current_user_can('upload_files')){
			return '';
		}

		$html	= '';

		$item_args	= ['id'=>'', 'name'=>$this->name.'[]', 'description'=>''];

		if($this->type == 'mu-img'){
			$mu_class	= 'mu-imgs';
			$item_class	.= ' mu-img';

			foreach($value as $img){
				if($img === ''){
					continue;
				}

				$img_url	= $this->item_type == 'url' ? $img : wp_get_attachment_url($img);
				$img_tag	= '<img src="'.wpjam_get_thumbnail($img_url, 200, 200).'" alt="">';
				$img_tag	= '<a href="'.$img_url.'" class="wpjam-modal">'.$img_tag.'</a>';

				if(!$this->readonly && !$this->disabled){
					$item_args	= array_merge($item_args, ['type'=>'hidden', 'value'=>$img]);
					$img_tag	.= $this->render_element($item_args);
				}

				$html	.= $this->item_wrap($img_tag, $item_class);
			}

			if(!$this->readonly && !$this->disabled){
				$html		.= $this->mu_button($item_class, -1);
			}else{
				$mu_class	.= ' readonly';
			}
		}else{
			$mu_class	= 'mu-files';

			foreach($value as $i => $item){
				$item_args	= array_merge($item_args, ['type'=>'url', 'value'=>$item]);
				$item_html	= $this->render_element($item_args);
				$html		.= $this->item_wrap($item_html, $item_class, $i);
			}
		}

		return $this->mu_wrap($html, $mu_class);
	}
}

class WPJAM_Fields{
	private $objects	= [];

	private function __construct($objects){
		$this->objects	= $objects;
	}

	public function get_objects(){
		return $this->objects;
	}

	public function get_show_if_keys(){
		$keys	= [];

		foreach($this->objects as $object){
			$show_if	= $object->show_if;

			if($show_if && !empty($show_if['key'])){
				$keys[]	= $show_if['key'];
			}

			if($object->type == 'fieldset'){
				$keys	= array_merge($keys, $object->get_show_if_keys());
			}
		}

		return array_unique($keys);
	}

	public function get_defaults(){
		$defaults	= [];

		foreach($this->objects as $object){
			if(!$object->is_editable()){
				continue;
			}

			if($object->type == 'fieldset'){
				$value		= $object->get_defaults();
				$defaults	= wpjam_array_merge($defaults, $value);
			}else{
				$name		= $object->get_top_name();
				$value		= $object->wrap_by_name($object->get_default());
				$defaults	= wpjam_array_merge($defaults, [$name=>$value]);
			}
		}

		return $defaults;
	}

	public function get_data($values=null, $show_if_values=null, $show_if=true){
		$get_show_if_values	= is_null($show_if_values);

		$data	= [];

		foreach($this->objects as $object){
			if(!$object->is_editable()){
				continue;
			}

			if($get_show_if_values){
				$field_show_if	= true;
			}else{
				$field_show_if	= $show_if ? $object->show_if($show_if_values) : $show_if;
			}

			if($object->type == 'fieldset'){
				$value	= $object->get_data($values, $show_if_values, $field_show_if);

				if(is_wp_error($value)){
					return $value;
				}

				$data	= wpjam_array_merge($data, $value);
			}else{
				$name	= $object->get_top_name();

				if(isset($values)){
					$value	= $values[$name] ?? null;
				}else{
					$value	= wpjam_get_parameter($name, ['method'=>'POST']);
				}

				$value	= $object->parse_by_name($value);
				$key	= $object->key;

				if($get_show_if_values){	// show_if 判断是基于 key 并且 fieldset array 的情况下的 key 是 ${key}_{$sub_key}
					$data[$key]	= $object->validate($value, false);
				}else{
					if($field_show_if){
						$value	= $object->validate($value, true);

						if(is_wp_error($value)){
							return $value;
						}
					}else{
						$value	= null;

						$show_if_values[$key]	= null;	// 第一次获取的值都是经过 json schema validate 的，可能存在 show_if 的字段在后面
					}

					$value	= $object->wrap_by_name($value);
					$data	= wpjam_array_merge($data, [$name=>$value]);
				}
			}
		}

		return $data;
	}

	public function validate($values=null){
		$show_if_values	= $this->get_show_if_keys() ? $this->get_data($values) : [];

		return $this->get_data($values, $show_if_values);
	}

	public function prepare($args=[]){
		$data	= [];

		foreach($this->objects as $object){
			if(!$object->show_in_rest){
				continue;
			}

			if($object->type == 'fieldset'){
				$data	= wpjam_array_merge($data, $object->prepare_by_fields($args));
			}else{
				$name	= $object->get_top_name();
				$value	= $object->value_callback($name, $args);
				$value	= $object->parse_by_name($value);
				$value	= $object->prepare($value);
				$value	= $object->wrap_by_name($value);
				$data	= wpjam_array_merge($data, [$name=>$value]);
			}
		}

		return $data;
	}

	public function render($args=[]){
		$echo	= wpjam_array_pull($args, 'echo', true);
		$class	= wpjam_array_pull($args, 'wrap_class', []);
		$tag 	= wpjam_array_pull($args, 'wrap_tag');

		if(is_null($tag)){
			$type	= wpjam_array_pull($args, 'fields_type', 'table');
			$map	= ['list'=>'li', 'table'=>'tr'];
			$tag	= $map[$type] ?? $type;
		}else{
			$type	= wpjam_array_pull($args, 'fields_type');
		}

		$html	= '';

		$args['show_if_keys']	= $this->get_show_if_keys();

		foreach($this->objects as $object){
			if($object->show_admin_column === 'only'){
				continue;
			}

			$html	.= $object->wrap($object->render($args), $tag, $class);
		}

		if($type == 'list'){
			$html	= '<ul>'.$html.'</ul>';
		}elseif($type == 'table'){
			$html	= '<table class="form-table" cellspacing="0"><tbody>'.$html.'</tbody></table>';
		}

		if($echo){
			echo $html;
		}else{
			return $html;
		}
	}

	public function callback($args=[]){
		_deprecated_function(__METHOD__, '6.0', '请使用 WPJAM_Fields::Render');
		return $this->render($args);
	}

	public static function create($fields){
		if(is_object($fields)){
			return $fields;
		}

		$objects	= [];

		foreach($fields as $key => $field){
			$objects[$key]	= is_object($field) ? $field : WPJAM_Field::create($field, $key);
		}

		return new WPJAM_Fields($objects);
	}
}

class WPJAM_JSON_Schema{
	private $schema;

	public function __construct($schema){
		$this->schema	= $this->parse($schema);
	}

	public function parse($schema){
		if(isset($schema['enum'])){
			if($schema['type'] == 'integer'){
				$schema['enum']	= array_map('intval', $schema['enum']);
			}elseif($schema['type'] == 'number'){
				$schema['enum']	= array_map('floatval', $schema['enum']);
			}else{
				$schema['enum']	= array_map('strval', $schema['enum']);
			}
		}elseif(isset($schema['properties'])){
			foreach($schema['properties'] as &$schema_property){
				if(isset($schema_property['enum'])){
					$schema_property	= $this->parse($schema_property);
				}
			}
		}elseif(isset($schema['items'])){
			$schema['items']	= $this->parse($schema['items']);
		}

		return $schema;
	}

	public function validate($value, $param='', $object=null){
		$value	= rest_sanitize_value_from_schema($value, $this->schema, $param);

		if(is_populated($value)){
			$result	= rest_validate_value_from_schema($value, $this->schema, $param);

			if(is_wp_error($result)){
				return $result;
			}
		}

		return $value;
	}

	public function prepare($value, $param='', $object=null){
		return rest_sanitize_value_from_schema($value, $this->schema, $param);
	}

	public function get_value(){
		return $this->schema;
	}

	public static function create_by_field($field){
		$object	= is_object($field) ? $field : WPJAM_Field::create($field);
		$schema	= $object->parse_json_schema();

		$map	= [];

		if($schema['type'] == 'string'){
			$map	= [
				'minlength'	=> 'minLength',
				'maxlength'	=> 'maxLength',
				'pattern'	=> 'pattern',
			];
		}elseif(in_array($schema['type'], ['number', 'integer'])){
			$map	= [
				'min'	=> 'minimum',
				'max'	=> 'maximum',
			];

			if($object->step && $object->step != 'any' && strpos($object->step, '.') === false){	// 浮点数不能求余数
				$schema['multipleOf']	= $object->step;
			}
		}elseif($schema['type'] == 'array'){
			$map	= [
				'max_items'		=> 'maxItems',
				'min_items'		=> 'minItems',
				'unique_items'	=> 'uniqueItems',
			];

			if($object->required){
				$schema['minItems']	= 1;
			}
		}

		foreach($map as $field_attr => $schema_attr){
			if(isset($object->$field_attr)){
				$schema[$schema_attr]	= $object->$field_attr;
			}
		}

		$show_in_rest	= $object->show_in_rest;

		if($show_in_rest && is_array($show_in_rest)){
			if(isset($show_in_rest['schema']) && is_array($show_in_rest['schema'])){
				$schema	= array_merge($schema, $show_in_rest['schema']);
			}

			if(isset($show_in_rest['type'])){
				$schema['type']	= $show_in_rest['type'];
			}
		}

		return new self($schema);
	}
}

class WPJAM_Field_Name{
	private $top_name	= '';
	private $sub_name	= '';
	private $name_arr	= [];
	private $sub_arr	= [];

	public function __construct($name){
		if(preg_match('/\[([^\]]*)\]/', $name)){
			$name_arr	= wp_parse_args($name);

			$this->top_name	= current(array_keys($name_arr));
			$this->name_arr	= current(array_values($name_arr));
		}else{
			$this->top_name	= $name;
		}

		$this->sub_name	= '['.$this->top_name.']';

		$name_arr	= $this->name_arr;

		while($name_arr){
			$name_key	= current(array_keys($name_arr));
			$name_arr	= current(array_values($name_arr));

			$this->sub_name	.='['.$name_key.']';

			array_unshift($this->sub_arr, $name_key);
		}
	}

	public function get_top(){
		return $this->top_name;
	}

	public function generate_sub(){
		return $this->sub_name;
	}

	public function parse_value($value){
		$name_arr	= $this->name_arr;

		while($name_arr){
			if(!is_array($value)){
				return null;
			}

			$sub_name	= current(array_keys($name_arr));
			$name_arr	= current(array_values($name_arr));
			$value		= $value[$sub_name] ?? null;
		}

		return $value;
	}

	public function wrap_value($value){
		foreach($this->sub_arr as $sub_name){
			$value	= [$sub_name => $value];
		}

		return $value;
	}

	public static function combine($name, ...$args){
		foreach($args as $sub){
			$object	= new self($sub);
			$name	.= $object->generate_sub();
		}

		return $name;
	}
}

class WPJAM_Field_Group{
	private $group = '';

	public function render($group){
		$return	= '';

		if($group != $this->group){
			if($this->group){
				$return	.= '</div>';
			}

			if($group){
				$return	.= '<div class="field-group" id="field_group_'.esc_attr($group).'">';
			}

			$this->group	= $group;
		}

		return $return;
	}

	public function reset(){
		return $this->render('');
	}
}