<?php
/*
Name: 样式定制
URI: https://mp.weixin.qq.com/s/Hpu1vz7zPUKEeHTF3wqyWw
Description: 对网站的前后台和登录界面的样式进行个性化设置。
Version: 2.0
*/
class WPJAM_Custom extends WPJAM_Option_Model{
	public static function get_sections(){
		return [
			'wpjam-custom'	=> ['title'=>'前台定制',	'fields'=>[
				'head'			=> ['title'=>'前台 Head 代码',	'type'=>'textarea',	'class'=>''],
				'footer'		=> ['title'=>'前台 Footer 代码',	'type'=>'textarea',	'class'=>''],
			]],
			'admin-custom'	=> ['title'=>'后台定制',	'fields'=>[
				'admin_logo'	=> ['title'=>'工具栏左上角 Logo',	'type'=>'img',	'item_type'=>'url',	'description'=>'建议大小：20x20。如果前台也显示工具栏，也会被修改。'],
				'admin_head'	=> ['title'=>'后台 Head 代码 ',	'type'=>'textarea',	'class'=>''],
				'admin_footer'	=> ['title'=>'后台 Footer 代码',	'type'=>'textarea',	'class'=>'']
			]],
			'login-custom'	=> ['title'=>'登录界面', 	'fields'=>[
				'login_head'				=> ['title'=>'登录界面 Head 代码',		'type'=>'textarea',	'class'=>''],
				'login_footer'				=> ['title'=>'登录界面 Footer 代码',	'type'=>'textarea',	'class'=>''],
				'login_redirect'			=> ['title'=>'登录之后跳转的页面',		'type'=>'text'],
				'disable_language_switcher'	=> ['title'=>'登录界面语言切换器',		'type'=>'checkbox',	'description'=>'屏蔽登录界面语言切换器'],
			]]
		];
	}

	public static function get_menu_page(){
		return [
			'parent'		=> 'wpjam-basic',
			'menu_title'	=> '样式定制',
			'function'		=> 'option',
			'order'			=> 21,
			'summary'		=> __FILE__,
		];
	}

	public static function ajax_fetch_signup_data(){
		$action	= wpjam_get_data_parameter('action');
		$type	= wpjam_get_data_parameter($action.'_type');
		$object	= wpjam_get_user_signup_object($type);

		if($object && method_exists($object, 'ajax_fetch_signup_data')){
			return $object->ajax_fetch_signup_data($action);
		}

		return new WP_Error('invalid_'.$action.'_type', '无效的类型');
	}

	public static function on_admin_bar_menu($wp_admin_bar){
		remove_action('admin_bar_menu',	'wp_admin_bar_wp_menu', 10);

		$logo	= self::get_setting('admin_logo');

		$wp_admin_bar->add_menu([
			'id'    => 'wp-logo',
			'title' => $logo ? '<img src="'.wpjam_get_thumbnail($logo, 40, 40).'" style="height:20px; padding:6px 0;">' : '<span class="ab-icon"></span>',
			'href'  => is_admin() ? self_admin_url() : site_url(),
			'meta'  => ['title'=>get_bloginfo('name')]
		]);
	}

	public static function filter_admin_title($admin_title){
		return str_replace(' &#8212; WordPress', '', $admin_title);
	}

	public static function filter_admin_footer_text($text){
		return self::get_setting('admin_footer') ?: '<span id="footer-thankyou">感谢使用<a href="https://cn.wordpress.org/" target="_blank">WordPress</a>进行创作。</span> | <a href="https://wpjam.com/" title="WordPress JAM" target="_blank">WordPress JAM</a>';
	}

	public static function filter_login_headerurl(){
		return home_url();
	}

	public static function filter_login_redirect($redirect_to, $request){
		return $request ?: (self::get_setting('login_redirect') ?: $redirect_to);
	}

	public static function on_custom(){
		$name	= current_action();

		if(in_array($name, ['wp_head', 'wp_footer'])){
			$name	= wpjam_remove_prefix($name, 'wp_');
		}

		echo self::get_setting($name);

		if(wpjam_basic_get_setting('optimized_by_wpjam') && $name == 'footer'){
			echo '<p id="optimized_by_wpjam_basic">Optimized by <a href="https://blog.wpjam.com/project/wpjam-basic/">WPJAM Basic</a>。</p>';
		}
	}

	public static function on_login_init(){
		$action	= wpjam_get_parameter('action', ['method'=>'REQUEST', 'default'=>'login']);

		wpjam_set_current_var('login_form_action', $action);

		if(in_array($action, ['login', 'bind'])){
			$objects	= wpjam_get_user_signups([$action=>true]);

			if($objects){
				add_action('login_form_'.$action,	[self::class, 'on_login_action']);
				add_action('login_form',			[self::class, 'on_login_form']);
				add_action('login_footer',			[self::class, 'on_login_footer'], 999);

				$action_type	= wpjam_get_parameter($action.'_type', ['method'=>'REQUEST']);

				if($action == 'login'){
					$action_type	= $action_type ?: apply_filters('wpjam_default_login_type', 'login');

					if(!$action_type && $_SERVER['REQUEST_METHOD'] == 'POST'){
						$action_type == 'login';
					}
				}

				if($action_type != 'login' && (!$action_type || !isset($objects[$action_type]))){
					$action_type	= array_key_first($objects);
				}

				wpjam_set_current_var($action.'_type', $action_type);
			}
		}

		wpjam_ajax_enqueue_scripts();

		wp_add_inline_style('login', join("\n", [
			'.login .message, .login #login_error{margin-bottom: 0;}',
			'.code_wrap label:last-child{display:flex;}',
			'.code_wrap input.button{margin-bottom:10px;}',
			'.login form .input, .login input[type=password], .login input[type=text]{font-size:20px; margin-bottom:10px;}',

			'p.login-types,p.bind-types{line-height:2; float:left; clear:left; margin-top:10px;}',
			'p.login-types a,p.bind-types a{text-decoration: none; display:block;}',
			'div.login-fields, div.bind-fields{margin-bottom:10px;}',
		]));
	}

	public static function on_login_action(){
		$action	= wpjam_get_current_var('login_form_action');

		if($action == 'login'){
			$action_type	= wpjam_get_current_var($action.'_type');
			$object			= wpjam_get_user_signup_object($action_type);

			if($object && $object->login_action && is_callable($object->login_action)){
				call_user_func($object->login_action);
			}

			if(empty($_COOKIE[TEST_COOKIE])){
				$_COOKIE[TEST_COOKIE]	= 'WP Cookie check';
			}
		}else{
			if(!is_user_logged_in()){
				wp_die('登录之后才能执行绑定操作！');
			}

			add_filter('login_display_language_dropdown', '__return_false');
		}
	}

	public static function on_login_form(){
		$action	= wpjam_get_current_var('login_form_action');

		echo '<p class="'.$action.'-types">';

		$data	= ['action'=>$action];

		foreach(wpjam_get_user_signups([$action=>true]) as $name => $object){
			$attr	= wpjam_get_ajax_attribute_string('fetch-signup-data', $data+[$action.'_type'=>$name]);
			$title	= $action == 'bind' ? '绑定'.$object->title : $object->login_title;

			echo '<a class="'.$action.'-type '.$name.'" href="javascript:;" data-'.$action.'_type="'.$name.'" '.$attr.'>'.$title.'</a>';

			add_action('login_footer',	[$object, $action.'_script'], 1000);
		}

		if($action == 'login'){
			echo '<a class="login-type login" href="javascript:;" data-login_type="login">使用账号和密码登录</a>';
		}

		echo '</p>';
	}

	public static function on_login_footer(){
		$action	= wpjam_get_current_var('login_form_action');

		?>
		<script type="text/javascript">
		jQuery(function($){
			let action	= <?php echo wpjam_json_encode($action); ?>;

			$('body').on('submit', '#loginform', function(e){
				if($(this).data('action')){
					e.preventDefault();

					$('div#login_error').remove();
					$(this).removeClass('shake');

					$(this).wpjam_submit(function(data){
						if(data.errcode){
							$('h1').after('<div id="login_error">'+data.errmsg+'</div>');
							$(this).addClass('shake');
						}else{
							if(action == 'bind'){
								window.location.reload();
							}else{
								if($('body').hasClass('interim-login')){
									$('body').addClass('interim-login-success');
									$(window.parent.document).find('.wp-auth-check-close').click();
								}else{
									window.location.href	= $.trim($('input[name="redirect_to"]').val());
								}
							}
						}
					});
				}
			});

			$('body').on('click', '.'+action+'-type', function(e){
				e.preventDefault();

				$('div#login_error').remove();

				$('#loginform').removeClass('shake').hide();

				if(action == 'login' && $(this).data(action+'_type') == 'login'){
					$('p#nav').show();

					$('div.'+action+'-fields').html(login_fields);
					$('#loginform').data('action', '').slideDown(300);

					$('a.login-type').show();
					$(this).hide();
				}else{
					$('p#nav').hide();

					$(this).wpjam_action(function(data){
						if(data.errcode != 0){
							alert(data.errmsg);
						}else{
							$('div.'+action+'-fields').html(data.fields);
							$('#loginform').data('action', data.action).data('nonce', data.nonce).slideDown(300);

							if(action == 'bind'){
								$('input#wp-submit').val(data.submit_text);
							}

							$('a.'+action+'-type').show();
							$(this).hide();
						}
					});
				}

				window.history.replaceState(null, null, action_url+action+'_type='+$(this).data(action+'_type'));

				return true;
			});

			$('p.'+action+'-types').insertBefore('p.submit');

			$('<div class="'+action+'-fields">').prependTo('#loginform');

			if(action == 'bind'){
				$('title').html('绑定');
				$('input#user_login').parent().remove();
				$('div.user-pass-wrap, p.forgetmenot').remove();
			}else{
				$('input#user_login').parent().appendTo('div.login-fields');
				$('div.user-pass-wrap, p.forgetmenot').appendTo('div.login-fields');
			}

			let login_fields	= $('div.'+action+'-fields').html();
			let action_type		= <?php echo wpjam_json_encode(wpjam_get_current_var($action.'_type')); ?>;
			let action_url		= <?php echo wpjam_json_encode(remove_query_arg([$action.'_type'], wpjam_get_current_page_url())); ?>;

			action_url	+= action_url.indexOf('?') >= 0 ? '&' : '?';

			if(action == 'login'){
				$('#loginform').prop('action', '<?php echo wp_login_url(); ?>?login_type=login');
			}

			if(action == 'login' && action_type == 'login'){
				$('body a.'+action+'-type.'+action_type).hide();
			}else{
				$('body a.'+action+'-type.'+action_type).click();
			}
		});
		</script>
		<?php
	}

	public static function on_admin_init(){
		if(wpjam_get_user_signups(['bind'=>true])){
			wpjam_add_menu_page('wpjam-bind', [
				'parent'		=> 'users',
				'menu_title'	=> '账号绑定',			
				'capability'	=> 'read',
				'function'		=> 'tab',
				'order'			=> 20,
				'load_callback'	=> [self::class, 'plugin_page_load']
			]);
		}

		if(wpjam_get_user_signups()){
			wpjam_register_builtin_page_load('user-binds', [
				'base'		=> 'users', 
				'callback'	=> [self::class, 'builtin_page_load']
			]);
		}
	}

	public static function plugin_page_load(){
		$user_id	= get_current_user_id();
		
		foreach(wpjam_get_user_signups(['bind'=>true]) as $bind_name => $object){
			wpjam_register_plugin_page_tab($bind_name, [
				'title'			=> $object->title,
				'bind_name'		=> $bind_name,
				'capability'	=> 'read',
				'function'		=> 'form',	
				'form_name'		=> $bind_name.'_bind',
				'fields'		=> [$object, 'get_bind_fields'],
				'callback'		=> [$object, 'bind_callback'],
				'submit_text'	=> $object->get_openid($user_id) ? '解除绑定' : '立刻绑定',
				'response'		=> 'redirect'
			]);

			if(!wp_doing_ajax()){
				add_action('admin_footer', [$object, 'bind_script']);
			}
		}
	}

	public static function builtin_page_load(){
		wpjam_register_list_table_column('openid', [
			'title'		=> '绑定账号',
			'order'		=> 20,
			'callback'	=> [self::class, 'openid_column_callback']
		]);
	}

	public static function openid_column_callback($user_id){
		$values	= [];

		foreach(wpjam_get_user_signups() as $object){
			$openid = $object->get_openid($user_id);

			if($openid){
				$values[]	= $object->title.'：<br />'.$openid;
			}
		}

		return $values ? implode('<br /><br />', $values) : '';
	}

	public static function init(){
		wpjam_register_ajax('fetch-signup-data', [
			'nopriv'	=> true,
			'verify'	=> false,
			'callback'	=> [self::class, 'ajax_fetch_signup_data']
		]);

		wpjam_register_bind('phone', '', ['domain'=>'@phone.sms']);

		add_action('admin_bar_menu',	[self::class, 'on_admin_bar_menu'], 1);

		if(is_admin()){
			add_action('admin_head',		[self::class, 'on_custom']);
			add_filter('admin_title', 		[self::class, 'filter_admin_title']);
			add_filter('admin_footer_text',	[self::class, 'filter_admin_footer_text']);

			add_action('wpjam_admin_init',	[self::class, 'on_admin_init']);
		}elseif(wpjam_is_login()){
			add_filter('login_headerurl',	[self::class, 'filter_login_headerurl']);
			add_filter('login_headertext',	'get_bloginfo');

			add_action('login_head', 		[self::class, 'on_custom']);
			add_action('login_footer',		[self::class, 'on_custom']);
			add_filter('login_redirect',	[self::class, 'filter_login_redirect'], 10, 2);

			if(wp_using_ext_object_cache()){
				add_action('login_init',	[self::class, 'on_login_init']);
			}

			if(self::get_setting('disable_language_switcher')){
				add_filter('login_display_language_dropdown',	'__return_false');
			}
		}else{
			add_action('wp_head',	[self::class, 'on_custom'], 1);
			add_action('wp_footer', [self::class, 'on_custom'], 99);
		}
	}
}

wpjam_register_option('wpjam-custom', [
	'site_default'	=> true,
	'model'			=> 'WPJAM_Custom',
]);

