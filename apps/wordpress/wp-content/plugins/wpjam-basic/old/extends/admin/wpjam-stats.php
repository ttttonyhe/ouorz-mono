<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 2017/2/28
 * Time: 16:34
 */

add_filter('wpjam_basic_sub_pages', 'wpjam_stats_admin_page');
function wpjam_stats_admin_page($wpjam_stats_sub_pages)
{
    $wpjam_stats_sub_pages['wpjam-stats'] = array(
        'menu_title' => '统计设置',
        'function' => 'option',
        'option_name' => 'wpjam-basic',
    );
    return $wpjam_stats_sub_pages;
}

add_filter('wpjam-stats_sections', 'wpjam_stats_sections');
function wpjam_stats_sections()
{
    $stats_fields = array(
        'google_analytics' => array('title' => 'Google 分析', 'type' => 'fieldset', 'fields' => array(
            'google_analytics_id' => array('title' => '跟踪 ID：', 'type' => 'text'),
            'google_universal' => array('title' => '', 'type' => 'checkbox', 'description' => '使用 Universal Analytics 跟踪代码。'),
        )),
        'baidu_tongji' => array('title' => '百度统计', 'type' => 'fieldset', 'fields' => array(
            'baidu_tongji_id' => array('title' => '跟踪 ID：', 'type' => 'text')
        ))
    );

    return array(
        'wpjam-stats' => array('title' => '统计代码', 'fields' => $stats_fields)
    );
}


