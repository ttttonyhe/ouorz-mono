jQuery(function($){

	var custom_uploader;
	if (custom_uploader) {
		custom_uploader.open();
		return;
	}

	//上传单个图片
	$('body').on("click", '.wpjam_upload', function(e) {	
		e.preventDefault();	// 阻止事件默认行为。

		var prev_input 	= $(this).prev("input");

		custom_uploader = wp.media({
			title: 		'选择图片',
			library: 	{ type: 'image' },
			button: 	{ text: '选择图片' },
			multiple:	false 
		}).on('select', function() {
			var attachment = custom_uploader.state().get('selection').first().toJSON();
			prev_input.val(attachment.url);
			$('.media-modal-close').trigger('click');
		}).open();

		return false;
	});

	//上传多个图片
	$('body').on('click', '.wpjam_multi_upload', function(e) {
		e.preventDefault();	// 阻止事件默认行为。

		var prev_input 	= $(this).prev("input");
		
		custom_uploader = wp.media({
			title: 		'选择图片',
			library: 	{ type: 'image' },
			button: 	{ text: '选择图片' },
			multiple: 	true
		}).on('select', function() {
			custom_uploader.state().get('selection').map( function( data ) {
				data	= data.toJSON();
				prev_input.before('<span><input type="text" name="'+prev_input.attr('name')+'" value="'+data.url+'" class="regular-text type-multi-image" /><a href="javascript:;" class="button del_item">删除</a><br /></span>');
			});
			$('.media-modal-close').trigger('click');
		}).open();

		prev_input.focus();

		return false;
	});

	//上传多个图片
	$('body').on('click', '.wpjam_multi_upload2', function(e) {
		e.preventDefault();	// 阻止事件默认行为。

		var prev_input 	= $(this).prev("input");
		
		custom_uploader = wp.media({
			title: 		'选择图片',
			library: 	{ type: 'image' },
			button: 	{ text: '选择图片' },
			multiple: 	true
		}).on('select', function() {
			custom_uploader.state().get('selection').map( function( data ) {
				data	= data.toJSON();
				prev_input.parent().before('<span class="mu_img"><img width="100" src="'+data.url+'" alt=""><input type="hidden" name="'+prev_input.attr('name')+'" value="'+data.id+'" class="regular-text" /><a href="javascript:;" class="del_item">—</a></span>');
			});
			$('.media-modal-close').trigger('click');
		}).open();

		prev_input.focus();

		return false;
	});

	// 添加多个选项
	$('body').on('click', 'a.wpjam_multi_text', function(){
		var prev_input 	= $(this).prev("input");
		$(this).parent().before('<span><input type="text" name="'+prev_input.attr('name')+'" value="'+prev_input.val()+'" class="regular-text type-multi-text" /><a href="javascript:;" class="button del_item">删除</a><br /></span>');
		prev_input.val('').focus();
		return false;
	});

	//  删除选项
	$('body').on('click', '.del_item', function(){
		$(this).parent().fadeOut(1000, function(){
			$(this).remove();
		});
		return false;
	});

	// 删除之前确认
	$('body').on('click', 'span.delete a', function(){
		return confirm('确定要删除吗?'); 
	});

	// Tab 切换
	if($('div.div-tab').length){
		var current_tab = '';

		if($('#current_tab').length){ // 如果是设置页面，获取当前的 current_tab 的值
			current_tab	= $('#current_tab').first().val();
		}
		
		if(current_tab == ''){ //设置第一个为当前 tab显示
			current_tab	= $('div.div-tab').first()[0].id.replace('tab-','');
		}

		var htitle		= $('#tab-title-'+current_tab).parent()[0].tagName;

		$('div.div-tab').hide();

		$('#tab-title-'+current_tab).addClass('nav-tab-active');
		$('#tab-'+current_tab).show();
		$('#current_tab').val(current_tab);

		$(htitle+' a.nav-tab').on('click',function(){

			var prev_tab	= current_tab;
			current_tab		= $(this)[0].id.replace('tab-title-','');

			$('#tab-title-'+prev_tab).removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');

			$('#tab-'+prev_tab).hide();
			$('#tab-'+current_tab).show();
			
			if($('#current_tab').length){
				$('#current_tab').val(current_tab);
			}
		});
	}

	// 删除 admin notice
	$('body').on('click', 'a.admin_notice_hide', function(){
		var notice_key	= $(this).data('key');
		var notice_time	= $(this).data('time');

		jQuery.ajax({
			type: "post",
			url: wpjam_setting.ajax_url,
			data: { 
				action:			'delete_admin_notice', 
				key:			notice_key,
				time:			notice_time,
				_ajax_nonce: 	wpjam_setting.nonce
			},
			success: function(html){
				$('#admin_notice_'+notice_key+'_'+notice_time).hide();
			}
		});
	});

	return false;
});

if (self != top) {
    document.getElementsByTagName('html')[0].className += ' TB_iframe';
}

function isset(obj){
	if(typeof(obj) != "undefined" && obj !== null) {
		return true;
	}else{
		return false;
	}
}