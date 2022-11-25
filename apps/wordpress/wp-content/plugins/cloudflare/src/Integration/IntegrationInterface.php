<?php

namespace CF\Integration;

interface IntegrationInterface
{
    /**
     * @return mixed
     */
    public function getConfig();

    /**
     * @return mixed
     */
    public function getIntegrationAPI();

    /**
     * @return mixed
     */
    public function getLogger();

    /**
     * @return mixed
     */
    public function getDataStore();
}
