<h1>Designing a template: the PHP class</h1>

<p>In this chapter, we will mostly talk about the PHP class that is used for a template.
The HTML and CSS part that makes the template is discussed <a href="designing_css.xaja.php">here</a>.</p>

<h2>Interfaces and base classes</h2>

<p>In Splash, all templates extend the "TemplateInterface" interface.
This interface contains a number of methods that should be implemented by the developer:</p>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
interface TemplateInterface {
	public function setDefaultScope(Scopable $scope);
	public function getDefaultScope();
	public function addContentFunction($function);
	public function addContentText($text);
	public function addContentFile($fileName, Scopable $scope = null);
	public function addContentHtmlElement(HtmlElementInterface $element);
	public function addHeaderFunction($function);
	public function addHeaderText($text);
	public function addHeaderFile($fileName, Scopable $scope = null);
	public function addHeaderHtmlElement(HtmlElementInterface $element);
	public function addLeftFunction($function);
	public function addLeftText($text);
	public function addLeftFile($fileName, Scopable $scope = null);
	...
	...
	public function draw();
}
?]]><![CDATA[>]]></codehighlight>

<p>It can be quite long to extend all these methods. Hopefully, the <b>BaseTemplate</b> class is an abstract
class that provides a default implementation for all these methods, except the <b>draw</b> method.</p>
<p>When implementing your own template, we stringly advise you to use the <b>BaseTemplate</b> class.</p>

<p>Here is the code for the SplashTemplate class, the default template used in Splash and Mouf:</p>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
/**
 * Template class for Splash.
 * This class relies on /views/template/splash.php for the design
 * 
 * @Component
 */
class SplashTemplate extends BaseTemplate  {
	/**
	 * Draws the Splash page by calling the template in /views/template/splash.php
	 */
	public function draw(){
		header('Content-Type: text/html; charset=utf-8');

		include "views/splash.php";
	}
}
?]]><![CDATA[>]]></codehighlight>

<p>As you can see, the code is really simple. The "draw" method is just calling an external PHP file that contains the HTML code.</p>

<h2>The <em>view</em> page</h2>

<p>The minimal view is displayed below:</p>

<pre>
&lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"&gt;
&lt;html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"&gt;
	&lt;head&gt;
		&lt;meta http-equiv="Content-Type" content="text/html; charset=utf-8" /&gt;
		&lt;title&gt;&lt;?php print $this-&gt;title ?&gt;&lt;/title&gt;
		&lt;?php print $head ?&gt;
		&lt;?php print $this-&gt;getCssFiles() ?&gt;
		&lt;?php print $this-&gt;getJsFiles() ?&gt;
		&lt;?php $this-&gt;drawArray($this-&gt;head); ?&gt;
	&lt;/head&gt;
	&lt;body&gt;
		&lt;div id="header"&gt;
		&lt;?php if (count($this-&gt;header) != 0) { ?&gt;
			&lt;div id="nav"&gt;
				&lt;?php $this-&gt;drawArray($this-&gt;header); ?&gt;
			&lt;/div&gt;
		&lt;?php } ?&gt;
		&lt;/div&gt;
		&lt;div id="left"&gt;
			&lt;?php $this-&gt;drawArray($this-&gt;left); ?&gt;
		&lt;/div&gt;
		&lt;div id="right"&gt;
			&lt;?php $this-&gt;drawArray($this-&gt;right); ?&gt;
		&lt;/div&gt;
		&lt;div id="content"&gt;
			&lt;?php $this-&gt;drawArray($this-&gt;content); ?&gt;
		&lt;/div&gt;
		&lt;div id="footer"&gt;
			&lt;?php $this-&gt;drawArray($this-&gt;footer); ?&gt;
		&lt;/div&gt;
	&lt;/body&gt;
&lt;/html&gt;
</pre>
<br/>
<p>First, take a look at the "drawArray" method:</p>
<pre>
&lt;?php $this-&gt;drawArray($this-&gt;content); ?&gt;
</pre>
<p>This will output automatically on the page anything related to the section passed in parameter.</p>
<p>The code above is actually very minimal. Indeed, you will certainly have more HTML/CSS code to set up the template.</p>

<h2>A note about the layouts</h2>

<p>With Splash templates, there is not one unique kind of layout for the page. Instead, depending on the
developer's will, there can be several layouts.</p>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
// In this example, the template has only 1 main column
$template->addContentText('<h1>Hello world!</h1>');
$template->draw();

// In this example, the template has 1 left column and one main column
$template->addLeftText('<p>Left column</p>');
$template->addContentText('<h1>Hello world!</h1>');
$template->draw();

// In this example, the template has 1 right column and one main column
$template->addRightText('<p>Right column</p>');
$template->addContentText('<h1>Hello world!</h1>');
$template->draw();

// In this example, the template has 1 left column, 1 right column and one main column
$template->addLeftText('<p>Left column</p>');
$template->addRightText('<p>Right column</p>');
$template->addContentText('<h1>Hello world!</h1>');
$template->draw();
?]]><![CDATA[>]]></codehighlight>

<p>The 4 layouts above must be managed by your Template PHP class.</p>
<p>For these layouts, you will certainly have different CSS classes. For instance, the "#content" div might have a class "fullwidth" if there is no menus on the left and the right,
and might have a class "normalwidth" if there are both columns, etc...</p>
<p>The PHP code displaying the page will take care of selecting the right class.</p>


