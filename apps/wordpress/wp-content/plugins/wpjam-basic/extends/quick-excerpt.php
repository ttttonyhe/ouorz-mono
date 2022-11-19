<?php
/*
Name: 摘要快速编辑
URI: https://blog.wpjam.com/m/quick-excerpt/
Description: 后台文章列表的快速编辑支持编辑摘要。
Version: 1.0
*/
if(is_admin()){
	wpjam_register_builtin_page_load('quick-excerp', [
		'base'		=> 'edit', 
		'callback'	=> function($screen){
			if(!post_type_supports($screen->post_type, 'excerpt')){
				return;
			}

			if(!wp_doing_ajax()){
				$scripts = <<<'EOT'
jQuery(function($){
	$('body').on('quick_edit', '#the-list', function(event, id){
		let edit_row	= $('#edit-'+id);

		if($('textarea[name="the_excerpt"]', edit_row).length == 0){
			$('.inline-edit-date', edit_row).before('<label><span class="title">摘要</span><span class="input-text-wrap"><textarea cols="22" rows="2" name="the_excerpt"></textarea></span></label>');
			$('textarea[name="the_excerpt"]', edit_row).val($('#inline_'+id+' div.post_excerpt').text());
		}
	});
});
EOT;
				wp_add_inline_script('jquery', $scripts);
			}
			
			add_filter('wp_insert_post_data', function($data){
				if(isset($_POST['the_excerpt'])){
					$data['post_excerpt']   = $_POST['the_excerpt'];
				}
					
				return $data;
			});
			
			add_filter('add_inline_data', function($post){
				echo '<div class="post_excerpt">'.esc_textarea(trim($post->post_excerpt)).'</div>';
			});
		}
	]);
}