<?php
require get_template_directory() . '/inc/setting.php';
//setting
require get_template_directory() . '/inc/views.php';
//views
require get_template_directory() . '/inc/rewrite.php';
//页面伪静态

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
    $parent = get_cat_name($category[0]->category_parent);
    //echo $parent;//得到父级分类名称
    foreach (get_the_category($post->ID) as $category) $keywords .= $parent.','. $category->cat_name . ', '.get_the_title();
    //关键词为父级分类名称，分类名称，文章标题
    //下面判断条件为不同模板
    $keywords = substr_replace($keywords , '' , -2);
  }
  if ($keywords) {
    echo "<meta name=\"keywords\" content=\"$keywords\">\n";
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
add_action('media_buttons_context', 'mee_insert_post_custom_button');
function mee_insert_post_custom_button($_var) {
  $_var .= '<button type="button" id="insert-media-button" class="button insert-post-embed" data-editor="content"><span class="dashicons dashicons-pressthis"></span>插入文章</button><div class="smilies-wrap"></div><script>jQuery(document).ready(function(){jQuery(document).on("click", ".insert-post-embed",function(){var post_id=prompt("输入单个文章ID","");if (post_id!=null && post_id!=""){send_to_editor("[fa_insert_post ids="+ post_id +"]");}return false;});});</script>';
  return $_var;
}
//插入文章
function fa_insert_posts($atts, $content = null) {
  extract(shortcode_atts(array(
    'ids' => ''
  ),
    $atts));
  $post = get_post((int)$ids);
  $content .= '<div class="warp-post-embed"><a style="text-decoration: none;" href="https://www.ouorz.com/post/'.$ids.'" target="_blank" ><div class="embed-content"><h2>'.$post->post_title.'</h2><p><b>ID: '. $post->ID .'</b>&nbsp;&nbsp;发布于: '. $post->post_date .'</p></div></a></div>';
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
  _make_cat_compat($tag);
  return $tag->count;
}

//add post thumbnails
if (function_exists('add_theme_support')) {
  add_theme_support('post-thumbnails');
}

//根据上传时间重命名文件
add_filter('wp_handle_upload_prefilter', 'custom_upload_filter');
function custom_upload_filter($file) {
  $info = pathinfo($file['name']);
  $ext = $info['extension'];
  $filedate = date('YmdHis').rand(10,99);
  //为了避免时间重复，再加一段2位的随机数
  $file['name'] = $filedate.'.'.$ext;
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
    'content',
    array(
      'get_callback' => 'get_post_content_for_api',
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

  foreach ($categories as $term) {
    $term_link = get_term_link($term);
    if (is_wp_error($term_link)) {
      continue;
    }
    $post_categories[] = array('term_id' => $term->term_id, 'name' => $term->name, 'link' => $term_link);
  }
  return $post_categories;

}
function wp_rest_get_plain_excerpt($post) {
  $excerpts = array();
  $excerpts['nine'] = wp_trim_words(get_the_content($post['id']), 160);
  $excerpts['four'] = wp_trim_words(get_the_content($post['id']), 70);
  $excerpts['rss'] = get_the_excerpt($post['id']);
  return $excerpts;
}

function wp_rest_get_normal_date($post) {
  if (get_option('king_date_format'))
    $format = get_option('king_date_format');
  else $format = 'd-m-y';
  $date = get_the_date($format,$post['id']);
  return $date;
}

function get_post_title_for_ordering() {
  $title = get_the_title($post['id']);
  return $title;
}

function get_page_content_for_api($post) {
  $content = get_post($post['id'])->post_content;
  $content = apply_filters('the_content', $content);
  $content = str_replace(']]>', ']]&gt;', $content);
  $content = str_replace('blog.ouorz.com', 'www.ouorz.com', $content);
  return $content;
}

function get_post_content_for_api($post) {
  $content = get_post($post['id'])->post_content;
  $content = apply_filters('the_content', $content);
  $content = str_replace(']]>', ']]&gt;', $content);
  $content = str_replace('blog.ouorz.com', 'www.ouorz.com', $content);
  $content = str_replace('<pre>', '<pre><code>', $content);
  $content = str_replace('</pre>', '</code></pre>', $content);
  return array('rendered' => $content);
}

function get_post_meta_for_api($post) {
  $post_meta = array();
  $post_meta['views'] = get_post_meta($post['id'],'post_views_count',true);
  $post_meta['link'] = get_post_meta($post['id'],'link',true);
  $post_meta['status'] = get_post_meta($post['id'],'status',true);
  $post_meta['img'] = wp_get_attachment_image_src(get_post_thumbnail_id($post['id']), 'full');
  $post_meta['title'] = get_the_title($post['id']);
  $tagsss = get_the_tags($post['id']);
  $post_meta['tag_name'] = $tagsss[0]->name;
  $post_meta['reading']['word_count'] = mb_strlen(preg_replace('/\s/','',html_entity_decode(strip_tags(get_the_content($post['id'])))),'UTF-8');
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
  $i = 0;
  foreach ($tags as $tag) {
    $tag_term[$i]['id'] = $tag->term_id;
    $tag_term[$i]['url'] = get_tag_link($tag->term_id);
    $tag_term[$i]['name'] = $tag->name;
    $i++;
  }
  return $tag_term;
}

//获取上下篇文章
function get_post_prenext_for_api($post) {
  $array = array();
  $prev_post = get_previous_post(false,'');
  $next_post = get_next_post(false,'');
  $array['prev'][0] = $prev_post->ID;
  $array['prev'][1] = $prev_post->post_title;
  $array['prev'][2] = wp_get_post_categories($prev_post->ID)[0];
  $array['next'][0] = $next_post->ID;
  $array['next'][1] = $next_post->post_title;
  $array['next'][2] = wp_get_post_categories($next_post->ID)[0];
  return $array;
}

//获取博客数据
function rest_data_callback() {
  if ($_GET['check'] == 'check') {
    global $wpdb;
    $sql = "SELECT SUM(meta_value+0) FROM $wpdb->posts left join $wpdb->postmeta on ($wpdb->posts.ID = $wpdb->postmeta.post_id) WHERE meta_key = 'post_views_count'";
    $comment_views = intval($wpdb->get_var($sql));
    $array = array();
    $array['pv_count'] = $comment_views;
    return $array;
  }
}
function data_rest_register_route() {
  register_rest_route('data/', 'site', [
    'methods' => 'GET',
    'callback' => 'rest_data_callback'
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
    $params['per_page']['maximum'] = $count_posts->publish;
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
  $contents = $wpdb->get_results("SELECT post_content FROM$wpdb->posts WHERE post_status = 'publish'");
  foreach ($contents as $content) {
    $totalWords += mb_strlen(strip_tags(apply_filters('the_content', $content->post_content)));
  }

  //增加逗号
  $countArray = str_split((string)$totalWords,1);
  $countArrayCount = count($countArray);
  for ($k = 0;$k < intval($countArrayCount/2);$k++) {
    $temp = $countArray[$k];
    $countArray[$k] = $countArray[$countArrayCount - $k - 1];
    $countArray[$countArrayCount - $k - 1] = $temp;
  }
  $addCount = 0;
  for ($i = 0;$i < $countArrayCount;$i++) {
    if ($addCount == 3) {
      $addCount = 0;
      $wordsArray[] = ',';
    } else {
      $wordsArray[] = $countArray[$i];
      $addCount++;
    }
  }
  for ($k = 0;$k < intval($countArrayCount/2);$k++) {
    $temp = $wordsArray[$k];
    $wordsArray[$k] = $wordsArray[$countArrayCount - $k - 1];
    $wordsArray[$countArrayCount - $k - 1] = $temp;
  }
  $totalWords = implode($wordsArray,'');
  return $totalWords;
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
  $id = $params['id'];
  $markCountBefore = (int)get_post_meta($id, "markCount",true);
  if (!empty($id)) {
    if (!$markCountBefore) {
      $markCountBefore = 0;
    }
    $status = update_post_meta($id, "markCount", $markCountBefore + 1);
    return $status ? array('status' => true,'markCountNow' => $markCountBefore + 1) : new WP_Error('Update has faild', 'Unknown error', array('status' => 404));
  } else {
    return new WP_Error('Post does not exist', 'Invalid post ID', array('status' => 404));
  }
}

add_action('rest_api_init', function () {
  register_rest_route('tony/v1', '/mark/(?P<id>\d+)', array(
    'methods' => 'GET',
    'callback' => 'handleMark',
  ));
}
);

/*
  Visit handler
*/
function handleVisit($params) {
  $id = $params['id'];
  $visitCountBefore = (int)get_post_meta($id, "post_views_count",true);
  if (!empty($id)) {
    if (!$visitCountBefore) {
      $visitCountBefore = 0;
    }
    $status = update_post_meta($id, "post_views_count", $visitCountBefore + 1);
    return $status ? array('status' => true,'visitCountNow' => $visitCountBefore + 1) : new WP_Error('Update has faild', 'Unknown error', array('status' => 404));
  } else {
    return new WP_Error('Post does not exist', 'Invalid post ID', array('status' => 404));
  }
}

add_action('rest_api_init', function () {
  register_rest_route('tony/v1', '/visit/(?P<id>\d+)', array(
    'methods' => 'GET',
    'callback' => 'handleVisit',
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
    'callback' => 'getPostsID'
  ));
}
);

// TODO: remove this
add_action('rest_api_init', function () {
  register_rest_route('tony/v1', '/posts_ids', array(
    'methods' => 'GET',
    'callback' => 'getPostsID'
  ));
}
);


/*
 * Get all pages' ID for Next.js static generation 
 */

function getPagesID() {
	$pageIDs = array_map('intval', get_all_page_ids());
	return $pageIDs;
}

add_action('rest_api_init', function () {
  register_rest_route('tony/v1', '/page_ids', array(
    'methods' => 'GET',
    'callback' => 'getPagesID'
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
    'callback' => 'getCatesID'
  ));
}
);

/*
 * Trigger Next.js On-demand ISR
 */
function triggerRevalidation( $post_id ) {
	// only revalidate posts (not pages)
	if ( get_post_type($post_id) !== 'post' ) {
        return;
    }
	$url = 'https://www.ouorz.com/api/revalidate';
	$data = array('token' => 'GVwup6VQqM6vRejJ', 'path' => '/post/'.$post_id);
	wp_remote_post( $url, array(
    	'method' => 'POST',
    	'body' => $data
    ));
}

add_action( 'save_post', 'triggerRevalidation' );


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
    'callback' => 'getPostsForSearchIndexing'
  ));
});


/*
 * Get all posts for rss feed
 */
function getPostsForRSSFeed() {
	$posts = get_posts(array(
    	'posts_per_page'  => -1,
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
    'callback' => 'getPostsForRSSFeed'
  ));
});









