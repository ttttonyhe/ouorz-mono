<?php

namespace CF\API;

class PluginRoutes
{
    public static $routes = array(
        'account' => array(
            'class' => 'CF\API\AbstractPluginActions',
            'methods' => array(
                'POST' => array(
                    'function' => 'login',
                ),
            ),
        ),

        'plugin/:id/settings' => array(
            'class' => 'CF\API\AbstractPluginActions',
            'methods' => array(
                'GET' => array(
                    'function' => 'getPluginSettings',
                ),
            ),
        ),

        'plugin/:id/settings/:human_readable_id' => array(
            'class' => 'CF\API\AbstractPluginActions',
            'methods' => array(
                'PATCH' => array(
                    'function' => 'patchPluginSettings',
                ),
            ),
        ),

        'config' => array(
            'class' => 'CF\API\AbstractPluginActions',
            'methods' => array(
                'GET' => array(
                    'function' => 'getConfig',
                ),
            ),
        ),
    );
}
