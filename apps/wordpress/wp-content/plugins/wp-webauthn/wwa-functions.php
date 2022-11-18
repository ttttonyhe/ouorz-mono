<?php
// WordPress transient adapter
function wwa_set_temp_val($name, $value, $client_id){
    return set_transient('wwa_'.$name.$client_id, serialize($value), 90);
}

function wwa_get_temp_val($name, $client_id){
    $val = get_transient('wwa_'.$name.$client_id);
    return $val === false ? false : unserialize($val);
}

function wwa_delete_temp_val($name, $client_id){
    return delete_transient('wwa_'.$name.$client_id);
}

// Destroy all transients
function wwa_destroy_temp_val($client_id){
    wwa_delete_temp_val('user_name_auth', $client_id);
    wwa_delete_temp_val('user_auth', $client_id);
    wwa_delete_temp_val('pkcco', $client_id);
    wwa_delete_temp_val('bind_config', $client_id);
    wwa_delete_temp_val('pkcco_auth', $client_id);
    wwa_delete_temp_val('usernameless_auth', $client_id);
    wwa_delete_temp_val('auth_type', $client_id);
}

// Destroy all transients before wp_die
function wwa_wp_die($message = '', $client_id = false){
    if($client_id !== false){
        wwa_destroy_temp_val($client_id);
    }
    wp_die($message);
}

// Init data for new options
function wwa_init_new_options(){
    if(wwa_get_option('allow_authenticator_type') === false){
        wwa_update_option('allow_authenticator_type', 'none');
    }
    if(wwa_get_option('remember_me') === false){
        wwa_update_option('remember_me', 'false');
    }
    if(wwa_get_option('usernameless_login') === false){
        wwa_update_option('usernameless_login', 'false');
    }
}

// Create random strings for user ID
function wwa_generate_random_string($length = 10){
    // Use cryptographically secure pseudo-random generator in PHP 7+
    if(function_exists('random_bytes')){
        $bytes = random_bytes(round($length/2));
        return bin2hex($bytes);
    }else{
        // Not supported, use normal random generator instead
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_';
        $randomString = '';
        for($i = 0; $i < $length; $i++){
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}

// Add log
function wwa_add_log($id, $content = '', $init = false){
    if(wwa_get_option('logging') !== 'true' && !$init){
        return;
    }
    $log = get_option('wwa_log');
    if($log === false){
        $log = array();
    }
    $log[] = '['.date('Y-m-d H:i:s', current_time('timestamp')).']['.$id.'] '.$content;
    update_option('wwa_log', $log);
}

// Format trackback
function wwa_generate_call_trace($exception = false){
    $e = $exception;
    if($exception === false){
        $e = new Exception();
    }
    $trace = explode("\n", $e->getTraceAsString());
    $trace = array_reverse($trace);
    array_shift($trace);
    array_pop($trace);
    $length = count($trace);
    $result = array();

    for($i = 0; $i < $length; $i++){
        $result[] = ($i + 1).')'.substr($trace[$i], strpos($trace[$i], ' '));
    }

    return "Traceback:\n                              ".implode("\n                              ", $result);
}

// Delete all credentials when deleting user
function wwa_delete_user($user_id){
    $res_id = wwa_generate_random_string(5);

    $user_data = get_userdata($user_id);
    $all_user_meta = wwa_get_option("user_id");
    $user_key = "";
    wwa_add_log($res_id, "Delete user => \"".$user_data->user_login."\"");

    // Delete user meta
    foreach($all_user_meta as $user => $id){
        if($user === $user_data->user_login){
            $user_key = $id;
            wwa_add_log($res_id, "Delete user_key => \"".$id."\"");
            unset($all_user_meta[$user]);
        }
    }

    // Delete credentials
    $all_credentials_meta = json_decode(wwa_get_option("user_credentials_meta"), true);
    $all_credentials = json_decode(wwa_get_option("user_credentials"), true);
    foreach($all_credentials_meta as $credential => $meta){
        if($user_key === $meta["user"]){
            wwa_add_log($res_id, "Delete credential => \"".$credential."\"");
            unset($all_credentials_meta[$credential]);
            unset($all_credentials[$credential]);
        }
    }
    wwa_update_option("user_id", $all_user_meta);
    wwa_update_option("user_credentials_meta", json_encode($all_credentials_meta));
    wwa_update_option("user_credentials", json_encode($all_credentials));
    wwa_add_log($res_id, "Done");
}
add_action('delete_user', 'wwa_delete_user');

// Add CSS and JS in login page
function wwa_login_js(){
    $wwa_not_allowed = false;
    if(!function_exists("mb_substr") || !function_exists("gmp_intval") || !wwa_check_ssl() && (parse_url(site_url(), PHP_URL_HOST) !== 'localhost' && parse_url(site_url(), PHP_URL_HOST) !== '127.0.0.1')){
        $wwa_not_allowed = true;
    }
    wp_enqueue_script('wwa_login', plugins_url('js/login.js', __FILE__), array(), get_option('wwa_version')['version'], true);
    $first_choice = wwa_get_option('first_choice');
    wp_localize_script('wwa_login', 'php_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'admin_url' => admin_url(),
        'usernameless' => (wwa_get_option('usernameless_login') === false ? 'false' : wwa_get_option('usernameless_login')),
        'remember_me' => (wwa_get_option('remember_me') === false ? 'false' : wwa_get_option('remember_me')),
        'allow_authenticator_type' => (wwa_get_option('allow_authenticator_type') === false ? "none" : wwa_get_option('allow_authenticator_type')),
        'webauthn_only' => ($first_choice === 'webauthn' && !$wwa_not_allowed) ? 'true' : 'false',
        'i18n_1' => __('Auth', 'wp-webauthn'),
        'i18n_2' => __('Authenticate with WebAuthn', 'wp-webauthn'),
        'i18n_3' => __('Hold on...', 'wp-webauthn'),
        'i18n_4' => __('Please proceed...', 'wp-webauthn'),
        'i18n_5' => __('Authenticating...', 'wp-webauthn'),
        'i18n_6' => '<span class="wwa-success"><span class="dashicons dashicons-yes"></span> '.__('Authenticated', 'wp-webauthn').'</span>',
        'i18n_7' => '<span class="wwa-failed"><span class="dashicons dashicons-no-alt"></span> '.__('Auth failed', 'wp-webauthn').'</span>',
        'i18n_8' => __('It looks like your browser doesn\'t support WebAuthn, which means you may unable to login.', 'wp-webauthn'),
        'i18n_9' => __('Username', 'wp-webauthn'),
        'i18n_10' => __('Username or Email Address'),
        'i18n_11' => __('<strong>Error</strong>: The username field is empty.', 'wp-webauthn'),
        'i18n_12' => '<span class="wwa-try-username">'.__('Try to enter the username', 'wp-webauthn').'</span>'
    ));
    if($first_choice === 'true' || $first_choice === 'webauthn'){
        wp_enqueue_script('wwa_default', plugins_url('js/default_wa.js', __FILE__), array(), get_option('wwa_version')['version'], true);
    }
    wp_enqueue_style('wwa_login_css', plugins_url('css/login.css', __FILE__), array(), get_option('wwa_version')['version']);
}
add_action('login_enqueue_scripts', 'wwa_login_js', 999);

// Disable password login
function wwa_disable_password($user){
    if(!function_exists("mb_substr") || !function_exists("gmp_intval") || !wwa_check_ssl() && (parse_url(site_url(), PHP_URL_HOST) !== 'localhost' && parse_url(site_url(), PHP_URL_HOST) !== '127.0.0.1')){
        return $user;
    }
    if(wwa_get_option('first_choice') === 'webauthn'){
        return new WP_Error('wwa_password_disabled', __('Logging in with password has been disabled by the site manager.', 'wp-webauthn'));
    }
    if(is_wp_error($user)){
        return $user;
    }
    if(get_the_author_meta('webauthn_only', $user->ID) === 'true'){
        return new WP_Error('wwa_password_disabled_for_account', __('Logging in with password has been disabled for this account.', 'wp-webauthn'));
    }
    return $user;
}
add_filter('wp_authenticate_user', 'wwa_disable_password', 10, 1);

// Show a notice in admin pages
function wwa_no_authenticator_warning(){
    $user_info = wp_get_current_user();
    $first_choice = wwa_get_option('first_choice');
    $check_self = true;
    if($first_choice !== 'webauthn' && get_the_author_meta('webauthn_only', $user_info->ID ) !== 'true'){
        $check_self = false;
    }

    if($check_self){
        // Check current user
        $user_id = '';
        $show_notice_flag = false;
        if(!isset(wwa_get_option('user_id')[$user_info->user_login])){
            $show_notice_flag = true;
        }else{
            $user_id = wwa_get_option('user_id')[$user_info->user_login];
        }

        if(!$show_notice_flag){
            $show_notice_flag = true;
            $data = json_decode(wwa_get_option('user_credentials_meta'), true);
            foreach($data as $value){
                if($user_id === $value['user']){
                    $show_notice_flag = false;
                    break;
                }
            }
        }

        if($show_notice_flag){?>
            <div class="notice notice-warning">
                <p><?php printf(__('Logging in with password has been disabled for %s but you haven\'t register any WebAuthn authenticator yet. You may unable to login again once you log out. <a href="%s#wwa-webauthn-start">Register</a>', 'wp-webauthn'), $first_choice === 'webauthn' ? __('the site', 'wp-webauthn') : __('your account', 'wp-webauthn'), admin_url('profile.php'));?></p>
            </div>
        <?php }
    }
    // Check other user
    global $pagenow;
    if($pagenow == 'user-edit.php' && isset($_GET['user_id']) && intval($_GET['user_id']) !== $user_info->ID){
        $user_id_wp = intval($_GET['user_id']);
        if($user_id_wp <= 0){
            return;
        }
        if(!current_user_can('edit_user', $user_id_wp)){
            return;
        }
        $user_info = get_user_by('id', $user_id_wp);

        if($user_info === false){
            return;
        }

        if($first_choice !== 'webauthn' && get_the_author_meta('webauthn_only', $user_info->ID ) !== 'true'){
            return;
        }

        $user_id = '';
        $show_notice_flag = false;
        if(!isset(wwa_get_option('user_id')[$user_info->user_login])){
            $show_notice_flag = true;
        }else{
            $user_id = wwa_get_option('user_id')[$user_info->user_login];
        }

        if(!$show_notice_flag){
            $show_notice_flag = true;
            $data = json_decode(wwa_get_option('user_credentials_meta'), true);
            foreach($data as $value){
                if($user_id === $value['user']){
                    $show_notice_flag = false;
                    break;
                }
            }
        }

        if($show_notice_flag){ ?>
            <div class="notice notice-warning">
                <p><?php printf(__('Logging in with password has been disabled for %s but <strong>this account</strong> haven\'t register any WebAuthn authenticator yet. This user may unable to login.', 'wp-webauthn'), $first_choice === 'webauthn' ? __('the site', 'wp-webauthn') : __('this account', 'wp-webauthn'));?></p>
            </div>
        <?php }
    }
}
add_action('admin_notices', 'wwa_no_authenticator_warning');

// Load Gutenberg block assets
function wwa_load_blocks(){
  wp_enqueue_script(
        'wwa_block_js',
        plugins_url('blocks/blocks.build.js', __FILE__),
        ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'],
        true
  );
  wp_set_script_translations('wwa_block_js', 'wp-webauthn', plugin_dir_path(__FILE__).'blocks/languages');
}
add_action('enqueue_block_editor_assets', 'wwa_load_blocks');

// Multi-language support
function wwa_load_textdomain(){
    load_plugin_textdomain('wp-webauthn', false, dirname(plugin_basename(__FILE__)).'/languages');
}
add_action('init', 'wwa_load_textdomain');

// Add meta links in plugin list page
function wwa_settings_link($links_array, $plugin_file_name){
    if($plugin_file_name === 'wp-webauthn/wp-webauthn.php'){
        $links_array[] = '<a href="options-general.php?page=wwa_admin">'.__('Settings', 'wp-webauthn').'</a>';
    }
    return $links_array;
}
add_filter('plugin_action_links', 'wwa_settings_link', 10, 2);

function wwa_meta_link($links_array, $plugin_file_name){
    if($plugin_file_name === 'wp-webauthn/wp-webauthn.php'){
        $links_array[] = '<a href="https://github.com/yrccondor/wp-webauthn">'.__('GitHub', 'wp-webauthn').'</a>';
        $links_array[] = '<a href="http://doc.flyhigher.top/wp-webauthn">'.__('Documentation', 'wp-webauthn').'</a>';
    }
    return $links_array;
}
add_filter('plugin_row_meta', 'wwa_meta_link', 10, 2);

// Check if we are under HTTPS
function wwa_check_ssl() {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['HTTPS'] !== '') {
        return true;
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
        return true;
    }
    if (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/3.0') {
        return true;
    }
    if (isset($_SERVER['REQUEST_SCHEME']) && ($_SERVER['REQUEST_SCHEME'] === 'quic' || $_SERVER['REQUEST_SCHEME'] === 'https')) {
        return true;
    }
    return false;
}

// Check user privileges
function wwa_validate_privileges() {
    $user = wp_get_current_user();
    $allowed_roles = array( 'administrator' );
    if(array_intersect($allowed_roles, $user->roles)){
        return true;
    }
    return false;
}
?>
