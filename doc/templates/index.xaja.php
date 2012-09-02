<h1>Using templates</h1>

<p>In Splash, templates are used to make the separation between the view and the controller.
By default, an action method (@Action) in the controller does not display anything. In particular,
unlike in many frameworks, there is no "default view" associated to a controller or to an action.
This means that, as a developer, you will have to explicitly call the view, through the template.</p>

<h2>The template package</h2>

<p>Splash templates are not directly part of Splash, which means you can use them without using Splash.</p>
<p>Templates are part of the Mouf group <b>html/template</b>.</p>
<p>The <b>BaseTemplate</b> package (<b>html/template/BaseTemplate</b>) contains the template interface 
that should be used by any template. It also contains a helper class that will help you build template quickly.</p>
<p>The <b>SplashTemplate</b> package (<b>html/template/SplashTemplate</b>) contains the default HTML template
used by Splash and Mouf. You can use it as an example to build your own templates.</p>

<h2>Sample usage</h2>

<p>Here is a basic example, using the SplashTemplate (the default template):</p>
<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
/**
 * My sample action.
 *
 * @Action
 */
public function sample() {

	$template = new SplashTemplate();		
	$template->addContentFile(dirname(__FILE__).'/../views/sample/sample.php');
	$template->draw();
}
?]]><![CDATA[>]]></codehighlight>

<p>This will display the content of the sample.php file, inside the SplashTemplate.
Of course, if you are using the "Mouf" framework, the best practice is to bind 
the template to your controller using the Mouf UI:</p>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
/**
 * The template we are using by default with this controller.
 *
 * @Component
 * @Compulsory
 * @var TemplateInterface
 */
public $template

/**
 * My sample action.
 *
 * @Action
 */
public function sample() {

	$template = $this->template;		
	$template->addContentFile(dirname(__FILE__).'/../views/sample/sample.php');
	$template->draw();
}
?]]><![CDATA[>]]></codehighlight>

<h2>Template structure</h2>

<p>In Splash, all templates extend the "TemplateInterface" interface.
This interface defines a number of sections that can/should be present in a web page.</p>

<img src="../images/template_structure.png" alt="" />

<p>The developer using the template can add content in those parts.</p>

<ul>
  <li><b>The &lt;head&gt; part</b> of the template corresponds to the &lt;head&gt; tag of the HTML page.
  Therefore, you can add additional CSS of Javascript files in the &lt;head&gt; part.
  </li>
  <li><b>The header part</b> of the template usually contains the top menu.</li>
  <li><b>The content part</b> is the main part of the page.</li>
  <li><b>The left part</b> of the template usually contains a menu. If the left part is empty, the left part 
  is not displayed and the content part should use the space left by the left part.</li>  
  <li><b>The right part</b> of the template usually contains additional information (or a menu). If the right part is empty, the right part 
  is not displayed and the content part should use the space left by the right part.</li>
  <li><b>The footer part</b> of the template contains... the footer.</li>
</ul>

<p>For each of those "parts", there are methods implemented from the TemplateInterface:</p>
<p>For instance, for the content part, you can use one of those 4 methods:</p>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
public function addContentText($text);
public function addContentFunction($function);
public function addContentFile($fileName, Scopable $scope = null);
public function addContentHtmlElement(HtmlElementInterface $element);
?]]><![CDATA[>]]></codehighlight>

<p>If you want to add some content to the left part of the template, we would be using one of those functions:</p>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
public function addLeftText($text);
public function addLeftFunction($function);
public function addLeftFile($fileName, Scopable $scope = null);
public function addLeftHtmlElement(HtmlElementInterface $element);
?]]><![CDATA[>]]></codehighlight>

<h2>Adding some content to the template</h2>

<p>Let's review the features we can use to add content:</p>

<h3>The <em>addXXXText</em> methods</h3>

<p>This is the simplest way to add some code in a template. Here is a sample usage:</p>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
/**
 * @Action
 */
public function sample() {
	$template = $this->template;
	// This will add some text in the main part of the page.
	$template->addContentText('<h1>Hello world!</h1>');
	$template->draw();
}
?]]><![CDATA[>]]></codehighlight>
<br/>
<h3>The <em>addXXXFunction</em> methods</h3>

<p>This will register a function or a method that will be called to generate some HTML.</p>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
/**
 * @Action
 */
public function sample() {
	$template = $this->template;
	// This will use the "displayMenu" function in the left menu.
	$template->addLeftFunction('displayMenu');
	$template->draw();
}
?]]><![CDATA[>]]></codehighlight>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
function displayMenu() {
	echo "This is my menu";
}
?]]><![CDATA[>]]></codehighlight>

<p>You can use additional parameters if you want. For instance:</p>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
/**
 * @Action
 */
public function sample() {
	$template = $this->template;
	// This will use the "displayMenu" function in the left menu.
	$template->addLeftFunction('displayMenu', 42);
	$template->draw();
}
?]]><![CDATA[>]]></codehighlight>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
function displayMenu($number) {
	echo "This parameter was passed: ".$number;
}
?]]><![CDATA[>]]></codehighlight>

<p>Finally, the first parameter is a <a href="http://us2.php.net/manual/en/language.pseudo-types.php">PHP callback</a>.</p>
<p>Therefore, you could call a method of the current controller using this syntax:</p>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
$template->addLeftFunction(array($this, 'displayMenu'), 42);
?]]><![CDATA[>]]></codehighlight>
<br/>
<h3>The <em>addXXXFile</em> methods</h3>

<p>These are maybe the most useful set of methods. These will directly load an HTML or PHP file and include it in the template.</p>
<p>Here is a sample:</p>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
$template->addContentFile('view.php');
?]]><![CDATA[>]]></codehighlight>

<p>The <b>view.php</b> file can contain any PHP code:</p>
<b style="text-align: center">view.php</b>
<codehighlight language="HTML"><h1>Hello world!</h1></codehighlight>

<p>The addXXXFile methods accept an optional parameter: the <b>Scope</b>.
This scope will tell what refers to the "$this" keywork inside the view file. For instance:</p>

<b style="text-align: center">MyController.php</b>
<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php

protected $value;

/**
 * @Action
 */
public function sample() {
	$this->value = 42;

	$template = $this->template;
	// This will use the "displayMenu" function in the left menu.
	$template->addContentFile('view.php', $this);
	$template->draw();
}
?]]><![CDATA[>]]></codehighlight>

<b style="text-align: center">view.php</b>
<codehighlight language="PHP">Value passed: <![CDATA[<]]><![CDATA[?php echo $this->value; ?]]><![CDATA[>]]></codehighlight>

<p>Please note that:</p>
<ul>
  <li>Fields accessed from the view should be protected or public (the view cannot access "private" fields)</li>
  <li>The "Scope" should implement the "Scopable" interface. Luckily, all controllers implement this interface because they inherit
  the "Controller" class that implements the "Scopable" interface.</li>
</ul>

<br/>
<h3>The <em>addXXXHtmlElement</em> methods</h3>

<p>Finally, the last way to add some text in a template is to use an object extending the <b>HtmlElementInterface</b> interface.</p>
<p>This interface is very simple:</p>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
/**
 * Every object extending this interface can be rendered in an HTML page, 
 * using the toHtml function.
 */
interface HtmlElementInterface {
	/**
	 * Renders the object in HTML.
	 * The Html is echoed directly into the output.
	 *
	 */
	function toHtml();
}
?]]><![CDATA[>]]></codehighlight>

<p>Most elements that can be rendered graphically in Mouf will use this interface. This way, you can add elements directly in the
template, without any code, just using the Mouf UI.</p>

<h2>Additional utility methods</h2>

<p>Finally, the developers using a template implementing the TemplateInterface can use a few useful methods, like:</p>
<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
/**
 * Sets the title for the HTML page
 * @return SplashTemplate
 */
public function setTitle($title);

/**
 * Gets the title for the HTML page
 */
public function getTitle();

/**
 * Adds a css file to the list of css files loaded.
 * @return SplashTemplate
 */
public function addCssFile($cssUrl);

/**
 * Adds a css file to the list of css files loaded.
 * @return SplashTemplate
 */
public function addJsFile($jsUrl);
?]]><![CDATA[>]]></codehighlight>

<h2>Final note</h2>

<p>As a final note, all the methods we have seen so for are returning the template itself.</p>
<p>Therefore, you can write things like:</p>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
$template->addContentFile('view.php', $this)
         ->addLeftFunction(array($this, 'displayMenu'), 42)
         ->setTitle('myTitle');
?]]><![CDATA[>]]></codehighlight>

<p>Some people find this useful and elegant, other people don't like it. Anyway, you can use this syntax if you like.</p>