<?php

namespace CF\WordPress\Test;

use CF\API\Request;
use CF\WordPress\ClientActions;
use CF\Integration\DefaultIntegration;

class ClientActionsTest extends \PHPUnit\Framework\TestCase
{
    private $mockClientAPI;
    private $mockConfig;
    private $mockWordPressAPI;
    private $mockDataStore;
    private $mockLogger;
    private $mockDefaultIntegration;

    public function setup(): void
    {
        $this->mockClientAPI = $this->getMockBuilder('CF\API\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockConfig = $this->getMockBuilder('CF\Integration\DefaultConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockWordPressAPI = $this->getMockBuilder('CF\WordPress\WordPressAPI')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockDataStore = $this->getMockBuilder('CF\WordPress\DataStore')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLogger = $this->getMockBuilder('CF\Integration\DefaultLogger')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockDefaultIntegration = new DefaultIntegration($this->mockConfig, $this->mockWordPressAPI, $this->mockDataStore, $this->mockLogger);
    }

    public function testReturnWordPressDomain()
    {
        $wordPressDomain = 'example.com';
        $request = new Request(null, null, null, null);

        $clientActions = new ClientActions($this->mockDefaultIntegration, $this->mockClientAPI, $request);
        $this->mockWordPressAPI->method('getDomainList')->willReturn(
            array($wordPressDomain)
        );
        $this->mockClientAPI->method('responseOk')->willReturn(true);
        $this->mockClientAPI->method('callAPI')->willReturn(array('result' => array()));
        $response = $clientActions->returnWordPressDomain();

        $this->assertEquals($wordPressDomain, $response['result'][0]['name']);
    }

    public function testCacheDomainNameCachesSubDomain()
    {
        $responseDomain = 'domain.com';
        $originalDomain = 'sub.domain.com';
        $cachedDomainList = '';
        $response = array(
            'result' => array(
                array(
                    'name' => $responseDomain,
                ),
            ),
        );

        $request = new Request(null, null, null, null);
        $clientActions = new ClientActions($this->mockDefaultIntegration, $this->mockClientAPI, $request);

        $this->mockWordPressAPI->method('getOriginalDomain')->willReturn($originalDomain);
        $this->mockWordPressAPI->method('getDomainList')->willReturn(array($cachedDomainList));

        $result = $clientActions->cacheDomainName($response);
        $this->assertEquals($originalDomain, $result);
    }

    public function testCacheDomainNameReturnsCachedDomain()
    {
        $originalDomain = 'domain.com';
        $cachedDomainList = 'domain.com';

        $request = new Request(null, null, null, null);
        $clientActions = new ClientActions($this->mockDefaultIntegration, $this->mockClientAPI, $request);

        $this->mockWordPressAPI->method('getOriginalDomain')->willReturn($originalDomain);
        $this->mockWordPressAPI->method('getDomainList')->willReturn(array($cachedDomainList));

        $result = $clientActions->cacheDomainName(array());
        $this->assertEquals($cachedDomainList, $result);
    }
}
