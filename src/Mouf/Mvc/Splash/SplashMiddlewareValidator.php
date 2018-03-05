<?php

namespace Mouf\Mvc\Splash;

use Mouf\Validator\MoufValidatorResult;
use Mouf\Validator\MoufStaticValidatorInterface;
use Mouf\MoufManager;

class SplashMiddlewareValidator implements MoufStaticValidatorInterface
{
    /**
     * Check if an instance named 'Mouf\\Mvc\\Splash\\SplashMiddleware' actually exists.
     *
     * @return \Mouf\Validator\MoufValidatorResult
     */
    public static function validateClass()
    {
        $instanceExists = MoufManager::getMoufManager()->instanceExists('Mouf\\Mvc\\Splash\\MiddlewarePipe');

        if ($instanceExists) {
            return new MoufValidatorResult(MoufValidatorResult::SUCCESS, "'Mouf\\Mvc\\Splash\\SplashMiddleware' instance found");
        } else {
            return new MoufValidatorResult(MoufValidatorResult::WARN, "Unable to find the 'Mouf\\Mvc\\Splash\\SplashMiddleware' instance. Please run the installer or click here to <a href='".MOUF_URL."mouf/newInstance2?instanceName=Mouf\\Mvc\\Splash\\SplashMiddleware&instanceClass=Mouf\\Mvc\\Splash\\SplashMiddleware'>create an instance of the SplashMiddleware class named 'Mouf\\Mvc\\Splash\\SplashMiddleware'</a>.");
        }
    }
}
