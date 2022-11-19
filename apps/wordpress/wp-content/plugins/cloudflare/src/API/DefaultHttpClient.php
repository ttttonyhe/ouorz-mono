<?php

namespace CF\API;

use CF\API\Request;

class DefaultHttpClient implements HttpClientInterface
{
    const CONTENT_TYPE_KEY = 'Content-Type';
    const APPLICATION_JSON_KEY = 'application/json';

    protected $endpoint;

    /**
     * @param String $endpoint
     */
    public function __construct($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @param  Request $request
     * @throws \Exception
     * @return Array $response
     */
    public function send(Request $request)
    {
        $requestOptions = $this->createRequestOptions($request);
        $url = $this->createRequestUrl($request);

        $response = wp_remote_request($url, $requestOptions);

        if (is_wp_error($response)) {
            throw new \Exception('Request error', $response->get_error_code);
        }

        $response_body = json_decode($response['body']);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error decoding client API JSON', json_last_error());
        }

        return $response_body;
    }

    /**
     * @param  Request $request
     * @return array $requestOptions
     */
    public function createRequestOptions(Request $request)
    {
        $requestOptions = array(
            'method' => $request->getMethod(),
            'headers' => $request->getHeaders(),
            'body' => $request->getBody(),
        );

        return $requestOptions;
    }

    /**
     * @param  Request $request
     * @return string $url
     */
    public function createRequestUrl(Request $request)
    {
        $url = $this->endpoint . $request->getUrl();
        foreach ($request->getParameters() as $key => $value) {
            $url = add_query_arg($key, $value, $url);
        }

        return $url;
    }
}
