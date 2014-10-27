<?php
namespace Mouf\Mvc\Splash\Routers;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Mouf\Utils\Cache\CacheInterface;
use Mouf\MoufManager;
use Mouf\Mvc\Splash\Store\SplashUrlNode;
use Psr\Log\LoggerInterface;
use Mouf\Mvc\Splash\Controllers\WebServiceInterface;

class SplashDefaultRouter implements HttpKernelInterface {
	
	/**
	 * The logger used by Splash
	 *
	 * @var LoggerInterface
	 */
	private $log;
	
	/**
	 * Splash uses the cache service to store the URL mapping (the mapping between a URL and its controller/action)
	 *
	 * @var CacheInterface
	 */
	private $cacheService;

	/**
	 * @var HttpKernelInterface
	 */
	private $fallBackRouter;
	
	/**
	 * @Important
	 * @param HttpKernelInterface $fallBackRouter Router used if no page is found for this controller.
	 * @param CacheInterface $cacheService Splash uses the cache service to store the URL mapping (the mapping between a URL and its controller/action)
	 * @param LoggerInterface $log The logger used by Splash
	 */
	public function __construct(HttpKernelInterface $fallBackRouter, CacheInterface $cacheService = null, LoggerInterface $log = null){
		$this->fallBackRouter = $fallBackRouter;
		$this->cacheService = $cacheService;
		$this->log = $log;
	}
	
	/**
	 * Handles a Request to convert it to a Response.
	 *
	 * When $catch is true, the implementation must catch all exceptions
	 * and do its best to convert them to a Response instance.
	 *
	 * @param Request $request A Request instance
	 * @param int     $type    The type of the request
	 *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
	 * @param bool    $catch Whether to catch exceptions or not
	 *
	 * @return Response A Response instance
	 *
	 * @throws \Exception When an Exception occurs during processing
	 */
	public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true){
		// FIXME: find a better way?
		$splashUrlPrefix = ROOT_URL; 
		
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
			
		$request_array = parse_url($_SERVER['REQUEST_URI']);
			
		if ($request_array === false) {
			throw new SplashException("Malformed URL: ".$_SERVER['REQUEST_URI']);
		}
			
		$request_path = $request_array['path'];
		$httpMethod = $_SERVER['REQUEST_METHOD'];
	
		$pos = strpos($request_path, $splashUrlPrefix);
		if ($pos === FALSE) {
			throw new \Exception('Error: the prefix of the web application "'.$splashUrlPrefix.'" was not found in the URL. The application must be misconfigured. Check the ROOT_URL parameter in your config.php file at the root of your project. It should have the same value as the RewriteBase parameter in your .htaccess file.');
		}
	
		$tailing_url = substr($request_path, $pos+strlen($splashUrlPrefix));
	
		$context = new SplashRequestContext();
		$splashRoute = $urlNodes->walk($tailing_url, $httpMethod);
	
		if ($splashRoute === null){
			return $this->fallBackRouter->handle($request, $type, $catch);
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
				array_walk_recursive($_POST, array($this, 'countRecursive'));
				if($this->count == $maxPost) {
					throw new SplashException('Max input vars reaches for post parameters ('.$maxPost.'). Check your variable max_input_vars in php.ini or suhosin module suhosin.post.max_vars.');
				}
			}
		}
		if(ini_get('max_input_vars') || ini_get('suhosin.request.max_vars')) {
			$maxRequest = $this->getMinInConfiguration(ini_get('max_input_vars'), ini_get('suhosin.request.max_vars'));
			if($maxRequest !== null) {
				$this->count = 0;
				array_walk_recursive($_REQUEST, array($this, 'countRecursive'));
				if($this->count == $maxRequest) {
					throw new SplashException('Max input vars reaches for request parameters ('.$maxRequest.'). Check your variable max_input_vars in php.ini or suhosin module suhosin.request.max_vars.');
				}
			}
		}
		if(isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post' && empty($_POST) && empty($_FILES)){
			$maxPostSize = self::iniGetBytes('post_max_size');
			if ($_SERVER['CONTENT_LENGTH'] > $maxPostSize) {
				throw new SplashException(
						sprintf('Max post size exceeded! Got %s bytes, but limit is %s bytes. Edit post_max_size setting in your php.ini.',
								$_SERVER['CONTENT_LENGTH'],
								$maxPostSize
						)
				);
			}
		}
			
		$controller = MoufManager::getMoufManager()->getInstance($splashRoute->controllerInstanceName);
		$action = $splashRoute->methodName;
		
		$context->setUrlParameters($splashRoute->filledParameters);
			
	
		if ($this->log != null) {
			if ($this->log instanceof LogInterface) {
				$this->log->trace("Routing user with URL ".$_SERVER['REDIRECT_URL']." to controller ".get_class($controller)." and action ".$action);
			} else {
				$this->log->info("Routing user with URL {url} to controller {controller} and action {action}", array(
						'url' => $_SERVER['REDIRECT_URL'],
						'controller' => get_class($controller),
						'action' => $action
				));
			}
		}
	
		if ($controller instanceof WebServiceInterface) {
			// FIXME: handle correctly webservices (or remove this exception and handle
			// webservice the way we handle controllers
			ob_start();
			$this->handleWebservice($controller);
			$html = ob_get_clean();
			return new Response($html);
		} else {
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
			
			ob_start();
			$result = call_user_func_array(array($controller,$action), $args);
			$html = ob_get_clean();
			
				
			foreach ($filters as $filter) {
				$filter->afterAction();
			}
			
			return new Response($html);
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
	
	/**
	 * Purges the urls cache.
	 * @throws Exception
	 */
	public function purgeUrlsCache() {
		$this->cacheService->purge("splashUrlNodes");
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
	
	/**
	 * Returns the number of bytes from php.ini parameter
	 *
	 * @param $val
	 * @return int|string
	 */
	private static function iniGetBytes($val)
	{
		$val = trim(ini_get($val));
		if ($val != '') {
			$last = strtolower(
					$val{strlen($val) - 1}
			);
		} else {
			$last = '';
		}
		switch ($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
	
		return $val;
	}
}