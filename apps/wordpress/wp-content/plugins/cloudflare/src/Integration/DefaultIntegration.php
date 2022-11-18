<?php

namespace CF\Integration;

use Psr\Log\LoggerInterface;

class DefaultIntegration implements IntegrationInterface
{
    private $config;
    private $integrationAPI;
    private $dataStore;
    private $logger;

    /**
     * @param ConfigInterface                          $config
     * @param IntegrationAPIInterface                  $integrationAPI
     * @param DataStoreInterface                       $dataStore
     * @param LoggerInterface|\Psr\Log\LoggerInterface $logger
     */
    public function __construct(ConfigInterface $config, IntegrationAPIInterface $integrationAPI, DataStoreInterface $dataStore, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->integrationAPI = $integrationAPI;
        $this->dataStore = $dataStore;
        $this->logger = $logger;
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @return integrationAPI
     */
    public function getIntegrationAPI()
    {
        return $this->integrationAPI;
    }

    /**
     * @param IntegrationAPIInterface $integrationAPI
     */
    public function setIntegrationAPI(IntegrationAPIInterface $integrationAPI)
    {
        $this->integrationAPI = $integrationAPI;
    }

    /**
     * @return DataStore
     */
    public function getDataStore()
    {
        return $this->dataStore;
    }

    /**
     * @param DataStoreInterface $dataStore
     */
    public function setDataStore(DataStoreInterface $dataStore)
    {
        $this->dataStore = $dataStore;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
