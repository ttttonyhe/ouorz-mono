<?php
/*
Plugin Name: WP-WebAuthn
Plugin URI: https://flyhigher.top
Description: WP-WebAuthn allows you to safely login to your WordPress site without password.
Version: 1.2.8
Author: Axton
Author URI: https://axton.cc
License: GPLv3
Text Domain: wp-webauthn
Domain Path: /languages
*/
/* Copyright 2020 Axton
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version  of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

register_activation_hook(__FILE__, 'wwa_init');

function wwa_init(){
    if(version_compare(get_bloginfo('version'), '4.4', '<')){
        deactivate_plugins(basename(__FILE__)); //disable
    }else{
        wwa_init_data();
    }
}

wwa_init_data();

function wwa_init_data(){
    if(!get_option('wwa_init')){
        // Init
        $site_domain = parse_url(site_url(), PHP_URL_HOST);
        $wwa_init_options = array(
            'user_credentials' => "{}",
            'user_credentials_meta' => "{}",
            'user_id' => array(),
            'first_choice' => 'true',
            'website_name' => get_bloginfo('name'),
            'website_domain' => $site_domain === NULL ? "" : $site_domain,
            'remember_me' => 'false',
            'user_verification' => 'false',
            'usernameless_login' => 'false',
            'allow_authenticator_type' => 'none',
            'logging' => 'false'
        );
        update_option('wwa_options', $wwa_init_options);
        include('wwa-version.php');
        update_option('wwa_version', $wwa_version);
        update_option('wwa_log', array());
        update_option('wwa_init', md5(date('Y-m-d H:i:s')));
    }else{
        include('wwa-version.php');
        if(!get_option('wwa_version') || get_option('wwa_version')['version'] != $wwa_version['version']){
            update_option('wwa_version', $wwa_version); //update version
        }
    }
}

// Wrap WP-WebAuthn settings
function wwa_get_option($option_name){
    $val = get_option("wwa_options");
    if(isset($val[$option_name])){
        return $val[$option_name];
    }else{
        return false;
    }
}

function wwa_update_option($option_name, $option_value){
    $options = get_option("wwa_options");
    $options[$option_name] = $option_value;
    update_option('wwa_options',$options);
    return true;
}

include('wwa-menus.php');
include('wwa-functions.php');
include('wwa-ajax.php');
include('wwa-shortcodes.php');
?>