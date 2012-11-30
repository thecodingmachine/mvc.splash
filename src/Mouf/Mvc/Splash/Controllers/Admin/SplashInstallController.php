<?php
namespace Mouf\Mvc\Splash\Controllers\Admin;

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
				
		$this->template->addContentFile(dirname(__FILE__)."/../../views/admin/installStep1.php", $this);
		$this->template->draw();
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

	protected $controllerDirectory;
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
				
		$this->controllerDirectory = $this->moufManager->getVariable("splashDefaultControllersDirectory");
		if ($this->controllerDirectory == null) {
			$this->controllerDirectory = "controllers";
		}
		$this->viewDirectory = $this->moufManager->getVariable("splashDefaultViewsDirectory");
		if ($this->viewDirectory == null) {
			$this->viewDirectory = "views";
		}
		
		$this->template->addContentFile(dirname(__FILE__)."/../../views/admin/installStep2.php", $this);
		$this->template->draw();
	}
	
	/**
	 * This action generates the TDBM instance, then the DAOs and Beans. 
	 * 
	 * @Action
	 * @param string $name
	 * @param string $selfedit
	 */
	public function generate($controllerdirectory, $viewdirectory, $selfedit="false") {
		$this->selfedit = $selfedit;		
		
		if ($selfedit == "true") {
			$this->moufManager = MoufManager::getMoufManager();
		} else {
			$this->moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
		
		$controllerdirectory = trim($controllerdirectory, "/\\");
		$controllerdirectory .= "/";
		$viewdirectory = trim($viewdirectory, "/\\");
		$viewdirectory .= "/";
		
		$this->moufManager->setVariable("splashDefaultControllersDirectory", $controllerdirectory);
		$this->moufManager->setVariable("splashDefaultViewsDirectory", $viewdirectory);
		
		
		// Let's start by performing basic checks about the instances we assume to exist.
		if (!$this->moufManager->instanceExists("splashTemplate")) {
			$this->displayErrorMsg("The Splash install process assumes there is a template whose instance name is 'splashTemplate'. Could not find the 'splashTemplate' instance.");
			return;
		}
		
		if (!$this->moufManager->instanceExists("splash")) {
			$splashInstance = $this->moufManager->createInstance("Splash");
			$splashInstance->setName("splash");
			$splashInstance->getProperty("defaultTemplate")->setValue($this->moufManager->getInstanceDescriptor("splashTemplate"));
			
			$configManager = $this->moufManager->getConfigManager();
			$constants = $configManager->getMergedConstants();
			
			if (!isset($constants['DEBUG_MODE'])) {
				$configManager->registerConstant("DEBUG_MODE", "bool", true, "When the application is in debug mode, stacktraces are outputed directly to the user. Otherwise, they are hidden.");
			}

			// TODO: gérer les ORIGIN
			$splashInstance->getProperty("debugMode")->setValue("DEBUG_MODE")->setOrigin("config");
				
			//
			// TODOOOOOOOOOOOOOOOOOOOOO: bind au ErrorLogLogger
		} else {
			$splashInstance = $this->moufManager->getInstanceDescriptor("splash");
		}
		
		if ($splashInstance->getProperty("cacheService")->getValue() == null) {
			if (!$this->moufManager->instanceExists("splashCacheApc")) {
				$splashCacheApc = $this->moufManager->createInstance("ApcCache");
				$splashCacheApc->setName("splashCacheApc");

				if (!$this->moufManager->instanceExists("splashCacheFile")) {
					$splashCacheFile = $this->moufManager->createInstance("FileCache");
					$splashCacheFile->setName("splashCacheFile");
					$splashCacheFile->getProperty("cacheDirectory")->setValue("splashCache");					
				} else {
					$splashCacheFile = $this->moufManager->getInstanceDescriptor("splashCacheApc");
				}
				$splashCacheApc->getProperty("fallback")->setValue($splashCacheFile);
			
			} else {
				$splashCacheApc = $this->moufManager->getInstanceDescriptor("splashCacheApc");
			}
		
			$splashInstance->getProperty("cacheService")->setValue($splashCacheApc);
		}
		
		$uri = $_SERVER["REQUEST_URI"];
		
		$installPos = strpos($uri, "/mouf/splashinstall/generate");
		if ($installPos !== FALSE) {
			$uri = substr($uri, 0, $installPos);
		}
		if (empty($uri)) {
			$uri = "/";
		}
		
		$this->splashGenerateService->writeHtAccess($uri, array("js", "ico", "gif", "jpg", "png", "css"), array("vendor"));
		
		if (!$this->moufManager->instanceExists("rootController")) {
			$this->splashGenerateService->generateRootController($controllerdirectory, $viewdirectory);

			$this->moufManager->declareComponent("rootController", "RootController");
			$this->moufManager->bindComponent("rootController", "template", "splashTemplate");
			
			$this->moufManager->registerComponent($controllerdirectory."RootController.php");
			// TODO: bind au ErrorLogLogger?
		}
		
		
		$this->moufManager->rewriteMouf();
				
		InstallUtils::continueInstall($selfedit == "true");
	}
	
	protected $errorMsg;
	
	private function displayErrorMsg($msg) {
		$this->errorMsg = $msg;
		$this->template->addContentFile(dirname(__FILE__)."/../../views/admin/installError.php", $this);
		$this->template->draw();
	}
}