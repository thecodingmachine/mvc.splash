<?php
namespace Mouf\Mvc\Splash;

use Mouf\Mvc\Splash\Routers\RouterInterface;
use Mouf\Validator\MoufValidatorResult;
use Mouf\Validator\MoufStaticValidatorInterface;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\MoufManager;
use Zend\Stratigility\MiddlewarePipe;

/**
 * The SplashMiddleware class is the root of the Splash framework.<br/>
 * It is in charge of binding an Url to a Controller.<br/>
 * There is one and only one instance of Splash per web application.<br/>
 * The name of the instance MUST be "splashMiddleware".<br/>
 * <br/>
 * The SplashMiddleware component has several ways to bind an URL to a Controller.<br/>
 * It can do so based on the @URL annotation, or based on the @Action annotation.<br/>
 * Check out the Splash documentation here:
 * <a href="https://github.com/thecodingmachine/mvc.splash/">https://github.com/thecodingmachine/mvc.splash/</a>
 *
 */
class SplashMiddleware extends MiddlewarePipe implements MoufStaticValidatorInterface
{

    /**
     * @param RouterInterface[] $routers
     */
    public function __construct(array $routers)
    {
        parent::__construct();
        foreach ($routers as $router) {
            $this->pipe($router->getPath(), $router->getMiddleware());
        }
    }

    /**
     * Check if an instance named 'splashMiddleware' actually exists
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
