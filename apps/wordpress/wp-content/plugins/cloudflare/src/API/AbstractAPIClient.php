<?php

namespace CF\API;

use CF\Integration\IntegrationInterface;
use CF\API\DefaultHttpClient;
use CF\API\HttpClientInterface;

abstract class AbstractAPIClient implements APIInterface
{
    const CONTENT_TYPE_KEY = 'Content-Type';
    const APPLICATION_JSON_KEY = 'application/json';

    protected $config;
    protected $data_store;
    protected $httpClient;
    protected $logger;
    protected $integrationAPI;

    /**
     * @param IntegrationInterface $integration
     */
    public function __construct(IntegrationInterface $integration)
    {
        $this->config = $integration->getConfig();
        $this->data_store = $integration->getDataStore();
        $this->logger = $integration->getLogger();
        $this->integrationAPI = $integration->getIntegrationAPI();
    }

    /**
     * @return HttpClientInterface $httpClient
     */
    public function getHttpClient()
    {
        if ($this->httpClient === null) {
            $this->httpClient = new DefaultHttpClient($this->getEndpoint());
        }

        return $this->httpClient;
    }
    /**
     * @param HttpClientInterface $httpClient
     */
    public function setHttpClient(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param Request $request
     *
     * @return array|mixed
     */
    public function callAPI(Request $request)
    {
        try {
            $request = $this->beforeSend($request);

            $response = $this->sendAndLog($request);

            $response = $this->getPaginatedResults($request, $response);

            return $response;
        } catch (\Exception $e) {
            $errorMessage = $this->getErrorMessage($e);

            $this->logAPICall($this->getAPIClientName(), array(
                'type' => 'request',
                'method' => $request->getMethod(),
                'path' => $request->getUrl(),
                'headers' => $request->getHeaders(),
                'params' => $request->getParameters(),
                'body' => $request->getBody(), ), true);
            $this->logAPICall($this->getAPIClientName(), array('type' => 'response', 'code' => $e->getCode(), 'body' => $errorMessage, 'stacktrace' => $e->getTraceAsString()), true);

            return $this->createAPIError($errorMessage);
        }
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
        $currentPage = 2; //$response already contains page 1
        $totalPages = $response['result_info']['total_pages'];

        while ($totalPages >= $currentPage) {
            $parameters = $request->getParameters();
            $parameters['page'] = $currentPage;
            $request->setParameters($parameters);

            $pagedResponse = $this->sendAndLog($request);

            $mergedResponse['result'] = array_merge($mergedResponse['result'], $pagedResponse['result']);
            // Notify the frontend that pagination is taken care.
            $mergedResponse['result_info']['notify'] = 'Backend has taken care of pagination. Ouput is merged in results.';
            $mergedResponse['result_info']['page'] = -1;
            $mergedResponse['result_info']['count'] = -1;

            $currentPage++;
        }
        return $mergedResponse;
    }

    /**
     * @param  Request $request
     * @return Array $response
     */
    public function sendAndLog(Request $request)
    {
        $response = $this->getHttpClient()->send($request);

        if (!$this->responseOk($response)) {
            $this->logAPICall($this->getAPIClientName(), array('type' => 'response', 'body' => $response), true);
        }

        return $response;
    }

    /**
     * @param  $error
     *
     * @return string
     */
    public function getErrorMessage($error)
    {
        return $error->getMessage();
    }

    /**
     * @param string $apiName
     * @param array  $message
     * @param bool   $isError
     */
    public function logAPICall($apiName, $message, $isError)
    {
        $sensitiveHeaderKeys = array(
            'Authorization',
            'X-Auth-Email',
            'X-Auth-Key'
        );

        $logLevel = 'error';
        if ($isError === false) {
            $logLevel = 'debug';
        }
        if (!is_string($message)) {
            foreach ($sensitiveHeaderKeys as $value) {
                if (!empty($message['headers'][$value])) {
                    $message['headers'][$value] = 'REDACTED';
                }
            }

            $message = print_r($message, true);
        }
        $this->logger->$logLevel('['.$apiName.'] '.$message);
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function getPath(Request $request)
    {
        //substring of everything after the endpoint is the path
        return substr($request->getUrl(), strpos($request->getUrl(), $this->getEndpoint()) + strlen($this->getEndpoint()));
    }

    public function shouldRouteRequest(Request $request)
    {
        return strpos($request->getUrl(), $this->getEndpoint()) !== false;
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    abstract public function beforeSend(Request $request);

    /**
     * @return mixed
     */
    abstract public function getAPIClientName();

    /**
     * @param $message
     *
     * @return array
     */
    abstract public function createAPIError($message);

    /**
     * @return mixed
     */
    abstract public function getEndpoint();
}
