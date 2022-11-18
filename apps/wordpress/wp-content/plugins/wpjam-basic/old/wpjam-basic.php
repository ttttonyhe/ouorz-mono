<?php
define('WPJAM_BASIC_PLUGIN_URL', plugins_url('', __FILE__));
define('WPJAM_BASIC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPJAM_BASIC_PLUGIN_FILE', __FILE__);

if (!function_exists('wpjam_option_page')) {
    include(WPJAM_BASIC_PLUGIN_DIR . 'include/wpjam-api.php');    // 加载 WPJAM 基础类库
}

// if(!function_exists('get_term_meta')){
// 	include(WPJAM_BASIC_PLUGIN_DIR.'include/simple-term-meta.php');
// }

include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-setting.php');    // 默认选项
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-functions.php');    // 常用函数
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-route.php');        // Module Action 路由
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-shortcode.php');    // Shortcode
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-cache.php');        // 缓存
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-cdn.php');        // CDN
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-thumbnail.php');    // 缩略图
include(WPJAM_BASIC_PLUGIN_DIR . 'term-thumbnail.php');    // term 缩略图
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-posts.php');        // 日志列表
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-stats.php');        // 统计
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-mcrypt.php');        // 加密解密 class

if (is_admin()) {
    include(WPJAM_BASIC_PLUGIN_DIR . 'admin/admin.php');
}

wpjam_include_extends();    // 加载扩展