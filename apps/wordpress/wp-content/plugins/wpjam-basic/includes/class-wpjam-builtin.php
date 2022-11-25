<?php
class WPJAM_Builtin_Page{
	protected $screen;

	public function __construct($screen){
		$this->screen	= $screen;
	}

	public function __get($key){
		return $this->screen->$key ?? null;
	}

	public function __isset($key){
		return $this->$key !== null;
	}

	public function load(){
		do_action('wpjam_builtin_page_load', $this->base, $this->screen);

		foreach(wpjam_list_sort(WPJAM_Builtin_Page_Load::get_pre_registereds()) as $object){
			$object->load($this->screen);
		}

		if(!wp_doing_ajax() && wpjam_get_page_summary()){
			add_filter('wpjam_html', [$this, 'filter_html']);
		}

		$method	= 'load_'.str_replace('-', '_', $this->base).'_page';

		if(method_exists($this, $method)){
			call_user_func([$this, $method]);
		}
	}

	public function filter_html($html){
		return str_replace('<hr class="wp-header-end">', '<hr class="wp-header-end">'.wpautop(wpjam_get_page_summary()), $html);
	}

	public function load_post_page(){
		$edit_form_hook	= $this->post_type == 'page' ? 'edit_page_form' : 'edit_form_advanced';

		add_action($edit_form_hook,			['WPJAM_Post_Option', 'on_edit_form'], 99);
		add_action('add_meta_boxes',		['WPJAM_Post_Option', 'on_add_meta_boxes'], 10, 2);
		add_action('wp_after_insert_post',	['WPJAM_Post_Option', 'on_after_insert_post'], 999, 2);
	}

	public function load_term_page(){
		add_action($this->taxonomy.'_edit_form_fields',	['WPJAM_Term_Option', 'on_edit_form_fields']);
	}

	public function load_edit_page(){
		if($this->post_type && post_type_exists($this->post_type)){
			$GLOBALS['wpjam_list_table']	= new WPJAM_Posts_List_Table();
		}
	}

	public function load_upload_page(){
		$mode	= get_user_option('media_library_mode', get_current_user_id()) ?: 'grid';

		if(isset($_GET['mode']) && in_array($_GET['mode'], ['grid', 'list'], true)){
			$mode	= $_GET['mode'];
		}

		if($mode == 'grid'){
			return;
		}

		$this->load_edit_page();
	}

	public function load_edit_tags_page(){
		if($this->taxonomy && taxonomy_exists($this->taxonomy)){
			add_action('edited_term',	['WPJAM_Term_Option', 'on_edited_term'], 10, 3);

			if(wp_doing_ajax()){
				if($_POST['action'] == 'add-tag'){
					add_filter('pre_insert_term',	['WPJAM_Term_Option', 'filter_pre_insert_term'], 10, 2);
					add_action('created_term', 		['WPJAM_Term_Option', 'on_created_term'], 10, 3);
				}
			}else{
				add_action($this->taxonomy.'_add_form_fields', 	['WPJAM_Term_Option', 'on_add_form_fields']);
			}

			$GLOBALS['wpjam_list_table']	= new WPJAM_Terms_List_Table();
		}
	}

	public function load_users_page(){
		$GLOBALS['wpjam_list_table']	= new WPJAM_Users_List_Table();
	}
}

class WPJAM_Builtin_Page_Load extends WPJAM_Register{
	public function is_available($screen){
		if($this->screen && is_callable($this->screen)){
			return call_user_func($this->screen, $screen);
		}

		foreach(['base', 'post_type', 'taxonomy'] as $key){
			if($this->$key){
				if(!wpjam_compare($screen->$key, (array)$this->$key)){
					return false;
				}
			}
		}

		return true;
	}

	public function load($screen){
		if($this->is_available($screen)){
			if($this->page_file && is_file($this->page_file)){
				$file	= $this->page_file;
				$files	= is_array($file) ? $file : [$file];

				foreach($files as $file){
					include $file;
				}
			}

			if($this->callback && is_callable($this->callback)){
				call_user_func($this->callback, $screen);
			}
		}
	}
}

class WPJAM_Builtin_List_Table extends WPJAM_List_Table{
	public function filter_bulk_actions($bulk_actions=[]){
		return array_merge($bulk_actions, $this->bulk_actions);
	}

	public function filter_columns($columns){
		if($this->columns){	// 在最后一个之前插入
			wpjam_array_push($columns, $this->columns, array_key_last($columns)); 
		}

		$removed	= wpjam_get_current_items(get_current_screen()->id.'_removed_columns');

		return wpjam_array_except($columns, $removed);
	}

	public function filter_custom_column($value, $name, $id){
		return $this->get_column_value($id, $name, $value);
	}

	public function filter_sortable_columns($sortable_columns){
		return array_merge($sortable_columns, $this->get_sortable_columns());
	}

	public function filter_html($html){
		if(!wp_doing_ajax() && $this->bulk_actions){
			$html	= WPJAM_List_Table_Action::set_bulk_data_attr($html);
		}

		return $this->single_row_replace($html);
	}

	public function get_single_row($id){		
		return apply_filters('wpjam_single_row', parent::get_single_row($id), $id);
	}

	public function get_list_table(){
		return $this->single_row_replace(parent::get_list_table());
	}

	public function single_row_replace($html){
		return preg_replace_callback('/<tr id="'.$this->singular.'-(\d+)".*?>.*?<\/tr>/is', function($matches){
			return apply_filters('wpjam_single_row', $matches[0], $matches[1]);
		}, $html);
	}

	public function wp_list_table(){
		if(!isset($GLOBALS['wp_list_table'])){
			$GLOBALS['wp_list_table'] = _get_list_table($this->builtin_class, ['screen'=>get_current_screen()]);
		}

		return $GLOBALS['wp_list_table'];
	}

	public function prepare_items(){
		$data	= wpjam_get_data_parameter();

		foreach($data as $key=>$value){
			$_GET[$key]	= $_POST[$key]	= $value;
		}

		$this->wp_list_table()->prepare_items();
	}
}

class WPJAM_Posts_List_Table extends WPJAM_Builtin_List_Table{
	public function __construct(){
		$screen		= get_current_screen();
		$post_type	= $screen->post_type;
		$args		= [
			'title'			=> $this->get_setting('label'),
			'singular'		=> 'post',
			'capability'	=> 'edit_post',
			'data_type'		=> 'post_type',
			'post_type'		=> $post_type,
			'model'			=> 'WPJAM_Post',
		];

		if($post_type == 'attachment'){
			$row_actions_filter		= 'media_row_actions';
			$column_filter_part		= 'media';

			$args['builtin_class']	= 'WP_Media_List_Table';
		}else{
			$row_actions_filter		= $this->get_setting('hierarchical') ? 'page_row_actions' : 'post_row_actions';
			$column_filter_part		= $post_type.'_posts';

			$args['builtin_class']	= 'WP_Posts_List_Table';

			add_filter('map_meta_cap',	[$this, 'filter_map_meta_cap'], 10, 4);
		}

		if(wp_doing_ajax()){
			add_action('wp_ajax_wpjam-list-table-action',	[$this, 'ajax_response']);
		}

		if(!wp_doing_ajax() || (wp_doing_ajax() && $_POST['action']=='inline-save')){
			add_filter('wpjam_html',	[$this, 'filter_html']);
		}

		add_action('pre_get_posts',	[$this, 'on_pre_get_posts']);

		add_filter('bulk_actions-'.$screen->id,	[$this, 'filter_bulk_actions']);

		add_filter($row_actions_filter,	[$this, 'filter_row_actions'], 1, 2);

		add_action('manage_'.$column_filter_part.'_custom_column',	[$this, 'on_custom_column'], 10, 2);

		add_filter('manage_'.$column_filter_part.'_columns',	[$this, 'filter_columns']);
		add_filter('manage_'.$screen->id.'_sortable_columns',	[$this, 'filter_sortable_columns']);

		// 一定要最后执行
		$this->_args	= $this->parse_args($args);
	}

	public function __get($name){
		if($name == 'post_type'){
			return get_current_screen()->$name;
		}else{
			return parent::__get($name);
		}
	}

	public function get_setting($key, $default=null){
		return wpjam_get_post_type_setting($this->post_type, $key, $default);
	}

	public function filter_map_meta_cap($caps, $cap, $user_id, $args){
		if($cap == 'edit_post' && empty($args[0])){
			return $this->get_setting('map_meta_cap') ? [$this->get_setting('cap')->edit_posts] : [$this->get_setting('cap')->$cap];
		}

		return $caps;
	}

	public function call_model_list_action($id, $data, $list_action){
		if($id && get_post($id)){
			$post_data	= [];

			foreach(get_post($id, ARRAY_A) as $post_key => $old_value){
				$value	= wpjam_array_pull($data, $post_key);

				if(!is_null($value) && $old_value != $value){
					$post_data[$post_key]	= $value;
				}
			}

			if($post_data){
				$result	= WPJAM_Post::update($id, $post_data);

				if(is_wp_error($result) || empty($data)){
					return $result;
				}
			}

			if(empty($data)){
				return true;
			}
		}

		return parent::call_model_list_action($id, $data, $list_action);
	}

	public function prepare_items(){
		$_GET['post_type']	= $this->post_type;

		parent::prepare_items();
	}

	public function list_table(){
		$wp_list_table	= $this->wp_list_table();

		if($this->post_type == 'attachment'){
			echo '<form id="posts-filter" method="get">';

			$wp_list_table->views();	
		}else{
			$wp_list_table->views();

			echo '<form id="posts-filter" method="get">';

			$status	= wpjam_get_data_parameter('post_status', ['default'=>'all']);

			echo wpjam_field(['key'=>'post_status',	'type'=>'hidden',	'class'=>'post_status_page',	'value'=>$status]);
			echo wpjam_field(['key'=>'post_type',	'type'=>'hidden',	'class'=>'post_type_page',		'value'=>$this->post_type]);

			if($show_sticky	= wpjam_get_data_parameter('show_sticky')){
				echo wpjam_field(['key'=>'show_sticky', 'type'=>'hidden', 'value'=>1]);
			}

			$wp_list_table->search_box($this->get_setting('labels')->search_items, 'post');
		}

		$wp_list_table->display(); 

		echo '</form>';
	}

	protected function filter_fields($fields, $key, $id){
		if($key && $id && !is_array($id) && !isset($fields['title']) && !isset($fields['post_title'])){
			$fields	= array_merge(['title'=>['title'=>$this->title.'标题', 'type'=>'view', 'value'=>get_post($id)->post_title]], $fields);
		}

		return $fields;
	}

	public function single_row($raw_item){
		global $post, $authordata;

		if($post = is_numeric($raw_item) ? get_post($raw_item) : $raw_item){
			$authordata = get_userdata($post->post_author);

			if($post->post_type == 'attachment'){
				$post_owner = (get_current_user_id() == $post->post_author) ? 'self' : 'other';

				echo '<tr id="post-'.$post->ID.'" class="'.trim(' author-' . $post_owner . ' status-' . $post->post_status).'">';

				$this->wp_list_table()->single_row_columns($post);

				echo '</tr>';
			}else{
				$this->wp_list_table()->single_row($post);
			}
		}
	}

	public function filter_bulk_actions($bulk_actions=[]){
		$split	= array_search((isset($bulk_actions['trash']) ? 'trash' : 'untrash'), array_keys($bulk_actions), true);

		return array_merge(array_slice($bulk_actions, 0, $split), $this->bulk_actions, array_slice($bulk_actions, $split));
	}

	public function filter_row_actions($row_actions, $post){
		foreach($this->get_row_actions($post->ID) as $key => $row_action){
			$action	= WPJAM_List_Table_Action::get($key);
			$status	= get_post_status($post);

			if($status == 'trash'){
				if($action->post_status && in_array($status, (array)$action->post_status)){
					$row_actions[$key]	= $row_action;
				}
			}else{
				if(is_null($action->post_status) || in_array($status, (array)$action->post_status)){
					$row_actions[$key]	= $row_action;
				}
			}
		}

		foreach(['trash', 'view'] as $key){
			if($row_action = wpjam_array_pull($row_actions, $key)){
				$row_actions[$key]	= $row_action;
			}
		}

		return array_merge($row_actions, ['id'=>'ID: '.$post->ID]);
	}

	public function on_custom_column($name, $post_id){
		echo $this->get_column_value($post_id, $name, null) ?: '';
	}

	public function filter_html($html){
		if(!wp_doing_ajax()){
			if($add_action = WPJAM_List_Table_Action::get('add')){
				$html	= preg_replace('/<a href=".*?" class="page-title-action">.*?<\/a>/i', $add_action->get_row_action(['class'=>'page-title-action']), $html);
			}
		}

		return parent::filter_html($html);
	}

	public function on_pre_get_posts($wp_query){
		if($sortable_columns = $this->get_sortable_columns()){
			$orderby	= $wp_query->get('orderby');

			if($orderby && is_string($orderby) && isset($sortable_columns[$orderby])){
				if($object = WPJAM_List_Table_Column::get($orderby)){
					$orderby_type	= $object->sortable_column ?? 'meta_value';

					if(in_array($orderby_type, ['meta_value_num', 'meta_value'])){
						$wp_query->set('meta_key', $orderby);
						$wp_query->set('orderby', $orderby_type);
					}else{
						$wp_query->set('orderby', $orderby);
					}
				}
			}
		}
	}
}

class WPJAM_Terms_List_Table extends WPJAM_Builtin_List_Table{
	public function __construct(){
		$screen		= get_current_screen();
		$taxonomy	= $screen->taxonomy;
		$args		= [
			'title'			=> $this->get_setting('label'),
			'capability'	=> $this->get_setting('cap')->edit_terms,
			'singular'		=> 'tag',
			'data_type'		=> 'taxonomy',
			'taxonomy'		=> $taxonomy,
			'post_type'		=> $screen->post_type,
			'model'			=> 'WPJAM_Term',
			'builtin_class'	=> 'WP_Terms_List_Table'
		];

		if($this->get_setting('hierarchical')){
			if($this->get_setting('sortable')){
				$args['sortable']	= [
					'items'			=> $this->get_sorteable_items(),
					'action_args'	=> ['row_action'=>false, 'callback'=>['WPJAM_Term', 'move']]
				];

				add_filter('edit_'.$taxonomy.'_per_page',	[$this, 'filter_per_page']);
			}

			if(!is_null($this->get_setting('levels'))){
				wpjam_register_list_table_action('children', ['title'=>'下一级']);

				add_filter('pre_insert_term',	[$this, 'filter_pre_insert_term'], 10, 2);
			}
		}

		if(wp_doing_ajax()){
			add_action('wp_ajax_wpjam-list-table-action', [$this, 'ajax_response']);
		}
		
		if(!wp_doing_ajax() || (wp_doing_ajax() && in_array($_POST['action'], ['inline-save-tax', 'add-tag']))){
			add_filter('wpjam_html',	[$this, 'filter_html']);
		}

		add_action('parse_term_query',	[$this, 'on_parse_term_query'], 0);

		add_filter('bulk_actions-'.$screen->id,	[$this, 'filter_bulk_actions']);
		add_filter($taxonomy.'_row_actions',	[$this, 'filter_row_actions'], 1, 2);

		add_filter('manage_'.$screen->id.'_columns',			[$this, 'filter_columns']);
		add_filter('manage_'.$taxonomy.'_custom_column',		[$this, 'filter_custom_column'], 10, 3);
		add_filter('manage_'.$screen->id.'_sortable_columns',	[$this, 'filter_sortable_columns']);

		$this->_args	= $this->parse_args($args);
	}

	public function __get($name){
		if(in_array($name, ['taxonomy', 'post_type'])){
			return get_current_screen()->$name;
		}else{
			return parent::__get($name);
		}
	}

	public function get_setting($key, $default=null){
		return wpjam_get_taxonomy_setting($this->taxonomy, $key, $default);
	}

	public function list_table(){
		if($this->get_setting('hierarchical') && $this->get_setting('sortable')){
			$sortable_items	= 'data-sortable_items="'.$this->get_sorteable_items().'"';
		}else{
			$sortable_items	= '';
		}

		echo '<form id="posts-filter" '.$sortable_items.' method="get">';

		echo wpjam_field(['key'=>'taxonomy',	'type'=>'hidden',	'value'=>$this->taxonomy]);
		echo wpjam_field(['key'=>'post_type',	'type'=>'hidden',	'value'=>$this->post_type]);

		$this->wp_list_table()->display(); 

		echo '</form>';
	}

	public function get_list_table(){
		return $this->append_extra_tablenav(parent::get_list_table());
	}

	public function filter_html($html){
		return parent::filter_html($this->append_extra_tablenav($html));
	}

	protected function filter_fields($fields, $key, $id){
		if($key && $id && !is_array($id)){
			$fields	= array_merge(['title'=>['title'=>$this->title, 'type'=>'view', 'value'=>get_term($id)->name]], $fields);
		}

		return $fields;
	}

	public function get_sorteable_items(){
		$parent	= $this->get_parent();
		$level	= $parent ? (wpjam_get_term_level($parent)+1) : 0;

		return 'tr.level-'.$level;
	}

	public function get_parent(){
		$parent	= wpjam_get_data_parameter('parent');

		if(is_null($parent)){
			if($this->get_setting('levels') == 1){
				return 0;
			}

			return null;
		}

		return (int)$parent;
	}

	public function get_edit_tags_link($args=[]){
		$args	= array_filter($args, 'is_exists');
		$args	= wp_parse_args($args, ['taxonomy'=>$this->taxonomy, 'post_type'=>$this->post_type]);

		return admin_url(add_query_arg($args, 'edit-tags.php'));
	}

	public function append_extra_tablenav($html){
		$extra	= '';

		if($this->get_setting('hierarchical') && $this->get_setting('levels') > 1){
			$parent	= $this->get_parent();

			if(is_null($parent)){
				$to		= 0;
				$text	= '只显示第一级';
			}elseif($parent > 0){
				$to		= 0;
				$text	= '返回第一级';
			}else{
				$to		= null;
				$text	= '显示所有';
			}

			$extra	= '<div class="alignleft actions"><a href="'.$this->get_edit_tags_link(['parent'=>$to]).'" class="button button-primary list-table-href">'.$text.'</a></div>';
		}

		if($extra = apply_filters('wpjam_terms_extra_tablenav', $extra, $this->taxonomy)){
			$html	= preg_replace('#(<div class="tablenav top">\s+?<div class="alignleft actions bulkactions">.*?</div>)#is', '$1 '.$extra, $html);
		}

		return $html;
	}

	public function single_row($raw_item){
		if($term = is_numeric($raw_item) ? get_term($raw_item) : $raw_item){
			$level	= wpjam_get_term_level($term);

			$this->wp_list_table()->single_row($term, $level);
		}
	}

	public function filter_row_actions($row_actions, $term){
		if(!in_array('slug', $this->get_setting('supports'))){
			unset($row_actions['inline hide-if-no-js']);
		}

		$row_actions	= array_merge($row_actions, $this->get_row_actions($term->term_id));

		if(isset($row_actions['children'])){
			$parent	= $this->get_parent();

			if((empty($parent) || $parent != $term->term_id) && get_term_children($term->term_id, $term->taxonomy)){
				$row_actions['children']	= '<a href="'.$this->get_edit_tags_link(['parent'=>$term->term_id]).'">下一级</a>';
			}else{
				unset($row_actions['children']);
			}
		}

		foreach(['delete', 'view'] as $key){
			if($row_action = wpjam_array_pull($row_actions, $key)){
				$row_actions[$key]	= $row_action;
			}
		}

		return array_merge($row_actions, ['term_id'=>'ID：'.$term->term_id]);
	}

	public function filter_columns($columns){
		$columns	= parent::filter_columns($columns);

		foreach(['slug', 'description'] as $key){
			if(!in_array($key, $this->get_setting('supports'))){
				unset($columns[$key]);
			}
		}

		return $columns;
	}

	public function filter_per_page($per_page){
		$parent	= $this->get_parent();

		return is_null($parent) ? $per_page : 9999;
	}

	public function filter_pre_insert_term($term, $taxonomy){
		if($this->get_setting('levels') && $taxonomy == $this->taxonomy){
			if(!empty($_POST['parent']) && $_POST['parent'] != -1){
				if(wpjam_get_term_level($_POST['parent']) >= $this->get_setting('levels') - 1){
					return new WP_Error('invalid_parent', '不能超过'.$this->get_setting('levels').'级');
				}
			}
		}

		return $term;
	}

	public function sort_column_callback($term_id){
		$parent	= $this->get_parent();
		
		if(is_null($parent) || wpjam_get_data_parameter('orderby') || wpjam_get_data_parameter('s')){
			return wpjam_admin_tooltip('<span class="dashicons dashicons-editor-help"></span>', '如要进行排序，请先点击「只显示第一级」按钮。');
		}elseif(get_term($term_id)->parent == $parent){
			$sortable_row_actions	= '';

			foreach(['move', 'up', 'down'] as $action_key){
				$sortable_row_actions	.= '<span class="'.$action_key.'">'.wpjam_get_list_table_row_action($action_key, ['id'=>$term_id]).'</span>';
			}

			return '<div class="row-actions">'.$sortable_row_actions.'</div>';
		}else{
			return '';
		}
	}

	public function on_parse_term_query($term_query){
		if(!in_array('WP_Terms_List_Table', array_column(debug_backtrace(), 'class'))){
			return;
		}

		$term_query->query_vars['list_table_query']	= true;

		if($sortable_columns = $this->get_sortable_columns()){
			$orderby	= $term_query->query_vars['orderby'];

			if($orderby && isset($sortable_columns[$orderby])){
				if($object = WPJAM_List_Table_Column::get($orderby)){
					$orderby_type	= $object->sortable_column ?? 'meta_value';

					if(in_array($orderby_type, ['meta_value_num', 'meta_value'])){
						$term_query->query_vars['meta_key']	= $orderby;
						$term_query->query_vars['orderby']	= $orderby_type;
					}else{
						$term_query->query_vars['orderby']	= $orderby;
					}
				}
			}
		}

		if($this->get_setting('hierarchical')){
			$parent	= $this->get_parent();
			
			if($parent){
				$hierarchy	= _get_term_hierarchy($this->taxonomy);
				$term_ids	= $hierarchy[$parent] ?? [];
				$term_ids[]	= $parent;

				if($ancestors = get_ancestors($parent, $this->taxonomy)){
					$term_ids	= array_merge($term_ids, $ancestors);
				}

				$term_query->query_vars['include']	= $term_ids;
				// $term_query->query_vars['pad_counts']	= true;
			}elseif($parent === 0){
				$term_query->query_vars['parent']	= $parent;
			}
		}
	}
}

class WPJAM_Users_List_Table extends WPJAM_Builtin_List_Table{
	public function __construct(){
		if(wp_doing_ajax()){
			add_action('wp_ajax_wpjam-list-table-action', [$this, 'ajax_response']);
		}else{
			add_filter('wpjam_html',	[$this, 'filter_html']);
		}

		add_filter('user_row_actions',	[$this, 'filter_row_actions'], 1, 2);

		add_filter('manage_users_columns',			[$this, 'filter_columns']);
		add_filter('manage_users_custom_column',	[$this, 'filter_custom_column'], 10, 3);
		add_filter('manage_users_sortable_columns',	[$this, 'filter_sortable_columns']);

		$this->_args	= $this->parse_args([
			'title'			=> '用户',
			'singular'		=> 'user',
			'capability'	=> 'edit_user',
			'data_type'		=> 'user',
			'model'			=> 'WPJAM_User',
			'builtin_class'	=> 'WP_Users_List_Table'
		]);
	}

	protected function filter_fields($fields, $key, $id){
		if($key && $id && !is_array($id)){
			$fields	= array_merge(['name'=>['title'=>'用户', 'type'=>'view', 'value'=>get_userdata($id)->display_name]], $fields);
		}

		return $fields;
	}

	public function single_row($raw_item){
		if($user = is_numeric($raw_item) ? get_userdata($raw_item) : $raw_item){
			echo $this->wp_list_table()->single_row($raw_item);
		}
	}

	public function filter_row_actions($row_actions, $user){
		foreach($this->get_row_actions($user->ID) as $key => $row_action){
			$action	= WPJAM_List_Table_Action::get($key);

			if(is_null($action->roles) || array_intersect($user->roles, (array)$action->roles)){
				$row_actions[$key]	= $row_action;
			}
		}

		foreach(['delete', 'remove', 'view'] as $key){
			if($row_action = wpjam_array_pull($row_actions, $key)){
				$row_actions[$key]	= $row_action;
			}
		}

		return array_merge($row_actions, ['id'=>'ID: '.$user->ID]);
	}
}