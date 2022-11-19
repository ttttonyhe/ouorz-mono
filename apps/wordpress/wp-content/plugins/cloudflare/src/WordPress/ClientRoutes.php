<?php

namespace CF\WordPress;

class ClientRoutes
{
    public static $routes = array(
        'zones' => array(
            'class' => 'CF\WordPress\ClientActions',
            'methods' => array(
                'GET' => array(
                    'function' => 'returnWordPressDomain',
                ),
            ),
        ),
    );
}
