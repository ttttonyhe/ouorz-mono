<?php

namespace CF\WordPress;

use CF\API\APIInterface;
use CF\API\AbstractPluginActions;
use CF\API\Exception\ZoneSettingFailException;
use CF\API\Plugin;
use CF\API\Request;
use CF\Integration\DefaultIntegration;
use CF\WordPress\Constants\Plans;

class PluginActions extends AbstractPluginActions
{
    protected $api;
    protected $clientAPI;
    protected $composer;
    protected $request;
    protected $userConfig;

    const CONFIG = [
        "debug" => false,
        "featureManagerIsFullZoneProvisioningEnabled" => false,
        "isDNSPageEnabled" => false,
        "isSubdomainCheckEnabled" => true,
        "useHostAPILogin" => false,
        "homePageCards" => [
            "ApplyDefaultSettingsCard",
            "AutomaticPlatformOptimizationCard",
            "PurgeCacheCard"
        ],
        "moreSettingsCards" => [
            "container.moresettings.speed" => [
                "AlwaysOnlineCard",
                "ImageOptimizationCard",
                "PluginSpecificCacheCard",
                "DevelopmentModeCard"
            ],
            "container.moresettings.security" => [
                "SecurityLevelCard",
                "WAFCard",
                "AdvanceDDoSCard",
                "AutomaticHTTPSRewritesCard"
            ]
        ],
        "locale" => "en",
        "integrationName" => "wordpress"
    ];

    const BANNED_KEYS = [
        'isDNSPageEnabled',
        'useHostAPILogin',
        'integrationName',
    ];

    const USER_CONFIG_PATH = '/../../config.json';
    const COMPOSER_CONFIG_PATH = '/../../composer.json';

    public function __construct(DefaultIntegration $defaultIntegration, APIInterface $api, Request $request)
    {
        parent::__construct($defaultIntegration, $api, $request);
        $this->clientAPI = new WordPressClientAPI($defaultIntegration);
    }

    public function setClientAPI(APIInterface $clientAPI)
    {
        // Inherited from AbstractPluginActions
        $this->clientAPI = $clientAPI;
    }

    /*
     * PATCH /plugin/:id/settings/default_settings
     *
     * Requests are synchronized
     */
    public function applyDefaultSettings()
    {
        $path_array = explode('/', $this->request->getUrl());
        $zoneId = $path_array[1];

        $result = true;
        $details = $this->clientAPI->zoneGetDetails($zoneId);

        if (!$this->clientAPI->responseOk($details)) {
            // Technically zoneGetDetails does not try to set Zone Settings
            // Can create a new exception but make things simple right?
            throw new ZoneSettingFailException();
        }

        $currentPlan = $details['result']['plan']['legacy_id'] ?? 'free';

        $result &= $this->clientAPI->changeZoneSettings($zoneId, 'security_level', array('value' => 'medium'));
        if (!$result) {
            throw new ZoneSettingFailException();
        }

        $result &= $this->clientAPI->changeZoneSettings($zoneId, 'cache_level', array('value' => 'aggressive'));
        if (!$result) {
            throw new ZoneSettingFailException();
        }

        $result &= $this->clientAPI->changeZoneSettings($zoneId, 'minify', array('value' => array('css' => 'on', 'html' => 'on', 'js' => 'on')));
        if (!$result) {
            throw new ZoneSettingFailException();
        }

        $result &= $this->clientAPI->changeZoneSettings($zoneId, 'browser_cache_ttl', array('value' => 14400));
        if (!$result) {
            throw new ZoneSettingFailException();
        }

        $result &= $this->clientAPI->changeZoneSettings($zoneId, 'always_online', array('value' => 'on'));
        if (!$result) {
            throw new ZoneSettingFailException();
        }

        $result &= $this->clientAPI->changeZoneSettings($zoneId, 'development_mode', array('value' => 'off'));
        if (!$result) {
            throw new ZoneSettingFailException();
        }

        $result &= $this->clientAPI->changeZoneSettings($zoneId, 'ipv6', array('value' => 'on'));
        if (!$result) {
            throw new ZoneSettingFailException();
        }

        $result &= $this->clientAPI->changeZoneSettings($zoneId, 'websockets', array('value' => 'on'));
        if (!$result) {
            throw new ZoneSettingFailException();
        }

        $result &= $this->clientAPI->changeZoneSettings($zoneId, 'ip_geolocation', array('value' => 'on'));
        if (!$result) {
            throw new ZoneSettingFailException();
        }

        $result &= $this->clientAPI->changeZoneSettings($zoneId, 'email_obfuscation', array('value' => 'on'));
        if (!$result) {
            throw new ZoneSettingFailException();
        }

        $result &= $this->clientAPI->changeZoneSettings($zoneId, 'server_side_exclude', array('value' => 'on'));
        if (!$result) {
            throw new ZoneSettingFailException();
        }

        $result &= $this->clientAPI->changeZoneSettings($zoneId, 'hotlink_protection', array('value' => 'off'));
        if (!$result) {
            throw new ZoneSettingFailException();
        }

        $result &= $this->clientAPI->changeZoneSettings($zoneId, 'rocket_loader', array('value' => 'off'));
        if (!$result) {
            throw new ZoneSettingFailException();
        }

        $result &= $this->clientAPI->changeZoneSettings($zoneId, 'automatic_https_rewrites', array('value' => 'on'));
        if (!$result) {
            throw new ZoneSettingFailException();
        }

        // If the plan supports Mirage and Polish try to set them on
        if (!Plans::planNeedsUpgrade($currentPlan, Plans::BIZ_PLAN)) {
            $result &= $this->clientAPI->changeZoneSettings($zoneId, 'mirage', array('value' => 'on'));
            if (!$result) {
                throw new ZoneSettingFailException();
            }

            $result &= $this->clientAPI->changeZoneSettings($zoneId, 'polish', array('value' => 'lossless'));
            if (!$result) {
                throw new ZoneSettingFailException();
            }
        }
    }

    public function getConfig()
    {
        $this->getUserConfig();
        $this->getComposerJson();

        //Clone the config to manipulate
        $config = array_merge(array(), self::CONFIG);

        //Add version from composer.json to the config
        $config['version'] = $this->composer['version'];

        //This removes all the banned keys from the userConfig so we don't over write them
        $this->userConfig = array_diff_key($this->userConfig, array_flip(self::BANNED_KEYS));

        //Merge and intersect userConfig with default config and return response
        $response = array_intersect_key($this->userConfig + $config, $config);

        return $this->api->createAPISuccessResponse($response);
    }

    public function getUserConfig()
    {
        if ($this->userConfig === null) {
            if (file_exists(dirname(__FILE__) . self::USER_CONFIG_PATH)) {
                $userConfigContent = file_get_contents(dirname(__FILE__) . self::USER_CONFIG_PATH);
            }

            // Need to set an empty array for merge into config so it doesnt throw a type error
            $this->userConfig = [];
            if (!empty($userConfigContent)) {
                $this->userConfig = json_decode($userConfigContent, true);
            }
        }
    }

    public function setUserConfig($userConfig)
    {
        $this->userConfig = $userConfig;
    }

    public function getComposerJson()
    {
        if ($this->composer === null && file_exists(dirname(__FILE__) . self::COMPOSER_CONFIG_PATH)) {
            $this->composer = json_decode(file_get_contents(dirname(__FILE__) . self::COMPOSER_CONFIG_PATH), true);
        }
    }

    public function setComposerJson($composer)
    {
        $this->composer = $composer;
    }
}
