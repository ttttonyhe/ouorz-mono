<?php
/*
Plugin Name: 301 跳转
Plugin URI: http://wpjam.net/item/wpjam-basic/
Description: 网站上的 404 页面跳转到正确页面。
Version: 1.0
*/
function wpjam_get_301_redirects(){
    $wpjam_301_redirects = get_option('301-redirects');

    $new_301_redirects = array();

    if($wpjam_301_redirects){
        $request        = $wpjam_301_redirects['request'];
        $destination    = $wpjam_301_redirects['destination'];

        $total = count($request);

        $counter = 0;
        while ($counter < $total) {
            if(trim($request[$counter]) && trim($destination[$counter])){
                $new_301_redirects[trim($request[$counter])] = trim($destination[$counter]);
            }
            $counter++;
        }
    }

    return $new_301_redirects;
}

add_action('template_redirect','wpjam_301_redirects',99);
function wpjam_301_redirects(){
    if(is_404()){

        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')? 'https' : 'http';
        $request_url = $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        if(strpos( $request_url, 'feed/atom/')  !== false){
            wp_redirect(str_replace('feed/atom/', '', $request_url),301);
            exit;
        }

        if(strpos( $request_url, 'comment-page-')  !== false){
            wp_redirect(preg_replace('/comment-page-(.*)\//', '',  $request_url),301);
            exit;
        }

        if(strpos( $request_url, 'page/')  !== false){
            wp_redirect(preg_replace('/page\/(.*)\//', '',  $request_url),301);
            exit;
        }

        $wpjam_301_redirects = wpjam_get_301_redirects();

        if($wpjam_301_redirects){

            if(isset($wpjam_301_redirects[$request_url])){
                //echo $wpjam_301_redirects[$request_url];
                wp_redirect($wpjam_301_redirects[$request_url],301);
                exit;
            }

        }
    }
}