<?php

use CloudFlare\IpUtils;

class IpUtilsTest extends PHPUnit_Framework_TestCase
{
    public function testIpv4()
    {
        $addr = '192.168.1.1';

        $this->assertTrue(IpUtils::checkIp($addr, '192.168.1.1'));
        $this->assertTrue(IpUtils::checkIp($addr, '192.168.1.1/1'));
        $this->assertTrue(IpUtils::checkIp($addr, '192.168.1.0/24'));
        $this->assertFalse(IpUtils::checkIp($addr, '1.2.3.4/1'));
        $this->assertFalse(IpUtils::checkIp($addr, '192.168.1.1/33')); // invalid subnet
        $this->assertTrue(IpUtils::checkIp($addr, array('1.2.3.4/1', '192.168.1.0/24')));
        $this->assertTrue(IpUtils::checkIp($addr, array('192.168.1.0/24', '1.2.3.4/1')));
        $this->assertFalse(IpUtils::checkIp($addr, array('1.2.3.4/1', '4.3.2.1/1')));
        $this->assertTrue(IpUtils::checkIp($addr,  '0.0.0.0/0'));
        $this->assertTrue(IpUtils::checkIp($addr, '192.168.1.0/0'));
        $this->assertFalse(IpUtils::checkIp($addr, '256.256.256/0')); // invalid CIDR notation
    }

    public function testIpv6()
    {
        if (!defined('AF_INET6')) {
            $this->markTestSkipped('Only works when PHP is compiled without the option "disable-ipv6".');
        }

        $this->assertTrue(IpUtils::checkIp('2a01:198:603:0:396e:4789:8e99:890f', '2a01:198:603:0::/65'));
        $this->assertFalse(IpUtils::checkIp('2a00:198:603:0:396e:4789:8e99:890f', '2a01:198:603:0::/65'));
        $this->assertFalse(IpUtils::checkIp('2a01:198:603:0:396e:4789:8e99:890f', '::1'));
        $this->assertTrue(IpUtils::checkIp('2a01:198:603:0:396e:4789:8e99:890f', array('::1', '2a01:198:603:0::/65')));
        $this->assertTrue(IpUtils::checkIp('2a01:198:603:0:396e:4789:8e99:890f', array('2a01:198:603:0::/65', '::1')));
        $this->assertFalse(IpUtils::checkIp('2a01:198:603:0:396e:4789:8e99:890f', array('::1', '1a01:198:603:0::/65')));
        $this->assertFalse(IpUtils::checkIp('2a01:198:603:0:396e:4789:8e99:890f', 'unknown'));
        $this->assertTrue(IpUtils::checkIp('0:0:0:0:0:0:0:1', '::1'));
        $this->assertFalse(IpUtils::checkIp('0:0:603:0:396e:4789:8e99:0001', '::1'));
        $this->assertFalse(IpUtils::checkIp('}__test|O:21:&quot;JDatabaseDriverMysqli&quot;:3:{s:2', '::1'));
        IpUtils::checkIp('2a01:198:603:0:396e:4789:8e99:890f', '2a01:198:603:0::/65');
    }
}
