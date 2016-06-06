<?php

namespace Mouf\Mvc\Splash\Services;

use Mouf\MoufManager;
use Mouf\Mvc\Splash\Routers\SplashDefaultRouter;

/**
 * Exports the list of Splash URLs in a PHP array (to be displayed by the SplashViewUrlsController).
 */
class SplashUrlsExporter
{
    public static function exportRoutes() : array
    {
        $moufManager = MoufManager::getMoufManager();
        $splashDefaultRouter = $moufManager->get(SplashDefaultRouter::class);
        $routes = $splashDefaultRouter->getSplashActionsList();

        return array_map(function (SplashRoute $route) {
            return [
                'url' => $route->getUrl(),
                'comment' => $route->getFullComment(),
                'controllerInstanceName' => $route->getControllerInstanceName(),
                'title' => $route->getTitle(),
                'methodName' => $route->getMethodName(),
                'httpMethods' => $route->getHttpMethods(),
            ];
        }, $routes);
    }
}
