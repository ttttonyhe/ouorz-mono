<?php
add_action( 'admin_init', 'wpjam_stats_admin_init' );
function wpjam_stats_admin_init(){
	// 在 WordPress 中所有的时间都是 UTC 时间，所以要把时间先转成 GMT 的时间。
	global $wpjam_stats_labels;

	$default					= date('Y-m-d',current_time('timestamp')-(DAY_IN_SECONDS*30));
	$wpjam_start_date			= wpjam_stats_get_var('start-date', $default);
	$wpjam_start_timestamp		= strtotime(get_gmt_from_date($wpjam_start_date.' 00:00:00'));

	$default					= date('Y-m-d',current_time('timestamp'));
	$wpjam_end_date 			= wpjam_stats_get_var('end-date', $default);
	$wpjam_end_timestamp		= strtotime(get_gmt_from_date($wpjam_end_date.' 23:59:59'));

	$default					= date('Y-m-d',current_time('timestamp')-DAY_IN_SECONDS);
	$wpjam_date					= wpjam_stats_get_var('date', $default);
	$wpjam_timestamp			= strtotime(get_gmt_from_date($wpjam_date.' 00:00:00'));

	$time_diff					= strtotime($wpjam_end_date)-strtotime($wpjam_start_date);
	$default 					= date('Y-m-d', strtotime($wpjam_start_date)-DAY_IN_SECONDS-$time_diff);
	$wpjam_start_date_2			= wpjam_stats_get_var('start-date-2', $default);
	$wpjam_start_timestamp_2	= strtotime(get_gmt_from_date($wpjam_start_date_2.' 00:00:00'));

	$default 					= date('Y-m-d',strtotime($wpjam_start_date)-DAY_IN_SECONDS);
	$wpjam_end_date_2			= wpjam_stats_get_var('end-date-2', $default);
	$wpjam_end_timestamp_2		= strtotime(get_gmt_from_date($wpjam_end_date_2.' 23:59:59'));

	$default 					= '按天';
	$wpjam_current_date_type	= wpjam_stats_get_var('date-type', $default);
	$wpjam_current_date_type 	= ($wpjam_current_date_type =='显示')?'按天':$wpjam_current_date_type;

	$wpjam_date_formats			= wpjam_get_date_formats();

	$wpjam_date_format			= isset($wpjam_date_formats[$wpjam_current_date_type])?$wpjam_date_formats[$wpjam_current_date_type]:'%Y-%m-%d';
	
	$wpjam_compare				= wpjam_stats_get_var('compare', 1);

	$compare_label		= $wpjam_start_date.' '.$wpjam_end_date;
	$compare_label_2	= $wpjam_start_date_2.' '.$wpjam_end_date_2;

	$wpjam_stats_labels = compact('wpjam_start_date','wpjam_end_date','wpjam_date','wpjam_start_timestamp','wpjam_end_timestamp','wpjam_start_date_2','wpjam_end_date_2','wpjam_start_timestamp_2','wpjam_end_timestamp_2','wpjam_date_format','wpjam_current_date_type','wpjam_compare','compare_label','compare_label_2');
}

add_action('admin_head','wpjam_stats_admin_head',999);
function wpjam_stats_admin_head(){ ?>
<?php add_thickbox(); ?>
<script type="text/javascript">
jQuery(function($){
	if (self != top) {
		<?php if($_POST){ ?>parent.window.tb_reload = 1;<?php } ?>
	}else{
		window.tb_reload	= '';
		var old_tb_remove	= window.tb_remove;

		window.tb_remove	= function() {
			old_tb_remove();
			if (window.tb_reload) {
				window.location.reload();
			}
		};
	}
});
</script>
<link rel="stylesheet" href="//cdn.staticfile.org/morris.js/0.5.1/morris.css" />
<script type='text/javascript' src="//cdn.staticfile.org/raphael/2.1.2/raphael-min.js"></script>
<script type='text/javascript' src="//cdn.staticfile.org/morris.js/0.5.1/morris.min.js"></script>
<?php
}

function wpjam_stats_header($args=''){
	global $wpdb, $plugin_page, $current_admin_url, $wpjam_stats_labels;
	$wpdb->query("SET time_zone = '+8:00';");

	extract($wpjam_stats_labels);
	extract( wp_parse_args( $args, array( 
		'show_date_type'	=> false,
		'show_compare'		=> false,
		'show_start_date'	=> true
	) ) );

	$current_type		= isset($_GET['type'])?$_GET['type']:'-1';
	$current_type		= ($current_type == 'all')?'-1':$current_type;
	$action 			= ($current_type == -1)?$current_admin_url:$current_admin_url.'&type='.$current_type;

	?>
	<div style="margin:20px 0;">
		<form method="POST" action="<?php echo $action; ?>" target="_self">
		<?php if($show_start_date){ ?>
			日期：
			<input type="date"		name="start-date"	id="start-date"	value="<?php echo $wpjam_start_date;?>"	size="11" /> - 
			<input type="date"		name="end-date"		id="end-date"	value="<?php echo $wpjam_end_date;?>"	size="11" />
		<?php } else { ?>
			日期：
			<input type="date"		name="date"			id="date"		value="<?php echo $wpjam_date;?>"		size="11" />
		<?php } ?>
		<?php if($show_date_type){ ?>
			<?php foreach (wpjam_get_date_formats($show_date_type) as $wpjam_date_type => $wpjam_date_format) { $class = ($wpjam_current_date_type == $wpjam_date_type)?'button button-primary':'button';?>
			<input type="submit"	name='date-type'	value="<?php echo $wpjam_date_type; ?>" class="<?php echo $class;?>">
			<?php } ?>
		<?php }else{ ?> 
			<input type="submit"	name='date-type'	value="显示" class="button button-secondary">
		<?php }?>
		<?php do_action('wpjam_stats_header');?>
		<?php if($current_type!=-1 && $show_compare){?>
			<br />对比：
			<input type="date"		name="start-date-2"	id="start-date-2"value="<?php echo $wpjam_start_date_2;?>"	size="11" /> -
			<input type="date"		name="end-date-2"	id="end-date-2"	value="<?php echo $wpjam_end_date_2;?>"		size="11" />
			<input type="checkbox"	name="compare"		id="compare"	value="1" <?php if($wpjam_compare){ echo 'checked="checked"'; } ?> />
		<?php } ?>
		</form>
	</div>
	<div class="clear"></div>
<?php
}

function wpjam_sub_summary($tabs){
	global $wpdb;
	?>
	<h2 class="nav-tab-wrapper nav-tab-small">
	<?php foreach ($tabs as $key => $tab) { ?>
		<a class="nav-tab" href="javascript:;" id="tab-title-<?php echo $key;?>"><?php echo $tab['name'];?></a>   
	<?php }?>
	</h2>

	<?php foreach ($tabs as $key => $tab) { ?>
	<div id="tab-<?php echo $key;?>" class="div-tab" style="margin-top:1em;">
	<?php

	$counts = $wpdb->get_results($tab['counts_sql']);
	$total  = $wpdb->get_var($tab['total_sql']);
	$labels = isset($tab['labels'])?$tab['labels']:'';
	$base   = isset($tab['link'])?$tab['link']:'';

	$new_counts = $new_types = array();
	foreach ($counts as $count) {
		$link   = $base?($base.'&'.$key.'='.$count->label):'';

		if(is_super_admin() && $tab['name'] == '手机型号'){
			$label  = ($labels && isset($labels[$count->label]))?$labels[$count->label]:'<span style="color:red;">'.$count->label.'</span>';
		}else{
			$label  = ($labels && isset($labels[$count->label]))?$labels[$count->label]:$count->label;
		}

		$new_counts[] = array(
			'label' => $label,
			'count' => $count->count,
			'link'  => $link
		);
	}

	wpjam_donut_chart($new_counts, array('total'=>$total,'show_line_num'=>1,'table_width'=>'420'));
	
	?>
	</div>
	<?php }?>

	<?php
}

function wpjam_line_chart($counts_array, $labels, $args=array(), $type = 'Line'){
	if(!$counts_array) return;
	
	extract( wp_parse_args($args, array(
		'chart_id'		=>'daily-chart',
		'day_label'		=>'时间',
		'day_key'		=>'day',
		'day_labels'	=>array(),
		'show_table'	=>true, 
		'show_chart'	=>true, 
		'show_sum'		=>true,
		'show_avg'		=>true,
	)));
	?>
	<?php if($show_chart){ ?>

	<?php 
	$morris_datas	= $morris_data =  array();
	
	$labels2 = $labels;

	foreach ($labels2 as $key => $value) {
		if(strpos($value,'%') !== false || strpos($value,'#') !== false){ // %,# 数据不写入图
			unset($labels2[$key]);
		}
	}

	$morris_ykeys	= array_keys($labels2);
	$morris_labels	= array_values($labels2);

	foreach ($counts_array as $day => $counts) {
		if(strpos($day,'%') !== false || strpos($day,'#') !== false){ // %,# 数据不写入图
			continue;
		}
		$day = ($day_labels && isset($day_labels[$day]))?$day_labels[$day]:$day;
		$counts			= (array)$counts;
		$morris_data	=  array();
		$morris_data[]	= '"'.$day_key.'": "'.$day.'"';
		foreach($morris_ykeys as $morris_ykey){
			$count			= isset($counts[$morris_ykey])?$counts[$morris_ykey]:0;
			$morris_data[]	= '"'.$morris_ykey.'": "'.$count.'"';
		}
		$morris_data		= '{'.implode(',', $morris_data).'}';
		$morris_datas[]		= $morris_data;
	}

	$morris_data_string		= "\n".implode(",\n", $morris_datas)."\n";
	$morris_ykey_string		= "'".implode("','", $morris_ykeys)."'";
	$morris_label_string	= "'".implode("','", $morris_labels)."'";

	?>
	
	<div id="<?php echo $chart_id?>"></div>

	<script type="text/javascript">
		Morris.<?php echo $type;?>({
			element:	'<?php echo $chart_id;?>',
			data:		[<?php echo $morris_data_string;?>],
			xkey:		'<?php echo $day_key;?>',
			ykeys:		[<?php echo $morris_ykey_string;?>],
			labels:		[<?php echo $morris_label_string;?>]//,
			//lineColors: [<?php //echo wpjam_get_chart_colors();?>]
		});
	</script>
	<?php } ?>

	<?php if($show_table){  ?>
	<?php
	$morris_ykeys	= array_keys($labels); 
	$morris_labels	= array_values($labels);
	$totol_array	= array();

	foreach($morris_ykeys as $morris_ykey){
		$totol_array[$morris_ykey]	= 0;
	}
	?>
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th><?php echo $day_label;?></th>
				<?php foreach ($morris_labels as $morris_label) { ?>
				<th><?php echo $morris_label;?></th>	
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($counts_array as $day=>$counts) {?> 
			<?php 
			$alternate	= empty($alternate)?'alternate':''; 
			$counts		= (array)$counts;
			$day		= ($day_labels && isset($day_labels[$day]))?$day_labels[$day]:$day;
			?>
			<tr class="<?php echo $alternate; ?>">
				<td><?php echo $day; ?></td>
				<?php foreach($morris_ykeys as $morris_ykey){ $count = isset($counts[$morris_ykey])?$counts[$morris_ykey]:0; $totol_array[$morris_ykey] += $count;?>
				<td><?php echo $count; ?></td>
				<?php } ?>
			</tr>
			<?php } ?>
			<?php if(count($counts_array) > 1){ ?>
			<?php if($show_sum){ ?>
			<?php $alternate = empty($alternate)?'alternate':'';?>
			<tr class="<?php echo $alternate; ?>">
				<td>累加</td>
				<?php foreach($morris_ykeys as $morris_ykey){ ?>
				<td><?php if(isset($counts[$morris_ykey]) && strpos($labels[$morris_ykey], '%')===false && strpos($labels[$morris_ykey], '#')===false){
					echo $totol_array[$morris_ykey];
				} ?></td>
				<?php } ?>
			</tr>
			<?php } ?>
			<?php if($show_avg){ ?>
			<?php $alternate = empty($alternate)?'alternate':'';?>
			<tr class="<?php echo $alternate; ?>">
				<td>平均</td>
				<?php foreach($morris_ykeys as $morris_ykey){ ?>
				<td><?php if(isset($counts[$morris_ykey]) && strpos($labels[$morris_ykey], '%')===false && strpos($labels[$morris_ykey], '#')===false){
					echo round($totol_array[$morris_ykey]/count($counts_array));
				} ?></td>
				<?php } ?>
			</tr>
			<?php } ?>
			<?php } ?>
		</tbody>
	</table>
	<?php } ?>
	<?php
}

function wpjam_bar_chart($counts_array, $labels, $args=array()){
	wpjam_line_chart($counts_array, $labels, $args, 'Bar');
}

function wpjam_donut_chart($counts, $args=array()) {
	global $current_admin_url;
	if(!$counts) return;

	global $chart_count;
	if(empty($chart_count)){
		$chart_count = 1;
	}else{
		$chart_count ++;
	}

	extract(wp_parse_args($args, array(
		'total'			=>0,
		'title'			=>'名称',
		'show_link'		=>false,
		'total_link'	=>$current_admin_url,
		'table_width'	=>'300',
		'chart_width'	=>'240', 
		'show_line_num'	=>false,
		'labels'		=>array()
	) ) );
	?>
	<div style="display:table; margin-bottom:20px;">

		<div style="display: table-row;">

			<div style="display: table-cell; float:left; width:<?php echo $table_width; ?>px; margin-right:20px;">
				<table class="widefat" cellspacing="0">
					<thead>
						<tr>
							<?php if($show_line_num){?><th style="width:40px;">排名</th><?php } ?>
							<th><?php echo $title; ?></th>
							<th style="width:25%;">数量</th>
							<?php if($total){ ?><th style="width:25%;">比例</th><?php } ?>
						</tr>
					</thead>
					<tbody>
					<?php 
					$data = array();
					$i = 0;
					?>
					<?php foreach ($counts as $count) { $alternate = empty($alternate)?'alternate':''; $i++; $count = (array)$count;?>
						<?php 
						$label 	= isset($count['label'])?$count['label']:'/';
						$link	= isset($count['link'])?$count['link']:'';
						$link = ($show_link)?$current_admin_url.'&type='.$label:'';

						$label 	= ($labels && isset($labels[$label]))?$labels[$label]:$label;
						
						$count 	= $count['count'];
						?>
						<?php if($i<=30){$data []= '{"label": "'.$label.'", "value": '.$count.' }';} ?>
						<tr class="<?php echo $alternate; ?>">
							<?php if($show_line_num){?><td><?php echo $i;?></td><?php } ?>
							<td><?php 
							if($link){
								echo '<a href="'.$link.'">'.$label.'</a>';
							}else{
								echo $label;
							}; 
							?></td>
							<td><?php echo $count; ?></td>
							<?php if($total){ ?><td><?php echo round($count/$total*100,2).'%';?></td><?php } ?>
						</tr>
					<?php }  ?>
						<?php $alternate = empty($alternate)?'alternate':'';?>
						<?php if($total){ ?>
						<tr class="<?php echo $alternate; ?>">
							<?php if($show_line_num) {?><td></td><?php } ?>
							<td><?php
							if($show_link){
								echo '<a href="'.$total_link.'">所有</a>';
							}else{
								echo '所有';
							}
							?></td>
							<td><?php echo $total; ?></td>
							<td>100%</td>
						</tr>
						<?php } ?>
					<?php $data = "\n".implode(",\n", $data)."\n";?>
					</tbody>
				</table>
			</div>

			<?php if($chart_width) {?>
			<div id="chart-<?php echo $chart_count; ?>" style="display: table-cell; width:<?php echo $chart_width; ?>px; height:<?php echo $chart_width; ?>px; float:left;"></div>
			<script type="text/javascript">
				Morris.Donut({
				  element: 'chart-<?php echo $chart_count; ?>',
				  data: [<?php echo $data;?>]//,
				  //colors: [<?php //echo wpjam_get_chart_colors(); ?>]
				});
			</script>
			<?php }?>
		</div>
	</div>
	<?php
}

function wpjam_stats_get_var($key, $default){
	$cookie_key = 'wpjam-'.$key;

	if( $_SERVER['REQUEST_METHOD'] == 'POST' && $key == 'compare'){
		$_POST[$key] = isset($_POST[$key])?$_POST[$key]:0;
	}

	if(isset($_POST[$key])){
		$var = $_POST[$key];
		setcookie($cookie_key, $var, time()+HOUR_IN_SECONDS);
		$_COOKIE[$cookie_key] = $var;
	}elseif(isset($_COOKIE[$cookie_key])) {
		$var = $_COOKIE[$cookie_key];
	}else{
		$var = $default;
	}

	return $var;
}

function wpjam_get_date_formats($filter=1){

	if($filter == 1){
		return array(
			'按分钟'	=> '%Y-%m-%d %H:%i',
			'按小时'	=> '%Y-%m-%d %H:00',
			'按天'	=> '%Y-%m-%d',
			'按周'	=> '%Y%U',
			'按月'	=> '%Y-%m'
		);
	}elseif($filter == 2){
		return array(
			'按天'	=> '%Y-%m-%d',
			'按周'	=> '%Y%U',
			'按月'	=> '%Y-%m'
		);
	}
}