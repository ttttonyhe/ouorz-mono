<?php

namespace CF\Test\WordPress {

    use CF\WordPress\DataStore;
    use CF\API\Plugin;

    class DataStoreTest extends \PHPUnit\Framework\TestCase
    {
        protected $dataStore;
        protected $mockLogger;
        protected $mockWordPressWrapper;

        public function setup(): void
        {
            $this->mockLogger = $this->getMockBuilder('CF\Integration\DefaultLogger')
                ->disableOriginalConstructor()
                ->getMock();

            $this->mockWordPressWrapper = $this->getMockBuilder('CF\WordPress\WordPressWrapper')
                ->disableOriginalConstructor()
                ->getMock();

            $this->dataStore = new DataStore($this->mockLogger);
            $this->dataStore->setWordPressWrapper($this->mockWordPressWrapper);
        }

        public function testCreateUserDataStoreSavesAPIKeyAndEmail()
        {
            $apiKey = 'apiKey';
            $email = 'email';

            $this->mockWordPressWrapper->expects($this->exactly(4))->method('updateOption')->willReturn(true);

            $this->assertTrue($this->dataStore->createUserDataStore($apiKey, $email, null, null));
        }

        public function testGetHostAPIUserUniqueIdReturnsNull()
        {
            $this->assertNull($this->dataStore->getHostAPIUserUniqueId());
        }

        public function testGetClientV4APIKeyReturnsCorrectValue()
        {
            $apiKey = 'apiKey';

            $this->mockWordPressWrapper->expects($this->once())->method('getOption')->with(DataStore::API_KEY)->willReturn($apiKey);
            $this->assertEquals($apiKey, $this->dataStore->getClientV4APIKey());
        }

        public function testGetHostAPIUserKeyReturnsNull()
        {
            $this->assertNull($this->dataStore->getHostAPIUserKey());
        }

        public function testGetDomainNameCacheReturnsDomainIfItExistsInCache()
        {
            $cachedDomain = 'cachedDomain';

            $this->mockWordPressWrapper->expects($this->once())->method('getOption')->with(DataStore::CACHED_DOMAIN_NAME)->willReturn($cachedDomain);

            $this->assertEquals($cachedDomain, $this->dataStore->getDomainNameCache());
        }

        public function testSetDomainNameCacheSetsDomain()
        {
            $domain = 'domain.com';

            $this->mockWordPressWrapper->expects($this->once())->method('updateOption')->willReturn(true);

            $this->assertTrue($this->dataStore->setDomainNameCache($domain));
        }

        public function testGetCloudFlareEmailReturnsCorrectValue()
        {
            $email = 'email';

            $this->mockWordPressWrapper->expects($this->once())->method('getOption')->with(DataStore::EMAIL)->willReturn($email);

            $this->assertEquals($email, $this->dataStore->getCloudFlareEmail());
        }

        public function testGetPluginSettingCallsGetOption()
        {
            $value = 'value';

            $this->mockWordPressWrapper->expects($this->once())->method('getOption')->with(Plugin::SETTING_DEFAULT_SETTINGS)->willReturn($value);

            $this->assertEquals($value, $this->dataStore->getPluginSetting(Plugin::SETTING_DEFAULT_SETTINGS));
        }

        public function testGetCallsEscSqlAndGetOption()
        {
            $value = 'value';
            $key = 'key';

            $this->mockWordPressWrapper->expects($this->once())->method('getOption')->with($key)->willReturn($value);

            $this->assertEquals($value, $this->dataStore->get($key));
        }

        public function testSetCallsEscSqlAndUpdateOption()
        {
            $value = 'value';
            $key = 'key';

            $this->mockWordPressWrapper->expects($this->once())->method('updateOption')->with($key, $value)->willReturn(true);

            $this->assertTrue($this->dataStore->set($key, $value));
        }

        public function testClearCallsSqlAndDeleteToption()
        {
            $key = 'key';

            $this->mockWordPressWrapper->expects($this->once())->method('deleteOption')->with($key);

            $this->dataStore->clear('key');
        }

        public function testClearDataStoreCallsExactNumberOfSqlCalls()
        {
            $pluginSettings = \CF\API\Plugin::getPluginSettingsKeys();
            $numberOfDataStoreKeys = 3;
            $totalNumberOfRowToClear = count($pluginSettings) + $numberOfDataStoreKeys;

            $this->mockWordPressWrapper->expects($this->exactly($totalNumberOfRowToClear))->method('deleteOption');

            $this->dataStore->clearDataStore();
        }
    }
}
