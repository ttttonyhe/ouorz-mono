<?php
//views
require get_template_directory() . '/inc/views.php';
//页面伪静态
require get_template_directory() . '/inc/rewrite.php';

//文章meta

//删除仪表盘区块
function disable_dashboard_widgets() {
  global $wp_meta_boxes;
  // wp..
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
  unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
  unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
  unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
  unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);
}
add_action('wp_dashboard_setup', 'disable_dashboard_widgets', 999);

//自动获取关键字 by suxing
function deel_keywords() {
  global $s, $post;
  //声明$post全局变量
  $keywords = '';
  if (is_single()) {
    //if ( get_the_tags( $post->ID ) ) {
    //foreach ( get_the_tags( $post->ID ) as $tag ) $keywords .= $tag->name . ', ';
    //}<br>    //如果文章有标签，关键词为标签
    $category = get_the_category();
    $parent = !empty($category) ? get_cat_name($category[0]->category_parent) : '';
    //echo $parent;//得到父级分类名称
    foreach (get_the_category($post->ID) as $category) $keywords .= $parent.','. $category->cat_name . ', '.get_the_title();
    //关键词为父级分类名称，分类名称，文章标题
    //下面判断条件为不同模板
    $keywords = substr_replace($keywords , '' , -2);
  }
  if ($keywords) {
    echo '<meta name="keywords" content="'.esc_attr($keywords)."\">\n";
  }
}
//关键字加入head头部代码
add_action('wp_head','deel_keywords');

//文章内时间以ago显示 by someone
function lb_time_since($older_date, $comment_date = false) {
  $chunks = array(
    array(24 * 60 * 60,' 天前'),
    array(60 * 60, ' 小时前'),
    array(60, ' 分钟前'),
    array(1,' 秒前')
  );
  $newer_date = time();
  $since = abs($newer_date - $older_date);
  if ($since < 30 * 24 * 60 * 60) {
    for ($i = 0, $j = count($chunks); $i < $j; $i ++) {
      $seconds = $chunks[$i][0];
      $name = $chunks[$i][1];
      if (($count = floor($since / $seconds)) != 0) {
        break;
      }
    }
    $output = $count . $name;
  } else {
    $output = $comment_date ? date('y-m-d', $older_date) : date('Y-m-d', $older_date);
  }

  return $output;
}

//插入文章内链 by suxing
//添加按钮
add_action('media_buttons', 'mee_insert_post_custom_button');
function mee_insert_post_custom_button($_var) {
  printf('<button type="button" id="insert-media-button" class="button insert-post-embed" data-editor="content"><span class="dashicons dashicons-pressthis"></span>Insert Post</button><div class="smilies-wrap"></div><script>jQuery(document).ready(function(){jQuery(document).on("click", ".insert-post-embed",function(){prompt("Copy the following shortcode","[fa_insert_post ids=123]")});});</script>');
}
//插入文章
function fa_insert_posts($atts, $content = null) {
  $atts = shortcode_atts(array('ids' => ''), $atts);
  $id = absint($atts['ids']);
  $post = $id ? get_post($id) : null;
  if (!$post || !is_post_publicly_viewable($post)) {
    return $content ?? '';
  }
  $content .= '<div class="warp-post-embed"><a style="text-decoration: none;" href="https://www.ouorz.com/post/'.$post->ID.'" target="_blank" ><div class="embed-content"><h2>'.esc_html(get_the_title($post)).'</h2><p><b>ID: '. $post->ID .'</b>&nbsp;&nbsp;发布于: '. esc_html($post->post_date) .'</p></div></a></div>';
  return $content;
}
add_shortcode('fa_insert_post', 'fa_insert_posts');


//去除抖动
function wps_login_error() {
  remove_action('login_head', 'wp_shake_js', 12);
}
add_action('login_head', 'wps_login_error');

//错误信息
function failed_login() {
  return '密码不正确';
}
add_filter('login_errors', 'failed_login');

//防止黑客
//去除版本信息
function remove_version() {
  return '';
}
add_filter('the_generator', 'remove_version');

//去除评论隐患
function lxtx_comment_body_class($content) {
  $pattern = "/(.*?)([^>]*)author-([^>]*)(.*?)/i";
  $replacement = '$1$4';
  $content = preg_replace($pattern, $replacement, $content);
  return $content;
}
add_filter('comment_class', 'lxtx_comment_body_class');
add_filter('body_class', 'lxtx_comment_body_class');


function get_tag_post_count_by_id($tag_id) {
  $tag = get_term_by('id', $tag_id, 'post_tag');
  return $tag && !is_wp_error($tag) ? (int)$tag->count : 0;
}

//add post thumbnails
if (function_exists('add_theme_support')) {
  add_theme_support('post-thumbnails');
}

//根据上传时间重命名文件
add_filter('wp_handle_upload_prefilter', 'custom_upload_filter');
function custom_upload_filter($file) {
  $info = pathinfo($file['name']);
  $ext = $info['extension'] ?? '';
  $filedate = date('YmdHis').rand(10,99);
  //为了避免时间重复，再加一段2位的随机数
  $file['name'] = $filedate.($ext === '' ? '' : '.'.$ext);
  return $file;
}


add_filter('wp_image_editors', 'change_graphic_lib');
function change_graphic_lib($array) {
  return array('WP_Image_Editor_GD', 'WP_Image_Editor_Imagick');
}




/* rest-api */
add_action('rest_api_init', 'wp_rest_insert_tag_links');

function wp_rest_insert_tag_links() {

  register_rest_field('post',
    'post_categories',
    array(
      'get_callback' => 'wp_rest_get_categories_links',
      'update_callback' => null,
      'schema' => null,
    )
  );
  register_rest_field('post',
    'post_excerpt',
    array(
      'get_callback' => 'wp_rest_get_plain_excerpt',
      'update_callback' => null,
      'schema' => null,
    )
  );
  register_rest_field('post',
    'post_date',
    array(
      'get_callback' => 'wp_rest_get_normal_date',
      'update_callback' => null,
      'schema' => null,
    )
  );
  register_rest_field('page',
    'post_date',
    array(
      'get_callback' => 'wp_rest_get_normal_date',
      'update_callback' => null,
      'schema' => null,
    )
  );
  register_rest_field('post',
    'post_metas',
    array(
      'get_callback' => 'get_post_meta_for_api',
      'update_callback' => null,
      'schema' => null,
    )
  );
  register_rest_field('page',
    'post_metas',
    array(
      'get_callback' => 'get_post_meta_for_api',
      'update_callback' => null,
      'schema' => null,
    )
  );
  register_rest_field('post',
    'post_img',
    array(
      'get_callback' => 'get_post_img_for_api',
      'update_callback' => null,
      'schema' => null,
    )
  );
  register_rest_field('post',
    'post_tags',
    array(
      'get_callback' => 'get_post_tags_for_api',
      'update_callback' => null,
      'schema' => null,
    )
  );
  register_rest_field('page',
    'page_content',
    array(
      'get_callback' => 'get_page_content_for_api',
      'update_callback' => null,
      'schema' => null,
    )
  );
  register_rest_field('post',
    'post_prenext',
    array(
      'get_callback' => 'get_post_prenext_for_api',
      'update_callback' => null,
      'schema' => null,
    )
  );
  register_rest_field('post',
    'post_total_count',
    array(
      'get_callback' => 'get_total_post_count',
      'update_callback' => null,
      'schema' => null,
    )
  );
  register_rest_field('post',
    'post_title',
    array(
      'get_callback' => 'get_post_title_for_ordering',
      'update_callback' => null,
      'schema' => null,
    )
  );
}

function get_total_post_count() {
  $count_posts = wp_count_posts();
  $array = array('post_count' => $count_posts->publish);
  return $array;
}

function wp_rest_get_categories_links($post) {
  $post_categories = array();
  $categories = wp_get_post_terms($post['id'], 'category', array('fields' => 'all'));
  if (is_wp_error($categories)) {
    return $post_categories;
  }

  foreach ($categories as $term) {
    $term_link = get_term_link($term);
    if (is_wp_error($term_link)) {
      continue;
    }
    $post_categories[] = array('term_id' => $term->term_id, 'name' => $term->name, 'link' => $term_link);
  }
  return $post_categories;

}
function peg_rest_can_read_content(WP_Post $post, WP_REST_Request $request): bool {
  $controller = new WP_REST_Posts_Controller($post->post_type);
  return !post_password_required($post) || $controller->can_access_password_content($post, $request);
}

function wp_rest_get_plain_excerpt($post, $field, $request) {
  $item = get_post($post['id']);
  if (!$item || !peg_rest_can_read_content($item, $request)) {
    return array('nine' => '', 'four' => '', 'rss' => '');
  }

  $password_filter = static function ($required, $post) use ($item) {
    return $post->ID === $item->ID ? false : $required;
  };
  add_filter('post_password_required', $password_filter, 10, 2);
  try {
    $content = get_the_content(null, false, $item);
    return array(
      'nine' => wp_trim_words($content, 160),
      'four' => wp_trim_words($content, 70),
      'rss' => get_the_excerpt($item),
    );
  } finally {
    remove_filter('post_password_required', $password_filter, 10);
  }
}

function wp_rest_get_normal_date($post) {
  if (get_option('king_date_format'))
    $format = get_option('king_date_format');
  else $format = 'd-m-y';
  $date = get_the_date($format,$post['id']);
  return $date;
}

function get_post_title_for_ordering($post) {
  $title = get_the_title($post['id']);
  return $title;
}

function get_page_content_for_api($post, $field, $request) {
  $item = get_post($post['id']);
  if (!$item || !peg_rest_can_read_content($item, $request)) {
    return '';
  }

  $content = $post['content']['rendered'] ?? apply_filters('the_content', $item->post_content);
  return peg_format_rest_content($content);
}

function peg_format_rest_content(string $content): string {
  return str_replace(array(']]>', 'blog.ouorz.com'), array(']]&gt;', 'www.ouorz.com'), $content);
}

function peg_prepare_rest_post($response) {
  $data = $response->get_data();
  if (isset($data['content']['rendered'])) {
    $data['content']['rendered'] = preg_replace_callback(
      '/<pre>(.*?)<\/pre>/s',
      static function ($match) {
        return preg_match('/^\s*<code(?:\s|>)/', $match[1]) ? $match[0] : '<pre><code>'.$match[1].'</code></pre>';
      },
      peg_format_rest_content($data['content']['rendered'])
    );
    $response->set_data($data);
  }
  return $response;
}
add_filter('rest_prepare_post', 'peg_prepare_rest_post');

function get_post_meta_for_api($post, $field, $request) {
  $post_meta = array();
  $post_meta['views'] = get_post_meta($post['id'],'post_views_count',true);
  $post_meta['link'] = get_post_meta($post['id'],'link',true);
  $post_meta['status'] = get_post_meta($post['id'],'status',true);
  $post_meta['img'] = wp_get_attachment_image_src(get_post_thumbnail_id($post['id']), 'full');
  $post_meta['title'] = get_the_title($post['id']);
  $tagsss = get_the_tags($post['id']);
  $post_meta['tag_name'] = !is_wp_error($tagsss) && !empty($tagsss) ? $tagsss[0]->name : '';
  $item = get_post($post['id']);
  $content = $item && peg_rest_can_read_content($item, $request) ? $item->post_content : '';
  $post_meta['reading']['word_count'] = mb_strlen(preg_replace('/\s/u','',html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8')),'UTF-8');
  $post_meta['reading']['time_required'] = ceil($post_meta['reading']['word_count']/300);
  if (!empty(get_post_meta($post['id'],'itemName',true))) {
    $post_meta['fineTool'] = array(
      'itemName' => get_post_meta($post['id'],'itemName',true),
      'itemDes' => get_post_meta($post['id'],'itemDes',true),
      'itemLinkName' => get_post_meta($post['id'],'itemLinkName',true),
      'itemLink' => get_post_meta($post['id'],'itemLink',true),
      'itemImgBorder' => get_post_meta($post['id'],'itemImgBorder',true)
    );
  }
  if (!empty(get_post_meta($post['id'],'linkImg',true))) {
    $post_meta['linkImg'] = get_post_meta($post['id'],'linkImg',true);
  }
  if (!empty(get_post_meta($post['id'],'markCount',true))) {
    $post_meta['markCount'] = (int)get_post_meta($post['id'],'markCount',true);
  } else {
    $post_meta['markCount'] = 0;
  }
  if (!empty(get_post_meta($post['id'],'podcast_name_chinese',true))) {
    $post_meta['podcast'] = array('chineseName' => get_post_meta($post['id'],'podcast_name_chinese',true),
      'englishName' => get_post_meta($post['id'],'podcast_name_english',true),
      'episode' => get_post_meta($post['id'],'podcast_episode',true),
      'audioUrl' => get_post_meta($post['id'],'podcast_audio_url',true),
      'episodeUrl' => get_post_meta($post['id'],'podcast_episode_url',true),
      'duration' => get_post_meta($post['id'],'podcast_duration',true),
      'fileSize' => get_post_meta($post['id'],'podcast_file_size',true)
    );
  }
  return $post_meta;
}

function get_post_img_for_api($post) {
  $post_img = array();
  $post_img['url'] = get_the_post_thumbnail_url($post['id']);
  return $post_img;
}
/* rest-api */

//获取全部分类
function admin_show_category() {
  global $wpdb;
  $request = "SELECT $wpdb->terms.term_id, name FROM $wpdb->terms ";
  $request .= " LEFT JOIN $wpdb->term_taxonomy ON $wpdb->term_taxonomy.term_id = $wpdb->terms.term_id ";
  $request .= " WHERE $wpdb->term_taxonomy.taxonomy = 'category' ";
  $request .= " ORDER BY term_id asc";
  $categorys = $wpdb->get_results($request);
  echo "<h3>全部分类及ID</h4><ul style='height:50px'>";
  foreach ($categorys as $category) {
    echo  '<li style="margin-right: 10px;float:left;">'.$category->name."（<code>".$category->term_id.'</code>）</li>';
  }
  echo "</ul>";
}

//设置站点title
function site_page_title() {
  if (is_home()) {
    bloginfo('name');
    echo " - ";
    bloginfo('description');
  } elseif (is_category()) {
    single_cat_title();
    echo " - ";
    bloginfo('name');
  } elseif (is_single() || is_page()) {
    single_post_title();
  } elseif (is_search()) {
    echo "搜索结果";
    echo " - ";
    bloginfo('name');
  } elseif (is_404()) {
    echo '没有找到页面';
  } else {
    wp_title('',true);
  }
}
/*
function get_site_info_api($get){
  if($get['api'] == 'get_api'){
  $status = array();
  //手机判断
  if(wp_is_mobile()){
    $status['wp_is_mobile'] = 1;
  }else{
    $status['wp_is_mobile'] = 0;
  }
  //文章页判断
  if(is_single()){
    $status['is_single'] = 1;
  }else{
    $status['is_single'] = 0;
  }
  //页面判断
  if(is_page()){
    $status['is_page'] = 1;
  }else{
    $status['is_page'] = 0;
  }
  //站点信息
  $status['site_url'] = site_url();
  echo json_encode($status);
  die();
  }
}

add_action('init', 'get_site_info_api');
*/

//获取文章标签
function get_post_tags_for_api($post) {
  $tag_term = array();
  $tags = wp_get_post_tags($post['id']);
  if (is_wp_error($tags)) {
    return $tag_term;
  }
  $i = 0;
  foreach ($tags as $tag) {
    $tag_term[$i]['id'] = $tag->term_id;
    $link = get_tag_link($tag->term_id);
    $tag_term[$i]['url'] = is_wp_error($link) ? '' : $link;
    $tag_term[$i]['name'] = $tag->name;
    $i++;
  }
  return $tag_term;
}

//获取上下篇文章
function get_post_prenext_for_api($post) {
  $current_post = $GLOBALS['post'] ?? null;
  $GLOBALS['post'] = get_post($post['id']);
  $array = array('prev' => array(null, null, null), 'next' => array(null, null, null));
  try {
    foreach (array('prev' => get_previous_post(false, ''), 'next' => get_next_post(false, '')) as $direction => $adjacent) {
      if (!$adjacent instanceof WP_Post) {
        continue;
      }
      $categories = wp_get_post_categories($adjacent->ID);
      $array[$direction] = array($adjacent->ID, $adjacent->post_title, is_wp_error($categories) ? null : ($categories[0] ?? null));
    }
  } finally {
    $GLOBALS['post'] = $current_post;
  }
  return $array;
}

//获取博客数据
function rest_data_callback($request) {
  if ($request['check'] === 'check') {
    global $wpdb;
    $sql = "SELECT SUM(meta_value+0) FROM $wpdb->posts left join $wpdb->postmeta on ($wpdb->posts.ID = $wpdb->postmeta.post_id) WHERE meta_key = 'post_views_count'";
    $comment_views = intval($wpdb->get_var($sql));
    $array = array();
    $array['pv_count'] = $comment_views;
    return $array;
  }
}
function data_rest_register_route() {
  register_rest_route('data', 'site', [
    'methods' => 'GET',
    'callback' => 'rest_data_callback',
    'permission_callback' => '__return_true',
  ]);
}
add_action('rest_api_init', 'data_rest_register_route');

//REST API数据提交验证
add_action('wp_enqueue_scripts', 'rest_theme_scripts');
function rest_theme_scripts() {
  wp_localize_script('jquery', 'wp', [
    'nonce' => wp_create_nonce('wp_rest'),
  ]);
}

//头部title 展示
function header_title() {
  if (is_home()) {
    bloginfo('name');
    echo " - ";
    bloginfo('description');
  } elseif (is_category()) {
    single_cat_title();
    echo " - ";
    bloginfo('name');
  } elseif (is_single() || is_page()) {
    single_post_title();
  } elseif (is_search()) {
    echo "搜索结果";
    echo " - ";
    bloginfo('name');
  } elseif (is_404()) {
    echo '没有找到页面';
  } else {
    wp_title('', true);
  }
}

//增加 per_page 的100限制
add_filter('rest_post_collection_params', 'my_prefix_change_post_per_page', 10, 1);

function my_prefix_change_post_per_page($params) {
  if (isset($params['per_page'])) {
    $count_posts = wp_count_posts();
    $params['per_page']['maximum'] = max(100, (int)$count_posts->publish);
  }
  return $params;
}

//获取阅读时间
function get_postlength() {
  global $post;
  return mb_strlen(strip_shortcodes(strip_tags(apply_filters('the_content', $post->post_content))));
}
function get_post_img_count() {
  global $post;
  preg_match_all('/<img.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>/sim', $post->post_content, $strResult, PREG_PATTERN_ORDER);
  return count($strResult[1]);
}
function get_post_readtime() {
  global $post;
  return ceil(get_postlength() / 800 + get_post_img_count() * 8 / 60);
}

// (一年内)全部文章字数
function get_words_count() {
  global $wpdb;
  $totalWords = 0;
  //$contents = $wpdb->get_results("SELECT post_content FROM $wpdb->posts WHERE post_status = 'publish' AND TO_DAYS(NOW()) - TO_DAYS(post_date) <= 365 ORDER BY post_date DESC");
  $contents = $wpdb->get_results("SELECT post_content FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post'");
  foreach ($contents as $content) {
    $totalWords += mb_strlen(strip_tags(apply_filters('the_content', $content->post_content)));
  }

  return number_format($totalWords);
}

//禁止压缩图片
add_filter('jpg_quality', 'high_jpg_quality');
function high_jpg_quality() {
  return 100;
}

//Feed 输出文章特色图像（缩略图）
function rss_post_thumbnail($content) {
  global $post;
  //查询全局文章
  if (has_post_thumbnail($post->ID)) {
    //如果有特色图像
    $output = get_the_post_thumbnail($post->ID) ;
    //获取缩略图
    $content = $output . $content ;
  }
  return $content;
}
add_filter('the_excerpt_rss', 'rss_post_thumbnail');
add_filter('the_content_feed', 'rss_post_thumbnail');

//在Feed中排除分类
function exclude_cat_feed($query) {
  if (is_feed()) {
    $query->set('cat','-1,-2,-5');
    //排除ID为 1 的分类
    return $query;
  }
}
add_filter('pre_get_posts', 'exclude_cat_feed');

//后台加上分割线
function Bing_admin_separators() {
  echo '<style type="text/css">#adminmenu li.wp-menu-separator {margin: 0;}.admin-color-fresh #adminmenu li.wp-menu-separator {background: #444;}.admin-color-midnight #adminmenu li.wp-menu-separator {background: #4a5258;}.admin-color-light #adminmenu li.wp-menu-separator {background: #c2c2c2;}.admin-color-blue #adminmenu li.wp-menu-separator {background: #3c85a0;}.admin-color-coffee #adminmenu li.wp-menu-separator {background: #83766d;}.admin-color-ectoplasm #adminmenu li.wp-menu-separator {background: #715d8d;}.admin-color-ocean #adminmenu li.wp-menu-separator {background: #8ca8af;}.admin-color-sunrise #adminmenu li.wp-menu-separator {background: #a43d39;}</style>';
}
add_action('admin_head', 'Bing_admin_separators');

/*在文章列表、页面列表中显示文章ID*/
function suxingme_post_id_column($post_columns) {
  $beginning = array_slice($post_columns, 0 ,1);
  $beginning['postid'] = __('ID', 'suxingme');
  $ending = array_slice($post_columns, 1);
  $post_columns = array_merge($beginning, $ending);
  return $post_columns;
}
add_filter('manage_posts_columns', 'suxingme_post_id_column');
//添加文章列表页ID标题
add_filter('manage_pages_columns', 'suxingme_post_id_column');
//添加页面列表页....

function suxingme_posts_id_column($col, $val) {
  if ($col == 'postid') echo $val;
}
add_action('manage_posts_custom_column', 'suxingme_posts_id_column', 10, 2);
//添加文章列表页ID列数值
add_action('manage_pages_custom_column', 'suxingme_posts_id_column', 10, 2);
//添加页面列表页ID...

function suxingme_posts_id_column_css() {
  echo '<style type="text/css">#postid { width: 50px; }</style>';
  //ID列宽度
}
add_action('admin_head-edit.php', 'suxingme_posts_id_column_css');

/*
  Marks handler
*/
function handleMark($params) {
  $count = peg_increment_post_counter(absint($params['id']), 'markCount');
  return is_wp_error($count) ? $count : array('status' => true, 'markCountNow' => $count);
}

add_action('rest_api_init', function () {
  register_rest_route('tony/v1', '/mark/(?P<id>\d+)', array(
    'methods' => 'POST',
    'callback' => 'handleMark',
    'permission_callback' => 'peg_counter_permissions',
  ));
}
);

/*
  Visit handler
*/
function handleVisit($params) {
  $count = peg_increment_post_counter(absint($params['id']), 'post_views_count');
  return is_wp_error($count) ? $count : array('status' => true, 'visitCountNow' => $count);
}

function peg_counter_permissions($request) {
  $post = get_post(absint($request['id']));
  if (!$post || $post->post_type !== 'post' || $post->post_status !== 'publish' || $post->post_password !== '') {
    return new WP_Error('rest_post_invalid_id', 'Invalid post ID', array('status' => 404));
  }
  return true;
}

function peg_increment_post_counter(int $post_id, string $key) {
  global $wpdb;
  $error = new WP_Error('rest_counter_update_failed', 'Could not update the counter', array('status' => 500));
  $visitor = $_SERVER['REMOTE_ADDR'] ?? '';
  if (getenv('FLY_APP_NAME') === 'ouorz-wordpress' && filter_var($_SERVER['HTTP_FLY_CLIENT_IP'] ?? '', FILTER_VALIDATE_IP)) {
    $visitor = $_SERVER['HTTP_FLY_CLIENT_IP'];
  }
  if (!filter_var($visitor, FILTER_VALIDATE_IP)) {
    return new WP_Error('rest_counter_invalid_visitor', 'A valid visitor address is required', array('status' => 400));
  }
  $dedup_key = 'peg_counter_'.$post_id.'_'.$key.'_'.hash_hmac('sha256', inet_pton($visitor), wp_salt('nonce'));
  $dedup_attempted = false;
  $committed = false;
  if ($wpdb->query('START TRANSACTION') === false) {
    return $error;
  }

  try {
    // Lock the post because a counter may not have a metadata row yet.
    $id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE ID = %d AND post_type IN ('post', 'page') AND post_status = 'publish' AND post_password = '' FOR UPDATE", $post_id));
    if (!$id) {
      return $wpdb->last_error ? $error : new WP_Error('rest_post_invalid_id', 'Invalid post ID', array('status' => 404));
    }

    $row = $wpdb->get_row($wpdb->prepare("SELECT meta_id, meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s ORDER BY meta_id LIMIT 1 FOR UPDATE", $post_id, $key));
    if ($wpdb->last_error) {
      return $error;
    }
    $count = max(0, (int)($row->meta_value ?? 0));
    if (get_transient($dedup_key)) {
      return $count;
    }
    $count++;
    $result = $row === null
      ? $wpdb->insert($wpdb->postmeta, array('post_id' => $post_id, 'meta_key' => $key, 'meta_value' => $count), array('%d', '%s', '%d'))
      : $wpdb->update($wpdb->postmeta, array('meta_value' => $count), array('post_id' => $post_id, 'meta_key' => $key), array('%d'), array('%d', '%s'));

    if (!$result) {
      return $error;
    }
    $dedup_attempted = true;
    if (!set_transient($dedup_key, 1, $key === 'markCount' ? DAY_IN_SECONDS : 5 * MINUTE_IN_SECONDS) || $wpdb->query('COMMIT') === false) {
      return $error;
    }
    $committed = true;
    wp_cache_delete($post_id, 'post_meta');
    return $count;
  } finally {
    if (!$committed) {
      $wpdb->query('ROLLBACK');
      if ($dedup_attempted) {
        wp_cache_delete($dedup_key, 'transient');
        wp_cache_delete('_transient_'.$dedup_key, 'options');
        wp_cache_delete('_transient_timeout_'.$dedup_key, 'options');
      }
    }
  }
}

add_action('rest_api_init', function () {
  register_rest_route('tony/v1', '/visit/(?P<id>\d+)', array(
    'methods' => 'POST',
    'callback' => 'handleVisit',
    'permission_callback' => 'peg_counter_permissions',
  ));
}
);

/*
  Get posts total count
*/
function getTotalPostsCount() {
  $posts_count = wp_count_posts();
  $posts = get_posts(array('numberposts' => (int)$posts_count->publish));
  $views_count = 0;
  foreach ($posts as $post) {
    $views_count += (int)get_post_meta($post->ID, "post_views_count", true);
  }
  return array(
    'status' => true,
    'count' => (int)$posts_count->publish,
    'views' => (int)$views_count,
  );
}

add_action('rest_api_init', function () {
  register_rest_route('tony/v1', '/poststats', array(
    'methods' => 'GET',
    'callback' => 'getTotalPostsCount',
    'permission_callback' => '__return_true',
  ));
}
);

/*
  Get all posts count for RSS feed
*/
function getPostsCount() {
  $posts_count = wp_count_posts();
  return array('count' => (int)$posts_count->publish);
}

add_action('rest_api_init', function () {
  register_rest_route('tony/v1', '/posts_count', array(
    'methods' => 'GET',
    'callback' => 'getPostsCount',
    'permission_callback' => '__return_true',
  ));
}
);


/*
 * Get all posts' ID for Next.js static generation 
 */

function getPostsID() {
	$posts = get_posts(array(
    	'fields'          => 'ids',
    	'posts_per_page'  => -1,
		'category__not_in' => [5,2,74,120]
	));
	return $posts;
}

add_action('rest_api_init', function () {
  register_rest_route('tony/v1', '/post_ids', array(
    'methods' => 'GET',
    'callback' => 'getPostsID',
    'permission_callback' => '__return_true',
  ));
}
);

/*
 * Get all pages' ID for Next.js static generation 
 */

function getPagesID() {
	$pageIDs = get_posts(array('post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'has_password' => false));
	return $pageIDs;
}

add_action('rest_api_init', function () {
  register_rest_route('tony/v1', '/page_ids', array(
    'methods' => 'GET',
    'callback' => 'getPagesID',
    'permission_callback' => '__return_true',
  ));
}
);


/*
 * Get all categories' ID for Next.js static generation 
 */

function getCatesID() {
	$cateIDs = get_terms(
    	array('category'),
    	array('fields' => 'ids')
	);
	return $cateIDs;
}

add_action('rest_api_init', function () {
  register_rest_route('tony/v1', '/cate_ids', array(
    'methods' => 'GET',
    'callback' => 'getCatesID',
    'permission_callback' => '__return_true',
  ));
}
);

/*
 * Get all posts for search indexing
 */

function getPostsForSearchIndexing() {
	$posts = get_posts(array(
    	'posts_per_page'  => -1,
		'category__not_in' => [5,2,74,120,58]
	));
	$ids = array_column($posts, 'ID');
	$titles = array_column($posts, 'post_title');
	return array('ids' => $ids, 'titles' => $titles);
}

add_action('rest_api_init', function () {
  register_rest_route('tony/v1', '/searchIndexes', array(
    'methods' => 'GET',
    'callback' => 'getPostsForSearchIndexing',
    'permission_callback' => '__return_true',
  ));
});


/*
 * Get all posts for rss feed
 */
function getPostsForRSSFeed() {
	$posts = get_posts(array(
    	'posts_per_page'  => -1,
		'has_password' => false,
		'category__not_in' => [5,2,74,120,58]
	));
	$ids = array_column($posts, 'ID');
	$titles = array_column($posts, 'post_title');
	$contents = array_column($posts, 'post_content_filtered');
	$dates = array_column($posts, 'post_date_gmt');
	return array('ids' => $ids, 'titles' => $titles, 'contents' => $contents, 'dates' => $dates);
}

add_action('rest_api_init', function () {
  register_rest_route('tony/v1', '/rssData', array(
    'methods' => 'GET',
    'callback' => 'getPostsForRSSFeed',
    'permission_callback' => '__return_true',
  ));
});









