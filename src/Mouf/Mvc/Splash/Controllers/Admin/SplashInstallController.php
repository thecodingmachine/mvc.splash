<?php
namespace Mouf\Mvc\Splash\Controllers\Admin;

use Mouf\Actions\InstallUtils;

use Mouf\MoufUtils;

use Mouf\Html\HtmlElement\HtmlBlock;

use Mouf\Html\Template\TemplateInterface;
use Mouf\Mvc\Splash\SplashGenerateService;
use Mouf\MoufManager;
use Mouf\Mvc\Splash\Controllers\Controller;


/**
 * The controller used in the Splash install process.
 * 
 * @Component
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
		
		$autoloadNamespaces = MoufUtils::getAutoloadNamespaces();
		if ($autoloadNamespaces) {
			$rootNamespace = $autoloadNamespaces[0]['namespace'].'\\';
			$this->sourceDirectory = $autoloadNamespaces[0]['directory'];
		} else {
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
	 * @param string $sourcedirectory
	 * @param string $controllernamespace
	 * @param string $viewdirectory
	 * @param string $selfedit
	 */
	public function generate($sourcedirectory, $controllernamespace, $viewdirectory, $selfedit="false") {
		$this->selfedit = $selfedit;		
		
		if ($selfedit == "true") {
			$this->moufManager = MoufManager::getMoufManager();
		} else {
			$this->moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
		
		$sourcedirectory = trim($sourcedirectory, "/\\");
		$sourcedirectory .= "/";
		$controllernamespace = trim($controllernamespace, "/\\");
		$controllernamespace .= "\\";
		$viewdirectory = trim($viewdirectory, "/\\");
		$viewdirectory .= "/";
		
		$this->moufManager->setVariable("splashDefaultSourceDirectory", $sourcedirectory);
		$this->moufManager->setVariable("splashDefaultControllersNamespace", $controllernamespace);
		$this->moufManager->setVariable("splashDefaultViewsDirectory", $viewdirectory);
		
		
		// Let's start by performing basic checks about the instances we assume to exist.
		if (!$this->moufManager->instanceExists("bootstrapTemplate")) {
			$this->displayErrorMsg("The install process assumes there is a template whose instance name is 'bootstrapTemplate'. Could not find the 'bootstrapTemplate' instance.");
			return;
		}
		
		if (!$this->moufManager->instanceExists("splash")) {
			$splashInstance = $this->moufManager->createInstance("Mouf\\Mvc\\Splash\\Splash");
			$splashInstance->setName("splash");
			
			$configManager = $this->moufManager->getConfigManager();
			$constants = $configManager->getMergedConstants();
			
			if (!isset($constants['DEBUG_MODE'])) {
				$configManager->registerConstant("DEBUG_MODE", "bool", true, "When the application is in debug mode, stacktraces are outputed directly to the user. Otherwise, they are hidden.");
			}
			$definedConstants = $configManager->getDefinedConstants();
			if (!isset($definedConstants['DEBUG_MODE'])) {
				$definedConstants['DEBUG_MODE'] = true;
			}
			$configManager->setDefinedConstants($definedConstants);

			$splashInstance->getProperty("debugMode")->setValue("DEBUG_MODE")->setOrigin("config");
				
			//
			// TODOOOOOOOOOOOOOOOOOOOOO: bind au ErrorLogLogger
		} else {
			$splashInstance = $this->moufManager->getInstanceDescriptor("splash");
		}
		
		// Let's create the errors controller.
		$httpErrorsController = InstallUtils::getOrCreateInstance("httpErrorsController", "Mouf\\Mvc\\Splash\\Controllers\\HttpErrorsController", $this->moufManager);
		if ($httpErrorsController->getProperty("template")->getValue() == null) {
			if ($this->moufManager->instanceExists("bootstrapTemplate")) {
				$httpErrorsController->getProperty("template")->setValue($this->moufManager->getInstanceDescriptor("bootstrapTemplate"));
			}
		}
		if ($httpErrorsController->getProperty("contentBlock")->getValue() == null) {
			if ($this->moufManager->instanceExists("block.content")) {
				$httpErrorsController->getProperty("contentBlock")->setValue($this->moufManager->getInstanceDescriptor("block.content"));
			}
		}
		if ($httpErrorsController->getProperty("debugMode")->getValue() == null) {
			$httpErrorsController->getProperty("debugMode")->setValue("DEBUG_MODE")->setOrigin("config");
		}
		
		if ($splashInstance->getProperty("http404Handler")->getValue() == null) {
			$splashInstance->getProperty("http404Handler")->setValue($httpErrorsController);
		}
		
		if ($splashInstance->getProperty("http500Handler")->getValue() == null) {
			$splashInstance->getProperty("http500Handler")->setValue($httpErrorsController);
		}
		
		$configManager = $this->moufManager->getConfigManager();
		$constants = $configManager->getMergedConstants();
		
		if ($splashInstance->getProperty("cacheService")->getValue() == null) {
			if (!$this->moufManager->instanceExists("splashCacheApc")) {
				$splashCacheApc = $this->moufManager->createInstance("Mouf\\Utils\\Cache\\ApcCache");
				$splashCacheApc->setName("splashCacheApc");

				if (!$this->moufManager->instanceExists("splashCacheFile")) {
					$splashCacheFile = $this->moufManager->createInstance("Mouf\\Utils\\Cache\\FileCache");
					$splashCacheFile->setName("splashCacheFile");
					$splashCacheFile->getProperty("cacheDirectory")->setValue("splashCache/");
				} else {
					$splashCacheFile = $this->moufManager->getInstanceDescriptor("splashCacheApc");
				}
				
				$splashCacheApc->getProperty("fallback")->setValue($splashCacheFile);
			
			} else {
				$splashCacheApc = $this->moufManager->getInstanceDescriptor("splashCacheApc");
			}
			
			if (isset($constants['ROOT_URL'])) {
				$splashCacheApc->getProperty('prefix')->setValue('ROOT_URL')->setOrigin('config');
			}
		
			$splashInstance->getProperty("cacheService")->setValue($splashCacheApc);
		}
				
		if (!$this->moufManager->instanceExists("rootController")) {
			$this->splashGenerateService->generateRootController($sourcedirectory, $controllernamespace, $viewdirectory);

			$this->moufManager->declareComponent("rootController", $controllernamespace."RootController");
			$this->moufManager->bindComponent("rootController", "template", "bootstrapTemplate");
			$this->moufManager->bindComponent("rootController", "content", "block.content");
				
			// TODO: bind au ErrorLogLogger?
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
		$uri = $_SERVER["REQUEST_URI"];
		
		$installPos = strpos($uri, "/vendor/mouf/mouf/splashinstall/writeHtAccess");
		if ($installPos !== FALSE) {
			$uri = substr($uri, 0, $installPos);
		}
		if (empty($uri)) {
			$uri = "/";
		}
		
		if ($selfedit == "true") {
			$moufManager = MoufManager::getMoufManager();
		} else {
			$moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
		
		$this->exludeExtentions = $moufManager->getVariable("splashexludeextentions");
		$this->exludeFolders = $moufManager->getVariable("splashexludefolders");
		if (empty($this->exludeExtentions)){
			$this->exludeExtentions = array("js", "ico", "gif", "jpg", "png", "css");
		}
		if (empty($this->exludeFolders)){
			$this->exludeFolders = array("vendor");
		}
		
		$this->splashGenerateService->writeHtAccess($uri, $this->exludeExtentions, $this->exludeFolders);
		
		InstallUtils::continueInstall($selfedit == "true");
	}
	
	protected $errorMsg;
	
	private function displayErrorMsg($msg) {
		$this->errorMsg = $msg;
		$this->content->addFile(dirname(__FILE__)."/../../../../../views/admin/installError.php", $this);
		$this->template->toHtml();
	}
}