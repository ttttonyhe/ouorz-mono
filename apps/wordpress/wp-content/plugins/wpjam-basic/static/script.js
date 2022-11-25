jQuery(function($){
	$.extend({
		wpjam_notice: function(notice, type){
			let notice_id	= '';

			if($('#TB_ajaxContent').length > 0){
				notice_id	='wpjam_action_notice';

				if($('#TB_ajaxContent #'+notice_id).length < 1){
					$('#TB_ajaxContent').prepend('<div id="'+notice_id+'" class="notice is-dismissible hidden"></div>');
				}

				$('#TB_ajaxContent').scrollTop(0);
			}else{
				notice_id	='wpjam_notice';

				if($('#'+notice_id).length < 1){
					$('hr.wp-header-end').after('<div id="'+notice_id+'" class="notice is-dismissible inline hidden"></div>');
				}

				if($('.wp-heading-inline').offset().top < $(window).scrollTop()){
					$('html, body').animate({scrollTop: 0}, 800);
				}
			}

			$('#'+notice_id).html('<p><strong>'+notice+'</strong></p>')
			.removeClass('notice-success notice-error notice-info hidden')
			.addClass('notice-'+type).css('opacity', 0)
			.slideDown(200, function(){
				$(this).fadeTo(200, 1, function(){
					let _this = $(this);

					if(_this.find('button.notice-dismiss').length > 0){
						return;
					}

					let button = $('<button type="button" class="notice-dismiss"><span class="screen-reader-text">忽略此提示。</span></button>');
					
					button.on('click.wp-dismiss-notice', function(e){
						e.preventDefault();
						_this.fadeTo(100, 0, function(){
							_this.slideUp(100, function(){
								_this.remove();
							});
						});
					});

					_this.append(button);

					if($('#TB_ajaxContent').length > 0){
						$('#TB_ajaxContent').scrollTop(0);
					}
				});
			});

			return $(this);
		},

		wpjam_show_modal: function(modal_id, html, title, width){
			modal_id	= modal_id || 'tb_modal';

			if($('#'+modal_id).length){
				if(html){
					$('#'+modal_id).html(html);
				}

				width	= width || $('#'+modal_id).data('width') || 720;
				title	= title || $('#'+modal_id).data('title') || ' ';

				let old_tb_position	= window.tb_position;
				window.tb_position	= $.wpjam_tb_position;

				tb_show(title, '#TB_inline?inlineId='+modal_id+'&width='+width);

				window.tb_position	= old_tb_position;
			}
		},

		wpjam_tb_position: function(){
			if($('#TB_window').length){
				$('#TB_window').addClass('abscenter');

				if($('#TB_ajaxContent').length || $('#TB_iframeContent').length){
					let W	= Math.min(TB_WIDTH, 720, $(window).width()-20);
					let H	= Math.min(900, $(window).height()-120);

					if($('#TB_ajaxContent').length){
						$('#TB_ajaxContent').css({width: W-50, height: '', maxHeight: H});
					}

					$('#TB_window').css({width : W});
				}

				$('#TB_overlay').off('click');
			}
		},

		wpjam_loading: function(action_type, args){
			if(action_type == 'submit' && args.bulk != 2){
				if(document.activeElement.tagName != 'BODY'){
					window.submit_button	= document.activeElement
				}

				if($(window.submit_button).next('.spinner').length == 0){
					$(window.submit_button).after('<span class="spinner"></span>');
				}

				$(window.submit_button).prop('disabled', true).next('.spinner').addClass('is-active');
			}else{
				let spinner = $.wpjam_list_table_spinner(args);

				if(spinner){
					let ids	= (args.bulk && args.bulk == 1) ? args.ids : [args.id];
					
					$.each(ids, function(index, id){
						$($.wpjam_list_table_tr_item(id) + ' .check-column input').after('<span class="spinner is-active"></span>').hide();
					});
				}

				if(spinner != 1){
					$('body').append("<div id='TB_load'><img src='"+imgLoader.src+"' width='208' /></div>");
					$('#TB_load').show();
				}
			}
		},

		wpjam_loaded: function(action_type, args){
			if(action_type == 'submit' && args.bulk != 2){
				if(window.submit_button){
					$(window.submit_button).prop('disabled', false).next('.spinner').remove();
				}
			}else{
				let spinner = $.wpjam_list_table_spinner(args);

				if(spinner){
					let ids	= (args.bulk && args.bulk == 1) ? args.ids : [args.id];
					
					$.each(ids, function(index, id){
						let tr_item	= $.wpjam_list_table_tr_item(id);

						$(tr_item+' .check-column input').show();
						$(tr_item+' .check-column .spinner').remove();
					});

					if(args.bulk && args.bulk != 2){
						$('thead td.check-column input').prop('checked', false);
						$('tfoot td.check-column input').prop('checked', false);
					}
				}

				if(spinner != 1){
					$('#TB_load').remove();
				}
			}
		},

		wpjam_list_table_spinner: function(args){
			if(args.action == 'wpjam-list-table-action' && $('tbody th.check-column').length > 0){
				if(args.action_type == 'form'){
					return 2;
				}else if($.inArray(args.action_type, ['query_items', 'left']) == -1){
					return 1;
				}
			}

			return 0;
		},

		wpjam_list_table_action: function(args){
			let list_action	= args.list_action;
			let action_type	= args.action_type = args.action_type || args.list_action_type;

			args		= $.wpjam_append_page_setting(args);
			args.action	= 'wpjam-list-table-action';

			$.wpjam_loading(action_type, args);

			return $.post(ajaxurl, args, function(data, status){
				let response	= (typeof data == 'object') ? data : JSON.parse(data);
				let tr_item		= $.wpjam_list_table_tr_item(args.id);

				if(!args.bulk && args.bulk != 2){
					$('.wp-list-table > tbody tr').not(tr_item).css('background-color', '');
				}

				$.wpjam_loaded(action_type, args);

				if(response.errcode != 0){
					if(action_type == 'direct'){
						alert(response.errmsg);
					}else{
						if(action_type == 'submit'){
							$('#TB_ajaxContent').scrollTop(0);
						}
						
						$.wpjam_notice(response.errmsg, 'error');
					}
				}else{
					if(action_type == 'query_items' || action_type == 'left'){
						if(action_type == 'left'){
							$('div#col-left div').html(response.left);
						}

						$('body div.list-table').html(response.data);
						$('html').scrollTop(0);
					}else if(action_type == 'form'){
						if($('#TB_ajaxContent').length > 0){
							$('#TB_ajaxWindowTitle').html(response.page_title);
							$('#TB_ajaxContent').html(response.form);
						}else{
							$.wpjam_show_modal('tb_modal', response.form, response.page_title, response.width);
						}
					}else{
						$.wpjam_list_table_response(response, args);
					}

					$.wpjam_push_state();

					response.list_action	= list_action;
					response.action_type	= response.list_action_type	= action_type;

					$('body').trigger('list_table_action_success', response);
				}
			});
		},

		wpjam_list_table_response: function(response, args){
			if(response.type == 'items' && response.items){
				$.each(response.items, function(i, item){
					$.wpjam_list_table_response(item, args);
				});
			}else if(response.type == 'redirect'){
				$.wpjam_response_redirect(response);
			}else if(response.type == 'append'){
				if($('#TB_ajaxContent').length > 0){
					$.wpjam_response_append(response);
				}else{
					$.wpjam_show_modal('tb_modal', response.data, response.page_title, response.width);
				}
			}else{
				if($('#TB_ajaxContent').length > 0){
					if(response.dismiss){
						tb_remove();
					}else{
						$('#TB_ajaxWindowTitle').html(response.page_title);
						$('#TB_ajaxContent').html(response.form);
						$('#TB_ajaxContent').scrollTop(0);
					}
				}

				if(response.errmsg){
					$.wpjam_notice(response.errmsg, 'success');
				}

				let tr_item	= $.wpjam_list_table_tr_item(args.id);

				if(response.type == 'form'){
					//
				}else if(response.type == 'list'){
					if(response.list_action == 'delete'){
						$.when($.wpjam_list_table_delete_item(response)).then(function(){
							setTimeout(function(){
								$('body div.list-table').html(response.data);
							}, 300);						
						});
					}else{
						$('body div.list-table').html(response.data);

						let ids			= response.bulk ? response.ids : [response.id];
						let bg_color 	= '#ffffdd';

						$.each(ids, function(index, id){
							bg_color 	= bg_color == '#ffffdd' ? '#ffffee' : '#ffffdd';

							$.wpjam_list_table_update_item({id: id}, bg_color);
						});
					}
				}else if(response.type == 'add' || response.type == 'duplicate'){
					$.wpjam_list_table_create_item(response, '#ffffee');
				}else if(response.type == 'delete'){
					$.wpjam_list_table_delete_item(response);
				}else if(response.type == 'up' || response.type == 'down'){
					if(response.type == 'up'){
						let tr_next	= $.wpjam_list_table_tr_item(args.next);
						$(tr_next).insertAfter(tr_item);
					}else{
						let tr_prev	= $.wpjam_list_table_tr_item(args.prev);
						$(tr_item).insertAfter(tr_prev);
					}

					$.wpjam_list_table_update_item(response, '#eeffff');
				}else if(response.type == 'move'){
					$.wpjam_list_table_update_item(response, '#eeffee');
				}else if(response.type == 'move_item'){
					$.wpjam_list_table_update_item(response, false);

					$(tr_item+' #item-'+args.pos).css('background-color', '#eeffee');
				}else if(response.type == 'add_item'){
					$.wpjam_list_table_update_item(response, false);

					$(tr_item+' .items .item').last().css('background-color', '#ffffee');
				}else if(response.type == 'edit_item'){
					$.wpjam_list_table_update_item(response, false);

					let	sp	= new URLSearchParams(args.defaults);

					$(tr_item+' #item-'+sp.get('i')).css('background-color', '#ffffee');
				}else if(response.type == 'del_item'){
					let	sp	= new URLSearchParams(args.data);

					$(tr_item+' #item-'+sp.get('i')).css('background-color', 'red').fadeOut(400, function(){ $(this).remove();});
				}else{
					let bg_color	= args.bg_color || '#ffffee';

					$.wpjam_list_table_update_item(response, bg_color);
				}

				if(response.next){
					wpjam_params.list_action	= response.next;
					
					if(response.next != 'add' && response.id){
						wpjam_params.id	= response.id;
					}

					if(args.data && response.type == 'form'){
						wpjam_params.data	= args.data;
					}
				}
			}
		},

		wpjam_list_table_bulk_action: function(ids, args){
			args.id	= ids.shift();

			let tr_item	= $.wpjam_list_table_tr_item(args.id);
			let offset	= $(tr_item).offset().top;
			let timeout	= 400;

			if(offset - $(window).scrollTop() > $(window).height()-100){
				$('html, body').animate({scrollTop: offset-100}, 300);
				timeout	+= 400;
			}

			setTimeout(function(){
				args.bg_color 	= args.bg_color == '#ffffdd' ? '#ffffee' : '#ffffdd';

				$.when($.wpjam_list_table_action(args)).then(function(){
					if(ids.length > 0){
						$.wpjam_list_table_bulk_action(ids, args);
					}else{
						$('thead td.check-column input').prop('checked', false);
						$('tfoot td.check-column input').prop('checked', false);
					}
				});
			}, timeout);
		},

		wpjam_list_table_create_item: function(response, bg_color){
			if(response.data){
				if(response.layout == 'calendar'){
					$.wpjam_list_table_update_calendar_date(response, bg_color);
				}else{
					if(response.bulk){
						$.each(response.data, function(id, item){
							bg_color 	= bg_color == '#ffffdd' ? '#ffffee' : '#ffffdd';

							$.wpjam_list_table_create_item({id: id, data: item, bulk: false}, bg_color);
						});
					}else{
						if(response.after){
							$($.wpjam_list_table_tr_item(response.after)).after(response.data);
						}else if(response.before){
							$($.wpjam_list_table_tr_item(response.before)).before(response.data);
						}else if(response.last){
							$('.wp-list-table > tbody tr').last().after(response.data);
						}else{
							$('.wp-list-table > tbody tr').first().before(response.data);
						}

						let tr_item	= $.wpjam_list_table_tr_item(response.id);

						$(tr_item).hide().css('background-color', bg_color).fadeIn(400);

						let offset	= $(tr_item).offset().top;

						if(offset - $(window).scrollTop() > $(window).height() - 100){
							$('html, body').animate({scrollTop: offset-100}, 400);
						}
					}	

					$('.no-items').remove();
				}
			}
		},

		wpjam_list_table_update_item: function(response, bg_color){
			if(response.layout == 'calendar'){
				$.wpjam_list_table_update_calendar_date(response, bg_color);
			}else{
				if(response.bulk){
					$.each(response.data, function(id, item){
						bg_color 	= bg_color == '#ffffdd' ? '#ffffee' : '#ffffdd';

						$.wpjam_list_table_update_item({id: id, data: item, bulk: false}, bg_color);
					});
				}else{
					if(response.id){
						let tr_item	= $.wpjam_list_table_tr_item(response.id);

						if(response.data){
							let tr_id	= $.wpjam_list_table_tr_id(response.id);

							$(tr_item).last().after('<span class="edit-'+tr_id+'"></span>');
							$(tr_item).remove();
							$('.edit-'+tr_id).before(response.data).remove();	
						}

						if(bg_color){
							$(tr_item).hide().css('background-color', bg_color).fadeIn(1000);
						}
					}
				}
			}
		},

		wpjam_list_table_delete_item: function(response){
			if(response.layout == 'calendar'){
				$.wpjam_list_table_update_calendar_date(response, '#ffffee');
			}else{
				if(response.bulk){
					$.each(response.ids, function(index, id){
						$.wpjam_list_table_delete_item({id: id, bulk: false});
					});
				}else{
					let tr_item	= $.wpjam_list_table_tr_item(response.id);
					$(tr_item).css('background-color', 'red').fadeOut(400, function(){ $(this).remove();});
				}
			}
		},

		wpjam_list_table_update_calendar_date(response, bg_color){
			$.each(response.data, function(date, item){
				bg_color 	= bg_color == '#ffffdd' ? '#ffffee' : '#ffffdd';
				$('td#date_'+date).html(item).css('background-color', bg_color);
			});
		},

		wpjam_list_table_tr_id: function(id){
			return typeof(id) == "string" ? id.replace(/\./g, '-') : id;
		},

		wpjam_list_table_tr_item: function(id){
			id	= $.wpjam_list_table_tr_id(id);

			if($('.tr-'+id).length > 0){
				return '.tr-'+id;
			}

			let prefix	= '#post';

			if($('.wp-list-table tbody').data('wp-lists')){
				prefix	= '#'+$('.wp-list-table tbody').data('wp-lists').split(':')[1];
				// prefix	= '#'+$('#the-list').data('wp-lists').split(':')[1];
			}

			return prefix+'-'+id;
		},

		wpjam_list_table_query_items: function(type){
			if(wpjam_list_table.left_key){
				let left_key	= wpjam_list_table.left_key;

				if(type == 'left'){
					delete wpjam_params[left_key];
				}else{
					wpjam_params[left_key]	= $('tr.left-current').data('id');
				}
			}

			if(wpjam_params.hasOwnProperty('id')){
				delete wpjam_params.id;
			}

			$.wpjam_list_table_action({
				action_type:	type || 'query_items',
				data:			$.param(wpjam_params)
			});

			return false;
		},

		wpjam_list_table_loaded: function(){
			if($(window).width() > 782){
				let bulkactions	= $('.tablenav.top').find('div.bulkactions');

				if(bulkactions.length == 1 && bulkactions.html().replace(/[\n\t]/g, '') == ''){
					bulkactions.remove();
				}

				if($('p.search-box').length){
					if($('ul.subsubsub').length){
						let width	= $('p.search-box').width() + 5;

						$('ul.subsubsub').css('max-width', 'calc(100% - '+width+'px)')
					}
				}else{
					if($('.tablenav.top').find('div.alignleft').length == 0){
						$('.tablenav.top').css({clear:'none'});
					}
				}	
			}

			if($('.wrap .list-table').length == 0){
				$('ul.subsubsub, form#posts-filter').wrapAll('<div class="list-table" />');
			}

			$('input[name=_wp_http_referer]').val($.wpjam_admin_url());

			wpjam_list_table.loaded	= true;

			if(wpjam_list_table.query_id){
				let query_id	= wpjam_list_table.query_id;
				
				if(!$($.wpjam_list_table_tr_item(query_id)).length){
					$.wpjam_list_table_action({
						action_type:	'query_item',
						id:				query_id
					});
				}else{
					$.wpjam_list_table_update_item({id:query_id}, '#ffffee');
				}

				delete wpjam_list_table.query_id;
			}

			if(wpjam_list_table.sortable){
				let items	= $('#'+wpjam_list_table.form_id).data('sortable_items') || wpjam_list_table.sortable.items  || ' > tr';

				$('.wp-list-table > tbody').sortable({
					items:		items,
					axis:		'y',
					containment:'.wp-list-table',
					cursor:		'move',
					handle:		'.list-table-move-action',

					create: function(e, ui){
						let items	= $(this).sortable('option', 'items');

						$(this).find(items).addClass('ui-sortable-item');
					},

					start: function(e, ui){
						ui.placeholder.css({
							'visibility':'visible',
							'background-color':'#eeffff',
							'width':ui.item.width()+'px', 
							'height':ui.item.height()+'px'
						});
					},

					helper: function(e, ui){
						let children = ui.children();

						for(let i=0; i<children.length; i++){
							$(children[i]).width($(children[i]).width());
						};

						return ui;
					},

					update:	function(e, ui){
						let _this	= $(this);

						_this.sortable('disable');

						let handle	= ui.item.find('.row-actions .move a');
						let	next	= ui.item.next().find('.ui-sortable-handle').data('id');
						let	prev	= ui.item.prev().find('.ui-sortable-handle').data('id');
						let data	= handle.data('data');

						data	= data ? data + '&type=drag' : 'type=drag';
						data	= next ? data + '&next='+next : data;
						data	= prev ? data + '&prev='+prev : data;

						ui.item.css('background-color', '#eeffee');

						$.when($.wpjam_list_table_action({
							action_type:	'direct',
							list_action:	handle.data('action'),
							data:			data,
							id:				handle.data('id'),
							_ajax_nonce: 	handle.data('nonce')
						})).then(function(){
							_this.sortable('enable');
						});
					}
				});
			}

			$('.wp-list-table > tbody .items.sortable:not(.ui-sortable)').sortable({
				items:	'> div.item',
				cursor:	'move',
				handle:	'.move-item',

				create: function(e, ui){
					$(this).sortable('option', 'containment', $(this).parent());
				},

				start: function(e, ui){
					ui.placeholder.css({
						'visibility':'visible',
						'background-color':'#eeffff', 
						'width':ui.item.width()+'px', 
						'height':ui.item.height()+'px'
					});
				},

				helper: function(e, ui){
					let children = ui.children();

					for(let i=0; i<children.length; i++){
						$(children[i]).width($(children[i]).width()).height($(children[i]).height());
					};

					return ui;
				},
				
				update:	function(e, ui) {
					let _this	= $(this);

					_this.sortable('disable');

					let handle	= ui.item.find('.move-item');
					let args	= {
						action_type:	'direct',
						list_action:	handle.data('action'),
						data:			handle.data('data'),
						id:				handle.data('id'),
						_ajax_nonce: 	handle.data('nonce')
					};

					args.data	= args.data ? args.data + '&type=drag' : 'type=drag';
					args.data	= args.data + '&'+$(this).sortable('serialize');

					if(ui.item.next().length){
						let next	= ui.item.next().data('i');
						args.data	= args.data + '&next=' + next;

						if(ui.item.data('i') >= next){
							args.pos	= next;	
						}
					}

					if(ui.item.prev().length){
						let prev	= ui.item.prev().data('i');
						args.data	= args.data + '&prev=' + prev;
						
						if(ui.item.data('i') <= prev){
							args.pos	= prev;	
						}
					}

					ui.item.css('background-color', '#eeffee');
					
					$.when($.wpjam_list_table_action(args)).then(function(){
						_this.sortable('enable');
					});
				}
			});

			if($.inArray(screen_base, ['edit', 'upload', 'edit-tags']) != -1 && wpjam_list_table.ajax){
				let page	= new URL($('#adminmenu a.current').prop('href'));

				$('body .subsubsub a, body tbody#the-list a').addClass(function(){
					if($(this).hasClass('editinline') 
						|| $(this).hasClass('list-table-href') 
						|| $(this).hasClass('list-table-action') 
						|| $(this).hasClass('list-table-filter')
						|| $(this).hasClass('list-table-no-href')
					){
						return;
					}

					let href_attr	= $(this).attr('href');

					if(!href_attr || href_attr == '#' || href_attr.indexOf('javascript:;') === 0){
						return 'list-table-no-href';
					}

					let href	= new URL($(this).prop('href'));

					if(page.hostname != href.hostname || page.pathname != href.pathname){
						return 'list-table-no-href';
					}

					let params	= {};

					for(let [key, value] of href.searchParams.entries()){
						if(key == 'page'){
							return 'list-table-no-href';
						}

						params[key]	= value;
					}

					for(let [key, value] of page.searchParams.entries()){
						if(!params[key] || params[key] != value){
							return 'list-table-no-href';
						}
					}

					return 'list-table-href';
				});
			}
		},

		wpjam_response_append: function(response){
			let wrap	= ($('#TB_ajaxContent').length > 0) ? '#TB_ajaxContent' : 'div.wrap';

			if($(wrap+' .response').length == 0){
				$(wrap).append('<div class="card response hidden"></div>');
			}

			$(wrap+' .response').html(response.data).fadeIn(400);

			if($('#TB_ajaxContent').length > 0){
				$('#TB_ajaxContent').scrollTop($('#TB_ajaxContent form').prop('scrollHeight'));
			}
		},

		wpjam_response_redirect: function(response){
			if(response.url){
				window.location.href	= response.url;
			}else{
				window.location.reload();
			}
		},

		wpjam_page_action: function (args){
			let action_type	= args.action_type = args.action_type || args.page_action_type || 'form';
			let page_action	= args.page_action;

			args		= $.wpjam_append_page_setting(args);
			args.action	= 'wpjam-page-action';

			$.wpjam_loading(action_type, args);

			$.post(ajaxurl, args, function(data, status){
				let response	= (typeof data == 'object') ? data : JSON.parse(data);

				$.wpjam_loaded(action_type, args);

				if(response.errcode != 0){
					if(action_type == 'submit'){
						$.wpjam_notice(args.page_title+'失败：'+response.errmsg, 'error');
					}else{
						alert(response.errmsg);
					}
				}else{
					if(action_type == 'submit'){
						if(response.type == 'append'){
							$.wpjam_response_append(response);
						}else if(response.type == 'redirect'){
							$.wpjam_response_redirect(response);
						}else{
							if($('#wpjam_form').length > 0){
								if(response.form){
									$('#wpjam_form').html(response.form);
								}
							}

							if(response.errmsg){
								$.wpjam_notice(response.errmsg, 'info');
							}else{
								$.wpjam_notice(args.page_title+'成功', 'success');
							}
						}

						if(response.done == 0){
							setTimeout(function(){
								args.data	= response.args;
								$.wpjam_page_action(args);
							}, 400);
						}
					}else if(action_type == 'form'){
						let response_form	= response.form || response.data;

						if(!response_form){
							alert('服务端未返回表单数据');
						}

						$.wpjam_show_modal('tb_modal', response_form, response.page_title, response.width);
					}else{
						if(response.type == 'redirect'){
							$.wpjam_response_redirect(response);
						}else{
							if(response.errmsg){
								$.wpjam_notice(response.errmsg, 'success');
							}
						}
					}

					$.wpjam_push_state();

					response.page_action	= page_action;
					response.action_type	= response.page_action_type	= action_type;

					$('body').trigger('page_action_success', response);
				}
			});

			return false;
		},

		wpjam_option_action: function(args){
			args		= $.wpjam_append_page_setting(args);
			args.action	= 'wpjam-option-action';

			$.wpjam_loading('submit', args);

			$.post(ajaxurl, args, function(data, status){
				let response	= (typeof data == 'object') ? data : JSON.parse(data);

				$.wpjam_loaded('submit', args);

				if(response.errcode != 0){
					let notice_msg	= args.option_action == 'reset' ? '重置' : '保存';

					$.wpjam_notice(notice_msg+'失败：'+response.errmsg, 'error');
				}else{
					$('body').trigger('option_action_success', response);

					if(response.type == 'reset' || response.type == 'redirect'){
						$('<form>').prop('method', 'POST').prop('action', window.location.href)
						.append($('<input>').prop('type', 'hidden').prop('name', 'response_type').prop('value', response.type))
						.appendTo(document.body)
						.submit();
					}else{
						$.wpjam_notice(response.errmsg, 'success');
					}
				}
			});

			return false;
		},

		wpjam_append_page_setting: function(args){
			args.screen_id	= wpjam_page_setting.screen_id;

			if(wpjam_page_setting.plugin_page){
				args.plugin_page	= wpjam_page_setting.plugin_page;
				args.current_tab	= wpjam_page_setting.current_tab;
			}

			if(wpjam_page_setting.post_type){
				args.post_type	= wpjam_page_setting.post_type;
			}

			if(wpjam_page_setting.taxonomy){
				args.taxonomy	= wpjam_page_setting.taxonomy;
			} 

			if(wpjam_page_setting.query_data){
				let query_data = wpjam_page_setting.query_data;

				if(args.data && typeof(args.data) != 'undefined'){
					$.each(args.data.split('&'), function(){
						let query = this.split('=');

						if(query_data.hasOwnProperty(query[0])){
							query_data[query[0]]	= query[1];
						}
					});

					args.data	= $.param(query_data)+'&'+args.data;
				}else{
					args.data	= $.param(query_data);
				}
			}

			if(wpjam_list_table && wpjam_list_table.left_key && args.action_type != 'left'){
				let left_query	= wpjam_list_table.left_key+'='+$('tr.left-current').data('id');

				if(args.data && typeof(args.data) != 'undefined'){
					args.data	= args.data+'&'+left_query;
				}else{
					args.data	= left_query;
				}
			}

			return args;
		},

		wpjam_admin_url: function(){
			let admin_url	= wpjam_admin_url || $('#adminmenu a.current').prop('href');
			let query		= $.extend({}, wpjam_params);
			
			if(query.data){
				query.data	= encodeURIComponent(query.data);
			}

			if(query.hasOwnProperty('paged') && query.paged <= 1){
				delete query.paged;
			}

			query	= $.param(query);

			if(query){
				admin_url	+= admin_url.indexOf('?') >= 0 ? '&' : '?';
				admin_url	+= decodeURIComponent(query);
			}

			return admin_url;
		},

		wpjam_push_state: function(){
			let admin_url	= $.wpjam_admin_url();
			
			if(window.location.href != admin_url){
				window.history.pushState({wpjam_params: wpjam_params}, null, admin_url);
			}
		},

		wpjam_delegate_events: function(selector, sub_selector){
			sub_selector	= sub_selector || '';

			$.each($._data($(selector).get(0), 'events'), function(type, events){
				$.each(events, function(i, event){
					if(event){
						if(event.selector){
							if(!sub_selector || event.selector == sub_selector){
								$('body').on(type, selector+' '+event.selector, event.handler);
								$(selector).off(type, event.selector, event.handler);
							}
						}else{
							$('body').on(type, selector, event.handler);
							$(selector).off(type, event.handler);
						}
					}
				});
			});
		}
	});

	let wpjam_list_table	= wpjam_page_setting.list_table;
	let wpjam_params		= wpjam_page_setting.params;
	let screen_base			= wpjam_page_setting.screen_base;
	let wpjam_admin_url		= wpjam_page_setting.admin_url;

	let old_send_to_editor	= window.send_to_editor;
	window.send_to_editor = function(html){
		let old_tb_remove	= window.tb_remove;
		window.tb_remove	= null;
		old_send_to_editor.apply(this, arguments);
		window.tb_remove	= old_tb_remove;
	};

	$(window).resize(function(){
		if($('#TB_window').hasClass('abscenter')){
			$.wpjam_tb_position();
		}
	});

	$('body').on('click', '.show-modal', function(){
		if($(this).data('modal_id')){
			$.wpjam_show_modal($(this).data('modal_id'));
		}
	});

	if($('#notice_modal').length){
		$.wpjam_show_modal('notice_modal');
	}

	$('body').on('tb_unload', '#TB_window', function(){
		if($('#notice_modal').find('.delete-notice').length){
			$('#notice_modal').find('.delete-notice').trigger('click');
		}

		if(wpjam_params.page_action){
			delete wpjam_params.page_action;
			delete wpjam_params.data;

			$.wpjam_push_state();
		}else if(wpjam_params.list_action && wpjam_list_table){
			delete wpjam_params.list_action;
			delete wpjam_params.id;
			delete wpjam_params.data;

			$.wpjam_push_state();
		}
	});

	$('body').on('click', '.is-dismissible .notice-dismiss', function(){
		if($(this).prev('.delete-notice').length){
			$(this).prev('.delete-notice').trigger('click');
		}
	});

	// From mdn: On Mac, elements that aren't text input elements tend not to get focus assigned to them.
	$('body').on('click', 'input[type=submit]', function(e){
		if(!$(document.activeElement).attr('id')){
			$(this).focus();	
		}
	});

	window.onpopstate = function(event){
		if(event.state && event.state.wpjam_params){
			wpjam_params	= event.state.wpjam_params;

			if(wpjam_params.page_action){
				$.wpjam_page_action($.extend({}, wpjam_params, {action_type: 'form'}));
			}else if(wpjam_params.list_action && wpjam_list_table){
				$.wpjam_list_table_action($.extend({}, wpjam_params, {action_type: 'form'}));
			}else{
				tb_remove();

				if(wpjam_list_table){
					$.wpjam_list_table_query_items();
				}
			}
		}
	};

	if(wpjam_list_table && wpjam_list_table.form_id){
		let list_table_form	= '#'+wpjam_list_table.form_id;

		$.wpjam_list_table_loaded();

		$('body').on('list_table_action_success', function(e, response){
			if(response.action_type != 'form'){
				$.wpjam_list_table_loaded();
			}
		});

		$('body').on('submit', '#list_table_action_form', function(e){
			e.preventDefault();

			if($(this).data('next')){
				window.action_flows = window.action_flows || [];
				window.action_flows.push($(this).data('action'));
			}

			let submit_button	= $(document.activeElement);

			if($(document.activeElement).prop('type') != 'submit'){
				submit_button	= $(this).find(':submit').first();
				submit_button.focus();
			}

			let ids		= $(this).data('ids');
			let args	= {
				action_type :	'submit',
				bulk : 			$(this).data('bulk'),
				list_action :	$(this).data('action'),
				submit_name:	submit_button.attr('name'),
				id :			$(this).data('id'),
				data : 			$(this).serialize(),
				defaults :		$(this).data('data'),
				_ajax_nonce :	$(this).data('nonce')
			};

			if(args.bulk == 2){
				tb_remove();
				$.wpjam_list_table_bulk_action(ids, args);
			}else{
				args.ids	= ids;
				$.wpjam_list_table_action(args);
			}
		});

		$('body').on('submit', list_table_form, function(e){
			let active_element_id	= $(document.activeElement).attr('id');

			if(active_element_id == 'doaction' || active_element_id == 'doaction2'){
				let bulk_action	= $('#'+active_element_id).prev('select').val();
				let bulk_option	= $('#'+active_element_id).prev('select').find('option:selected');

				if(bulk_action == '-1'){
					alert('请选择要进行的批量操作！');
					return false;
				}

				let ids	= $.map($('tbody .check-column input[type="checkbox"]:checked'), function(cb){
					return cb.value;
				});

				if(ids.length == 0){
					alert('请至少选择一项！');
					return false;
				}

				if(bulk_option.data('action')){
					if(bulk_option.data('confirm') && confirm('确定要'+bulk_option.text()+'吗?') == false){
						return false;
					}

					let args	= {
						list_action:	bulk_action,
						action_type:	bulk_option.data('direct') ? 'direct' : 'form',
						data:			bulk_option.data('data'),
						_ajax_nonce: 	bulk_option.data('nonce'),
						bulk: 			bulk_option.data('bulk')
					};

					if(args.action_type != 'form' && args.bulk == 2){
						$.wpjam_list_table_bulk_action(ids, args);
					}else{
						args.ids	= ids;
						args.bulk	= 1;

						$.wpjam_list_table_action(args);
					}

					return false;
				}
			}else if(wpjam_list_table.ajax){
				let search_input_id	= $(list_table_form+' input[type=search]').attr('id');

				if(active_element_id == 'current-page-selector'){
					let paged	= parseInt($('#current-page-selector').val());
					let total	= parseInt($('#current-page-selector').next('span').find('span.total-pages').text());

					if(paged < 1 || paged > total){
						alert(paged < 1 ? '页面数字不能小于为1' : '页面数字不能大于'+total);

						return false
					}

					wpjam_params.paged	= paged;

					return $.wpjam_list_table_query_items();
				
				}else if(active_element_id == 'search-submit' || active_element_id == search_input_id){
					wpjam_params	= {s:$('#'+search_input_id).val()};

					return $.wpjam_list_table_query_items();
				}else if(active_element_id == 'filter_action' || active_element_id == 'post-query-submit'){
					wpjam_params	= {};

					$.each($(this).serializeArray(), function(index, param){
						if($.inArray(param.name, ['page', 'tab', 's', 'paged', '_wp_http_referer', '_wpnonce', 'action', 'action2']) == -1){
							wpjam_params[param.name]	= param.value;
						}
					});

					return $.wpjam_list_table_query_items();
				}
			}
		});

		$('body').on('click', '.list-table-action', function(){
			if($(this).data('confirm') && confirm('确定要'+$(this).attr('title')+'吗?') == false){
				return false;
			}

			let args	= {
				action_type :	$(this).data('direct') ? 'direct' : 'form',
				list_action :	$(this).data('action'),
				id : 			$(this).data('id'),
				data : 			$(this).data('data'),
				_ajax_nonce :	$(this).data('nonce')
			};

			let tr_item	= $.wpjam_list_table_tr_item(args.id);

			if(args.list_action == 'up'){
				args.next	= $(tr_item).prev().find('.ui-sortable-handle').data('id');

				if(!args.next){
					alert('已经是第一个了，不可上移了。');
					return false;
				}
				
				args.data	= args.data ? args.data + '&next='+args.next : 'next='+args.next;
			}else if(args.list_action == 'down'){
				args.prev	= $(tr_item).next().find('.ui-sortable-handle').data('id');

				if(!args.prev){
					alert('已经最后一个了，不可下移了。');
					return false;
				}
				
				args.data	= args.data ? args.data + '&prev='+args.prev : 'prev='+args.prev;
			}else if(args.action_type == 'form'){
				wpjam_params.list_action	= args.list_action;

				if(args.list_action != 'add' && args.id){
					wpjam_params.id	= args.id;
				}

				if(args.data){
					wpjam_params.data	= args.data;
				}
			}

			$.wpjam_list_table_action(args);

			$(this).blur();
		});

		$('body').on('click', '.list-table-href', function(){
			let href	= new URL($(this).prop('href'));
			let excepts	= ['post_type'];

			if(screen_base == 'edit-tags'){
				excepts	= ['post_type', 'taxonomy'];
			}

			wpjam_params	= {};

			for(let [key, value] of href.searchParams.entries()){
				if($.inArray(key, excepts) == -1){
					wpjam_params[key]	= value;
				}
			}

			return $.wpjam_list_table_query_items();
		});

		$('body').on('click', '.list-table-filter', function(){
			wpjam_params	= $(this).data('filter');

			return $.wpjam_list_table_query_items();
		});

		$('body').on('click', 'div#col-left .left-item', function(){
			$('div#col-left .left-item').removeClass('left-current');
			$(this).addClass('left-current');
			
			return $.wpjam_list_table_query_items();
		});

		if(wpjam_list_table.ajax){
			$('body').on('click', list_table_form+' .pagination-links a', function(){
				wpjam_params.paged	= (new URL($(this).prop('href'))).searchParams.get('paged');

				return $.wpjam_list_table_query_items();
			});

			$('body').on('click', list_table_form+' th.sorted a, '+list_table_form+' th.sortable a', function(){
				let href = new URL($(this).prop('href'));

				let orderby	= href.searchParams.get('orderby');
				let order	= href.searchParams.get('order');

				wpjam_params.orderby	= orderby || $(this).parent().attr('id');
				wpjam_params.order		= order || ($(this).parent().hasClass('asc') ? 'desc' : 'asc');
				wpjam_params.paged		= 1;

				return $.wpjam_list_table_query_items();
			});
		}

		$('body').on('click', '#col-left .left-pagination-links a', function(){
			let paged	= $(this).hasClass('goto') ? parseInt($(this).prev('input').val()) : $(this).data('left_paged');
			let total	= $(this).parents('.left-pagination-links').find('span.total-pages').text();

			if(paged < 1 || paged > total){
				alert(paged < 1 ? '页面数字不能小于为1' : '页面数字不能大于'+total);

				return false
			}

			wpjam_params.left_paged	= paged;

			return $.wpjam_list_table_query_items('left');
		});

		$('body').on('change', '#col-left select.left-filter', function(){
			let name = $(this).prop('name');

			wpjam_params.left_paged	= 1;
			wpjam_params[name]		= $(this).val();

			return $.wpjam_list_table_query_items('left');
		});

		$('body').on('keyup', '#left-current-page-selector', function(e) {
			if(e.key === 'Enter' || e.keyCode === 13){
				$(this).next('a').trigger('click');
			}
		});
	}

	window.history.replaceState({wpjam_params: wpjam_params}, null);

	if(wpjam_params.page_action){
		$.wpjam_page_action($.extend({}, wpjam_params, {action_type: 'form'}));
	}else if(wpjam_params.list_action && wpjam_list_table){
		$.wpjam_list_table_action($.extend({}, wpjam_params, {action_type: 'form'}));
	}

	$('body').on('click', '.wpjam-button', function(e){
		e.preventDefault();

		if($(this).data('confirm') && confirm('确定要'+$(this).data('title')+'吗?') == false){
			return false;
		}

		let args	= {
			action_type:	$(this).data('direct') ? 'direct' : 'form',
			data:			$(this).data('data'),
			form_data:		$(this).parents('form').serialize(),
			page_action:	$(this).data('action'),
			page_title:		$(this).data('title'),
			_ajax_nonce:	$(this).data('nonce')
		};

		if(args.action_type == 'form'){
			wpjam_params.page_action	= args.page_action;

			if(args.data){
				wpjam_params.data	= args.data;
			}
		}

		return $.wpjam_page_action(args);
	});

	$('body').on('submit', '#wpjam_form', function(e){
		e.preventDefault();

		let submit_button	= $(document.activeElement);

		if($(document.activeElement).prop('type') != 'submit'){
			submit_button	= $(this).find(':submit').first();
			submit_button.focus();
		}

		return $.wpjam_page_action({
			action_type:	'submit',
			data: 			$(this).serialize(),
			page_action:	$(this).data('action'),
			submit_name:	submit_button.attr('name'),
			page_title:		submit_button.attr('value'),
			_ajax_nonce:	$(this).data('nonce')
		});
	});

	$('body').on('submit', '#wpjam_option', function(e){
		e.preventDefault();

		let option_action	= $(document.activeElement).data('action');

		if(option_action == 'reset'){
			if(confirm('确定要重置吗?') == false){
				return false;
			}
		}

		$.wpjam_option_action({
			option_action:	option_action,
			data:			$(this).serialize()
		});
	});
});