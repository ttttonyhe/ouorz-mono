=== WPS Hide Login ===

Contributors: WPServeur, NicolasKulka, wpformation
Donate link : https://www.paypal.me/donateWPServeur
Tags: rename, login, wp-login, wp-login.php, custom login url, jetpack, wpserveur
Requires at least: 4.1
Tested up to: 6.0
Requires PHP: 7.0
Stable tag: 1.9.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Change wp-login.php to anything you want.

== Description ==

= English =

*WPS Hide Login* is a very light plugin that lets you easily and safely change the url of the login form page to anything you want. It doesn’t literally rename or change files in core, nor does it add rewrite rules. It simply intercepts page requests and works on any WordPress website. The wp-admin directory and wp-login.php page become inaccessible, so you should bookmark or remember the url. Deactivating this plugin brings your site back exactly to the state it was before.

This plugin is kindly proposed by <a href="https://www.wpserveur.net/?refwps=14&campaign=wpshidelogin" target="_blank">WPServeur</a> the specialized WordPress web host.

Discover also our other free extensions:
- <a href="https://wordpress.org/plugins/wps-limit-login/" target="_blank">WPS Limit Login</a> to block brute force attacks.
- <a href="https://wordpress.org/plugins/wps-bidouille/" target="_blank">WPS Bidouille</a> to optimize your WordPress and get more info.
- <a href="https://wordpress.org/plugins/wps-cleaner/" target="_blank">WPS Cleaner</a> to clean your WordPress site.

This plugin is only maintained, which means we do not guarantee free support. Consider reporting a problem and be patient.

= Français =

*WPS Hide Login* est un plugin très léger qui vous permet de changer facilement et en toute sécurité l'url de la page de formulaire de connexion. Il ne renomme pas littéralement ou ne modifie pas les fichiers dans le noyau, ni n'ajoute des règles de réécriture. Il intercepte simplement les demandes de pages et fonctionne sur n'importe quel site WordPress. Le répertoire wp-admin et la page wp-login.php deviennent inaccessibles, vous devez donc ajouter un signet ou vous souvenir de l'URL. Désactiver ce plugin ramène votre site exactement à l'état dans lequel il était auparavant.

Ce plugin vous est gentiment proposé par <a href="https://www.wpserveur.net/?refwps=14&campaign=wpshidelogin" target="_blank">WPServeur</a> l'hébergeur spécialisé WordPress.

Plus d'infos sur son utilisation : <a href="https://wpformation.com/wps-hide-login-url-connexion-wordpress/" target="_blank">https://wpformation.com/wps-hide-login-url-connexion-wordpress/</a>

Découvrez également nos autres extensions gratuites :
- <a href="https://fr.wordpress.org/plugins/wps-limit-login/" target="_blank">WPS Limit Login</a> pour bloquer les attaques par force brute.
- <a href="https://fr.wordpress.org/plugins/wps-bidouille/" target="_blank">WPS Bidouille</a> pour optimiser votre WordPress et faire le plein d'infos.
- <a href="https://fr.wordpress.org/plugins/wps-cleaner/">WPS Cleaner</a> pour nettoyer votre site WordPress.

Ce plugin est seulement maintenu, ce qui signifie que nous ne garantissons pas un support gratuit. Envisagez de signaler un problème et soyez patient.

= Compatibility =

= English =

Requires WordPress 4.1 or higher. All login related things such as the registration form, lost password form, login widget and expired sessions just keep working.

It’s also compatible with any plugin that hooks in the login form, including:

* BuddyPress,
* bbPress,
* Jetpack,
* WPS Limit Login,
* and User Switching.

Obviously it doesn’t work with plugins or themes that *hardcoded* wp-login.php.

Works with multisite, with subdomains and subfolders. Activating it for a network allows you to set a networkwide default. Individual sites can still rename their login page to something else.

If you’re using a **page caching plugin** other than WP Rocket, you should add the slug of the new login url to the list of pages not to cache. WP Rocket is already fully compatible with the plugin.

For W3 Total Cache and WP Super Cache this plugin will give you a message with a link to the field you should update.

= Français =

Nécessite WordPress 4.1 ou supérieur. Toutes les choses liées à la connexion telles que le formulaire d'inscription, le formulaire de mot de passe perdu, le widget de connexion et les sessions expirées continuent de fonctionner.

Il est également compatible avec tout plugin qui se connecte au formulaire de connexion, notamment:

* BuddyPress,
* bbPress,
* Jetpack,
* WPS Limit Login,
* and User Switching.

Évidemment, cela ne fonctionne pas avec les plugins ou les thèmes *hardcoded* wp-login.php.

Fonctionne en multisite, avec sous-domaines ou sous dossiers. L'activer pour un réseau vous permet de définir une valeur par défaut pour l'ensemble du réseau. Les sites individuels peuvent toujours renommer leur page de connexion pour autre chose.

Si vous utilisez un **plugin de mise en cache de pages** autre que WP Rocket, vous devez ajouter le slug de la nouvelle URL de connexion à la liste des pages à ne pas mettre en cache. WP Rocket est déjà entièrement compatible avec le plugin.

Pour W3 Total Cache et WP Super Cache, ce plugin vous donnera un message avec un lien vers le champ que vous devriez mettre à jour.

= GitHub =

https://github.com/tabrisrp/wps-hide-login

== Installation ==

= English =

1. Go to Plugins › Add New.
2. Search for *WPS Hide Login*.
3. Look for this plugin, download and activate it.
4. The page will redirect you to the settings. Change your login url there.
5. You can change this option any time you want, just go back to Settings › WPS Hide Login.

= Français =

1. Aller dans Extensions › Ajouter.
2. Rechercher *WPS Hide Login*.
3. Recherchez ce plugin, téléchargez-le et activez-le.
4. La page vous redirigera vers les paramètres. Changez votre URL de connexion.
5. Vous pouvez changer cette option quand vous le souhaitez, il vous suffit de retourner dans Paramètres > WPS Hide Login.

== Screenshots ==
1. Setting on single site installation
2. Setting for network wide

== Frequently Asked Questions ==

= I forgot my login url!  =

Either go to your MySQL database and look for the value of `whl_page` in the options table, or remove the `wps-hide-login` folder from your `plugins` folder, log in through wp-login.php and reinstall the plugin.

On a multisite install the `whl_page` option will be in the sitemeta table, if there is no such option in the options table.

= Registration and lost password URL =

You have to give the url. example: /login?action=register or /login?action=lostpassword
But there is no redirection via the plugin, the default URL of WordPress (/wp-login.php?action=register or /wp-login.php?action=lostpassword) otherwise everyone could know the url of administration of your site.

= I'm locked out! =

This case can come from plugins modifying your .htaccess files to add or change rules, or from an old WordPress MU configuration not updated since Multisite was added.

First step is to check your .htaccess file and compare it to a regular one, to see if the problem comes from it.

= J'ai oublié mon identifiant de connexion ! =

Allez dans votre base de données MySQL et recherchez la valeur de 'whl_page' dans la table des options, ou supprimez le dossier 'wps-hide-login' de votre dossier 'plugins', connectez-vous via wp-login.php et réinstallez le plugin .

Sur une installation multisite, l'option 'whl_page' sera dans la table de sitemeta, si l'option n'existe pas dans la table des options.

= URL d'inscription et de mot de passe oublié =

Il vous faut donner l'url. exemple : /login?action=register ou /login?action=lostpassword
Mais il n'y pas de redirection via le plugin, de l'url par défaut de WordPress (/wp-login.php?action=register ou /wp-login.php?action=lostpassword) sinon tout le monde pourrait connaître l'url d'administration de votre site.

= Je suis bloqué ! =

Ce cas peut provenir de plugins modifiant vos fichiers .htaccess pour ajouter ou modifier des règles, ou d'une ancienne configuration de WordPress MU non mise à jour depuis l'ajout de Multisite.

La première étape consiste à vérifier votre fichier .htaccess et à le comparer à un fichier .htaccess normal, pour voir si le problème provient de ce fichier.

== Changelog ==

= 1.9.6 =
* Tested up to 6.0

= 1.9.4 =
* Tested up to 5.9

= 1.9.3 =
* Fix : PHP Warning:  Undefined array key "path"

= 1.9.2 =
* Add action before redirect
* Fix redirect with wp-cli (Thanks @netson)

= 1.9.1 =
* Fix : by-pass security issue allowing an unauthenticated user to get login page by setting a random referer string via curl request.

= 1.9 =
* Fix : redirect ajax add_to_cart

= 1.8.8 =
* Fix : redirect_url (Thanks Don)

= 1.8.7 =
* Fix : remove redirect in doing cron

= 1.8.6 =
* Tested up to 5.8

= 1.8.5 =
* Fix : Force refresh permalinks update option 'whl_page'

= 1.8.4 =
* Tested up to 5.7

= 1.8.3 =
* Fix : remove WP_Review

= 1.8.2 =
* Fix notice "Notice: Trying to get property 'href' of non-object"

= 1.8.1 =
* Fix fatal error with vendor wp-dismissible-notices-handler and wp-review-me

= 1.8 =
* Fix multisite subdomain for website menu (Thanks Eric Celeste)

= 1.7 =
* Fix vulnerability (Thanks Sebastian Schmitt) : Posting "post_password" with arbitrary content to /wp-login.php reveals the normal wordpress login page.

= 1.6.1 =
* Fix : loopback request site-health

= 1.6 =
* Tested up to 5.6
* Add compatibility with PHP8

= 1.5.7 =
* Fix : Text Domain Issue

= 1.5.6 =
* Fix : flush rewrite rules after install or update option
* Tested up to 5.4

= 1.5.5 =
* Add filter to redirect in cases where the user is already logged in.
* Fix : add rawurldecode for all $_SERVER['REQUEST_URI'] (Thanks @nintechnet)

= 1.5.4.2 =
* Revert to code in tag 1.5.3

= 1.5.4.1 =
* Fix : home_url / site_url

= 1.5.4 =
* Fix : Compatibility with WPML (Thanks @susansiow)

= 1.5.3 =
* Fix : Security vulnerabilities (Thanks @juliobox)

= 1.5.2.2 =
* Tested up to 5.2
* Fix : Domain language

= 1.5.2.1 =
* Fix : Notice: Undefined index: query

= 1.5.2 =
* Fix : Action URL wp_send_user_request()

= 1.5.1 =
* Fix : Action URL get_the_password_form()

= 1.5 =
* Enhancement: Add custom redirection URL

= 1.4.5 =
* Fix : function wp_login_url on page 404 now returns an empty link

= 1.4.4 =
* Fix : Too many redirects when a user clicks “Log in with WordPress.com”

= 1.4.3 =
* Fix : Fatal Error with multisite WP

= 1.4.2 =
* Fix : Error with library for compat WordPress and PHP

= 1.4.1 =
* Fix : Remove message review if PHP is too old

= 1.4 =
* Enhancement code with composer, namespace and autoload

= 1.3.4.2 =
* Fix : Remove message review if PHP is too old

= 1.3.4.1 =
* Fix : Deprecated method

= 1.3.4 =
* Add : Review message
* Fix : Redirect url wp-admin/options.php

= 1.3.3 =
* Add : Filter hook for enable wp-signup (@sumobi)

= 1.3.2 =
* Fix : Encoding of the login with a space in the emails

= 1.3.1 =
* Fix : redirect change admin email

= 1.3 =
* Fix : redirect wp-register.php

= 1.2.7 =
* Enhancement for Woocommerce email notification

= 1.2.6.1 =
* Revert redirect after login

= 1.2.6 =
* Fix : redirect after login

= 1.2.5.1 =
* Fix : add action in hook activate

= 1.2.5 =
* Remove : redirect activate

= 1.2.4 =
* Remove: Third party wpserveur

= 1.2.3.1 =
* Enhancement: Add translations cs_CZ, da_DK, es_ES, it_IT, ru_RU
* Fix: Parse error classes/plugin.php l.530

= 1.2.3 =
* Fix: change 403 to 404 error on wp-admin
* Fix: activate plugin
* Enhancement: Third party wpserveur

= 1.2.2 =
* Enhancement: Compatibility 4.9.x

= 1.2.1 =
* Enhancement: Prevent access to the login page by using the URL encoded version of wp-login.php

= 1.2 =
* Enhancement: Prevent redirection to login URL when accessing /wp-admin/customize.php directly
* Enhancement: Redirect to admin URL when already logged-in and accessing login URL without the action query string

= 1.1.7 =
* Fix: change fake 404 on wp-admin when not logged-in to a 403 forbidden to prevent fatal errors with various themes & plugins

= 1.1.6 =
* Fix: bug with Yoast SEO causing a Fatal Error and blank screen when loading /wp-admin/ without being logged-in

= 1.1.5 =
* Fix: Stop displaying the new login url notice everywhere when settings are updated (thanks @ kmelia on GitHub)
* Improvement: better way of retrieving the 404 template

= 1.1.4 =
* Fix: bypass the plugin when $pagenow is admin-post.php

= 1.1.3 =
* Fix: issue if no 404 template in active theme directory

= 1.1.2 =
* Modified priority on hooks to fix a problem with some configurations

= 1.1.1 =
* Check for Rename wp-login.php activation before activating WPS Hide Login to prevent conflict

= 1.1 =
* Fix : CSRF security issue when saving option value in single site and multisite mode. Thanks to @Secupress
* Improvement : changed option location from permalinks to general, because register_setting doesn't work on permalinks page.
* Improvement : notice after saving is now dismissible (compatibility with WP 4.2)
* Uninstall function is now in it's separate file uninstall.php
* Some cleaning and reordering of code

= 1.0 =

* Initial version. This is a fork of the Rename wp-login.php plugin, which is unmaintained https://wordpress.org/plugins/rename-wp-login/. All previous changelogs can be found there.
