<?php

namespace CloudFlare;

class IpRewrite
{
    private $is_loaded = false;
    private $original_ip = null;
    private $rewritten_ip = null;

    // Found at https://www.cloudflare.com/ips/
    private $cf_ipv4 = array(
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/12',
        '172.64.0.0/13',
        '131.0.72.0/22',
    );

    private $cf_ipv6 = array(
        '2400:cb00::/32',
        '2405:8100::/32',
        '2405:b500::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2c0f:f248::/32',
        '2a06:98c0::/29'
    );

    public function __construct()
    {
        $this->rewrite();
    }

    /**
     * Is a request from CloudFlare?
     * @return bool
     */
    public function isCloudFlare()
    {
        if (!isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return false;
        } else {
            // Check if original ip has already been restored, e.g. by nginx - assume it was from cloudflare then
            if ($_SERVER['REMOTE_ADDR'] === $_SERVER['HTTP_CF_CONNECTING_IP']) {
                return true;
            }
        }

        return $this->isCloudFlareIP();
    }

    /**
     * Check if a request comes from a CloudFlare IP.
     * @return bool
     */
    public function isCloudFlareIP()
    {
        // Store original remote address in $original_ip
        $this->original_ip = $this->getOriginalIP();
        if (!isset($this->original_ip)) {
            return false;
        }

        // Process original_ip if on cloudflare
        $ip_ranges = $this->cf_ipv4;
        if (IpUtils::isIpv6($this->original_ip)) {
            $ip_ranges = $this->cf_ipv6;
        }

        foreach ($ip_ranges as $range) {
            if (IpUtils::checkIp($this->original_ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the original IP Address of a given request.
     * @return IP Address or null on error
     */
    public function getOriginalIP()
    {
        // If $original_ip is not set, return the REMOTE_ADDR
        if (!isset($this->original_ip)) {
            $this->original_ip = $_SERVER['REMOTE_ADDR'];
        }

        return $this->original_ip;
    }

    /**
     * Gets the re-written IP after rewrite() is run.
     * @return IP Address or null on error
     */
    public function getRewrittenIP()
    {
        return $this->rewritten_ip;
    }

    /**
     * Handle the rewriting of CloudFlare IP Addresses to end-user IP Addresses.
     * NOTE: This function will ultimately rewrite $_SERVER["REMOTE_ADDR"] if the site is on CloudFlare
     * @return bool
     * @
     */
    public function rewrite()
    {
        // only should be run once per page load
        if ($this->is_loaded) {
            return false;
        }
        $this->is_loaded = true;

        $is_cf = $this->isCloudFlare();
        if (!$is_cf) {
            return false;
        }

        $this->rewritten_ip = $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
        return true;
    }
}
