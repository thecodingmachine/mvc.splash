<h1>Using Drupal Themes</h1>

<p><b>Splash</b> let you the possibility to use <b>Drupal themes</b> very simply.</p>

<h2>Installation</h2>

<p>To use a <b>Drupal theme</b>, you have to put the <b>theme directory</b> in your project. 
You can download some themes on the <a href="http://drupal.org/project/Themes">Drupal site </a>or add your own one.</p>
<p>Once done, you have to enable the <b>Drupal Template package</b> in the Mouf web interface.
Then, create an instance of <b>DrupalTemplate</b> for your theme. In the configuration panel of your instance, you will see a "templateFile" checkbox.
If you enable this option, Mouf will include the <b>template.php</b> file of your Drupal theme.
You can refer to the <a href="../install.html">Splash installation guide</a> if you don't know how to create an instance using the Mouf web interface.</p>
<p>As <b>Mouf</b> use the .info file of <b>Drupal theme</b>, only themes for Drupal version 6.x and above are supported.</p>

<h2>Sample Usage</h2>

<p>Using a <b>Drupal theme</b> is practically the same as a <b>Splash template</b>. 
If you don't know how to use <b>Splash template</b>, you should read <a href="index.xaja.php">this tutorial</a> first.
Here is a basic example, using the <b>Drupal garland theme</b>.</p>
<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
require_once 'Mouf.php';

$template = Mouf::getGarland_template();
$template->addHeaderText('title');
$template->draw();
?]]><![CDATA[>]]></codehighlight>
<p>This will display the garland template in your website with "title" as header.</p>

<h2>The DrupalTemplate Structure</h2>

<p>The <b>DrupalTemplate</b> class extends the <b>BaseTemplate</b> class. 
Therefore, it has the same <a href="index.xaja.php">base functions</a> for adding content in regions.</p>
<p>Mouf manages automatically the <b>custom regions</b> of Drupal themes using the .info file.
If the .info file doesn't exist or is empty, Mouf will throw an InfoFileException.</p>
<p>If you want to add some content to the <b>custom part</b> of the template, we would be using one of those functions: </p>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
$template->addOptionalRegionFunction($region, $function);
$template->addOptionalRegionFile($region, $fileName, Scopable $scope = null);
$template->addOptionalRegionText($region, $text);
?]]><![CDATA[>]]></codehighlight>

<h2>The custom regions</h2>

<p>Here, an example showing how to add content to a <b>custom region</b> : </p>

<codehighlight language="PHP"><![CDATA[<]]><![CDATA[?php
require_once 'Mouf.php';

$template = Mouf::getMy_template();
$template->addHeaderText('title');
$template->addOptionalRegionText('myCustomRegion', 'my text');
$template->draw();
?]]><![CDATA[>]]></codehighlight>

<h2>Session cache</h2>

<p>To avoid loading the <b>.info</b> file many times, Mouf can store the content of the <b>.info</b> file in <b>cache</b> for each session.
You just have to enable the Session Cache package in the Mouf web interface. Then, create an instance of SessionCache and modify your DrupalTemplate
instance to use it.</p>

<p>Note that you have a property for adding a <b>logger</b> to your SessionCache instance. This will help you to trace the <b>cache activity</b>.</p>
