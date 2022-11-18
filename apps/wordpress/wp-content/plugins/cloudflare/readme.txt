=== Cloudflare ===
Contributors: icyapril, manatarms, thillcf, deuill, epatryk, jacobbednarz
Tags: cloudflare, seo, ssl, ddos, speed, security, cdn, performance, free
Requires at least: 3.4
Tested up to: 5.9
Stable tag: 4.11.0
Requires PHP: 7.2
License: BSD-3-Clause

All of Cloudflare’s performance and security benefits in a simple one-click install.

== Description ==

= What this plugin can do for you =

https://www.youtube.com/watch?v=DWANhxoDxFI?feature=youtu.be

**Automatic Platform Optimization (APO)**

Speed up your WordPress site by up to 300% with Cloudflare’s Automatic Platform Optimization (APO) plugin. APO allows Cloudflare to serve your entire WordPress site from our edge network of over 250+ data centers worldwide ensuring fast & reliable performance for your visitors no matter where they are.

Optimizing your WordPress site with multiple plugins can be overwhelming. Take your WordPress site’s performance to the next level by switching to a single plugin for CDN, intelligent caching, and other key WordPress optimizations with Cloudflare (APO). Visit our [announcement blog](https://blog.cloudflare.com/automatic-platform-optimizations-starting-with-wordpress/) to learn more about APO.

**What makes APO different from other caching plugins?**

The key differentiating factor between Cloudflare APO and other traditional page caching and CDN solutions is its ability to directly cache static HTML at Cloudflare’s edge. Every other plugin and CDN will cache your static assets (images, javascript, CSS), but none help you cache the actual content on your site (the HTML) using a massive edge network like Cloudflare’s.

APO intelligently caches your HTML pages and will automatically purge content from the cache that you update, so users will always see the latest content without compromising the performance of pages that haven't been recently updated

**What you get with Cloudflare APO**

APO is a $5 add-on with Cloudflare’s free plan and comes with an unlimited amount of subdomains. With APO you also get to leverage many of the other benefits of Cloudflare such as **Free DNS, Free Automated SSL Certificates, Free DDoS Mitigation, and more.** APO is free for all paid plan users so if you have Cloudflare Pro or Business already you can just turn it on. You can compare all our plans [here](https://www.cloudflare.com/plans/?utm_source=promo&utm_medium=social&utm_term=&utm_content=&utm_campaign=g421o-pl-apo-wordpress-plans).

**Protect your WordPress site with Cloudflare’s Web Application Firewall (WAF)**

Cloudflare’s WAF is available on all our [paid plans](https://www.cloudflare.com/plans/?utm_source=promo&utm_medium=social&utm_term=&utm_content=&utm_campaign=g421o-pl-apo-wordpress-plans) and comes with built-in rulesets, specifically tailored to mitigate WordPress threats and vulnerabilities. These security rules are regularly updated by our team of experts. At the flip of a switch, you’ll have your WAF up and running without any difficult adjustments to your site. With over 26 million internet properties under our protection, you can sleep easy knowing Cloudflare has your back.

= Additional features =

* Header rewrite to prevent a redirect loop when Cloudflare’s Universal SSL is enabled

* Change Cloudflare settings from within the plugin itself without needing to navigate to the cloudflare.com dashboard. You can change settings for cache purge, security level, Always Online, and image optimization

* View analytics such as total visitors, bandwidth saved, and threats blocked

* Support for [HTTP2/Server Push](https://blog.cloudflare.com/announcing-support-for-http-2-server-push-2/)

== Installation ==

= Prerequisite =

Make sure your PHP version is 7.2 or higher.

= Speed Up Your WordPress Site with Cloudflare APO =

https://www.youtube.com/watch?v=XJ0f5SawEFI&t=20s

= Getting Started =

**Setting up Cloudflare APO**

If you’re currently utilizing Cloudflare’s free plan you can add APO to your plan for just $5/month. If you are on Cloudflare’s Pro Plan Cloudflare APO is already included in your subscription. We recommend you start with our [APO developer documentation](https://developers.cloudflare.com/automatic-platform-optimization/get-started) which includes all the information you need to get APO up and running.

**How to check if APO is working**
Using [Uptrends](https://www.uptrends.com/tools/http-response-header-check) you can verify if Cloudflare APO is working by checking to see if APO response headers are present. You can follow along in this [video](https://youtu.be/XJ0f5SawEFI?t=318).

**APO Support Resource can be found [here](https://developers.cloudflare.com/automatic-platform-optimization/)**

**Get Started with Cloudflare SSL [here](https://support.cloudflare.com/hc/en-us/articles/360023792171-Getting-Started-with-Cloudflare-SSL)**

**View our Cloudflare APO blog post [here](https://blog.cloudflare.com/automatic-platform-optimizations-starting-with-wordpress/)**

== Frequently Asked Questions ==

= Do I need a Cloudflare account to use the plugin? =

Yes, on install and activation of the plugin, first time users will be asked to enter their email address (used to sign-up for an account at cloudflare.com) and either an API Token or their API key. This is needed to support all the features offered by the plugin.

= What settings are applied when I click "Apply Default Settings" in Cloudflare's WordPress plugin? =

 You can review the recommended settings that are applied [here](https://support.cloudflare.com/hc/en-us/articles/227342487).

= What do I do if Cloudflare does not detect the WordPress plugin for use with APO (Automatic Platform Optimization) =

 APO works best when the WordPress plugin is used. We do not recommend using APO without the plugin installed. If you face issues with Cloudflare detecting the plugin then follow these steps:
 1. Go to Cloudflare WordPress plugin
 2. Disable APO in the card
 3. Enable APO in the card (will set proper settings for APO feature)
 4. Clear any server cache used via other plugins (WP Rocket being an example)
 5. Verify that your origin starts serving response header “cf-edge-cache: cache,platform=wordpress”

 You can read more about APO with WordPress [here](https://support.cloudflare.com/hc/en-us/articles/360049822312)

= Does the plugin work if I have Varnish enabled? =

Yes, Cloudflare works with, and helps speed up your site even more, if you have Varnish enabled.

== Screenshots ==

== Changelog ==

= 4.11.0 - 2022-07-27 =

* Restrict access to sensitive files using `.htaccess` configuration.
* Added `cloudflare_use_cache` hook to determine when to cache.
* Allow arrays with `url` keys to be passed into cache purge requests.
* Handle `getZoneSettings` not returning a key and throwing an unset array key error.

= 4.10.1 - 2022-06-06 =

* Fix logic for ignoring cache purge operations.

= 4.10.0 - 2022-06-03 =

* Ignore feed URLs in cache purge operations unless a cache override is in place.

= 4.9.1 - 2022-05-04 =

* Handle empty URL arrays for purging.
* Swap `publicly_queryable` for `is_post_type_viewable` when determining if the post is public.
* Update `always_use_https` check to work with the API lacking a "value" for the key.
* `purgeCacheByRelevantURLs` now accepts either an array or single ID

= 4.8.3 - 2022-03-22 =

* When a zone has "Always Use HTTPS" enabled, only send HTTPS based URLs. HTTP URLs will never be hit and never present in the cache.

= 4.8.2 - 2022-03-18 =

* Retag 4.8.1 with correct version in user agent

= 4.8.1 - 2022-03-18 =

* Loosen domain check for cache purge calls to allow subdomains

= 4.8.0 - 2022-03-15 =

* Updated supported WordPress version to 5.9
* Replace Guzzle with wp_remote_request for remote calls
* Update cache purge logic to improve efficiency of what we send to the remote service

= 4.7.0 - 2021-10-28 =

* Merge cloudflare-plugin-backend into Cloudflare-WordPress repository

= 4.6.0 - 2021-10-11 =

* Make frontend use native await/async
* Purge cache on mobile if APO Cache By Device Type

= 4.5.1 - 2021-06-03 =

* Rewrite PHP 8 bootstrap files for `symfony/polyfill` to be PHP 7 compatible

= 4.5.0 - 2021-06-02 =

* Document unintuitive `transition_post_status` WP hook behavior
* Only purge public taxonomies while clearing any empty values from the list
* Better handling of cases where `wp_get_attachment_image_src` is false and not a usable array
* Support activation of IDN domains
* Improve development experience by shipping a Docker Compose file with more tooling and documentation

= 4.4.0 - 2021-03-23 =

* Purge posts when transitioning to or from the 'published' state
* Remove conditional logic for subdomain, allow to activate APO feature on the subdomain
* Further work to autocorrect APO settings

= 4.3.0 - 2021-03-19 =

* Sanitise sensitive HTTP header logs
* Stop sending `cfCRSFToken` to remote API
* Add warnings for incorrectly configured Automatic Platform Optimization
* Purge posts that go from public to private
* Purge pagination for first 3 pages

= 4.2.2 - 2021-03-08 =

* Fix warning for file_get_contents of composer.json

= 4.2.1 - 2021-03-05 =

* Deprecate REST dashboard analytics

= 4.2.0 - 2021-03-02 =

* Allow configuration of Cloudflare credentials via environment variables
* Prevent purging of cache before comments have been moderated
* Remove unnecessary symfony/yaml dependency

= 4.1.0 - 2021-02-11 =

* Removed development dependencies from vendor directory
* Update CLOUDFLARE_MIN_PHP_VERSION to match the plugin requirements

= 4.0.0 - 2021-02-09 =

* Drop support for PHP 5.6, 7.0 and 7.1

= 3.8.10 - 2021-01-29 =

* Add Jacob to maintainers list

= 3.8.9 - 2021-01-14 =

* Revert Add pagination purging

= 3.8.8 - 2021-01-13 =

* Add pagination purging

= 3.8.7 - 2020-12-07 =

* Purge taxonomy feed URLs
* Fix changing APO settings (cf, wordpress, plugin) when running on subdomain
* Fix setting hostname override

= 3.8.6 - 2020-11-19 =

* Add subdomain support for APO card

= 3.8.5 - 2020-10-15 =

* Added automatic purge cache on new comment

= 3.8.4 - 2020-10-13 =

* Added composer's type=wordpress-plugin for Bedrock users
* Fixed typo in readme.txt
* Fix modify header exception thrown during wp-cron

1. Cloudflare Plugin

== Changelog ==

= 3.8.3 - 2020-10-05 =

* Fixed setting all APO values properly for correct dashboard rendering

= 3.8.2 - 2020-10-02 =

* re-relase broken version, no changes

= 3.8.1 - 2020-10-02 =

* Fixed typo in config.js(on) that resulted in warning [#292](https://github.com/cloudflare/Cloudflare-WordPress/pull/292)
* Check for array indicies are set before using [#278](https://github.com/cloudflare/Cloudflare-WordPress/pull/278)

= 3.8.0 - 2020-10-01 =

* Added APO support
* Renamed Automatic Cache Management card to Auto Purge Content On Update

= 3.7.0 - 2020-09-25 =

* Bump cloudflare-plugin-backend v2.3.0 and cloudflare-plugin-frontend v3.1.0 [#283](https://github.com/cloudflare/Cloudflare-WordPress/pull/283)

= 3.6.0 - 2020-09-17 =

* Bump cloudflare-plugin-backend [#276](https://github.com/cloudflare/Cloudflare-WordPress/pull/276)

= 3.5.1 - 2020-07-02 =

*Fixed*

* Fixed Cache Purges failing [#266](https://github.com/cloudflare/Cloudflare-WordPress/issues/266)

= 3.5.0 - 2020-06-26 =

*Fixed*

* Disable HTTP/2 Push on wp-admin pages [#214](https://github.com/cloudflare/Cloudflare-WordPress/pull/214)
* Fix PHP 7.4 notice [#256](https://github.com/cloudflare/Cloudflare-WordPress/pull/256)

*Added*

* Purge attachment URLs [#208](https://github.com/cloudflare/Cloudflare-WordPress/pull/208)
* Purge URLs on page/post update [#206](https://github.com/cloudflare/Cloudflare-WordPress/pull/206)
* Turn on IPv6 by default [#229](https://github.com/cloudflare/Cloudflare-WordPress/pull/229)
* Add constants for better control HTTP/2 Server Push [#213](https://github.com/cloudflare/Cloudflare-WordPress/pull/213)
* Allow custom actions for purge url and purge everything actions [#212](https://github.com/cloudflare/Cloudflare-WordPress/pull/212)

= 3.4.1 - 2019-08-29 =

*Fixed*

* Updated list of contributors.
* Updated tested WordPress version to latest (5.2.2).

= 3.4.0 - 2019-08-29 =

*Added*

* Added support for API Token authentication.

= 3.3.2 - 2017-12-12 =

*Fixed*

* Bug in cf-ip-rewrite

*Added*

* Added a new filter cloudflare_purge_by_url allowing users to have better control on automatically purged urls.

= 3.3.1 - 2017-6-29 =

*Fixed*

* Potential bug by using $_GET.

= 3.3.0 - 2017-6-29 =

*Added*

* Added a new Splash Screen
* Added userConfig.js file allowing custom configurations.
* Added logs in debug mode for Automatic Cache Purge.
* Added logs for oversized Server Push HTTP headers.

*Changed*

* Automatic Cache Purge now purges Autoptimize by everything rather than by URL.
* Updated IP Ranges

*Fixed*

* Bug where domains which had capital letters not working.
* Bug where Automatic Cache Purge couldn't purge front page.
* Bug related to work with IWP.
* Bug where if PHP is compiled with ipv6-disable flag, it crashed the site.

= 3.2.1 - 2017-3-14 =

*Fixed*

* Bug where accounts which had more than 20 zones would not show up correctly.

= 3.2.0 - 2017-3-1 =

*Added*

* Bypass Cache By Cookie functionality.
* HTTP/2 Server Push functionality (disabled by default).

*Changed*

* Lowered the plugin size.
* Automatic Cache Management feature includes purging taxonomies.
* Automatic Cache Management feature supports sites which use both HTTP and HTTPS.

*Fixed*

* Admin bar disappearing from the plugin.
* Bug where spinner was loading forever.
* Bug where the backend errors where not being shown in the frontend.
* Issues where IE11 was not working properly.

= 3.1.1 - 2016-11-17 =

*Changed*

* Moved Admin Bar behind Automatic Cache Purge toggle.

= 3.1.0 - 2016-11-17 =

*Added*

* Added ability to automatically purge cache when a post is published, edited or deleted. (Thanks to brandomeniconi and mike503)
* Added ability to work with WordPress MU Domain Mapping plugin. (Thanks to brandomeniconi)

*Changed*

* Changed the UI to look more like cloudflare.com dashboard.
* Changed plugin description.
* Disabled showing WordPress Admin Bar and Edit Post Link to avoid caching problems for users using HTML Caching.

*Fixed*

* Fixed bug where require vendor folders was not working.
* Fixed bug where static files were cached which caused issues updating the plugin.
* Fixed dependencies which caused issues with PHP Compatibility Checker plugin.

= 3.0.6 - 2016-10-6 =

*Added*

* Added ability to toggle Development Mode.

*Fixed*

* Fixed bug where active zone dropdown was not working properly.

*Changed*

* Compressed resources to lower plugin size.
* Updated Cloudflare logo.

= 3.0.5 - 2016-09-28 =

*Fixed*

* Fixed bug where refactored Flexible SSL fix was causing the settings page hook not to load.

= 3.0.4 - 2016-09-27 =

*Added*

* Ability for users to toggle Automatic HTTPS Rewrites (enabled by default, solves for most mixed content errors).

*Fixed*

* Fixed an issue where low PHP version where getting syntax error.
* Fixed issue where some users using Flexible SSL where not able to login to wp-admin .
* Fixed a bug where the active zone selector was not paginating through the whole zone list.
* Fixed an issue where the setting for Image Optimization was being displayed incorrectly.
* Fixed a bug in Analytics where the  Uniques Visitors data was not displaying accurately.

*Changed*

* Compressed assets to lower plugin size.
* Hooks loading logic refactored to make it more simple and readable.

= 3.0.3 - 2016-09-21 =

*Fixed*

* Fixed an issue where some domains were being incorrectly propagated to the domain selector dropdown
* Fixed an issue where the Web Application Firewall was accidentally triggering RFI Attack Rules
* Fixed an issue where image optimization was not being enabled for Pro and higher Cloudflare plans

= 3.0.2 - 2016-09-16 =

*Fixed*

* Disabled HTTP/2 Server Push which was leading to 520 and 502 errors for some websites.

= 3.0.1 - 2016-09-16 =

*Fixed*

* Fixed HTTP/2 Server Push exceeding the header limit Cloudflare has which caused 520 errors.
* Fixed warning message in HTTP/2 Server Push.

= 3.0.0 - 2016-09-15 =

*Added*

* Added one-click application oft WordPress specific recommended settings
* Added ability to purge the Cloudflare cache
* Integrated with WordPress cache management to automatically clear the Cloudflare cache on updating site appearance
* Added ability to change Cloudflare settings (Always Online mode, I’m Under Attack, Image Optimization, Security Level, Web Application Firewall)
* Added Analytics showing Cached Requests, bandwidth used, unique visitors, threats blocked
* Added Header rewrite to prevent a redirect loop when Cloudflare’s Universal SSL is enabled
* Added HTTP/2 Server Push support
* Added Support for PHP 5.3+

*Removed*

* Removed HTTPS Protocol Rewriting
* Removed submission of spam comments
* Removed ability to toggle Development Mode On/Off

*Changed*

* Updated user interface
* Started to support WordPress 3.4+ instead of 2.8+ because we depend on the  [WordPress Options API](https://codex.wordpress.org/Options_API)
