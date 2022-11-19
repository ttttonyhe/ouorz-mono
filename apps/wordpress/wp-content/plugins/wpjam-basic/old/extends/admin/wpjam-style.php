<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 2017/2/28
 * Time: 16:11
 */

add_filter('wpjam_basic_sub_pages', 'wpjam_style_admin_page');
function wpjam_style_admin_page($wpjam_basic_sub_pages)
{
    $wpjam_basic_sub_pages['wpjam-style'] = array(
        'menu_title' => '样式定制',
        'function' => 'option',
        'option_name' => 'wpjam-basic'
    );
    return $wpjam_basic_sub_pages;
}

