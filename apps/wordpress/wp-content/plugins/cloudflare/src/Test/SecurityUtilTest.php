<?php

namespace CF;

class SecurityUtilTest extends \PHPUnit\Framework\TestCase
{
    public function testCSRFTokenValidateReturnsTrueForValidToken()
    {
        $secret = 'secret';
        $user = 'user';
        $token = SecurityUtil::csrfTokenGenerate($secret, $user);
        $this->assertTrue(SecurityUtil::csrfTokenValidate($secret, $user, $token));
    }

    public function testCSRFTokenValidateReturnsFalseForInvalidToken()
    {
        $secret = 'secret';
        $user = 'user';
        $timeValidUntil = time() + 86400;
        $token = SecurityUtil::csrfTokenGenerate($secret, $user, $timeValidUntil);
        $this->assertFalse(SecurityUtil::csrfTokenValidate('bad secret', $user, $token));
    }

    public function testCSRFTokenValidateReturnsFalseForExpiredToken()
    {
        $secret = 'secret';
        $user = 'user';
        $timeValidUntil = time() - 1;
        $token = SecurityUtil::csrfTokenGenerate($secret, $user, $timeValidUntil);
        $this->assertFalse(SecurityUtil::csrfTokenValidate($secret, $user, $token));
    }
}
