<?php
namespace Mouf\Mvc\Splash\Controllers\Admin;

use Mouf\Actions\InstallUtils;

use Mouf\Composer\ClassNameMapper;
use Mouf\MoufUtils;

use Mouf\Html\HtmlElement\HtmlBlock;

use Mouf\Html\Template\TemplateInterface;
use Mouf\Mvc\Splash\Services\SplashCreateControllerService;
use Mouf\Mvc\Splash\SplashGenerateService;
use Mouf\MoufManager;
use Mouf\Mvc\Splash\Controllers\Controller;


/**
 * The controller used in the Splash install process.
 * 
 */
class SplashInstallController extends Controller {
	
	public $selfedit;
	
	/**
	 * The active MoufManager to be edited/viewed
	 *
	 * @var MoufManager
	 */
	public $moufManager;

	/**
	 * The service in charge of generating files.
	 * 
	 * @Property
	 * @Compulsory
	 * @var SplashGenerateService
	 */
	public $splashGenerateService;
	
	
	/**
	 * The template used by the install page.
	 *
	 * @Property
	 * @Compulsory
	 * @var TemplateInterface
	 */
	public $template;
	
	/**
	 *
	 * @var HtmlBlock
	 */
	public $content;
	
	/**
	 * Displays the first install screen.
	 * 
	 * @Action
	 * @Logged
	 * @param string $selfedit If true, the name of the component must be a component from the Mouf framework itself (internal use only) 
	 */
	public function defaultAction($selfedit = "false") {
		$this->selfedit = $selfedit;
		
		if ($selfedit == "true") {
			$this->moufManager = MoufManager::getMoufManager();
		} else {
			$this->moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
				
		$this->content->addFile(__DIR__."/../../../../../views/admin/installStep1.php", $this);
		$this->template->toHtml();
	}

	/**
	 * Skips the install process.
	 * 
	 * @Action
	 * @Logged
	 * @param string $selfedit If true, the name of the component must be a component from the Mouf framework itself (internal use only)
	 */
	public function skip($selfedit = "false") {
		InstallUtils::continueInstall($selfedit == "true");
	}

	protected $sourceDirectory;
	protected $controllerNamespace;
	protected $viewDirectory;
	
	/**
	 * Displays the second install screen.
	 * 
	 * @Action
	 * @Logged
	 * @param string $selfedit If true, the name of the component must be a component from the Mouf framework itself (internal use only) 
	 */
	public function configure($selfedit = "false") {
		$this->selfedit = $selfedit;
		
		if ($selfedit == "true") {
			$this->moufManager = MoufManager::getMoufManager();
		} else {
			$this->moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
		
		$classNameMapper = ClassNameMapper::createFromComposerFile(__DIR__.'/../../../../../../../../../composer.json');
        $namespaces = $classNameMapper->getManagedNamespaces();
		if ($namespaces) {
			$rootNamespace = $namespaces[0];
		} else {
            set_user_message('<strong>Warning</strong> : Mouf could not find a PSR-0 or PSR-4 autoloader configured
                in your composer.json file. Therefore, unless you are using your own autoloader, it is likely that mouf will be unable to find the Splash Controllers.
                <br/>You should :
                <ol>
                    <li><a href="http://getcomposer.org/doc/04-schema.md#psr-0" target="_blank">Configure PSR-4 in your composer.json</a></li>
                    <li>Regenerate your composer autoloader : <pre>php composer.phar dumpautoload</pre></li>
                    <li>Refresh this page</li>
                </ol>');
			$rootNamespace = '';
		}
		
		$this->controllerNamespace = $this->moufManager->getVariable("splashDefaultControllersNamespace");
		if ($this->controllerNamespace == null) {
			$this->controllerNamespace = $rootNamespace."Controllers";
		}
		$this->viewDirectory = $this->moufManager->getVariable("splashDefaultViewsDirectory");
		if ($this->viewDirectory == null) {
			$this->viewDirectory = "views/";
		}
		
		$this->content->addFile(__DIR__."/../../../../../views/admin/installStep2.php", $this);
		$this->template->toHtml();
	}
	
	/**
	 * This action generates the TDBM instance, then the DAOs and Beans. 
	 * 
	 * @Action
	 * @param string $controllernamespace
	 * @param string $viewdirectory
	 * @param string $selfedit
	 */
	public function generate($controllernamespace, $viewdirectory, $selfedit="false") {
		$this->selfedit = $selfedit;		
		
		if ($selfedit == "true") {
			$this->moufManager = MoufManager::getMoufManager();
		} else {
			$this->moufManager = MoufManager::getMoufManagerHiddenInstance();
		}

		$controllernamespace = trim($controllernamespace, "/\\");
		$controllernamespace .= "\\";
		$viewdirectory = trim($viewdirectory, "/\\");
		$viewdirectory .= "/";

		$this->moufManager->setVariable("splashDefaultControllersNamespace", $controllernamespace);
		$this->moufManager->setVariable("splashDefaultViewsDirectory", $viewdirectory);
		
		
		// Let's start by performing basic checks about the instances we assume to exist.
		if (!$this->moufManager->instanceExists("bootstrapTemplate")) {
			$this->displayErrorMsg("The install process assumes there is a template whose instance name is 'bootstrapTemplate'. Could not find the 'bootstrapTemplate' instance.");
			return;
		}

		$moufManager = $this->moufManager;

		// These instances are expected to exist when the installer is run.
		$psr_errorLogLogger = $moufManager->getInstanceDescriptor('psr.errorLogLogger');
		$bootstrapTemplate = $moufManager->getInstanceDescriptor('bootstrapTemplate');
		$block_content = $moufManager->getInstanceDescriptor('block.content');

		// Let's create the instances.
		$splashMiddleware = InstallUtils::getOrCreateInstance('splashMiddleware', 'Mouf\\Mvc\\Splash\\SplashMiddleware', $moufManager);
		$exceptionRouter = InstallUtils::getOrCreateInstance('exceptionRouter', 'Mouf\\Mvc\\Splash\\Routers\\ExceptionRouter', $moufManager);
		$httpErrorsController = InstallUtils::getOrCreateInstance('httpErrorsController', 'Mouf\\Mvc\\Splash\\Controllers\\HttpErrorsController', $moufManager);
		$whoopsMiddleware = InstallUtils::getOrCreateInstance('whoopsMiddleware', 'Franzl\\Middleware\\Whoops\\Middleware', $moufManager);
		$phpVarsCheckRouter = InstallUtils::getOrCreateInstance('phpVarsCheckRouter', 'Mouf\\Mvc\\Splash\\Routers\\PhpVarsCheckRouter', $moufManager);
		$splashDefaultRouter = InstallUtils::getOrCreateInstance('splashDefaultRouter', 'Mouf\\Mvc\\Splash\\Routers\\SplashDefaultRouter', $moufManager);
		$notFoundRouter = InstallUtils::getOrCreateInstance('notFoundRouter', 'Mouf\\Mvc\\Splash\\Routers\\NotFoundRouter', $moufManager);
		$splashCacheApc = InstallUtils::getOrCreateInstance('splashCacheApc', 'Mouf\\Utils\\Cache\\ApcCache', $moufManager);
		$splashCacheFile = InstallUtils::getOrCreateInstance('splashCacheFile', 'Mouf\\Utils\\Cache\\FileCache', $moufManager);
		$anonymousErrorRouter = $moufManager->createInstance('Mouf\\Mvc\\Splash\\Routers\\ErrorRouter');
		$anonymousErrorRouter2 = $moufManager->createInstance('Mouf\\Mvc\\Splash\\Routers\\ErrorRouter');
		$anonymousRouter = $moufManager->createInstance('Mouf\\Mvc\\Splash\\Routers\\Router');
		$anonymousRouter2 = $moufManager->createInstance('Mouf\\Mvc\\Splash\\Routers\\Router');
		$anonymousRouter3 = $moufManager->createInstance('Mouf\\Mvc\\Splash\\Routers\\Router');

		// Let's bind instances together.
		if (!$splashMiddleware->getConstructorArgumentProperty('routers')->isValueSet()) {
			$splashMiddleware->getConstructorArgumentProperty('routers')->setValue(array(0 => $anonymousErrorRouter, 1 => $anonymousErrorRouter2, 2 => $anonymousRouter, 3 => $anonymousRouter2, 4 => $anonymousRouter3, ));
		}
		if (!$exceptionRouter->getConstructorArgumentProperty('errorController')->isValueSet()) {
			$exceptionRouter->getConstructorArgumentProperty('errorController')->setValue($httpErrorsController);
		}
		if (!$exceptionRouter->getConstructorArgumentProperty('log')->isValueSet()) {
			$exceptionRouter->getConstructorArgumentProperty('log')->setValue($psr_errorLogLogger);
		}
		if (!$httpErrorsController->getConstructorArgumentProperty('template')->isValueSet()) {
			$httpErrorsController->getConstructorArgumentProperty('template')->setValue($bootstrapTemplate);
		}
		if (!$httpErrorsController->getConstructorArgumentProperty('contentBlock')->isValueSet()) {
			$httpErrorsController->getConstructorArgumentProperty('contentBlock')->setValue($block_content);
		}
		if (!$httpErrorsController->getConstructorArgumentProperty('debugMode')->isValueSet()) {
			$httpErrorsController->getConstructorArgumentProperty('debugMode')->setValue('DEBUG');
			$httpErrorsController->getConstructorArgumentProperty('debugMode')->setOrigin("config");
		}
		if (!$phpVarsCheckRouter->getConstructorArgumentProperty('log')->isValueSet()) {
			$phpVarsCheckRouter->getConstructorArgumentProperty('log')->setValue($psr_errorLogLogger);
		}
		if (!$splashDefaultRouter->getConstructorArgumentProperty('cacheService')->isValueSet()) {
			$splashDefaultRouter->getConstructorArgumentProperty('cacheService')->setValue($splashCacheApc);
		}
		if (!$notFoundRouter->getConstructorArgumentProperty('pageNotFoundController')->isValueSet()) {
			$notFoundRouter->getConstructorArgumentProperty('pageNotFoundController')->setValue($httpErrorsController);
		}
		if (!$splashCacheApc->getPublicFieldProperty('prefix')->isValueSet()) {
			$splashCacheApc->getPublicFieldProperty('prefix')->setValue('SECRET');
			$splashCacheApc->getPublicFieldProperty('prefix')->setOrigin("config");
		}
		if (!$splashCacheApc->getPublicFieldProperty('fallback')->isValueSet()) {
			$splashCacheApc->getPublicFieldProperty('fallback')->setValue($splashCacheFile);
		}
		if (!$splashCacheFile->getPublicFieldProperty('prefix')->isValueSet()) {
			$splashCacheFile->getPublicFieldProperty('prefix')->setValue('SECRET');
			$splashCacheFile->getPublicFieldProperty('prefix')->setOrigin("config");
		}
		if (!$splashCacheFile->getPublicFieldProperty('cacheDirectory')->isValueSet()) {
			$splashCacheFile->getPublicFieldProperty('cacheDirectory')->setValue('splashCache/');
		}
		$anonymousErrorRouter->getConstructorArgumentProperty('middleware')->setValue($exceptionRouter);
		$anonymousErrorRouter2->getConstructorArgumentProperty('middleware')->setValue($whoopsMiddleware);
		$anonymousRouter->getConstructorArgumentProperty('middleware')->setValue($phpVarsCheckRouter);
		$anonymousRouter2->getConstructorArgumentProperty('middleware')->setValue($splashDefaultRouter);
		$anonymousRouter3->getConstructorArgumentProperty('middleware')->setValue($notFoundRouter);

		// Let's rewrite the MoufComponents.php file to save the component
		$this->moufManager->rewriteMouf();



		if (!$this->moufManager->instanceExists("rootController")) {
            $splashGenerateService = new SplashCreateControllerService();
            $splashGenerateService->generate($this->moufManager, "RootController", "rootController",
                $controllernamespace, false, true, false,
                array(
                    array(
                        'url' => '/',
                        'method' => 'index',
                        'view' => 'twig',
                        'twigFile' => 'views/root/index.twig',
                        'anyMethod' => 'true',
                        'getMethod' => 'false',
                        'postMethod' => 'false',
                        'putMethod' => 'false',
                        'deleteMethod' => 'false',
                    )
                )
                );

            // Let's overwrite the generated Twig file.
            file_put_contents(ROOT_PATH.'../../../'.$viewdirectory.'root/index.twig', '
<h1>Hello {{message}}!</h1>
<h2>Welcome to Splash</h2>
<p>This file is your welcome page. It is generated by the '.$controllernamespace.'RootController class and the '.$viewdirectory.'root/index.php file. Please feel free to customize it.</p>');

		}
		
		
		$this->moufManager->rewriteMouf();
				
		InstallUtils::continueInstall($selfedit == "true");
	}
	
	/**
	 * Write .htaccess
	 *
	 * @Action
	 * @param string $selfedit
	 */
	public function writeHtAccess($selfedit="false") {
		/*$uri = $_SERVER["REQUEST_URI"];
		
		$installPos = strpos($uri, "/vendor/mouf/mouf/splashinstall/writeHtAccess");
		if ($installPos !== FALSE) {
			$uri = substr($uri, 0, $installPos);
		}
		if (empty($uri)) {
			$uri = "/";
		}*/
		
		if ($selfedit == "true") {
			$moufManager = MoufManager::getMoufManager();
		} else {
			$moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
		
		$this->exludeExtentions = $moufManager->getVariable("splashexludeextentions");
		$this->exludeFolders = $moufManager->getVariable("splashexludefolders");
		if (empty($this->exludeExtentions)){
			$this->exludeExtentions = array("js", "ico", "gif", "jpg", "png", "css", "woff", "ttf", "svg", "eot", "map");
		}
		if (empty($this->exludeFolders)){
			$this->exludeFolders = array("vendor");
		}
		
		$this->splashGenerateService->writeHtAccess($this->exludeExtentions, $this->exludeFolders);
		
		InstallUtils::continueInstall($selfedit == "true");
	}
	
	protected $errorMsg;
	
	private function displayErrorMsg($msg) {
		$this->errorMsg = $msg;
		$this->content->addFile(dirname(__FILE__)."/../../../../../views/admin/installError.php", $this);
		$this->template->toHtml();
	}
}