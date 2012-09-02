<?php
namespace Mouf\Mvc\Splash;

/**
 * A Splash Action is an object representing an action in Splash.
 * Therefore, it contains a controller instance, and the name of the function to be called.
 * 
 * @Component
 */
class SplashAction {
	/**
	 * The controller instance to be called. 
	 *
	 * @Property
	 * @Compulsory
	 * @var Controller
	 */
	public $controller;
	/**
	 * The name of the action to be called.
	 *
	 * @Property
	 * @var string
	 */
	public $actionName;
}
?>