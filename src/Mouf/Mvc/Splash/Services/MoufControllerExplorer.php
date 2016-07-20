<?php

namespace Mouf\Mvc\Splash\Services;

use Doctrine\Common\Annotations\AnnotationException;
use Mouf\MoufManager;
use Mouf\Mvc\Splash\Utils\SplashException;

/**
 * This class scans the Mouf container in order to find all instances that point to classes containing a @URL or @Action annotation.
 * Use it to discover instances.
 */
class MoufControllerExplorer implements ControllerDetector
{
    /**
     * Returns a list of controllers.
     * It is the name of the controller (in the container) that is returned (not the container itself).
     *
     * @return string[]
     */
    public function getControllerIdentifiers(ControllerAnalyzer $controllerAnalyzer) : array
    {
        $moufManager = MoufManager::getMoufManager();
        $instanceNames = $moufManager->getInstancesList();

        $isController = [];

        $controllers = [];

        foreach ($instanceNames as $instanceName => $className) {
            if ($className === null) {
                continue;
            }
            
            if (!isset($isController[$className])) {
                try {
                    $isController[$className] = $controllerAnalyzer->isController($className);
                } catch (AnnotationException $e) {
                    // Unknown annotation?
                    // Is there a slight chance this class might be a controller? Let's apply heuristics here.
                    if ($this->shouldBeController($className)) {
                        throw $e;
                    }

                    // Let's bypass the controller altogether.
                    $isController[$className] = false;
                }
            }

            if ($isController[$className] === true) {
                $controllers[] = $instanceName;
            }
        }

        return $controllers;
    }

    /**
     * If we arrive in this method, annotations have failed to parse.
     * Let's try to see (heuristically) if this class has a good chance to be a controller or not.
     * If it has, let's display a big error message.
     *
     * @param string $className
     * @return bool
     */
    private function shouldBeController($className) : bool
    {
        if (strpos($className, 'Controller') !== false) {
            return true;
        }

        $reflectionClass = new \ReflectionClass($className);
        $file = $reflectionClass->getFileName();

        $content = file_get_contents($file);

        if (strpos($content, '@URL') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Returns a unique tag representing the list of SplashRoutes returned.
     * If the tag changes, the cache is flushed by Splash.
     *
     * Important! This must be quick to compute.
     *
     * @return mixed
     */
    public function getExpirationTag() : string
    {
        return filemtime(__DIR__.'/../../../../../../../../mouf/MoufComponents.php');
    }
}
