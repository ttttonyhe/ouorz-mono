<?php
add_action( 'admin_menu', 'wpjam_basic_admin_menu',9 );
function wpjam_basic_admin_menu() {
	add_menu_page( 						'WPJAM Basic',					'WPJAM Basic',	'manage_options',	'wpjam-basic',		'wpjam_basic_setting_page',	'dashicons-performance');
	add_submenu_page( 'wpjam-basic',	'WPJAM Basic &rsaquo; 设置',		'设置',			'manage_options',	'wpjam-basic',		'wpjam_basic_setting_page');
	if(wpjam_basic_check_domain()){
		add_submenu_page( 'wpjam-basic',	'WPJAM Basic &rsaquo; 新增功能',	'新增的功能',		'manage_options',	'wpjam-functions',	'wpjam_functions_page');
		add_submenu_page( 'wpjam-basic',	'WPJAM Basic &rsaquo; 内置列表',	'内置列表',		'manage_options',	'wpjam-list',		'wpjam_list_page');
		add_submenu_page( 'wpjam-basic',	'WPJAM Basic &rsaquo; 数据库优化','数据库优化',		'manage_options',	'wpjam-db-optimize','wpjam_db_optimize_page');
		add_submenu_page( 'wpjam-basic',	'WPJAM Basic &rsaquo; 数据清理',	'数据清理',		'manage_options',	'wpjam-clear',		'wpjam_clear_page');
		if(wpjam_basic_get_setting('show_all_setting')){
			add_options_page('所有设置', '所有设置', 'manage_options', 'options.php');
		}
	}
}

add_filter('pre_update_option_active_plugins', 'wpjam_basic_set_plugin_load_first');
function wpjam_basic_set_plugin_load_first($active_plugins){
	$wpjam_basic_plugin	= plugin_basename(WPJAM_BASIC_PLUGIN_FILE);
	if(false !== ($plugin_key	= array_search($wpjam_basic_plugin, $active_plugins))){
		unset($active_plugins[$plugin_key]);
		array_unshift($active_plugins,$wpjam_basic_plugin);
	}
	return $active_plugins;	
}

function wpjam_clear_page(){
 
	global $wpdb,$plugin_page;

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
		if ( !wp_verify_nonce($_POST['wpjam_basic_nonce'],'wpjam_basic') ){
			ob_clean();
			wp_die('非法操作');
		}

		$succeed_msg = '';
		if(isset($_POST['delete_revision'])){
			$revison_count = $wpdb->query("DELETE a,b,c FROM {$wpdb->posts} a LEFT JOIN {$wpdb->term_relationships} b ON (a.ID = b.object_id) LEFT JOIN {$wpdb->postmeta} c ON (a.ID = c.post_id) WHERE a.post_type = 'revision'");
			$succeed_msg .= $revison_count.' 条日志修订记录已经被清理。<br />';
		}

		if(isset($_POST['delete_postmeta'])){
			$useless_postmeta_count = $wpdb->query("DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.ID IS NULL");
			$succeed_msg .= $useless_postmeta_count.' 条无用的postmeta记录已经被清理。<br />';
		}

		if(isset($_POST['delete_tag'])){
			$useless_postmeta_count = $wpdb->query("DELETE a,b,c FROM {$wpdb->terms} AS a LEFT JOIN {$wpdb->term_taxonomy} AS c ON a.term_id = c.term_id LEFT JOIN {$wpdb->term_relationships} AS b ON b.term_taxonomy_id = c.term_taxonomy_id WHERE ( c.taxonomy = 'post_tag' AND c.count = 0);");

			$succeed_msg .= $useless_postmeta_count.' 个无用的标签已经被清理。<br />';
		}

		if(isset($_POST['delete_comment'])){
			$useless_comment_count = $wpdb->query("DELETE c FROM {$wpdb->comments} c WHERE comment_approved in ('0','spam');");

			$succeed_msg .= $useless_comment_count.' 条未审核或者垃圾留言已经被清理。<br />';

			$useless_commentmeta_count = $wpdb->query("DELETE cm FROM {$wpdb->commentmeta} cm LEFT JOIN {$wpdb->comments} c ON c.comment_ID = cm.comment_id WHERE c.comment_ID IS NULL");
			$succeed_msg .= $useless_commentmeta_count.' 条commentmeta已经被清理。<br />';
		}
	}

	$revison_count = $wpdb->get_var("SELECT count(*) FROM {$wpdb->posts} WHERE `post_type` = 'revision'");
	$useless_tag_count = $wpdb->get_var("SELECT count(*) From {$wpdb->terms} wt INNER JOIN {$wpdb->term_taxonomy} wtt ON wt.term_id=wtt.term_id WHERE wtt.taxonomy='post_tag' AND wtt.count=0;");
	$useless_postmeta_count = $wpdb->get_var("SELECT count(*) FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL");
	$useless_comment_count = $wpdb->get_var("SELECT count(*) FROM  {$wpdb->comments} WHERE comment_approved in ('0','spam');");

	?>
	<div class="wrap">
		<h2>数据清理</h2>
		<?php if(!empty($succeed_msg)){?>
		<div class="updated">
			<p><?php echo $succeed_msg;?></p>
		</div>
		<?php }?>

		<form method="post" action="<?php echo admin_url('admin.php?page='.$plugin_page); ?>" enctype="multipart/form-data" id="form">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">一建清理 WordPress 冗余数据</th>
						<td>
							<fieldset>

							<label for="delete_revision">
								<input name="delete_revision" type="checkbox" id="delete_revision" value="1"> <strong><?php echo $revison_count; ?></strong> 条日志修订记录
							</label><br />
							<label for="delete_postmeta">
								<input name="delete_postmeta" type="checkbox" id="delete_postmeta" value="1"> <strong><?php echo $revison_count; ?></strong> 条无用的postmeta记录
							</label><br />
							<label for="delete_tag">
								<input name="delete_tag" type="checkbox" id="delete_tag" value="1"> <strong><?php echo $useless_tag_count; ?></strong> 个无用的标签
							</label><br />
							<label for="delete_comment">
								<input name="delete_comment" type="checkbox" id="delete_comment" value="1"> <strong><?php echo $useless_comment_count; ?></strong> 条未审核或者垃圾留言
							</label><br />
							
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>

			<?php wp_nonce_field('wpjam_basic','wpjam_basic_nonce'); ?>

			<p class="submit"><input class="button-primary" type="submit" value="一键清理？" /></p>
		</form>

		<?php /*<p style="color:red;font-weight:bold;">该优化动作直接操作 WordPress 数据库，为了数据的安全，请事先做好数据备份</p> */?>

		<h3>其他问题</h3>
		<p>WPJAM Basic 主要功能是屏蔽一些 WordPress 很少用到的功能和清理 WordPress 数据库中冗余数据，但是 WordPress 真正需要性能提升是需要<a href="http://blog.wpjam.com/m/wordpress-memcached/">内存缓存</a>。</p>
		<p><strong style="color:red;">如果你需要支持内存缓存的主机，请联系 Denis，QQ：11497107。</strong></p>
	</div>
<?php
}


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
		[/list]

		<h2>新增的函数</h2>

		[list]
			<a href="http://blog.wpjam.com/m/wpjam-pagenavi/"><code>wpjam_pagenavi()</code>：实现任何页面的导航</a>
			<a href="http://blog.wpjam.com/m/get-the-first-image-in-posts/"><code>get_post_first_image($post_content)</code>：获取日志中的第一个图片地址</a>
			<a href="http://blog.wpjam.com/m/get_post_excerpt/"><code>get_post_excerpt($post,$excerpt_length=240)</code>：获取日志摘要</a>
			<a href="http://blog.wpjam.com/m/get_post_excerpt/"><code> get_first_p($post)</code>：获取日志内容的第一段</a>
			<a href="http://blog.wpjam.com/m/wpjam_nav_menu/"><code>wpjam_nav_menu</code>：缓存版 <code>wp_nav_menu</code>，大大提高效率</a>
			<a href="http://blog.wpjam.com/m/wpjam_blacklist_check/"><code>wpjam_blacklist_check($str)</code>：检测字符串中是否有非法关键字</a>
		[/list]

		<h2>新增的短代码（Shortcode）</h2>

		[list]
			<a href="http://blog.wpjam.com/m/wordpress-shortcode-for-list/">[[list]]：快速插入列表。</a>
			<a href="http://blog.wpjam.com/m/wordpress-shortcode-for-table/">[[table]]：快速插入表格。</a>
			<a href="http://blog.wpjam.com/project/antispambot-shortcode/">[[email]]：插入邮箱地址，并可以防止被爬虫收集。</a>
			<a href="http://blog.wpjam.com/m/video-shortcode/">[[youku]]和[[tudou]]：使用 Shortcode 方式插入视频，并支持全平台播放。</a>
		[/list]
	';
	?>
	<div class="wrap">
		<h2>新增的功能</h2>
		<?php echo do_shortcode(str_replace("\n", "\r\n", $html)); ?>
	</div>
	<?php
}


function wpjam_list_page(){
	$tabs = array(
		'shortcodes'	=> 'Shortcodes',
		'constants'		=> '系统常量',
		'hooks'			=> 'Hooks',
		'oembeds'		=> 'Oembeds',
	);
	?>
	<div class="wrap">
		<h2 class="nav-tab-wrapper">
		<?php $i = 0; ?>
		<?php foreach ($tabs as $tab => $name) { $i++;?>
			<a class="nav-tab <?php if($i == 1) { ?>nav-tab-active<?php }?>" href="javascript:void();" id="tab-title-<?php echo $tab; ?>"><?php echo $name;?></a>
		<?php }?>
		</h2>

		<?php foreach ($tabs as $tab => $name) { ?>
		<div id="tab-<?php echo $tab; ?>" class="div-tab hidden" >
		<?php call_user_func('wpjam_'.$tab.'_list'); ?>
		</div>
		<?php } ?>
	</div>
	<?php
}

function wpjam_shortcodes_list(){
	?>
	<h3>短代码列表</h3>
	<p>本页面列出系统中定义的所有短代码和相关函数。</p>
	<?php global $shortcode_tags; ?>
	<?php $alternate = ''; $i=0;?>
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th>行数</th>
				<th>Shortcode</th>
				<th>处理函数</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($shortcode_tags as $tag => $function) { $alternate = $alternate?'':'alternate'; $i++;?>
			<tr class="<?php echo $alternate; ?>">
				<td><?php echo $i;?></td>
				<td><?php echo $tag;?></td>
				<td><?php
				if(is_array($function)){
					echo get_class($function[0]).'->'.(string)$function[1];
				}else{
					echo $function;
				}
				?></td>
			</tr>
		<?php }?>
		</tbody>
	</table>
	<?php
}

function wpjam_oembeds_list(){
	?>
	<h3>Oembed</h3>
	<p>本页面列出系统中定义的所有 Oembeds。</p>
	<?php 
	require_once( ABSPATH . WPINC . '/class-oembed.php' );
	$oembed = _wp_oembed_get_object();
	?>
	<?php $alternate = ''; $i=0;?>
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th>行数</th>
				<th>格式</th>
				<th>oembed 地址</th>
				<th>使用正则</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($oembed->providers as $reg => $provider) { $alternate = $alternate?'':'alternate'; $i++;?>
			<tr class="<?php echo $alternate; ?>">
				<td><?php echo $i;?></td>
				<td><?php echo $reg;?></td>
				<td><?php echo $provider[0];?></td>
				<td><?php echo $provider[1]?'是':'否';?></td>
			</tr>
		<?php }?>
		</tbody>
	</table>
	<?php
}

function wpjam_hooks_list(){
	?>
	<h3>Hook</h3>
	<p>本页面列出系统中定义的所有 HOOK 和回调函数。</p>
	<?php 
	global $wp_filter, $merged_filters, $wp_actions;
	//print_r( get_defined_constants());
	?>
	<?php $alternate = ''; $i=0; ?>
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th>行数</th>
				<th>Hook</th>
				<th>函数</th>
				<th>优先级</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($wp_filter as $tag => $filter_array) { $alternate = $alternate?'':'alternate';?>
			<?php foreach ($filter_array as $priority => $function_array) {?>
				<?php foreach ($function_array as $function => $function_detail) { $i++;?>

				<tr class="<?php echo $alternate; ?>">
					<td><?php echo $i;?></td>
					<td><?php echo $tag;?></td>
					<td><?php echo $function;?></td>
					<td><?php echo $priority;?></td>
				</tr>
				<?php }?>
			<?php }?>
		<?php }?>
		</tbody>
	</table>
	<?php 
}

function wpjam_constants_list(){
	?>
	<h3>系统常量</h3>
	<p>本页面列出系统中定义的所有常量。</p>
	<?php $alternate = ''; $i = 0; ?>
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th>行数</th>
				<th>常量名</th>
				<th>值</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach (get_defined_constants() as $name => $value) { $alternate = $alternate?'':'alternate'; $i++;?>
			<tr class="<?php echo $alternate; ?>">
				<td><?php echo $i;?></td>
				<td><?php echo $name;?></td>
				<td><?php echo $value;?></td>
			</tr>
		<?php }?>
		</tbody>
	</table>
	<?php
}

function wpjam_db_optimize_page(){
	?>
	<div class="wrap">
		<h2>数据库优化</h2>
		<p>点击该页面直接优化你博客中的所有数据表。</p>
		<?php global $wpdb; ?>
		<?php $alternate = ''; ?>
		<table class="widefat" cellspacing="0">
			<thead>
				<tr>
					<th>数据表</th>
					<th>状态</th>
					<th>大小</th>
					<th>多余</th>
				</tr>
			</thead>
			<tbody>
			<?php

			$all_tables	= $wpdb->get_results('SHOW TABLE STATUS');
			$total_size	= 0;

			foreach ($all_tables as $table){
			$result = $wpdb->get_row("OPTIMIZE TABLE ".$table->Name);

			if ($result != false) {  
				$alternate = $alternate?'':'alternate';
				$total_size += $table->Data_length;
			?>
				<tr class="<?php echo $alternate; ?>">
					<td><?php echo $result->Table; ?></td>
					<td><?php echo $result->Msg_type.' : '.$result->Msg_text; ?></td>
					<td><?php echo wpjam_format_size($table->Data_length); ?></td>
					<td><?php echo wpjam_format_size($table->Data_free); ?></td>
				</tr>
			<?php } }
			?>
				<tr class="<?php echo $alternate; ?>">
					<td colspan="2">合计</td>
					<td colspan="2"><?php echo wpjam_format_size($total_size); ?></td>
				</tr>
			</tbody>
		</table>
	<?php
}

function wpjam_basic_check_domain(){
	// $domain = parse_url(home_url(), PHP_URL_HOST);
	// if(get_option('wpjam_net_domain_check_71') == md5($domain.'71')){
	// 	return true;
	// }

	$weixin_user = wpjam_topic_get_weixin_user();
	if($weixin_user && $weixin_user['subscribe']){
		return true;
	}

	return false;
}

function wpjam_basic_setting_page() {
	if(wpjam_basic_check_domain()){
		echo '<div class="wrap">';
		wpjam_option_page('wpjam-basic');
		echo '</div>';
	}else{
		global $current_admin_url;
		$current_admin_url = admin_url('admin.php?page=wpjam-basic');
		wpjam_topic_setting_page('WPJAM Basic','<p>请使用微信扫描下面的二维码，获取验证码之后提交即可验证通过！</p>');
	}
}

add_filter('wpjam_settings', 'wpjam_basic_settings');
function wpjam_basic_settings($wpjam_settings){
	$wpjam_settings['wpjam-basic'] 	= array('sections'=>wpjam_basic_get_option_sections());
	return $wpjam_settings;
}

function wpjam_basic_get_option_sections(){

	$wpjam_basic_section_fields = array(
		'remove_head_links'		=>array('title'=>'移除头部无用代码',		'type'=>'checkbox',	'description'=>'<a href="http://blog.wpjam.com/m/emove-unnecessary-code-from-wp_head/">移除 wp_head 中无关紧要的代码</a>，保持整洁，提高安全性。'),
		'diable_revision'		=>array('title'=>'屏蔽日志修订功能',		'type'=>'checkbox',	'description'=>'<a href="http://blog.wpjam.com/m/disable-post-revision/">屏蔽日志修订功能</a>，提高数据库效率。'),
		'disable_trackbacks'	=>array('title'=>'关闭 Trackbacks 功能',	'type'=>'checkbox',	'description'=>'<a href="http://blog.wpjam.com/m/bye-bye-trackbacks/">彻底关闭 Trackbacks</a>，和垃圾留言说拜拜。'),
		'disable_autoembed'		=>array('title'=>'禁用 Auto Embeds 功能',	'type'=>'checkbox',	'description'=>'Auto Embeds 基本不支持国内网站，建议<a href="http://blog.wpjam.com/m/disable-auto-embeds-in-wordpress/">禁用 Auto Embeds 功能</a>，加快页面解析速度。'),
		'disable_xml_rpc'		=>array('title'=>'关闭 XML RPC 功能',		'type'=>'checkbox',	'description'=>'如果你无需通过 APP 客户端发布日志，建议<a href="http://blog.wpjam.com/m/disable-xml-rpc/">关闭 XML-RPC 功能</a>。'),
		'disable_cron'			=>array('title'=>'禁用 WP Cron 功能',		'type'=>'checkbox',	'description'=>'如果你的 WordPress 没有需要定时执行的作业（比如定时发布），可以<a href="http://blog.wpjam.com/m/disable-wp-cron/">禁用 WP Cron</a>。'),
		'disable_rest_api'		=>array('title'=>'屏蔽 REST API 功能',	'type'=>'checkbox',	'description'=>'<a href="http://blog.wpjam.com/m/disable-wordpress-rest-api/">屏蔽 WordPress REST API 功能</a>。'),
		'disable_post_embed'	=>array('title'=>'屏蔽文章 Embed 功能',	'type'=>'checkbox',	'description'=>'<a href="http://blog.wpjam.com/m/disable-wordpress-post-embed/">屏蔽文章 Embed 功能</a>。'),
		'remove_admin_bar'		=>array('title'=>'全局移除工具栏',			'type'=>'checkbox',	'description'=>'全局<a href="http://blog.wpjam.com/m/remove-wp-3-1-admin-bar/">移除工具栏（admin bar）</a>，所有人包括管理员都看不到，并且个人页面关于工具栏的选项也失效。'),
		'remove_dashboard_widgets'=>array('title'=>'移除后台仪表盘',		'type'=>'checkbox',	'description'=>'移除后台仪表盘所有的 Widgets。'),
		'304_headers'			=>array('title'=>'开启 304 Header',		'type'=>'checkbox',	'description'=>'<a href="http://blog.wpjam.com/m/wpjam-304-header/">给未登录用户开启 304 Not Modified Header</a>，再次访问同一页面不再请求服务器资源。'),
		'defer'					=>array('title'=>'Defer 模式加载 JS',		'type'=>'checkbox',	'description'=>'<a href="http://blog.wpjam.com/m/javascript-defer/">使用 defer 延迟加载 JavaScript</a>，加快页面渲染。'),
		'shortcode_first'		=>array('title'=>'优先执行 Shortcode',	'type'=>'checkbox',	'description'=>'让 Shortcode 优先于 wpautop 执行，<a href="http://blog.wpjam.com/m/solution-to-wordpress-adding-br-and-p-tags-around-shortcodes/">解决 Shortcode 中自动添加的 br 或者 p 标签</a>。'),
		'locale'				=>array('title'=>'前台不加载语言包',		'type'=>'checkbox',	'description'=>'前台<a href="http://blog.wpjam.com/m/setup-different-admin-and-frontend-language-on-wordpress/">不加载语言包</a>，可以提高0.1-0.5秒。'),
		'disable_update'		=>array('title'=>'移除后台更新提示',		'type'=>'checkbox',	'description'=>'<a href="http://blog.wpjam.com/m/disable-update-notification/">移除后台核心，插件和主题的更新提示</a>。'),
		// 'remove_unuse_rewrite'	=>array('title'=>'移除无用的rewrite',		'type'=>'checkbox',	'description'=>'<a href="#">移除tracback等无用的rewrite，提高网站效率</a>。'), 
		'order_by_registered'	=>array('title'=>'按照用户注册时间排序',	'type'=>'checkbox',	'description'=>'后台用户列表<a href="http://blog.wpjam.com/m/order-by-user-registered-time/">按照用户注册时间排序</a>。'),
		'strict_user'			=>array('title'=>'严格用户模式',			'type'=>'checkbox',	'description'=>'严格用户模式下，昵称和显示名称都是唯一的，并且用户名中不允许出现非法关键词（非法关键词是在 <strong>设置</strong> &amp; <strong>讨论</strong> 中 <code>评论审核</code> 和 <code>评论黑名单</code> 中定义的关键词）。'),
	);

	global $wp_object_cache;
	if(function_exists('process_postviews') && wp_using_ext_object_cache()){ //已经安装了 postview 插件，并且系统启用了 Memcached
		$wpjam_basic_section_fields['postviews_cache'] = array('title'=>'缓存 Postviews',		'type'=>'checkbox',	'description'=>'将文章浏览数保存在内存中，每10次才写入数据库。');
	}


	$wpjam_seo_section_fields = array(
		'active_seo'			=>array('title'=>'开启简单 SEO',			'type'=>'checkbox',	'description'=>'简单快捷的 WordPress SEO 功能。'),
		'seo_use_keywords'		=>array('title'=>'使用 Keywords',		'type'=>'checkbox', 'description'=>'文章页面是否将 tag 作为 Meta Keywords。'	),
		'seo_home_keywords'		=>array('title'=>'首页 Keywords',		'type'=>'text',		'description'=>'' ),
		'seo_home_description'	=>array('title'=>'首页描述',				'type'=>'textarea', 'rows'=>'4',	'description'=>'' ),
		'seo_robots'			=>array('title'=>'robots.txt',			'type'=>'textarea',	'description'=>'如果博客的根目录下已经有 robots.txt 文件，请先删除，否则这里设置的无法生效。'	),
	);

	$wpjam_smtp_section_fields = array(
		'active_smtp'			=>array('title'=>'使用 SMTP 发送邮件',		'type'=>'checkbox',	'description'=>'使用 SMTP 发送邮件可以提高邮件的发送成功率，防止进入垃圾邮箱。'),
		'smtp_mail_from_name'	=>array('title'=>'发送者姓名',			'type'=>'text'	),
		'smtp_host'				=>array('title'=>'SMTP 地址',			'type'=>'text'	),
		'smtp_ssl'				=>array('title'=>'SMTP 发送协议',			'type'=>'text'	),
		'smtp_port'				=>array('title'=>'SMTP SSL 协议端口',		'type'=>'text'	),
		'smtp_user'				=>array('title'=>'SMTP 邮箱账号',			'type'=>'text'	),
		'smtp_pass'				=>array('title'=>'SMTP 邮箱密码',			'type'=>'password'	),
		'smtp_reply_to_mail'	=>array('title'=>'默认回复邮箱地址',		'type'=>'text'	),
		'smtp_reply_to_name'	=>array('title'=>'默认回复邮箱姓名',		'type'=>'text'	),
	);

	$wpjam_stats_section_fields = array(
		'active_stats'			=>array('title'=>'开启统计',				'type'=>'checkbox',	'description'=>'开启 Google 分析和百度统计。'),
		'google_analytics_id'	=>array('title'=>'Google 分析跟踪 ID',	'type'=>'text'	),
		'google_universal'		=>array('title'=>'Universal Analytics',	'type'=>'checkbox',	'description'=>'Google 分析使用 Universal Analytics 跟踪代码。'	),
		'baidu_tongji_id'		=>array('title'=>'百度统计 ID',			'type'=>'text'	)
	);

	$wpjam_custom_section_fields = array(
		'admin_head_style'		=>array('title'=>'后台自定义 CSS ',		'type'=>'textarea',	'rows'=>'4'),
		'admin_footer_text'		=>array('title'=>'后台 Footer 文本',		'type'=>'textarea',	'rows'=>'4'),
		'login_head_style'		=>array('title'=>'登录界面自定义 CSS',		'type'=>'textarea',	'rows'=>'4'),
		'head'					=>array('title'=>'前台 Head 代码',		'type'=>'textarea',	'rows'=>'4'),
		'show_all_setting'		=>array('title'=>'显示所有设置',			'type'=>'checkbox',	'description'=>'在设置菜单下面显示<strong>所有设置</strong>子菜单。'),
		'image_default_link_type' =>array('title'=>'媒体文件默认链接到：',	'type'=>'select',	'options'=>array('none'=>'无','file'=>'媒体文件','post'=>'附件页面')),
	);

	$sections = array( 
		'wpjam-basic'	=>array('title'=>'优化设置', 		'fields'=>$wpjam_basic_section_fields,		'summary'=>'<p>下面的选项，可以让你关闭 WordPress 中一些不常用的功能来提速，但是注意关闭一些功能会引起一些操作无法执行。</p>'),
		'wpjam-seo'		=>array('title'=>'SEO',			'fields'=>$wpjam_seo_section_fields,		'callback'=>'wpjam_seo_section_callback'),
		'wpjam-smtp'	=>array('title'=>'SMTP', 		'fields'=>$wpjam_smtp_section_fields,		'summary'=>'<p>*只有使用 wp_mail 函数进行发送邮件，才会调用 SMTP 的设置，使用 PHP 默认的 mail 函数发送是无法调用的。<br /><a href="http://blog.wpjam.com/m/gmail-qmail-163mail-imap-smtp-pop3/">常用邮箱的 SMTP 地址请点击这里</a>。</p>'),
		'wpjam-stats'	=>array('title'=>'统计', 		'fields'=>$wpjam_stats_section_fields),
		'wpjam-custom'	=>array('title'=>'定制', 		'fields'=>$wpjam_custom_section_fields)
	);

	return apply_filters('wpjam_basic_setting',$sections);
}

add_filter('wpjam-basic_defaults', 'wpjam_basic_get_defaults');
function wpjam_basic_get_defaults(){
	$site_url = parse_url( site_url() );
	$path = ( !empty( $site_url['path'] ) ) ? $site_url['path'] : '';
	$seo_robots	= '';
	$seo_robots	.= "User-agent: *\n";
	$seo_robots	.= "Disallow: /wp-admin/\n";
	$seo_robots	.= "Disallow: /wp-includes/\n";
	$seo_robots	.= "Disallow: /cgi-bin/\n";
	$seo_robots	.= "Disallow: $path/wp-content/plugins/\n";
	$seo_robots	.= "Disallow: $path/wp-content/themes/\n";
	$seo_robots	.= "Disallow: $path/wp-content/cache/\n";
	$seo_robots	.= "Disallow: $path/author/\n";
	$seo_robots	.= "Disallow: $path/trackback/\n";
	$seo_robots	.= "Disallow: $path/feed/\n";
	$seo_robots	.= "Disallow: $path/comments/\n";
	$seo_robots	.= "Disallow: $path/author/\n";
	$seo_robots	.= "Disallow: $path/search/\n";

	$defaults = array(
		'disable_xml_rpc'		=> 0,
		'disable_cron'			=> 0,
		'remove_head_links'		=> 1,
		'diable_revision'		=> 1,
		'disable_autoembed'		=> 1,
		'disable_trackbacks'	=> 1,
		'remove_admin_bar'		=> 1,
		'remove_dashboard_widgets'	=>0,
		'304_headers'			=> 0,
		'disable_update'		=> 0,
		// 'remove_unuse_rewrite'	=> 0,
		'order_by_registered'	=> 1,
		'strict_user'			=> 0,
		'defer'					=> 0,
		'shortcode_first'		=> 0,
		'locale'				=> 0,
		'postviews_cache'		=> 0,
		
		'active_seo'			=> 0,
		'seo_use_keywords'		=> 0,
		'seo_home_keywords'		=> '',
		'seo_home_description'	=> '',
		'seo_robots'			=> $seo_robots,

		'active_smtp'			=> 0,
		'smtp_mail_from_name'	=> '',
		'smtp_host'				=> '',
		'smtp_ssl'				=> 'ssl',
		'smtp_port'				=> '465',
		'smtp_user'				=> '',
		'smtp_pass'				=> '',
		'smtp_reply_to_mail'	=> '',
		'smtp_reply_to_name'	=> '',

		'active_stats'			=> 0,
		'google_analytics_id'	=> '',
		'google_universal'		=> 1,
		'baidu_tongji_id'		=> '',

		'admin_head_style'		=> '',
		'admin_footer_text'		=> '',
		'login_head_style'		=> '',
		'head'					=> '',
		'favicon'				=> '',
		'apple_touch_icon'		=> '',
		'show_all_setting'		=> 0,
		'image_default_link_type' => get_option('image_default_link_type'),
	);

	return apply_filters('wpjam_basic_defaults',$defaults);
}

add_filter('wpjam-basic_field_validate','wpjam_basic_field_validate');
function wpjam_basic_field_validate($wpjam_basic){
	update_option('image_default_link_type',$wpjam_basic['image_default_link_type']);
	flush_rewrite_rules();
	return $wpjam_basic;
}

function wpjam_seo_section_callback(){
	echo '<ol>';
	echo '
	<li>可以设置首页的 Meta Description 和 Keywords。</li>
	<li>自动获取文章摘要作为文章页面的 Meta Description，可以将文章页面的 Tag 作为 Meta Keywords。</li>
	<li>自动获取分类和 Tag 的描述作为分类和 Tag 页面的 Meta Description。</li>
	<li>如果博客支持并开启固定链接，自动生成 <a href="'.home_url('/robots.txt').'" target="_blank">robots.txt</a> 和 <a href="'.home_url('/sitemap.xml').'" target="_blank">sitemap.xml</a>。</li>
	';
	echo '</ol>';
}

function wpjam_basic_get_setting($setting_name){
	return wpjam_get_setting('wpjam-basic', $setting_name);
}

