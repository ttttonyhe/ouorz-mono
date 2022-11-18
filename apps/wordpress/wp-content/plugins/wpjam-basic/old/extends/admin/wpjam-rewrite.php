<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 2017/2/28
 * Time: 16:34
 */

add_filter('wpjam_basic_sub_pages', 'wpjam_rewrite_admin_page');
function wpjam_rewrite_admin_page($wpjam_rewrite_sub_pages)
{
    $wpjam_rewrite_sub_pages['wpjam-rewrite'] = array(
        'menu_title' => 'Rewrite设置',
        'function' => 'option',
        'option_name' => 'wpjam-basic',
    );
    return $wpjam_rewrite_sub_pages;
}

add_filter('wpjam-rewrite_sections', 'wpjam_rewrite_sections');
function wpjam_rewrite_sections()
{
    $rewrite_fields = array(
        'rewrite' => array('title' => '移除 Rewrite 规则', 'type' => 'fieldset', 'fields' => array(
            // 'remove_trackback_rewrite'		=> array('title'=>'Trackback',	'type'=>'checkbox',	'description'=>'移除 Trackback Rewrite 规则'	),
            'remove_type/_rewrite' => array('title' => '', 'type' => 'checkbox', 'description' => '移除文章格式 Rewrite 规则'),
            'remove_comment_rewrite' => array('title' => '', 'type' => 'checkbox', 'description' => '移除留言 Rewrite 规则'),
            'remove_comment-page_rewrite' => array('title' => '', 'type' => 'checkbox', 'description' => '移除留言分页 Rewrite 规则'),
            'remove_author_rewrite' => array('title' => '', 'type' => 'checkbox', 'description' => '移除作者 Rewrite 规则'),
            'remove_feed=_rewrite' => array('title' => '', 'type' => 'checkbox', 'description' => '移除分类 Feed Rewrite 规则'),
            'remove_attachment_rewrite' => array('title' => '', 'type' => 'checkbox', 'description' => '移除附件页面 Rewrite 规则'),
        ))
    );

    return array(
        'wpjam-rewrite' => array('title' => 'Rewrite', 'fields' => $rewrite_fields, 'summary' => '<p>如果你的网站没有使用以下功能，可以移除相关功能的的 Rewrite 规则以提高网站效率！</p>'),
    );
}


