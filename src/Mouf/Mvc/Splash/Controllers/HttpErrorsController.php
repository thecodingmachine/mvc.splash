<?php
namespace Mouf\Mvc\Splash\Controllers;

use Mouf\Html\HtmlElement\Scopable;

use Mouf\Html\HtmlElement\HtmlBlock;

use Mouf\Html\Template\TemplateInterface;
use Mouf\Html\HtmlElement\HtmlElementInterface;
use Mouf\Mvc\Splash\HtmlResponse;

/**
 * This class provides the default Splash behaviour when a HTTP 404 and HTTP 500 error is triggered.
 * Fill free to configure/override/replace this controller with your own if you want to provide
 * a customized HTTP 404/500 page. 
 * 
 * @author David Négrier
 * @Component
 */
class HttpErrorsController implements Http404HandlerInterface, Http500HandlerInterface, Scopable {
	// TODO: remove the public modifier from these properties.
	/**
	 * The template used by Splash for displaying error pages (HTTP 404 and 500)
	 *
	 * @Property
	 * @Compulsory
	 * @var TemplateInterface
	 */
	private $template;
	
	/**
	 * The content block the template will be written into.
	 * 
	 * @Property
	 * @Compulsory
	 * @var HtmlBlock
	 */
    private $contentBlock;
	
	/**
	 * Whether we should display exception stacktrace or not in HTTP 500.
	 * 
	 * @Property
	 * @var bool
	 */
    private $debugMode = true;
	
	/**
	 * Content block displayed in case of a 404 error.
	 * If not set, a default block will be used instead.
	 * 
	 * @var HtmlElementInterface
	 */
	protected $contentFor404;
	
	/**
	 * Content block displayed in case of a 500 error.
	 * If not set, a default block will be used instead.
	 *
	 * @var HtmlElementInterface
	 */
	protected $contentFor500;
	
	protected $message;
	protected $exception;
	
	public function __construct(TemplateInterface $template = null, HtmlBlock $contentBlock = null, $debugMode = true) {
		$this->template = $template;
		$this->contentBlock = $contentBlock;
		$this->debugMode = $debugMode;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mouf\Mvc\Splash\Controllers.Http404HandlerInterface::pageNotFound()
	 */
	public function pageNotFound($message) {
		$this->message = $message;
		if ($this->contentFor404) {
			$this->contentBlock->addHtmlElement($this->contentFor404);
		} else {
			$this->contentBlock->addFile(__DIR__."/../../../../views/404.php", $this);
		}

        return HtmlResponse::create($this->template, 404);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mouf\Mvc\Splash\Controllers.Http500HandlerInterface::serverError()
	 */
	public function serverError(\Exception $exception) {
		$this->exception = $exception;
		if ($this->contentFor500) {
			$this->contentBlock = $this->contentFor500;
		} else {
			$this->contentBlock->addFile(__DIR__."/../../../../views/500.php", $this);
		}

        return HtmlResponse::create($this->template, 500);
	}
	
	/**
	 * Inludes the file (useful to load a view inside the Controllers scope).
	 *
	 * @param unknown_type $file
	 */
	public function loadFile($file) {
		include $file;
	}
	
	/**
	 * Content block displayed in case of a 404 error.
	 * If not set, a default block will be used instead.
	 * 
	 * @param HtmlElementInterface $contentFor404        	
	 */
	public function setContentFor404(HtmlElementInterface $contentFor404) {
		$this->contentFor404 = $contentFor404;
		return $this;
	}
	
	/**
	 * Content block displayed in case of a 500 error.
	 * If not set, a default block will be used instead.
	 * 
	 * @param HtmlElementInterface $contentFor500        	
	 */
	public function setContentFor500(HtmlElementInterface $contentFor500) {
		$this->contentFor500 = $contentFor500;
		return $this;
	}
}

?>