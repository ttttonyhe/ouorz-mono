<?php

require_once __DIR__.'/vendor/autoload.php';

use CloudFlare\IpRewrite;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Rewrites Cloudflare IP
try {
    $ipRewrite = new IpRewrite();

    $isCf = $ipRewrite->isCloudFlare();

    if ($isCf) {
        // Fixes Flexible SSL
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $_SERVER['HTTPS'] = 'on';
        }

        // Rewrite Cloudflare IPs when the plugin is loaded,
        // Doing this later in the plugin lifecycle will not update the IPs correctly
        add_action('plugins_loaded', function () {
            $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
            $_SERVER['HTTP_X_FORWARDED_FOR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }, 1);
    }
} catch (\RuntimeException $e) {
    error_log($e->getMessage());
}

// Initialize Hooks class which contains WordPress hook functions
$cloudflareHooks = new \CF\WordPress\Hooks();

add_action('plugins_loaded', array($cloudflareHooks, 'getCloudflareRequestJSON'));

// Enable HTTP2 Server Push
if (defined('CLOUDFLARE_HTTP2_SERVER_PUSH_ACTIVE') && CLOUDFLARE_HTTP2_SERVER_PUSH_ACTIVE && !is_admin()) {
    add_action('init', array($cloudflareHooks, 'http2ServerPushInit'));
}

add_action('init', array($cloudflareHooks, 'initAutomaticPlatformOptimization'));

if (is_admin()) {
    //Register proxy AJAX endpoint
    add_action('wp_ajax_cloudflare_proxy', array($cloudflareHooks, 'initProxy'));

    //Add CloudFlare Plugin homepage to admin settings menu
    add_action('admin_menu', array($cloudflareHooks, 'cloudflareConfigPage'));

    //Add CloudFlare Plugin homepage to admin settings menu
    add_action('plugin_action_links_cloudflare/cloudflare.php', array($cloudflareHooks, 'pluginActionLinks'));

    // Load Activation Script
    register_activation_hook(CLOUDFLARE_PLUGIN_DIR.'cloudflare.php', array($cloudflareHooks, 'activate'));

    // Load Deactivation Script
    register_deactivation_hook(CLOUDFLARE_PLUGIN_DIR.'cloudflare.php', array($cloudflareHooks, 'deactivate'));
}

// Load Automatic Cache Purge
$cloudflarePurgeEverythingActions = array(
    'autoptimize_action_cachepurged',   // Compat with https://wordpress.org/plugins/autoptimize
    'switch_theme',                     // Switch theme
    'customize_save_after'              // Edit theme
);

$cloudflarePurgeEverythingActions = apply_filters('cloudflare_purge_everything_actions', $cloudflarePurgeEverythingActions);

foreach ($cloudflarePurgeEverythingActions as $action) {
    add_action($action, array($cloudflareHooks, 'purgeCacheEverything'), PHP_INT_MAX);
}

/**
 * You can filter the list of URLs that get purged by Cloudflare after a post is
 * modified by implementing a filter for the "cloudflare_purge_by_url" hook.
 *
 * @Example:
 *
 * /**
 *  * @param array $urls A list of post related URLs
 *  * @param integer $post_id the post ID that was modified
 *  * /
 * function your_cloudflare_url_filter($urls, $post_id) {
 *   // modify urls
 *   return $urls;
 * }
 *
 * add_filter('cloudflare_purge_by_url', your_cloudflare_url_filter, 10, 2);
 */

$cloudflarePurgeURLActions = array(
    'deleted_post',                     // Delete a post
    'delete_attachment',                // Delete an attachment - includes re-uploading
);

$cloudflarePurgeURLActions = apply_filters('cloudflare_purge_url_actions', $cloudflarePurgeURLActions);

foreach ($cloudflarePurgeURLActions as $action) {
    add_action($action, array($cloudflareHooks, 'purgeCacheByRelevantURLs'), PHP_INT_MAX);
}

/**
 * Register action to account for post status changes
 * This includes
 * - publish => publish transitions (editing a published post: no actual status change but the hook runs nevertheless)
 * - manually publishing/unpublishing a post
 * - WordPress automatically publishing a scheduled post at the appropriate time
 */
add_action('transition_post_status', array($cloudflareHooks, 'purgeCacheOnPostStatusChange'), PHP_INT_MAX, 3);

/**
 * Register two new actions which account for comment status before purging cache
 */
add_action('transition_comment_status', array($cloudflareHooks, 'purgeCacheOnCommentStatusChange'), PHP_INT_MAX, 3);
add_action('comment_post', array($cloudflareHooks, 'purgeCacheOnNewComment'), PHP_INT_MAX, 3);
