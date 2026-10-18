<?php 
/*
Template Name: 单栏页面
*/
if (($_GET['from'] ?? '') !== 'front') {
    wp_redirect('https://www.ouorz.com/page/'.get_queried_object_id(), 301);
    exit;
}
setPostViews(get_queried_object_id());
header('Access-Control-Allow-Origin: *');
get_header(); ?>
<?php get_footer(); ?>
