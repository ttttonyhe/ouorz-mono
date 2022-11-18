=== WP-WebAuthn ===
Contributors: axton
Donate link: https://flyhigher.top/about
Tags: u2f, fido, fido2, webauthn, login, security, password, authentication
Requires at least: 5.0
Tested up to: 6.0
Stable tag: 1.2.8
Requires PHP: 7.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

WP-WebAuthn enables passwordless login through FIDO2 and U2F devices like FaceID or Windows Hello for your site.

== Description ==

WebAuthn is a new way for you to authenticate in web. It helps you replace your passwords with devices like USB Keys, fingerprint scanners, Windows Hello compatible cameras, FaceID/TouchID and more. Using WebAuthn, you can login to your a website with a glance or touch.

When using WebAuthn, you just need to click once and perform a simple verification on the authenticator, then you are logged in. **No password needed.**

WP-WebAuthn is a plug-in for WordPress to enable WebAuthn on your site. Just download and install it, and you are in the future of web authentication.

WP-WebAuthn also supports usernameless authentication.

This plugin has 4 built-in shortcodes and 4 built-in Gutenberg blocks, so you can add components like register form to frontend pages.

Please refer to the [documentation](http://doc.flyhigher.top/wp-webauthn) before using the plugin.

**PHP extensions gmp and mbstring are required.**

**WebAuthn requires HTTPS connection or `localhost` to function normally.**

You can contribute to this plugin on [GitHub](https://github.com/yrccondor/wp-webauthn).

Please note that this plugin does NOT support Internet Explorer (including IE 11). To use FaceID or TouchID, you need to use iOS/iPadOS 14+.

= Security and Privacy =

WebAuthn has become a W3C Recommendation since March 2019, which enabling the creation and use of strong, attested, scoped, public key-based credentials by web applications, for the purpose of strongly authenticating users using hardware authenticators. WebAuthn focuses on both security and privacy, it offers the possibility to create a secure authentication process without having to transfer any private data such as recognition data and fingerprint data. It will be the future of web authentication.

= GDPR Friendly =

When authenticating with WebAuthn, no private data will leave user's device and no third-party involvement. The credentials transferred are not associate to any user's information but only for authentication. It's GDPR Friendly.

== Installation ==

Notice: PHP extensions gmp and mbstring are required.

1. Upload the plugin files to the `/wp-content/plugins/wp-webauthn` directory, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->WP-WebAuthn screen to configure the plugin
4. Make sure that all settings are set, and you can start to register authenticators in your profile page

== Frequently Asked Questions ==

= What languages does this plugin support? =

This plugin supports English, Chinese (Simplified), Traditional Chinese (Hong Kong), Traditional Chinese (Taiwan), Turkish, French & German currently. If you are using WordPress in none of those languages, English will be displayed as default language.

All translation files are hosted on [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/wp-webauthn/) and [GitHub](https://github.com/yrccondor/wp-webauthn/tree/master/languages). You can help us to translate WP-WebAuthn into other languages!

= What should I do if the plugin could not work? =

Make sure your are using HTTPS or host your site in `localhost`. Then check whether you have installed the gmp extension for PHP.

If you can't solve the problem, [open an issue](https://github.com/yrccondor/wp-webauthn/issues/new) on [GitHub](https://github.com/yrccondor/wp-webauthn) with plugin log.

= Which browsers support WebAuthn? =

The latest version of Chrome, FireFox, Edge and Safari are support WebAuthn. You can learn more on [Can I Use](https://caniuse.com/#feat=webauthn).

To use FaceID or TouchID, you need to use iOS/iPadOS 14+.

== Screenshots ==

1. Verifying
2. Verifying without username on iPad
3. The login page
4. The settings page
5. Profile

== Changelog ==

= 1.2.8 =
Fix: privilege check for admin panel

= 1.2.7 =
Add: Now a security warning will be shown if user verification is disabled
Fix: Style broken with some locales
Fix: privilege check for admin panel (thanks to @vanpop)
Update: Third party libraries

= 1.2.6 =
Update: Third party libraries

= 1.2.5 =
Update: German translation (thanks to niiconn)
Fix: HTTPS check

= 1.2.4 =
Add: French translation (thanks to Spomky) and Turkish translate (thanks to Sn0bzy)
Fix: HTTPS check
Update: Existing translations
Update: Third party libraries

= 1.2.3 =
Feat: Avoid locking users out if WebAuthn is not available
Update: translations
Update: Third party libraries

= 1.2.2 =
Fix: Cannot access to js files in apache 2.4+

= 1.2.1 =
Feat: Allow to disable password login completely
Feat: Now we use WordPress transients instead of PHP sessions
Feat: Move register related settings to user's profile
Feat: Gutenberg block support
Feat: Traditional Chinese (Hong Kong) & Traditional Chinese (Taiwan) translation
Update: Chinese translation
Update: Third-party libraries

= 1.1.0 =
Add: Allow to remember login option
Add: Only allow a specific type of authenticator option
Fix: Toggle button may not working in login form
Update: Chinese translation
Update: Third-party libraries

== Upgrade Notice ==

= 1.2.5 =
Improvred HTTPS checking and updated German translation (by niiconn)

= 1.2.4 =
Improvred HTTPS checking and added new translations

= 1.2.3 =
Avoid locking users out if WebAuthn is not available and update translations

= 1.2.2 =
Fixed a problem that js files were broken in apache 2.4+

= 1.2.1 =
New features, bug fixing and new translations

= 1.1.0 =
2 new features & bug fixing
