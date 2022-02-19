<?php
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

//增加 per_page 的100限制
add_filter('rest_post_collection_params', 'my_prefix_change_post_per_page', 10, 1);

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