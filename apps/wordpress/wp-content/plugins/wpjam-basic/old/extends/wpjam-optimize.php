<?php
/*
Plugin Name: 性能优化
Plugin URI: http://wpjam.net/item/wpjam-basic/
Description: 可以让你关闭 WordPress 中一些不常用的功能来提速
Version: 1.0
 */

//移除 WP_Head 无关紧要的代码
if (wpjam_basic_get_setting('remove_head_links')) {
    remove_action('wp_head', 'wp_generator'); //删除 head 中的 WP 版本号
    foreach (array('rss2_head', 'commentsrss2_head', 'rss_head', 'rdf_header', 'atom_head', 'comments_atom_head', 'opml_head', 'app_head') as $action) {
        remove_action($action, 'the_generator');
    }

    remove_action('wp_head', 'rsd_link'); //删除 head 中的 RSD LINK
    remove_action('wp_head', 'wlwmanifest_link'); //删除 head 中的 Windows Live Writer 的适配器？

    remove_action('wp_head', 'feed_links_extra', 3); //删除 head 中的 Feed 相关的link
    //remove_action( 'wp_head', 'feed_links', 2 );

    remove_action('wp_head', 'index_rel_link'); //删除 head 中首页，上级，开始，相连的日志链接
    remove_action('wp_head', 'parent_post_rel_link', 10);
    remove_action('wp_head', 'start_post_rel_link', 10);
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);

    remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0); //删除 head 中的 shortlink

    remove_action('wp_head', 'rest_output_link_wp_head', 10); // 删除头部输出 WP RSET API 地址

    remove_action('template_redirect', 'wp_shortlink_header', 11); //禁止短链接 Header 标签。
    remove_action('template_redirect', 'rest_output_link_header', 11); // 禁止输出 Header Link 标签。
}

//让用户自己决定是否书写正确的 WordPress
foreach (array('the_content', 'the_title', 'wp_title') as $filter) {
    remove_filter($filter, 'capital_P_dangit', 11);
}
remove_filter('comment_text', 'capital_P_dangit', 31);

//让 Shortcode 优先于 wpautop 执行。
if (wpjam_basic_get_setting('shortcode_first')) {
    remove_filter('the_content', 'wpautop');
    add_filter('the_content', 'wpautop', 12);
    remove_filter('the_content', 'shortcode_unautop');
    add_filter('the_content', 'shortcode_unautop', 13);
}

//禁用日志修订功能
if (wpjam_basic_get_setting('diable_revision')) {
    define('WP_POST_REVISIONS', false);
    remove_action('pre_post_update', 'wp_save_post_revision');

    // 自动保存设置为10个小时
    define('AUTOSAVE_INTERVAL', 36000);
}

//移除 admin bar
if (wpjam_basic_get_setting('remove_admin_bar')) {
    add_filter('show_admin_bar', '__return_false');
}

//禁用 XML-RPC 接口
if (wpjam_basic_get_setting('disable_xml_rpc')) {
    add_filter('xmlrpc_enabled', '__return_false');
    remove_action('xmlrpc_rsd_apis', 'rest_output_rsd');
}

if (wpjam_basic_get_setting('disable_trackbacks')) {
    //彻底关闭 pingback
    add_filter('xmlrpc_methods', 'wpjam_xmlrpc_methods');
    function wpjam_xmlrpc_methods($methods)
    {
        $methods['pingback.ping']                    = '__return_false';
        $methods['pingback.extensions.getPingbacks'] = '__return_false';

        return $methods;
    }

    //禁用 pingbacks, enclosures, trackbacks
    remove_action('do_pings', 'do_all_pings', 10);

    //去掉 _encloseme 和 do_ping 操作。
    remove_action('publish_post', '_publish_post_hook', 5);
}

//阻止非法访问
add_action('init', 'wpjam_block_bad_queries');
function wpjam_block_bad_queries()
{
    if (is_admin()) {
        return;
    }
    //if(strlen($_SERVER['REQUEST_URI']) > 255 ||
    if (
        strpos($_SERVER['REQUEST_URI'], "eval(") ||
        strpos($_SERVER['REQUEST_URI'], "base64") ||
        strpos($_SERVER['REQUEST_URI'], "/**/")
    ) {
        @header("HTTP/1.1 414 Request-URI Too Long");
        @header("Status: 414 Request-URI Too Long");
        @header("Connection: Close");
        @exit;
    }
}

// 禁止使用 admin 用户名尝试登录
if (wpjam_basic_get_setting('no_admin')) {
    add_filter('wp_authenticate', 'wpjam_no_admin_user');
    function wpjam_no_admin_user($user)
    {
        if ('admin' == $user) {
            exit;
        }
    }

    add_filter('sanitize_user', 'wpjam_sanitize_user_no_admin', 10, 3);
    function wpjam_sanitize_user_no_admin($username, $raw_username, $strict)
    {
        if ('admin' == $raw_username || 'admin' == $username) {
            exit;
        }

        return $username;
    }
}

//前台不加载语言包
if (wpjam_basic_get_setting('locale')) {
    global $wpjam_locale;
    $wpjam_locale = get_locale();

    add_filter('language_attributes', 'wpjam_language_attributes');
    function wpjam_language_attributes($language_attributes)
    {
        global $wpjam_locale;

        if (function_exists('is_rtl') && is_rtl()) {
            $attributes[] = 'dir="rtl"';
        }

        if ($wpjam_locale) {
            if (get_option('html_type') == 'text/html') {
                $attributes[] = "lang=\"$wpjam_locale\"";
            }

            if (get_option('html_type') != 'text/html') {
                $attributes[] = "xml:lang=\"$wpjam_locale\"";
            }
        }

        $output = implode(' ', $attributes);

        return $output;
    }

    add_filter('locale', 'wpjam_locale');
}

function wpjam_locale($locale)
{
    $locale = (is_admin()) ? $locale : 'en_US';

    return $locale;
}

if (wpjam_basic_get_setting('strict_user')) {
    add_filter('sanitize_user', 'wpjam_sanitize_user', 3, 3);
    function wpjam_sanitize_user($username, $raw_username, $strict)
    {
        // 设置用户名只能大小写字母和 - . _
        $username = preg_replace('|[^a-z0-9_.\-]|i', '', $username);

        //检测待审关键字和黑名单关键字
        if (wpjam_blacklist_check($username)) {
            $username = '';
        }

        return $username;
    }
}

// 屏蔽 Emoji
if (wpjam_basic_get_setting('disable_emoji')) {
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');

    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');

    remove_action('embed_head', 'print_emoji_detection_script');

    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

    add_filter('tiny_mce_plugins', 'wpjam_disable_emoji_tiny_mce_plugin');
    function wpjam_disable_emoji_tiny_mce_plugin($plugins)
    {
        return array_diff($plugins, array('wpemoji'));
    }

    add_filter('emoji_svg_url', '__return_false');
}

// 屏蔽 REST API
if (wpjam_basic_get_setting('disable_rest_api')) {
    remove_action('init', 'rest_api_init');
    remove_action('rest_api_init', 'rest_api_default_filters', 10);
    remove_action('parse_request', 'rest_api_loaded');

    add_filter('rest_enabled', '__return_false');
    add_filter('rest_jsonp_enabled', '__return_false');

    // 移除头部 wp-json 标签和 HTTP header 中的 link
    remove_action('wp_head', 'rest_output_link_wp_head', 10);
    remove_action('template_redirect', 'rest_output_link_header', 11);
}

//禁用 Auto OEmbed
if (wpjam_basic_get_setting('disable_autoembed')) {
    //remove_filter( 'the_content', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );
    remove_filter('the_content', array($GLOBALS['wp_embed'], 'autoembed'), 8);
    //remove_action( 'pre_post_update', array( $GLOBALS['wp_embed'], 'delete_oembed_caches' ) );
    //remove_action( 'edit_form_advanced', array( $GLOBALS['wp_embed'], 'maybe_run_ajax_cache' ) );
}

if (wpjam_basic_get_setting('disable_post_embed')) {
    remove_action('rest_api_init', 'wp_oembed_register_route');
    remove_filter('rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10, 4);

    add_filter('embed_oembed_discover', '__return_false');

    remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
    remove_filter('oembed_response_data', 'get_oembed_response_data_rich', 10, 4);

    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');

    add_filter('tiny_mce_plugins', 'wpjam_disable_post_embed_tiny_mce_plugin');
    function wpjam_disable_post_embed_tiny_mce_plugin($plugins)
    {
        return array_diff($plugins, array('wpembed'));
    }

    add_filter('query_vars', 'wpjam_wpjam_disable_post_embed_query_var');
    function wpjam_wpjam_disable_post_embed_query_var($public_query_vars)
    {
        return array_diff($public_query_vars, array('embed'));
    }
}

if (wpjam_basic_get_setting('disable_auto_update')) {
    add_filter('automatic_updater_disabled', '__return_true');
    remove_action('init', 'wp_schedule_update_checks');
}

//删除中文包中的一些无用代码
add_action('init', 'remove_zh_ch_functions');
function remove_zh_ch_functions()
{
    remove_action('admin_init', 'zh_cn_l10n_legacy_option_cleanup');
    remove_action('admin_init', 'zh_cn_l10n_settings_init');
    wp_embed_unregister_handler('tudou');
    wp_embed_unregister_handler('youku');
    wp_embed_unregister_handler('56com');
}

//当搜索结果只有一篇时直接重定向到日志
add_action('template_redirect', 'wpjam_redirect_single_post');
function wpjam_redirect_single_post()
{
    if (is_search() && get_query_var('module') == '') {
        global $wp_query;
        $paged = get_query_var('paged');
        if (1 == $wp_query->post_count && empty($paged)) {
            wp_redirect(get_permalink($wp_query->posts['0']->ID));
        }
    }
}

//remove_action( 'template_redirect', 'wp_old_slug_redirect');
//remove_action( 'template_redirect', 'redirect_canonical');
//解决日志改变 post type 之后跳转错误的问题，
add_action('template_redirect', 'wpjam_old_slug_redirect', 1);
function wpjam_old_slug_redirect()
{
    global $wp_query;

    // wpjam_print_r($wp_query);

    if (is_404() && '' != $wp_query->query_vars['name']) {
        global $wpdb;

        $id = (int) $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_old_slug' AND meta_value = %s", $wp_query->query_vars['name']));

        if (!$id) {
            $link = wpjam_redirect_guess_404_permalink();
        } else {
            $link = get_permalink($id);
        }

        if ($link) {
            wp_redirect($link, 301);
            exit;
        }
    }
}

function wpjam_redirect_guess_404_permalink()
{
    global $wpdb, $wp_rewrite;

    if (get_query_var('name') == 'feed') {
        return false;
    }

    if (get_query_var('name')) {
        $where = $wpdb->prepare("post_name LIKE %s", $wpdb->esc_like(get_query_var('name')) . '%');

        $post_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE $where AND post_status = 'publish'");
        if (!$post_id) {
            return false;
        }

        if (get_query_var('feed')) {
            return get_post_comments_feed_link($post_id, get_query_var('feed'));
        } elseif (get_query_var('page')) {
            return trailingslashit(get_permalink($post_id)) . user_trailingslashit(get_query_var('page'), 'single_paged');
        }
        else{
            return get_permalink($post_id);
        }
    }

    return false;
}

// 支持 term 排序
add_filter("terms_clauses", 'wpjam_terms_clauses', 10, 3);
function wpjam_terms_clauses($pieces, $taxonomies, $args)
{
    if (!is_admin()) {
        $orderby = $args['orderby'];

        if ('meta_key' == $orderby || 'meta_key_num' == $orderby) {
            $meta_key = isset($args['meta_key']) ? $args['meta_key'] : '';

            if (empty($meta_key)) {
                $pieces['orderby'] = 't.name';

                return $pieces;
            }

            global $wpdb;
            $pieces['join']  = $pieces['join'] . " LEFT JOIN {$wpdb->prefix}termmeta AS tm ON t.term_id = tm.term_id";
            $pieces['where'] = $pieces['where'] . " AND tm.meta_key = '{$meta_key}'";
            if ('meta_key' == $orderby) {
                $pieces['orderby'] = "GROUP BY t.term_id ORDER BY tm.meta_value";
            } elseif ('meta_key_num' == $orderby) {
                $pieces['orderby'] = "GROUP BY t.term_id ORDER BY (tm.meta_value + 0)";
            }
        }
    }

    return $pieces;
}

add_filter('get_avatar_url', 'wpjam_get_avatar_url', 10, 3);
function wpjam_get_avatar_url($url, $id_or_email, $args)
{
    if ($custom_avatar = wpjam_get_custom_avatar_src($id_or_email, $args['size'])) {
        return $custom_avatar;
    }

    return str_replace(array("www.gravatar.com", "0.gravatar.com", "1.gravatar.com", "2.gravatar.com"), "cn.gravatar.com", $url);
}

add_action('generate_rewrite_rules', 'wpjam_optimize_rewrite_rules');
function wpjam_optimize_rewrite_rules($wp_rewrite)
{

    $wp_rewrite->rules           = wpjam_remove_rewrite_rules($wp_rewrite->rules);
    $wp_rewrite->extra_rules_top = wpjam_remove_rewrite_rules($wp_rewrite->extra_rules_top);

    $wpjam_rewrite_rules = array();

    //重新加回全站的 feed permalink
    $wpjam_rewrite_rules['feed/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?&feed=$matches[1]';
    $wpjam_rewrite_rules['(feed|rdf|rss|rss2|atom)/?$']      = 'index.php?&feed=$matches[1]';

    $wp_rewrite->rules = array_merge($wpjam_rewrite_rules, $wp_rewrite->rules);
}

function wpjam_remove_rewrite_rules($rules)
{

    $unuse_rewrite_keys = array('comment-page', 'comment', 'author', 'type/', 'feed=', 'attachment');

    foreach ($unuse_rewrite_keys as $i => $unuse_rewrite_key) {
        if (wpjam_basic_get_setting('remove_' . $unuse_rewrite_key . '_rewrite') == false) {
            unset($unuse_rewrite_keys[$i]);
        }
    }

    foreach ($rules as $key => $rule) {
        if ($unuse_rewrite_keys) {
            foreach ($unuse_rewrite_keys as $unuse_rewrite_key) {
                if (strpos($key, $unuse_rewrite_key) !== false || strpos($rule, $unuse_rewrite_key) !== false) {
                    unset($rules[$key]);
                }
            }
        }
        if (wpjam_basic_get_setting('disable_post_embed')) {
            if (strpos($rule, 'embed=true') !== false) {
                unset($rules[$key]);
            }
        }

        if (wpjam_basic_get_setting('disable_trackbacks')) {
            if (strpos($rule, 'tb=1') !== false) {
                unset($rules[$key]);
            }
        }
    }

    return $rules;
}

add_action('wpjam_remove_invild_crons', 'wpjam_remove_invild_crons');
function wpjam_remove_invild_crons()
{
    global $wp_filter;

    $wp_crons = _get_cron_array();

    foreach ($wp_crons as $timestamp => $wp_cron) {
        foreach ($wp_cron as $hook => $dings) {
            if (empty($wp_filter[$hook])) {
                // 系统不存在的定时作业，清理掉
                foreach ($dings as $sig => $data) {
                    wp_unschedule_event($timestamp, $hook, $data['args']);
                }
            }
        }
    }
}

add_action('plugins_loaded', 'wpjam_basic_cron');
function wpjam_basic_cron()
{
    if (!wpjam_is_scheduled_event('wpjam_remove_invild_crons')) {
        wp_schedule_event(time(), 'daily', 'wpjam_remove_invild_crons');
    }
}

add_filter('parse_query', 'wpjam_parse_query');
function wpjam_parse_query($query)
{
    if (isset($_GET['w'])) {
        // 去掉星期参数
        unset($query->query['w']);
        unset($query->query_vars['w']);
    }
}

// 用户未登录时，设置 304 header
// if(wpjam_basic_get_setting('304_headers')){
//     add_filter('wp_headers','wpjam_headers',10,2);
//     function wpjam_headers($headers,$wp){
//         unset($headers['X-Pingback']);
//         if(!is_user_logged_in() && empty($wp->query_vars['feed'])){
//             $headers['Cache-Control']    = 'max-age:600';
//             $headers['Expires']         = gmdate('D, d M Y H:i:s', time()+600) . " GMT";

//             //$wpjam_timestamp = get_lastpostmodified('GMT')>get_lastcommentmodified('GMT')?get_lastpostmodified('GMT'):get_lastcommentmodified('GMT');
//             $wpjam_timestamp = get_lastpostmodified('GMT');
//             $wp_last_modified = mysql2date('D, d M Y H:i:s', $wpjam_timestamp, 0).' GMT';
//             $wp_etag = '"' . md5($wp_last_modified) . '"';
//             $headers['Last-Modified'] = $wp_last_modified;
//             $headers['ETag'] = $wp_etag;

//             // Support for Conditional GET
//             if (isset($_SERVER['HTTP_IF_NONE_MATCH']))
//                 $client_etag = stripslashes(stripslashes($_SERVER['HTTP_IF_NONE_MATCH']));
//             else $client_etag = false;

//             $client_last_modified = empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? '' : trim($_SERVER['HTTP_IF_MODIFIED_SINCE']);
//             // If string is empty, return 0. If not, attempt to parse into a timestamp
//             $client_modified_timestamp = $client_last_modified ? strtotime($client_last_modified) : 0;

//             // Make a timestamp for our most recent modification...
//             $wp_modified_timestamp = strtotime($wp_last_modified);

//             $exit_required = false;

//             if ( ($client_last_modified && $client_etag) ?
//                      (($client_modified_timestamp >= $wp_modified_timestamp) && ($client_etag == $wp_etag)) :
//                      (($client_modified_timestamp >= $wp_modified_timestamp) || ($client_etag == $wp_etag)) ) {
//                 $status = 304;
//                 $exit_required = true;
//             }

//             if ( $exit_required ){
//                 if ( ! empty( $status ) ){
//                     status_header( $status );
//                 }
//                 foreach( (array) $headers as $name => $field_value ){
//                     @header("{$name}: {$field_value}");
//                 }

//                 if ( isset( $headers['Last-Modified'] ) && empty( $headers['Last-Modified'] ) && function_exists( 'header_remove' ) ){
//                     @header_remove( 'Last-Modified' );
//                 }

//                 exit();
//             }
//         }
//         return $headers;
//     }
// }

// if(wpjam_basic_get_setting('defer') && !is_admin() ){
//     add_filter( 'clean_url', 'wpjam_defer_script',11);
//     function wpjam_defer_script( $url ){
//         if(strpos($url, '.js') === false || (is_singular() && get_post_meta(get_the_ID(), 'custom_footer', true))) {
//             return $url;
//         }
//         return "$url' defer='defer";
//     };
// }

// // 加载 jQuery。
// add_action( 'wp_basic_scripts', 'wpjam_basic_scripts' );
// function wp_basic_scripts() {
//     wp_enqueue_script('jquery');
// }

//禁用 WP 初始化之前的主题检查
//remove_action( 'init','check_theme_switched',99);

//禁用 WP_CRON
// if(wpjam_basic_get_setting('disable_cron')){
//     defined('DISABLE_WP_CRON');
//     remove_action( 'init', 'wp_cron' );
// }

// Removes the white spaces from wp_title
// add_filter('wp_title', 'trim', 999);

//remove_action( 'admin_init', 'register_admin_color_schemes', 1);
//remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );

// add_action('plugins_loaded', 'wpjam_plugins_loaded');
// function wpjam_plugins_loaded(){
//     if(wpjam_basic_get_setting('remove_default_post_types')){
//         remove_action( 'init', 'create_initial_post_types', 0 );
//     }

//     if(wpjam_basic_get_setting('remove_default_taxonomies')){
//         remove_action( 'init', 'create_initial_taxonomies', 0 );
//     }
// }

// avatar 换成 CDN
// add_filter('get_avatar', 'wpjam_get_avatar', 10, 5);
// function wpjam_get_avatar($avatar, $id_or_email, $size, $default, $alt) {
//     if( $custom_avatar = wpjam_get_custom_avatar_src( $id_or_email, $size ) ){
//         $safe_alt = ( false === $alt )? '' : esc_attr( $alt );
//         $avatar = "<img alt='{$safe_alt}' src='{$custom_avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
//     }else{
//         $avatar = str_replace(array("www.gravatar.com", "0.gravatar.com", "1.gravatar.com", "2.gravatar.com"), "secure.gravatar.com", $avatar);
//     }
//     return $avatar;
// }

// add_filter('pre_get_avatar_data', 'wpjam_pre_get_avatar_data', 10, 2);
// function wpjam_pre_get_avatar_data($args, $id_or_email ){
//     if( $custom_avatar = wpjam_get_custom_avatar_src( $id_or_email, $args['size'] ) ){
//         $args['url'] = $custom_avatar;
//     }

//     return $args;
// }

// add_filter('search_rewrite_rules', 'wpjam_rewrite_rules');
// add_filter('post_rewrite_rules', 'wpjam_rewrite_rules');
// add_filter('date_rewrite_rules', 'wpjam_rewrite_rules');
// add_filter('root_rewrite_rules', 'wpjam_rewrite_rules');
//add_filter('comments_rewrite_rules', 'wpjam_rewrite_rules');
// add_filter('author_rewrite_rules', 'wpjam_rewrite_rules');
// add_filter('page_rewrite_rules', 'wpjam_rewrite_rules');
// function wpjam_rewrite_rules($rewrite_rules){
//     wpjam_print_r($rewrite_rules);
//     return array();
// }
