<?php
$action		= get_query_var('action');

if(isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
	ob_start('ob_gzhandler'); 
}else{
	ob_start(); 
}

if($action == 'feed'){
	$post_id	= get_query_var('p');
	$views		= wpjam_get_post_total_view($post_id)+1;

	wpjam_update_post_feed_views($post_id);

	header("Content-Type: image/png");
	$im = @imagecreate(84, 24) or die("Cannot Initialize new GD image stream");
	$background_color = imagecolorallocate($im, 0, 0, 0);
	$text_color = imagecolorallocate($im, 233, 14, 91);
	imagestring($im, 2, 5, 5,  $views.' VIEWS', $text_color);

	imagepng($im);
	imagedestroy($im);
}elseif($action == 'post'){
	$post_id	= get_query_var('p');
	$views		= wpjam_get_post_total_view($post_id)+1;

	wpjam_update_post_views($post_id);

	header("Content-Type: text/javascript");
	?>
	jQuery(document).ready(function(){
		jQuery('#views-<?php echo $post_id;?>').html('浏览：<?php echo $views;?>');
	});
	<?php
}elseif($action == 'posts'){
	if(!empty($_GET['post_ids'])){
	header("Content-Type: text/javascript");
	$post_ids = $_GET['post_ids'];
	?>
	jQuery(document).ready(function(){
	<?php foreach($post_ids as $post_id){ ?>
		jQuery('#views-<?php echo $post_id;?>').html('浏览：<?php echo wpjam_get_post_total_view($post_id);?> ');
	<?php } ?>
	});
	<?php
	}
}

exit;

	