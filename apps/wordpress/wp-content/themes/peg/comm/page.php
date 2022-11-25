<?php 
/*
Template Name: 单栏页面
*/
header('Access-Control-Allow-Origin: *');
get_header(); ?>
<?php
if($_GET['from'] !== 'front'){
    header("Location:https://www.ouorz.com/page".$_SERVER['REQUEST_URI']);
}else{
    setPostViews($post->ID);
} ?>
<?php get_footer(); ?>