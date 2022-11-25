<?php

namespace CF\WordPress;

use Symfony\Polyfill\Tests\Intl\Idn;

class WordPressWrapper
{
    public function getOption($key, $default)
    {
        return get_option($key, $default);
    }

    public function updateOption($key, $value)
    {
        return update_option($key, $value);
    }

    public function deleteOption($key)
    {
        return delete_option($key);
    }

    public function getSiteURL()
    {
        if (defined("CLOUDFLARE_DOMAIN") && CLOUDFLARE_DOMAIN != "") {
            $site_url = idn_to_ascii(CLOUDFLARE_DOMAIN, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        } else {
            $site_url = get_site_url();
        }

        if (function_exists('domain_mapping_siteurl')) {
            $site_url = domain_mapping_siteurl($site_url);
        }

        return strtolower($site_url);
    }
}
