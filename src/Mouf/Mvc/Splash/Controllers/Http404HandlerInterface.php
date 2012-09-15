<?php 
namespace Mouf\Mvc\Splash\Controllers;

/**
 * Classes implementing this interface can be used when a HTTP 404 error is triggered.
 * 
 * The clas smust be registered in the "splash" instance to be called.
 * 
 * @author David Négrier
 */
interface Http404HandlerInterface {
	
	/**
	 * This function is called when a HTTP 404 error is triggered by the user.
	 */
	public function pageNotFound();
}

?>