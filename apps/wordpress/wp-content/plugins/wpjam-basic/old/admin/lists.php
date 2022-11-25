<?php
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