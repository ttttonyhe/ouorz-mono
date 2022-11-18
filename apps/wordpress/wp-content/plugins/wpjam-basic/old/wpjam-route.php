<?php
//设置 headers
add_action('send_headers', 'wpjam_send_headers');
function wpjam_send_headers($wp){
	$module = isset($wp->query_vars['module'])?$wp->query_vars['module']:'';
	$action = isset($wp->query_vars['action'])?$wp->query_vars['action']:'';

	if($module)	remove_action('template_redirect', 'redirect_canonical');

	if($module == 'json'){ // 输出 JSON header
		$content_type = isset( $_GET['callback'] ) ? 'application/javascript' : 'application/json';

		header( 'Content-Type: ' .  $content_type.'; charset=' . get_option('blog_charset') );
		header( 'X-Content-Type-Options: nosniff' );
		// header( 'Access-Control-Expose-Headers: X-WP-Total, X-WP-TotalPages' );
		// header( 'Access-Control-Allow-Headers: Authorization' );

		// if(strpos($action, 'get_') === 0){
		// 	header( 'Access-Control-Allow-Methods: GET' );
		// }else{
		// 	header( 'Access-Control-Allow-Methods: GET POST' );
		// }

		if(isset($_GET['callback'])){
			send_origin_headers();
		}
	}

	do_action('wpjam_send_headers', $module, $action);
}

add_filter('request', 'wpjam_request');
function wpjam_request($query_vars){
	if(!empty($_GET['tag_id'])){
		$query_vars['tag_id'] = $_GET['tag_id'];
	}else{
		$custom_taxonomies = wpjam_get_custom_taxonomies();
		if($custom_taxonomies){
			foreach ($custom_taxonomies as $custom_taxonomy) {

				$custom_taxonomy_id = isset($query_vars[$custom_taxonomy.'_id'])?$query_vars[$custom_taxonomy.'_id']:'';
				if(!$custom_taxonomy_id){
					$custom_taxonomy_id = isset($_GET[$custom_taxonomy.'_id'])?$_GET[$custom_taxonomy.'_id']:'';
				}

				if($custom_taxonomy_id){
					$term		= get_term($custom_taxonomy_id, $custom_taxonomy);
					$query_vars['taxonomy'] = $custom_taxonomy;
					$query_vars['term'] 	= $term->slug;
				}
			}
		}
	}

	return $query_vars;
}

add_filter('query_vars', 'wpjam_route_query_vars');
function wpjam_route_query_vars($public_query_vars) {
	$public_query_vars[]	= 'module';
	$public_query_vars[]	= 'action';
	$custom_taxonomies = wpjam_get_custom_taxonomies();
	if($custom_taxonomies){
		foreach ($custom_taxonomies as $custom_taxonomy) {
			$public_query_vars[]	= $custom_taxonomy.'_id';
		}
	}

	// print_R($public_query_vars);
	return $public_query_vars;
}

add_filter('template_include', 'wpjam_template_include');
function wpjam_template_include($template){
	$module	= get_query_var('module');
	$action	= get_query_var('action');

	if($module){
		$action = ($action == 'new' || $action == 'add')?'edit':$action;

		if($action){
			$wpjam_template = STYLESHEETPATH.'/template/'.$module.'/'.$action.'.php';
		}else{
			$wpjam_template = STYLESHEETPATH.'/template/'.$module.'/index.php';
		}

        $wpjam_template		= apply_filters( 'wpjam_template', $wpjam_template, $module, $action );

        if(is_file($wpjam_template)){
			return $wpjam_template;
		}else{
			wp_die('路由错误！');
		}
	}

	return $template;
}

add_filter( 'posts_where', 'wpjam_api_posts_where',10,2);
function wpjam_api_posts_where($where, $wp_query){
	global $wpdb;

	if($wp_query->is_main_query()){
		$first_time	= !empty($_GET['first_time'])?get_date_from_gmt(date('Y-m-d H:i:s',(int)$_GET['first_time'])):'';
		$last_time	= !empty($_GET['last_time'])?get_date_from_gmt(date('Y-m-d H:i:s',(int)$_GET['last_time'])):'';

		if(!$first_time && !$last_time){    //不指定first_time和last_time，默认返回最新的数据，就是客户端第一次加载
		    //do nothing
		}elseif($first_time){               //指定first_time，获取大于first_time的最新数据，就是客户端下拉刷新
		    $where .= " AND ({$wpdb->posts}.post_date > '{$first_time}')";
		}elseif($last_time){                //指定last_time，获取小于last_time的更多数据，就是加载更多
		    $where .= " AND ({$wpdb->posts}.post_date < '{$last_time}')";
		}
	}

	return $where;
}

function wpjam_api_set_response(&$response){
	global $wp_query;

	if($wp_query->have_posts()){

		if(isset($_GET['s'])){
			$response['total_pages']	= (int)$wp_query->max_num_pages;
			$response['current_page']	= (int)(isset($_GET['paged'])?$_GET['paged']:1);
		}else{
			$response['has_more']	= ($wp_query->max_num_pages>1)?1:0;

			$first_post_time = (int)strtotime(get_gmt_from_date($wp_query->posts[0]->post_date));
			$post = end($wp_query->posts);
			$last_post_time = (int)strtotime(get_gmt_from_date($post->post_date));

			$first_time	= isset($_GET['first_time'])?(int)$_GET['first_time']:'';
			$last_time	= isset($_GET['last_time'])?(int)$_GET['last_time']:'';

			if(!$first_time && !$last_time){								//第一次加载，需要返回first_time和最后last_time
				$response['first_time']	= $first_post_time;
				$response['last_time'] 	= $last_post_time;
			}elseif($first_time && $wp_query->max_num_pages > 1){			//下拉刷新，数据超过一页：需要返回fist_time和last_time，客户端需要把所有数据清理
				$response['first_time']	= $first_post_time;
				$response['last_time'] 	= $last_post_time;
			}elseif($first_time && $wp_query->max_num_pages < 2){			//下拉刷新，数据不超过一页：需要返回first_time，不需要last_time
				$response['first_time']	= $first_post_time;
			}elseif($last_time){											//加载更多：不需要first_time，需要返回last_time
				$response['last_time']	= $last_post_time;
			}

			$response['total_pages']	= (int)$wp_query->max_num_pages;
			$response['current_page']	= (int)(isset($_GET['paged'])?$_GET['paged']:1);
		}
	}
}

function wpjam_api_signon(){
	$user = isset($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:'';
	$pass = isset($_SERVER['PHP_AUTH_PW'])?$_SERVER['PHP_AUTH_PW']:'';

	if(empty($user) || empty($pass)){
		return false;
	}

	$wp_user = wp_signon(array(
		'user_login'	=> $user,
		'user_password'	=> $pass,
	));

	if(is_wp_error($wp_user)){
		return false;
	}else{
		// $current_user	= wp_get_current_user();
		if(current_user_can('mamage_options')){
			return true;
		}else{
			return false;
		}
	}
}

function wpjam_output_json($response, $options = JSON_UNESCAPED_UNICODE, $depth = 512){
	wpjam_send_json($response, $options,$depth);
}

function wpjam_send_json($response, $options = JSON_UNESCAPED_UNICODE, $depth = 512){
	if(isset($_REQUEST['callback'])){
		echo $_REQUEST['callback'].'('.wp_json_encode($response, $options, $depth).')';
	}else{
		echo wp_json_encode($response, $options, $depth);
	}
	exit;
}

function wpjam_json_encode( $data, $options = JSON_UNESCAPED_UNICODE, $depth = 512){
	return wp_json_encode( $data, $options, $depth );
}

function is_module($module='', $action=''){
	$current_module	= get_query_var('module');
	$current_action	= get_query_var('action');

	if(!$current_module){	// 没设置 module
		return false;
	}

	if(!$module){			// 不用确定当前是什么 module
		return true;
	}

	if($module != $current_module){
		return false;
	}

	if(!$action){
		return true;
	}

	if($action != $current_action){
		return false;
	}

	return true;
}

// add_action('parse_query','wpjam_parse_query');
// function wpjam_parse_query($query){
// 	$module = isset($query['module'])?$query['module']:'';
// 	$action = isset($query['module'])?$query['module']:'';

// 	if($module){
// 		// $query->is_home 	= false;	// 不能这样设置， JSON 和 移动网站的 is_home 就没法用了
// 		$query->is_module 	= true;
// 	}
// }

/**
 * 解决客户端COOKIE过期后不失效的问题
 */
//add_action('auth_cookie_expired', 'wp_clear_auth_cookie');

add_action('generate_rewrite_rules', 'wpjam_generate_rewrite_rules');
function wpjam_generate_rewrite_rules($wp_rewrite){

    $wpjam_rewrite_rules = array();
    $wpjam_rewrite_rules['api/([^/]+).json?$']					= 'index.php?module=json&action=$matches[1]';
    $wpjam_rewrite_rules	= apply_filters('wpjam_rewrite_rules', $wpjam_rewrite_rules);
    $wp_rewrite->rules		= array_merge($wpjam_rewrite_rules, $wp_rewrite->rules);

}