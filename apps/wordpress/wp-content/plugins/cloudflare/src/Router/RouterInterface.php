<?php

namespace CF\Router;

use CF\API\Request;

interface RouterInterface
{
    public function route(Request $request);
    public function getAPIClient();
}
