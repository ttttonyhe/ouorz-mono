<?php

namespace CF\API;

use CF\Integration\DataStoreInterface;
use CF\Integration\DefaultIntegration;

abstract class AbstractPluginActions
{
    protected $api;
    protected $config;
    protected $integrationAPI;
    protected $dataStore;
    protected $logger;
    protected $request;
    protected $clientAPI;

    /**
     * @param DefaultIntegration $defaultIntegration
     * @param APIInterface       $api
     * @param Request            $request
     */
    public function __construct(DefaultIntegration $defaultIntegration, APIInterface $api, Request $request)
    {
        $this->api = $api;
        $this->config = $defaultIntegration->getConfig();
        $this->integrationAPI = $defaultIntegration->getIntegrationAPI();
        $this->dataStore = $defaultIntegration->getDataStore();
        $this->logger = $defaultIntegration->getLogger();
        $this->request = $request;

        $this->clientAPI = new Client($defaultIntegration);
    }

    /**
     * @param APIInterface $api
     */
    public function setAPI(APIInterface $api)
    {
        $this->api = $api;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param APIInterface $clientAPI
     */
    public function setClientAPI(APIInterface $clientAPI)
    {
        $this->clientAPI = $clientAPI;
    }

    /**
     * @param DataStoreInterface $dataStore
     */
    public function setDataStore(DataStoreInterface $dataStore)
    {
        $this->dataStore = $dataStore;
    }

    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * POST /account.
     *
     * @return mixed
     */
    public function login()
    {
        $requestBody = $this->request->getBody();
        if (empty($requestBody['apiKey'])) {
            return $this->api->createAPIError("Missing required parameter: 'apiKey'.");
        }
        if (empty($requestBody['email'])) {
            return $this->api->createAPIError("Missing required parameter: 'email'.");
        }

        $isCreated = $this->dataStore->createUserDataStore($requestBody['apiKey'], $requestBody['email'], null, null);
        if (!$isCreated) {
            return $this->api->createAPIError('Unable to save user credentials');
        }

        $params = array();
        // Only Wordpress gives us access to the zone name, so check for it here
        if ($this->integrationAPI instanceof \CF\WordPress\WordPressAPI) {
            $params =  array('name' => $this->integrationAPI->getOriginalDomain());
        }

        //Make a test request to see if the API Key, email are valid
        $testRequest = new Request('GET', 'zones/', $params, array());
        $testResponse = $this->clientAPI->callAPI($testRequest);

        if (!$this->clientAPI->responseOk($testResponse)) {
            //remove bad credentials
            $this->dataStore->createUserDataStore(null, null, null, null);

            return $this->api->createAPIError('Email address or API key invalid.');
        }

        $response = $this->api->createAPISuccessResponse(array('email' => $requestBody['email']));

        return $response;
    }

    /**
     * GET /plugin/:zonedId/settings.
     *
     * @return mixed
     */
    public function getPluginSettings()
    {
        $settingsList = Plugin::getPluginSettingsKeys();

        $formattedSettings = array();
        foreach ($settingsList as $setting) {
            $value = $this->dataStore->get($setting);
            if ($value === null) {
                //setting hasn't been set yet.
                $value = $this->api->createPluginSettingObject($setting, null, true, null);
            }
            array_push($formattedSettings, $value);
        }

        $response = $this->api->createAPISuccessResponse(
            $formattedSettings
        );

        return $response;
    }

    /**
     * For PATCH /plugin/:zonedId/settings/:settingId
     * @return mixed
     * @throws \Exception
     */
    public function patchPluginSettings()
    {
        $path_array = explode('/', $this->request->getUrl());
        $settingId = $path_array[3];

        $body = $this->request->getBody();
        $value = $body['value'] ?? "";
        $options = $this->dataStore->set($settingId, $this->api->createPluginSettingObject($settingId, $value, true, true));

        if (!isset($options)) {
            return $this->api->createAPIError('Unable to update plugin settings');
        }

        if ($settingId === Plugin::SETTING_DEFAULT_SETTINGS) {
            try {
                $this->applyDefaultSettings();
            } catch (\Exception $e) {
                if ($e instanceof Exception\CloudFlareException) {
                    return $this->api->createAPIError($e->getMessage());
                } else {
                    throw $e;
                }
            }
        }

        $response = $this->api->createAPISuccessResponse($this->dataStore->get($settingId));

        return $response;
    }

    /**
     * For GET /userconfig
     * @return mixed
     */
    public function getConfig()
    {
        $response = $this->api->createAPISuccessResponse(
            []
        );

        return $response;
    }


    /**
     * Children should implement this method to apply the plugin specific default settings.
     *
     * @return mixed
     */
    abstract public function applyDefaultSettings();
}
