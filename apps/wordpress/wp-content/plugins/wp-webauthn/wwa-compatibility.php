<?php
// Two Factor
if(has_action('wp_login', array('Two_Factor_Core', 'wp_login')) !== false){
    remove_action('wp_login', array('Two_Factor_Core', 'wp_login'), 10, 2);
}
?>