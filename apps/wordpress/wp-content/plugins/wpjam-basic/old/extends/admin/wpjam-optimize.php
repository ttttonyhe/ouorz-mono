<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 2017/2/28
 * Time: 16:11
 */

add_filter('wpjam_basic_sub_pages', 'wpjam_optimize_admin_page');
function wpjam_optimize_admin_page($wpjam_basic_sub_pages)
{
    $wpjam_basic_sub_pages['wpjam-optimize'] = array(
        'menu_title' => '优化设置',
        'function' => 'option',
        'option_name' => 'wpjam-basic'
    );
    return $wpjam_basic_sub_pages;
}

add_filter('wpjam-optimize_sections', 'wpjam_optimize_sections');
function wpjam_optimize_sections()
{
    $basic_fields = array(
        'disabled' => array('title' => '功能屏蔽', 'type' => 'fieldset', 'fields' => array(
            'diable_revision' => array('title' => '', 'type' => 'checkbox', 'description' => '<a href="http://blog.wpjam.com/m/disable-post-revision/">屏蔽日志修订功能</a>，提高数据库效率。'),
            'disable_trackbacks' => array('title' => '', 'type' => 'checkbox', 'description' => '<a href="http://blog.wpjam.com/m/bye-bye-trackbacks/">彻底关闭 Trackbacks</a>，和垃圾留言说拜拜。'),
            'disable_xml_rpc' => array('title' => '', 'type' => 'checkbox', 'description' => '如果你无需通过 APP 客户端发布日志，建议<a href="http://blog.wpjam.com/m/disable-xml-rpc/">关闭 XML-RPC 功能</a>。'),
            'disable_rest_api' => array('title' => '', 'type' => 'checkbox', 'description' => '如果你的博客没有客户端，建议<a href="http://blog.wpjam.com/m/disable-wordpress-rest-api/">屏蔽 REST API 功能</a>。'),
            'disable_autoembed' => array('title' => '', 'type' => 'checkbox', 'description' => 'Auto Embeds 基本不支持国内网站，建议<a href="http://blog.wpjam.com/m/disable-auto-embeds-in-wordpress/">禁用 Auto Embeds 功能</a>，加快页面解析速度。'),
            'disable_post_embed' => array('title' => '', 'type' => 'checkbox', 'description' => '<a href="http://blog.wpjam.com/m/diable-wordpress-post-embed/">屏蔽文章 Embed 功能</a>。'),
            'disable_emoji' => array('title' => '', 'type' => 'checkbox', 'description' => 'WordPress Emoji 的渲染图片都在国外，并且经常无法打开，建议<a href="http://blog.wpjam.com/m/diable-emoji/">屏蔽 Emoji 功能</a>。'),
            'disable_auto_update' => array('title' => '', 'type' => 'checkbox', 'description' => 'WordPress 更新服务器在国外，经常无法打开，<a href="http://blog.wpjam.com/m/disable-wordpress-auto-update/">建议关闭 WordPress 后台和自动更新功能</a>！'),
        )),

        'remove' => array('title' => '清理优化', 'type' => 'fieldset', 'fields' => array(
            'remove_head_links' => array('title' => '', 'type' => 'checkbox', 'description' => '<a href="http://blog.wpjam.com/m/emove-unnecessary-code-from-wp_head/">移除 WordPress Header 中无关紧要的代码</a>，保持整洁，提高安全性。'),
            'remove_admin_bar' => array('title' => '', 'type' => 'checkbox', 'description' => '全局<a href="http://blog.wpjam.com/m/remove-wp-3-1-admin-bar/">移除工具栏（admin bar）</a>，所有人包括管理员都看不到，并且个人页面关于工具栏的选项也失效。'),
            'remove_dashboard_widgets' => array('title' => '', 'type' => 'checkbox', 'description' => '移除后台仪表盘所有的 Widgets。'),
            'no_admin' => array('title' => '', 'type' => 'checkbox', 'description' => '<a href="http://blog.wpjam.com/m/no-admin-try/">禁止使用 admin 用户名尝试登录 WordPress</a>，提高网站的安全性。'),
            'locale' => array('title' => '', 'type' => 'checkbox', 'description' => '<a href="http://blog.wpjam.com/m/setup-different-admin-and-frontend-language-on-wordpress/">前台不加载语言包</a>，可以提高0.1-0.5秒。'),
        )),

        'enhance' => array('title' => '功能增强', 'type' => 'fieldset', 'fields' => array(
            'shortcode_first' => array('title' => '', 'type' => 'checkbox', 'description' => '让 Shortcode 优先于 wpautop 执行，<a href="http://blog.wpjam.com/m/solution-to-wordpress-adding-br-and-p-tags-around-shortcodes/">解决 Shortcode 中自动添加的 br 或者 p 标签</a>。'),
            'order_by_registered' => array('title' => '', 'type' => 'checkbox', 'description' => '后台用户列表<a href="http://blog.wpjam.com/m/order-by-user-registered-time/">按照用户注册时间排序</a>。'),
            'strict_user' => array('title' => '', 'type' => 'checkbox', 'description' => '严格用户模式下，昵称和显示名称都是唯一的，并且用户名中不允许出现非法关键词（非法关键词是在 <strong>设置</strong> &amp; <strong>讨论</strong> 中 <code>评论审核</code> 和 <code>评论黑名单</code> 中定义的关键词）。'),
            'show_all_setting' => array('title' => '', 'type' => 'checkbox', 'description' => '在设置菜单下面显示<strong>所有设置</strong>子菜单。'),
            'image_default_link_type' => array('title' => '媒体文件默认链接到：', 'type' => 'select', 'options' => array('none' => '无', 'file' => '媒体文件', 'post' => '附件页面')),
        )),

        //'sql_debug'				=> array('title'=>'SQL 查询记录',			'type'=>'checkbox',	'description'=>'开启 SQL 查询文件记录'),
        //'disable_cron'			=> array('title'=>'禁用 WP Cron 功能',	'type'=>'checkbox',	'description'=>'如果你的 WordPress 没有需要定时执行的作业（比如定时发布），可以<a href="http://blog.wpjam.com/m/disable-wp-cron/">禁用 WP Cron</a>。'),
        // 'remove_default_post_types'	=> array('title'=>'移除默认日志类型',	'type'=>'checkbox',	'description'=>'移除默认日志类型：Post。'),
        // 'remove_default_taxonomies'	=> array('title'=>'移除默认分类和标签',	'type'=>'checkbox',	'description'=>'移除默认分类（Category）和标签（Tag）。'),
        //'304_headers'			=> array('title'=>'开启 304 Header',		'type'=>'checkbox',	'description'=>'<a href="http://blog.wpjam.com/m/wpjam-304-header/">给未登录用户开启 304 Not Modified Header</a>，再次访问同一页面不再请求服务器资源。'),
        //'defer'				=> array('title'=>'Defer 模式加载 JS',	'type'=>'checkbox',	'description'=>'<a href="http://blog.wpjam.com/m/javascript-defer/">使用 defer 延迟加载 JavaScript</a>，加快页面渲染。'),
        //'disable_update'		=> array('title'=>'移除后台更新提示',		'type'=>'checkbox',	'description'=>'<a href="http://blog.wpjam.com/m/disable-update-notification/">移除后台核心，插件和主题的更新提示</a>。'),
    );

    return array(
        'wpjam-optimize' => array('title' => '优化设置', 'fields' => $basic_fields, 'summary' => '<p>下面的选项，可以让你关闭 WordPress 中一些不常用的功能来提速，但是注意关闭一些功能会引起一些操作无法执行。</p>'),
    );
}