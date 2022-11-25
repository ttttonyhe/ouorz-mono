<?php

namespace CF\Router\Test;

use CF\API\Request;
use CF\Integration\DefaultIntegration;
use CF\Router\DefaultRestAPIRouter;

class DefaultRestAPIRouterTest extends \PHPUnit\Framework\TestCase
{
    private $clientV4APIRouter;
    private $mockConfig;
    private $mockClientAPI;
    private $mockAPI;
    private $mockIntegration;
    private $mockDataStore;
    private $mockLogger;
    private $mockRoutes = array();

    public function setup(): void
    {
        $this->mockConfig = $this->getMockBuilder('CF\Integration\DefaultConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockClientAPI = $this->getMockBuilder('CF\API\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockAPI = $this->getMockBuilder('CF\Integration\IntegrationAPIInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockDataStore = $this->getMockBuilder('CF\Integration\DataStoreInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLogger = $this->getMockBuilder('CF\Integration\DefaultLogger')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockIntegration = new DefaultIntegration($this->mockConfig, $this->mockAPI, $this->mockDataStore, $this->mockLogger);
        $this->clientV4APIRouter = new DefaultRestAPIRouter($this->mockIntegration, $this->mockClientAPI, $this->mockRoutes);
    }

    public function testGetRouteReturnsClassFunctionForValidRoute()
    {
        $routes = array(
            'zones' => array(
                'class' => 'testClass',
                'methods' => array(
                    'GET' => array(
                        'function' => 'testFunction',
                    ),
                ),
            ),
        );
        $this->clientV4APIRouter->setRoutes($routes);

        $request = new Request('GET', 'zones', array(), array());

        $response = $this->clientV4APIRouter->getRoute($request);

        $this->assertEquals(array(
            'class' => 'testClass',
            'function' => 'testFunction',
        ), $response);
    }

    public function testGetRouteReturnsFalseForNoRouteFound()
    {
        $request = new Request('GET', 'zones', array(), array());
        $response = $this->clientV4APIRouter->getRoute($request);
        $this->assertFalse($response);
    }
}
