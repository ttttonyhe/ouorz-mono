<?php

namespace CF\WordPress;

class Utils
{
    const COMPOSER_CONFIG_PATH = '/../../composer.json';

    /**
     * @param $haystack
     * @param $needle
     *
     * @return bool
     */
    public static function strEndsWith($haystack, $needle)
    {
        if (empty($haystack) || empty($needle)) {
            return false;
        }
        $needle_len = strlen($needle);
        return ($needle_len === 0 || 0 === substr_compare($haystack, $needle, -$needle_len));
    }

    public static function isSubdomainOf($subDomainName, $domainName)
    {
        if (empty($subDomainName) || empty($domainName)) {
            return false;
        }

        // Check if strpos is a positive integer
        $dotPosition = strpos($subDomainName, $domainName) - 1;
        if ($dotPosition === -1) {
            return false;
        }

        return self::strEndsWith($subDomainName, $domainName) &&
            $subDomainName !== $domainName &&
            $subDomainName[$dotPosition] == '.';
    }

    public static function getRegistrableDomain($domainName)
    {
        // Remove characters up to the first "." character.
        // For example:
        // blog.domain.com -> domain.com
        // does not work with multiple subdomain
        // sub1.sub2.domain.com -> sub2.domain.com
        return preg_replace('/^[^.]*.\s*/', '', $domainName);
    }

    public static function getComposerJson(): array
    {
        if (!file_exists(dirname(__FILE__) . self::COMPOSER_CONFIG_PATH)) {
            return [];
        }
        return json_decode(file_get_contents(dirname(__FILE__) . self::COMPOSER_CONFIG_PATH), true);
    }
}
