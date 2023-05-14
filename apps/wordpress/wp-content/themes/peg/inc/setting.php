<?php
if(is_admin()){
	 $options = array(
        //开始第一个选项标签数组
        array(
            'title' => '站点设置',//标签显示的文字
            'id'    => 'one',//标签的ID
            'type'  => 'panelstart' //顶部标签的类型
        ),
    
    
    array(
		'name' => '最新版本',
		'id' => 'tony_version',
		'type' => 'text',
		'op_des' => '博客主题版本'
		),
	array(
		'name' => '下载地址',
		'id' => 'tony_download',
		'type' => 'text',
		'op_des' => '主题下载地址'
		),
    
	array(
		'name' => '站点Head关键词',
		'id' => 'king_gjc',
		'type' => 'text',
		'std' => 'xxx,xxx,xxx,xxx',
		'op_des' => '此博客的关键词，以英文半角逗号分隔，将添加在博客head部分'
		),
	array(
		'name' => '站点描述',
		'id' => 'king_ms',
		'type' => 'textarea',
		'std' => 'xxxxxxxxxxxx',
		'op_des' => '此博客的描述，将添加在博客head部分与首页顶部'
		),
	array(
		'name' => '站点ICO图标',
		'id' => 'king_ico',
		'type' => 'text',
		'std' => 'xxxxxxxxxxxx',
		'op_des' => '此博客的favicon图标链接地址'
		),
	array(
		'name' => '站点Logo图标',
		'id' => 'king_logo',
		'type' => 'text',
		'std' => 'xxxxxxxxxxxx',
		'op_des' => '此博客的Logo图标链接地址'
		),
	array(
		'name' => '站长统计代码',
		'id' => 'king_zztj',
		'type' => 'textarea',
		'std' => '<script>xxxxxxxxx</script>',
		'op_des' => '包含script标签的站长统计代码，将添加在博客head部分')
	,
	array(
            'type'  => 'panelend'//标签段的结束
        ),
        
        
    array(
            'title' => '内容设置',//标签显示的文字
            'id'    => 'two',//标签的ID
            'type'  => 'panelstart' //顶部标签的类型
        ),
        
	array(
		'name' => '首页排除分类',
		'id' => 'king_index_exclude',
		'type' => 'text',
		'std' => '1,2,3',
		'op_des' => '在站点首页不显示的分类ID，以英文半角逗号分隔'
		),
    array(
		'name' => '文章目录引索',
		'id' => 'king_single_index',
		'type' => 'select',
		'op_des' => '在文章左方展示的目录标题所对应的html标签，如h3',
		'options' => array('h1','h2','h3','h4')
		),
	array(
		'name' => '顶栏排除分类',
		'id' => 'king_index_cate_exclude',
		'type' => 'text',
		'std' => '1,2,3',
		'op_des' => '在站点顶栏不显示的分类ID，以英文半角逗号分隔'
		),
	array(
		'name' => '展示分类标签的分类',
		'id' => 'king_cate_cate',
		'type' => 'number',
		'std' => 'xxx',
		'op_des' => '该分类将在文章列表展示分类名与第一个标签')
	,
	array(
		'name' => '无标签时占位内容',
		'id' => 'king_cate_cate_ph',
		'type' => 'text',
		'std' => 'xxx',
		'op_des' => '展示分类标签的分类不存在第一个标签时的占位内容')
	,
	array(
		'name' => '友情链接分类',
		'id' => 'king_fre_cate',
		'type' => 'number',
		'std' => 'xxx',
		'op_des' => '友情链接分类将展示不同样式的文章列表')
	,
	array(
		'name' => '作品集分类',
		'id' => 'king_wor_cate',
		'type' => 'number',
		'std' => 'xxx',
		'op_des' => '作品集分类将展示与友情链接相同样式的文章列表与不同的描述')
	,
	array(
            'type'  => 'panelend'//标签段的结束
        ),
        
        
    
    array(
            'title' => '导航设置',//标签显示的文字
            'id'    => 'three',//标签的ID
            'type'  => 'panelstart' //顶部标签的类型
        ),
    
    array(
		'name' => '「关于我」页面链接',
		'id' => 'king_abt_url',
		'type' => 'text',
		'std' => 'xxxxxxxxxxxx',
		'op_des' => '展示在导航栏上的关于我链接'
		),
	array(
		'name' => '导航栏第二页面链接',
		'id' => 'king_nav_pu',
		'type' => 'text',
		'std' => 'xxxxxxxxxxxx',
		'op_des' => '展示在导航栏上的第二页面链接'
		),
	array(
		'name' => '导航栏第二页面名称',
		'id' => 'king_nav_pn',
		'type' => 'text',
		'std' => 'xxxxxxxxxxxx',
		'op_des' => '展示在导航栏上的第二页面名称'
		),
    
    array(
            'type'  => 'panelend'//标签段的结束
        ),
    
    );
    //主题后台设置已完成，下面可以不用看了
    function git_add_theme_options_page() {
        global $options;
        if ($_GET['page'] == basename(__FILE__)) {
            if ('update' == $_REQUEST['action']) {
                foreach($options as $value) {
                    if (isset($_REQUEST[$value['id']])) {
                        update_option($value['id'], $_REQUEST[$value['id']]);
                    } else {
                        delete_option($value['id']);
                    }
                }
                update_option('git_options_setup', true);
                header('Location: themes.php?page=setting.php&update=true');
                die;
            } else if( 'reset' == $_REQUEST['action'] ) {
                foreach ($options as $value) {
                    delete_option($value['id']);
                }
                delete_option('git_options_setup');
                header('Location: themes.php?page=setting.php&reset=true');
                die;
            }
        }
        add_theme_page('主题设置', '主题设置', 'edit_theme_options', basename(__FILE__) , 'git_options_page');
    }
    add_action('admin_menu', 'git_add_theme_options_page');
     
    function git_options_page() {
        global $options;
        $optionsSetup = get_option('git_options_setup') != '';
        if ($_REQUEST['update']) echo '<div class="updated" style="margin-top:15px"><p><strong>设置已保存</strong></p></div>';
        if ($_REQUEST['reset']) echo '<div class="updated" style="margin-top:15px"><p><strong>设置已重置</strong></p></div>';
    ?>
     
    <div class="wrap" style="width: 47%;margin: 10vh auto;">
        <h1 style="font-weight: 600;font-size: 2.5rem;">主题设置</h1>
        <p style="margin: 4px 0;color: #777;letter-spacing: .4px;">本主题基于免费主题 King 修改,前端使用了 Vue.js,请务必开启Wordpress Rest Api功能<br/>本主题已开源,尊重作者版权:<a target="_blank" href="https://github.com/ttttonyhe/ouorz" style="color: #555;text-decoration: none;margin-left: 5px;">https://github.com/ttttonyhe/ouorz_theme</a></p>
        <div style="background: #f7f8f9;padding: 5px 20px;box-shadow: rgba(0, 0, 0, 0.08) 0px 1px 2px !important;border-radius: 4px;margin: 20px 0;">
            <?php admin_show_category(); ?>
        </div>
        
        <form method="post" style="box-shadow: rgba(0, 0, 0, 0.08) 0px 1px 2px !important;background: #f7f8f9;padding: 10px 20px;border-radius:4px">
            <h2 class="nav-tab-wrapper" style="border:none">
    <?php
    $panelIndex = 0;
    foreach ($options as $value ) {
        if($panelIndex !== 0){ $margin = 'margin-left:10px'; }
        if ( $value['type'] == 'panelstart' ) echo '<a href="#' . $value['id'] . '" class="nav-tab' . ( $panelIndex == 0 ? ' nav-tab-active' : '' ) . '" style="border: none;background: #fff;border-radius: 4px;padding: 5px 15px;margin: 0px;box-shadow: rgba(0, 0, 0, 0.08) 0px 1px 2px !important;'.$margin.'">' . $value['title'] . '</a>';
        $panelIndex++;
    }
    ?>
    </h2>
    <!-- 开始建立选项类型 -->
    <?php
    $panelIndex = 0;
    foreach ($options as $value) {
    switch ( $value['type'] ) {
        case 'panelstart'://最高标签
            echo '<div class="panel" id="' . $value['id'] . '" ' . ( $panelIndex == 0 ? ' style="display:block"' : '' ) . '><table class="form-table">';
            $panelIndex++;
            break;
        case 'panelend':
            echo '</table></div>';
            break;
        case 'subtitle':
            echo '<tr><th colspan="2"><h3>' . $value['title'] . '</h3></th></tr>';
            break;
        case 'text':
    ?>
    <tr>
        <th><label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label></th>
        <td>
            <label>
            <input name="<?php echo $value['id']; ?>" class="regular-text" id="<?php echo $value['id']; ?>" type='text' value="<?php if ( $optionsSetup || get_option( $value['id'] ) != '') { echo stripslashes(get_option( $value['id'] )); } else { echo $value['std']; } ?>" />
            <span class="description"><?php echo $value['desc']; ?></span>
            </label>
            <p style="color: #999;margin-left: 3px;letter-spacing: .5px;"><?php echo $value['op_des'] ?></p>
        </td>
    </tr>
    <?php
        break;
        case 'number':
    ?>
    <tr>
        <th><label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label></th>
        <td>
            <label>
            <input name="<?php echo $value['id']; ?>" class="small-text" id="<?php echo $value['id']; ?>" type="number" value="<?php if ( $optionsSetup || get_option( $value['id'] ) != '') { echo get_option( $value['id'] ); } else { echo $value['std']; } ?>" />
            <span class="description"><?php echo $value['desc']; ?></span>
            </label>
            <p style="color: #999;margin-left: 3px;letter-spacing: .5px;"><?php echo $value['op_des'] ?></p>
        </td>
    </tr>
    <?php
        break;
        case 'password':
    ?>
    <tr>
        <th><label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label></th>
        <td>
            <label>
            <input name="<?php echo $value['id']; ?>" class="regular-text" id="<?php echo $value['id']; ?>" type="password" value="<?php if ( $optionsSetup || get_option( $value['id'] ) != '') { echo get_option( $value['id'] ); } else { echo $value['std']; } ?>" />
            <span class="description"><?php echo $value['desc']; ?></span>
            </label>
            <p style="color: #999;margin-left: 3px;letter-spacing: .5px;"><?php echo $value['op_des'] ?></p>
        </td>
    </tr>
    <?php
        break;
        case 'textarea':
    ?>
    <tr>
        <th><?php echo $value['name']; ?></th>
        <td>
            <p><label for="<?php echo $value['id']; ?>"><?php echo $value['desc']; ?></label></p>
            <p><textarea name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" rows="5" cols="50" class="large-text code"><?php if ( $optionsSetup || get_option( $value['id'] ) != '') { echo stripslashes(get_option( $value['id'] )); } else { echo $value['std']; } ?></textarea></p>
            <p style="color: #999;margin-left: 3px;letter-spacing: .5px;"><?php echo $value['op_des'] ?></p>
        </td>
    </tr>
    <?php
        break;
        case 'select':
    ?>
    <tr>
        <th><label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label></th>
        <td>
            <label>
                <select name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
                    <?php foreach ($value['options'] as $option) : ?>
                    <option value="<?php echo $option; ?>" <?php selected( get_option( $value['id'] ), $option); ?>>
                        <?php echo $option; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <span class="description"><?php echo $value['desc']; ?></span>
            </label>
            <p style="color: #999;margin-left: 3px;letter-spacing: .5px;"><?php echo $value['op_des'] ?></p>
        </td>
    </tr>
     
    <?php
        break;
        case 'radio':
    ?>
    <tr>
        <th><label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label></th>
        <td>
            <?php foreach ($value['options'] as $name => $option) : ?>
            <label>
                <input type="radio" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="<?php echo $option; ?>" <?php checked( get_option( $value['id'] ), $option); ?>>
                <?php echo $name; ?>
            </label>
            <?php endforeach; ?>
            <p><span class="description"><?php echo $value['desc']; ?></span></p>
            <p style="color: #999;margin-left: 3px;letter-spacing: .5px;"><?php echo $value['op_des'] ?></p>
        </td>
    </tr>
     
    <?php
        break;
        case 'checkbox':
    ?>
    <tr>
        <th><?php echo $value['name']; ?></th>
        <td>
            <label>
                <input type='checkbox' name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="1" <?php echo checked(get_option($value['id']), 1); ?> />
                <span><?php echo $value['desc']; ?></span>
            </label>
            <p style="color: #999;margin-left: 3px;letter-spacing: .5px;"><?php echo $value['op_des'] ?></p>
        </td>
    </tr>
     
    <?php
        break;
        case 'checkboxs':
    ?>
    <tr>
        <th><?php echo $value['name']; ?></th>
        <td>
            <?php $checkboxsValue = get_option( $value['id'] );
            if ( !is_array($checkboxsValue) ) $checkboxsValue = array();
            foreach ( $value['options'] as $id => $title ) : ?>
            <label>
                <input type="checkbox" name="<?php echo $value['id']; ?>[]" id="<?php echo $value['id']; ?>[]" value="<?php echo $id; ?>" <?php checked( in_array($id, $checkboxsValue), true); ?>>
                <?php echo $title; ?>
            </label>
            <?php endforeach; ?>
            <span class="description"><?php echo $value['desc']; ?></span>
            <p style="color: #999;margin-left: 3px;letter-spacing: .5px;"><?php echo $value['op_des'] ?></p>
        </td>
    </tr>
    <?php
        break;
    }
    }
    ?>
    <!-- 结束建立选项类型 -->
    <p class="submit">
        <input name="submit" type="submit" class="button button-primary" value="保存选项"/>
        <input type="hidden" name="action" value="update" />
    </p>
    </form>
    <form method="post" style="position: absolute;margin-top: -76px;margin-left: 110px;">
    <p>
        <input name="reset" type="submit" class="button button-secondary" value="重置选项" onclick="return confirm('你确定要重置选项吗？重置之后您的全部设置将被清空，您确定您没有搞错？？ ');"/>
        <input type="hidden" name="action" value="reset" />
    </p>
    </form>
    </div>
    <style>.panel{display:none}.panel h3{margin:0;font-size:1.2em}#panel_update ul{list-style-type:disc}.nav-tab-wrapper{clear:both}.nav-tab{position:relative}.nav-tab i:before{position:absolute;top:-10px;right:-8px;display:inline-block;padding:2px;border-radius:50%;background:#e14d43;color:#fff;content:"\f463";vertical-align:text-bottom;font:400 18px/1 dashicons;speak:none}#theme-options-search{display:none;float:right;margin-top:-34px;width:280px;font-weight:300;font-size:16px;line-height:1.5}.updated+#theme-options-search{margin-top:-91px}.wrap.searching .nav-tab-wrapper a,.wrap.searching .panel tr,#attrselector{display:none}.wrap.searching .panel{display:block !important}#attrselector[attrselector*=ok]{display:block}</style>
    <style id="theme-options-filter"></style>
    <div id="attrselector" attrselector="ok" ></div>
    <script>
    jQuery(function ($) {
        $(".nav-tab").click(function () {
            $(this).addClass("nav-tab-active").siblings().removeClass("nav-tab-active");
            $(".panel").hide();
            $($(this).attr("href")).show();
            return false;
        });
     
        var themeOptionsFilter = $("#theme-options-filter");
        themeOptionsFilter.text("ok");
        if ($("#attrselector").is(":visible") && themeOptionsFilter.text() != "") {
            $(".panel tr").each(function (el) {
                $(this).attr("data-searchtext", $(this).text().replace(/\r|\n/g, '').replace(/ +/g, ' ').toLowerCase());
            });
     
            var wrap = $(".wrap");
            $("#theme-options-search").show().on("input propertychange", function () {
                var text = $(this).val().replace(/^ +| +$/, "").toLowerCase();
                if (text != "") {
                    wrap.addClass("searching");
                    themeOptionsFilter.text(".wrap.searching .panel tr[data-searchtext*='" + text + "']{display:block}");
                } else {
                    wrap.removeClass("searching");
                    themeOptionsFilter.text("");
                };
            });
        };
    });
    </script>
    <?php
    }
    //启用主题后自动跳转至选项页面
    global $pagenow;
        if ( is_admin() && isset( $_GET['activated'] ) && $pagenow == 'setting.php' )
        {
            wp_redirect( admin_url( 'themes.php?page=functions.php' ) );
        exit;
    }
    /*
    function git_enqueue_pointer_script_style( $hook_suffix ) {
        $enqueue_pointer_script_style = false;
        $dismissed_pointers = explode( ',', get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
        if( !in_array( 'git_options_pointer', $dismissed_pointers ) ) {
            $enqueue_pointer_script_style = true;
            add_action( 'admin_print_footer_scripts', 'git_pointer_print_scripts' );
        }
        if( $enqueue_pointer_script_style ) {
            wp_enqueue_style( 'wp-pointer' );
            wp_enqueue_script( 'wp-pointer' );
        }
    }
    add_action( 'admin_enqueue_scripts', 'git_enqueue_pointer_script_style' );
    
    
    
    function git_pointer_print_scripts() {
        ?>
        <script>
        jQuery(document).ready(function($) {
            var $menuAppearance = $("#menu-appearance");
            $menuAppearance.pointer({
                content: '<h3>Hi！</h3><p>感谢使用King主题修改版</p>',
                position: {
                    edge: "left",
                    align: "center"
                },
                close: function() {
                    $.post(ajaxurl, {
                        pointer: "git_options_pointer",
                        action: "dismiss-wp-pointer"
                    });
                }
            }).pointer("open").pointer("widget").find("a").eq(0).click(function() {
                var href = $(this).attr("href");
                $menuAppearance.pointer("close");
                setTimeout(function(){
                    location.href = href;
                }, 700);
                return false;
            });
     
            $(window).on("resize scroll", function() {
                $menuAppearance.pointer("reposition");
            });
            $("#collapse-menu").click(function() {
                $menuAppearance.pointer("reposition");
            });
        });
        </script>
     
    <?php
    }
    
    function theme_check_update( $hook_suffix ) {
        add_action( 'admin_print_footer_scripts', 'theme_update_notice' );
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_script( 'wp-pointer' );
    }
    add_action( 'admin_enqueue_scripts', 'theme_check_update' );
    
    function theme_update_notice() {
        ?>
        <script>
        jQuery(document).ready(function($) {
        var v = <?php echo (int)get_bloginfo('version'); ?>;
        $.ajax({
                url:'https://blog.ouorz.com/check_update.html?v='+v,
                type:"POST",
                cache: false,
                dataType : 'json',
                processData: false,
                contentType: false,
                success:function(data){
                    if(data.status == true){
                        show(data.version,data.download);
                    }
                }
            });
            
            var show = function(new_v,d_url){
            var $menuAppearance = $("#menu-appearance");
            $menuAppearance.pointer({
                content: '<h3>更新提示</h3><p>Tony 主题现已更新至 V'+ new_v +'，包含重要更新<br/>请前往 <a href="https://github.com/ttttonyhe/tony">Github</a> / <a href="'+d_url+'">直接下载</a></p>',
                position: {
                    edge: "left",
                    align: "center"
                },
                close: function() {
                    $.post(ajaxurl, {
                        pointer: "git_options_pointer",
                        action: "dismiss-wp-pointer"
                    });
                }
            }).pointer("open").pointer("widget").find("a").eq(0).click(function() {
                var href = $(this).attr("href");
                $menuAppearance.pointer("close");
                setTimeout(function(){
                    location.href = href;
                }, 700);
                return false;
            });
     
            $(window).on("resize scroll", function() {
                $menuAppearance.pointer("reposition");
            });
            $("#collapse-menu").click(function() {
                $menuAppearance.pointer("reposition");
            });
            }
        });
        </script>
     
    <?php
    } */
 }
?>
