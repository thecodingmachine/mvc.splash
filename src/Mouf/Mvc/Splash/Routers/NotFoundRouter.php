<?php
namespace Mouf\Mvc\Splash\Routers;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Mouf\Mvc\Splash\Controllers\Http404HandlerInterface;
use Mouf\Utils\Value\ValueInterface;
use Mouf\Utils\Value\ValueUtils;
use Symfony\Component\BrowserKit\Response;

class NotFoundRouter implements HttpKernelInterface {
	
	/**
	 * The logger used by Splash
	 *
	 * Note: accepts both old and new PSR-3 compatible logger
	 *
	 * @var LoggerInterface
	 */
	private $log;
	
	/**
	 * The "404" message
	 * @var string|ValueInterface
	 */
	private $message = "Page not found";
	
	/**
	 * @var Http404HandlerInterface
	 */
	private $pageNotFoundController;
	
	
	public function __construct(Http404HandlerInterface $pageNotFoundController, LoggerInterface $log = null){
		$this->pageNotFoundController = $pageNotFoundController;
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
		$message = ValueUtils::val($this->message); 
		ob_start();
		$this->pageNotFoundController->pageNotFound($message);
		$html = ob_get_clean();
		return new Response($html);
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