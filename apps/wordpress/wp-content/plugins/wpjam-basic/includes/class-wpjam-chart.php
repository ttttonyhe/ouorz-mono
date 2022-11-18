<?php
class WPJAM_Chart{
	private static $parameters	= [];
	private static $args		= [ 
		'show_form'			=> true,
		'show_date_type'	=> false,
		'show_compare'		=> false,
		'show_start_date'	=> true
	];

	private static $enqueued	= false;

	public static function line($counts_array, $labels, $args=[], $type='Line'){
		if(!$counts_array){
			return;
		}

		self::enqueue_script();

		global $current_admin_url;
		
		$args	= wp_parse_args($args, [
			'chart_id'		=>'daily-chart',
			'day_label'		=>'时间',
			'day_key'		=>'day',
			'day_labels'	=>[],
			'total_link'	=>$current_admin_url,
			'show_link'		=>false,
			'show_table'	=>true, 
			'show_chart'	=>true, 
			'show_sum'		=>true,
			'show_avg'		=>true,
		]);

		$day_labels	= $args['day_labels'];
		$day_label	= $args['day_label'];
		$day_key	= $args['day_key'];

		if($args['show_chart']){
			$morris_datas	= $morris_data = [];
			$labels2		= $labels;

			foreach ($labels as $key => $value) {
				if(strpos($value,'%') === false && strpos($value,'#') === false){ // %,# 数据不写入图
					$labels2[$key]		= $value;
					$morris_ykeys[]		= $key;
					$morris_labels[]	= $value;
				}
			}

			$data	= [];

			foreach ($counts_array as $day => $counts) {
				if(strpos($day,'%') === false && strpos($day,'#') === false){
					$counts	= (array)$counts;
					$day	= ($day_labels && isset($day_labels[$day])) ? $day_labels[$day] : $day;
					$item 	= [];

					$item[$day_key]	= $day;

					foreach($morris_ykeys as $morris_ykey){
						$item[$morris_ykey]	= $counts[$morris_ykey] ?? 0;
					}

					$data[]	= $item;	
				}
			}

			$morris	= [
				'element'	=> $args['chart_id'],
				'data'		=> $data,
				'xkey'		=> $day_key,
				'ykeys'		=> $morris_ykeys,
				'labels'	=> $morris_labels,
			];

			echo '<div id="'.$args['chart_id'].'"></div>';
			echo '
			<script type="text/javascript">
			jQuery(function($){
				Morris.'.$type.'('.wpjam_json_encode($morris).');
			});
			</script>
			';
		}

		if($args['show_table']){ 
			$totol_array	= []; 
			$toggle_row		= '<button type="button" class="toggle-row"><span class="screen-reader-text">显示详情</span></button>';
			
			echo '<table class="wp-list-table widefat striped" cellspacing="0">';
			echo '<thead>';
			echo '<tr>';
			echo '<th scope="col" id="'.$day_key.'" class="column-'.$day_key.' column-primary">'.$day_label.'</th>';

			foreach ($labels as $morris_ykey => $morris_label) { 
				if(strpos($labels[$morris_ykey], '%')===false && strpos($labels[$morris_ykey], '#')===false){
					$totol_array[$morris_ykey]	= 0;
				}

				echo '<th  scope="col" id="'.$morris_ykey.'" class="column-'.$morris_ykey.'">'.$morris_label.'</th>';
			}

			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

			foreach ($counts_array as $day=>$counts) {
				$counts		= (array)$counts;
				$day_value	= ($day_labels && isset($day_labels[$day]))?$day_labels[$day]:$day;

				if($args['show_link']){
					$day_value	= '<a href="'.$args['total_link'].'&'.$day_key.'='.$day.'">'.$day_value.'</a>';
				}

				echo '<tr>';	
				echo '<td class="column-'.$day_key.' column-primary" data-colname="'.$day_label.'">'.$day_value.$toggle_row.'</td>';
				
				foreach($labels as $morris_ykey => $morris_label){ 
					$count	= $counts[$morris_ykey] ?? 0; 
						
					if(isset($totol_array[$morris_ykey])){
						$totol_array[$morris_ykey] += $count;
					}

					echo '<td class="column-'.$morris_ykey.'" data-colname="'.$morris_label.'">'.$count.'</td>';
				}
				echo '</tr>';
			}

			if(count($counts_array) > 1){

				if($args['show_sum']){
					echo '<tr>';
					echo '<td class="column-'.$day_key.' column-primary" data-colname="'.$day_label.'">累加'.$toggle_row.'</td>';

					foreach($labels as $morris_ykey => $morris_label){
						echo '<td class="column-'.$morris_ykey.'" data-colname="'.$morris_label.'">';

						if(isset($totol_array[$morris_ykey])){
							echo $totol_array[$morris_ykey];
						}

						echo '</td>';
					}
					echo '</tr>';
				}

				if($args['show_avg']){
					echo '<tr>';
					echo '<td class="column-'.$day_key.' column-primary" data-colname="'.$day_label.'">平均'.$toggle_row.'</td>';

					$number	= count($counts_array);

					foreach($labels as $morris_ykey => $morris_label){
						echo '<td class="column-'.$morris_ykey.'" data-colname="'.$morris_label.'">';

						if(isset($totol_array[$morris_ykey])){
							echo round($totol_array[$morris_ykey]/$number);
						}

						echo '</td>';
					}
					echo '</tr>';
				}
			}

			echo '</tbody>';
			echo '</table>';
		}
	}

	public static function donut($counts, $args=[]){
		if(!$counts){
			return;
		}

		self::enqueue_script();

		global $current_admin_url;

		$args	= wp_parse_args($args, [
			'chart_id'		=>'',
			'total'			=>0,
			'title'			=>'名称',
			'key'			=>'type',
			'show_link'		=>false,
			'total_link'	=>$current_admin_url,
			'table_width'	=>'300',
			'chart_width'	=>'240', 
			'show_line_num'	=>false,
			'labels'		=>[]
		]);

		if($args['chart_id']){
			$chart_id	= $args['chart_id'];
		}else{
			static $chart_count;
			if(empty($chart_count)){
				$chart_count = 1;
			}else{
				$chart_count ++;
			}

			$chart_id	= 'chart_'.$chart_count;
		}

		$labels	= $args['labels'];

		echo '<div style="display:table; margin-bottom:20px;">';
		echo '<div style="display: table-row;">';
		echo '<div style="display: '.($args['table_width'] ? 'table-cell' : 'none').'; float:left; width:'.$args['table_width'].'px; margin-right:20px;">';
		echo '<table class="wp-list-table widefat striped" cellspacing="0">';
		echo '<thead>';
		echo '<tr>';
		
		if($args['show_line_num']){
			echo '<th style="width:40px;">排名</th>';
		}

		echo '<th>'.$args['title'].'</th>';
		echo '<th style="width:25%;">数量</th>';
		
		if($args['total']){
			echo '<th style="width:25%;">比例</th>';
		}

		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		$data	= [];
		$i		= 0;

		foreach ($counts as $count) { 
			$i++; 

			$count	= (array)$count;
			$label 	= $count['label'] ?? '/';
			$link	= $count['link'] ?? '';

			$link	= '';

			if($args['show_link']){
				$value	= $count[$args['key']] ?? $label;
				$link	= $args['total_link'].'&'.$args['key'].'='.$value;
			}

			$label 	= ($labels && isset($labels[$label])) ? $labels[$label] : $label;
			$count 	= $count['count'];

			if($i<=30){
				$data[]= ['label'=>$label, 'value'=>$count];
			}

			echo '<tr>';

			if($args['show_line_num']){
				echo '<td>'.$i.'</td>'; 
			} 

			echo '<td>'.($link ? '<a href="'.$link.'">'.$label.'</a>' : $label).'</td>';
			echo '<td>'.$count.'</td>';

			if($args['total']){
				echo '<td>'.round($count/$args['total']*100,2).'%'.'</td>';
			}

			echo '</tr>';
		}

		if($args['total']){
			echo '<tr>';

			if($args['show_line_num']){
				echo '<td> </td>'; 
			}

			echo '<td>'.($args['show_link'] ? '<a href="'.$args['total_link'].'">所有</a>' : '所有').'</td>';
			echo '<td>'.$args['total'].'</td>';

			if($args['total']){
				echo '<td>100%</td>';
			}

			echo '</tr>';
		}
		
		echo '</tbody>';
		echo '</table>';
		echo '</div>';

		$morris	= ['element'=>$chart_id, 'data'=>$data];

		if($args['chart_width']){
			echo '<div id="'.$chart_id.'" style="display: table-cell; width:'.$args['chart_width'].'px; height:'.$args['chart_width'].'px; float:left;"></div>';

			echo '
			<script type="text/javascript">
			jQuery(function($){
				Morris.Donut('.wpjam_json_encode($morris).');
			});
			</script>
			';
		}

		echo '</div>';
		echo '</div>';
	}

	public static function init($args=[]){
		if(self::$parameters){
			return;
		}

		$args	= is_array($args) ? $args : [];

		self::$args	= wp_parse_args($args, self::$args);

		if(!self::$args['show_form']){
			return;
		}
		
		$offset	= (int)get_option('gmt_offset');
		$offset	= $offset >= 0 ? '+'.$offset.':00' : $offset.':00';
		
		$GLOBALS['wpdb']->query("SET time_zone = '{$offset}';");

		$current_time	= current_time('timestamp');

		if(self::$args['show_start_date']){
			self::set_parameter('start_date',	date('Y-m-d', $current_time - DAY_IN_SECONDS*30));
			self::set_parameter('end_date',		date('Y-m-d', $current_time));

			if(self::$args['show_compare']){
				$time_diff	= self::get_parameter('end_timestamp') - self::get_parameter('start_timestamp');

				self::set_parameter('start_date_2',	date('Y-m-d', self::get_parameter('start_timestamp') - DAY_IN_SECONDS - $time_diff));
				self::set_parameter('end_date_2',	date('Y-m-d', self::get_parameter('start_timestamp') - DAY_IN_SECONDS));
				
				self::set_parameter('compare',		0);
			}
		}else{
			self::set_parameter('date',	date('Y-m-d', $current_time - DAY_IN_SECONDS));
		}

		if(self::$args['show_date_type']){
			self::set_parameter('date_type', '按天');
		}
	}

	public static function get_parameter($key){
		if($key == 'start_timestamp'){
			return strtotime(get_gmt_from_date(self::get_parameter('start_date').' 00:00:00'));
		}elseif($key == 'timestamp'){
			return strtotime(get_gmt_from_date(self::get_parameter('date').' 00:00:00'));
		}elseif($key == 'end_timestamp'){
			return strtotime(get_gmt_from_date(self::get_parameter('end_date').' 23:59:59'));
		}elseif($key == 'start_timestamp_2'){
			return strtotime(get_gmt_from_date(self::get_parameter('start_date_2').' 00:00:00'));
		}elseif($key == 'end_timestamp_2'){
			return strtotime(get_gmt_from_date(self::get_parameter('end_date_2').' 23:59:59'));
		}elseif($key == 'date_format'){
			$date_type		= self::get_parameter('date_type');
			$date_type		= $date_type =='显示' ? '按天' : $date_type;

			$date_formats	= self::get_date_formats();

			return $date_formats[$date_type] ?? '%Y-%m-%d';
		}elseif($key == 'date_type'){
			if(self::$args['show_date_type']){
				return self::$parameters[$key] ?? '按天';
			}else{
				return '按天';
			}
		}else{
			return self::$parameters[$key] ?? '';
		}
	}

	public static function set_parameter($key, $default){
		if($value = wpjam_get_parameter($key, ['method'=>'POST'])){
			self::$parameters[$key]	= $value;
			wpjam_set_cookie($key, $value, HOUR_IN_SECONDS);
		}elseif(isset($_COOKIE[$key])) {
			self::$parameters[$key]	= wp_unslash($_COOKIE[$key]);
		}else{
			self::$parameters[$key]	= $default;
		}
	}

	public static function form(){
		global $current_admin_url;

		if(!self::$args['show_form']){
			return;
		}

		$current_type	= $_GET['type'] ?? '-1';
		$current_type	= $current_type == 'all' ? '-1' : $current_type;
		$action 		= $current_type == -1 ? $current_admin_url : $current_admin_url.'&type='.$current_type;

		$fields	= [];

		if(self::$args['show_start_date']){
			$fields['start_date']	= ['title'=>'日期： ',	'type'=>'date',	'description'=>'- ',	'value'=>self::get_parameter('start_date')];
			$fields['end_date']		= ['title'=>'',			'type'=>'date',	'description'=>' ',	'value'=>self::get_parameter('end_date')];
		}else{
			$fields['date']			= ['title'=>'',			'type'=>'date',	'description'=>' ',	'value'=>self::get_parameter('date')];
		}

		if(self::$args['show_date_type']){
			foreach (self::get_date_formats(self::$args['show_date_type']) as $date_type => $date_format) { 
				$class = self::get_parameter('date_type') == $date_type ? 'button button-primary' : 'button';
				$fields['date_type_'.$date_type]	= ['title'=>'',	'type'=>'submit',	'name'=>'date_type',	'description'=>' ',	'value'=>$date_type,	'class'=>$class];
			}
		}else{
			$fields['date_type']	= ['title'=>'',	'type'=>'submit',	'name'=>'date_type',	'description'=>' ',	'description'=>' ',	'value'=>'显示',	'class'=>'button button-secondary'];
		}

		if($current_type !=-1 && self::$args['show_start_date'] && self::$args['show_compare']){
			$fields['start_date_2']	= ['title'=>'对比： ',	'type'=>'date',		'description'=>' ',	'value'=>self::get_parameter('start_date_2')];
			$fields['end_date_2']	= ['title'=>'',			'type'=>'date',		'description'=>' ',	'value'=>self::get_parameter('end_date_2')];
			$fields['compare']		= ['title'=>'',			'type'=>'checkbox',	'description'=>' ',	'value'=>self::get_parameter('compare')];
		}

		$fields	= apply_filters('wpjam_chart_fields', $fields);

		echo '<div style="margin:20px 0;">';
		echo '<form method="POST" action="'.$action.'" target="_self">';

		wpjam_fields($fields, ['fields_type'=>'']);

		echo '</form>';
		echo '</div>';
		echo '<div class="clear"></div>';
	}

	public static function get_date_formats($filter=1){
		if($filter == 1 || is_array($filter)){
			$date_formats	= [
				'按分钟'	=> '%Y-%m-%d %H:%i',
				'按小时'	=> '%Y-%m-%d %H:00',
				'按天'	=> '%Y-%m-%d',
				'按周'	=> '%Y%U',
				'按月'	=> '%Y-%m'
			];

			if(is_array($filter)){
				return wp_array_slice_assoc($date_formats, $filter);
			}else{
				return $date_formats;
			}
		}elseif($filter == 2){
			return [
				'按天'	=> '%Y-%m-%d',
				'按周'	=> '%Y%U',
				'按月'	=> '%Y-%m'
			];
		}
	}

	public static function enqueue_script(){
		if(self::$enqueued){
			return;
		}

		self::$enqueued	= true;

		wp_enqueue_style('morris',		'https://cdn.staticfile.org/morris.js/0.5.1/morris.css');
		wp_enqueue_script('raphael',	'https://cdn.staticfile.org/raphael/2.3.0/raphael.min.js');
		wp_enqueue_script('morris',		'https://cdn.staticfile.org/morris.js/0.5.1/morris.min.js', ['raphael']);
	}
}