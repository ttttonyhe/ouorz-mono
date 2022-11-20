<?php
// Insert CSS and JS
wp_enqueue_script('wwa_profile', plugins_url('js/profile.js', __FILE__));
wp_localize_script('wwa_profile', 'php_vars', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'user_id' => $user->ID,
    'i18n_1' => __('Initializing...', 'wp-webauthn'),
    'i18n_2' => __('Please follow instructions to finish registration...', 'wp-webauthn'),
    'i18n_3' => '<span class="wwa-success">'._x('Registered', 'action', 'wp-webauthn').'</span>',
    'i18n_4' => '<span class="wwa-failed">'.__('Registration failed', 'wp-webauthn').'</span>',
    'i18n_5' => __('Your browser does not support WebAuthn', 'wp-webauthn'),
    'i18n_6' => __('Registrating...', 'wp-webauthn'),
    'i18n_7' => __('Please enter the authenticator identifier', 'wp-webauthn'),
    'i18n_8' => __('Loading failed, maybe try refreshing?', 'wp-webauthn'),
    'i18n_9' => __('Any', 'wp-webauthn'),
    'i18n_10' => __('Platform authenticator', 'wp-webauthn'),
    'i18n_11' => __('Roaming authenticator', 'wp-webauthn'),
    'i18n_12' => __('Remove', 'wp-webauthn'),
    'i18n_13' => __('Please follow instructions to finish verification...', 'wp-webauthn'),
    'i18n_14' => __('Verifying...', 'wp-webauthn'),
    'i18n_15' => '<span class="wwa-failed">'.__('Verification failed', 'wp-webauthn').'</span>',
    'i18n_16' => '<span class="wwa-success">'.__('Verification passed! You can now log in through WebAuthn', 'wp-webauthn').'</span>',
    'i18n_17' => __('No registered authenticators', 'wp-webauthn'),
    'i18n_18' => __('Confirm removal of authenticator: ', 'wp-webauthn'),
    'i18n_19' => __('Removing...', 'wp-webauthn'),
    'i18n_20' => __('Rename', 'wp-webauthn'),
    'i18n_21' => __('Rename the authenticator', 'wp-webauthn'),
    'i18n_22' => __('Renaming...', 'wp-webauthn'),
    'i18n_24' => __('Ready', 'wp-webauthn'),
    'i18n_25' => __('No', 'wp-webauthn'),
    'i18n_26' => __(' (Unavailable)', 'wp-webauthn'),
    'i18n_27' => __('The site administrator has disabled usernameless login feature.', 'wp-webauthn'),
    'i18n_28' => __('After removing this authenticator, you will not be able to login with WebAuthn', 'wp-webauthn'),
    'i18n_29' => __(' (Disabled)', 'wp-webauthn'),
    'i18n_30' => __('The site administrator only allow platform authenticators currently.', 'wp-webauthn'),
    'i18n_31' => __('The site administrator only allow roaming authenticators currently.', 'wp-webauthn')
));
wp_enqueue_style('wwa_profile', plugins_url('css/admin.css', __FILE__));
wp_localize_script('wwa_profile', 'configs', array('usernameless' => (wwa_get_option('usernameless_login') === false ? "false" : wwa_get_option('usernameless_login')), 'allow_authenticator_type' => (wwa_get_option('allow_authenticator_type') === false ? "none" : wwa_get_option('allow_authenticator_type'))));
?>
<br>
<h2 id="wwa-webauthn-start">WebAuthn</h2>
<?php
$wwa_not_allowed = false;
if(!function_exists("mb_substr") || !function_exists("gmp_intval") || !wwa_check_ssl() && (parse_url(site_url(), PHP_URL_HOST) !== 'localhost' && parse_url(site_url(), PHP_URL_HOST) !== '127.0.0.1')){
    $wwa_not_allowed = true;
?>
<div id="wp-webauthn-error-container">
    <div class="notice notice-error is-dismissible" role="alert" id="wp-webauthn-error">
        <p><?php _e('This site is not correctly configured to use WebAuthn. Please contact the site administrator.', 'wp-webauthn')?></p>
    </div>
</div>
<?php } ?>
<table class="form-table">
<tr class="user-rich-editing-wrap">
    <th scope="row"><?php _e('WebAuthn Only', 'wp-webauthn');?></th>
        <td>
            <label for="webauthn_only">
                <?php $wwa_v_first_choice = wwa_get_option('first_choice');?>
                <input name="webauthn_only" type="checkbox" id="webauthn_only" value="true"<?php if(!$wwa_not_allowed){if($wwa_v_first_choice === 'webauthn'){echo ' disabled checked';}else{if(get_the_author_meta('webauthn_only', $user->ID) === 'true'){echo ' checked';}}}else{echo ' disabled';} ?>> <?php _e('Disable password login for this account', 'wp-webauthn');?>
            </label>
            <p class="description"><?php _e('When checked, password login will be completely disabled. Please make sure your browser supports WebAuthn and you have a registered authenticator, otherwise you may unable to login.', 'wp-webauthn');if($wwa_v_first_choice === 'webauthn' && !$wwa_not_allowed){?><br><strong><?php _e('The site administrator has disabled password login for the whole site.', 'wp-webauthn');?></strong><?php }?></p>
        </td>
    </tr>
</table>
<h3><?php _e('Registered WebAuthn Authenticators', 'wp-webauthn');?></h3>
<div class="wwa-table">
<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th><?php _e('Identifier', 'wp-webauthn');?></th>
            <th><?php _e('Type', 'wp-webauthn');?></th>
            <th><?php _ex('Registered', 'time', 'wp-webauthn');?></th>
            <th><?php _e('Last used', 'wp-webauthn');?></th>
            <th class="wwa-usernameless-th"><?php _e('Usernameless', 'wp-webauthn');?></th>
            <th><?php _e('Action', 'wp-webauthn');?></th>
        </tr>
    </thead>
    <tbody id="wwa-authenticator-list">
        <tr>
            <td colspan="5"><?php _e('Loading...', 'wp-webauthn');?></td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th><?php _e('Identifier', 'wp-webauthn');?></th>
            <th><?php _e('Type', 'wp-webauthn');?></th>
            <th><?php _ex('Registered', 'time', 'wp-webauthn');?></th>
            <th><?php _e('Last used', 'wp-webauthn');?></th>
            <th class="wwa-usernameless-th"><?php _e('Usernameless', 'wp-webauthn');?></th>
            <th><?php _e('Action', 'wp-webauthn');?></th>
      </tr>
    </tfoot>
</table>
</div>
<p id="wwa_usernameless_tip"></p>
<p id="wwa_type_tip"></p>
<button id="wwa-add-new-btn" class="button" title="<?php _e('Register New Authenticator', 'wp-webauthn');?>"<?php if($wwa_not_allowed){echo ' disabled';}?>><?php _e('Register New Authenticator', 'wp-webauthn');?></button>&nbsp;&nbsp;<button id="wwa-verify-btn" class="button" title="<?php _e('Verify Authenticator', 'wp-webauthn');?>"><?php _e('Verify Authenticator', 'wp-webauthn');?></button>
<div id="wwa-new-block" tabindex="-1">
<button class="button button-small wwa-cancel"><?php _e('Close');?></button>
<h2><?php _e('Register New Authenticator', 'wp-webauthn');?></h2>
<p class="description"><?php printf(__('You are about to associate an authenticator with the current account <strong>%s</strong>.<br>You can register multiple authenticators for an account.', 'wp-webauthn'), $user->user_login);?></p>
<table class="form-table">
<tr>
<th scope="row"><label for="wwa_authenticator_type"><?php _e('Type of authenticator', 'wp-webauthn');?></label></th>
<td>
<?php
$allowed_type = wwa_get_option('allow_authenticator_type') === false ? 'none' : wwa_get_option('allow_authenticator_type');
?>
<select name="wwa_authenticator_type" id="wwa_authenticator_type">
    <option value="none" id="type-none" class="sub-type"<?php if($allowed_type !== 'none'){echo ' disabled';}?>><?php _e('Any', 'wp-webauthn');?></option>
    <option value="platform" id="type-platform" class="sub-type"<?php if($allowed_type === 'cross-platform'){echo ' disabled';}?>><?php _e('Platform (e.g. built-in fingerprint sensors)', 'wp-webauthn');?></option>
    <option value="cross-platform" id="type-cross-platform" class="sub-type"<?php if($allowed_type === 'platform'){echo ' disabled';}?>><?php _e('Roaming (e.g. USB security keys)', 'wp-webauthn');?></option>
</select>
<p class="description"><?php _e('If a type is selected, the browser will only prompt for authenticators of selected type. <br> Regardless of the type, you can only log in with the very same authenticators you\'ve registered.', 'wp-webauthn');?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="wwa_authenticator_name"><?php _e('Authenticator Identifier', 'wp-webauthn');?></label></th>
<td>
    <input name="wwa_authenticator_name" type="text" id="wwa_authenticator_name" class="regular-text">
    <p class="description"><?php _e('An easily identifiable name for the authenticator. <strong>DOES NOT</strong> affect the authentication process in anyway.', 'wp-webauthn');?></p>
</td>
</tr>
<?php if(wwa_get_option('usernameless_login') === "true"){?>
<tr>
<th scope="row"><label for="wwa_authenticator_usernameless"><?php _e('Login without username', 'wp-webauthn');?></th>
<td>
    <fieldset>
        <label><input type="radio" name="wwa_authenticator_usernameless" class="wwa_authenticator_usernameless" value="true"> <?php _e("Enable", "wp-webauthn");?></label><br>
        <label><input type="radio" name="wwa_authenticator_usernameless" class="wwa_authenticator_usernameless" value="false" checked="checked"> <?php _e("Disable", "wp-webauthn");?></label><br>
        <p class="description"><?php _e('If registered authenticator with this feature, you can login without enter your username.<br>Some authenticators like U2F-only authenticators and some browsers <strong>DO NOT</strong> support this feature.<br>A record will be stored in the authenticator permanently untill you reset it.', 'wp-webauthn');?></p>
    </fieldset>
</td>
</tr>
<?php }?>
</table>
<button id="wwa-bind" class="button"><?php _e('Start Registration', 'wp-webauthn');?></button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="wwa-show-progress"></span>
</div>
<div id="wwa-verify-block" tabindex="-1">
<button class="button button-small wwa-cancel"><?php _e('Close');?></button>
<h2><?php _e('Verify Authenticator', 'wp-webauthn');?></h2>
<p class="description"><?php _e('Click Test Login to verify that the registered authenticators are working.', 'wp-webauthn');?></p>
<button id="wwa-test" class="button"><?php _e('Test Login', 'wp-webauthn');?></button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="wwa-show-test"></span>
<?php if(wwa_get_option('usernameless_login') === "true"){?>
<br><br><button id="wwa-test_usernameless" class="button"><?php _e('Test Login (usernameless)', 'wp-webauthn');?></button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="wwa-show-test-usernameless"></span>
<?php }?>
</div>