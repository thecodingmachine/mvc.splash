<?php
namespace Mouf\Mvc\Splash\Controllers;

use Mouf\Html\HtmlElement\Scopable;

use Mouf\Html\HtmlElement\HtmlBlock;

use Mouf\Html\Template\TemplateInterface;

/**
 * This class provides the default Splash behaviour when a HTTP 404 and HTTP 500 error is triggered.
 * Fill free to configure/override/replace this controller with your own if you want to provide
 * a customized HTTP 404/500 page. 
 * 
 * @author David Négrier
 * @Component
 */
class HttpErrorsController implements Http404HandlerInterface, Http500HandlerInterface, Scopable {
	/**
	 * The template used by Splash for displaying error pages (HTTP 404 and 500)
	 *
	 * @Property
	 * @Compulsory
	 * @var TemplateInterface
	 */
	public $template;
	
	/**
	 * The content block the template will be written into.
	 * 
	 * @Property
	 * @Compulsory
	 * @var HtmlBlock
	 */
	public $contentBlock;
	
	/**
	 * Whether we should display exception stacktrace or not in HTTP 500.
	 * 
	 * @Property
	 * @var bool
	 */
	public $debugMode = true;
	
	protected $message;
	protected $exception;
	
	/**
	 * (non-PHPdoc)
	 * @see Mouf\Mvc\Splash\Controllers.Http404HandlerInterface::pageNotFound()
	 */
	public function pageNotFound($message) {
		$this->message = $message;
		$this->contentBlock->addFile(__DIR__."/../../../../views/404.php", $this);
		$this->template->toHtml();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mouf\Mvc\Splash\Controllers.Http500HandlerInterface::serverError()
	 */
	public function serverError(\Exception $exception) {
		$this->exception = $exception;
		$this->contentBlock->addFile(__DIR__."/../../../../views/500.php", $this);
		$this->template->toHtml();
	}
	
	/**
	 * Inludes the file (useful to load a view inside the Controllers scope).
	 *
	 * @param unknown_type $file
	 */
	public function loadFile($file) {
		include $file;
	}
}

?>