<?php

namespace CF\API\Test;

use CF\Integration\DefaultIntegration;
use CF\Integration\DefaultLogger;
use CF\Integration\DataStoreInterface;
use CF\Integration\IntegrationAPIInterface;
use CF\API\HttpClientInterface;
use \CF\API\Request;
use \CF\API\AbstractAPIClient;
use \CF\Integration\DefaultConfig;

class AbstractAPIClientTest extends \PHPUnit\Framework\TestCase
{
    protected $mockAbstractAPIClient;
    protected $mockAPI;
    protected $mockClient;
    protected $mockConfig;
    protected $mockDataStore;
    protected $mockLogger;
    protected $mockRequest;

    const TOTAL_PAGES = 3;
    const MOCK_RESPONSE = [
        'result' => [],
        'result_info' => [
            'total_pages' => self::TOTAL_PAGES
        ]
    ];

    public function setup(): void
    {
        $this->mockRequest = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockClient = $this->getMockBuilder(HttpClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockConfig = $this->getMockBuilder(DefaultConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockConfig->method('getValue')->willReturn(true);
        $this->mockAPI = $this->getMockBuilder(IntegrationAPIInterface::class)
            ->getMock();
        $this->mockDataStore = $this->getMockBuilder(DataStoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLogger = $this->getMockBuilder(DefaultLogger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockIntegration = new DefaultIntegration($this->mockConfig, $this->mockAPI, $this->mockDataStore, $this->mockLogger);

        $this->mockAbstractAPIClient = $this->getMockBuilder(AbstractAPIClient::class)
            ->setConstructorArgs([$this->mockIntegration])
            ->getMockForAbstractClass();
        $this->mockAbstractAPIClient->setHttpClient($this->mockClient);
    }

    public function testGetPaginatedResultsRequestsAllPages()
    {
        $this->mockRequest->method('getMethod')->willReturn('GET');
        $this->mockClient->expects($this->exactly((self::TOTAL_PAGES - 1)))->method('send')->willReturn([
            'result' => []
        ]);
        $this->mockAbstractAPIClient->getPaginatedResults($this->mockRequest, self::MOCK_RESPONSE);
    }

    public function testGetPaginatedResultsOnlyExecutesForGET()
    {
        $methods = ['DELETE', 'PUT', 'PATCH', 'POST'];
        $this->mockClient->expects($this->never())->method('send');

        foreach ($methods as $method) {
            $this->mockRequest = $this->getMockBuilder(Request::class)
                ->disableOriginalConstructor()
                ->getMock();
            $this->mockRequest->method('getMethod')->willReturn($method);
            $this->mockAbstractAPIClient->getPaginatedResults($this->mockRequest, self::MOCK_RESPONSE);
        }
    }

    public function testGetPaginatedResultsOnlyExecutesForPagedResults()
    {
        $this->mockClient->expects($this->never())->method('send');
        $this->mockAbstractAPIClient->getPaginatedResults($this->mockRequest, []);
    }

    public function testGetPathReturnsPath()
    {
        $endpoint = 'http://api.cloudflare.com/client/v4';
        $path = '/zones';
        $this->mockRequest->method('getUrl')->willReturn($endpoint . $path);
        $this->mockAbstractAPIClient->method('getEndpoint')->willReturn($endpoint);
        $this->assertEquals($path, $this->mockAbstractAPIClient->getPath($this->mockRequest));
    }

    public function testShouldRouteRequestReturnsTrueForValidRequest()
    {
        $endpoint = 'http://api.cloudflare.com/client/v4';
        $url = $endpoint . '/zones';
        $this->mockRequest->method('getUrl')->willReturn($url);
        $this->mockAbstractAPIClient->method('getEndpoint')->willReturn($endpoint);
        $this->assertTrue($this->mockAbstractAPIClient->shouldRouteRequest($this->mockRequest));
    }

    public function testShouldRouteRequestReturnsFalseForInvalidRequest()
    {
        $this->mockRequest->method('getUrl')->willReturn('http://api.cloudflare.com/client/v4/zones');
        $this->mockAbstractAPIClient->method('getEndpoint')->willReturn('https://api.cloudflare.com/host-gw.html');
        $this->assertFalse($this->mockAbstractAPIClient->shouldRouteRequest($this->mockRequest));
    }

    public function testSendAndLogCallsLogger()
    {
        $this->mockLogger->expects($this->once())->method('error');
        $this->mockAbstractAPIClient->sendAndLog($this->mockRequest);
    }
}
