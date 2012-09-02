<?php 
namespace Mouf\Mvc\Splash\Controllers\Admin;

/**
 * The controller that will write the .htaccess file.
 *
 * @Component
 */
class SplashAdminApacheConfigureController extends Controller {

	/**
	 * The template used by the Splash page.
	 *
	 * @Property
	 * @Compulsory
	 * @var TemplateInterface
	 */
	public $template;
	
	/**
	 * The service in charge of generating files.
	 * 
	 * @Property
	 * @Compulsory
	 * @var SplashGenerateService
	 */
	public $splashGenerateService;
	
	/**
	 * Displays the config page. 
	 *
	 * @Action
	 */
	public function defaultAction($selfedit = 'false') {
		
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
			$this->exludeFolders = array("plugins", "mouf");
		}
		$this->template->addContentFile(dirname(__FILE__)."/../../views/admin/splashAdminApache.php", $this);
		$this->template->draw();
	}
	
	/**
	 * Writes the .htaccess file. 
	 *
	 * @Action
	 */
	public function write($selfedit, $textExtentions, $textFolders) {
		$uri = $_SERVER["REQUEST_URI"];
		
		$exludeExtentions = explode("\r\n", $textExtentions);
		$exludeFolders = explode("\r\n", $textFolders);
		
		if ($selfedit == "true") {
			$moufManager = MoufManager::getMoufManager();
		} else {
			$moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
		
		$moufManager->setVariable("splashexludeextentions", $exludeExtentions);
		$moufManager->setVariable("splashexludefolders", $exludeFolders);
		$moufManager->rewriteMouf();
		
		$installPos = strpos($uri, "/mouf/splashApacheConfig/write");
		if ($installPos !== FALSE) {
			$uri = substr($uri, 0, $installPos);
		}
		if (empty($uri)) {
			$uri = "/";
		}
		
		$this->splashGenerateService->writeHtAccess($uri, $exludeExtentions, $exludeFolders);
		
		header("Location: ".ROOT_URL."mouf/?selfedit=".$selfedit);
	}
	
	
}

?>