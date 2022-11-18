<?php

use CloudFlare\IpRewrite;

class RewriteTest extends \PHPUnit_Framework_TestCase
{
    private $ipRewrite = null;

    public function tearDown()
    {
        unset($_SERVER['HTTP_CF_CONNECTING_IP']);
        unset($_SERVER['REMOTE_ADDR']);
    }

    public function testOnCloudFlareIPv4()
    {
        $remote_addr = '103.21.244.2';
        $connecting_ip = '8.8.8.8';

        $_SERVER['REMOTE_ADDR'] = $remote_addr;
        $_SERVER['HTTP_CF_CONNECTING_IP'] = $connecting_ip;

        $this->ipRewrite = new IpRewrite();

        $this->assertTrue($this->ipRewrite->isCloudFlare());
        $this->assertTrue($this->ipRewrite->isCloudFlareIP());
        $this->assertEquals($this->ipRewrite->getRewrittenIP(), $connecting_ip);
        $this->assertEquals($this->ipRewrite->getOriginalIP(), $remote_addr);
    }


    public function testRequestFromCloudFlareNoConnectingIPHeader()
    {
        $remote_addr = '103.21.244.2';

        $_SERVER['REMOTE_ADDR'] = $remote_addr;

        $this->ipRewrite = new IpRewrite();

        $this->assertFalse($this->ipRewrite->isCloudFlare());
        $this->assertTrue($this->ipRewrite->isCloudFlareIP());
        $this->assertNull($this->ipRewrite->getRewrittenIP());
        $this->assertEquals($this->ipRewrite->getOriginalIP(), $remote_addr);
    }

    public function testOffCloudFlareIPv4()
    {
        $remote_addr = '8.8.8.8';

        $_SERVER['REMOTE_ADDR'] = $remote_addr;

        $this->ipRewrite = new IpRewrite();

        $this->assertFalse($this->ipRewrite->isCloudFlare());
        $this->assertFalse($this->ipRewrite->isCloudFlareIP());
        $this->assertNull($this->ipRewrite->getRewrittenIP());
        $this->assertEquals($this->ipRewrite->getOriginalIP(), $remote_addr);
    }

    public function testOffCloudFlareIPv4FakeModCloudflare()
    {
        $remote_addr = '8.8.8.8';
        $connecting_ip = '8.8.4.4';

        $_SERVER['REMOTE_ADDR'] = $remote_addr;
        $_SERVER['HTTP_CF_CONNECTING_IP'] = $connecting_ip;

        $this->ipRewrite = new IpRewrite();

        $this->assertFalse($this->ipRewrite->isCloudFlare());
        $this->assertFalse($this->ipRewrite->isCloudFlareIP());
        $this->assertNull($this->ipRewrite->getRewrittenIP());
        $this->assertEquals($this->ipRewrite->getOriginalIP(), $remote_addr);
    }

    public function testOnlyProcessOnce()
    {
        $remote_addr = '108.162.192.2';
        $connecting_ip = '8.8.8.8';

        $_SERVER['REMOTE_ADDR'] = $remote_addr;
        $_SERVER['HTTP_CF_CONNECTING_IP'] = $connecting_ip;

        $this->ipRewrite = new IpRewrite();

        $this->assertTrue($this->ipRewrite->isCloudFlare());
        $this->assertTrue($this->ipRewrite->isCloudFlareIP());
        $this->assertEquals($this->ipRewrite->getRewrittenIP(), $connecting_ip);
        $this->assertEquals($this->ipRewrite->getOriginalIP(), $remote_addr);

        // swap values and expect the original still, since it only allows one run per load
        $remote_addr2 = '103.21.244.2';
        $connecting_ip2 = '8.8.4.4';

        $_SERVER['REMOTE_ADDR'] = $remote_addr2;
        $_SERVER['HTTP_CF_CONNECTING_IP'] = $connecting_ip2;

        $this->assertTrue($this->ipRewrite->isCloudFlare());
        $this->assertEquals($this->ipRewrite->getRewrittenIP(), $connecting_ip);
        $this->assertEquals($this->ipRewrite->getOriginalIP(), $remote_addr);
    }

    public function testOnCloudFlareIPv6()
    {
        $remote_addr = '2803:f800::23';
        $connecting_ip = '2001:4860:4860::8888';

        $_SERVER['REMOTE_ADDR'] = $remote_addr;
        $_SERVER['HTTP_CF_CONNECTING_IP'] = $connecting_ip;

        $this->ipRewrite = new IpRewrite();

        $this->assertTrue($this->ipRewrite->isCloudFlare());
        $this->assertTrue($this->ipRewrite->isCloudFlareIP());
        $this->assertEquals($this->ipRewrite->getRewrittenIP(), $connecting_ip);
        $this->assertEquals($this->ipRewrite->getOriginalIP(), $remote_addr);
    }

    public function testOffCloudFlareIPv6()
    {
        $remote_addr = '2001:4860:4860::8888';

        $_SERVER['REMOTE_ADDR'] = $remote_addr;

        $this->ipRewrite = new IpRewrite();

        $this->assertFalse($this->ipRewrite->isCloudFlare());
        $this->assertFalse($this->ipRewrite->isCloudFlare());
        $this->assertNull($this->ipRewrite->getRewrittenIP());
        $this->assertEquals($this->ipRewrite->getOriginalIP(), $remote_addr);
    }

    public function testRequestFromCloudflareNginxRealIp()
    {
        $connecting_ip = '8.8.8.8';

        // REMOTE_ADDR already rewritten by Nginx
        $_SERVER['REMOTE_ADDR'] = $connecting_ip;
        $_SERVER['HTTP_CF_CONNECTING_IP'] = $connecting_ip;

        $this->ipRewrite = new IpRewrite();

        $this->assertTrue($this->ipRewrite->isCloudFlare());
        $this->assertEquals($this->ipRewrite->getRewrittenIP(), $connecting_ip);
        $this->assertEquals($this->ipRewrite->getOriginalIP(), $connecting_ip);
    }
}
