<?php 
namespace Mouf\Mvc\Splash\Controllers\Admin;

use Mouf\MoufManager;

use Mouf\Html\HtmlElement\HtmlBlock;

use Mouf\Mvc\Splash\SplashGenerateService;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Mvc\Splash\Controllers\Controller;


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
	 *
	 * @var HtmlBlock
	 */
	public $content;
	
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
			$this->exludeExtentions = array("js", "ico", "gif", "jpg", "png", "css", "woff", "ttf", "svg", "eot", "txt");
		}
		if (empty($this->exludeFolders)){
			$this->exludeFolders = array("vendor");
		}
		$this->content->addFile(__DIR__."/../../../../../views/admin/splashAdminApache.php", $this);
		$this->template->toHtml();
	}
	
	/**
	 * Writes the .htaccess file. 
	 *
	 * @Action
	 */
	public function write($selfedit, $textExtentions, $textFolders) {
		//$uri = $_SERVER["REQUEST_URI"];
		
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
		
		/*$installPos = strpos($uri, "/vendor/mouf/mouf/splashApacheConfig/write");
		if ($installPos !== FALSE) {
			$uri = substr($uri, 0, $installPos);
		}
		if (empty($uri)) {
			$uri = "/";
		}*/
		
		$this->splashGenerateService->writeHtAccess(/*$uri,*/ $exludeExtentions, $exludeFolders);
		
		header("Location: ".MOUF_URL."?selfedit=".$selfedit);
	}
	
	
}

?>
