<?php
//代码高亮集成于Pure-Highlight插件，Plugin URI: https://github.com/icodechef/Pure-Highlightjs
if ( !defined( 'THEME_URI' ) ) {
	define( 'THEME_URI', get_template_directory_uri() );
}
add_action( 'wp_enqueue_scripts', 'pure_highlightjs_assets' );
function pure_highlightjs_assets() {
    wp_enqueue_style( 'pure-highlightjs-style', THEME_URI . 'code/highlight/styles/github.css', array(), THEME_VER );
    wp_enqueue_style( 'pure-highlightjs-css', THEME_URI . '/code/css/pure-highlight.css', array(), THEME_VER );
    wp_enqueue_script( 'pure-highlightjs-pack', THEME_URI . '/code/highlight/highlight.pack.js', array(), THEME_VER, true );
}


add_action( 'admin_enqueue_scripts', 'pure_highlightjs_admin_assets' );
function pure_highlightjs_admin_assets() {
    global $hook_suffix;

    if ( in_array( $hook_suffix, array(
            'index.php', 
            'post.php',
            'post-new.php',
            'settings_page_pure-highlightjs-config',
        ) ) ) {
        wp_enqueue_script( 'pure-highlightjs', THEME_URI . '/code/js/pure-highlight.js', array(), THEME_VER, true );

        wp_enqueue_script( 'pure-highlightjs-pack', THEME_URI . '/code/highlight/highlight.pack.js', array(), THEME_VER, true );

        wp_localize_script( 'pure-highlightjs', 'PureHighlightjsTrans', array(
            'title'    => '插入代码',
            'language' => '选择语言',
            'code'     => '代码',
        ));
    }
}
add_filter('mce_external_plugins', 'pure_highlightjs_mce_plugin');

function pure_highlightjs_mce_plugin( $mce_plugins ) {
    $mce_plugins['purehighlightjs'] = THEME_URI . '/code/tinymce/tinymce.js';
    return $mce_plugins;
}

add_filter( 'mce_css', 'pure_highlightjs_mce_css');

function pure_highlightjs_mce_css( $mce_css ) {
    if (! is_array($mce_css) ) {
        $mce_css = explode(',', $mce_css);
    }

    $mce_css[] = THEME_URI . '/code/tinymce/tinymce.css';

    return implode( ',', $mce_css );
}

add_filter('mce_buttons', 'pure_highlightjs_mce_buttons', 101);

function pure_highlightjs_mce_buttons( $buttons ) {
    if (! in_array('PureHighlightjsInsert', $buttons) ){
        $buttons[] = 'PureHighlightjsInsert';
    }
    return $buttons;
}

function pure_highlightjs_get_style_list($theme = '') {
    $path = THEME_URI . '/code/highlight/styles';

    $themes = array();
    foreach (new DirectoryIterator($path) as $fileInfo) {
        if ($fileInfo->isDot() || ! $fileInfo->isFile()) {
            continue;
        }

        $filename = $fileInfo->getFilename();

        if ('.css' != substr($filename, -4)) {
            continue;
        }

        $themes[] = substr($filename, 0, - 4);;
    }

    sort($themes);

    return $themes;
}