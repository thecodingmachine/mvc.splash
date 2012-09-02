<h1>Writing controllers</h1>

<h2>What is a controller?</h2>

<p>In Splash, a controller is a <a href="http://www.thecodingmachine.com/ext/mouf/doc/components.html">Mouf component</a>, that contains a number of <em>Actions</em>.</p>
<p><em>Actions</em> are methods that can be directly accessed from the browser.</p>

<p>There are several ways to declare a method to be an action. The most common are:</p>
<ul>
	<li>The <strong>@URL</strong> annotation</li>
	<li>The <strong>@Action</strong> annotation</li>
</ul>

<h3>The @URL annotation</h3>

<p>This is the preferred way of declaring an action:</p>

<pre class="brush: php">
&lt;?php
/**
 * This is my test controller.
 *
 * @Component
 */
class MyController extends Controller {
	
	/**
	 * My first action.
	 *
	 * @URL /path/to/my/action
	 * @param string $var1
	 * @param string $var2
	 */
	public function my_url($var1, $var2) {
		 echo "&lt;html&gt;&lt;head&gt;&lt;/head&gt;";
		 echo "&lt;body&gt;";
		 echo "var1 value is ".htmlentities($var1)." and var2 value is ".htmlentities($var2);
		 echo "&lt;/body&gt;";
	}
}
?&gt;
</pre>

<p>First thing you can see: the MyController class extends the "Controller" class provided by Splash. Also, it is a Mouf component, since we can read the "@Component"
annotation in the PHPDoc comment of the class.</p>
<p>The <strong>@URL</strong> annotation points to the web path the action is bound to.</p>

<p>The action takes 2 parameters: var1 and var2. This means that the page needs both parameters passed either in GET or POST.</p>

<p>In order to test this, we must first create an instance of the controller in Mouf.</p>
<p>We will do this using the Mouf User Interface.</p>
<p>We will start by including the MyController.php file, using the Mouf "Load components" menu.</p>

<img src="images/register_controller_file.jpg" alt="" />

<p>Once this is registered, we can go in the "create a new instance" menu and create the "my_controller".</p>
<img src="images/create_instance.jpg" alt="" />

<h3>The @Get / @Post annotations</h3>

<p>We might want to decide that an action should always be called via GET, or via POST (or PUT or DELETE if you want to provide REST services).</p>
<p>Splash makes that very easy to handle. You can just add a @Get or @Post annotation (or @Put or @Delete). Here is a sample:</p>

<pre class="brush: php">
&lt;?php
/**
 * This is a sample user controller.
 *
 * @Component
 */
class UserController extends Controller {
	
	/**
	 * Viewing the user is performed by a @Get.
	 *
	 * @URL /user
	 * @Get
	 * @param string $id
	 */
	public function viewUser($id) {
		 echo "Here, we might put the form for user ".htmlentities($id);
	}

	/**
	 * Modifying the user is performed by a @Post.
	 *
	 * @URL /user
	 * @Post
	 * @param string $id
	 */
	public function editUser($id, $name, $email) {
		 echo "Here, we might put the code to change the user object.";
	}

}
?&gt;
</pre>

<p>In the exemple above (a sample controller to view/modify users), the "/user" URL is bound to 2 different methods
based in the HTTP method used to access this URL.</p>

<h3>Parameterized URLs</h3>

<p>You can put parameters in the URLs and fetch them very easily:</p>

<pre class="brush: php">
&lt;?php
/**
 * This is a sample user controller.
 *
 * @Component
 */
class UserController extends Controller {
	
	/**
	 * Viewing the user is performed by a @Get.
	 *
	 * @URL /user/{id}/view
	 * @Get
	 * @param string $id
	 */
	public function viewUser($id) {
		 echo "Here, we might put the form for user ".htmlentities($id);
	}
}
?&gt;
</pre>

<p>Do you see the @URL annotation? The {id} part is a placeholder that will be replaced by any value found in the URL.</p>
<p>So for instance, if you access http://[server]/[appname]/user/42/view, the $id parameter will be filled with "42".</p> 

<h3>The @Action annotation</h3>

<p>The @Action parameter can replace the @URL parameter.</p>
<p>You simply put a @Action annotation in your method. The URLs to access a @Action method are always:</p>
<code>http://[server-url]/[webapp-path]/[mouf-controller-instance-name]/[action-name]?[action-parameters]</code>

<p>Here is a sample:</p>

<pre class="brush: php">
&lt;?php
/**
 * This is my test controller.
 *
 * @Component
 */
class MyController extends Controller {
	
	/**
	 * My first action.
	 *
	 * @Action
	 * @param string $var1
	 * @param string $var2
	 */
	public function my_action($var1, $var2) {
		 echo "&lt;html&gt;&lt;head&gt;&lt;/head&gt;";
		 echo "&lt;body&gt;";
		 echo "var1 value is ".htmlentities($var1)." and var2 value is ".htmlentities($var2);
		 echo "&lt;/body&gt;";
	}
}
?&gt;
</pre>

<p>The <em>my_action</em> method is a Splash action. You know this because there is a @Action annotation in the PHPDoc comment of the method.</p>

<p>Now, we can access the example page using this URL:<br/>
<code>http://[server-url]/[webapp-path]/my_controller/my_action?var1=42&var2=toto</code>
</p>

<h2>Default actions</h2>

<p>Sometimes, when using @Action annotations, we might want to have a URL that is a bit shorter than /my_webapp/my_controller/my_action.</p>
<p>Splash supports a special method called "index". If no action is provided in the URL, the index method will be called instead.</p>

<pre class="brush: php">
&lt;?php
/**
 * This is my test controller.
 *
 * @Component
 */
class MyController extends Controller {
	
	/**
	 * The action called if no action is provided in the URL.
	 *
	 * @Action
	 */
	public function index() {
		 echo "This is the index";
	}
}
?&gt;
</pre>

<p>The test page can be accessed using the URL: <code>http://[server-url]/[webapp-path]/my_controller/</code>.</p>

