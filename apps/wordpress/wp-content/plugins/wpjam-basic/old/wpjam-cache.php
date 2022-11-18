<?php
//给 wp_nav_menu 加上对象缓存，加快效率
//add_filter( 'pre_wp_nav_menu', 'wpjam_get_nav_menu_cache', 10, 2 );
//function wpjam_get_nav_menu_cache( $nav_menu, $args ) {
//    $cache_key		= wpjam_get_nav_menu_cache_key($args);
//    $cached_menus	= wpjam_get_nav_menus_cache();
//
//    if ( isset($cached_menus[$cache_key]) ){
//        return $cached_menus[$cache_key];
//    }
//
//    return $nav_menu;
//}
//
//add_filter( 'wp_nav_menu', 'wpjam_set_nav_menu_cache', 10, 2 );
//function wpjam_set_nav_menu_cache( $nav_menu, $args ) {
//    $cache_key      = wpjam_get_nav_menu_cache_key($args);
//    $cached_menus	= wpjam_get_nav_menus_cache();
//    $cached_menus[$cache_key]	= $nav_menu;
//    set_transient( 'wpjam-nav-menus', $cached_menus, 3600 );
//
//    return $nav_menu;
//}
//
//function wpjam_get_nav_menus_cache(){
//	$nav_menus	= get_transient( 'wpjam-nav-menus' );
//	$nav_menus	= ($nav_menus)?$nav_menus:array();
//	return $nav_menus;
//}
//
//function wpjam_get_nav_menu_cache_key($args){
//	// Get the nav menu based on the requested menu
//	$menu = wp_get_nav_menu_object( $args->menu );
//
//	// Get the nav menu based on the theme_location
//	if ( ! $menu && $args->theme_location && ( $locations = get_nav_menu_locations() ) && isset( $locations[ $args->theme_location ] ) )
//		$menu = wp_get_nav_menu_object( $locations[ $args->theme_location ] );
//
//	// get the first menu that has items if we still can't find a menu
//	if ( ! $menu && !$args->theme_location ) {
//		$menus = wp_get_nav_menus();
//		foreach ( $menus as $menu_maybe ) {
//			if ( $menu_items = wp_get_nav_menu_items( $menu_maybe->term_id, array( 'update_post_term_cache' => false ) ) ) {
//				$menu = $menu_maybe;
//				break;
//			}
//		}
//	}
//
//	if ( empty( $args->menu ) ) {
//		$args->menu = $menu;
//	}
//
//    return apply_filters( 'nav_menu_cache_key', 'nav-menu-' . md5( serialize( $args ).serialize(get_queried_object()) ) );
//}
//
//// 更新菜单，清理缓存
//add_action( 'wp_update_nav_menu', 'wpjam_delete_nav_menus_cache' );
//function wpjam_delete_nav_menus_cache( $menu_id){
//	delete_transient( 'wpjam-nav-menus' );
//}

// add_filter('the_comments', 'wpjam_the_comments', 10, 2);
// function wpjam_the_comments( $comments, $wp_comment_query ){
// 	wp_cache_set(md5(serialize($wp_comment_query->query_vars)), $comments, 'wpjam_comments', 3600);
// 	return $comments;
// }

// add_action('pre_get_comments','wpjam_pre_get_comments');
// function wpjam_pre_get_comments( $wp_comment_query ){
// 	$comments = wp_cache_get(md5(serialize($wp_comment_query->query_vars)), 'wpjam_comments');
// 	if($comments !== false){
// 		return $comments;
// 	}
// }

// global $wp_object_cache;
// foreach ($wp_object_cache->no_mc_groups as $no_mc_group_key => $no_mc_group_name ) {
// 	if( $no_mc_group_name == 'comment'){
// 		unset($wp_object_cache->no_mc_groups[$no_mc_group_key]);
// 	}
// }

// 设置 WP Query 缓存
function wpjam_query($args=array(), $cache_time='600'){
	$cache_key		= 'wpjam_query'.md5(serialize($args));

	$wpjam_query = get_transient($cache_key);

	if($wpjam_query === false){
		$wpjam_query = new WP_Query($args);
		set_transient($cache_key, $wpjam_query, $cache_time);
	}

	return $wpjam_query;
}

function wpjam_query_cache($args=array(), $cache_time='600'){
	return wpjam_query($args, $cache_time);
}

/*
Usage:
	$wpjam_html_cache = new WPJAM_HTML_Cache(); // Second param is TTL
	if ( !$wpjam_html_cache->output('unique-key') ) { // NOTE, testing for a return of false
		functions_that_do_stuff_live();
		these_should_echo();
		// IMPORTANT
		$wpjam_html_cache->store('3600');
		// YOU CANNOT FORGET THIS. If you do, the site will break.
	}
*/

// 页面片段缓存
class WPJAM_HTML_Cache {
	var $key;

	// public function __construct( $key, $ttl='600' ) {
	// 	$this->key = 'wpjam_html_cache_'.$key;
	// 	$this->ttl = $ttl;
	// }

	public function output($key) {
		$this->key	= 'wpjam_html_cache_'.$key;
		$output		= get_transient( $this->key );
		if ( !empty( $output ) ) {
			echo $output;
			return true;
		} else {
			ob_start();
			return false;
		}
	}

	public function store($cache_time='600') {
		$output = ob_get_flush();
		set_transient( $this->key, $output, $cache_time );
	}
}

global $wpjam_html_cache;
$wpjam_html_cache = new WPJAM_HTML_Cache();



// 高级缓存，缓存 posts 列表

// 生成当前 WP_Query 的 Cache Key
add_filter( 'posts_selection', 'wpjam_generate_query_cache_key' );
function wpjam_generate_query_cache_key ( $posts_selection ) {	
	$GLOBALS['query_cache_key'] = md5( $posts_selection );
}

// 获取当前 WP_Query 的缓存
function wpjam_get_query_cache(){
	$cached = wp_cache_get( "query_where", 'adv_post_cache' );

	if($cached && isset($cached[ $GLOBALS['query_cache_key'] ])){
		return $cached[ $GLOBALS['query_cache_key'] ];
	}else{
		return false;
	}
}

// 设置当前 WP_Query 的缓存，注意只缓存主循环
function wpjam_set_query_cache($post_ids){
	$cached = wp_cache_get( "query_where", 'adv_post_cache' );

	if ( !isset( $cached[ $GLOBALS['query_cache_key'] ] ) ) {
		$cached[ $GLOBALS['query_cache_key'] ] = $post_ids;
		wp_cache_set( "query_where", $cached, 'adv_post_cache' );
	}
}

add_filter( 'posts_join_request',		'wpjam_blank_if_cached' );
add_filter( 'posts_groupby_request',	'wpjam_blank_if_cached' );
add_filter( 'posts_orderby_request',	'wpjam_blank_if_cached' );
add_filter( 'found_posts_query',		'wpjam_blank_if_cached' );
function wpjam_blank_if_cached( $v ) {

	if($post_ids = wpjam_get_query_cache()){
		return '';
	}else{
		return $v;
	}
}

add_filter( 'post_limits_request', 'wpjam_lame_hack_if_cached' ); // we still need the FOUND_ROWS stuff to run
function wpjam_lame_hack_if_cached( $v ) {
	if ( $v && ( !$v = wpjam_blank_if_cached( $v ) ) )
		return ' /* */ ';
	return $v;
}

add_filter( 'posts_where_request', 'wpjam_make_ids_if_cached' );
function wpjam_make_ids_if_cached( $where ) {
	global $wpdb;

	if(!$post_ids = wpjam_get_query_cache())
		return $where;

	// but if we have the IDs
	// remove IDs we can cache
	$to_grab = array();
	foreach ( $post_ids as $post_id ) {
		$value = wp_cache_get( $post_id, 'posts' );
		if ( !is_object( $value ) )
			$to_grab[] = $post_id;
	}

	$new = '';
	if ( $to_grab ) {
		$new = " AND $wpdb->posts.ID IN ( " . join( ',', $to_grab ) . ' ) ';
	} else { // if all the posts are cached, kill the query later
		add_filter( 'posts_request', 'wpjam_blank_posts_request_once' );
	}
	return $new;
}

function wpjam_blank_posts_request_once() {
	remove_filter( 'posts_request', 'wpjam_blank_posts_request_once' );
	return '';
}

add_filter( 'the_posts', 'wpjam_adv_post_cache', 10, 2 );
function wpjam_adv_post_cache( $posts, $wp_query ) {

	if(!$wp_query->is_main_query()){	// 只缓存主循环
		return $posts;
	}

	if(isset($wp_query->query_vars['orderby']) && $wp_query->query_vars['orderby'] == 'rand'){	// 随机排序就不能缓存了
		return $posts;
	}

	$status = apply_filters('wpjam_query_cache', true, $wp_query);

	if($status === false){
		return $posts;
	}
	
	$post_ids = array();
	foreach ( $posts as $p )
		$post_ids[] = $p->ID;

	if ( !$post_ids )	// 已经缓存了
		return array();

	wpjam_set_query_cache($post_ids);

	return $posts;
}

add_filter( 'posts_results', 'wpjam_reorder_posts_from_cache' );
function wpjam_reorder_posts_from_cache( $posts ) {
	// the final problem is that mysql returns the posts in the order of their ID, asc, rather than the order we cached them in
	// so here we juggle the array back how it started
	
	if(!$post_ids = wpjam_get_query_cache())
		return $posts;

	if ( !$posts )
		$posts = array();

	$got_ids = $to_get = array();
	foreach ( $posts as $p )
		$got_ids[] = $p->ID;

	$to_get = array_diff( $post_ids, $got_ids );
	foreach ( $to_get as $post_id ) {
		$post = wp_cache_get( $post_id, 'posts' );
		if ( $post )
			$posts[] = $post;
	}

	if ( 1 == count( $post_ids ) ) // no needto reorder if there's just one
		return $posts;

	
	foreach ( $posts as $p ) {
		$loc = array_search( $p->ID, $post_ids );
		$new_posts[ $loc ] = $p;
	}
	ksort( $new_posts );

	return $new_posts;
}

add_filter( 'found_posts', 'wpjam_cached_posts_found' );
function wpjam_cached_posts_found( $v ) {
	$found = wp_cache_get( 'posts_found', 'adv_post_cache' );

	// "it must have been" = "we think it probably was"
	// If it's set, then it must have been an IN query and $v is wrong
	if ( isset( $found[ $GLOBALS['query_cache_key'] ] ) )
		return $found[ $GLOBALS['query_cache_key'] ];

	// If it's not set, it must have been fresh query, so $v must be right.
	$found[ $GLOBALS['query_cache_key'] ] = $v;
	wp_cache_set( 'posts_found', $found, 'adv_post_cache' );
	return $v;
}

function dumpit( $v ) {
	var_dump($v);
	return $v;
}
//add_filter( 'posts_request', 'dumpit' );


add_action( 'clean_term_cache', 'wpjam_clear_adv_post_cache' );
add_action( 'clean_post_cache', 'wpjam_clear_adv_post_cache' );
function wpjam_clear_adv_post_cache($arg = '') {
	wp_cache_delete( 'query_where', 'adv_post_cache' );
	wp_cache_delete( 'posts_found', 'adv_post_cache' );
}
