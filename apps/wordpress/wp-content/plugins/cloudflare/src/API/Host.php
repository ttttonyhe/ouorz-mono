<?php

namespace CF\API;

class Host extends AbstractAPIClient
{
    const CF_INTEGRATION_HEADER = 'CF-Integration';
    const CF_INTEGRTATION_VERSION_HEADER = 'CF-Integration-Version';
    const HOST_API_NAME = 'HOST API';
    //self::ENDPOINT_BASE_URL . self::ENDPOINT_PATH isn't a thing so you have to update it twice if it changes.
    const ENDPOINT_BASE_URL = 'https://api.cloudflare.com/';
    const ENDPOINT_PATH = 'host-gw.html';
    const ENDPOINT = 'https://api.cloudflare.com/host-gw.html';

    /**
     * @param Request $request
     *
     * @return Request
     */
    public function beforeSend(Request $request)
    {
        //Host API isn't restful so path must always self::ENDPOINT_PATH
        $request->setUrl(self::ENDPOINT_PATH);

        $headers = array(
            self::CF_INTEGRATION_HEADER => $this->config->getValue('integrationName'),
            self::CF_INTEGRTATION_VERSION_HEADER => $this->config->getValue('version'),
        );
        $request->setHeaders($headers);

        $body = $request->getBody();
        $user_key_actions = array('zone_set', 'full_zone_set');
        if (in_array(strtolower($body['act'] ?? ""), $user_key_actions)) {
            $body['user_key'] = $this->data_store->getHostAPIUserKey();
        }
        $body['host_key'] = $this->integrationAPI->getHostAPIKey();
        $request->setBody($body);

        return $request;
    }

    /**
     * @param $host_api_response
     *
     * @return bool
     */
    public function responseOk($host_api_response)
    {
        return $host_api_response['result'] === 'success';
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function getPath(Request $request)
    {
        return $request->getBody()['act'];
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return self::ENDPOINT;
    }

    /**
     * @return string
     */
    public function getAPIClientName()
    {
        return self::HOST_API_NAME;
    }

    /**
     * @param $message
     *
     * @return array
     */
    public function createAPIError($message)
    {
        return array(
            'request' => array(
                'act' => '',
            ),
            'result' => 'error',
            'msg' => $message,
            'err_code' => '',
        );
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function shouldRouteRequest(Request $request)
    {
        return $request->getUrl() === $this->getEndpoint();
    }
}
