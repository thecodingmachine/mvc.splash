<?php
namespace Mouf\Mvc\Splash;

use Mouf\Mvc\Splash\Utils\SplashException;
use Mouf\Validator\MoufValidatorResult;
use Mouf\Validator\MoufStaticValidatorInterface;
use Mouf\Utils\Cache\CacheInterface;
use Mouf\Mvc\Splash\Controllers\WebServiceInterface;
use Mouf\Mvc\Splash\Utils\ExceptionUtils;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Mvc\Splash\Controllers\Http404HandlerInterface;
use Mouf\Mvc\Splash\Controllers\Http500HandlerInterface;
use Mouf\Mvc\Splash\Services\SplashUtils;
use Mouf\Mvc\Splash\Services\SplashRequestContext;
use Mouf\Mvc\Splash\Store\SplashUrlNode;
use Mouf\Utils\Log\LogInterface;
use Psr\Log\LoggerInterface;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\MoufManager;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * The Splash component is the root of the Splash framework.<br/>
 * It is in charge of binding an Url to a Controller.<br/>
 * There is one and only one instance of Splash per web application.<br/>
 * The name of the instance MUST be "splash".<br/>
 * <br/>
 * The Splash component has several ways to bind an URL to a Controller.<br/>
 * It can do so based on the @URL annotation, or based on the @Action annotation.<br/>
 * Check out the Splash documentation here: 
 * <a href="https://github.com/thecodingmachine/mvc.splash/">https://github.com/thecodingmachine/mvc.splash/</a>
 *
 * @RequiredInstance "splash"
 */
class Splash implements MoufStaticValidatorInterface {

	/**
	 * The first router that will handle the request
	 * @var HttpKernelInterface
	 */
	private $router;
	
	public function __construct(HttpKernelInterface $router) {
		$this->router = $router;
	}
	
	/**
	 * Route the user to the right controller according to the URL.
	 * 
	 * @param string $splashUrlPrefix The beginning of the URL before Splash is activated. This is basically the webapp directory name.
	 * 
	 * @throws Exception
	 */
	public function route($splashUrlPrefix) {
		$request = Request::createFromGlobals();

        //json_decode postdata if content_type is json (application/json, ...)
        if (0 === strpos($request->getContentType(), 'json')) {
            $postdata = file_get_contents("php://input");
            $postdata = json_decode($postdata, true);
            $request->request = new ParameterBag($postdata);
        }

		$response = $this->router->handle($request);
		
		$response->send();
	}
	
	/**
	 * Check if an instance named 'splash' actually exists
	 * @return \Mouf\Validator\MoufValidatorResult
	 */
	public static function validateClass() {
		$instanceExists = MoufManager::getMoufManager()->instanceExists('splash');
	
		if ($instanceExists) {
			return new MoufValidatorResult(MoufValidatorResult::SUCCESS, "'splash' instance found");
		} else {
			return new MoufValidatorResult(MoufValidatorResult::WARN, "Unable to find the 'splash' instance. Click here to <a href='".MOUF_URL."mouf/newInstance2?instanceName=splash&instanceClass=Mouf\\Mvc\\Splash\\Splash'>create an instance of the Splash class named 'splash'</a>.");
		}
	}		
}
