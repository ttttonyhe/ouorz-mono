<?php
function wpjam_functions_page(){
	$html = '
		[list]
			<a href="http://blog.wpjam.com/m/block-bad-queries/">防止 WordPress 遭受恶意 URL 请求</a>
			<a href="http://blog.wpjam.com/m/wpjam_redirect_guess_404_permalink/">改进 404 页面导向正确的页面的效率</a>
			<a href="http://blog.wpjam.com/m/redirect-to-post-if-search-results-only-returns-one-post/">当搜索结果只有一篇时直接重定向到日志</a>
			<a href="http://blog.wpjam.com/m/how-to-display-post-id-in-the-wordpress-admin/">在后台显示日志 ID</a>
			<a href="http://blog.wpjam.com/m/display-page-templates-on-page-list/">在后台页面列表显示使用的页面模板</a>
			<a href="http://blog.wpjam.com/m/enhance-wordpress-user-query/">增强用户搜索，支持通过 display_name, nickname, user_email 进行检索</a>
			<a href="http://blog.wpjam.com/m/wpjam_blacklist_check/">提供选项让你设置用户注册时候不能含有非法关键字</a>
			<a href="http://blog.wpjam.com/m/cache-wp_nav_menu/">缓存自定义菜单，加快博客速度</a>
		[/list]

		<h2>新增的函数</h2>

		[table border="0"]
			<code>wpjam_pagenavi()</code>
			<a href="http://blog.wpjam.com/m/wpjam-pagenavi/">实现任何页面的导航</a>

			<code>get_post_first_image($post_content)</code>
			<a href="http://blog.wpjam.com/m/get-the-first-image-in-posts/">获取日志中的第一个图片地址</a>

			<code>get_post_excerpt($post,$excerpt_length=240)</code>
			<a href="http://blog.wpjam.com/m/get_post_excerpt/">获取日志摘要</a>

			<code> get_first_p($post)</code>
			<a href="http://blog.wpjam.com/m/get_post_excerpt/">获取日志内容的第一段</a>

			<code>wpjam_blacklist_check($str)</code>
			<a href="http://blog.wpjam.com/m/wpjam_blacklist_check/">检测字符串中是否有非法关键字</a>
		[/table]

		<h2>新增的短代码（Shortcode）</h2>

		[table border="0"]
			[list]
			<a href="http://blog.wpjam.com/m/wordpress-shortcode-for-list/">快速插入列表</a>

			[table]
			<a href="http://blog.wpjam.com/m/wordpress-shortcode-for-table/">快速插入表格</a>

			[email]
			<a href="http://blog.wpjam.com/project/antispambot-shortcode/">插入邮箱地址，并可以防止被爬虫收集</a>

			[youku]和[tudou]
			<a href="http://blog.wpjam.com/project/antispambot-shortcode/">使用 Shortcode 方式插入视频，并支持全平台播放</a>
		[/table]
	';
	?>
	<div class="wrap">
		<h2>新增的功能</h2>
		<?php echo do_shortcode(str_replace("\n", "\r\n", $html)); ?>
	</div>
	<?php
}