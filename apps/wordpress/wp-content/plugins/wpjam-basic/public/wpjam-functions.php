<?php
// 注册基础函数
function wpjam_register($group, $name, $args=[], $priority=10){
	return WPJAM_Register::register_by_group($group, $name, $args, '', $priority);
}

function wpjam_pre_register($group, $name, $args=[]){
	return WPJAM_Register::register_by_group($group, $name, $args, 'pre');
}

function wpjam_unregister($group, $name, $args=[]){
	WPJAM_Register::unregister_by_group($group, $name, $args);
}

function wpjam_unregister_pre($group, $name, $args=[], $type=''){
	WPJAM_Register::unregister_by_group($group, $name, $args, 'pre');
}

function wpjam_get_registereds($group, $type=''){
	return WPJAM_Register::get_by_group($group, $type);
}

function wpjam_get_registered_object($group, $name){
	return WPJAM_Register::get($name, $group);
}

// handler
function wpjam_register_handler($name, $args=[]){
	return WPJAM_Handler::register($name, $args);
}

function wpjam_get_handler($name){
	$object	= WPJAM_Handler::get($name);

	return $object ? $object->handler : null;
}

function wpjam_get_object_by_model($model, $id){
	if(!method_exists($model, 'get_instance')){
		return new WP_Error('model_get_instance_not_exists', $model.'「get_instance」方法未定义');
	}

	$object	= call_user_func([$model, 'get_instance'], $id);

	return $object ?: new WP_Error('model_object_not_exists', $model.'对象无法获取');
}

// LazyLoader
function wpjam_register_lazyloader($name, $args){
	return WPJAM_Lazyloader::register($name, $args);
}

function wpjam_lazyload($name, $ids, ...$args){
	$object	= WPJAM_Lazyloader::get($name);

	if($object){
		$object->queue_objects($ids, ...$args);
	}
}

// Var
function wpjam_get_ip(){
	return WPJAM_Var::get_ip();
}

function wpjam_get_user_agent(){
	return WPJAM_Var::get_user_agent();
}

function wpjam_parse_ip($ip=''){
	return WPJAM_Var::parse_ip($ip);
}

function wpjam_parse_user_agent($user_agent='', $referer=''){
	return WPJAM_Var::parse_user_agent($user_agent, $referer);
}

// Platform
function wpjam_register_platform($name, $args){
	return WPJAM_Platform::register($name, $args);
}

function wpjam_is_platform($platform){
	return WPJAM_Platform::get($platform)->verify();
}

function wpjam_get_current_platform($platforms=[], $type='key'){
	return WPJAM_Platform::get_current($platforms, $type);
}

function wpjam_get_platform_options($type='bit'){
	return WPJAM_Platform::get_options($type);
}

function wpjam_get_current_platforms(){
	return WPJAM_Path::get_platforms();
}

// Path
function wpjam_register_path($page_key, ...$args){
	return WPJAM_Path::create($page_key, ...$args);
}

function wpjam_unregister_path($page_key, $path_type=''){
	if($path_type){
		$object	= WPJAM_Path::get($page_key);

		if($object){
			$object->remove_type($path_type);
		}
	}else{
		WPJAM_Path::unregister($page_key);
	}
}

function wpjam_get_path_object($page_key){
	return WPJAM_Path::get($page_key);
}

function wpjam_get_paths($path_type){
	return WPJAM_Path::get_by(['path_type'=>$path_type]);
}

function wpjam_get_tabbar_options($path_type){
	return WPJAM_Path::get_tabbar_options($path_type);
}

function wpjam_get_path_fields($path_types, $for=''){
	return WPJAM_Path::get_path_fields($path_types, $for);
}

function wpjam_get_page_keys($path_type){
	return WPJAM_Path::get_page_keys($path_type);
}

// wpjam_get_path($path_type, $page_key, $args);
// wpjam_get_path($path_type, $args);
function wpjam_get_path($path_type, ...$args){
	if(is_array($args[0])){
		$args		= $args[0];
		$page_key	= wpjam_array_pull($args, 'page_key');
	}else{
		$page_key	= $args[0];
		$args		= $args[1] ?? [];
	}

	$object	= wpjam_get_path_object($page_key);

	return $object ? $object->get_path($path_type, $args) : '';
}

function wpjam_parse_path_item($item, $path_type, $parse_backup=true){
	$parsed	= WPJAM_Path::parse_item($item, $path_type);

	if(empty($parsed) && $parse_backup && !empty($item['page_key_backup'])){
		$parsed	= WPJAM_Path::parse_item($item, $path_type, true);
	}

	return $parsed ?: ['type'=>'none'];
}

function wpjam_validate_path_item($item, $path_types){
	$result	= WPJAM_Path::validate_item($item, $path_types);

	if(is_wp_error($result) && $result->get_error_code() == 'invalid_page_key' && count($path_types) > 1){
		return WPJAM_Path::validate_item($item, $path_types, true);	
	}

	return $result;
}

function wpjam_get_path_item_link_tag($parsed, $text){
	return WPJAM_Path::get_item_link_tag($parsed, $text);
}

// Data Type
function wpjam_register_data_type($name, $args=[]){
	return WPJAM_Data_Type::register($name, $args);
}

function wpjam_get_data_type_object($name){
	return WPJAM_Data_Type::get($name);
}

function wpjam_parse_data_type_query_args($args){
	return WPJAM_Data_Type::parse_query_args($args);
}

function wpjam_get_data_type_field($name, $args){
	$object	= wpjam_get_data_type_object($name);

	return $object ? $object->get_field($args) : [];
}

function wpjam_get_post_id_field($post_type='post', $args=[]){
	return WPJAM_Post_Type_Data_Type::get_field(array_merge($args, ['post_type'=>$post_type]));
}

function wpjam_get_term_id_field($taxonomy='category', $args=[]){
	return WPJAM_Taxonomy_Data_Type::get_field(array_merge($args, ['taxonomy'=>$taxonomy]));
}

function wpjam_get_taxonomy_query_key($taxonomy){
	return WPJAM_Taxonomy_Data_Type::get_query_key($taxonomy);
}

function wpjam_get_authors($args=[], $return='users'){
	return WPJAM_Author_Data_Type::get_authors($args, $return);
}

function wpjam_get_video_mp4($id_or_url){
	return WPJAM_Video_Data_Type::get_video_mp4($id_or_url);
}

function wpjam_get_qqv_mp4($vid){
	return WPJAM_Video_Data_Type::get_qqv_mp4($vid);
}

function wpjam_get_qqv_id($id_or_url){
	return WPJAM_Video_Data_Type::get_qqv_id($id_or_url);
}

// Setting
function wpjam_get_setting_object($type, $option, $blog_id=0){
	return WPJAM_Setting::get_instance($type, $option, $blog_id);
}

function wpjam_get_setting($option, $name, $blog_id=0){
	return wpjam_get_setting_object('option', $option, $blog_id)->get_setting($name);
}

function wpjam_update_setting($option, $name, $value, $blog_id=0){
	return wpjam_get_setting_object('option', $option, $blog_id)->update_setting($name, $value);
}

function wpjam_delete_setting($option, $name, $blog_id=0){
	return wpjam_get_setting_object('option', $option, $blog_id)->delete_setting($name);
}

function wpjam_get_option($option, $blog_id=0, $default=[]){
	$object	= wpjam_get_setting_object('option', $option, $blog_id);

	return $default !== [] ? $object->get_value($default) : $object->get_option();
}

function wpjam_update_option($option, $value, $blog_id=0){
	return wpjam_get_setting_object('option', $option, $blog_id)->update_option($value);
}

function wpjam_get_site_setting($option, $name){
	return wpjam_get_setting_object('site_option', $option)->get_setting($name);
}

function wpjam_get_site_option($option, $default=[]){
	$object	= wpjam_get_setting_object('site_option', $option);

	return $default !== [] ? $object->get_value($default) : $object->get_option();
}

function wpjam_update_site_option($name, $value){
	return wpjam_get_setting_object('site_option', $name)->update_option($value);
}

function wpjam_sanitize_option_value($value){
	return WPJAM_Setting::sanitize_option($value);
}

// Option
function wpjam_register_option($name, $args=[]){
	return WPJAM_Option_Setting::register($name, $args);
}

function wpjam_get_option_object($name){
	return WPJAM_Option_Setting::get($name);
}

function wpjam_option_get_setting($option, $setting='', $default=null){
	$object = wpjam_get_option_object($option);

	return $object ? $object->get_setting($setting, $default) : $default;
}

function wpjam_option_update_setting($option, $setting, $value){
	$object = wpjam_get_option_object($option);

	return $object ? $object->update_setting($setting, $value) : null;
}

function wpjam_option_delete_setting($option, $setting){
	$object = wpjam_get_option_object($option);

	return $object ? $object->delete_setting($setting) : null;
}

function wpjam_register_extend_option($name, $dir, $args=[]){
	return WPJAM_Extend::create($dir, $args, $name);
}

function wpjam_register_extend_type($name, $dir, $args=[]){
	return wpjam_register_extend_option($name, $dir, $args);
}

function wpjam_load_extends($dir, $args=[]){
	WPJAM_Extend::create($dir, $args);
}

function wpjam_get_file_summary($file){
	return WPJAM_Extend::get_file_summay($file);
}

function wpjam_get_extend_summary($file){
	return WPJAM_Extend::get_file_summay($file);
}

// Permastruct
function wpjam_get_permastruct($name){
	return $GLOBALS['wp_rewrite']->get_extra_permastruct($name);
}

function wpjam_set_permastruct($name, $value){
	return $GLOBALS['wp_rewrite']->extra_permastructs[$name]['struct']	= $value;
}


// Meta Type
function wpjam_register_meta_type($name, $args=[]){
	return WPJAM_Meta_Type::register($name, $args);
}

function wpjam_get_meta_type_object($name){
	return WPJAM_Meta_Type::get($name);
}

function wpjam_get_object_by_meta_type($name, $object_id, ...$args){
	$object	= $object_id ? WPJAM_Meta_Type::get($name) : null;

	return $object ? $object->get_object($object_id, ...$args) : null;
}

function wpjam_get_by_meta($meta_type, ...$args){
	$object	= wpjam_get_meta_type_object($meta_type);

	return $object ? $object->get_by_key(...$args) : [];
}

// wpjam_get_metadata($meta_type, $object_id, $meta_keys)
// wpjam_get_metadata($meta_type, $object_id, $meta_key, $default)
function wpjam_get_metadata($meta_type, $object_id, ...$args){
	$object	= wpjam_get_meta_type_object($meta_type);

	return $object ? $object->get_data_with_default($object_id, ...$args) : null;
}

// wpjam_update_metadata($meta_type, $object_id, $data, $defaults=[])
// wpjam_update_metadata($meta_type, $object_id, $meta_key, $meta_value, $default=null)
function wpjam_update_metadata($meta_type, $object_id, ...$args){
	$object	= wpjam_get_meta_type_object($meta_type);

	return $object ? $object->update_data_with_default($object_id, ...$args) : null;
}


// Post Type
function wpjam_register_post_type($name, $args=[]){
	return WPJAM_Post_Type::create($name, $args);
}

function wpjam_add_post_type_field($post_type, $key, $field){
	$object	= WPJAM_Post_Type::get($post_type);

	return $object ? $object->add_field($key, $field) : null;
}

function wpjam_remove_post_type_field($post_type, $key){
	$object	= WPJAM_Post_Type::get($post_type);

	return $object ? $object->remove_field($key) : null;
}

function wpjam_get_post_type_fields($post_type){
	$object	= WPJAM_Post_Type::get($post_type);

	return $object ? $object->get_fields() : [];
}

function wpjam_get_post_type_setting($post_type, $key, $default=null){
	$object	= WPJAM_Post_Type::get($post_type);

	return $object ? $object->get_arg($key, $default) : $default;
}

function wpjam_update_post_type_setting($post_type, ...$args){
	$object	= WPJAM_Post_Type::get($post_type);

	return $object ? $object->update_arg(...$args) : null;
}


// Post Option
function wpjam_register_post_option($meta_box, $args=[]){
	return WPJAM_Post_Option::register($meta_box, $args);
}

function wpjam_unregister_post_option($meta_box){
	WPJAM_Post_Option::unregister($meta_box);
}

// Post Column
function wpjam_register_posts_column($name, ...$args){
	if(is_admin()){
		$field	= is_array($args[0]) ? $args[0] : ['title'=>$args[0], 'callback'=>($args[1] ?? null)];

		return wpjam_register_list_table_column($name, array_merge($field, ['data_type'=>'post_type']));
	}
}

function wpjam_unregister_posts_column($name){
	if(is_admin() && did_action('current_screen')){
		wpjam_add_current_item(get_current_screen()->id.'_removed_columns', $name);
	}
}

// Post
function wpjam_get_post_object($post){
	return WPJAM_Post::get_instance($post);
}

function wpjam_validate_post($post_id, $post_type=''){
	return WPJAM_Post::validate($post_id, $post_type);
}

function wpjam_get_post($post, $args=[]){
	$object	= wpjam_get_post_object($post);

	return $object ? $object->parse_for_json($args) : null;
}

function wpjam_get_post_views($post=null){
	$object	= wpjam_get_post_object($post);

	return $object ? $object->views : 0;
}

function wpjam_update_post_views($post=null, $addon=1){
	$object	= wpjam_get_post_object($post);

	return $object ? $object->view($addon) : null;
}

function wpjam_get_post_excerpt($post=null, $length=0, $more=null){
	$object	= wpjam_get_post_object($post);

	return $object ? $object->get_excerpt($length, $more) : '';
}

function wpjam_get_post_content($post=null, $raw=false){
	$object	= wpjam_get_post_object($post);

	return $object ? $object->get_content($raw) : '';
}

function wpjam_get_post_images($post=null, $large='', $thumbnail=''){
	$object	= wpjam_get_post_object($post);

	return $object ? $object->get_images($large, $thumbnail) : [];
}

function wpjam_get_post_thumbnail_url($post=null, $size='full', $crop=1){
	$object	= wpjam_get_post_object($post);

	return $object ? $object->get_thumbnail_url($size, $crop) : '';
}

function wpjam_get_post_first_image_url($post=null, $size='full'){
	$object	= wpjam_get_post_object($post);

	return $object ? $object->get_first_image_url($size) : '';
}

function wpjam_get_posts($post_ids, $args=[]){
	$posts = WPJAM_Post::get_by_ids($post_ids, $args);

	return $posts ? array_values($posts) : [];
}


// Post Query
function wpjam_query($args=[]){
	return new WP_Query(wp_parse_args($args, [
		'no_found_rows'			=> true,
		'ignore_sticky_posts'	=> true,
		'cache_it'				=> true
	]));
}

function wpjam_parse_query_vars($query_vars, &$args=[]){
	return WPJAM_Query_Parser::parse_query_vars($query_vars, $args);
}

function wpjam_parse_query($wp_query, $args=[], $parse=true){
	$object	= new WPJAM_Query_Parser($wp_query, $args);

	return $parse ? $object->parse($args) : $object->render($args);
}

function wpjam_render_query($wp_query, $args=[]){
	return wpjam_parse_query($wp_query, $args, false);
}

function wpjam_related_posts($args=[]){
	echo wpjam_get_related_posts(null, $args, false);
}

function wpjam_get_related_posts($post=null, $args=[], $parse=false){
	$wp_query	= wpjam_get_related_posts_query($post, $args);

	if($parse){
		$args['filter']	= 'wpjam_related_post_json';
	}

	return wpjam_parse_query($wp_query, $args, $parse);
}

// wpjam_get_related_posts_query($number);
// wpjam_get_related_posts_query($post_id, $args);
function wpjam_get_related_posts_query(...$args){
	if(count($args) <= 1){
		$post	= get_the_ID();
		$args	= ['number'=>$args[0] ?? 5];
	}else{
		$post	= $args[0];
		$args	= $args[1];
	}

	$object	= wpjam_get_post_object($post);

	return $object ? $object->get_related_query($args) : false;
}

function wpjam_get_new_posts($args=[], $parse=false){
	return wpjam_parse_query([
		'post_type'			=> 'post',
		'post_status'		=> 'publish',
		'posts_per_page'	=> 5,
		'orderby'			=> 'date',
	], $args, $parse);
}

function wpjam_get_top_viewd_posts($args=[], $parse=false){
	return wpjam_parse_query([
		'post_type'			=> 'post',
		'post_status'		=> 'publish',
		'posts_per_page'	=> 5,
		'orderby'			=> 'meta_value_num',
		'meta_key'			=> 'views',
	], $args, $parse);
}

function wpjam_get_related_object_ids($tt_ids, $number, $page=1){
	$id_str		= implode(',', array_map('intval', $tt_ids));
	$cache_key	= 'related_object_ids:'.$id_str.':'.$page.':'.$number;
	$object_ids	= wp_cache_get($cache_key, 'terms');

	if($object_ids === false){
		$object_ids	= $GLOBALS['wpdb']->get_col('SELECT object_id, count(object_id) as cnt FROM '.$GLOBALS['wpdb']->term_relationships.' WHERE term_taxonomy_id IN ('.$id_str.') GROUP BY object_id ORDER BY cnt DESC, object_id DESC LIMIT '.(($page-1) * $number).', '.$number);

		wp_cache_set($cache_key, $object_ids, 'terms', DAY_IN_SECONDS);
	}

	return $object_ids;
}


// Taxonomy
function wpjam_register_taxonomy($name, ...$args){
	return WPJAM_Taxonomy::create($name, ...$args);
}

function wpjam_add_taxonomy_field($taxonomy, $key, $field){
	$object	= WPJAM_Taxonomy::get($taxonomy);

	return $object ? $object->add_field($key, $field) : null;
}

function wpjam_remove_taxonomy_field($taxonomy, $key){
	$object	= WPJAM_Taxonomy::get($taxonomy);

	return $object ? $object->remove_field($key) : null;
}

function wpjam_get_taxonomy_fields($taxonomy){
	$object	= WPJAM_Taxonomy::get($taxonomy);

	return $object ? $object->get_fields() : [];
}

function wpjam_get_taxonomy_setting($taxonomy, $key, $default=null){
	$object	= WPJAM_Taxonomy::get($taxonomy);

	return $object ? $object->get_arg($key, $default) : $default;
}

function wpjam_update_taxonomy_setting($taxonomy, ...$args){
	$object	= WPJAM_Taxonomy::get($taxonomy);

	return $object ? $object->update_arg(...$args) : null;
}


// Term Option
function wpjam_register_term_option($name, $args=[]){
	return WPJAM_Term_Option::register($name, $args);
}

function wpjam_unregister_term_option($name){
	WPJAM_Term_Option::unregister($name);
}

// Term Column
function wpjam_register_terms_column($name, ...$args){
	if(is_admin()){
		$field	= is_array($args[0]) ? $args[0] : ['title'=>$args[0], 'callback'=>($args[1] ?? null)];

		return wpjam_register_list_table_column($name, array_merge($field, ['data_type'=>'taxonomy']));
	}
}

function wpjam_unregister_terms_column($name){
	if(is_admin() && did_action('current_screen')){
		wpjam_add_current_item(get_current_screen()->id.'_removed_columns', $name);
	}
}

// Term
function wpjam_get_term_object($term, $taxonomy=''){
	return WPJAM_Term::get_instance($term, $taxonomy);
}

function wpjam_validate_term($term_id, $taxonomy=''){
	return WPJAM_Term::validate($term_id, $taxonomy);
}

function wpjam_get_term($term, $taxonomy=''){
	$object	= wpjam_get_term_object($term, $taxonomy);

	return $object ? $object->parse_for_json() : null;
}

function wpjam_get_term_thumbnail_url($term=null, $size='full', $crop=1){
	$object	= wpjam_get_term_object($term);

	return $object ? $object->get_thumbnail_url($size, $crop) : '';
}

function wpjam_get_term_level($term){
	$object	= wpjam_get_term_object($term);

	return $object ? $object->get_level() : '';
}

function wpjam_get_terms($args, $max_depth=null){
	return WPJAM_Term::get_terms($args, $max_depth);
}

// User
function wpjam_get_user_object($user){
	return WPJAM_User::get_instance($user);
}

function wpjam_get_user($user, $size=96){
	$object	= wpjam_get_user_object($user);

	return $object ? $object->parse_for_json($size) : null;
}

// Bind
function wpjam_register_bind($type, $appid, $args){
	$object	= wpjam_get_bind_object($type, $appid);

	if($object){
		return $object;
	}

	return WPJAM_Bind::create($type, $appid, $args);
}

function wpjam_get_bind_object($type, $appid){
	return WPJAM_Bind::get($type.':'.$appid);
}

// User Signup
function wpjam_register_user_signup($name, $args){
	return WPJAM_User_Signup::create($name, $args);
}

function wpjam_get_user_signups($args=[], $output='objects', $operator='and'){
	return WPJAM_User_Signup::get_registereds($args, $output, $operator);
}

function wpjam_get_user_signup_object($name){
	return WPJAM_User_Signup::get($name);
}


// Field
function wpjam_fields($fields, $args=[]){
	return (WPJAM_Fields::create($fields))->render($args);
}

function wpjam_validate_fields_value($fields, $values=null){
	if(is_wp_error($fields)){
		return $fields;
	}

	return (WPJAM_Fields::create($fields))->validate($values);
}

function wpjam_prepare_fields_value($fields, $args=[]){
	return (WPJAM_Fields::create($fields))->prepare($args);
}

function wpjam_get_fields_defaults($fields){
	return (WPJAM_Fields::create($fields))->get_defaults();
}

function wpjam_field($field, $args=[]){
	return (WPJAM_Field::create($field))->render($args);
}

function wpjam_validate_field_value($field, $value){
	return (WPJAM_Field::create($field))->validate($value);
}

function wpjam_field_get_icon($name){
	return WPJAM_Field::get_icon($name);
}

function wpjam_attribute_string($attributes, $type=''){
	if($type == 'data'){
		return wpjam_data_attribute_string($attributes);
	}else{
		return WPJAM_Field::generate_attr_string($attributes);
	}
}

function wpjam_data_attribute_string($attributes){
	return WPJAM_Field::generate_data_attr_string($attributes);
}

function wpjam_parse_show_if($args){
	$object	= new WPJAM_Compare($args);

	return $object->key ? $object->args : [];
}

function wpjam_show_if($item, $args){
	if(wp_is_numeric_array($args)){
		foreach($args as $_args){
			if(!(new WPJAM_Compare($_args))->compare($item)){
				return false;
			}
		}

		return true;
	}

	return (new WPJAM_Compare($args))->compare($item);
}

// wpjam_compare($value, $args)
// wpjam_compare($value, $values)
// wpjam_compare($value, $operator, $compare_value);
function wpjam_compare($value, ...$args){
	if(count($args) == 1 || is_array($args[0])){
		$args	= $args[0];

		if(wp_is_numeric_array($args)){
			$args	= ['compare'=>'IN', 'value'=>$args];
		}
	}else{
		$args	= ['compare'=>$args[0], 'value'=>$args[1]];
	}

	return (new WPJAM_Compare($args))->compare($value);
}

// AJAX
function wpjam_register_ajax($name, $args){	
	return WPJAM_AJAX::register($name, $args);
}

function wpjam_get_ajax_object($name){
	return WPJAM_AJAX::get($name);
}

function wpjam_get_ajax_attributes($name, $data=[]){
	$object	= wpjam_get_ajax_object($name);

	return $object ? $object->get_attributes($data) : [];
}

function wpjam_get_ajax_attribute_string($name, $data=[]){
	return wpjam_data_attribute_string(wpjam_get_ajax_attributes($name, $data));
}

function wpjam_ajax_enqueue_scripts(){
	WPJAM_AJAX::enqueue_scripts();
}

// Capability
function wpjam_register_capability($cap, $map_meta_cap){
	return WPJAM_Capability::create($cap, $map_meta_cap);
}

// Cron
function wpjam_register_cron($name, $args=[]){
	if(is_callable($name)){
		return wpjam_register_job($name, $args);
	}

	return WPJAM_Cron::register($name, $args);
}

function wpjam_register_job($name, $args=[]){
	return WPJAM_Job::create($name, $args);
}

function wpjam_is_scheduled_event($hook) {	// 不用判断参数
	return WPJAM_Cron::is_scheduled($hook);
}

// Verification Code
function wpjam_generate_verification_code($key, $group='default'){
	$object	= WPJAM_Verification_Code::get($group);

	return $object ? $object->generate($key) : new WP_Error('invalid_verification_code_group', '无效的验证码分组');
}

function wpjam_verify_code($key, $code, $group='default'){
	$object	= WPJAM_Verification_Code::get($group);

	return $object ? $object->verify($key, $code) : new WP_Error('invalid_verification_code_group', '无效的验证码分组');
}

function wpjam_register_verification_code_group($name, $args=[]){
	return WPJAM_Verification_Code::register($name, $args);
}



function wpjam_register_verify_txt($name, $args){
	return WPJAM_Verify_TXT::register($name, $args);
}

function wpjam_register_gravatar_services($name, $args){
	return WPJAM_Gravatar::register($name, $args);
}

function wpjam_register_google_font_services($name, $args){
	return WPJAM_Google_Font::register($name, $args);
}


// Upgrader
function wpjam_register_plugin_updater($hostname, $update_url){
	return WPJAM_Updater::create('plugin', $hostname, $update_url);
}

function wpjam_register_theme_updater($hostname, $update_url){
	return WPJAM_Updater::create('theme', $hostname, $update_url);
}

// Notice
function wpjam_add_admin_notice($notice, $blog_id=0){
	if(is_multisite()){
		if($blog_id){
			if(!get_site($blog_id)){
				return;
			}
		}else{
			$blog_id	= get_current_blog_id();
		}
	}

	return WPJAM_Notice::get_instance('admin_notice', $blog_id)->insert($notice);
}

function wpjam_add_user_notice($user_id, $notice){
	if(get_userdata($user_id)){
		return WPJAM_Notice::get_instance('user_notice', $user_id)->insert($notice);
	}
}

// Menu Page
function wpjam_add_menu_page($menu_slug, $args=[]){
	if(is_admin()){
		if(!empty($args['menu_title'])){
			WPJAM_Menu_Page::add($menu_slug, $args);
		}
	}else{
		if(isset($args['function']) && $args['function'] == 'option'){
			if(!empty($args['sections']) || !empty($args['fields'])){
				$option_name	= $args['option_name'] ?? $menu_slug;

				wpjam_register_option($option_name, $args);
			}
		}
	}
}


// Image
function wpjam_upload_bits($bits, ...$args){
	if(isset($args[0]) && is_array($args[0])){
		$args	= $arg[0];
	}else{
		$args	= [
			'name'		=> $args[0] ?? '',
			'post_id'	=> $args[1] ?? 0,
			'media'		=> true,
		];
	}

	return WPJAM_Image::upload_bits($bits, $args);
}

function wpjam_download_image($img_url, ...$args){
	if(isset($args[0]) && is_array($args[0])){
		$args	= $args[0];
	}else{
		$args	= [
			'name'		=> $args[0] ?? '',
			'media'		=> $args[1] ?? false,
			'post_id'	=> $args[2] ?? 0,
		];
	}

	return WPJAM_Image::download_external($img_url, $args);
}

function wpjam_fetch_external_images(&$img_urls, ...$args){
	if(isset($args[0]) && is_array($args[0])){
		$args	= $args[0];
	}else{
		$args	= [
			'post_id'	=> $args[0] ?? 0, 
			'media'		=> $args[1] ?? true
		];
	}

	return WPJAM_Image::fetch_external($img_urls, $args);
}

function wpjam_is_image($img_url){
	return WPJAM_Image::is_image($img_url);
}

function wpjam_is_external_image($img_url, $scene=''){
	return WPJAM_Image::is_external($img_url, $scene);
}

function wpjam_remove_prefix($str, $prefix){
	if(str_starts_with($str, $prefix)){
		return substr($str, strlen($prefix));
	}

	return $str;
}

function wpjam_remove_postfix($str, $postfix){
	if(str_ends_with($str, $postfix)){
		return substr($str, 0, strlen($str) - strlen($postfix));
	}

	return $str;
}

function wpjam_unserialize(&$serialized){
	if($serialized){
		$fixed	= preg_replace_callback('!s:(\d+):"(.*?)";!', function($m) {
			return 's:'.strlen($m[2]).':"'.$m[2].'";';
		}, $serialized);

		$unserialized	= unserialize($fixed);

		if($unserialized && is_array($unserialized)){
			$serialized	= $fixed;

			return $unserialized;
		}
	}

	return false;
}

// 去掉非 utf8mb4 字符
function wpjam_strip_invalid_text($text, $charset='utf8mb4'){
	return (new WPJAM_Text($text))->strip_invalid_text($charset);
}

// 去掉 4字节 字符
function wpjam_strip_4_byte_chars($text){
	return (new WPJAM_Text($text))->strip_4_byte_chars();
}

// 去掉控制字符
function wpjam_strip_control_chars($text){
	return (new WPJAM_Text($text))->strip_control_chars();
}

function wpjam_strip_control_characters($text){
	return wpjam_strip_control_chars($text);
}

//获取纯文本
function wpjam_get_plain_text($text){
	return (new WPJAM_Text($text, true))->get_plain();
}

//获取第一段
function wpjam_get_first_p($text){
	return (new WPJAM_Text($text, true))->get_first_p();
}

//中文截取方式
function wpjam_mb_strimwidth($text, $start=0, $width=40, $trimmarker='...', $encoding='utf-8'){
	return (new WPJAM_Text($text, true))->strimwidth($start, $width, $trimmarker, $encoding);
}

function wpjam_unicode_decode($text){
	return (new WPJAM_Text($text))->unicode_decode();
}

function wpjam_zh_urlencode($url){
	return (new WPJAM_Text($url))->zh_urlencode();
}

// 检查非法字符
function wpjam_blacklist_check($text, $name='内容'){
	if($text){
		$pre	= apply_filters('wpjam_pre_blacklist_check', null, $text, $name);

		if(!is_null($pre)){
			return $pre;
		}

		return (new WPJAM_Text($text))->disallowed_check();
	}

	return false;
}

function wpjam_array_push(&$array, $data, $key=null){
	$object	= WPJAM_Array::create($array);

	if($object && $object->push($data, $key)){
		$array	= $object->get_data();
	}
}

function wpjam_array_first($array, $callback=null){
	$object	= WPJAM_Array::create($array);

	return $object ? $object->first($callback) : null;
}

function wpjam_array_get($array, $key, $default=null){
	$object	= WPJAM_Array::create($array);

	return $object ? $object->get($key, $default) : $default;
}

function wpjam_array_pull(&$array, $key, $default=null){
	$object	= WPJAM_Array::create($array);

	if($object){
		$value	= $object->pull($key, $default);
		$array	= $object->get_data();

		return $value;
	}

	return $default;
}

function wpjam_array_except($array, ...$keys){
	$object	= WPJAM_Array::create($array);

	return $object ? $object->except(...$keys) : $array;
}

function wpjam_array_filter($array, $callback, $mode=0){
	$object	= WPJAM_Array::create($array);

	return $object ? $object->filter($callback, $mode) : null;
}

function wpjam_array_merge($array, $data){
	$object	= WPJAM_Array::create($array);

	return $object ? $object->merge($data) : $array;
}

function wpjam_list_sort($list, $orderby='order', $order='DESC'){
	$object	= WPJAM_Array::create($list);

	return $object ? $object->list_sort($orderby, $order) : $list;
}

function wpjam_list_filter($list, $args=[], $operator='AND'){	// 增强 wp_list_filter ，支持 show_if 判断
	$object	= WPJAM_Array::create($list);

	return $object ? $object->list_filter($args, $operator) : $list;
}

function wpjam_list_flatten($list, $depth=0, $args=[]){
	$object	= WPJAM_Array::create($list);

	return $object ? $object->list_flatten($depth, $args) : $list;
}

function wpjam_hex2rgba($color, $opacity=null){
	if($color[0] == '#'){
		$color	= substr($color, 1);
	}

	if(strlen($color) == 6){
		$hex	= [$color[0].$color[1], $color[2].$color[3], $color[4].$color[5]];
	}elseif(strlen($color) == 3) {
		$hex	= [$color[0].$color[0], $color[1].$color[1], $color[2].$color[2]];
	}else{
		return $color;
	}

	$rgb 	=  array_map('hexdec', $hex);

	if(isset($opacity)){
		$opacity	= $opacity > 1 ? 1.0 : $opacity;
		
		return 'rgba('.implode(",",$rgb).','.$opacity.')';
	}else{
		return 'rgb('.implode(",",$rgb).')';
	}
}

function wpjam_generate_random_string($length){
	return WPJAM_Crypt::generate_random_string($length);
}

function wpjam_doing_debug(){
	if(isset($_GET['debug'])){
		return $_GET['debug'] ? sanitize_key($_GET['debug']) : true;
	}else{
		return false;
	}
}

function wpjam_parse_shortcode_attr($str, $tagnames=null){
	$pattern = get_shortcode_regex([$tagnames]);

	if(preg_match("/$pattern/", $str, $m)){
		return shortcode_parse_atts($m[3]);
	}else{
		return [];
	}
}

function wpjam_get_current_page_url(){
	return set_url_scheme('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
}

function wpjam_human_time_diff($from, $to=0){
	$to	= $to ?: time();

	if($to - $from > 0){
		return sprintf(__('%s ago'), human_time_diff($from, $to));
	}else{
		return sprintf(__('%s from now'), human_time_diff($to, $from));
	}
}

function wpjam_human_date_diff($from, $to=0){
	$to		= $to ? new DateTimeImmutable($to) : new DateTime('today');
	$from	= new DateTimeImmutable($from);
	$diff	= $to->diff($from);
	$days	= (int)$diff->format('%R%a');

	if($days == 0){
		return '今天';
	}elseif($days == -1){
		return '昨天';
	}elseif($days == -2){
		return '前天';
	}elseif($days == 1){
		return '明天';
	}elseif($days == 2){
		return '后天';
	}

	$week_day	= __($from->format('l'));
	$week_diff	= $from->format('W') - $to->format('W');

	if($week_diff == 0){
		return $week_day;
	}elseif($week_diff == -1){
		// return '上'.$week_day;
	}elseif($week_diff == 1){
		// return '下'.$week_day;
	}

	return $from->format('m月d日');
}

// 打印
function wpjam_print_r($value){
	$capability	= is_multisite() ? 'manage_site' : 'manage_options';

	if(current_user_can($capability)){
		echo '<pre>';
		print_r($value);
		echo '</pre>'."\n";
	}
}

function wpjam_var_dump($value){
	$capability	= is_multisite() ? 'manage_site' : 'manage_options';
	if(current_user_can($capability)){
		echo '<pre>';
		var_dump($value);
		echo '</pre>'."\n";
	}
}

function wpjam_pagenavi($total=0, $echo=true){
	$args = [
		'prev_text'	=> '&laquo;',
		'next_text'	=> '&raquo;'
	];

	if(!empty($total)){
		$args['total']	= $total;
	}

	$result	= '<div class="pagenavi">'.paginate_links($args).'</div>';

	if($echo){
		echo $result;
	}else{
		return $result; 
	}
}

function wpjam_localize_script($handle, $object_name, $l10n ){
	wp_localize_script($handle, $object_name, ['l10n_print_after' => $object_name.' = '.wpjam_json_encode($l10n)]);
}

function wpjam_is_mobile_number($number){
	return preg_match('/^0{0,1}(1[3,5,8][0-9]|14[5,7]|166|17[0,1,3,6,7,8]|19[8,9])[0-9]{8}$/', $number);
}

function wpjam_set_cookie($key, $value, $expire=DAY_IN_SECONDS){
	$expire	= $expire < time() ? $expire+time() : $expire;

	setcookie($key, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);

	if(COOKIEPATH != SITECOOKIEPATH){
		setcookie($key, $value, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
	}
}

function wpjam_clear_cookie($key){
	setcookie($key, ' ', time() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
	setcookie($key, ' ', time() - YEAR_IN_SECONDS, SITECOOKIEPATH, COOKIE_DOMAIN);
}

function wpjam_get_filter_name($name='', $type=''){
	$filter	= str_replace('-', '_', $name);
	$filter	= str_replace('wpjam_', '', $filter);

	return 'wpjam_'.$filter.'_'.$type;
}

function wpjam_is_login(){
	if(preg_match('#(wp-login\.php)([?/].*?)?$#i', $_SERVER['PHP_SELF'])){
		return true;
	}

	return false;
}

function wpjam_get_filesystem(){
	if(empty($GLOBALS['wp_filesystem'])){
		if(!function_exists('WP_Filesystem')){
			require_once(ABSPATH.'wp-admin/includes/file.php');
		}

		WP_Filesystem();
	}

	return $GLOBALS['wp_filesystem'];
}
