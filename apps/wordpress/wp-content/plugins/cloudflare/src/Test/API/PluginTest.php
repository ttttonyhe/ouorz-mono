<?php

namespace CF\Test\API;

use CF\Integration\DefaultIntegration;
use CF\API\Plugin;

class PluginTest extends \PHPUnit\Framework\TestCase
{
    private $mockConfig;
    private $mockWordPressAPI;
    private $mockDataStore;
    private $mockLogger;
    private $mockDefaultIntegration;
    private $mockRequest;
    private $pluginAPIClient;

    public function setup(): void
    {
        $this->mockConfig = $this->getMockBuilder('CF\Integration\DefaultConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockWordPressAPI = $this->getMockBuilder('CF\Integration\IntegrationAPIInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockDataStore = $this->getMockBuilder('CF\Integration\DataStoreInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLogger = $this->getMockBuilder('CF\Integration\DefaultLogger')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockRequest = $this->getMockBuilder('CF\API\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockDefaultIntegration = new DefaultIntegration($this->mockConfig, $this->mockWordPressAPI, $this->mockDataStore, $this->mockLogger);
        $this->pluginAPIClient = new Plugin($this->mockDefaultIntegration);
    }

    public function testCreateAPISuccessResponse()
    {
        $resultString = 'result';
        $resultArray = array('email' => $resultString);

        $firstResponse = $this->pluginAPIClient->createAPISuccessResponse($resultString);
        $secondResponse = $this->pluginAPIClient->createAPISuccessResponse($resultArray);

        $this->assertTrue($firstResponse['success']);
        $this->assertTrue($secondResponse['success']);
        $this->assertEquals($resultString, $firstResponse['result']);
        $this->assertEquals($resultArray, $secondResponse['result']);
    }

    public function testCreateAPIErrorReturnsError()
    {
        $response = $this->pluginAPIClient->createAPIError('error Message');

        $this->assertFalse($response['success']);
    }

    public function testCallAPIReturnsError()
    {
        $response = $this->pluginAPIClient->callAPI($this->mockRequest);

        $this->assertFalse($response['success']);
    }

    public function testCreatePluginSettingObject()
    {
        $pluginSettingKey = 'key';
        $value = 'value';
        $editable = false;
        $modifiedOn = null;

        $expected = array(
            Plugin::SETTING_ID_KEY => $pluginSettingKey,
            Plugin::SETTING_VALUE_KEY => $value,
            Plugin::SETTING_EDITABLE_KEY => $editable,
            Plugin::SETTING_MODIFIED_DATE_KEY => $modifiedOn,
        );

        $result = $this->pluginAPIClient->createPluginSettingObject($pluginSettingKey, $value, $editable, $modifiedOn);

        $this->assertEquals($expected, $result);
    }

    public function testCreatePluginSettingObjectReturnsISO8061DateForNonNullValue()
    {
        $result = $this->pluginAPIClient->createPluginSettingObject(null, null, null, true);
        //DateTime() will throw an exception if $result['modified_on'] isn't a valid date
        $this->assertInstanceOf('\DateTime', new \DateTime($result['modified_on']));
    }
}
