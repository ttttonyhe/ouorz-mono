<?php
// Add menu
function wwa_admin_menu(){
    add_options_page('WP-WebAuthn' , 'WP-WebAuthn', 'read', 'wwa_admin','wwa_display_main_menu');
}
function wwa_display_main_menu(){
    include('wwa-admin-content.php');
}

// Add setting to profile page
function wwa_user_profile_fields($user){
    include('wwa-profile-content.php');
}
add_action('show_user_profile', 'wwa_user_profile_fields');

// Save setting to profile page
function wwa_save_user_profile_fields($user_id){
    if(empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-user_'.$user_id)){
        return;
    }

    if(!current_user_can('edit_user', $user_id)){
        return false;
    }

    if(wwa_get_option('first_choice') === 'webauthn'){
        return;
    }

    if(!isset($_POST['webauthn_only'])){
        update_user_meta($user_id, 'webauthn_only', 'false');
    }else if(sanitize_text_field($_POST['webauthn_only']) === 'true'){
        update_user_meta($user_id, 'webauthn_only', 'true');
    }else{
        update_user_meta($user_id, 'webauthn_only', 'false');
    }
}
add_action('personal_options_update', 'wwa_save_user_profile_fields');

// Check user privileges
function wwa_user_profile_fields_check(){
    if(current_user_can('edit_users')){
        add_action('edit_user_profile', 'wwa_user_profile_fields');
        add_action('edit_user_profile_update', 'wwa_save_user_profile_fields');
    }
    if(wwa_validate_privileges()){
        add_action('admin_menu', 'wwa_admin_menu');
    }
}
add_action('plugins_loaded', 'wwa_user_profile_fields_check');
