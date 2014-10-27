<?php
namespace Mouf\Mvc\Splash\Routers;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Mouf\Mvc\Splash\Controllers\Http500HandlerInterface;

/**
 * This router returns transforms exceptions into HTTP 500 pages, based on the configured error controller.
 *
 * @author Kevin Nguyen
 * @author David Négrier
 */
class ExceptionRouter implements HttpKernelInterface {
	
	/**
	 * The logger
	 *
	 * @var LoggerInterface
	 */
	private $log;
	
	/**
	 * @var HttpKernelInterface
	 */
	private $router;
	
	/**
	 * The controller that will display 500 errors
	 * @var Http500HandlerInterface
	 */
	private $errorController;
	

	/**
	 * The "500" message
	 * @var string|ValueInterface
	 */
	private $message = "Page not found";
	
	public function __construct(HttpKernelInterface $router, LoggerInterface $log = null){
		$this->router = $router;
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
		if ($catch){
			try {
				$this->router->handle($request, $type, false);
			} catch (\Exception $e) {
				$this->handleException($e);
			}		
		}else{
			$this->router->handle($request, $type);
		}
	}
	
	private function handleException(\Exception $e) {
		if ($this->log != null) {
			if ($this->log instanceof LogInterface) {
				$this->log->error($e);
			} else {
				$this->log->error("Exception thrown inside a controller.", array(
						'exception' => $e
				));
			}
		} else {
			// If no logger is set, let's log in PHP error_log
			error_log($e->getMessage()." - ".$e->getTraceAsString());
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
	
	/**
	 * The "404" message
	 * @param string|ValueInterface $message
	 */
	public function setMessage($message){
		$this->message = $message;
	}
	
}