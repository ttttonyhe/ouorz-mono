<?php
/*
Template Name: 更新页面
*/
?>

<?php
if(get_option('tony_version')) $v = get_option('tony_version'); else $v = 1;
if(get_option('tony_download')) $d = get_option('tony_download'); else $d = 'https://static.ouorz.com/wordpress_theme_tony.zip';

if($_GET['v'] !== $v){
    $array = array('status'=>true,'version'=>$v,'download'=>$d);
    header('Access-Control-Allow-Origin: *');
    echo json_encode($array);
}else{
    $array = array('status'=>false);
    header('Access-Control-Allow-Origin: *');
    echo json_encode($array);
}
?>