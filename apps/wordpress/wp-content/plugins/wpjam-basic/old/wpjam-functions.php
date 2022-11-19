<?php
//WP Pagenavi
function wpjam_pagenavi($total=0){
	if(!$total){
		global $wp_query;
		$total = $wp_query->max_num_pages;
	}

	$big = 999999999; // need an unlikely integer
	
	$pagination = array(
		'base'		=> str_replace( $big, '%#%', get_pagenum_link( $big ) ),
		'format'	=> '',
		'total'		=> $total,
		'current'	=> max( 1, get_query_var('paged') ),
		'prev_text'	=> __('&laquo;'),
		'next_text'	=> __('&raquo;'),
		'end_size'	=> 2,
		'mid_size'	=> 2
	);

	echo '<div class="pagenavi">'.paginate_links($pagination).'</div>'; 
}

remove_filter( 'get_the_excerpt', 'wp_trim_excerpt'  );
add_filter('get_the_excerpt','wpjam_get_the_excerpt');
function wpjam_get_the_excerpt($post_excerpt){
	return get_post_excerpt();
}

if(!function_exists('get_post_excerpt')){   
	//获取日志摘要
	function get_post_excerpt($post=null, $excerpt_length=240){
		$post = get_post($post);
		if ( empty( $post ) ) {
			return '';
		}

		$post_excerpt = $post->post_excerpt;
		if($post_excerpt == ''){
			$post_content   = strip_shortcodes($post->post_content);
			//$post_content = apply_filters('the_content',$post_content);
			$post_content   = wp_strip_all_tags( $post_content );
			$excerpt_length = apply_filters('excerpt_length', $excerpt_length);	 
			$excerpt_more   = apply_filters('excerpt_more', ' ' . '...');
			$post_excerpt   = get_first_p($post_content); // 获取第一段
			if(mb_strwidth($post_excerpt) < $excerpt_length*1/3 || mb_strwidth($post_excerpt) > $excerpt_length){ // 如果第一段太短或者太长，就获取内容的前 $excerpt_length 字
				$post_excerpt = mb_strimwidth($post_content,0,$excerpt_length,$excerpt_more,'utf-8');
			}
		}

		$post_excerpt = wp_strip_all_tags( $post_excerpt );
		$post_excerpt = trim( preg_replace( "/[\n\r\t ]+/", ' ', $post_excerpt ), ' ' );

		return $post_excerpt;
	}

	//获取第一段
	function get_first_p($text){
		if($text){
			$text = explode("\n",strip_tags($text)); 
			$text = trim($text['0']); 
		}
		return $text;
	}
}

function wpjam_blacklist_check($str){
	$moderation_keys	= trim(get_option('moderation_keys'));
	$blacklist_keys		= trim(get_option('blacklist_keys'));

	$keys = $moderation_keys ."\n".$blacklist_keys;

	$words = explode("\n", $keys );

	foreach ( (array) $words as $word) {
		$word = trim($word);

		// Skip empty lines
		if ( empty($word) )
			continue;

		// Do some escaping magic so that '#' chars in the
		// spam words don't break things:
		$word = preg_quote($word, '#');

		$pattern = "#$word#i";
		if ( preg_match($pattern, $str) ) return true;
	}

	return false;
}

//获取纯文本
function wpjam_get_plain_text($text){

	$text = wp_strip_all_tags($text);
	
	$text = str_replace('"', '', $text); 
	$text = str_replace('\'', '', $text);	
	// replace newlines on mac / windows?
	$text = str_replace("\r\n", ' ', $text);
	// maybe linux uses this alone
	$text = str_replace("\n", ' ', $text);
	$text = str_replace("  ", ' ', $text);
	return $text;
}

// 去掉非 utf8mb4 字符
function wpjam_strip_invalid_text($str){
	$regex = '/
	(
		(?: [\x00-\x7F]                  # single-byte sequences   0xxxxxxx
		|   [\xC2-\xDF][\x80-\xBF]       # double-byte sequences   110xxxxx 10xxxxxx
		|   \xE0[\xA0-\xBF][\x80-\xBF]   # triple-byte sequences   1110xxxx 10xxxxxx * 2
		|   [\xE1-\xEC][\x80-\xBF]{2}
		|   \xED[\x80-\x9F][\x80-\xBF]
		|   [\xEE-\xEF][\x80-\xBF]{2}
		|    \xF0[\x90-\xBF][\x80-\xBF]{2} # four-byte sequences   11110xxx 10xxxxxx * 3
		|    [\xF1-\xF3][\x80-\xBF]{3}
		|    \xF4[\x80-\x8F][\x80-\xBF]{2}
		){1,50}                          # ...one or more times
	)
	| .                                  # anything else
	/x';

	return preg_replace($regex, '$1', $str);
}

// 去掉控制字符
function wpjam_strip_control_characters($str){
	return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F]/u', '', $str);	// 移除除了 line feeds 和 carriage returns 所有控制字符
	// return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '', $str);	// 移除除了 line feeds 和 carriage returns 所有控制字符
}

// 检测用户名是合法标准
function wpjam_check_nickname($nickname, $user_id=0 ){
	if($nickname){
		if(mb_strwidth($nickname)>20){
			return new WP_Error('too_long','不能超过20个字符。');
		}

		if(wpjam_blacklist_check($nickname)){
			return new WP_Error('illegal','含有非法字符。');
		}

		if(!wpjam_validate_nickname($nickname)){
			return new WP_Error('invalid','只能含有中文汉字、英文字母、数字、下划线、中划线和点。');
		}

		if(wpjam_duplicate_nickname($nickname,$user_id)){
			return new WP_Error('duplicate','重复。');
		}

		return true;
	} 

	return new WP_Error('empty','为空');
}

// 检测用户名是否重复
function wpjam_duplicate_nickname($nickname,$user_id=0){
	global $wpdb;

	$sql = $wpdb->prepare("SELECT U.ID, U.display_name, UM.meta_value AS nickname FROM {$wpdb->users} as U LEFT JOIN {$wpdb->usermeta} UM ON ( U.ID = UM.user_id ) WHERE U.ID<>%d AND UM.meta_key = 'nickname' AND ( user_login = %s OR user_nicename = %s OR display_name = %s OR UM.meta_value = %s ) LIMIT 1", $user_id, $nickname, $nickname, $nickname, $nickname);
	
	if($wpdb->get_row($sql)){
		return true;
	}else{
		return false;
	}
}

// 验证用户名
function wpjam_validate_nickname($raw_nickname){

	$nickname = wpjam_get_validated_nickname($raw_nickname);
	
	if($raw_nickname == $nickname){
		return true;
	}else{
		return false;
	}
}

function wpjam_get_validated_nickname($nickname){

	$nickname = remove_accents( $nickname );
	$nickname = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $nickname);
	$nickname = preg_replace('/&.+?;/', '', $nickname); // Kill entities
	
	//限制不能使用特殊的中文
	$nickname = preg_replace('/[^A-Za-z0-9_.\-\x{4e00}-\x{9fa5}]/u', '', $nickname);

	//检测待审关键字和黑名单关键字
	if(wpjam_blacklist_check($nickname)){
		$nickname = '';
	}
	
	$nickname = trim( $nickname );
	// Consolidate contiguous whitespace
	$nickname = preg_replace( '|\s+|', ' ', $nickname );
	
	return $nickname;
}

/*判断是否是机器人*/
function is_bot(){
	$useragent = trim($_SERVER['HTTP_USER_AGENT']);
	if(stristr($useragent, 'bot') !== false || stristr($useragent, 'spider') !== false){
		return true;
	}
	return false;
}

// 向关联数组指定的 Key 之前插入数据
function wpjam_array_push(&$array, $data=null, $key=false){
	$data	= (array)$data;

	$offset	= ($key===false)?false:array_search($key, array_keys($array));
	$offset	= ($offset)?$offset:false;

	if($offset){
		$array = array_merge(
			array_slice($array, 0, $offset), 
			$data, 
			array_slice($array, $offset)
		);
	}else{	// 没指定 $key 或者找不到，就直接加到末尾
		$array = array_merge($array, $data);
	}
}

function wpjam_get_custom_taxonomies(){
    $args = array(
        'public'   => true,
        '_builtin' => false
    );

    return get_taxonomies($args); 
}


function wpjam_get_custom_avatar_src($user, $size = '96'){
	if(is_numeric($user)){
		$user_id = $user;
	}elseif(is_object($user) && ! empty( $user->ID ) ) {
		$user_id = (int) $user->ID;
	}else{
		return false;
	}

	if( $custom_avatar = get_user_meta( $user_id, 'avatar', true ) ){
		return wpjam_get_thumbnail($custom_avatar, $size, $size, $crop=1);
	}

	return false;
}

function wpjam_get_attachment_id($img_url) {

	$cache_key	= md5($img_url);

	$post_id	= wp_cache_get($cache_key, 'wpjam_attachment_id' );

	if($post_id == false){

		$attr		= wp_upload_dir();
		$base_url	= $attr['baseurl']."/";
		
		if(function_exists('wpjam_domain_mapping_replace')){
			$img_url	= wpjam_domain_mapping_replace($img_url);
			$base_url	= wpjam_domain_mapping_replace($base_url);
		}		

		$path = str_replace($base_url, "", $img_url);

		if($path){
			global $wpdb;
			$post_id	= $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_value = '{$path}'");
			$post_id	= $post_id?$post_id:''; 
		}else{
			$post_id	= '';
		}

		wp_cache_set( $cache_key, $post_id, 'wpjam_attachment_id', 86400);
		
	}
	return $post_id;
}

function wpjam_localize_script($handle, $object_name, $l10n ){
	$l10n = array(
		'l10n_print_after' => $object_name.' = ' . wpjam_json_encode( $l10n )
	);

	wp_localize_script( $handle, $object_name, $l10n );
}

function wpjam_is_ipad(){
	static $is_ipad;
	if ( isset($is_ipad) )
		return $is_ipad;

	if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== false){
		$is_ipad = true;
	}else{
		$is_ipad = false;
	}
	return $is_ipad;
}

function wpjam_is_iphone(){
	static $is_iphone;
	if ( isset($is_iphone) )
		return $is_iphone;

	if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false){
		$is_iphone = true;
	}else{
		$is_iphone = false;
	}
	return $is_iphone;
}

function wpjam_is_ios(){
	return wpjam_is_iphone() || wpjam_is_ipad();
}

function wpjam_is_android(){
	static $is_android;
	if ( isset($is_android) )
		return $is_android;

	if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false){
		$is_android = true;
	}else{
		$is_android = false;
	}
	return $is_android;
}

function wpjam_is_mobile() {
	return wp_is_mobile();
}

function wpjam_is_mobile_number($number){
	return preg_match('/^0{0,1}(13[0-9]|15[0-3]|15[5-9]|147|170|17[6-8]|18[0-9])[0-9]{8}$/', $number);
}

function wpjam_is_400_number($number){
	return preg_match('/^400(\d{7})$/', $number);
}

function wpjam_is_800_number($number){
	return preg_match('/^800(\d{7})$/', $number);
}

function wpjam_is_scheduled_event( $hook ) {	// 不用判断参数
	$crons = _get_cron_array();
	if ( empty($crons) )
		return false;
	foreach ( $crons as $timestamp => $cron ) {
		if ( isset( $cron[$hook] ) )
			return true;
	}
	return false;
}

function wpjam_is_holiday($date=''){
	$date	= ($date)?$date:date('Y-m-d', current_time('timestamp'));
	$w		= date('w', strtotime($date));

	$is_holiday = ($w == 0 || $w == 6)?1:0;

	return apply_filters('wpjam_holiday', $is_holiday, $date);
}


function wpjam_urlencode_img_cn_name($img_url){
	$pos_start	= strrpos($img_url,'/');
	$pos_end	= strrpos($img_url,'.');
	return substr($img_url, 0,$pos_start+1).urlencode(substr($img_url, $pos_start+1, $pos_end-$pos_start)).substr($img_url, $pos_start+1);
}

function wpjam_remote_request($url, $args=array(), $err_args=array()){
	$args = wp_parse_args( $args, array(
		'timeout'			=> 5,
		'method'			=> '',
		'body'				=> array(),
		'sslverify'			=> false,
		'blocking'			=> true,	// 如果不需要立刻知道结果，可以设置为 false
		'stream'			=> false,	// 如果是保存远程的文件，这里需要设置为 true
		'filename'			=> null,	// 设置保存下来文件的路径和名字
		'need_json_decode'	=> true,
		// 'headers'		=> array('Accept-Encoding'=>'gzip;'),	//使用压缩传输数据
		// 'headers'		=> array('Accept-Encoding'=>''),
		// 'compress'		=> false,
		'decompress'		=> true,
	) );

	if(isset($_GET['debug'])){
		wpjam_print_r($args);	
	}

	$need_json_decode	= $args['need_json_decode'];
	$method				= ($args['method'])?strtoupper($args['method']):($args['body']?'POST':'GET');

	unset($args['need_json_decode']);
	unset($args['method']);

	if($method == 'GET'){
		$response = wp_remote_get($url, $args);
	}elseif($method == 'POST'){
		$response = wp_remote_post($url, $args);
	}elseif($method == 'FILE'){	// 上传文件
		$args['method'] = ($args['body'])?'POST':'GET';
		$args['sslcertificates']	= isset($args['sslcertificates'])?$args['sslcertificates']: ABSPATH.WPINC.'/certificates/ca-bundle.crt';
		$args['user-agent']			= isset($args['user-agent'])?$args['user-agent']:'WordPress';
		$wp_http_curl	= new WP_Http_Curl();
		$response		= $wp_http_curl->request($url, $args);
	}

	if(is_wp_error($response)){
		trigger_error($url."\n".$response->get_error_code().' : '.$response->get_error_message()."\n".var_export($args['body'],true));
		return $response;
	}

	if(isset($_GET['debug'])){
		wpjam_print_r($response);
	}

	$response = $response['body'];

	if($need_json_decode){
		// $response	= wpjam_strip_invalid_text($response);
		$response_json_decoded	= json_decode($response,true);

		if(is_null($response_json_decoded)){
			require_once( ABSPATH . WPINC . '/class-json.php' );
			$wp_json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);

			$response_json_decoded	= $wp_json->decode($response); 

			if(is_null($response_json_decoded)){
				if(isset($_GET['debug'])){
					wpjam_print_r(json_last_error());
					wpjam_print_r(json_last_error_msg());
				}
				// trigger_error($url."\n".'JSON_DECODE_ERROR： '. json_last_error_msg()."\n".var_export($response,true));
				return new WP_Error('JSON_DECODE_ERROR', json_last_error_msg());
			}else{
				$response = $response_json_decoded;
			}
		}else{
			$response = $response_json_decoded;
		}
	}

	if($err_args){
		extract(wp_parse_args($err_args,  array(
			'errcode'	=>'errcode',
			'errmsg'	=>'errmsg',
			'detail'	=>'detail'
		)));

		if(isset($response[$errcode]) && $response[$errcode]){
			$errcode	= $response[$errcode];
			$errmsg		= isset($response[$errmsg])?$response[$errmsg]:'';
			$errmsg		.= isset($response[$detail])?"\n".$response[$detail]:'';

			trigger_error($url."\n".$errcode.' : '.$errmsg."\n".var_export($args['body'],true));
			return new WP_Error($errcode, $errmsg);
		}
	}

	if(isset($_GET['debug'])){
		echo $url;
		wpjam_print_r($response);
	}

	return $response;
}


function wpjam_baidu_api_remote_request($url, $args=array()){

	$args = wp_parse_args( $args, array(
		'headers'	=> array('apikey'=>'494969c1cb7d9d1b05960c7257750648'),
		'body'		=> ''
	) );

	$response = wpjam_remote_request($url, $args);

	if(is_wp_error($response)){
		return $response;
	}

	if(isset($response['errNum']) && $response['errNum']){
		$errNum	= $response['errNum'];
		// $errMsg	= isset($response['errMsg'])?$response['retData'][0]:(isset($response['retMsg'])?$response['retMsg']:'');
		$errMsg	= isset($response['errMsg'])?$response['errMsg']:(isset($response['retData'])?$response['retData'][0]:'');
		
		trigger_error($url."\n".$errNum.' : '.$errMsg."\n".var_export($args['body'],true));
		
		return new WP_Error($errNum, $errMsg);
	}

	return $response;
}

















function wpjam_get_post_type_labels($label_name){
	return array(
		'name'				=> $label_name,
		'singular_name'		=> $label_name,
		'add_new'			=> '新增'.$label_name,
		'add_new_item'		=> '新增'.$label_name,
		'edit_item'			=> '编辑'.$label_name,
		'new_item'			=> '添加'.$label_name,
		'all_items'			=> '所有'.$label_name,
		'view_item'			=> '查看'.$label_name,
		'search_items'		=> '搜索'.$label_name,
		'not_found'			=> '找不到相关'.$label_name,
		'not_found_in_trash'=> '回收站中没有'.$label_name, 
		'parent_item_colon'	=> '',
		'menu_name'			=> $label_name
	);
}

function wpjam_get_taxonomy_labels($label_name){
	return array(
		'name'				=> $label_name,
		'singular_name'		=> $label_name,
		'search_items'		=> '搜索'.$label_name,
		'popular_items'		=> '最受欢迎的'.$label_name,
		'all_items'			=> '所有'.$label_name,
		'parent_item'	  	=> '父级'.$label_name,
		'parent_item_colon'	=> '父级'.$label_name,
		'edit_item'			=> '编辑'.$label_name, 
		'update_item'		=> '更新'.$label_name,
		'add_new_item'		=> '新增'.$label_name,
		'new_item_name'		=> '添加'.$label_name,
		'menu_name'			=> $label_name
	); 
}

function wpjam_nav_menu( $args = array() ){
	return wp_nav_menu( $args );
}

function wpjam_format_size($size) {
	return size_format($size, 2);
}

function wpjam_csv($args=array()){
	global $wpdb;

	extract(wp_parse_args( $args, array(
		'sql'	   => '',
		'start'	 => 0,
		'count'	 => 10000,
		'fields'	=> '',
		'filename'  => '',
		'callback'  => ''
	) ) );

	header("Content-type:text/csv"); 
	header("Content-Disposition:attachment;filename=".$filename); 
	header('Cache-Control:must-revalidate,post-check=0,pre-check=0'); 
	header('Expires:0'); 
	header('Pragma:public'); 

	foreach ($fields as $key => $value) {
		echo iconv('utf-8', 'gb2312', $value);
		echo ',';
	}
	echo "\n";

	$per_page   = ($count > 500)?500:$count;

	do{
		$the_sql = $sql.' LIMIT '.$start.','.$per_page;
		
		$datas = $wpdb->get_results($the_sql, ARRAY_A);
		$total = $wpdb->get_var("SELECT FOUND_ROWS();");

		if(empty($end)){
			$end = $start+$count;
			$end = ($total > $end)?$end:$total;
		}

		foreach ($datas as $data) {
			if($callback){
				$data = call_user_func($callback, $data);
			}
			foreach ($fields as $key => $value) {
				if($data[$key]) {
					echo iconv('utf-8', 'gb2312', $data[$key]);
				}
				echo ',';
			}
			echo "\n";
		}
		$start = $start + $per_page;
	}while ($start < $end );
}

function get_avatar_src($id_or_email, $size = '96', $default = ''){
	$args = compact('size', 'defaule');
	return get_avatar_url($id_or_email, $args);
}