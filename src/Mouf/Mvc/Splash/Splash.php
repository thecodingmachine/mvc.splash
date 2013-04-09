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
use Mouf\Html\Template\TemplateInterface;
use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\MoufManager;


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
	 * The logger used by Splash
	 *
	 * @Property
	 * @Compulsory
	 * @var LogInterface
	 */
	public $log;

	/**
	 * The instance in charge of displaying HTTP 404 errors.
	 * 
	 * @Property
	 * @var Http404HandlerInterface
	 */
	public $http404Handler;
	
	/**
	 * The instance in charge of displaying HTTP 500 errors.
	 *
	 * @Property
	 * @var Http500HandlerInterface
	 */
	public $http500Handler;
	
	/**
	 * Splash uses the cache service to store the URL mapping (the mapping between a URL and its controller/action)
	 *
	 * @Property
	 * @Compulsory
	 * @var CacheInterface
	 */
	public $cacheService;

	/**
	 * If Splash debug mode is enabled, stack traces on error messages will be displayed.
	 *
	 * @Property
	 * @var bool
	 */
	public $debugMode;

	/**
	 * Set to "true" if the server supports HTTPS.
	 * This can be used by various plugins (especially the RequiresHttps annotation).
	 *
	 * @Property
	 * @var boolean
	 */
	public $supportsHttps;

	/**
	 * Defines the route map for input URLs
	 * FIXME: currently unused
	 * 
	 * @Property
	 * @var array<string,SplashAction>
	 */
	//public $routeMap;

	/**
	 * 
	 *
	 * @var string
	 */
	private $splashUrlPrefix;

	/**
	 * Count number of element in POST GET or REQUEST
	 * @var int
	 */
	private $count;
	
	/**
	 * Route the user to the right controller according to the URL.
	 * 
	 * @param string $splashUrlPrefix The beginning of the URL before Splash is activated. This is basically the webapp directory name.
	 * @throws Exception
	 */
	public function route($splashUrlPrefix) {

		try {
			if ($this->cacheService == null) {
				// Retrieve the split parts
				$urlsList = $this->getSplashActionsList();
				$urlNodes = $this->generateUrlNode($urlsList);
			} else {
				$urlNodes = $this->cacheService->get("splashUrlNodes");
				if ($urlNodes == null) {
					// No value in cache, let's get the URL nodes
					$urlsList = $this->getSplashActionsList();
					$urlNodes = $this->generateUrlNode($urlsList);
					$this->cacheService->set("splashUrlNodes", $urlNodes);
				}
			}
			
			// TODO: add support for %instance% for injecting the instancename of the controller
			
			$redirect_uri = $_SERVER['REDIRECT_URL'];
			$httpMethod = $_SERVER['REQUEST_METHOD'];
	
			$pos = strpos($redirect_uri, $splashUrlPrefix);
			if ($pos === FALSE) {
				throw new \Exception('Error: the prefix of the web application "'.$splashUrlPrefix.'" was not found in the URL. The application must be misconfigured. Check the ROOT_URL parameter in your config.php file at the root of your project. It should have the same value as the RewriteBase parameter in your .htaccess file.');
			}
	
			$tailing_url = substr($redirect_uri, $pos+strlen($splashUrlPrefix));
	
			$context = new SplashRequestContext();
			$splashRoute = $urlNodes->walk($tailing_url, $httpMethod);
	
			
			if ($splashRoute == null) {
				// Let's go for the 404
				$this->http404Handler->pageNotFound(null);
				return;
			}
	
			// Check if there is a limit of input number in php
			// Throw exception if the limit is reached
			if(ini_get('max_input_vars') || ini_get('suhosin.get.max_vars')) {
				$maxGet = $this->getMinInConfiguration(ini_get('max_input_vars'), ini_get('suhosin.get.max_vars'));
				if($maxGet !== null) {
					$this->count = 0;
					array_walk_recursive($_GET, array($this, 'countRecursive'));
					if($this->count == $maxGet) {
						throw new SplashException('Max input vars reaches for get parameters ('.$maxGet.'). Check your variable max_input_vars in php.ini or suhosin module suhosin.get.max_vars.');
					}
				}
			}
			if(ini_get('max_input_vars') || ini_get('suhosin.post.max_vars')) {
				$maxPost = $this->getMinInConfiguration(ini_get('max_input_vars'), ini_get('suhosin.post.max_vars'));
				if($maxPost !== null) {
					$this->count = 0;
					array_walk_recursive($_GET, array($this, 'countRecursive'));
					if($this->count == $maxPost) {
						throw new SplashException('Max input vars reaches for post parameters ('.$maxPost.'). Check your variable max_input_vars in php.ini or suhosin module suhosin.post.max_vars.');
					}
				}
			}
			if(ini_get('max_input_vars') || ini_get('suhosin.request.max_vars')) {
				$maxRequest = $this->getMinInConfiguration(ini_get('max_input_vars'), ini_get('suhosin.request.max_vars'));
				if($maxRequest !== null) {
					$this->count = 0;
					array_walk_recursive($_GET, array($this, 'countRecursive'));
					if($this->count == $maxRequest) {
						throw new SplashException('Max input vars reaches for request parameters ('.$maxRequest.'). Check your variable max_input_vars in php.ini or suhosin module suhosin.request.max_vars.');
					}
				}
			}
			
			
			$controller = MoufManager::getMoufManager()->getInstance($splashRoute->controllerInstanceName);
			$action = $splashRoute->methodName;
			
			$context->setUrlParameters($splashRoute->filledParameters);
			
	
			if ($this->log != null) {
				$this->log->trace("Routing user with URL ".$_SERVER['REDIRECT_URL']." to controller ".get_class($controller)." and action ".$action);
			}
	
			if ($controller instanceof Controller) {
				// Let's pass everything to the controller:
				$args = array();
				foreach ($splashRoute->parameters as $paramFetcher) {
					/* @var $param SplashParameterFetcherInterface */
					try {
						$args[] = $paramFetcher->fetchValue($context);
					} catch (SplashValidationException $e) {
						
						$e->setPrependedMessage(SplashUtils::translate("validate.error.while.validating.parameter", $paramFetcher->getName()));
						throw $e;
					}
				}

				// Handle action__GET or action__POST method (for legacy code).
				if(method_exists($controller, $action.'__'.$_SERVER['REQUEST_METHOD'])) {
					$action = $action.'__'.$_SERVER['REQUEST_METHOD'];
				}
				
				$filters = $splashRoute->filters;
				
				// Apply filters
				for ($i=count($filters)-1; $i>=0; $i--) {
					$filters[$i]->beforeAction();
				}
			
				// Ok, now, let's store the parameters.
				//call_user_func_array(array($this,$method), AdminBag::getInstance()->argsArray);
				//$result = call_user_func_array(array($this,$method), $argsArray);
				$result = call_user_func_array(array($controller,$action), $args);
			
				foreach ($filters as $filter) {
					$filter->afterAction();
				}
				
			} elseif ($controller instanceof WebServiceInterface) {
				// FIXME: handle correctly webservices
				$this->handleWebservice($controller);
			} else {
				// "Invalid class";
				$this->print404("The class ".get_class($controller)." should extend the Controller class or the WebServiceInterface class.");
				exit();
			}
		}
		catch (\Exception $e) {
			return $this->handleException($e);
		}


	}

	/**
	 * Handles the call to the webservice
	 *
	 * @param WebServiceInterface $webserviceInstance
	 */
	private function handleWebservice(WebServiceInterface $webserviceInstance) {
		$url = $webserviceInstance->getWebserviceUri();

		$server = new SoapServer(null, array('uri' => $url));
		$server->setObject($webserviceInstance);
		$server->handle();
	}
	
	/**
	 * Returns the list of all SplashActions.
	 * This call is LONG and should be cached
	 * 
	 * @return array<SplashAction>
	 */
	private function getSplashActionsList() {
		$moufManager = MoufManager::getMoufManager();
		$instanceNames = $moufManager->findInstances("Mouf\\Mvc\\Splash\\Services\\UrlProviderInterface");
		
		$urls = array();
		
		foreach ($instanceNames as $instanceName) {
			$urlProvider = $moufManager->getInstance($instanceName);
			/* @var $urlProvider UrlProviderInterface */
			$tmpUrlList = $urlProvider->getUrlsList();
			$urls = array_merge($urls, $tmpUrlList);
		}
		
		
		return $urls;
	}
	
	/**
	 * Generates the URLNodes from the list of URLS.
	 * URLNodes are a very efficient way to know whether we can access our page or not.
	 * 
	 * @param array<SplashAction> $urlsList
	 * @return SplashUrlNode
	 */
	private function generateUrlNode($urlsList) {
		$urlNode = new SplashUrlNode();
		foreach ($urlsList as $splashAction) {
			$urlNode->registerCallback($splashAction);
		}
		return $urlNode;
	}
	
	private function handleException(\Exception $e) {
		$logger = $this->log;
		if ($logger != null) {
			$logger->error($e);
		}
	
		$debug = $this->debugMode;
	
		
		if (!headers_sent() && !ob_get_contents()) {
			$this->http500Handler->serverError($e);
			return;
		} else {
			//UnhandledException($e,$debug);
			
			echo "<div>".nl2br($e->getMessage())."</div>";
			
			echo "<div>".ExceptionUtils::getHtmlForException($e)."</div>";
				
		}
	
	}
	
	public function print404($message) {
	
		$text = "The page you request is not available. Please use <a href='".ROOT_URL."'>this link</a> to return to the home page.";
	
		if ($this->debugMode) {
			$text .= "<div class='info'>".$message.'</div>';
		}
	
		if ($this->log != null) {
			$this->log->info("HTTP 404 : ".$message);
		}
	
	
		$this->http404Handler->pageNotFound($message);
		/*if ($this->defaultTemplate != null && $this->content != null) {
			$this->content->addFunction("FourOFour",$text);
			$this->defaultTemplate->setTitle("404 - Not Found");
			$this->defaultTemplate->toHtml();
		} else {
			FourOFour($text);
		}*/
	
	}
	
	/**
	 * Purges the urls cache.
	 * @throws Exception
	 */
	public function purgeUrlsCache() {
		$this->cacheService->purge("splashUrlNodes");
	}
	
	/**
	 * @return \Mouf\Validator\MoufValidatorResult
	 */
	public static function validateClass() {
	
		$instanceExists = MoufManager::getMoufManager()->instanceExists('splash');
		
		if ($instanceExists) {
			return new MoufValidatorResult(MoufValidatorResult::SUCCESS, "'splash' instance found");
		} else {
			return new MoufValidatorResult(MoufValidatorResult::WARN, "Unable to find the 'splash' instance. Click here to <a href='".ROOT_URL."mouf/mouf/newInstance?instanceName=splash&instanceClass=Splash'>create an instance of the Splash class named 'splash'</a>.");
		}
	}
	
	/**
	 * Get the min in 2 values if there exist
	 * @param int $val1
	 * @param int $val2
	 * @return int|NULL
	 */
	private function getMinInConfiguration($val1, $val2) {
		if($val1 && $val2)
			return min(array($val1, $val2));
		if($val1)
			return $val1;
		if($val2)
			return $val2;
		return null;
	}
	
	/**
	 * Count number of element in array
	 * @param mixed $item
	 * @param mixed $key
	 */
	private function countRecursive($item, $key) {
		$this->count ++;
	}
}

?>