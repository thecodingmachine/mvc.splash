<?php

namespace Mouf\Mvc\Splash\Services;

use Mouf\MoufManager;
use TheCodingMachine\Splash\Routers\SplashRouter;

/**
 * Exports the list of Splash URLs in a PHP array (to be displayed by the SplashViewUrlsController).
 */
class SplashUrlsExporter
{
    public static function exportRoutes() : array
    {
        $moufManager = MoufManager::getMoufManager();
        $splashRouter = $moufManager->get(SplashRouter::class);
        $routes = $splashRouter->getSplashActionsList();

        return array_map(function (\TheCodingMachine\Splash\Services\SplashRoute $route) {
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
