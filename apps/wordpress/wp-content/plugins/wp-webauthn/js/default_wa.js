'use strict';

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelectorAll('#lostpasswordform, #registerform, .admin-email-confirm-form').length > 0) {
        return;
    }
    window.onload = () => {
        if (php_vars.webauthn_only === 'true') {
            if ((window.PublicKeyCredential === undefined || navigator.credentials.create === undefined || typeof navigator.credentials.create !== 'function')) {
                // Not support, show a message
                if (document.querySelectorAll('#login > h1').length > 0) {
                    let dom = document.createElement('p');
                    dom.className = 'message';
                    dom.innerHTML = php_vars.i18n_8;
                    document.querySelectorAll('#login > h1')[0].parentNode.insertBefore(dom, document.querySelectorAll('#login > h1')[0].nextElementSibling)
                }
            }
            wwa_dom('#loginform', (dom) => { dom.classList.add('wwa-webauthn-only') });
            if (document.getElementsByClassName('user-pass-wrap').length > 0) {
                wwa_dom('.user-pass-wrap, #wp-submit', (dom) => { dom.parentNode.removeChild(dom) });
                if (php_vars.remember_me === 'false' ) {
                    wwa_dom('.forgetmenot', (dom) => { dom.parentNode.removeChild(dom) });
                }
            } else {
                // WordPress 5.2-
                wwa_dom('#wp-submit', (dom) => { dom.parentNode.removeChild(dom) });
                if (php_vars.remember_me === 'false' ) {
                    wwa_dom('.forgetmenot', (dom) => { dom.parentNode.removeChild(dom) });
                }
                const targetDOM = document.getElementById('loginform').getElementsByTagName('p')[1];
                targetDOM.parentNode.removeChild(targetDOM);
            }
        }
        if (!(window.PublicKeyCredential === undefined || navigator.credentials.create === undefined || typeof navigator.credentials.create !== 'function') || php_vars.webauthn_only === 'true') {
            // If supported, toggle
            if (php_vars.webauthn_only !== 'true') {
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
            }
            wwa_dom('wp-webauthn-notice', (dom) => { dom.style.display = 'flex' }, 'class');
            wwa_dom('wp-webauthn-check', (dom) => { dom.style.cssText = `${dom.style.cssText}display: block !important` }, 'id');
            wwa_dom('user_login', (dom) => { dom.focus() }, 'id');
            wwa_dom('wp-submit', (dom) => { dom.disabled = true }, 'id');
        }
        if (document.querySelectorAll('#lostpasswordform, #registerform').length > 0) {
            return;
        }
        wwa_dom('user_pass', (dom) => { dom.disabled = false }, 'id');
        let dom = document.querySelectorAll('#loginform label');
        if (dom.length > 0) {
            if (dom[0].getElementsByTagName('input').length > 0) {
                // WordPress 5.2-
                dom[0].innerHTML = `<span id="wwa-username-label">${php_vars.i18n_9}</span>${dom[0].innerHTML.split('<br>')[1]}`;
            } else {
                dom[0].innerText = php_vars.i18n_9;
            }
        }
    }
})