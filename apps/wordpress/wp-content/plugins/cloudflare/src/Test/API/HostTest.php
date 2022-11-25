<?php

namespace CF\API\Test;

use CF\API\Host;
use CF\API\Request;
use CF\Integration\DefaultIntegration;

class HostTest extends \PHPUnit\Framework\TestCase
{
    private $hostAPI;
    private $mockConfig;
    private $mockAPI;
    private $mockDataStore;
    private $mockLogger;
    private $mockCpanelIntegration;

    public function setup(): void
    {
        $this->mockConfig = $this->getMockBuilder('CF\Integration\DefaultConfig')
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
        $this->mockCpanelIntegration = new DefaultIntegration($this->mockConfig, $this->mockAPI, $this->mockDataStore, $this->mockLogger);

        $this->hostAPI = new Host($this->mockCpanelIntegration);
    }

    public function testBeforeSendSetsCorrectPath()
    {
        $request = new Request(null, null, null, null);
        $request = $this->hostAPI->beforeSend($request);

        $this->assertEquals(Host::ENDPOINT_PATH, $request->getUrl());
    }

    public function testBeforeSendSetsIntegrationHeaders()
    {
        $integrationName = 'integrationName';
        $version = 'version';

        $this->mockConfig->method('getValue')->will(
            $this->returnValueMap(
                array(
                    array($integrationName, $integrationName),
                    array($version, $version),
                )
            )
        );

        $request = new Request(null, null, null, null);
        $request = $this->hostAPI->beforeSend($request);

        $requestHeaders = $request->getHeaders();

        $this->assertEquals($integrationName, $requestHeaders[Host::CF_INTEGRATION_HEADER]);
        $this->assertEquals($version, $requestHeaders[Host::CF_INTEGRTATION_VERSION_HEADER]);
    }

    public function testBeforeSendSetsUserKeyforActZoneSet()
    {
        $userKey = 'userKey';
        $this->mockDataStore->method('getHostAPIUserKey')->willReturn($userKey);

        $request = new Request(null, null, null, array('act' => 'zone_set'));
        $request = $this->hostAPI->beforeSend($request);

        $requestBody = $request->getBody();

        $this->assertEquals($userKey, $requestBody['user_key']);
    }

    public function testBeforeSendSetsUserKeyforActFullZoneSet()
    {
        $userKey = 'userKey';
        $this->mockDataStore->method('getHostAPIUserKey')->willReturn($userKey);

        $request = new Request(null, null, null, array('act' => 'full_zone_set'));
        $request = $this->hostAPI->beforeSend($request);

        $requestBody = $request->getBody();

        $this->assertEquals($userKey, $requestBody['user_key']);
    }

    public function testBeforeSendSetsHostKey()
    {
        $hostKey = 'hostKey';
        $this->mockAPI->method('getHostAPIKey')->willReturn($hostKey);

        $request = new Request(null, null, null, null);
        $request = $this->hostAPI->beforeSend($request);

        $requestBody = $request->getBody();

        $this->assertEquals($hostKey, $requestBody['host_key']);
    }

    public function testResponseOkReturnsTrueForValidResponse()
    {
        $hostAPIResponse = array(
            'result' => 'success',
        );

        $this->assertTrue($this->hostAPI->responseOk($hostAPIResponse));
    }

    public function testClientApiErrorReturnsValidStructure()
    {
        $message = 'message';

        $errorResponse = $this->hostAPI->createAPIError($message);

        $this->assertEquals($message, $errorResponse['msg']);
        $this->assertEquals('error', $errorResponse['result']);
    }

    public function testGetPathReturnsBodyActParameter()
    {
        $act = 'act';
        $request = new Request(null, null, null, array($act => $act));
        $this->assertEquals($act, $this->hostAPI->getPath($request));
    }

    public function testShouldRouteRequestReturnsTrueIfUrlsAreEqual()
    {
        $request = new Request(null, Host::ENDPOINT, null, null);
        $this->assertTrue($this->hostAPI->shouldRouteRequest($request));
    }
}
