<?php

namespace CF;

class SecurityUtil
{
    /**
     * @return bool|string
     */
    public static function generate16bytesOfSecureRandomData()
    {
        if (function_exists('random_bytes')) {
            $randBytes = random_bytes(16);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $wasItSecure = false;
            $randBytes = openssl_random_pseudo_bytes(16, $wasItSecure);
            if ($wasItSecure === false) {
                return false;
            }
        } else {
            return false;
        }

        return bin2hex($randBytes);
    }

    /**
     * @param $secret - string a cryptographically strong secret
     * @param $user - string a piece of unique user data
     * @param $timeValidUntil - int of time the token will be valid for in seconds
     *
     * @return string
     */
    public static function csrfTokenGenerate($secret, $user, $timeValidUntil = null)
    {
        if ($timeValidUntil === null) {
            $timeValidUntil = time() + 86400;
        }
        $hashedSecret = hash('sha512', $secret);
        $dataToHash = sprintf('%s-%s-%s', $hashedSecret, $user, $timeValidUntil);
        $hashedData = static::hashFunction($dataToHash);

        return sprintf('%s-%s', $timeValidUntil, $hashedData);
    }

    /**
     * @param $secret - string a cryptographically strong secret
     * @param $user - string a piece of unique user data
     * @param $token- string the token that needs to be validated.
     *
     * @return bool
     */
    public static function csrfTokenValidate($secret, $user, $token)
    {
        $tokenParts = explode('-', $token);
        if (count($tokenParts) !== 2) {
            return false;
        }

        list($timeValidFor, $hash) = $tokenParts;

        $hashedSecret = hash('sha512', $secret);
        $dataToHash = sprintf('%s-%s-%s', $hashedSecret, $user, $timeValidFor);
        $newHash = static::hashFunction($dataToHash);
        if ($newHash !== $hash) {
            return false;
        }
        if (time() > $timeValidFor) {
            return false;
        }

        return true;
    }

    /**
     * @param $data - string the data that will be hashed.
     *
     * @return string
     */
    private static function hashFunction($data)
    {
        $hash = hash('sha512', $data);

        return substr($hash, 64);
    }
}
