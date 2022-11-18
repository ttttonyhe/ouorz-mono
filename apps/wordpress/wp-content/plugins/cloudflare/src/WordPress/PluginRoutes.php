<?php

namespace CF\WordPress;

class PluginRoutes extends \CF\API\PluginRoutes
{
    /**
     * @param $routeList
     *
     * @return mixed
     */
    public static function getRoutes($routeList)
    {
        foreach ($routeList as $routePath => $route) {
            $route['class'] = '\CF\WordPress\PluginActions';
            $routeList[$routePath] = $route;
        }

        return $routeList;
    }
}
