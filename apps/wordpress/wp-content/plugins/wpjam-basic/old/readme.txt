=== WPJAM Basic ===
Contributors: denishua
Donate link: http://wpjam.com/
Tags: WPJAM,性能优化
Requires at least: 3.0
Tested up to: 4.7.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WPJAM Basic 是我爱水煮鱼博客多年来使用 WordPress 来整理的优化插件。

== Description ==

WPJAM Basic 是我爱水煮鱼博客多年来使用 WordPress 来整理的优化插件，主要功能，就是去掉 WordPress 当中一些不常用的功能，比如日志修订等，还有就是提供一些经常使用的函数，比如获取日志中第一张图，获取日志摘要等。如果你的主机安装了 Memcacached 等这类内存缓存组件和对应的 WordPress 插件，这个插件也针对提供一些针对一些常用的插件和函数提供了对象缓存的优化版本。

详细介绍： <a href="http://blog.wpjam.com/project/wpjam-basic/">http://blog.wpjam.com/project/wpjam-basic/</a>

最新版本已集成<a href="https://wordpress.org/plugins/wpjam-qiniu/">七牛插件</a>, 并兼容1.4.5及以上版本七牛插件。
如果启用该版本插件，请先停用七牛插件1.4.5以下版本。

WPJAM Basic 自2.6版本开始，只作为开发基础库。「微信机器人」和「七牛镜像存储插件」均基于此基础库开发。原有各个功能已拆分成组件，默认不启用。

使用上有问题，请加入<a href="https://wx.xiaomiquan.com/mweb/views/joingroup/join_group.html?group_id=4222114248">WordPress小密圈</a >


== Installation ==

1. 上传 `wpjam-qiniutek`目录 到 `/wp-content/plugins/` 目录
2. 在后台插件菜单激活该插件

== Changelog ==

= 2.6 =
* 分拆功能组件
* WPJAM Basic 作为基础插件库使用

= 2.5.3 =
* 修复七牛图片在微信客户端显示问题

= 2.5.2 =
* bug修复

= 2.5.1 =
* bug修复

= 2.5.0 =
* 版本大更新

= 2.4.1 =
* 新增屏蔽 WordPress REST API 功能
* 新增屏蔽文章 Embed 功能
* 由于腾讯已经取消“通过发送邮件的方式发表 Qzone 日志”功能，取消同步到QQ空间功能

= 2.4 =
* 更新 wpjam-setting-api.php
* 上架 WordPress 官方插件站

= 2.3 = 
* 新增数据库优化
* 内置列表功能

= 2.2 = 
* 新增短代码
* 新增 SMTP 功能
* 新增插入统计代码功能

= 2.1 = 
* 新增最简洁效率最高的 SEO 功能

= 0.1 =
* 初始版本