<?php

namespace CF\API;

interface APIInterface
{
    public function callAPI(Request $request);
    public function createAPIError($message);
    public function responseOk($response);
    public function getEndpoint();
}
