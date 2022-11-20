'use strict';

// Send an AJAX request and get the response
const wwa_ajax = function () {
    let xmlHttpReq = new XMLHttpRequest();
    return {
        /** Send an AJAX GET request and get the response
         *
         * @param {string} url URL
         * @param {string} data Attached data
         * @param {object} callback Callback function
         */
        get: (url, data = '', callback = () => { }) => {
            xmlHttpReq.open('GET', url + data, true);
            xmlHttpReq.send();
            xmlHttpReq.onreadystatechange = () => {
                if (xmlHttpReq.readyState === 4 && xmlHttpReq.status === 200) {
                    callback(xmlHttpReq.responseText, true);
                } else if (xmlHttpReq.readyState === 4) {
                    callback('Network Error.', false);
                }
            }
        },
        /** Send an AJAX POST request and get the response
         *
         * @param {string} url URL
         * @param {string} data Attached data
         * @param {object} callback Callback function
         */
        post: (url, data = '', callback = () => { }) => {
            xmlHttpReq.open('POST', url, true);
            xmlHttpReq.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xmlHttpReq.send(data);
            xmlHttpReq.onreadystatechange = () => {
                if (xmlHttpReq.readyState === 4 && xmlHttpReq.status === 200) {
                    callback(xmlHttpReq.responseText, true);
                } else if (xmlHttpReq.readyState === 4) {
                    callback('Network Error.', false);
                }
            }
        }
    }
};

/** Operate selected DOMs
 * 
 * @param {string} selector DOM selector
 * @param {object} callback Callbck function
 * @param {string} method Selecte method
 */
const wwa_dom = (selector, callback = () => { }, method = 'query') => {
    let dom_list = [];
    if (method === 'id') {
        let dom = document.getElementById(selector);
        if (dom) {
            callback(dom);
        }
        return;
    } else if (method === 'class') {
        dom_list = document.getElementsByClassName(selector);
    } else if (method === 'tag') {
        dom_list = document.getElementsByTagName(selector);
    } else {
        dom_list = document.querySelectorAll(selector);
    }
    for (let dom of dom_list) {
        callback(dom);
    }
    return;
}

let wwaSupported = true;
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelectorAll('#lostpasswordform, #registerform, .admin-email-confirm-form').length > 0) {
        return;
    }
    let button_check = document.createElement('button');
    button_check.id = 'wp-webauthn-check';
    button_check.type = 'button';
    button_check.className = 'button button-large button-primary';
    button_check.innerHTML = php_vars.i18n_1;
    let button_toggle = document.createElement('button');
    if (php_vars.webauthn_only !== 'true') {
        button_toggle.id = 'wp-webauthn';
        button_toggle.type = 'button';
        button_toggle.className = 'button button-large';
        button_toggle.innerHTML = '<span class="dashicons dashicons-update-alt"></span>';
    }
    let submit = document.getElementById('wp-submit');
    if (submit) {
        if (php_vars.webauthn_only !== 'true') {
            submit.parentNode.insertBefore(button_toggle, submit.nextElementSibling);
        }
        submit.parentNode.insertBefore(button_check, submit.nextElementSibling);
    }
    let notice = document.createElement('div');
    notice.className = 'wp-webauthn-notice';
    notice.innerHTML = `<span><span class="dashicons dashicons-shield-alt"></span> ${php_vars.i18n_2}<span>`;
    let forgetmenot = document.getElementsByClassName('forgetmenot');
    if (forgetmenot.length > 0) {
        forgetmenot[0].parentNode.insertBefore(notice, forgetmenot[0]);
    }
    wwa_dom('wp-webauthn-notice', (dom) => {
        const passwordInput = document.getElementsByClassName('user-pass-wrap');
        if (passwordInput.length > 0) {
            dom.style.height = (passwordInput[0].offsetHeight - 10) + 'px';
        } else {
            // WordPress 5.2-
            const legacyPasswordInput = document.getElementById('loginform').getElementsByTagName('p')[1];
            dom.style.height = (legacyPasswordInput.offsetHeight - 10) + 'px';
        }
    }, 'class');
    let btnWidth = document.getElementById('wp-submit') ? document.getElementById('wp-submit').clientWidth : 0;
    if (btnWidth < 20 || btnWidth === undefined) {
        wwa_dom('wp-webauthn-check', (dom) => { dom.style.width = 'auto' }, 'id');
    } else {
        wwa_dom('wp-webauthn-check', (dom) => { dom.style.width = btnWidth }, 'id');
    }
    if (window.PublicKeyCredential === undefined || navigator.credentials.create === undefined || typeof navigator.credentials.create !== 'function') {
        wwaSupported = false;
        wwa_dom('wp-webauthn', (dom) => { dom.style.display = 'none' }, 'id');
    }
    wwa_dom('wp-webauthn-check', (dom) => { dom.addEventListener('click', check, false) }, 'id');
    wwa_dom('wp-webauthn', (dom) => { dom.addEventListener('click', toggle, false) }, 'id');
})

window.onresize = function () {
    if (document.querySelectorAll('#lostpasswordform, #registerform').length > 0) {
        return;
    }
    let btnWidth = document.getElementById('wp-submit').clientWidth;
    if (btnWidth < 20 || btnWidth === undefined) {
        wwa_dom('wp-webauthn-check', (dom) => { dom.style.width = 'auto' }, 'id');
    } else {
        wwa_dom('wp-webauthn-check', (dom) => { dom.style.width = btnWidth }, 'id');
    }
}

document.addEventListener('keydown', parseKey, false);

function parseKey(event) {
    if (wwaSupported && document.getElementById('wp-webauthn-check').style.display === 'block') {
        if (event.keyCode === 13) {
            event.preventDefault();
            wwa_dom('wp-webauthn-check', (dom) => { dom.click() }, 'id');
        }
    }
}

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


function arrayToBase64String(a) {
    return btoa(String.fromCharCode(...a));
}

function getQueryString(name) {
    let reg = new RegExp(`(^|&)${name}=([^&]*)(&|$)`, 'i');
    let reg_rewrite = new RegExp(`(^|/)${name}/([^/]*)(/|$)`, 'i');
    let r = window.location.search.substr(1).match(reg);
    let q = window.location.pathname.substr(1).match(reg_rewrite);
    if (r != null) {
        return unescape(r[2]);
    } else if (q != null) {
        return unescape(q[2]);
    } else {
        return null;
    }
}

function toggle() {
    if (document.querySelectorAll('#lostpasswordform, #registerform').length > 0) {
        return;
    }
    if (wwaSupported) {
        if (document.getElementsByClassName('wp-webauthn-notice')[0].style.display === 'flex') {
            if (document.getElementsByClassName('user-pass-wrap').length > 0) {
                wwa_dom('.user-pass-wrap, .forgetmenot, #wp-submit', (dom) => { dom.style.display = 'block' });
            } else {
                // WordPress 5.2-
                wwa_dom('.forgetmenot, #wp-submit', (dom) => { dom.style.display = 'block' });
                document.getElementById('loginform').getElementsByTagName('p')[1].style.display = 'block';
            }
            wwa_dom('wp-webauthn-notice', (dom) => { dom.style.display = 'none' }, 'class');
            wwa_dom('wp-webauthn-check', (dom) => { dom.style.cssText = `${dom.style.cssText.split('display: block !important')[0]}display: none !important` }, 'id');
            wwa_dom('user_pass', (dom) => { dom.disabled = false }, 'id');
            wwa_dom('user_login', (dom) => { dom.focus() }, 'id');
            wwa_dom('wp-webauthn-notice', (dom) => { dom.innerHTML = `<span><span class="dashicons dashicons-shield-alt"></span> ${php_vars.i18n_2}</span>` }, 'class');
            wwa_dom('wp-submit', (dom) => { dom.disabled = false }, 'id');
            let inputDom = document.querySelectorAll('#loginform label')
            if (inputDom.length > 0) {
                if (document.getElementById('wwa-username-label')) {
                    // WordPress 5.2-
                    document.getElementById('wwa-username-label').innerText = php_vars.i18n_10;
                } else {
                    inputDom[0].innerText = php_vars.i18n_10;
                }
            }
        } else {
            if (document.getElementsByClassName('user-pass-wrap').length > 0) {
                wwa_dom('.user-pass-wrap, #wp-submit', (dom) => { dom.style.display = 'none' });
                if (php_vars.remember_me === 'false' ) {
                    wwa_dom('.forgetmenot', (dom) => { dom.style.display = 'none' });
                }
            } else {
                // WordPress 5.2-
                wwa_dom('#wp-submit', (dom) => { dom.style.display = 'none' });
                if (php_vars.remember_me === 'false' ) {
                    wwa_dom('.forgetmenot', (dom) => { dom.style.display = 'none' });
                }
                document.getElementById('loginform').getElementsByTagName('p')[1].style.display = 'none';
            }
            wwa_dom('wp-webauthn-notice', (dom) => { dom.style.display = 'flex' }, 'class');
            wwa_dom('wp-webauthn-check', (dom) => { dom.style.cssText = `${dom.style.cssText.split('display: none !important')[0]}display: block !important` }, 'id');
            wwa_dom('user_login', (dom) => { dom.focus() }, 'id');
            wwa_dom('wp-webauthn-notice', (dom) => { dom.innerHTML = `<span><span class="dashicons dashicons-shield-alt"></span> ${php_vars.i18n_2}</span>` }, 'class');
            wwa_dom('wp-submit', (dom) => { dom.disabled = true }, 'id');
            let inputDom = document.querySelectorAll('#loginform label')
            if (inputDom.length > 0) {
                if (document.getElementById('wwa-username-label')) {
                    // WordPress 5.2-
                    document.getElementById('wwa-username-label').innerText = php_vars.i18n_9;
                } else {
                    inputDom[0].innerText = php_vars.i18n_9;
                }
            }
        }
    }
}

// Shake the login form, code from WordPress
function wwa_shake(id, a, d) {
    const c = a.shift();
    document.getElementById(id).style.left = c + 'px';
    if (a.length > 0) {
        setTimeout(() => {
            wwa_shake(id, a, d);
        }, d);
    } else {
        try {
            document.getElementById(id).style.position = 'static';
            wwa_dom('user_login', (dom) => { dom.focus() }, 'id');
        } catch (e) { }
    }
}

function check() {
    if (document.querySelectorAll('#lostpasswordform, #registerform').length > 0) {
        return;
    }
    if (wwaSupported) {
        if (document.getElementById('user_login').value === '' && php_vars.usernameless !== 'true') {
            wwa_dom('login_error', (dom) => { dom.remove() }, 'id');
            wwa_dom('p.message', (dom) => { dom.remove() });
            if (document.querySelectorAll('#login > h1').length > 0) {
                let dom = document.createElement('div');
                dom.id = 'login_error';
                dom.innerHTML = php_vars.i18n_11;
                document.querySelectorAll('#login > h1')[0].parentNode.insertBefore(dom, document.querySelectorAll('#login > h1')[0].nextElementSibling)
            }
            // Shake the login form, code from WordPress
            let shake = new Array(15, 30, 15, 0, -15, -30, -15, 0);
            shake = shake.concat(shake.concat(shake));
            var form = document.forms[0].id;
            document.getElementById(form).style.position = 'relative';
            wwa_shake(form, shake, 20);
            return;
        }
        wwa_dom('user_login', (dom) => { dom.readOnly = true }, 'id');
        wwa_dom('#wp-webauthn-check, #wp-webauthn', (dom) => { dom.disabled = true });
        wwa_dom('wp-webauthn-notice', (dom) => { dom.innerHTML = php_vars.i18n_3 }, 'class');
        let request = wwa_ajax();
        request.get(php_vars.ajax_url, `?action=wwa_auth_start&user=${encodeURIComponent(document.getElementById('user_login').value)}&type=auth`, (rawData, status) => {
            if (status) {
                wwa_dom('wp-webauthn-notice', (dom) => { dom.innerHTML = php_vars.i18n_4 }, 'class');
                let data = rawData;
                try {
                    data = JSON.parse(rawData);
                } catch (e) {
                    console.warn(rawData);
                    if (php_vars.usernameless === 'true' && document.getElementById('user_login').value === '') {
                        wwa_dom('wp-webauthn-notice', (dom) => { dom.innerHTML = php_vars.i18n_7 + php_vars.i18n_12 }, 'class');
                    } else {
                        wwa_dom('wp-webauthn-notice', (dom) => { dom.innerHTML = php_vars.i18n_7 }, 'class');
                    }
                    wwa_dom('user_login', (dom) => { dom.readOnly = false }, 'id');
                    wwa_dom('#wp-webauthn-check, #wp-webauthn', (dom) => { dom.disabled = false });
                    return;
                }
                data.challenge = Uint8Array.from(window.atob(base64url2base64(data.challenge)), (c) => c.charCodeAt(0));

                if (data.allowCredentials) {
                    data.allowCredentials = data.allowCredentials.map((item) => {
                        item.id = Uint8Array.from(window.atob(base64url2base64(item.id)), (c) => c.charCodeAt(0));
                        return item;
                    });
                }

                if (data.allowCredentials && php_vars.allow_authenticator_type && php_vars.allow_authenticator_type !== 'none') {
                    for (let credential of data.allowCredentials) {
                        if (php_vars.allow_authenticator_type === 'cross-platform') {
                            credential.transports = ['usb', 'nfc', 'ble'];
                        } else if (php_vars.allow_authenticator_type === 'platform') {
                            credential.transports = ['internal'];
                        }
                    }
                }

                // Save client ID
                const clientID = data.clientID;
                delete data.clientID;

                navigator.credentials.get({ 'publicKey': data }).then((credentialInfo) => {
                    wwa_dom('wp-webauthn-notice', (dom) => { dom.innerHTML = php_vars.i18n_5 }, 'class');
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
                    let response = wwa_ajax();
                    response.post(`${php_vars.ajax_url}?action=wwa_auth`, `data=${encodeURIComponent(window.btoa(AuthenticatorResponse))}&type=auth&clientid=${clientID}&user=${encodeURIComponent(document.getElementById('user_login').value)}&remember=${php_vars.remember_me === 'false' ? 'false' : (document.getElementById('rememberme') ? (document.getElementById('rememberme').checked ? 'true' : 'false') : 'false')}`, (data, status) => {
                        if (status) {
                            if (data === 'true') {
                                wwa_dom('wp-webauthn-notice', (dom) => { dom.innerHTML = php_vars.i18n_6 }, 'class');
                                if (document.querySelectorAll('p.submit input[name="redirect_to"]').length > 0) {
                                    setTimeout(() => {
                                        window.location.href = document.querySelectorAll('p.submit input[name="redirect_to"]')[0].value;
                                    }, 200);
                                } else {
                                    if (getQueryString('redirect_to')) {
                                        setTimeout(() => {
                                            window.location.href = getQueryString('redirect_to');
                                        }, 200);
                                    } else {
                                        setTimeout(() => {
                                            window.location.href = php_vars.admin_url
                                        }, 200);
                                    }
                                }
                            } else {
                                if (php_vars.usernameless === 'true' && document.getElementById('user_login').value === '') {
                                    wwa_dom('wp-webauthn-notice', (dom) => { dom.innerHTML = php_vars.i18n_7 + php_vars.i18n_12 }, 'class');
                                } else {
                                    wwa_dom('wp-webauthn-notice', (dom) => { dom.innerHTML = php_vars.i18n_7 }, 'class');
                                }
                                wwa_dom('user_login', (dom) => { dom.readOnly = false }, 'id');
                                wwa_dom('#wp-webauthn-check, #wp-webauthn', (dom) => { dom.disabled = false });
                            }
                        } else {
                            if (php_vars.usernameless === 'true' && document.getElementById('user_login').value === '') {
                                wwa_dom('wp-webauthn-notice', (dom) => { dom.innerHTML = php_vars.i18n_7 + php_vars.i18n_12 }, 'class');
                            } else {
                                wwa_dom('wp-webauthn-notice', (dom) => { dom.innerHTML = php_vars.i18n_7 }, 'class');
                            }
                            wwa_dom('user_login', (dom) => { dom.readOnly = false }, 'id');
                            wwa_dom('#wp-webauthn-check, #wp-webauthn', (dom) => { dom.disabled = false });
                        }
                    })
                }).catch((error) => {
                    console.warn(error);
                    if (php_vars.usernameless === 'true' && document.getElementById('user_login').value === '') {
                        wwa_dom('wp-webauthn-notice', (dom) => { dom.innerHTML = php_vars.i18n_7 + php_vars.i18n_12 }, 'class');
                    } else {
                        wwa_dom('wp-webauthn-notice', (dom) => { dom.innerHTML = php_vars.i18n_7 }, 'class');
                    }
                    wwa_dom('user_login', (dom) => { dom.readOnly = false }, 'id');
                    wwa_dom('#wp-webauthn-check, #wp-webauthn', (dom) => { dom.disabled = false });
                })
            } else {
                if (php_vars.usernameless === 'true' && document.getElementById('user_login').value === '') {
                    wwa_dom('wp-webauthn-notice', (dom) => { dom.innerHTML = php_vars.i18n_7 + php_vars.i18n_12 }, 'class');
                } else {
                    wwa_dom('wp-webauthn-notice', (dom) => { dom.innerHTML = php_vars.i18n_7 }, 'class');
                }
                wwa_dom('user_login', (dom) => { dom.readOnly = false }, 'id');
                wwa_dom('#wp-webauthn-check, #wp-webauthn', (dom) => { dom.disabled = false });
            }
        })
    }
}