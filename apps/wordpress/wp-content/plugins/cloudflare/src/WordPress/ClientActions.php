<?php

namespace CF\WordPress;

use CF\API\APIInterface;
use CF\API\Request;
use CF\Integration\DefaultIntegration;
use Symfony\Polyfill\Tests\Intl\Idn;

class ClientActions
{
    private $api;
    private $config;
    private $wordpressAPI;
    private $dataStore;
    private $logger;
    private $request;

    /**
     * @param DefaultIntegration $defaultIntegration
     * @param APIInterface       $api
     * @param Request            $request
     */
    public function __construct(DefaultIntegration $defaultIntegration, APIInterface $api, Request $request)
    {
        $this->api = $api;
        $this->config = $defaultIntegration->getConfig();
        $this->wordpressAPI = $defaultIntegration->getIntegrationAPI();
        $this->dataStore = $defaultIntegration->getDataStore();
        $this->logger = $defaultIntegration->getLogger();
        $this->request = $request;
    }

    /**
     * GET /zones.
     *
     * @return mixed
     */
    public function returnWordPressDomain()
    {
        // Call GET /zones
        $response = $this->api->callAPI($this->request);

        // We tried to fetch a zone but it's possible we're using an API token,
        // So try again with a zone name filtered API call
        if (!$this->api->responseOk($response)) {
            $zoneRequest = new Request('GET', 'zones/', array('name' => idn_to_ascii($this->wordpressAPI->getOriginalDomain(), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46), array()));
            $zoneResponse = $this->api->callAPI($zoneRequest);

            return $zoneResponse;
        }

        // Cache the domain for subdomains
        $this->cacheDomainName($response);

        // Get zone information
        $cfZonesList = $this->filterZones($response);

        return $cfZonesList;
    }

    private function filterZones($response)
    {
        $cfZonesList = $response;
        $wpDomainList = $this->wordpressAPI->getDomainList();
        $wpDomain = $wpDomainList[0];

        $domainList = array();
        if ($this->api->responseOk($cfZonesList)) {
            $found = false;
            foreach ($cfZonesList['result'] as $zone) {
                if (idn_to_ascii($zone['name'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) === idn_to_ascii($wpDomain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46)) {
                    $found = true;
                    array_push($domainList, $zone);
                }
            }

            if ($found === false) {
                array_push($domainList, array(
                    'name' => $wpDomain,
                    'plan' => array('name' => ''),
                    'type' => '',
                    'status' => 'inactive',
                ));
            }
        }
        $cfZonesList['result'] = $domainList;

        return $cfZonesList;
    }

    public function cacheDomainName($response)
    {
        // Check if domain name needs to be cached
        $wpDomain = $this->wordpressAPI->getOriginalDomain();
        $cachedDomainList = $this->wordpressAPI->getDomainList();
        $cachedDomain = isset($cachedDomainList[0]) ? $cachedDomainList[0] : '';

        if (Utils::getRegistrableDomain($wpDomain) !== $cachedDomain) {
            // If it's not a subdomain cache the current domain
            $domainName = $wpDomain;

            // Get cloudflare zones to find if the current domain is a
            // subdomain of any cloudflare zones registered
            $validDomainName = $this->wordpressAPI->checkIfValidCloudflareSubdomain($response, $wpDomain);

            // Check if it's a subdomain, if it is cache the zone instead of the
            // subdomain
            if ($this->api->responseOK($response) && $validDomainName) {
                $domainName = Utils::getRegistrableDomain($wpDomain);
            }

            $this->wordpressAPI->setDomainNameCache($domainName);

            // Log for debugging
            $this->logger->debug("Current domain -> $wpDomain");
            $this->logger->debug("Valid domain -> $validDomainName");
            $this->logger->debug("Cached domain -> $domainName");

            return $domainName;
        }

        return $cachedDomain;
    }
}
