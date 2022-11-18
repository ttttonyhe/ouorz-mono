<?php

namespace CF\API;

interface HttpClientInterface
{
    /**
     * @param  Request $request
     * @return Array   $response
     */
    public function send(Request $request);
}
