<?php
namespace Mouf\Mvc\Splash\Controllers;

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
class HttpErrorsController implements Http404HandlerInterface {
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
	 * (non-PHPdoc)
	 * @see Mouf\Mvc\Splash\Controllers.Http404HandlerInterface::pageNotFound()
	 */
	public function pageNotFound() {
		$this->contentBlock->addFile(__DIR__."/../../../../views/404.php");
		$this->template->toHtml();
	}
}

?>