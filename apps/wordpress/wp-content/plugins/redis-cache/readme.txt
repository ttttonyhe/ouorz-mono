=== Redis Object Cache ===
Contributors: tillkruess
Donate link: https://github.com/sponsors/tillkruss
Tags: redis, object cache, cache, object caching, caching performance, relay, predis, phpredis
Requires at least: 3.3
Tested up to: 6.0
Requires PHP: 7.2
Stable tag: 2.2.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A persistent object cache backend powered by Redis. Supports Predis, PhpRedis, Relay, replication, sentinels, clustering and WP-CLI.


== Description ==

A persistent object cache backend powered by Redis. Supports [Predis](https://github.com/predis/predis/), [PhpRedis (PECL)](https://github.com/phpredis/phpredis), [Relay](https://relaycache.com), replication, sentinels, clustering and [WP-CLI](http://wp-cli.org/).

To adjust the connection parameters, prefix cache keys or configure replication/clustering, please see [our wiki](https://github.com/rhubarbgroup/redis-cache/wiki).

= Object Cache Pro =

A **business class** Redis object cache backend. Truly reliable, highly optimized, fully customizable and with a dedicated engineer when you most need it.

* Rewritten for raw performance
* 100% WordPress API compliant
* Faster serialization and compression
* Easy debugging & logging
* Cache prefetching and analytics
* Fully unit tested (100% code coverage)
* Secure connections with TLS
* Health checks via WordPress & WP CLI
* Optimized for WooCommerce, Jetpack & Yoast SEO

Learn more about [Object Cache Pro](https://objectcache.pro/?ref=oss&amp;utm_source=wp-plugin&amp;utm_medium=readme).


== Installation ==

For detailed installation instructions, please read the [standard installation procedure for WordPress plugins](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

1. Make sure [Redis is installed and running](http://redis.io/topics/quickstart).
2. Install and activate plugin.
3. Enable the object cache under _Settings -> Redis_, or in Multisite setups under _Network Admin -> Settings -> Redis_.
4. If necessary, adjust [connection parameters](http://wordpress.org/extend/plugins/redis-cache/other_notes/).

If your server doesn't support the [WordPress Filesystem API](https://codex.wordpress.org/Filesystem_API), you have to manually copy the `object-cache.php` file from the `/plugins/redis-cache/includes/` directory to the `/wp-content/` directory.


== Connection Parameters ==

By default the object cache drop-in will connect to Redis over TCP at `127.0.0.1:6379` and select database `0`.

To adjust the connection parameters, client, timeouts and intervals, please see the [connection parameters wiki page](https://github.com/rhubarbgroup/redis-cache/wiki/Connection-Parameters).


== Configuration Options ==

The plugin comes with quite a few configuration options, such as key prefixes, a maximum time-to-live for keys, ignored group and many more.

Please see the [configuration options wiki page](https://github.com/rhubarbgroup/redis-cache/wiki/Configuration-Options) for a full list.


== Replication & Clustering ==

To use Replication, Sharding or Clustering, make sure your server is running PHP7 or higher and you consulted the [Predis](https://github.com/predis/predis) or [PhpRedis](https://github.com/phpredis/phpredis) documentation.

Please see the [replication & clustering wiki page](https://github.com/rhubarbgroup/redis-cache/wiki/Replication-&-Clustering) for more information.


== WP-CLI Commands ==

To see a list of all available WP-CLI commands, please see the [WP CLI commands wiki page](https://github.com/rhubarbgroup/redis-cache/wiki/WP-CLI-Commands).


== Screenshots ==

1. Plugin settings, connected to a single Redis server.
2. Plugin settings, displaying recent response time metrics.
3. Plugin settings, showing diagnostic information.
4. Dashboard widget, displaying recent response time metrics.


== Changelog ==

= 2.2.2 =

- Use `QM_Data_Cache` instead of `QM_Data`
- Fixed `WP_Error` use statement non-compound name warning

= 2.2.1 =

- Added WordPress 6.1 `wp_cache_supports()` function
- Updated Predis to v2.0.3
- Avoid early `microtime()` calls in `WP_Object_Cache::get()`
- Support Query Monitor's new `QM_Data` class
- Throw exception of pipeline returns unexpected results

= 2.2.0 =

- Added `redis_cache_add_non_persistent_groups` filter
- Fixed `wp_add_dashboard_widget` parameters
- Fixed `WP_REDIS_SERVERS` replication issue with Predis v2.0
- Fixed `WP_REDIS_CLUSTER` string support
- Fixed issue when `MGET` fails in `get_multiple()` call
- Fixed several warnings in the event of pipeline failures

= 2.1.6 =

- Fixed SVN discrepancies

= 2.1.5 =

- Fixed `is_predis()` call

= 2.1.4 =

- Added `is_predis()` helper

= 2.1.3 =

- Fixed bug in `wp_cache_add_multiple()` and `wp_cache_set_multiple()`

= 2.1.2 =

- Fixed and improved `wp_cache_*_multiple()` logic
- Call `redis_object_cache_set` action in `wp_cache_set_multiple()`
- Call `redis_object_cache_delete` action in `wp_cache_delete_multiple()`
- Check if raw group name is ignored, not sanitized name
- Removed tracing

= 2.1.1 =

- Bumped PHP requirement to 7.2
- Renamed `WP_REDIS_DIR` to `WP_REDIS_PLUGIN_DIR`
- Fixed rare fatal error in diagnostics
- Allow Predis v1.1 Composer installs
- Support using `WP_REDIS_CLUSTER` string

= 2.1.0 =

- Bumped PHP requirement to 7.0
- Deprecated Credis and HHVM clients
- Updated Predis to v2.0.0
- Updated Credis to v1.13.1
- Improved cluster readability in diagnostics
- Improved connecting to clusters
- Fixed pinging clusters after connecting
- Fixed several bugs in `connect_using_credis()`

= 2.0.26 =

- Fixed a bug in `wp_cache_delete_multiple()` when using Predis
- Fixed a bug in `wp_cache_add_multiple()` when cache addition is suspended

= 2.0.25 =

- Removed broken `wp_cache_add_multiple()` function

= 2.0.24 =

- Improve metrics label/tooltip formatting
- Fix metrics chart not rendering
- Updated Predis to v1.1.10
- Updated Credis to v1.13.0
- Support `composer/installers` v1 and v2
- Link to settings page when foreign drop-in was found
- Added `wp_cache_flush_runtime()` function
- Added `wp_cache_add_multiple()` function
- Added `wp_cache_delete_multiple()` function

= 2.0.23 =

- Added support for [Relay](https://relaycache.com)
- Minor UX fixes and improvements
- Fixed PHP 8.1 deprecation notice
- Updated ApexCharts to v3.31.0

= 2.0.22 =

- PHP 8.1 compatibility fixes
- Upgraded to Predis v1.1.9
- Added settings link to widget
- Overhauled diagnostics pane
- Updated ApexCharts to v3.30.0
- Redirect to plugin settings after activation
- Fixed wrong path to `diagnostics.php` file
- Fixed chart overflow in settings tab
- Fixed Predis cluster ping
- Avoid warning when content folder is not writeable

= 2.0.21 =

- Added metrics diagnostics
- Added `WP_Object_Cache::decr()` alias
- Moved `diagnostics.php` file

= 2.0.20 =

- Fix wp.org release

= 2.0.19 =

- Make metric identifier unique
- Set unique prefix for sites hosted on Cloudways
- Don't print HTML debug comment when `WP_CLI` is `true`

= 2.0.18 =

- Added `redis_object_cache_trace` action and `WP_REDIS_TRACE` constant
- Updated ApexCharts to v3.26.0
- Fixed and issue with `WP_REDIS_DISABLE_METRICS`

= 2.0.17 =

- Code cleanup
- Fixed missing metrics
- Fixed filesystem test

= 2.0.16 =

- Updated Credis to v1.11.4
- Fixed drop-in notice styling
- Moved metrics into dedicated class
- Added `redis_cache_validate_dropin` filter
- Use `WP_DEBUG_DISPLAY` (instead of `WP_DEBUG`) constant to display debug information
- Fixed rare error in `wp_cache_get_multiple()`
- Removed `intval()` usage

= 2.0.15 =

- Reverted `build_key()` changes due to issues in multisite environments

= 2.0.14 =

- Made Object Cache Pro card translatable
- Added `WP_REDIS_SERIALIZER` to diagnostics
- Improved speed of `build_key()`
- Support settings `WP_REDIS_PREFIX` and `WP_REDIS_SELECTIVE_FLUSH` via environment variable
- Added `WP_REDIS_METRICS_MAX_TIME` to adjust stored metrics timeframe
- Delay loading of text domain and schedule until `init` hook
- Upgraded bundled Predis library to v1.1.6
- Prevent variable referencing issue in `connect_using_credis()`

= 2.0.13 =

- Updated bundled Predis library to v1.1.4
- Made `redis-cache` a global group for improved metrics on multisite
- Switched to short array syntax
- Added `@since` tags to all hooks
- Use `parse_url()` instead of `wp_parse_url()` in drop-in
- Fixed plugin instance variable name in `wp redis status`

= 2.0.12 =

- Fixed bytes metrics calculation
- Fixed an issue with non-standard Predis configurations
- Improve WordPress Coding Standards

= 2.0.11 =

- Fixed an issue in `wp_cache_get_multiple()` when using Predis
- Prevent undefined index notice in diagnostics

= 2.0.10 =

- Fixed unserializing values in `wp_cache_get_multiple()`

= 2.0.9 =

- Highlight current metric type using color
- Show "Metrics" tab when metrics are disabled
- Refactored connection and Redis status logic
- Updated Predis to v1.1.2
- Remove Predis deprecation notice
- Fixed fetching derived keys in `wp_cache_get_multiple()`

= 2.0.8 =

- Fixed tabs not working in 2.0.6 and 2.0.7 due to WP.org SVN issue

= 2.0.7 =

- Fixed issue with `wp_cache_get_multiple()`

= 2.0.6 =

- Added experimental filesystem test to diagnostics
- Refactored settings tab logic (fixed jumping, too)
- Fixed issues with `wp_cache_get_multiple()`
- Return boolean from `wp_cache_delete()`
- Use `redis-cache` as JS event namespace
- Hide Pro line in widget when banners are disabled
- Renamed `redis_object_cache_get_multi` action to `redis_object_cache_get_multiple`

= 2.0.5 =

Version 2.0 is a significant rewrite of the plugin. Please read the v2.0.0 release notes.

- Fixed multisite action buttons not working
- Removed outdated PHP 5.4 warning
- Added `read_timeout` support to Credis
- Display connection parameters when using Credis
- Added wiki link to Predis upgrade notice

= 2.0.4 =

- Attempt to reliably update the dropin when it's outdated
- Show ACL username on settings screen
- Show full diagnostics with `wp redis status`
- Always set `FS_CHMOD_FILE` when copying the `object-cache.php`
- Don't encode bullets in password diagnostics
- Call `redis_object_cache_update_dropin` during dropin update

= 2.0.3 =

- Hide "Metrics" tab when metrics are disabled
- Fixed `admin.js` not loading in multisite environments
- Avoid fatal error when interacting with metrics but Redis went away
- Added `WP_Object_Cache::__get()` for backwards compatibility

= 2.0.2 =

- Updated POT file and comments for translators

= 2.0.1 =

- Support older versions of Query Monitor
- Made "Dropin" status more helpful
- Hide Redis version in settings when it isn't available
- Collapsed dependency paths using `composer-custom-directory-installer` package
- Prevent `QM_Collector` conflicts with other plugins
- Prevent metric issues when cache is not available
- Fixed "Settings" link in plugin list
- Fixed `WP_REDIS_DISABLED` logic

= 2.0.0 =

Version 2.0 is a significant rewrite. The plugin now requires PHP 5.6, just like WordPress 5.2 does.

The GitHub and Composer repository was moved from `tillkruss/redis-cache` to `rhubarbgroup/redis-cache`.

On multisite networks, be sure to "Network Activate" the plugin after upgrading to v2.x.

- Require PHP 5.6
- Plugin is now "network-only"
- Switch to WPCS for code standards
- Overhauled the settings screen
- Added object cache metrics (on dashboard widget and settings)
- Added support for Query Monitor
- Added `Rhubarb\RedisCache` namespace to all files
- Added support for WP 5.5's new `wp_cache_get_multi()` function
- Added `redis_object_cache()` function to retrieve plugin instance
- Added dropin warnings to network dashboard
- Added support for setting Sentinel database numbers
- Support Redis 6 ACL username and password authentication
- Support overwriting existing dropin on setting screen
- Use singleton pattern to instantiate plugin
- Use Composer to install and load Predis
- Update object cache dropin during plugin update
- Use separate methods to connect with all clients
- Removed `CUSTOM_USER_TABLE` and `CUSTOM_USER_META_TABLE` weirdness
- Added `themes` as ignored group
- Changed default connection and read timeout to 1 second
- Prevent race condition in `add_or_replace()`
- Renamed `WP_CACHE_KEY_SALT` to `WP_REDIS_PREFIX` for clarity
- Replaced "slave" terminology with "replica"
- Only `SELECT` database when it's not `0`

= 1.6.1 =

- Fixed issue with footer comment showing during AJAX requests

= 1.6.0 =

- Improved group name sanitization (thanks @naxvog)
- Prevent fatal error when replacing foreign dropin
- Added HTML footer comment with optional debug information
- Removed prefix suggestions

_The HTML footer comment only prints debug information when `WP_DEBUG` is enabled. To disable the comment entirely, set the `WP_REDIS_DISABLE_COMMENT` constant to `true`._

= 1.5.9 =

- Fixed missing `$info` variable assignment in constructor
- Fixed MaxTTL warning condition
- Switched to using default button styles

= 1.5.8 =

- Added warning message about invalid MaxTTL
- Added warning about unmaintained Predis library
- Added suggestion about shorter, human-readable prefixes
- Added Redis Cache Pro compatibility to settings
- Fixed flushing the cache when the prefix contains special characters
- Fixed calling Redis `INFO` when using clusters
- Cleaned up the settings a little bit

= 1.5.7 =

- Added support for PhpRedis TLS connections
- Added support for timeout, read timeout and password when using PhpRedis cluster
- Fixed issue with `INFO` command
- Fixed object cloning when setting cache keys

= 1.5.6 =

- Added object cloning to in-memory cache
- Fixed PHP notice related to `read_timeout` parameter

= 1.5.5 =

Please flush the object cache after updating the drop to v1.5.5 to avoid dead keys filling up Redis memory.

- Removed lowercasing keys
- Remove scheduled metrics event
- Fixed Redis version call when using replication

= 1.5.4 =

- Removed metrics

= 1.5.3 =

- Fixed: Call to undefined function `get_plugin_data()`
- Fixed: Call to undefined method `WP_Object_Cache::redis_version()`

= 1.5.2 =

- Added Redis version to diagnostics
- Added `WP_REDIS_DISABLE_BANNERS` constant to disable promotions
- Fixed an issue with `redis.replicate_commands()`

= 1.5.1 =

This plugin turned 5 years today (Nov 14th) and its only fitting to release the business edition today as well.
[Object Cache Pro](https://objectcache.pro/) is a truly reliable, highly optimized and easy to debug rewrite of this plugin for SMBs.

- Added execution times to actions
- Added `WP_REDIS_VERSION` constant
- Fixed PhpRedis v3 compatibility
- Fixed an issue with selective flushing
- Fixed an issue with `mb_*` functions not existing
- Replaced Email Address Encoder card with Redis Cache Pro card
- Gather version metrics for better decision making

= 1.5.0 =

Since Predis isn't maintained any longer, it's highly recommended to switch over to PhpRedis (the Redis PECL extension).

- Improved Redis key name builder
- Added support for PhpRedis serializers
- Added `redis_object_cache_error` action
- Added timeout, read-timeout and retry configuration
- Added unflushable groups (defaults to `['userlogins']`)
- Fixed passwords not showing in server list

= 1.4.3 =

- Require PHP 5.4 or newer
- Use pretty print in diagnostics
- Throw exception if Redis library is missing
- Fixed cache not flushing for some users
- Fixed admin issues when `WP_REDIS_DISABLED` is `false`

= 1.4.2 =

- Added graceful Redis failures and `WP_REDIS_GRACEFUL` constant
- Improved cluster support
- Added `redis_cache_expiration` filter
- Renamed `redis_object_cache_get` filter to `redis_object_cache_get_value`

= 1.4.1 =

- Fixed potential fatal error related to `wp_suspend_cache_addition()`

= 1.4.0 =

- Added support for igbinary
- Added support for `wp_suspend_cache_addition()`

= 1.3.9 =

- Fixed `WP_REDIS_SHARDS` not showing up in server list
- Fixed `WP_REDIS_SHARDS` not working when using PECL extension
- Removed `WP_REDIS_SCHEME` and `WP_REDIS_PATH` leftovers

= 1.3.8 =

- Switched from single file Predis version to full library

= 1.3.7 =

- Revert back to single file Predis version

= 1.3.6 =

- Added support for Redis Sentinel
- Added support for sharing
- Switched to PHAR version of Predis
- Improved diagnostics
- Added `WP_REDIS_SELECTIVE_FLUSH`
- Added `$fail_gracefully` parameter to `WP_Object_Cache::__construct()`
- Always enforce `WP_REDIS_MAXTTL`
- Pass `$selective` and `$salt` to `redis_object_cache_flush` action
- Don’t set `WP_CACHE_KEY_SALT` constant

= 1.3.5 =

- Added basic diagnostics to admin interface
- Added `WP_REDIS_DISABLED` constant to disable cache at runtime
- Prevent "Invalid plugin header" error
- Return integer from `increment()` and `decrement()` methods
- Prevent object cache from being instantiated more than once
- Always separate cache key `prefix` and `group` by semicolon
- Improved performance of `build_key()`
- Only apply `redis_object_cache_get` filter if callbacks have been registered
- Fixed `add_or_replace()` to only set cache key if it doesn't exist
- Added `redis_object_cache_flush` action
- Added `redis_object_cache_enable` action
- Added `redis_object_cache_disable` action
- Added `redis_object_cache_update_dropin` action

= 1.3.4 =

- Added WP-CLI support
- Show host and port unless scheme is unix
- Updated default global and ignored groups
- Do a cache flush when activating, deactivating and uninstalling

= 1.3.3 =

- Updated Predis to `v1.1.1`
- Added `redis_instance()` method
- Added `incr()` method alias for Batcache compatibility
- Added `WP_REDIS_GLOBAL_GROUPS` and `WP_REDIS_IGNORED_GROUPS` constant
- Added `redis_object_cache_delete` action
- Use `WP_PLUGIN_DIR` with `WP_CONTENT_DIR` as fallback
- Set password when using a cluster or replication
- Show Redis client in `stats()`
- Change visibility of `$cache` to public
- Use old array syntax, just in case

= 1.3.2 =

- Make sure `$result` is not `false` in `WP_Object_Cache::get()`

= 1.3.1 =

- Fixed connection issue

= 1.3 =

- New admin interface
- Added support for `wp_cache_get()`'s `$force` and `$found` parameter
- Added support for clustering and replication with Predis

= 1.2.3 =

- UI improvements

= 1.2.2 =

- Added `redis_object_cache_set` action
- Added `redis_object_cache_get` action and filter
- Prevented duplicated admin status messages
- Load bundled Predis library only if necessary
- Load bundled Predis library using `WP_CONTENT_DIR` constant
- Updated `stats()` method output to be uniform with WordPress

= 1.2.1 =

- Added `composer.json`
- Added deactivation and uninstall hooks to delete `object-cache.php`
- Added local serialization functions for better `advanced-cache.php` support
- Updated bundled Predis version to `1.0.3`
- Updated heading structure to be semantic

= 1.2 =

- Added Multisite support
- Moved admin menu under _Settings_ menu
- Fixed PHP notice in `get_redis_client_name()`

= 1.1.1 =

- Call `select()` and optionally `auth()` if HHVM extension is used

= 1.1 =

- Added support for HHVM's Redis extension
- Added support for PECL Redis extension
- Added `WP_REDIS_CLIENT` constant, to set preferred Redis client
- Added `WP_REDIS_MAXTTL` constant, to force expiration of cache keys
- Improved `add_or_replace()`, `get()`, `set()` and `delete()` methods
- Improved admin screen styles
- Removed all internationalization/localization from drop-in

= 1.0.2 =

- Added "Flush Cache" button
- Added support for UNIX domain sockets
- Improved cache object retrieval performance significantly
- Updated bundled Predis library to version `1.0.1`

= 1.0.1 =

- Load plugin translations
- Hide global admin notices from non-admin users
- Prevent direct file access to `redis-cache.php` and `admin-page.php`
- Colorize "Disable Object Cache" button
- Call `Predis\Client->connect()` to avoid potential uncaught `Predis\Connection\ConnectionException`

= 1.0 =

- Initial release


== Upgrade Notice ==

= 2.2.2 =

New WordPress 6.1 functions, updated Predis to v2.0.3 and various improvements.
