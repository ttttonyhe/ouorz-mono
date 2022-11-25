<?php
/*
Plugin Name: 样式定制
Plugin URI: http://wpjam.net/item/wpjam-basic/
Description: 前后台样式定制
Version: 1.0
*/

// 定制后台登录页面链接的连接
add_filter('login_headerurl', 'wpjam_login_headerurl');
function wpjam_login_headerurl(){
    return home_url();
}

// 定制后台登录页面链接的标题
add_filter('login_headertitle', 'wpjam_login_headertitle');
function wpjam_login_headertitle(){
    return get_bloginfo('name');
}

// 定制后台登录页面 HEAD
add_action('login_head', 'wpjam_login_head');
function wpjam_login_head() {
    echo wpjam_basic_get_setting('login_head');
    if($login_logo = wpjam_basic_get_setting('login_logo')){
        $login_logo_size = wpjam_basic_get_setting('login_logo_size');
        $width	= round($login_logo_size['width']/2);
        $height	= round($login_logo_size['height']/2);
        ?>
        <style type="text/css">
            .login h1 a { width:<?php echo $width;?>px; height:<?php echo $height;?>px; background-size: <?php echo $width.'px '.$height.'px';?>; background-image: url('<?php echo $login_logo;?>') !important;}
        </style>
        <?php
    }
}

add_action('login_footer','wpjam_login_footer');
function wpjam_login_footer() {
    echo wpjam_basic_get_setting('login_footer');
}

add_filter('login_redirect', 'wpjam_login_redirect', 10, 3);
function wpjam_login_redirect( $redirect_to, $request, $user ) {
    if($request){
        return $request;
    }

    if($login_redirect = wpjam_basic_get_setting('login_redirect')){
        return $login_redirect;
    }

    return $redirect_to;
}

add_action('wp_head','wpjam_custom_head',1);
function wpjam_custom_head(){
    // if($favicon = wpjam_basic_get_setting('favicon')){
    // 	echo '<link rel="shortcut icon" href="'.$favicon.'">'."\n";
    // }

    // if($apple_touch_icon = wpjam_basic_get_setting('apple_touch_icon')){
    // 	echo '<link rel="apple-touch-icon" href="'.$apple_touch_icon.'">';
    // }

    if( ( $head = wpjam_basic_get_setting('head') ) && !is_admin() ){
        echo $head;
    }
    ?>

    <?php if(is_single()){ ?>
        <meta property="og:type" content="article" />
        <meta property="og:url" content="<?php the_permalink(); ?>" />
        <meta property="og:title" content="<?php the_title(); ?>" />
        <meta property="og:description" content="<?php echo get_post_excerpt();?>" />
        <?php if($thumb = wpjam_get_post_thumbnail_uri()){ ?><meta property="og:image" content="<?php echo $thumb; ?>" /><?php } ?>
    <?php }
}

add_action('wp_footer','wpjam_footer',99);
function wpjam_footer(){
    if($footer = wpjam_basic_get_setting('footer')){
        echo $footer;
    }
    if(is_singular() && wpjam_basic_get_setting('custom_footer')){
        echo get_post_meta(get_the_ID(), 'custom_footer', true);
    }
}

// 微博赞以后再加。to do
/*
//add_filter('language_attributes','wpjam_wb_open_graph_language_attributes');
function wpjam_wb_open_graph_language_attributes($text){
	if(is_single()){
		return $text . ' xmlns:wb="http://open.weibo.com/wb"';
	}
}

//add_action('wp_footer','wpjam_wb_open_graph_footer');
add_action( 'wp_enqueue_scripts', 'wpjam_wb_open_graph_footer' );

function wpjam_wb_open_graph_footer(){
	if(is_single()){
	    wp_enqueue_script( 'wb-open-graph', 'http://tjs.sjs.sinajs.cn/open/api/js/wb.js', array('jquery'), '', true );
	}
}

function wb_like(){
?>
<wb:like></wb:like>
<?php
}

function wb_praise(){
	wb_like();
}*/
