<?php
// 设置菜单
add_filter('wpjam_basic_sub_pages', 'wpjam_crons_admin_pages');
function wpjam_crons_admin_pages($wpjam_pages){
	$wpjam_pages['wpjam-crons']	= array('menu_title'=>'定时作业');
	return $wpjam_pages;
}

function wpjam_crons_page_load(){
	global $wpjam_list_table;

	$action			= isset($_GET['action'])?$_GET['action']:'';

	if(in_array($action, array('add','edit','set','bulk-edit'))) return;

	$columns		= array(
		'hook'			=> 'Hook',
		'args'			=> '参数',
		'timestamp'		=> '下次运行',
		'interval'		=> '频率'
	);

	$wpjam_list_table = wpjam_list_table( array(
		'plural'			=> 'crons',
		'singular' 			=> 'cron',
		'columns'			=> $columns,
		'actions_column'	=> 'hook'
	) );
}

function wpjam_crons_page(){
	
	$action = isset($_GET['action'])?$_GET['action']:'';

	if($action == 'edit' || $action == 'add' ){
		wpjam_cron_edit_page();
	}else{
		wpjam_cron_list_page();
	}
}

function wpjam_cron_list_page(){
	global $current_admin_url, $wpjam_list_table, $wp_filter;

	$wp_crons = _get_cron_array();

	$action = $wpjam_list_table->current_action();

	if($action){
		if( !current_user_can( 'manage_options' )){
			ob_clean();
			wp_die('无权限');
		}
	
		if($action == 'delete'){
			if(!empty($_GET['sig'])){
				check_admin_referer('delete-'.$wpjam_list_table->get_singular().'-'.$_GET['sig']);

				if(isset($wp_crons[$_GET['timestamp']][$_GET['hook']][$_GET['sig']])){
					$data = $wp_crons[$_GET['timestamp']][$_GET['hook']][$_GET['sig']];
					wp_unschedule_event( $_GET['timestamp'], $_GET['hook'], $data['args'] );
				}

				$redirect_to = add_query_arg( array( 'deleted' => 'true' ), wpjam_get_referer() );

				wp_redirect($redirect_to);
			}
		}elseif($action == 'do'){
			if(!empty($_GET['sig'])){
				check_admin_referer('do-'.$wpjam_list_table->get_singular().'-'.$_GET['sig']);

				if(isset($wp_crons[$_GET['timestamp']][$_GET['hook']][$_GET['sig']])){
					$data = $wp_crons[$_GET['timestamp']][$_GET['hook']][$_GET['sig']];
					do_action_ref_array($_GET['hook'], $data['args']);
				}

				$redirect_to = add_query_arg( array( 'updated' => 'true' ), wpjam_get_referer() );

				wp_redirect($redirect_to);
			}

		}elseif($action == 'del-all'){
			if( !current_user_can( 'manage_options' )){
				ob_clean();
				wp_die('无权限');
			}

			delete_option('cron');
			wpjam_admin_add_error('所有定时作业删除成功');
		}
	}

	?>
	<h2>定时作业</h2>

	<?php

	echo '现在时间：'.current_time('mysql');

	$wp_crons	= _get_cron_array();
	$new_crons	= array();

	foreach ($wp_crons as $timestamp => $wp_cron) {
		foreach ($wp_cron as $hook => $dings) {

			foreach( $dings as $sig=>$data ) {
				if(empty($wp_filter[$hook])){
					wp_unschedule_event($timestamp, $hook, $data['args']);	// 系统不存在的定时作业，自动清理
				}else{
					$new_cron = array(
						'id'			=> $sig,
						'timestamp'		=> get_date_from_gmt( date('Y-m-d H:i:s', $timestamp) ),
						'hook'			=> $hook,
						'args'			=> $data['args']?implode(',', $data['args']):'',
						'interval'		=> isset($data['interval'])?(__($data['schedule']).'（'.$data['interval']).'）':'',
						'row_actions'	=> array(
							'do'		=> '<a href="'.esc_url(wp_nonce_url($current_admin_url.'&action=do&timestamp='.$timestamp.'&hook='.$hook.'&sig='.$sig, 'do-'.$wpjam_list_table->get_singular().'-'.$sig)).'">立即执行</a>',
							'delete'	=> '<a href="'.esc_url(wp_nonce_url($current_admin_url.'&action=delete&timestamp='.$timestamp.'&hook='.$hook.'&sig='.$sig, 'delete-'.$wpjam_list_table->get_singular().'-'.$sig)).'">删除</a>',
						)
					);

					$new_crons[]	= $new_cron;
				}
			}
		}
	}

	$wpjam_list_table->prepare_items($new_crons, count($new_crons));
	$wpjam_list_table->display(array('search'=>false));
}