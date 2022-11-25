<?php
class_alias('WPJAM_Verify_TXT', 'WPJAM_VerifyTXT');
class_alias('WPJAM_Option_Items', 'WPJAM_Option');
class_alias('WPJAM_Items', 'WPJAM_Item');
class_alias('WPJAM_Post', 'WPJAM_PostType');
class_alias('WPJAM_List_Cache', 'WPJAM_ListCache');

if(!function_exists('is_exists')){
	function is_exists($var){
		return isset($var);
	}
}

if(!function_exists('is_blank')){
	function is_blank($var){
		return empty($var) && !is_numeric($var);
	}
}

if(!function_exists('is_populated')){
	function is_populated($var){
		return !is_blank($var);
	}
}

if(!function_exists('update_usermeta_cache')){
	function update_usermeta_cache($user_ids) {
		return update_meta_cache('user', $user_ids);
	}
}

if(!function_exists('get_userdata')){
	function get_userdata($user_id){
		$check	= apply_filters('wpjam_get_userdata', null, $user_id);

		if(null !== $check){
			return $check;
		}

		return get_user_by('id', $user_id);
	}
}

if(!function_exists('get_post_excerpt')){
	function get_post_excerpt($post=null, $excerpt_length=240){
		return wpjam_get_post_excerpt($post, $excerpt_length);
	}
}

if(!function_exists('str_replace_deep')){
	function str_replace_deep($search, $replace, $value){
		return map_deep($value, function($value) use($search, $replace){
			return str_replace($search, $replace, $value);
		});
	}
}

if(!function_exists('user_can_for_blog')){
	function user_can_for_blog($user, $blog_id, $capability, ...$args){
		$switched	= is_multisite() ? switch_to_blog( $blog_id ) : false;
		$can		= user_can( $user, $capability, ...$args );

		if($switched){
			restore_current_blog();
		}

		return $can;
	}
}

if(!function_exists('get_metadata_by_value')){
	function get_metadata_by_value($meta_type, $meta_value, $meta_key=''){
		if($datas = wpjam_get_by_meta($meta_type, ['meta_key'=>$meta_key, 'meta_value'=>$meta_value])){
			return (object)current($datas);
		}

		return false;
	}
}

if(!function_exists('wp_cache_delete_multi')){
	function wp_cache_delete_multi($keys, $group = ''){
		foreach ($keys as $key) {
			wp_cache_delete($key, $group);
		}

		return true;
	}
}

if(!function_exists('wp_cache_get_multi')){
	function wp_cache_get_multi($keys, $group = ''){

		$datas = [];

		foreach ($keys as $key) {
			$datas[$key] = wp_cache_get($key, $group);
		}

		return $datas;
	}
}

if(!function_exists('wp_cache_get_with_cas')){
	function wp_cache_get_with_cas($key, $group = '', &$cas_token=null){
		return wp_cache_get($key, $group);
	}
}

if(!function_exists('wp_cache_cas')){
	function wp_cache_cas($cas_token, $key, $data, $group='', $expire=0){
		return wp_cache_set($key, $data, $group, $expire);
	}
}

if(!function_exists('get_post_type_support_value')){
	function get_post_type_support_value($post_type, $feature){
		$supports	= get_all_post_type_supports($post_type);

		if($supports && isset($supports[$feature])){
			if(is_array($supports[$feature]) && wp_is_numeric_array($supports[$feature]) && count($supports[$feature]) == 1){
				return current($supports[$feature]);
			}else{
				return $supports[$feature];
			}
		}else{
			return false;
		}
	}
}

if(!function_exists('array_key_first')){
	function array_key_first( array $arr ) {
		foreach ( $arr as $key => $value ) {
			return $key;
		}
	}
}

if(!function_exists('array_key_last')){
	function array_key_last( array $arr ) {
		if ( empty( $arr ) ) {
			return null;
		}
		end( $arr );
		return key( $arr );
	}
}

if ( ! function_exists( 'str_contains' ) ) {
	function str_contains( $haystack, $needle ) {
		return ( '' === $needle || false !== strpos( $haystack, $needle ) );
	}
}

if ( ! function_exists( 'str_starts_with' ) ) {
	function str_starts_with( $haystack, $needle ) {
		if ( '' === $needle ) {
			return true;
		}
		return 0 === strpos( $haystack, $needle );
	}
}

if ( ! function_exists( 'str_ends_with' ) ) {
	function str_ends_with( $haystack, $needle ) {
		if ( '' === $haystack && '' !== $needle ) {
			return false;
		}
		$len = strlen( $needle );
		return 0 === substr_compare( $haystack, $needle, -$len, $len );
	}
}

if(!function_exists('is_login')){
	function is_login(){
		return wpjam_is_login();
	}
}

function wpjam_register_field_data_type($name, $args=[]){
	return wpjam_register_data_type($name, $args);
}

function wpjam_get_ipdata($ip=''){
	return wpjam_parse_ip($ip);
}

function wpjam_get_ua(){
	return wpjam_get_user_agent();
}

function is_mac(){
	return is_macintosh();
}

function wpjam_flatten_terms($terms){
	return wpjam_list_flatten($terms);
}

function wpjam_get_option_setting($name){
	$object = WPJAM_Option_Setting::get($name);

	return $object ? $object->to_array() : null;
}

function wpjam_get_ajax_data_attr($name, $data=[], $return=''){
	if($return == ''){
		return wpjam_get_ajax_attribute_string($name, $data);
	}else{
		return wpjam_get_ajax_attributes($name, $data);
	}
}

function wpjam_get_ajax_button($args){
	$args	= wp_parse_args($args, [
		'action'		=> '',
		'data'			=> [],
		'direct'		=> '',
		'confirm'		=> '',
		'button_text'	=> '保存',
		'page_title'	=> '',
		'tag'			=> 'a',
		'nonce'			=> '',
		'class'			=> 'button-primary large',
		'style'			=> ''
	]);

	if(empty($args['action'])){
		return '';
	}

	$title	= $args['page_title'] ?: $args['button_text'];
	$attr	= wpjam_attribute_string([
		'title'	=> $title,
		'class'	=> $args['class'].' wpjam-button',
		'style'	=> $args['style'],
		'data'	=> array_merge([
			'action'	=> $args['action'],
			'nonce'		=> $args['nonce'] ?: wp_create_nonce(wpjam_get_nonce_action($args['action'])),
			'data'		=> $args['data'],
			'title'		=> $title
		], wp_array_slice_assoc($args, ['direct', 'confirm']))
	]);

	if($args['tag'] == 'a'){
		$attr	= 'href="javascript:;" '.$attr;
	}

	return '<'.$args['tag'].' '.$attr.'>'.$args['button_text'].'</'.$args['tag'].'>';
}

function wpjam_get_ajax_form($args){
	$args	= wp_parse_args($args, [
		'data_type'		=> 'form',
		'fields_type'	=> 'table',
		'fields'		=> [],
		'data'			=> [],
		'action'		=> '',
		'page_title'	=> '',
		'submit_text'	=> '',
		'nonce'			=> '',
		'form_id'		=> 'wpjam_form'
	]);

	if($args['nonce']){
		$nonce	= $args['nonce'];
	}elseif($args['action']){
		$nonce	= wp_create_nonce(wpjam_get_nonce_action($args['action']));
	}else{
		$nonce	= '';
	}

	$attr	= wpjam_attribute_string([
		'method'	=> 'post',
		'action'	=> '#',
		'id'		=> $args['form_id'],
		'data'		=> [
			'title'		=> $args['page_title'] ?: $args['submit_text'],
			'action'	=> $args['action'],
			'nonce'		=> $nonce
		]
	]);

	$form_fields	= $args['fields'] ? wpjam_fields($args['fields'], array_merge($args, ['echo'=>false])) : '';
	$submit_button	= $args['submit_text'] ? get_submit_button($args['submit_text'], 'primary', 'page_submit') : '';

	return 	'<form '.$attr.'>'.$form_fields.$submit_button.'</form>';
}

function wpjam_ajax_button($args){
	echo wpjam_get_ajax_button($args);
}

function wpjam_ajax_form($args){
	echo wpjam_get_ajax_form($args);
}

function wpjam_page_action_compact($action){
	$action_type	= wpjam_get_parameter('action_type',	['method'=>'POST', 'sanitize_callback'=>'sanitize_key']);

	if($action_type != 'form'){
		$nonce	= wpjam_get_parameter('_ajax_nonce',	['method'=>'POST']);

		if(!wp_verify_nonce($nonce, wpjam_get_nonce_action($action))){
			wpjam_send_json(['errcode'=>'invalid_nonce',	'errmsg'=>'非法操作']);
		}
	}

	do_action_deprecated('wpjam_page_action', [$action, $action_type], 'WPJAM Basic 4.6');

	$ajax_response	= wpjam_get_filter_name($GLOBALS['plugin_page'], 'ajax_response');
	$ajax_response	= apply_filters_deprecated('wpjam_page_ajax_response', [$ajax_response, $GLOBALS['plugin_page'], $action, $action_type], 'WPJAM Basic 4.6');

	if(is_callable($ajax_response)){
		$result	= call_user_func($ajax_response, $action);
		$result	= (is_wp_error($result) || is_array($result)) ? $result : [];
	}else{
		$result	= new WP_Error('invalid_ajax_response', '无效的回调函数');
	}

	return $result;
}

function wpjam_get_post_options($post_type, $post_id=null){
	$pt_options		= [];

	foreach(WPJAM_Post_Option::get_registereds() as $meta_box => $object){
		if($object->is_available($post_type)){
			$pt_options[$meta_box] = $object->to_array();
		}
	}

	return $pt_options;
}

function wpjam_get_post_option_fields($post_type, $post_id=null){
	$pt_fields	= [];

	foreach(WPJAM_Post_Option::get_registereds() as $object){
		if($object->is_available($post_type)){
			if(empty($object->update_callback) && ($fields = $object->get_fields($post_type, $post_id))){
				$pt_fields	= array_merge($pt_fields, $fields);
			}
		}
	}

	return $pt_fields;
}

function wpjam_get_term_options($taxonomy, $term_id=null){
	$tax_fields	= [];

	foreach(WPJAM_Term_Option::get_registereds() as $object){
		if($object->is_available($taxonomy)){
			if($fields = $object->get_fields($term_id)){
				$tax_fields	= array_merge($tax_fields, $fields);
			}
		}
	}

	return $tax_fields;
}

function wpjam_get_post_fields($post_type, $post_id=null){
	return wpjam_get_post_option_fields($post_type, $post_id);
}

function wpjam_register_map_meta_cap($capability, $map_meta_cap){
	return wpjam_register_capability($capability, $map_meta_cap);
}

add_filter('rewrite_rules_array', function($rules){
	if(has_filter('wpjam_rewrite_rules')){
		return array_merge(apply_filters('wpjam_rewrite_rules', []), $rules);
	}
	return $rules;
});

add_action('wpjam_builtin_page_load', function($screen_base, $screen){
	if($screen_base == 'post'){
		$post_type	= $screen->post_type;

		if(has_action('wpjam_post_page_file')){
			do_action('wpjam_post_page_file', $post_type);
		}

		if(has_filter('wpjam_post_options')){
			if($post_options = apply_filters('wpjam_post_options', [], $post_type)){
				foreach($post_options as $meta_box => $args){
					wpjam_register_post_option($meta_box, $args);
				}
			}
		}
	}elseif($screen_base == 'edit'){
		if(has_action('wpjam_post_list_page_file')){
			do_action('wpjam_post_list_page_file', $screen->post_type);
		}
	}elseif(in_array($screen_base, ['term', 'edit-tags'])){
		$taxonomy	= $screen->taxonomy;

		if(has_action('wpjam_term_list_page_file')){
			do_action('wpjam_term_list_page_file', $taxonomy);
		}

		if(has_filter('wpjam_term_options')){
			if($term_options = apply_filters('wpjam_term_options', [], $taxonomy)){
				foreach($term_options as $key => $args){
					wpjam_register_term_option($key, $args);
				}
			}
		}

		if(has_filter('wpjam_'.$taxonomy.'_term_options')){
			if($term_options = apply_filters('wpjam_'.$taxonomy.'_term_options', [])){
				foreach($term_options as $key => $args){
					wpjam_register_term_option($key, array_merge($args, ['taxonomy'=>$taxonomy]));
				}
			}
		}
	}
}, 10, 2);

// 判断一个数组是关联数组，还是顺序数组
function wpjam_is_assoc_array($arr){
	return !wp_is_numeric_array($arr);
	if([] === $arr){
		return false;
	}

	return array_keys($arr) !== range(0, count($arr) - 1);
}

function wpjam_sort_items($items, $orderby='order', $order='DESC'){
	return wpjam_list_sort($items, $orderby, $order);
}

function wpjam_form_field_tmpls($echo=true){}

function wpjam_urlencode_img_cn_name($img_url){
	return $img_url;
}

function wpjam_image_hwstring($size){
	$width	= (int)($size['width']);
	$height	= (int)($size['height']);
	return image_hwstring($width, $height);
}

function wpjam_get_taxonomy_levels($taxonomy){
	return wpjam_get_taxonomy_setting($taxonomy, 'levels', 0);
}

function wpjam_get_api_setting($name){
	return wpjam_get_json_object($name);
}

function wpjam_get_api($name){
	return wpjam_get_json_object($name);
}

function wpjam_get_json(){
	return wpjam_get_current_json();
}

function wpjam_is_json($json=''){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 4.2', 'wpjam_get_json');

	$wpjam_json = wpjam_get_json();

	if($wpjam_json){
		if($json){
			return $wpjam_json == $json;
		}

		return true;
	}

	return false;	
}

function is_wpjam_json($json=''){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 4.2', 'wpjam_get_json');

	return wpjam_is_json($json);
}

function is_module($module='', $action=''){
	return wpjam_is_module($module, $action);
}

function wpjam_has_path($path_type, $page_key){
	$path_obj	= WPJAM_Path::get($page_key);

	return is_null($path_obj) ? false : $path_obj->has($path_type);
}

function wpjam_get_paths_by_post_type($post_type, $path_type){
	return WPJAM_Path::get_by(compact('post_type', 'path_type'));
}

function wpjam_get_paths_by_taxonomy($taxonomy, $path_type){
	return WPJAM_Path::get_by(compact('taxonomy', 'path_type'));
}

function wpjam_generate_path($data){
	$page_key	= $data['page_key'] ?? '';
	$path_type	= $data['path_type'] ?? '';
	$path_type	= $path_type ?: 'weapp'; 	// 历史遗留问题，默认都是 weapp， 非常 ugly	return wpjam_get_path($path_type, $page_key, $data);
}

function wpjam_get_path_obj($page_key){
	return wpjam_get_path_object($page_key);
}

function wpjam_get_path_objs($path_type){
	return wpjam_get_paths($path_type);
}

function wpjam_render_path_item($item, $text, $platforms=[]){
	$platform	= wpjam_get_current_platform($platforms);
	$parsed		= wpjam_parse_path_item($item, $platform);

	return wpjam_get_path_item_link_tag($parsed, $text);
}

function wpjam_get_post_list($wp_query, $args=[]){
	return wpjam_render_query($wp_query, $args);
}

function wpjam_new_posts($args=[]){
	echo wpjam_get_new_posts($args);
}

function wpjam_top_viewd_posts($args=[]){
	echo wpjam_get_top_viewd_posts($args);
}

function wpjam_attachment_url_to_postid($url){
	$post_id = wp_cache_get($url, 'attachment_url_to_postid');

	if($post_id === false){
		global $wpdb;

		$upload_dir	= wp_get_upload_dir();
		$path		= str_replace(parse_url($upload_dir['baseurl'], PHP_URL_PATH).'/', '', parse_url($url, PHP_URL_PATH));

		$post_id	= $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value = %s", $path));

		wp_cache_set($url, $post_id, 'attachment_url_to_postid', DAY_IN_SECONDS);
	}

	return (int) apply_filters( 'attachment_url_to_postid', $post_id, $url );
}

// 获取远程图片
function wpjam_get_content_remote_image_url($img_url, $post_id=null){
	return $img_url;
}

function wpjam_image_remote_method($img_url=''){
	return '';
}

function wpjam_is_remote_image($img_url, $strict=true){
	if($strict){
		return !wpjam_is_cdn_url($img_url);
	}else{
		return wpjam_is_external_image($img_url);
	}
}

function wpjam_get_content_width(){
	return (int)apply_filters('wpjam_content_image_width', wpjam_cdn_get_setting('width'));
}

function wpjam_cdn_replace_local_hosts($html, $to_cdn=true){
	return wpjam_cdn_host_replace($html, $to_cdn);
}

function wpjam_cdn_content($content){
	return WPJAM_CDN::content_images($content);
}

function wpjam_content_images($content, $max_width=0){
	return WPJAM_CDN::content_images($content, $max_width);
}

function wpjam_get_content_remote_img_url($img_url, $post_id=0){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_content_remote_image_url');
	return wpjam_get_content_remote_image_url($img_url, $post_id);
}

function wpjam_get_post_first_image($post=null, $size='full'){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_post_first_image_url');
	return wpjam_get_post_first_image_url($post, $size);
}

function wpjam_get_qqv_vid($id_or_url){
	return WPJAM_Utli::get_qqv_id($id_or_url);
}

function wpjam_get_qq_vid($id_or_url){
	return WPJAM_Utli::get_qqv_id($id_or_url);
}

function wpjam_sha1(...$args){
	return WPJAM_Crypt::sha1(...$args);
}

function wpjam_is_mobile() {
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wp_is_mobile');
	return wp_is_mobile();
}

function get_post_first_image($post_content=''){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_post_first_image');
	return wpjam_get_post_first_image($post_content);
}

function wpjam_get_post_image_url($image_id, $size='full'){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wp_get_attachment_image_url');

	if($thumb = wp_get_attachment_image_src($image_id, $size)){
		return $thumb[0];
	}

	return false;
}

function wpjam_has_post_thumbnail(){
	return wpjam_get_post_thumbnail_url() ? true : false;
}

function wpjam_post_thumbnail($size='thumbnail', $crop=1, $class='wp-post-image', $ratio=2){
	echo wpjam_get_post_thumbnail(null, $size, $crop, $class, $ratio);
}

function wpjam_get_post_thumbnail($post=null, $size='thumbnail', $crop=1, $class='wp-post-image', $ratio=2){
	$size	= wpjam_parse_size($size, $ratio);
	if($post_thumbnail_url = wpjam_get_post_thumbnail_url($post, $size, $crop)){
		$image_hwstring	= image_hwstring($size['width']/$ratio, $size['height']/$ratio);
		return '<img src="'.$post_thumbnail_url.'" alt="'.the_title_attribute(['echo'=>false]).'" class="'.$class.'"'.$image_hwstring.' />';
	}else{
		return '';
	}
}

function wpjam_get_post_thumbnail_src($post=null, $size='thumbnail', $crop=1, $ratio=1){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_post_thumbnail_url');
	return wpjam_get_post_thumbnail_url($post, $size, $crop, $ratio);
}

function wpjam_get_post_thumbnail_uri($post=null, $size='full'){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_post_thumbnail_url');
	return wpjam_get_post_thumbnail_url($post, $size);
}

function wpjam_get_default_thumbnail_src($size){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_default_thumbnail_url');
	return wpjam_get_default_thumbnail_url($size);
}

function wpjam_get_default_thumbnail_uri(){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_default_thumbnail_url');
	return wpjam_get_default_thumbnail_url('full');
}

function wpjam_has_term_thumbnail(){
	return wpjam_get_term_thumbnail_url()? true : false;
}

function wpjam_term_thumbnail($size='thumbnail', $crop=1, $class="wp-term-image", $ratio=2){
	echo wpjam_get_term_thumbnail(null, $size, $crop, $class);
}

function wpjam_get_term_thumbnail($term=null, $size='thumbnail', $crop=1, $class="wp-term-image", $ratio=2){
	$size	= wpjam_parse_size($size, $ratio);

	if($term_thumbnail_url = wpjam_get_term_thumbnail_url($term, $size, $crop)){
		$image_hwstring	= image_hwstring($size['width']/$ratio, $size['height']/$ratio);

		return  '<img src="'.$term_thumbnail_url.'" class="'.$class.'"'.$image_hwstring.' />';
	}else{
		return '';
	}
}

/* category thumbnail */
function wpjam_has_category_thumbnail(){
	return wpjam_has_term_thumbnail();
}

function wpjam_get_category_thumbnail_url($term=null, $size='full', $crop=1, $ratio=1){
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $ratio);
}

function wpjam_get_category_thumbnail($term=null, $size='thumbnail', $crop=1, $class="wp-category-image", $ratio=2){
	return wpjam_get_term_thumbnail($term, $size, $crop, $class, $ratio);
}

function wpjam_category_thumbnail($size='thumbnail', $crop=1, $class="wp-category-image", $ratio=2){
	wpjam_term_thumbnail($size, $crop, $class, $ratio);
}

function wpjam_get_category_thumbnail_src($term=null, $size='thumbnail', $crop=1, $ratio=1){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $ratio);
}

function wpjam_get_category_thumbnail_uri($term=null){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, 'full');
}

/* tag thumbnail */
function wpjam_has_tag_thumbnail(){
	return wpjam_has_term_thumbnail();
}

function wpjam_get_tag_thumbnail_url($term=null, $size='full', $crop=1, $ratio=1){
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $ratio);
}

function wpjam_get_tag_thumbnail($term=null, $size='thumbnail', $crop=1, $class="wp-tag-image", $ratio=2){
	return wpjam_get_term_thumbnail($term, $size, $crop, $class, $ratio);
}

function wpjam_tag_thumbnail($size='thumbnail', $crop=1, $class="wp-tag-image", $ratio=2){
	wpjam_term_thumbnail($size, $crop, $class, $ratio);
}

function wpjam_get_tag_thumbnail_src($term=null, $size='thumbnail', $crop=1, $ratio=1){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $ratio);
}

function wpjam_get_tag_thumbnail_uri($term=null){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, 'full');
}

function wpjam_get_term_thumbnail_src($term=null, $size='thumbnail', $crop=1, $ratio=1){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $ratio);
}

function wpjam_get_term_thumbnail_uri($term=null){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, 'full');
}

function wpjam_display_errors(){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 4.2');
}

function wpjam_parse_field_value($field, $args=[]){
	return (WPJAM_Field::create($field))->parse_value($args);
}

function wpjam_get_field_value($field, $args=[]){
	return wpjam_parse_field_value($field, $args);
}

function wpjam_get_field_html($field){
	return wpjam_field($field);
}

function wpjam_render_field($field, $args=[]){
	return wpjam_field($field, $args);
}

function wpjam_get_form_fields($admin_column = false){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 4.2');
	return [];
}

function wpjam_get_form_post($fields, $nonce_action='', $capability='manage_options'){
	check_admin_referer($nonce_action);

	if(!current_user_can($capability)){
		ob_clean();
		wp_die('无权限');
	}

	return wpjam_validate_fields_value($fields);
}

function wpjam_form($fields, $form_url, $nonce_action='', $submit_text=''){
	echo '<form method="post" action="'.$form_url.'" enctype="multipart/form-data" id="form">';

	echo wpjam_fields($fields);

	wp_nonce_field($nonce_action);
	wp_original_referer_field(true, 'previous');

	if($submit_text!==false){		submit_button($submit_text);
	}

	echo '</form>';
}

function wpjam_api_validate_quota($json='', $max_times=1000){
	$today	= date('Y-m-d', current_time('timestamp'));
	$times	= wp_cache_get($json.':'.$today, 'wpjam_api_times');
	$times	= $times ?: 0;

	if($times < $max_times){
		wp_cache_set($json.':'.$today, $times+1, 'wpjam_api_times', DAY_IN_SECONDS);
		return true;
	}else{
		wpjam_send_json(['errcode'=>'api_exceed_quota', 'errmsg'=>'API 调用次数超限']);
	}
}

function wpjam_api_validate_access_token(){
	$result	= WPJAM_Grant::get_instance()->validate_access_token();

	if(is_wp_error($result) && wpjam_is_json_request()){
		wpjam_send_json($result);
	}

	return $result;
}

add_filter('wpjam_html', function($html){
	if(has_filter('wpjam_html_replace')){
		$html	= apply_filters_deprecated('wpjam_html_replace', [$html], 'WPJAM Basic 3.4', 'wpjam_html');
	}

	return $html;
},9);

add_action('wpjam_api', function($json){
	if(has_action('wpjam_api_template_redirect')){
		do_action('wpjam_api_template_redirect', $json);
	}
});

function wpjam_stats_header($args=[]){
	global $wpjam_stats_labels;

	$wpjam_stats_labels	= [];

	WPJAM_Chart::init($args);
	WPJAM_Chart::form($args);

	// do_action('wpjam_stats_header');

	foreach(['start_date', 'start_timestamp', 'end_date', 'end_timestamp', 'date', 'timestamp', 'start_date_2', 'start_timestamp_2', 'end_date_2', 'end_timestamp_2', 'date_type', 'date_format', 'compare'] as $key){
		$wpjam_stats_labels['wpjam_'.$key]	= WPJAM_Chart::get_parameter($key);
	}

	$wpjam_stats_labels['compare_label']	= WPJAM_Chart::get_parameter('start_date').' '.WPJAM_Chart::get_parameter('end_date');
	$wpjam_stats_labels['compare_label_2']	= WPJAM_Chart::get_parameter('start_date_2').' '.WPJAM_Chart::get_parameter('end_date_2');
}

function wpjam_sub_summary($tabs){
	?>
	<h2 class="nav-tab-wrapper nav-tab-small">
	<?php foreach ($tabs as $key => $tab) { ?>
		<a class="nav-tab" href="javascript:;" id="tab-title-<?php echo $key;?>"><?php echo $tab['name'];?></a>  	<?php }?>
	</h2>

	<?php foreach ($tabs as $key => $tab) { ?>
	<div id="tab-<?php echo $key;?>" class="div-tab" style="margin-top:1em;">
	<?php
	global $wpdb;

	$counts = $wpdb->get_results($tab['counts_sql']);
	$total  = $wpdb->get_var($tab['total_sql']);
	$labels = isset($tab['labels'])?$tab['labels']:'';
	$base   = isset($tab['link'])?$tab['link']:'';

	$new_counts = $new_types = array();
	foreach ($counts as $count) {
		$link   = $base?($base.'&'.$key.'='.$count->label):'';

		if(is_super_admin() && $tab['name'] == '手机型号'){
			$label  = ($labels && isset($labels[$count->label]))?$labels[$count->label]:'<span style="color:red;">'.$count->label.'</span>';
		}else{
			$label  = ($labels && isset($labels[$count->label]))?$labels[$count->label]:$count->label;
		}

		$new_counts[] = array(
			'label' => $label,
			'count' => $count->count,
			'link'  => $link
		);
	}

	wpjam_donut_chart($new_counts, array('total'=>$total,'show_line_num'=>1,'table_width'=>'420'));

	?>
	</div>
	<?php }
}

function wpjam_send_user_message(...$args){
	if(count($args) == 2){
		$receiver	= $args[0];
		$message	= $args[1];
	}else{
		$message	= $args[0];
		$receiver	= $message['receiver'];
	}

	return WPJAM_User_Message::get_instance($receiver)->add($message);
}

// add_action('wpjam_admin_init', function(){
// 	$user_id	= get_current_user_id();
// 	$instance	= WPJAM_User_Message::get_instance($user_id);

// 	wpjam_add_menu_page('wpjam-messages', [
// 		'menu_title'	=>'站内消息',
// 		'capability'	=>'read',
// 		'parent'		=>'users',
// 		'function'		=>[$instance, 'plugin_page'],
// 		'load_callback'	=>[$instance, 'load_plugin_page']
// 	]);
// });

function wpjam_api_set_response(&$response){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 4.3');
}

function wpjam_api_signon(){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 4.3');
}

function wpjam_register_theme_upgrader($upgrader_url){
	$object	= WPJAM_Theme_Upgrader::register(get_template(), ['upgrader_url'=>$upgrader_url]);

	add_filter('site_transient_update_themes',	[$object, 'filter_site_transient']);
}

class WPJAM_Theme_Upgrader extends WPJAM_Register{
	public function filter_site_transient($transient){
		if($this->upgrader_url){
			$theme	= $this->name;

			if(empty($transient->checked[$theme])){
				return $transient;
			}

			$remote	= get_transient('wpjam_theme_upgrade_'.$theme);

			if(false == $remote){
				$remote = wpjam_remote_request($this->upgrader_url);

				if(!is_wp_error($remote)){
					set_transient('wpjam_theme_upgrade_'.$theme, $remote, HOUR_IN_SECONDS*12);
				}
			}

			if($remote && !is_wp_error($remote)){
				if(version_compare($transient->checked[$theme], $remote['new_version'], '<')){
					$transient->response[$theme]	= $remote;
				}
			}
		}

		return $transient;
	}
}

class WPJAM_PlatformBit extends WPJAM_Bit{
	public function set_platform($bit){
		$this->bit	= $bit;
	}

	public function get_platform(){
		return $this->bit;
	}
}

class WPJAM_OPENSSL_Crypt{
	private $key;
	private $method = 'aes-128-cbc';
	private $iv = '';
	private $options = OPENSSL_RAW_DATA;

	public function __construct($key, $args=[]){
		$this->key		= $key;
		$this->method	= $args['method'] ?? $this->method;
		$this->options	= $args['options'] ?? $this->options;
		$this->iv		= $args['iv'] ?? '';
	}

	public function encrypt($text){
		return openssl_encrypt($text, $this->method, $this->key, $this->options, $this->iv);
	}

	public function decrypt($encrypted_text){
		return openssl_decrypt($encrypted_text, $this->method, $this->key, $this->options, $this->iv);
	}
}

class WPJAM_PostContent extends WPJAM_Content_Items{
	public function __construct($args=[]){
		$post_id	= wpjam_get_data_parameter('post_id');
		parent::__construct($post_id, $args);
	}
}

class WPJAM_MetaItem extends WPJAM_Meta_Items{
	public function __construct($meta_type, $meta_key, $args=[]){
		$object_id	= wpjam_get_data_parameter($meta_type.'_id');
		parent::__construct($meta_type, $object_id, $meta_key, $args);
	}
}

class WPJAM_User_Message{
	private $user_id	= 0;
	private $messages	= [];

	private static $instances	= [];

	public static function get_instance($user_id){
		if(!isset(self::$instances[$user_id])){
			self::$instances[$user_id] = new self($user_id);
		}

		return self::$instances[$user_id];
	}

	private function __construct($user_id){
		$this->user_id	= $user_id;

		if($user_id && ($messages = get_user_meta($user_id, 'wpjam_messages', true))){
			$this->messages	= array_filter($messages, function($message){ return $message['time'] > time() - MONTH_IN_SECONDS * 3; });
		}
	}

	public function get_messages(){
		return $this->messages;
	}

	public function get_unread_count(){
		$messages	= array_filter($this->messages, function($message){ return $message['status'] == 0; });

		return count($messages);
	}

	public function set_all_read(){
		array_walk($this->messages, function(&$message){ $message['status'] == 1; });

		return $this->save();
	}

	public function add($message){
		$message	= wp_parse_args($message, [
			'sender'	=> '',
			'receiver'	=> '',
			'type'		=> '',
			'content'	=> '',
			'status'	=> 0,
			'time'		=> time()
		]);

		$message['content'] = wp_strip_all_tags($message['content']);

		$this->messages[]	= $message;

		return $this->save();
	}

	public function delete($i){
		if(isset($this->messages[$i])){
			unset($this->messages[$i]);
			return $this->save();
		}

		return true;
	}

	public function save(){
		if(empty($this->messages)){
			return delete_user_meta($this->user_id, 'wpjam_messages');
		}else{
			return update_user_meta($this->user_id, 'wpjam_messages', $this->messages);
		}
	}

	public function load_plugin_page(){
		wpjam_register_page_action('delete_message', [
			'button_text'	=> '删除',
			'class'			=> 'message-delete',
			'callback'		=> [$this, 'ajax_delete'],
			'direct'		=> true,
			'confirm'		=> true
		]);
	}

	public function ajax_delete(){
		$message_id	= (int)wpjam_get_data_parameter('message_id');
		$messages	= $this->get_messages();

		if($messages && isset($messages[$message_id])){
			$result	= $this->delete($message_id);

			if(is_wp_error($result)){
				wpjam_send_json($result);
			}else{
				wpjam_send_json(['message_id'=>$message_id]);
			}
		}

		wpjam_send_json(['errcode'=>'invalid_message_id', '无效的消息ID']);
	}

	public function plugin_page(){
		$messages	= $this->data;

		if(empty($messages)){ 
			echo '<p>暂无站内消息</p>';
			return;
		}

		if($this->get_unread_count()){
			$this->set_all_read();
		}

		$sender_ids			= [];
		$post_ids_list		= [];
		$comment_ids_list	= [];

		foreach($messages as $message) {
			$sender_ids[]	= $message['sender'];
			$blog_id		= $message['blog_id'];
			$post_id		= $message['post_id'];
			$comment_id		= $message['comment_id'];
			if($blog_id){
				if($post_id){
					$post_ids_list[$blog_id][]		= $post_id;
				}

				if($comment_id){
					$comment_ids_list[$blog_id][]	= $comment_id;
				}
			}
		}

		$senders	= get_users(['blog_id'=>0, 'include'=>$sender_ids]);

		foreach ($post_ids_list as $blog_id => $post_ids) {
			$switched	= is_multisite() ? switch_to_blog($blog_id) : false;

			WPJAM_Post::update_caches($post_ids);

			if($switched){
				restore_current_blog();
			}
		}

		foreach ($comment_ids_list as $blog_id => $comment_ids) {
			$switched	= is_multisite() ? switch_to_blog($blog_id) : false;

			get_comments(['include'=>$comment_ids]);

			if($switched){
				restore_current_blog();
			}
		}
		?>

		<ul class="messages">
		<?php foreach ($messages as $i => $message) { 
			$alternate	= empty($alternate)?'alternate':'';
			$sender		= get_userdata($message['sender']);

			$type		= $message['type'];
			$content	= $message['content'];
			$blog_id	= $message['blog_id'];
			$post_id	= $message['post_id'];
			$comment_id	= $message['comment_id'];
			

			if(empty($sender)){
				continue;
			}

			if($blog_id && $post_id){
				$switched	= is_multisite() ? switch_to_blog($blog_id) : false;

				$post		= get_post($post_id);

				if($post){
					$topic_title	= $post->post_title;
				}

				if($switched){
					restore_current_blog();
				}
			}else{
				$topic_title		= '';
			}
		?>
			<li id="message_<?php echo $i; ?>" class="<?php echo $alternate; echo empty($message['status'])?' unread':'' ?>">
				<div class="sender-avatar"><?php echo get_avatar($message['sender'], 60);?></div>
				<div class="message-time"><?php echo wpjam_human_time_diff($message['time']);?><p><?php echo wpjam_get_page_button('delete_message',['data'=>['message_id'=>$i]]);?></p></div>
				<div class="message-content">
				
				<?php 

				if($type == 'topic_comment'){
					$prompt	= '<span class="message-sender">'.$sender->display_name.'</span>在你的帖子「<a href="'.admin_url('admin.php?page=wpjam-topics&action=comment&id='.$post_id.'#comment_'.$comment_id).'">'.$topic_title.'</a>」给你留言了：'."\n\n";
				}elseif($type == 'comment_reply' || $type == 'topic_reply'){
					$prompt	= '<span class="message-sender">'.$sender->display_name.'</span>在帖子「<a href="'.admin_url('admin.php?page=wpjam-topics&action=comment&id='.$post_id.'#comment_'.$comment_id).'">'.$topic_title.'</a>」回复了你的留言：'."\n\n";
				}else{
					$prompt	= '<span class="message-sender">'.$sender->display_name.'：'."\n\n";
				}

				echo wpautop($prompt.$content);

				?>
				</div>
			</li>
			<?php } ?>
		</ul>

		<style type="text/css">
			ul.messages{ max-width:640px; }
			ul.messages li {margin: 10px 0; padding:10px; margin:10px 0; background: #fff; min-height: 60px;}
			ul.messages li.alternate{background: #f9f9f9;}
			ul.messages li.unread{font-weight: bold;}
			ul.messages li a {text-decoration:none;}
			ul.messages li div.sender-avatar {float:left; margin:0px 10px 0px 0;}
			ul.messages li div.message-time{float: right; width: 60px;}
			ul.messages li .message-delete{color: #a00;}
			ul.messages li div.message-content p {margin: 0 70px 10px 70px; }
		</style>
		
		<script type="text/javascript">
		jQuery(function($){
			$('body').on('page_action_success', function(e, response){
				var action		= response.page_action;
				var action_type	= response.page_action_type;

				if(action == 'delete_message'){
					var message_id	= response.message_id;
					$('#message_'+message_id).animate({opacity: 0.1}, 500, function(){ $(this).remove();});
				}
			});
		});
		</script>
		
		<?php
	}
}

trait WPJAM_Setting_Trait{
	private $settings		= [];
	private $option_name	= '';
	private $site_default	= false;

	private function init($option_name, $site_default=false){
		$this->option_name	= $option_name;
		$this->site_default	= $site_default;

		$this->reset_settings();
	}

	public function __get($name){
		if(in_array($name, ['option_name', 'site_default'])){
			return $this->$name;
		}

		if(is_null(get_option($option_name, null))){
			add_option($option_name, []);
		}

		return $this->get_setting($name);
	}

	public function __set($name, $value){
		return $this->update_setting($name, $value);
	}

	public function __isset($name){
		return isset($this->settings[$name]);
	}

	public function __unset($name){
		$this->delete_setting($name);
	}

	public function get_settings(){
		return $this->settings;
	}

	public function reset_settings(){
		$value	= wpjam_get_option($this->option_name);

		$this->settings	= is_array($value) ? $value : [];

		if($this->site_default){
			$site_value	= wpjam_get_site_option($this->option_name);
			$site_value	= is_array($site_value) ? $site_value : [];

			$this->settings	+= $site_value;
		}
	}

	public function get_setting($name='', $default=null){
		return $name ? ($this->settings[$name] ?? $default) : $this->settings;
	}

	public function update_setting($name, $value){
		$this->settings[$name]	= $value;

		return $this->save();
	}

	public function delete_setting($name){
		$this->settings	= wpjam_array_except($this->settings, $name);

		return $this->save();
	}

	private function save($settings=[]){
		if($settings){
			$this->settings	= array_merge($this->settings, $settings);
		}

		return update_option($this->option_name, $this->settings);
	}

	private static $instances	= [];

	public static function get_instance(){
		$blog_id = get_current_blog_id();	//多站点情况下，switch_to_blog 之后还能从正确的站点获取设置

		if(!isset(self::$instances[$blog_id])){
			self::$instances[$blog_id] = new self();
		}

		return self::$instances[$blog_id];
	}

	public static function register_option($args=[]){
		$instance	= self::get_instance();
		$defaults	= [];

		$defaults['site_default']	= $instance->site_default;

		if(method_exists($instance, 'sanitize_callback')){
			$defaults['sanitize_callback']	= [$instance, 'sanitize_callback'];
		}

		if(method_exists($instance, 'get_summary')){
			$defaults['summary']	= [$instance, 'get_summary'];
		}

		if(method_exists($instance, 'get_sections')){
			$defaults['sections']	= [$instance, 'get_sections'];
		}elseif(method_exists($instance, 'get_fields')){
			$defaults['fields']		= [$instance, 'get_fields'];
		}

		if(current_user_can('manage_options') && isset($_GET['reset'])){
			delete_option($instance->option_name);
		}

		return wpjam_register_option($instance->option_name, wp_parse_args($args, $defaults));
	}
}

trait WPJAM_Register_Trait{
	protected $name;
	protected $args;
	protected $filtered	= false;

	public function __construct($name, $args=[]){
		$this->name	= $name;
		$this->args	= $args;
	}

	public function parse_args(){
		return $this->args;
	}

	protected function get_args(){
		if(!$this->filtered){
			$filter	= strtolower(get_called_class()).'_args';
			$args	= $this->parse_args();

			$this->args		= apply_filters($filter, $args, $this->name);
			$this->filtered	= true;
		}

		return $this->args;
	}

	public function __get($key){
		if($key == 'name'){
			return $this->name;
		}else{
			$args	= $this->get_args();
			return $args[$key] ?? null;
		}
	}

	public function __set($key, $value){
		if($key != 'name'){
			$this->args	= $this->get_args();
			$this->args[$key]	= $value;
		}
	}

	public function __isset($key){
		$args	= $this->get_args();
		return isset($args[$key]);
	}

	public function __unset($key){
		$this->args	= $this->get_args();
		unset($this->args[$key]);
	}

	public function to_array(){
		return $this->get_args();
	}

	protected static $_registereds	= [];

	public static function parse_name($name){
		if(empty($name)){
			trigger_error(self::class.'的注册 name 为空');
			return null;
		}elseif(is_numeric($name)){
			trigger_error(self::class.'的注册 name「'.$name.'」'.'为纯数字');
			return null;
		}elseif(!is_string($name)){
			trigger_error(self::class.'的注册 name「'.var_export($name, true).'」不为字符串');
			return null;
		}

		return $name;
	}

	public static function register(...$args){
		if(count($args) == 1){
			$object	= $args[0];
			$name	= $object->name;
		}else{
			$name	= self::parse_name($args[0]);

			if(is_null($name)){
				return null;
			}

			$object	= new static($name, $args[1]);
		}

		self::$_registereds[$name]	= $object;

		return $object;
	}

	protected static function register_instance($name, $object){
		self::$_registereds[$name]	= $object;

		return $object;
	}

	public static function unregister($name){
		unset(self::$_registereds[$name]);
	}

	public static function get_by($args=[], $output='objects'){
		return self::get_registereds($args, $output);
	}

	public static function get_registereds($args=[], $output='objects', $operator='and'){
		$registereds	= $args ? wp_filter_object_list(self::$_registereds, $args, $operator, false) : self::$_registereds;

		if($output == 'names'){
			return array_keys($registereds);
		}elseif(in_array($output, ['args', 'settings'])){
			return array_map(function($registered){
				return $registered->to_array();
			}, $registereds);
		}else{
			return $registereds;
		}
	}

	public static function get($name){
		return self::$_registereds[$name] ?? null;
	}

	public static function exists($name){
		return self::get($name) ? true : false;
	}
}

trait WPJAM_Type_Trait{
	use WPJAM_Register_Trait;
}

// 直接在 handler 里面定义即可。
// 需要在使用的 CLASS 中设置 public static $meta_type
trait WPJAM_Meta_Trait{
	public static function get_meta_type_object(){
		return wpjam_get_meta_type_object(self::$meta_type);
	}

	public static function add_meta($id, $meta_key, $meta_value, $unique=false){
		return self::get_meta_type_object()->add_data($id, $meta_key, $meta_value, $unique);
	}

	public static function delete_meta($id, $meta_key, $meta_value=''){
		return self::get_meta_type_object()->delete_data($id, $meta_key, $meta_value);
	}

	public static function get_meta($id, $key = '', $single = false){
		return self::get_meta_type_object()->get_data($id, $key, $single);
	}

	public static function update_meta($id, $meta_key, $meta_value, $prev_value=''){
		return self::get_meta_type_object()->update_data($id, $meta_key, wp_slash($meta_value), $prev_value);
	}

	public static function delete_meta_by_key($meta_key){
		return self::get_meta_type_object()->delete_by_key($meta_key);
	}

	public static function update_meta_cache($object_ids){
		self::get_meta_type_object()->update_cache($object_ids);
	}

	public static function create_meta_table(){
		self::get_meta_type_object()->create_table();
	}

	public static function get_meta_table(){
		return self::get_meta_type_object()->get_table();
	}
}

trait WPJAM_Qrcode_Bind_Trait{
	public function verify_qrcode($scene, $code){
		return new WP_Error('invalid_code', '无效验证码');
	}

	public function scan_qrcode($openid, $scene){
		return new WP_Error('qrcode_scaned', '已有用户扫描该二维码！');
	}

	public function get_qrcode($scene){
		return new WP_Error('invalid_scene', '二维码无效或已过期，请刷新页面再来验证！');
	}
}