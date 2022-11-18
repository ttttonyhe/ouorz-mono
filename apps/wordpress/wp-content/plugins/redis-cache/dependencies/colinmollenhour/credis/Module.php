<?php

/**
 * Credis_Module
 *
 * Implements Redis Modules support. see http://redismodules.com
 *
 * @author Igor Veremchuk <igor.veremchuk@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package Credis_Module
 */
class Credis_Module
{
    const MODULE_COUNTING_BLOOM_FILTER = 'CBF';

    /** @var Credis_Client */
    protected $client;

    /** @var  string */
    protected $moduleName;

    /**
     * @param Credis_Client $client
     * @param string $module
     */
    public function __construct(Credis_Client $client, $module = null)
    {
        $client->forceStandalone(); // Redis Modules command not currently supported by phpredis
        $this->client = $client;

        if (isset($module)) {
            $this->setModule($module);
        }
    }

    /**
     * Clean up client on destruct
     */
    public function __destruct()
    {
        $this->client->close();
    }

    /**
     * @param $moduleName
     * @return $this
     */
    public function setModule($moduleName)
    {
        $this->moduleName = (string) $moduleName;

        return $this;
    }

    /**
     * @param string $name
     * @param string $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        if ($this->moduleName === null) {
            throw new \LogicException('Module must be set.');
        }

        return call_user_func(array($this->client, sprintf('%s.%s', $this->moduleName, $name)), $args);
    }
}
