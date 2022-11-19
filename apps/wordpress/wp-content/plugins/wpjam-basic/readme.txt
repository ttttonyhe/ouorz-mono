=== WPJAM Basic ===
Contributors: denishua
Donate link: https://wpjam.com/
Tags: WPJAM, Memcached, 性能优化
Requires at least: 5.8
Requires PHP: 7.2
Tested up to: 6.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

<strong>最新 3.0 版本，要求 Linux 服务器，和 PHP 7.2 版本，以及服务器支持 Memcached。</strong> WPJAM Basic 是我爱水煮鱼博客多年来使用 WordPress 来整理的优化插件，WPJAM Basic 除了能够优化你的 WordPress，也是 WordPress 果酱团队进行 WordPress 二次开发的基础。

== Description ==

WPJAM Basic 是<a href="http://blog.wpjam.com/">我爱水煮鱼博客</a>多年来使用 WordPress 来整理的优化插件，WPJAM Basic 除了能够优化你的 WordPress ，也是 WordPress 果酱团队进行 WordPress 二次开发的基础。

WPJAM Basic 主要功能，就是去掉 WordPress 当中一些不常用的功能，比如文章修订等，还有就是提供一些经常使用的函数，比如获取文章中第一张图，获取文章摘要等。

如果你的主机安装了 Memcacached 等这类内存缓存组件和对应的 WordPress 插件，这个插件也针对提供一些针对一些常用的插件和函数提供了对象缓存的优化版本。

详细介绍和安装说明： <a href="http://blog.wpjam.com/project/wpjam-basic/">http://blog.wpjam.com/project/wpjam-basic/</a>。

除此之外，WPJAM Basic 还支持多达十七个扩展，你可以根据自己的需求选择开启：

| 扩展 | 简介 | 
| ------ | ------ |
| 文章数量 | 设置不同页面不同的文章列表数量，不同的分类不同文章列表数量。 |
| 文章目录 | 自动根据文章内容里的子标题提取出文章目录，并显示在内容前。 |
| 相关文章 | 根据文章的标签和分类，自动生成相关文章，并在文章末尾显示。 |
| 用户角色 | 用户角色管理，以及用户额外权限设置。 |
| 统计代码 | 自动添加百度统计和 Google 分析代码。 |
| 百度站长 | 支持主动，被动，自动以及批量方式提交链接到百度站长。 |
| 移动主题 | 给移动设备设置单独的主题，以及在PC环境下进行移动主题的配置。 |
| 301 跳转 | 支持网站上的 404 页面跳转到正确页面。 |
| 简单 SEO | 设置简单快捷，功能强大的 WordPress SEO 功能。 |
| SMTP 发信 | 简单配置就能让 WordPress 使用 SMTP 发送邮件。 |
| 常用短代码 | 添加 list table 等常用短代码，并在后台罗列所有系统所有短代码。|
| 文章浏览统计 | 统计文章阅读数，激活该扩展，请不要再激活 WP-Postviews 插件。|
| 文章快速复制 | 在后台文章列表，添加一个快速复制按钮，点击可快复制一篇草稿用于新建。 |
| 摘要快速编辑 | 在后台文章列表，点击快速编辑之后也支持编辑文章摘要。 |
| Rewrite 优化 | 清理无用的 Rewrite 代码，和添加自定义 rewrite 代码。 |
| 文章类型转换器 | 文章类型转换器，可以将文章在多种文章类型中进行转换。 |
| 自定义文章代码 | 在文章编辑页面可以单独设置每篇文章 Head 和 Footer 代码。 |

== Installation ==

1. 上传 `wpjam-basic`目录 到 `/wp-content/plugins/` 目录
2. 激活插件，开始设置使用。

== Changelog ==

= 6.1.1 =
* 优化 post/get 接口
* wpjam_http_request 支持 headers 参数

= 6.1 =
* 接口请求的时候不显示 PHP 警告信息
* 菜单 summary 参数支持传递文件路径，程序会自动根据文件头信息
* 优化 wpjam_register_post_type 和 wpjam_register_taxonomy，在 init 之后也能够成功注册
* WPJAM_Register 的所有子类如有 autuload 方法，则会在自动 init 的时候自动执行
* WPJAM_Register 增加开关属性处理，支持注册时候自动开启和生成后台配置字段等能力
* 新增函数 wpjam_register_extend_option 和 wpjam_load_extends
* 新增函数 wpjam_register_handler 和 wpjam_get_handler
* 新增函数 wpjam_load，用于处理基于 action 判断加载
* 新增函数 wpjam_get_current_var 和 wpjam_set_current_var
* 新增函数 wpjam_get_platform_options 用于获取平台信息
* 新增函数 wpjam_register_data_type 和 wpjam_get_data_type_object
* 新增函数 wpjam_remove_prefix 和 wpjam_remove_postfix
* 新增函数 wpjam_get_post_type_setting 和 wpjam_update_post_type_setting
* 新增函数 wpjam_get_taxonomy_setting 和 wpjam_update_taxonomy_setting
* 新增函数 wpjam_add_post_type_field 和 wpjam_get_post_type_fields
* 新增函数 wpjam_add_taxonomy_field 和 wpjam_get_taxonomy_fields
* 增强函数 wpjam_show_if 和 wpjam_compare
* 新增 Class WPJAM_Error 用于自定义错误信息显示
* 新增 Class WPJAM_Option_Model 用于所有设置页面 Class 的基类
* 新增 Class WPJAM_Screen_Option 用于后台页面参数和选项处理
* 新增 Class WPJAM_Register，支持 group 和独立子类两种方式注册
* 新增 Class WPJAM_Meta_Option，用于支撑所有 Meta 选项注册
* 新增 Class WPJAM_Extend_Type，用于所有插件的扩展管理
* 函数 wpjam_register 新增 priority 参数
* 函数 wpjam_register_option 新增 field_default 和 menu_page 参数。
* 函数 wpjam_register_builtin_page_load 和 wpjam_register_plugin_page_load 都新增 page_file 参数
* Class WPJAM_Register 新增 init 参数，支持在 WordPress init 时回调
* 增强 Class WPJAM_Option_Setting
* 新增 wpjam_terms_extra_tablenav filter
* 增强「文章浏览」扩展，支持批量增加浏览数
* 增强「相关文章」扩展，新增日期限制
* 完全重构 WPJAM_Field，通过子类把功能分拆，并支持 JSON Schema validate
* 使用 wp_content_img_tag filter 改进 CDN 中图片处理。
* 修复 show_if 判断，和空值 validate 的问题。
* 其他优化和bug修复

= 6.0 =
* object-cache.php 支持 6.0 的批量操作
* 优化屏蔽 emoji 图片在 6.0 的兼容问题
* 优化登录界面处理
* 优化「相关文章」扩展的文章类型处理
* 优化「文章目录」扩展，使用ID进行锚点定位
* 「简单 SEO」 扩展支持设置唯一的 TDK
* 新增登录界面去掉语言切换器功能
* 缩略图设置支持设置多张默认缩略图
* 增强 wpjam_send_json 数据处理能力
* 基于 WP 5.9 优化 lazy loading 处理
* 新增函数 wpjam_register_builtin_page_load
* 新增函数 wpjam_register_plugin_page_load
* 新增函数 wpjam_generate_verification_code
* 新增函数 wpjam_verify_code
* 新增函数 wpjam_is_login，用于判断登录界面
* 新增 Class WPJAM_Fields，用于表单渲染
* 增强 Class WPJAM_JSON，整合接口验证
* wpjam_fields 函数支持 wrap_tag 参数
* WPJAM_Field 新增 json schema 解析和验证功能
* WPJAM_Fields 新增 get_defaults 方法
* WPJAM_Page_Action 支持多个提交按钮
* WPJAM_List_Table 支持多个提交按钮
* 火山引擎 veImageX 也支持自动 WebP 转换
* 兼容 PHP 8 废弃在可选参数后声明强制参数
* 修复作者下拉菜单引起的参数弃用提醒

= 5.9 =
* 优化后台文章列表操作
* CDN 加速水印设置增加最小图片设置
* CDN 加速新增支持火山引擎的 veImageX
* 解决部分博客插件冲突造成文章列表页空白的问题
* 解决 show_if 和默认 disabled 字段兼容问题
* 在文章列表页新增「上传外部图片」操作
* 全面实现后台文章和分类列表页 AJAX 操作
* 全面优化 CDN 加速功能，提供更多选项设置
* 新增函数 wpjam_lazyload，用于后端懒加载
* 新增函数 wpjam_get_by_meta 直接在 meta 表中查询数据
* 新增函数 wpjam_compare，用于两个数据比较
* 新增函数 wpjam_unserialize，用于反序列化失败之后修复数据，再次反序列化
* 新增函数 wpjam_is_external_image，用于判断外部图片
* 新增函数 wpjam_hex2rgba，支持将16进制颜色转成RGBA格式
* 新增函数 wpjam_list_filter，支持 in_array 判断
* 新增函数 wpjam_register_capability
* 新增函数 wpjam_get_ajax_data_attr
* 新增和优化 Gravatar 加速和 Google 字体加速服务
* 新增 field 支持 minlength 和 maxlength 服务端验证
* WPJAM_Field 支持 is_boolean_attribute 的判断
* WPJAM_Page_Action 新增 validate 参数使支持字段验证
* 文章类型转换支持在文章列表页进行转换操作
* mu-img 图片点击支持放大显示
* 取消「前台不加载语言包」功能

= 5.8 =
* 实现后台的文章列表和分类列表页 AJAX 操作
* 取消「屏蔽 REST API」功能
* 取消「禁止admin用户名」功能
* 修正 WPJAM_Model 同时存在 __call 和 __callStatic 上下文问题
* 优化上传的图片加上时间戳功能
* 增强 value_callback 处理
* 优化 class WPJAM_Cron
* 优化 wp_get_current_commenter filter 接口统一处理
* 优化 WPJAM_Query
* 优化 query_data 的全局处理，带参数的 list_table 菜单显示自动处理
* 新增自定义表 meta 查询 
* 新增 WPJAM_Field 分组打横显示功能
* 新增 wpjam_autocomplete_selected trigger
* 新增 wpjam_iframe JS 方法，默认在后台右下角显示
* 新增 class WPJAM_Bind 用于用户相关业务连接
* 新增 class WPJAM_Phone_Bind 用于手机号码相关业务连接
* 新增 class WPJAM_Cache_Group 用于处理同一 group 下的缓存
* 新增 class WPJAM_CDN_Type，优化 CDN 处理
* 新增 class WPJAM_AJAX 用于前台统一 AJAX 处理
* 新增 class WPJAM_Calendar_List_Table 用于日历后台
* 新增函数 PHP 7.3 以下版本 array_key_first 和 array_key_last 
* 新增函数 wpjam_render_list_table_column_items
* 新增函数 wpjam_register_meta_type
* 新增函数 wpjam_register_bind 
* 新增函数 wpjam_get_bind_object
* 新增函数 wpjam_zh_urlencode

= 5.7 =
* 优化字段处理能力，支持 required 后端判断
* 解决文章时间戳相同引起的排序问题
* 优化所有 List Table
* 优化定时作业处理
* 新增函数 wpjam_validate_field_value
* 新增函数 wpjam_array_pop
* 新增函数 wpjam_array_first
* 新增函数 wpjam_array_excerpt
* 新增函数 wpjam_get_permastruct
* 新增函数 wpjam_get_taxonomy_query_key 
* 新增函数 wpjam_get_post_id_field 
* 新增函数 wpjam_get_term_id_field

= 5.6 = 
* 跳过其他版本直接升级到 5.6 和 WordPress 保持一致
* wpjam_register_api 支持 callback
* WPJAM_DB 新增 lazyload_callback 参数
* CDN 文件扩展设置和媒体库对应
* 优化文章列表分类筛选
* 优化 WPJAM 路由处理
* 调用 save_post 改成调用 wp_after_insert_post

= 5.2 =
* 新增函数 wpjam_register_route_module
* wpjam_add_menu_page 支持 load_callback 参数
* form 页面支持 summary
* wpjam_register_option 支持自定义 update_callback
* wpjam_register_option 支持 reset 选项
* 优化 wpjam_register_option 的 sanitize_callback 回调
* 优化远程图片保存功能
* 优化缩略图扩展设置

= 5.1 =
* 支持腾讯云 COS 的 WebP 转换，节省流量
* 类型转换函数全部切换成强制类型转换
* 新增 Class WPJAM_Lazyloader
* CDN 后台媒体库只镜像图片
* CDN 远程图片功能上传到媒体库
* 支持停用 CDN，切换回使用本站图片
* 优化后台的类库加载，防止重复加载
* 修复相关文章扩展图片尺寸问题

= 5.0 =
* 缩略图设置支持应用到原生的缩略图中
* 优化图片的 $max_width 处理
* 新增函数 wpjam_parse_query
* 新增函数 wpjam_render_query
* 支持评论者头像存到 commentmeta 中
* 优化加密解密类 WPJAM_Crypt
* 优化后台脚本加载

= 4.6 =
* 优化反斜杠转义的处理
* 优化 global 变量处理
* 优化 list table 操作：新增 rediect response_type
* 增强 class WPJAM_Cron

= 4.5 =
* 新增函数 wpjam_download_image，用于下载远程图片
* 新增函数 wpjam_is_image，用于判断当前链接是否为图片
* 新增函数 wpjam_get_plugin_page_setting
* 新增函数 wpjam_register_list_table_action
* 新增函数 wpjam_register_list_table_column
* 新增函数 wpjam_register_page_action
* 新增函数 wpjam_is_webp_supported，用于判断是否支持 webp
* 新增用户处理的 class WPJAM_User
* 修复 Safari 浏览器下的批量操作不生效问题
* 优化后台图表功能
* 优化用户站内消息

= 4.4 =
* 阿里云 OSS 支持水印和 WebP 图片格式转换
* 字段处理增加 show_if 
* 兼容 PHP 7.4
* 优化代码效率
* Google 字体加速服务支持自定义地址
* Gravatar 加速服务支持自定义地址
* 修复「SEO扩展」分类设置失效问题

= 4.3 =
* 新增 wpjam_unicode_decode
* 新增函数 wpjam_admin_tooltip，用于后台提示
* 后台文章列表可配置缩略图，浏览数，作者过滤和排序选择
* Class WPJAM_List_Table 支持左右栏模式
* 百度站长扩展支持快速收录

= 4.2 =
* 新增函数 wpjam_get_current_platform()，用于获取当前平台
* 新增验证文本文件管理 class WPJAM_Verify_TXT
* 全面提升插件的安全性

= 4.1 =
* 新增禁止古腾堡编辑器加载 Google 字体
* 常用短代码新增B站视频支持 [bilibili]
* 经典编辑器标签切换优化

= 4.0 =
* 新增 wpjam_is_json_request 函数
* 新增 wpjam_sha1 函数
* 新增路径管理 Class WPJAM_Path 和对应函数

= 3.7 =
* 改进 object-cache.php，建议重新覆盖
* 远程图片支持复制到本地再镜像到云存储选项
* 新增「文章数量」扩展
* 新增「摘要快速编辑」扩展
* 新增「文章快速复制」扩展
* 新增后台文章列表页搜索支持ID功能
* 新增后台文章列表页作者筛选功能
* 新增后台文章列表页排序选型功能
* 新增后台文章列表页修改特色图片功能
* 新增后台文章列表页修改浏览数功能
* 优化 WPJAM_Model 缓存处理
* 「简单SEO」扩展支持列表页快速操作
* 「百度站长」扩展支持列表页批量提交
* 字段新增支持 name[subname] 方式的字段
* Class WPJAM_List_Table 新增拖动排序

= 3.6 =
* 「去掉URL中category」支持多级分类
* 新增屏蔽字符转码功能
* 新增屏蔽Feed功能
* 新增Google字体加速服务
* 新增Gravatar加速服务
* 新增移除后台界面右上角的选项
* 新增移除后台界面右上角的帮助
* 增强附件名新增时间戳功能
* 新增 str_replace_deep 函数
* 将文章页代码独立成独立扩展
* 「百度站长」扩展支持不加载推送 JS
* 「Rewrite」扩展支持查看所有规则

= 3.5 =
* 插件 readme 增加 PHP 7.2 最低要求
* Class WPJAM_List_Table 增强 overall 操作
* 「用户角色」扩展添加重置功能
* 修正自定义文章类型和自定义分类模式更新提示
* 修复图片编辑失效的问题
* 加强「屏蔽Trackbacks」功能
* 去掉「屏蔽主题Widget」功能
* 优化 Admin Notice 功能
* 增强 wpjam_send_json

= 3.4 =
* 新增「301跳转」扩展
* 新增「移动主题」扩展
* 新增「百度站长」扩展，修正预览提交
* 修正「简单SEO」的标题功能
* CDN 功能更好支持缩图和 HTTPS
* 「移动主题」扩展支持在后台启用移动主题

= 3.3 =
* 支持 UCLOUD 对象存储
* 支持屏蔽 Gutenberg
* 新增「相关文章」扩展

= 3.2 =
* 提供选项让用户去掉 URL 中category
* 提供选项让用户上传图片加上时间戳
* 增强 WPJAM SEO 扩展，支持 sitemap 拆分
* 更新 WPJAM 后台 Javascript 库

= 3.1 =
* 修正 WPJAM Basic 3.0 以后大部分 bug
* 新增 object-cache.php 到 template 目录

= 3.0 =
* 基于 PHP 7.2 进行代码重构，效率更高，更加快速
* 全AJAX操作后台

= 2.2 =
* 上架 WordPress 官方插件站
* 分拆功能组件
* WPJAM Basic 作为基础库使用
* 由于腾讯取消“通过发送邮件的方式发表 Qzone 文章”功能，下架同步到QQ空间功能


= 2.1 = 
* 新增「简单SEO」扩展
* 新增「短代码」扩展
* 新增 SMTP 扩展
* 新增「统计代码」扩展

= 2.0 =
* 初始版本直接来个 2.0 显得牛逼点