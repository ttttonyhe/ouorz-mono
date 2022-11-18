<?php
add_filter('server-status_tabs',	'wpjam_server_status_tabs');
function wpjam_server_status_tabs($tabs){
	return array(
		'server'	=> array('title'=>'服务器',		'function'=>'wpjam_server_page'),
		'opcache'	=> array('title'=>'Opcache',	'function'=>'wpjam_opcache_page'),
		'memcached'	=> array('title'=>'Memcached',	'function'=>'wpjam_memcached_page'),
	);
}

function wpjam_server_page(){
	add_filter('wpjam_dashboard_widgets', 'wpjam_server_dashboard_widgets');
	wpjam_admin_dashboard_page('服务器信息');
}

function wpjam_server_dashboard_widgets($wpjam_dashboard_wdigets){
	$wpjam_dashboard_wdigets['wpjam-server']			= array('title'=>'服务器信息');
	$wpjam_dashboard_wdigets['wpjam-server-version']	= array('title'=>'服务器版本',	'context'=>'side');
	$wpjam_dashboard_wdigets['wpjam-server-php']		= array('title'=>'PHP');
	return $wpjam_dashboard_wdigets;
}

function wpjam_server_dashboard_widget_callback($dashboard, $meta_box){
	?>
	<table class="widefat" style="border:none;">
		<tbody>
			<?php 
			// $file = file('/proc/uptime');
			
			// wpjam_print_r($file);
			// wpjam_print_r(getrusage());

			// echo memory_get_usage();
			// echo '<br />';
			// echo memory_get_usage(true);
			// echo '<br />';
			// echo memory_get_peak_usage();
			// echo '<br />';
			// echo memory_get_peak_usage(true);

			// wpjam_print_r(ini_get_all());

			?>
			
			<tr class="alternate">
				<th style="width:84px;">服务器</th>
				<td><?php echo gethostname().'（'.$_SERVER['HTTP_HOST'].'）'; //get_current_user().' - '.?></td>
			</tr>
			<tr>
				<th>服务器IP</th>
				<td><?php 
					echo $_SERVER['SERVER_ADDR'];
					$ipdata = wpjam_get_ipdata($_SERVER['SERVER_ADDR']);
					echo '（'.$ipdata['country'];
					echo ' '.$ipdata['region'];
					echo ($ipdata['region']!=$ipdata['city'])?' '.$ipdata['city']:'）';
					echo '<!-- '.$ipdata['isp'].'-->';
				?></td>
			</tr>
			<tr class="alternate">
				<th>系统</th>
				<td><?php echo php_uname('s');?></td>
			</tr>
			<tr>
				<th>配置</th>
				<td><?php 
					$cpus	= trim(file_get_contents('/proc/cpuinfo'));
					$cpus	= explode("\n\n", $cpus);
					$cpu_count	= count($cpus);

					$mems	= trim(file_get_contents('/proc/meminfo'));
					$mems	= explode("\n", $mems);

					$mems_list = array();

					foreach ($mems as $mem) {
						list($key, $value)	= explode(':', $mem);
						$mems_list[$key]	= (int)$value;
					}

					$mem_total = $mems_list['MemTotal'];
					
					echo $cpu_count.'核CPU&nbsp;&nbsp;/&nbsp;&nbsp;'.round($mem_total/1024/1024).'G内存';
				?></td>
			</tr>
			<tr class="alternate">
				<th>运行时间</th>
				<td><?php 
					$uptime = trim(file_get_contents('/proc/uptime'));
					$uptime	= explode(' ', $uptime);
					echo human_time_diff('', $uptime[0]);
				?></td>
			</tr>
			<tr>
				<th>空闲率</th>
				<td><?php echo round($uptime[1]*100/($uptime[0]*$cpu_count), 2).'%';?></td>
			</tr>
			<tr class="alternate">
				<th>系统负载</th>
				<td><?php $server_loads = sys_getloadavg(); echo '<strong>'.$server_loads[0].'&nbsp;&nbsp;'.$server_loads[1].'&nbsp;&nbsp;'.$server_loads[2].'</strong>';?></td>
			</tr>
			<tr>
				<th>文档根目录</th>
				<td><?php echo $_SERVER['DOCUMENT_ROOT'];?></td>
			</tr>
		</tbody>
	</table>
	<?php
}

function wpjam_server_php_dashboard_widget_callback($dashboard, $meta_box){
	?>
	<table class="widefat" style="border:none;">
		<tbody>
			<tr class="alternate">
				<th>扩展</th>
				<td><?php echo implode(', ', get_loaded_extensions());//echo apache_get_version();?></td>
			</tr>
		</tbody>
	</table>
	<?php
}

function wpjam_server_version_dashboard_widget_callback($dashboard, $meta_box){
	?>
	<table class="widefat" style="border:none;">
		<tbody>
			<tr class="alternate">
				<th><?php global $is_apache, $is_nginx, $is_iis; if($is_apache){ echo 'Apache';}elseif($is_nginx){echo 'nginx';}elseif($is_iis){echo 'IIS';}?></th>
				<td><?php echo $_SERVER['SERVER_SOFTWARE'];//echo apache_get_version();?></td>
			</tr>
			<tr>
				<th>MySQL</th>
				<td><?php global $wpdb,$required_mysql_version; echo $wpdb->db_version().'（最低要求：'.$required_mysql_version.'）';?></td>
			</tr>
			<tr class="alternate">
				<th style="width:84px;">PHP</th>
				<td><?php global $required_php_version; echo phpversion().'（最低要求：'.$required_php_version.'）'; //echo DEFAULT_INCLUDE_PATH; ?></td>
			</tr>
			<tr>
				<th>Zend</th>
				<td><?php echo Zend_Version();?></td>
			</tr>
			<tr class="alternate">
				<th>WordPress</th>
				<td><?php global $wp_version,$wp_db_version; echo $wp_version.'（'.$wp_db_version.'）';?></td>
			</tr>
			<tr>
				<th>TinyMCE</th>
				<td><?php global $tinymce_version; echo $tinymce_version; ?></td>
			</tr>
		</tbody>
	</table>
	<?php
}

function wpjam_opcache_page(){
	global $current_admin_url;

	$action	= isset($_GET['action']) ? $_GET['action'] : '';
	if($action == 'flush'){
		check_admin_referer('flush-memcached');
		opcache_reset();

		$redirect_to = add_query_arg( array( 'deleted' => 'true' ), wpjam_get_referer() );
		wp_redirect($redirect_to);
	}
	?>

	<h2>OPCache状态</h2>
	<?php wpjam_admin_errors();?>
	<?php 
	$capability	= (is_multisite())?'manage_site':'manage_options';
	if(current_user_can($capability)){ ?>
	<a href="<?php echo esc_url(wp_nonce_url($current_admin_url.'&action=flush', 'flush-memcached'))?>" class="button-primary">刷新缓存</a>
	<?php }?>

	<?php 
	add_filter('wpjam_dashboard_widgets', 'wpjam_opcache_dashboard_widgets');
	wpjam_admin_dashboard_page();
}

function wpjam_opcache_dashboard_widgets($wpjam_dashboard_wdigets){
	$wpjam_dashboard_wdigets['wpjam-opcache-usage']			= array('title'=>'OPCache使用率',);
	$wpjam_dashboard_wdigets['wpjam-opcache-status']		= array('title'=>'OPCache状态',		'context'=>'side');
	$wpjam_dashboard_wdigets['wpjam-opcache-configuration']	= array('title'=>'OPCache配置信息',	'context'=>'side');
	return $wpjam_dashboard_wdigets;
}

function wpjam_opcache_status_dashboard_widget_callback($dashboard, $meta_box){
	$opcache_status	= opcache_get_status();
	?>
	<table class="widefat" style="border:none;">
		<tbody>
		<?php foreach ( $opcache_status['opcache_statistics'] as $key => $value ) { ?>
			<tr class="<?php $alternate	= empty($alternate)?'alternate':''; echo $alternate;?>">
				<th><?php echo $key;?></th>
				<td><?php echo $value;?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php
}

function wpjam_opcache_usage_dashboard_widget_callback($dashboard, $meta_box){
	$opcache_status	= opcache_get_status();

	$counts		= array();

	$counts[]	= array('label'=>'已用内存','count'=>round($opcache_status['memory_usage']['used_memory']/(1024*1024),2));
	$counts[]	= array('label'=>'剩余内存','count'=>round($opcache_status['memory_usage']['free_memory']/(1024*1024),2));
	$counts[]	= array('label'=>'浪费内存','count'=>round($opcache_status['memory_usage']['wasted_memory']/(1024*1024),2));

	$total		= round(($opcache_status['memory_usage']['used_memory']+$opcache_status['memory_usage']['free_memory']+$opcache_status['memory_usage']['wasted_memory'])/(1024*1024),2);

	wpjam_donut_chart($counts, array('title'=>'内存使用','total'=>$total,'chart_width'=>150,'table_width'=>320));

	$counts		= array();

	$counts[]	= array('label'=>'命中','count'=>$opcache_status['opcache_statistics']['hits']);
	$counts[]	= array('label'=>'未命中','count'=>$opcache_status['opcache_statistics']['misses']);

	$total		= $opcache_status['opcache_statistics']['hits']+$opcache_status['opcache_statistics']['misses'];

	wpjam_donut_chart($counts, array('title'=>'命中率','total'=>$total,'chart_width'=>150,'table_width'=>320));

	$counts		= array();

	$counts[]	= array('label'=>'已用Keys','count'=>$opcache_status['opcache_statistics']['num_cached_keys']);
	$counts[]	= array('label'=>'剩余Keys','count'=>$opcache_status['opcache_statistics']['max_cached_keys']-$opcache_status['opcache_statistics']['num_cached_keys']);

	$total		= $opcache_status['opcache_statistics']['max_cached_keys'];

	wpjam_donut_chart($counts, array('title'=>'存储Keys','total'=>$total,'chart_width'=>150,'table_width'=>320));

	$counts		= array();

	$counts[]	= array('label'=>'已用内存','count'=>round($opcache_status['interned_strings_usage']['used_memory']/(1024*1024),2));
	$counts[]	= array('label'=>'剩余内存','count'=>round($opcache_status['interned_strings_usage']['free_memory']/(1024*1024),2));

	$total		= round($opcache_status['interned_strings_usage']['buffer_size']/(1024*1024),2);

	wpjam_donut_chart($counts, array('title'=>'临时字符串存储内存','total'=>$total,'chart_width'=>150,'table_width'=>320));
}

function wpjam_opcache_configuration_dashboard_widget_callback($dashboard, $meta_box){
	?>
	<table class="widefat" style="border:0;">
		<tbody>
		<?php $opcache_configuration = opcache_get_configuration();?>
		<?php foreach ( $opcache_configuration['version'] as $key => $value ) { ?>
			<tr class="<?php $alternate	= empty($alternate)?'alternate':''; echo $alternate;?>">
				<th><?php echo $key;?></th>
				<td><?php echo $value;?></td>
			</tr>
		<?php } ?>
		<?php foreach ( $opcache_configuration['directives'] as $key => $value ) { ?>
			<tr class="<?php $alternate	= empty($alternate)?'alternate':''; echo $alternate;?>">
				<th><?php echo str_replace('opcache.', '', $key);?></th>
				<td><?php echo $value;?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php
}

function wpjam_memcached_page(){
	global $current_admin_url;

	$action	= isset($_GET['action']) ? $_GET['action'] : '';
	if($action == 'flush'){
		check_admin_referer('flush-memcached');
		wp_cache_flush();

		$redirect_to = add_query_arg( array( 'deleted' => 'true' ), wpjam_get_referer() );
		wp_redirect($redirect_to);
	}

	?>
	<h2>Memcached 状态</h2>
	<?php wpjam_admin_errors();?>
	<?php 
	$capability	= (is_multisite())?'manage_site':'manage_options';
	if(current_user_can($capability)){ ?>
	<a href="<?php echo esc_url(wp_nonce_url($current_admin_url.'&action=flush', 'flush-memcached'))?>" class="button-primary">刷新缓存</a>
	<?php }?>
	<?php 

	add_filter('wpjam_dashboard_widgets', 'wpjam_memcached_dashboard_widgets');
	wpjam_admin_dashboard_page();
}


function wpjam_memcached_dashboard_widgets($wpjam_dashboard_wdigets){
	$wpjam_dashboard_wdigets['wpjam-memcached-usage']	= array('title'=>'Memcached使用率');
	$wpjam_dashboard_wdigets['wpjam-memcached-status']	= array('title'=>'Memcached状态',	'context'=>'side');
	return $wpjam_dashboard_wdigets;
}

function wpjam_memcached_status_dashboard_widget_callback($dashboard, $meta_box){
	global $wp_object_cache;

	$mc		= $wp_object_cache->get_mc('defaul');
	$stats	= $mc->getStats();
	?>

	<?php foreach ($stats as $key => $details) { ?>

	<table class="widefat" style="border:0;">
		<tbody>
			<!-- <tr>
				<td>Memcached进程ID</td>
				<td><?php echo $details['pid'];?></td>
			</tr> -->
			<tr class="alternate">
				<th>Memcached地址</th>
				<td><?php echo $key;?></td>
			</tr>
			<tr>
				<th>Memcached版本</th>
				<td><?php echo $details['version'];?></td>
			</tr>
			<tr class="alternate">
				<th>启动时间</th>
				<td><?php echo get_date_from_gmt(date('Y-m-d H:i:s',($details['time']-$details['uptime'])));?></td>
			</tr>
			<tr>
				<th>运行时间</th>
				<td><?php echo human_time_diff(0,$details['uptime']);?></td>
			</tr>
			<tr class="alternate">
				<th>已用/分配的内存</th>
				<td><?php echo size_format($details['bytes']) . ' / '. size_format($details['limit_maxbytes']);?></td>
			</tr>
			<tr>
				<th>当前/启动后总数量</th>
				<td><?php echo $details['curr_items'] . ' / ' . $details['total_items'];?></td>
			</tr>
			<tr class="alternate">
				<th>为获取内存而删除数量</th>
				<td><?php echo $details['evictions'];?></td>
			</tr>
			
			<tr>
				<th>当前/总打开过连接数</th>
				<td><?php echo $details['curr_connections'] . ' / ' . $details['total_connections'];?></td>
			</tr>
			<tr class="alternate">
				<th>命中次数</th>
				<td><?php echo $details['get_hits'];?></td>
			</tr>
			<tr>
				<th>未命中次数</th>
				<td><?php echo $details['get_misses'];?></td>
			</tr>
			<tr class="alternate">
				<th>总获取请求次数</th>
				<td><?php echo $details['cmd_get'];?></td>
			</tr>
			<tr>
				<th>总设置请求次数</th>
				<td><?php echo $details['cmd_set'];?></td>
			</tr>
			<tr class="alternate">
				<th>Item平均大小</th>
				<td><?php echo size_format($details['bytes']/$details['curr_items']);?></td>
			</tr>
		</tbody>
	</table>
	<?php
	/*$description_list = array(
		'pid'						=>'Memcached服务器的进程ID',
		'uptime'					=>'服务器已经运行的秒数',
		'time'						=>'服务器当前的unix时间戳',
		'version'					=>'Memcached版本',
		'pointer_size'				=>'当前操作系统的位数',
		'rusage_user'				=>'进程的累计用户时间',
		'rusage_user_seconds'		=>'进程的累计用户时间（秒）',
		'rusage_user_microseconds'	=>'进程的累计用户时间（毫秒）',
		'rusage_system'				=>'进程的累计系统时间',
		'rusage_system_seconds'		=>'进程的累计系统时间（秒）',
		'rusage_system_microseconds'=>'进程的累计系统时间（毫秒）',
		'curr_items'				=>'服务器当前存储的items数量',
		'total_items'				=>'从服务器启动以后存储的items总数量',
		'bytes'						=>'当前服务器存储items占用的字节数',
		'curr_connections'			=>'当前打开着的连接数',
		'total_connections'			=>'从服务器启动以后曾经打开过的连接数',
		'connection_structures'		=>'服务器分配的连接构造数',
		'cmd_get'					=>'get命令（获取）总请求次数',
		'cmd_set'					=>'set命令（保存）总请求次数',
		'get_hits'					=>'总命中次数',
		'get_misses'				=>'总未命中次数',
		'evictions'					=>'为获取空闲内存而删除的items数<br />（分配给Memcached的空间用满后需要删除旧的items来得到空间分配给新的items）',
		'bytes_read'				=>'总读取字节数（请求字节数）',
		'bytes_written'				=>'总发送字节数（结果字节数）',
		'limit_maxbytes'			=>'分配给Memcached的内存大小（字节）',
		'threads'					=>'当前线程数'
	);
	?>
	<?php */?>
	<?php
	}
}

function wpjam_memcached_usage_dashboard_widget_callback($dashboard, $meta_box){
	global $wp_object_cache;

	$mc		= $wp_object_cache->get_mc('defaul');
	$stats	= $mc->getStats();
	?>

	<?php foreach ($stats as $key => $details) { 
		$counts		= array();

		$counts[]	= array('label'=>'命中次数','count'=>$details['get_hits']);
		$counts[]	= array('label'=>'未命中次数','count'=>$details['get_misses']);

		$total		= $details['cmd_get'];

		wpjam_donut_chart($counts, array('title'=>'命中率','total'=>$total,'chart_width'=>150,'table_width'=>320));

		$counts		= array();

		$counts[]	= array('label'=>'已用内存','count'=>round($details['bytes']/(1024*1024),2));
		$counts[]	= array('label'=>'剩余内存','count'=>round(($details['limit_maxbytes']-$details['bytes'])/(1024*1024),2));

		$total		= round($details['limit_maxbytes']/(1024*1024),2);

		wpjam_donut_chart($counts, array('title'=>'内存使用','total'=>$total,'chart_width'=>150,'table_width'=>320));
		?>
		<h3>Memcached效率</h3>
		<table class="widefat">
			<tbody>
				<tr class="alternate">
					<th>每秒命中次数</th>
					<td><?php echo round($details['get_hits']/$details['uptime'],2);?></td>
				</tr>
				<tr>
					<th>每秒未命中次数</th>
					<td><?php echo round($details['get_misses']/$details['uptime'],2);?></td>
				</tr>
				<tr class="alternate">
					<th>每秒获取请求次数</th>
					<td><?php echo round($details['cmd_get']/$details['uptime'],2);?></td>
				</tr>
				<tr>
					<th>每秒设置请求次数</th>
					<td><?php echo round($details['cmd_set']/$details['uptime'],2);?></td>
				</tr>
			</tbody>
		</table>
		<?php
	}
}
