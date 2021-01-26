<?php
add_action('rest_api_init', 'wp_rest_insert_tag_links');

function wp_rest_insert_tag_links()
{

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

function get_total_post_count()
{
    $count_posts = wp_count_posts();
    $array = array('post_count' => $count_posts->publish);
    return $array;
}

function wp_rest_get_categories_links($post)
{
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
function wp_rest_get_plain_excerpt($post)
{
    $excerpts = array();
    $excerpts['nine'] = wp_trim_words(get_the_content($post['id']), 30);
    $excerpts['four'] = wp_trim_words(get_the_content($post['id']), 15);
    return $excerpts;
}

function wp_rest_get_normal_date($post)
{
    if (get_option('king_date_format')) {
        $format = get_option('king_date_format');
    } else {
        $format = 'd-m-y';
    }

    $date = get_the_date($format, $post['id']);
    return $date;
}

function get_post_title_for_ordering()
{
    return get_the_title($post['id']);
}

function get_page_content_for_api($post)
{
    $content = get_post($post['id'])->post_content;
    $content = apply_filters('the_content', $content);
    $content = str_replace(']]>', ']]&gt;', $content);
    $content = str_replace('blog.ouorz.com', 'www.ouorz.com', $content);
    return $content;
}

function get_post_content_for_api($post)
{
    $content = get_post($post['id'])->post_content;
    $content = apply_filters('the_content', $content);
    $content = str_replace(']]>', ']]&gt;', $content);
    $content = str_replace('blog.ouorz.com', 'www.ouorz.com', $content);
    $content = str_replace('<pre>', '<pre><code>', $content);
    $content = str_replace('</pre>', '</code></pre>', $content);
    return array('rendered' => $content);
}

function get_post_meta_for_api($post)
{
    $post_meta = array();
    $post_meta['views'] = get_post_meta($post['id'], 'post_views_count', true);
    $post_meta['link'] = get_post_meta($post['id'], 'link', true);
    $post_meta['status'] = get_post_meta($post['id'], 'status', true);
    $post_meta['img'] = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
    $post_meta['title'] = get_the_title($post['id']);
    $tagsss = get_the_tags($post['id']);
    $post_meta['tag_name'] = $tagsss[0]->name;
    $post_meta['reading']['word_count'] = mb_strlen(preg_replace('/\s/', '', html_entity_decode(strip_tags(get_the_content($post['id'])))), 'UTF-8');
    $post_meta['reading']['time_required'] = ceil($post_meta['reading']['word_count'] / 300);
    if (!empty(get_post_meta($post['id'], 'itemName', true))) {
        $post_meta['fineTool'] = array(
            'itemName' => get_post_meta($post['id'], 'itemName', true),
            'itemDes' => get_post_meta($post['id'], 'itemDes', true),
            'itemLinkName' => get_post_meta($post['id'], 'itemLinkName', true),
            'itemLink' => get_post_meta($post['id'], 'itemLink', true),
            'itemImgBorder' => get_post_meta($post['id'], 'itemImgBorder', true),
        );
    }
    if (!empty(get_post_meta($post['id'], 'linkImg', true))) {
        $post_meta['linkImg'] = get_post_meta($post['id'], 'linkImg', true);
    }
    if (!empty(get_post_meta($post['id'], 'markCount', true))) {
        $post_meta['markCount'] = (int) get_post_meta($post['id'], 'markCount', true);
    } else {
        $post_meta['markCount'] = 0;
    }
    if (!empty(get_post_meta($post['id'], 'podcast_name_chinese', true))) {
        $post_meta['podcast'] = array('chineseName' => get_post_meta($post['id'], 'podcast_name_chinese', true), 'englishName' => get_post_meta($post['id'], 'podcast_name_english', true), 'episode' => get_post_meta($post['id'], 'podcast_episode', true), 'audioUrl' => get_post_meta($post['id'], 'podcast_audio_url', true), 'episodeUrl' => get_post_meta($post['id'], 'podcast_episode_url', true));
    }
    return $post_meta;
}

function get_post_img_for_api($post)
{
    $post_img = array();
    $post_img['url'] = get_the_post_thumbnail_url($post['id']);
    return $post_img;
}

//获取文章标签
function get_post_tags_for_api($post)
{
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
function get_post_prenext_for_api($post)
{
    $array = array();
    $prev_post = get_previous_post(false, '');
    $next_post = get_next_post(false, '');
    $array['prev'][0] = $prev_post->ID;
    $array['prev'][1] = $prev_post->post_title;
    $array['prev'][2] = wp_get_post_categories($prev_post->ID)[0];
    $array['next'][0] = $next_post->ID;
    $array['next'][1] = $next_post->post_title;
    $array['next'][2] = wp_get_post_categories($next_post->ID)[0];
    return $array;
}

//增加 per_page 的100限制
add_filter('rest_post_collection_params', 'my_prefix_change_post_per_page', 10, 1);

function my_prefix_change_post_per_page($params)
{
    if (isset($params['per_page'])) {
        $count_posts = wp_count_posts();
        $params['per_page']['maximum'] = $count_posts->publish;
    }
    return $params;
}

//获取阅读时间
function get_postlength()
{
    global $post;
    return mb_strlen(strip_shortcodes(strip_tags(apply_filters('the_content', $post->post_content))));
}
function get_post_img_count()
{
    global $post;
    preg_match_all('/<img.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>/sim', $post->post_content, $strResult, PREG_PATTERN_ORDER);
    return count($strResult[1]);
}
function get_post_readtime()
{
    global $post;
    return ceil(get_postlength() / 800 + get_post_img_count() * 8 / 60);
}

function handleMark($params)
{
    $id = $params['id'];
    $markCountBefore = (int) get_post_meta($id, "markCount", true);
    if (!empty($id)) {
        if (!$markCountBefore) {
            $markCountBefore = 0;
        }
        $status = update_post_meta($id, "markCount", $markCountBefore + 1);
        return $status ? array('status' => true, 'markCountNow' => $markCountBefore + 1) : new WP_Error('Update has faild', 'Unknown error', array('status' => 404));
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

function handleVisit($params)
{
    $id = $params['id'];
    $visitCountBefore = (int) get_post_meta($id, "post_views_count", true);
    if (!empty($id)) {
        if (!$visitCountBefore) {
            $visitCountBefore = 0;
        }
        $status = update_post_meta($id, "post_views_count", $visitCountBefore + 1);
        return $status ? array('status' => true, 'visitCountNow' => $visitCountBefore + 1) : new WP_Error('Update has faild', 'Unknown error', array('status' => 404));
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

function getTotalPostsCount()
{
    $count_posts = wp_count_posts();
    return array('status' => true, 'count' => $count_posts->publish);
}

add_action('rest_api_init', function () {
    register_rest_route('tony/v1', '/count', array(
        'methods' => 'GET',
        'callback' => 'getTotalPostsCount',
    ));
}
);
