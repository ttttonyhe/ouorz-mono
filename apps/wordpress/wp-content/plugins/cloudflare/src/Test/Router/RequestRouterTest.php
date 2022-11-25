<?php

namespace CF\Router\Test;

use CF\API\Client;
use CF\API\Request;
use CF\Integration\DefaultConfig;
use CF\Integration\DefaultLogger;
use CF\Integration\DefaultIntegration;
use CF\Integration\DataStoreInterface;
use CF\Integration\IntegrationAPIInterface;
use CF\Router\RequestRouter;
use CF\Router\DefaultRestAPIRouter;

class RequestRouterTest extends \PHPUnit\Framework\TestCase
{
    protected $mockConfig;
    protected $mockClient;
    protected $mockAPI;
    protected $mockIntegration;
    protected $mockDataStore;
    protected $mockLogger;
    protected $mockRequest;
    protected $requestRouter;

    public function setup(): void
    {
        $this->mockConfig = $this->getMockBuilder(DefaultConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockAPI = $this->getMockBuilder(IntegrationAPIInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockDataStore = $this->getMockBuilder(DataStoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLogger = $this->getMockBuilder(DefaultLogger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockIntegration = new DefaultIntegration($this->mockConfig, $this->mockAPI, $this->mockDataStore, $this->mockLogger);

        $this->mockRequest = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestRouter = new RequestRouter($this->mockIntegration);
    }

    public function testAddRouterAddsRouter()
    {
        $clientName = "clientName";
        $this->mockClient->method('getAPIClientName')->willReturn($clientName);

        $this->requestRouter->addRouter($this->mockClient, null);
        $this->assertEquals(DefaultRestAPIRouter::class, get_class($this->requestRouter->getRouterList()[$clientName]));
    }

    public function testRoutePassesValidRequestToDefaultRestAPIRouter()
    {
        $mockDefaultRestAPIRouter = $this->getMockBuilder('CF\Router\DefaultRestAPIRouter')
            ->disableOriginalConstructor()
            ->getMock();
        $mockAPIClient = $this->getMockBuilder('CF\API\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $mockAPIClient->method('shouldRouteRequest')->willReturn(true);
        $mockDefaultRestAPIRouter->method('getAPIClient')->willReturn($mockAPIClient);
        $mockDefaultRestAPIRouter->expects($this->once())->method('route');

        $this->requestRouter->setRouterList(array($mockDefaultRestAPIRouter));

        $this->requestRouter->route($this->mockRequest);
    }
}
