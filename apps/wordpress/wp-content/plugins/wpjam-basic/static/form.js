jQuery(function($){
	$.fn.extend({
		wpjam_add_attachment: function(attachment){
			$(this).next('input').val($(this).data('item_type') == 'url' ? attachment.url : attachment.id);
			$(this).find('img').remove();
			$(this).html($.wpjam_render('img', {
				img_url		: attachment.url,
				img_style	: $(this).data('img_style'),
				thumb_args	: $(this).data('thumb_args')
			})+$(this).html()).addClass('has-img');
		},

		wpjam_add_mu_attachment: function(attachment){
			let max_items	= parseInt($(this).data('max_items'));

			if(max_items && $(this).parent().parent().find('div.mu-item').length >= max_items){
				return ;
			}

			$(this).before('<div class="'+$(this).data('item_class')+'">'+$.wpjam_render('mu-img', {
				img_url		: attachment.url, 
				img_value	: ($(this).data('item_type') == 'url') ? attachment.url : attachment.id,
				thumb_args	: $(this).data('thumb_args'),
				name		: $(this).data('name')
			})+$(this).html()+'</div>');
		},

		wpjam_show_if: function(){
			this.each(function(){
				let show_if_key	= $(this).data('key');
				let show_if_val	= $(this).val();

				if($(this).is(':checkbox')){
					let wrap_id	= $(this).data('wrap_id');

					if(wrap_id){
						show_if_val	= [];

						$('#'+wrap_id+' input:checked').each(function(){
							show_if_val.push($(this).val());
						});
					}else{
						if(!$(this).is(':checked')){
							show_if_val	= 0;
						}
					}
				}else if($(this).is(':radio')) {
					if(!$(this).is(':checked')){
						return;
					}
				}

				if($(this).prop('disabled')){
					show_if_val	= null;
				}

				$('.show-if-'+show_if_key).each(function(){
					if($.wpjam_compare(show_if_val, $(this).data('show_if').compare, $(this).data('show_if').value)){
						$(this).removeClass('hidden');

						if($(this).is('option')){
							$(this).prop('disabled', false);
						}else{
							$(this).find(':input').not('.disabled').prop('disabled', false);
						}
					}else{
						$(this).addClass('hidden');

						if($(this).is('option')){
							$(this).prop('disabled', true);
							$(this).prop('selected', false);
						}else{
							$(this).find(':input').not('.disabled').prop('disabled', true);	
						}
					}

					if($(this).is('option')){
						$(this).parents('select').wpjam_show_if();
					}else{
						$(this).find('.show-if-key').wpjam_show_if();
					}
				});
			});
		},

		wpjam_autocomplete: function(){
			this.each(function(){
				if($(this).next('.wpjam-query-title').hasClass('hidden')){
					$(this).removeClass('hidden');
				}else{
					$(this).addClass('hidden');
				}

				$(this).autocomplete({
					minLength:	0,
					source: function(request, response){
						let data_type	= this.element.data('data_type');
						let query_args	= this.element.data('query_args');

						if(request.term){
							if(data_type == 'post_type'){
								query_args.s		= request.term;
							}else{
								query_args.search	= request.term;
							}
						}

						$.post(ajaxurl, $.wpjam_append_page_setting({
							action:		'wpjam-query',
							data_type:	data_type,
							query_args:	query_args
						}), function(data, status){
							response(data.items);
						});
					},
					select: function(event, ui){
						$(this).addClass('hidden').next('.wpjam-query-title').removeClass('hidden').find('.wpjam-query-text').html(ui.item.label);
						$('body').trigger('wpjam_autocomplete_selected', ui.item, $(this));
					}
				}).focus(function(){
					if(this.value == ''){
						$(this).autocomplete('search');
					}
				});
			});
		},

		wpjam_editor: function(){
			this.each(function(){
				if(wp.editor){
					let id	= $(this).attr('id');

					wp.editor.remove(id);
					wp.editor.initialize(id, $(this).data('settings'));
				}else{
					alert('请在页面加载 add_action(\'admin_footer\', \'wp_enqueue_editor\');');
				}
			});
		},

		wpjam_tabs: function(){
			this.each(function(){
				$(this).tabs({
					activate: function(event, ui){
						$('.ui-corner-top a').removeClass('nav-tab-active');
						$('.ui-tabs-active a').addClass('nav-tab-active');

						let tab_href = window.location.origin + window.location.pathname + window.location.search +ui.newTab.find('a').attr('href');
						window.history.replaceState(null, null, tab_href);
						$('input[name="_wp_http_referer"]').val(tab_href);
					},
					create: function(event, ui){
						if(ui.tab.find('a').length){
							ui.tab.find('a').addClass('nav-tab-active');
							if(window.location.hash){
								$('input[name="_wp_http_referer"]').val($('input[name="_wp_http_referer"]').val()+window.location.hash);
							}
						}
					}
				});
			});
		},

		wpjam_max_reached: function(){
			let	max_items = parseInt($(this).data('max_items'));

			if(max_items){
				if($(this).find(' > div.mu-item').length >= max_items){
					alert('最多'+max_items+'个');

					return true;
				}
			}

			return false;
		}
	});

	$.extend({
		wpjam_render: function(tmpl_id, args){
			let render	= wp.template('wpjam-'+tmpl_id);

			return render(args);
		},

		wpjam_compare: function(a, compare, b){
			if(a === null){
				return false;
			}

			if(Array.isArray(a)){
				if(compare == '='){
					return a.indexOf(b) != -1;
				}else if(compare == '!='){
					return a.indexOf(b) == -1;
				}else if(compare == 'IN'){
					return a.filter(function(n) { return b.indexOf(n) !== -1; }).length == b.length;
				}else if(compare == 'NOT IN'){
					return a.filter(function(n) { return b.indexOf(n) !== -1; }).length == 0;
				}else{
					return false;
				}
			}else{
				if(compare == '='){
					return a == b;
				}else if(compare == '!='){
					return a != b;
				}else if(compare == '>'){
					return a > b;
				}else if(compare == '>='){
					return a >= b;
				}else if(compare == '<'){
					return a < b;
				}else if(compare == '<='){
					return a <= b;
				}else if(compare == 'IN'){
					return b.indexOf(a) != -1;
				}else if(compare == 'NOT IN'){
					return b.indexOf(a) == -1;
				}else if(compare == 'BETWEEN'){
					return a > b[0] && a < b[1];
				}else if(compare == 'NOT BETWEEN'){
					return a < b[0] && a > b[1];
				}else{
					return false;
				}
			}
		},

		wpjam_form_init: function(){
			// 拖动排序
			$('.mu-fields').sortable({
				handle: '.dashicons-menu',
				cursor: 'move'
			});

			$('.mu-images').sortable({
				handle: '.dashicons-menu',
				cursor: 'move'
			});

			$('.mu-files').sortable({
				handle: '.dashicons-menu',
				cursor: 'move'
			});

			$('.mu-texts').sortable({
				handle: '.dashicons-menu',
				cursor: 'move'
			});

			$('.mu-imgs').sortable({
				cursor: 'move'
			});

			$('.wpjam-tooltip .wpjam-tooltip-text').css('margin-left', function(){
				return 0 - Math.round($(this).width()/2);
			});

			$('.tabs').wpjam_tabs();
			$('.show-if-key').wpjam_show_if();
			$('.wpjam-autocomplete').wpjam_autocomplete();

			$('input.color').wpColorPicker();

			$('textarea.wpjam-editor').wpjam_editor();
		}
	});

	$('body').on('change', '.show-if-key', function(){
		$(this).wpjam_show_if();
	});

	$('body').on('change', 'input[type="radio"]', function(){
		if($(this).is(':checked')){
			let wrap_id	= $(this).data('wrap_id');

			if(wrap_id){
				$('#'+wrap_id+' label').removeClass('checked');
				$(this).parent('label').addClass('checked');
			}
		}
	});

	$('body').on('change', 'input[type="checkbox"]', function(){
		if($(this).is(':checked')){
			let wrap_id	= $(this).data('wrap_id');

			if(wrap_id){
				$('#'+wrap_id+' label').removeClass('checked');
				let max_items	= parseInt($('#'+wrap_id).data('max_items'));

				if(max_items && $('#'+wrap_id+' input:checkbox:checked').length > max_items){ 
					alert('最多支持'+max_items+'个选项');
					$(this).prop('checked', false);
					return false;
				}
			}

			$(this).parent('label').addClass('checked');
		}else{
			$(this).parent('label').removeClass('checked');
		}
	});

	$.wpjam_form_init();

	$('body').on('list_table_action_success', function(event, response){
		$.wpjam_form_init();
	});

	$('body').on('page_action_success', function(event, response){
		$.wpjam_form_init();
	});

	$('body').on('option_action_success', function(event, response){
		$.wpjam_form_init();
	});

	$('body').on('click', '.wpjam-query-title span.dashicons', function(){
		$(this).parent().fadeOut(300, function(){
			$(this).prev('input').val('').removeClass('hidden');
			$(this).addClass('hidden').css('display', '');
		});
	});

	$('body').on('click', '.wpjam-modal', function(e){
		e.preventDefault();

		wpjam_modal($(this).prop('href'));
	});

	var custom_uploader;
	if(custom_uploader){
		custom_uploader.open();
		return;
	}

	$('body').on('click', '.wpjam-file a', function(e) {
		let prev_input	= $(this).prev('input');
		let item_type	= $(this).data('item_type');
		let title		= item_type == 'image' ? '选择图片' : '选择文件';

		custom_uploader = wp.media({
			id:			$(this).data('uploader_id'),
			title:		title,
			library:	{ type: item_type },
			button:		{ text: title },
			multiple:	false 
		}).on('select', function() {
			let attachment = custom_uploader.state().get('selection').first().toJSON();
			prev_input.val(attachment.url);
			$('.media-modal-close').trigger('click');
		}).open();

		return false;
	});

	//上传单个图片
	$('body').on('click', '.wpjam-img', function(e) {
		let _this	= $(this);

		if(wp.media.view.settings.post.id){
			custom_uploader = wp.media({
				id:			$(this).data('uploader_id'),
				title:		'选择图片',
				library:	{ type: 'image' },
				button:		{ text: '选择图片' },
				frame:		'post',
				multiple:	false 
			// }).on('select', function() {
			}).on('open',function(){
				$('.media-frame').addClass('hide-menu');
			}).on('insert', function() {
				_this.wpjam_add_attachment(custom_uploader.state().get('selection').first().toJSON());

				$('.media-modal-close').trigger('click');
			}).open();
		}else{
			custom_uploader = wp.media({
				id:			$(this).data('uploader_id'),
				title:		'选择图片',
				library:	{ type: 'image' },
				button:		{ text: '选择图片' },
				multiple:	false 
			}).on('select', function() {
				_this.wpjam_add_attachment(custom_uploader.state().get('selection').first().toJSON());

				$('.media-modal-close').trigger('click');
			}).open();
		}

		return false;
	});

	//上传多个图片或者文件
	$('body').on('click', '.wpjam-mu-file', function(e) {
		if($(this).parents('.mu-files').wpjam_max_reached()){
			return false;
		}

		let _this		= $(this);
		let item_type	= $(this).data('item_type');
		let title		= $(this).data('title');

		custom_uploader = wp.media({
			id:			$(this).data('uploader_id'),
			title:		title,
			library:	{ type: item_type },
			button:		{ text: title },
			multiple:	true
		}).on('select', function() {
			let name		= _this.data('name');
			let item_class	= _this.data('item_class');
			
			custom_uploader.state().get('selection').map(function(attachment){
				attachment	= attachment.toJSON();

				_this.parent().before('<div class="'+item_class+'">'+$.wpjam_render('mu-file', {
					img_url	: attachment.url,
					name	: name,
				})+$.wpjam_render('mu-action')+'</div>');
			});

			$('.media-modal-close').trigger('click');
		}).open();

		return false;
	});

	//上传多个图片
	$('body').on('click', '.wpjam-mu-img', function(e){
		if($(this).parents('.mu-imgs').wpjam_max_reached()){
			return false;
		}

		let _this	= $(this);

		if(wp.media.view.settings.post.id){
			custom_uploader = wp.media({
				id:			$(this).data('uploader_id'),
				title:		'选择图片',
				library:	{ type: 'image' },
				button:		{ text: '选择图片' },
				frame:		'post',
				multiple:	true
			// }).on('select', function() {
			}).on('open',function(){
				$('.media-frame').addClass('hide-menu');
			}).on('insert', function() {
				custom_uploader.state().get('selection').map(function(attachment){
					_this.wpjam_add_mu_attachment(attachment.toJSON());
				});

				$('.media-modal-close').trigger('click');
			}).open();
		}else{
			custom_uploader = wp.media({
				id:			$(this).data('uploader_id'),
				title:		'选择图片',
				library:	{ type: 'image' },
				button:		{ text: '选择图片' },
				multiple:	true
			}).on('select', function() {
				custom_uploader.state().get('selection').map(function(attachment){
					_this.wpjam_add_mu_attachment(attachment.toJSON());
				});

				$('.media-modal-close').trigger('click');
			}).open();
		}

		return false;
	});

	// 添加多个选项
	$('body').on('click', 'a.wpjam-mu-text', function(){
		if($(this).parents('.mu-texts').wpjam_max_reached()){
			return false;
		}

		let item	= $(this).parent().clone();

		item.insertAfter($(this).parent());
		item.find(':input').val('');
		item.find('.wpjam-query-title').addClass('hidden');
		item.find('.wpjam-autocomplete').removeClass('hidden').wpjam_autocomplete();

		$(this).parent().append($.wpjam_render('mu-action'));
		$(this).remove();

		return false;
	});

	$('body').on('click', 'a.wpjam-mu-fields', function(){
		if($(this).parents('.mu-fields').wpjam_max_reached()){
			return false;
		}

		let i		= $(this).data('i')+1;
		let item	= $($.wpjam_render($(this).data('tmpl_id'), {i:i}));

		item.insertAfter($(this).parent());
		item.find('.show-if-key').wpjam_show_if();
		item.find('.wpjam-autocomplete').wpjam_autocomplete();

		item.find('a.wpjam-mu-fields').data('i', i);

		item.wrapAll('<div class="'+$(this).data('item_class')+'"></div>');

		$(this).parent().append($.wpjam_render('mu-action'));
		$(this).parent().parent().trigger('mu_fields_added', i);
		$(this).remove();

		return false;
	});

	//  删除图片
	$('body').on('click', '.wpjam-del-img', function(){
		$(this).parent().next('input').val('');
		$(this).prev('img').fadeOut(300, function(){
			$(this).parent().removeClass('has-img');
			$(this).remove();
		});

		return false;
	});

	//  删除选项
	$('body').on('click', '.wpjam-del-item', function(){
		let next_input	= $(this).parent().next('input');
		if(next_input.length > 0){
			next_input.val('');
		}

		$(this).parent().fadeOut(300, function(){
			$(this).remove();
		});

		return false;
	});
});

if(self != top){
	document.getElementsByTagName('html')[0].className += ' TB_iframe';
}

function isset(obj){
	if(typeof(obj) != 'undefined' && obj !== null) {
		return true;
	}else{
		return false;
	}
}

function wpjam_modal(src, type, css){
	type	= type || 'img';

	if(jQuery('#wpjam_modal_wrap').length == 0){
		jQuery('body').append('<div id="wpjam_modal_wrap" class="hidden"><div id="wpjam_modal"></div></div>');
		jQuery("<a id='wpjam_modal_close' class='dashicons dashicons-no-alt del-item-icon'></a>")
		.on('click', function(e){
			e.preventDefault();
			jQuery('#wpjam_modal_wrap').remove();
		})
		.prependTo('#wpjam_modal_wrap');
	}

	if(type == 'iframe'){
		css	= css || {};
		css = jQuery.extend({}, {width:'300px', height:'500px'}, css);

		jQuery('#wpjam_modal').html('<iframe style="width:100%; height: 100%;" src='+src+'>你的浏览器不支持 iframe。</iframe>');
		jQuery('#wpjam_modal_wrap').css(css).removeClass('hidden');
	}else if(type == 'img'){
		let img_preloader	= new Image();
		let img_tag			= '';

		img_preloader.onload	= function(){
			img_preloader.onload	= null;

			let width	= img_preloader.width/2;
			let height	= img_preloader.height/2;

			if(width > 400 || height > 500){
				let radio	= (width / height >= 400 / 500) ? (400 / width) : (500 / height);
				
				width	= width * radio;
				height	= height * radio;
			}

			jQuery('#wpjam_modal').html('<img src="'+src+'" width="'+width+'" height="'+height+'" />');
			jQuery('#wpjam_modal_wrap').css({width:width+'px', height:height+'px'}).removeClass('hidden');
		}

		img_preloader.src	= src;
	}
}

function wpjam_iframe(src, css){
	wpjam_modal(src, 'iframe', css);
}