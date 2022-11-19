<?php

namespace CF\WordPress;

use CF\API;
use CF\API\Plugin;
use CF\Integration\IntegrationInterface;
use CF\Router\RequestRouter;

class Proxy
{
    protected $config;
    protected $dataStore;
    protected $logger;
    protected $wordpressAPI;
    protected $wordpressClientAPI;
    protected $wordpressIntegration;
    protected $requestRouter;

    /**
     * @param IntegrationInterface $integration
     */
    public function __construct(IntegrationInterface $integration)
    {
        $this->config = $integration->getConfig();
        $this->dataStore = $integration->getDataStore();
        $this->logger = $integration->getLogger();
        $this->wordpressAPI = $integration->getIntegrationAPI();
        $this->wordpressIntegration = $integration;
        $this->wordpressClientAPI = new WordPressClientAPI($this->wordpressIntegration);
        $this->pluginAPI = new Plugin($this->wordpressIntegration);

        $this->requestRouter = new RequestRouter($this->wordpressIntegration);
        $this->requestRouter->addRouter($this->wordpressClientAPI, ClientRoutes::$routes);
        $this->requestRouter->addRouter($this->pluginAPI, PluginRoutes::getRoutes(PluginRoutes::$routes));
    }

    /**
     * @param API\APIInterface $wordpressClientAPI
     */
    public function setWordpressClientAPI(API\APIInterface $wordpressClientAPI)
    {
        $this->wordpressClientAPI = $wordpressClientAPI;
    }

    /**
     * @param RequestRouter $requestRouter
     */
    public function setRequestRouter(RequestRouter $requestRouter)
    {
        $this->requestRouter = $requestRouter;
    }

    public function run()
    {
        header('Content-Type: application/json');

        $request = $this->createRequest();

        $response = null;
        $body = $request->getBody();
        $csrfToken = isset($body['cfCSRFToken']) ? $body['cfCSRFToken'] : null;
        if ($this->isCloudFlareCSRFTokenValid($request->getMethod(), $csrfToken)) {
            $response = $this->requestRouter->route($request);
        } else {
            if ($csrfToken === null) {
                $response = $this->wordpressClientAPI->createAPIError('CSRF Token not found. It\'s possible another plugin is altering requests sent by the Cloudflare plugin.');
            } else {
                $response = $this->wordpressClientAPI->createAPIError('CSRF Token not valid.');
            }
        }

        //die is how WordPress ajax keeps the rest of the app from loading during an ajax request
        wp_die(json_encode($response));
    }

    public function createRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $parameters = $_GET;
        $jsonInput = $this->getJSONBody();
        $body = json_decode($jsonInput, true);
        $path = null;

        if (strtoupper($method === 'GET')) {
            if ($_GET['proxyURLType'] === 'CLIENT') {
                $path = API\Client::ENDPOINT . $_GET['proxyURL'];
            } elseif ($_GET['proxyURLType'] === 'PLUGIN') {
                $path = API\Plugin::ENDPOINT . $_GET['proxyURL'];
            }
        } else {
            $path = $body['proxyURL'] ?? '';
        }

        unset($parameters['proxyURLType']);
        unset($parameters['proxyURL']);
        unset($body['proxyURL']);

        return new API\Request($method, $path, $parameters, $body);
    }

    /**
     * Wrapped in a function so it can be
     * mocked during testing
     *
     * @return json
     */
    public function getJSONBody()
    {
        return $GLOBALS[Hooks::CLOUDFLARE_JSON];
    }

    /**
     * https://codex.wordpress.org/Function_Reference/wp_verify_nonce.
     *
     * Boolean false if the nonce is invalid. Otherwise, returns an integer with the value of:
     * 1 – if the nonce has been generated in the past 12 hours or less.
     * 2 – if the nonce was generated between 12 and 24 hours ago.
     *
     * @param $csrfToken
     *
     * @return bool
     */
    public function isCloudFlareCSRFTokenValid($method, $csrfToken)
    {
        if ($method === 'GET') {
            return true;
        }

        return wp_verify_nonce($csrfToken, WordPressAPI::API_NONCE) !== false;
    }
}
