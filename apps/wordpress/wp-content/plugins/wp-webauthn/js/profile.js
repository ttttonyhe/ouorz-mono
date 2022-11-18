// Whether the broswer supports WebAuthn
if (window.PublicKeyCredential === undefined || navigator.credentials.create === undefined || typeof navigator.credentials.create !== 'function') {
    jQuery('#wwa-bind, #wwa-test').attr('disabled', 'disabled');
    jQuery('#wwa-show-progress').html(php_vars.i18n_5);
}

jQuery(() => {
    updateList();
})

window.addEventListener('load', () => {
    if (document.getElementById('wp-webauthn-error-container')) {
        document.getElementById('wp-webauthn-error-container').insertBefore(document.getElementById('wp-webauthn-error'), null);
    }
})

// Update authenticator list
function updateList() {
    jQuery.ajax({
        url: php_vars.ajax_url,
        type: 'GET',
        data: {
            action: 'wwa_authenticator_list',
            user_id: php_vars.user_id
        },
        success: function (data) {
            if (typeof data === 'string') {
                console.warn(data);
                jQuery('#wwa-authenticator-list').html(`<tr><td colspan="${jQuery('.wwa-usernameless-th').css('display') === 'none' ? '5' : '6'}">${php_vars.i18n_8}</td></tr>`);
                return;
            }
            if (data.length === 0) {
                if (configs.usernameless === 'true') {
                    jQuery('.wwa-usernameless-th, .wwa-usernameless-td').show();
                } else {
                    jQuery('.wwa-usernameless-th, .wwa-usernameless-td').hide();
                }
                jQuery('#wwa-authenticator-list').html(`<tr><td colspan="${jQuery('.wwa-usernameless-th').css('display') === 'none' ? '5' : '6'}">${php_vars.i18n_17}</td></tr>`);
                jQuery('#wwa_usernameless_tip').text('');
                jQuery('#wwa_usernameless_tip').hide();
                jQuery('#wwa_type_tip').text('');
                jQuery('#wwa_type_tip').hide();
                return;
            }
            let htmlStr = '';
            let has_usernameless = false;
            let has_disabled_type = false;
            for (item of data) {
                let item_type_disabled = false;
                if (item.usernameless) {
                    has_usernameless = true;
                }
                if (configs.allow_authenticator_type !== 'none') {
                    if (configs.allow_authenticator_type !== item.type) {
                        has_disabled_type = true;
                        item_type_disabled = true;
                    }
                }
                htmlStr += `<tr><td>${item.name}</td><td>${item.type === 'none' ? php_vars.i18n_9 : (item.type === 'platform' ? php_vars.i18n_10 : php_vars.i18n_11)}${item_type_disabled ? php_vars.i18n_29 : ''}</td><td>${item.added}</td><td>${item.last_used}</td><td class="wwa-usernameless-td">${item.usernameless ? php_vars.i18n_24 + (configs.usernameless === 'true' ? '' : php_vars.i18n_26) : php_vars.i18n_25}</td><td id="${item.key}"><a href="javascript:renameAuthenticator('${item.key}', '${item.name}')">${php_vars.i18n_20}</a> | <a href="javascript:removeAuthenticator('${item.key}', '${item.name}')">${php_vars.i18n_12}</a></td></tr>`;
            }
            jQuery('#wwa-authenticator-list').html(htmlStr);
            if (has_usernameless || configs.usernameless === 'true') {
                jQuery('.wwa-usernameless-th, .wwa-usernameless-td').show();
            } else {
                jQuery('.wwa-usernameless-th, .wwa-usernameless-td').hide();
            }
            if (has_usernameless && configs.usernameless !== 'true') {
                jQuery('#wwa_usernameless_tip').text(php_vars.i18n_27);
                jQuery('#wwa_usernameless_tip').show();
            } else {
                jQuery('#wwa_usernameless_tip').text('');
                jQuery('#wwa_usernameless_tip').hide();
            }
            if (has_disabled_type && configs.allow_authenticator_type !== 'none') {
                if (configs.allow_authenticator_type === 'platform') {
                    jQuery('#wwa_type_tip').text(php_vars.i18n_30);
                } else {
                    jQuery('#wwa_type_tip').text(php_vars.i18n_31);
                }
                jQuery('#wwa_type_tip').show();
            } else {
                jQuery('#wwa_type_tip').text('');
                jQuery('#wwa_type_tip').hide();
            }
        },
        error: function () {
            jQuery('#wwa-authenticator-list').html(`<tr><td colspan="${jQuery('.wwa-usernameless-th').css('display') === 'none' ? '5' : '6'}">${php_vars.i18n_8}</td></tr>`);
        }
    })
}

/** Code Base64URL into Base64
 *
 * @param {string} input Base64URL coded string
 */
function base64url2base64(input) {
    input = input.replace(/=/g, '').replace(/-/g, '+').replace(/_/g, '/');
    const pad = input.length % 4;
    if (pad) {
        if (pad === 1) {
            throw new Error('InvalidLengthError: Input base64url string is the wrong length to determine padding');
        }
        input += new Array(5 - pad).join('=');
    }
    return input;
}

/** Code Uint8Array into Base64 string
 *
 * @param {Uint8Array} a The Uint8Array needed to be coded into Base64 string
 */
function arrayToBase64String(a) {
    return btoa(String.fromCharCode(...a));
}

jQuery('#wwa-add-new-btn').click((e) => {
    e.preventDefault();
    jQuery('#wwa-new-block').show();
    jQuery('#wwa-verify-block').hide();
    setTimeout(() => {
        jQuery('#wwa-new-block').focus();
    }, 0);
})

jQuery('#wwa-verify-btn').click((e) => {
    e.preventDefault();
    jQuery('#wwa-new-block').hide();
    jQuery('#wwa-verify-block').show();
    setTimeout(() => {
        jQuery('#wwa-verify-block').focus();
    }, 0);
})

jQuery('.wwa-cancel').click((e) => {
    e.preventDefault();
    jQuery('#wwa-new-block').hide();
    jQuery('#wwa-verify-block').hide();
})

jQuery('#wwa_authenticator_name').keydown((e) => {
    if (e.keyCode === 13) {
        jQuery('#wwa-bind').trigger('click');
        e.preventDefault();
    }
  });

// Bind an authenticator
jQuery('#wwa-bind').click((e) => {
    e.preventDefault();
    if (jQuery('#wwa_authenticator_name').val() === '') {
        alert(php_vars.i18n_7);
        return;
    }

    // Disable inputs to avoid changing in process
    jQuery('#wwa-show-progress').html(php_vars.i18n_1);
    jQuery('#wwa-bind').attr('disabled', 'disabled');
    jQuery('#wwa_authenticator_name').attr('disabled', 'disabled');
    jQuery('.wwa_authenticator_usernameless').attr('disabled', 'disabled');
    jQuery('#wwa_authenticator_type').attr('disabled', 'disabled');
    jQuery.ajax({
        url: php_vars.ajax_url,
        type: 'GET',
        data: {
            action: 'wwa_create',
            name: jQuery('#wwa_authenticator_name').val(),
            type: jQuery('#wwa_authenticator_type').val(),
            usernameless: jQuery('.wwa_authenticator_usernameless:checked').val() ? jQuery('.wwa_authenticator_usernameless:checked').val() : 'false',
            user_id: php_vars.user_id
        },
        success: function (data) {
            if (typeof data === 'string') {
                console.warn(data);
                jQuery('#wwa-show-progress').html(`${php_vars.i18n_4}: ${data}`);
                jQuery('#wwa-bind').removeAttr('disabled');
                jQuery('#wwa_authenticator_name').removeAttr('disabled');
                jQuery('.wwa_authenticator_usernameless').removeAttr('disabled');
                jQuery('#wwa_authenticator_type').removeAttr('disabled');
                updateList();
                return;
            }
            // Get the args, code string into Uint8Array
            jQuery('#wwa-show-progress').text(php_vars.i18n_2);
            let challenge = new Uint8Array(32);
            let user_id = new Uint8Array(32);
            challenge = Uint8Array.from(window.atob(base64url2base64(data.challenge)), (c) => c.charCodeAt(0));
            user_id = Uint8Array.from(window.atob(base64url2base64(data.user.id)), (c) => c.charCodeAt(0));

            let public_key = {
                challenge: challenge,
                rp: {
                    id: data.rp.id,
                    name: data.rp.name
                },
                user: {
                    id: user_id,
                    name: data.user.name,
                    displayName: data.user.displayName
                },
                pubKeyCredParams: data.pubKeyCredParams,
                authenticatorSelection: data.authenticatorSelection,
                timeout: data.timeout
            }

            // If some authenticators are already registered, exclude
            if (data.excludeCredentials) {
                public_key.excludeCredentials = data.excludeCredentials.map((item) => {
                    item.id = Uint8Array.from(window.atob(base64url2base64(item.id)), (c) => c.charCodeAt(0));
                    return item;
                })
            }

            // Save client ID
            const clientID = data.clientID;
            delete data.clientID;

            // Create, a pop-up window should appear
            navigator.credentials.create({ 'publicKey': public_key }).then((newCredentialInfo) => {
                jQuery('#wwa-show-progress').html(php_vars.i18n_6);
                return newCredentialInfo;
            }).then((data) => {
                // Code Uint8Array into string for transmission
                const publicKeyCredential = {
                    id: data.id,
                    type: data.type,
                    rawId: arrayToBase64String(new Uint8Array(data.rawId)),
                    response: {
                        clientDataJSON: arrayToBase64String(new Uint8Array(data.response.clientDataJSON)),
                        attestationObject: arrayToBase64String(new Uint8Array(data.response.attestationObject))
                    }
                };
                return publicKeyCredential;
            }).then(JSON.stringify).then((AuthenticatorAttestationResponse) => {
                // Send attestation back to RP
                jQuery.ajax({
                    url: `${php_vars.ajax_url}?action=wwa_create_response`,
                    type: 'POST',
                    data: {
                        data: window.btoa(AuthenticatorAttestationResponse),
                        name: jQuery('#wwa_authenticator_name').val(),
                        type: jQuery('#wwa_authenticator_type').val(),
                        usernameless: jQuery('.wwa_authenticator_usernameless:checked').val() ? jQuery('.wwa_authenticator_usernameless:checked').val() : 'false',
                        clientid: clientID,
                        user_id: php_vars.user_id
                    },
                    success: function (data) {
                        if (data === 'true') {
                            // Registered
                            jQuery('#wwa-show-progress').html(php_vars.i18n_3);
                            jQuery('#wwa-bind').removeAttr('disabled');
                            jQuery('#wwa_authenticator_name').removeAttr('disabled');
                            jQuery('#wwa_authenticator_name').val('');
                            jQuery('.wwa_authenticator_usernameless').removeAttr('disabled');
                            jQuery('#wwa_authenticator_type').removeAttr('disabled');
                            updateList();
                        } else {
                            // Register failed
                            jQuery('#wwa-show-progress').html(php_vars.i18n_4);
                            jQuery('#wwa-bind').removeAttr('disabled');
                            jQuery('#wwa_authenticator_name').removeAttr('disabled');
                            jQuery('.wwa_authenticator_usernameless').removeAttr('disabled');
                            jQuery('#wwa_authenticator_type').removeAttr('disabled');
                            updateList();
                        }
                    },
                    error: function () {
                        jQuery('#wwa-show-progress').html(php_vars.i18n_4);
                        jQuery('#wwa-bind').removeAttr('disabled');
                        jQuery('#wwa_authenticator_name').removeAttr('disabled');
                        jQuery('.wwa_authenticator_usernameless').removeAttr('disabled');
                        jQuery('#wwa_authenticator_type').removeAttr('disabled');
                        updateList();
                    }
                })
            }).catch((error) => {
                // Creation abort
                console.warn(error);
                jQuery('#wwa-show-progress').html(`${php_vars.i18n_4}: ${error}`);
                jQuery('#wwa-bind').removeAttr('disabled');
                jQuery('#wwa_authenticator_name').removeAttr('disabled');
                jQuery('.wwa_authenticator_usernameless').removeAttr('disabled');
                jQuery('#wwa_authenticator_type').removeAttr('disabled');
                updateList();
            })
        },
        error: function () {
            jQuery('#wwa-show-progress').html(php_vars.i18n_4);
            jQuery('#wwa-bind').removeAttr('disabled');
            jQuery('#wwa_authenticator_name').removeAttr('disabled');
            jQuery('.wwa_authenticator_usernameless').removeAttr('disabled');
            jQuery('#wwa_authenticator_type').removeAttr('disabled');
            updateList();
        }
    })
});

// Test WebAuthn
jQuery('#wwa-test, #wwa-test_usernameless').click((e) => {
    jQuery('#wwa-test, #wwa-test_usernameless').attr('disabled', 'disabled');
    let button_id = e.target.id;
    let usernameless = 'false';
    let tip_id = '#wwa-show-test';
    if (button_id === 'wwa-test_usernameless') {
        usernameless = 'true';
        tip_id = '#wwa-show-test-usernameless';
    }
    jQuery(tip_id).text(php_vars.i18n_1);
    jQuery.ajax({
        url: php_vars.ajax_url,
        type: 'GET',
        data: {
            action: 'wwa_auth_start',
            type: 'test',
            usernameless: usernameless,
            user_id: php_vars.user_id
        },
        success: function (data) {
            if (typeof data === 'string') {
                console.warn(data);
                jQuery(tip_id).html(`${php_vars.i18n_15}: ${data}`);
                jQuery('#wwa-test, #wwa-test_usernameless').removeAttr('disabled');
                return;
            }
            if (data === 'User not inited.') {
                jQuery(tip_id).html(`${php_vars.i18n_15}: ${php_vars.i18n_17}`);
                jQuery('#wwa-test, #wwa-test_usernameless').removeAttr('disabled');
                return;
            }
            jQuery(tip_id).text(php_vars.i18n_13);
            data.challenge = Uint8Array.from(window.atob(base64url2base64(data.challenge)), (c) => c.charCodeAt(0));

            if (data.allowCredentials) {
                data.allowCredentials = data.allowCredentials.map((item) => {
                    item.id = Uint8Array.from(window.atob(base64url2base64(item.id)), (c) => c.charCodeAt(0));
                    return item;
                });
            }

            if (data.allowCredentials && configs.allow_authenticator_type && configs.allow_authenticator_type !== 'none') {
                for (let credential of data.allowCredentials) {
                    if (configs.allow_authenticator_type === 'cross-platform') {
                        credential.transports = ['usb', 'nfc', 'ble'];
                    } else if (configs.allow_authenticator_type === 'platform') {
                        credential.transports = ['internal'];
                    }
                }
            }

            // Save client ID
            const clientID = data.clientID;
            delete data.clientID;

            navigator.credentials.get({ 'publicKey': data }).then((credentialInfo) => {
                jQuery(tip_id).html(php_vars.i18n_14);
                return credentialInfo;
            }).then((data) => {
                const publicKeyCredential = {
                    id: data.id,
                    type: data.type,
                    rawId: arrayToBase64String(new Uint8Array(data.rawId)),
                    response: {
                        authenticatorData: arrayToBase64String(new Uint8Array(data.response.authenticatorData)),
                        clientDataJSON: arrayToBase64String(new Uint8Array(data.response.clientDataJSON)),
                        signature: arrayToBase64String(new Uint8Array(data.response.signature)),
                        userHandle: data.response.userHandle ? arrayToBase64String(new Uint8Array(data.response.userHandle)) : null
                    }
                };
                return publicKeyCredential;
            }).then(JSON.stringify).then((AuthenticatorResponse) => {
                jQuery.ajax({
                    url: `${php_vars.ajax_url}?action=wwa_auth`,
                    type: 'POST',
                    data: {
                        data: window.btoa(AuthenticatorResponse),
                        type: 'test',
                        remember: 'false',
                        clientid: clientID,
                        user_id: php_vars.user_id
                    },
                    success: function (data) {
                        if (data === 'true') {
                            jQuery(tip_id).html(php_vars.i18n_16);
                            jQuery('#wwa-test, #wwa-test_usernameless').removeAttr('disabled');
                            updateList();
                        } else {
                            jQuery(tip_id).html(php_vars.i18n_15);
                            jQuery('#wwa-test, #wwa-test_usernameless').removeAttr('disabled');
                        }
                    },
                    error: function () {
                        jQuery(tip_id).html(php_vars.i18n_15);
                        jQuery('#wwa-test, #wwa-test_usernameless').removeAttr('disabled');
                    }
                })
            }).catch((error) => {
                console.warn(error);
                jQuery(tip_id).html(`${php_vars.i18n_15}: ${error}`);
                jQuery('#wwa-test, #wwa-test_usernameless').removeAttr('disabled');
            })
        },
        error: function () {
            jQuery(tip_id).html(php_vars.i18n_15);
            jQuery('#wwa-test, #wwa-test_usernameless').removeAttr('disabled');
        }
    })
});

/**
 * Rename an authenticator
 * @param {string} id Authenticator ID
 * @param {string} name Current authenticator name
 */
function renameAuthenticator(id, name) {
    let new_name = prompt(php_vars.i18n_21, name);
    if (new_name === '') {
        alert(php_vars.i18n_7);
    } else if (new_name !== null && new_name !== name) {
        jQuery(`#${id}`).text(php_vars.i18n_22)
        jQuery.ajax({
            url: php_vars.ajax_url,
            type: 'GET',
            data: {
                action: 'wwa_modify_authenticator',
                id: id,
                name: new_name,
                target: 'rename',
                user_id: php_vars.user_id
            },
            success: function () {
                updateList();
            },
            error: function (data) {
                alert(`Error: ${data}`);
                updateList();
            }
        })
    }
}

/**
 * Remove an authenticator
 * @param {string} id Authenticator ID
 * @param {string} name Authenticator name
 */
function removeAuthenticator(id, name) {
    if (confirm(php_vars.i18n_18 + name + (jQuery('#wwa-authenticator-list > tr').length === 1 ? '\n' + php_vars.i18n_28 : ''))) {
        jQuery(`#${id}`).text(php_vars.i18n_19)
        jQuery.ajax({
            url: php_vars.ajax_url,
            type: 'GET',
            data: {
                action: 'wwa_modify_authenticator',
                id: id,
                target: 'remove',
                user_id: php_vars.user_id
            },
            success: function () {
                updateList();
            },
            error: function (data) {
                alert(`Error: ${data}`);
                updateList();
            }
        })
    }
}
