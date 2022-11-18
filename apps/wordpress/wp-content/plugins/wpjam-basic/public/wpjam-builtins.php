<?php
class WPJAM_Basic_Admin{
	public static function on_admin_init(){
		self::add_sub_page('wpjam-posts',	[
			'menu_title'	=> '文章设置',
			'order'			=> 18,
			'function'		=> 'tab',
			'tabs'			=> ['posts'=>[
				'title'			=> '文章设置',
				'function'		=> 'option',
				'option_name'	=> 'wpjam-basic',
				'site_default'	=> true,
				'order'			=> 20,
				'summary'		=> '文章设置优化，增强后台文章列表页和详情页功能。',
			]]
		]);

		self::add_sub_page('wpjam-crons',	[
			'menu_title'	=> '定时作业',
			'order'			=> 9,
			'function'		=> 'tab',
			'tabs'			=> ['crons'=>[
				'title'		=> '定时作业',
				'function'	=> 'list',
				'plural'	=> 'crons',
				'singular'	=> 'cron',
				'model'		=> 'WPJAM_Crons_Admin',
				'order'		=> 20,
				'tab_file'	=> __DIR__.'/wpjam-pages.php'
			]],
		]);

		self::add_sub_page('server-status',	[
			'menu_title'	=> '系统信息',
			'order'			=> 9,
			'function'		=> 'tab',
			'page_file'		=> __DIR__.'/wpjam-pages.php',
			'load_callback'	=> ['WPJAM_Server_Status', 'page_load']
		]);

		self::add_sub_page('dashicons',	[
			'menu_title'	=> 'Dashicons',
			'order'			=> 9,
			'function'		=> 'wpjam_dashicons_page',
			'page_file'		=> __DIR__.'/wpjam-pages.php',
		]);

		self::add_sub_page('wpjam-about',	[
			'menu_title'	=> '关于WPJAM',
			'order'			=> 1,
			'function'		=> 'wpjam_about_page',
			'page_file'		=> __DIR__.'/wpjam-pages.php',
		]);

		if(WPJAM_Plugin_Page::get_registereds(['plugin_page'=>'wpjam-links'])){
			self::add_sub_page('wpjam-links',	[
				'menu_title'	=> '链接设置',
				'order'			=> 16,
				'function'		=> 'tab',
				'network'		=> false
			]);
		}

		if($GLOBALS['plugin_page'] == 'wpjam-grant'){
			self::add_sub_page('wpjam-grant',	[
				'menu_title'	=> '开发设置',
				'load_callback'	=> ['WPJAM_Grants_Admin', 'load_plugin_page'],
				'function'		=> ['WPJAM_Grants_Admin', 'plugin_page'],
				'page_file'		=> __DIR__.'/wpjam-pages.php'
			]);
		}

		if($GLOBALS['plugin_page'] == 'wpjam-errors'){
			self::add_sub_page('wpjam-errors',	[
				'menu_title'	=> '错误代码',
				'function'		=> 'list',
				'function'		=> 'list',
				'plural'		=> 'errors',
				'singular'		=> 'error',
				'model'			=> 'WPJAM_Errors_Admin',
				'page_file'		=> __DIR__.'/wpjam-pages.php'
			]);
		}

		wpjam_register_builtin_page_load('builtin', [
			'callback'	=> ['WPJAM_Basic_Builtin', 'callback'],
		]);
	}

	public static function add_sub_page($slug, $sub){
		wpjam_add_menu_page($slug, array_merge($sub, [
			'parent'	=> 'wpjam-basic',
			'summary'	=> [self::class, 'summary_callback']
		]));
	}

	public static function add_separator(){
		$GLOBALS['menu']['58.88']	= ['',	'read',	'separator'.'58.88', '', 'wp-menu-separator'];
	}

	public static function summary_callback($plugin_page=''){
		$summary	= [
			'wpjam-posts'	=> ['url'=>'https://mp.weixin.qq.com/s/XS3xk-wODdjX3ZKndzzfEg',	'summary'=>'文章设置把文章编辑的一些常用操作，提到文章列表页面，方便设置和操作'],
			'wpjam-crons'	=> ['url'=>'https://mp.weixin.qq.com/s/mSqzZdslhxwkNHGRpa3WmA',	'summary'=>'定时作业让你可以可视化管理 WordPress 的定时作业'],
			'dashicons'		=> ['url'=>'https://mp.weixin.qq.com/s/4BEv7KUDVacrX6lRpTd53g',	'summary'=>'Dashicons 功能列出所有的 Dashicons 以及每个的名称和 HTML 代码'],
			'server-status'	=> ['url'=>'https://mp.weixin.qq.com/s/kqlag2-RWn_n481R0QCJHw',	'summary'=>'系统信息让你在后台一个页面就能够快速实时查看当前的系统状态'],
		];

		$plugin_page	= $plugin_page ?: $GLOBALS['plugin_page'];

		if(isset($summary[$plugin_page])){
			$summary	= $summary[$plugin_page];

			return $summary['summary'].'，详细介绍请点击：<a href="'.$summary['url'].'" target="_blank">'.wpjam_get_plugin_page_setting('menu_title').'</a>。';
		}

		return '';
	}
}

class WPJAM_Basic_Builtin{
	public static function callback($screen){
		$object	= new self($screen);
		$base	= $screen->base;

		if($base == 'edit'){
			$object->load_edit_page();
		}elseif($base == 'upload'){
			$object->load_edit_page();
		}elseif($base == 'post'){
			$object->load_post_page();
		}elseif(in_array($base, ['edit-tags', 'term'])){
			$object->load_term_page();
		}elseif(in_array($base, ['plugins', 'plugins-network'])){
			$object->load_plugins_page();
		}elseif(in_array($base, ['dashboard', 'dashboard-network', 'dashboard-user'])){
			$object->load_dashboard_page();
		}
	}

	protected $screen;

	protected function __construct($screen){
		$this->screen	= $screen;
	}

	public function __get($key){
		return $this->screen->$key ?? null;
	}

	public function __isset($key){
		return $this->$key !== null;
	}

	public function validate($key){
		if($key == 'post_type'){
			$post_type	= $this->post_type;

			if(!$post_type || !get_post_type_object($post_type)){
				return false;
			}

			return $post_type;
		}elseif($key == 'taxonomy'){
			$taxonomy	= $this->taxonomy;

			if(!$taxonomy || !get_taxonomy($taxonomy)){
				return false;
			}

			return $taxonomy;
		}
	}

	public function load_edit_page(){
		$ptype	= $this->validate('post_type');

		if(!$ptype){
			return;
		}

		add_action('restrict_manage_posts',	[$this, 'taxonomy_dropdown'], 1);
		add_action('restrict_manage_posts',	[$this, 'author_dropdown'], 1);
		add_action('restrict_manage_posts',	[$this, 'orderby_dropdown'], 999);
		add_filter('request',				[$this, 'filter_request']);

		$this->set_list_table_option();

		$style	= ['.fixed .column-date{width:8%;}'];

		if($ptype != 'attachment'){
			add_filter('post_column_taxonomy_links',	[$this, 'filter_taxonomy_links'], 10, 3);
			add_filter('wpjam_single_row', 				[$this, 'filter_post_single_row'], 10, 2);

			if(is_object_in_taxonomy($ptype, 'category')){
				add_filter('disable_categories_dropdown', '__return_true');
			}

			$fields	= [];

			$fields['post_title']	= ['title'=>'标题',	'type'=>'text',	'required'];

			if(post_type_supports($ptype, 'excerpt')){
				$fields['post_excerpt']	= ['title'=>'摘要',	'type'=>'textarea',	'class'=>'',	'rows'=>3];
			}

			if(post_type_supports($ptype, 'thumbnail')){
				$fields['_thumbnail_id']	= ['title'=>'头图', 'type'=>'img', 'size'=>'600x0'];
			}

			$fields	= array_merge($fields, wpjam_get_post_type_fields($ptype));

			if(!WPJAM_List_Table_Action::get('set')){
				wpjam_register_list_table_action('set', [
					'title'			=> '设置',
					'page_title'	=> '设置'.get_post_type_object($ptype)->label,
					'fields'		=> $fields,
					'row_action'	=> false
				]);
			}

			if(wpjam_basic_get_setting('upload_external_images')){
				wpjam_register_list_table_action('upload_external_images', [
					'title'			=> '上传外部图片',
					'page_title'	=> '上传外部图片',
					'direct'		=> true,
					'confirm'		=> true,
					'bulk'			=> 2,
					'order'			=> 9,
					'callback'		=> [$this, 'upload_external_images']
				]);
			}

			$style[]	= '#bulk-titles, ul.cat-checklist{height:auto; max-height: 14em;}';

			if($ptype == 'page'){
				wpjam_register_posts_column('template', '模板', 'get_page_template_slug');

				$style[]	= '.fixed .column-template{width:15%;}';
			}elseif($ptype == 'product'){
				if(wpjam_basic_get_setting('post_list_set_thumbnail', 1) && defined('WC_PLUGIN_FILE')){
					wpjam_unregister_posts_column('thumb');
				}
			}
		}

		$width_columns	= [];

		if(post_type_supports($ptype, 'author')){
			$width_columns[]	= '.fixed .column-author';
		}

		foreach(get_object_taxonomies($ptype, 'objects') as $taxonomy => $tax_obj){
			if($tax_obj->show_admin_column){
				$width_columns[]	= '.fixed .column-'.$this->get_taxonomy_column_key($taxonomy);
			}
		}

		$count = count($width_columns);

		if($count){
			$widths		= ['14%',	'12%',	'10%',	'8%',	'7%'];
			$style[]	= implode(',', $width_columns).'{width:'.($widths[$count-1] ?? '6%').'}';
		}

		wp_add_inline_style('list-tables', "\n".implode("\n", $style)."\n");
	}

	public function load_post_page(){
		$ptype	= $this->validate('post_type');

		if(!$ptype){
			return;
		}

		add_filter('post_updated_messages',		[$this, 'filter_post_updated_messages']);
		add_filter('admin_post_thumbnail_html',	[$this, 'filter_admin_thumbnail_html'], 10, 2);
		add_filter('redirect_post_location',	[$this, 'filter_redirect_location']);

		add_filter('post_edit_category_parent_dropdown_args',	[$this, 'filter_edit_category_parent_dropdown_args']);

		$style	= [];

		foreach(get_object_taxonomies($ptype, 'objects') as $taxonomy => $tax_obj){
			if(wpjam_get_taxonomy_setting($taxonomy, 'levels') == 1){
				$style[]	= '#new'.$taxonomy.'_parent{display:none;}';
			}
		}

		if(wpjam_basic_get_setting('disable_trackbacks')){
			$style[]	= 'label[for="ping_status"]{display:none !important;}';
		}

		if($style){
			wp_add_inline_style('list-tables', "\n".implode("\n", $style));
		}
		
		if(wpjam_basic_get_setting('disable_autoembed')){
			if($this->is_block_editor){
				$scripts	= "
				jQuery(function($){
					wp.domReady(function () {
						wp.blocks.unregisterBlockType('core/embed');
					});
				});
				";

				wp_add_inline_script('jquery', $scripts);
			}else{
				remove_action('edit_form_advanced',	[$GLOBALS['wp_embed'], 'maybe_run_ajax_cache']);
				remove_action('edit_page_form',		[$GLOBALS['wp_embed'], 'maybe_run_ajax_cache']);
			}
		}
	}

	public function load_term_page(){
		$taxonomy	= $this->validate('taxonomy');

		if(!$taxonomy){
			return;
		}

		add_filter('term_updated_messages',			[$this, 'filter_term_updated_messages']);
		add_filter('taxonomy_parent_dropdown_args',	[$this, 'filter_parent_dropdown_args'], 10, 3);

		if($this->base == 'edit-tags'){
			add_filter('wpjam_single_row',	[$this, 'filter_term_single_row'], 10, 2);

			$this->set_list_table_option();

			$fields	= wpjam_get_taxonomy_fields($taxonomy);

			if($fields){
				wpjam_register_list_table_action('set_thumbnail', [
					'title'			=> '设置',
					'page_title'	=> '设置缩略图',
					'fields'		=> $fields,
					'row_action'	=> false
				]);
			}

			$style		= [
				'.fixed th.column-slug{ width:16%; }',
				'.fixed th.column-description{width:22%;}',
				'.form-field.term-parent-wrap p{display: none;}',
				'.form-field span.description{color:#666;}'
			];
		}else{
			$style		= [];
		}

		$supports	= wpjam_get_taxonomy_setting($taxonomy, 'supports');

		foreach(['slug', 'description', 'parent'] as $key){ 
			if(!in_array($key, $supports)){
				$style[]	= '.form-field.term-'.$key.'-wrap{display: none;}'."\n";
			}
		}

		wp_add_inline_style('list-tables', "\n".implode("\n", $style));
	}

	public function load_plugins_page(){
		wpjam_register_plugin_updater('blog.wpjam.com', 'https://jam.wpweixin.com/api/template/get.json?name=wpjam-plugin-versions');

		// delete_site_transient( 'update_plugins' );
		// wpjam_print_r(get_site_transient( 'update_plugins' ));
	}

	public function load_dashboard_page(){
		$name	= str_replace(['dashboard', '-'], '', $this->base);
		$action	= $name ? 'wp_'.$name.'_dashboard_setup' : 'wp_dashboard_setup';

		add_action($action,	[$this, 'on_dashboard_setup'], 1);
	}

	public function get_taxonomy_column_key($taxonomy){
		if('category' === $taxonomy) {
			return 'categories';
		}elseif('post_tag' === $taxonomy){
			return 'tags';
		}else{
			return 'taxonomy-'.$taxonomy;
		}
	}

	public function set_list_table_option(){
		if($this->base == 'edit' && $this->is_wc_shop_post_type()){
			$ajax	= false;
		}else{
			$scripts	= '';

			if(wpjam_basic_get_setting('post_list_ajax', 1)){
				$ajax		= true;
				$scripts	= "
				jQuery(function($){
					$(window).load(function(){
						if($('#the-list').length){
							$.wpjam_delegate_events('#the-list', '.editinline');
						}

						if($('#doaction').length){
							$.wpjam_delegate_events('#doaction');
						}
					});
				})
				";
			}else{
				$ajax	= false;
			}

			$scripts	.= "
			jQuery(function($){
				let observer = new MutationObserver(function(mutations){
					if($('#the-list .inline-editor').length > 0){
						let tr_id	= $('#the-list .inline-editor').attr('id');

						if(tr_id == 'bulk-edit'){
							$('#the-list').trigger('bulk_edit');
						}else{
							let id	= tr_id.split('-')[1];

							if(id > 0){
								$('#the-list').trigger('quick_edit', id);
							}
						}
					}
				});

				observer.observe(document.querySelector('body'), {childList: true, subtree: true});
			});
			";
			wp_add_inline_script('jquery', $scripts);
		}

		$this->screen->add_option('wpjam_list_table', ['ajax'=>$ajax, 'form_id'=>'posts-filter']);
	}

	public function upload_external_images($post_id){
		$content	= get_post($post_id)->post_content;
		$bulk		= (int)wpjam_get_parameter('bulk', ['method'=>'POST']);

		if(preg_match_all('/<img.*?src=[\'"](.*?)[\'"].*?>/i', $content, $matches)){
			$img_urls	= array_unique($matches[1]);
			$replace	= wpjam_fetch_external_images($img_urls, $post_id);

			if($replace){
				$content	= str_replace($img_urls, $replace, $content);

				return wp_update_post(['post_content'=>$content, 'ID'=>$post_id], true);
			}else{
				return $bulk == 2 ? true : new WP_Error('no_external_images', '文章中无外部图片');
			}
		}

		return $bulk == 2 ? true : new WP_Error('no_images', '文章中无图片');
	}

	public function term_edit_link_replace($link, $term_id){
		$term		= get_term($term_id);
		$taxonomy	= $term->taxonomy;

		$query_var	= get_taxonomy($taxonomy)->query_var;
		$query_key	= wpjam_get_taxonomy_query_key($taxonomy);
		$query_str	= $query_var ? $query_var.'='.$term->slug : 'taxonomy='.$taxonomy.'&#038;term='.$term->slug;

		return str_replace($query_str, $query_key.'='.$term->term_id, $link);
	}

	public function is_wc_shop_post_type(){
		return defined('WC_PLUGIN_FILE') && in_array($this->post_type, ['shop_order', 'shop_coupon', 'shop_webhook']);
	}

	public function taxonomy_dropdown($ptype){
		foreach(get_object_taxonomies($ptype, 'objects') as $taxonomy => $tax_obj){
			$filterable	= wpjam_get_taxonomy_setting($taxonomy, 'filterable', ($taxonomy == 'category' ? true : false));

			if(empty($filterable) || empty($tax_obj->show_admin_column)){
				continue;
			}

			$query_key	= wpjam_get_taxonomy_query_key($taxonomy);
			$selected	= wpjam_get_data_parameter($query_key);

			if(is_null($selected)){
				if($query_var = $tax_obj->query_var){
					$term_slug	= wpjam_get_data_parameter($query_var);
				}elseif(wpjam_get_data_parameter('taxonomy') == $taxonomy){
					$term_slug	= wpjam_get_data_parameter('term');
				}else{
					$term_slug	= '';
				}

				$term 		= $term_slug ? get_term_by('slug', $term_slug, $taxonomy) : null;
				$selected	= $term ? $term->term_id : '';
			}

			if($tax_obj->hierarchical){
				wp_dropdown_categories([
					'taxonomy'			=> $taxonomy,
					'show_option_all'	=> $tax_obj->labels->all_items,
					'show_option_none'	=> '没有设置',
					'option_none_value'	=> 'none',
					'name'				=> $query_key,
					'selected'			=> $selected,
					'hierarchical'		=> true
				]);
			}else{
				echo wpjam_field([
					'key'			=> $query_key,
					'value'			=> $selected,
					'type'			=> 'text',
					'data_type'		=> 'taxonomy',
					'taxonomy'		=> $taxonomy,
					'placeholder'	=> '请输入'.$tax_obj->label,
					'title'			=> '',
					'class'			=> ''
				]);
			}
		}
	}

	public function author_dropdown($ptype){
		if(wpjam_basic_get_setting('post_list_author_filter', 1) && post_type_supports($ptype, 'author')){
			wp_dropdown_users(wpjam_get_authors([
				'name'						=> 'author',
				'orderby'					=> 'post_count',
				'order'						=> 'DESC',
				'hide_if_only_one_author'	=> true,
				'show_option_all'			=> $ptype == 'attachment' ? '所有上传者' : '所有作者',
				'selected'					=> (int)wpjam_get_data_parameter('author')
			], 'args'));
		}
	}

	public function orderby_dropdown($ptype){
		if(wpjam_basic_get_setting('post_list_sort_selector', 1) && !$this->is_wc_shop_post_type()){
			$options		= [''=>'排序','ID'=>'ID'];
			$wp_list_table	= _get_list_table('WP_Posts_List_Table', ['screen'=>$this->id]);

			list($columns, $hidden, $sortable_columns)	= $wp_list_table->get_column_info();

			foreach($sortable_columns as $sortable_column => $data){
				if(isset($columns[$sortable_column])){
					$options[$data[0]]	= $columns[$sortable_column];
				}
			}

			if($ptype != 'attachment'){
				$options['modified']	= '修改时间';
			}

			$orderby	= wpjam_get_data_parameter('orderby', ['sanitize_callback'=>'sanitize_key']);
			$order		= wpjam_get_data_parameter('order', ['sanitize_callback'=>'sanitize_key', 'default'=>'DESC']);

			echo wpjam_field(['key'=>'orderby',	'type'=>'select',	'value'=>$orderby,	'options'=>$options]);
			echo wpjam_field(['key'=>'order',	'type'=>'select',	'value'	=>$order,	'options'=>['desc'=>'降序','asc'=>'升序']]);
		}
	}

	public function update_dashboard_widget(){
		?>
		<style type="text/css">
			#dashboard_wpjam .inside{margin:0; padding:0;}
			a.jam-post {border-bottom:1px solid #eee; margin: 0 !important; padding:6px 0; display: block; text-decoration: none; }
			a.jam-post:last-child{border-bottom: 0;}
			a.jam-post p{display: table-row; }
			a.jam-post img{display: table-cell; width:40px; height: 40px; margin:4px 12px; }
			a.jam-post span{display: table-cell; height: 40px; vertical-align: middle;}
		</style>
		<div class="rss-widget">
		<?php

		$jam_posts = get_transient('dashboard_jam_posts');

		if($jam_posts === false){
			$response	= wpjam_remote_request('https://jam.wpweixin.com/api/post/list.json', ['timeout'=>1]);

			if(is_wp_error($response)){
				$jam_posts	= [];
			}else{
				$jam_posts	= $response['posts'];
			}

			set_transient('dashboard_jam_posts', $jam_posts, 12 * HOUR_IN_SECONDS );
		}

		if($jam_posts){
			$i = 0;
			foreach ($jam_posts as $jam_post){
				if($i == 5) break;
				echo '<a class="jam-post" target="_blank" href="http://blog.wpjam.com'.$jam_post['post_url'].'"><p>'.'<img src="'.str_replace('imageView2/1/w/200/h/200/', 'imageView2/1/w/100/h/100/', $jam_post['thumbnail']).'" /><span>'.$jam_post['title'].'</span></p></a>';
				$i++;
			}
		}	
		?>
		</div>

		<?php
	}

	public function filter_request($query_vars){
		$tax_query	= [];

		foreach(get_object_taxonomies($this->post_type, 'objects') as $taxonomy => $tax_obj){
			if(!$tax_obj->show_ui){
				continue;
			}

			$tax	= $taxonomy == 'post_tag' ? 'tag' : $taxonomy;

			if($tax != 'category'){
				$tax_id	= wpjam_get_data_parameter($tax.'_id');

				if($tax_id){
					$query_vars[$tax.'_id']	= $tax_id;
				}
			}

			$tax_arg		= ['taxonomy'=>$taxonomy,	'field'=>'term_id'];

			$tax__and		= wpjam_get_data_parameter($tax.'__and',	['sanitize_callback'=>'wp_parse_id_list']);
			$tax__in		= wpjam_get_data_parameter($tax.'__in',		['sanitize_callback'=>'wp_parse_id_list']);
			$tax__not_in	= wpjam_get_data_parameter($tax.'__not_in',	['sanitize_callback'=>'wp_parse_id_list']);

			if($tax__and){
				if(count($tax__and) == 1){
					$tax__in	= is_null($tax__in) ? [] : $tax__in;
					$tax__in[]	= reset($tax__and);
				}else{
					$tax_query[]	= array_merge($tax_arg, ['terms'=>$tax__and,	'operator'=>'AND']);	// 'include_children'	=> false,
				}
			}

			if($tax__in){
				$tax_query[]	= array_merge($tax_arg, ['terms'=>$tax__in]);
			}

			if($tax__not_in){
				$tax_query[]	= array_merge($tax_arg, ['terms'=>$tax__not_in,	'operator'=>'NOT IN']);
			}
		}

		if($tax_query){
			$tax_query['relation']		= wpjam_get_data_parameter('tax_query_relation',	['default'=>'and']);
			$query_vars['tax_query']	= $tax_query;
		}

		return $query_vars;
	}

	public function filter_taxonomy_links($term_links, $taxonomy, $terms){
		$permastruct	= wpjam_get_permastruct($taxonomy);

		if($taxonomy == 'post_format'){
			foreach($term_links as &$term_link){
				$term_link	= str_replace('post-format-', '', $term_link);
			}
		}elseif(empty($permastruct) || strpos($permastruct, '/%'.$taxonomy.'_id%')){
			foreach($terms as $i => $term){
				$term_links[$i]	= $this->term_edit_link_replace($term_links[$i], $term);
			}
		}

		return $term_links;
	}

	public function filter_post_single_row($single_row, $post_id){
		$ptype	= get_post_type($post_id);

		if(wpjam_basic_get_setting('post_list_set_thumbnail', 1) 
			&& (post_type_supports($ptype, 'thumbnail') || post_type_supports($ptype, 'images'))
		){	
			$thumbnail	= get_the_post_thumbnail($post_id, [50,50]) ?: '<span class="no-thumbnail">暂无图片</span>';
			$thumbnail	= wpjam_get_list_table_row_action('set', ['id'=>$post_id, 'class'=>'wpjam-thumbnail-wrap', 'title'=>$thumbnail, 'fallback'=>true]);
			$single_row	= str_replace('<a class="row-title" ', $thumbnail.'<a class="row-title" ', $single_row);
		}

		if(wpjam_basic_get_setting('post_list_ajax', 1)){
			$quick_edit	= '<a title="快速编辑" href="javascript:;" class="editinline row-action"><span class="dashicons dashicons-edit"></span></a>';

			if(post_type_supports($ptype, 'author')){
				$single_row = preg_replace('/(<td class=\'author column-author\' .*?>)(.*?)(<\/td>)/is', '$1$2 '.$quick_edit.'$3', $single_row);
			}

			foreach(get_object_taxonomies($ptype, 'objects') as $taxonomy => $tax_obj){
				if($tax_obj->show_in_quick_edit){
					$column_key	= $this->get_taxonomy_column_key($taxonomy);
					$single_row	= preg_replace('/(<td class=\''.$column_key.' column-'.$column_key.'\' .*?>)(.*?)(<\/td>)/is', '$1$2 '.$quick_edit.'$3', $single_row);
				}
			}
		}

		return $single_row;
	}

	public function filter_post_updated_messages($messages){
		$ptype	= $this->post_type;
		$pt_obj	= get_post_type_object($ptype);
		$key	= $pt_obj->hierarchical ? 'page' : 'post';

		if(isset($messages[$key])){
			$search		= $key == 'post' ? '文章':'页面';
			$replace	= $pt_obj->labels->name;

			foreach($messages[$key] as &$message){
				$message	= str_replace($search, $replace, $message);
			}
		}

		return $messages;
	}

	public function filter_admin_thumbnail_html($content, $post_id){
		if($post_id){
			$ptype		= get_post_type($post_id);
			$size		= wpjam_get_post_type_setting($ptype, 'thumbnail_size');
			$content	.= $size ? wpautop('尺寸：'.$size) : '';
		}

		return $content;
	}

	public function filter_redirect_location($location){
		if(parse_url($location, PHP_URL_FRAGMENT)){
			return $location;
		}

		if($fragment = parse_url(wp_get_referer(), PHP_URL_FRAGMENT)){
			return $location.'#'.$fragment;
		}

		return $location;
	}

	public function filter_edit_category_parent_dropdown_args($args){
		$levels	= wpjam_get_taxonomy_setting($args['taxonomy'], 'levels', 0);

		if($levels == 1){
			$args['parent']	= -1;
		}elseif($levels > 1){
			$args['depth']	= $levels - 1;
		}

		return $args;
	}

	public function filter_content_save_pre($content){
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
			return $content;
		}

		if(!preg_match_all('/<img.*?src=\\\\[\'"](.*?)\\\\[\'"].*?>/i', $content, $matches)){
			return $content;
		}

		$img_urls	= array_unique($matches[1]);
		
		if($replace	= wpjam_fetch_external_images($img_urls)){
			if(is_multisite()){
				setcookie('wp-saving-post', $_POST['post_ID'].'-saved', time()+DAY_IN_SECONDS, ADMIN_COOKIE_PATH, false, is_ssl());
			}

			$content	= str_replace($img_urls, $replace, $content);
		}

		return $content;
	}

	public function filter_term_updated_messages($messages){
		$taxonomy	= $this->taxonomy;

		if(!in_array($taxonomy, ['post_tag', 'category'])){
			$label	= get_taxonomy($taxonomy)->labels->name;
			
			foreach($messages['_item'] as $key => $message){
				$messages[$taxonomy][$key]	= str_replace(['项目', 'Item'], [$label, ucfirst($label)], $message);
			}
		}

		return $messages;
	}

	public function filter_parent_dropdown_args($args, $taxonomy, $action_type){
		$levels	= wpjam_get_taxonomy_setting($taxonomy, 'levels', 0);

		if($levels > 1){
			$args['depth']	= $levels - 1;

			if($action_type == 'edit'){
				$term_id	= $args['exclude_tree'];
				$term_level	= wpjam_get_term_level($term_id);

				if($children = get_term_children($term_id, $taxonomy)){
					$child_level	= 0;

					foreach($children as $child){
						$new_child_level	= wpjam_get_term_level($child);

						if($child_level	< $new_child_level){
							$child_level	= $new_child_level;
						}
					}
				}else{
					$child_level	= $term_level;
				}

				$redueced	= $child_level - $term_level;

				if($redueced < $args['depth']){
					$args['depth']	-= $redueced;
				}else{
					$args['parent']	= -1;
				}
			}
		}

		return $args;
	}

	public function filter_term_single_row($single_row, $term_id){
		$taxonomy	= get_term($term_id)->taxonomy;

		if(WPJAM_List_Table_Action::get('set_thumbnail')){
			$thumb_url	= wpjam_get_term_thumbnail_url($term_id, [100, 100]);
			$thumbnail	= $thumb_url ? '<img class="wp-term-image" src="'.$thumb_url.'"'.image_hwstring(50,50).' />' : '<span class="no-thumbnail">暂无图片</span>';
			$thumbnail	= wpjam_get_list_table_row_action('set_thumbnail', ['id'=>$term_id, 'class'=>'wpjam-thumbnail-wrap', 'title'=>$thumbnail, 'fallback'=>true]);
			$single_row	= str_replace('<a class="row-title" ', $thumbnail.'<a class="row-title" ', $single_row);
		}

		$permastruct	= wpjam_get_permastruct($taxonomy);

		if(empty($permastruct) || strpos($permastruct, '/%'.$taxonomy.'_id%')){
			$single_row	= $this->term_edit_link_replace($single_row, $term_id);
		}

		return $single_row;
	}

	public function filter_dashboard_posts_query($query_args){
		$query_args['post_type']	= get_post_types(['show_ui'=>true, 'public'=>true, '_builtin'=>false])+['post'];;
		$query_args['cache_it']		= true;

		return $query_args;
	}

	public function on_pre_get_comments($query){
		$query->query_vars['post_type']	= get_post_types(['show_ui'=>true, 'public'=>true, '_builtin'=>false])+['post'];;
		$query->query_vars['type']		= 'comment';
	}

	public function on_dashboard_setup(){
		remove_meta_box('dashboard_primary', $this->screen, 'side');

		if(is_multisite() && !is_user_member_of_blog()){
			remove_meta_box('dashboard_quick_press', $this->screen, 'side');
		}

		add_filter('dashboard_recent_posts_query_args',		[$this, 'filter_dashboard_posts_query']);
		add_filter('dashboard_recent_drafts_query_args',	[$this, 'filter_dashboard_posts_query']);

		add_action('pre_get_comments',	[$this, 'on_pre_get_comments']);
		
		$widgets	= apply_filters('wpjam_dashboard_widgets', ['wpjam_update'=>[
			'title'		=> 'WordPress资讯及技巧',
			'context'	=> 'side',	// 位置，normal 左侧, side 右侧
			'callback'	=> [$this, 'update_dashboard_widget']
		]]);

		foreach($widgets as $widget_id => $widget){
			$title		= $widget['title'];
			$callback	= $widget['callback'] ?? wpjam_get_filter_name($widget_id, 'dashboard_widget_callback');
			$context	= $widget['context'] ?? 'normal';	// 位置，normal 左侧, side 右侧
			$args		= $widget['args'] ?? [];

			add_meta_box($widget_id, $title, $callback, $this->screen, $context, 'core', $args);
		}
	}
}

class WPJAM_Verify{
	public static function verify(){
		$verify_user	= get_user_meta(get_current_user_id(), 'wpjam_weixin_user', true);

		if(empty($verify_user) || empty($verify_user['subscribe'])){
			return false;
		}elseif(time() - $verify_user['last_update'] < DAY_IN_SECONDS){
			return true;
		}

		$openid		= $verify_user['openid'];
		$hash		= $verify_user['hash']	?? '';
		$user_id	= get_current_user_id();

		if(get_transient('fetching_wpjam_weixin_user_'.$openid)){
			return false;
		}

		set_transient('fetching_wpjam_weixin_user_'.$openid, 1, 10);

		if($hash){
			$response	= wpjam_remote_request('http://wpjam.wpweixin.com/api/weixin/verify.json', [
				'method'	=> 'POST',
				'body'		=> ['openid'=>$openid, 'hash'=>$hash]
			]);
		}else{
			$response	= wpjam_remote_request('http://jam.wpweixin.com/api/topic/user/get.json?openid='.$openid);
		}

		if(is_wp_error($response) && $response->get_error_code() != 'invalid_openid'){
			$failed_times	= (int)get_user_meta($user_id, 'wpjam_weixin_user_failed_times');
			$failed_times ++;

			if($failed_times >= 3){	// 重复三次
				delete_user_meta($user_id, 'wpjam_weixin_user_failed_times');
				delete_user_meta($user_id, 'wpjam_weixin_user');
			}else{
				update_user_meta($user_id, 'wpjam_weixin_user_failed_times', $failed_times);
			}

			return false;
		}

		if($hash){
			$verify_user	= $response;
		}else{
			$verify_user	= $response['user'];
		}

		delete_user_meta($user_id, 'wpjam_weixin_user_failed_times');

		if(empty($verify_user) || !$verify_user['subscribe']){
			delete_user_meta($user_id, 'wpjam_weixin_user');

			return false;
		}else{
			update_user_meta($user_id, 'wpjam_weixin_user', array_merge($verify_user, ['last_update'=>time()]));

			return true;
		}
	}

	public static function page_load(){
		wpjam_register_page_action('verify_wpjam', [
			'submit_text'	=> '验证',
			'response'		=> 'redirect',
			'callback'		=> [self::class, 'ajax_callback'],
			'fields'		=> [
				'qr_set'	=> ['title'=>'1. 二维码',	'type'=>'fieldset',	'fields'=>[
					'qrcode_view'	=> ['type'=>'view',	'value'=>'使用微信扫描下面的二维码：'],
					'qrcode2'		=> ['type'=>'view',	'value'=>'<img src="https://open.weixin.qq.com/qr/code?username=wpjamcom" style="max-width:250px;" />']
				]],
				'keyword'	=> ['title'=>'2. 关键字',	'type'=>'view',	'value'=>'回复关键字「<strong>验证码</strong>」。'],
				'code_set'	=> ['title'=>'3. 验证码',	'type'=>'fieldset',	'fields'=>[
					'code_view'		=> ['type'=>'view',	'value'=>'将获取验证码输入提交即可！'],
					'code'			=> ['type'=>'number',	'class'=>'all-options',	'description'=>'验证码5分钟内有效！'],
				]],
				'notes'		=> ['title'=>'4. 注意事项',	'type'=>'view',	'value'=>'验证码5分钟内有效！<br /><br />如果验证不通过，请使用 Chrome 浏览器验证，并在验证之前清理浏览器缓存。'],
			]
		]);

		wp_add_inline_style('list-tables', "\n".'.form-table th{width: 100px;}');
	}

	public static function ajax_callback(){
		// $url	= 'http://jam.wpweixin.com/api/weixin/qrcode/verify.json';
		$url	= 'https://wpjam.wpweixin.com/api/weixin/verify.json';
		$data	= wpjam_get_parameter('data', ['method'=>'POST', 'sanitize_callback'=>'wp_parse_args']);

		$verify_user	= wpjam_remote_request($url, [
			'method'	=> 'POST',
			'body'		=> $data
		]);

		if(is_wp_error($verify_user)){
			return $verify_user;
		}

		update_user_meta(get_current_user_id(), 'wpjam_weixin_user', array_merge($verify_user, ['last_update'=>time()]));

		return ['url'=>admin_url('admin.php?page=wpjam-extends')];
	}

	public static function on_admin_init(){
		$menu_filter	= (is_multisite() && is_network_admin()) ? 'wpjam_network_pages' : 'wpjam_pages';

		if(get_transient('wpjam_basic_verify')){
			add_filter($menu_filter, [self::class, 'filter_menu_pages']);
		}elseif(self::verify()){
			if(isset($_GET['unbind_wpjam_user'])){
				delete_user_meta(get_current_user_id(), 'wpjam_weixin_user');

				wp_redirect(admin_url('admin.php?page=wpjam-verify'));
			}
		}else{
			add_filter($menu_filter, [self::class, 'filter_menu_pages']);

			wpjam_add_menu_page('wpjam-verify', [
				'parent'		=> 'wpjam-basic',
				'order'			=> 3,
				'menu_title'	=> '扩展管理',
				'page_title'	=> '验证 WPJAM',
				'function'		=> 'form',
				'form_name'		=> 'verify_wpjam',
				'load_callback'	=> [self::class, 'page_load']
			]);
		}
	}

	public static function filter_menu_pages($menu_pages){
		$subs	= &$menu_pages['wpjam-basic']['subs'];

		if(get_transient('wpjam_basic_verify')){
			$subs	= wpjam_array_except($subs, ['wpjam-about']);
		}elseif(!self::verify()){
			$subs	= wp_array_slice_assoc($subs, ['wpjam-basic', 'wpjam-verify']);
		}

		return $menu_pages;
	}
}

add_action('wpjam_admin_init',	['WPJAM_Basic_Admin', 'on_admin_init']);
add_action('wpjam_admin_init',	['WPJAM_Verify', 'on_admin_init'], 11);
add_action('admin_menu',		['WPJAM_Basic_Admin', 'add_separator']);