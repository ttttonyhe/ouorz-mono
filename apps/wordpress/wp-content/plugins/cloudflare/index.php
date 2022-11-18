<?php
if (!defined('ABSPATH')) { // Exit if accessed directly
    exit;
}

$config = new CF\Integration\DefaultConfig(file_get_contents('config.json', true));
$logger = new CF\Integration\DefaultLogger($config->getValue('debug'));
$dataStore = new CF\WordPress\DataStore($logger);
$wordpressAPI = new CF\WordPress\WordPressAPI($dataStore);

$pluginData = get_plugin_data(CLOUDFLARE_PLUGIN_DIR.'cloudflare.php');
$pluginVersion = $pluginData['Version'];

wp_register_style('cf-corecss', plugins_url('stylesheets/cf.core.css', __FILE__), null, $pluginVersion);
wp_enqueue_style('cf-corecss');
wp_register_style('cf-componentscss', plugins_url('stylesheets/components.css', __FILE__), null, $pluginVersion);
wp_enqueue_style('cf-componentscss');
wp_register_style('cf-hackscss', plugins_url('stylesheets/hacks.css', __FILE__), null, $pluginVersion);
wp_enqueue_style('cf-hackscss');
wp_enqueue_script('cf-compiledjs', plugins_url('compiled.js', __FILE__), null, $pluginVersion);
?>
<div id="root" class="cloudflare-partners site-wrapper"></div>
<script>
//Set global absolute base url
window.absoluteUrlBase = '<?php echo plugins_url('/cloudflare/'); ?>';

cfCSRFToken = '<?php echo wp_create_nonce(\CF\WordPress\WordPressAPI::API_NONCE); ?>';
localStorage.cfEmail = '<?php echo $dataStore->getCloudFlareEmail(); ?>';

/*
 * A callback for cf-util-http to proxy all calls to our backend
 *
 * @param {Object} [opts]
 * @param {String} [opts.method] - GET/POST/PUT/PATCH/DELETE
 * @param {String} [opts.url]
 * @param {Object} [opts.parameters]
 * @param {Object} [opts.headers]
 * @param {Object} [opts.body]
 * @param {Function} [opts.onSuccess]
 * @param {Function} [opts.onError]
 */
window.RestProxyCallback = (opts) => {
    // Only proxy external REST calls
    if (opts.url.lastIndexOf('http', 0) === 0) {
        if (!opts.parameters) {
            opts.parameters = {};
        }

        // WordPress Ajax Action
        opts.parameters['action'] = '<?php echo \CF\WordPress\Hooks::WP_AJAX_ACTION; ?>'

        if (opts.method.toUpperCase() === 'GET') {
            var clientAPIURL = '<?php echo \CF\API\Client::ENDPOINT; ?>';
            var pluginAPIURL = '<?php echo \CF\API\Plugin::ENDPOINT; ?>';

            // If opts.url begins with clientAPIURL or pluginAPIURL,
            // remove the API URL and assign the rest to proxyURL
            if (opts.url.substring(0, clientAPIURL.length) === clientAPIURL) {
                opts.parameters['proxyURL'] = opts.url.substring(clientAPIURL.
                    length);
                opts.parameters['proxyURLType'] = 'CLIENT';
            } else if (opts.url.substring(0, pluginAPIURL.length) === pluginAPIURL) {
                opts.parameters['proxyURL'] = opts.url.substring(pluginAPIURL.length);
                opts.parameters['proxyURLType'] = 'PLUGIN';
            }
        } else {
            if (!opts.body) {
                opts.body = {};
            }

            opts.body['cfCSRFToken'] = cfCSRFToken;
            opts.body['proxyURL'] = opts.url;
        }

        // WordPress Ajax Global
        opts.url = ajaxurl;
    } else {
        // To avoid static files getting cached add the version number
        // to the url
        var versionNumber = '<?php echo $pluginVersion; ?>';
        opts.url = absoluteUrlBase + opts.url + '?ver=' + versionNumber;
    }
}
</script>
