<?php
// 后端字段解析函数
function wpjam_parse_field($field,$sub=false){
	$field['key']	= isset($field['key'])?$field['key']:'';
	$field['name']	= isset($field['name'])?$field['name']:$field['key'];
	$field['type']	= isset($field['type'])?$field['type']:'text';
	$field['value']	= isset($field['value'])?$field['value']:'';

	if(is_admin() && $field['type'] == 'file'){
		$field['type'] = 'image';
	}

	if($field['type'] ==  'mulit_image' || $field['type'] == 'multi_image' || $field['type'] == 'mulit-image' || $field['type'] == 'mulit-image'){
		$field['type']	= 'mu-image';
	}elseif($field['type'] == 'mulit_text' || $field['type'] == 'multi_text' || $field['type'] == 'mulit-text' || $field['type'] == 'multi-text'){
		$field['type']	= 'mu-text';
	}elseif($field['type'] == 'br' ){
		$field['type']	= 'view';
	}

	$default_classes = array(
		'textarea'	=> 'large-text',
		'checkbox'	=> '',
		'radio'		=> '',
		'file'		=> '',
		'select'	=> '',
		'color'		=> ''
	);

	$class	= isset($field['class'])?$field['class']:(isset($default_classes[$field['type']])?$default_classes[$field['type']]:'regular-text');
	$field['class']	= 'type-'.$field['type'].' '.$class ;

	$field['description']	= isset($field['description'])?$field['description']:'';

	if($field['description']){
		if($field['type'] == 'view' || $field['type'] == 'hr'){
			$field['description'] = '';
		}elseif($field['type'] == 'checkbox'){
			$field['description']	= ' <label for="'.$field['key'].'">'.$field['description'].'</label>';	
		}else{
			if($sub === false){
				$field['description']	= '<p>'.$field['description'].'</p>';
			}
		}
	}
	
	$datalist = '';
	if(isset($field['list']) && !empty($field['options'])){
		$datalist	.= '<datalist id="'.$field['list'].'">';
		foreach ($field['options'] as $option) {
			if(is_array($option)){
				$datalist	.= '<option label="'.$option['label'].'" value="'.$option['value'].'" />';
			}else{
				$datalist	.= '<option value="'.$option.'" />';
			}
		}
		$datalist	.= '</datalist>';
	}
	
	$field['datalist'] = $datalist;
	
	$extra	= '';
	foreach ($field as $attr_key => $attr_value) {
		if(is_numeric($attr_key)){
			$extra .= $attr_value.' ';
			if(strtolower(trim($attr_value)) == 'readonly'){
				$field['readonly']	= 1;
			}
			if(strtolower(trim($attr_value)) == 'disabled'){
				$field['disabled']	= 1;
			}
		}elseif( !in_array($attr_key, array('fields','type','name','title','key','description','class','value','default','options','show_admin_column','sortable_column','taxonomies','datalist','settings') ) ) {
			$extra .= $attr_key.'="'.$attr_value.'" ';
		}
	}

	$field['extra'] = $extra;

	return $field;
}

// 获取表单 HTML
function wpjam_get_field_html($field, $sub=false){
	extract(wpjam_parse_field($field,$sub));

	switch ($type) {
		case 'image':
			$field_html	= wpjam_get_input_field_html('url', $name, $key, $class, $value, $extra).'<input type="button" class="wpjam_upload button" value="选择图片">';
			break;

		case 'color':
			$extra		.= 'style="padding:0;margin:0;border:0;background:none;box-shadow:none;-webkit-box-shadow:none;height:28px;"';
			$field_html	= wpjam_get_input_field_html($type, $name, $key, $class, $value, $extra);
			break;

		case 'file':
			$value		= ($value)?'<span style="background-color:yellow; padding:2px;margin:0 4px 0 0;">已上传</span>':'';
			if(empty($field['formenctype'])){
				$extra	.= 'formenctype="multipart/form-data" ';
			}
			$field_html	= $value.wpjam_get_input_field_html($type, $key, $key, $class, '', $extra);
			break;

		case 'range':
			$extra		.=	' onchange="jQuery(\'#'.$key.'_span\').html(jQuery(\'#'.$key.'\').val());"';
			$field_html	= wpjam_get_input_field_html($type, $name, $key, $class, $value, $extra).' <span id="'.$key.'_span">'.$value.'</span>';
			break;

		case 'checkbox':
			if(!empty($field['options'])){
				$field_html	= '';
				foreach ($field['options'] as $option_value => $option_title){ 
					if($value && in_array($option_value, $value)){
						$checked	= " checked='checked'";
					}else{
						$checked	= '';
					}
					$field_html .= wpjam_get_input_field_html($type, $name.'[]', '', $class, $option_value, $checked.$extra).$option_title.'&nbsp;&nbsp;&nbsp;';
				}
			}else{
				$extra		.= checked("1", $value, false);
				$field_html	= wpjam_get_input_field_html($type, $name, $key, $class, '1', $extra);
			}
			break;

		case 'textarea':
			$rows = isset($field['rows'])?$field['rows']:6;
			$field_html = '<textarea name="'.$name.'" id="'.$key.'" class="'.$class.' code" rows="'.$rows.'" cols="50" '.$extra.' >'.esc_textarea($value).'</textarea>';
			break;

		case 'editor':
			$field_html = '';
			ob_start();
			$settings = isset($field['settings'])?$field['settings']:array();
			wp_editor($value, $key, $settings);
			$field_html = ob_get_contents();
			ob_end_clean();
			break;

		case 'select':
			$field_html	= '<select name="'.$name.'" id="'. $key.'" class="'.$class.'" '.$extra.' >';
			if(!empty($field['options'])){
				foreach ($field['options'] as $option_value => $option_title){ 
					$field_html .= '<option value="'.$option_value.'" '.selected($option_value, $value, false).'>'.$option_title.'</option>';
				}
			}
			$field_html .= '</select>';
			break;

		case 'radio':
			$field_html	= '';
			if(!empty($field['options'])){
				if($value == ''){
					$values	= array_keys($field['options']);
					$value	= $values[0];
				}
				$sep = (count($field['options'])>3)?'<br />':'&nbsp;&nbsp;&nbsp;';

				foreach ($field['options'] as $option_value => $option_title) {
					$checked	= checked($option_value, $value, false);
					$field_html	.= '<input type="radio" name="'.$name.'" id="'.$key.'_'.$option_value.'" class="'.$class.'" value="'.$option_value.'" '.$extra.$checked.' /><label for="'.$key.'_'.$option_value.'">'.$option_title."</label>".$sep;
				}
			}
			break;

		case 'mu-image':
			$field_html  = '';
			if(is_array($value)){
				foreach($value as $image){
					if(!empty($image)){
						$field_html .= '<span><input type="text" name="'.$name.'[]" id="'.$key.'" class="'.$class.'" value="'.esc_attr($image).'"  /><a href="javascript:;" class="button del_item">删除</a><br /></span>';
					}
				}
			}
			$field_html  .= '<span><input type="text" name="'.$name.'[]" id="'.$key.'" value="" class="'.$class.'" /><input type="button" class="wpjam_multi_upload button" value="选择图片[多选]" title="按住Ctrl点击鼠标左键可以选择多张图片"></span>';
			break;

		case 'mu-img':
			$field_html  = '';

			if(is_array($value)){
				$i = 0;
				foreach($value as $img_id){
					if(!empty($img_id)){
						$img	= wp_get_attachment_image_src($img_id,'full');
						$img_src= $img[0];

						if(function_exists('wpjam_get_thumbnail')){
							$img_src = wpjam_get_thumbnail($img_src, 200);
						}

						$field_html .= '<span class="mu_img"><img width="100" src="'.$img_src.'" alt=""><input type="hidden" name="'.$name.'[]" id="'.$key.'" class="'.$class.'" value="'.$img_id.'"  /><a href="javascript:;" class="del_item">—</a></span>';

						$i++;

						if($i%5 == 0){
							$field_html .= '<br />';
						}
					}
				}
			}
			$field_html  .= '<span style="display:block;"><input type="hidden" name="'.$name.'[]" id="'.$key.'" value="" class="'.$class.'" /><input type="button" class="wpjam_multi_upload2 button" value="选择图片[多选]" title="按住Ctrl点击鼠标左键可以选择多张图片"></span>';
			break;

		case 'mu-text':
			$field_html  = '';
			if(is_array($value)){
				foreach($value as $item){
					if(!empty($item)){
						$field_html .= '<span><input type="text" name="'.$name.'[]" id="'. $key.'" value="'.esc_attr($item).'"  class="'.$class.'" /><a href="javascript:;" class="button del_item">删除</a><br /></span>';
					}
				}
			}
			$field_html .= '<span><input type="text" name="'.$name.'[]" id="'.$key.'" value="" class="'.$class.'" /><a class="wpjam_multi_text button">添加选项</a></span>';
			break;

		case 'view':
			if(!empty($field['options'])){
				$value		= ($value)?$value:0;
				$field_html	= isset($field['options'][$value])?$field['options'][$value]:'';
			}else{
				$field_html	= $value;
			}
			
			break;

		case 'hr':
			$field_html	= '<hr />';
			break;

		case 'fieldset':
			$field_html  = '';
			if(!empty($fields)){
				foreach ($fields as $sub_key=>$sub_field) {
					$sub_field['key']	= $sub_key;
					// $sub_field['value']	= isset($sub_field['value'])?$sub_field['value']:(isset($value[$sub_key])?$value[$sub_key]:'');
					$field_title 		= (!empty($sub_field['title']))?'<label class="sub_field_label" for="'.$sub_key.'">'.$sub_field['title'].'</label>':'';
					$field_html			.= '<p id="p_'.$sub_key.'">'.$field_title.wpjam_get_field_html($sub_field,$sub=true).'</p>';
				}
			}
			break;

		case '':
			$field_html	= $value;
			break;
		
		default:
			$field_html = wpjam_get_input_field_html($type, $name, $key, $class, $value, $extra);
			break;
	}

	return apply_filters('wpjam_field_html', $field_html.$datalist.$description, $field);
}

// 获取 input 表单 HTML
function wpjam_get_input_field_html($type, $name, $key, $class, $value, $extra=''){
	$value	= ($value)?'value="'.esc_attr($value).'"':'';
	$class	= ($class)?'class="'.$class.'"':'';
	return '<input type="'.$type.'" name="'.$name.'" id="'.$key.'" '.$class.' '.$value.' '.$extra.' />';
}

// 获取后台自定义 POST 数据
function wpjam_get_form_post($form_fields, $nonce_action='', $capability='manage_options'){
	global $plugin_page;
	$nonce_action	= $nonce_action ? $nonce_action : $plugin_page;

	check_admin_referer($nonce_action);

	if( !current_user_can( $capability )){
		ob_clean();
		wp_die('无权限');
	}

	$data = array();

	foreach ($form_fields as $key => $form_field) {
		if($form_field['type'] == 'fieldset'){
			if($form_field['fields']){
				foreach ($form_field['fields'] as $sub_key => $sub_form_field) {
					$field_value = wpjam_form_field_validate($sub_key, $sub_form_field);
					if($field_value === false){
						continue;
					}else{
						$data[$sub_key] = $field_value;
					}
				}
			}
		}else{
			$field_value = wpjam_form_field_validate($key, $form_field);
			if($field_value === false){
				continue;
			}else{
				$data[$key] = $field_value;
			}
		}
	}

	return $data;
}

function wpjam_form_field_validate($key, $field){
	$field	= wpjam_parse_field($field);
	$type	= $field['type'];

	if($type == 'view' || $type == 'hr'){
		return false;
	}

	if(!empty($field['readonly']) || !empty($field['diabled'])){
		return false;
	}

	if(isset($field['show_admin_column']) && ($field['show_admin_column'] === 'only')){
		return false;
	}

	$value	= isset($_POST[$key])?$_POST[$key]:'';

	if(in_array($type, array('mu-image','mu-text','mu-img'))){
		if(!is_array($value)){
			$value = '';
		}else{
			foreach($value as $item_key =>$item_value){
				$item_value = trim($item_value);
				if(empty($item_value)){
					unset($value[$item_key]);
				}
			}
		}
	}

	if(!is_array($value)){
		$value	= stripslashes(trim($value));	
	}

	if($type == 'textarea'){
		$value	= str_replace("\r\n", "\n",$value);
	}

	return $value;
}

// 设置自定义页面的字段
function wpjam_get_form_fields($admin_column = false){
	global $plugin_page;
	$form_fields = apply_filters($plugin_page.'_fields', array());

	if($form_fields){
		foreach($form_fields as $key => $field){
			if($field['type'] == 'fieldset'){
				foreach ($field['fields'] as $sub_key => $sub_field) {
					if($admin_column){
						if(empty($sub_field['show_admin_column'])){
							unset($form_fields[$key]['fields'][$sub_key]);
						}
					}else{
						if(isset($sub_field['show_admin_column']) && $sub_field['show_admin_column'] === 'only'){
							unset($form_fields[$key]['fields'][$sub_key]);
						}
					}
				}
				if(empty($form_fields[$key]['fields'])){
					unset($form_fields[$key]);
				}
			}else{
				if($admin_column){
					if(empty($field['show_admin_column'])){
						unset($form_fields[$key]);
					}
				}else{
					if(isset($field['show_admin_column']) && $field['show_admin_column'] === 'only'){
						unset($form_fields[$key]);
					}
				}
			}
		}
	}

	return $form_fields;
}

// 编辑表单
function wpjam_form($form_fields, $form_url, $nonce_action='', $submit_text=''){
	global $plugin_page;
	$nonce_action	= $nonce_action ? $nonce_action : $plugin_page;

	wpjam_admin_errors();	// 显示错误
	?>
	<form method="post" action="<?php echo $form_url; ?>" enctype="multipart/form-data" id="form">
		<?php wpjam_form_fields($form_fields); ?>
		<?php wp_nonce_field($nonce_action);?>
		<?php wp_original_referer_field(true, 'previous');?>
		<?php if($submit_text!==false){ submit_button($submit_text); } ?>
	</form>
	<?php
}

// 显示字段
function wpjam_form_fields($fields, $fields_type = 'table', $item_class=''){
	$item_class			= ($item_class)?' class="'.$item_class.'"':''; 

	$new_fields = array();
	foreach($fields as $key => $field){ 

		if(isset($field['show_admin_column']) && ($field['show_admin_column'] === 'only')){
			continue;
		}
		
		$field['key']		= $key;
		$field_html			= wpjam_get_field_html($field);
		$field_title 		= (!empty($field['title']))?($field['type']=='fieldset'?$field['title']:'<label for="'.$key.'">'.$field['title'].'</label>'):'';

		$new_fields[$key]	= array('title'=>$field_title, 'html'=>$field_html, 'type'=>$field['type']);
	}
	?>
	<?php if($fields_type == 'list'){ ?>
	<ul>
	<?php foreach ($new_fields as $key=>$field) { ?>
		<li<?php echo $item_class; ?>><?php echo $field['title']; ?> <?php echo $field['html']; ?> </li>
	<?php } ?>
	</ul>
	<?php } elseif($fields_type == 'table'){ ?>
	<table class="form-table" cellspacing="0">
		<tbody>
		<?php foreach ($new_fields as $key=>$field) { ?>
		<?php if($field['type'] == 'hidden'){ ?>
			<?php echo $field['html']; ?>
		<?php }else{ ?>
			<tr<?php echo $item_class; ?> valign="top" id="tr_<?php echo $key; ?>">
			<?php if($field['title']) { ?>
				<th scope="row"><?php echo $field['title']; ?></th>
				<td><?php echo $field['html']; ?></td>
			<?php } else { ?>
				<th colspan="2"><?php echo $field['html']; ?></th>
			<?php } ?>
			</tr>
		<?php }?>
		<?php } ?>
		</tbody>
	</table>
	<?php } elseif($fields_type == 'tr') { ?>
		<?php foreach ($new_fields as $key=>$field) { ?>
		<?php if($field['type'] == 'hidden'){ ?>
			<?php echo $field['html']; ?>
		<?php }else{?>
			<tr<?php echo $item_class; ?> id="tr_<?php echo $key; ?>">
			<?php if($field['title']) { ?>
				<th scope="row"><?php echo $field['title']; ?></th>
				<td><?php echo $field['html']; ?></td>
			<?php } else { ?>
				<th colspan="2"><?php echo $field['html']; ?></th>
			<?php } ?>
			</tr>
		<?php } ?>
		<?php } ?>
	<?php } elseif($fields_type == 'div') { ?> 
		<?php foreach ($new_fields as $key=>$field) { ?>
		<?php if($field['type'] == 'hidden'){ ?>
			<?php echo $field['html']; ?>
		<?php }else{?>
			<div<?php echo $item_class; ?> id="div_<?php echo $key; ?>">
				<?php echo $field['title']; ?>
				<?php echo $field['html']; ?>
			</div>
		<?php } ?>
		<?php } ?>
	<?php } ?>
	<?php
}

// 后台表单 JS
add_action('admin_enqueue_scripts', 'wpjam_upload_image_enqueue_scripts');
function wpjam_upload_image_enqueue_scripts() {
	wp_enqueue_media();
	wp_enqueue_script('wpjam-setting', WPJAM_BASIC_PLUGIN_URL.'/include/static/wpjam.js', array('jquery'));
	wp_localize_script('wpjam-setting', 'wpjam_setting', array(
		'ajax_url'	=> admin_url('admin-ajax.php'),
		'nonce'		=> wp_create_nonce('wpjam_setting_nonce')
	));

	wp_enqueue_style('wpjam-style', WPJAM_BASIC_PLUGIN_URL.'/include/static/wpjam-style.css');
}