<?php
function wwa_localize_frontend(){
    wp_enqueue_script('wwa_frontend_js', plugins_url('js/frontend.js', __FILE__), array(), get_option('wwa_version')['version'], true);
    wp_localize_script('wwa_frontend_js', 'wwa_php_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'admin_url' => admin_url(),
        'usernameless' => (wwa_get_option('usernameless_login') === false ? "false" : wwa_get_option('usernameless_login')),
        'remember_me' => (wwa_get_option('remember_me') === false ? "false" : wwa_get_option('remember_me')),
        'allow_authenticator_type' => (wwa_get_option('allow_authenticator_type') === false ? "none" : wwa_get_option('allow_authenticator_type')),
        'i18n_1' => __('Ready', 'wp-webauthn'),
        'i18n_2' => __('Authenticate with WebAuthn', 'wp-webauthn'),
        'i18n_3' => __('Hold on...', 'wp-webauthn'),
        'i18n_4' => __('Please proceed...', 'wp-webauthn'),
        'i18n_5' => __('Authenticating...', 'wp-webauthn'),
        'i18n_6' => '<span class="wwa-success">'.__('Authenticated', 'wp-webauthn'),
        'i18n_7' => '<span class="wwa-failed">'.__('Auth failed', 'wp-webauthn').'</span>',
        'i18n_8' => __('No', 'wp-webauthn'),
        'i18n_9' => __(' (Unavailable)', 'wp-webauthn'),
        'i18n_10' => __('The site administrator has disabled usernameless login feature.', 'wp-webauthn'),
        'i18n_11' => __('Error: The username field is empty.', 'wp-webauthn'),
        'i18n_12' => __('Please enter the authenticator identifier', 'wp-webauthn'),
        'i18n_13' => __('Please follow instructions to finish verification...', 'wp-webauthn'),
        'i18n_14' => __('Verifying...', 'wp-webauthn'),
        'i18n_15' => '<span class="failed">'.__('Verification failed', 'wp-webauthn').'</span>',
        'i18n_16' => '<span class="success">'.__('Verification passed! You can now log in through WebAuthn', 'wp-webauthn').'</span>',
        'i18n_17' => __('Loading failed, maybe try refreshing?', 'wp-webauthn'),
        'i18n_18' => __('Confirm removal of authenticator: ', 'wp-webauthn'),
        'i18n_19' => __('Removing...', 'wp-webauthn'),
        'i18n_20' => __('Rename', 'wp-webauthn'),
        'i18n_21' => __('Rename the authenticator', 'wp-webauthn'),
        'i18n_22' => __('Renaming...', 'wp-webauthn'),
        'i18n_23' => __('No registered authenticators', 'wp-webauthn'),
        'i18n_24' => __('Any', 'wp-webauthn'),
        'i18n_25' => __('Platform authenticator', 'wp-webauthn'),
        'i18n_26' => __('Roaming authenticator', 'wp-webauthn'),
        'i18n_27' => __('Remove', 'wp-webauthn'),
        'i18n_28' => __('Please follow instructions to finish registration...', 'wp-webauthn'),
        'i18n_29' => '<span class="success">'._x('Registered', 'action', 'wp-webauthn').'</span>',
        'i18n_30' => '<span class="failed">'.__('Registration failed', 'wp-webauthn').'</span>',
        'i18n_31' => __('Your browser does not support WebAuthn', 'wp-webauthn'),
        'i18n_32' => __('Registrating...', 'wp-webauthn'),
        'i18n_33' => '<span class="wwa-try-username">'.__('Try to enter the username', 'wp-webauthn').'</span>',
        'i18n_34' => __('After removing this authenticator, you will not be able to login with WebAuthn', 'wp-webauthn'),
        'i18n_35' => __(' (Disabled)', 'wp-webauthn'),
        'i18n_36' => __('The site administrator only allow platform authenticators currently.', 'wp-webauthn'),
        'i18n_37' => __('The site administrator only allow roaming authenticators currently.', 'wp-webauthn')
    ));
}

// Login form
function wwa_login_form_shortcode($vals){
    extract(shortcode_atts(
        array(
            'traditional' => 'true',
            'username' => '',
            'auto_hide' => 'true',
            'to' => ''
        ), $vals)
    );

    if($auto_hide === "true" && current_user_can("read")){
        return '';
    }

    // Load Javascript & CSS
    if(!wp_script_is('wwa_frontend_js')){
        wwa_localize_frontend();
    }
    wp_enqueue_style('wwa_frondend_css', plugins_url('css/frontend.css', __FILE__), array(), get_option('wwa_version')['version']);

    $html_form = '<div class="wwa-login-form">';

    $args = array('echo' => false, 'value_username' => $username);
    $to_wwa = "";
    if($to !== ""){
        $args["redirect"] = $to;
        if(substr($to, 0, 7) !== "http://" && substr($to, 0, 8) !== "https://" && substr($to, 0, 6) !== "ftp://" && substr($to, 0, 7) !== "mailto:"){
            $to_wwa = '<input type="hidden" name="wwa-redirect-to" class="wwa-redirect-to" id="wwa-redirect-to" value="http://'.$to.'">';
        }else{
            $to_wwa = '<input type="hidden" name="wwa-redirect-to" class="wwa-redirect-to" id="wwa-redirect-to" value="'.$to.'">';
        }
    }

    if($traditional === 'true' && wwa_get_option('first_choice') !== 'webauthn'){
        $html_form .= '<div class="wwa-login-form-traditional">'.wp_login_form($args).'<br><a class="wwa-t2w" href="#"><span>'.__('Authenticate with WebAuthn', 'wp-webauthn').'</span></a></div>';
    }

    $html_form .= '<div class="wwa-login-form-webauthn"><p class="wwa-login-username"><label for="wwa-user-name">'.__('Username', 'wp-webauthn').'</label><input type="text" name="wwa-user-name" id="wwa-user-name" class="wwa-user-name" value="'.$username.'" size="20"></p><div class="wp-webauthn-notice">'.__('Authenticate with WebAuthn', 'wp-webauthn').'</div><p class="wwa-login-submit-p">'.$to_wwa.((wwa_get_option('remember_me') === false ? 'false' : wwa_get_option('remember_me') !== 'false') ? '<label class="wwa-remember-label"><input name="wwa-rememberme" type="checkbox" id="wwa-rememberme" value="forever"> '.__('Remember Me').'</label>' : '').'<input type="button" name="wwa-login-submit" id="wwa-login-submit" class="wwa-login-submit button button-primary" value="'.__('Auth', 'wp-webauthn').'"><a class="wwa-w2t" href="#">'.__('Authenticate with password', 'wp-webauthn').'</a></p></div></div>';

    return $html_form;
}
add_shortcode('wwa_login_form', 'wwa_login_form_shortcode');

// Register form
function wwa_register_form_shortcode($vals){
    extract(shortcode_atts(
        array(
            'display' => 'true'
        ), $vals)
    );

    // If always display
    if(!current_user_can("read")){
        if($display === "true"){
            return '<div class="wwa-register-form"><p class="wwa-bind">'.__('You haven\'t logged in yet.', 'wp-webauthn').'</p></div>';
        }else{
            return '';
        }
    }

    // Load Javascript & CSS
    if(!wp_script_is('wwa_frontend_js')){
        wwa_localize_frontend();
    }
    wp_enqueue_style('wwa_frondend_css', plugins_url('css/frontend.css', __FILE__), array(), get_option('wwa_version')['version']);

    $allowed_type = wwa_get_option('allow_authenticator_type') === false ? 'none' : wwa_get_option('allow_authenticator_type');
    return '<div class="wwa-register-form"><label for="wwa-authenticator-type">'.__('Type of authenticator', 'wp-webauthn').'</label><select name="wwa-authenticator-type" class="wwa-authenticator-type" id="wwa-authenticator-type"><option value="none" class="wwa-type-none"'.($allowed_type !== 'none' ? ' disabled' : '').'>'.__('Any', 'wp-webauthn').'</option><option value="platform" class="wwa-type-platform"'.($allowed_type === 'cross-platform' ? ' disabled' : '').'>'.__('Platform (e.g. built-in fingerprint sensors)', 'wp-webauthn').'</option><option value="cross-platform" class="wwa-type-cross-platform"'.($allowed_type === 'platform' ? ' disabled' : '').'>'.__('Roaming (e.g. USB security keys)', 'wp-webauthn').'</option></select><p class="wwa-bind-name-description">'.__('If a type is selected, the browser will only prompt for authenticators of selected type. <br> Regardless of the type, you can only log in with the very same authenticators you\'ve registered.', 'wp-webauthn').'</p><label for="wwa-authenticator-name">'.__('Authenticator identifier', 'wp-webauthn').'</label><input required name="wwa-authenticator-name" type="text" class="wwa-authenticator-name" id="wwa-authenticator-name"><p class="wwa-bind-name-description">'.__('An easily identifiable name for the authenticator. <strong>DOES NOT</strong> affect the authentication process in anyway.', 'wp-webauthn').'</p>'.((wwa_get_option('usernameless_login') === "true") ? '<label for="wwa-authenticator-usernameless">'.__('Login without username', 'wp-webauthn').'<br><label><input type="radio" name="wwa-authenticator-usernameless" class="wwa-authenticator-usernameless" value="true"> '.__("Enable", "wp-webauthn").'</label><br><label><input type="radio" name="wwa-authenticator-usernameless" class="wwa-authenticator-usernameless" value="false" checked="checked"> '.__("Disable", "wp-webauthn").'</label></label><br><p class="wwa-bind-usernameless-description">'.__('If registered authenticator with this feature, you can login without enter your username.<br>Some authenticators like U2F-only authenticators and some browsers <strong>DO NOT</strong> support this feature.<br>A record will be stored in the authenticator permanently untill you reset it.', 'wp-webauthn').'</p>' : '').'<p class="wwa-bind"><button class="wwa-bind-submit">'.__('Start registration', 'wp-webauthn').'</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="wwa-show-progress"></span></p></div>';
}
add_shortcode('wwa_register_form', 'wwa_register_form_shortcode');

// Verify button
function wwa_verify_button_shortcode($vals){
    extract(shortcode_atts(
        array(
            'display' => 'true'
        ), $vals)
    );

    // If always display
    if(!current_user_can("read")){
        if($display === "true"){
            return '<p class="wwa-test">'.__('You haven\'t logged in yet.', 'wp-webauthn').'</p>';
        }else{
            return '';
        }
    }

    // Load Javascript
    if(!wp_script_is('wwa_frontend_js')){
        wwa_localize_frontend();
    }

    return '<p class="wwa-test"><button class="wwa-test-submit">'.__('Test Login', 'wp-webauthn').'</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="wwa-show-test"></span>'.(wwa_get_option('usernameless_login') === "true" ? '<br><button class="wwa-test-usernameless-submit">'.__('Test Login (usernameless)', 'wp-webauthn').'</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="wwa-show-test-usernameless"></span>' : '').'</p>';
}
add_shortcode('wwa_verify_button', 'wwa_verify_button_shortcode');

// Authenticator list
function wwa_list_shortcode($vals){
    extract(shortcode_atts(
        array(
            'display' => 'true'
        ), $vals)
    );

    $thead = '<div class="wwa-table-container"><table class="wwa-list-table"><thead><tr><th>'.__('Identifier', 'wp-webauthn').'</th><th>'.__('Type', 'wp-webauthn').'</th><th>'._x('Registered', 'time', 'wp-webauthn').'</th><th>'.__('Last used', 'time', 'wp-webauthn').'</th><th class="wwa-usernameless-th">'.__('Usernameless', 'wp-webauthn').'</th><th>'.__('Action', 'wp-webauthn').'</th></tr></thead><tbody class="wwa-authenticator-list">';
    $tbody = '<tr><td colspan="5">'.__('Loading...', 'wp-webauthn').'</td></tr>';
    $tfoot = '</tbody><tfoot><tr><th>'.__('Identifier', 'wp-webauthn').'</th><th>'.__('Type', 'wp-webauthn').'</th><th>'._x('Registered', 'time', 'wp-webauthn').'</th><th>'.__('Last used', 'time', 'wp-webauthn').'</th><th class="wwa-usernameless-th">'.__('Usernameless', 'wp-webauthn').'</th><th>'.__('Action', 'wp-webauthn').'</th></tr></tfoot></table></div><p class="wwa-authenticator-list-usernameless-tip"></p><p class="wwa-authenticator-list-type-tip"></p>';

    // If always display
    if(!current_user_can("read")){
        if($display === "true"){
            // Load CSS
            wp_enqueue_style('wwa_frondend_css', plugins_url('css/frontend.css', __FILE__), array(), get_option('wwa_version')['version']);

            return $thead.'<tr><td colspan="5">'.__('You haven\'t logged in yet.', 'wp-webauthn').'</td></tr>'.$tfoot;
        }else{
            return '';
        }
    }

    // Load Javascript & CSS
    if(!wp_script_is('wwa_frontend_js')){
        wwa_localize_frontend();
    }
    wp_enqueue_style('wwa_frondend_css', plugins_url('css/frontend.css', __FILE__), array(), get_option('wwa_version')['version']);

    return $thead.$tbody.$tfoot;
}
add_shortcode('wwa_list', 'wwa_list_shortcode');
?>