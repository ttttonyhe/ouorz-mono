<?php

namespace CF\Integration;

class DefaultConfig implements ConfigInterface
{
    private $config;

    /**
     * @param $config from file_get_contents()
     */
    public function __construct($config = "[]")
    {
        $this->config = json_decode($config, true);
    }

    /**
     * @param $key
     *
     * @return value or key or null
     */
    public function getValue($key)
    {
        $value = null;
        if (array_key_exists($key, $this->config)) {
            $value = $this->config[$key];
        }

        return $value;
    }
}
