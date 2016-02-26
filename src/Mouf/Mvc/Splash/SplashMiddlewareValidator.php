<?php

namespace Mouf\Mvc\Splash;

use Mouf\Validator\MoufValidatorResult;
use Mouf\Validator\MoufStaticValidatorInterface;
use Mouf\MoufManager;

class SplashMiddlewareValidator implements MoufStaticValidatorInterface
{
    /**
     * Check if an instance named 'splashMiddleware' actually exists.
     *
     * @return \Mouf\Validator\MoufValidatorResult
     */
    public static function validateClass()
    {
        $instanceExists = MoufManager::getMoufManager()->instanceExists('splashMiddleware');

        if ($instanceExists) {
            return new MoufValidatorResult(MoufValidatorResult::SUCCESS, "'splashMiddleware' instance found");
        } else {
            return new MoufValidatorResult(MoufValidatorResult::WARN, "Unable to find the 'splashMiddleware' instance. Please run the installer or click here to <a href='".MOUF_URL."mouf/newInstance2?instanceName=splash&instanceClass=Mouf\\Mvc\\Splash\\SplashMiddleware'>create an instance of the SplashMiddleware class named 'splashMiddleware'</a>.");
        }
    }
}
