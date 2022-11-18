<?php

namespace CF\WordPress\Test;

use CF\Integration\DefaultIntegration;
use CF\WordPress\Constants\Plans;
use CF\WordPress\PluginActions;
use phpmock\phpunit\PHPMock;

class PluginActionsTest extends \PHPUnit\Framework\TestCase
{
    use PHPMock;

    private $mockConfig;
    private $mockDataStore;
    private $mockDefaultIntegration;
    private $mockGetAdminUrl;
    private $mockLogger;
    private $mockPluginAPIClient;
    private $mockWordPressAPI;
    private $mockWordPressClientAPI;
    private $mockWPLoginUrl;
    private $mockRequest;
    private $pluginActions;

    public function setup(): void
    {
        $this->mockConfig = $this->getMockBuilder('CF\Integration\DefaultConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockDataStore = $this->getMockBuilder('CF\WordPress\DataStore')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockGetAdminUrl = $this->getFunctionMock('CF\WordPress', 'get_admin_url');
        $this->mockLogger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockPluginAPIClient = $this->getMockBuilder('CF\API\Plugin')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockWordPressAPI = $this->getMockBuilder('CF\WordPress\WordPressAPI')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockWordPressClientAPI = $this->getMockBuilder('CF\WordPress\WordPressClientAPI')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockWPLoginUrl = $this->getFunctionMock('CF\WordPress', 'wp_login_url');
        $this->mockRequest = $this->getMockBuilder('CF\API\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockDefaultIntegration = new DefaultIntegration($this->mockConfig, $this->mockWordPressAPI, $this->mockDataStore, $this->mockLogger);
        $this->pluginActions = new PluginActions($this->mockDefaultIntegration, $this->mockPluginAPIClient, $this->mockRequest);
        $this->pluginActions->setClientAPI($this->mockWordPressClientAPI);
    }

    public function testReturnApplyDefaultSettingsWithZoneWithPlanBIZ()
    {
        $this->mockRequest->method('getUrl')->willReturn('/plugin/:id/settings/default_settings');

        $this->mockWordPressClientAPI->method('zoneGetDetails')->willReturn(
            array(
                'result' => array(
                    'plan' => array(
                        'legacy_id' => Plans::BIZ_PLAN,
                    ),
                ),
            )
        );
        $this->mockWordPressClientAPI->method('responseOk')->willReturn(true);
        $this->mockWordPressClientAPI->method('changeZoneSettings')->willReturn(true);
        $this->mockWordPressClientAPI->expects($this->exactly(16))->method('changeZoneSettings');

        $this->pluginActions->applyDefaultSettings();
    }

    public function testReturnApplyDefaultSettingsWithZoneWithFreePlan()
    {
        $this->mockRequest->method('getUrl')->willReturn('/plugin/:id/settings/default_settings');

        $this->mockWordPressClientAPI->method('zoneGetDetails')->willReturn(
            array(
                'result' => array(
                    'plan' => array(
                        'legacy_id' => Plans::FREE_PLAN,
                    ),
                ),
            )
        );
        $this->mockWordPressClientAPI->method('responseOk')->willReturn(true);
        $this->mockWordPressClientAPI->method('changeZoneSettings')->willReturn(true);
        $this->mockWordPressClientAPI->expects($this->exactly(14))->method('changeZoneSettings');

        $this->pluginActions->applyDefaultSettings();
    }

    public function testReturnApplyDefaultSettingsZoneDetailsThrowsZoneSettingFailException()
    {
        $this->expectException('\CF\API\Exception\ZoneSettingFailException');

        $this->mockRequest->method('getUrl')->willReturn('/plugin/:id/settings/default_settings');

        $this->mockWordPressClientAPI->method('zoneGetDetails')->willReturn(false);

        $this->pluginActions->applyDefaultSettings();
    }

    public function testReturnApplyDefaultSettingsChangeZoneSettingsThrowsZoneSettingFailException()
    {
        $this->expectException('\CF\API\Exception\ZoneSettingFailException');

        $this->mockRequest->method('getUrl')->willReturn('/plugin/:id/settings/default_settings');

        $this->mockWordPressClientAPI->method('zoneGetDetails')->willReturn(false);

        $this->mockWordPressClientAPI->method('zoneGetDetails')->willReturn(true);
        $this->mockWordPressClientAPI->method('responseOk')->willReturn(true);
        $this->mockWordPressClientAPI->method('changeZoneSettings')->willReturn(false);


        $this->pluginActions->applyDefaultSettings();
    }
}
