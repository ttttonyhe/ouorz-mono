<?php
function html_page_permalink() {
global $wp_rewrite;
if ( !strpos($wp_rewrite->get_page_permastruct(), '.html')){
$wp_rewrite->page_structure = $wp_rewrite->page_structure . '.html';
}
}
add_action('init', 'html_page_permalink', -1);
?>