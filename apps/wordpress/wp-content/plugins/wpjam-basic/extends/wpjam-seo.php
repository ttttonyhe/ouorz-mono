<?php
/*
Name: 简单 SEO
URI: https://mp.weixin.qq.com/s/LzGWzKCEl5SdJCQdBvFipg
Description: 最简单快捷的方式设置 WordPress 站点的 SEO。
Version: 1.0
*/
class WPJAM_SEO extends WPJAM_Option_Model{
	public static function sanitize_callback($value){
		flush_rewrite_rules();

		return $value;
	}

	public static  function get_sections(){
		if(file_exists(ABSPATH.'robots.txt')){
			$robots_type	= 'view';
			$robots_value	= '博客的根目录下已经有 robots.txt 文件。<br />请直接编辑或者删除之后在后台自定义。';
		}else{
			$robots_type	= 'textarea';
			$robots_value	= self::get_default_robots();
		}

		if(file_exists(ABSPATH.'sitemap.xml')){
			$wpjam_sitemap	= '博客的根目录下已经有 sitemap.xml 文件。<br />删除之后才能使用插件自动生成的 sitemap.xml。';
		}else{
			$wpjam_sitemap	= '<table>
				<tr><td style="padding:0 10px 8px 0;">首页/分类/标签：</td><td style="padding:0 10px 8px 0;"><a href="'.home_url('/sitemap.xml').'" target="_blank">'.home_url('/sitemap.xml').'</a></td></tr>
				<tr><td style="padding:0 10px 8px 0;">前1000篇文章：</td><td style="padding:0 10px 8px 0;"><a href="'.home_url('/sitemap-1.xml').'" target="_blank">'.home_url('/sitemap-1.xml').'</a></td></tr>
				<tr><td style="padding:0 10px 8px 0;">1000-2000篇文章：</td><td style="padding:0 10px 8px 0;"><a href="'.home_url('/sitemap-2.xml').'" target="_blank">'.home_url('/sitemap-2.xml').'</a></td></tr>
				<tr><td style="padding:0 10px 8px 0;" colspan=2>以此类推...</a></td></tr>
			</table>';
		}

		$wp_sitemap	= '<a href="'.home_url('/wp-sitemap.xml').'" target="_blank">'.home_url('/wp-sitemap.xml').'</a>';
		$auto_view	= '文章摘要作为页面的 Meta Description，文章的标签作为页面的 Meta Keywords。<br />分类和标签的描述作为页面的 Meta Description，页面没有 Meta Keywords。';
		$unique		= '如果当前主题或其他插件也会生成摘要和关键字，可以通过勾选该选项移除。<br />如果当前主题没有<code>wp_head</code>Hook，也可以通过勾选该选项确保生成摘要和关键字。';

		return [
			'setting'	=> ['title'=>'SEO设置',	'fields'=>[
				'post_set'		=> ['title'=>'文章和分类页',	'type'=>'fieldset',	'fields'=>[
					'individual'	=> ['type'=>'select', 	'options'=>[0=>'自动获取摘要和关键字',1=>'单独设置 SEO TDK。']],
					'auto'			=> ['type'=>'view', 	'show_if'=>['key'=>'individual', 'value'=>'0'],	'value'=>$auto_view],
					'list_table'	=> ['type'=>'select',	'show_if'=>['key'=>'individual', 'value'=>'1'],	'value'=>1,	'options'=>['1'=>'编辑和列表页都可设置','0'=>'仅可在编辑页设置','only'=>'仅可在列表页设置']]
				]],
				'unique'		=> ['title'=>'确保唯一设置',	'type'=>'checkbox',		'description'=>$unique],
				'robots'		=> ['title'=>'robots.txt',	'type'=>$robots_type,	'class'=>'',	'rows'=>10,	'value'=>$robots_value],
				'sitemap_set'	=> ['title'=>'Sitemap',	'type'=>'fieldset',	'fields'=>[
					'sitemap'		=> ['type'=>'select',	'options'=>[0=>'使用 WPJAM 生成的 sitemap', 'wp'=>'使用 WordPress 内置的 sitemap']],
					'wpjam_sitemap'	=> ['type'=>'view',		'value'=>$wpjam_sitemap,	'show_if'=>['key'=>'sitemap',	'value'=>0]],
					'wp_sitemap'	=> ['type'=>'view',		'value'=>$wp_sitemap,		'show_if'=>['key'=>'sitemap',	'value'=>'wp']]
				]]
			]],
			'home'		=> ['title'=>'首页设置',	'fields'=>[
				'home_title'		=> ['title'=>'SEO 标题',		'type'=>'text'],
				'home_description'	=> ['title'=>'SEO 描述',		'type'=>'textarea', 'class'=>''],
				'home_keywords'		=> ['title'=>'SEO 关键字',	'type'=>'text' ],
			]]
		];
	}

	public static function get_menu_page(){
		return [
			'parent'		=> 'wpjam-basic',
			'menu_title'	=> 'SEO设置',
			'page_title'	=> '简单SEO',
			'network'		=> false,
			'function'		=> 'option',
			'summary'		=> __FILE__,
		];
	}

	public static function get_default_robots(){
		$site_url	= parse_url( site_url() );
		$path		= !empty($site_url['path'])  ? $site_url['path'] : '';

		return "User-agent: *
		Disallow: /wp-admin/
		Disallow: /wp-includes/
		Disallow: /cgi-bin/
		Disallow: $path/wp-content/plugins/
		Disallow: $path/wp-content/themes/
		Disallow: $path/wp-content/cache/
		Disallow: $path/author/
		Disallow: $path/trackback/
		Disallow: $path/feed/
		Disallow: $path/comments/
		Disallow: $path/search/";
	}

	public static function sitemap($action){
		$sitemap	= '';

		if(empty($action)){
			$last_mod	= str_replace(' ', 'T', get_lastpostmodified('GMT')).'+00:00';
			$sitemap	.= "\t<url>\n";
			$sitemap	.="\t\t<loc>".home_url()."</loc>\n";
			$sitemap	.="\t\t<lastmod>".$last_mod."</lastmod>\n";
			$sitemap	.="\t\t<changefreq>daily</changefreq>\n";
			$sitemap	.="\t\t<priority>1.0</priority>\n";
			$sitemap	.="\t</url>\n";

			$taxonomies = [];
			foreach (get_taxonomies(['public' => true]) as $taxonomy => $value) {
				if($taxonomy != 'post_format'){
					$taxonomies[]	= $taxonomy;
				}
			}

			$terms	= get_terms(['taxonomy'=>$taxonomies]);

			foreach ($terms as $term) {
				$priority		= ($term->taxonomy == 'category')?0.6:0.4;
				$sitemap	.="\t<url>\n";
				$sitemap	.="\t\t<loc>".get_term_link($term)."</loc>\n";
				$sitemap	.="\t\t<lastmod>".$last_mod."</lastmod>\n";
				$sitemap	.="\t\t<changefreq>daily</changefreq>\n";
				$sitemap	.="\t\t<priority>".$priority."</priority>\n";
				$sitemap	.="\t</url>\n";
			}
		}elseif(is_numeric($action)){
			$post_types = [];

			foreach (get_post_types(['public' => true]) as $post_type => $value) {
				if($post_type != 'page' && $post_type != 'attachment'){
					$post_types[] = $post_type;
				}
			}

			$sitemap_posts	= WPJAM_Query([
				'posts_per_page'	=> 1000,
				'paged'				=> $action,
				'post_type'			=> $post_types,
			])->posts;

			if($sitemap_posts){
				foreach ($sitemap_posts as $sitemap_post) {
					$permalink	= get_permalink($sitemap_post->ID); //$siteurl.$sitemap_post->post_name.'/';
					$last_mod	= str_replace(' ', 'T', $sitemap_post->post_modified_gmt).'+00:00';
					$sitemap	.="\t<url>\n";
					$sitemap	.="\t\t<loc>".$permalink."</loc>\n";
					$sitemap	.="\t\t<lastmod>".$last_mod."</lastmod>\n";
					$sitemap	.="\t\t<changefreq>weekly</changefreq>\n";
					$sitemap	.="\t\t<priority>0.8</priority>\n";
					$sitemap	.="\t</url>\n";
				}
			}
		}else{
			$sitemap = apply_filters('wpjam_'.$action.'_sitemap', '');
		}

		if(!wpjam_doing_debug()){
			header ("Content-Type:text/xml");

			$renderer	= new WP_Sitemaps_Renderer();

			echo '<?xml version="1.0" encoding="UTF-8"?>
		<?xml-stylesheet type="text/xsl" href="'.$renderer->get_sitemap_stylesheet_url().'"?>
		<!-- generated-on="'.date('d. F Y').'" -->
		<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n".$sitemap."\n".'</urlset>'."\n";
		}else{
			global $wpdb;
			echo get_num_queries();echo ' queries in ';timer_stop(1);echo ' seconds.<br>';

			echo '按执行顺序：<br>';
			echo '<pre>';
			var_dump($wpdb->queries);
			echo '</pre>';

			echo '按耗时：<br>';
			echo '<pre>';
			$qs = array();
			foreach($wpdb->queries as $q){
			$qs[''.$q[1].''] = $q;
			}
			krsort($qs);
			print_r($qs);
			echo '</pre>';
		}
		exit;
	}

	public static function get_meta_value($type='title'){
		if(is_front_page()){
			if(get_query_var('paged') < 2 && !wpjam_is_module()){
				$meta_value	= self::get_setting('home_'.$type);
			}
		}elseif(is_tag() || is_category() || is_tax()){
			if(get_query_var('paged') < 2){
				if(self::get_setting('individual')){
					$meta_value	= get_term_meta(get_queried_object_id(), 'seo_'.$type, true);
				}

				if(empty($meta_value) && $type == 'description'){
					$meta_value	= term_description();
				}
			}
		}elseif(is_singular()){
			if(self::get_setting('individual')){				
				$meta_value	= get_post_meta(get_the_ID(), 'seo_'.$type, true);
			}

			if(empty($meta_value)){
				if($type == 'description'){
					$meta_value	= get_the_excerpt();
				}elseif($type == 'keywords'){
					if($tags = get_the_tags()){
						$meta_value = implode(',', wp_list_pluck($tags, 'name'));
					}
				}
			}
		}

		return !empty($meta_value) ? wp_slash(wpjam_get_plain_text($meta_value)) : '';
	}

	public static function filter_document_title($title){
		if($meta_title = self::get_meta_value('title')){
			return $meta_title;
		}

		return $title;
	}

	public static function filter_post_json($post_json, $post_id){
		$post_json['meta_description']	= self::get_meta_value('description');
		$post_json['meta_keywords']		= self::get_meta_value('keywords');

		return $post_json;
	}

	public static function filter_posts_json($posts_json){
		$posts_json['meta_description']	= self::get_meta_value('description');
		$posts_json['meta_keywords']	= self::get_meta_value('keywords');

		return $posts_json;
	}

	public static function filter_robots_txt($output, $public){
		return '0' == $public ? "Disallow: /\n" : self::get_setting('robots');
	}

	public static function filter_html($html){
		$meta_title 	= self::get_meta_value('title');
		$meta_values	= [];

		foreach(['description', 'keywords'] as $type){
			if($meta_value = self::get_meta_value($type)){
				$meta_values[$type]	= "<meta name='{$type}' content='{$meta_value}' />";
			}
		}

		if($meta_values){
			$html	= preg_replace('#<meta\s{1,}name=[\'"]('.implode('|', array_keys($meta_values)).')[\'"]\s{1,}content=[\'"].*?[\'"]\s{1,}\/>#is', '', $html);
		}

		if($meta_title || $meta_values){
			$meta_title		= $meta_title ?: '\1';
			$meta_values	= $meta_values ? "\n\n".implode("\n", $meta_values)."\n\n" : '';

			return preg_replace('#<title>(.*?)<\/title>#is', '<title>'.$meta_title.'</title>'.$meta_values, $html);
		}

		return $html;
	}

	public static function on_wp_head(){
		remove_action('wp_head', '_wp_render_title_tag', 1);

		if($meta_title = self::get_meta_value('title')){
			echo '<title>'.$meta_title.'</title>'."\n";
		}else{
			_wp_render_title_tag();
		}

		foreach(['description', 'keywords'] as $type){
			if($meta_value = self::get_meta_value($type)){
				echo "<meta name='{$type}' content='{$meta_value}' />\n";
			}
		}
	}

	public static function get_fields(){
		return [
			'seo_title'			=> ['title'=>'SEO标题',	'type'=>'text',		'class'=>'large-text',	'placeholder'=>'不填则使用标题'],
			'seo_description'	=> ['title'=>'SEO描述',	'type'=>'textarea'],
			'seo_keywords'		=> ['title'=>'SEO关键字','type'=>'text',		'class'=>'large-text']
		];
	}

	public static function builtin_page_load($screen){
		if(in_array($screen->base, ['edit', 'post']) 
			&& $screen->post_type != 'attachment' 
			&& is_post_type_viewable($screen->post_type)
		){
			wpjam_register_post_option('seo', [
				'title'			=> 'SEO设置',
				'page_title'	=> 'SEO设置',
				'context'		=> 'side',
				'list_table'	=> self::get_setting('list_table', 1),
				'fields'		=> [self::class,'get_fields']
			]);
		}elseif(in_array($screen->base, ['edit-tags', 'term']) 
			&& is_taxonomy_viewable($screen->taxonomy)
		){
			wpjam_register_term_option('seo', [
				'title'			=> 'SEO设置',
				'page_title'	=> 'SEO设置',
				'action'		=> 'edit',
				'submit_text'	=> '设置',
				'list_table'	=> self::get_setting('list_table', 1),
				'fields'		=> [self::class, 'get_fields']
			]);
		}
	}

	public static function init(){
		if(WPJAM_SEO::get_setting('sitemap') == 0){
			add_rewrite_rule($GLOBALS['wp_rewrite']->root.'sitemap\.xml?$', 'index.php?module=sitemap', 'top');
			add_rewrite_rule($GLOBALS['wp_rewrite']->root.'sitemap-(.*?)\.xml?$', 'index.php?module=sitemap&action=$matches[1]', 'top');

			wpjam_register_route_module('sitemap', ['callback'=>[self::class, 'sitemap']]);
		}

		if(WPJAM_SEO::get_setting('unique')){
			add_filter('wpjam_html',	[self::class, 'filter_html']);
		}else{
			add_action('wp_head',		[self::class, 'on_wp_head'], 0);
		}

		add_filter('robots_txt',		[self::class, 'filter_robots_txt'], 10, 2);
		add_filter('document_title',	[self::class, 'filter_document_title']);
		add_filter('wpjam_post_json',	[self::class, 'filter_post_json'], 10, 2);
		add_filter('wpjam_posts_json',	[self::class, 'filter_posts_json']);

		if(is_admin() && self::get_setting('individual')){
			wpjam_register_builtin_page_load('wpjam-seo', [
				'base'		=> ['post','edit', 'edit-tags', 'term'], 
				'callback'	=> [self::class, 'builtin_page_load']
			]);
		}
	}
}

wpjam_register_option('wpjam-seo',	['model'=>'WPJAM_SEO',]);
