<?php

namespace CF\API;

use CF\Integration\IntegrationInterface;
use CF\WordPress\Utils;

class Client extends AbstractAPIClient
{
    const CLIENT_API_NAME = 'CLIENT API';
    const ENDPOINT = 'https://api.cloudflare.com/client/v4/';
    const X_AUTH_KEY = 'X-Auth-Key';
    const X_AUTH_EMAIL = 'X-Auth-Email';
    const AUTHORIZATION = 'Authorization';
    const AUTH_KEY_LEN = 37;
    const USER_AGENT = 'User-Agent';

    /**
     * @param Request $request
     *
     * @return Request
     */
    public function beforeSend(Request $request)
    {
        $key = $this->data_store->getClientV4APIKey();
        $headers = array(
            self::CONTENT_TYPE_KEY => self::APPLICATION_JSON_KEY,
        );

        $composer = Utils::getComposerJson();
        $version = $composer['version'] ?? 'unknown';
        $wp_version = $GLOBALS['wp_version'] ?? 'unknown';
        $headers[self::USER_AGENT] =  'wordpress/' . $wp_version . '; cloudflare-wordpress-plugin/' . $version;

        // Determine authentication method from key format. Global API keys are
        // always returned in hexadecimal format, while API Tokens are encoded
        // using a wider range of characters.
        if (strlen($key) === self::AUTH_KEY_LEN && preg_match('/^[0-9a-f]+$/', $key)) {
            $headers[self::X_AUTH_EMAIL] = $this->data_store->getCloudFlareEmail();
            $headers[self::X_AUTH_KEY] = $key;
        } else {
            $headers[self::AUTHORIZATION] = "Bearer {$key}";
        }

        $request->setHeaders($headers);

        // Remove cfCSRFToken (a custom header) to save bandwidth
        $body = $request->getBody();
        unset($body['cfCSRFToken']);
        $request->setBody($body);

        return $request;
    }

    /**
     * @param $message
     *
     * @return array
     */
    public function createAPIError($message)
    {
        $this->logger->error($message);

        return array(
            'result' => null,
            'success' => false,
            'errors' => array(
                array(
                    'code' => '',
                    'message' => $message,
                ),
            ),
            'messages' => array(),
        );
    }

    /**
     * @param error
     *
     * @return string
     */
    public function getErrorMessage($error)
    {
        $jsonResponse = json_decode($error->getResponse()->getBody(), true);
        $errorMessage = $error->getMessage();

        if (count($jsonResponse['errors']) > 0) {
            $errorMessage = $jsonResponse['errors'][0]['message'];
        }

        return $errorMessage;
    }

    /**
     * @param $response
     *
     * @return bool
     */
    public function responseOk($response)
    {
        return isset($response['success']) ? $response['success'] : false;
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
        return self::CLIENT_API_NAME;
    }

    /**
     * GET /zones/:id.
     *
     * @param $zone_tag
     *
     * @return string
     */
    public function zoneGetDetails($zone_tag)
    {
        $request = new Request('GET', 'zones/'.$zone_tag, array(), array());

        return $this->callAPI($request);
    }
}
