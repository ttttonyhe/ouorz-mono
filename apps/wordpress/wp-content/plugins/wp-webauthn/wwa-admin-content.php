<?php
// Insert CSS and JS
wp_enqueue_script('wwa_admin', plugins_url('js/admin.js', __FILE__));
wp_localize_script('wwa_admin', 'php_vars', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'i18n_1' => __('User verification is disabled by default because some mobile devices do not support it (especially on Android devices). But we <strong>recommend you to enable it</strong> if possible to further secure your login.', 'wp-webauthn'),
    'i18n_2' => __('Log count: ', 'wp-webauthn'),
    'i18n_3' => __('Loading failed, maybe try refreshing?', 'wp-webauthn')
));
wp_enqueue_style('wwa_admin', plugins_url('css/admin.css', __FILE__));
?>
<div class="wrap"><h1>WP-WebAuthn</h1>
<?php
$wwa_not_allowed = false;
if(!function_exists('gmp_intval')){
    add_settings_error('wwa_settings', 'gmp_error', __("PHP extension gmp doesn't seem to exist, rendering WP-WebAuthn unable to function.", 'wp-webauthn'));
    $wwa_not_allowed = true;
}
if(!function_exists('mb_substr')){
    add_settings_error('wwa_settings', 'mbstr_error', __("PHP extension mbstring doesn't seem to exist, rendering WP-WebAuthn unable to function.", 'wp-webauthn'));
    $wwa_not_allowed = true;
}
if(!function_exists('sodium_crypto_sign_detached')){
    add_settings_error('wwa_settings', 'sodium_error', __("PHP extension sodium doesn't seem to exist, rendering WP-WebAuthn unable to function.", 'wp-webauthn'));
    $wwa_not_allowed = true;
}
if(!wwa_check_ssl() && (parse_url(site_url(), PHP_URL_HOST) !== 'localhost' && parse_url(site_url(), PHP_URL_HOST) !== '127.0.0.1')){
    add_settings_error('wwa_settings', 'https_error', __('WebAuthn features are restricted to websites in secure contexts. Please make sure your website is served over HTTPS or locally with <code>localhost</code>.', 'wp-webauthn'));
    $wwa_not_allowed = true;
}
// Only admin can change settings
// if((isset($_POST['wwa_ref']) && $_POST['wwa_ref'] === 'true') && check_admin_referer('wwa_options_update') && wwa_validate_privileges() && ($_POST['first_choice'] === 'true' || $_POST['first_choice'] === 'false' || $_POST['first_choice'] === 'webauthn') && ($_POST['remember_me'] === 'true' || $_POST['remember_me'] === 'false') && ($_POST['user_verification'] === 'true' || $_POST['user_verification'] === 'false') && ($_POST['usernameless_login'] === 'true' || $_POST['usernameless_login'] === 'false') && ($_POST['allow_authenticator_type'] === 'none' || $_POST['allow_authenticator_type'] === 'platform' || $_POST['allow_authenticator_type'] === 'cross-platform') && ($_POST['after_user_registration'] === 'none' || $_POST['after_user_registration'] === 'login' || $_POST['after_user_registration'] === 'guide') && ($_POST['logging'] === 'true' || $_POST['logging'] === 'false')){
if((isset($_POST['wwa_ref']) && $_POST['wwa_ref'] === 'true') && check_admin_referer('wwa_options_update') && wwa_validate_privileges() && ($_POST['first_choice'] === 'true' || $_POST['first_choice'] === 'false' || $_POST['first_choice'] === 'webauthn') && ($_POST['remember_me'] === 'true' || $_POST['remember_me'] === 'false') && ($_POST['user_verification'] === 'true' || $_POST['user_verification'] === 'false') && ($_POST['usernameless_login'] === 'true' || $_POST['usernameless_login'] === 'false') && ($_POST['allow_authenticator_type'] === 'none' || $_POST['allow_authenticator_type'] === 'platform' || $_POST['allow_authenticator_type'] === 'cross-platform') && ($_POST['logging'] === 'true' || $_POST['logging'] === 'false')){
    $res_id = wwa_generate_random_string(5);
    if(sanitize_text_field($_POST['logging']) === 'true' && wwa_get_option('logging') === 'false'){
        // Initialize log
        if(!function_exists('gmp_intval')){
            wwa_add_log($res_id, 'Warning: PHP extension gmp not found', true);
        }
        if(!function_exists('mb_substr')){
            wwa_add_log($res_id, 'Warning: PHP extension mbstring not found', true);
        }
        if(!function_exists('sodium_crypto_sign_detached')){
            wwa_add_log($res_id, 'Warning: PHP extension sodium not found', true);
        }
        if(!wwa_check_ssl() && (parse_url(site_url(), PHP_URL_HOST) !== 'localhost' && parse_url(site_url(), PHP_URL_HOST) !== '127.0.0.1')){
            wwa_add_log($res_id, 'Warning: Not in security context', true);
        }
        wwa_add_log($res_id, 'PHP Version => '.phpversion().', WordPress Version => '.get_bloginfo('version').', WP-WebAuthn Version => '.get_option('wwa_version')['version'], true);
        // wwa_add_log($res_id, 'Current config: first_choice => "'.wwa_get_option('first_choice').'", website_name => "'.wwa_get_option('website_name').'", website_domain => "'.wwa_get_option('website_domain').'", remember_me => "'.wwa_get_option('remember_me').'", user_verification => "'.wwa_get_option('user_verification').'", allow_authenticator_type => "'.wwa_get_option('allow_authenticator_type').'", usernameless_login => "'.wwa_get_option('usernameless_login').'", after_user_registration => "'.wwa_get_option('after_user_registration').'"', true);
        wwa_add_log($res_id, 'Current config: first_choice => "'.wwa_get_option('first_choice').'", website_name => "'.wwa_get_option('website_name').'", website_domain => "'.wwa_get_option('website_domain').'", remember_me => "'.wwa_get_option('remember_me').'", user_verification => "'.wwa_get_option('user_verification').'", allow_authenticator_type => "'.wwa_get_option('allow_authenticator_type').'", usernameless_login => "'.wwa_get_option('usernameless_login').'"', true);
        wwa_add_log($res_id, 'Logger initialized', true);
    }
    wwa_update_option('logging', sanitize_text_field($_POST['logging']));

    $post_first_choice = sanitize_text_field($_POST['first_choice']);
    if($post_first_choice !== wwa_get_option('first_choice')){
        wwa_add_log($res_id, 'first_choice: "'.wwa_get_option('first_choice').'"->"'.$post_first_choice.'"');
    }
    wwa_update_option('first_choice', $post_first_choice);

    $post_website_name = sanitize_text_field($_POST['website_name']);
    if($post_website_name !== wwa_get_option('website_name')){
        wwa_add_log($res_id, 'website_name: "'.wwa_get_option('website_name').'"->"'.$post_website_name.'"');
    }
    wwa_update_option('website_name', $post_website_name);

    $post_website_domain = str_replace('https:', '', str_replace('/', '', sanitize_text_field($_POST['website_domain'])));
    if($post_website_domain !== wwa_get_option('website_domain')){
        wwa_add_log($res_id, 'website_domain: "'.wwa_get_option('website_domain').'"->"'.$post_website_domain.'"');
    }
    wwa_update_option('website_domain', $post_website_domain);

    $post_remember_me = sanitize_text_field($_POST['remember_me']);
    if($post_remember_me !== wwa_get_option('remember_me')){
        wwa_add_log($res_id, 'remember_me: "'.wwa_get_option('remember_me').'"->"'.$post_remember_me.'"');
    }
    wwa_update_option('remember_me', $post_remember_me);

    $post_user_verification = sanitize_text_field($_POST['user_verification']);
    if($post_user_verification !== wwa_get_option('user_verification')){
        wwa_add_log($res_id, 'user_verification: "'.wwa_get_option('user_verification').'"->"'.$post_user_verification.'"');
    }
    wwa_update_option('user_verification', $post_user_verification);

    $post_allow_authenticator_type = sanitize_text_field($_POST['allow_authenticator_type']);
    if($post_allow_authenticator_type !== wwa_get_option('allow_authenticator_type')){
        wwa_add_log($res_id, 'allow_authenticator_type: "'.wwa_get_option('allow_authenticator_type').'"->"'.$post_allow_authenticator_type.'"');
    }
    wwa_update_option('allow_authenticator_type', $post_allow_authenticator_type);

    $post_usernameless_login = sanitize_text_field($_POST['usernameless_login']);
    if($post_usernameless_login !== wwa_get_option('usernameless_login')){
        wwa_add_log($res_id, 'usernameless_login: "'.wwa_get_option('usernameless_login').'"->"'.$post_usernameless_login.'"');
    }
    wwa_update_option('usernameless_login', $post_usernameless_login);

    // $post_after_user_registration = sanitize_text_field($_POST['after_user_registration']);
    // if($post_after_user_registration !== wwa_get_option('after_user_registration')){
    //     wwa_add_log($res_id, 'after_user_registration: "'.wwa_get_option('after_user_registration').'"->"'.$post_after_user_registration.'"');
    // }
    // wwa_update_option('after_user_registration', $post_after_user_registration);

    add_settings_error('wwa_settings', 'save_success', __('Settings saved.', 'wp-webauthn'), 'success');
}elseif((isset($_POST['wwa_ref']) && $_POST['wwa_ref'] === 'true')){
    add_settings_error('wwa_settings', 'save_error', __('Settings NOT saved.', 'wp-webauthn'));
}
settings_errors('wwa_settings');

wp_localize_script('wwa_admin', 'configs', array('usernameless' => (wwa_get_option('usernameless_login') === false ? 'false' : wwa_get_option('usernameless_login')), 'allow_authenticator_type' => (wwa_get_option('allow_authenticator_type') === false ? 'none' : wwa_get_option('allow_authenticator_type'))));

// Only admin can change settings
if(wwa_validate_privileges()){ ?>
<form method="post" action="">
<?php
wp_nonce_field('wwa_options_update');
?>
<input type='hidden' name='wwa_ref' value='true'>
<table class="form-table">
<tr>
<th scope="row"><label for="first_choice"><?php _e('Preferred login method', 'wp-webauthn');?></label></th>
<td>
<?php $wwa_v_first_choice=wwa_get_option('first_choice');?>
<select name="first_choice" id="first_choice">
    <option value="true"<?php if($wwa_v_first_choice === 'true' || !$wwa_not_allowed){?> selected<?php }?>><?php _e('Prefer WebAuthn', 'wp-webauthn');?></option>
    <option value="false"<?php if($wwa_v_first_choice === 'false'){?> selected<?php }?>><?php _e('Prefer password', 'wp-webauthn');?></option>
    <option value="webauthn"<?php if($wwa_v_first_choice === 'webauthn' && !$wwa_not_allowed){?> selected<?php }if($wwa_not_allowed){?> disabled<?php }?>><?php _e('WebAuthn Only', 'wp-webauthn');?></option>
</select>
<p class="description"><?php _e('When using "WebAuthn Only", password login will be completely disabled. Please make sure your browser supports WebAuthn, otherwise you may unable to login.<br>User that doesn\'t have any registered authenticator (e.g. new user) will unable to login when using "WebAuthn Only".<br>When the browser does not support WebAuthn, the login method will default to password if password login is not disabled.', 'wp-webauthn');?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="website_name"><?php _e('Website identifier', 'wp-webauthn');?></label></th>
<td>
    <input required name="website_name" type="text" id="website_name" value="<?php echo wwa_get_option('website_name');?>" class="regular-text">
    <p class="description"><?php _e('This identifier is for identification purpose only and <strong>DOES NOT</strong> affect the authentication process in anyway.', 'wp-webauthn');?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="website_domain"><?php _e('Website domain', 'wp-webauthn');?></label></th>
<td>
    <input required name="website_domain" type="text" id="website_domain" value="<?php echo wwa_get_option('website_domain');?>" class="regular-text">
    <p class="description"><?php _e('This field <strong>MUST</strong> be exactly the same with the current domain or parent domain.', 'wp-webauthn');?></p>
</td>
</tr>
<tr>
<th scope="row"></th>
</tr>
<tr>
<th scope="row"><label for="remember_me"><?php _e('Allow to remember login', 'wp-webauthn');?></label></th>
<td>
<?php $wwa_v_rm=wwa_get_option('remember_me');
if($wwa_v_rm === false){
    wwa_update_option('remember_me', 'false');
    $wwa_v_rm = 'false';
}
?>
    <fieldset>
        <label><input type="radio" name="remember_me" value="true" <?php if($wwa_v_rm === 'true'){?>checked="checked"<?php }?>> <?php _e("Enable", "wp-webauthn");?></label><br>
        <label><input type="radio" name="remember_me" value="false" <?php if($wwa_v_rm === 'false'){?>checked="checked"<?php }?>> <?php _e("Disable", "wp-webauthn");?></label><br>
        <p class="description"><?php _e('Show the \'Remember Me\' checkbox beside the login form when using WebAuthn.', 'wp-webauthn');?></p>
    </fieldset>
</td>
</tr>
<tr>
<th scope="row"><label for="user_verification"><?php _e('Require user verification', 'wp-webauthn');?></label></th>
<td>
<?php $wwa_v_uv=wwa_get_option('user_verification');?>
    <fieldset id="wwa-uv-field">
        <label><input type="radio" name="user_verification" value="true" <?php if($wwa_v_uv === 'true'){?>checked="checked"<?php }?>> <?php _e("Enable", "wp-webauthn");?></label><br>
        <label><input type="radio" name="user_verification" value="false" <?php if($wwa_v_uv === 'false'){?>checked="checked"<?php }?>> <?php _e("Disable", "wp-webauthn");?></label><br>
        <p class="description"><?php _e('User verification can improve security, but is not fully supported by mobile devices. <br> If you cannot register or verify your authenticators, please consider disabling user verification.', 'wp-webauthn');?></p>
    </fieldset>
</td>
</tr>
<tr>
<th scope="row"><label for="usernameless_login"><?php _e('Allow to login without username', 'wp-webauthn');?></label></th>
<td>
<?php $wwa_v_ul=wwa_get_option('usernameless_login');
if($wwa_v_ul === false){
    wwa_update_option('usernameless_login', 'false');
    $wwa_v_ul = 'false';
}
?>
    <fieldset>
        <label><input type="radio" name="usernameless_login" value="true" <?php if($wwa_v_ul === 'true'){?>checked="checked"<?php }?>> <?php _e("Enable", "wp-webauthn");?></label><br>
        <label><input type="radio" name="usernameless_login" value="false" <?php if($wwa_v_ul === 'false'){?>checked="checked"<?php }?>> <?php _e("Disable", "wp-webauthn");?></label><br>
        <p class="description"><?php _e('Allow users to register authenticator with usernameless authentication feature and login without username.<br><strong>User verification will be enabled automatically when authenticating with usernameless authentication feature.</strong><br>Some authenticators and some browsers <strong>DO NOT</strong> support this feature.', 'wp-webauthn');?></p>
    </fieldset>
</td>
</tr>
<tr>
<th scope="row"><label for="allow_authenticator_type"><?php _e('Allow a specific type of authenticator', 'wp-webauthn');?></label></th>
<td>
<?php $wwa_v_at=wwa_get_option('allow_authenticator_type');
if($wwa_v_at === false){
    wwa_update_option('allow_authenticator_type', 'none');
    $wwa_v_at = 'none';
}
?>
<select name="allow_authenticator_type" id="allow_authenticator_type">
    <option value="none"<?php if($wwa_v_at === 'none'){?> selected<?php }?>><?php _e('Any', 'wp-webauthn');?></option>
    <option value="platform"<?php if($wwa_v_at === 'platform'){?> selected<?php }?>><?php _e('Platform (e.g. built-in fingerprint sensors)', 'wp-webauthn');?></option>
    <option value="cross-platform"<?php if($wwa_v_at === 'cross-platform'){?> selected<?php }?>><?php _e('Roaming (e.g. USB security keys)', 'wp-webauthn');?></option>
</select>
<p class="description"><?php _e('If a type is selected, the browser will only prompt for authenticators of selected type when authenticating and user can only register authenticators of selected type.', 'wp-webauthn');?></p>
</td>
</tr>
<!-- <tr>
<th scope="row"></th>
</tr>
<tr>
<th scope="row"><label for="after_user_registration"><?php _e('After User Registration', 'wp-webauthn');?></label></th>
<td> -->
<?php $wwa_v_aur=wwa_get_option('after_user_registration');
if($wwa_v_aur === false){
    wwa_update_option('after_user_registration', 'none');
    $wwa_v_aur = 'none';
}
?>
<!-- <select name="after_user_registration" id="after_user_registration">
    <option value="none"<?php if($wwa_v_aur === 'none'){?> selected<?php }?>><?php _e('No action', 'wp-webauthn');?></option>
    <option value="login"<?php if($wwa_v_aur === 'login'){?> selected<?php }?>><?php _e('Log user in immediately', 'wp-webauthn');?></option>
    <option value="guide"<?php if($wwa_v_aur === 'guide'){?> selected<?php }?>><?php _e('Redirect to WP-WebAuthn guide page', 'wp-webauthn');?></option>
</select>
<p class="description"><?php _e('What to do when a new user registered. Useful when "WebAuthn Only" is enabled.', 'wp-webauthn');?></p>
</td>
</tr>
<tr>
<th scope="row"></th>
</tr> -->
<tr>
<th scope="row"><label for="logging"><?php _e('Logging', 'wp-webauthn');?></label></th>
<td>
<?php $wwa_v_log=wwa_get_option('logging');
if($wwa_v_log === false){
    wwa_update_option('logging', 'false');
    $wwa_v_log = 'false';
}
?>
    <fieldset>
        <label><input type="radio" name="logging" value="true" <?php if($wwa_v_log === 'true'){?>checked="checked"<?php }?>> <?php _e("Enable", "wp-webauthn");?></label><br>
        <label><input type="radio" name="logging" value="false" <?php if($wwa_v_log === 'false'){?>checked="checked"<?php }?>> <?php _e("Disable", "wp-webauthn");?></label><br>
        <p>
            <button id="clear_log" class="button" <?php $log = get_option('wwa_log');if($log === false || ($log !== false && count($log) === 0)){?> disabled<?php }?>><?php _e('Clear log', 'wp-webauthn');?></button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="log-count"><?php echo __("Log count: ", "wp-webauthn").($log === false ? "0" : strval(count($log)));?></span>
        </p>
        <p class="description"><?php _e('For debugging only. Enable only when needed.<br><strong>Note: Logs may contain sensitive information.</strong>', 'wp-webauthn');?></p>
    </fieldset>
</td>
</tr>
</table><?php submit_button(); ?></form>
<?php
    if(wwa_get_option('logging') === 'true' || ($log !== false && count($log) > 0)){
?>
<div<?php if(wwa_get_option('logging') !== 'true'){?> id="wwa-remove-log"<?php }?>>
<h2><?php _e('Log', 'wp-webauthn');?></h2>
<textarea name="wwa_log" id="wwa_log" rows="20" cols="108" readonly><?php echo get_option("wwa_log") === false ? "" : implode("\n", get_option("wwa_log"));?></textarea>
<p class="description"><?php _e('Automatic update every 5 seconds.', 'wp-webauthn');?></p>
<br>
</div>
<?php }}?>
<p class="description"><?php printf(__('To register a new authenticator or edit your authenticators, please go to <a href="%s#wwa-webauthn-start">your profile</a>.', 'wp-webauthn'), admin_url('profile.php'));?></p>
</div>