<?php

namespace CF\Router;

use CF\API\APIInterface;
use CF\API\Client;
use CF\API\Request;
use CF\Integration\IntegrationInterface;

class DefaultRestAPIRouter implements RouterInterface
{
    private $api;
    private $dataStore;
    private $integration;
    private $integrationAPI;
    private $logger;
    private $routes;

    const ENDPOINT = 'https://api.cloudflare.com/client/v4/';

    // Placeholders you can use to pattern match part of a URI
    public static $API_ROUTING_PLACEHOLDERS = array(
        ':id' => '[0-9a-z]{32}',
        ':bigint_id' => '[0-9]{1,19}',
        ':human_readable_id' => '[-0-9a-z_]{1,120}',
        ':rayid' => '[0-9a-z]{16}',
        ':firewall_rule_id' => '[0-9a-zA-Z\\-_]{1,160}',
        ':file_name' => '[0-9A-Za-z_\\.\\-]{1,120}',
        ':uuid' => '[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}',
    );

    /**
     * @param IntegrationInterface $integration
     * @param APIInterface         $api
     * @param $routes
     */
    public function __construct(IntegrationInterface $integration, APIInterface $api, $routes)
    {
        $this->api = $api;
        $this->dataStore = $integration->getDataStore();
        $this->integration = $integration;
        $this->integrationAPI = $integration->getIntegrationAPI();
        $this->logger = $integration->getLogger();
        $this->routes = $routes;
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function route(Request $request)
    {
        $request->setUrl($this->api->getPath($request));

        $routeParameters = $this->getRoute($request);
        if ($routeParameters) {
            $class = $routeParameters['class'];
            $function = $routeParameters['function'];
            $routeClass = new $class($this->integration, $this->api, $request);

            return $routeClass->$function();
        } else {
            return $this->api->callAPI($request);
        }
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function getPath(Request $request)
    {
        //substring of everything after the endpoint is the path
        return substr($request->getUrl(), strpos($request->getUrl(), $this->api->getEndpoint()) + strlen($this->api->getEndpoint()));
    }
    
    /**
     * @param Request $request
     *
     * @return array|bool
     */
    public function getRoute(Request $request)
    {
        /*
         * This method allows CPanel to hook into our API calls that require Cpanel specific functionality.
         * Be VERY careful editing it, make sure you're code only fires for the specific API call you need to interact with.
         */

        //Load up our routes and replace their placeholders (i.e. :id changes to [0-9a-z]{32})
        foreach ($this->routes as $routeKey => $route_details_array) {
            //Replace placeholders in route
            $regex = str_replace(
                array_keys(static::$API_ROUTING_PLACEHOLDERS),
                array_values(static::$API_ROUTING_PLACEHOLDERS),
                $routeKey
            );

            //Check to see if this is our route
            if (preg_match('#^'.$regex.'/?$#', $request->getUrl())) {
                if (in_array($request->getMethod(), $route_details_array['methods']) || array_key_exists(
                    $request->getMethod(),
                    $route_details_array['methods']
                )
                ) {
                    $this->logger->debug('Route matched for '.$request->getMethod().$request->getUrl().' now using '.$route_details_array['methods'][$request->getMethod()]['function']);

                    return array(
                        'class' => $route_details_array['class'],
                        'function' => $route_details_array['methods'][$request->getMethod()]['function'],
                    );
                }
            }
        }

        //if no route was found call our API normally
        return false;
    }

    /**
     * @return Client
     */
    public function getAPIClient()
    {
        return $this->api;
    }

    /**
     * @param $routes
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;
    }
}
