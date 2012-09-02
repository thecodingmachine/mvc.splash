<?php
namespace Mouf\Mvc\Splash;

/**
 * The SplashUrlAnalyzer component is in charge of binding an Url to a Controller.
 * There is one and only one instance of SplashUrlAnalyzer per web application.
 * The name of the instance MUST be "splashUrlAnalyzer".
 * 
 * The SplashUrlAnalyzer has several ways to bind an URL to a Controller.
 * It can do so:
 * - using the instance name of a controller that has been instanciated with Mouf.
 * For instance, if a controller has an instance name that is "myController", then
 * the http://[myserver]/[mywebapp]/myController URL will lead to the default action
 * of that controller.
 * The http://[myserver]/[mywebapp]/myController/myAction URL will lead to the myAction action
 * of that controller. 
 * 
 * @Component
 * @RequiredInstance "splashUrlAnalyzer"
 */
class SplashUrlAnalyzer {

	/**
	 * The controller in the Url (this should be the first "directory" of the URL after the webapp.
	 *
	 * @var string
	 */
	public $controller;

	/**
	 * The action in the Url (this should be the second "directory" of the URL after the webapp.
	 *
	 * @var string
	 */
	public $action;
	
	/**
	 * An array containing all the directories after the action.
	 *
	 * @var string
	 */
	public $args;
	
	/**
	 * True if the URL analyze has been already performed.
	 *
	 * @var boolean
	 */
	private $analyzeDone;
	
	/**
	 * Analyze the URL and fills the "controller", "action" and "args" variables.
	 *
	 */
	private function analyze() {
		$redirect_uri = $_SERVER['REDIRECT_URL'];
		$pos = strpos($redirect_uri, ROOT_URL);
		$action = substr($redirect_uri, $pos+strlen(ROOT_URL));

		$array = split("/", $action);
		$this->controller = $array[0];
		$this->action = $array[1];
		$this->args = array();

		array_shift($array);
		array_shift($array);

		$this->args = $array;

		$this->analyzeDone = true;
	}
	
	/**
	 * Returns the instance of the destination controller, or null if the controller was not found.
	 *
	 * @return Controller
	 */
	public function getControllerInstance() {
		if (!$this->analyzeDone) {
			$this->analyze();
		}
		
		return MoufManager::getMoufManager()->getInstance($this->controller);
	}
	
	/**
	 * Returns the controller string, or null if the user wants the root directory.
	 *
	 */
	public function getControllerName() {
		if (!$this->analyzeDone) {
			$this->analyze();
		}
		
		return $this->controller;
	}
	
	/**
	 * Returns the action, or null if none is provided by the user.
	 *
	 * @return unknown
	 */
	public function getAction() {
		if (!$this->analyzeDone) {
			$this->analyze();
		}
		
		return $this->action;
	}
	
}

?>