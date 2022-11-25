<?php

// TODO:
// Get rid of $GLOBALS and use static variables
// Make class functions non-static
// Add debug logs. Need dependency injection of logger to this class
namespace CF\WordPress;

class HTTP2ServerPush
{
    private static $initiated = false;

    public static function init()
    {
        if (!self::$initiated) {
            self::initHooks();
        }

        ob_start();
    }

    public static function initHooks()
    {
        self::$initiated = true;

        $autoptimize_js_enabled = (get_option('autoptimize_js') && get_option('autoptimize_js') === 'on');
        $autoptimize_css_enabled = (get_option('autoptimize_css') && get_option('autoptimize_css') === 'on');

        add_action('wp_head', array('\CF\WordPress\HTTP2ServerPush', 'http2ResourceHints'), 99, 1);

        // If Autoptimize exists, prefer the optimized assets it emits over usual WordPress enqueued scripts
        if (class_exists('autoptimizeMain')) {
            add_filter('autoptimize_filter_cache_getname', array('\CF\WordPress\HTTP2ServerPush', 'http2LinkPreloadHeader'), 99, 1);
            if (!$autoptimize_js_enabled) {
                add_filter('script_loader_src', array('\CF\WordPress\HTTP2ServerPush', 'http2LinkPreloadHeader'), 99, 1);
            }
            if (!$autoptimize_css_enabled) {
                add_filter('style_loader_src', array('\CF\WordPress\HTTP2ServerPush', 'http2LinkPreloadHeader'), 99, 1);
            }
        } else {
            // Autoptimize plugin is not activated, so fallback to the usual WordPress script and style queues
            add_filter('script_loader_src', array('\CF\WordPress\HTTP2ServerPush', 'http2LinkPreloadHeader'), 99, 1);
            add_filter('style_loader_src', array('\CF\WordPress\HTTP2ServerPush', 'http2LinkPreloadHeader'), 99, 1);
        }
    }

    public static function http2LinkPreloadHeader($src)
    {
        if (strpos($src, home_url()) !== false) {
            $preload_src = apply_filters('http2_link_preload_src', $src);

            if (!empty($preload_src)) {
                $newHeader = sprintf(
                    'Link: <%s>; rel=preload; as=%s',
                    esc_url(self::http2LinkUrlToRelativePath($preload_src)),
                    sanitize_html_class(self::http2LinkResourceHintAs(current_filter(), $preload_src))
                );
                $headerAsString = implode('  ', headers_list());

                // +2 comes from the last CRLF since it's two bytes
                $headerSize = strlen($headerAsString) + strlen($newHeader) + 2;

                // If the current header size is larger than $maxHeaderSize bytes
                // ignore following resources which can be pushed
                // This is a workaround for Cloudflare's 8KiB header limit
                // and fastcgi default 4KiB header limit
                $maxHeaderSize = 3072; // 3 KiB by default
                if (defined('CLOUDFLARE_HTTP2_SERVER_PUSH_HEADER_SIZE')) {
                    $maxHeaderSize = absint(CLOUDFLARE_HTTP2_SERVER_PUSH_HEADER_SIZE);
                };

                if ($headerSize > $maxHeaderSize) {
                    if (defined('CLOUDFLARE_HTTP2_SERVER_PUSH_LOG') && CLOUDFLARE_HTTP2_SERVER_PUSH_LOG) {
                        error_log("Cannot Server Push (header size over $maxHeaderSize bytes).");
                    }
                    return $src;
                }

                header($newHeader, false);

                $GLOBALS['http2_'.self::http2LinkResourceHintAs(current_filter(), $preload_src).'_srcs'][] = self::http2LinkUrlToRelativePath($preload_src);
            }
        }

        return $src;
    }

    /**
     * Render "resource hints" in the <head> section of the page. These encourage preload/prefetch behavior
     * when HTTP/2 support is lacking.
     */
    public static function http2ResourceHints()
    {

        $resource_types = array('script', 'style');
        array_walk($resource_types, function ($resource_type) {
            $key = "http2_{$resource_type}_srcs";
            if (isset($GLOBALS[$key]) && is_array($GLOBALS[$key])) {
                array_walk($GLOBALS[$key], function ($src) use ($resource_type) {
                    printf('<link rel="preload" href="%s" as="%s">', esc_url($src), esc_html($resource_type));
                });
            }
        });
    }

    /**
     * Convert an URL with authority to a relative path.
     *
     * @param string $src URL
     *
     * @return string mixed relative path
     */
    public static function http2LinkUrlToRelativePath($src)
    {
        return '//' === substr($src, 0, 2) ? preg_replace('/^\/\/([^\/]*)\//', '/', $src) : preg_replace('/^http(s)?:\/\/[^\/]*/', '', $src);
    }

    /**
     * Maps a WordPress hook to an "as" parameter in a resource hint.
     *
     * @param string $current_hook pass current_filter()
     *
     * @return string 'style' or 'script'
     */
    public static function http2LinkResourceHintAs($current_hook, $src)
    {

        switch ($current_hook) {
            case 'style_loader_src':
                return 'style';
            case 'script_loader_src':
                return 'script';
            case 'autoptimize_filter_cache_getname':
                $ext = pathinfo($src, PATHINFO_EXTENSION);
                if ($ext === 'js') {
                    return 'script';
                } elseif ($ext === 'css') {
                    return 'style';
                }
                return '';
            default:
                return '';
        }
    }
}
