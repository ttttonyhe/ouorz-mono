<?php

namespace CF\WordPress;

use CF\API\Client;
use CF\API\Request;
use Symfony\Polyfill\Tests\Intl\Idn;

class WordPressClientAPI extends Client
{
    /**
     * @param $zone_name
     *
     * @return mixed
     */
    public function getZoneTag($zone_name)
    {
        $zone_name = idn_to_ascii($zone_name, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

        $zone_tag = wp_cache_get('cloudflare/client-api/zone-tag/' . $zone_name);
        if (false !== $zone_tag) {
            return $zone_tag;
        }

        $request = new Request('GET', 'zones/', array('name' => $zone_name), array());
        $response = $this->callAPI($request);

        $zone_tag = null;
        if ($this->responseOk($response)) {
            foreach ($response['result'] as $zone) {
                if (idn_to_ascii($zone['name'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) === idn_to_ascii($zone_name, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46)) {
                    $zone_tag = $zone['id'];
                    break;
                }
            }
        }

        wp_cache_set('cloudflare/client-api/zone-tag/' . $zone_name, $zone_tag);

        return $zone_tag;
    }

    /**
     * @param $zoneId
     *
     * @return bool
     */
    public function zonePurgeCache($zoneId)
    {
        $request = new Request('DELETE', 'zones/' . $zoneId . '/purge_cache', array(), array('purge_everything' => true));
        $response = $this->callAPI($request);

        return $this->responseOk($response);
    }

    /**
     * @param $zoneId
     * @param $files
     *
     * @return bool
     */
    public function zonePurgeFiles($zoneId, $files)
    {
        $request = new Request('DELETE', 'zones/' . $zoneId . '/purge_cache', array(), array('files' => $files));
        $response = $this->callAPI($request);

        return $this->responseOk($response);
    }

    /**
     * @param $zoneId
     * @param $settingName
     * @param $params
     *
     * @return bool
     */
    public function changeZoneSettings($zoneId, $settingName, $params)
    {
        $request = new Request('PATCH', 'zones/' . $zoneId . '/settings/' . $settingName, array(), $params);
        $response = $this->callAPI($request);

        return $this->responseOk($response);
    }

    /**
     * @param $zoneId
     * @param $settingName
     * @param $params
     *
     * @return bool
     */
    public function getZoneSetting($zoneId, $settingName)
    {
        $request = new Request('GET', 'zones/' . $zoneId . '/settings/' . $settingName, array(), null);
        $response = $this->callAPI($request);

        return $response["result"];
    }

    /**
     * @param $urlPattern
     *
     * @return array
     */
    public function createPageRule($zoneId, $body)
    {
        $request = new Request('POST', 'zones/' . $zoneId . '/pagerules/', array(), $body);
        $response = $this->callAPI($request);

        return $this->responseOk($response);
    }

    /**
     * Returns all page rules in the desired state. Defaults to active.
     *
     * @param mixed $zoneId
     * @param string $status
     * @return array
     */
    public function getPageRules($zoneId, $status = "active")
    {
        $request = new Request('GET', 'zones/' . $zoneId . "/pagerules?status=$status", array(), null);
        $response = $this->callAPI($request);

        if ($this->responseOk($response)) {
            return $response["result"];
        }

        return [];
    }

    /**
     * @param Request $request
     *
     * @return array|mixed
     */
    public function callAPI(Request $request)
    {
        $request = $this->beforeSend($request);
        $response = $this->sendRequest($request);
        $response = $this->getPaginatedResults($request, $response);

        return $response;
    }


    /**
     * @param  Request $request
     * @return [Array] $response
     */
    public function sendRequest(Request $request)
    {
        $requestParams = array(
            'timeout' => 30,
            'method' => $request->getMethod(),
            'headers' => $request->getHeaders(),
        );

        if ($requestParams['method'] !== 'GET') {
            $requestParams['body'] = json_encode($request->getBody());
            $requestParams['headers']['Content-Type'] = 'application/json';
        }

        // Construct URL
        $url = add_query_arg($request->getParameters(), $this->getEndpoint() . $request->getUrl());

        // Send Request
        $requestResponse = wp_remote_request($url, $requestParams);

        // Check for connection error
        if (is_wp_error($requestResponse)) {
            $errorMessage = $requestResponse->get_error_message();

            $this->logAPICall($this->getAPIClientName(), array_merge(array('type' => 'request', 'path' => $url), $requestParams), true);
            $this->logAPICall($this->getAPIClientName(), array('type' => 'response', 'reason' => $requestResponse->get_error_message(), 'code' => $requestResponse->get_error_code(), 'body' => $errorMessage), true);

            return $this->createAPIError($errorMessage);
        }

        // Check for response error != 2XX
        if (wp_remote_retrieve_response_code($requestResponse) > 299) {
            $errorMessage = wp_remote_retrieve_response_message($requestResponse);

            $this->logAPICall($this->getAPIClientName(), array_merge(array('type' => 'request', 'path' => $url), $requestParams), true);
            $this->logAPICall($this->getAPIClientName(), array('type' => 'response', 'reason' => $errorMessage, 'code' => wp_remote_retrieve_response_code($requestResponse)), true);

            return $this->createAPIError($errorMessage);
        }

        // Decode request to JSON
        $response = json_decode(wp_remote_retrieve_body($requestResponse), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMessage = 'Error decoding client API JSON';
            $this->logAPICall($errorMessage, array('error' => json_last_error()), true);

            return $this->createAPIError($errorMessage);
        }

        if (!$this->responseOk($response)) {
            $this->logAPICall($this->getAPIClientName(), array('type' => 'response', 'body' => $response), true);
        }

        return $response;
    }

    /**
     * @param  Request $request
     * @param  [Array] $response
     * @return [Array] $paginatedResponse
     */
    public function getPaginatedResults(Request $request, $response)
    {
        if (strtoupper($request->getMethod()) !== 'GET' || !isset($response['result_info']['total_pages'])) {
            return $response;
        }
        $mergedResponse = $response;
        $currentPage = 2; // $response already contains page 1
        $totalPages = $response['result_info']['total_pages'];
        while ($totalPages >= $currentPage) {
            $parameters = $request->getParameters();
            $parameters['page'] = $currentPage;
            $request->setParameters($parameters);
            $pagedResponse = $this->sendRequest($request);
            $mergedResponse['result'] = array_merge($mergedResponse['result'], $pagedResponse['result']);

            // Notify the frontend that pagination is taken care.
            $mergedResponse['result_info']['notify'] = 'Backend has taken care of pagination. Output is merged in results.';
            $mergedResponse['result_info']['page'] = -1;
            $mergedResponse['result_info']['count'] = -1;
            $currentPage++;
        }
        return $mergedResponse;
    }
}
